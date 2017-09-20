<?php
require_once dirname(__FILE__).'/../operaterecord/include.php';

	//定义请求参数中主键的变量名称
	$pkFieldName = 'op_id';
	
	//验证必要条件
	$pid = get_request_param($pkFieldName);
	if (empty($pid)) {
		$json = ResultHandle::missedPrimaryKeyErrToJsonAndOutput($pkFieldName);
		return;
	}
	$wheres = array($pkFieldName=>new SQLParam($pid));
	$checkDigits = array($pkFieldName); //数字校验条件
	
	$instance = OperateRecordService::get_instance();
	
	//验证对本记录是否有操作权限
	$userId = $_SESSION[USER_ID_NAME];
	$qWheres = array_merge($wheres, array(/*'user_id'=>$userId*/));  //待定：判断是否有删除权限
	if (!DataAuthority::isRowExists($existRows, 'op_id', $qWheres, $checkDigits, $instance))
		return;
	
	//设定更新值
	$params = array('is_deleted'=>1);
	
	//执行更新
	$result = $instance->update($params, $wheres);
	$json = ResultHandle::updatedResultToJsonAndOutput($result);