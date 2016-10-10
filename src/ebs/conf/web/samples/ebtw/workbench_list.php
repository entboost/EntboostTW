<?php
include dirname(__FILE__).'/preferences.php';
require_once dirname(__FILE__).'/include.php';
require_once dirname(__FILE__).'/plan/include.php';
require_once dirname(__FILE__).'/task/include.php';
require_once dirname(__FILE__).'/report/include.php';
require_once dirname(__FILE__).'/useraccount/useraccount.php';
require_once dirname(__FILE__).'/model/EBUsualForm.class.php';

	$output = !isset($embed);

	if (empty($formObj)) {
		$formObj = new EBUsualForm();
		$formObj->setValuesFromRequest();
	}
	
	//验证必填字段
	$queryType = $formObj->{REQUEST_QUERY_TYPE};
	if (!in_array($queryType, array('1', '2', '3'))) {
		$errMsg = REQUEST_QUERY_TYPE.' is not matched';
		$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return;
	}
	
	$userId = $_SESSION[USER_ID_NAME];
	$forCount = $formObj->{REQUEST_FOR_COUNT};
	$instance = PlanService::get_instance();
	
	$finalOutput = $output;
	$output = false;
	
	if ($queryType==1) { //工作台看板界面查询"待办事宜"
		$pkFieldName = 'ptr_id';
		
		//组装SQL语句
		$sql = 'select t_a.plan_id as ptr_id, t_a.plan_name as ptr_name, t_a.period, t_a.start_time, t_a.stop_time, '
			.'t_a.create_uid, t_a.create_name, t_a.create_time, t_a.last_modify_time, t_a.class_id, t_a.important, t_a.status, t_a.open_flag, '
			.'t_b.share_id as su_share_id, t_b.from_type as ptr_type, t_b.share_uid as su_share_uid, '
			.'t_b.share_name as su_share_name, t_b.create_time as su_create_time, '
			.'t_b.read_flag as su_read_flag, t_b.read_time as su_read_time, '
			.'t_b.result_status as su_result_status, t_b.result_time as su_result_time, t_c.account as create_account '
			.'from eb_plan_info_t t_a, eb_share_user_t t_b, user_account_t t_c '
			.'where t_a.plan_id=t_b.from_id and t_a.create_uid = t_c.user_id and t_b.share_type=1 and t_b.from_type = 1 '
			.'and t_b.share_uid = ? and t_a.is_deleted = 0 and t_b.valid_flag = 1 and t_a.status <=3 '
			.'UNION ALL '
			.'select t_a.report_id as ptr_id, t_a.completed_work as ptr_name, t_a.period, t_a.start_time, t_a.stop_time, '
			.'t_a.report_uid as create_uid, t_a.create_name, t_a.create_time, t_a.last_modify_time, t_a.class_id, -1 as important, t_a.status, t_a.open_flag, '
			.'t_b.share_id as su_share_id, t_b.from_type as ptr_type, t_b.share_uid as su_share_uid, ' 
			.'t_b.share_name as su_share_name, t_b.create_time as su_create_time, '
			.'t_b.read_flag as su_read_flag, t_b.read_time as su_read_time, ' 
			.'t_b.result_status as su_result_status, t_b.result_time as su_result_time, t_c.account as create_account '
			.'from eb_report_info_t t_a, eb_share_user_t t_b, user_account_t t_c '
			.'where t_a.report_id=t_b.from_id and t_a.report_uid = t_c.user_id and t_b.share_type=1 and t_b.from_type = 3 '
			.'and t_b.share_uid = ? and t_b.valid_flag = 1 and t_a.status <=2 ';
		
		$orderby = $formObj->getOrderby(); //get_request_param(REQUEST_ORDER_BY);
		if (!empty($orderby)) {
			$sql .= 'order by '.$orderby;
			//order by su_create_time desc
		}
		
		$sqlOfCount = 'select count(ptr_id) as record_count from ('.$sql.') temp_tb';
		
		$conditions = array($userId, $userId);
		include dirname(__FILE__).'/include_list/include_list_simple.php';
	} else if ($queryType==2) { //工作台最新动态界面
		$pkFieldName = 'op_id';
		$lastCreateTimeStr = date('Y-m-d H:i:s', strtotime("-31 days"));
		$opTypes = get_request_param('op_type'); //"操作类型"查询条件
		
		//获取当前用户的下级用户，并生成用于查询下级人员动态信息的查询条件
		$json1 = get_mysubordinates();
		$result1 = get_results_from_json($json1, $tmpObj1);
		if (!empty($result1)) {
			$mySubordinates = array();
			foreach ($result1 as $group) {
				foreach ($group->members as $member) {
					$mySubordinates[$member->user_id] = $member->user_id;
				}
			}
			
			$mySubordinateString = '';
			foreach ($mySubordinates as $subordinateUserId) {
				$mySubordinateString.= $subordinateUserId.',';
			}
			if (mb_strlen($mySubordinateString)>0)
				$mySubordinateString = substr($mySubordinateString, 0, -1);
		}
		
		//组装SQL语句
		$openFlagSql = '';
		$outerLimit = $formObj->getPerPage();
		$limitStr = ' limit '.$outerLimit;
		
		$createTimeSql = "and t_a.create_time >='$lastCreateTimeStr' "; 
		$opTypeSql = '';
		if (!empty($opTypes)) {
			$opTypestring = '';
			foreach ($opTypes as $opType) {
				$opTypestring.= $opType.',';
			}
			if (mb_strlen($opTypestring)>0)
				$opTypestring = substr($opTypestring, 0, -1);
			
			$opTypeSql.= "and op_type in($opTypestring) ";
		}
		
		$fromTypeSql = '';
		$fromType = get_request_param('from_type');
		if (isset($fromType)) {
			if (!in_array($fromType, array('1', '2', '3'))) {
				$errMsg = 'from_type is not matched';
				$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
			$fromTypeSql.="and t_a.from_type = $fromType ";
		}
		
		$groupby = ' group by ttt.from_type, ttt.from_id, ttt.op_type '; //分组
		$fieldSql = ($forCount==1)?'t_a.op_id, t_a.create_time':'t_a.*';

		$ptrUnique = get_request_param('ptr_unique', '0'); //标识是否每一个计划或任务、每一种操作类型只显示一条记录
		if ($ptrUnique==1) {
			$orderby = ' order by t_a.create_time desc '; //排序字段
			
			//--plan 计划
			$fieldSql_0 = ($forCount==1)?'':', t_b.plan_name as ptr_name, t_b.create_uid as ptr_create_uid, t_b.create_name as ptr_create_name, t_b.create_time as ptr_create_time, t_d.account as user_account ';
			$partSql_prefix_outer = "SELECT $fieldSql $fieldSql_0 from (".'';
			$partSql_suffix_outer = ") t_xx, eb_operate_record_t t_a, eb_plan_info_t t_b, user_account_t t_d where t_a.user_id = t_d.user_id and t_a.from_id = t_b.plan_id "
					."and t_xx.op_id = t_a.op_id and t_xx.from_id = t_a.from_id and t_xx.op_type = t_a.op_type".$orderby.$limitStr;
			$partSql_prefix = 'SELECT max(op_id) as op_id, from_type, from_id, op_type from (';
			$partSql_suffix = ') ttt group by ttt.from_type, ttt.from_id, ttt.op_type ';
			
			$partSql_prefix_inner = "select t_a.op_id, t_a.from_type, t_a.from_id, t_a.op_type from eb_operate_record_t t_a, eb_plan_info_t t_b ";
			$partSql_suffix_inner = " where t_a.from_id = t_b.plan_id ".$fromTypeSql." and t_a.is_deleted = 0 and t_b.is_deleted = 0 ".$createTimeSql.$opTypeSql;
			
			if (!empty($mySubordinateString))
				$openFlagSql = ' and (t_b.open_flag=0 and (t_b.create_uid in('.$mySubordinateString.')) or t_b.open_flag=1) ';
			
			$part1Sql = $partSql_prefix_outer.$partSql_prefix.$partSql_prefix_inner.$partSql_suffix_inner.$openFlagSql.$partSql_suffix.$partSql_suffix_outer;
			$part2Sql = $partSql_prefix_outer.$partSql_prefix.$partSql_prefix_inner.', eb_share_user_t t_c '.$partSql_suffix_inner.'and t_c.share_uid = ? and t_b.plan_id = t_c.from_id and t_c.from_type=1 and t_c.share_type in (1,3) '.$partSql_suffix.$partSql_suffix_outer;
			$part3Sql = $partSql_prefix_outer.$partSql_prefix.$partSql_prefix_inner.$partSql_suffix_inner.'and (t_a.user_id =? or t_b.create_uid = ?) '.$partSql_suffix.$partSql_suffix_outer;
			
			$sql1 = 'select * from ('.$part1Sql.') a1 '
					.'UNION ALL '
					.'select * from ('.$part2Sql.') a2 '
					.'UNION ALL '
					.'select * from ('.$part3Sql.') a3 ';
					
			//--task 任务
			$fieldSql_1 = ($forCount==1)?'':', t_b.task_name as ptr_name, t_b.create_uid as ptr_create_uid, t_b.create_name as ptr_create_name, t_b.create_time as ptr_create_time, t_d.account as user_account ';
			$partSql_prefix_outer = "SELECT $fieldSql $fieldSql_1 from (".'';
			$partSql_suffix_outer = ") t_xx, eb_operate_record_t t_a, eb_task_info_t t_b, user_account_t t_d where t_a.user_id = t_d.user_id and t_a.from_id = t_b.task_id "
					."and t_xx.op_id = t_a.op_id and t_xx.from_id = t_a.from_id and t_xx.op_type = t_a.op_type".$orderby.$limitStr;
			$partSql_prefix = 'SELECT max(op_id) as op_id, from_type, from_id, op_type from (';
			$partSql_suffix = ') ttt group by ttt.from_type, ttt.from_id, ttt.op_type ';
			
			$partSql_prefix_inner = "select t_a.op_id, t_a.from_type, t_a.from_id, t_a.op_type from eb_operate_record_t t_a, eb_task_info_t t_b ";
			$partSql_suffix_inner = " where t_a.from_id = t_b.task_id ".$fromTypeSql." and t_a.is_deleted = 0 ".$createTimeSql.$opTypeSql;
			
			if (!empty($mySubordinateString))
				$openFlagSql = ' and (t_b.open_flag=0 and (t_b.create_uid in('.$mySubordinateString.')) or t_b.open_flag=1) ';
			
			$part1Sql = $partSql_prefix_outer.$partSql_prefix.$partSql_prefix_inner.$partSql_suffix_inner.$openFlagSql.$partSql_suffix.$partSql_suffix_outer;
			$part2Sql = $partSql_prefix_outer.$partSql_prefix.$partSql_prefix_inner.', eb_share_user_t t_c '.$partSql_suffix_inner.'and t_c.share_uid = ? and t_b.task_id = t_c.from_id and t_c.from_type=2 and t_c.share_type in (2,3,4,5) '.$partSql_suffix.$partSql_suffix_outer;
			$part3Sql = $partSql_prefix_outer.$partSql_prefix.$partSql_prefix_inner.$partSql_suffix_inner.'and (t_a.user_id =? or t_b.create_uid = ?) '.$partSql_suffix.$partSql_suffix_outer;
				
			$sql2 = 'select * from ('.$part1Sql.') a1 '
					.'UNION ALL '
					.'select * from ('.$part2Sql.') a2 '
					.'UNION ALL '
					.'select * from ('.$part3Sql.') a3 ';
			
			//--report 报告
			$excludeSql = ' and t_b.period<>1 ';
			$fieldSql_2 = ($forCount==1)?'':', t_b.completed_work as ptr_name, t_b.report_uid as ptr_create_uid, t_b.create_name as ptr_create_name, t_b.create_time as ptr_create_time, t_d.account as user_account ';
			$partSql_prefix_outer = "SELECT $fieldSql $fieldSql_2 from (".'';
			$partSql_suffix_outer = ") t_xx, eb_operate_record_t t_a, eb_report_info_t t_b, user_account_t t_d where t_a.user_id = t_d.user_id and t_a.from_id = t_b.report_id "
					."and t_xx.op_id = t_a.op_id and t_xx.from_id = t_a.from_id and t_xx.op_type = t_a.op_type".$orderby.$limitStr;
			$partSql_prefix = 'SELECT max(op_id) as op_id, from_type, from_id, op_type from (';
			$partSql_suffix = ') ttt group by ttt.from_type, ttt.from_id, ttt.op_type ';
			
			$partSql_prefix_inner = "select t_a.op_id, t_a.from_type, t_a.from_id, t_a.op_type from eb_operate_record_t t_a, eb_report_info_t t_b ";
			$partSql_suffix_inner = " where t_a.from_id = t_b.report_id ".$excludeSql.$fromTypeSql.$createTimeSql.$opTypeSql;
			
			if (!empty($mySubordinateString))
				$openFlagSql = ' and (t_b.open_flag=0 and (t_b.report_uid in('.$mySubordinateString.')) or t_b.open_flag=1) ';
			
			$part1Sql = $partSql_prefix_outer.$partSql_prefix.$partSql_prefix_inner.$partSql_suffix_inner.$openFlagSql.$partSql_suffix.$partSql_suffix_outer;
			$part2Sql = $partSql_prefix_outer.$partSql_prefix.$partSql_prefix_inner.', eb_share_user_t t_c '.$partSql_suffix_inner.'and t_c.share_uid = ? and t_b.report_id = t_c.from_id and t_c.from_type=3 and t_c.share_type in (1) '.$partSql_suffix.$partSql_suffix_outer;
			$part3Sql = $partSql_prefix_outer.$partSql_prefix.$partSql_prefix_inner.$partSql_suffix_inner.'and (t_a.user_id =? or t_b.report_uid = ?) '.$partSql_suffix.$partSql_suffix_outer;
	
			$sql3 = 'select * from ('.$part1Sql.') a1 '
					.'UNION ALL '
					.'select * from ('.$part2Sql.') a2 '
					.'UNION ALL '
					.'select * from ('.$part3Sql.') a3 ';
		} else {
			$orderby = ' order by t_a.create_time desc, t_a.op_id '; //排序字段
			
			//--plan 计划
			$fieldSql_0 = ($forCount==1)?'':', t_b.plan_name as ptr_name, t_b.create_uid as ptr_create_uid, t_b.create_name as ptr_create_name, t_b.create_time as ptr_create_time, t_d.account as user_account';
			$partSql_prefix = "select * from (SELECT $fieldSql $fieldSql_0 from eb_operate_record_t t_a, eb_plan_info_t t_b, user_account_t t_d ";
			$partSql_suffix = 'where t_a.user_id = t_d.user_id and t_a.from_type = 1 and t_a.from_id = t_b.plan_id and t_a.is_deleted = 0 and t_b.is_deleted = 0 ';
			$part1Sql = $partSql_prefix.$partSql_suffix.$createTimeSql.$opTypeSql.$fromTypeSql;
			$part2Sql = $partSql_prefix.', eb_share_user_t t_c '.$partSql_suffix.$createTimeSql.$opTypeSql.$fromTypeSql;
			$part3Sql = $partSql_prefix.$partSql_suffix.$createTimeSql.$opTypeSql.$fromTypeSql.'and (t_a.user_id =? or t_b.create_uid = ?) ';
			if (!empty($mySubordinateString))
				$openFlagSql = 't_b.open_flag=0 and (t_b.create_uid in('.$mySubordinateString.')) or ';
			$sql1 = $part1Sql.'and ('.$openFlagSql.'t_b.open_flag=1) '.$orderby.$limitStr.') a1 '
					.'UNION ALL '
					.$part2Sql.'and t_c.share_uid = ? and t_b.plan_id = t_c.from_id and t_c.from_type=1 and t_c.share_type in (1,3) '.$orderby.$limitStr.') a2 '
					.'UNION ALL '
					.$part3Sql.$orderby.$limitStr.') a3 ';
			
			//--task 任务
			$fieldSql_1 = ($forCount==1)?'':', t_b.task_name as ptr_name, t_b.create_uid as ptr_create_uid, t_b.create_name as ptr_create_name, t_b.create_time as ptr_create_time, t_d.account as user_account';
			$partSql_prefix = "select * from (SELECT $fieldSql $fieldSql_1 from eb_operate_record_t t_a, eb_task_info_t t_b, user_account_t t_d ";
			$partSql_suffix = 'where t_a.user_id = t_d.user_id and t_a.from_type = 2 and t_a.from_id = t_b.task_id and t_a.is_deleted = 0 ';
			$part1Sql = $partSql_prefix.$partSql_suffix.$createTimeSql.$opTypeSql.$fromTypeSql;
			$part2Sql = $partSql_prefix.', eb_share_user_t t_c '.$partSql_suffix.$createTimeSql.$opTypeSql.$fromTypeSql;
			$part3Sql = $partSql_prefix.$partSql_suffix.$createTimeSql.$opTypeSql.$fromTypeSql.'and (t_a.user_id =? or t_b.create_uid = ?) ';
			if (!empty($mySubordinateString))
				$openFlagSql = 't_b.open_flag=0 and (t_b.create_uid in('.$mySubordinateString.')) or ';
			$sql2 = $part1Sql.'and ('.$openFlagSql.'t_b.open_flag=1) '.$orderby.$limitStr.') b1 '
					.'UNION ALL '
					.$part2Sql.'and t_c.share_uid = ? and t_b.task_id = t_c.from_id and t_c.from_type=2 and t_c.share_type in (2,3,4,5) '.$orderby.$limitStr.') b2 '
					.'UNION ALL '
					.$part3Sql.$orderby.$limitStr.') b3 ';
			
			//--report 报告
			$fieldSql_2 = ($forCount==1)?'':', t_b.completed_work as ptr_name, t_b.report_uid as ptr_create_uid, t_b.create_name as ptr_create_name, t_b.create_time as ptr_create_time, t_d.account as user_account';
			$excludeSql = ' and t_b.period<>1 ';
			$partSql_prefix = "select * from (SELECT $fieldSql $fieldSql_2 from eb_operate_record_t t_a, eb_report_info_t t_b, user_account_t t_d ";
			$partSql_suffix = 'where t_a.user_id = t_d.user_id and t_a.from_type = 3 and t_a.from_id = t_b.report_id ';
			$part1Sql = $partSql_prefix.$partSql_suffix.$createTimeSql.$opTypeSql.$fromTypeSql.$excludeSql;
			$part2Sql = $partSql_prefix.', eb_share_user_t t_c '.$partSql_suffix.$createTimeSql.$opTypeSql.$fromTypeSql.$excludeSql;
			$part3Sql = $partSql_prefix.$partSql_suffix.$createTimeSql.$opTypeSql.$fromTypeSql.$excludeSql.'and (t_a.user_id =? or t_b.report_uid = ?) ';
			if (!empty($mySubordinateString))
				$openFlagSql = 't_b.open_flag=0 and (t_b.report_uid in('.$mySubordinateString.')) or ';
			$sql3 = $part1Sql.'and ('.$openFlagSql.'t_b.open_flag=1) '.$orderby.$limitStr.') c1 '
					.'UNION ALL '
					.$part2Sql.'and t_c.share_uid = ? and t_b.report_id = t_c.from_id and t_c.from_type=3 and t_c.share_type in (1) '.$orderby.$limitStr.') c2 '
					.'UNION ALL '
					.$part3Sql.$orderby.$limitStr.') c3 ';
		}
		//--最后组装
		$sql = 'select'.(($forCount==1)?' op_id, create_time ':' op_id, from_type, from_id, from_name, user_id, user_name, op_type, op_data, op_name, op_time, remark, create_time, user_account, last_modify_time, modify_count, is_deleted, ptr_name, ptr_create_uid, ptr_create_name, ptr_create_time ')
			.'from ('
			.$sql1
			.' UNION ALL '
			.$sql2
			.' UNION ALL '
			.$sql3
			.') tmp_tb '
			.' ORDER BY create_time desc, op_id ';
			
		$pageSize = $outerLimit*9; //预留最大查询冗余数量
		$sqlOfCount = 'select count(op_id) as record_count from ('.$sql.') temp_tb';
		
		$conditions = array();
		$conditions0 = array($userId, $userId, $userId);
		$conditions = array_merge($conditions, $conditions0);
		$conditions = array_merge($conditions, $conditions0);
		$conditions = array_merge($conditions, $conditions0);
		
		
		//临时去除获取数量的参数设置
		if ($forCount==1) {
			$isCount = true;
			$forCount=0;
		}
		include dirname(__FILE__).'/include_list/include_list_simple.php';
		//恢复获取数量的参数设置
		if (isset($isCount)) {
			$forCount = 1;
		}
		
		//过滤重复记录
		$results = get_results_from_json($json, $tmpObj);
		$lastArry = array();
		if (!empty($results)) {
			array_push($lastArry, array_shift($results));
			while (count($results)>0) {
				$last = end($lastArry);
				$shift = array_shift($results);
				if ($last->op_id!=$shift->op_id) {
					array_push($lastArry, $shift);
					//判断是否超出返回结果的最大限制
					if (count($lastArry)>=$outerLimit)
						break;
				}
			}
		}
		
		if ($forCount==1) {
			$json = ResultHandle::successToJsonAndOutput(null, array('count'=>count($lastArry)), null, $output);
		} else {
			$json = ResultHandle::listedResultToJsonAndOutput($lastArry, $output, null, -1);
		}
		
		//补全任务负责人资料
		if ($forCount!=1)
			$json = completing_list_shareusers('list', 2, $json, 'from_id', 5, NULL, 1, $isQuery);
		
		//补全统计相关评论数量
		$count_discuss = get_request_param('count_discuss', 0);
		if (!empty($count_discuss)) {
			$opTypes = array(3,4,5);
			$countRules = array('discuss'=>array(3,4,5));
			$json = completing_list_count_of_operaterecords('list', 1, $opTypes, 'from_id', $countRules, false, $json, $isQuery);
			$json = completing_list_count_of_operaterecords('list', 2, $opTypes, 'from_id', $countRules, false, $json, $isQuery);
			$json = completing_list_count_of_operaterecords('list', 3, $opTypes, 'from_id', $countRules, false, $json, $isQuery);
		}
	} else if($queryType==3) { //工作台文件界面：一次以多个文档(计划、任务、报告)编号为条件，查询明细和操作权限
		$ptrType = get_request_param('ptr_type');
		$ptrIds = get_request_param('ptr_id');
		
		//验证必要条件
		if (!in_array($ptrType, array('1', '2', '3'))) {
			$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('ptr_type', $output);
			if ($finalOutput)
				echo $json;
			return;
		}
		if (empty($ptrIds)) {
			$json = ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('ptr_id', $output);
			if ($finalOutput)
				echo $json;
			return;
		}
		
		if ($ptrType==1) { //计划
			$pkFieldName = 'plan_id';
			$tableName = 'eb_plan_info_t';
			$instance = PlanService::get_instance();
			
			$fieldNames = $instance->fieldNamesAfterRemovedSome(array('last_modify_time', 'modify_count', 'remark', 'class_id', 'is_deleted', 'from_type', 'from_id'));
		} else if ($ptrType==2) { //任务
			$pkFieldName = 'task_id';
			$tableName = 'eb_task_info_t';
			$instance = TaskService::get_instance();
			
			$fieldNames = $instance->fieldNamesAfterRemovedSome(array('last_modify_time', 'modify_count', 'remark', 'class_id', 'from_type', 'from_id', 'im_group_id'));
		} else if ($ptrType==3) { //报告
			$pkFieldName = 'report_id';
			$tableName = 'eb_report_info_t';
			$instance = ReportService::get_instance();
			
			$fieldNames = $instance->fieldNamesAfterRemovedSome(array('last_modify_time', 'modify_count', 'remark', 'class_id', 'uncompleted_work'));
		}
		
		$whereType = SQLParamComb::$TYPE_AND;
		$wheres = array(new SQLParamComb(array($pkFieldName=>$ptrIds), SQLParamComb::$TYPE_OR));
		$checkDigits = array($pkFieldName);
		include dirname(__FILE__).'/include_list/include_list_general.php';
	}
	
	if ($forCount!=1) {
 		if ($queryType==1 || $queryType==3) {
 			//获取(评审/评阅人、负责人、参与人、共享人、关注人等)资料
 			$shareType = 0;
 			$validFlag = 1;
 			$json = completing_list_shareusers('list', 1, $json, $pkFieldName, $shareType, NULL, $validFlag, $isQuery);
 			$json = completing_list_shareusers('list', 2, $json, $pkFieldName, $shareType, NULL, $validFlag, $isQuery);
 			$json = completing_list_shareusers('list', 3, $json, $pkFieldName, $shareType, NULL, $validFlag, $isQuery);
 			
			//整理数据操作权限
			if ($queryType==1)
				$ptrTypes = array(1,3);
			else
				$ptrTypes = array(1,2,3);
			$json = DataAuthority::reorganizeAllowedActions($ptrTypes, 1, $json, $userId);
			$json = DataAuthority::reorganizeAllowedActions($ptrTypes, 2, $json, $userId);
			$json = DataAuthority::reorganizeAllowedActions($ptrTypes, 3, $json, $userId);
			$json = DataAuthority::reorganizeAllowedActions($ptrTypes, 4, $json, $userId);
			$json = DataAuthority::reorganizeAllowedActions($ptrTypes, 5, $json, $userId);
 		}
	}
	
	if ($finalOutput)
		echo $json;