<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class UserAccountService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'user_id';
		$this->tableName = 'user_account_t';
		$this->fieldNames = 'user_id, account, username';
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
	 * 获取指定某个部门经理的(部门/群组)成员的用户编号列表
	 * @param {string} [可选] $entCode 企业编号
	 * @param {array} [可选] $groupCodes 群组的编号列表
	 * @param {string} $managerUid 部门经理的用户编号
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getMemberUidsByManagerUid($entCode, $groupCodes, $managerUid) {
		if (!isset($entCode) && !isset($groupCodes)) {
			log_err('getGroupsByManagerUid error, $entCode and $groupCodes are all empty');
			return false;
		}
		if (empty($managerUid)) {
			log_err('getGroupsByManagerUid error, $userId is empty');
			return false;
		}
		//select distinct t_b.emp_uid as user_id from department_info_t t_a join employee_info_t t_b on (t_a.group_id=t_b.group_id)
		//where (t_a.ent_id = 1000000000000030 or t_a.group_id in (999001)) and manager_uid=888002
		
		$sql = "select distinct t_b.emp_uid as user_id from department_info_t t_a join employee_info_t t_b on (t_a.group_id=t_b.group_id) ";
		
		$checkDigits =array('owner_type, owner_id, manager_uid');
		$params = array('manager_uid'=>$managerUid);
		
		//'归属'条件
		$ownerParams = array();
		if (isset($entCode))
			array_push($ownerParams, new SQLParam($entCode, 't_a.ent_id', SQLParam::$OP_EQ));
		if (!empty($groupCodes))
			array_push($ownerParams, new SQLParam($groupCodes, 't_a.group_id', SQLParam::$OP_IN));
		
		$params['owner'] = new SQLParamComb($ownerParams, SQLParamComb::$TYPE_OR);
		
		$limit =1000;
		$result = $this->simpleSearch($sql, null, $params, $checkDigits, null, $limit);
		return $result;
	}
	
	/**
	 * 查询部门的成员列表
	 * @param {string} $entCode 企业编号
	 * @param {string} $groupId [可选] 部门编号
	 * @param {string} $memberName [可选] 成员名称，支持模糊查询
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getMembers($entCode, $groupId=NULL, $memberName=NULL) {
		if (!isset($entCode)) {
			log_err('getMembersByGroupId error, $entCode is empty');
			return false;
		}
		/*
		select t_a.* from employee_info_t t_a, department_info_t t_b
		where t_a.group_id = t_b.group_id and ent_id = 1000000000000030 and t_a.group_id = 999001
		order by t_a.username
		 */
		$sql = "select t_a.* from employee_info_t t_a, department_info_t t_b where t_a.group_id = t_b.group_id";
		
		$params = array('ent_id'=>$entCode);
		if (isset($groupId))
			$params['group_id'] = new SQLParam($groupId, 't_a.group_id');
		if (isset($memberName))
			$params['username'] = new SQLParam("%$memberName%", 't_a.username', SQLParam::$OP_LIKE);
		
		$orderBy = 't_a.username';
		$checkDigits = array('ent_id', 'group_id');
		$limit = 100;
		
		return $this->simpleSearch($sql, null, $params, $checkDigits, $orderBy, $limit);
	}
	
	/**
	 * 获取用户资料
	 * @param {string} $entCode 企业编号
	 * @param {string} $userName [可选] 用户名称，支持模糊查询
	 * @return boolean
	 */
	function getUsers($entCode, $userName=NULL) {
		if (!isset($entCode)) {
			log_err('getUsers error, $entCode is empty');
			return false;
		}
		/*
		select distinct t_a.emp_uid, t_a.username from employee_info_t t_a, department_info_t t_b
		where t_a.group_id = t_b.group_id and ent_id = 1000000000000030
		order by t_a.username
		 */
		$sql = "select distinct t_a.emp_uid, t_a.username, t_d.account as user_account from employee_info_t t_a, department_info_t t_b, user_account_t t_d "
				."where t_a.group_id = t_b.group_id and t_a.emp_uid=t_d.user_id "
				;
		
		$params = array('ent_id'=>$entCode);
		if (isset($userName))
			$params['username'] = new SQLParam("%$userName%", 't_a.username', SQLParam::$OP_LIKE);
		
		$orderBy = 't_a.username';
		$checkDigits = array('ent_id');
		$limit = 100;
		
		return $this->simpleSearch($sql, null, $params, $checkDigits, $orderBy, $limit);
	}
}