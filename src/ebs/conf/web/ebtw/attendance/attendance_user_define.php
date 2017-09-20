<?php
//考勤专员配置
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
	$FIELD_NAME_UD_ID 	= 'ud_id'; //考勤专员编号
	$FIELD_NAME_DISABLE = 'disable'; //是否有效
	
	//检查操作类型条件
	$actionType = get_request_param($FIELD_NAME_ACTION_TYPE);
	if (!isset($actionType) || !in_array($actionType, array(ACTION_TYPE_ATTENDANCE_USER_DEFINE_DISABLE_ENABLE
			, ACTION_TYPE_ATTENDANCE_USER_DEFINE_DELETE, ACTION_TYPE_ATTENDANCE_USER_DEFINE_SAVE))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_ACTION_TYPE, $output);
		return;
	}	
	
	//验证输入参数合法性
	$udId = get_request_param($FIELD_NAME_UD_ID);
	if (isset($udId) && !EBModelBase::checkDigit($udId, $outErrMsg)) {
		ResultHandle::fieldValidNotDigitErrToJsonAndOutput($FIELD_NAME_UD_ID, $output);
		return;
	}
	
	$udInstance = UserDefineService::get_instance();
	
	//验证数据权限
	$udEntity = null;
	if (!empty($udId)) {
		$udResult = $udInstance->getOneRecordByPrimaryKey($udId);
		if ($udResult===false) {
			$errMsg = 'get one attendUserDefine record error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		if (count($udResult)==0) {
			$errMsg = 'attendUserDefine is not exist';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
	
		$udEntity = $udResult[0];
		if ($udEntity['owner_id']!==$entCode || $udEntity['owner_type']!=='1') {
			ResultHandle::noAuthErrToJsonAndOutput($output);
			return;
		}
	}
	
	$now = date('Y-m-d H:i:s', time());
	
	//分别按不同操作类型执行
	switch ($actionType) {
		case ACTION_TYPE_ATTENDANCE_USER_DEFINE_DISABLE_ENABLE: //禁用或启用考勤专员
		case ACTION_TYPE_ATTENDANCE_USER_DEFINE_DELETE: //删除考勤专员
			//验证'ud_id'输入参数
			if (empty($udId)) {
				ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($FIELD_NAME_UD_ID, $output);
				return;
			}
			
			if ($actionType==ACTION_TYPE_ATTENDANCE_USER_DEFINE_DISABLE_ENABLE) {
				//验证'disable'输入参数
				$disable = get_request_param($FIELD_NAME_DISABLE);
				if (!in_array($disable, array(0, 1))) {
					ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_DISABLE, $output);
					return;
				}
				
				$sets = array('disable'=>$disable);
				$wheres = array('ud_id'=>$udId);
				$result = $udInstance->update($sets, $wheres, array('disable'), array('ud_id'));
				ResultHandle::updatedResultToJsonAndOutput($result, $output);
			} else if ($actionType==ACTION_TYPE_ATTENDANCE_USER_DEFINE_DELETE) {
				//删除attendUserDefine记录
				$delResult = $udInstance->deleteByPrimaryKey($udId);
				if ($delResult===false) {
					$errMsg = 'delete one attendUserDefine record error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
			
				ResultHandle::successToJsonAndOutput('delete ok', null, null, $output);
			}
		break;
		case ACTION_TYPE_ATTENDANCE_USER_DEFINE_SAVE: //保存(新建与更新)考勤专员
			log_info($_REQUEST);
			
			$displayIndex = get_request_param('display_index', '0');
			$udUserId = get_request_param('user_id');
			$authorityManagement = get_request_param('authority_management', '0');
			$disable = get_request_param('disable', '0');
			
			$params = array('display_index'=>$displayIndex, 'disable'=>$disable);
			//获取用户名称
			if (!empty($udUserId)) {
				$uResult = UserAccountService::get_instance()->getOneRecordByPrimaryKey($udUserId);
				if ($uResult===false) {
					$errMsg = 'get user_account record error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				if (count($uResult)>0)
					$params['user_name'] = $uResult[0]['username'];
			}
			
			if (empty($udId)) { //新建
				//判断该考勤专员已经存在
				$existResults = $udInstance->getAttendanceManagers($entCode, array(), null, null, $udUserId);
				if ($existResults===false) {
					$errMsg = 'getAttendanceManagers error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				if (count($existResults)>0) {
					$errMsg = 'user already exists';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output, EBStateCode::$EB_STATE_ALEADY_EXIST_ERROR);
					return;
				}
				
				//
				$params['owner_type'] = '1';
				$params['owner_id'] = $entCode;
				$params['create_time'] = $now;
				$params['create_uid'] = $userId;
				$params['user_type'] = '1';
				$params['user_id'] = $udUserId;
				
				//管理权限值
				if ($authorityManagement==1)
					$params['param_int'] = 0x1;
				else
					$params['param_int'] = 0x0;
				
				$ud = new EBUserDefine();
				$createResult = $udInstance->insertOne($params, $ud->createCheckDigits());
				ResultHandle::createdResultToJsonAndOutput($createResult, $output);
			} else { //更新
				//管理权限值
				$paramInt = intval($udEntity['param_int']);
				if ($authorityManagement==1)
					$params['param_int'] = $paramInt|0x1;
				else 
					$params['param_int'] = $paramInt&(~0x1);
				
				$wheres = array('ud_id'=>$udId);
				$updateResult = $udInstance->update($params, $wheres, array('param_int', 'display_index', 'disable'), array('ud_id'));
				ResultHandle::updatedResultToJsonAndOutput($updateResult, $output);
			}
		break;
	}
	