<?php 
$ECHO_MODE = 'json'; //输出类型
require_once dirname(__FILE__).'/include.php';
	$output = true;
	
	//定义参数字段名
	$FIELD_NAME_REC_STATE = 'rec_state';
	$FIELD_NAME_ATTEND_DATE_START = 'attend_date_start'; //考勤日期-开始
	$FIELD_NAME_ATTEND_DATE_END = 'attend_date_end'; //考勤日期-结束
	$FIELD_NAME_EXCLUDE_NORMAL_REC_STATE = 'exclude_normal_rec_state'; //是否过滤通过审批的考勤记录
	$FIELD_NAME_IGNORE_ZERO_TIMID = 'ignore_zero_timid'; //是否过滤没有绑定考勤规则的考勤记录
	$FIELD_NAME_REQ_TYPE = 'req_type'; //考勤审批申请类型
	
	//考勤状态参数
	$recState = get_request_param($FIELD_NAME_REC_STATE);
	if (isset($recState)) { 
		if (!EBModelBase::checkDigit($recState, $errMsg)) {
			ResultHandle::fieldValidNotDigitErrToJsonAndOutput($FIELD_NAME_REC_STATE, $output);
			return;
		}
		$recState = intval($recState);
	}
	//考勤审批申请类型
	$reqType = get_request_param($FIELD_NAME_REQ_TYPE);
	if (isset($reqType)) {
		if (!EBModelBase::checkDigit($reqType, $errMsg)) {
			ResultHandle::fieldValidNotDigitErrToJsonAndOutput($FIELD_NAME_REQ_TYPE, $output);
			return;
		}
	}
	
	//考勤日期范围参数
	//$yesterday = date('Y-m-d', strtotime("-1 day")); //昨天日期
	$attendDateStart = get_request_param($FIELD_NAME_ATTEND_DATE_START);//, $yesterday);
	$attendDateEnd = get_request_param($FIELD_NAME_ATTEND_DATE_END);//, $yesterday);
	if (isset($attendDateStart) && !validateDateString($attendDateStart)) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_ATTEND_DATE_START, $output);
		return;
	}
	if (isset($attendDateEnd) && !validateDateString($attendDateEnd)) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_ATTEND_DATE_END, $output);
		return;
	}
	
	$forCount = get_request_param(REQUEST_QUERY_TYPE);
	$excludeNormalRecState = get_request_param($FIELD_NAME_EXCLUDE_NORMAL_REC_STATE);
	$ignoreZeroTimidRecord = get_request_param($FIELD_NAME_IGNORE_ZERO_TIMID);
	
	if (isset($ignoreZeroTimidRecord)) {
		if ($ignoreZeroTimidRecord==1)
			$ignoreZeroTimidRecord =true;
		else 
			$ignoreZeroTimidRecord = false;
	}
	
	//允许主动获取没有绑定考勤规则的疑似'加班'考勤记录
	$initiativeZeroTimidRecord = null;
	if ($recState===ATTEND_STATE_WORK_OVERTIME && $ignoreZeroTimidRecord!==true) {
		$initiativeZeroTimidRecord = true;
	}
	
	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
	$allowedActions = array(0, 1, 6, 7);
	
	log_debug("attendDateStart=$attendDateStart, attendDateEnd=$attendDateEnd, recState=$recState");
	//查询考勤记录
	$result = AttendDailyService::get_instance()->getRecords($userId, ($forCount==1?true:false), ($excludeNormalRecState==1?true:false)
			, $ignoreZeroTimidRecord, $initiativeZeroTimidRecord, $recState, $attendDateStart, $attendDateEnd);
	if ($result===false) {
		$errMsg = 'getRecords error';
		ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return;
	}
	
	if ($forCount!=1) {
		$totalCount = count($result);
		
		if ($totalCount>0) {
			$recIds = array();
			foreach ($result as $recEntity) {
				array_push($recIds, $recEntity['att_rec_id']);
			}
				
			//查询关联的考勤审批申请
			$reqResult = AttendReqService::get_instance()->getAttendReqsByRecId($recIds, null, $reqType);
			if ($reqResult===false) {
				$errMsg = 'getAttendReqsByRecId error';
				$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			
			//遍历分析每个记录
			foreach ($result as &$mentity) {
				$mentity['allowedActions'] = $allowedActions;
				$mentity['start_time'] = $mentity['attend_date'].' '.$mentity['standard_signin_time'];
				$mentity['stop_time'] = $mentity['attend_date'].' '.$mentity['standard_signout_time'];
				
				for ($i=0; $i<5; $i++) {
					if($mentity['att_rec_id']===$mentity["att_rec_id$i"])
						$mentity['rec_state'] = $mentity['att_rec_id'.$i.'_state'];
				}
				
				//匹配考勤记录的审批申请状态
				if (count($reqResult)>0) {
					foreach ($reqResult as $reqEntity) {
						if ($mentity['att_rec_id']==$reqEntity['att_rec_id']) {
							$mentity['att_req_id'] = $reqEntity['att_req_id'];
							$mentity['req_state'] = $reqEntity['req_status'];
							$mentity['req_type'] = $reqEntity['req_type'];
							break;
						}
					}
				}
			}
		}
		
		ResultHandle::listedResultToJsonAndOutput($result, $output, null, $totalCount);
	} else {
		$totalCount = -1;
		$json = ResultHandle::countedResultToJsonAndOutput($result, false, $errMsg, $totalCount);
		if ($output)
			echo $json;
	}
?>