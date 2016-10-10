<?php
include dirname(__FILE__).'/../task/preferences.php';
require_once dirname(__FILE__).'/../task/include.php';

	$formObj = new EBTaskForm();
	$formObj->setValuesFromRequest();
	
	//验证必填字段
	if (!$formObj->validNotEmpty('task_name, start_time, stop_time', $outErrMsg)) {
		$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($outErrMsg);
		return;
	}
	
	//验证表单字段合法性
	if (!$formObj->validFormFields($json)) {
		return;
	}
	
	//创建者
	$userId = $_SESSION[USER_ID_NAME];
	$userName = $_SESSION[USER_NAME_NAME];
	$checkDigits = $formObj->createCheckDigits();
	
	$params = $formObj->createFields();
	$formObj->removeKeepFields($params);
	$params['create_uid'] = $userId;
	$params['create_name'] = $userName;
	$params['create_time'] = date(DATE_TIME_FORMAT);
	
	//插入新记录
	$result = TaskService::get_instance()->insertOne($params, $checkDigits, NULL, $outErrMsg);
	$json = ResultHandle::createdResultToJsonAndOutput($result, false, $outErrMsg);
	
	$id = get_field_value_from_json($json, 'id', $tmpObj);
	if (empty($id)) {
		echo $json;
		return;
	}
	$task_name = $formObj->task_name;
	
	if (!empty($formObj->from_type) && !empty($formObj->from_id)) {
		$jsonP = get_plan($formObj->from_id);
		//echo $jsonP;
		get_first_entity_from_json($jsonP, $entity, $outObj);
		$planName = $entity->plan_name;
		//52：计划转任务（op_data=任务ID，op_name=任务名称）
		create_operaterecord($formObj->from_id, $planName, 1, 52, $id, $task_name);
		//53：计划转任务（op_data=计划ID，op_name=计划名称）
		create_operaterecord($id, $task_name, $PTRType, 53, $formObj->from_id, $planName);
	} else {
		//新建任务日志
		create_operaterecord($id, $task_name, $PTRType, 60);
	}
	
	
	//负责人、参与人、共享人
	$principalPerson = get_request_param('principal_person'); //'123, 56958, ';
	$helperPerson = get_request_param('helper_person'); //'123 , 98 1';
	$sharerPerson = get_request_param('sharer_person');
	$userIds = array_values(array_unique(preg_split('/[\s,]+/', $principalPerson.','.$helperPerson.','.$sharerPerson, -1, PREG_SPLIT_NO_EMPTY)));
	
	if (!empty($userIds)) {
		$json1 = get_useraccounts($userIds);
		$results = get_results_from_json($json1, $outObj);
		$users = array();
		foreach($results as $user) {
			$users[$user->user_id] = $user->username;
		}
		
		if (!empty($principalPerson)) { //负责人
			$shareUids = array_values(array_unique(preg_split('/[\s,]+/', $principalPerson, -1, PREG_SPLIT_NO_EMPTY)));
			foreach ($shareUids as $shareUid) {
				$readFlag = ($shareUid==$userId)?1:0;
				$json2 = create_shareuser($PTRType, $id, 5, $shareUid, $users[$shareUid], $readFlag);
				
				//负责人不是当前用户时，创建"指派负责人(10)"操作日志
				if ($shareUid!=$userId) {
					$shareId = get_field_value_from_json($json, 'id', $tmpObj2);
					if (!empty($shareId))
						create_operaterecord($id, $task_name, $PTRType, 10, $shareUid, $users[$shareUid], NULL, NULL, TRUE);
				}
			}
		}
		if (!empty($helperPerson)) { //参与人
			$shareUids = array_values(array_unique(preg_split('/[\s,]+/', $helperPerson, -1, PREG_SPLIT_NO_EMPTY)));
			foreach ($shareUids as $shareUid) {
				$readFlag = ($shareUid==$userId)?1:0;
				$json2 = create_shareuser($PTRType, $id, 2, $shareUid, $users[$shareUid], $readFlag);
				$shareId = get_field_value_from_json($json, 'id', $tmpObj2);
				if (!empty($shareId))
					create_operaterecord($id, $task_name, $PTRType, 11, $shareUid, $users[$shareUid], NULL, NULL, TRUE);
			}
		}
		if (!empty($sharerPerson)) { //共享人
			$shareUids = array_values(array_unique(preg_split('/[\s,]+/', $sharerPerson, -1, PREG_SPLIT_NO_EMPTY)));
			foreach ($shareUids as $shareUid) {
				$readFlag = ($shareUid==$userId)?1:0;
				$json2 = create_shareuser($PTRType, $id, 3, $shareUid, $users[$shareUid], $readFlag);
				$shareId = get_field_value_from_json($json, 'id', $tmpObj2);
				if (!empty($shareId))
					create_operaterecord($id, $task_name, $PTRType, 13, $shareUid, $users[$shareUid], NULL, NULL, TRUE);
			}
		}
	}
	
	echo $json;
	