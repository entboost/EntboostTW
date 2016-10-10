<?php
require_once 'EBModelBase.class.php';

class EBShareUser extends EBModelBase
{
	/**
	 * 共享编号(数字)
	 * @var string
	 */
	public $share_id;
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
	 * 共享用户编号(数字)
	 * @var string
	 */
	public $share_uid;
	/**
	 * 共享用户名称
	 * @var string
	 */
	public $share_name;	
	/**
	 * 周期
	 * 0：评审/评阅人
	 * 1：参与人
	 * 2：共享人
	 * 3：关注人
	 * @var int
	 */
	public $share_type;
	/**
	 * 创建时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $create_time;
	/**
	 * 已读标记，0=未读，1=已读
	 * @var int
	 */
	public $read_flag;
	/**
	 * 阅读时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $read_time;	
	/**
	 * 处理结果
	 * 0：未处理
	 * 其它：处理后结果
	 * @var int
	 */
	public $result_status;
	/**
	 * 处理时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $result_time;
	/**
	 * 有效标记，1=有效，0=无效
	 * @var int
	 */
	public $valid_flag;
	
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
		
		unset($fields['share_id']);
		unset($fields['create_time']);
		//unset($fields['read_flag']);
		unset($fields['read_time']);
		unset($fields['result_status']);
		unset($fields['result_time']);
		unset($fields['valid_flag']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('share_id', 'from_id', 'from_type', 'share_uid', 'share_type', 'read_flag', 'result_status', 'valid_flag');
		return array_merge($parentCheckDigits, $checkDigits);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::validNotEmpty()
	 */
	public function validNotEmpty($fieldNames, &$outErrMsg, $instance=NULL) {
		return parent::validNotEmpty($fieldNames, $outErrMsg, isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::validDigit()
	 */
	public function validDigit($fieldNames, &$outErrMsg, $instance=NULL) {
		return parent::validDigit($fieldNames, $outErrMsg, isset($instance)?$instance:$this);
	}
	
	/**
	 * 验证表单字段逻辑合法性(只校验非空值的字段)
	 * @param {string} $outJson 输出参数 验证不通过结果字符串(json封装)
	 * @param {boolean} $output	是否输出到页面，默认true
	 * @return {boolean} 验证结果：true=通过，false=不通过
	 */
	public function validFormFields(&$outJson, $output=true) {
		//验证share_id
		if (isset($this->share_id) && !$this->validDigit('share_id', $outErrMsg)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		//验证from_id
		if (isset($this->from_id) && !$this->validDigit('from_id', $outErrMsg)) {
			$outJson = ResultHandle::fieldValidNotDigitErrToJsonAndOutput('from_id', $output);
			return false;
		}
		//验证from_type
		if (isset($this->from_type) && !in_array($this->from_type, array('1', '2', '3'))) {
			$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('from_type', $output);
			return false;
		}
		//验证share_uid
		if (isset($this->share_uid) && !$this->validDigit('share_uid', $outErrMsg)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		//验证share_type
		if (isset($this->share_type)) {
			if (!is_array($this->share_type)) {
				if (!in_array($this->share_type, array('1', '2', '3', '4', '5', '6'))) {
					$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('share_type', $output);
					return false;
				}
			} else {
				foreach($this->share_type as $shareType) {
					if (!in_array($shareType, array('1', '2', '3', '4', '5', '6'))) {
						$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('share_type', $output);
						return false;
					}
				}
			}
		}
		//验证read_flag
		if (isset($this->read_flag) && !in_array($this->read_flag, array('0', '1'))) {
			$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('read_flag', $output);
			return false;
		}
		//验证result_status
		if (isset($this->result_status) && !$this->validDigit('result_status', $outErrMsg)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		//验证valid_flag
		if (isset($this->valid_flag) && !in_array($this->valid_flag, array('0', '1'))) {
			$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('valid_flag', $output);
			return false;
		}
		
		return true;
	}
}