<?php
require_once 'EBModelBase.class.php';

class EBEmployeeInfo extends EBModelBase
{
	/**
	 * 成员编号(数字)
	 * @var string
	 */
	public $emp_id;
	/**
	 * 用户编号(数字)
	 * @var string
	 */
	public $emp_uid;
	/**
	 * 部门或群组编号(数字)
	 * @var string
	 */
	public $group_uid;	
	/**
	 * 成员名称
	 * @var string
	 */
	public $username;
	/**
	 * 创建时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $create_time;
	
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
	
		unset($fields['emp_id']);
		unset($fields['create_time']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('emp_id, emp_uid, group_uid');
		return array_merge($parentCheckDigits, $checkDigits);
	}
}