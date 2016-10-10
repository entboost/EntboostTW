<?php
include dirname(__FILE__).'/../task/preferences.php';
require_once dirname(__FILE__).'/../task/include.php';

	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);
	
	if (empty($formObj)) {
		$formObj = new EBTaskForm();
		$formObj->setValuesFromRequest();
	}
	
	$wheres = $formObj->createWhereConditions();
	$pkFieldName = $PTRIdFieldName;
	
	//验证必填字段
	$queryType = $formObj->{REQUEST_QUERY_TYPE};
	if (!in_array($queryType, array('1', '2', '3', '4', '5', '6','20','7', '8'))) {
		$errMsg = REQUEST_QUERY_TYPE+' is not matched';
		$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return;
	}
	//验证字段合法性
	if (!$formObj->validFormFields($json, $output)) {
		return;
	}
	
	if (!empty($formObj->task_name_lk)) //把task_name_lk转换为like形式的查询条件
		AbstractService::changeToSQLParam($wheres, 'task_name_lk', 'task_name', 'like', '%'.$formObj->task_name_lk.'%');
	
	/*=====松散的时间范围条件=====*/
	include dirname(__FILE__).'/../include_list/include_list_search_time_condition.php';		
		
	if (!empty($formObj->start_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'start_time_s', 'start_time', '>=', $formObj->start_time_s);
	
	if (!empty($formObj->start_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'start_time_e', 'start_time', '<=', $formObj->start_time_e);
	
	if (!empty($formObj->stop_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'stop_time_s', 'stop_time', '>=', $formObj->stop_time_s);
	
	if (!empty($formObj->stop_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'stop_time_e', 'stop_time', '<=', $formObj->stop_time_e);
	
	if (!empty($formObj->create_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'create_time_s', 'create_time', '>=', $formObj->create_time_s);
	
	if (!empty($formObj->create_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'create_time_e', 'create_time', '<=', $formObj->create_time_e);
	
	if (!empty($formObj->last_modify_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'last_modify_time_s', 'last_modify_time', '>=', $formObj->last_modify_time_s);
	
	if (!empty($formObj->last_modify_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'last_modify_time_e', 'last_modify_time', '<=', $formObj->last_modify_time_e);
	
	if ($formObj->status_uncomplete==1) //未完成的任务
		AbstractService::changeToSQLParam($wheres, 'status_uncomplete', 'status', '<=', 2);	
	
	$fromType = $PTRType;
	$whereType = SQLParamComb::$TYPE_AND;
	$checkDigits = $formObj->createCheckDigits();
	
	$orderby = $formObj->getOrderby();
	if ($queryType!=8 && !empty($orderby)) {
		//附加创建时间作为第二排序字段
		if (preg_match('/desc/i', $orderby)) {
			if (!preg_match('/[, ]?create_time[, ]?/i', $orderby))
				$orderby .= ', create_time desc';
		} else if (preg_match('/asc/i', $orderby)) {
			if (!preg_match('/[, ]?create_time[, ]?/i', $orderby))
				$orderby .= ', create_time asc';
		} else {
			if (!preg_match('/[, ]?create_time[, ]?/i', $orderby))
				$orderby .= ', create_time';
		}
	}
	
	$forCount = $formObj->{REQUEST_FOR_COUNT}; //get_request_param(REQUEST_FOR_COUNT);
	$userId = $_SESSION[USER_ID_NAME];
	
	$instance = TaskService::get_instance();
	//$instance1 = ShareUserService::get_instance();
	
	$fieldNames = $instance->fieldNamesAfterRemovedSome(array('modify_count'));
	$tableName1 = 'eb_task_info_t';
	
	$finalOutput = $output;
	$output = false; //暂时关闭输出
	$shareType = 0;
	//1 我提交的任务
	//2 我负责的任务
	//3 我参与的任务
	//4 我关注的任务
	//5 共享给我的任务
	//6 下级的任务
	//7 被关联的任务
	if ($queryType==1) { //1 我提交的任务
		$wheres['create_uid'] = $userId;
		
		if (!empty($orderby) && preg_match('/share_name/i', $orderby)) {
			$shareType = 5;
			$tableName1 = $instance->tableName;
			
			//补充表别名
			$orderby = preg_replace('/share_name/i', 't_b.share_name', $orderby);
			$orderby = preg_replace('/create_time/i', 't_a.create_time', $orderby);
			
			include dirname(__FILE__).'/../include_list/include_list_leftjoin_share_user.php';
		} else {
			include dirname(__FILE__).'/../include_list/include_list_general.php';
		}
	} else if ($queryType==2 || $queryType==3 || $queryType==4 || $queryType==5) {
		//2 我负责的任务；//3 我参与的任务；//4 我关注的任务；5 共享给我的任务
		//$gotShareUser = true;
		$shareType = ($queryType==2)?5:(($queryType==3)?2:($queryType==4?4:3));
		$wheres2 = array('valid_flag'=>1);
		
		if(!empty($orderby))
			$orderby = $instance->insertTableNameAliasPrefix($orderby, 't_a.');
		
		include dirname(__FILE__).'/../include_list/include_list_share_user.php';
	} else if ($queryType==6) { //6 下级的任务
		$ownerFieldName = 'create_uid';
		$open_flag_str = ' and t_a.open_flag<>2';
		
		$fieldNames3 = ', t_s.share_uid as principal_uid, t_s.share_name as principal_name ';
		$leftJoinTableName = ' left join eb_share_user_t t_s on t_a.task_id = t_s.from_id and 2 = t_s.from_type and t_s.share_type = 5 ';
		$shareUserCondition = ' or t_s.share_uid=t_b.emp_uid ';
		
		if(!empty($orderby))
			$orderby = $instance->insertTableNameAliasPrefix($orderby, 't_a.', 'principal_name');
		
		include dirname(__FILE__).'/../include_list/include_list_subordinate.php';
	} else if ($queryType==20) { //20=团队任务
		//$additionalFieldNames = ', principal_uid, principal_name';
		$fieldNames3 = ', t_s.share_uid as principal_uid, t_s.share_name as principal_name ';
		$leftJoinTableName = ' left join eb_share_user_t t_s on t_a.task_id = t_s.from_id and 2 = t_s.from_type and t_s.share_type = 5 ';
		
		if(!empty($orderby))
			$orderby = $instance->insertTableNameAliasPrefix($orderby, 't_a.');
		
		include dirname(__FILE__).'/../include_list/include_list_task_colleagues.php';
	} else if ($queryType==7) { //7 被关联的任务
		if (empty($formObj->from_id) || empty($formObj->from_type)) {
			$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('from_id or from_type', $finalOutput);
			return;
		}
		if ($formObj->from_type!=1) {
			$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('from_type', $finalOutput);
				return;			
		}
		
		//验证当前用户对指定的任务(plan_id)是否有操作权限
// 		$shareType = 0;
// 		if (!DataAuthority::isAuthority(0, 1, $shareType, $userId, $existRows, 'plan_id, create_uid, open_flag', array('plan_id'=>$formObj->from_id), array('plan_id'), PlanService::get_instance(), 1, SQLParamComb_TYPE_AND, false, $outErrMsg, $json)) {
// 			if (!empty($json)) {
// 				if ($finalOutput)
// 					echo $json;
// 				return;
// 			}
// 			$json = ResultHandle::noAuthErrToJsonAndOutput($finalOutput);
// 			return;
// 		}
		
		//执行查询
		$checkDigits = array('from_id', 'from_type');
		$wheres = array('from_id'=>$formObj->from_id, 'from_type'=>$formObj->from_type);
		$conditions = array();
		$joinCondition = 't_a.create_uid = t_b.user_id';
		
		include dirname(__FILE__).'/../include_list/include_list_join_user_account.php';
		//include dirname(__FILE__).'/../include_list/include_list_general.php';
	} else if ($queryType==8) { //工作台看板：未完成的任务
		$statusCondition = 'and status<=2 ';
		$partFieldSql = 'select t_a.task_id,t_a.task_name,t_a.remark,t_a.start_time,t_a.stop_time,t_a.create_uid,t_a.create_name,t_a.create_time, '
			.'t_a.last_modify_time,t_a.class_id,t_a.important,t_a.status,t_a.percentage,t_a.work_time,t_a.open_flag,t_a.from_type, t_a.from_id,t_a.im_group_id, ';
		$sql = $partFieldSql
			.' -1 as su_read_flag, 0 as su_share_id, 0 as su_share_type '
			.'from eb_task_info_t t_a '
			.'where create_uid = ? '.$statusCondition
			.'union all '
			.$partFieldSql
			.'t_b.read_flag as su_read_flag, t_b.share_id as su_share_id, t_b.share_type as su_share_type '
			.'from eb_task_info_t t_a, eb_share_user_t t_b '
			.'where t_a.task_id=t_b.from_id and t_b.share_type in(2, 5) and t_b.from_type = 2 and t_b.share_uid = ? '.$statusCondition;
		
		if (!empty($orderby)) {
			$sql .= 'order by '.$orderby.' , task_id, su_read_flag';
			//order by stop_time desc, create_time desc
		}
		
		$sqlOfCount = 'select count(task_id) as record_count from ('.$sql.') temp_tb';
		$conditions = array($userId, $userId);
		$pageSize = $formObj->getPerPage()*3; //预留冗余的查询结果，保证有足够记录来进行去重
		
		include dirname(__FILE__).'/../include_list/include_list_simple.php';
		
		//过滤重复记录，优先留下"我提交的"记录
		$results = get_results_from_json($json, $tmpObj);
		if (!empty($results)) {
			$lastArry = array();
			array_push($lastArry, array_shift($results));
			while (count($results)>0) {
				$last = end($lastArry);
				$shift = array_shift($results);
// 				log_info('================================');
// 				log_info($shift);
				if ($last->task_id!=$shift->task_id) {
					array_push($lastArry, $shift);
					//判断是否超出返回结果的最大限制
					if (count($lastArry)>=$formObj->getPerPage())
						break;
				}
			}
			$json = ResultHandle::listedResultToJsonAndOutput($lastArry, $output, null, -1);
		}
	}
	
	if ($forCount!=1) {
		//获取(评阅人、负责人、参与人、共享人等)资料
		$validFlag = 1;
		$json = completing_list_shareusers('list', $PTRType, $json, NULL, 0, NULL, $validFlag, $isQuery);
		//$json = completing_list_shareusers('prop', $PTRType, $json, NULL, $shareType, $userId, 1, $isQuery);
		
		//整理数据操作权限
		$json = DataAuthority::reorganizeAllowedActions($PTRType, 1, $json, $userId);
		$json = DataAuthority::reorganizeAllowedActions($PTRType, 2, $json, $userId);
		$json = DataAuthority::reorganizeAllowedActions($PTRType, 3, $json, $userId);
		$json = DataAuthority::reorganizeAllowedActions($PTRType, 4, $json, $userId);
		$json = DataAuthority::reorganizeAllowedActions($PTRType, 5, $json, $userId);
	}
	
	if ($finalOutput)
		echo $json;