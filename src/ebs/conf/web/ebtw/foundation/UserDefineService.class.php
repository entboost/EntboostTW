<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class UserDefineService extends AbstractService {
	private static $instance  = NULL;
	
	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'ud_id';
		$this->tableName = 'eb_user_define_t';
		$this->fieldNames = 'ud_id, owner_type, owner_id, create_time, create_uid, user_type, user_id, user_name, param_int, param_str, display_index, disable';
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
	 * 获取'考勤专员'列表
	 * $entCode、$groupCodes、$ownerUserId至少填一项，它们之间是'or'关系
	 * @param {string} [可选] $entCode 企业编号
	 * @param {array} [可选] $groupCodes 群组的编号列表
	 * @param {string} [可选] $ownerUserId 用户编号
	 * @param {int} [可选] $disable 是否禁用：0=有效，1=禁用；填空忽略本条件；默认NULL
	 * @param {string} [可选] $targetUserId 目标用户的用户编号，精确查询；填空忽略本条件；默认NULL
	 * @param {string} [可选] $targetUserName 目标用户的名称，支持模糊查询；填空忽略本条件；默认NULL
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendanceManagers($entCode, $groupCodes, $ownerUserId, $disable=NULL, $targetUserId=NULL, $targetUserName=NULL) {
		if (!isset($entCode) && !isset($groupCode) && !isset($ownerUserId)) {
			log_err('getAttendanceManagers error, $entCode and $groupCode and $userId are all empty');
			return false;
		}
		
		$sql = "select t_a.*, t_d.account as user_account from eb_user_define_t t_a, user_account_t t_d where t_a.user_id = t_d.user_id";
		
		$limit = 100;
		$checkDigits =array('owner_type, owner_id, dict_type, disable');
		$orderBy = 'display_index desc';
		$params = array('user_type'=>1);
	
		//'归属'条件
		$ownerParams = array();
		if (isset($entCode))
			array_push($ownerParams, new SQLParamComb(array('owner_type'=>1, 'owner_id'=>$entCode)));
		if (!empty($groupCodes))
			array_push($ownerParams, new SQLParamComb(array('owner_type'=>2, 'owner_id'=>new SQLParam($groupCodes, 'owner_id', SQLParam::$OP_IN))));
		if (isset($ownerUserId))
			array_push($ownerParams, new SQLParamComb(array('owner_type'=>3, 'owner_id'=>$ownerUserId)));
		
		$comb = new SQLParamComb($ownerParams, SQLParamComb::$TYPE_OR);
		$params['owner'] = $comb;
		
		//是否禁止
		if (isset($disable))
			$params['disable'] = $disable;
		//'目标用户编号'条件
		if (isset($targetUserId))
			$params['t_a.user_id'] = $targetUserId;
		//'目标用户名称'条件
		if (!empty($targetUserName))
			$params['user_name'] = new SQLParam("%$targetUserName%", 't_a.user_name', SQLParam::$OP_LIKE);
		
		return $this->simpleSearch($sql, null, $params, $checkDigits, $orderBy, $limit);
	}
	
	/**
	 * 获取一个"考勤专员"的资料
	 * $entCode、$groupCodes、$ownerUserId至少填一项，它们之间是'or'关系
	 * @param {string} [可选] $entCode 企业编号
	 * @param {array} [可选] $groupCodes 群组的编号列表
	 * @param {string} [可选] $ownerUserId 归属的用户编号
	 * @param {string} $userId 考勤专员的用户编号
	 * @param (boolean} $authorityManagement 是否检查管理权限，默认false
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendanceManager($entCode, $groupCodes, $ownerUserId, $userId, $authorityManagement=false) {
		if (!isset($entCode) && !isset($groupCode) && !isset($ownerUserId)) {
			log_err('getAttendanceManager error, $entCode and $groupCode and $userId are all empty');
			return false;
		}
		if (empty($userId)) {
			log_err('getAttendanceManager error, $userId is empty');
			return false;
		}
		
		$limit = 1;
		$checkDigits =array('owner_type, owner_id, dict_type, disable, user_id');
		$params = array('user_type'=>1, 'disable'=>0, 'user_id'=>$userId);
		
		//'归属'条件
		$ownerParams = array();
		if (isset($entCode))
			array_push($ownerParams, new SQLParamComb(array('owner_type'=>1, 'owner_id'=>$entCode)));
		if (!empty($groupCodes))
			array_push($ownerParams, new SQLParamComb(array('owner_type'=>2, 'owner_id'=>new SQLParam($groupCodes, 'owner_id', SQLParam::$OP_IN))));
		if (isset($ownerUserId))
			array_push($ownerParams, new SQLParamComb(array('owner_type'=>3, 'owner_id'=>$ownerUserId)));
		
		$comb = new SQLParamComb($ownerParams, SQLParamComb::$TYPE_OR);
		$params['owner'] = $comb;
		
		$sql = "select ".$this->fieldNames." from ".$this->tableName;
		if ($authorityManagement===true)
			$sql .= " where param_int&1=1";
		
		return $this->simpleSearch($sql, null, $params, $checkDigits, null, $limit);		
	}
	
}