<?php
require_once 'EBAttendRecord.class.php';
/**
 * 考勤审批申请-表单自动绑定类
 */
class EBAttendRecordForm extends EBAttendRecord
{
	/**
	 * 查询时间范围-起点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $search_time_s;
	/**
	 * 查询时间范围-终点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $search_time_e;
}