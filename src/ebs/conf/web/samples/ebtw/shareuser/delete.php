<?php
require_once dirname(__FILE__).'/../shareuser/include.php';
	
	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);

	//定义请求参数中变量名称
	$pkFieldName = 'share_id';
	$fromIdName = 'from_id';
	
	$instance = ShareUserService::get_instance();
	
	if (isset($formObj)) {
		$pid = $formObj->share_id;
		$fromId = $formObj->from_id;
		$fromType = $formObj->from_type;
		
		$shareUid = $formObj->share_uid;
		$shareType = $formObj->share_type;
		//验证两个参数是否数字
		if (isset($shareUid) && !$instance::checkDigitParam($shareUid, $outErrMsg, 'share_uid')) {
			$json = ResultHandle::fieldValidNotDigitErrToJsonAndOutput('share_uid', $output);
			return;
		}
		if (isset($shareType) && !$instance::checkDigitParam($shareType, $outErrMsg, 'share_type')) {
			$json = ResultHandle::fieldValidNotDigitErrToJsonAndOutput('share_type', $output);
			return;
		}
	} else {
		$pid = get_request_param($pkFieldName);
		$fromId = get_request_param($fromIdName);
		$fromType = get_request_param('from_type');
	}
	
	//验证必要条件
	if (empty($pid) && empty($fromId)) {
		//$json = ResultHandle::missedPrimaryKeyErrToJsonAndOutput($pkFieldName, $output);
		$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($pkFieldName.' or '.$fromIdName, $output);
		return;
	}
	if (!empty($pid)) {
		$wheres = array($pkFieldName=>new SQLParam($pid));
		$checkDigits = array($pkFieldName); //数字校验条件
	} else {
		$wheres = array($fromIdName=>new SQLParam($fromId));
		$checkDigits = array($fromIdName); //数字校验条件
	}
	
	//验证from_type
	if (!in_array($fromType, array('1', '2', '3'))) {
		$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('from_type', $output);
		return;
	}
	
	//验证对本记录是否有操作权限：创建者
	//待定：支持更多角色可以执行删除操作
	switch ($fromType) {
		case 1: //计划
			$subSql = 'select plan_id from eb_plan_info_t where eb_share_user_t.from_id=plan_id and create_uid=?';
			break;
		case 2: //任务
			$subSql = 'select task_id from eb_task_info_t where eb_share_user_t.from_id=task_id and create_uid=?';
			break;
		case 3: //报告
			$subSql = 'select report_id from eb_report_info_t where eb_share_user_t.from_id=report_id and report_uid=?';
			break;
	}
	
	$UserId = $_SESSION[USER_ID_NAME];
	//$qWheres = array_merge($wheres, array('share_uid'=>$UserId));
	$prefixSql = 'delete from eb_share_user_t where from_id in ('.$subSql.') and from_type='.$fromType;
	$conditions = array($UserId);
	if (isset($shareUid)) {
		$prefixSql .= ' and share_uid=?';
		array_push($conditions, $shareUid);
	}
	if (isset($shareType)) {
		$prefixSql .= ' and share_type=?';
		array_push($conditions, $shareType);
	}
	
	$result = $instance->delete($wheres, $checkDigits, SQLParamComb_TYPE_AND, $prefixSql, $conditions);
	$json = ResultHandle::deletedResultToJsonAndOutput($result, $output);
	