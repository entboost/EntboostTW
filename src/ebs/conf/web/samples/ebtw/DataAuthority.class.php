<?php
require_once dirname(__FILE__).'/dictionary.php';
require_once dirname(__FILE__).'/useraccount/useraccount.php';

class DataAuthority
{
	protected $instance;
	
	function __construct($instance=NULL) {
		$this->instance = $instance;
	}
	
	/**
	 * 整理JSON字符串，加入允许的操作标记
	 * @param {string|int|array} $fromType 文档类型(元素或数组)： 1=计划，2=任务，3=报告
	 * @param {string|int} $shareType 共享类型：1=评审/评阅人（计划/报告）2=参与人（任务）3=共享人（计划/任务）4=关注人（任务）5=负责人（任务）6=审批人（审批）
	 * @param {string|object} $jsonOrObj 列表查询结果(参数引用方式)
	 * @param {string} $userId 用户编号
	 * @param {array} $mySubordinateUids (可选) 下级人员的用户编号，默认NULL
	 * @return {JSON字符串|object} 补全后的列表查询结果
	 */
	static public function reorganizeAllowedActions($fromTypes, $shareType, &$jsonOrObj, $userId, $mySubordinateUids=NULL) {
		if (is_string($jsonOrObj)) {
			$results = get_results_from_json($jsonOrObj, $tmpObj); //从json字符串提取列表记录
		} else {
			$results = &$jsonOrObj->results;
		}
		
		if (empty($results))
			return $jsonOrObj;
		
		//传入的$fromType转换为数组结构
		$fromTypeArry = $fromTypes;
		if (!is_array($fromTypes))
			$fromTypeArry = array($fromTypes);
			
		foreach ($fromTypeArry as $fromType) {
			foreach($results as &$ptr) {
				//过滤不符合类型的文档记录
				if (isset($ptr->ptr_type) && $ptr->ptr_type!=$fromType)
					continue;
				
				//提取已加入允许的操作标记数组
				if (isset($ptr->allowedActions)) {
					//if (is_object($ptr->allowedActions)) //如果目标是对象，先转换为数组
						$ptr->allowedActions = array_values((array)$ptr->allowedActions);
					$actions = $ptr->allowedActions;
				} else {
					$actions = array();
				}
				
				//开发属性open_flag: 0=上级，1=所有人，2=仅相关人
				if ($ptr->open_flag==1) { //所有人
					array_splice($actions, count($actions), 0, array(0)); //赋予"查看"权限
				}
				
				if ($fromType==1 || $fromType==2) { //计划或任务
					if (!property_exists($ptr, 'create_uid')) {
						$ptr->allowedActions = array_values(array_unique($actions));
						continue;
					}
					
					if ($ptr->open_flag==0 && !empty($mySubordinateUids) && in_array($ptr->create_uid, $mySubordinateUids)) { //上级
						array_splice($actions, count($actions), 0, array(0)); //赋予"查看"权限
					}
					
					if ($ptr->create_uid==$userId) {
						//log_info('-----'.$userId.', $shareType='.$shareType);
						array_splice($actions, count($actions), 0, getPtrDataControlCodes($fromType, 0));
					}
				} else if ($fromType==3) { // 日报或报告
					if (property_exists($ptr, 'report_uid')) {
						if ($ptr->open_flag==0 && !empty($mySubordinateUids) && in_array($ptr->report_uid, $mySubordinateUids)) { //上级
							array_splice($actions, count($actions), 0, array(0)); //赋予"查看"权限
						}
						
						if ($ptr->report_uid==$userId) {
							array_splice($actions, count($actions), 0, getPtrDataControlCodes($fromType, 0));
						}
					} else if (property_exists($ptr, 'create_uid')) {
						if ($ptr->open_flag==0 && !empty($mySubordinateUids) && in_array($ptr->create_uid, $mySubordinateUids)) { //上级
							array_splice($actions, count($actions), 0, array(0)); //赋予"查看"权限
						}
						
						if ($ptr->create_uid==$userId) {
							array_splice($actions, count($actions), 0, getPtrDataControlCodes($fromType, 0));
						}
					} else {
						$ptr->allowedActions = array_values(array_unique($actions));
						continue;
					}
				}
				
				if (!property_exists($ptr, 'shares')/*!property_exists($ptr, 'su_share_id') || */) {
					$ptr->allowedActions = array_values(array_unique($actions));
					continue;
				}
				
				if (is_array($ptr->shares)) {
					if (array_key_exists($shareType, $ptr->shares))
						$shares = $ptr->shares[(string)$shareType];
				} else if (is_object($ptr->shares)) {
					if (property_exists($ptr->shares, $shareType))
						$shares = $ptr->shares->{$shareType};
				}
				
				if (empty($shares)) {
					$ptr->allowedActions = array_values(array_unique($actions));
					continue;
				}
				
				$finalShares = array();
				foreach ($shares as $share) {
					if ($share->share_uid==$userId)
						array_push($finalShares, $share);
				}
				if (empty($finalShares)) {
					$ptr->allowedActions = array_values(array_unique($actions));
					//log_info('$share continue allowedActions:'.implode(',',$ptr->allowedActions));
					continue;
				}
				
				//匹配关联关系和追加权限代码
				foreach ($shares as $share) {
					if ($share->share_uid==$userId/* && $share->share_id==$ptr->su_share_id*/) {
						array_splice($actions, count($actions), 0, getPtrDataControlCodes($fromType, $shareType));
						break;
					}
				}
				$ptr->allowedActions = array_values(array_unique($actions));
			}
		}
		
		if (is_string($jsonOrObj))
			return json_encode($tmpObj); //重新生成JSON字符串
		else 
			return $jsonOrObj;
	}
	
	/**
	 * 验证是否存在符合条件的记录(通常用于检验是否有数据权限)
	 * @param boolean|array $outExistRows false查询失败或查询结果数组
	 * @param string $fieldNames 字段名，例如:'a, b'
	 * @param array $wheres 查询条件数组
	 * @param array $checkDigits 数字校验数组
	 * @param AbstractService $instance 执行任务的服务对象
	 * @param int $limit 返回的最大记录数量，默认1
	 * @param int $whereType 查询条件连接类型，默认SQLParamComb_TYPE_AND
	 * @param boolean $output 是否输出到页面
	 * @param string $outErrMsg 输出参数 检测错误信息
	 * @param string $json 输出参数 JSON字符串，验证结果
	 * @return boolean 验证结果：false=不通过，true=通过
	 */
	static public function isRowExists(&$outExistRows, $fieldNames, array $wheres, $checkDigits=NULL, $instance=NULL, $limit=1, $whereType=SQLParamComb_TYPE_AND, $output=true, &$outErrMsg=NULL, &$json=NULL) {
		if (!isset($instance)) {
			$outErrMsg = 'isRowExists->$instance is null';
			$json = ResultHandle::errorToJsonAndOutput($outErrMsg, $outErrMsg, $output);
			return false;
		}
		
		//查询获取第一条记录
		$outExistRows = $instance->search($fieldNames, $wheres, $checkDigits, NULL, $limit, 0, $whereType, $outErrMsg);
		
		$json = ResultHandle::failureResultToJson($outExistRows, EBStateCode::$EB_STATE_ERROR, $outErrMsg);
		if (!empty($json)) {
			if ($output)
				echo $json;
			return false;
		}
		if (!is_array($outExistRows) || empty($outExistRows)) {
			//$json = ResultHandle::noAuthErrToJsonAndOutput($output, EBStateCode::$EB_STATE_NOT_AUTH_ERROR, $outErrMsg);
			$outErrMsg = 'row is not exists';
			$json = ResultHandle::errorToJsonAndOutput($outErrMsg, $outErrMsg, $output);
			return false;
		}
		return true;
	}
	
	/**
	 * 验证是否有数据权限
	 * @param {string|int} $actionType 操作类型代码
	 * @param {string|int} $fromType 1=计划，2=任务，3=报告
	 * @param {string|int} $shareType 共享类型，默认0(全部)
	 * @param {string} $shareUid 共享用户编号
	 * @param boolean|array $outExistRows false查询失败或查询结果数组
	 * @param string $fieldNames 字段名，例如:'a, b'
	 * @param array $wheres 查询条件数组
	 * @param array $checkDigits 数字校验数组
	 * @param AbstractService $instance 执行任务的服务对象
	 * @param int $limit 返回的最大记录数量，默认1
	 * @param int $whereType 查询条件连接类型，默认SQLParamComb_TYPE_AND
	 * @param boolean $output 是否输出到页面
	 * @param string $outErrMsg 输出参数 检测错误信息
	 * @param string $json 输出参数 JSON字符串，验证结果
	 * @return {boolean} 验证结果：false=不通过，true=通过
	 */
	static public function isAuthority($actionType, $ptrType, $shareType=0, $shareUid, &$outExistRows, $fieldNames, array $wheres, $checkDigits=NULL, $instance=NULL, $limit=1, $whereType=SQLParamComb_TYPE_AND, $output=true, &$outErrMsg=NULL, &$json=NULL) {
		$resultOfExists = self::isRowExists($outExistRows, $fieldNames, $wheres, $checkDigits, $instance, $limit, $whereType, $output, $outErrMsg, $json);
		if (!$resultOfExists) {
			log_err('$userId='.$shareUid.' has not authority to operation '.$actionType.', ptrType='.$ptrType);
			return false;
		}
		
		$tmpObj = new stdClass();
		$tmpObj->code = 0;
		//每行记录从数组格式转为对象格式
		foreach ($outExistRows as &$row) {
			$row = (Object)$row;
		}
		$tmpObj->results = $outExistRows;
		$validFlag = 1;
		completing_list_shareusers('list', $ptrType, $tmpObj, NULL, $shareType, $shareUid, $validFlag, $isQuery); //补全关联用户资料
		
		//获取当前用户下级成员的用户编号
		$mySubordinateUids = get_uid_of_mysubordinates();
		//获取当前用户对于这些记录拥有的权限代码
		if ($shareType!=0) {
			self::reorganizeAllowedActions($ptrType, $shareType, $tmpObj, $shareUid, $mySubordinateUids);
		} else {
			self::reorganizeAllowedActions($ptrType, 1, $tmpObj, $shareUid, $mySubordinateUids);
			self::reorganizeAllowedActions($ptrType, 2, $tmpObj, $shareUid, $mySubordinateUids);
			self::reorganizeAllowedActions($ptrType, 3, $tmpObj, $shareUid, $mySubordinateUids);
			self::reorganizeAllowedActions($ptrType, 4, $tmpObj, $shareUid, $mySubordinateUids);
			self::reorganizeAllowedActions($ptrType, 5, $tmpObj, $shareUid, $mySubordinateUids);
		}
		
		//log_info($tmpObj);
		//检测是否有指定的数据操作权限
		if (!in_array($actionType, $outExistRows[0]->allowedActions)) {
			log_err('$shareUid='.$shareUid.' has not authority to operation '.$actionType.', $ptrType='.$ptrType.'; row:'.json_encode($outExistRows[0]));
			return false;
		}
		return true;
	}
	
	/**
	 * 使用表连接方式查询验证是否存在符合条件的记录(通常用于检验是否有数据权限)
	 * @param boolean|array $outExistRows false查询失败或查询结果数组
	 * @param array $tableNameAlias 表别名数组，例如：array('表A'=>'t_a', '表B'=>'t_b', '表C'=>'t_c')
	 * @param string $prefixSql SQL语句前段
	 * @param array $conditions 查询条件数组(已和问号占位符匹配)，例如array(123, 'absdfd')
	 * @param array $paramsGroup 查询条件数组，与$tableNameAlias一一对应
	  	例如： array( '表A'=>array(
	 	 'a'=>123,
	 	 'b'=>'文档xx',
	 	 'c'=>new SQLParam('防fd', 'c', 'like'),
	 	 'time_s'=>SQLParam('2016-01-01 00:00:00', 'time', '>='),
	 	 new SQLParamComb(array('important'=>0, 'c'=>new SQLParam('防fd', 'c', 'like')), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('d'=>array(2,3)), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('status'=>0, 'is_deleted'=>0, new SQLParamComb(array('important'=>0, 'open_flag'=>0), SQLParamComb::$TYPE_OR)), SQLParamComb::$TYPE_AND)
	 	 ), '表B'=>array(...) )
	 * @param array $checkDigitsGroup 数字检测开关(多个)，与$tableNameAlias一一对应，例如：array('表A'=>array('a字段名', 'b字段名'), '表B'=>array(...))
	 * @param AbstractService $instance 执行任务的服务对象
	 * @param int $limit 单页最大记录数量，默认1
	 * @param string $whereType SQLParamComb::$TYPE_AND, SQLParamComb::TYPE_OR 		默认:$TYPE_AND
	 * @param boolean $output 是否输出到页面
	 * @param string $outErrMsg 输出参数 检测错误信息
	 * @param string $json 输出参数 JSON字符串，验证结果
	 * @return boolean 验证结果：false=不通过，true=通过
	 */
	static public function isRowExists_usingJoinSearch(&$outExistRows, $tableNameAlias, $prefixSql, $conditions, $paramsGroup, $checkDigitsGroup, $instance=NULL, $limit=1, $whereType=SQLParamComb_TYPE_AND, $output=true, &$outErrMsg=NULL, &$json=NULL) {
		if (!isset($instance)) {
			$outErrMsg = 'isRowExists_usingJoinSearch->$instance is null';
			$json = ResultHandle::errorToJsonAndOutput($outErrMsg, $outErrMsg, $output);
			return false;
		}
		
		//执行查询
		$outExistRows = $instance->joinSearch($tableNameAlias, $prefixSql, $conditions, $paramsGroup, $checkDigitsGroup, NULL, $limit, 0, $whereType, $outErrMsg);
		
		$json = ResultHandle::failureResultToJson($outExistRows, EBStateCode::$EB_STATE_ERROR, $outErrMsg);
		if (!empty($json)) {
			if ($output)
				echo $json;
			return false;
		}
		if (!is_array($outExistRows) || empty($outExistRows)) {
			//$json = ResultHandle::noAuthErrToJsonAndOutput($output, EBStateCode::$EB_STATE_NOT_AUTH_ERROR, $outErrMsg);
			$outErrMsg = 'row is not exists';
			$json = ResultHandle::errorToJsonAndOutput($outErrMsg, $outErrMsg, $output);
			return false;
		}
		return true;
	}
	
}