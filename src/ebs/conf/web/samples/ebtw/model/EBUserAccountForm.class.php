<?php
require_once 'EBUserAccount.class.php';

/**
 * 任务-表单自动绑定类
 */
class EBUserAccountForm extends EBUserAccount
{
	
	/**
	 * {@inheritDoc}
	 * @see EBUserAccount::setValuesFromRequest()
	 */
	public function setValuesFromRequest($instance=NULL) {
		parent::setValuesFromRequest(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBUserAccount::createWhereConditions()
	 */
	public function createWhereConditions($instance=NULL) {
		return parent::createWhereConditions(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBUserAccount::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array();
		return array_merge($parentCheckDigits, $checkDigits);
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see EBUserAccount::validNotEmpty()
	 */
	public function validNotEmpty($fieldNames, &$outErrMsg, $instance=NULL) {
		return parent::validNotEmpty($fieldNames, $outErrMsg, isset($instance)?$instance:$this);
	}
	
	/**
	 * 验证表单字段逻辑合法性
	 * @param $outJson 输出 验证不通过结果字符串(json封装)
	 * @return boolean 验证结果：true=通过，false=不通过
	 */
	public function validFormFields(&$outJson) {
		return true;
	}	
}