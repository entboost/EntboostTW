<?php
require_once dirname(__FILE__).'/../classification/include.php';
	
	//定义请求参数中主键的变量名称
	$pkFieldName = 'class_id';
	
	//验证必要条件
	$pid = get_request_param($pkFieldName);
	if (empty($pid)) {
		ResultHandle::missedPrimaryKeyErrToJsonAndOutput($pkFieldName);
		return;
	}
	$wheres = array($pkFieldName=>new SQLParam($pid));
	$checkDigits = array($pkFieldName); //数字校验条件
	
	$instance = ClassificationService::get_instance();
	
	//验证对本记录是否有操作权限
	$userId = $_SESSION[USER_ID_NAME];
	$qWheres = array_merge($wheres, array('user_id'=>$userId));
	array_push($checkDigits, 'user_id');
	if (!DataAuthority::isRowExists($existRows, 'class_id, class_type', $qWheres, $checkDigits, $instance))
		return;
	
	$classType = $existRows[0]['class_type'];
	if ($classType==1) { //计划
		$planSets = array('class_id'=>0);
		$planWheres = array('create_uid'=>$userId, 'class_id'=>$pid);
		$result = PlanService::get_instance()->update($planSets, $planWheres);
		$json = ResultHandle::updatedResultToJsonAndOutput($result, false);
		$tmpObj = json_decode($json);
		if ($tmpObj->code!=0) {
			log_info('update plan class_id error, class_id:'.$pid);
			echo $json;
			return;
		}
	} else if ($classType==2) { //任务
		
	} else if ($classType==3) { //报告
		
	} else {
		ResultHandle::errorToJsonAndOutput('class_type is not matched', 'class_id:'.$pid.', class_type is not matched');
		return;
	}
	
	$result = $instance->delete($qWheres, $checkDigits);
	ResultHandle::deletedResultToJsonAndOutput($result);