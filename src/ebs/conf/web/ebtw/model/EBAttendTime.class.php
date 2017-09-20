<?php
require_once 'EBModelBase.class.php';

/**
 * 考勤时段表
 *
 */
class EBAttendTime extends EBModelBase
{
	/**
	 * 编号(数字)
	 * @var string
	 */
	public $att_tim_id;
	/**
	 * 考勤时间段名称，如“上午”，“下午”等
	 * @var string
	 */
	public $name;
	/**
	 * 签到时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $signin_time;
	/**
	 * 忽略不计迟到的时长（单位：分钟），范围：0-60
	 * @var int
	 */
	public $signin_ignore;
	/**
	 * 签退时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $signout_time;
	/**
	 * 忽略不计早退的时长（单位：分钟），范围：0-60
	 * @var int
	 */
	public $signout_ignore;
	/**
	 * 休息时长（单位：分钟）
	 * 用于例如：09:00-18:00考勤时间段，设置中午休息120分钟
	 * @var int
	 */
	public $rest_duration;
	/**
	 * 工作时长（单位：分钟）
	 * 用于例如：09:00-18:00考勤时间段，中午休息120分钟后，实际工作时长只有420分钟
	 * @var int
	 */
	public $work_duration;
	/**
	 * 创建时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $create_time;
	
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
	
		unset($fields['att_tim_id']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('att_tim_id', 'signin_ignore', 'signout_ignore', 'rest_duration', 'work_duration');
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
	 * 检查两个关联数组是否有重要更新
	 * @param {array} $entity1 关联数组1
	 * @param {array} $entity2 关联数组2
	 * @return {boolean} 是否有重要更新
	 */
	public function checkImportantUpdate($entity1, $entity2) {
		if ($entity1['signin_time']!=$entity2['signin_time'])
			return true;
		if ($entity1['signout_time']!=$entity2['signout_time'])
			return true;
		if ($entity1['signin_ignore']!=$entity2['signin_ignore'])
			return true;
		if ($entity1['signout_ignore']!=$entity2['signout_ignore'])
			return true;
		if ($entity1['rest_duration']!=$entity2['rest_duration'])
			return true;
		if ($entity1['work_duration']!=$entity2['work_duration'])
			return true;
		
		return false;
	}
}