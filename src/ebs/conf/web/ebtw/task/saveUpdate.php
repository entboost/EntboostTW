<?php
include dirname(__FILE__).'/../task/preferences.php';
require_once dirname(__FILE__).'/../task/include.php';
	
	$output = true;

	$opType = get_request_param('op_type');
	
	//15：创建 IM 临时讨论组（op_data=讨论组 ID，op_name=讨论组名称）
	//16：解散 IM 临时讨论组（op_data=讨论组 ID，op_name=讨论组名称）
	//31：上报进度（op_data=进度百分比0‐100，remark=工作内容，支持修改）
	//32：上报工时（op_time=工作时间，op_data=工时分钟，remark=工作内容，支持修改）
	//33：中止任务（remark=备注）
	//34：标为完成
	//61：编辑任务
	//62：拆分子任务（op_data=子任务 ID，op_name=子任务名称）
	//200: 更新"未阅"状态为"已阅"
	//210: 修改"重要程度"
	
	//验证op_type
	if (!isset($opType) || !in_array($opType, array('15', '16', '31', '32', '33', '34', '61', '62', '200', '210'))) {
		$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('op_type', $output);
		return;
	}
	
	switch ($opType) {
		case 15:
		case 16:
			break;
		case 31: //上报进度
			$LOCAL_ACTION_TYPE = 22;
			break;
		case 32: //上报工时
			$LOCAL_ACTION_TYPE = 23;
			break;
		case 33: //中止任务
			$LOCAL_ACTION_TYPE = 10;
			break;
		case 34: //标为完成
			$LOCAL_ACTION_TYPE = 9;
			break;
		case 61: //编辑任务
			$LOCAL_ACTION_TYPE = 2;
			break;
		case 62: //拆分子任务
			$LOCAL_ACTION_TYPE = 24;
			break;
		case 200: //更新"未阅"状态为"已阅"
			$LOCAL_ACTION_TYPE = 2;
			break;
		case 210: //修改"重要程度"
			$LOCAL_ACTION_TYPE = 14;
			break;
	}
	
	$formObj = new EBTaskForm();
	$formObj->setValuesFromRequest();
	$checkDigits = $formObj->createCheckDigits();
	$params = $formObj->createFields();
	$wheres = array();
	$fromType = 2;
	
	//定义请求参数中主键的变量名称
	$pkFieldName = 'pk_task_id';
	//验证必要条件
	$pid = get_request_param($pkFieldName);
	if (empty($pid)) {
		$json = ResultHandle::missedPrimaryKeyErrToJsonAndOutput($pkFieldName, $output);
		return;
	}
	$wheres[$pkFieldName] = new SQLParam($pid, 'task_id');
	array_push($checkDigits, $pkFieldName);//追加数字校验条件
	
	//验证表单字段合法性
	if (!$formObj->validFormFields($json, $output)) {
		return;
	}
	
	$userId = $_SESSION[USER_ID_NAME];
	$userName = $_SESSION[USER_NAME_NAME];
	$instance = TaskService::get_instance();
	
	//验证对本记录是否有操作权限
	$shareType = 0;
	if (!DataAuthority::isAuthority($LOCAL_ACTION_TYPE, $PTRType, $shareType, $userId, $existRows, 'task_id, task_name, modify_count, work_time, status, create_uid, open_flag', $wheres, $checkDigits, $instance, 1, SQLParamComb_TYPE_AND, false, $outErrMsg, $json)) {
		if (!empty($json)) {
			if ($output) echo $json;
			return;
		}
		$json = ResultHandle::noAuthErrToJsonAndOutput($output);
		return;
	}
	
	$taskName = isset($formObj->task_name)?$formObj->task_name:$existRows[0]->task_name;
	$status = $existRows[0]->status;
	
	//设定更新值
	if ($opType==61) {
		$formObj->removeKeepFields($params);
		$params['create_name'] = $userName;
		$params['last_modify_time'] = date(DATE_TIME_FORMAT);
		$params['modify_count'] = $existRows[0]->modify_count + 1;
	} else {
		$params = array();
		switch ($opType) {
			case 15:
			case 16:
				if (!isset($formObj->im_group_id)) {
					$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('im_group_id', $output);
					return;
				}
				$params['im_group_id'] = $formObj->im_group_id;
				break;
			case 31: //上报进度
				if (!isset($formObj->percentage)) {
					$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('percentage', $output);
					return;
				}
				
				$percentage = (int)$formObj->percentage;
				$params['percentage'] = $percentage;
				//百分比大于0
				if ($percentage>0) {
					//百分比到达100时，自动设置为完成状态
					//百分比小于100时，自动设置为进行中
					//状态不回退
					if ($percentage>=100) {
						if ($status<3) $params['status'] = 3;
					} else {
						if ($status<2) $params['status'] = 2;
					}
				}
				break;
			case 32: //上报工时
				if (!isset($formObj->work_time)) {
					$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('work_time', $output);
					return;
				}
				$params['work_time'] = ((int)$formObj->work_time) + $existRows[0]->work_time;
				break;
			case 33: //中止任务
				$params['status'] = 4;
				break;
			case 34: //标为完成
				$params['status'] = 3;
				$params['percentage'] = 100;
				break;
			case 62:
// 				$params['from_id'] = $formObj->from_id;
// 				$params['from_type'] = 2;
				break;
			case 200:
				if ($status==0) 
					$params['status'] = 1;
				break;
			case 210:
					$params['important'] = $formObj->important;
				break;
		}
	}
	
	//执行更新
	$result = $instance->update($params, $wheres, $checkDigits, $checkDigits, SQLParamComb_TYPE_AND, NULL, NULL, $outErrMsg);
	$json = ResultHandle::updatedResultToJsonAndOutput($result, false, $outErrMsg);
	
	$affected = get_field_value_from_json($json, 'affected', $tmpObj);
	if (!isset($affected)/* empty($affected) || $affected==0*/) {
		echo $json;
		return;
	}
	
	//写入日志
	$opData = NULL;
	$opName = NULL;
	$remark = NULL;
	$opTime = NULL;
	if ($opType==15 || $opType==16) {
		$opData = $formObj->im_group_id;
		$opName = get_request_param('im_group_name');
	} else if ($opType==31) {
		$opData = $formObj->percentage;
		$remark = get_request_param('op_remark');
	} else if ($opType==32) {
		$opData = $formObj->work_time;
		$remark = get_request_param('op_remark');
		$opTime = get_request_param('op_time');
	} else if ($opType==33) {
		$remark = get_request_param('op_remark');
	} else if ($opType==34) {
		
	} if ($opType==62) {
// 		$opData = get_request_param('sub_task_id');
// 		$opName = get_request_param('sub_task_name');
	}
	if ($opType!=200) {
		create_operaterecord($pid, $taskName, $fromType, $opType, $opData, $opName, $remark, $opTime);
	}
	
	if ($opType==61) { //编辑任务
		//负责人、参与人、共享人
		$oldPrincipalPerson = get_request_param('old_principal_person');
		$oldHelperPerson = get_request_param('old_helper_person');
		$oldSharerPerson = get_request_param('old_sharer_person');
		$principalPerson = get_request_param('principal_person');
		$helperPerson = get_request_param('helper_person');
		$sharerPerson = get_request_param('sharer_person');
		
		if (isset($oldPrincipalPerson) && isset($oldHelperPerson) && isset($oldSharerPerson) && isset($principalPerson) && isset($helperPerson) && isset($sharerPerson)) {
			$userIds = array_values(array_unique(preg_split('/[\s,]+/', $oldPrincipalPerson.','.$oldHelperPerson.','.$oldSharerPerson.','.$principalPerson.','.$helperPerson.','.$sharerPerson, -1, PREG_SPLIT_NO_EMPTY)));
			if (!empty($userIds)) {
				$json1 = get_useraccounts($userIds);
				$results = get_results_from_json($json1, $outObj);
				$users = array();
				foreach($results as $user)
					$users[$user->user_id] = $user->username;
				
				if (isset($principalPerson) && isset($oldPrincipalPerson)) //负责人
					updatePtrAssociatePerson($fromType, $pid, $taskName, 5, $oldPrincipalPerson, $principalPerson, array(10), $users, $userId);
				if (isset($helperPerson) && isset($oldHelperPerson)) //参与人
					updatePtrAssociatePerson($fromType, $pid, $taskName, 2, $oldHelperPerson, $helperPerson, array(11, 12), $users, $userId);
				if (isset($sharerPerson) && isset($oldSharerPerson)) //共享人
					updatePtrAssociatePerson($fromType, $pid, $taskName, 3, $oldSharerPerson, $sharerPerson, array(13, 14), $users, $userId);
			}
		}
		
		//更新 操作类型"3评论/回复"的任务标题from_name
		//更新 操作类型"60新建任务"的任务标题from_name
		//更新 操作类型"53计划转任务"的任务标题from_name
		//更新 操作类型"52计划转任务"的任务标题op_name
		if (array_key_exists('task_name', $params)) {
			if ($params['task_name']!=$existRows[0]->task_name) {
				//3评论/回复
				$json1 = update_operaterecords($fromType, $pid, 3, $params['task_name']);
				//60新建任务
				$json1 = update_operaterecords($fromType, $pid, 60, $params['task_name']);
				//53计划转任务
				$json1 = update_operaterecords($fromType, $pid, 53, $params['task_name']);
				//52计划转任务
				$json1 = get_operaterecords($fromType, $pid, 53);
				$results1 = get_results_from_json($json1, $tmpObj1);
				if (!empty($results1)) {
					foreach ($results1 as $opr1) {
						$taskId = $opr1->op_data;
						$json2 = update_operaterecords(1, $taskId, 52, null, null, $params['task_name']);
					}
				}
			}
		}		
		
	}
	
	if ($output)
		echo $json;