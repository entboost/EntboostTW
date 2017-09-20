<?php
include dirname(__FILE__).'/../attendance/preferences.php';
//$ECHO_MODE = 'json'; //输出类型
require_once dirname(__FILE__).'/../attendance/include.php';
require_once dirname(__FILE__).'/../attendance/attendance_functions.php';
	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);

	if (empty($formObj)) {
		$formObj = new EBAttendReqForm();
		$formObj->setValuesFromRequest();
	}
	
	//字段名称定义
	//$FIELD_NAME_QUERY_TYPE = REQUEST_QUERY_TYPE; //操作类型
	
	//验证必填字段
	$queryType = $formObj->{REQUEST_QUERY_TYPE};
	if (!in_array($queryType, array(2, 3, 4, 6, 7, 8))) {
		$errMsg = REQUEST_QUERY_TYPE.' is not matched';
		$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return;
	}
	//验证字段合法性
	if (!$formObj->validFormFields($json, $output)) {
		return;
	}

	$wheres = $formObj->createWhereConditions();
	
	$finalOutput = $output;
	//$fromType = $PTRType;
	$forCount = $formObj->{REQUEST_FOR_COUNT};
	$userId = $_SESSION[USER_ID_NAME];
	$entCode = $_SESSION[USER_ENTERPRISE_CODE]; //企业编号
	$isEntManager = $_SESSION[IS_ENTERPRISE_MANAGER]; //是否企业管理者
	
	$reqInstance = AttendReqService::get_instance();
	$recInstance = AttendRecordService::get_instance();
	$adInstance = AttendDailyService::get_instance();
// 	$finalOutput = $output;
// 	$output = false;
	
	$groupCodes = array();
	if ($queryType==2 || $queryType==3) { //2=我的申请，3=考勤审批
		$results = $reqInstance->getAttendReqList($entCode, $groupCodes, $queryType==2?$userId:null, $queryType==3?$userId:null, $formObj, !empty($forCount)?true:false);
		if ($results===false) {
			$errMsg = 'getAttendReqList error';
			$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		//总记录数量
		$countResult = $results[0];
		if ($countResult===false) {
			$errMsg = 'getAttendReqList error [0]';
			$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		$totalCount = -1;
		$json = ResultHandle::countedResultToJsonAndOutput($countResult, false, $errMsg, $totalCount);
		$formObj->setRecordCount($totalCount);
		
		if ($forCount!=1) {
			//记录列表
			$reqResult = $results[1];
			if ($reqResult===false) {
				$errMsg = 'getAttendReqList error [1]';
				$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			
			//拼接req_id查询条件
			$reqIds = array();
			foreach ($reqResult as $entity) {
				array_push($reqIds, $entity['att_req_id']);
			}
			$reqIds = array_unique($reqIds);
			
			if (!empty($reqIds)) {
				//查询与考勤审批关联的考勤记录
				$recResult = $reqInstance->getAttendRecordsByReqId($reqIds);
				if ($recResult===false) {
					$errMsg = 'getAttendRecordsByReqId error';
					$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				
				//遍历封装关联的考勤记录
				foreach ($reqResult as &$mentity) {
					$recs = array();
					foreach ($recResult as &$mrec) {
						if (!array_key_exists('rec_state', $mrec)) {
							$recStateAry = getAttendRecStateAndRecIdFieldName($mrec, $mrec['att_rec_id']);
							$mrec['rec_state'] = $recStateAry[0];
						}
						//匹配相同的考勤审批记录
						if ($mentity['att_req_id']===$mrec['att_req_id']) {
							//解析考勤状态及名称
							$recState = intval($mrec['rec_state']);
							$recStateDic = splitAttendRecState($recState);
							
							unset($mrec['rec_state_name']); //清理内容，否则可能重复
							foreach ($recStateDic as $subDic) {
								if (array_key_exists('rec_state_name', $mrec))
									$mrec['rec_state_name'] .= ('、'.$subDic[1]);
								else 
									$mrec['rec_state_name'] = $subDic[1];
							}
							
							array_push($recs, $mrec);
						}
					}
					$mentity['recs'] = $recs;
				}
			}
			
			$json = ResultHandle::listedResultToJsonAndOutput($reqResult, false, $errMsg, $totalCount, $formObj);
			
			//获取(评审人、共享人等)资料
			$validFlag = 1;
			$fromType = 11;
			$shareType = 6;
			$json = completing_list_shareusers('list', $fromType, $json, NULL, 0, NULL, $validFlag, $isQuery);
			//$json = completing_list_shareusers('list', $PTRType, $json, NULL, $shareType, $userId, 1, $isQuery);
			//整理数据操作权限
			//$json = DataAuthority::reorganizeAllowedActions($PTRType, $shareType, $json, $userId);
			$json = DataAuthority::reorganizeAllowedActions($PTRType, $shareType, $json, $userId);
		}
	} else if ($queryType==4) { //4=考勤异常
		$formObj->abnormal_rec_state = 1;
		$memberUids = array();
		$isAttendanceManager = false; //是否考勤专员
		
		if (!$isEntManager) {
			//获取考勤管理人员(包括考勤专员和部门经理)的权限情况
			$authorityResult = getAttendanceManageAuthority($entCode, $groupCodes, $userId);
			if ($authorityResult===false) {
				$errMsg = 'getAttendanceManageAuthority error';
				$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			if ($authorityResult===true)
				$isAttendanceManager = true;
			else 
				$memberUids = $authorityResult;
		}
		
		//获取考勤记录与审批申请记录
		$results = $reqInstance->getAttendRecordsLJoinReq($entCode, $groupCodes, $isEntManager?null:$userId, null, $isAttendanceManager, $memberUids, $formObj, !empty($forCount)?true:false);
		//$results = $reqInstance->getAttendRecordsLJoinReq($entCode, $groupCodes, null, null, $isAttendanceManager, $memberUids, $formObj, !empty($forCount)?true:false);
		if ($results===false) {
			$errMsg = 'getAttendRecordsLJoinReq error';
			$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		//总记录数量
		$countResult = $results[0];
		if ($countResult===false) {
			$errMsg = 'getAttendRecordsLJoinReq error [0]';
			$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		$totalCount = -1;
		$json = ResultHandle::countedResultToJsonAndOutput($countResult, false, $errMsg, $totalCount);
		$formObj->setRecordCount($totalCount);
		
		if ($forCount!=1) {
			//记录列表
			$recResult = $results[1];
			if ($recResult===false) {
				$errMsg = 'getAttendRecordsLJoinReq error [1]';
				$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			
			//考勤状态
			foreach ($recResult as &$mentity) {
				$recStateAry = getAttendRecStateAndRecIdFieldName($mentity, $mentity['att_rec_id']);
				$mentity['rec_state'] = $recStateAry[0];
				
				//解析考勤状态及名称
				$recState = intval($mentity['rec_state']);
				$recStateDic = splitAttendRecState($recState);
				$mentity['rec_state_name'] = '';
				foreach ($recStateDic as $subDic) {
					if (empty($subDic[1]))
						continue;
					if (!empty($mentity['rec_state_name']))
						$mentity['rec_state_name'] .= ('、'.$subDic[1]);
					else
						$mentity['rec_state_name'] = $subDic[1];
				}
			}
			
			$json = ResultHandle::listedResultToJsonAndOutput($recResult, false, $errMsg, $totalCount, $formObj);
			
			//获取(评审人、共享人等)资料
			$validFlag = 1;
			$fromType = 11;
			$shareType = 6;
			$json = completing_list_shareusers('list', $fromType, $json, NULL, 0, NULL, $validFlag, $isQuery);
			//整理数据操作权限
			$json = DataAuthority::reorganizeAllowedActions($PTRType, $shareType, $json, $userId);
		}
	} else if (in_array($queryType, array(6,7,8))) {
		$memberUids = array();
		$groupIds = array();
		$isAttendanceManager = false; //是否考勤专员
		
		if (!$isEntManager) {
			//获取考勤管理人员(包括考勤专员和部门经理)的权限情况
			$authorityResult = getAttendanceManageAuthority($entCode, $groupCodes, $userId, false, false);
			if ($authorityResult===false) {
				$errMsg = 'getAttendanceManageAuthority error';
				$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			if ($authorityResult===true)
				$isAttendanceManager = true;
			else {
				//$memberUids = $authorityResult;
				//获取当前用户作为部门经理的部门列表
				$groupResults = DepartmentInfoService::get_instance()->getGroupInfos($entCode, 0, $userId);
				if ($groupResults===false) {
					$errMsg = 'getGroupInfos error';
					$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				foreach ($groupResults as $group)
					array_push($groupIds, $group['group_id']);
			}
		}
		
		if ($queryType==6) { //工作时长
			$results = $recInstance->getAttendRecord0($entCode, null, $isEntManager?null:$userId, $isAttendanceManager, $groupIds, $memberUids, $formObj);
			if ($results===false) {
				$errMsg = 'getAttendRecord0 error';
				$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			
			//总记录数量
			$countResult = $results[0];
			if ($countResult===false) {
				$errMsg = 'getAttendRecord0 error [0]';
				$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			$totalCount = -1;
			$json = ResultHandle::countedResultToJsonAndOutput($countResult, false, $errMsg, $totalCount);
			$formObj->setRecordCount($totalCount);
			
			if ($forCount!=1) {
				//记录列表
				$recResult = $results[1];
				if ($recResult===false) {
					$errMsg = 'getAttendRecord0 error [1]';
					$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				
				if (count($recResult)>0) {
					$recIds = array();
					foreach ($recResult as $recEntity) {
						array_push($recIds, $recEntity['att_rec_id']);
					}
					
					//查询关联的考勤审批申请
					$reqResult = $reqInstance->getAttendReqsByRecId($recIds, 2);
					if ($reqResult===false) {
						$errMsg = 'getAttendReqsByRecId error';
						$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
						return;
					}
					
					//匹配对应记录
					if (count($reqResult)>0) {
						foreach ($recResult as &$mrecEntity) {
							foreach ($reqResult as $reqEntity) {
								if ($mrecEntity['att_rec_id']==$reqEntity['att_rec_id']) {
									$mrecEntity['att_req_id']=$reqEntity['att_req_id'];
									$mrecEntity['req_type'] = $reqEntity['req_type'];
									$mrecEntity['req_status'] = $reqEntity['req_status'];
								}
							}
						}
					}
				}
				$json = ResultHandle::listedResultToJsonAndOutput($recResult, false, $errMsg, $totalCount, $formObj);
			}
		} else if ($queryType==7 || $queryType==8) { //考勤汇总、考勤报表
			if ($queryType==7)
				$results = $adInstance->collectedRecords($entCode, $isEntManager?null:$userId, $isAttendanceManager, $groupIds, $memberUids, $formObj);
			else 
				$results = $adInstance->collectedRecords2($entCode, $isEntManager?null:$userId, $isAttendanceManager, $groupIds, $memberUids, $formObj);
			log_info($results);
			if ($results===false) {
				$errMsg = 'collectedRecords error';
				$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			
			//总记录数量
			$countResult = $results[0];
			if ($countResult===false) {
				$errMsg = 'collectedRecords error [0]';
				$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			$totalCount = -1;
			$json = ResultHandle::countedResultToJsonAndOutput($countResult, false, $errMsg, $totalCount);
			$formObj->setRecordCount($totalCount);
			
			if ($forCount!=1) {
				//记录列表
				$recResult = $results[1];
				if ($recResult===false) {
					$errMsg = 'collectedRecords error [1]';
					$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
				
				//把查询条件-搜索时间段封装到标准时间
				$searchTimeS = $formObj->search_time_s;
				$searchTimeE = $formObj->search_time_e;
				if (isset($searchTimeS) || isset($searchTimeE)) {
					$extDatas = array();
					$extDatas['search_time_s'] = $searchTimeS;
					$extDatas['search_time_e'] = $searchTimeE;
				}
				
				$json = ResultHandle::listedResultToJsonAndOutput($recResult, false, $errMsg, $totalCount, $formObj, !empty($extDatas)?$extDatas:null);
			}
		}
	}
	
	if ($finalOutput)
		echo $json;
