<?php
require_once dirname(__FILE__).'/../plan/include.php';

	$formObj = new EBPlanForm();
	$formObj->setValuesFromRequest();
	$PTRType = 1;
	
	//验证必填字段
	if (!$formObj->validNotEmpty('plan_name, start_time, stop_time', $outErrMsg)) {
		$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($outErrMsg);
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
	$params['create_uid'] = $userUid;
	$params['create_name'] = $userName;
	$params['create_time'] = date(DATE_TIME_FORMAT);
	
	//有选中评审人，自动提交评审(修改状态)
	$approvalUserId = get_request_param('approval_user_id');
	$approvalUserName = get_request_param('approval_user_name');
	if (!empty($approvalUserId)) {
		$params['status'] = 2;
	}
	
	//执行创建
	$result = PlanService::get_instance()->insertOne($params, $checkDigits, NULL, $outErrMsg);
	$json = ResultHandle::createdResultToJsonAndOutput($result, false, $outErrMsg);
	
	$id = get_field_value_from_json($json, 'id', $tmpObj);
	if (empty($id)) {
		echo $json;
		return;
	}
	//新建计划日志
	create_operaterecord($id, $formObj->plan_name, $PTRType, 50);
	
	//有选中评审人，自动提交评审(创建评审关系)
	if (!empty($approvalUserId)) {
		//创建评审关系
		create_shareuser($PTRType, $id, 1, $approvalUserId, $approvalUserName);
		//操作日志： 20：提交评审/评阅（op_data=评审人ID，op_name=评审人名称）
		create_operaterecord($id, $formObj->plan_name, $PTRType, 20, $approvalUserId, $approvalUserName, NULL, NULL, TRUE);
	}
	
	echo $json;
	