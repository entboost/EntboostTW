<?php
/**
 * 列表-评审人/评阅人、参与人、共享人、关注人的事宜查询
 */
	$tableName2 = 'eb_share_user_t';
	$tableNameAlias = array($tableName1=>'t_a', $tableName2=>'t_b');
	$conditions = array($userId);
	
	$shareUserFormObj = new EBShareUserForm();
	$shareUserFormObj->setValuesFromRequest();
	if (empty($wheres2))
		$wheres2 = $shareUserFormObj->createWhereConditions();
	else 
		$wheres2 = array_merge($shareUserFormObj->createWhereConditions(), $wheres2);
	
	$paramsGroup = array($tableName1=>$wheres, $tableName2=>$wheres2);
	$checkDigitsGroup = array($tableName1=>$checkDigits, $tableName2=>$shareUserFormObj->createCheckDigits());
	
	$createUidName = ($fromType==1||$fromType==2)?'t_a.create_uid':'t_a.report_uid';
		
	$prefixSqlOfCount = 'select count(1) as record_count from '.$tableName1.' t_a, '.$tableName2.' t_b, user_account_t t_d where '.$createUidName.' = t_d.user_id and t_a.'.$instance->primaryKeyName.'=t_b.from_id and t_b.share_type='.$shareType.' and t_b.from_type = '.$fromType.' and t_b.share_uid = ?';
	
	$totalCount = -1;
	if ($forCount!=1) { //查询列表
		$fieldNames = $instance->insertTableNameAliasPrefix($fieldNames, 't_a.');
		
		$fieldNames1 = 't_b.share_id as su_share_id, t_b.from_id as su_from_id, t_b.from_type as su_from_type, t_b.share_uid as su_share_uid, t_b.share_name as su_share_name, t_b.share_type as su_share_type, t_b.create_time as su_create_time'
				.', t_b.valid_flag as su_valid_flag, t_b.read_flag as su_read_flag, t_b.read_time as su_read_time, t_b.result_status as su_result_status, t_b.result_time as su_result_time';
		//$fieldNames1 = $instance1->insertTableNameAliasPrefix($fieldNames1, 't_b.');
		
		$fieldNames = $fieldNames . ', ' . $fieldNames1;
		
		$prefixSql = 'select ' . $fieldNames . ', t_d.account as user_account from '.$tableName1.' t_a, '.$tableName2.' t_b, user_account_t t_d where '.$createUidName.' = t_d.user_id and t_a.'.$instance->primaryKeyName.'=t_b.from_id and t_b.share_type='.$shareType.' and t_b.from_type = '.$fromType.' and t_b.share_uid = ?';
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