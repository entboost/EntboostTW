<?php
require_once dirname(__FILE__).'/../plan/include.php';

/**
 * 获取某个计划资料
 * @param {string} $planId 计划编号
 * @param {boolean} $fetchAuthorityInfo 是否获取权限信息，默认false
 * @return {string} JSON字符串，查询结果
 */
function get_plan($planId, $fetchAuthorityInfo=false) {
	$formObj = new EBPlanForm();
	$formObj->plan_id = $planId;
	$formObj->fetch_authority_info = empty($fetchAuthorityInfo)?0:1;
	
	$embed = 1;
	include dirname(__FILE__).'/../plan/get_one.php';
	return $json;
}

/**
 * 批量获取计划记录
 * @param {array} $planIds 计划编号
 * @param {number} (可选) $isDeleted 删除标记，默认NULL(忽略本条件)
 * @param {string} (可选) $fieldNames 指定字段名，默认NULL尽量获取全部字段
 * @return {string} JSON字符串
 */
function get_plans(array $planIds, $isDeleted=NULL, $fieldNames=NULL, $output=true) {
	$formObj = new EBPlanForm();
	$formObj->{PER_PAGE_NAME} = MAX_RECORDS_OF_LOADALL;
	
	$forCount = 0;
	$wheres = array();
	$whereType = SQLParamComb::$TYPE_AND;
	$checkDigits = $formObj->createCheckDigits();
	$instance = PlanService::get_instance();
	
	if (empty($fieldNames))
		$fieldNames = $instance->fieldNamesAfterRemovedSome(array('modify_count'));
	$tableName1 = 'eb_plan_info_t';
	
	if($isDeleted!=NULL)
		$wheres['is_deleted'] = $isDeleted;
	array_push($wheres, new SQLParamComb(array(new SQLParamComb(array('plan_id'=>$planIds), SQLParamComb_TYPE_OR)), SQLParamComb_TYPE_AND));
	include dirname(__FILE__).'/../include_list/include_list_general.php';
	
	return $json;
}

/**
 * 获取某任务的关联计划列表
 * @param {string} $taskId 任务编号
 * @param {boolean} $fetchAuthorityInfo 是否获取权限信息，默认false
 * @param {string} $orderBy 排序，默认使用创建时间倒序
 * @return {string} JSON字符串，查询结果
 */
function get_associate_plan_of_task($taskId, $fetchAuthorityInfo=false, $orderBy='create_time desc') {
	$formObj = new EBPlanForm();
	$formObj->{REQUEST_QUERY_TYPE} = 7;
	$formObj->task_id = $taskId;
	$formObj->setOrderby($orderBy);
	$formObj->fetch_authority_info = empty($fetchAuthorityInfo)?0:1;
	
	$embed = 1;
	include dirname(__FILE__).'/../plan/list.php';
	return $json;
}
