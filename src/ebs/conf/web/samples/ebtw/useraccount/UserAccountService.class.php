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

}