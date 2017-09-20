<?php
require_once dirname(__FILE__).'/../operaterecord/include.php';
	
	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);
	//获取输入值
	if (!isset($formObj)) {
		$formObj = new EBOperateRecordForm();
		$formObj->setValuesFromRequest();
	}
	
	$checkDigits = $formObj->createCheckDigits();
	//$params = $formObj->createFields(); //仅支持部分字段的有限更新
	$params = array();
	$wheres = array();
	
	//定义请求参数中主键的变量名称
	$pkFieldName = 'pk_op_id';
	
	//验证必要条件：pk_op_id和（from_id, from_type, origin_op_data）两组条件不可以同时缺失
	$pid = get_request_param($pkFieldName);
	if (empty($pid)) {
		$originOpData = get_request_param('origin_op_data');
		$originOpType = $formObj->op_type;
		if (!$formObj->validNotEmpty('from_id, from_type', $outErrMsg) || (!isset($originOpData) && !isset($originOpType))) {
			$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('pk_op_id and (from_id from_type (origin_op_data or op_type))', $output);
			return;
		} else if ((isset($originOpData) && !EBModelBase::checkDigit($originOpData, $outErrMsg, 'origin_op_data')) || (isset($originOpData) && !EBModelBase::checkDigit($originOpType, $outErrMsg, 'op_type'))) {
			$json = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return;
		} else if (trim($originOpData)=='0') {
			$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('origin_op_data', $output);
			return ;
		}
	}
	//更新操作的业务限制
	if (!in_array($formObj->op_type, array('1', '3', '31', '32','50','52','53','60'))) { //待定：以后改进为通过op_id查询数据库获取op_type后再判断
		$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('op_type', $output);
		return;
	}
	//31 上报进度，修改工作内容
	//32 上报工时，修改工作内容
	if (in_array($formObj->op_type, array('31', '32'))) {
		if (!$formObj->validNotEmpty('remark', $outErrMsg)) {
			$json = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
			return;
		}
	}
	//1 添加附件，清空资源编号
	if (in_array($formObj->op_type, array('1'))) {
		if (!$formObj->validNotEmpty('op_data', $outErrMsg)) {
			$json = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
			return;
		}
	}
	// 3 评论/回复，修改备注或清空资源编号、标题(from_name)
	if (in_array($formObj->op_type, array('3'))) {
		if (!$formObj->validNotEmpty('remark', $outErrMsg) && !$formObj->validNotEmpty('op_data', $outErrMsg) && empty($formObj->from_name)) {
			$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('remark and op_data', $output);
			return;
		}
	}
	
	if (!empty($pid)) {
		$wheres[$pkFieldName] = new SQLParam($pid, 'op_id');
		array_push($checkDigits, $pkFieldName);//追加数字校验条件
	}
	if ($formObj->validNotEmpty('from_id, from_type', $outErrMsg)) {
		$wheres['from_id'] = $formObj->from_id;
		$wheres['from_type'] = $formObj->from_type;
	}
	if (isset($originOpData)) {
		$wheres['origin_op_data'] = new SQLParam($originOpData, 'op_data');
		array_push($checkDigits, 'origin_op_data');//追加数字校验条件
	}
	if (isset($originOpType)) {
		unset($params['op_type']);
		$wheres['origin_op_type'] = new SQLParam($originOpType, 'op_type');
		array_push($checkDigits, 'origin_op_type');//追加数字校验条件
	}
	
	
	$instance = OperateRecordService::get_instance();
	
	//验证对本记录是否有操作权限
	$userId = $_SESSION[USER_ID_NAME];
	$qWheres = array_merge($wheres, array(/*'user_id'=>$userId*/)); //待定：修改权限
	if (!DataAuthority::isRowExists($existRows, 'op_id, modify_count', $qWheres, $checkDigits, $instance, 1, SQLParamComb_TYPE_AND, $output, $outErrMsg, $json))
		return;
	
	//设定更新值
	//$formObj->removeKeepFields($params); //仅支持部分字段的有限更新
	if ($formObj->validNotEmpty('from_name', $outErrMsg))
		$params['from_name'] = $formObj->from_name;
	if ($formObj->validNotEmpty('remark', $outErrMsg))
		$params['remark'] = $formObj->remark;
	if ($formObj->validNotEmpty('op_data', $outErrMsg))
		$params['op_data'] = $formObj->op_data;	
	if ($formObj->validNotEmpty('from_name', $outErrMsg))
		$params['from_name'] = $formObj->from_name;	
	if ($formObj->validNotEmpty('op_data', $outErrMsg))
		$params['op_data'] = $formObj->op_data;
// 	log_info('==========');
// 	log_info($formObj);
	if ($formObj->validNotEmpty('op_name', $outErrMsg))
		$params['op_name'] = $formObj->op_name;		
			
	$params['last_modify_time'] = date(DATE_TIME_FORMAT);
	$params['modify_count'] = $existRows[0]['modify_count'] + 1;
	
	//执行更新
	$result = $instance->update($params, $wheres);
	$json = ResultHandle::updatedResultToJsonAndOutput($result, $output);
	
	