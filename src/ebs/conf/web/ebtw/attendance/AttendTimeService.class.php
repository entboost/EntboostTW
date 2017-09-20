<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class AttendTimeService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'att_tim_id';
		$this->tableName = 'eb_attend_time_t';
		$this->fieldNames = 'att_tim_id, name, signin_time, signin_ignore, signout_time, signout_ignore, rest_duration, work_duration, create_time';
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