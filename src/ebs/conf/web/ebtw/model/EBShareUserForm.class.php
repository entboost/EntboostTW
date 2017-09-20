<?php
require_once 'EBShareUser.class.php';

class EBShareUserForm extends EBShareUser
{
	/**
	 * 更新类型
	 * @var int
	 */
	public $update_type;
	/**
	 * 查询条件使用的有效标记，1=有效，0=无效
	 * @var int
	 */
	public $valid_flag_for_query;
	/**
	 * 自定义属性，用于标识一些细微的操作特性
	 * @var string
	 */
	public $custom_param;
	
	/**
	 * {@inheritDoc}
	 * @see EBShareUser::setValuesFromRequest()
	 */
	public function setValuesFromRequest($instance=NULL) {
		parent::setValuesFromRequest(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBShareUser::createWhereConditions()
	 */
	public function createWhereConditions($instance=NULL) {
		return parent::createWhereConditions(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBShareUser::createFields()
	 */
	public function createFields($instance=NULL) {
		$fields = parent::createFields(isset($instance)?$instance:$this);
		unset($fields['update_type']);
		unset($fields['valid_flag_for_query']);
		return $fields;
	}	
	
	/**
	 * {@inheritDoc}
	 * @see EBShareUser::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array();
		return array_merge($parentCheckDigits, $checkDigits);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBShareUser::validNotEmpty()
	 */
	public function validNotEmpty($fieldNames, &$outErrMsg, $instance=NULL) {
		return parent::validNotEmpty($fieldNames, $outErrMsg, isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBShareUser::validFormFields()
	 */
	public function validFormFields(&$outJson, $output=true) {
		if (!parent::validFormFields($outJson, $output)) {
			return false;
		}
		//验证updateType
		if (isset($this->update_type) && !in_array($this->update_type, array('1', '2', '3', '4'))) {
			$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('update_type', $output);
			return;
		}
		//验证valid_flag_for_query
		if (isset($this->valid_flag_for_query) && !in_array($this->valid_flag_for_query, array('0', '1'))) {
			$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('valid_flag_for_query', $output);
			return false;
		}
		
		return true;
	}
}