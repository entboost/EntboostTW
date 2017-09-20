<?php
require_once dirname(__FILE__).'/../classification/include.php';

	$formObj = new EBClassificationForm();
	$formObj->setValuesFromRequest();
	$wheres = $formObj->createWhereConditions();
	
	//强制添加操作权限(待定：另外几种情况)
	$userId = $_SESSION[USER_ID_NAME];
	$wheres['user_id'] = $userId;
	
	if (!empty($formObj->class_name_lk)) //把class_name_lk转换为like形式的查询条件
		AbstractService::changeToSQLParam($wheres, 'class_name_lk', 'class_name', 'like', '%'.$formObj->class_name_lk.'%');
	
	if (!empty($formObj->create_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'create_time_s', 'create_time', '>=', $formObj->create_time_s);
	
	if (!empty($formObj->create_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'create_time_e', 'create_time', '<=', $formObj->create_time_e);
	
	if (!empty($formObj->last_modify_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'last_modify_time_s', 'last_modify_time', '>=', $formObj->last_modify_time_s);
	
	if (!empty($formObj->last_modify_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'last_modify_time_e', 'last_modify_time', '<=', $formObj->last_modify_time_e);
	
	$whereType = SQLParamComb::$TYPE_AND;
	$checkDigits = $formObj->createCheckDigits();
	$forCount = $formObj->{REQUEST_FOR_COUNT};//get_request_param(REQUEST_FOR_COUNT);
	
	$instance = ClassificationService::get_instance();
	
	if ($forCount!=1) { //查询获取记录列表
		$minimum = $formObj->{REQUEST_FETCH_MINIMUM};//@$_REQUEST[REQUEST_FETCH_MINIMUM];
		if (isset($minimum))
			$fieldNames = 'class_id, class_name';
		else
			$fieldNames = $instance->fieldNamesAfterRemovedSome(array('user_id'));
	}
	
	$output = true;
	include dirname(__FILE__).'/../include_list/include_list_general.php';
