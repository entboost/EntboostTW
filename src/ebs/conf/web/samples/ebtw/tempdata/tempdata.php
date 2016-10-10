<?php
//require_once dirname(__FILE__).'/../tempdata/include.php';
require_once dirname(__FILE__).'/../usual_function.php';
require_once dirname(__FILE__).'/../model/EBTempData.class.php';
require_once dirname(__FILE__).'/../tempdata/TempDataService.class.php';

/**
 * 保存临时数据
 * @param {string} strValue 临时保存的字符串值
 * @param {string} (可选) intValue 临时保存的数字值
 * @return {string|boolean} 执行结果 false=保存失败，其它情况返回主键ID
 */
function create_tempdata($strValue='', $intValue=0) {
	$formObj = new EBTempData();
	
	$formObj->str_value = $strValue;
	$formObj->int_value = $intValue;

	$instance = TempDataService::get_instance();
	$checkDigits = $formObj->createCheckDigits();
// 	log_info('-----------------------------------------');
// 	log_info($intValue);
// 	log_info($formObj);
	
	$params = $formObj->createFields();
	$formObj->removeKeepFields($params);
	$params['create_time'] = date(DATE_TIME_FORMAT);
	
	//执行插入记录
	$result = $instance->insertOne($params, $checkDigits, NULL, $outErrMsg);
	$json = ResultHandle::createdResultToJsonAndOutput($result, false, $outErrMsg);
	
	if (empty($json))
		return false;
	
	$results = get_results_from_json($json, $tmpObj);
	if ($tmpObj->code!=0)
		return false;
	
	return (string)$tmpObj->id;
}

/**
 * 删除临时数据
 * @param {string} $temp_key
 * @return {boolean} 执行结果
 */
function delete_tempdata($temp_key) {
	$wheres = array('temp_key'=>new SQLParam($temp_key));
	$checkDigits = array('temp_key'); //数字校验条件
	$instance = TempDataService::get_instance();
	
	//执行删除
	$result = $instance->delete($wheres, $checkDigits);
	$json = ResultHandle::deletedResultToJsonAndOutput($result, false);
	
	if (empty($json))
		return false;
	
	$results = get_results_from_json($json, $tmpObj);
	if ($tmpObj->code!=0)
		return false;
	
	return true;
}
