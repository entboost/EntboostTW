<?php
require_once 'EBModelBase.class.php';

class EBTask extends EBModelBase
{
	/**
	 * 任务编号(数字)
	 * @var string
	 */
	public $task_id;
	/**
	 * 任务名称
	 * @var string
	 */
	public $task_name;
	/**
	 * 描述
	 * @var string
	 */
	public $remark;
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
	 * 创建者用户编号(数字)
	 * @var string
	 */
	public $create_uid;
	/**
	 * 创建者名称
	 * @var string
	 */
	public $create_name;	
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
	 * 重要程度
	 * 0 普通
	 * 1 重要
	 * 2 紧急
	 * @var int
	 */
	public $important;
	/**
	 * 状态
	 * 0 未查阅
	 * 1 未开始
	 * 2 进行中（百分比）
	 * 3 已完成
	 * 4 已中止
	 * @var int
	 */
	public $status;
	/**
	 * 进度百分比 0‐100
	 * @var int
	 */
	public $percentage;
	/**
	 * 工时，单位分钟
	 * @var int
	 */
	public $work_time;
	/**
	 * 开放属性
	 * 0 上级
	 * 1 所有人
	 * 2 仅相关人
	 * @var int
	 */
	public $open_flag;
	/**
	 * 来源标识
	 * 0 新建
	 * 1 计划转任务（from_id=任务编号）
	 * 2 拆分子任务（from_id=父任务编号）
	 * @var int
	 */
	public $from_type;
	/**
	 * 来源ID(数字)，配合fromType
	 * @var string
	 */
	public $from_id;
	/**
	 * 成员IM讨论组ID(数字)
	 * @var string
	 */
	public $im_group_id;
	
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
	
		unset($fields['task_id']);
		unset($fields['create_uid']);
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
		$checkDigits = array('task_id', 'status', 'percentage', 'work_time', 'important', 'class_id', 'open_flag', 'from_type', 'from_id', 'create_uid', 'im_group_id', 'modify_count');
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