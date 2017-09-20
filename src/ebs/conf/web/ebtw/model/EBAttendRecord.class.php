<?php
require_once 'EBModelBase.class.php';

/**
 * 考勤记录表
 *
 */
class EBAttendRecord extends EBModelBase
{
	/**
	 * 记录编号(数字)
	 * @var string
	 */
	public $att_rec_id;
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
	 * 考勤日期，格式如：2016-01-20
	 * @var string
	 */
	public $attend_date;
	/**
	 * 对应考勤规则编号，关联编号(数字)
	 * @var string
	 */
	public $att_rul_id;
	/**
	 * 对应考勤时间段编号，关联编号(数字)
	 * @var string
	 */
	public $att_tim_id;
	/**
	 * 实际签到时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $signin_time;
	/**
	 * 申请签到时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */	
	public $req_signin_time;
	/**
	 * 实际签退时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $signout_time;
	/**
	 * 申请签退时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $req_signout_time;	
	/**
	 * 签到来源：1=PC电脑端，2=手机APP
	 * @var int
	 */
	public $signin_from;
	/**
	 * 签退来源：1=PC电脑端，2=手机APP
	 * @var int
	 */
	public $signout_from;
	/**
	 * 签到地址
	 * @var string
	 */
	public $signin_address;
	/**
	 * 签退地址
	 * @var string
	 */
	public $signout_address;
	/**
	 * 实际出勤时长，单位：分钟
	 * @var int
	 */
	public $work_duration;
	/**
	 * 申请出勤时长，单位：分钟
	 * @var int
	 */
	public $req_duration;
	/**
	 * 实际出勤状态
	 * 0=默认状态
	 * 1=全勤
	 * 2=未签到
	 * 3=未签退
	 * 4=旷工
	 * 5=迟到
	 * 6=早退
	 * @var int
	 */
	public $attend_status;
	/**
	 * 数据标识
	 * 0=默认数据
	 * 1=虚拟数据
	 * @var int
	 */
	public $data_flag;
	
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
	
		unset($fields['att_rec_id']);
		unset($fields['owner_type']);
		unset($fields['owner_id']);
		unset($fields['user_id']);
		unset($fields['create_time']);
		unset($fields['last_time']);
		unset($fields['data_flag']);
		unset($fields['attend_date']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('att_rec_id', 'owner_type', 'owner_id', 'user_id', 'att_rul_id', 'att_tim_id', 'signin_from', 'signout_from', 'work_duration', 'req_duration', 'attend_status', 'data_flag');
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