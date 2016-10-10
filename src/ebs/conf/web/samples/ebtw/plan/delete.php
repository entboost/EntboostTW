<?php
include dirname(__FILE__).'/../plan/preferences.php';
require_once dirname(__FILE__).'/../plan/include.php';
	
	$LOCAL_ACTION_TYPE = 3; //删除操作

	$output = true;
	
	//定义请求参数中主键的变量名称
	$pkFieldName = $PTRIdFieldName;
	
	//验证必要条件
	$pid = get_request_param($pkFieldName);
	if (empty($pid)) {
		ResultHandle::missedPrimaryKeyErrToJsonAndOutput($pkFieldName, $output);
		return;
	}
	$wheres = array($pkFieldName=>new SQLParam($pid));
	$checkDigits = array($pkFieldName); //数字校验条件
	$userId = $_SESSION[USER_ID_NAME];
	$instance = PlanService::get_instance();
	
	//验证对本记录是否有操作权限
	$shareType = 0;
	if (!DataAuthority::isAuthority($LOCAL_ACTION_TYPE, $PTRType, $shareType, $userId, $existRows, 'plan_id, create_uid, open_flag', $wheres, $checkDigits, $instance, 1, SQLParamComb_TYPE_AND, false, $outErrMsg, $json)) {
		if (!empty($json)) {
			if ($output) echo $json;
			return;
		}
		$json = ResultHandle::noAuthErrToJsonAndOutput($output);
		return;
	}
	
	$result = $instance->delete($wheres, $checkDigits);
	$json = ResultHandle::deletedResultToJsonAndOutput($result, $output);