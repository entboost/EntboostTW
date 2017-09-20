<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class AttendRuleService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'att_rul_id';
		$this->tableName = 'eb_attend_rule_t';
		$this->fieldNames = 'att_rul_id, work_day, att_tim_id1, att_tim_id2, att_tim_id3, att_tim_id4'
				.', att_tim_newid1, att_tim_newid2, att_tim_newid3, att_tim_newid4, flexible_work, flag, create_time';
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
	 * 使未生效的考勤时间段生效
	 * @param {string} $rulId 考勤配置编号
	 * @param {array} $newTimIds 考勤规则编号数组
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function effectTimeNewIds($rulId, $newTimIds) {
		if (empty($rulId) || empty($newTimIds)) {
			log_err('effectTimeNewIds error, $ruleId or $newTimIds is empty');
			return false;
		}
	
		$sets = array();
		$setCheckDigits = array();
		foreach ($newTimIds as $i=>$newTimId) {
			if ($newTimId==='-1') {
				$sets["att_tim_id$i"] = '0';
				$sets["att_tim_newid$i"] = '0';
			} else if ($newTimId!=='0') {
				$sets["att_tim_id$i"] = $newTimId;
				$sets["att_tim_newid$i"] = '0';
			}
				
			array_push($setCheckDigits, "att_tim_id$i");
			array_push($setCheckDigits, "att_tim_newid$i");
		}
		log_info($sets);
	
		if (empty($sets)) {
			log_err('effectTimeNewIds error, nothing to update');
			return false;
		}
	
		$wheres = array('att_rul_id'=>$rulId);
		return $this->update($sets, $wheres, $setCheckDigits, array('att_rul_id'));
	}	
}