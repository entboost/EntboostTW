<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class PlanService extends AbstractService
{
	private static $instance  = NULL;
	
	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'plan_id';
		$this->tableName = 'eb_plan_info_t';
		$this->fieldNames = 'plan_id, plan_name, remark, period, start_time, stop_time, create_uid, create_name, create_time, last_modify_time, modify_count, class_id, important, status, open_flag, is_deleted, from_type, from_id';
	}
	
	/**
	 * 获取单例对象，PHP的单例对象只相对于当次而言
	 */
	public static function get_instance() {
		if(self::$instance==NULL)
			self::$instance = new self;
		return self::$instance;
	}
	
}