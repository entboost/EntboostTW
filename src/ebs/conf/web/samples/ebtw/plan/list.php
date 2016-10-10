<?php
include dirname(__FILE__).'/../plan/preferences.php';
require_once dirname(__FILE__).'/../plan/include.php';

	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);

	if (empty($formObj)) {
		$formObj = new EBPlanForm();
		$formObj->setValuesFromRequest();
	}
	
	$wheres = $formObj->createWhereConditions();
	$pkFieldName = $PTRIdFieldName;
	
	//验证必填字段
	$queryType = $formObj->{REQUEST_QUERY_TYPE};
	if (!in_array($queryType, array('1', '2', '3', '4', '5', '7'))) {
		$errMsg = REQUEST_QUERY_TYPE+' is not matched';
		$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return;
	}
	//验证字段合法性
	if (!$formObj->validFormFields($json, $output)) {
		return;
	}
	
	if (!empty($formObj->plan_name_lk)) //把plan_name_lk转换为like形式的查询条件
		AbstractService::changeToSQLParam($wheres, 'plan_name_lk', 'plan_name', 'like', '%'.$formObj->plan_name_lk.'%');
	
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
	
	if ($formObj->status_uncomplete==1) //未完成的计划
		AbstractService::changeToSQLParam($wheres, 'status_uncomplete', 'status', '<=', 5);
	
// 	if ($formObj->status_reviewing==1) //评审中的计划 条件已废弃
// 		AbstractService::changeToSQLParamComb($wheres, 'status_reviewing', 'status', new SQLParamComb(array('status'=>array(2,3)), SQLParamComb::$TYPE_OR));
	
	$fromType = $PTRType;
	$whereType = SQLParamComb::$TYPE_AND;
	$checkDigits = $formObj->createCheckDigits();
	$orderby = $formObj->getOrderby();
	$forCount = $formObj->{REQUEST_FOR_COUNT};
	$userId = $_SESSION[USER_ID_NAME];
	
	$instance = PlanService::get_instance();
	
	$fieldNames = $instance->fieldNamesAfterRemovedSome(array('modify_count'));
	$tableName1 = 'eb_plan_info_t';
	
	$finalOutput = $output;
	$output = false;
	$shareType = 0;
	//1 主键查询 或 个人计划
	//2 评审计划(当前用户是评审人)
	//3 共享计划(当前用户是被共享人)
	//4 下级计划
	//5 团队计划
	//7 被关联的计划
	if ($queryType==1) { //1=主键查询 或 个人计划
		$wheres['create_uid'] = $userId;
		include dirname(__FILE__).'/../include_list/include_list_general.php';
	} else if ($queryType==2 || $queryType==3) { //2=评审计划(当前用户是评审人)；//3=共享计划(当前用户是被共享人)
		//$gotShareUser = true;
		$shareType = ($queryType==2)?1:3;
		if ($queryType==3)
			$wheres2 = array('valid_flag'=>1);
		
		//当查询评审计划的数量时，只统计有效记录
		if ($queryType==2 && $forCount==1) {
			$wheres2 = array('valid_flag'=>1);
		}
			
		if(!empty($orderby)) {
			$orderby = $instance->insertTableNameAliasPrefix($orderby, 't_a.');
			$orderby = preg_replace('/t_a.su_create_time/i', 'su_create_time', $orderby);
		}
			
		include dirname(__FILE__).'/../include_list/include_list_share_user.php';
	} else if ($queryType==4) { //4=下级计划
		$ownerFieldName = 'create_uid';
		$open_flag_str = ' and t_a.open_flag<>2';
		
		if(!empty($orderby))
			$orderby = $instance->insertTableNameAliasPrefix($orderby, 't_a.');
		
		include dirname(__FILE__).'/../include_list/include_list_subordinate.php';
	} else if ($queryType==5) { //5=团队计划
		$ownerFieldName = 'create_uid';
		$open_flag_str = ' and t_a.open_flag=1';
		
		$fieldNames3 = ', t_d.account as user_account ';
		$tableName3 =  ', user_account_t t_d ';
		$tableJoinCondition3 = ' and t_d.user_id= t_a.create_uid ';

		if(!empty($orderby))
			$orderby = $instance->insertTableNameAliasPrefix($orderby, 't_a.');
		
		include dirname(__FILE__).'/../include_list/include_list_plan_colleagues.php';
	} else if ($queryType==7) { //7=被关联的计划
		$taskIdName = 'task_id';
		if (!EBModelBase::checkDigit($formObj->{$taskIdName}, $outErrMsg, $taskIdName)) {
			$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($taskIdName, $finalOutput);
			return;
		}
		
		//验证当前用户对指定的任务(task_id)是否有操作权限
// 		$shareType = 0;
// 		if (!DataAuthority::isAuthority(0, 2, $shareType, $userId, $existRows, 'task_id, create_uid, open_flag', array($taskIdName=>$formObj->{$taskIdName}), array($taskIdName), TaskService::get_instance(), 1, SQLParamComb_TYPE_AND, false, $outErrMsg, $json)) {
// 			if (!empty($json)) {
// 				if ($finalOutput)
// 					echo $json;
// 				return;
// 			}
// 			$json = ResultHandle::noAuthErrToJsonAndOutput($finalOutput);
// 			return;
// 		}
		
		//执行查询
		$wheres = array($PTRIdFieldName=>$formObj->{$PTRIdFieldName});
		include dirname(__FILE__).'/../plan/include_list_plan_task.php';
	}
	
	if ($forCount!=1) {
		//获取(评审人、共享人等)资料
		$validFlag = 1;
		$json = completing_list_shareusers('list', $PTRType, $json, NULL, 0, NULL, $validFlag, $isQuery);
		//$json = completing_list_shareusers('list', $PTRType, $json, NULL, $shareType, $userId, 1, $isQuery);
		//整理数据操作权限
		//$json = DataAuthority::reorganizeAllowedActions($PTRType, $shareType, $json, $userId);
		$json = DataAuthority::reorganizeAllowedActions($PTRType, 1, $json, $userId);
		$json = DataAuthority::reorganizeAllowedActions($PTRType, 3, $json, $userId);
	}
	
	if ($finalOutput)
		echo $json;