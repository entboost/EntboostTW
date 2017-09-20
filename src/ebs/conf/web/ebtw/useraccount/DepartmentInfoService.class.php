<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

/**
 * 部门、群组表访问类
 *
 */
class DepartmentInfoService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'group_id';
		$this->tableName = 'department_info_t';
		$this->fieldNames = 'group_id, dep_name, parent_gid, ent_id, manager_uid, type, create_uid';
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
	 * 获取部门或群组信息列表
	 * @param {string} $entCode 企业编号
	 * @param {int} $type [可选] 0=公司部门，1=项目组，2=个人群组，9=临时讨论组；默认0
	 * @param {string} $managerUid [可选] 部门经理的用户编号；默认NULL
	 * @param {string} $groupName [可选] 部门或群组名称，模糊查询
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getGroupInfos($entCode, $type=0, $managerUid=NULL, $groupName=NULL) {
		if (!in_array($type, array(0,1,2,9))) {
			log_err('getGroupInfos error, type is not matched');
			return false;
		}
		
		$limit = 100;
		$orderBy = 'dep_name';
		$checkDigits = array('ent_id', 'type', 'manager_uid');
		$params = array('ent_id'=>$entCode);
		if (isset($type))
			$params['type'] = $type;
		if (isset($managerUid))
			$params['manager_uid'] = $managerUid;
		if (isset($groupName))
			$params['dep_name'] = new SQLParam("%$groupName%", 'dep_name', SQLParam::$OP_LIKE);
		
		return $this->search($this->fieldNames, $params, $checkDigits, $orderBy, $limit);
	}
	
	/**
	 * 查询一个用户所在部门或群组的列表
	 * @param {string} $entCode 企业编号
	 * @param {array} $empUids 用户编号列表
	 * @param {int} $type [可选] 0=公司部门，1=项目组，2=个人群组，9=临时讨论组；默认0
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getGroupInfosByUserid($entCode, $empUids, $type=0) {
		if (empty($empUids)) {
			log_err('getGroupInfosByUserid error, $empUids is empty');
			return false;
		}
		if (!in_array($type, array(0,1,2,9))) {
			log_err('getGroupInfosByUserid error, type is not matched');
			return false;
		}
		/*
		select t_a.group_id, t_a.dep_name, t_a.type as group_type, t_a.ent_id, t_b.emp_uid as user_id, t_b.username as user_name, t_b.emp_id from department_info_t t_a, employee_info_t t_b
		where t_a.group_id = t_b.group_id and t_a.ent_id = 1000000000000030 and t_a.type = 0 and t_b.emp_uid in (888000, 80)
		order by t_a.dep_name
		 */
		$sql = "select t_a.group_id, t_a.dep_name, t_a.type as group_type, t_a.ent_id, t_b.emp_uid as user_id, t_b.username as user_name, t_b.emp_id from department_info_t t_a, employee_info_t t_b "
				."where t_a.group_id = t_b.group_id and t_a.ent_id = $entCode and t_a.type = $type";
		
		$params = array('emp_uid'=>new SQLParam($empUids, 't_b.emp_uid', SQLParam::$OP_IN));
		
		$limit = 1000;
		$orderBy = "t_a.dep_name";
		return $this->simpleSearch($sql, null, $params, null, $orderBy, $limit);
	}
	
	/**
	 * 获取企业信息
	 * @param {string} $entCode 企业编号
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getEnterpriseInfo($entCode) {
		$sql = "select * from enterprise_info_t where ent_id = $entCode";
		return $this->simpleSearch($sql);
	}
}