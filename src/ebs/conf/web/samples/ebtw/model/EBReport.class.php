<?php
require_once 'EBModelBase.class.php';

class EBReport extends EBModelBase
{
	/**
	 * 报告编号(数字)
	 * @var string
	 */
	public $report_id;
	/**
	 * 创建者用户编号(数字)
	 * @var string
	 */
	public $report_uid;
	/**
	 * 创建者名称
	 * @var string
	 */
	public $create_name;	
	/**
	 * 已完成工作(描述)
	 * @var string
	 */
	public $completed_work;
	/**
	 * 未完成工作(描述)
	 * @var string
	 */
	public $uncompleted_work;
	/**
	 * 周期
	 * 1 日报
	 * 2 周报
	 * 3 月报
	 * 4 季报
	 * 5 年报
	 * @var int
	 */
	public $period;
	/**
	 * 开始时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $start_time;
	/**
	 * 结束时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $stop_time;
	/**
	 * 创建时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $create_time;
	/**
	 * 更新时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $last_modify_time;
	/**
	 * 修改次数
	 * @var int
	 */
	public $modify_count;
	/**
	 * 分类编号(数字)
	 * @var string
	 */
	public $class_id;
	/**
	 * 心情日历
	 * 1 伤心 
	 * 2 难过 
	 * 3 努力 
	 * 4 微笑 
	 * 5 高兴
	 * @var int
	 */
	public $self_mood;
	/**
	 * 状态
	 * 0 默认（未提交评阅）
	 * 1 提交评阅未读
	 * 2 提交评阅已读
	 * 3 评阅回复
	 * @var int
	 */
	public $status;
	/**
	 * 开放属性
	 * 0 上级
	 * 1 所有人
	 * 2 仅相关人
	 * @var int
	 */
	public $open_flag;
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::setValuesFromRequest()
	 */
	public function setValuesFromRequest($instance=NULL) {
		parent::setValuesFromRequest(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createWhereConditions()
	 */
	public function createWhereConditions($instance=NULL) {
		return parent::createWhereConditions(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createFields()
	 */
	public function createFields($instance=NULL) {
		return parent::createFields(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::removeKeepFields()
	 */
	public function removeKeepFields(&$fields) {
		parent::removeKeepFields($fields);
	
		unset($fields['report_id']);
		unset($fields['report_uid']);
		unset($fields['create_name']);
		unset($fields['create_time']);
		unset($fields['last_modify_time']);
		unset($fields['modify_count']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('report_id', 'report_uid', 'status', 'period', 'self_mood', 'class_id', 'modify_count');
		return array_merge($parentCheckDigits, $checkDigits);
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see EBModelBase::validNotEmpty()
	 */
	public function validNotEmpty($fieldNames, &$outErrMsg, $instance=NULL) {
		return parent::validNotEmpty($fieldNames, $outErrMsg, isset($instance)?$instance:$this);
	}
}