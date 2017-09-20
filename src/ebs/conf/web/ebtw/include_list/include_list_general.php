<?php
/**
 * 列表-普通事宜查询
 */
	$totalCount = -1;
	if ($forCount==1) { //只查询获取数量
		$result = $instance->searchForCount($wheres, $checkDigits, $whereType, $outErrMsg);
		$json = ResultHandle::countedResultToJsonAndOutput($result, $output, $outErrMsg, $totalCount);
	} else { //查询获取记录列表
		if (empty($orderby))
			$orderby = $formObj->getOrderby();//get_request_param(REQUEST_ORDER_BY);
		
		$result = $instance->searchForCount($wheres, $checkDigits, $whereType, $outErrMsg);
		ResultHandle::countedResultToJsonAndOutput($result, false, $outErrMsg, $totalCount);
		$json = $formObj->setRecordCount($totalCount); //保存总记录数
		
		$result = $instance->search($fieldNames, $wheres, $checkDigits, $orderby, $formObj->getPerPage(), ($formObj->getCurrentPage()-1)*$formObj->getPerPage(), $whereType, $outErrMsg);
		$json = ResultHandle::listedResultToJsonAndOutput($result, $output, $outErrMsg, $totalCount, $formObj);
	}