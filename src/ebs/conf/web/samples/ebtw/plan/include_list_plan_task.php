<?php
/**
 * 列表-关联计划查询
 */
	$tableName2 = 'eb_task_info_t';
	$tableNameAlias = array($tableName1=>'t_a', $tableName2=>'t_b');
	$conditions = array();//array($formObj->{$taskIdName});
	
	$taskFormObj = new EBTaskForm();
	$taskFormObj->setValuesFromRequest();
	$wheres2 = $taskFormObj->createWhereConditions();
	
	$paramsGroup = array($tableName1=>$wheres, $tableName2=>$wheres2);
	$checkDigitsGroup = array($tableName1=>$checkDigits, $tableName2=>$taskFormObj->createCheckDigits());
	
	$prefixSqlOfCount = 'select count(distinct '.$pkFieldName.') as record_count from '.$tableName1.' t_a, '.$tableName2.' t_b, user_account_t t_c where t_b.create_uid = t_c.user_id and t_a.plan_id = t_b.from_id and t_b.from_type=1';
	
	$totalCount = -1;
	if ($forCount!=1) { //查询列表
		$fieldNames = $instance->insertTableNameAliasPrefix($fieldNames, 't_a.');
			
		$fieldNames1 = 't_b.task_id, t_b.task_name';
		//$fieldNames1 = $instance1->insertTableNameAliasPrefix($fieldNames1, 't_b.');
	
		//$fieldNames = $fieldNames . ', ' . $fieldNames1;
			
		$prefixSql = 'select distinct ' . $fieldNames . ', t_c.account as user_account from '.$tableName1.' t_a, '.$tableName2.' t_b, user_account_t t_c where t_b.create_uid = t_c.user_id and t_a.plan_id = t_b.from_id and t_b.from_type=1';
		if (empty($orderby))
			$orderby = $formObj->getOrderby();//get_request_param(REQUEST_ORDER_BY);
			
		//查询总数量
		$result = $instance->joinSearchForCount($tableNameAlias, $prefixSqlOfCount, $conditions, $paramsGroup, $checkDigitsGroup, $whereType, $outErrMsg);
		$json = ResultHandle::countedResultToJsonAndOutput($result, false, $outErrMsg, $totalCount);
		$formObj->setRecordCount($totalCount); //保存总记录数
		
		//分页查询列表
		$result = $instance->joinSearch($tableNameAlias, $prefixSql, $conditions, $paramsGroup, $checkDigitsGroup, $orderby, $formObj->getPerPage(), ($formObj->getCurrentPage()-1)*$formObj->getPerPage(), $whereType, $outErrMsg);
		$json = ResultHandle::listedResultToJsonAndOutput($result, $output, $outErrMsg, $totalCount, $formObj);
		
		//查询创建人资料
// 		$json =
// 		$fieldName = 'create_uid';
// 		$targetFieldName = 'create_username';
// 		include dirname(__FILE__).'/query_useraccount.php';
		
		//输出查询结果
// 		echo $json;
	} else { //查询数量
		//执行查询
		$result = $instance->joinSearchForCount($tableNameAlias, $prefixSqlOfCount, $conditions, $paramsGroup, $checkDigitsGroup, $whereType);
		$json = ResultHandle::countedResultToJsonAndOutput($result, $output, $outErrMsg, $totalCount);
	}