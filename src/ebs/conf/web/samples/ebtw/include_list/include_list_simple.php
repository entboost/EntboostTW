<?php
/**
 * 列表-简单SQL语句查询方式
 */
	$totalCount = -1;
	if ($forCount==1) { //只查询获取数量
		$result = $instance->simpleSearchForCount($sqlOfCount, $conditions, NULL, NULL, SQLParamComb_TYPE_AND, $outErrMsg);
		$json = ResultHandle::countedResultToJsonAndOutput($result, $output, $outErrMsg, $totalCount);
	} else { //查询获取记录列表
// 		if (empty($orderby))
// 			$orderby = $formObj->getOrderby();//get_request_param(REQUEST_ORDER_BY);
		
		$result = $instance->simpleSearchForCount($sqlOfCount, $conditions, NULL, NULL, SQLParamComb_TYPE_AND, $outErrMsg);
		ResultHandle::countedResultToJsonAndOutput($result, false, $outErrMsg, $totalCount);
		$json = $formObj->setRecordCount($totalCount); //保存总记录数
		
		$result = $instance->simpleSearch($sql, $conditions, NULL, NULL, NULL, isset($pageSize)?$pageSize:$formObj->getPerPage(), ($formObj->getCurrentPage()-1)*$formObj->getPerPage(), SQLParamComb_TYPE_AND, $outErrMsg);
		$json = ResultHandle::listedResultToJsonAndOutput($result, $output, $outErrMsg, $totalCount, $formObj);
	}