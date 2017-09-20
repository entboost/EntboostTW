<?php
require_once 'EBModelBase.class.php';

/**
 * 考勤日结表
 *
 */
class EBAttendDaily extends EBModelBase
{
	/**
	 * 记录编号(数字)
	 * @var string
	 */
	public $att_dai_id;
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
	 * 考勤日期，格式如：2016-01-20
	 * @var string
	 */
	public $attend_date;
	/**
	 * 当天考勤时段0实出勤状态
	 * 0x0	= 默认状态
	 * 0x40	= 加班
	 * @var int
	 */
	public $att_rec_id0_state;
	/**
	 * 当天考勤时段0对应考勤记录编号(数字)；0表示没有考勤记录数据
	 * @var string
	 */
	public $att_rec_id0;	
	/**
	 * 当天考勤时段1实出勤状态
	 * 0x0	= 默认状态
	 * 0x1	= 全勤
	 * 0x2	= 未签到
	 * 0x4	= 未签退
	 * 0x8	= 旷工
	 * 0x10	= 迟到
	 * 0x20	= 早退
	 * 0x40	= 加班
	 * 0x80	= 外勤
	 * 0x100=请假
	 * 0x200=补签
	 * 0x1000=审批通过标识
	 * @var int
	 */
	public $att_rec_id1_state;
	/**
	 * 当天考勤时段1对应考勤记录编号(数字)；0表示没有考勤记录数据，如“旷工”
	 * @var string
	 */
	public $att_rec_id1;
	/**
	 * 当天考勤时段2实出勤状态
	 * 具体值见字段att_rec_id1_state
	 * @var int
	 */
	public $att_rec_id2_state;
	/**
	 * 当天考勤时段2对应考勤记录编号(数字)；0表示没有考勤记录数据，如“旷工”
	 * @var string
	 */
	public $att_rec_id2;
	/**
	 * 当天考勤时段3实出勤状态
	 * 具体值见字段att_rec_id1_state
	 * @var int
	 */
	public $att_rec_id3_state;
	/**
	 * 当天考勤时段3对应考勤记录编号(数字)；0表示没有考勤记录数据，如“旷工”
	 * @var string
	 */
	public $att_rec_id3;
	/**
	 * 当天考勤时段4实出勤状态
	 * 具体值见字段att_rec_id1_state
	 * @var int
	 */
	public $att_rec_id4_state;
	/**
	 * 当天考勤时段4对应考勤记录编号(数字)；0表示没有考勤记录数据，如“旷工”
	 * @var string
	 */
	public $att_rec_id4;
	
	/**
	 * 是否无效：1无效，0有效
	 * @var int
	 */
	public $invalid;
	/**
	 * 是否考勤日：1是，0不是
	 * @var int
	 */
	public $calcul_day;
	/**
	 * 应出勤次数
	 * @var int
	 */
	public $expected_count;
	/**
	 * 应出勤时长；单位：分钟
	 * @var int
	 */
	public $expected_duration;
	/**
	 * 实际出勤次数
	 * @var int
	 */
	public $real_count;
	/**
	 * 实际出勤时长；单位：分钟
	 * @var int
	 */
	public $real_duration;
	/**
	 * 加班次数
	 * @var int
	 */
	public $work_overtime_count;
	/**
	 * 加班时长；单位：分钟
	 * @var int
	 */
	public $work_overtime_duration;
	/**
	 * 异常考勤次数
	 * @var int
	 */
	public $abnormal_count;
	/**
	 * 签到次数
	 * @var int
	 */
	public $signin_count;
	/**
	 * 未签到次数
	 * @var int
	 */
	public $unsignin_count;
	/**
	 * 迟到次数
	 * @var int
	 */
	public $late_count;
	/**
	 * 签退次数
	 * @var int
	 */
	public $signout_count;
	/**
	 * 未签退次数
	 * @var int
	 */
	public $unsignout_count;
	/**
	 * 早退次数
	 * @var int
	 */
	public $leave_early_count;
	/**
	 * 外勤次数
	 * @var int
	 */
	public $work_outside_count;
	/**
	 * 外勤时长；单位：分钟
	 * @var int
	 */
	public $work_outside_duration;
	
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
	
		unset($fields['att_dai_id']);
		unset($fields['owner_type']);
		unset($fields['owner_id']);
		unset($fields['user_id']);
		unset($fields['create_time']);
		unset($fields['attend_date']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('att_dai_id', 'owner_type', 'owner_id', 'user_id', 'invalid', 'att_rec_id0_state', 'att_rec_id1_state', 'att_rec_id2_state', 'att_rec_id3_state', 'att_rec_id4_state'
				, 'att_rec_id0', 'att_rec_id1', 'att_rec_id2', 'att_rec_id3', 'att_rec_id4', 'calcul_day', 'expected_count', 'expected_duration', 'real_count', 'real_duration'
				, 'work_overtime_count', 'work_overtime_duration', 'abnormal_count', 'signin_count', 'unsignin_count', 'late_count', 'signout_count', 'unsignout_count'
				, 'leave_early_count', 'work_outside_count', 'work_outside_duration');
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
	 * 把记录转换为对象
	 * @param {array} $fieldDefines 字段定义
	 * @param {array} $record 记录实例(关联数组)
	 * @param {EBAttendDaily} $instance [可选，引用] 实例对象；如传入对象不为空，则给该对象赋值，否则给本对象赋值
	 * @return boolean 是否成功
	 */
	public function setValuesFromRecord($fieldDefines, array $record, &$instance=NULL) {
		if (!isset($fieldDefines)) {
			$fieldDefines = array('att_dai_id', 'owner_type,int', 'owner_id', 'user_id', 'user_name', 'create_time', 'attend_date', 'invalid,int', 'att_rec_id0_state,int'
					, 'att_rec_id0', 'att_rec_id1_state,int', 'att_rec_id1', 'att_rec_id2_state,int', 'att_rec_id2', 'att_rec_id3_state,int', 'att_rec_id3'
					, 'att_rec_id4_state,int', 'att_rec_id4', 'calcul_day,int', 'expected_count,int', 'expected_duration,int', 'real_count,int'
					, 'real_duration,int', 'work_overtime_count,int', 'work_overtime_duration,int', 'abnormal_count,int', 'signin_count,int', 'unsignin_count,int'
					, 'late_count,int', 'signout_count,int', 'unsignout_count,int', 'leave_early_count,int', 'work_outside_count,int', 'work_outside_duration,int');
		}
		
		$tmpInstance = isset($instance)?$instance:$this;
		return parent::setValuesFromRecord($fieldDefines, $record, $tmpInstance);
	}
}