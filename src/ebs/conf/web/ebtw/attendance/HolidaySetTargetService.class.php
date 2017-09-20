<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class HolidaySetTargetService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'hs_tar_id';
		$this->tableName = 'eb_hol_set_target_t';
		$this->fieldNames = 'hs_tar_id, target_id, target_type, hol_set_id, create_uid, create_time';
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
	 * 按假期配置编号删除关联的适用范围记录
	 * @param {string} $holSetId 考勤设置编号
	 * @return {boolean|array} false=执行失败，array=结果列表
	 */
	public function deleteByHolidaySettingId($holSetId) {
		if (empty($holSetId)) {
			log_err('deleteByHolidaySettingId error, $holSetId is empty');
			return false;
		}
		
		return $this->delete(array('hol_set_id'=>$holSetId), array('hol_set_id'));
	}
	
	/**
	 * 删除关联的适用范围记录
	 * @param {string} $holSetId 假期配置编号
	 * @param {int|string} $targetType 目标类型
	 * @param {string} $targetId 目标编号
	 */
	public function deleteByHolidaySettingTargetTypeAndId($holSetId, $targetType, $targetId) {
		if (empty($holSetId) || empty($targetType) || empty($targetId)) {
			log_err('deleteByHolidaySettingTargetTypeAndId error, $holSetId or $targetType or $targetId is empty');
			return false;
		}
		
		$params =array('hol_set_id'=>$holSetId, 'target_type'=>$targetType, 'target_id'=>$targetId);
		$checkDigits = array('hol_set_id', 'target_type', 'target_id');
		return $this->delete($params, $checkDigits);
	}

	/**
	 * 按假期配置编号获取关联的适用范围记录
	 * @param {string} $holSetId 假期配置编号
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function getRecordsByHolidaySettingId($holSetId) {
		if (empty($holSetId)) {
			log_err('getRecordsByHolidaySettingId error, $holSetId is empty');
			return false;
		}
		
		$limit = 1000;
		$fieldNames = $this->fieldNames.", #concat(target_type, #concat(',', target_id)) as target_type_id";
		return $this->search($fieldNames, array('hol_set_id'=>$holSetId), array('hol_set_id'), null, $limit);
	}
}