<?php
include dirname(__FILE__).'/../plan/preferences.php';
require_once dirname(__FILE__).'/../plan/include.php';
	
	$LOCAL_ACTION_TYPE = 2; //编辑操作
	
	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);

	//获取输入值
	if (!isset($formObj)) {
		$formObj = new EBPlanForm();
		$formObj->setValuesFromRequest();
	}
	
	$opType = $formObj->op_type;
	//验证op_type, 200="新建未阅"变更为"未处理"状态
	if (isset($opType) && !in_array($opType, array('200'))) {
		$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('op_type', $output);
		return;
	}
	
	$checkDigits = $formObj->createCheckDigits();
	$params = $formObj->createFields();
	$wheres = array();
	
	//定义请求参数中主键的变量名称
	$pkFieldName = 'pk_plan_id';
	
	//验证必要条件
	$pid = $formObj->pk_plan_id;
	//$pid = get_request_param($pkFieldName);
	
	if (empty($pid)) {
		$json = ResultHandle::missedPrimaryKeyErrToJsonAndOutput($pkFieldName, $output);
		return;
	}
	$wheres[$pkFieldName] = new SQLParam($pid, 'plan_id');
	array_push($checkDigits, $pkFieldName);//追加数字校验条件
	
	//验证表单字段合法性
	if (!$formObj->validFormFields($json, $output)) {
		return;
	}
	
	$userId = $_SESSION[USER_ID_NAME];
	$userName = $_SESSION[USER_NAME_NAME];
	$instance = PlanService::get_instance();
	
	//验证对本记录是否有操作权限
	$shareType = 0;
	if (!DataAuthority::isAuthority($LOCAL_ACTION_TYPE, $PTRType, $shareType, $userId, $existRows, 'plan_id, plan_name, modify_count, status, create_uid, open_flag', $wheres, $checkDigits, $instance, 1, SQLParamComb_TYPE_AND, false, $outErrMsg, $json)) {
		if (!empty($json)) {
			if ($output) echo $json;
			return;
		}
		$json = ResultHandle::noAuthErrToJsonAndOutput($output);
		return;
	}
	
	$status = (int)$existRows[0]->status;
	
	if ($opType==200) {
		if ($status!=0) {
			$json = ResultHandle::successToJsonAndOutput('do nothing, no need to mark', $output);
			return;
		}
		
		$params = array('status'=>1);
		//执行更新
		$result = $instance->update($params, $wheres, $checkDigits, $checkDigits, SQLParamComb_TYPE_AND, NULL, NULL, $outMsg);
		$json = ResultHandle::updatedResultToJsonAndOutput($result, false, $outMsg);
	} else {
		$fromType = 1;
		$shareType = 1;
		
		$fromName = $formObj->plan_name?$formObj->plan_name:$existRows[0]->plan_name;
		
		//设定更新值
		$formObj->removeKeepFields($params);
		$params['create_name'] = $userName;
		$params['last_modify_time'] = date(DATE_TIME_FORMAT);
		$params['modify_count'] = $existRows[0]->modify_count + 1;
		
		//有选中评审人，自动提交评审(修改状态)
		$oldApprovalUserId = get_request_param('old_approval_user_id');
		$approvalUserId = get_request_param('approval_user_id');
		$approvalUserName = get_request_param('approval_user_name');
		if (!empty($approvalUserId) && ($status<4 || $status==5)) {
			$params['status'] = 2;
		}
		
		//执行更新
		$result = $instance->update($params, $wheres, $checkDigits, $checkDigits, SQLParamComb_TYPE_AND, NULL, NULL, $outErrMsg);
		$json = ResultHandle::updatedResultToJsonAndOutput($result, false, $outErrMsg);
		
		//写入日志
		$affected = get_field_value_from_json($json, 'affected', $tmpObj);
		if (!isset($affected)/* empty($affected) || $affected==0*/) {
			echo $json;
			return;
		}
		create_operaterecord($pid, $fromName, $fromType, 51);
		
		//如有选中评审人，自动提交评审(创建评审关系)
	 	if ($status<4 || $status==5) { //'评审中'和'已完成'状态不允许提交评审
	 		$newExist = !empty($approvalUserId);	//有新人
	 		$oldExist = !empty($oldApprovalUserId);	//有旧人
	 		$noPerson = !$newExist && !$oldExist; //新旧都没人
	 		$eqPerson = ($newExist || $oldExist) && ($approvalUserId==$oldApprovalUserId);	//新人和旧人是同一个人
	 		
	 		log_info('$approvalUserId='.$approvalUserId.',$oldApprovalUserId='.$oldApprovalUserId);
	 		log_info('$eqPerson='.($eqPerson?'true':'false').', $noPerson='.($noPerson?'true':'false'));
	 		if ($eqPerson || $noPerson) { //同一个人或先后没人的情况
	 			if ($eqPerson && $status==5) { //被拒绝后重新提交评审
	 				//更新旧评审关系(设置valid_flag=0[表示无效])
	 				update_shareuser_field(2, 0, $fromType, $pid, $shareType, NULL, NULL, 1);
	 				
	 				//创建新评审关系
	 				create_shareuser($fromType, $pid, $shareType, $approvalUserId, $approvalUserName);
	 				//操作日志： 20：提交评审/评阅（op_data=评审人ID，op_name=评审人名称）
	 				create_operaterecord($pid, $fromName, $fromType, 20, $approvalUserId, $approvalUserName, NULL, NULL, TRUE);	 				
	 			}
	 		} else { //不是同一个人或者先后有人
	 			if ($oldExist) {
	 				//更新旧评审关系(设置valid_flag=0[表示无效])
	 				update_shareuser_field(2, 0, $fromType, $pid, $shareType, NULL/*$oldApprovalUserId*/, NULL, 1);
	 				//delete_shareuser($fromType, $pid, $shareType, $oldApprovalUserId);
	 				//不记录操作日志
	 			}
	 			if ($newExist) {
	 				//创建新评审关系
	 				create_shareuser($fromType, $pid, $shareType, $approvalUserId, $approvalUserName);
	 				//操作日志： 20：提交评审/评阅（op_data=评审人ID，op_name=评审人名称）
	 				create_operaterecord($pid, $fromName, $fromType, 20, $approvalUserId, $approvalUserName, NULL, NULL, TRUE);
	 			}
	 		}
	 	}
	 	
	 	//更新 操作类型"3评论/回复"的计划标题from_name
	 	//更新 操作类型"50新建计划"的计划标题from_name
	 	//更新 操作类型"52计划转任务"的计划标题from_name
	 	//更新 操作类型"53计划转任务"的计划标题op_name
	 	if (array_key_exists('plan_name', $params)) {
	 		if ($params['plan_name']!=$existRows[0]->plan_name) {
	 			//3评论/回复
	 			$json1 = update_operaterecords($fromType, $pid, 3, $params['plan_name']);
	 			//50新建计划
	 			$json1 = update_operaterecords($fromType, $pid, 50, $params['plan_name']);
	 			//52计划转任务
	 			$json1 = update_operaterecords($fromType, $pid, 52, $params['plan_name']);
	 			//53计划转任务
	 			$json1 = get_operaterecords($fromType, $pid, 52);
	 			$results1 = get_results_from_json($json1, $tmpObj1);
	 			if (!empty($results1)) {
	 				foreach ($results1 as $opr1) {
	 					$taskId = $opr1->op_data;
	 					$json2 = update_operaterecords(2, $taskId, 53, null, null, $params['plan_name']);
	 				}
	 			}
	 		}
	 	}
	}
	
	if ($output)
 		echo $json;