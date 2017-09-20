<?php
//假期配置
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
	$FIELD_NAME_HOL_ID = 'hol_id'; //请假类型编号
	$FIELD_NAME_DISABLE = 'disable'; //是否有效
	
	//检查操作类型条件
	$actionType = get_request_param($FIELD_NAME_ACTION_TYPE);
	if (!isset($actionType) || !in_array($actionType, array(ACTION_TYPE_ATTENDANCE_HOLIDAY_DISABLE_ENABLE
			, ACTION_TYPE_ATTENDANCE_HOLIDAY_DELETE, ACTION_TYPE_ATTENDANCE_HOLIDAY_SAVE))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_ACTION_TYPE, $output);
		return;
	}

	//验证输入参数合法性
	$holId = get_request_param($FIELD_NAME_HOL_ID);
	if (isset($holId) && !EBModelBase::checkDigit($holId, $outErrMsg)) {
		ResultHandle::fieldValidNotDigitErrToJsonAndOutput($FIELD_NAME_HOL_ID, $output);
		return;
	}

	$holInstance = HolidaySettingService::get_instance();
	$holSetTarInstance = HolidaySetTargetService::get_instance();
	
	//验证数据权限
	$holEntity = null;
	if (!empty($holId)) {
		$holResult = $holInstance->getOneRecordByPrimaryKey($holId);
		if ($holResult===false) {
			$errMsg = 'get one holidaySetting record error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		if (count($holResult)==0) {
			$errMsg = 'holidaySetting is not exist';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
	
		$holEntity = $holResult[0];
		if ($holEntity['owner_id']!==$entCode || $holEntity['owner_type']!=='1') {
			ResultHandle::noAuthErrToJsonAndOutput($output);
			return;
		}
	}
	
	$now = date('Y-m-d H:i:s', time());
	
	//分别按不同操作类型执行
	switch ($actionType) {
		case ACTION_TYPE_ATTENDANCE_HOLIDAY_DISABLE_ENABLE: //禁用或启用假期配置
		case ACTION_TYPE_ATTENDANCE_HOLIDAY_DELETE: //删除假期配置
			//验证'hol_id'输入参数
			if (empty($holId)) {
				ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($FIELD_NAME_DICT_ID, $output);
				return;
			}
			
			if ($actionType==ACTION_TYPE_ATTENDANCE_HOLIDAY_DISABLE_ENABLE) {
				//验证'disable'输入参数
				$disable = get_request_param($FIELD_NAME_DISABLE);
				if (!in_array($disable, array(0, 1))) {
					ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_DISABLE, $output);
					return;
				}
	
				$sets = array('disable'=>$disable);
				$wheres = array('hol_set_id'=>$holId);
				$result = $holInstance->update($sets, $wheres, array('disable'), array('hol_set_id'));
				ResultHandle::updatedResultToJsonAndOutput($result, $output);
			} else if ($actionType==ACTION_TYPE_ATTENDANCE_HOLIDAY_DELETE) {
				//删除适用范围对象记录
				$delResult = $holSetTarInstance->deleteByHolidaySettingId($holId);
				if ($delResult===false) {
					$errMsg = 'deleteByHolidaySettingId error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				
				//删除holidaySetting记录
				$delResult = $holInstance->deleteByPrimaryKey($holId);
				ResultHandle::deletedResultToJsonAndOutput($delResult, $output);
			}
			break;
		case ACTION_TYPE_ATTENDANCE_HOLIDAY_SAVE: //保存(新建与更新)假期配置
			log_info($_REQUEST);
			
			$period = get_request_param('period');
			$flag = get_request_param('flag');
			$disable = get_request_param($FIELD_NAME_DISABLE, '0');
			$holSetTargets = get_request_param('holiday_set_targets', '');
			
			//验证period合法性
			if (!isset($period) || !in_array($period, array(0,1,2,3))) {
				ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('period', $output);
				return;
			}
			//验证flag合法性
			if (!isset($flag) || !in_array($flag, array(0,1,2))) {
				ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('flag', $output);
				return;
			}
			
			//验证请假类型名称的合法性
			$holName = trim(get_request_param('holiday_name', ''));
			if (empty($holName)) {
				ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('holiday_name', $output);
				return;
			}
			
			$period = intval($period);
			$flag = intval($flag);
			$params = array('name'=>$holName, 'period'=>$period, 'flag'=>$flag);
			
			
			switch ($period) {
				case 0: //一次性假期
					$startTime = get_request_param('start_time');
					$stopTime = get_request_param('stop_time');
					if (empty($startTime) || empty($stopTime)) {
						ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('start_time or stop_time', $output);
						return;
					}
					
					$params['start_time'] = $startTime;
					$params['stop_time'] = $stopTime;
					break;
				case 1: //每年假期
					$yearPeriodFrom = get_request_param('year_period_from');
					$yearPeriodTo = get_request_param('year_period_to');
					if (empty($yearPeriodFrom) || empty($yearPeriodTo)) {
						ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('year_period_from or year_period_to', $output);
						return;
					}
					
					$params['period_from'] = str_replace('-', '', $yearPeriodFrom);
					$params['period_to'] = str_replace('-', '', $yearPeriodTo);
					break;
				case 2: //每月假期
					$monthPeriodFrom = get_request_param('month_period_from');
					$monthPeriodTo = get_request_param('month_period_to');
					if (!EBModelBase::checkDigit($monthPeriodFrom, $errMsg) || !EBModelBase::checkDigit($monthPeriodTo, $errMsg)) {
						ResultHandle::fieldValidNotDigitErrToJsonAndOutput('month_period_from or month_period_to', $output);
						return;
					}
					
					$params['period_from'] = $monthPeriodFrom;
					$params['period_to'] = $monthPeriodTo;	
					break;
				case 3: //每周假期
					$weekPeriodFrom = get_request_param('week_period_from');
					$weekPeriodTo = get_request_param('week_period_to');
					if (!EBModelBase::checkDigit($weekPeriodFrom, $errMsg) || !EBModelBase::checkDigit($weekPeriodTo, $errMsg)) {
						ResultHandle::fieldValidNotDigitErrToJsonAndOutput('week_period_from or week_period_to', $output);
						return;
					}
						
					$params['period_from'] = $weekPeriodFrom;
					$params['period_to'] = $weekPeriodTo;					
					break;
			}
			
			$hol = new EBHolidaySetting();
			if (empty($holId)) { //新建
				$params['owner_type'] = 1;
				$params['owner_id'] = $entCode;
				$params['disable'] = $disable;
				$params['create_time'] = $now;
				$params['create_uid'] = $userId;				
				
				$holId = $holInstance->insertOne($params, $hol->createCheckDigits());
				if ($holId===false) {
					$errMsg = 'insert one holidaySetting record error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				//ResultHandle::createdResultToJsonAndOutput($createResult, $output);
			} else { //更新
				$wheres = array('hol_set_id'=>$holId);
				$updateResult = $holInstance->update($params, $wheres, $hol->createCheckDigits(), array('hol_set_id'));
				if ($updateResult===false) {
					$errMsg = 'update holidaySetting record error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				//ResultHandle::updatedResultToJsonAndOutput($updateResult, $output);
			}
			
			//获取原有的适用者列表
			$holSetTarResults = $holSetTarInstance->getRecordsByHolidaySettingId($holId);
			if ($holSetTarResults===false) {
				$errMsg = 'get holidaySetTarget error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			$oldSetTargets = array();
			foreach ($holSetTarResults as $holSetTar)
				array_push($oldSetTargets, $holSetTar['target_type_id']);
					
			//本次提交的适用者列表
			$sSetTargets= array();
			if (strlen($holSetTargets)>0) {
				//1,1000000000000030;2,999001;3,80
				$sSetTargets = preg_split('/;/', $holSetTargets);
			}
			//比较新旧适用者列表的差异
			$toDelSetTargets = array_values(array_unique(custom_array_diff($oldSetTargets, $sSetTargets)));
			$toAddSetTargets = array_values(array_unique(custom_array_diff($sSetTargets, $oldSetTargets)));
				
			//删除适用范围者记录
			if (count($toDelSetTargets)>0) {
				foreach ($toDelSetTargets as $sTarget) {
					if (strlen($sTarget)==0)
						continue;
							
						$pos = strpos($sTarget, ',');
						$delResult = $holSetTarInstance->deleteByHolidaySettingTargetTypeAndId($holId, substr($sTarget, 0, $pos), substr($sTarget, $pos+1));
						if ($delResult===false) {
							$errMsg = 'deleteByHolidaySettingTargetTypeAndId error';
							ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
							return;
						}
				}
			}
				
			//新建每一个适用范围对象记录
			if (count($toAddSetTargets)>0) {
				$holSetTar = new EBAttSetTarget();
				foreach ($toAddSetTargets as $sTarget) {
					if (strlen($sTarget)==0)
						continue;
							
						$pos = strpos($sTarget, ',');
						$stParams = array('target_id'=>substr($sTarget, $pos+1), 'target_type'=>substr($sTarget, 0, $pos), 'hol_set_id'=>$holId, 'create_uid'=>$userId, 'create_time'=>$now);
						$holSetTarResult = $holSetTarInstance->insertOne($stParams, $holSetTar->createCheckDigits(), $holSetTarInstance->primaryKeyName, $errMsg);
						if ($holSetTarResult===false) {
							$errMsg = 'create one holSetTarget record error';
							ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
							return;
						}
				}
			}
				
			ResultHandle::successToJsonAndOutput('ok', null, null, $output);
			break;
	}
	