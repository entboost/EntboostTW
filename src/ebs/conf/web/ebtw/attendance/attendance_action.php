<?php
include dirname(__FILE__).'/../attendance/preferences.php';
require_once dirname(__FILE__).'/../attendance/include.php';
require_once dirname(__FILE__).'/../attendance/attendance_functions.php';
	$output = true;

	$Action_Type_Name = 'action_type';
	$actionType = get_request_param($Action_Type_Name);
	if (!isset($actionType) || !in_array($actionType, array(ACTION_TYPE_SIGN_QUERY, ACTION_TYPE_SIGN_IN, ACTION_TYPE_SIGN_OUT))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($Action_Type_Name, $output);
		return;
	}
	
	$userId = $_SESSION[USER_ID_NAME];
	$userName = $_SESSION[USER_NAME_NAME];
	$entCode = $_SESSION[USER_ENTERPRISE_CODE];
	$groupCodes = array(); //待定：补充查找当前登录用户所属的部门列表
	$now = time();
	
	if ($actionType==ACTION_TYPE_SIGN_QUERY) { //查询应采取的操作："签到"或"签退"
		$attendSignIn = decideSignInOutAction($now, false, $userId, $groupCodes, $entCode);
		ResultHandle::customResultToJsonAndOutput(array('signActionType'=>$attendSignIn), null, null, $output);
	} else { //执行签到、签退
		log_info("attendance userId = $userId, userName = $userName, actionType = $actionType");
		$signCheckMiddleTime = false;
		
		$times = findActionRuleTimes($now, $output, $userId, $groupCodes, $entCode);
		if ($times===false) //发生了错误
			return;
		
		$isHoliday = isHoliday($now, $userId, $groupCodes, $entCode);
		
		if (count($times)>0) {
			//没到达第一个考勤时间段，不允许签退
			if ($actionType==ACTION_TYPE_SIGN_OUT) {
				$firstTimeRec = $times[0];
				$signinTime = strtotime(date('Y-m-d', $now).' '.$firstTimeRec['signin_time']);
				if ($now<$signinTime) {
					$errMsg = 'not arrive attendance signin time';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output, EBStateCode::$EB_STATE_DISABLE_SIGN_OUT);					
					return;
				}
			}
			
			//尝试分析出一个适用考勤时间段
			$controlAction = $isHoliday?false:true;
			$matchedTimeRec = analyzeAttendTimesWithActionType($now, $output, $times, $actionType, $signCheckMiddleTime, $controlAction);
			if ($isHoliday) {
				if ($matchedTimeRec!==false && empty($matchedTimeRec)) {
					log_info("no attend time after analyze, it's holiday for userId=$userId");
					$matchedTimeRec = generateHolidayAttendRuleTimeRec();
				}
			}
		} else {
			log_info("no attend time, it's holiday for userId=$userId");
			$matchedTimeRec = generateHolidayAttendRuleTimeRec();
		}
		
		if ($matchedTimeRec!==false) {
			log_debug($matchedTimeRec);
			//查询应采取的操作："签到"或"签退"
			executeSignInOutAction($now, $output, $actionType, $matchedTimeRec, $userId, $userName, null, $entCode);
		}
	}