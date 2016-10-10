<?php
require_once 'EBModelBase.class.php';

class EBPlan extends EBModelBase
{
	/**
	 * 计划编号(数字)
	 * @var string
	 */
	public $plan_id;
	/**
	 * 计划名称
	 * @var string
	 */
	public $plan_name;
	/**
	 * 详细内容
	 * @var string
	 */
	public $remark;
	/**
	 * 周期
	 * 1 日计划
	 * 2 周计划
	 * 3 月计划
	 * 4 季计划
	 * 5 年计划
	 * 6 自定义计划
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
	 * 0 新建未阅
	 * 1 未处理
	 * 2 申请评审未阅
	 * 3 申请评审已阅
	 * 4 评审通过
	 * 5 评审拒绝
	 * 6 已完成（已结束）
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
	 * 删除标识
	 * 0 正常数据
	 * 1 删除放入回收站
	 * @var int
	 */
	public $is_deleted;
	/**
	 * 来源标识
	 * 0 新建
	 * 1 报告创建计划（fromId=报告编号）
	 * @var int
	 */
	public $from_type;
	/**
	 * 来源ID(数字)，配合fromType
	 * @var string
	 */
	public $from_id;
	
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
		
		unset($fields['plan_id']);
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
		$checkDigits = array('plan_id', 'period', 'status', 'is_deleted', 'important', 'class_id', 'open_flag', 'from_type', 'from_id', 'create_uid', 'modify_count');
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