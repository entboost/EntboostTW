<?php
require_once 'EBModelBase.class.php';

/**
 * 考勤设置表
 *
 */
class EBAttendSetting extends EBModelBase
{
	/**
	 * 编号(数字)
	 * @var string
	 */
	public $att_set_id;
	/**
	 * 考勤规则名称
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
	 * 考勤规则1，关联编号(数字)
	 * @var string
	 */
	public $att_rul_id1;
	/**
	 * 考勤规则2，关联编号(数字)
	 * @var string
	 */
	public $att_rul_id2;
	/**
	 * 考勤规则3，关联编号(数字)
	 * @var string
	 */
	public $att_rul_id3;
	/**
	 * 考勤规则4，关联编号(数字)
	 * @var string
	 */
	public $att_rul_id4;
	/**
	 * 考勤规则5，关联编号(数字)
	 * @var string
	 */
	public $att_rul_id5;
	/**
	 * 考勤规则6，关联编号(数字)
	 * @var string
	 */
	public $att_rul_id6;
	/**
	 * 考勤规则7，关联编号(数字)
	 * @var string
	 */
	public $att_rul_id7;
	/**
	 * 新考勤规则1，关联编号(数字)
	 * @var string
	 */
	public $att_rul_newid1;
	/**
	 * 新考勤规则2，关联编号(数字)
	 * @var string
	 */
	public $att_rul_newid2;
	/**
	 * 新考勤规则3，关联编号(数字)
	 * @var string
	 */
	public $att_rul_newid3;
	/**
	 * 新考勤规则4，关联编号(数字)
	 * @var string
	 */
	public $att_rul_newid4;
	/**
	 * 新考勤规则5，关联编号(数字)
	 * @var string
	 */
	public $att_rul_newid5;
	/**
	 * 新考勤规则6，关联编号(数字)
	 * @var string
	 */
	public $att_rul_newid6;
	/**
	 * 新考勤规则7，关联编号(数字)
	 * @var string
	 */
	public $att_rul_newid7;	
	/**
	 * 是否默认规则 0/1
	 * 0=非默认规则
	 * 1=默认规则，用于用户在多个考勤规则下，默认使用该考勤规则
	 * @var int
	 */
	public $is_default;
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
		
		unset($fields['att_set_id']);
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
		$checkDigits = array('att_set_id', 'create_uid', 'last_uid', 'att_rul_id1', 'att_rul_id2', 'att_rul_id3', 'att_rul_id4', 'att_rul_id5'
				, 'att_rul_id6', 'att_rul_id7', 'att_rul_newid1', 'att_rul_newid2', 'att_rul_newid3', 'att_rul_newid4', 'att_rul_newid5'
				, 'att_rul_newid6', 'att_rul_newid7', 'is_default', 'disable', 'owner_id', 'owner_type');
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
	 * @return {boolean} 是否有关键更新
	 */
	public function checkImportantUpdate($entity1, &$entity2, $sameDay) {
		$result = false;

		for ($i=1; $i<=7; $i++) {
			if (!array_key_exists("att_rul_id$i", $entity2)) {
				log_debug("miss att_rul_id$i");
				continue;
			}
			
			if ($entity2["att_rul_id$i"]==='-1' && $sameDay) { //删除一个考勤规则
				$entity2["att_rul_id$i"] = '0';
				$entity2["att_rul_newid$i"] = '0';
				$result = true;
			} else {
				if ($entity2["att_rul_id$i"]!=='0' && $entity2["att_rul_id$i"]!==$entity1["att_rul_newid$i"]) {
					if ($entity2["att_rul_id$i"]===$entity1["att_rul_id$i"]) { //没有变化
						$entity2["att_rul_newid$i"] = '0';
						$entity2["att_rul_id$i"] = $entity1["att_rul_id$i"];
					} else {
						if ($sameDay) {
							$entity2["att_rul_newid$i"] = '0';
							//$entity2["att_rul_id$i"]
						} else {
							$entity2["att_rul_newid$i"] = $entity2["att_rul_id$i"];
							$entity2["att_rul_id$i"] = $entity1["att_rul_id$i"];
						}
						$result = true;
					}
				} else { //不涉及的字段，直接复制旧值
					$entity2["att_rul_newid$i"] = $entity1["att_rul_newid$i"];
					$entity2["att_rul_id$i"] = $entity1["att_rul_id$i"];
				}
			}
		}
		
		return $result;
	}
}