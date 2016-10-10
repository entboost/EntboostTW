<?php
require_once dirname(__FILE__).'/../report/include.php';

	$formObj = new EBReportForm();
	$formObj->setValuesFromRequest();
	$PTRType = 3;
	
	//验证必填字段
	if (!$formObj->validNotEmpty('start_time, stop_time', $outErrMsg)) {
		$json = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg);
		return;
	}
	
	//验证表单字段合法性
	if (!$formObj->validFormFields($json)) {
		return;
	}
	
	//创建者
	$userUid = $_SESSION[USER_ID_NAME];
	$userName = $_SESSION[USER_NAME_NAME];
	$checkDigits = $formObj->createCheckDigits();
	
	$params = $formObj->createFields();
	$formObj->removeKeepFields($params);
	$params['report_uid'] = $userUid;
	$params['create_name'] = $userName;
	$params['create_time'] = date(DATE_TIME_FORMAT);
	
	//有选中评阅人，自动提交评阅(修改状态)
	if (!empty($_REQUEST['review_user_id'])) {
		$params['status'] = 1; //"提交评阅未读"状态
	}
	
	//执行创建
	$result = ReportService::get_instance()->insertOne($params, $checkDigits, NULL, $outErrMsg);
	$json = ResultHandle::createdResultToJsonAndOutput($result, false, $outErrMsg);
	
	$id = get_field_value_from_json($json, 'id', $tmpObj);
	if (empty($id)) {
		echo $json;
		return;
	}
	//新建计划日志
	create_operaterecord($id, /*$formObj->plan_name*/'日报', $PTRType, 70);
	
	//有选中评阅人，自动提交评阅(创建评阅关系)
	if (!empty($_REQUEST['review_user_id'])) {
		$reviewUserId = get_request_param('review_user_id');
		$reviewUserName = get_request_param('review_user_name');
		//创建评阅关系
		create_shareuser($PTRType, $id, 1, $reviewUserId, $reviewUserName);
		//操作日志： 20：提交评审/评阅（op_data=评审人ID，op_name=评审人名称）
		create_operaterecord($id, '日报'/*$formObj->plan_name*/, $PTRType, 20, $reviewUserId, $reviewUserName, NULL, NULL, TRUE);
	}
	
	echo $json;	