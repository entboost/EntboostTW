<?php
require_once dirname(__FILE__).'/include.php';

	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);

	if (empty($formObj)) {
		$formObj = new EBShareUserForm();
		$formObj->setValuesFromRequest();
	}
	
	//验证必填字段
// 	if (!$formObj->validNotEmpty('share_id', $outErrMsg)) {	
	if (empty($formObj->share_id)) {
		if (!$formObj->validNotEmpty('from_id, from_type', $outErrMsg)) {
			$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($outErrMsg, $output);
			return;
		}
	}
	//字段值合法性校验
	if (!$formObj->validFormFields($json, $output)) {
		return;
	}
	
	$wheres = $formObj->createWhereConditions();
	$whereType = SQLParamComb::$TYPE_AND;
	$checkDigits = $formObj->createCheckDigits();
	$orderby = $formObj->getOrderby();
	$forCount = $formObj->{REQUEST_FOR_COUNT}; //get_request_param(REQUEST_FOR_COUNT);
	$userId = $_SESSION[USER_ID_NAME];
	
	$instance = ShareUserService::get_instance();
	$fieldNames = $instance->fieldNamesAfterRemovedSome();
	
	$tableName1 = $instance->tableName;
	
	$conditions = array();
	$joinCondition = 't_a.share_uid = t_b.user_id';
	
	include dirname(__FILE__).'/../include_list/include_list_join_user_account.php';
	//include dirname(__FILE__).'/../include_list/include_list_share_user_account.php';
	//include dirname(__FILE__).'/../include_list/include_list_general.php';
	