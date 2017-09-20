<?php
require_once 'EBModelBase.class.php';

/**
 * 考勤审批申请表子项
 *
 */
class EBAttendReqItem extends EBModelBase
{
	/**
	 * 考勤审批申请子项记录编号(数字)
	 * @var string
	 */
	public $att_req_item_id;
	/**
	 * 考勤审批申请记录编号(数字)
	 * @var string
	 */
	public $att_req_id;
	/**
	 * 考勤记录编号，关联编号(数字)
	 * @var string
	 */
	public $att_rec_id;
	/**
	 * 开始时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $req_start_time;
	/**
	 * 结束时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $req_stop_time;
	/**
	 * 时长，单位：分钟
	 * @var int
	 */
	public $req_duration;
	/**
	 * 创建时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $create_time;
	/**
	 * 创建标识
	 * 0=提交申请时创建
	 * 1=执行时创建
	 * @var int
	 */
	public $flag;
	
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
		
		unset($fields['att_req_item_id']);
		unset($fields['att_req_id']);
		unset($fields['att_rec_id']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('att_req_item_id', 'att_req_id', 'att_rec_id', 'req_duration', 'flag');
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