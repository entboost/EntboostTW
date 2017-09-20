<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class TempDataService extends AbstractService
{
	private static $instance  = NULL;
	
	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'temp_key';
		$this->tableName = 'temp_data_t';
		$this->fieldNames = 'temp_key, int_value, str_value, create_time';
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