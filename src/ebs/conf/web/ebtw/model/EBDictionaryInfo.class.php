<?php
require_once 'EBModelBase.class.php';

/**
 * 字典表
 *
 */
class EBDictionaryInfo extends EBModelBase
{
	/**
	 * 编号(数字)
	 * @var string
	 */
	public $dict_id;
	/**
	 * 所有者类型
	 * 0=系统默认类型
	 * 1=企业（owner_id=ent_id）
	 * 2=部门/群组（owner_id=group_id）
	 * 3=用户（owner_id=user_id）
	 * @var int
	 */
	public $owner_type;
	/**
	 * 所有者编号(数字)
	 * @var string
	 */
	public $owner_id;
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
	 * 类型：1=请假
	 * @var int
	 */
	public $dict_type;	
	/**
	 * 名称
	 * @var string
	 */
	public $dict_name;
	/**
	 * 存储值
	 * @var int
	 */
	public $param_int;
	/**
	 * 存储值
	 * @var string
	 */
	public $param_str;
	/**
	 * 排序值，值小排前面
	 * @var int
	 */
	public $display_index;
	/**
	 * 是否禁用：1=禁用，0=有效
	 * @var int
	 */
	public $disable;
	
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
		
		unset($fields['dict_id']);
		unset($fields['owner_type']);
		unset($fields['owner_id']);
		unset($fields['create_uid']);
		unset($fields['create_time']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('dict_id', 'owner_type', 'owner_id', 'create_uid', 'dict_type', 'param_int', 'display_index', 'disable');
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