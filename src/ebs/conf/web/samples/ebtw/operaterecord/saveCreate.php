<?php
require_once dirname(__FILE__).'/../operaterecord/include.php';

	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);

	if (!isset($oprFormObj)) {
		$oprFormObj = new EBOperateRecordForm();
		$oprFormObj->setValuesFromRequest();
	}
	
	//验证必填字段
	if (!$oprFormObj->validNotEmpty('from_id, from_type, op_type', $outErrMsg)) {
		$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput($outErrMsg, $output);
		return;
	}
	
	//验证from_type
	if (!in_array($oprFormObj->from_type, array('1', '2', '3'))) {
		$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('from_type', $output);
		return;
	}
	
	//验证op_type
	if (!$oprFormObj->validOpType($json, $output)) {
		return;
	}
	
	//查询计划、任务、报告是否存在
	$InstanceDict = array(1=>'PlanService', 2=>'TaskService', 3=>'ReportService');
	$FormTypeDict = array(1=>array('plan_id', 'plan_name'), 2=>array('task_id', 'task_name'), 3=>array('report_id'));
	$fromType = (int)$oprFormObj->from_type;
	$fromId = $oprFormObj->from_id;
	$opType = $oprFormObj->op_type;
	$dict = $FormTypeDict[$fromType];
	$qWheres = array($dict[0]=>$oprFormObj->from_id);
	$qCheckDigits = array($dict[0]);
	$instance = $InstanceDict[$fromType]::get_instance();
	if (!DataAuthority::isRowExists($existRows, implode($dict, ','), $qWheres, $qCheckDigits, $instance, 1, SQLParamComb_TYPE_AND, $output, $outErrMsg, $json)) {
		return;
	}
	
	//创建者
	$userId = $_SESSION[USER_ID_NAME];
	$userName = $_SESSION[USER_NAME_NAME];
	
	$checkDigits = $oprFormObj->createCheckDigits();
	
	$params = $oprFormObj->createFields();
	$oprFormObj->removeKeepFields($params);
	$params['user_id'] = $userId;
	$params['user_name'] = $userName;
	//名称
	if (count($dict)>1)
		$params['from_name'] = $existRows[0][$dict[1]];
	
	$params['create_time'] = isset($now)?$now:date(DATE_TIME_FORMAT);
	
	$result = OperateRecordService::get_instance()->insertOne($params, $checkDigits, NULL, $outErrMsg);
	$json = ResultHandle::createdResultToJsonAndOutput($result, $output, $outErrMsg);
	
	//=============发送提醒消息========================
	
	//3 评论/回复
	//10 指派负责人
	//11 添加参与人
	//13 添加共享人
	//20 提交评审/评阅
	//21 评审/评阅已阅
	//22 评审通过/评阅回复
	//23 评审拒绝
	//24 评审撤销
	//30 负责人已阅
	//31 上报进度
	//32 上报工时
	//33 标为中止
	//34 标为完成
	//35 参与人已阅
	if (in_array($opType, array(3,10,11,13,20,21,22,23,24,30,31,32,33,34,35))) {
		if (!empty($json)) {
			get_results_from_json($json, $tmpObj);
			if ($tmpObj->code==0) {
				$opId = $tmpObj->id;
				$ptr = get_onePtr_before_sendBCMsg($fromType, $fromId);
				$opData = $oprFormObj->op_data;
				$extend = NULL;
				$custom = 'tab_type=';
				$validFlag  = 1;
				$target;
				$fromName;
				
				log_info("before sendBcMsg opType=$opType, fromType=$fromType");
				
				if (!empty($ptr)) {
					switch ($fromType) {
						case 1: //计划  3,13,20,21,22,23,24
							$fromName = $ptr->plan_name;
							switch ($opType) {
								case 3: //计划创建人
									$custom.='2';
									$target = $ptr->create_uid;
									break;
								case 13: //新计划共享人[待定?仅新增加的人?]
									$target = $oprFormObj->op_data;
									break;
								case 20: //评审人
									$custom.='1';
									$target = $oprFormObj->op_data;
									break;
								case 21: //计划评审上报人
									$custom.='1';
									$target = $ptr->create_uid;
									break;
								case 22: //计划评审上报人
									$custom.='1';
									$target = $ptr->create_uid;
									break;
								case 23: //计划评审上报人
									$custom.='1';
									$target = $ptr->create_uid;
									break;
								case 24: //原评审人
									$custom.='1';
									$shareType = 1;
									$shares = get_shareusers_in_json(get_shareusers($fromType, array($fromId), $shareType, NULL, NULL), $shareType);
									if (!empty($shares)) {
										//遍历寻找最后的评审人
										$share = array_shift($shares);
										foreach ($shares as $s) {
											if (strcmp($s->create_time, $share->create_time)>0)
												$share = $s;
										}
										$shares = array($share);
									}
									break;
							}
							break; //任务 3,10,11,13,30,31,32,33,34,35
						case 2:
							$fromName = $ptr->task_name;
							switch ($opType) {
								case 3: //任务创建人、负责人
									$custom.='3';
									$target = $ptr->create_uid;
									$shareType = 5;
									$shares = get_shareusers_in_json(get_shareusers($fromType, array($fromId), $shareType, NULL, 1), $shareType);
									break;
								case 10: //新任务负责人
									$custom.='11';
									$target = $oprFormObj->op_data;
									break;
								case 11: //新任务参与人
									$target = $oprFormObj->op_data;
									break;
								case 13: //新任务共享人[待定?仅新增加的人?]
									$target = $oprFormObj->op_data;
									break;
								case 30: //任务创建人
									$target = $ptr->create_uid;
									break;
								case 31: //任务创建人、负责人、参与人、共享人，关注人
									$custom.='1';
									$target = $ptr->create_uid;
									$shares = get_shareusers_in_json(get_shareusers($fromType, array($fromId), 0, NULL, 1));
									break;
								case 32: //任务创建人、负责人
									$custom.='2';
									$target = $ptr->create_uid;
									$shareType = 5;
									$shares = get_shareusers_in_json(get_shareusers($fromType, array($fromId), $shareType, NULL, 1), $shareType);
									$opData = round(((int)$opData/60)*100)/100;
									$extend = round(((int)$ptr->work_time/60)*100)/100;
									break;
								case 33: //任务创建人、负责人、参与人、共享人，关注人
								case 34:
									$custom.='11';
									$target = $ptr->create_uid;
									$shares = get_shareusers_in_json(get_shareusers($fromType, array($fromId), 0, NULL, 1));
									break;
								case 35: //任务创建人，负责人
									$target = $ptr->create_uid;
									$shareType = 5;
									$shares = get_shareusers_in_json(get_shareusers($fromType, array($fromId), $shareType, NULL, 1), $shareType);
									break;
							}
							break;
						case 3: //日报 3,20,21,22
							$fromName = date('Y年m月d日', strtotime($ptr->start_time)).' 日报';
							switch ($opType) {
								case 3: //报告创建人
									$custom.='2';
									$target = $ptr->report_uid;
									break;
								case 20: //评阅人
									$custom.='1';
									$target = $oprFormObj->op_data;
									break;
								case 21: //日报填写人
									$custom.='1';
									$target = $ptr->report_uid;
									break;
								case 22: //日报填写人
									$custom.='1';
									$target = $ptr->report_uid;
									break;
								break;
							}
							break;
					}
					
					//过滤，不通知当前用户
					if (!empty($target) && $target==$userId)
						unset($target);
					
					if (!empty($shares)) {
						if (empty($target)) {
							$target = array();
						} else
							$target = array($target);
						
						foreach ($shares as $share) {
							if ($share->share_uid!=$userId)
								array_push($target, $share->share_uid);
						}
						$target = array_values(array_unique($target));
					}
					
					//执行发送提醒
					if (!empty($target)) {
						sendBCMsg_after_operaterecord($target, $opId, $opType, $userId, $userName, $fromType, $fromId, $fromName, $opData, $oprFormObj->op_name, $oprFormObj->remark, $oprFormObj->op_time, $extend, ($custom=='tab_type=')?NULL:$custom);
					}
				} else {
					log_err("can not execute sendBCMsg_after_operaterecord, ptr is not found opType=$opType, fromType=$fromType, fromId = $fromId");
				}
			}
		}
	}
	