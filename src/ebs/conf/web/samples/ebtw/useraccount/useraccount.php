<?php
require_once dirname(__FILE__).'/../useraccount/include.php';

/**
 * 查询获取用户资料
 * @param {array} $userIds 用户编号数组
 * @return {string} JSON字符串
 */
function get_useraccounts(array $userIds) {
	$instance = UserAccountService::get_instance();
	$checkDigits = array('user_id');
	$fieldNames = $instance->fieldNamesAfterRemovedSome();
	$params = array(new SQLParamComb(array('user_id'=>$userIds), SQLParamComb::$TYPE_OR));
	
	$results = $instance->search($fieldNames, $params, $checkDigits, null, MAX_RECORDS_OF_LOADALL, 0, SQLParamComb::$TYPE_AND, $outErrMsg);
	$json = ResultHandle::listedResultToJsonAndOutput($results, false, $outErrMsg);
	return $json;
}

/**
 * 查询获取当前用户下级成员的用户资料
 * @return {JSON字符串}
 */
function get_mysubordinates() {
	$embed = 1;
	include dirname(__FILE__).'/../useraccount/mySubordinates.php';
	return $json;
}

/**
 * 查询获取当前用户下级成员的用户编号
 * @return {array} 用户编号数组
 */
function get_uid_of_mysubordinates() {
	$json = get_mysubordinates();
	$results = get_results_from_json($json, $tmpObj);
	
	$uids = array();
	if (!empty($results)) {
		foreach ($results as $group) {
			if (!empty($group->members)) {
				foreach ($group->members as $member) {
					array_push($uids, $member->user_id);
				}
			}
		}
	}
	
	return array_values(array_unique($uids));
}