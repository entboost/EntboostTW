<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class DictionaryInfoService extends AbstractService {
	private static $instance  = NULL;
	
	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'dict_id';
		$this->tableName = 'eb_dictionary_info_t';
		$this->fieldNames = 'dict_id, owner_type, owner_id, create_time, create_uid, dict_type, dict_name, param_int, param_str, display_index, disable';
	}
	
	/**
	 * 获取单例对象，PHP的单例对象只相对于当次而言
	 */
	public static function get_instance() {
		if(self::$instance==NULL)
			self::$instance = new self;
			return self::$instance;
	}
	
	/**
	 * 获取'请假类型'列表
	 * $entCode、$groupCodes、$userId至少填一项，它们之间是'or'关系
	 * @param {string} [可选] $entCode 企业编号
	 * @param {array} [可选] $groupCodes 群组的编号列表
	 * @param {string} [可选] $userId 用户编号
	 * @param {int} [可选] $disable 是否禁用：0=有效，1=禁用；填空忽略本条件；默认NULL
	 * @param {string} [可选] $dictName 请假类型名称；填空忽略本条件；默认NULL
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getHolidayInfos($entCode, $groupCodes, $userId, $disable=NULL, $dictName=NULL) {
		if (!isset($entCode) && !isset($groupCode) && !isset($userId)) {
			log_err('getHolidayInfos error, $entCode and $groupCode and $userId are all empty');
			return false;
		}
		
		$limit = 100;
		$checkDigits =array('owner_type, owner_id, dict_type, disable');
		$orderBy = 'display_index desc';
		$params = array('dict_type'=>1);
		
		$ownerParams = array();
		if (isset($entCode))
			array_push($ownerParams, new SQLParamComb(array('owner_type'=>1, 'owner_id'=>$entCode)));
		if (!empty($groupCodes))
			array_push($ownerParams, new SQLParamComb(array('owner_type'=>2, 'owner_id'=>new SQLParam($groupCodes, 'owner_id', SQLParam::$OP_IN))));
		if (isset($userId))
			array_push($ownerParams, new SQLParamComb(array('owner_type'=>3, 'owner_id'=>$userId)));
		
		$comb = new SQLParamComb($ownerParams, SQLParamComb::$TYPE_OR);
		$params['owner'] = $comb;
		
		//是否禁止
		if (isset($disable))
			$params['disable'] = $disable;
		//请假类型名称
		if (isset($dictName))
			$params['dict_name'] = $dictName;
		
		$result = $this->search($this->fieldNames, $params, $checkDigits, $orderBy, $limit);
		return $result;		
	}
}