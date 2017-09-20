<?php
require_once dirname(__FILE__).'/../report/include.php';

/**
 * 获取某个任务资料
 * @param {string} $reportId 任务编号
 * @param {boolean} $fetchAuthorityInfo 是否获取权限信息，默认false
 * @return {string} JSON字符串，查询结果
 */
function get_report($reportId, $fetchAuthorityInfo=false) {
	$formObj = new EBReportForm();
	$formObj->report_id = $reportId;
	$formObj->fetch_authority_info = empty($fetchAuthorityInfo)?0:1;

	$embed = 1;
	include dirname(__FILE__).'/../report/get_one.php';
	return $json;
}