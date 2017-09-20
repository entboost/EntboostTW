<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class OperateRecordService extends AbstractService
{
	private static $instance  = NULL;
	
	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'op_id';
		$this->tableName = 'eb_operate_record_t';
		$this->fieldNames = 'op_id, is_deleted, from_type, from_id, from_name, user_id, user_name, op_type, op_data, op_name, op_time, remark, create_time, last_modify_time, modify_count, is_deleted';
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