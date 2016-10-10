<?php
require_once dirname(__FILE__).'/../classification/include.php';

	$formObj = new EBClassificationForm();
	$formObj->setValuesFromRequest();
	
	//验证必填字段
	if (!$formObj->validNotEmpty('class_type, class_name', $outErrMsg)) {
		//ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($outErrMsg);
		return;
	}
	
	//验证class_type
	if (!in_array($formObj->class_type, array('1', '2', '3'))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('class_type');
		return;
	}
	
	//创建者
	$userId = $_SESSION[USER_ID_NAME];
	
	$checkDigits = $formObj->createCheckDigits();
	
	$params = $formObj->createFields();
	$formObj->removeKeepFields($params);
	$params['user_id'] = $userId;
	$params['create_time'] = date(DATE_TIME_FORMAT);
	
	$result = ClassificationService::get_instance()->insertOne($params, $checkDigits, NULL, $outErrMsg);
	ResultHandle::createdResultToJsonAndOutput($result, true, $outErrMsg);
	