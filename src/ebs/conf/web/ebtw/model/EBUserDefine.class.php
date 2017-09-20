<?php
require_once 'EBModelBase.class.php';

/**
 * 特殊用户定义表
 *
 */
class EBUserDefine extends EBModelBase
{
	/**
	 * 编号(数字)
	 * @var string
	 */
	public $ud_id;
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
	 * 用户类型：1=考勤专员
	 * @var int
	 */
	public $user_type;
	/**
	 * 用户编号(数字)
	 * @var sting
	 */
	public $user_id;
	/**
	 * 用户名称
	 * @var string
	 */
	public $user_name;
	/**
	 * 存储值
	 * 配合user_type用户类型用
	 * 使用|相加，使用&判断
	 * user_type=1考勤专员(0x1=管理权限;0x2=其他)
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
		
		unset($fields['ud_id']);
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
		$checkDigits = array('ud_id', 'owner_type', 'owner_id', 'create_uid', 'user_type', 'user_id', 'param_int', 'display_index', 'disable');
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