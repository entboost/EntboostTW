<?php
require_once 'EBModelBase.class.php';

/**
 * 节假日设置表
 *
 */
class EBHolidaySetting extends EBModelBase
{
	/**
	 * 编号(数字)
	 * @var string
	 */
	public $hol_set_id;
	/**
	 * 节假日名称，如"国庆"
	 * @var string
	 */
	public $name;
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
	 * 最后修改时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $last_time;
	/**
	 * 最后修改者的用户编号(数字)
	 * @var string
	 */
	public $last_uid;
	/**
	 * 开始日期时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $start_time;
	/**
	 * 结束日期时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $stop_time;
	/**
	 * 假期周期性类型
	 * 0：一次性假期（如2016-11-23，公司周年庆）
	 * 1：每年假期（如10-01~10-07每年国庆放假）
	 * 2：每月假期（如每月1 ~3号，企业自定义）
	 * 3：每周假期（如每周几至周几，企业自定义）
	 * @var int
	 */
	public $period;
	/**
	 * 周期性开始，配合period使用
	 * period=1每年假期，1001=十月一号，901=九月
	 * period=2每月假期，1=每月一号
	 * period=3每周假期，0=星期天；1=星期一
	 * @var int
	 */
	public $period_from;
	/**
	 * 周期性结束，配合period使用
	 * period=1每年假期，1001=十月一号，901=九月
	 * period=2每月假期，1=每月一号
	 * period=3每周假期，0=星期天；1=星期一
	 * @var int
	 */
	public $period_to;
	
	/**
	 * 假期计算标识
	 * 0=按日（全天）计算假期（如国庆）
	 * 1=按半天（上午）计算假期（如某些单位3月8日妇女节放假半天等）
	 * 2=按半天（下午）计算假期（如某些单位3月8日妇女节放假半天等
	 * @var int
	 */
	public $flag;
	/**
	 * 是否禁用 0/1
	 * 0=有效
	 * 1=禁用
	 * @var int
	 */
	public $disable;
	/**
	 * 所有者类型
	 * 0=系统默认类型（不支持）
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
		
		unset($fields['hol_set_id']);
		unset($fields['create_uid']);
		unset($fields['create_time']);
		unset($fields['last_uid']);
		unset($fields['last_time']);
		unset($fields['owner_id']);
		unset($fields['owner_type']);		
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('hol_set_id', 'create_uid', 'last_uid', 'period', 'period_from', 'period_to', 'flag', 'disable', 'owner_id', 'owner_type');
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