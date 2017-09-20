<?php
//请假类型配置
include dirname(__FILE__).'/../attendance/preferences.php';
$ECHO_MODE = 'json'; //输出类型
require_once dirname(__FILE__).'/../attendance/include.php';
require_once dirname(__FILE__).'/../attendance/attendance_functions.php';
	$output = true;
	
	$userId = $_SESSION[USER_ID_NAME]; //用户编号
	$entCode = $_SESSION[USER_ENTERPRISE_CODE]; //企业编号
	$isEntManager = $_SESSION[IS_ENTERPRISE_MANAGER]; //是否企业管理者
	
	//检查操作权限
	$authorityResult = getAttendanceManageAuthority($entCode, array(), $userId, true, false);
	if (!$isEntManager && $authorityResult!==true) {
		ResultHandle::noAuthErrToJsonAndOutput($output);
		return;
	}
	
	//字段名称定义
	$FIELD_NAME_ACTION_TYPE = 'action_type'; //操作类型
	$FIELD_NAME_DICT_ID = 'dict_id'; //请假类型编号
	$FIELD_NAME_DISABLE = 'disable'; //是否有效
	
	//检查操作类型条件
	$actionType = get_request_param($FIELD_NAME_ACTION_TYPE);
	if (!isset($actionType) || !in_array($actionType, array(ACTION_TYPE_ATTENDANCE_LEAVE_TYPE_DISABLE_ENABLE
			, ACTION_TYPE_ATTENDANCE_LEAVE_TYPE_DELETE, ACTION_TYPE_ATTENDANCE_LEAVE_TYPE_SAVE))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_ACTION_TYPE, $output);
		return;
	}
	
	//验证输入参数合法性
	$dictId = get_request_param($FIELD_NAME_DICT_ID);
	if (isset($dictId) && !EBModelBase::checkDigit($dictId, $outErrMsg)) {
		ResultHandle::fieldValidNotDigitErrToJsonAndOutput($FIELD_NAME_DICT_ID, $output);
		return;
	}
	
	$dictInstance = DictionaryInfoService::get_instance();
	
	//验证数据权限
	$dictEntity = null;
	if (!empty($dictId)) {
		$dictResult = $dictInstance->getOneRecordByPrimaryKey($dictId);
		if ($dictResult===false) {
			$errMsg = 'get one dictionaryInfo record error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		if (count($dictResult)==0) {
			$errMsg = 'dictionaryInfo is not exist';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
	
		$dictEntity = $dictResult[0];
		if ($dictEntity['owner_id']!==$entCode || $dictEntity['owner_type']!=='1') {
			ResultHandle::noAuthErrToJsonAndOutput($output);
			return;
		}
	}
	
	$now = date('Y-m-d H:i:s', time());
	
	//分别按不同操作类型执行
	switch ($actionType) {
		case ACTION_TYPE_ATTENDANCE_LEAVE_TYPE_DISABLE_ENABLE: //禁用或启用请假类型
		case ACTION_TYPE_ATTENDANCE_LEAVE_TYPE_DELETE: //删除请假类型
			//验证'dict_id'输入参数
			if (empty($dictId)) {
				ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($FIELD_NAME_DICT_ID, $output);
				return;
			}
			
			if ($actionType==ACTION_TYPE_ATTENDANCE_LEAVE_TYPE_DISABLE_ENABLE) {
				//验证'disable'输入参数
				$disable = get_request_param($FIELD_NAME_DISABLE);
				if (!in_array($disable, array(0, 1))) {
					ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_DISABLE, $output);
					return;
				}
				
				$sets = array('disable'=>$disable);
				$wheres = array('dict_id'=>$dictId);
				$result = $dictInstance->update($sets, $wheres, array('disable'), array('dict_id'));
				ResultHandle::updatedResultToJsonAndOutput($result, $output);
			} else if ($actionType==ACTION_TYPE_ATTENDANCE_LEAVE_TYPE_DELETE) {
				//删除dictionaryInfo记录
				$delResult = $dictInstance->deleteByPrimaryKey($dictId);
				ResultHandle::deletedResultToJsonAndOutput($delResult, $output);
			}
			break;
		case ACTION_TYPE_ATTENDANCE_LEAVE_TYPE_SAVE: //保存(新建与更新)请假类型
			log_info($_REQUEST);
			
			//验证请假类型名称的合法性
			$dictName = trim(get_request_param('dict_name', ''));
			if (empty($dictName)) {
				ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('dict_name', $output);
				return;
			}
			
			$displayIndex = get_request_param('display_index', '0');
			$disable = get_request_param($FIELD_NAME_DISABLE, '0');
			
			$params = array('display_index'=>$displayIndex, 'disable'=>$disable, 'dict_name'=>$dictName);
			
			if (empty($dictId)) { //新建
				//验证请假类型名称的重复性
				$dictResults = $dictInstance->getHolidayInfos($entCode, array(), null, null, $dictName);
				if ($dictResults===false) {
					$errMsg = 'getHolidayInfos error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				if (count($dictResults)>0) {
					$errMsg = 'the record already exists';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output, EBStateCode::$EB_STATE_ALEADY_EXIST_ERROR);
					return;
				}
				
				//
				$params['owner_type'] = '1';
				$params['owner_id'] = $entCode;
				$params['create_time'] = $now;
				$params['create_uid'] = $userId;
				$params['dict_type'] = '1';
				
				$dict = new EBDictionaryInfo();
				$createResult = $dictInstance->insertOne($params, $dict->createCheckDigits());
				ResultHandle::createdResultToJsonAndOutput($createResult, $output);
			} else { //更新
				$wheres = array('dict_id'=>$dictId);
				$updateResult = $dictInstance->update($params, $wheres, array('display_index', 'disable'), array('dict_id'));
				ResultHandle::updatedResultToJsonAndOutput($updateResult, $output);
			}
			break;
	}
	