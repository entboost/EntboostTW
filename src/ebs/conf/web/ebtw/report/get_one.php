<?php
include dirname(__FILE__).'/../report/preferences.php';
require_once dirname(__FILE__).'/../report/include.php';
	
	$LOCAL_ACTION_TYPE = 0; //查看操作

	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);
	
	if (empty($formObj)) {
		$formObj = new EBReportForm();
		$formObj->setValuesFromRequest();
	}
	
	//验证必要条件
	//$pid = get_request_param($PTRIdFieldName);
	$pid = $formObj->{$PTRIdFieldName};
	if (empty($pid)) {
		$json = ResultHandle::missedPrimaryKeyErrToJsonAndOutput($PTRIdFieldName, $output);
		return;
	}
	
	$wheres = array();
	$checkDigits = array();
	$wheres[$PTRIdFieldName] = new SQLParam($pid, $PTRIdFieldName);
	array_push($checkDigits, $PTRIdFieldName);//追加数字校验条件
	$userId = $_SESSION[USER_ID_NAME];
	$instance = ReportService::get_instance();
	
	//定义输出字段
	$fieldNames = $instance->fieldNamesAfterRemovedSome(array('modify_count'));

	//验证对本记录是否有操作权限
	$shareType = 0;
	if (!DataAuthority::isAuthority($LOCAL_ACTION_TYPE, $PTRType, $shareType, $userId, $existRows, $fieldNames, $wheres, $checkDigits, $instance, 1, SQLParamComb_TYPE_AND, false, $outErrMsg, $json)) {
		if (!empty($json)) {
			if ($output) echo $json;
			return;
		}
		$json = ResultHandle::noAuthErrToJsonAndOutput($output);
		return;
	}
	
	//执行查询
	$result = $instance->search($fieldNames, $wheres, $checkDigits, null, 1, 0, SQLParamComb::$TYPE_AND, $outErrMsg);
	//处理查询结果
	$json = ResultHandle::listedResultToJsonAndOutput($result, false, $outErrMsg);
	$objs = get_results_from_json($json, $tmpObj);
	if (!empty($objs)) {
		//统计用户当日进行的计划和任务活动(操作日志)数量(自动汇报功能使用)
		$opTypes = $PlanTaskOpTypes;
		$countRuleOpTypes = $opTypes;
		$countRules = array(/*'all'=>null, */'plan_task'=>$countRuleOpTypes);
		$json = completing_list_count_of_operaterecords_by_userid_and_createtime('list', array(1,2), $opTypes, $countRules, true, $json, $isQuery);
		//log_info($json);
			
		//统计日志数量、日志分类(评阅和评论)数量
		$opTypes = NULL;
		$countRules = array('all'=>null, 'discuss'=>array(3,4,5), 'review'=>array(20,21,22,23), 'edit'=>array(50, 51, 60, 61, 70, 71));
		$json = completing_list_count_of_operaterecords('list', $PTRType, $opTypes, NULL, $countRules, true, $json, $isQuery);
		//log_info($json);
		
		if (/*!$output && */!isset($formObj->fetch_authority_info) || $formObj->fetch_authority_info) {
			//查询关联人员资料
			$shareType = 0;
			$json = completing_list_shareusers('list', $PTRType, $json, NULL, $shareType, NULL, 1, $isQuery);
			//$json = completing_list_shareusers('list', $PTRType, $json, NULL, 1, NULL, 0, $isQuery); //获取已失效的评阅人
			
			//整理数据操作权限
			//$json = completing_list_shareusers('prop', $PTRType, $json, NULL, 0, $userId, 1, $isQuery); //待定：可能要去掉，以后再确认
			//验证当前用户$shareType的权限
			$json = DataAuthority::reorganizeAllowedActions($PTRType, 1, $json, $userId);
		}
		
		get_first_entity_from_json($json, $entity, $tmpObj);
	}
	
	if ($output)
		echo $json;
	