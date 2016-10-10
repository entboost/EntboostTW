<?php
/**
 * 列表-与用户资料关联查询
 */
	$tableName2 = 'user_account_t';
	$tableNameAlias = array($tableName1=>'t_a', $tableName2=>'t_b');
	
	$userAccountFormObj = new EBUserAccountForm();
	$userAccountFormObj->setValuesFromRequest();
	if (empty($wheres2))
		$wheres2 = $userAccountFormObj->createWhereConditions();
	else 
		$wheres2 = array_merge($userAccountFormObj->createWhereConditions(), $wheres2);
	
	$paramsGroup = array($tableName1=>$wheres, $tableName2=>$wheres2);
	$checkDigitsGroup = array($tableName1=>$checkDigits, $tableName2=>$userAccountFormObj->createCheckDigits());
	
	$prefixSqlOfCount = 'select count(1) as record_count from '.$tableName1.' t_a, '.$tableName2.' t_b where '.$joinCondition;
	
	$totalCount = -1;
	if ($forCount!=1) { //查询列表
		$fieldNames = $instance->insertTableNameAliasPrefix($fieldNames, 't_a.');
		
		$fieldNames1 = 't_b.account as user_account';
		$fieldNames = (!empty($fieldNamesExtend)?($fieldNamesExtend.', '):'') . $fieldNames . ', ' . $fieldNames1;
		
		$prefixSql = 'select ' . $fieldNames . ' from '.$tableName1.' t_a, '.$tableName2.' t_b where '.$joinCondition;
		if (empty($orderby))
			$orderby = $formObj->getOrderby();
		
		//查询总数量
		$result = $instance->joinSearchForCount($tableNameAlias, $prefixSqlOfCount, $conditions, $paramsGroup, $checkDigitsGroup, $whereType, $outErrMsg);
		ResultHandle::countedResultToJsonAndOutput($result, false, $outErrMsg, $totalCount);
		$formObj->setRecordCount($totalCount); //保存总记录数
		
		//分页查询列表
		$result = $instance->joinSearch($tableNameAlias, $prefixSql, $conditions, $paramsGroup, $checkDigitsGroup, $orderby, $formObj->getPerPage(), ($formObj->getCurrentPage()-1)*$formObj->getPerPage(), $whereType, $outErrMsg);
		$json = ResultHandle::listedResultToJsonAndOutput($result, $output, $outErrMsg, $totalCount, $formObj);
	} else { //查询数量
		//执行查询
		$result = $instance->joinSearchForCount($tableNameAlias, $prefixSqlOfCount, $conditions, $paramsGroup, $checkDigitsGroup, $whereType);
		$json = ResultHandle::countedResultToJsonAndOutput($result, $output, $outErrMsg, $totalCount);
	}