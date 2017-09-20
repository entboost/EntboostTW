<?php
require_once dirname(__FILE__).'/../useraccount/include.php';

	$wheres = array();
	$instance = UserAccountService::get_instance();
	$userUid = $_SESSION[USER_ID_NAME];
	$sql = 'select t_d.group_id, t_d.dep_name, t_e.user_id, t_e.account as user_account, t_e.username as user_name, t_c.emp_id from employee_info_t t_c, department_info_t t_d, user_account_t t_e where t_c.group_id=t_d.group_id and t_c.emp_uid=t_e.user_id'
		.' and t_c.group_id in (select t_b.group_id from employee_info_t t_a, department_info_t t_b where t_a.group_id=t_b.group_id and t_b.ent_id>0 and t_a.emp_uid=?)'
		.' order by dep_name, user_name';
	
	$sql = preg_replace('/\?/i', $userUid, $sql);
	
	//执行查询
	$result = $instance->simpleSearch($sql, NULL, NULL, NULL, NULL, MAX_RECORDS_OF_LOADALL, 0, SQLParamComb_TYPE_AND, $outErrMsg);
	$json = ResultHandle::listedResultToJsonAndOutput($result, false, $outErrMsg);
	
	$results = get_results_from_json($json, $tmpObj); //从json字符串提取列表记录
	if (empty($results)) {
		echo $json;
		return;
	}
	
	//遍历重整数据
	$finalResults = array();
	foreach ($results as $record) {
		$groupId = $record->group_id;
		if (!isset($finalResults[$groupId])) {
			$finalResults[$groupId] = new stdClass();
			$finalResults[$groupId]->group_id = $record->group_id;
			$finalResults[$groupId]->group_name = $record->dep_name;
			$finalResults[$groupId]->members = array();
			$list = &$finalResults[$groupId]->members;
		} else {
			$list = &$finalResults[$groupId]->members;
		}
		
		unset($record->group_id);
		unset($record->dep_name);
		array_push($list, $record);
	}
	
	$tmpObj->results = array();
	foreach ($finalResults as $groupId=>$members) {
		array_push($tmpObj->results, $members);
	}
	$json = json_encode($tmpObj);
	
	echo $json;