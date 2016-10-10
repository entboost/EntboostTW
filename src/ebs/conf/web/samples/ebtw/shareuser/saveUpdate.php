<?php
require_once dirname(__FILE__).'/../shareuser/include.php';
require_once dirname(__FILE__).'/../shareuser/shareuser.php';
	
	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);
	
	//定义请求参数中变量名称
	$pkFieldName = 'share_id';
	$fromIdName = 'from_id';
	
	//获取输入值
	if (!isset($formObj)) {
		$formObj = new EBShareUserForm();
		$formObj->setValuesFromRequest();
	}
	
	//验证必填字段
	if (!$formObj->validNotEmpty('update_type', $outErrMsg)) {
		$json = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
		return;
	}
	//字段值合法性校验
	if (!$formObj->validFormFields($json, $output)) {
		return;
	}
	
	$updateType = $formObj->update_type;
	
	//1：以主键作为查询条件，其它字段作为更新内容（通用更新）
	//2：更新valid_flag，其它字段(包括主键)作为查询条件
	//3：更新result_status和result_time，其它字段(包括主键)作为查询条件
	//4：更新read_flag和read_time，其它字段(包括主键)作为查询条件
	switch ($updateType) {
		case 1:
			//验证主键和from_type是否存在
			//from_type在这里主要用途是控制数据操作权限，必须与已存在值相同，否则更新将不会生效
			if (empty($formObj->{$pkFieldName}) || empty($formObj->from_type)) {
				$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($pkFieldName.' or from_type', $output);
				return;
			}
			
			$whereCheckDigits = array($pkFieldName); //数字校验条件
			$wheres = array($pkFieldName=>new SQLParam($formObj->{$pkFieldName}, 'share_id')); //查询条件
			
			$setCheckDigits = array(); //待更新的字段的数字校验条件
			$sets = $formObj->createFields(); //待更新的字段
			//过滤不允许直接request输入的字段，并重新设定新值
			$formObj->removeKeepFields($sets);
			unset($sets['share_uid']);
			
			if (isset($formObj->valid_flag)) {
				array_push($setCheckDigits, 'valid_flag');
				$sets['valid_flag'] = $formObj->valid_flag;
			}
			if (isset($formObj->result_status)) {
				array_push($setCheckDigits, 'result_status');
				$sets['result_status'] = $formObj->result_status;
				//处理时间
				if ($formObj->result_status!='0')
					$sets['result_time'] = date(DATE_TIME_FORMAT);
			}			
			if (isset($formObj->read_flag)) {
				array_push($setCheckDigits, 'read_flag');
				$sets['read_flag'] = $formObj->read_flag;
				//标记已读时间
				if ($formObj->read_flag=='1')
					$sets['read_time'] = date(DATE_TIME_FORMAT);
			}
			break;
		case 2:
		case 3:
		case 4:
			$pid = $formObj->{$pkFieldName}; //get_request_param($pkFieldName);
			//验证必要字段
			//if (empty($formObj->{$fromIdName})) {
			if (empty($pid) && !$formObj->validNotEmpty('from_type, from_id, share_type', $outMsg)) {
				$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('all share_id, from_type, from_id, share_type', $output);
				return;
			}
			
			$whereCheckDigits = $formObj->createCheckDigits(); //数字校验条件
			$wheres = $formObj->createFields(); //查询条件
			//过滤不允许直接request输入的字段，并重新设定新值
			//$formObj->removeKeepFields($wheres);
			
			if (!empty($pid))
				$wheres[$pkFieldName] = new SQLParam($pid, 'share_id');
		break;
	}
	
	$userId = $_SESSION[USER_ID_NAME];
	$instance = ShareUserService::get_instance();
	
	switch ($formObj->from_type) {
		case 1: //计划
			$fieldNames = 'plan_id, plan_name as ptr_name, modify_count, status, create_uid, open_flag';
			$ownerFieldName = 'create_uid';
			$ptrInstance = PlanService::get_instance();
			break;
		case 2: //任务
			$fieldNames = 'task_id, task_name as ptr_name, modify_count, status, create_uid, open_flag';
			$ownerFieldName = 'create_uid';
			$ptrInstance = TaskService::get_instance();
			break;
		case 3: //报告
			$fieldNames = 'report_id, \'日报\' as ptr_name, modify_count, status, report_uid, open_flag';
			$ownerFieldName = 'report_uid';
			$ptrInstance = ReportService::get_instance();
			break;
	}
	
	if ($updateType==1) {
		//验证对本记录是否有操作权限：只允许创建者
		$qWheres = array_merge($wheres, array($ownerFieldName=>$userId));
		$qWhereCheckDigits = array_merge($whereCheckDigits, array($ownerFieldName));
		if (!DataAuthority::isRowExists($existRows, $fieldNames, $qWheres, $qWhereCheckDigits, $ptrInstance, 1, SQLParamComb_TYPE_AND, $output, $outErrMsg, $json)) {
			return;
		}
	} else {
		//调整不符合业务逻辑的查询条件
		if ($updateType==2) {
			unset($wheres['valid_flag']);
		} else if ($updateType==3) {
			unset($wheres['result_status']);
		} else if ($updateType==4) {
			unset($wheres['read_flag']);
		}
		
		if (isset($formObj->valid_flag_for_query)) {
			$wheres['valid_flag'] = $formObj->valid_flag_for_query;
		}
		//获取关联用户记录
// 		log_info('++++++++++++++++++++++++++++++++');
// 		log_info($wheres);
		if (!DataAuthority::isRowExists($shareuserExistRows, 'from_id, from_type, share_type, read_flag, read_time, result_status, result_time, valid_flag', $wheres, $whereCheckDigits, $instance, 1, SQLParamComb_TYPE_AND, $output, $outErrMsg, $json)) {
			return;
		}
		
		$shareuserExistRow = $shareuserExistRows[0];
		$fromId = $shareuserExistRow['from_id'];
		$fromType = $shareuserExistRow['from_type'];
		$shareType = $shareuserExistRow['share_type'];
		
		$setCheckDigits = array(); //待更新的字段的数字校验条件
		$sets = array(); //待更新的字段
		if ($updateType==2) {
			//验证valid_flag是否存在
			if (!isset($formObj->valid_flag)) {
				$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('valid_flag', $output);
				return;
			}
			array_push($setCheckDigits, 'valid_flag');
			$sets['valid_flag'] = $formObj->valid_flag;
		} else if ($updateType==3) {
			//验证result_status是否存在
			if (!isset($formObj->result_status)) {
				$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('result_status', $output);
				return;
			}
			array_push($setCheckDigits, 'result_status');
			$sets['result_status'] = $formObj->result_status;
			
			//处理时间
			//if ($formObj->result_status!='0')
			$sets['result_time'] = date(DATE_TIME_FORMAT);
		} else if ($updateType==4) {
			//验证read_flag是否存在
			if (!isset($formObj->read_flag)) {
				$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('read_flag', $output);
				return;
			}
			array_push($setCheckDigits, 'read_flag');
			$sets['read_flag'] = $formObj->read_flag;
			
			//标记已读时间
			if ($formObj->read_flag==1 && $formObj->read_flag!=$shareuserExistRow['read_flag'] ) {
				$sets['read_time'] = date(DATE_TIME_FORMAT);
				$markToRead = true;
			}
		}
		
		//$fromId = $formObj->from_id;
		//$fromType = $formObj->from_type;
		//$shareType = 0;
		$checkDigits = $formObj->createCheckDigits();
		
		switch ($fromType) {
			case 1: //计划
				$qwheres = array('plan_id'=>$fromId);
				break;
			case 2: //任务
				$qwheres = array('task_id'=>$fromId);
				break;
			case 3: //报告
				$qwheres = array('report_id'=>$fromId);
				break;
		}
		//验证对指定文档(计划、任务、报告)是否有操作权限
		$actionType = 12; //“变更共享状态”
		if (!DataAuthority::isAuthority($actionType, $fromType, $shareType, $userId, $existRows, $fieldNames, $qwheres, $checkDigits, $ptrInstance, 1, SQLParamComb_TYPE_AND, false, $outErrMsg, $json)) {
			if (!empty($json)) {
				if ($output) echo $json;
				return;
			}
			$json = ResultHandle::noAuthErrToJsonAndOutput($output);
			return;
		}
	}

	$result = $instance->update($sets, $wheres, $setCheckDigits, $whereCheckDigits, SQLParamComb_TYPE_AND, NULL, NULL, $outErrMsg);
	$json = ResultHandle::updatedResultToJsonAndOutput($result, $output, $outErrMsg);
	
	if ($updateType==4 && !empty($markToRead) && empty($formObj->custom_param)) { //此处custom_param"逻辑空"表示记录操作日志
		//创建已阅的操作记录
		$results = get_results_from_json($json, $tmpObj);
		if ($tmpObj->code==0) {
			$fromName = $existRows[0]->ptr_name;
			if ($fromType==2) {
				if ($shareType==5)
					$opType = 30; //30: 新负责人已阅
				else if ($shareType==2)
					$opType = 35; //35: 新参与人已阅
			} else {
				if ($shareType==1)
					$opType = 21; //21：评审人/评阅人已读
			}
			
			if(isset($opType))
				create_operaterecord($fromId, $fromName, $formObj->from_type, $opType);
		}
	}