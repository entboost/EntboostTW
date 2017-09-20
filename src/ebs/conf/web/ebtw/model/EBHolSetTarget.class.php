<?php
require_once 'EBModelBase.class.php';

/**
 * 节假日适应对象表
 *
 */
class EBHolSetTarget extends EBModelBase
{
	/**
	 * 编号(数字)
	 * @var string
	 */
	public $hs_tar_id;
	/**
	 * 目标适用者
	 * 1=企业（target_id=ent_id）
	 * 2=部门/群组（target_id=group_id）
	 * 3=用户（target_id=user_id）
	 * @var int
	 */
	public $target_type;
	/**
	 * 目标适用者编号(数字)
	 * @var string
	 */
	public $target_id;
	/**
	 * 节假日设置编号，关联编号(数字)
	 * @var string
	 */
	public $hol_set_id;
	/**
	 * 创建时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $create_time;
	/**
	 * 创建者的用户编号(数字)
	 * @var string
	 */
	public $create_uid;	
	
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
	
		unset($fields['hs_tar_id']);
		unset($fields['target_type']);
		unset($fields['target_id']);
		unset($fields['create_uid']);
		unset($fields['create_time']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('hs_tar_id', 'target_type', 'target_id', 'create_uid', 'hol_set_id');
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