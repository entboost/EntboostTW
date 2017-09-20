<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class AttendReqItemService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'att_req_item_id';
		$this->tableName = 'eb_attend_req_item_t';
		$this->fieldNames = 'att_req_item_id, att_req_id, att_rec_id, req_start_time, req_stop_time, req_duration, create_time, flag';
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