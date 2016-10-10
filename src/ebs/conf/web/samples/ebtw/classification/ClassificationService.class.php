<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class ClassificationService extends AbstractService
{
	private static $instance  = NULL;
	
	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'class_id';
		$this->tableName = 'eb_classification_list_t';
		$this->fieldNames = 'class_id, user_id, class_name, class_type, create_time, last_modify_time';
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