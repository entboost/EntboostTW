<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class AttendSettingService extends AbstractService
{
	private static $instance  = NULL;
	
	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'att_set_id';
		$this->tableName = 'eb_attend_setting_t';
		$this->fieldNames = 'att_set_id, name, create_uid, create_time, last_uid, last_time, att_rul_id1, att_rul_id2'
				.', att_rul_id3, att_rul_id4, att_rul_id5, att_rul_id6, att_rul_id7, att_rul_newid1, att_rul_newid2'
				.', att_rul_newid3, att_rul_newid4, att_rul_newid5, att_rul_newid6, att_rul_newid7, is_default, disable, owner_type, owner_id';
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
	 * 获取考勤设置适用对象列表
	 * @param array $attendSettingIds 考勤设置编号列表
	 * @param {string} $entCode [可选] 企业编号
	 * @param {array} $groupCodes [可选] 部门或群组编号列表
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function getAttendSettingTargets(array $attendSettingIds, $entCode, array $groupCodes) {
		if (empty($attendSettingIds)) {
			log_err('getSettingTargets error, $attendSettingIds is empty');
			return false;
		}
		
		/*
		select * from (
		select t_a.*, t_b.ent_name as target_name, '' as ext_name, '' as user_account from eb_att_set_target_t t_a, enterprise_info_t t_b
		where t_a.target_type =1 and t_a.target_id = t_b.ent_id and t_a.att_set_id in (1)
		
		union all
		select t_a.*, t_b.dep_name as target_name, '' as ext_name, '' as user_account from eb_att_set_target_t t_a, department_info_t t_b
		where t_a.target_type = 2 and t_a.target_id = t_b.group_id and t_a.att_set_id in (1)
		
		union all
		select t_a.*, TB3.username as target_name, dep_name as ext_name, t_b.account as user_account from eb_att_set_target_t t_a 
		join user_account_t t_b on (t_a.target_id = t_b.user_id)
		left join (
			select t_x.group_id, t_x.dep_name, t_y.emp_uid, t_y.username from employee_info_t t_y, department_info_t t_x where t_x.group_id=t_y.group_id
			and (t_x.ent_id = 1000000000000030 or t_x.group_id in (999001))
		) TB3 on (t_a.target_id=TB3.emp_uid)
		where t_a.target_type = 3 and t_a.att_set_id in (1)
		) TMP
		order by target_type, target_name, ext_name
		 */
		//范围条件
		$ownerSql = '';
		if (isset($entCode))
			$ownerSql = "(t_x.ent_id = $entCode)";
		if (!empty($groupCodes)) {
			if (!empty($ownerSql))
				$ownerSql .= ' or ';
			$ownerSql .= "(t_x.group_id in(".implode(',', $groupCodes)."))";
		}
		//考勤设置编号
		$aSettingSql = implode(',', $attendSettingIds);
		
		//拼接SQL
		$sql = "select * from ("
				."select t_a.*, t_b.ent_name as target_name, '' as ext_name, '' as user_account from eb_att_set_target_t t_a, enterprise_info_t t_b "
				."where t_a.target_type =1 and t_a.target_id = t_b.ent_id and t_a.att_set_id in ($aSettingSql) "
				."union all "
				."select t_a.*, t_b.dep_name as target_name, '' as ext_name, '' as user_account from eb_att_set_target_t t_a, department_info_t t_b "
				."where t_a.target_type = 2 and t_a.target_id = t_b.group_id and t_a.att_set_id in ($aSettingSql) "
				."union all "
				."select t_a.*, TB3.username as target_name, dep_name as ext_name, t_b.account as user_account from eb_att_set_target_t t_a "
				."join user_account_t t_b on (t_a.target_id = t_b.user_id) "
				."left join ("
				."select t_x.group_id, t_x.dep_name, t_y.emp_uid, t_y.username from employee_info_t t_y, department_info_t t_x where t_x.group_id=t_y.group_id "
				."and ($ownerSql) "
				.") TB3 on (t_a.target_id=TB3.emp_uid) "
				."where t_a.target_type = 3 and t_a.att_set_id in ($aSettingSql) "
				.") TMP"
				;
		
		$limit = 1000;
		$orderBy = 'target_type, target_name, ext_name';
		return $this->simpleSearch($sql, null, null, null, $orderBy, $limit);
	}
	
	/**
	 * 获取可管理的考勤设置列表
	 * @param {string} $entCode [可选] 企业编号
	 * @param {array} $groupCodes [可选] 部门或群组编号列表
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function getManagedAttendSettings($entCode, array $groupCodes) {
		if (!isset($entCode) && empty($groupCodes)) {
			log_err('getManagedAttendSettings error, $entCode and $groupCodes are all empty');
			return false;
		}
		
		/*
		select * from eb_attend_setting_t where ((owner_type=1 and owner_id = 1000000000000030) or (owner_type=2 and owner_id in(999001))) order by owner_type, create_time
		 */
		//归属条件
		$ownerSql = '';
		if (isset($entCode)) {
			$ownerSql = "(owner_type=1 and owner_id = $entCode)";
		}
		if (!empty($groupCodes)) {
			if (!empty($ownerSql))
				$ownerSql .= ' or ';
			$ownerSql .= "(owner_type=2 and owner_id in(".implode(',', $groupCodes)."))";
		}
		
		$sql = "select * from eb_attend_setting_t where ($ownerSql)";
		
		$orderBy = 'owner_type, create_time';
		$limit = 100;
		return $this->simpleSearch($sql, null, null, null, $orderBy, $limit);
	}
	
	/**
	 * 获取考勤设置列表
	 * 注意1：userId $groupCodes $entCode至少填一项
	 * 注意2：结果已按target_type降序,is_default降序
	 *
	 * @param {string} $userId [可选] 用户编号
	 * @param {array} $groupCodes [可选] 部门或群组编号列表
	 * @param {string} $entCode [可选] 企业编号
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function getAttendSettings($userId = NULL, array $groupCodes, $entCode = NULL) {
		if (!isset($userId) && empty($groupCodes) && !isset($entCode)) {
			log_err('userId groupCodes entCode are all empty');
			return false;
		}
		/*
		SELECT t_x.target_id, t_x.target_type,
		t_y.att_rul_id1, t_y.att_rul_id2, t_y.att_rul_id3, t_y.att_rul_id4, t_y.att_rul_id5, t_y.att_rul_id6, t_y.att_rul_id7, t_y.att_set_id, t_y.name, t_y.is_default
		from eb_att_set_target_t t_x JOIN eb_attend_setting_t t_y on t_x.att_set_id = t_y.att_set_id
		where ((t_x.target_id = '1000000000000030' and t_x.target_type = 1) or (t_x.target_id in ('999001') and t_x.target_type = 2) or (t_x.target_id = '80' and t_x.target_type = 3))
		and t_y.disable = 0 order by t_x.target_type desc, t_y.is_default desc
		*/
		//规则适用目标的条件
		$targetSql = "";
		if (isset($userId)) {
			if (!empty($targetSql))
				$targetSql .= " or ";
			$targetSql .= "(t_x.target_id = $userId and t_x.target_type = 3)";
		}
		if (!empty($groupCodes)) {
			if (!empty($targetSql))
				$targetSql .= " or ";
			$targetSql .= "(t_x.target_id in (".implode(',', $groupCodes).") and t_x.target_type = 2)";
		}
		if (isset($entCode)) {
			if (!empty($targetSql))
				$targetSql .= " or ";
			$targetSql .= "(t_x.target_id = $entCode and t_x.target_type = 1)";
		}
	
		//拼接查询语句
		$sql = "SELECT t_x.target_id, t_x.target_type, t_y.att_rul_id1, t_y.att_rul_id2, t_y.att_rul_id3, "
				."t_y.att_rul_id4, t_y.att_rul_id5, t_y.att_rul_id6, t_y.att_rul_id7, t_y.att_set_id, t_y.name, t_y.is_default "
				."from eb_att_set_target_t t_x JOIN eb_attend_setting_t t_y on t_x.att_set_id = t_y.att_set_id "
				."where ($targetSql) and t_y.disable = 0 order by t_x.target_type desc, t_y.is_default desc";
		
		$result = $this->simpleSearch($sql);
		return $result;
	}
	
	/**
	 * 获取考勤规则时间列表
	 * @param {string} $attendSettingId 考勤规则设置ID
	 * @param {integer} $workDayValue 周几的值（例如周一使用work_day&1=1判断），填空则忽略本条件
	 * 		1(0x01) 周一
	 *		2(0x02) 周二
	 *		4(0x04) 周三
	 *		8(0x08) 周四
	 *		16(0x10)周五
	 *		32(0x20)周六
	 *		64(0x40)周日
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function getAttendRuleTimes($attendSettingId, $workDayValue=NULL) {
		/*
			select TMP.att_set_id, TMP.name as att_set_name, TMP.create_time as att_set_create_time, TMP.att_rul_id, TMP.flexible_work, TMP.att_tim_id1, TMP.att_tim_id2, TMP.att_tim_id3, TMP.att_tim_id4,
			t_x.name as att_time_name, att_tim_id, signin_time, signin_ignore, signout_time,signout_ignore,rest_duration, work_duration, t_x.create_time as att_time_create_time from (
			select t_a.att_set_id, t_a.name, t_a.create_time as create_time, t_b.att_rul_id, t_b.flexible_work, t_b.att_tim_id1 ,t_b.att_tim_id2, t_b.att_tim_id3, t_b.att_tim_id4 from eb_attend_setting_t t_a join eb_attend_rule_t t_b
			on (work_day&8=8 and (t_a.att_rul_id1 = t_b.att_rul_id or t_a.att_rul_id2 = t_b.att_rul_id or t_a.att_rul_id3 = t_b.att_rul_id
			or t_a.att_rul_id4 = t_b.att_rul_id or t_a.att_rul_id5 = t_b.att_rul_id or t_a.att_rul_id6 = t_b.att_rul_id
			or t_a.att_rul_id7 = t_b.att_rul_id))
			where t_a.disable = 0 and t_a.att_set_id in (1)) TMP join eb_attend_time_t t_x
			on (TMP.att_tim_id1 = t_x.att_tim_id or TMP.att_tim_id2 = t_x.att_tim_id or TMP.att_tim_id3 = t_x.att_tim_id or TMP.att_tim_id4 = t_x.att_tim_id)
			order by t_x.signin_time, t_x.signout_time desc
			*/
		//拼接查询语句
		$workDaySql = "";
		if (isset($workDayValue)) {
			$workDaySql = "work_day&$workDayValue=$workDayValue and ";
		}
	
		$sql ="select TMP.att_set_id, TMP.name as att_set_name, TMP.create_time as att_set_create_time, TMP.att_rul_id, TMP.flexible_work, TMP.att_tim_id1, TMP.att_tim_id2, TMP.att_tim_id3, TMP.att_tim_id4, "
				."t_x.name as att_time_name, att_tim_id, signin_time, signin_ignore, signout_time,signout_ignore,rest_duration, work_duration, t_x.create_time as att_time_create_time from ("
				."select t_a.att_set_id, t_a.name, t_a.create_time as create_time, t_b.att_rul_id, t_b.flexible_work, t_b.att_tim_id1 ,t_b.att_tim_id2, t_b.att_tim_id3, t_b.att_tim_id4 from eb_attend_setting_t t_a join eb_attend_rule_t t_b "
				."on ($workDaySql (t_a.att_rul_id1 = t_b.att_rul_id or t_a.att_rul_id2 = t_b.att_rul_id or t_a.att_rul_id3 = t_b.att_rul_id "
				."or t_a.att_rul_id4 = t_b.att_rul_id or t_a.att_rul_id5 = t_b.att_rul_id or t_a.att_rul_id6 = t_b.att_rul_id or t_a.att_rul_id7 = t_b.att_rul_id)) "
				."where t_a.disable = 0 and t_a.att_set_id in ($attendSettingId)) TMP join eb_attend_time_t t_x "
				."on (TMP.att_tim_id1 = t_x.att_tim_id or TMP.att_tim_id2 = t_x.att_tim_id or TMP.att_tim_id3 = t_x.att_tim_id or TMP.att_tim_id4 = t_x.att_tim_id) "
				."order by t_x.signin_time, t_x.signout_time desc";

		$result = $this->simpleSearch($sql);
		return $result;
	}
	
	/**
	 * 获取考勤规则列表
	 * @param {array} $attendSettingIds 考勤规则设置ID
	 * @param {integer} $workDay [可选] 周几的值（例如周一使用work_day&1=1判断），填空则忽略本条件
	 * 		1(0x01) 周一
	 *		2(0x02) 周二
	 *		4(0x04) 周三
	 *		8(0x08) 周四
	 *		16(0x10)周五
	 *		32(0x20)周六
	 *		64(0x40)周日
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function getAttendRules(array $attendSettingIds, $workDayValue=NULL) {
		if (empty($attendSettingIds)) {
			log_err('attendSettingIds is empty');
			return false;
		}
		/*
			select distinct t_a.att_set_id, t_b.* from eb_attend_setting_t t_a join eb_attend_rule_t t_b
			on (work_day&1=1 and (t_a.att_rul_id1 = t_b.att_rul_id or t_a.att_rul_id2 = t_b.att_rul_id or t_a.att_rul_id3 = t_b.att_rul_id
			or t_a.att_rul_id4 = t_b.att_rul_id or t_a.att_rul_id5 = t_b.att_rul_id or t_a.att_rul_id6 = t_b.att_rul_id or t_a.att_rul_id7 = t_b.att_rul_id
			or t_a.att_rul_newid1 = t_b.att_rul_id or t_a.att_rul_newid2 = t_b.att_rul_id or t_a.att_rul_newid3 = t_b.att_rul_id
			or t_a.att_rul_newid4 = t_b.att_rul_id or t_a.att_rul_newid5 = t_b.att_rul_id or t_a.att_rul_newid6 = t_b.att_rul_id or t_a.att_rul_newid7 = t_b.att_rul_id))
			where t_a.att_set_id in (1)
			*/
		//拼接查询语句
		$workDaySql = "";
		if (isset($workDayValue))
			$workDaySql = "work_day&$workDayValue=$workDayValue and ";
		
		$sql = "select distinct t_a.att_set_id, t_b.* from eb_attend_setting_t t_a join eb_attend_rule_t t_b "
				."on ($workDaySql (t_a.att_rul_id1 = t_b.att_rul_id or t_a.att_rul_id2 = t_b.att_rul_id or t_a.att_rul_id3 = t_b.att_rul_id "
				."or t_a.att_rul_id4 = t_b.att_rul_id or t_a.att_rul_id5 = t_b.att_rul_id or t_a.att_rul_id6 = t_b.att_rul_id or t_a.att_rul_id7 = t_b.att_rul_id "
				."or t_a.att_rul_newid1 = t_b.att_rul_id or t_a.att_rul_newid2 = t_b.att_rul_id or t_a.att_rul_newid3 = t_b.att_rul_id "
				."or t_a.att_rul_newid4 = t_b.att_rul_id or t_a.att_rul_newid5 = t_b.att_rul_id or t_a.att_rul_newid6 = t_b.att_rul_id or t_a.att_rul_newid7 = t_b.att_rul_id)) "
				."where t_a.att_set_id in (".implode(',', $attendSettingIds).")";

		$limit = 1000;
		$result = $this->simpleSearch($sql, null, null, null, null, $limit);
		return $result;
	}
	
	/**
	 * 获取考勤时间段列表
	 * 注意：结果已按signin_time升序,signout_time升序
	 * @param array $ruleIds 考勤规则列表
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function getAttendTimes(array $ruleIds) {
		if (empty($ruleIds)) {
			log_err('getAttendTimes error, ruleIds is empty');
			return false;
		}
		
		/*
		select distinct t_a.att_rul_id, t_b.* from eb_attend_rule_t t_a join eb_attend_time_t t_b
		on (t_a.att_tim_id1 = t_b.att_tim_id or t_a.att_tim_id2 = t_b.att_tim_id or t_a.att_tim_id3 = t_b.att_tim_id or t_a.att_tim_id4 = t_b.att_tim_id
				or t_a.att_tim_newid1 = t_b.att_tim_id or t_a.att_tim_newid2 = t_b.att_tim_id or t_a.att_tim_newid3 = t_b.att_tim_id or t_a.att_tim_newid4 = t_b.att_tim_id)
		where t_a.att_rul_id in (1)
		order by t_b.signin_time, t_b.signout_time desc
		 */
		//拼接查询语句
		$sql = "select distinct t_a.att_rul_id, t_b.* from eb_attend_rule_t t_a join eb_attend_time_t t_b "
				."on (t_a.att_tim_id1 = t_b.att_tim_id or t_a.att_tim_id2 = t_b.att_tim_id or t_a.att_tim_id3 = t_b.att_tim_id or t_a.att_tim_id4 = t_b.att_tim_id "
				."or t_a.att_tim_newid1 = t_b.att_tim_id or t_a.att_tim_newid2 = t_b.att_tim_id or t_a.att_tim_newid3 = t_b.att_tim_id or t_a.att_tim_newid4 = t_b.att_tim_id) "
				."where t_a.att_rul_id in (".implode(',', $ruleIds).") order by t_b.signin_time, t_b.signout_time";
	
		$limit = 1000;
		$result = $this->simpleSearch($sql, null, null, null, null, $limit);
		return $result;
	}
	
	/**
	 * 获取考勤时间段
	 * @param {string} $attTimId
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function getAttendTime($attTimId) {
		if (!isset($attTimId)) {
			log_err('getAttendTime error, $attTimId is empty');
			return false;
		}
	
		$sql = "select att_tim_id, name, signin_time, signin_ignore, signout_time, signout_ignore, rest_duration, work_duration, create_time from eb_attend_time_t "
				."where att_tim_id=$attTimId";
	
		$result = $this->simpleSearch($sql);
		return $result;
	}
	
	/**
	 * 获取未生效的配置记录
	 * @param {string} $entCode 企业编号
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function getNeedEffectiveRecords($entCode) {
		/*
		 select t_a.*, t_b.att_rul_id, t_b.work_day, t_b.flexible_work, t_b.flag, t_b.att_tim_id1, t_b.att_tim_id2, t_b.att_tim_id3, t_b.att_tim_id4
		 ,t_b.att_tim_newid1, t_b.att_tim_newid2, t_b.att_tim_newid3, t_b.att_tim_newid4, t_b.create_time as rule_create_time
		 from eb_attend_setting_t t_a join eb_attend_rule_t t_b
		 on (t_a.att_rul_id1=t_b.att_rul_id or t_a.att_rul_id2=t_b.att_rul_id or t_a.att_rul_id3=t_b.att_rul_id or t_a.att_rul_id4=t_b.att_rul_id
		 or t_a.att_rul_id5=t_b.att_rul_id or t_a.att_rul_id6=t_b.att_rul_id or t_a.att_rul_id7=t_b.att_rul_id
		 or t_a.att_rul_newid1 = t_b.att_rul_id or t_a.att_rul_newid2 = t_b.att_rul_id or t_a.att_rul_newid3 = t_b.att_rul_id
		 or t_a.att_rul_newid4 = t_b.att_rul_id or t_a.att_rul_newid5 = t_b.att_rul_id or t_a.att_rul_newid6 = t_b.att_rul_id or t_a.att_rul_newid7 = t_b.att_rul_id)
		 where owner_type = 1 and owner_id = 1000000000000030
		 and (t_a.att_rul_newid1<>0 or t_a.att_rul_newid2<>0 or t_a.att_rul_newid3<>0 or t_a.att_rul_newid4<>0 or t_a.att_rul_newid5<>0 or t_a.att_rul_newid6<>0 or t_a.att_rul_newid7<>0
		 or t_b.att_tim_newid1<>0 or t_b.att_tim_newid2<>0 or t_b.att_tim_newid3<>0 or t_b.att_tim_newid4<>0)
		 */
		$sql = "select t_a.*, t_b.att_rul_id, t_b.work_day, t_b.flexible_work, t_b.flag, t_b.att_tim_id1, t_b.att_tim_id2, t_b.att_tim_id3, t_b.att_tim_id4 "
				.",t_b.att_tim_newid1, t_b.att_tim_newid2, t_b.att_tim_newid3, t_b.att_tim_newid4, t_b.create_time as rule_create_time "
				."from eb_attend_setting_t t_a join eb_attend_rule_t t_b "
				."on (t_a.att_rul_id1=t_b.att_rul_id or t_a.att_rul_id2=t_b.att_rul_id or t_a.att_rul_id3=t_b.att_rul_id or t_a.att_rul_id4=t_b.att_rul_id "
				."or t_a.att_rul_id5=t_b.att_rul_id or t_a.att_rul_id6=t_b.att_rul_id or t_a.att_rul_id7=t_b.att_rul_id "
				."or t_a.att_rul_newid1 = t_b.att_rul_id or t_a.att_rul_newid2 = t_b.att_rul_id or t_a.att_rul_newid3 = t_b.att_rul_id "
				."or t_a.att_rul_newid4 = t_b.att_rul_id or t_a.att_rul_newid5 = t_b.att_rul_id or t_a.att_rul_newid6 = t_b.att_rul_id or t_a.att_rul_newid7 = t_b.att_rul_id) "
				."where owner_type = 1 and owner_id = $entCode "
				."and (t_a.att_rul_newid1<>0 or t_a.att_rul_newid2<>0 or t_a.att_rul_newid3<>0 or t_a.att_rul_newid4<>0 or t_a.att_rul_newid5<>0 or t_a.att_rul_newid6<>0 or t_a.att_rul_newid7<>0 "
				."or t_b.att_tim_newid1<>0 or t_b.att_tim_newid2<>0 or t_b.att_tim_newid3<>0 or t_b.att_tim_newid4<>0)"
				;

		$limit = 1000;
		return $this->simpleSearch($sql, null, null, null, null, $limit);
	}
	
	/**
	 * 使未生效的考勤规则生效
	 * @param {string} $aSetId 考勤配置编号
	 * @param {array} $newRulIds 考勤规则编号数组
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function effectRuleNewIds($aSetId, $newRulIds) {
		if (empty($aSetId) || empty($newRulIds)) {
			log_err('effectRuleNewIds error, $aSetId or $newRulIds is empty');
			return false;
		}
		
		$sets = array();
		$setCheckDigits = array();
		foreach ($newRulIds as $i=>$newRulId) {
			if ($newRulId==='-1') {
				$sets["att_rul_id$i"] = '0';
				$sets["att_rul_newid$i"] = '0';
			} else if ($newRulId!=='0') {
				$sets["att_rul_id$i"] = $newRulId;
				$sets["att_rul_newid$i"] = '0';
			}
			
			array_push($setCheckDigits, "att_rul_id$i");
			array_push($setCheckDigits, "att_rul_newid$i");
		}
		log_info($sets);
		
		if (empty($sets)) {
			log_err('effectRuleNewIds error, nothing to update');
			return false;
		}
		
		$wheres = array('att_set_id'=>$aSetId);
		return $this->update($sets, $wheres, $setCheckDigits, array('att_set_id'));
	}
}