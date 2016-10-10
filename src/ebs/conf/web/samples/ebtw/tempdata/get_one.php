<?php
require_once dirname(__FILE__).'/../tempdata/include.php';
	
	$LOCAL_ACTION_TYPE = 0; //查看操作

	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);
	
	if (empty($formObj)) {
		$formObj = new EBTempData();
		$formObj->setValuesFromRequest();
	}
	
	//定义请求参数中主键的变量名称
	$pkFieldName = 'temp_key';
	
	//验证必要条件
	$pid = $formObj->{$pkFieldName};
	if (empty($pid)) {
		$json = ResultHandle::missedPrimaryKeyErrToJsonAndOutput($pkFieldName, $output);
		return;
	}
	
	$wheres = array();
	$checkDigits = array();
	$wheres[$pkFieldName] = new SQLParam($pid, $pkFieldName);
	array_push($checkDigits, $pkFieldName);//追加数字校验条件
	$userId = $_SESSION[USER_ID_NAME];
	$instance = TempDataService::get_instance();
	
	log_info("get tempdata for key=$pid, userId=$userId");
	
	//定义输出字段
	$fieldNames = $instance->fieldNamesAfterRemovedSome();
	
	//执行查询
	$result = $instance->search($fieldNames, $wheres, $checkDigits, null, 1, 0, SQLParamComb::$TYPE_AND, $outErrMsg);
	
	//处理查询结果
	$json = ResultHandle::listedResultToJsonAndOutput($result, $output, $outErrMsg);	
	$results = get_results_from_json($json, $tmpObj);
	if (!empty($results)) {
		if (!delete_tempdata($pid)) //删除临时变量
			log_err("delete tempdata error for key=$pid, userId=$userId");
	}
	