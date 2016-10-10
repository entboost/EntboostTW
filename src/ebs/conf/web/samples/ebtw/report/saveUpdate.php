<?php
include dirname(__FILE__).'/../report/preferences.php';
require_once dirname(__FILE__).'/../report/include.php';
	
	$output = true;

	$opType = get_request_param('op_type');

	//22: 评阅回复
	//71：修改报告
	
	//验证op_type
	if (!isset($opType) || !in_array($opType, array('22', '71'))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('op_type', $output);
		return;
	}
	
	switch ($opType) {
		case 22: //评阅回复
			$LOCAL_ACTION_TYPE = 4;
			break;
		case 71: //修改报告
			$LOCAL_ACTION_TYPE = 2;
			break;
	}	
	
	//验证必要字段
	if ($opType==22) {
		$remark = get_request_param('remark');
		if (empty($remark)) {
			ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('remark', $output);
			return;
		}
	}
	
	$formObj = new EBReportForm();
	$formObj->setValuesFromRequest();
	
	$checkDigits = $formObj->createCheckDigits();
	$params = $formObj->createFields();
	$wheres = array();
	
	//定义请求参数中主键的变量名称
	$pkFieldName = 'pk_report_id';
	
	//验证必要条件
	$pid = get_request_param($pkFieldName);
	if (empty($pid)) {
		ResultHandle::missedPrimaryKeyErrToJsonAndOutput($pkFieldName, $output);
		return;
	}
	$wheres[$pkFieldName] = new SQLParam($pid, 'report_id');
	array_push($checkDigits, $pkFieldName);//追加数字校验条件
	
	//验证表单字段合法性
	if (!$formObj->validFormFields($json, $output)) {
		return;
	}
	
	$userId = $_SESSION[USER_ID_NAME];
	$userName = $_SESSION[USER_NAME_NAME];
	$instance = ReportService::get_instance();
	
	//验证对本记录是否有操作权限
	$shareType = 0;
	if ($LOCAL_ACTION_TYPE==4)
		$shareType = 1;
	if (!DataAuthority::isAuthority($LOCAL_ACTION_TYPE, $PTRType, $shareType, $userId, $existRows, 'report_id, modify_count, status, report_uid, open_flag', $wheres, $checkDigits, $instance, 1, SQLParamComb_TYPE_AND, false, $outErrMsg, $json)) {
		if (!empty($json)) {
			if ($output) echo $json;
			return;
		}
		$json = ResultHandle::noAuthErrToJsonAndOutput($output);
		return;
	}
	
	$fromType = 3;
	$shareType = 1;
	//$fromName = $formObj->plan_name?$formObj->plan_name:$existRows[0]->plan_name;
	$fromName = '日报';
	$status = (int)$existRows[0]->status;
	
	//设定更新值
	if ($opType==71) {
		$formObj->removeKeepFields($params);
		$params['create_name'] = $userName;
		$params['last_modify_time'] = date(DATE_TIME_FORMAT);
		$params['modify_count'] = $existRows[0]->modify_count + 1;
	} else if ($opType==22) { //评阅回复
		if ($status!=3)
			$params['status'] = 3;
	}
	
	//有选中评阅人，自动提交评阅(修改状态)
	$oldReviewUserId = get_request_param('old_review_user_id');
	$reviewUserId = get_request_param('review_user_id');
	$reviewUserName = get_request_param('review_user_name');
	if ($opType==71 && !empty($reviewUserId) && ($status==0)) {
		$params['status'] = 1; //"提交评阅未读"状态
	}
	
	//执行更新
	if ($opType==71 || ($opType==22 && $status!=3)) {
		$result = $instance->update($params, $wheres, $checkDigits, $checkDigits, SQLParamComb_TYPE_AND, NULL, NULL, $outErrMsg);
		$json = ResultHandle::updatedResultToJsonAndOutput($result, false, $outErrMsg);
		
		$affected = get_field_value_from_json($json, 'affected', $tmpObj);
		if (!isset($affected)/* empty($affected) || $affected==0*/) {
			echo $json;
			return;
		}
		
		//写入更新日志
		if ($opType==71) {
			create_operaterecord($pid, $fromName, $fromType, $opType);
		}
	}
	
	//如有选中评阅人，自动提交评阅(创建评阅关系)
	if ($opType==71 && $status==0) {
		$newExist = !empty($reviewUserId);	//有新人
		$oldExist = !empty($oldReviewUserId);	//有旧人
		$noPerson = !$newExist && !$oldExist; //新旧都没人
		$eqPerson = ($newExist || $oldExist) && ($reviewUserId==$oldReviewUserId);	//新人和旧人是同一个人
			
		log_info('$reviewUserId='.$reviewUserId.',$oldReviewUserId='.$oldReviewUserId);
		log_info('$eqPerson='.($eqPerson?'true':'false').', $noPerson='.($noPerson?'true':'false'));
		if ($eqPerson || $noPerson) { //同一个人或先后没人的情况
			//do nothing
		} else { //不是同一个人或者先后有人
			if ($oldExist) {
				//更新旧评阅关系(设置valid_flag=0[表示无效])
				update_shareuser_field(2, 0, $fromType, $pid, $shareType, NULL/*$oldReviewUserId*/, NULL, 1);
				//delete_shareuser($fromType, $pid, $shareType, $oldReviewUserId);
				//不记录操作日志
			}
			if ($newExist) {
				//创建新评阅关系
				create_shareuser($fromType, $pid, $shareType, $reviewUserId, $reviewUserName);
				//操作日志： 20：提交评阅（op_data=评阅人ID，op_name=评阅人名称）
				create_operaterecord($pid, $fromName, $fromType, 20, $reviewUserId, $reviewUserName, NULL, NULL, TRUE);
			}
		}
	}
	
	//保存评阅回复
	if ($opType==22 && $status!=3) {
		$json = create_operaterecord($pid, $fromName, $fromType, $opType, NULL, NULL, $remark, NULL, TRUE);
		$tmpObj1 = json_decode($json);
		//执行下一步
		if ($tmpObj1->code==0) {
 			//更新关联用户(评阅关系)记录为无效(valid_flag=0)，更新结果状态，更新已读状态
			$json = update_shareuser_field(4, 1, $fromType, $pid, $shareType, $userId, NULL, 1);
 			$json = update_shareuser_field(3, 4, $fromType, $pid, $shareType, $userId, NULL, 1);
			//$json = update_shareuser_field(2, 0, $fromType, $pid, $shareType, $userId, NULL, 1); 保持最新一条记录始终有效
		}
	}
	
	if ($output)
		echo $json;