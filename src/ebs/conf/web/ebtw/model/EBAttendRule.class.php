<?php
require_once 'EBModelBase.class.php';

/**
 * 考勤规则表
 *
 */
class EBAttendRule extends EBModelBase
{
	/**
	 * 编号(数字)
	 * @var string
	 */
	public $att_rul_id;
	/**
	 * 工作日设置，保存值使用|相加
	 * 0x01=周一（使用work_day&1=1判断，下同）
	 * 0x02=周二
	 * 0x04=周三
	 * 0x08=周四
	 * 0x10=周五
	 * 0x20=周六
	 * 0x40=周日
	 * @var int
	 */
	public $work_day;
	/**
	 * 考勤时间段1，关联编号(数字)
	 * @var string
	 */
	public $att_tim_id1;
	/**
	 * 考勤时间段2，关联编号(数字)
	 * @var string
	 */
	public $att_tim_id2;
	/**
	 * 考勤时间段3，关联编号(数字)
	 * @var string
	 */
	public $att_tim_id3;
	/**
	 * 考勤时间段4，关联编号(数字)
	 * @var string
	 */
	public $att_tim_id4;
	/**
	 * 新考勤时间段1，关联编号(数字)
	 * @var string
	 */
	public $att_tim_newid1;
	/**
	 * 新考勤时间段2，关联编号(数字)
	 * @var string
	 */
	public $att_tim_newid2;
	/**
	 * 新考勤时间段3，关联编号(数字)
	 * @var string
	 */
	public $att_tim_newid3;
	/**
	 * 新考勤时间段4，关联编号(数字)
	 * @var string
	 */
	public $att_tim_newid4;	
	/**
	 * 弹性工作机制 0/1
	 * 0=严格按照考勤时间段，计算迟到和早退
	 * 1=满足工作时长条件，不算为迟到或早退
	 * @var int
	 */
	public $flexible_work;
	/**
	 * 使用标识 0/1
	 * 0=默认标识
	 * 1=保留标识（用于工作日设置修改，已经有用户签到签退记录保留数据）
	 * @var int
	 */
	public $flag;
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
	
		unset($fields['att_rul_id']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('att_rul_id', 'work_day', 'att_tim_id1', 'att_tim_id2', 'att_tim_id3', 'att_tim_id4'
				, 'att_tim_newid1', 'att_tim_newid2', 'att_tim_newid3', 'att_tim_newid4', 'flexible_work', 'flag');
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
	 * 检查两个关联数组是否有重要更新(仅可以执行一次)
	 * @param {array} $entity1 关联数组1，已存在的记录
	 * @param {array} $entity2 (引用) 关联数组2，即将保存的记录
	 * @param {boolean} $sameDay 是否同一天
	 * @return {boolean} 是否有重要更新
	 */
	public function checkImportantUpdate($entity1, &$entity2, $sameDay) {
		$result = false;
		
		if ($entity1['work_day']!=$entity2['work_day'])
			$result = true;
		if ($entity1['flexible_work']!=$entity2['flexible_work'])
			$result = true;
		
		for ($i=1; $i<=4; $i++) {
			if (!array_key_exists("att_tim_id$i", $entity2)) {
				log_debug("miss att_tim_id$i");
				continue;
			}
			
			if ($entity2["att_tim_id$i"]==='-1' && $sameDay) { //删除一个考勤时间段
				$entity2["att_tim_id$i"] = '0';
				$entity2["att_tim_newid$i"] = '0';
				$result = true;
			} else {
				if ($entity2["att_tim_id$i"]!=='0' && $entity2["att_tim_id$i"]!==$entity1["att_tim_newid$i"]) {
					if ($entity2["att_tim_id$i"]===$entity1["att_tim_id$i"]) { //没有变化
						$entity2["att_tim_newid$i"] = '0';
						$entity2["att_tim_id$i"] = $entity1["att_tim_id$i"];
					} else {
						if ($sameDay) {
							$entity2["att_tim_newid$i"] = '0';
							//$entity2["att_tim_id$i"]
						} else {
							$entity2["att_tim_newid$i"] = $entity2["att_tim_id$i"];
							$entity2["att_tim_id$i"] = $entity1["att_tim_id$i"];
						}
						$result = true;
					}
				} else { //不涉及的字段，直接复制旧值
					$entity2["att_tim_newid$i"] = $entity1["att_tim_newid$i"];
					$entity2["att_tim_id$i"] = $entity1["att_tim_id$i"];
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * 检查是否有待更新的tim_newid(值不等于0)
	 * @param {array} $entity 关联数组
	 */
	public function hasTimNewid($entity) {
		for ($i=1; $i<=4; $i++) {
			if (!array_key_exists("att_tim_newid$i", $entity))
				continue;			
			
			if ($entity["att_tim_newid$i"]!=='0')
				return true;
		}
		return false;
	}
	
	/**
	 * 检查是否有待删除的tim_id(值等于-1)
	 * @param {array} $entity 关联数组
	 */
	public function hasDeleteFlag($entity) {
		for ($i=1; $i<=4; $i++) {
			if (!array_key_exists("att_tim_id$i", $entity))
				continue;
			
			if ($entity["att_tim_id$i"]==='-1')
				return true;
		}
		return false;
	}
}