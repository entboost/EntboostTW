<?php
require_once dirname(__FILE__).'/attendance_define.php';

/**
 * 寻找适用的考勤时间段[多个]
 * @param {timestamp} $signDateTime 指定时间戳
 * @param {boolean} $output 是否输出结果到页面
 * @param {string} $userId 用户编号
 * @param {array} $groupCodes 群组的编号列表
 * @param {string} $entCode 企业编号
 * @return {boolean|array} false=执行失败，array=考勤时间段列表
 */
function findActionRuleTimes($signDateTime, $output, $userId, $groupCodes, $entCode) {
	//声明引用外部变量
	global $WEEK_VALUES;
	
	$instance = AttendSettingService::get_instance();
	
	//查找相适应的考勤设置
	$settings = $instance->getAttendSettings($userId, $groupCodes, $entCode);
	
	if ($settings===false) {
		$errMsg = "getAttendSettings error";
		ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return false;
	}
	
	if (count($settings)>0) {
		$entity = $settings[0];
		log_info('found att_set_id='.$entity['att_set_id'].', setting_name='.$entity['name'].', target_type='.$entity['target_type'].', target_id='.$entity['target_id']);

		//查找相应的考勤规则
		$weekDayValue = $WEEK_VALUES[date('w', $signDateTime)];
		
		$times = $instance->getAttendRuleTimes($entity['att_set_id'], $weekDayValue);
		if (is_bool($times)) {
			$errMsg = "getAttendRuleTimes error";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		
		return $times;
	} else { //没有考勤设置，视为假期
		log_info("no attend setting, it's holiday for userId=$userId");
		return array();
		//return generateHolidayAttendRuleTimeRec();
	}
}

/**
 * 在指定时刻下，尝试分析出一个适用考勤时间段
 * @param {timestamp} $signDateTime 指定时间戳
 * @param {boolean} $output 是否输出结果到页面
 * @param {array} $times 时间段列表
 * @param {int} $actionType 考勤操作：1=签到，2=签退
 * @param {boolean} $signCheckMiddleTime 是否使用时间段中间作为参考
 * @param {boolean} $controlAction 是否控制"签到"、"签退"
 * @return {boolean|array} false=执行失败，array(关联数组，元素实际表示一个对象的各字段)=一个可执行的考勤时间段记录
 */
function analyzeAttendTimesWithActionType($signDateTime, $output, $times, $actionType, $signCheckMiddleTime, $controlAction) {
	$errMsg = ($actionType==1?"sign in":"sign out")." error";
	
	$toSignDate = date('Y-m-d', $signDateTime);
	$toSignTime = date('H:i:s', $signDateTime);
	$matchedTimeRec; //保存已匹配的时间段记录
	$lastTimeRec; //暂存上一条时间段记录
	$lastMiddleTime; //暂存上一条记录时间段的中间
	$lastSignoutTime; //暂存上一条记录的签退时间
	
	//遍历查找最迟的适用时间段
	foreach ($times as $timeRec) {
		//log_debug($timeRec);
		$signinTimeStr = $timeRec['signin_time'];
		$signoutTimeStr = $timeRec['signout_time'];
		$signinTime = strtotime($toSignDate.' '.$signinTimeStr);
		$signoutTime = strtotime($toSignDate.' '.$signoutTimeStr);
		$diff = $signoutTime - $signinTime;
		//两个时间的一半
		$middleTime = $signinTime + $diff/2;
		$middleTimeStr = date('Y-m-d H:i:s', $middleTime);
		log_debug("actionType=$actionType, toSignTime=$toSignTime, signinTimeStr=$signinTimeStr, middleTimeStr=$middleTimeStr, signoutTimeStr=$signoutTimeStr");
			
		//允许签到：当前时间小于[签到时间与签退时间的一半]
		//允许签退：当前时间大于等于[签到时间与签退时间的一半]，并且小于下一个考勤时间段(或第二天)
			
		//======== 签退 =======
		if (ACTION_TYPE_SIGN_OUT==$actionType) {
			if (!empty($matchedTimeRec)) {
				if ($signDateTime<$signinTime) { //签退操作不得晚于下一个时间段
					log_debug("match signout time before next signinTime $signinTimeStr");
					break;
				} else if ($signCheckMiddleTime && $signDateTime<$middleTime) { //签退时间未到[签到时间与签退时间的一半]，并且已进入下一个时间段，不允许签退
					log_debug("not arrive signout time $middleTimeStr");
					unset($matchedTimeRec);
					break;
				}
			}
			if (($signCheckMiddleTime && $signDateTime>=$middleTime) || (!$signCheckMiddleTime && $signDateTime>$signinTime)) {
				$matchedTimeRec = $timeRec;
			}
		}
		
		//======== 签到 =======
		if (ACTION_TYPE_SIGN_IN==$actionType) {
			if (($signCheckMiddleTime && $signDateTime<$middleTime) || (!$signCheckMiddleTime && $signDateTime<$signoutTime)) {
				$matchedTimeRec = $timeRec;
			}
			//签到时间小于上一个时间段的签退时间，不允许签到
			if (!empty($lastTimeRec) && !empty($matchedTimeRec)) {
				//$lastSignoutTimeStr = $nowDate.' '.$lastTimeRec['signout_time'];
				if ($signDateTime <= $lastSignoutTime) {
					$lastSignoutTimeStr = date('Y-m-d H:i:s', $lastSignoutTime);
					log_debug("not pass last signout time $lastSignoutTimeStr, use last record");
					$matchedTimeRec = $lastTimeRec;

					//继续判断$lastTimeRec是否受$signCheckMiddleTime限制，是否在合法的签到时间
					if (($signCheckMiddleTime && $signDateTime>=$lastMiddleTime) || (!$signCheckMiddleTime && $signDateTime>=$lastSignoutTime)) {
						unset($matchedTimeRec);
						log_debug("last record is not matched for sign in, signCheckMiddleTime=$signCheckMiddleTime");
					}
				}
				break;
			}
		}
			
		$lastTimeRec = $timeRec;
		$lastMiddleTime = $middleTime;
		$lastSignoutTime = $signoutTime;
	}

	if (!empty($matchedTimeRec)) {
		return $matchedTimeRec;
	} else { //没有可用时间段
		if ($controlAction) { //禁止"签到"、"签退"
			ResultHandle::errorToJsonAndOutput($errMsg, "no matched attend time", $output
					, ACTION_TYPE_SIGN_IN==$actionType?EBStateCode::$EB_STATE_DISABLE_SIGN_IN:EBStateCode::$EB_STATE_DISABLE_SIGN_OUT);
			return false;
		} else { //不禁止，由调用方决定下一步
			return null;
		}
	}	
}

/**
 * 产生假期考勤规则记录
 */
function generateHolidayAttendRuleTimeRec() {
	return array('holiday'=>true, 'att_rul_id'=>'0', 'att_tim_id'=>'0');
	/*
	if ($holiday) {
		$matchedTimeRec = array('holiday'=>true, 'att_rul_id'=>'0', 'att_tim_id'=>'0');
	} else {
		$weekNum = intval(date('w', $date));
		//周一至周五是工作日
		$holiday = true;
		if ($weekNum>0 && $weekNum<6)
			$holiday = false;
		
			$matchedTimeRec = array('holiday'=>false, 'att_rul_id'=>'0', 'att_tim_id'=>'0'
					, 'name'=>'默认考勤规则', 'signin_time'=>'09:00:00', 'signin_ignore'=>'0', 'signout_time'=>'18:00:00', 'signout_ignore'=>'0'
					, 'work_day'=>'31', 'att_tim_id1'=>'0', 'att_tim_id2'=>'0', 'att_tim_id3'=>'0', 'att_tim_id4'=>'0', 'flexible_work'=>'0', 'flag'=>'0'
			);		
	}
	return $matchedTimeRec;*/
}

/**
 * 执行签到/签退操作
 * @param {timestamp} $signDateTime 指定时间戳
 * @param {boolean} $output 是否输出结果到页面
 * @param {int} $actionType 考勤操作：1=签到，2=签退
 * @param {array} $matchedTimeRec 一个可执行的考勤时间段记录(关联数组，元素实际表示一个对象的各字段)
 * @param {string} $userId 用户编号
 * @param {string} $userName 用户名称
 * @param {string} $groupCode 群组编号(暂保留，填NULL)
 * @param {string} $entCode 企业编号
 * @param boolean 执行结果
 */
function executeSignInOutAction($signDateTime, $output, $actionType, $matchedTimeRec, $userId, $userName, $groupCode, $entCode) {
	$errMsg = ($actionType==ACTION_TYPE_SIGN_IN?"sign in":"sign out")." error";
	$signDate = date('Y-m-d', $signDateTime);
	$instance = AttendRecordService::get_instance();
	
	//！！！==注意使用假期类型的$matchedTimeRec，缺少某些字段==!!!
	//假期判断 if(!empty($matchedTimeRec['holiday']))
	//假期按加班进行签到(签退)
	$attRuleId = $matchedTimeRec['att_rul_id'];
	$attTimId = $matchedTimeRec['att_tim_id'];
	
	//获取假期签到、签退的记录列表
	$records = $instance->getAttendRecord($entCode, null, $userId, $signDate, 0, 0);
	if (is_bool($records)) {
		ResultHandle::errorToJsonAndOutput($errMsg, "getAttendRecord error", $output);
		return false;
	}
	if (count($records)>0) {
		$record = $records[0];
		$signin_time = $record['signin_time'];
		if (!empty($signin_time) && $signDateTime>=strtotime($signin_time) && $actionType==ACTION_TYPE_SIGN_OUT) {
			log_info("found holiday attend record ".$record['att_rec_id']);
			$attRuleId = 0;
			$attTimId = 0;
		}
	}
	
	//获取今天已签到或签退的记录列表
	$records = $instance->getAttendRecord($entCode, null, $userId, $signDate, $attRuleId, $attTimId);
	if (is_bool($records)) {
		ResultHandle::errorToJsonAndOutput($errMsg, "getAttendRecord error", $output);
		return false;
	}
	
	//当前日期时间字符串
	$nowStr = date('Y-m-d H:i:s', time());
	//指定签到时间字符串
	$signDateTimeStr = date('Y-m-d H:i:s', $signDateTime);
	
	if (ACTION_TYPE_SIGN_IN==$actionType) { //签到
		//一个时间段仅可以一次签到

		if (empty($records)) { //没有签到记录，执行签到(新建)
			$form = new EBAttendRecord();
			$form->owner_type = 1;
			$form->owner_id = $entCode;
			$form->user_id = $userId;
			$form->user_name = $userName;
			$form->create_time = $nowStr;
			//$form->last_time =
			$form->att_rul_id = $attRuleId;
			$form->att_tim_id = $attTimId;
			$form->signin_time = $signDateTimeStr;
			$form->signin_from = 1; //PC
			$form->attend_date = $signDate;
			
			$params = $form->createFields();
			$checkDigits = $form->createCheckDigits();
			
			$rId = $instance->insertOne($params, $checkDigits, 'att_rec_id', $outErrMsg);
			//执行成功
			if ($rId!==false) {
				log_info("sign in finish, new rId=$rId");
				ResultHandle::successToJsonAndOutput('success', null, null, $output);
				return true;
			}
			//执行失败
			ResultHandle::errorToJsonAndOutput($errMsg, "insert one attend record error", $output);
			return false;
		} else if (empty($records[0]['signin_time'])) { //有记录但没签到，执行签到(更新)
			$record = $records[0];
			
			$setCheckDigits = array('signin_from');
			$whereCheckDigits = array('att_rec_id');
			$sets = array('last_time'=>$nowStr, 'signin_time'=>$signDateTimeStr, 'signin_from'=>1);
			$wheres = array('att_rec_id'=>$record['att_rec_id']);
			
			$updateResult = $instance->update($sets, $wheres, $setCheckDigits, $whereCheckDigits);
			//执行成功
			if ($updateResult!==false) {
				log_info("sign in finish, update rId=".$record['att_rec_id']);
				ResultHandle::successToJsonAndOutput('success', null, null, $output);
				return true;
			}
			//执行失败
			ResultHandle::errorToJsonAndOutput($errMsg, "update attend record error", $output);
			return false;
		} else { //已有记录
			$record = $records[0];
			$lastSigninTimeStr= $record['signin_time'];
			$recordId = $record['att_rec_id'];
			
			ResultHandle::errorToJsonAndOutput("duplicate sign in", "duplicate sign in, last sign in time $lastSigninTimeStr, recordId=$recordId, miss it", $output, EBStateCode::$EB_STATE_ALEADY_SIGN_IN);
			return true;
		}
	} else if (ACTION_TYPE_SIGN_OUT==$actionType) { //签退
		//同一个时间段可多次签退
		if (empty($records)) { //没有签退记录，执行签退
			$form = new EBAttendRecord();
			$form->owner_type = 1;
			$form->owner_id = $entCode;
			$form->user_id = $userId;
			$form->user_name = $userName;
			$form->create_time = $nowStr;
			//$form->last_time =
			$form->att_rul_id = $attRuleId;
			$form->att_tim_id = $attTimId;
			$form->signout_time = $signDateTimeStr;
			$form->signout_from = 1; //PC
			$form->attend_date = $signDate;

			$params = $form->createFields();
			$checkDigits = $form->createCheckDigits();
			//log_debug($checkDigits);

			$rId = $instance->insertOne($params, $checkDigits, 'att_rec_id', $outErrMsg);
			//执行成功
			if ($rId!==false) {
				log_info("sign out finish, new rId=$rId");
				ResultHandle::successToJsonAndOutput('success', null, null, $output);
				return true;
			}
			//执行失败
			ResultHandle::errorToJsonAndOutput($errMsg, "insert one attend record error", $output);
			return false;
		} else { //已有记录
			$record = $records[0];
			$restDuration = 0;
			$workDuration = 0;
			
			$signinTime = $record['signin_time'];
			if (!empty($signinTime)) {
				//获取标准休息时长(分钟)
				if (!empty($attTimId)) {
					$timResult = AttendSettingService::get_instance()->getAttendTime($attTimId);
					if ($timResult!==false && count($timResult)>0) {
						$restDuration = intval($timResult[0]['rest_duration']);
					}
				}
				//计算工作时长(分钟) = 实际签退时间-实际签到时间-标准休息时长
				$minutes = diffMinutesBetweenTwoTimes($nowStr, $signinTime);
				$workDuration = $minutes-$restDuration;
				//log_debug("workDuration=$workDuration");
				if ($workDuration<0)
					$workDuration = 0;
			}
			
			$sets = array('last_time'=>$nowStr, 'signout_time'=>$signDateTimeStr, 'signout_from'=>1);
			if ($workDuration>0)
				$sets['work_duration'] = $workDuration;
			
			$wheres = array('att_rec_id'=>$record['att_rec_id']);
			
			$setCheckDigits = array('signout_from');
			$whereCheckDigits = array('att_rec_id');
			
			$updateResult = $instance->update($sets, $wheres, $setCheckDigits, $whereCheckDigits);
			//执行成功
			if ($updateResult!==false) {
				log_info("sign out finish, update rId=".$record['att_rec_id']);
				ResultHandle::successToJsonAndOutput('success', null, null, $output);
				return true;
			}
			//执行失败
			ResultHandle::errorToJsonAndOutput($errMsg, "update attend record error", $output);
			return false;
		}
	}
}

/**
 * 判断指定的"签到"、"签退"时间是否在假期内
 * @param {timestamp} $signDateTime
 * @param {string} $userId 用户编号
 * @param {array} $groupCodes 群组的编号列表
 * @param @param {string} $entCode 企业编号
 */
function isHoliday($signDateTime, $userId, $groupCodes, $entCode) {
	$signDate = date('Y-m-d', $signDateTime);
	
	$holidays = HolidaySettingService::get_instance()->getHolidays($entCode, $groupCodes, $userId, $signDate);
	if ($holidays===false) //发生了错误
		return false;
	
	//参考预设置的假期
	$holidayMiddleTime = strtotime($signDate." 12:00:00");
	$isHoliday = false;
	if (count($holidays)>0) {
		foreach ($holidays as $holidayRec) {
			$flag = intval($holidayRec['flag']);
			if ($flag==0) { //全天
				$isHoliday = true;
				log_debug("signDate $signDate all day is holiday");
				break;
			} else if ($flag==1) { //上半天
				if ($signDateTime<=$holidayMiddleTime) {
					$isHoliday = true;
					log_debug("signDate $signDate forenoon is holiday");
					break;
				}
			} else if ($flag==2) { //下半天
				if ($signDateTime>=$holidayMiddleTime) {
					$isHoliday = true;
					log_debug("signDate $signDate afternoon is holiday");
					break;
				}
			}
		}
	}
	
	return $isHoliday;
}

/**
 * 决定当前时间能执行"签到"还是"签退"
 * @param {timestamp} $signDateTime 指定的时间戳
 * @param {boolean} $output 是否输出结果到页面
 * @param {string} $userId 用户编号
 * @param {array} $groupCodes 群组的编号列表
 * @param {string} $entCode 企业编号
 * @return {int} 签到(ACTION_TYPE_SIGN_IN)或签退(ACTION_TYPE_SIGN_OUT)
 */
function decideSignInOutAction($signDateTime, $output, $userId, $groupCodes, $entCode) {
	$attendSignIn = ACTION_TYPE_SIGN_IN;
	$canSignIn = true;
	$signDate = date('Y-m-d', $signDateTime);
	
	$times = findActionRuleTimes($signDateTime, $output, $userId, $groupCodes, $entCode);
	if ($times===false) //发生了错误
		return $attendSignIn;
	
	$isHoliday = isHoliday($signDateTime, $userId, $groupCodes, $entCode);
	
	//检测签到情况
	if (count($times)>0) {
		$actionType = ACTION_TYPE_SIGN_IN;
		$signCheckMiddleTime = $isHoliday?false:true;
		$controlAction = $isHoliday?false:true;
		$matchedTimeRec = analyzeAttendTimesWithActionType($signDateTime, $output, $times, $actionType, $signCheckMiddleTime, $controlAction);
		
		if ($isHoliday) {//符合假期设置的假期
			if (!empty($matchedTimeRec))
				$matchedTimeRec['holiday'] = true;
			else {
				log_info("(actionType $actionType) it's holiday for userId $userId");
				$matchedTimeRec = generateHolidayAttendRuleTimeRec();
			}
		}
	} else {//没有工作时间段的假期(普通休息日)
		log_info("no attend time, it's holiday for userId $userId");
		$matchedTimeRec = generateHolidayAttendRuleTimeRec();
	}
	
	if ($matchedTimeRec!==false && !empty($matchedTimeRec)) {
		if(!empty($matchedTimeRec['holiday'])) { //假期
			//获取当天已签到或签退的记录列表
			$attRuleId = $matchedTimeRec['att_rul_id'];
			$attTimeId = $matchedTimeRec['att_tim_id'];
			$records = AttendRecordService::get_instance()->getAttendRecord($entCode, null, $userId, $signDate, $attRuleId, $attTimeId);
			if (is_array($records) && count($records)>0) {
				foreach ($records as $record) {
					if (!empty($record['signin_time'])) { //已有签到时间
						$canSignIn = false;
						break;
					}
				}
			}
		} else { //工作时间
			$attRuleId = $matchedTimeRec['att_rul_id'];
			$attTimeId = $matchedTimeRec['att_tim_id'];
			$records = AttendRecordService::get_instance()->getAttendRecord($entCode, null, $userId, $signDate, $attRuleId, $attTimeId);
			if (is_array($records) && count($records)>0) {
				foreach ($records as $record) {
					if (!empty($record['signin_time'])) { //已有签到时间
						$canSignIn = false;
						break;
					}
				}
			}
		}
	} else {
		$canSignIn = false;
	}
	
	//检测签退情况
	if (!$canSignIn) {
		if (count($times)>0) {
			$actionType = ACTION_TYPE_SIGN_OUT;
			$signCheckMiddleTime = false;
			$controlAction = $isHoliday?false:true;
			$matchedTimeRec = analyzeAttendTimesWithActionType($signDateTime, $output, $times, $actionType, $signCheckMiddleTime, $controlAction);
			
			if ($isHoliday) {//符合假期设置的假期
				if (!empty($matchedTimeRec))
					$matchedTimeRec['holiday'] = true;
				else {
					log_info("(actionType $actionType) it's holiday for userId $userId");
					$matchedTimeRec = generateHolidayAttendRuleTimeRec();
				}
			}
		} else {
			log_info("no attend time, it's holiday for userId=$userId");
			$matchedTimeRec = generateHolidayAttendRuleTimeRec();
		}
		
		if ($matchedTimeRec!==false && !empty($matchedTimeRec)) {
			$attendSignIn = ACTION_TYPE_SIGN_OUT;
		}
	}
	
	return $attendSignIn;
}

/**
 * 让考勤规则变更生效的作业
 * @param {boolean} $output 是否输出结果到页面
 * @param {string} $entCode 企业编号
 * @return boolean false=执行失败， true=执行成功
 */
function runAttendSettingEffective($output, $entCode) {
	if (empty($entCode)) {
		$errMsg = 'runAttendSettingEffective error, $entCode is empty';
		ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return false;
	}
	
	$aSetInstance = AttendSettingService::get_instance();
	$aRuleInstance = AttendRuleService::get_instance();
	
	//获取未生效的配置记录
	$results = $aSetInstance->getNeedEffectiveRecords($entCode);
	if ($results===false) {
		$errMsg = 'getNeedEffectiveRecords error';
		ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return false;
	}
	
	//遍历记录组装更新结构
	$toUpdateSettings = array();
	$toUpdateRules = array();
	foreach ($results as $record) {
		for ($i=1; $i<=7; $i++) {
			if ($record["att_rul_newid$i"]!=='0') {
				$aSetId = $record['att_set_id'];
				if (!array_key_exists($aSetId, $toUpdateSettings))
					$toUpdateSettings[$aSetId] = array();
				$toUpdateSettings[$aSetId][$i] = $record["att_rul_newid$i"];
			}
		}
		
		for ($i=1; $i<=4; $i++) {
			if ($record["att_tim_newid$i"]!=='0') {
				$rulId = $record['att_rul_id'];
				if (!array_key_exists($rulId, $toUpdateRules))
					$toUpdateRules[$rulId] = array();
				$toUpdateRules[$rulId][$i] = $record["att_tim_newid$i"];
			}
		}
	}
	
	log_info($toUpdateSettings);
	log_info($toUpdateRules);
	
	//执行考勤时间段的生效变更
	foreach ($toUpdateRules as $rulId=>$newTimIds) {
		$effectResult = $aRuleInstance->effectTimeNewIds($rulId, $newTimIds);
		if ($effectResult===false) {
			$errMsg = 'effectTimeNewIds error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}		
	}
	//执行考勤规则的生效变更
	foreach ($toUpdateSettings as $aSetId=>$newRulIds) {
		$effectResult = $aSetInstance->effectRuleNewIds($aSetId, $newRulIds);
		if ($effectResult===false) {
			$errMsg = 'effectRuleNewIds error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;			
		}
	}
	
	return true;
}

/**
 * 对指定一批用户执行考勤日结扩展作业
 * @param {boolean} $output 是否输出结果到页面
 * @param {array} $users 待执行考勤日结作业的用户对象列表
 * @param {string} $entCode 企业编号
 * @param {string} $attendDate 考勤日期
 * @return boolean false=执行失败， true=执行成功
 */
function runAttendDailyExtensionForUsers($output, $users, $entCode, $attendDate) {
	global $WEEK_VALUES;
	$aRecInstance = AttendRecordService::get_instance();
	$adInstance = AttendDailyService::get_instance();
	$aSetInstance = AttendSettingService::get_instance();
	
	//遍历处理每一个用户的考勤记录
	foreach ($users as $user) {
		$userId = $user['user_id'];
		$attSets = $aSetInstance->getAttendSettings($userId, array(), $entCode);
		if ($attSets===false || !is_array($attSets)) {
			$errMsg = "getAttendSettings error";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		
		$attSetId = '0';
		if (count($attSets)>0)
			$attSetId = $attSets[0]['att_set_id'];
		$weekDayValue = $WEEK_VALUES[date('w', strtotime($attendDate))];
		$times = $aSetInstance->getAttendRuleTimes($attSetId, $weekDayValue);
		if ($times===false || !is_array($times)) {
			$errMsg = "getAttendRuleTimes error";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		
		$sets = array();
		//分析时间段与考勤记录情况
		if (count($times)>0) {
			$sets['calcul_day'] = 1; //是否考勤日
			$expectedCount = 0;
			$expectedDuration = 0;
			foreach ($times as $timeEntity) {
				$expectedCount++;
				$minutes = diffMinutesBetweenTwoTimes($attendDate.' '.$timeEntity['signin_time'], $attendDate.' '.$timeEntity['signout_time']);
				$restDuration = intval($timeEntity['rest_duration']);
				$expectedDuration += (($minutes-$restDuration)>0?($minutes-$restDuration):0);
			}
			
			$sets['expected_count'] = $expectedCount; //应出勤次数
			$sets['expected_duration'] = $expectedDuration; //应出勤时长
		} else {
			$sets['calcul_day'] = 0; //是否考勤日
			$sets['expected_count'] = 0; //应出勤次数
			$sets['expected_duration'] = 0; //应出勤时长
		}
		
		//获取 "签到"/"签退" 记录
		$aRecords = $aRecInstance->getAttendRecord6($entCode, null, $userId, $attendDate);
		if ($aRecords===false || !is_array($aRecords)) {
			$errMsg = "getAttendRecord error";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		
		//实出勤次数和时长
		$realCount = 0;
		$realDuration = 0;
		//签到次数
		$signinCount = 0;
		//签退次数
		$signoutCount = 0;
		//外勤次数
		$workOutsideCount = 0;
		//外勤时长
		$workOutsideDuration = 0;
		foreach ($aRecords as $recEntity) {
			$workDuration = intval($recEntity['work_duration']);
			$reqDuration = intval($recEntity['req_duration']);
			
			if ($workDuration>0 || $reqDuration>0)
				$realCount++;
			
			if ($reqDuration>0)
				$realDuration += $reqDuration;
			else 
				$realDuration += $workDuration;
			
			if (!empty($recEntity['signin_time']))
				$signinCount++;
			if (!empty($recEntity['signout_time']))
				$signoutCount++;
			
			$recStateArry = getAttendRecStateAndRecIdFieldName($recEntity, $recEntity['att_rec_id']);
			$recState = intval($recStateArry[0]);
			//外勤
			if (($recState&ATTEND_STATE_WORK_OUTSIDE)!=0) {
				$workOutsideCount++;
				$workOutsideDuration += $reqDuration;
			}
		}
		$sets['real_count'] = $realCount; //实出勤次数
		$sets['real_duration'] = $realDuration; //实出勤时长
		$sets['signin_count'] = $signinCount; //签到次数
		$sets['signout_count'] = $signoutCount; //签退次数
		$sets['work_outside_count'] = $workOutsideCount; //外勤次数
		$sets['work_outside_duration'] = $workOutsideDuration; //外勤时长
		
		//获取考勤日期对应的考勤记录
		$adZeroResult = $adInstance->getZeroRecordInOneAttendDate($entCode, null, $userId, $attendDate);
		if ($adZeroResult===false) {
			$errMsg = "getZeroRecordInOneAttendDate error";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		if (count($adZeroResult)==0) {
			$errMsg = "cannot found attendDaily record for userId $userId, attendDate $attendDate";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		
		//加班次数和时长
		$workOvertimeCount = 0;
		$workOvertimeDuration = 0;
		//异常考勤次数
		$abnormalCount = 0;
		//未签到次数
		$unsigninCount = 0;
		//迟到次数
		$lateCount = 0;
		//未签退次数
		$unsignoutCount = 0;
		//早退次数
		$leaveEarlyCount = 0;

		$adZeroEntity = $adZeroResult[0];
		//加班相关
		if (!empty($adZeroEntity['att_rec_id0']) && !empty($adZeroEntity['att_rec_id'])
				&& ((intval($adZeroEntity['att_rec_id0_state'])&ATTEND_STATE_WORK_OVERTIME)==ATTEND_STATE_WORK_OVERTIME)) {
			$workOvertimeCount++;
			$workOvertimeDuration += intval($adZeroEntity['req_duration']);
		}
		//签到、签退相关
		for ($i=1; $i<=4; $i++) {
			$recId = $adZeroEntity["att_rec_id$i"];
			$recState = intval($adZeroEntity['att_rec_id'.$i.'_state']);
			if ($recId!='0') {
				//未通过考勤审批
				$noAttendanceReqPass = !(($recState&ATTENDANCE_STATE_NOT_ABNORMAL_GROUP)!=0);
				
				//考勤异常
				if (($recState&ATTENDANCE_STATE_ABNORMAL_GROUP)!=0 && $noAttendanceReqPass)
					$abnormalCount++;
				//未签到
				if (($recState&(ATTEND_STATE_UNSIGNIN|ATTEND_STATE_ABSENTEEISM))!=0 && $noAttendanceReqPass)
					$unsigninCount++;
				//迟到
				if (($recState&ATTEND_STATE_LATE)!=0 && $noAttendanceReqPass)
					$lateCount++;
				//未签退
				if (($recState&(ATTEND_STATE_UNSIGNOUT|ATTEND_STATE_ABSENTEEISM))!=0 && $noAttendanceReqPass)
					$unsignoutCount++;
				//迟到
				if (($recState&ATTEND_STATE_LEFT_EARLY)!=0 && $noAttendanceReqPass)
					$leaveEarlyCount++;
				//外勤
// 				if (($recState&ATTEND_STATE_WORK_OUTSIDE)!=0)
// 					$workOutsideCount++;
			}
		}
		
		$sets['work_overtime_count'] = $workOvertimeCount;
		$sets['work_overtime_duration'] = $workOvertimeDuration;
		$sets['abnormal_count'] = $abnormalCount;
		$sets['unsignin_count'] = $unsigninCount;
		$sets['late_count'] = $lateCount;
		$sets['unsignout_count'] = $unsignoutCount;
		$sets['leave_early_count'] = $leaveEarlyCount;
// 		$sets['work_outside_count'] = $workOutsideCount;
		
		$wheres = array($adInstance->primaryKeyName=>$adZeroEntity['att_dai_id']);
		//执行更新
		$result = $adInstance->update($sets, $wheres);
		if ($result===false) {
			$errMsg = "update attendDaily error";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
	}
	
	return true;
}

/**
 * 对指定一批用户执行考勤日结作业
 * @param {boolean} $output 是否输出结果到页面
 * @param {array} $users 待执行考勤日结作业的用户对象列表
 * @param {string} $entCode 企业编号
 * @param {string} $attendDate 考勤日期
 * @return boolean false=执行失败， true=执行成功
 */
function runAttendDailyForUsers($output, $users, $entCode, $attendDate) {
	global $WEEK_VALUES;
	$aRecInstance = AttendRecordService::get_instance();
	$aDailyInstance = AttendDailyService::get_instance();
	$aSetInstance = AttendSettingService::get_instance();
	$aReqInstance = AttendReqService::get_instance();
	$reqItemInstance = AttendReqItemService::get_instance();
	
	//遍历处理每一个用户的考勤记录
	foreach ($users as $user) {
		unset($form); //重置$form变量
		$userId = $user['user_id'];
		$userName = $user['user_name'];
		
		//查询该用户在本考勤日期是否有已批准的请假申请
		$reqResults = $aReqInstance->getAttendReqsOfLeave($entCode, array(), $userId, $attendDate);
		if ($reqResults===false) {
			$errMsg = "getAttendReqsOfLeave error";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		
		$reqTimeRecords = null;
		if (count($reqResults)>0) {
			//遍历每一个考勤审批申请
			foreach ($reqResults as $reqEntity) {
				$reqId = $reqEntity['att_req_id'];
				
				//获取考勤审批申请相关的考勤编号
				$limit = 1000;
				$reqTimeRecords = $reqItemInstance->search($reqItemInstance->fieldNames, array('att_req_id'=>$reqId), array('att_req_id'), null, $limit);
				if ($reqTimeRecords===false) {
					$errMsg = 'search attendReqItem error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return false;
				}
				
				//补偿缺少的关联子项
				$compResult = compensateAttendReqItems($entCode, null, $reqId, $reqEntity
						, date('Y-m-d H:i:s', time()), 2, $reqTimeRecords, $aRecInstance, $reqItemInstance, $newReqItemCount);
				if ($compResult===false) {
					$errMsg = 'compensateAttendReqItems error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return false;
				}
				log_info("create $newReqItemCount attendReqItem records");
				
				//重新获取考勤审批申请相关的考勤编号
				if ($newReqItemCount>0) {
					$reqTimeRecords = $reqItemInstance->search($reqItemInstance->fieldNames, array('att_req_id'=>$reqId), array('att_req_id'), null, $limit);
					if ($reqTimeRecords===false) {
						$errMsg = 'search attendReqItem error';
						ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
						return false;
					}			
				}
			}
		}
		
		//查询是否已存在考勤日结记录
		$aDailyResult = $aDailyInstance->getDailyRecords($entCode, array(), $userId, $attendDate);
		if ($aDailyResult===false) {
			$errMsg = "getDailyRecords error";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		
		if (count($aDailyResult)>0) {
			$form = new EBAttendDaily();
			$form->setValuesFromRecord(null, $aDailyResult[0]);
		}
		
		//获取考勤设置列表
		$attSets = $aSetInstance->getAttendSettings($userId, array(), $entCode);
		if ($attSets===false || !is_array($attSets)) {
			$errMsg = "getAttendSettings error";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		
		//获取各个考勤时间段
		$attSetId = '0';
		if (count($attSets)>0)
			$attSetId = $attSets[0]['att_set_id'];
		$weekDayValue = $WEEK_VALUES[date('w', strtotime($attendDate))];
		$times = $aSetInstance->getAttendRuleTimes($attSetId, $weekDayValue);
		if ($times===false || !is_array($times)) {
			$errMsg = "getAttendRuleTimes error";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		
		//分析时间段与考勤记录情况
		if (count($times)>0) { //工作日
			$ruleIdsAndTimeIds = array();
			array_push($ruleIdsAndTimeIds, array('0', '0')); //没有绑定考勤时间段的考勤记录
			foreach ($times as $timeRec) {
				array_push($ruleIdsAndTimeIds, array($timeRec['att_rul_id'], $timeRec['att_tim_id']));
			}
			//获取 "签到"/"签退" 记录
			$aRecords = $aRecInstance->getAttendRecord2($entCode, null, $userId, $attendDate, $ruleIdsAndTimeIds);
			if ($aRecords===false || !is_array($aRecords)) {
				$errMsg = "getAttendRecord2 error";
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return false;
			}
	
			//遍历每一个工作时间段
			foreach ($times as $timeRec) {
				$matchedARec = null;
				$zeroARec = null;
				//考勤规则匹配考勤记录
				foreach ($aRecords as $aRec) {
					$rulId1 = $timeRec['att_rul_id'];
					$timId1 = $timeRec['att_tim_id'];
					$rulId2 = $aRec['att_rul_id'];
					$timId2 = $aRec['att_tim_id'];
					
					if ($rulId2==='0' && $timId2==='0') {// 没有绑定考勤时间段的考勤记录
						$zeroARec = $aRec;
					}
					if ($rulId1===$rulId2 && $timId1===$timId2) { //匹配已绑定考勤时间段的考勤记录
						log_debug("match rulId=$rulId1, timId=$timId1");
						$matchedARec = $aRec;
						break;
					}
				}
				
				//如果"标准考勤时段"不存在对应的"实际考勤记录"，并且不存在没有绑定考勤时间段的考勤记录时，自动创建一条"虚拟考勤记录"
				if (empty($matchedARec) && empty($zeroARec)) {
					$rform = new EBAttendRecord();
					$rform->owner_type = 1;
					$rform->owner_id = $entCode;
					$rform->user_id = $userId;
					$rform->user_name = $userName;
					$rform->create_time = date('Y-m-d H:i:s', time());
					//$rform->last_time =
					$rform->att_rul_id = $timeRec['att_rul_id'];
					$rform->att_tim_id = $timeRec['att_tim_id'];
					//$rform->signin_time = $signDateTimeStr;
					//$rform->signin_from = 1; //PC
					$rform->attend_date = $attendDate;
					$rform->data_flag = 1;
					
					$params = $rform->createFields();
					$checkDigits = $rform->createCheckDigits();
						
					$rId = $aRecInstance->insertOne($params, $checkDigits, 'att_rec_id', $errMsg);
					//插入考勤记录失败
					if ($rId===false) {
						ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
						return false;
					}
					//插入虚拟考勤记录成功
					log_info("insert one virtual attend record success, new rId=$rId");
					//
					$results = $aRecInstance->search($aRecInstance->fieldNames, array($aRecInstance->primaryKeyName=>$rId), null, null, 1, 0, SQLParamComb_TYPE_AND, $errMsg);
					if ($results===false) {
						ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
						return false;
					}
					if (count($results)>0)
						$matchedARec = $results[0];
				}
				
				if (createAttendDaily($form, $entCode, $userId, $userName, $attendDate, $timeRec, $matchedARec, $reqTimeRecords)===false) {
					$errMsg = "createAttendDaily error";
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return false;
				}
			}
			
			//处理没有绑定时间段的考勤记录
			foreach ($aRecords as $aRec) {
				if ($aRec['att_rul_id']==='0' && $aRec['att_tim_id']==='0') {
					log_info("zero tim record match, att_rec_id=".$aRec['att_rec_id']);
					$form->att_rec_id0 = $aRec['att_rec_id'];
					break;
				}
			}
			
			if (!saveAttendDaily($form)) {
				$errMsg = "saveAttendDaily error";
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return false;
			}
		} else { //非工作日
			log_debug("no attendTime, it's holiday for userId=$userId");
			
			$ruleIdsAndTimeIds = array(array('0', '0')); //假期签到记录
			//获取 "签到"/"签退" 记录
			$aRecords = $aRecInstance->getAttendRecord2($entCode, null, $userId, $attendDate, $ruleIdsAndTimeIds);
			if ($aRecords===false || !is_array($aRecords)) {
				$errMsg = "getAttendRecord2 error";
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return false;
			}
			
			if (createAttendDaily($form, $entCode, $userId, $userName, $attendDate)===false) {
				$errMsg = "createAttendDaily error";
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return false;
			}
			
			if (count($aRecords)>0) {
				$recId = $aRecords[0]['att_rec_id'];
				$form->att_rec_id0 = $recId;
				log_info("work in holiday, att_rec_id $recId");
			}
			
			if (!saveAttendDaily($form)) {
				$errMsg = "saveAttendDaily error";
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return false;
			}
		}
	}
	
	return true;
}

/**
 * 保存或更新考勤日结记录
 * @param {object} $form 考勤日结对象
 * @return boolean 是否执行成功
 */
function saveAttendDaily($form) {
	if (!empty($form)) {
		$aDailyInstance = AttendDailyService::get_instance();
		$primaryKeyName = $aDailyInstance->primaryKeyName;
		$params = $form->createFields();
		$checkDigits = $form->createCheckDigits();
		
		//新建记录
		if (empty($form->{$primaryKeyName})) {
			$rId = $aDailyInstance->insertOne($params, $checkDigits, $primaryKeyName, $outErrMsg);
			if ($rId===false) { //执行失败
				log_err("insert AttendDaily record error");
				return false;
			}
			
			log_debug("insert AttendDaily record success, new rId=$rId");
		} else { //更新记录
			$wheres = array($primaryKeyName=>$form->{$primaryKeyName});
			$sets = $params;
			$form->removeKeepFields($sets);
			
			$updateResult = $aDailyInstance->update($sets, $wheres, $checkDigits, array($primaryKeyName));
			if ($updateResult===false) { //执行失败
				log_err("update AttendDaily record error");
				return false;
			}
			
			log_debug("update AttendDaily record success");
		}
	}
	return true;
}

/**
 * 创建一个考勤日结记录对象(非数据库)
 * @param {EBAttendDaily} $form [输出参数] 考勤日结记录对象
 * @param {string} $entCode 企业编号
 * @param {string} $userId 用户编号
 * @param {string} $userName 用户名称
 * @param {string} $attendDate 考勤日期
 * @param {array} $timeRec [可选] 一个可参考的考勤时间段记录(关联数组，元素实际表示一个对象的各字段)
 * @param {array} $matchedARec [可选] 一个已实际执行的考勤时间段记录(关联数组，元素实际表示一个对象的各字段)
 * @param {array} $reqTimeRecords [可选] 考勤审批申请(请假)的子项列表
 * @return boolean 是否执行成功
 */
function createAttendDaily(&$form, $entCode, $userId, $userName, $attendDate, $timeRec=NULL, $matchedARec=NULL, $reqTimeRecords=NULL) {
	log_debug($timeRec);
	$groupCodes = array(); //待定：暂不支持
	
	//如果传入$form对象为空，创建一个新的
	if (!isset($form)) {
		$form = new EBAttendDaily();
		$form->owner_type = 1;
		$form->owner_id = $entCode;
		$form->user_id = $userId;
		$form->user_name = $userName;
		$form->create_time = date('Y-m-d H:i:s');
		$form->attend_date = $attendDate;
	} else {
		$form->invalid = 0; //使记录有效
	}
	
	//没有可参考的考勤时间段，认为非工作日
	//没有匹配的考勤记录
	if (empty($timeRec) || empty($matchedARec)) {
		//忽略处理
		//为支持更新，所以此处不再赋值，以保持旧记录的值
		//$form->att_rec_id0 = 0;
		//$form->att_rec_id0_state = ATTEND_STATE_DEFAULT;
		return true;
	}
	
	$flexibleWork = intval($timeRec['flexible_work']); //是否弹性工作机制：0=否，1=是
	$standardSigninTime = strtotime("$attendDate ".$timeRec['signin_time']); //参考签到时间，不允许为空
	$standardSignoutTime = strtotime("$attendDate ".$timeRec['signout_time']); //参考签退时间，不允许为空
	$signinIgnore = intval($timeRec['signin_ignore']);	//不计入迟到的时长(分钟)
	$signoutIgnore = intval($timeRec['signout_ignore']); //不计入早退的时长(分钟)
	
	if (!empty($matchedARec)) { //有记录
		//判断出勤状态
		$recState = ATTEND_STATE_DEFAULT;
		$signinTime = $matchedARec['signin_time']; //实际签到时间，可能为空
		$signoutTime = $matchedARec['signout_time']; //实际签退时间，可能为空
		//转化为timestamp时间戳类型
		$signinTime = empty($signinTime)?null:strtotime($signinTime);
		$signoutTime = empty($signoutTime)?null:strtotime($signoutTime);
		
		log_debug("signinTime=".(empty($signinTime)?"":date("Y-m-d H:i:s", $signinTime)).", standardSigninTime=".date("Y-m-d H:i:s", $standardSigninTime)
				.", signoutTime=".(empty($signoutTime)?"":date("Y-m-d H:i:s", $signoutTime)).", standardSignoutTime=".date("Y-m-d H:i:s", $standardSignoutTime));
		
		if (empty($signinTime) && empty($signoutTime)) { //旷工(缺席)
			$recState = ATTEND_STATE_ABSENTEEISM;
		} else if(empty($signinTime) && !empty($signoutTime)) { //未签到
			$recState = ATTEND_STATE_UNSIGNIN;
			
			if ($flexibleWork==0) { //严格按照考勤时间段，计算早退
				//参考不计入[早退]的缓冲时长(分钟)
				if ($signoutIgnore>0) {
					$signoutTime = strtotime("+$signoutIgnore minute", $signoutTime);
					log_debug("ignore $signoutIgnore minute(s) to new signoutTime ".date('Y-m-d H:i:s', $signoutTime));
				}
				//早退
				if ($signoutTime<$standardSignoutTime)
					$recState |= ATTEND_STATE_LEFT_EARLY;
			}
		} else if (!empty($signinTime) && empty($signoutTime)) { //未签退
			$recState = ATTEND_STATE_UNSIGNOUT;
			
			if ($flexibleWork==0) { //严格按照考勤时间段，计算迟到
				//参考不计入[迟到]的缓冲时长(分钟)
				if ($signinIgnore>0) {
					$signinTime = strtotime("-$signinIgnore minute", $signinTime);
					log_debug("ignore $signinIgnore minute(s) to new signinTime ".date('Y-m-d H:i:s', $signinTime));
				}
				//迟到
				if ($signinTime>$standardSigninTime)
					$recState |= ATTEND_STATE_LATE;
			}			
		} else { //有签到和签退记录
			if ($flexibleWork==0) { //严格按照考勤时间段，计算迟到和早退
				//参考不计入[迟到、早退]的缓冲时长(分钟)
				if ($signinIgnore>0) {
					$signinTime = strtotime("-$signinIgnore minute", $signinTime);
					log_debug("ignore $signinIgnore minute(s) to new signinTime ".date('Y-m-d H:i:s', $signinTime));
				}
				if ($signoutIgnore>0) {
					$signoutTime = strtotime("+$signoutIgnore minute", $signoutTime);
					log_debug("ignore $signoutIgnore minute(s) to new signoutTime ".date('Y-m-d H:i:s', $signoutTime));
				}
				
				if ($signinTime<=$standardSigninTime && $signoutTime>=$standardSignoutTime) { //正常
					$recState = ATTEND_STATE_DEFAULT; //ATTEND_STATE_NORMAL;
				} else {
					if ($signinTime>$standardSigninTime)  //迟到
						$recState |= ATTEND_STATE_LATE;
					if ($signoutTime<$standardSignoutTime) //早退
						$recState |= ATTEND_STATE_LEFT_EARLY;
				}
			} else { //满足工作时长条件，不算为迟到或早退
				$workDuration = (int)$timeRec['work_duration']; //参考工作时长(分钟)
				$realWorkDuration = (int)(($signoutTime-$signinTime)/60); //实际工作时长(分钟)
				log_debug("workDuration=$workDuration, realWorkDuration=$realWorkDuration");
				
				if ($realWorkDuration>=$workDuration) {
					$recState = ATTEND_STATE_DEFAULT; //ATTEND_STATE_NORMAL;
				} else {
					if ($signinTime<=$standardSigninTime && $signoutTime>=$standardSignoutTime) { //正常
						$recState = ATTEND_STATE_DEFAULT; //ATTEND_STATE_NORMAL;
					} else {
						if ($signinTime>$standardSigninTime) { //迟到
							$recState |= ATTEND_STATE_LATE;
						}
						if ($signoutTime<$standardSignoutTime) { //早退
							$recState |= ATTEND_STATE_LEFT_EARLY;
						}
					}
				}
			}
		}
		
		//考虑休假情况
		$results = HolidaySettingService::get_instance()->checkHoliday($entCode, $groupCodes, $userId, $attendDate, $flexibleWork
					, $signinIgnore, $signoutIgnore, $signinTime, $signoutTime, $standardSigninTime, $standardSignoutTime, $recState);
		if ($results===false) {
			log_err('checkHoliday error');
			return false;
		}
		
		//给相应考勤字段赋值
		for ($i=1; $i<=4; $i++) {
			if ($timeRec["att_tim_id$i"]===$timeRec['att_tim_id']) {
				if ($form->{"att_rec_id$i"}===$matchedARec['att_rec_id']) {
					$cancelState = ~(ATTENDANCE_STATE_ABNORMAL_GROUP | ATTEND_STATE_NORMAL); //取消几种受重跑日结影响的状态
					$lastState = ($form->{'att_rec_id'.$i.'_state'} & $cancelState) | $recState;
				} else
					$lastState = $recState;
				
				//检查该考勤记录是否属于请假
				if (!empty($reqTimeRecords)) {
					foreach ($reqTimeRecords as $reqItem) {
						if ($reqItem['att_rec_id']===$matchedARec['att_rec_id']) {
							log_info('rec_id='.$matchedARec['att_rec_id'].' is leave state');
							$lastState |= (ATTEND_STATE_FURLOUGH | ATTEND_STATE_APPROVE_PASS);
							break;
						}
					}
				}
					
				$form->{'att_rec_id'.$i.'_state'} = $lastState;
				$form->{"att_rec_id$i"} = $matchedARec['att_rec_id'];
				break;
			}
		}
	} else { //无记录(旷工/缺席) !!!应该不存在这种情况
		/*
		$recState = ATTEND_STATE_ABSENTEEISM;
		
		//考虑休假情况
		//没有实际考勤记录
		$results = HolidaySettingService::get_instance()->checkHoliday($entCode, $groupCodes, $userId, $attendDate, $flexibleWork
								, $signinIgnore, $signoutIgnore, null, null, $standardSigninTime, $standardSignoutTime, $recState);
		if ($results===false)
			return false;
		
		//给相应字段赋值
		if ($timeRec['att_tim_id1']===$timeRec['att_tim_id']) {
			$form->att_rec_id1 = 0;
			$form->att_rec_id1_state = $recState;
		} else if ($timeRec['att_tim_id2']===$timeRec['att_tim_id']) {
			$form->att_rec_id2 = 0;
			$form->att_rec_id2_state = $recState;
		} else if ($timeRec['att_tim_id3']===$timeRec['att_tim_id']) {
			$form->att_rec_id3 = 0;
			$form->att_rec_id3_state = $recState;
		} else if ($timeRec['att_tim_id4']===$timeRec['att_tim_id']) {
			$form->att_rec_id4 = 0;
			$form->att_rec_id4_state = $recState;
		}*/
	}
	
	return true;
}

/**
 * 获取企业编码列表
 * @param {boolean} $output 是否输出结果到页面
 * @param {int} $limit 单次返回最大记录数 
 * @param {int} $offset 偏移量
 * @return boolean|array false=查询失败，array=查询结果列表
 */
function getEntCodes($output, $limit=100, $offset=0) {
	log_info("getEntCodes limit=$limit, offset=$offset");
	$ents = AttendDailyService::get_instance()->getEnterprises($limit, $offset);
	if ($ents===false) {
		$errMsg = "get getEnterprises error";
		ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return false;
	}
	
	$entCodes = array();
	foreach ($ents as $ent) {
		array_push($entCodes, $ent['ent_id']);
	}
	
	return $entCodes;
}

/**
 * 执行考勤日结作业
 * @param {boolean} $output 是否输出结果到页面
 * @param {int} $taskJobType 任务类型
 * @param {timestamp} $startDateTime 作业开始时间
 * @param {string} $attendDateFieldName 考勤作业日期字段名
 * @param {string} $attendDate  考勤作业日期
 * @param {boolean} $isCalculatedDate 考勤作业日期是否计算而来(非提交参数传入)
 * @param {string} $entCodeFieldName 企业编码字段名
 * @param {array} $entCodes 企业编码列表
 * @param {boolean} $entCodesFromDB 企业代码是否从数据库读取而来(非参数传入)
 * @param {int} $returnSeconds 参考运行时长(秒)
 * @param {boolean} $taskJobForce 是否强制运行(强制将先清除符合条件的已存在记录，慎用)
 * @return boolean|int|string false=执行错误，int=特殊的状态(EB_STATE_CONTINUE)，string=成功执行的结果(JSON字符串)
 */
function executeDailyAttendanceTaskJob($output, $taskJobType, $startDateTime, $attendDateFieldName, $attendDate, $isCalculatedDate
		, $entCodeFieldName, $entCodes, $entCodesFromDB, $returnSeconds, $taskJobForce) {
	log_info("attendDate $attendDate");
	log_info("entCodes：");
	log_info($entCodes);
	
	$daiInstance = AttendDailyService::get_instance();
	
	$entCodeLimit = 3;
	$entCodeOffset = 0;
	if ($entCodesFromDB) {
		log_info("$entCodeFieldName is not in parameters, read them from DB");
		
		$entCodes = getEntCodes($output, $entCodeLimit, $entCodeOffset);
		if ($entCodes===false)
			return false;
	}
	
	$enabled = true; //忽略被禁用的用户
	$max = 2000; //最大限制(重点是为了防止死循环)
	$limit = 100; //单次执行最多用户数
	$total = 0; //本次作业累加处理用户数量
	
	log_info("job setting: job's max=$max, batch's limit=$limit");
	
	//遍历运行每一个企业的日结作业
	while (count($entCodes)>0) {
		$entCode = trim($entCodes[0]);
		array_splice($entCodes, 0 ,1);
		log_info("start to run attendance task job for entCode $entCode");
			
		//每批获取$limit个用户，逐批执行
		do {
			//获取没有作业处理过考勤的用户编号列表
			$users = $daiInstance->get_noprocessed_users($attendDate, $entCode, $enabled, $limit);
			
			if ($users===false || !is_array($users)) {
				$errMsg = "get_noprocessed_users error";
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return false;
			}
				
			$userCount = count($users);
			$total += $userCount;
			log_info("found $userCount user(s) in this batch");
			if ($userCount==0) {
				log_info("entCode $entCode run finish, the total user's count of all enterprise is $total");
				break;
			}
			
			$result = runAttendDailyForUsers($output, $users, $entCode, $attendDate);
			if ($result===false) {
				return false;
			}
			
			$result = runAttendDailyExtensionForUsers($output, $users, $entCode, $attendDate);
			if ($result===false) {
				return false;
			}
			
			//判断"参考运行时长"，是否结束本次运行等待下次调用
			$runningSeconds = time()-$startDateTime;
			if ($returnSeconds>0 && $runningSeconds>$returnSeconds) {
				log_warn("to be continue, runningSeconds $runningSeconds over return_seconds $returnSeconds");
				$subInfo = array('continue'=>true, 'continue_type'=>1, 'last_count'=>$total);
				if (!$isCalculatedDate)
					$subInfo[$attendDateFieldName] = $attendDate;
				if (!$entCodesFromDB)
					$subInfo[$entCodeFieldName] = implode(',', $entCodes);
				
				$json = ResultHandle::customResultToJsonAndOutput(array('msg'=>'to be continue'), 'sub_info', (object)$subInfo, $output, EBStateCode::$EB_STATE_CONTINUE);
				log_debug($json);
				return EBStateCode::$EB_STATE_CONTINUE;
			}
		} while ($total<$max);
		
		//本次作业运行个数超过最大限制
		if ($total>=$max) {
			log_warn("to be continue, lastTotal $total");
			$subInfo = array('continue'=>true, 'continue_type'=>2, 'last_count'=>$total);
			if (!$isCalculatedDate)
				$subInfo[$attendDateFieldName] = $attendDate;
			if (!$entCodesFromDB)
				$subInfo[$entCodeFieldName] = implode(',', $entCodes);
			
			$json = ResultHandle::customResultToJsonAndOutput(array('msg'=>'to be continue'), 'sub_info', (object)$subInfo, $output, EBStateCode::$EB_STATE_CONTINUE);
			log_debug($json);
			return EBStateCode::$EB_STATE_CONTINUE;
		} else {
			//继续读取下一批企业编码
			if (count($entCodes)==0 && $entCodesFromDB) {
				$entCodeOffset += $entCodeLimit;
				if ($entCodeOffset==0) {
					$errMsg = '==================logical error=======================';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return false;
				}
				
				$entCodes = getEntCodes($output, $entCodeLimit, $entCodeOffset);
				if ($entCodes===false)
					return false;
			}
		}
			
		log_info("end running attendance task job for entCode $entCode");
		
		//开始考勤设置生效作业
		log_info("start to run attendance settting job for entCode $entCode");
		$result = runAttendSettingEffective($output, $entCode);
		if ($result===false) {
			return false;
		}
		log_info("end running attendance settting job for entCode $entCode");
	}
	
	//返回成功
	$json = ResultHandle::successToJsonAndOutput(null, null, "task job type $taskJobType, attend_date $attendDate finish", false);
	return $json;
}

/**
 * 提取考勤记录中rec_id和rec_state
 * @param {object|array} $entity 对象或关联数组
 * @param {string} $recId 考勤记录编号
 * @return {array} 结果:[0]=rec_state, [1]=有效的rec_id字段名, [2]=有效的rec_state字段名
 */
function getAttendRecStateAndRecIdFieldName($entity, $recId) {
	if(empty($entity) || empty($recId)) {
		log_err('getRecIdAndState error, entity or recId is empty');
		return;
	}
	
	if (is_object($entity)) {
		for($i=0; $i<5; $i++) {
			if ($entity->{"att_rec_id$i"} === $recId)
				return array($entity->{'att_rec_id'.$i.'_state'}, "att_rec_id$i", 'att_rec_id'.$i.'_state');
		}
	} else if (is_array($entity)) {
		for($i=0; $i<5; $i++) {
			if ($entity["att_rec_id$i"] === $recId) {
				return array($entity['att_rec_id'.$i.'_state'], "att_rec_id$i", 'att_rec_id'.$i.'_state');
			}
		}		
	}
}

/**
 * 获取考勤时间段在考勤规则下的顺序号
 * @param {array|object} $entity 关联数组或对象
 * @param {string} $timId 考勤时间段编号
 * @return {boolean|array} false=获取错误；[0]=顺序号{int}，[1]=该考勤时间段是否即将失效{boolean}，[2]=是否从new字段匹配{boolean}
 */
function getAttendTimeIndex($entity, $timId) {
	if(empty($entity) || empty($timId)) {
		log_err('getAttendTimeIndex error, entity or timId is empty');
		return false;
	}
	
	if (is_object($entity)) {
		for($i=1; $i<=4; $i++) {
			$newValue = $entity->{"att_tim_newid$i"};
			$value = $entity->{"att_tim_id$i"};
			if ($timId===$newValue)
				return array($i, false, true);
			else if($timId===$value)
				return array($i, ($newValue==='0')?false:true, false);
		}		
	} else if (is_array($entity)) {
		for($i=1; $i<=4; $i++) {
			$newValue = $entity["att_tim_newid$i"];
			$value = $entity["att_tim_id$i"];
			if ($timId===$newValue)
				return array($i, false, true);
			else if($timId===$value)
				return array($i, ($newValue==='0')?false:true, false);
		}
	}
	
	return array();
}

/**
 * 获取考勤规则在考勤设置下的顺序号
 * @param {array|object} $entity 关联数组或对象
 * @param {string} $rulId 考勤规则编号
 * @return {boolean|array} false=获取错误；[0]=顺序号{int}，[1]=该考勤规则是否即将失效{boolean}，[2]=是否在new字段匹配{boolean}
 */
function getAttendRuleIndex($entity, $rulId) {
	if(empty($entity) || empty($rulId)) {
		log_err('getAttendRuleIndex error, entity or rulId is empty');
		return false;
	}
	
	if (is_object($entity)) {
		for($i=1; $i<=7; $i++) {
			$newValue = $entity->{"att_rul_newid$i"};
			$value = $entity->{"att_rul_newid$i"};
			if ($rulId===$newValue)
				return array($i, false, true);
			else if($rulId===$value)
				return array($i, ($newValue==='0')?false:true, false);
		}
	} else if (is_array($entity)) {
		for($i=1; $i<=7; $i++) {
			$newValue = $entity["att_rul_newid$i"];
			$value = $entity["att_rul_id$i"];
			if ($rulId===$newValue)
				return array($i, false, true);
			else if($rulId===$value)
				return array($i, ($newValue==='0')?false:true, false);
		}
	}
	
	return array();	
}

/**
 * 拆分考勤状态数组
 * @param {int} $recState
 * @return {array} 拆分后的状态数组
 */
function splitAttendRecState($recState) {
	global $ATTENDANCE_STATE_ARRAY;
	$results = array();
	
// 	if (($recState&ATTEND_STATE_NORMAL)==ATTEND_STATE_NORMAL)
// 		array_push($results, array(ATTEND_STATE_NORMAL, $ATTENDANCE_STATE_ARRAY[ATTEND_STATE_NORMAL]));
	
	if (($recState&ATTEND_STATE_UNSIGNIN)==ATTEND_STATE_UNSIGNIN)
		array_push($results, array(ATTEND_STATE_UNSIGNIN, $ATTENDANCE_STATE_ARRAY[ATTEND_STATE_UNSIGNIN]));
	
	if (($recState&ATTEND_STATE_UNSIGNOUT)==ATTEND_STATE_UNSIGNOUT)
		array_push($results, array(ATTEND_STATE_UNSIGNOUT, $ATTENDANCE_STATE_ARRAY[ATTEND_STATE_UNSIGNOUT]));
	
	if (($recState&ATTEND_STATE_ABSENTEEISM)==ATTEND_STATE_ABSENTEEISM)
		array_push($results, array(ATTEND_STATE_ABSENTEEISM, $ATTENDANCE_STATE_ARRAY[ATTEND_STATE_ABSENTEEISM]));
	
	if (($recState&ATTEND_STATE_LATE)==ATTEND_STATE_LATE)
		array_push($results, array(ATTEND_STATE_LATE, $ATTENDANCE_STATE_ARRAY[ATTEND_STATE_LATE]));
	
	if (($recState&ATTEND_STATE_LEFT_EARLY)==ATTEND_STATE_LEFT_EARLY)
		array_push($results, array(ATTEND_STATE_LEFT_EARLY, $ATTENDANCE_STATE_ARRAY[ATTEND_STATE_LEFT_EARLY]));
	
	if (($recState&ATTEND_STATE_WORK_OVERTIME)==ATTEND_STATE_WORK_OVERTIME)
		array_push($results, array(ATTEND_STATE_WORK_OVERTIME, $ATTENDANCE_STATE_ARRAY[ATTEND_STATE_WORK_OVERTIME]));
	
	if (($recState&ATTEND_STATE_WORK_OUTSIDE)==ATTEND_STATE_WORK_OUTSIDE)
		array_push($results, array(ATTEND_STATE_WORK_OUTSIDE, $ATTENDANCE_STATE_ARRAY[ATTEND_STATE_WORK_OUTSIDE]));
	
	if (($recState&ATTEND_STATE_FURLOUGH)==ATTEND_STATE_FURLOUGH)
		array_push($results, array(ATTEND_STATE_FURLOUGH, $ATTENDANCE_STATE_ARRAY[ATTEND_STATE_FURLOUGH]));
	
	if (($recState&ATTEND_STATE_RESIGN)==ATTEND_STATE_RESIGN)
		array_push($results, array(ATTEND_STATE_RESIGN, $ATTENDANCE_STATE_ARRAY[ATTEND_STATE_RESIGN]));	
		
// 	if (($recState&ATTEND_STATE_APPROVE_PASS)==ATTEND_STATE_APPROVE_PASS)
// 		array_push($results, array(ATTEND_STATE_APPROVE_PASS, $ATTENDANCE_STATE_ARRAY[ATTEND_STATE_APPROVE_PASS]));
		
	return $results;
}

/**
 * 从request读取多组勤审批时间段数据
 * @param {string} $recIds 考勤记录编号(支持多个，逗号分隔)
 * @param {string} $createTime 创建时间
 * @return {array} 考勤审批时间段数据列表，行对象字段：rec_id, req_start_time, req_stop_time
 */
function getAttendReqTimesInRequest($recIds, $createTime) {
	$results = array();
	$recIdArry = preg_split('/[\s,]+/', $recIds, -1, PREG_SPLIT_NO_EMPTY);
	
	//尝试从输入参数读取最多10个rec_id
	$realRecIds = array();
	for ($i=0; $i<10; $i++) {
		$realRecId = get_request_param("rec_id_$i");
		if (isset($realRecId))
			$realRecIds[$i] = $realRecId;
	}
	
	//遍历匹配rec记录并读取签到时间、签退时间
	foreach ($recIdArry as $recId) {
		foreach ($realRecIds as $key=>$realRecId) {
			if ($recId===$realRecId) {
				$result = new stdClass();
				$result->rec_id = $recId;
				
				$reqStartTime = get_request_param("signin_time_$key");
				if (!empty($reqStartTime))
					$result->req_start_time = substr($reqStartTime, 6, 10).' '.substr($reqStartTime, 0, 5).':00';
				$reqEndTime = get_request_param("signout_time_$key");
				if (!empty($reqEndTime))
					$result->req_stop_time = substr($reqEndTime, 6, 10).' '.substr($reqEndTime, 0, 5).':00';
				$reqDuration = get_request_param("req_duration_$key");
				if (!empty($reqDuration))
					$result->req_duration = intval(floatval($reqDuration)*60);
				$result->flag = 0;
				$result->create_time = $createTime;
				
				array_push($results, $result);
			}
		}
	}
	
	return $results;
}

/**
 * 获取考勤审批的关联资料
 * @param {string} $reqId 考勤审批申请编号
 * @param {int} $shareType 关联人员类型
 * @param {array} $entity 审批申请资料(关联数组)
 * @param {array} $reqRecs 关联的考勤记录列表
 */
function getAttendReqAssociatedInfo($reqId, $shareType, &$entity, &$reqRecs) {
	//获取审批人信息
	$json1 = get_shareusers(11, array($reqId), $shareType, null, 1);
	$results1 = get_results_from_json($json1, $tmpObj1);
	if (!empty($results1)) {
		$shares = array($shareType=>array());
		for ($i=0; $i<count($results1); $i++) {
			array_push($shares[$shareType], $results1[$i]);
		}
		$entity['shares'] = $shares;
	}
	
	//获取已保存的考勤记录
	$reqItemInstance = AttendReqItemService::get_instance();
	$results1 = $reqItemInstance->search($reqItemInstance->fieldNames, array('att_req_id'=>$reqId), array('att_req_id'));
	if ($results1!==false)
		$reqRecs = $results1;	
}

/**
 * 依据考勤记录状态得出'需补充的签到签退情况'
 * @param {int} $recState 考勤记录状态
 * @param {boolean} $missPassFlag 是否忽略审批通过标识，默认true
 * @returns {int} 0x1=补充签到，0x2=补充签退，0x1&0x2=两者都补充
 */
function compensatedTimeTypeOfAttendRecState($recState, $missPassFlag=true) {
	//	0x2=未签到
	//	0x4=未签退
	//	0x8=旷工
	//	0x10=迟到
	//	0x20=早退
	//  0x1000=审批通过标识
	
	$result = ATTEND_STATE_DEFAULT;
	if (($recState & ATTEND_STATE_ABSENTEEISM)==ATTEND_STATE_ABSENTEEISM) {
		if ($missPassFlag)
			$result += (0x1|0x2);
	} else {
		if (($recState & ATTEND_STATE_UNSIGNIN)==ATTEND_STATE_UNSIGNIN || ($recState & ATTEND_STATE_LATE)==ATTEND_STATE_LATE) {
			if ($missPassFlag)
				$result += 0x1;
		}
		if (($recState & ATTEND_STATE_UNSIGNOUT)==ATTEND_STATE_UNSIGNOUT || ($recState & ATTEND_STATE_LEFT_EARLY)==ATTEND_STATE_LEFT_EARLY) {
			if ($missPassFlag)
				$result += 0x2;
		}
	}
	return $result;
}

/**
 * 用考勤记录列表生成考勤审批申请子项列表
 * @param {array} $records 考勤记录列表
 * @param {string} $createTime 指定创建时间
 * @param {int|string} $flag 创建标识
 * @return {array} 子项列表
 */
function createAttendReqTimeObjs($records, $createTime, $flag) {
	$reqTimeObjs = array();
	foreach ($records as $record) {
		$reqTimeObj = new stdClass();
		$reqTimeObj->rec_id = $record['att_rec_id'];
		$reqTimeObj->req_start_time = $record['attend_date'].' '.$record['standard_signin_time'];
		$reqTimeObj->req_stop_time = $record['attend_date'].' '.$record['standard_signout_time'];
		$reqTimeObj->req_duration = 0;
		$reqTimeObj->create_time = $createTime;
		$reqTimeObj->flag = $flag;
		array_push($reqTimeObjs, $reqTimeObj);
	}
	return $reqTimeObjs;
}

/**
 * 创建考勤审批子项记录
 * @param {string} $reqId 
 * @param {array} $reqTimeObjs 子项记录对象的数组
 * @param {array} $existReqTimeRecords 已存在的子项记录列表，用于过滤
 * @param {object} $reqItemInstance 记录操作对象
 * @param {int} $insertCount [引用] 新建子项的记录数量
 * @return {boolean}
 */
function createAttendReqItem($reqId, $reqTimeObjs, $existReqTimeRecords, $reqItemInstance, &$insertCount) {
	//待定：需要支持批量插入和事务
	
	$insertCount = 0;
	if (!empty($reqTimeObjs)) {
		//遍历建立每一组时间段记录
		foreach ($reqTimeObjs as $reqTimeObj) {
			//检查是否有已存在的相同的子项记录
			if (!empty($existReqTimeRecords)) {
				$matched = false;
				foreach ($existReqTimeRecords as $existItem) {
					if ($existItem['att_rec_id'] ===  $reqTimeObj->rec_id) {
						log_debug('matched a same attendReqItem record rec_id='.$reqTimeObj->rec_id);
						$matched = true;
						break;
					}
				}
				if ($matched)
					continue;
			}
			
			//写入req_item
			$itemForm = new EBAttendReqItem();
			$itemForm->att_req_id = $reqId;
			$itemForm->att_rec_id = $reqTimeObj->rec_id;
			if (isset($reqTimeObj->req_start_time))
				$itemForm->req_start_time = $reqTimeObj->req_start_time;
			if (isset($reqTimeObj->req_stop_time))
				$itemForm->req_stop_time = $reqTimeObj->req_stop_time;
			if (isset($reqTimeObj->req_duration))
				$itemForm->req_duration = $reqTimeObj->req_duration;
			if (isset($reqTimeObj->create_time))
				$itemForm->create_time = $reqTimeObj->create_time;
			if (isset($reqTimeObj->flag))
				$itemForm->flag = $reqTimeObj->flag;
			
			$params = $itemForm->createFields();
			$checkDigits = $itemForm->createCheckDigits();
			$result = $reqItemInstance->insertOne($params, $checkDigits, $reqItemInstance->primaryKeyName, $errMsg);
			if ($result===false) {
				log_err('create attendReqItem error');
				return false;
			}
			
			$insertCount++;
		}
	}
	
	return true;
}

/**
 * 补偿某个考勤审批申请缺少的关联子项（仅支持请假申请）
 * @param {string} $entCode 企业编号
 * @param {string} $groupCode 部门/群组代码
 * @param {string} $reqId 考勤审批申请编号
 * @param {array} $reqEntity 考勤审批申请信息(关联数组) 
 * @param {string} $createTime 指定创建时间
 * @param {int} $flag 创建标识：0=提交申请时创建，1=执行时创建
 * @param {array} $existReqTimeRecords 已存在的子项记录列表，用于过滤 
 * @param {object} $recordInstance 考勤记录操作对象
 * @param {object} $reqItemInstance 考勤审批申请子项记录操作对象
 * @param {int} $insertCount [引用] 新建子项的记录数量 
 * @return boolean 是否执行成功
 */
function compensateAttendReqItems($entCode, $groupCode, $reqId, $reqEntity, $createTime, $flag, $existReqTimeRecords, $recordInstance, $reqItemInstance, &$newReqItemCount) {
	//查询请假期间的相关考勤记录(旷工也包括在内)
	$records = $recordInstance->getAttendRecord4($entCode, $groupCode, $reqEntity['user_id'], $reqEntity['start_time'], $reqEntity['stop_time']);
	if ($records===false) {
		log_err('getAttendRecord4 error');
		return false;
	}
	
	//补充创建考勤审批申请子项记录
	$reqTimeObjs = createAttendReqTimeObjs($records, $createTime, $flag);
	if (!createAttendReqItem($reqId, $reqTimeObjs, $existReqTimeRecords, $reqItemInstance, $newReqItemCount)) {
		log_err('create createAttendReqItem error');
		return false;
	}
	
	return true;
}

/**
 * 处理审批通过
 * @param {boolean} $output 是否输出结果到页面
 * @param {string} $entCode 企业编号
 * @param {string} $groupCode 部门/群组代码
 * @param {string} $reqId 考勤审批申请编号
 * @param {array} $reqEntity 考勤审批申请信息(关联数组) 
 * @return boolean 是否执行成功
 */
function handleAttendanceReqPass($output, $entCode, $groupCode, $reqId, $reqEntity) {
	$reqItemInstance = AttendReqItemService::get_instance();
	$recordInstance = AttendRecordService::get_instance();
	$adInstance = AttendDailyService::get_instance();
	$now = date('Y-m-d H:i:s', time());
	
	$reqType = intval($reqEntity['req_type']);
	if ($reqType!=4) { //非加班
		//获取考勤审批申请相关的考勤编号
		$limit = 1000;
		$rItemResults = $reqItemInstance->search($reqItemInstance->fieldNames, array('att_req_id'=>$reqId), array('att_req_id'), null, $limit);
		if ($rItemResults===false) {
			$errMsg = 'search attendReqItem error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		
		if ($reqType==3) { //请假
			$compResult = compensateAttendReqItems($entCode, $groupCode, $reqId, $reqEntity, $now, 1, $rItemResults, $recordInstance, $reqItemInstance, $newReqItemCount);
			if ($compResult===false) {
				$errMsg = 'compensateAttendReqItems error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return false;				
			}
			
			//重新获取考勤审批申请子项列表
			if ($newReqItemCount>0) {
				log_info('reload AttendReqItem records, $reqId='.$reqId);
				$rItemResults = $reqItemInstance->search($reqItemInstance->fieldNames, array('att_req_id'=>$reqId), array('att_req_id'), null, $limit);
				if ($rItemResults===false) {
					$errMsg = 'search attendReqItem error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return false;
				}
			}
		}
		
		$recIds = array();
		foreach ($rItemResults as $rItem) {
			array_push($recIds, $rItem['att_rec_id']);
		}
		
		if (!empty($recIds)) {
			//获取关联的考勤记录
			$recResults = $recordInstance->getAttendRecord5($recIds);
			if ($recResults===false) {
				$errMsg = 'getAttendRecord5 error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return false;
			}
	
			//遍历更新考勤记录和考勤日结记录
			foreach ($recResults as $rec) {
				foreach ($rItemResults as $rItem) {
					$recId = $rec['att_rec_id'];
					if ($recId===$rItem['att_rec_id']) {
						$recStateArry = getAttendRecStateAndRecIdFieldName($rec, $recId);
						$recState = intval($recStateArry[0]);
						$recIdFieldName = $recStateArry[2];
						
						//更新考勤记录
						$reqSigninTime = null;
						$reqSignoutTime = null;
						$reqDuration = null;
						$compensatedTimeType = compensatedTimeTypeOfAttendRecState($recState);
						if(($compensatedTimeType&0x1)==0x1) //补充签到时间
							$reqSigninTime = $rItem['req_start_time'];
						if (($compensatedTimeType&0x2)==0x2) //补充签退时间
							$reqSignoutTime = $rItem['req_stop_time'];
						if ($rItem['req_duration']!='0') //申请的工作时长
							$reqDuration = intval($rItem['req_duration']);
						//执行更新
						if (isset($reqSigninTime) || isset($reqSignoutTime) || isset($reqDuration)) {
							$updateResult = $recordInstance->updateReqFields($recId, $reqSigninTime, $reqSignoutTime, $reqDuration);
							if ($updateResult===false) {
								$errMsg = 'updateReqFields error';
								ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
								return false;
							}
						}
						
						//更新考勤日结记录
						//$reverseRecState = ~(ATTEND_STATE_ABSENTEEISM|ATTEND_STATE_UNSIGNIN|ATTEND_STATE_UNSIGNOUT|ATTEND_STATE_LATE|ATTEND_STATE_LEFT_EARLY); //异常状态按位取反
						//$recState &= $reverseRecState;
						$recState |= ATTEND_STATE_APPROVE_PASS; //增加审批通过标识
						if ($reqType==1) //补签
							$recState |= ATTEND_STATE_RESIGN;
						else if ($reqType==2) //外勤
							$recState |= ATTEND_STATE_WORK_OUTSIDE;
						else if ($reqType==3) //请假
							$recState |= ATTEND_STATE_FURLOUGH;
						
						//执行更新
						$sets = array($recIdFieldName=>$recState);
						$wheres = array($adInstance->primaryKeyName=>$rec['att_dai_id']);
						$setCheckDigits = array($recIdFieldName);
						$whereCheckDigits = array($adInstance->primaryKeyName);
						$updateResult = $adInstance->update($sets, $wheres, $setCheckDigits, $whereCheckDigits);
						if ($updateResult===false) {
							$errMsg = 'update attendDaily error';
							ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
							return false;
						}
					}
				}
			}
		}
	} else { //加班申请
		$reqStartTime = $reqEntity['start_time'];
		$reqStopTime = $reqEntity['stop_time'];
		$reqDuration = $reqEntity['req_duration'];
		$attendDate = substr($reqStartTime, 0, 10);
		
		$adResult = $adInstance->getZeroRecordInOneAttendDate(($reqEntity['owner_type']==1?$reqEntity['owner_id']:null), ($reqEntity['owner_type']==2?$reqEntity['owner_id']:null), $reqEntity['user_id'], $attendDate);
		if ($adResult===false) {
			$errMsg = 'getZeroRecordInOneAttendDate error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		
		//判断记录是否已存在
		$otRecExists = false;
		if (count($adResult)>0) {
			$adEntity = $adResult[0];
			if ($adEntity['att_rec_id0']!='0' && !empty($adEntity['att_rec_id']))
				$otRecExists = true;
		}
		
		$recState = ATTEND_STATE_WORK_OVERTIME;
		if ($otRecExists) { //已存在加班考勤日结记录，更新它
			$recId = $adEntity['att_rec_id0'];
			$recState = $recState | intval($adEntity['att_rec_id0_state']);
			//$recState |= ATTEND_STATE_WORK_OVERTIME;
			
			//更新考勤日结记录
			$sets = array('att_rec_id0_state'=>$recState);
			$wheres = array($adInstance->primaryKeyName=>$adEntity['att_dai_id']);
			$setCheckDigits = array('att_rec_id0');
			$whereCheckDigits = array($adInstance->primaryKeyName);
			$updateResult = $adInstance->update($sets, $wheres, $setCheckDigits, $whereCheckDigits);
			if ($updateResult===false) {
				$errMsg = 'update attendDaily error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return false;
			}
			//更新考勤记录
			$sets = array('last_time'=>date('Y-m-d H:i:s'), 'req_signin_time'=>$reqStartTime, 'req_signout_time'=>$reqStopTime, 'req_duration'=>$reqDuration);
			$wheres = array($recordInstance->primaryKeyName=>$recId);
			$setCheckDigits = array('req_duration');
			$whereCheckDigits = array($recordInstance->primaryKeyName);
			$insertResult = $recordInstance->update($sets, $wheres, $setCheckDigits, $whereCheckDigits);
			if ($updateResult===false) {
				$errMsg = 'update attendRecord error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return false;
			}
		} else { //不存在加班考勤记录
			//新建一条考勤记录
			$params = array('owner_type'=>$reqEntity['owner_type'], 'owner_id'=>$reqEntity['owner_id'], 'user_id'=>$reqEntity['user_id'], 'user_name'=>$reqEntity['user_name']
					, 'attend_date'=>$attendDate,'create_time'=>date('Y-m-d H:i:s'), 'req_signin_time'=>$reqStartTime, 'req_signout_time'=>$reqStopTime, 'req_duration'=>$reqDuration, 'data_flag'=>1);
				
			$checkDigits = array('owner_type', 'owner_id', 'user_id', 'req_duration', 'data_flag');
			$insertResult = $recordInstance->insertOne($params, $checkDigits);
			if ($insertResult===false) {
				$errMsg = 'insert attendRecord error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return false;
			}
			$recId = $insertResult;
			
			//考勤日结记录不存在，创建一条
			if (empty($adEntity)) {
				$params = array('owner_type'=>$reqEntity['owner_type'], 'owner_id'=>$reqEntity['owner_id'], 'user_id'=>$reqEntity['user_id'], 'attend_date'=>$attendDate
						,'create_time'=>date('Y-m-d H:i:s'), 'user_name'=>$reqEntity['user_name'], 'att_rec_id0_state'=>$recState, 'att_rec_id0'=>$recId);
				$checkDigits = array('owner_type', 'owner_id', 'user_id', 'att_rec_id0_state', 'att_rec_id0');
				$insertResult = $adInstance->insertOne($params, $checkDigits);
				if ($insertResult===false) {
					$errMsg = 'insert attendDaily error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return false;
				}
			} else { //更新考勤日结记录
				$sets = array('att_rec_id0_state'=>$recState, 'att_rec_id0'=>$recId);
				$wheres = array($adInstance->primaryKeyName=>$adEntity['att_dai_id']);
				$setCheckDigits = array('att_rec_id0_state', 'att_rec_id0');
				$whereCheckDigits = array($adInstance->primaryKeyName);
				$updateResult = $adInstance->update($sets, $wheres, $setCheckDigits, $whereCheckDigits);
				if ($updateResult===false) {
					$errMsg = 'update attendDaily error';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return false;
				}
			}
		}
		
		//创建一个关联关系
		$itemForm = new EBAttendReqItem();
		$itemForm->att_req_id = $reqEntity['att_req_id'];
		$itemForm->att_rec_id = $recId;
		
		$params = $itemForm->createFields();
		$checkDigits = $itemForm->createCheckDigits();
		$result = $reqItemInstance->insertOne($params, $checkDigits, $reqItemInstance->primaryKeyName, $errMsg);
		if ($result===false) {
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
	}
	
	if ($reqType!=3)  {//非请假
		//针对一个用户运行一次指定考勤日期的考勤日结扩展作业
		$users = array(array('user_id'=>$reqEntity['user_id'], 'user_name'=>$reqEntity['user_name']));
		$entCode = $reqEntity['owner_id'];
		if ($reqType==1 || $reqType==2)
			$attendDate = $reqEntity['attend_date'];
		else
			$attendDate = substr($reqEntity['start_time'], 0, 10);
		
		return runAttendDailyExtensionForUsers($output, $users, $entCode, $attendDate);
	} else
		return true;
}

/**
 * 获取考勤管理人员(包括考勤专员和部门经理)的权限情况
 * @param {string} [可选] $entCode 企业编号
 * @param {array} [可选] $groupCodes 群组的编号列表
 * @param {string} $userId 指定的用户编号
 * @param (boolean} $authorityManagement 是否检查管理权限，默认false
 * @param {boolean} $getMemberUid 是否查询成员列表，默认true
 * @return boolean|array|undefined false=查询失败；true=指定的用户是考勤专员；array=指定的用户非考勤专员，以他作为部门经理的部门成员的用户编号列表；undefined(未定义)=普通用户
 */
function getAttendanceManageAuthority($entCode, array $groupCodes, $userId, $authorityManagement=false, $getMemberUid=true) {
	//判断指定用户是否"考勤专员"
	$attManagerResult = UserDefineService::get_instance()->getAttendanceManager($entCode, $groupCodes, null, $userId, $authorityManagement);
	if ($attManagerResult===false) {
		$errMsg = 'getAttendanceManager error';
		$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return false;
	}
	if (count($attManagerResult)>0)
		return true;
	
	if ($getMemberUid) {
		$memberUids = array();
		
		//查询以当前用户为部门经理的部门成员的用户编号列表
		$memberUidResults = UserAccountService::get_instance()->getMemberUidsByManagerUid($entCode, $groupCodes, $userId);
		if ($memberUidResults===false) {
			$errMsg = 'getMemberUidsByManagerUid error';
			$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return false;
		}
		foreach ($memberUidResults as $uidEntity)
			array_push($memberUids, $uidEntity['user_id']);
		
		return $memberUids;
	}
}

/**
 * 考勤规则记录排序
 * @param {array} $a 关联数组记录1
 * @param {array} $b 关联数组记录2
 * @return {int} 比较值
 */
function attendRuleSort($a, $b) {
	if ($a['rul_index']==$b['rul_index']) return 0;
	return (intval($a['rul_index']) < intval($b['rul_index']))?-1:1;
}

/**
 * 考勤时间段记录排序
 * @param {array} $a 关联数组记录1
 * @param {array} $b 关联数组记录2
 * @return {int} 比较值
 */
function attendTimeSort($a, $b) {
	if ($a['tim_index']==$b['tim_index']) return 0;
	return (intval($a['tim_index']) < intval($b['tim_index']))?-1:1;
}
