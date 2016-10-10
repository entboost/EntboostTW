<?php
require_once dirname(__FILE__).'/../shareuser/include.php';

	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);
	
	if (!isset($formObj)) {
		$formObj = new EBShareUserForm();
		$formObj->setValuesFromRequest();
	}
	
	$userId = $_SESSION[USER_ID_NAME];
	$userName = $_SESSION[USER_NAME_NAME];
	
	//默认关注人为当前用户
	if ($formObj->share_type==4 && empty($formObj->share_uid)) {
		$formObj->share_uid = $userId;
		$formObj->share_name = $userName;
	}
	
	//验证必填字段
	if (!$formObj->validNotEmpty('share_uid, from_id, from_type, share_type', $outErrMsg)) {
		$json = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
		return;
	}
	
	//验证share_type
	if (!in_array($formObj->share_type, array('1', '2', '3', '4', '5'))) {
		$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('share_type', $output);
		return;
	}
	//验证from_type
	if (!in_array($formObj->from_type, array('1', '2', '3'))) {
		$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('from_type', $output);
		return;
	}
	
	$instance = ShareUserService::get_instance();
	$checkDigits = $formObj->createCheckDigits();
	
	//如果该用户已关注，忽略本次操作并返回已经存在的主键
	if ($formObj->share_type==4) {
		$wheres = array('share_uid'=>$formObj->share_uid, 'from_id'=>$formObj->from_id, 'from_type'=>$formObj->from_type, 'share_type'=>$formObj->share_type, 'valid_flag'=>1);
// 		log_info($wheres);
		if (DataAuthority::isRowExists($outExistRows, "share_id, share_uid, share_name", $wheres, $checkDigits, $instance, 1, SQLParamComb_TYPE_AND, false)) {
			log_info("share_type=4, from_type=$formObj->from_type, from_id=$formObj->from_id, share_uid=$formObj->share_uid, valid_flag=1 is exists");
			$json = ResultHandle::successToJsonAndOutput(null, array('id'=>$outExistRows[0]['share_id']), null, $output);
			return;
		}
	}
	
	//待定：数据操作权限(计划、任务、报告的创建者，...)
	$params = $formObj->createFields();
	$formObj->removeKeepFields($params);
	$params['share_uid'] = $formObj->share_uid;
	$params['create_time'] = date(DATE_TIME_FORMAT);
	
	//如已读标识等于1，自动填入查阅时间
	if (isset($formObj->read_flag) && $formObj->read_flag==1) {
		$params['read_time'] = date(DATE_TIME_FORMAT);
	}
	
	//执行插入记录
	$result = $instance->insertOne($params, $checkDigits, NULL, $outErrMsg);
	$json = ResultHandle::createdResultToJsonAndOutput($result, $output, $outErrMsg);