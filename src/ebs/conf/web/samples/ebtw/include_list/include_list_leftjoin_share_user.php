<?php
/**
 * 列表-与关联用户进行左连接查询
 */
	$tableName2 = 'eb_share_user_t';
	$tableNameAlias = array($tableName1=>'t_a', $tableName2=>'t_b');
	$conditions = array(/*$userId*/);
	
	$shareUserFormObj = new EBShareUserForm();
	$shareUserFormObj->setValuesFromRequest();
	if (empty($wheres2))
		$wheres2 = $shareUserFormObj->createWhereConditions();
	else 
		$wheres2 = array_merge($shareUserFormObj->createWhereConditions(), $wheres2);
	
	$paramsGroup = array($tableName1=>$wheres, $tableName2=>$wheres2);
	$checkDigitsGroup = array($tableName1=>$checkDigits, $tableName2=>$shareUserFormObj->createCheckDigits());
	
	$prefixSqlOfCount = 'select count(1) as record_count from '.$tableName1.' t_a left join '.$tableName2.' t_b on t_a.'.$instance->primaryKeyName.'=t_b.from_id where '.'t_b.share_type='.$shareType.' and t_b.from_type = '.$fromType;
	
	$totalCount = -1;
	if ($forCount!=1) { //查询列表
		$fieldNames = $instance->insertTableNameAliasPrefix($fieldNames, 't_a.');
		
		$fieldNames1 = 't_b.share_id as su_share_id, t_b.from_id as su_from_id, t_b.from_type as su_from_type, t_b.share_uid as su_share_uid, t_b.share_name, t_b.share_name as su_share_name, t_b.share_type as su_share_type, t_b.create_time as su_create_time'
				.', t_b.valid_flag as su_valid_flag, t_b.read_flag as su_read_flag, t_b.read_time as su_read_time, t_b.result_status as su_result_status, t_b.result_time as su_result_time';
		
		$fieldNames = $fieldNames . ', ' . $fieldNames1;
		
		$prefixSql = 'select ' . $fieldNames . ' from '.$tableName1.' t_a left join '.$tableName2.' t_b on t_a.'.$instance->primaryKeyName.'=t_b.from_id where '.'t_b.share_type='.$shareType.' and t_b.from_type = '.$fromType;
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