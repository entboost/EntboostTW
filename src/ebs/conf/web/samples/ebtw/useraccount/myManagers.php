<?php
require_once dirname(__FILE__).'/../useraccount/include.php';

	$wheres = array();
	$instance = UserAccountService::get_instance();
	$userUid = $_SESSION[USER_ID_NAME];
	$sql = 'select t_b.group_id, t_b.dep_name, t_c.user_id, t_c.account, t_c.username from employee_info_t t_a, department_info_t t_b, user_account_t t_c' 
		.' where t_a.group_id=t_b.group_id and t_b.manager_uid =t_c.user_id and t_b.ent_id>0 and t_a.emp_uid=? and t_c.user_id<>?'
		.' order by t_c.username, t_b.dep_name';
	$sql = preg_replace('/\?/i', $userUid, $sql);
	
	//执行查询
	$result = $instance->simpleSearch($sql, NULL, NULL, NULL, NULL, MAX_RECORDS_OF_LOADALL, 0, SQLParamComb_TYPE_AND, $outErrMsg);
	ResultHandle::listedResultToJsonAndOutput($result, true, $outErrMsg);