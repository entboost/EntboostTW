<?php
require_once dirname(__FILE__).'/../attendance/include.php';

/**
 * 获取某个考勤审批资料
 * @param {string} $reqId 考勤审批申请编号
 * @param {boolean} $fetchAuthorityInfo 是否获取权限信息，默认false
 * @return {string} JSON字符串，查询结果
 */
function get_attend_req($reqId, $fetchAuthorityInfo=false) {
	$formObj = new EBAttendReqForm();
	$formObj->att_req_id = $reqId;
	$formObj->fetch_authority_info = empty($fetchAuthorityInfo)?0:1;

	$embed = 1;
	include dirname(__FILE__).'/../attendance/get_one_attend_req.php';
	return $json;
}
