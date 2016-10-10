<?php
require_once dirname(__FILE__).'/../task/include.php';

/**
 * 获取某个任务资料
 * @param {string} $taskId 任务编号
 * @param {boolean} $fetchAuthorityInfo 是否获取权限信息，默认false
 * @return {string} JSON字符串，查询结果
 */
function get_task($taskId, $fetchAuthorityInfo=false) {
	$formObj = new EBTaskForm();
	$formObj->task_id = $taskId;
	$formObj->fetch_authority_info = empty($fetchAuthorityInfo)?0:1;
	
	$embed = 1;
	include dirname(__FILE__).'/../task/get_one.php';
	return $json;
}

/**
 * 获取某计划的关联任务列表
 * @param {string} $fromId 计划或上层任务编号
 * @param {number} $fromType 类型：0=新建 1=计划转任务（from_id=任务编号） 2=拆分子任务（from_id=父任务编号）
 * @param {boolean} $fetchAuthorityInfo 是否获取权限信息，默认false
 * @param {string} $orderBy 排序，默认使用创建时间倒序
 * @return {string} JSON字符串，查询结果
 */
function get_associate_task_of_plan($fromId, $fromType, $fetchAuthorityInfo=false, $orderBy='create_time desc') {
	$formObj = new EBTaskForm();
	$formObj->{REQUEST_QUERY_TYPE} = 7;
	$formObj->from_id = $fromId;
	$formObj->from_type = $fromType;
	$formObj->setOrderby($orderBy);
	
	$formObj->fetch_authority_info = empty($fetchAuthorityInfo)?0:1;
	
	$embed = 1;
	include dirname(__FILE__).'/../task/list.php';
	return $json;
}
