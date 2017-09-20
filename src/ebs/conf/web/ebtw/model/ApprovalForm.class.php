<?php
require_once 'EBModelBase.class.php';

class ApprovalForm extends EBModelBase
{
	/**
	 * 评审/评阅操作类型
	 * 1：提交
	 * 2：通过
	 * 3：拒绝
	 * @var int
	 */
	public $approval_action;
	/**
	 * 来源标识
	 * 1：计划（from_id=plan_id）
	 * 2：任务（from_id=task_id）
	 * 3：报告（from_id=report_id）
	 * @var int
	 */
	public $from_type;
	/**
	 * 来源ID(数字)，配合fromType
	 * @var string
	 */
	public $from_id;
	/**
	 * 评审人/评阅人用户编号
	 * @var string
	 */
	public $approval_user_id;
	/**
	 * 评审人/评阅人用户名称
	 * @var string
	 */
	public $approval_user_name;
	/**
	 * 备注
	 * @var string
	 */
	public $remark;
	/**
	 * 自定义属性，用于标识一些细微的操作特性
	 * @var string
	 */
	public $custom_param;
	
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
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		return parent::createCheckDigits(isset($instance)?$instance:$this);
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