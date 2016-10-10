<?php
include dirname(__FILE__).'/../report/preferences.php';
require_once dirname(__FILE__).'/../report/include.php';

	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);

	if (empty($formObj)) {
		$formObj = new EBReportForm();
		$formObj->setValuesFromRequest();
	}
	
	$wheres = $formObj->createWhereConditions();
	$pkFieldName = $PTRIdFieldName;
	
	//验证必填字段
	$queryType = $formObj->{REQUEST_QUERY_TYPE};
	if (!in_array($queryType, array('1', '2', '3', '7'))) {
		$errMsg = REQUEST_QUERY_TYPE+' is not matched';
		$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return;
	}
	if (!in_array($formObj->daily, array('0', '1'))) {
		$errMsg = 'daily is not matched';
		$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return;
	}
	//验证字段合法性
	if (!$formObj->validFormFields($json, $output)) {
		return;
	}	
	
	if (!empty($formObj->report_work_lk)) { //把report_work_lk转换为like形式的查询条件
		array_push($wheres, new SQLParamComb(array(
				'completed_work'=>new SQLParam('%'.$formObj->report_work_lk.'%', 'completed_work', 'like'),
				'uncompleted_work'=>new SQLParam('%'.$formObj->report_work_lk.'%', 'uncompleted_work', 'like')
		), SQLParamComb_TYPE_OR));
		
		AbstractService::removeWhereCondition($wheres, 'report_work_lk');
	}
	
	/*=====松散的时间范围条件=====*/
	include dirname(__FILE__).'/../include_list/include_list_search_time_condition.php';
		
	if (!empty($formObj->start_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'start_time_s', 'start_time', '>=', $formObj->start_time_s);
	
	if (!empty($formObj->start_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'start_time_e', 'start_time', '<=', $formObj->start_time_e);
	
	if (!empty($formObj->stop_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'stop_time_s', 'stop_time', '>=', $formObj->stop_time_s);
	
	if (!empty($formObj->stop_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'stop_time_e', 'stop_time', '<=', $formObj->stop_time_e);
	
	if (!empty($formObj->create_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'create_time_s', 'create_time', '>=', $formObj->create_time_s);
	
	if (!empty($formObj->create_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'create_time_e', 'create_time', '<=', $formObj->create_time_e);
	
	if (!empty($formObj->last_modify_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'last_modify_time_s', 'last_modify_time', '>=', $formObj->last_modify_time_s);
	
	if (!empty($formObj->last_modify_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'last_modify_time_e', 'last_modify_time', '<=', $formObj->last_modify_time_e);
	
	if ($formObj->daily==0) //普通报告
		AbstractService::changeToSQLParam($wheres, 'daily', 'period', '<>', 1);
	else if ($formObj->daily==1) //日报
		AbstractService::changeToSQLParam($wheres, 'daily', 'period', '=', 1);
	
	if ($formObj->this_month==1) {
		$today = date("Y-m-d");
		$days = getSepcialDaysOfMonth($today);
		array_push($wheres, new SQLParamComb(array(
					'create_time'=>array(new SQLParam($days->first_day.' 00:00:00', 'create_time', '>='), new SQLParam($days->last_day.' 23:59:59', 'create_time', '<='))
				)));
		AbstractService::removeWhereCondition($wheres, 'this_month');
	}
	
	$fromType = $PTRType;
	$whereType = SQLParamComb::$TYPE_AND;
	$checkDigits = $formObj->createCheckDigits();
	
	$forCount = $formObj->{REQUEST_FOR_COUNT};//get_request_param(REQUEST_FOR_COUNT);
	$userId = $_SESSION[USER_ID_NAME];
	
	$instance = ReportService::get_instance();
	//$instance1 = ShareUserService::get_instance();
	
	$fieldNames = $instance->fieldNamesAfterRemovedSome(array('modify_count'));
	$tableName1 = 'eb_report_info_t';
	
	$finalOutput = $output;
	$output = false;
	$shareType = 0;
	//1 我提交的报告
	//2 要我评阅的报告
	//3 下级报告
	if ($queryType==1) { //我提交的报告
		$wheres['report_uid'] = $userId;
		
		include dirname(__FILE__).'/../include_list/include_list_general.php';
	} else if ($queryType==2) { //要我评阅的报告
		$shareType = 1;
		
		include dirname(__FILE__).'/../include_list/include_list_share_user.php';
	} else if ($queryType==3) { //下级报告
		$ownerFieldName = 'report_uid';
		$open_flag_str = '';// and t_a.open_flag<>2';
		
		include dirname(__FILE__).'/../include_list/include_list_subordinate.php';
		
		//补全当前用户各部门内下级成员的用户资料
		if ($forCount!=1) {
			$results = get_results_from_json($json, $tmpObj);
// 			if (!empty($results)) {
			$json1 = get_mysubordinates();
			$results1 = get_results_from_json($json1, $tmpObj1);
			$tmpObj->mysubordinates = $results1;
			
			$json = json_encode($tmpObj);
// 			}
		}
	} else if ($queryType==7) { //自动汇报数量
		//统计一个报告对应的用户在对应时间范围内进行的计划和任务活动(操作日志)数量(自动汇报功能使用)
		
		if (isset($formObj->{$pkFieldName}) && !$formObj->checkDigit($formObj->{$pkFieldName}, $errMsg)) {
			$json = ResultHandle::validNotDigitErrToJsonAndOutput($errMsg, $finalOutput);
			return;
		}
		
		//验证当前用户对指定的报告(report_id)是否有操作权限
		$shareType = 0;
		if (!DataAuthority::isAuthority(0, 3, $shareType, $userId, $existRows, 'report_id, report_uid, open_flag', array($pkFieldName=>$formObj->{$pkFieldName}), array('report_id'), $instance, 1, SQLParamComb_TYPE_AND, false, $outErrMsg, $json)) {
			if (!empty($json)) {
				if ($finalOutput)
					echo $json;
				return;
			}
			$json = ResultHandle::noAuthErrToJsonAndOutput($finalOutput);
			return;
		}
		
		$opTypes = $PlanTaskOpTypes;
		$countRuleOpTypes = $opTypes;
		$ruleName = 'plan_task';
		$countRules = array($ruleName=>$countRuleOpTypes);
		
		$results = array();
		$result = new stdClass();
		$result->{$pkFieldName} = $formObj->{$pkFieldName};
		$result->report_uid = $formObj->report_uid;
		$result->start_time = $formObj->start_time;
		$result->stop_time = $formObj->stop_time;
		array_push($results, $result);
		
		$datetimeAndUseridConditions = array();
		$datePart = substr($formObj->start_time, 0, 10);
		$key = $formObj->report_uid.'|'.$datePart;
		$datetimeAndUseridConditions[$key] = array('create_time_s'=>$datePart.' 00:00:00', 'create_time_e'=>$datePart.' 23:59:59', 'user_id'=>$formObj->report_uid);
		
		completing_list_count_of_operaterecords_using_resultsobj_by_userid_and_createtime('list', array(1,2), $opTypes, $datetimeAndUseridConditions, $countRules, true, $results, $isQuery);
		//log_info($results[0]->countedOprs);
		$count = $results[0]->countedOprs[$ruleName];
		$json = '{"code":0, "count":'.$count.'}';
	}
	
	if ($forCount!=1) {
		//统计用户当日进行的计划和任务活动(操作日志)数量(自动汇报功能使用)
		$opTypes = $PlanTaskOpTypes;
		$countRuleOpTypes = $opTypes;
		$countRules = array(/*'all'=>null, */'plan_task'=>$countRuleOpTypes);
		$json = completing_list_count_of_operaterecords_by_userid_and_createtime('list', array(1,2), $opTypes, $countRules, false, $json, $isQuery);
		//log_info($json);
		
		//统计日志数量、日志分类(评阅和评论)数量
		$opTypes = NULL;
		$countRules = array('all'=>null, 'discuss'=>array(3,4,5), 'review'=>array(20,21,22,23), 'edit'=>array(50, 51, 60, 61, 70, 71));
		$json = completing_list_count_of_operaterecords('list', $PTRType, $opTypes, NULL, $countRules, false, $json, $isQuery);
		//log_info($json);
		
		
		
		//查询关联人员资料(评阅人、共享用户、关注用户)
		$shareType = 0;//查询全部类型
		$validFlag = 1;
		$json = completing_list_shareusers('list', $PTRType, $json, NULL, $shareType, NULL, $validFlag, $isQuery);
		$json = completing_list_shareusers('list', $PTRType, $json, NULL, 1, NULL, 0, $isQuery); //获取已失效的评阅人
		
		//整理当前用户数据操作权限
// 		$json = completing_list_shareusers('list', $PTRType, $json, NULL, $shareType, $userId, 1, $isQuery); //待定：可能要去掉，以后再确认
		$json = DataAuthority::reorganizeAllowedActions($PTRType, 1, $json, $userId); //1=验证评阅人权限
	}
	
	if ($finalOutput)
		echo $json;	