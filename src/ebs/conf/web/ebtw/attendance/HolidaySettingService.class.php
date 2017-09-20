<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class HolidaySettingService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'hol_set_id';
		$this->tableName = 'eb_holiday_setting_t';
		$this->fieldNames = 'hol_set_id, name, create_uid, create_time, last_uid, last_time, start_time, stop_time, period, period_from, period_to, flag, disable, owner_id, owner_type';
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
	 * 获取假期设置适用对象列表
	 * @param array $hoSettingIds 假期设置编号列表
	 * @param {string} $entCode [可选] 企业编号
	 * @param {array} $groupCodes [可选] 部门或群组编号列表
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	public function getHolidayTargets(array $hoSettingIds, $entCode, array $groupCodes) {
		if (empty($hoSettingIds)) {
			log_err('getHolidayTargets error, $hoSettingIds is empty');
			return false;
		}
	
		/*
		select * from (
			select t_a.*, t_b.ent_name as target_name, '' as ext_name, '' as user_account from eb_hol_set_target_t t_a, enterprise_info_t t_b
			where t_a.target_type =1 and t_a.target_id = t_b.ent_id and t_a.hol_set_id in (1)
		
			union all
			select t_a.*, t_b.dep_name as target_name, '' as ext_name, '' as user_account from eb_hol_set_target_t t_a, department_info_t t_b
			where t_a.target_type = 2 and t_a.target_id = t_b.group_id and t_a.hol_set_id in (1)
		
			union all
			select t_a.*, TB3.username as target_name, dep_name as ext_name, t_b.account as user_account from eb_hol_set_target_t t_a
			join user_account_t t_b on (t_a.target_id = t_b.user_id)
			left join (
			select t_x.group_id, t_x.dep_name, t_y.emp_uid, t_y.username from employee_info_t t_y, department_info_t t_x where t_x.group_id=t_y.group_id
			and (t_x.ent_id = 1000000000000030 or t_x.group_id in (999001))
			) TB3 on (t_a.target_id=TB3.emp_uid)
			where t_a.target_type = 3 and t_a.hol_set_id in (1)
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
			$hoSettingSql = implode(',', $hoSettingIds);
	
			//拼接SQL
			$sql = "select * from ("
					."select t_a.*, t_b.ent_name as target_name, '' as ext_name, '' as user_account from eb_hol_set_target_t t_a, enterprise_info_t t_b "
					."where t_a.target_type =1 and t_a.target_id = t_b.ent_id and t_a.hol_set_id in ($hoSettingSql) "
					."union all "
					."select t_a.*, t_b.dep_name as target_name, '' as ext_name, '' as user_account from eb_hol_set_target_t t_a, department_info_t t_b "
					."where t_a.target_type = 2 and t_a.target_id = t_b.group_id and t_a.hol_set_id in ($hoSettingSql) "
					."union all "
					."select t_a.*, TB3.username as target_name, dep_name as ext_name, t_b.account as user_account from eb_hol_set_target_t t_a "
					."join user_account_t t_b on (t_a.target_id = t_b.user_id) "
					."left join ("
					."select t_x.group_id, t_x.dep_name, t_y.emp_uid, t_y.username from employee_info_t t_y, department_info_t t_x where t_x.group_id=t_y.group_id "
					."and ($ownerSql) "
					.") TB3 on (t_a.target_id=TB3.emp_uid) "
					."where t_a.target_type = 3 and t_a.hol_set_id in ($hoSettingSql) "
					.") TMP"
					;

			$limit = 1000;
			$orderBy = 'target_type, target_name, ext_name';
			return $this->simpleSearch($sql, null, null, null, $orderBy, $limit);
	}	
	
	/**
	 * 查询全部假期配置列表
	 * @param {string} $entCode 企业编号
	 * @param {array} $groupCodes 群组的编号列表
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAllHolidays($entCode, array $groupCodes) {
		if (!isset($entCode) && empty($groupCodes) && isset($userId)) {
			log_err('getAllHolidays error, $entCode and $groupCodes are all empty');
			return false;
		}
		//归属条件
		$ownerSql = "";
		if (isset($entCode))
			$ownerSql .= "(owner_id = $entCode and owner_type = 1)";
		if (!empty($groupCodes)) {
			if (!empty($ownerSql))
				$ownerSql .= " or ";
			$ownerSql .= "(owner_id in (".implode(',', $groupCodes).") and owner_type=2)";
		}
		
		//拼接sql
		$sql = "select * from eb_holiday_setting_t where ($ownerSql)";
		
		$limit = 1000;
		$orderBy = "create_time desc";
		return $this->simpleSearch($sql, null, null, null, $orderBy, $limit);
	}
	
	/**
	 * 查询指定日期的假期记录
	 * @param {string} $entCode 企业编号
	 * @param {array} $groupCodes 群组的编号列表
	 * @param {string} $userId 用户编号
	 * @param {string} $attendDate 考勤日期
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getHolidays($entCode, $groupCodes, $userId, $attendDate) {
		/*
		SELECT t_b.target_id, t_b.target_type, t_a.* from eb_holiday_setting_t t_a join eb_hol_set_target_t t_b on t_a.hol_set_id = t_b.hol_set_id
		where ((target_type=1 and target_id=1000000000000030) or (target_type=2 and target_id=111) or (target_type=3 and target_id=80)) and disable=0
		and ((period=0 and start_time>='2017-03-09 00:00:00' and stop_time<='2017-03-09 23:59:59') or (period=1 and period_from<=1001 and period_to>=1007)
		or (period=2 and period_from<=11 and period_to>=12) or (period=3 and period_from<=4 and period_to>=4))
		order by target_type desc
		 */
		//归属条件
		$targetSql = "";
		if (isset($userId)) {
			$targetSql .= "(target_id=$userId and target_type=3)";
		}
		if (!empty($groupCodes)) {
			if (!empty($targetSql))
				$targetSql .= " or ";
			$targetSql .= "(target_id in (".implode(',', $groupCodes).") and target_type=2)";
		}
		if (isset($entCode)) {
			if (!empty($targetSql))
				$targetSql .= " or ";
			$targetSql .= "(target_id = $entCode and target_type = 1)";
		}
		
		$attendDateTime = strtotime($attendDate);
		$period1 = intval(date('md', $attendDateTime));
		$period2 = intval(date('d', $attendDateTime));
		$period3 = intval(date('w', $attendDateTime));
		//拼接查询语句
		$sql = "SELECT t_b.target_id, t_b.target_type, t_a.* from eb_holiday_setting_t t_a join eb_hol_set_target_t t_b on t_a.hol_set_id = t_b.hol_set_id "
				."where ($targetSql) and disable=0 "
				."and ((period=0 and start_time>='$attendDate 00:00:00' and stop_time<='$attendDate 23:59:59') " //一次性假期
						."or (period=1 and period_from<=$period1 and period_to>=$period1) " //每年假期
						."or (period=2 and period_from<=$period2 and period_to>=$period2) " //每月假期
						."or (period=3 and period_from<=$period3 and period_to>=$period3)) " //每周假期
				."order by target_type desc";
		
		$result = $this->simpleSearch($sql);
		return $result;
	}
	
	/**
	 * 在指定一个时间段内，与假期一起确定考勤情况
	 * @param {string} $entCode 企业编号
	 * @param {array} $groupCodes 群组的编号列表
	 * @param {string} $userId 用户编号
	 * @param {string} $attendDate 考勤日期
	 * @param {int} $flexibleWork 是否弹性工作机制 0=严格按照考勤时间段，1=满足工作时长条件
	 * @param {int} $signinIgnore 不计入迟到的时长(分钟)
	 * @param {int} $signoutIgnore 不计入早退的时长(分钟)
	 * @param {timestamp} $signinTime 时间签到时间，注意：可能为空
	 * @param {timestamp} $signoutTime 实际签退时间，注意：可能为空
	 * @param {timestamp} $standardSigninTime 参考签到时间
	 * @param {timestamp} $standardSignoutTime 参考签退时间
	 * @param {int} $recState [输出参数，输入时已有值] 考勤状态
	 * @return boolean 是否成功执行
	 */
	function checkHoliday($entCode, $groupCodes, $userId, $attendDate, $flexibleWork, $signinIgnore, $signoutIgnore, $signinTime, $signoutTime
			, $standardSigninTime, $standardSignoutTime, &$recState) {
		$results = $this->getHolidays($entCode, $groupCodes, $userId, $attendDate);
		if ($results===false) {
			log_err('getHolidays error');
			return false;
		}
		if (empty($results))
			return true;
		
		//遍历假期记录，为异常考勤做"减法"
		foreach ($results as $holidayRec) {
			//$period = intval($holidayRec['period']);
			//取消几种考勤异常状态
			$cancelState = ~ATTENDANCE_STATE_ABNORMAL_GROUP;
			$flag = intval($holidayRec['flag']);
			$holidayMiddleTime = strtotime($attendDate." 12:00:00");
			
			switch ($flag) {
				case 0: //全天
					$recState &= $cancelState;
					log_debug('standardSigninTime '.date('Y-m-d H:i:s', $standardSigninTime)."===============all day is holiday");
					break;
				case 1: //上半天
				case 2: //下半天
					if ($flexibleWork===0) { //非弹性工作机制，严格按照考勤时间段
						if ($flag==1) { //上半天
							//$holidayTime = strtotime($attendDate." 00:00:00");
							if ($standardSignoutTime<=$holidayMiddleTime) { //"假期时间段"包含"参考考勤时间段"
								$recState &= $cancelState;
								log_debug(date('Y-m-d H:i:s', $standardSigninTime)." - ".date('Y-m-d H:i:s', $standardSignoutTime)." flag=$flag ===============it's holiday");
							} else if ($standardSigninTime>=$holidayMiddleTime) { //"假期时间段"与"参考考勤时间段"没有交集
								//miss
							} else { //有交集
								//log_debug("--------------------------------$recState ---->".(($recState&ATTEND_STATE_LATE)==ATTEND_STATE_LATE));
								if (($recState&ATTEND_STATE_UNSIGNIN)==ATTEND_STATE_UNSIGNIN 
										|| ($recState&ATTEND_STATE_UNSIGNOUT)==ATTEND_STATE_UNSIGNOUT 
										|| ($recState&ATTEND_STATE_ABSENTEEISM)==ATTEND_STATE_ABSENTEEISM
										|| ($recState&ATTEND_STATE_LEFT_EARLY)==ATTEND_STATE_LEFT_EARLY) { //没签到、没签退、旷工、早退
									//miss
								}
								if (($recState&ATTEND_STATE_LATE)==ATTEND_STATE_LATE && !empty($signinTime)) { //迟到
									$signinTime = strtotime("-$signinIgnore minute", $signinTime); //实际签到时间往前移N分钟
									log_debug("xxxxxxxxxxxxxxx new signinTime ".date("Y-m-d H:i:s", $signinTime));
									if ($signinTime<=$holidayMiddleTime) { //签到时间不大于假期结束时间，就当作没有迟到
										$recState &= ~ATTEND_STATE_LATE;
										log_debug('standardSigninTime '.date('Y-m-d H:i:s', $standardSigninTime)." flag=$flag ===============not late");
									}
								}
							}
						} else { //下半天
							//$holidayTime = strtotime($attendDate." 23:59:59");
							if ($standardSigninTime>=$holidayMiddleTime) { //"假期时间段"包含"参考考勤时间段"
								$recState &= $cancelState;
								log_debug(date('Y-m-d H:i:s', $standardSigninTime)." - ".date('Y-m-d H:i:s', $standardSignoutTime)." flag=$flag ===============it's holiday");
							} else if ($standardSignoutTime<=$holidayMiddleTime) { //"假期时间段"与"参考考勤时间段"没有交集
								//miss
							} else { //有交集
								if (($recState&ATTEND_STATE_UNSIGNIN)==ATTEND_STATE_UNSIGNIN
										|| ($recState&ATTEND_STATE_UNSIGNOUT)==ATTEND_STATE_UNSIGNOUT
										|| ($recState&ATTEND_STATE_ABSENTEEISM)==ATTEND_STATE_ABSENTEEISM
										|| ($recState&ATTEND_STATE_LATE)==ATTEND_STATE_LATE) { //没签到、没签退、旷工、迟到
											//miss
								}
								if (($recState&ATTEND_STATE_LEFT_EARLY)==ATTEND_STATE_LEFT_EARLY && !empty($signoutTime)) { //早退
									$signoutTime = strtotime("+$signoutIgnore minute", $signoutTime); //签退时间往后移N分钟
									log_debug("xxxxxxxxxxxxxxx new signoutTime ".date("Y-m-d H:i:s", $signoutTime));
									if ($signoutTime>=$holidayMiddleTime) { //签退时间不小于假期开始时间，就当作没有早退
										$recState &= ~ATTEND_STATE_LEFT_EARLY;
										log_debug('standardSignoutTime '.date('Y-m-d H:i:s', $standardSignoutTime)." flag=$flag ==============not left ealy");
									}
								}
							}
						}
					} else { //弹性工作制，满足工作时长条件即可
						//思路：按"假期时间段"与"考勤时间段"算出所需的工作时长
						if ($flag==1) { //上半天
							if ($standardSignoutTime<=$holidayMiddleTime) { //"假期时间段"包含"参考考勤时间段"
								$recState &= $cancelState;
								log_debug(date('Y-m-d H:i:s', $standardSigninTime)." - ".date('Y-m-d H:i:s', $standardSignoutTime)
										." flexibleWork=$flexibleWork flag=$flag ===============it's holiday");
							} else if ($standardSigninTime>=$holidayMiddleTime) { //"假期时间段"与"参考考勤时间段"没有交集
								//miss
							} else {
								if (($recState&ATTEND_STATE_UNSIGNIN)==ATTEND_STATE_UNSIGNIN
										|| ($recState&ATTEND_STATE_UNSIGNOUT)==ATTEND_STATE_UNSIGNOUT
										|| ($recState&ATTEND_STATE_ABSENTEEISM)==ATTEND_STATE_ABSENTEEISM) { //没签到、没签退、旷工
									//miss
								}
								if ((($recState&ATTEND_STATE_LATE)==ATTEND_STATE_LATE && !empty($signinTime))
										|| (($recState&ATTEND_STATE_LEFT_EARLY)==ATTEND_STATE_LEFT_EARLY && !empty($signoutTime))) { //迟到、早退
									$diff = $standardSignoutTime - $holidayMiddleTime;
									$realDiff = $signoutTime - $signinTime;
									log_debug("realDiff=$realDiff, diff=$diff");
									if ($realDiff >= $diff) {
										$recState &= ~(ATTEND_STATE_LATE | ATTEND_STATE_LEFT_EARLY);
										log_debug(date('Y-m-d H:i:s', $standardSigninTime)." - ".date('Y-m-d H:i:s', $standardSignoutTime)
												." flexibleWork=$flexibleWork flag=$flag ==============not left ealy and late");
									}
								}
							}
						} else { //下半天
							if ($standardSigninTime>=$holidayMiddleTime) { //"假期时间段"包含"参考考勤时间段"
								$recState &= $cancelState;
								log_debug(date('Y-m-d H:i:s', $standardSigninTime)." - ".date('Y-m-d H:i:s', $standardSignoutTime)
										." flexibleWork=$flexibleWork flag=$flag ===============it's holiday");
							} else if ($standardSignoutTime<=$holidayMiddleTime) { //"假期时间段"与"参考考勤时间段"没有交集
								//miss
							} else {
								if (($recState&ATTEND_STATE_UNSIGNIN)==ATTEND_STATE_UNSIGNIN
										|| ($recState&ATTEND_STATE_UNSIGNOUT)==ATTEND_STATE_UNSIGNOUT
										|| ($recState&ATTEND_STATE_ABSENTEEISM)==ATTEND_STATE_ABSENTEEISM) { //没签到、没签退、旷工
									//miss
								}
								if ((($recState&ATTEND_STATE_LATE)==ATTEND_STATE_LATE && !empty($signinTime))
										|| (($recState&ATTEND_STATE_LEFT_EARLY)==ATTEND_STATE_LEFT_EARLY && !empty($signoutTime))) { //迟到、早退
									$diff = $standardSignoutTime - $holidayMiddleTime;
									$realDiff = $signoutTime - $signinTime;
									log_debug("realDiff=$realDiff, diff=$diff");
									
									if ($realDiff>=$diff) {
										$recState &= ~(ATTEND_STATE_LATE | ATTEND_STATE_LEFT_EARLY);
										log_debug(date('Y-m-d H:i:s', $standardSigninTime)." - ".date('Y-m-d H:i:s', $standardSignoutTime)
												." flexibleWork=$flexibleWork flag=$flag ==============not left ealy and late");
									}
								}
							}
						}
					}
				break;
			}
			
		}
		
	}
}