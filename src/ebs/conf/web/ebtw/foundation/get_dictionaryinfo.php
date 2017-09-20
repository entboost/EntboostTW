<?php 
$ECHO_MODE = 'json'; //输出类型
require_once dirname(__FILE__).'/../include.php';
require_once dirname(__FILE__).'/../foundation/DictionaryInfoService.class.php';
	$output = true;
	
	define('DICT_TYPE_FURLOUGH', 1); //请假类型
	
	$FIELD_NAME_DICT_TYPE = 'dict_type'; //字典类型
	
	//检查字典类型条件
	$dictType = get_request_param($FIELD_NAME_DICT_TYPE);
	if (!isset($dictType) || !in_array($dictType, array(DICT_TYPE_FURLOUGH))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_DICT_TYPE, $output);
		return;
	}
	
	$userId = $_SESSION[USER_ID_NAME];
	$entCode = $_SESSION[USER_ENTERPRISE_CODE];
	$groupCodes = array(); //待定：补充查找当前登录用户所属的部门列表	
	
	if ($dictType==DICT_TYPE_FURLOUGH) { //查询'请假类型'字典
		$infos = DictionaryInfoService::get_instance()->getHolidayInfos($entCode, $groupCodes, $userId, 0);
		if ($infos!==false)
			ResultHandle::listedResultToJsonAndOutput($infos, $output);
	} else {
		
	}