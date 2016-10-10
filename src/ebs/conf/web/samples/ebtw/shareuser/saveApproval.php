<?php
require_once dirname(__FILE__).'/../shareuser/include.php';
require_once dirname(__FILE__).'/../shareuser/shareuser.php';

	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);
	
	$formObj = new ApprovalForm();
	$formObj->setValuesFromRequest();
	
	//验证approval_action：1=提交，2=通过/回复，3=拒绝，4=撤销申请，5=标为完成
	if (!in_array($formObj->approval_action, array('1', '2', '3', '4', '5'))) {
		$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('approval_action', $output);
		return;
	}
	//验证from_type
	if (!in_array($formObj->from_type, array('1', '3'))) { //任务操作不走本通道
		$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('from_type', $output);
		return;
	}
	//验证必填字段
	if (!$formObj->validNotEmpty('from_id', $outErrMsg)) {
		$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($outErrMsg, $output);
		return;
	}
	//验证必填字段
	if ($formObj->approval_action==1) {
		if (!$formObj->validNotEmpty('approval_user_id', $outErrMsg)) {
			$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($outErrMsg, $output);
			return;
		}
	}

//{from_type:fromType, from_id:fromId, approval_user_id:approvalUserId, approval_user_name:approvalUserName, remark:remark};
	$userId = $_SESSION[USER_ID_NAME];
	
	$approvalAction = $formObj->approval_action;
	$fromType = $formObj->from_type;
	$fromId = $formObj->from_id;
	$pkFieldName = ($fromType==1)?'plan_id':($fromType==2?'task_id':'report_id');
	$nameField = ($fromType==1)?'plan_name':($fromType==2?'task_name':'');
	$instance = ($fromType==1)?PlanService::get_instance():($fromType==2?TaskService::get_instance():ReportService::get_instance());
	$qWheres = array($pkFieldName=>$fromId);
	$params = array();
	
	//查询当前记录
	if (!DataAuthority::isRowExists($existRows, $pkFieldName.',status'.(empty($nameField)?'':(','.$nameField)), $qWheres, array(), $instance, 1, SQLParamComb_TYPE_AND, $output, $outErrMsg, $json))
		return;
	
	$fromName = empty($nameField)?'':$existRows[0][$nameField];
	//准备新状态
	$status = (int)$existRows[0]['status'];
	
	if ($approvalAction==1) { //提交
		if ($fromType==1) {
			if ($status<4 || $status==5)
				$params['status'] = 2;
		} else if ($fromType==3) {
			if ($status==0)
				$params['status'] = 1;
		}
		//不符合执行条件
		if (!isset($params['status'])) {
			$json = ResultHandle::errorToJsonAndOutput('当前状态不允许提交评审或评阅', $output);
			return;
		}
	} else if ($approvalAction==2) { //通过
		if ($fromType==1) {
			if ($status==2 || $status==3)
				$params['status'] = 4;
		} else if ($fromType==3) {
			if ($status==1 || $status==2)
				$params['status'] = 3;
		}
		//不符合执行条件
		if (!isset($params['status'])) {
			$json = ResultHandle::errorToJsonAndOutput('当前状态不允许执行通过评审或评阅回复', $output);
			return;
		}
	}  else if ($approvalAction==3) { //拒绝
		if ($fromType==1) {
			if ($status==2 || $status==3)
				$params['status'] = 5;
		}
		//不符合执行条件
		if (!isset($params['status'])) {
			$json = ResultHandle::errorToJsonAndOutput('当前状态不允许执行拒绝评审', $output);
			return;
		}
	} else if ($approvalAction==4) { //撤销申请
		if ($fromType==1) {
			if ($status==2 || $status==3)
				$params['status'] = 1;
		} else if ($fromType==3) {
			if ($status==1 || $status==2)
				$params['status'] = 0;
		}
		//不符合执行条件
		if (!isset($params['status'])) {
			$json = ResultHandle::errorToJsonAndOutput('当前状态不允许执行取消', $output);
			return;
		}		
	} else if ($approvalAction==5) { //标为完成
		if ($fromType==1) {
			if ($status==0 ||$status==1 || $status==4 || $status==5)
				$params['status'] = 6;
		}
		//不符合执行条件
		if (!isset($params['status'])) {
			$json = ResultHandle::errorToJsonAndOutput('当前状态不允许执行标为完成', $output);
			return;
		}		
	}
	
	//执行状态更新
	$result = $instance->update($params, $qWheres, array(), array(), SQLParamComb_TYPE_AND, NULL, NULL, $outErrMsg);
	$json = ResultHandle::updatedResultToJsonAndOutput($result, false, $outErrMsg);

	$tmpObj = json_decode($json);
 	if ($tmpObj->code==0 /*&& $tmpObj->affected==1*/) {
 		$shareType = 1;
 		$shareUid = $userId;
 		
 		if ($approvalAction==1) { //提交评审/评阅
	 		//更新旧关联用户关系为无效(valid_flag=0)
	 		//$json1 = delete_shareuser($fromType, $fromId, $shareType);
	 		$json1 = update_shareuser_field(2, 0, $fromType, $fromId, $shareType, NULL, NULL, 1); //待定：此处如果旧记录不存在时，将有执行报错，看是否需要进一步处理
	 		//$tmpObj1 = json_decode($json1);
	 		//执行下一步
	 		//if ($tmpObj1->code==0) {
	 			//创建评审/评阅关系
	 			$json1 = create_shareuser($fromType, $fromId, 1, $formObj->approval_user_id, $formObj->approval_user_name);
	 			//操作日志： 20：提交评审/评阅（op_data=评审人ID，op_name=评审人名称）
	 			create_operaterecord($fromId, $fromName, $fromType, 20, $formObj->approval_user_id, $formObj->approval_user_name, $formObj->remark);
	 		//}
 		} else if ($approvalAction==2) { //评审通过/评阅回复
 			//更新已读状态
 			$json1 = update_shareuser_field(4, 1, $fromType, $fromId, $shareType, $shareUid, NULL, 1, $formObj->custom_param);
 			
	 		//操作日志： 22：评审通过/评阅回复（remark=备注）
	 		$json1 = create_operaterecord($fromId, $fromName, $fromType, 22, null, null, $formObj->remark);
	 		
	 		//更新关联用户(评审或评阅关系)记录为无效(valid_flag=0)，更新结果状态
	 		$json1 = update_shareuser_field(3, 4, $fromType, $fromId, $shareType, $shareUid, NULL, 1);
	 		//$json1 = update_shareuser_field(2, 0, $fromType, $fromId, $shareType, $shareUid, NULL, 1); 保持最新一条记录始终有效
 		} else if ($approvalAction==3) { //评审拒绝
 			//更新已读状态
 			$json1 = update_shareuser_field(4, 1, $fromType, $fromId, $shareType, $shareUid, NULL, 1, $formObj->custom_param);
 			
 			//操作日志： 23：评审拒绝（remark=备注）
 			$json1 = create_operaterecord($fromId, $fromName, $fromType, 23, null, null, $formObj->remark);

	 		//更新关联用户(评审或评阅关系)记录为无效(valid_flag=0)，更新结果状态
	 		$json1 = update_shareuser_field(3, 5, $fromType, $fromId, $shareType, $shareUid, NULL, 1);
	 		//$json1 = update_shareuser_field(2, 0, $fromType, $fromId, $shareType, $shareUid, NULL, 1); 保持最新一条记录始终有效
 		} else if ($approvalAction==4) { //撤销申请
 			//操作日志： 24：撤销申请
 			$json1 = create_operaterecord($fromId, $fromName, $fromType, 24);
 			$tmpObj1 = json_decode($json1);
 			//执行下一步
 			if ($tmpObj1->code==0) {
 				$json1 = update_shareuser_field(2, 0, $fromType, $fromId, $shareType, NULL, NULL, 1);
 			}
 		} else if ($approvalAction==5) { //标为完成
 			//操作日志： 34：标为完成
 			$json1 = create_operaterecord($fromId, $fromName, $fromType, 34);
 			$tmpObj1 = json_decode($json1);
 			//执行下一步
//  			if ($tmpObj1->code==0) { "标为完成"不应也不需与关联用户表有关系
//  				$json1 = update_shareuser_field(2, 0, $fromType, $fromId, $shareType, NULL, NULL, 1);
//  			}
 		}
	}
	echo $json;
	