<?php
include dirname(__FILE__).'/../attendance/preferences.php';
//$ECHO_MODE = 'json'; //输出类型
require_once dirname(__FILE__).'/../attendance/include.php';
require_once dirname(__FILE__).'/../attendance/attendance_functions.php';

//验证必填字段
$queryType = $formObj->{REQUEST_QUERY_TYPE};
if (!in_array($queryType, array(1))) {
	$errMsg = REQUEST_QUERY_TYPE.' is not matched';
	$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
	return;
}
