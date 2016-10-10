<?php
/**
 * 列表-团队(同事)的任务查询
 */
	$tableName2 = 'employee_info_t';

// 	if (!isset($additionalFieldNames))
// 		$additionalFieldNames = '';	
	if (!isset($fieldNames3))
		$fieldNames3 = '';
	if (!isset($leftJoinTableName))
		$leftJoinTableName = '';
	
// 	$sql = 'select distinct '.$fieldNames.$additionalFieldNames.' from (';
// 	$sql .= 'select distinct '.$instance->insertTableNameAliasPrefix($fieldNames, 't_a.').$fieldNames3;
// 	$sql .= ' from '.$tableName1.' t_a'.$leftJoinTableName.', '.$tableName2.' t_b ';
// 	$sql .= ' where t_a.create_uid = t_b.emp_uid and t_a.open_flag=1 and t_b.emp_uid<>? and t_b.group_id in (select distinct group_id from employee_info_t where emp_uid = ?)';
// 	$sql .= ' union all ';
// 	$sql .= 'select distinct '.$instance->insertTableNameAliasPrefix($fieldNames, 't_a.').$fieldNames3;
// 	$sql .= ' from '.$tableName1.' t_a'.$leftJoinTableName.', '.$tableName2.' t_b '.', eb_share_user_t t_c';
// 	$sql .= ' where t_c.share_uid = t_b.emp_uid and t_a.open_flag=1 and t_b.emp_uid<>? and t_a.task_id = t_c.from_id and t_c.from_type=2 and t_c.share_type=5 and t_b.group_id in (select distinct group_id from employee_info_t where emp_uid = ?)';
// 	$sql .=') temp_tb ' ;
	
	$sql = 'select distinct '.$instance->insertTableNameAliasPrefix($fieldNames, 't_a.').$fieldNames3;
	$sql .= ' from '.$tableName1.' t_a'.$leftJoinTableName.', '.$tableName2.' t_b '.', eb_share_user_t t_c';
	$sql .= ' where t_a.open_flag=1 and (t_a.create_uid = t_b.emp_uid or t_c.share_uid=t_b.emp_uid) and t_b.emp_uid<>? and t_a.task_id = t_c.from_id and t_c.from_type=2 and t_c.share_type=5 and t_b.group_id in (select distinct group_id from employee_info_t where emp_uid = ?)';
		
	if (empty($orderby))
		$orderby = $formObj->getOrderby();
// 	if (!empty($orderby)) {
// 		$sql .= 'order by '.$orderby;
// 	}
	
	$sqlOfCount = 'select count(task_id) as record_count from ('.$sql.') temp_tb';
	$conditions = array($userId, $userId);
	
	$totalCount = -1;
	if ($forCount==1) { //只查询获取数量
		$result = $instance->simpleSearchForCount($sqlOfCount, $conditions, $wheres, $checkDigits, $whereType, $outErrMsg);
		$json = ResultHandle::countedResultToJsonAndOutput($result, $output, $outErrMsg, $totalCount);
	} else { //查询获取记录列表
		$result = $instance->simpleSearchForCount($sqlOfCount, $conditions, $wheres, $checkDigits, $whereType, $outErrMsg);
		ResultHandle::countedResultToJsonAndOutput($result, false, $outErrMsg, $totalCount);
		$json = $formObj->setRecordCount($totalCount); //保存总记录数
		
		$result = $instance->simpleSearch($sql, $conditions, $wheres, $checkDigits, $orderby, isset($pageSize)?$pageSize:$formObj->getPerPage(), ($formObj->getCurrentPage()-1)*$formObj->getPerPage(), $whereType, $outErrMsg);
		$json = ResultHandle::listedResultToJsonAndOutput($result, $output, $outErrMsg, $totalCount, $formObj);
	}