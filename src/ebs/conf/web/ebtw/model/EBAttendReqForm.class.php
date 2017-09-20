<?php
require_once 'EBAttendReq.class.php';
/**
 * 考勤审批申请-表单自动绑定类
 */
class EBAttendReqForm extends EBAttendReq
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
	/**
	 * 考勤状态
	 * @var int
	 */
	public $search_rec_state;
	/**
	 * 审批人是否有效
	 * @var int
	 */
	public $valid_flag;
	/**
	 * 是否只选择异常状态的考勤记录
	 * @var int
	 */
	public $abnormal_rec_state;
	/**
	 * 查询部门编号
	 * @var string
	 */
	public $search_group_id;
	/**
	 * 被查询员工的用户编号
	 * @var string
	 */
	public $search_user_id;
	
	/**
	 * {@inheritDoc}
	 * @see EBPlan::getOrderby()
	 */
	public function getOrderby() {
		$orderby = parent::getOrderby();
		return $orderby;
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBAttendReq::setValuesFromRequest()
	 */
	public function setValuesFromRequest($instance=NULL) {
		parent::setValuesFromRequest(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBAttendReq::createWhereConditions()
	 */
	public function createWhereConditions($instance=NULL) {
		return parent::createWhereConditions(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBAttendReq::removeKeepFields()
	 */
	public function removeKeepFields(&$fields) {
		parent::removeKeepFields($fields);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBAttendReq::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array();
		return array_merge($parentCheckDigits, $checkDigits);
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see EBAttendReq::validNotEmpty()
	 */
	public function validNotEmpty($fieldNames, &$outErrMsg, $instance=NULL) {
		return parent::validNotEmpty($fieldNames, $outErrMsg, $this, isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBShareUser::validFormFields()
	 */
	public function validFormFields(&$outJson, $output=true) {
		if (!parent::validFormFields($outJson, $output)) {
			return false;
		}
		
		if (isset($this->search_time_s) && !validateDateTimeString($this->search_time_s)) {
			$outErrMsg = 'search_time_s';
			$outJson = ResultHandle::validNotMatchedErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->search_time_e) && !validateDateTimeString($this->search_time_e)) {
			$outErrMsg = 'search_time_e';
			$outJson = ResultHandle::validNotMatchedErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		
		if (isset($this->search_rec_state) && !$this->validDigit('search_rec_state', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->valid_flag) && !$this->validDigit('valid_flag', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}	
		if (isset($this->abnormal_rec_state) && !$this->validDigit('abnormal_rec_state', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->search_group_id) && !$this->validDigit('search_group_id', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->search_user_id) && !$this->validDigit('search_user_id', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		
		return true;
	}
}