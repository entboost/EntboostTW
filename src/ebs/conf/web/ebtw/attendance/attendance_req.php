<?php
include dirname(__FILE__).'/../attendance/preferences.php';
$ECHO_MODE = 'json'; //输出类型
require_once dirname(__FILE__).'/../attendance/include.php';
require_once dirname(__FILE__).'/../attendance/attendance_functions.php';
	$output = true;
	
	//字段名称定义
	$FIELD_NAME_ACTION_TYPE = 'action_type'; //操作类型
	$FIELD_NAME_ATTEND_DATE = 'attend_date'; //考勤日期
	$FIELD_NAME_REQ_TYPE = 'req_type'; //审批类型
	$FIELD_NAME_START_TIME = 'start_time'; //开始时间，格式如：2017-06-10 10:00:00
	$FIELD_NAME_STOP_TIME = 'stop_time'; //结束时间，格式如：2017-06-10 12:00:00
	$FIELD_NAME_REQ_NAME = 'req_name';
	$FIELD_NAME_REQ_CONTENT = 'req_content'; //申请内容
	$FIELD_NAME_REQ_DURATION = 'req_duration'; //申请时长
	$FIELD_NAME_REC_IDS = 'rec_ids'; //考勤记录编号(支持多个，逗号分隔)
	$FIELD_NAME_REQ_ID = 'req_id'; //审批申请编号
	$FIELD_NAME_ATT_RUL_ID = 'att_rul_id';
	$FIELD_NAME_ATT_TIM_ID = 'att_tim_id';
	$FIELD_NAME_APPROVER_PERSON = 'approver_person'; //审批人
	$FIELD_NAME_OLD_APPROVER_PERSON = 'old_approver_person'; //旧审批人
	
	//检查操作类型条件
	$actionType = get_request_param($FIELD_NAME_ACTION_TYPE);
	if (!isset($actionType) || !in_array($actionType, array(ACTION_TYPE_REQ_TIME_RANGE_OTHER, ACTION_TYPE_REQ_TIME_RANGE
			, ACTION_TYPE_ATTENDANCE_REQ, ACTION_TYPE_ATTENDANCE_REQ_PASS, ACTION_TYPE_ATTENDANCE_REQ_REJECT, ACTION_TYPE_ATTENDANCE_REQ_REVOKE))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_ACTION_TYPE, $output);
		return;
	}
	
	$userId = $_SESSION[USER_ID_NAME];
	$userName = $_SESSION[USER_NAME_NAME];
	$entCode = $_SESSION[USER_ENTERPRISE_CODE];
	$groupCodes = array(); //待定：补充查找当前登录用户所属的部门列表
	$fromType = 11; //考勤审批，share_user来源类型
	$shareType = 6; //审批人，share_user共享类型
	$now = date('Y-m-d H:i:s', time()); //当前日期时间字符串
	
	if ($actionType==ACTION_TYPE_REQ_TIME_RANGE_OTHER || $actionType==ACTION_TYPE_REQ_TIME_RANGE) { //考勤时间段查询
		//检查日期条件
		$attendDate = get_request_param($FIELD_NAME_ATTEND_DATE);
		if (empty($attendDate)) {
			ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($attendDate, $output);
			return;
		}
		
		$forUserId = $userId;
		//如果申请非当前用户所提，则获取申请人的用户编号
		if ($actionType==ACTION_TYPE_REQ_TIME_RANGE_OTHER) {
			$reqId = get_request_param($FIELD_NAME_REQ_ID);
			if (!empty($reqId)) {
				$reqInstance = AttendReqService::get_instance();
				$result = $reqInstance->search($reqInstance->fieldNames, array($reqInstance->primaryKeyName=>new SQLParam($reqId)), array($reqInstance->primaryKeyName, null, 1));
				if ($result===false) {
					$errMsg = "get attendReq error";
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				if (count($result)==0) {
					$errMsg = "can not found the attendReq";
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				
				$req = $result[0];
				$forUserId = $req['user_id'];
			} else {
				ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('req_id', $output);
				return;
			}
		}
		
		$ruleIdsAndTimeIds = array();
		
		//获取考勤记录
		$records = AttendRecordService::get_instance()->getAttendRecord3($entCode, null, $forUserId, $attendDate, $ruleIdsAndTimeIds);
		if (is_array($records)) {
			foreach ($records as &$mrecord) {
				//解析审批状态名称
				$arry = getAttendRecStateAndRecIdFieldName($mrecord, $mrecord['att_rec_id']);
				if (!empty($arry)) {
					$recState = intval($arry[0]);
					$mrecord['rec_state'] = $recState;
					$recStateDic = splitAttendRecState($recState);
					$mrecord['rec_state_arry'] = $recStateDic;
				}
			}
		}
		
		ResultHandle::listedResultToJsonAndOutput($records, $output);
	} else if ($actionType == ACTION_TYPE_ATTENDANCE_REQ) { //提交考勤审批
		//log_debug($_REQUEST);
		$errMsg = null;
		
		//验证审批类型
		$reqType = get_request_param($FIELD_NAME_REQ_TYPE);
		if (!isset($reqType) || !in_array($reqType, array(1, 2, 3, 4))) {
			ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($FIELD_NAME_REQ_TYPE, $output);
			return;
		}
		//获取其它通用输入参数
		$attendDate = get_request_param($FIELD_NAME_ATTEND_DATE);
		$startTime = get_request_param($FIELD_NAME_START_TIME);
		$stopTime = get_request_param($FIELD_NAME_STOP_TIME);
		$reqContent = get_request_param($FIELD_NAME_REQ_CONTENT);
		$recIds = get_request_param($FIELD_NAME_REC_IDS);
		$reqId = get_request_param($FIELD_NAME_REQ_ID);
		$approverPerson = get_request_param($FIELD_NAME_APPROVER_PERSON);
		$approverOldPerson = get_request_param($FIELD_NAME_OLD_APPROVER_PERSON);
		
		$reqItemInstance = AttendReqItemService::get_instance();
		$reqInstance = AttendReqService::get_instance();
		$recordInstance = AttendRecordService::get_instance();
		
		if ($reqType==1 || $reqType==2) { //补签、外勤
			//验证考勤日期
			if (empty($attendDate)) {
				ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($attendDate, $output);
				return;
			}
			//考勤时间段必填
			if (empty($recIds)) {
				ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($FIELD_NAME_REC_IDS, $output);
				return;
			}
		}
		
		if ($reqType==3 || $reqType==4) { //请假、加班
			//验证时段输入参数
			if (empty($startTime) || empty($stopTime)) {
				ResultHandle::fieldValidNotEmptyErrToJsonAndOutput("$FIELD_NAME_START_TIME or $FIELD_NAME_STOP_TIME", $output);
				return;
			}
			
			if ($reqType==3) {
				//请假类型
				$reqName = get_request_param($FIELD_NAME_REQ_NAME);
				if (empty($reqName)) {
					ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($FIELD_NAME_REQ_NAME, $output);
					return;
				}
			}
			
			if ($reqType==4) {
				//控制在同一天
				if (substr($startTime, 0, 10)!=substr($stopTime, 0, 10)) {
					$errMsg = "$FIELD_NAME_START_TIME and $FIELD_NAME_STOP_TIME must in a same day";
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				
				//加班时长
				$reqDuration = get_request_param($FIELD_NAME_REQ_DURATION);
				if (empty($reqDuration) || !EBModelBase::checkDigit($reqDuration, $errMsg, $FIELD_NAME_REQ_DURATION)) {
					if (empty($reqDuration))
						$errMsg = "$FIELD_NAME_REQ_DURATION is empty";
						ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
						return;
				}
			}
		}
		
		//验证其它通用输入参数
		if (empty($reqContent)) {
			ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($FIELD_NAME_REQ_CONTENT, $output);
			return;
		}
		if (!empty($recIds) && !EBModelBase::checkDigits($recIds, $errMsg, $FIELD_NAME_REC_IDS)) {
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		if (!empty($reqId) && !EBModelBase::checkDigit($reqId, $errMsg, $FIELD_NAME_REQ_ID)) {
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		if (empty($approverPerson) || !EBModelBase::checkDigits($approverPerson, $errMsg, $FIELD_NAME_APPROVER_PERSON)) {
			if (empty($approverPerson))
				$errMsg = "$FIELD_NAME_APPROVER_PERSON is empty";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		if (!empty($approverOldPerson) && !EBModelBase::checkDigits($approverOldPerson, $errMsg, $FIELD_NAME_OLD_APPROVER_PERSON)) {
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		if (!empty($reqId)) {
			$results = $reqInstance->search($reqInstance->fieldNames, array($reqInstance->primaryKeyName=>$reqId));
			if ($results===false) {
				$errMsg = 'search attendReq error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			if (count($results)>0) {
				$reqEntity = $results[0];
				if ($reqType!=$reqEntity['req_type']) {
					$errMsg = 'not allow change req_type';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				
				//验证申请状态
				if ($reqEntity['req_status']!=3 && $reqEntity['req_status']!=4) {
					$errMsg = 'req status is not matched';
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
			}
		}
			
		//建立审批主记录
		if (!isset($reqEntity)) {
			$reqForm = new EBAttendReq();
			$reqForm->owner_type = 1;
			$reqForm->owner_id = $entCode;
			$reqForm->user_id = $userId;
			$reqForm->user_name = $userName;
			$reqForm->create_time = date('Y-m-d H:i:s');
			$reqForm->req_type = $reqType;
			$reqForm->req_status = 1;
			if ($reqType==1 || $reqType==2)
				$reqForm->attend_date = $attendDate;
			$reqForm->req_content = $reqContent;
			if ($reqType==3 || $reqType==4) {
				$reqForm->start_time = $startTime;
				$reqForm->stop_time = $stopTime;
				if ($reqType==3)
					$reqForm->req_name = $reqName; //请假类型
				if ($reqType==4) {
					$reqForm->req_duration = intval(floatval($reqDuration)*60); //加班时长
				}
			}
			
			$reqParams = $reqForm->createFields();
			$reqCheckDigits = $reqForm->createCheckDigits();
			
			$reqId = $reqInstance->insertOne($reqParams, $reqCheckDigits, $reqInstance->primaryKeyName, $errMsg);
			if ($reqId===false) {
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
		} else {
			$sets = array('last_time'=>date('Y-m-d H:i:s'), 'req_status'=>1, 'req_content'=>$reqContent);
			if ($reqType==1 || $reqType==2) //补签和外勤
				$sets['attend_date'] = $attendDate;
			if ($reqType==3 || $reqType==4) { //请假和加班
				$sets['start_time'] = $startTime;
				$sets['stop_time'] = $stopTime;
				if ($reqType==3)
					$sets['req_name'] = $reqName; //请假类型
				if ($reqType==4)
					$sets['req_duration'] = intval(floatval($reqDuration)*60); //加班时长
			}
			$setCheckDigits = array('req_status, req_duration');
			$wheres = array($reqInstance->primaryKeyName=>$reqId);
			
			$result = $reqInstance->update($sets, $wheres, $setCheckDigits);
			if ($result===false) {
				$errMsg = 'update attendReq error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
		}
		
		//删除旧时间段记录
		if (isset($reqEntity)) {
			$params = array('att_req_id'=>$reqId);
			$checkDigits = array('att_req_id');
			$result = $reqItemInstance->delete($params, $checkDigits);
			if ($result===false) {
				$errMsg = 'delete attendReqItem error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
		}
		
		$reqTimeObjs = null;
		if ($reqType==1 || $reqType==2) { //补签、外勤
			//从request读取多组考勤审批时间段数据
			$reqTimeObjs = getAttendReqTimesInRequest($recIds, $now);
		} else if ($reqType==3) { //请假
			$records = $recordInstance->getAttendRecord4($entCode, null, $userId, $startTime, $stopTime);
			if ($records===false) {
				$errMsg = 'getAttendRecord4 error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			
			$reqTimeObjs = createAttendReqTimeObjs($records, $now, 0);
		} else if ($reqType==4) { //加班
			//忽略关联考勤记录
		}
		
		if (!empty($reqTimeObjs)) {
			//创建考勤审批申请子项
			if (!createAttendReqItem($reqId, $reqTimeObjs, null, $reqItemInstance, $newReqItemCount)) {
				log_err('create createAttendReqItem error');
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			log_debug("create $newReqItemCount attendReqItem records, reqId=$reqId");
		}
		
		//获取关联审批人的属性
		$json1 = get_useraccounts(array($approverPerson));
		$results = get_results_from_json($json1, $outObj);
		if (empty($results)) {
			$errMsg = 'get_useraccounts error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		$approverUser = $results[0];
		
		//创建或更新关联审批人
		$readFlag = ($approverPerson===$userId)?1:0;
		if (!isset($reqEntity)) {
			//创建关联审批人
			$json2 = create_shareuser($fromType, $reqId, $shareType, $approverUser->user_id, $approverUser->username, $readFlag);
			$result = json_decode($json2, true);
			if ($result['code']!=0) {
				$errMsg = 'create_shareuser error';
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
		} else {
			//更新旧评审人关系(设置valid_flag=0[表示无效])
			$oldPersons = array_values(array_unique(preg_split('/[\s,]+/', $approverOldPerson, -1, PREG_SPLIT_NO_EMPTY)));
			foreach ($oldPersons as $shareUid) {
				if ($reqEntity['req_status']==4) //如果是撤销的审批，删除有效的旧评审人关系
					delete_shareuser($fromType, $reqId, $shareType, $shareUid, NULL, 1);
				else //更新旧评审人关系为无效
					update_shareuser_field(2, 0, $fromType, $reqId, $shareType, NULL, NULL, 1);
			}
			//重新创建关联审批人
			create_shareuser($fromType, $reqId, $shareType, $approverUser->user_id, $approverUser->username, $readFlag);
			//更新关联审批人
			//updatePtrAssociatePerson($fromType, $reqId, null, $shareType, '', $approverPerson, array(), array($approverUser->user_id=>$approverUser->username), $userId);
		}
		
		//操作日志： 20：提交审批（op_data=审批人ID，op_name=审批人名称）
		$fromName = '';
		create_operaterecord($reqId, $fromName, $fromType, 20, $approverUser->user_id, $approverUser->username, '');
		
		ResultHandle::successToJsonAndOutput('success', array('id'=>$reqId), null, $output);
	} else if (in_array($actionType, array(ACTION_TYPE_ATTENDANCE_REQ_PASS, ACTION_TYPE_ATTENDANCE_REQ_REJECT, ACTION_TYPE_ATTENDANCE_REQ_REVOKE))) {
		//获取审批申请编号
		$reqId = get_request_param($FIELD_NAME_REQ_ID);
		if (!EBModelBase::checkDigit($reqId, $errMsg, $FIELD_NAME_REQ_ID)) {
			ResultHandle::fieldValidNotDigitErrToJsonAndOutput($FIELD_NAME_REQ_ID, $output);
			return;
		}
		
		//查询审批申请记录
		$reqInstance = AttendReqService::get_instance();
		$result = $reqInstance->getAttendReqAndShareUser($entCode, array(), $reqId, 1);
		if ($result===false) {
			$errMsg = "getAttendReqAndShareUser error";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		if (count($result)==0) {
			$errMsg = "can not found the attendReq";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		$reqEntity = $result[0];
		
		//验证申请状态
		if ($reqEntity['req_status']!=1) {
			$errMsg = 'req status is not matched';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		//按不同操作类型分别处理
		$newReqStatus;
		switch (intval($actionType)) {
			case ACTION_TYPE_ATTENDANCE_REQ_PASS: //审批通过
			case ACTION_TYPE_ATTENDANCE_REQ_REJECT: //审批不通过
				//验证权限
				if ($reqEntity['share_uid']!=$userId) {
					ResultHandle::noAuthErrToJsonAndOutput($output);
					return;
				}
				
				if ($actionType==ACTION_TYPE_ATTENDANCE_REQ_PASS)
					$newReqStatus = 2;
				else
					$newReqStatus = 3;
				
				break;
			case ACTION_TYPE_ATTENDANCE_REQ_REVOKE: //审批撤销
				//验证权限
				if ($reqEntity['user_id']!=$userId) {
					ResultHandle::noAuthErrToJsonAndOutput($output);
					return;
				}
				
				$newReqStatus = 4;
				break;
		}
		
		//更新审批状态
		$sets = array('last_time'=>date('Y-m-d H:i:s'), 'req_status'=>$newReqStatus);
		$setCheckDigits = array('req_status');
		$wheres = array($reqInstance->primaryKeyName=>$reqId);
		$whereCheckDigits = array($reqInstance->primaryKeyName);
		
		$result = $reqInstance->update($sets, $wheres, $setCheckDigits, $whereCheckDigits);
		if ($result===false) {
			$errMsg = 'update attendReq error';
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		//更新审批人处理状态
		if ($actionType==ACTION_TYPE_ATTENDANCE_REQ_PASS || $actionType==ACTION_TYPE_ATTENDANCE_REQ_REJECT)
			update_shareuser_field(3, $newReqStatus, $fromType, $reqId, $shareType, NULL, NULL, 1);
		
		//更新相关异常考勤记录和考勤日结记录
		if ($actionType==ACTION_TYPE_ATTENDANCE_REQ_PASS) {
			if (!handleAttendanceReqPass($output, $entCode, null, $reqId, $reqEntity))
				return;
		}
		
		$fromName = '';
		$remark = '';
		if ($actionType==ACTION_TYPE_ATTENDANCE_REQ_PASS) {
			//操作日志： 22：审批通过（remark=备注）
			$json1 = create_operaterecord($reqId, $fromName, $fromType, 22, null, null, $remark);
		} else if ($actionType==ACTION_TYPE_ATTENDANCE_REQ_REJECT) {
			//操作日志： 23：审批拒绝（remark=备注）
			$json1 = create_operaterecord($reqId, $fromName, $fromType, 23, null, null, $remark);			
		} else if ($actionType==ACTION_TYPE_ATTENDANCE_REQ_REVOKE) {
			//操作日志： 24：撤销申请
			$json1 = create_operaterecord($reqId, $fromName, $fromType, 24);
		}
		
		ResultHandle::successToJsonAndOutput('success', array('id'=>$reqId), null, $output);
	}
	
	