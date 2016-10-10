<?php
require_once dirname(__FILE__).'/include.php';

	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);
	
	if (empty($formObj)) {
		$formObj = new EBOperateRecordForm();
		$formObj->setValuesFromRequest();
	}
	
	$wheres = $formObj->createWhereConditions();
	
	//验证必填字段
	if (empty($formObj->op_id)) {
		if (!$formObj->validNotEmpty('from_id, from_type', $outErrMsg)) {
			if (empty($formObj->classification_statistic) && empty($formObj->datetimeAndUseridConditions)) {
				$json = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
				return;
			}
		}
	}
	
	//验证from_type
	if (isset($formObj->from_type)) {
		$valid = true;
		if (is_array($formObj->from_type)) {
			foreach ($formObj->from_type as $fromType) {
				$valid = in_array($fromType, array('1', '2', '3'));
				if (!$valid)
					break;
			}
		} else { 
			$valid = in_array($formObj->from_type, array('1', '2', '3'));
		}
		if (!$valid) {
			$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('from_type', $output);
			return;
		}
	}
	
	if (!empty($formObj->from_name_lk)) //把from_name_lk转换为like形式的查询条件
		AbstractService::changeToSQLParam($wheres, '$from_name_lk', 'from_name', 'like', '%'.$formObj->$from_name_lk.'%');
	
	if (!empty($formObj->user_name_lk)) //把user_name_lk转换为like形式的查询条件
		AbstractService::changeToSQLParam($wheres, '$user_name_lk', 'user_name', 'like', '%'.$formObj->$user_name_lk.'%');
		
	if (!empty($formObj->create_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'create_time_s', 'create_time', '>=', $formObj->create_time_s);
	
	if (!empty($formObj->create_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'create_time_e', 'create_time', '<=', $formObj->create_time_e);
	
	if (!empty($formObj->last_modify_time_s)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'last_modify_time_s', 'last_modify_time', '>=', $formObj->last_modify_time_s);
	
	if (!empty($formObj->last_modify_time_e)) //转换为范围条件
		AbstractService::changeToSQLParam($wheres, 'last_modify_time_e', 'last_modify_time', '<=', $formObj->last_modify_time_e);
	
	//操作类型映射条件
	if (isset($formObj->op_type_class)) {
		//验证合法性
		if (!in_array($formObj->op_type_class, array('0', '1', '2', '3', '4', '11'))) {
			$json = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('op_type_class', $output);
			return;
		}
		
		$op_type_class = (int)$formObj->op_type_class;
		switch ($op_type_class) {
			case 0: //全部
				unset($wheres['op_type_class']); //去除查询条件
				break;
			case 1: //评论/回复动态：op_type>=3 AND op_type<=5
			case 2: //成员(参与人、共享人)：op_type>=10 AND op_type<=16
			case 3: //评审：op_type>=20 AND op_type<=24
				if ($op_type_class==1) {
					$ltValue = 3;
					$gtValue = 5;
				} else if ($op_type_class==2) {
					$ltValue = 10;
					$gtValue = 16;
				} else if ($op_type_class==3) {
					$ltValue = 20;
					$gtValue = 24;
				}
				$op_typeWheres = array('op_type'=>array(new SQLParam($ltValue, 'op_type', SQLParam::$OP_GT_EQ), new SQLParam($gtValue, 'op_type', SQLParam::$OP_LT_EQ)));
				AbstractService::changeToSQLParamComb($wheres, 'op_type_class', 'op_type', new SQLParamComb($op_typeWheres, SQLParamComb::$TYPE_AND));
				break;
			case 4: //进度：op_type=31 OR op_type=32
				$op_typeWheres = array('op_type'=>array(31, 32));
				AbstractService::changeToSQLParamComb($wheres, 'op_type_class', 'op_type', new SQLParamComb($op_typeWheres, SQLParamComb::$TYPE_OR));
				break;
			case 11: //内容：op_type=10 OR op_type=33 OR op_type=34 OR op_type=50 OR op_type=51 OR op_type=60 OR op_type=61 OR op_type=70 OR op_type=71
				$op_typeWheres = array('op_type'=>array(10, 33, 34, 50, 51, 60, 61, 70, 71));
				AbstractService::changeToSQLParamComb($wheres, 'op_type_class', 'op_type', new SQLParamComb($op_typeWheres, SQLParamComb::$TYPE_OR));
				break;
		}
	}
	
	$instance = OperateRecordService::get_instance();
	
	$whereType = SQLParamComb::$TYPE_AND;
	$checkDigits = $formObj->createCheckDigits();
	$userId = $_SESSION[USER_ID_NAME];
	array_push($checkDigits, 'user_id');
	$fieldNames = $instance->fieldNamesAfterRemovedSome(array('modify_count'));
	
	$orderby = $formObj->getOrderby();
	if(!empty($orderby))
		$orderby = $instance->insertTableNameAliasPrefix($orderby, 't_a.');
	
	//解析日期范围及用户编号查询条件
	if (!empty($formObj->datetimeAndUseridConditions)) {
		unset($wheres['datetimeAndUseridConditions']);
			
		$sqlCombs = array();
		foreach ($formObj->datetimeAndUseridConditions as $cdt) {
			$sqlComb = new SQLParamComb(array('create_time_s'=>new SQLParam($cdt['create_time_s'], 'create_time', '>='),
					'create_time_e'=>new SQLParam($cdt['create_time_e'], 'create_time', '<='),
					'user_id'=>$cdt['user_id'],
			), SQLParamComb::$TYPE_AND);
			array_push($sqlCombs, $sqlComb);
		}
		array_push($wheres, new SQLParamComb($sqlCombs, SQLParamComb::$TYPE_OR));
		
		$fieldNamesExtend = '1 as query_mark_type';
	}
	
	if (isset($formObj->classification_statistic)) { //仅进行分类统计数量
		unset($wheres['classification_statistic']);
		
		$conditions = array();
		if (!$instance->processWheres($wheres, $whereString, $conditions, $checkDigits, $whereType, true, $errMsg)) {
			$json = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		if (!empty($formObj->from_id)) { //按文档编号查询的模式
			$sql = 'select count(op_type) as c, op_type, from_id, from_type FROM eb_operate_record_t '.$whereString .' GROUP BY from_type, op_type, from_id ORDER BY from_type, from_id, op_type';
		} else if (!empty($formObj->datetimeAndUseridConditions)) { //按日期范围及用户编号查询的模式
			$sql = 'select count(op_type) as c, op_type, from_id, from_type, user_id, substr(#date_to_str(create_time), 1 ,10) as create_date from eb_operate_record_t '
					.$whereString .' GROUP BY from_type, op_type, from_id, user_id, create_date'.' ORDER BY create_date desc, user_id, from_type, from_id, op_type';
		}
		
		$result = $instance->simpleSearch($sql, $conditions, NULL, NULL, NULL, MAX_RECORDS_OF_LOADALL, 0, SQLParamComb_TYPE_AND, $errMsg);
		$json = ResultHandle::listedResultToJsonAndOutput($result, $output, $errMsg);
	} else if (isset($formObj->ptrnews)) { //工作台最新动态
		unset($wheres['ptrnews']);
	} else { //其它
		$forCount = $formObj->{REQUEST_FOR_COUNT};
		$tableName1 = $instance->tableName;
		
		$conditions = array();
		$joinCondition = 't_a.user_id = t_b.user_id';
		include dirname(__FILE__).'/../include_list/include_list_join_user_account.php';
		
		if ($forCount!=1) {
			//$json = completing_list_ptr_info($json, 1, NULL, $isQuery); //补全对应计划的资料 //暂时不需要
			
			//如果op_data字段是用户编号，则通过它获取关联的用户资料
			$results = get_results_from_json($json, $tmpObj);
			if (!empty($results)) {
				$userIds = array();
				
				//遍历提取符合条件的用户编号
				foreach ($results as $opr) {
					//暂时只支持op_type=20(评审人、评阅人)
					if (in_array($opr->op_type, array(20)))
						array_push($userIds, $opr->op_data);
				}
				//过滤重复值
				$userIds = array_values(array_unique($userIds));
				
				//查询获取用户资料
				if (!empty($userIds)) {
					$users = array();
					$json1 = get_useraccounts($userIds);
					$results1 = get_results_from_json($json1, $tmpObj1);
					foreach($results1 as $user) {
						foreach ($results as &$opr) {
							if ($user->user_id==$opr->op_data) {
								$opr->op_account = $user->account;
								$matched = true;
							}
						}
					}
					
					//重新生成JSON字符串
					if ($matched)
						$json = json_encode($tmpObj);
				}
			}
			
			//补全任务负责人资料
			$json = completing_list_shareusers('list', 2, $json, 'from_id', 5, NULL, 1, $isQuery);
		}
	}
	