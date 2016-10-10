<?php
require_once 'EBModelBase.class.php';

//表单通用类
class EBUsualForm extends EBModelBase
{
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::setValuesFromRequest()
	 */
	public function setValuesFromRequest($instance=NULL) {
		parent::setValuesFromRequest(isset($instance)?$instance:$this);
	}
}