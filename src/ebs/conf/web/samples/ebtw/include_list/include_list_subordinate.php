<?php
/**
 * 列表-下级的事宜查询
 */
	$tableName2 = 'employee_info_t';
	$tableNameAlias = array($tableName1=>'t_a', $tableName2=>'t_b');
	$conditions = array($userId, $userId);
	
	$employeeInfoFormObj = new EBEmployeeInfoForm();
	$employeeInfoFormObj->setValuesFromRequest();
	$wheres2 = $employeeInfoFormObj->createWhereConditions();
	
	$paramsGroup = array($tableName1=>$wheres, $tableName2=>$wheres2);
	$checkDigitsGroup = array($tableName1=>$checkDigits, $tableName2=>$employeeInfoFormObj->createCheckDigits());
	
	$createUidName = ($fromType==1||$fromType==2)?'t_a.create_uid':'t_a.report_uid';
	
	if (!isset($fieldNames3))
		$fieldNames3 = '';
	if (!isset($leftJoinTableName))
		$leftJoinTableName = '';
	if (!isset($shareUserCondition))
		$shareUserCondition = '';
	
	$prefixSqlOfCount = 'select count(distinct '.$pkFieldName.') as record_count from '.$tableName1.' t_a '.$leftJoinTableName.', '.$tableName2.' t_b, user_account_t t_d where '.$createUidName.' = t_d.user_id'.$open_flag_str.' and (t_a.'.$ownerFieldName.' = t_b.emp_uid'.$shareUserCondition.') and t_b.emp_uid<>? and t_b.group_id in (select group_id from department_info_t where manager_uid=?)';
	
	$totalCount = -1;
	if ($forCount!=1) { //查询列表
		$fieldNames = $instance->insertTableNameAliasPrefix($fieldNames, 't_a.');
		//$fieldNames1 = 't_b.emp_id, t_b.emp_uid, t_b.username as emp_name';
		
		$prefixSql = 'select distinct ' . $fieldNames . ', t_d.account as user_account '.$fieldNames3.' from '.$tableName1.' t_a '.$leftJoinTableName.', '.$tableName2.' t_b, user_account_t t_d where '.$createUidName.' = t_d.user_id'.$open_flag_str.' and( t_a.'.$ownerFieldName.' = t_b.emp_uid'.$shareUserCondition.') and t_b.emp_uid<>? and t_b.group_id in (select group_id from department_info_t where manager_uid=?)';
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