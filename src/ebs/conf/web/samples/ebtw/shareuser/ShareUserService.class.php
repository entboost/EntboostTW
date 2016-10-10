<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class ShareUserService extends AbstractService
{
	private static $instance  = NULL;
	
	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'share_id';
		$this->tableName = 'eb_share_user_t';
		$this->fieldNames = 'share_id, from_type, from_id, share_uid, share_name, share_type, create_time, read_flag, read_time, result_status, result_time, valid_flag';
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