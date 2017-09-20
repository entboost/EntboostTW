<?php
require_once dirname(__FILE__).'/../useraccount/include.php';

	$output = true;
	$FIELD_NAME_SEARCH_TYPE = 'search_type'; //搜索类型
	$FIELD_NAME_SEARCH_VALUE = 'search_value'; //搜索值

	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
	$entCode = $_SESSION[USER_ENTERPRISE_CODE]; //企业编号
	
	//检查搜索类型条件
	$searchType = get_request_param($FIELD_NAME_SEARCH_TYPE);
	if (!isset($searchType) || !in_array($searchType, array(1, 11, 12, 13))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_SEARCH_TYPE, $output);
		return;
	}
	
	$searchValue = get_request_param($FIELD_NAME_SEARCH_VALUE);
	
	$uaInstance = UserAccountService::get_instance();
	$dtpInstance = DepartmentInfoService::get_instance();
	
	$finalResult = array();
	if ($searchType==1) { //查询指定部门的成员列表
		$finalResult = $uaInstance->getMembers($entCode, $searchValue);
		if ($finalResult===false) {
			$errMsg = 'getMembers error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
	} else 	if ($searchType==11) { //查询当前用户所在企业的简要信息
		$result = $dtpInstance->getEnterpriseInfo($entCode);
		if ($result===false) {
			$errMsg = 'getEnterpriseInfo error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		foreach ($result as $entity)
			array_push($finalResult, array('data_id'=>$entity['ent_id'], 'data_name'=>$entity['ent_name']));
	} else if ($searchType==12) { //查询部门列表
		$result = $dtpInstance->getGroupInfos($entCode, 0, null, $searchValue);
		if ($result===false) {
			$errMsg = 'getGroupInfos error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		foreach ($result as $entity)
			array_push($finalResult, array('data_id'=>$entity['group_id'], 'data_name'=>$entity['dep_name']));	
	} else if ($searchType==13) { //查询用户列表
		$result = $uaInstance->getUsers($entCode, $searchValue);
		if ($result===false) {
			$errMsg = 'getUsers error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		foreach ($result as $entity)
			array_push($finalResult, array('data_id'=>$entity['emp_uid'], 'data_name'=>$entity['username'], 'data_extprop'=>$entity['user_account']));
	}
	
	ResultHandle::listedResultToJsonAndOutput($finalResult, $output);
	