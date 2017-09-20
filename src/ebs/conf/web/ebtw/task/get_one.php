<?php
include dirname(__FILE__).'/../task/preferences.php';
require_once dirname(__FILE__).'/../task/include.php';

	$LOCAL_ACTION_TYPE = 0; //查看操作

	//$embed标记当前php脚本是否被嵌入其它脚本
	$output = !isset($embed);
	
	if (empty($formObj)) {
		$formObj = new EBTaskForm();
		$formObj->setValuesFromRequest();
	}
	
	//验证必要条件
	//$pid = get_request_param($PTRIdFieldName);
	$pid = $formObj->{$PTRIdFieldName};
	if (empty($pid)) {
		$json = ResultHandle::missedPrimaryKeyErrToJsonAndOutput($PTRIdFieldName, $output);
		return;
	}
	
	$wheres = array();
	$checkDigits = array();
	$wheres[$PTRIdFieldName] = new SQLParam($pid, $PTRIdFieldName);
	array_push($checkDigits, $PTRIdFieldName);//追加数字校验条件
	$userId = $_SESSION[USER_ID_NAME];
	$instance = TaskService::get_instance();
	
	//定义输出字段
	$fieldNames = $instance->fieldNamesAfterRemovedSome(array('modify_count'));
	
	//验证对本记录是否有操作权限
	$shareType = 0;
	if (!DataAuthority::isAuthority($LOCAL_ACTION_TYPE, $PTRType, $shareType, $userId, $existRows, $fieldNames, $wheres, $checkDigits, $instance, 1, SQLParamComb_TYPE_AND, false, $outErrMsg, $json)) {
		if (!empty($json)) {
			if ($output) echo $json;
			return;
		}
		$json = ResultHandle::noAuthErrToJsonAndOutput($output);
		return;
	}
	
	//执行查询
	$result = $instance->search($fieldNames, $wheres, $checkDigits, null, 1, 0, SQLParamComb::$TYPE_AND, $outErrMsg);
	//处理查询结果
	$json = ResultHandle::listedResultToJsonAndOutput($result, false, $outErrMsg);
	$objs = get_results_from_json($json, $tmpObj);
	if (!empty($objs)) {
		if (/*!$output && */!isset($formObj->fetch_authority_info) || $formObj->fetch_authority_info) {
			//查询关联人员(负责人、参与人、共享人、关注人等)资料
			$shareType = 0;
			$json = completing_list_shareusers('list', $PTRType, $json, NULL, $shareType, NULL, 1, $isQuery);
			
			
			//整理数据操作权限
			//$json = completing_list_shareusers('prop', $PTRType, $json, NULL, 0, $userId, 1, $isQuery);
			//验证当前用户$shareType的权限
			$json = DataAuthority::reorganizeAllowedActions($PTRType, 2, $json, $userId);
			$json = DataAuthority::reorganizeAllowedActions($PTRType, 3, $json, $userId);
			$json = DataAuthority::reorganizeAllowedActions($PTRType, 4, $json, $userId);
			$json = DataAuthority::reorganizeAllowedActions($PTRType, 5, $json, $userId);
		}
		
		get_first_entity_from_json($json, $entity, $tmpObj);
		
		//获取创建人的账号
		$createUid = $entity->create_uid;
		$json1 = get_useraccounts(array($createUid));
		if (!empty($json1)) {
			$userAccount = get_first_entity_from_json($json1, $entity1, $tmpObj1);
			$entity->create_account = $userAccount->account;
		}
	}
	
	if ($output)
		echo $json;
	