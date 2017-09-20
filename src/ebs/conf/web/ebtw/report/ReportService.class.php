<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class ReportService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'report_id';
		$this->tableName = 'eb_report_info_t';
		$this->fieldNames = 'report_id, completed_work, uncompleted_work, start_time, stop_time, report_uid, create_name, create_time, last_modify_time, modify_count, class_id, period, status, open_flag, self_mood';
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