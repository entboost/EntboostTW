<?php
require_once dirname(__FILE__).'/../shareuser/include.php';

/**
 * 补全列表数据里相关(评审人、共享人等)资料
 * @param {string} $completingMode 补全方式：list=列表方式，数据将以数组方式填入；prop=属性方式，数据将以属性方式填入(即只填一条补全数据)
 * @param {string|int} $fromType 1=计划，2=任务，3=报告
 * @param {string|object} $jsonOrObj 列表查询结果(参数引用方式)
 * @param {string} $pkFieldName (可选) 记录主键名，默认NULL；当NULL时根据$fromType进行计算
 * @param {string|int} $shareType (可选) 共享类型，默认0(全部)
 * @param {string} $shareUid (可选) 共享用户编号，默认NULL
 * @param {int} $validFlag (可选) 是否有效：0=无效，1=有效
 * @param {boolean} $isQuery 输出参数 是否执行过查询
 * @return {JSON字符串|object} 补全后的列表查询结果
 */
function completing_list_shareusers($completingMode='list', $fromType, &$jsonOrObj, $pkFieldName=NULL, $shareType=0, $shareUid=NULL, $validFlag=NULL, &$isQuery) {
	if (empty($pkFieldName))
		$pkFieldName = $fromType==1?'plan_id':($fromType==2?'task_id':'report_id');
	
	$isQuery = false;
	
	if (is_string($jsonOrObj)) {
		$results = get_results_from_json($jsonOrObj, $tmpObj); //从json字符串提取列表记录
	} else {
		$results = &$jsonOrObj->results;
	}
	if (empty($results))
		return $jsonOrObj;
	
	//编号汇成数组
	$fromIds = array();
	foreach ($results as $ptr) {
		//过滤不符合类型的文档记录
		if (isset($ptr->ptr_type) && $ptr->ptr_type!=$fromType) {
			continue;
		}
		array_push($fromIds, $ptr->{$pkFieldName});
	}
	
	if (empty($fromIds)) {
		return $jsonOrObj;
	}
	
	//执行查询(评审人、共享人等)资料
	$json1 = get_shareusers($fromType, $fromIds, isset($shareType)?$shareType:0, $shareUid, $validFlag);
	$isQuery = true;
	$results1 = get_results_from_json($json1, $tmpObj1);
	log_info('get_shareusers for fromIds:'.implode(',', $fromIds).'; result count:'.(empty($results1)?0:count($results1)) );
	
	if (empty($results1))
		return $jsonOrObj;
	
	//遍历并赋值相关字段
	foreach($results as &$ptr) {
		//过滤不符合类型的文档记录
		if (isset($ptr->ptr_type) && $ptr->ptr_type!=$fromType)
			continue;
		
		for ($i=0;$i<count($results1);$i++) {
			$ptr1 = $results1[$i];
			if ($ptr->{$pkFieldName}==$ptr1->from_id) {
				if ($completingMode=='prop') { //属性方式
					$ptr->su_share_id = $ptr1->share_id;
					$ptr->su_from_id = $ptr1->from_id;
					$ptr->su_from_type = $ptr1->from_type;
					$ptr->su_share_uid = $ptr1->share_uid;
					$ptr->su_share_name = $ptr1->share_name;
					$ptr->su_share_type = $ptr1->share_type;
					$ptr->su_valid_flag = $ptr1->valid_flag;
					break;
				} else if ($completingMode=='list') { //列表方式
					if (!isset($ptr->shares))
						$ptr->shares = array();
					$shares = &$ptr->shares;
					
					$type = $ptr1->share_type;
// 					if (!isset($shares->{$type})) {
// 						$shares->{$type} = array();
// 					} 
// 					array_push($shares->{$type}, $ptr1);
					if (is_array($shares)) {
						if (!isset($shares[$type])) {
							$shares[$type] = array();
						}
						array_push($shares[$type], $ptr1);
					} else {
						if (!isset($shares->{$type})) {
							$shares->{$type} = array();
						}
						array_push($shares->{$type}, $ptr1);
					}
				}
			}
		}
	}
	
	if (is_string($jsonOrObj))
		return json_encode($tmpObj); //重新生成JSON字符串
	else 
		return $jsonOrObj;
}

/**
 * 从JSON字符串提取指定共享类型和用户编号的关联用户数组
 * @param {string} $json JSON字符串
 * @param {int} (可选) $shareType 共享类型
 * @param {string} (可选) $shareUid 关联用户的用户编号
 * @return {array} 关联用户数组
 */
function get_shareusers_in_json($json, $shareType=NULL, $shareUid=NULL) {
	if (!empty($json)) {
		$shares = get_results_from_json($json, $tmpObj);
		//忽略查询条件，返回全部
		if (empty($shareType) && empty($shareUid))
			return $shares;
		
		$finalShares = array();
		foreach ($shares as $share) {
			if (empty($shareType)) {
				if ($share->share_uid!=$shareUid)
					continue;
				array_push($finalShares, $share);				
			} else {
				if ($share->share_type==$shareType) {
					if (!empty($shareUid) && $share->share_uid!=$shareUid)
						continue;
					array_push($finalShares, $share);
				}
			}
		}
		return $finalShares;
	}
}

/**
 * 获取(评审人、共享人等)资料，支持多个
 * @param {string|int} $fromType 1=计划，2=任务，3=报告
 * @param array $fromIds (计划、任务、报告)编号的数组
 * @param {string|int} $shareType 共享类型
 * @param {string} $shareUid (可选) 共享用户编号；0=全部类型，1=评审/评阅人（计划/报告），2=参与人（任务），3=共享人（计划/任务），4=关注人（任务），5=负责人（任务） 
 * @param {int} $validFlag 是否有效：0=无效，1=有效
 * @return {string} JSON字符串，查询结果
 */
function get_shareusers($fromType, array $fromIds, $shareType, $shareUid=NULL, $validFlag=NULL) {
	$formObj = new EBShareUserForm();
	$formObj->from_type = $fromType;
	$formObj->from_id = $fromIds;
	$formObj->share_uid = $shareUid;
	$formObj->valid_flag = $validFlag;
	$formObj->{PER_PAGE_NAME} = MAX_RECORDS_OF_LOADALL;
	if ($shareType>0)
		$formObj->share_type = $shareType;
	
	$formObj->setOrderby('share_name');
	
	$embed = 1;
	include dirname(__FILE__).'/../shareuser/list.php';
	return $json;
}

/**
 * 创建关联用户关系(评审/评阅人、共享人、参与人、负责人等)
 * @param {string|int} $fromType 1=计划，2=任务，3=报告
 * @param {string} $fromId (计划、任务、报告)的编号
 * @param {string|int} $shareType 共享类型
 * @param {string} $shareUid 共享用户编号
 * @param {string} $shareName 关联用户名称
 * @param {int} $read_flag (可选) 已读标识：0=未阅，1=已阅；如果填入1，read_time将自动被填为当前时间；默认NULL(忽略)
 * @return {json字符串} 执行结果
 */
function create_shareuser($fromType, $fromId, $shareType, $shareUid, $shareName, $read_flag=NULL) {
	$formObj = new EBShareUserForm();
	$formObj->from_type = $fromType;	
	$formObj->from_id = $fromId;
	$formObj->share_uid = $shareUid;
	$formObj->share_name = $shareName;
	$formObj->read_flag = $read_flag;
	if ($shareType>0)
		$formObj->share_type = $shareType;
	
	$embed = 1;
	include dirname(__FILE__).'/../shareuser/saveCreate.php';
	
	log_info('create_shareuser $fromType=' . $fromType . ', $fromId=' . $fromId . ', $shareUid=' . $shareUid 
			. ', $shareType='. $shareType . ', $shareName=' . $shareName . ', $read_flag='. $read_flag . ' : ' . $json);
	return $json;
}

/**
 * 删除关联用户关系(评审/评阅人、共享人、参与人、负责人等)
 * @param {string|int} $fromType
 * @param {string} $fromId
 * @param {string|int} $shareType
 * @param {string} $shareUid
 * @param {string} $shareId
 */
function delete_shareuser($fromType, $fromId, $shareType=NULL, $shareUid=NULL, $shareId=NULL) {
	$formObj = new EBShareUserForm();
	$formObj->from_type = $fromType;
	$formObj->from_id = $fromId;
	$formObj->share_uid = $shareUid;
	$formObj->share_id = $shareId;
	$formObj->share_type = $shareType;
	
	$embed = 1;
	include dirname(__FILE__).'/../shareuser/delete.php';
	
	log_info('delete_shareuser: '.$json);
	return $json;
}

/**
 * 更新关联用户(评审/评阅人、共享人、参与人、负责人等)的关系属性
 * @param {string|int} $updateType 更新类型： 2=更新valid_flag，3=更新result_status，4=更新read_flag
 * @param {mixed} $value 待更新的值
 * @param {string|int} $fromType 1=计划，2=任务，3=报告
 * @param {string} $fromId (计划、任务、报告)的编号
 * @param {string|int} $shareType 共享类型
 * @param {string} $shareUid (可选) 共享用户编号，默认NULL
 * @param {string} $shareId (可选) 主键，默认NULL
 * @param {mixed} $assistantValue (可选) 辅助参数，默认NULL； 说明：本参数可用于指定valid_flag为查询条件时的值
 * @param {string} $customParam (可选) 自定义参数：与$updateType=4匹配使用；当empty($customParam)成立时，记录一条"查阅操作"的日志。例如$customParam=1表示不记录"查阅操作"日志
 */
function update_shareuser_field($updateType, $value, $fromType, $fromId, $shareType, $shareUid=NULL, $shareId=NULL, $assistantValue=NULL, $customParam=NULL) {
	log_info('go to update_shareuser_field, update_type='.$updateType);
	$formObj = new EBShareUserForm();
	$formObj->update_type = $updateType;
	$formObj->from_type = $fromType;
	$formObj->from_id = $fromId;
	$formObj->share_uid = $shareUid;
	$formObj->share_id = $shareId;
	$formObj->share_type = $shareType;
	$formObj->custom_param = $customParam;
	
	switch ($updateType) {
		case 2:
			$formObj->valid_flag = $value;
			$formObj->valid_flag_for_query = $assistantValue;
			break;
		case 3:
			$formObj->result_status = $value;
			$formObj->valid_flag_for_query = $assistantValue;
			break;
		case 4:
			$formObj->read_flag = $value;
			$formObj->valid_flag_for_query = $assistantValue;
			break;
	}
	
	$embed = 1;
	include dirname(__FILE__).'/../shareuser/saveUpdate.php';

	log_info('update_shareuser: '.$json);
	return $json;
}