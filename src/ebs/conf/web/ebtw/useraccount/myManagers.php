<?php
require_once dirname(__FILE__).'/../useraccount/include.php';
	
	$managerName = get_request_param('manager_name');
	
	$wheres = array();
	$instance = UserAccountService::get_instance();
	$userId = $_SESSION[USER_ID_NAME];
	
	$sql = 'select t_b.group_id, t_b.dep_name, t_c.user_id, t_c.account, t_c.username from employee_info_t t_a, department_info_t t_b, user_account_t t_c ' 
			."where t_a.group_id=t_b.group_id and t_b.manager_uid =t_c.user_id and t_b.ent_id>0 and t_a.emp_uid=$userId and t_c.user_id<>$userId";
	//$sql = preg_replace('/\?/i', $userUid, $sql);
	
	$params = array();
	if (!empty($managerName)) {
		$params['t_c.username'] = new SQLParam("%$managerName%", 't_c.username', SQLParam::$OP_LIKE);
	}
	
	$orderBy = 't_c.username, t_b.dep_name';
	//执行查询
	$limit = 100;
	$offset = 0;
	$result = $instance->simpleSearch($sql, NULL, $params, NULL, $orderBy, $limit, $offset, SQLParamComb_TYPE_AND, $outErrMsg);
	ResultHandle::listedResultToJsonAndOutput($result, true, $outErrMsg);