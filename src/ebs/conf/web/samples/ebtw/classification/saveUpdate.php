<?php
require_once dirname(__FILE__).'/../classification/include.php';
	
	$formObj = new EBClassificationForm();
	$formObj->setValuesFromRequest();
	
	$checkDigits = $formObj->createCheckDigits();
	$params = $formObj->createFields();
	$wheres = array();
	
	//定义请求参数中主键的变量名称
	$pkFieldName = 'pk_class_id';
	
	//验证必要条件
	$pid = get_request_param($pkFieldName);
	if (empty($pid)) {
		ResultHandle::missedPrimaryKeyErrToJsonAndOutput($pkFieldName);
		return;
	}
	$wheres[$pkFieldName] = new SQLParam($pid, 'class_id');
	array_push($checkDigits, $pkFieldName);//追加数字校验条件
	
	$instance = ClassificationService::get_instance();
	
	//验证对本记录是否有操作权限(待定：另外几种情况)
	$userId = $_SESSION[USER_ID_NAME];
	$qWheres = array_merge($wheres, array('user_id'=>$userId));
	array_push($checkDigits, 'user_id');
	if (!DataAuthority::isRowExists($existRows, 'class_id', $qWheres, $checkDigits, $instance))
		return;
	
	//设定更新值
	$formObj->removeKeepFields($params);
	$params['last_modify_time'] = date(DATE_TIME_FORMAT);
	
	//执行更新
	$result = $instance->update($params, $wheres);
	ResultHandle::updatedResultToJsonAndOutput($result);
	