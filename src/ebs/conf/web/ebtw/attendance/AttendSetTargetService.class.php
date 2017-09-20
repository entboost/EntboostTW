<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class AttendSetTargetService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'as_tar_id';
		$this->tableName = 'eb_att_set_target_t';
		$this->fieldNames = 'as_tar_id, target_id, target_type, att_set_id, create_uid, create_time';
	}

	/**
	 * 获取单例对象，PHP的单例对象只相对于当次而言
	 */
	public static function get_instance() {
		if(self::$instance==NULL)
			self::$instance = new self;
			return self::$instance;
	}
	
	/**
	 * 按考勤设置编号删除关联的适用范围记录
	 * @param {string} $aSetId 考勤设置编号
	 * @return {boolean|array} false=执行失败，array=结果列表
	 */
	public function deleteByAttendSettingId($aSetId) {
		if (empty($aSetId)) {
			log_err('deleteByAttendSettingId error, $aSetId is empty');
			return false;
		}
		
		return $this->delete(array('att_set_id'=>$aSetId), array('att_set_id'));
	}
	
	/**
	 * 删除关联的适用范围记录
	 * @param {string} $aSetId 考勤设置编号
	 * @param {int|string} $targetType 目标类型
	 * @param {string} $targetId 目标编号
	 */
	public function deleteByAttendSettingTargetTypeAndId($aSetId, $targetType, $targetId) {
		if (empty($aSetId) || empty($targetType) || empty($targetId)) {
			log_err('deleteByAttendSettingId error, $aSetId or $targetType or $targetId is empty');
			return false;
		}
		
		$params =array('att_set_id'=>$aSetId, 'target_type'=>$targetType, 'target_id'=>$targetId);
		$checkDigits = array('att_set_id', 'target_type', 'target_id');
		return $this->delete($params, $checkDigits);
	}

	/**
	 * 按考勤设置编号获取关联的适用范围记录
	 * @param {string} $aSetId 考勤设置编号
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function getRecordsByAttendSettingId($aSetId) {
		if (empty($aSetId)) {
			log_err('getRecordsByAttendSettingId error, $aSetId is empty');
			return false;
		}
		
		$limit = 1000;
		$fieldNames = $this->fieldNames.", #concat(target_type, #concat(',', target_id)) as target_type_id";
		return $this->search($fieldNames, array('att_set_id'=>$aSetId), array('att_set_id'), null, $limit);
	}
}