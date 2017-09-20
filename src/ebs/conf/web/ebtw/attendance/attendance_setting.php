<?php
//考勤规则配置
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
	$FIELD_NAME_SETTING_ID 	= 'set_id'; //考勤设置编号
	$FIELD_NAME_DISABLE 	= 'disable'; //是否有效
	
	//检查操作类型条件
	$actionType = get_request_param($FIELD_NAME_ACTION_TYPE);
	if (!isset($actionType) || !in_array($actionType, array(ACTION_TYPE_ATTENDANCE_SETTING_DISABLE_ENABLE
			, ACTION_TYPE_ATTENDANCE_SETTING_DELETE, ACTION_TYPE_ATTENDANCE_SETTING_SAVE))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_ACTION_TYPE, $output);
		return;
	}
	
	$aSetInstance = AttendSettingService::get_instance();
	$aTimeInstance = AttendTimeService::get_instance();
	$aRuleInstance = AttendRuleService::get_instance();
	$aSetTarInstance = AttendSetTargetService::get_instance();
	
	//验证输入参数合法性
	$aSetId = get_request_param($FIELD_NAME_SETTING_ID);
	if (isset($aSetId) && !EBModelBase::checkDigit($aSetId, $outErrMsg)) {
		ResultHandle::fieldValidNotDigitErrToJsonAndOutput($FIELD_NAME_SETTING_ID, $output);
		return;
	}
	//验证数据权限
	if (!empty($aSetId)) {
		$aSetResult = $aSetInstance->getOneRecordByPrimaryKey($aSetId);
		if ($aSetResult===false) {
			$errMsg = 'getOneRecordByPrimaryKey error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		if (count($aSetResult)==0) {
			$errMsg = 'attendSetting is not exist';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		$aSetEntity = $aSetResult[0];
		if ($aSetEntity['owner_id']!==$entCode || $aSetEntity['owner_type']!=='1') {
			ResultHandle::noAuthErrToJsonAndOutput($output);
			return;
		}
	}
	
	$now = date('Y-m-d H:i:s', time());
	
	//分别按不同操作类型执行
	switch ($actionType) {
		case ACTION_TYPE_ATTENDANCE_SETTING_DISABLE_ENABLE: //禁用或启用考勤规则
		case ACTION_TYPE_ATTENDANCE_SETTING_DELETE: //删除考勤规则
			//验证'set_id'输入参数
			if (empty($aSetId)) {
				ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($FIELD_NAME_SETTING_ID, $output);
				return;
			}
			
			if ($actionType==ACTION_TYPE_ATTENDANCE_SETTING_DISABLE_ENABLE) {
				//验证'disable'输入参数
				$disable = get_request_param($FIELD_NAME_DISABLE);
				if (!in_array($disable, array(0, 1))) {
					ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_DISABLE, $output);
					return;
				}
				
				$sets = array('disable'=>$disable, 'last_uid'=>$userId, 'last_time'=>$now);
				$wheres = array('att_set_id'=>$aSetId);
				$result = $aSetInstance->update($sets, $wheres, array('disable', 'last_uid'), array('att_set_id'));
				ResultHandle::updatedResultToJsonAndOutput($result, $output);
			} else if ($actionType==ACTION_TYPE_ATTENDANCE_SETTING_DELETE) {
				//删除适用范围对象记录
				$delResult = $aSetTarInstance->deleteByAttendSettingId($aSetId);
				if ($delResult===false) {
					$errMsg = 'deleteByAttendSettingId error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				
				//删除attendSetting记录
				$delResult = $aSetInstance->deleteByPrimaryKey($aSetId);
				if ($delResult===false) {
					$errMsg = 'delete one attendSetting record error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				
				ResultHandle::successToJsonAndOutput('delete ok', null, null, $output);
			}
			break;
		case ACTION_TYPE_ATTENDANCE_SETTING_SAVE: //保存(新建与更新)考勤规则
			//log_info($_REQUEST);
			
			$settingName = get_request_param('att_set_name', '');
			$attSetTargets = get_request_param('att_set_targets', '');
			$isDefault = get_request_param('is_default', '0'); //是否默认规则
			$ruleIndexes = get_request_param('rule_index', array());
			
			$rulArry = array();
			//遍历处理每一个考勤规则
			foreach ($ruleIndexes as $ruleIndex) {
				$rulId = get_request_param("att_rul_id_$ruleIndex"); //考勤规则编号
				$delRuleFlag = get_request_param("att_rul_del_$ruleIndex", '0'); //删除规则标记
				
				$rulEntity = null;
				//查询已存在的考勤规则记录
				if (!empty($rulId)) {
					$aRuleResult = $aRuleInstance->getOneRecordByPrimaryKey($rulId);
					if ($aRuleResult===false) {
						$errMsg = 'get one attendRule record error';
						ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
						return;
					}
					if (count($aRuleResult)>0)
						$rulEntity = $aRuleResult[0];
				}
				
				//该考勤规则即将被删除
				if ($delRuleFlag==='1') {
					$rulArry[$ruleIndex] = '-1';
					if (!empty($rulEntity) && substr($rulEntity['create_time'], 0, 10)===substr($now, 0, 10)) {
						log_info('delete attendRule record for $rulId = '.$rulId);
						$aRuleInstance->deleteByPrimaryKey($rulId);
					}
					continue;
				}
				
				$flexibleWork = get_request_param("flexible_work_$ruleIndex", '0'); //是否弹性工作机制
				$workDays = get_request_param("week_value_$ruleIndex");
				$workDay = 0;
				if (!empty($workDays)) {
					foreach ($workDays as $wd)
						$workDay |= intval($wd); 
				}
				
				$timIndexes = get_request_param("tim_index_$ruleIndex", array());
				
				//遍历处理每一个考勤时段
				$timArry = array();
				foreach ($timIndexes as $timIndex) {
					$rtSuffix = $ruleIndex.'_'.$timIndex;
					$timId = get_request_param("att_tim_id_$rtSuffix"); //考勤时间段编号
					$timName = get_request_param("att_time_name_$rtSuffix"); //考勤时间段名称
					$delTimeFlag = get_request_param("att_tim_del_$rtSuffix", '0'); //删除时间段标记
					
					$timEntity = null;
					//查询已存在的考勤时间段记录
					if (!empty($timId)) {
						$aTimeResult = $aTimeInstance->getOneRecordByPrimaryKey($timId);
						if ($aTimeResult===false) {
							$errMsg = 'getOneRecordByPrimaryKey error';
							ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
							return;
						}
						if (count($aTimeResult)>0)
							$timEntity = $aTimeResult[0];
					}
					
					//该考勤时间段将被删除
					if ($delTimeFlag==='1') {
						$timArry[$timIndex] = '-1';
						if (!empty($timEntity) && substr($timEntity['create_time'], 0, 10)===substr($now, 0, 10)) {
							log_info('delete attendTime record for $timId = '.$timId);
							$aTimeInstance->deleteByPrimaryKey($timId);
						}
						continue;
					}
					
					$signinTime = get_request_param("signin_time_$rtSuffix"); //签到时间
					$signoutTime = get_request_param("signout_time_$rtSuffix"); //签到时间
					$signinIgnore = get_request_param("signin_ignore_$rtSuffix"); //不计算迟到时长(分钟)
					$signoutIgnore = get_request_param("signout_ignore_$rtSuffix"); //不计算早退(分钟)
					$restDuration = get_request_param("rest_duration_$rtSuffix"); //休息时长(分钟)
					$workDuration = get_request_param("work_duration_$rtSuffix"); //工作时长(分钟)
					
					$timParams = array('name'=>$timName, 'signin_time'=>substr($signinTime, 0, 5).':00', 'signout_time'=>substr($signoutTime, 0, 5).':00'
							, 'signin_ignore'=>$signinIgnore, 'signout_ignore'=>$signoutIgnore, 'rest_duration'=>$restDuration
							, 'work_duration'=>$workDuration, 'create_time'=>$now
					);
					$aT = new EBAttendTime();
					
					if (empty($timEntity)) { //新的考勤时段记录
						//执行插入新记录
						$timId = $aTimeInstance->insertOne($timParams, $aT->createCheckDigits());
						if ($timId===false) {
							$errMsg = 'create one attendTime record error';
							ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
							return;
						}
					} else { //已存在考勤时段记录
						if (substr($timEntity['create_time'], 0, 10)===substr($now, 0, 10)) { //与当前日期同一天，直接修改属性
							log_debug('attendTime [$timId='.$timId.'] is in same day');
							unset($timParams['create_time']); //不更新创建时间
							//执行更新记录
							$aTimeResult = $aTimeInstance->update($timParams, array('att_tim_id'=>$timId), $aT->createCheckDigits(), array('att_tim_id'));
							if ($aTimeResult===false) {
								$errMsg = 'update attendTime error';
								ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
								return;
							}
						} else { //日期不同
							log_debug('attendTime [$timId='.$timId.'] is in difference day');
							//检查两个关联数组是否有重要更新，重要更新需要创建新记录
							if ($aT->checkImportantUpdate($timEntity, $timParams)) { //重要更新
								//执行插入新记录
								$timId = $aTimeInstance->insertOne($timParams, $aT->createCheckDigits());
								if ($timId===false) {
									$errMsg = 'create one attendTime record error';
									ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
									return;
								}
							} else { //次要更新
								unset($timParams['create_time']); //不更新创建时间
								//执行更新记录
								$aTimeResult = $aTimeInstance->update($timParams, array('att_tim_id'=>$timId), $aT->createCheckDigits(), array('att_tim_id'));
								if ($aTimeResult===false) {
									$errMsg = 'update attendTime error';
									ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
									return;
								}
							}
						}
					}
					
					$timArry[$timIndex] = $timId;
				}
				
				$aR = new EBAttendRule();
				$rulParams = array('work_day'=>$workDay, 'flexible_work'=>$flexibleWork, 'create_time'=>$now);
				//关联考勤时段
				foreach ($timArry as $key=>$value)
					$rulParams["att_tim_id$key"] = $value;
				
				if (empty($rulEntity)) { //新的考勤规则记录
					//执行插入新记录
					$rulId = $aRuleInstance->insertOne($rulParams, $aR->createCheckDigits());
					if ($rulId===false) {
						$errMsg = 'create one attendRule record error';
						ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
						return;
					}
				} else { //已存在对应的考勤规则记录
					if (substr($rulEntity['create_time'], 0, 10)===substr($now, 0, 10)) { //与当前日期同一天
						log_debug('attendRule [$rulId='.$rulId.'] is in same day');
						if ($aR->hasTimNewid($rulEntity) || $aR->hasDeleteFlag($rulParams)) {
							$aR->checkImportantUpdate($rulEntity, $rulParams, true);
						}
						
						unset($rulParams['create_time']); //不更新创建时间
						//执行更新记录
						$aRuleResult = $aRuleInstance->update($rulParams, array('att_rul_id'=>$rulId), $aR->createCheckDigits(), array('att_rul_id'));
						if ($aRuleResult===false) {
							$errMsg = 'update attendRule error';
							ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
							return;
						}
					} else { //日期不同
						log_debug('attendRule [$rulId='.$rulId.'] is in difference day');
						if ($aR->checkImportantUpdate($rulEntity, $rulParams, false)) { //重要更新
							$rulId = $aRuleInstance->insertOne($rulParams, $aR->createCheckDigits());
							if ($rulId===false) {
								$errMsg = 'create one attendRule record error';
								ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
								return;
							}
						} else { //次要更新
							unset($rulParams['create_time']); //不更新创建时间
							//执行更新记录
							$aRuleResult = $aRuleInstance->update($rulParams, array('att_rul_id'=>$rulId), $aR->createCheckDigits(), array('att_rul_id'));
							if ($aRuleResult===false) {
								$errMsg = 'update attendRule error';
								ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
								return;
							}
						}
					}
				}
				
				$rulArry[$ruleIndex] = $rulId;
			}
			
			$aSetEntity = null;
			//查询已存在的考勤配置记录
			if (!empty($aSetId)) {
				$aSetResult = $aSetInstance->getOneRecordByPrimaryKey($aSetId);
				if ($aSetResult===false) {
					$errMsg = 'get one attendSetting record error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				if (count($aSetResult)>0)
					$aSetEntity = $aSetResult[0]; 
			}
			
			$aS = new EBAttendSetting();
			$aSetParams = array('name'=>$settingName, 'create_uid'=>$userId, 'owner_id'=>$entCode, 'owner_type'=>'1', 'is_default'=>$isDefault, 'create_time'=>$now);
			//关联考勤规则
			foreach ($rulArry as $key=>$value)
				$aSetParams["att_rul_id$key"] = $value;
			
			if (empty($aSetEntity)) { //新的考勤配置
				//执行插入新记录
				$aSetId = $aSetInstance->insertOne($aSetParams, $aS->createCheckDigits());
				if ($aSetId===false) {
					$errMsg = 'create one attendSetting record error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
			} else { //已存在对应的考勤配置记录
				if (substr($aSetEntity['create_time'], 0, 10)===substr($now, 0, 10)) { //与当前日期同一天
					//检查是否重要更新
					$aS->checkImportantUpdate($aSetEntity, $aSetParams, true);
					
					$aS->removeKeepFields($aSetParams);
					$aSetParams['last_uid'] = $userId;
					$aSetParams['last_time'] = $now;
					
					//执行更新记录
					$aSetResult = $aSetInstance->update($aSetParams, array('att_set_id'=>$aSetId), $aS->createCheckDigits(), array('att_set_id'));
					if ($aSetResult===false) {
						$errMsg = 'update attendSetting error';
						ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
						return;
					}
				} else { //日期不同
					//检查是否重要更新
					$aS->checkImportantUpdate($aSetEntity, $aSetParams, false);
					
					$aS->removeKeepFields($aSetParams);
					$aSetParams['last_uid'] = $userId;
					$aSetParams['last_time'] = $now;
					
					//执行更新记录
					$aSetResult = $aSetInstance->update($aSetParams, array('att_set_id'=>$aSetId), $aS->createCheckDigits(), array('att_set_id'));
					if ($aSetResult===false) {
						$errMsg = 'update attendSetting error';
						ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
						return;
					}
				}
			}
			
			//获取原有的适用者列表
			$aSetTarResults = $aSetTarInstance->getRecordsByAttendSettingId($aSetId);
			if ($aSetTarResults===false) {
				$errMsg = 'get attendSetTarget error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			$oldSetTargets = array();
			foreach ($aSetTarResults as $aSetTar)
				array_push($oldSetTargets,$aSetTar['target_type_id']);
			
			//本次提交的适用者列表
			$sSetTargets= array();
			if (strlen($attSetTargets)>0) {
				//1,1000000000000030;2,999001;3,80
				$sSetTargets = preg_split('/;/', $attSetTargets);
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
					$delResult = $aSetTarInstance->deleteByAttendSettingTargetTypeAndId($aSetId, substr($sTarget, 0, $pos), substr($sTarget, $pos+1));
					if ($delResult===false) {
						$errMsg = 'deleteByAttendSettingTargetTypeAndId error';
						ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
						return;
					}
				}
			}
			
			//新建每一个适用范围对象记录
			if (count($toAddSetTargets)>0) {
				$aSetTar = new EBAttSetTarget();
				foreach ($toAddSetTargets as $sTarget) {
					if (strlen($sTarget)==0)
						continue;
					
					$pos = strpos($sTarget, ',');
					$stParams = array('target_id'=>substr($sTarget, $pos+1), 'target_type'=>substr($sTarget, 0, $pos), 'att_set_id'=>$aSetId, 'create_uid'=>$userId, 'create_time'=>$now);
					$aSetTarResult = $aSetTarInstance->insertOne($stParams, $aSetTar->createCheckDigits(), $aSetTarInstance->primaryKeyName, $errMsg);
					if ($aSetTarResult===false) {
						$errMsg = 'create one attSetTarget record error';
						ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
						return;
					}
				}
			}
			
			ResultHandle::successToJsonAndOutput('ok', null, null, $output);
			break;
	}
