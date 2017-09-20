<?php
$ECHO_MODE = 'json'; //输出类型
require_once dirname(__FILE__).'/../include.php';
require_once dirname(__FILE__).'/../foundation/UserDefineService.class.php';
	$output = true;
	
	define('USER_TYPE_ATTENDANCE_MANAGER', 1); //考勤专员
	
	$FIELD_NAME_USER_TYPE = 'user_type'; //用户类型
	$FIELD_NAME_TARGET_USER_NAME = 'target_user_name'; //目标用户的名称
	
	//检查字典类型条件
	$userType = get_request_param($FIELD_NAME_USER_TYPE);
	if (!isset($userType) || !in_array($userType, array(USER_TYPE_ATTENDANCE_MANAGER))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_USER_TYPE, $output);
		return;
	}
	
	$targetUserName = get_request_param($FIELD_NAME_TARGET_USER_NAME);
	
	$userId = $_SESSION[USER_ID_NAME];
	$entCode = $_SESSION[USER_ENTERPRISE_CODE];
	$groupCodes = array(); //待定：补充查找当前登录用户所属的部门列表
	
	if ($userType==USER_TYPE_ATTENDANCE_MANAGER) { //查询'考勤专员'字典
		$list = UserDefineService::get_instance()->getAttendanceManagers($entCode, $groupCodes, $userId, 0, null, $targetUserName);
		if ($list===false) {
			$errMsg = 'getAttendanceManagers error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		ResultHandle::listedResultToJsonAndOutput($list, $output);
	} else {
	
	}