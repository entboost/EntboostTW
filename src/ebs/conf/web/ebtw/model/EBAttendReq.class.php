<?php
require_once 'EBModelBase.class.php';

/**
 * 考勤审批申请表
 *
 */
class EBAttendReq extends EBModelBase
{
	/**
	 * 考勤审批申请记录编号(数字)
	 * @var string
	 */
	public $att_req_id;
	/**
	 * 所有者类型
	 * 1=企业（owner_id=ent_id）
	 * 2=部门/群组（owner_id=group_id）
	 * @var int
	 */
	public $owner_type;
	/**
	 * 所有者编号(数字)
	 * @var string
	 */
	public $owner_id;
	/**
	 * 用户编号(数字)
	 * @var string
	 */
	public $user_id;
	/**
	 * 用户名称
	 * @var string
	 */
	public $user_name;
	/**
	 * 创建时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $create_time;
	/**
	 * 最后修改时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $last_time;
	/**
	 * 考勤日期，补签/外勤申请使用；格式如：2016-01-20
	 * @var string
	 */
	public $attend_date;
	/**
	 * 开始时间，请假/加班申请使用；格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $start_time;
	/**
	 * 结束时间，请假/加班申请使用；格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $stop_time;
	/**
	 * 申请出勤时长，单位：分钟
	 * @var int
	 */
	public $req_duration;
	/**
	 * 审批类型
	 * 0=未申请（默认）
	 * 1=补签
	 * 2=外勤
	 * 3=请假
	 * 4=加班
	 * @var int
	 */
	public $req_type;
	/**
	 * 状态
	 * 0=默认状态
	 * 1=审批中
	 * 2=审批通过
	 * 3=审批不通过
	 * 4=审批回退
	 * @var int
	 */
	public $req_status;
	/**
	 * 名称
	 * @var string
	 */
	public $req_name;
	/**
	 * 内容
	 * @var string
	 */
	public $req_content;
	/**
	 * 保留字段
	 * @var int
	 */
	public $req_param_int;
	
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
	
		unset($fields['att_req_id']);
		unset($fields['owner_type']);
		unset($fields['owner_id']);
		unset($fields['user_id']);
		unset($fields['user_name']);
		unset($fields['create_time']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('att_req_id', 'owner_type', 'owner_id', 'user_id', 'req_type', 'req_duration', 'req_status', 'req_param_int');
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
	
	/**
	 * 验证表单字段逻辑合法性(只校验非空值的字段)
	 * @param {string} $outJson 输出参数 验证不通过结果字符串(json封装)
	 * @param {boolean} $output	是否输出到页面，默认true
	 * @return {boolean} 验证结果：true=通过，false=不通过
	 */
	public function validFormFields(&$outJson, $output=true) {
		if (isset($this->att_req_id) && !$this->validDigit('att_req_id', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->owner_type) && !$this->validDigit('owner_type', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->owner_id) && !$this->validDigit('owner_id', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->user_id) && !$this->validDigit('user_id', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->req_duration) && !$this->validDigit('req_duration', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->req_type) && !$this->validDigit('req_type', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->req_status) && !$this->validDigit('req_status', $outErrMsg, $this)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}		
		
		if (isset($this->create_time) && !validateDateTimeString($this->create_time)) {
			$outErrMsg = 'create_time';
			$outJson = ResultHandle::validNotMatchedErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->last_time) && !validateDateTimeString($this->last_time)) {
			$outErrMsg = 'last_time';
			$outJson = ResultHandle::validNotMatchedErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}		
		if (isset($this->start_time) && !validateDateTimeString($this->start_time)) {
			$outErrMsg = 'start_time';
			$outJson = ResultHandle::validNotMatchedErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		if (isset($this->stop_time) && !validateDateTimeString($this->stop_time)) {
			$outErrMsg = 'stop_time';
			$outJson = ResultHandle::validNotMatchedErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		
		if (isset($this->attend_date) && !validateDateString($this->attend_date)) {
			$outErrMsg = 'attend_date';
			$outJson = ResultHandle::validNotMatchedErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		
		return true;
	}
}