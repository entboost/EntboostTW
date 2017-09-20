<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

/**
 * 考勤日结服务
 *
 */
class AttendDailyService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'att_dai_id';
		$this->tableName = 'eb_attend_daily_t';
		$this->fieldNames = 'att_dai_id, owner_type, owner_id, user_id, user_name, create_time, attend_date, invalid, att_rec_id0_state, att_rec_id1_state, att_rec_id2_state'
				.', att_rec_id3_state, att_rec_id4_state, att_rec_id0, att_rec_id1, att_rec_id2, att_rec_id3, att_rec_id4, calcul_day, expected_count, expected_duration'
				.', real_count, real_duration, work_overtime_count, work_overtime_duration, abnormal_count, signin_count, unsignin_count, late_count, signout_count'
				.', unsignout_count, leave_early_count, work_outside_count, work_outside_duration';
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
	 * 查询未被处理过的用户列表
	 * @param {string} $attendDate 考勤日期，格式如：2017-01-01
	 * @param {string} $entCode 企业编号
	 * @param {boolean} $enabled 禁用标记，默认选取非禁用用户
	 * @param {int} $limit 返回的最大记录数量
	 * @return array|boolean false:查询失败，array:查询结果列表
	 */
	function get_noprocessed_users($attendDate, $entCode, $enabled, $limit=100) {
		/*
		 select * from 
		(SELECT DISTINCT t_a.user_id, t_a.username as user_name, t_a.account user_account from user_account_t t_a, employee_info_t t_b, department_info_t t_c 
		where t_a.user_id = t_b.emp_uid and t_b.group_id = t_c.group_id and t_a.state<>-1 and t_c.ent_id = 1000000000000030) TMP 
		where user_id not in (SELECT user_id from eb_attend_daily_t where attend_date = '2017-08-01' and invalid=0 and owner_type=1 and owner_id=1000000000000030)
		 */
		
		$stateSql = "";
		if (isset($enabled)) {
			$stateSql = "and t_a.state".($enabled?"<>-1":"=-1");
		}
		
		$sql = "select * from "
				."(SELECT DISTINCT t_a.user_id, t_a.username as user_name, t_a.account as user_account from user_account_t t_a, employee_info_t t_b, department_info_t t_c "
				."where t_a.user_id = t_b.emp_uid and t_b.group_id = t_c.group_id $stateSql and t_c.ent_id = ?) TMP "
				."where user_id not in (SELECT user_id from eb_attend_daily_t where attend_date = ? and invalid=0 and owner_type=1 and owner_id=?)";
		$conditions = array($entCode, $attendDate, $entCode);
		
		$results = $this->simpleSearch($sql, $conditions, null, null, null, $limit);
		return $results;
	}
	
	/**
	 * 查询考勤日结记录
	 * @param {string} $userId 用户编号(数字)
	 * @param {boolean} $forCount 是否仅查询数量，默认false
	 * @param {boolean} $excludeNormalRecState 是否排除已通过审批的记录，默认NULL
	 * @param {boolean} $ignoreZeroTimidRecord 是否忽略没有绑定考勤规则的考勤记录，默认NULL;注意：$ignoreZeroTimidRecord和$initiativeZeroTimidRecord不可以同时等于true
	 * @param {boolean} $initiativeZeroTimidRecord 是否主动获取没有绑定考勤规则的考勤记录，默认NULL;注意：$ignoreZeroTimidRecord和$initiativeZeroTimidRecord不可以同时等于true
	 * @param {int} $recState 考勤状态
	 * @param {string} $attendDateStart 考勤日期开始
	 * @param {string} $attendDateEnd 考勤日期结束
	 * @param {string} $entCode 企业编号
	 * @param {string} $groupCode 群组编号(保留，暂不支持)
	 * @param {string} $orderBy 排序
	 * @param {int} $limit 返回最大记录数量
	 * @param {int} $offset 偏移量
	 * @return boolean|array false=查询失败，array=查询结果
	 */	
	function getRecords($userId, $forCount=false, $excludeNormalRecState=false, $ignoreZeroTimidRecord=NULL, $initiativeZeroTimidRecord=NULL, $recState=NULL, $attendDateStart=NULL, $attendDateEnd=NULL, $entCode=NULL, $groupCode=NULL, $orderBy=NULL, $limit=100, $offset=0) {
		/*
		select TMP.*, t_c.signin_time as standard_signin_time, t_c.signout_time as standard_signout_time from (
		select t_b.att_rec_id, t_b.owner_type, t_b.owner_id, t_b.user_id, t_b.user_name, t_b.create_time, t_b.att_rul_id, t_b.att_tim_id,
		t_b.signin_time, t_b.signout_time, t_b.req_signin_time, t_b.req_signout_time, t_b.work_duration, t_a.attend_date
			, t_a.att_rec_id0, t_a.att_rec_id0_state, t_a.att_rec_id1, t_a.att_rec_id1_state, t_a.att_rec_id2, t_a.att_rec_id2_state
			, t_a.att_rec_id3, t_a.att_rec_id3_state, t_a.att_rec_id4, t_a.att_rec_id4_state
		from eb_attend_daily_t t_a , eb_attend_record_t t_b
		where nd ((t_a.att_rec_id0 = t_b.att_rec_id and t_a.att_rec_id0_state<>0 and (t_a.att_rec_id0_state&2=2 /or t_b.att_tim_id=0 and t_b.att_tim_id<>0/)) 
			or (t_a.att_rec_id1 = t_b.att_rec_id and t_a.att_rec_id1_state<>0 and (t_a.att_rec_id1_state&2=2 /or t_b.att_tim_id=0 and t_b.att_tim_id<>0/)) 
			or (t_a.att_rec_id2 = t_b.att_rec_id and t_a.att_rec_id2_state<>0 and (t_a.att_rec_id2_state&2=2 /or t_b.att_tim_id=0 and t_b.att_tim_id<>0/)) 
			or (t_a.att_rec_id3 = t_b.att_rec_id and t_a.att_rec_id3_state<>0 and (t_a.att_rec_id3_state&2=2 /or t_b.att_tim_id=0 and t_b.att_tim_id<>0/)) 
			or (t_a.att_rec_id4 = t_b.att_rec_id and t_a.att_rec_id4_state<>0 and (t_a.att_rec_id4_state&2=2 /or t_b.att_tim_id=0 and t_b.att_tim_id<>0/)))
		) TMP left join eb_attend_time_t t_c on TMP.att_tim_id = t_c.att_tim_id
		where TMP.user_id = 80 and TMP.attend_date >='2017-05-09' and TMP.attend_date <='2017-05-30' and TMP.owner_type = 1 and TMP.owner_id = 1000000000000030
		order by attend_date desc, standard_signin_time desc
		*/
		
		$notAbNormalState = ATTENDANCE_STATE_NOT_ABNORMAL_GROUP; //非异常状态组合
		
		$drSql = '';
		for ($i=0; $i<=4; $i++) {
			if ($i!=0)
				$drSql .= 'or ';
			$drSql .= "(t_a.att_rec_id$i = t_b.att_rec_id ";
			
			if (!empty($excludeNormalRecState))
				$drSql .= 'and t_a.att_rec_id'.$i.'_state<>0 ';
			if (isset($recState)) {
				$drSql .= "and (t_a.att_rec_id".$i."_state&$recState=$recState ";
				if ($ignoreZeroTimidRecord===true) {
					$drSql .= "and t_b.att_tim_id<>0 ";
				} 
				if ($initiativeZeroTimidRecord===true) {
					$drSql .= "or t_b.att_tim_id=0 ";
				}
				$drSql .= ") ";
			}
			
			$drSql .= (!empty($excludeNormalRecState)?"and t_a.att_rec_id".$i."_state&$notAbNormalState=0":"").") ";
		}
// 		$drSql = "(t_a.att_rec_id0 = t_b.att_rec_id ".(isset($recState)?"and t_a.att_rec_id0_state&$recState=$recState ":" ").(!empty($excludeNormalRecState)?"and t_a.att_rec_id0_state&$notAbNormalState=0":"").") "
// 				."or (t_a.att_rec_id1 = t_b.att_rec_id ".(isset($recState)?"and t_a.att_rec_id1_state&$recState=$recState ":" ").(!empty($excludeNormalRecState)?"and t_a.att_rec_id1_state&$notAbNormalState=0":"").") "
// 				."or (t_a.att_rec_id2 = t_b.att_rec_id ".(isset($recState)?" and t_a.att_rec_id2_state&$recState=$recState ":" ").(!empty($excludeNormalRecState)?"and t_a.att_rec_id2_state&$notAbNormalState=0":"").") "
// 				."or (t_a.att_rec_id3 = t_b.att_rec_id ".(isset($recState)?" and t_a.att_rec_id3_state&$recState=$recState ":" ").(!empty($excludeNormalRecState)?"and t_a.att_rec_id3_state&$notAbNormalState=0":"").") "
// 				."or (t_a.att_rec_id4 = t_b.att_rec_id ".(isset($recState)?" and t_a.att_rec_id4_state&$recState=$recState ":" ").(!empty($excludeNormalRecState)?"and t_a.att_rec_id4_state&$notAbNormalState=0":"").") "
// 				;
		
		$sql = "select TMP.*, t_c.signin_time as standard_signin_time, t_c.signout_time as standard_signout_time from ( "
				."select t_b.att_rec_id, t_b.owner_type, t_b.owner_id, t_b.user_id, t_b.user_name, t_b.create_time, t_b.att_rul_id, t_b.att_tim_id, "
				."t_b.signin_time, t_b.signout_time, t_b.req_signin_time, t_b.req_signout_time, t_b.work_duration, t_a.attend_date "
				.", t_a.att_rec_id0, t_a.att_rec_id0_state, t_a.att_rec_id1, t_a.att_rec_id1_state, t_a.att_rec_id2, t_a.att_rec_id2_state "
				.", t_a.att_rec_id3, t_a.att_rec_id3_state, t_a.att_rec_id4, t_a.att_rec_id4_state "
				."from eb_attend_daily_t t_a , eb_attend_record_t t_b "
				."where ($drSql) ";
		$sql .=	") TMP left join eb_attend_time_t t_c on TMP.att_tim_id = t_c.att_tim_id "
				."where TMP.user_id = ?";
		
		$conditions = array($userId);
		
		if (isset($attendDateStart)) {
			$sql .= " and TMP.attend_date >=?";
			array_push($conditions, "$attendDateStart"); //"$attendDateStart 00:00:00"
		}
		if (isset($attendDateEnd)) {
			$sql .= " and TMP.attend_date <=?";
			array_push($conditions, "$attendDateEnd 23:59:59");
		}
		
		if (isset($entCode)) {
			$sql .= " and TMP.owner_type = 1 and TMP.owner_id = ?";
			array_push($conditions, $entCode);
		}
		
		if (!isset($orderBy))
			$orderBy = 'attend_date desc, standard_signin_time desc';

		$sqlOfCount = 'select count(att_rec_id) as record_count from ({$sql}) temp_tb';
		if ($forCount) { //仅查询数量
			$result = $this->simpleSearchForCount($sqlOfCount, $sql, $conditions);
			return $result;
		} else {
			$results = $this->simpleSearch($sql, $conditions, null, null, $orderBy, $limit, $offset);
		}
		return $results;
	}
	
	/**
	 * 获取系统内企业资料(代码和名称 )
	 * @param {int} $limit 单次返回最大记录数
	 * @param {int} $offset 偏移量 
	 * @return boolean|array false=查询失败，array=查询结果
	 */
	function getEnterprises($limit=100, $offset=0) {
		$sql = 'select ent_id, ent_name from enterprise_info_t';
		$orderBy = 'ent_id';
		return $this->simpleSearch($sql, null, null, null, $orderBy, $limit, $offset);
	}
	
	/**
	 * 获取考勤日结记录
	 * @param {string} $entCode 企业编号
	 * @param array $groupCodes 群组编号
	 * @param {string} $userId [可选] 用户编号
	 * @param {string} $attendDate [可选] 考勤日期，格式如：2017-01-01
	 * @return array|boolean false:执行失败，array:执行结果列表
	 */
	function getDailyRecords($entCode, array $groupCodes, $userId=NULL, $attendDate=NULL) {
		if (!isset($entCode) && empty($groupCodes)) {
			log_err('getRecords error, $entCode and $groupCodes are all empty');
			return false;
		}
		
		$params = array();
		//归属条件
		if (isset($entCode)) {
			$params['owner_type'] = 1;
			$params['owner_id'] = $entCode;
		}
		if (!empty($groupCodes)) {
			$params['owner_type'] = 2;
			$params['owner_id'] = new SQLParam($groupCodes, 'owner_id', SQLParam::$OP_IN);
		}
		
		if (isset($userId))
			$params['user_id'] = $userId;
		if (isset($attendDate))
			$params['attend_date'] = $attendDate;
		
		$limit = 1000;
		$checkDigits = array('owner_type', 'owner_id', 'user_id');
		return $this->search($this->fieldNames, $params, $checkDigits, null, $limit);
	}
	
	/**
	 * 清除指定企业和指定考勤日期的日结记录
	 * @param {array} $attendDates 考勤日期列表，子元素格式如：2017-01-01
	 * @param {array} $entCodes 企业编号列表
	 * @param {boolean} $clearAllEnt 是否涉及所有企业，默认否(由$entCodes决定)
	 * @return array|boolean false:执行失败，array:执行结果列表
	 */
	function clearRecords($attendDates, $entCodes, $clearAllEnt=false) {
		/*
		 delete from eb_attend_daily_t where owner_type=1 and owner_id in (1000000000000030, 123) and attend_date in ('2017-05-10', '2017-05-11')
		 */
		$sql = "delete from eb_attend_daily_t where owner_type=1";
		$conditions = array();
		
		$checkDigits = array('owner_id');
		$params = array('attend_date'=>new SQLParam($attendDates, 'attend_date', SQLParam::$OP_IN));
		if ($clearAllEnt===false)
			$params['owner_id'] = new SQLParam($entCodes, 'owner_id', SQLParam::$OP_IN);
		
		$results = $this->simpleExecute($sql, $conditions, $params, $checkDigits);
		return $results;
	}
	
	/**
	 * 使指定企业和指定考勤日期的日结记录失效
	 * @param {array} $attendDates 考勤日期列表，子元素格式如：2017-01-01
	 * @param {array} $entCodes 企业编号列表
	 * @param {boolean} $clearAllEnt 是否涉及所有企业，默认否(由$entCodes决定)
	 * @return array|boolean false:执行失败，array:执行结果列表
	 */
	function invalidRecords($attendDates, $entCodes, $clearAllEnt=false) {
		//update eb_attend_daily_t set invalid=1 where owner_type=1 and owner_id in (1000000000000030, 123) and attend_date in ('2017-05-10', '2017-05-11')
		$sql = "update eb_attend_daily_t set invalid=1 where owner_type=1";
		$conditions = array();
		
		$checkDigits = array('owner_id');
		$params = array('attend_date'=>new SQLParam($attendDates, 'attend_date', SQLParam::$OP_IN));
		if ($clearAllEnt===false)
			$params['owner_id'] = new SQLParam($entCodes, 'owner_id', SQLParam::$OP_IN);
		
		$results = $this->simpleExecute($sql, $conditions, $params, $checkDigits);
		return $results;
	}
	
	/**
	 * 获取在一个考勤日期的"0"考勤日结记录
	 * $entCode、$groupCode至少一个不能为NULL
	 * @param {string} $entCode 企业编号
	 * @param {string} $groupCode 群组编号
	 * @param {string} $userId 用户编号(数字)
	 * @param {string} $attendDate 考勤日期，格式如：2017-01-01
	 * @return array|boolean false:执行失败，array:执行结果列表
	 */
	function getZeroRecordInOneAttendDate($entCode, $groupCode, $userId, $attendDate) {
		/*
		SELECT t_a.att_dai_id, t_a.attend_date, t_a.att_rec_id0, t_a.att_rec_id0_state, t_a.att_rec_id1, t_a.att_rec_id1_state, t_a.att_rec_id2, t_a.att_rec_id2_state
			, t_a.att_rec_id3, t_a.att_rec_id3_state, t_a.att_rec_id4, t_a.att_rec_id4_state, t_b.* 
		from eb_attend_daily_t t_a left join eb_attend_record_t t_b on t_a.att_rec_id0 = t_b.att_rec_id
		where t_a.user_id = 80 and t_a.attend_date = '2017-05-13'
		and t_a.owner_type = 1 and t_a.owner_id = 1000000000000030
		 */
		$sql = "SELECT t_a.att_dai_id, t_a.attend_date, t_a.att_rec_id0, t_a.att_rec_id0_state, t_a.att_rec_id1, t_a.att_rec_id1_state, t_a.att_rec_id2, t_a.att_rec_id2_state "
				.", t_a.att_rec_id3, t_a.att_rec_id3_state, t_a.att_rec_id4, t_a.att_rec_id4_state, t_b.* "
				."from eb_attend_daily_t t_a left join eb_attend_record_t t_b on t_a.att_rec_id0 = t_b.att_rec_id "
				."where t_a.user_id = $userId and t_a.attend_date = '$attendDate' ";
		if (isset($entCode))
			$sql .= "and t_a.owner_type = 1 and t_a.owner_id = $entCode ";
		if (isset($groupCode))
			$sql .= "and t_a.owner_type = 2 and t_a.owner_id = $groupCode ";
		
		$limit = 1000;
		return $this->simpleSearch($sql, null, null, null, null, $limit);
	}
	
	/**
	 * 考勤汇总查询
	 * @param {string} $entCode 企业编号
	 * @param {string} $userId [可选] 考勤人员的用户编号
	 * @param {boolean} $isAttendanceManager 是否考勤专员；考勤专员可查询全部人员，非考勤专员按$groupUids列表限制条件
	 * @param {array} $groupIds 部门/群组编号列表
	 * @param {array} $memberUids 部门/群组内成员的用户编号列表
	 * @param {object} $formObj 封装查询条件的对象
	 * @param {boolean} $forCount 是否仅查询数量，默认false
	 * @return {boolean|array} false=查询失败，array数组 [0]=总数量result, [1]=结果列表array
	 */
	function collectedRecords($entCode, $userId, $isAttendanceManager, array $groupIds, array $memberUids, $formObj, $forCount=false) {
		if (empty($entCode)) {
			log_err('$entCode is empty');
			return false;
		}
		
		//提取查询条件
		$searchTimeS = $formObj->search_time_s;
		$searchTimeE = $formObj->search_time_e;
		$userName = escapeQuotes($formObj->user_name);
		$searchGroupId = $formObj->search_group_id;
		$searchUserId = $formObj->search_user_id;
		//检查查询字段合法性
		if (isset($searchGroupId)) {
			if(!EBModelBase::checkDigit($searchGroupId, $errMsg, 'search_group_id')) {
				log_err($errMsg);
				return false;
			}
		}
		if (isset($searchUserId)) {
			if(!EBModelBase::checkDigit($searchUserId, $errMsg, 'search_user_id')) {
				log_err($errMsg);
				return false;
			}
		}
		
		/*
		select distinct TMP.user_name, TMP.user_id, TMP.user_account, max(dep_name) as dep_name, calcul_days, expected_counts, expected_durations, real_counts, real_durations, work_overtime_counts, work_overtime_durations, abnormal_counts
				, (select count(att_req_id) from eb_attend_req_t where user_id=TMP.user_id and owner_type=1 and owner_id = 1000000000000030 
					and req_type=3 and req_status=2 and ((start_time>='2017-05-01 01:00:00' and start_time<='2017-07-30 23:59:59.000') or (stop_time>='2017-05-01 01:00:00' and stop_time<='2017-07-30 23:59:59.000')) limit 1) as furlough_count 
		from (
			select t_a.user_id, t_a.user_name, t_c.group_id, t_c.dep_name, t_d.account as user_account, sum(calcul_day) as calcul_days
				, sum(expected_count) as expected_counts, sum(expected_duration) as expected_durations
				, sum(real_count) as real_counts, sum(real_duration) as real_durations, sum(work_overtime_count) as work_overtime_counts
				, sum(work_overtime_duration) as work_overtime_durations, sum(abnormal_count) as abnormal_counts
			from eb_attend_daily_t t_a, employee_info_t t_b, department_info_t t_c, user_account_t t_d
			where t_a.user_id = t_b.emp_uid and t_a.user_id=t_d.user_id and t_b.group_id = t_c.group_id and t_c.ent_id = 1000000000000030
			and owner_type=1 and owner_id = 1000000000000030
			-- and t_a.user_id=80
			-- and t_c.group_id in (999001)
			and (attend_date>='2017-05-01' and attend_date<='2017-07-30 23:59:59.000')
			-- and t_a.user_name like '%管理员%'
			group by t_a.user_id, t_a.user_name, t_c.group_id, t_c.dep_name, t_d.account
		) TMP group by user_id, user_name, user_account, calcul_days, expected_counts, expected_durations, real_counts, real_durations, work_overtime_counts, work_overtime_durations, abnormal_counts
		order by TMP.user_name, TMP.user_id
		 */
		
		//时间范围1
		//((start_time>='2017-05-01 01:00:00' and start_time<='2017-07-30 23:59:59.000') or (stop_time>='2017-05-01 01:00:00' and stop_time<='2017-07-30 23:59:59.000'))
		$searchTimeSqlOfFurlough = '';
		if (!empty($searchTimeS) || !empty($searchTimeE)) {
			$searchTimeSqlOfFurlough = 'and (';
			
			$searchTimeSqlOfFurlough .= '(';
			if (!empty($searchTimeS))
				$searchTimeSqlOfFurlough .= "start_time>= '$searchTimeS' ";
			if (!empty($searchTimeE)) {
				if (!empty($searchTimeS))
					$searchTimeSqlOfFurlough .= 'and ';
				$searchTimeSqlOfFurlough .= "start_time<= '$searchTimeE' ";
			}
			
			$searchTimeSqlOfFurlough .= ') or (';
			
			if (!empty($searchTimeS))
				$searchTimeSqlOfFurlough .= "stop_time>= '$searchTimeS' ";
			if (!empty($searchTimeE)) {
				if (!empty($searchTimeS))
					$searchTimeSqlOfFurlough .= 'and ';
				$searchTimeSqlOfFurlough .= "stop_time<= '$searchTimeE' ";
			}
			
			$searchTimeSqlOfFurlough .= '))';
		}
		//时间范围2
		$searchTimeSql = '';
		if (!empty($searchTimeS) || !empty($searchTimeE)) {
			$searchTimeSql = 'and (';
				
			if (!empty($searchTimeS))
				$searchTimeSql .= "attend_date>='".substr($searchTimeS, 0, 10)."' ";
			if (!empty($searchTimeE)) {
				if (!empty($searchTimeS))
					$searchTimeSql .= "and ";
				$searchTimeSql .= "attend_date<='$searchTimeE'";
			}
			
			$searchTimeSql .= ')';
		}
		
		$sql = "select distinct TMP.user_name, TMP.user_id, TMP.user_account, max(dep_name) as dep_name, calcul_days, expected_counts, expected_durations, real_counts, real_durations, work_overtime_counts, work_overtime_durations, abnormal_counts "
				.", (select count(att_req_id) from eb_attend_req_t where user_id=TMP.user_id and owner_type=1 and owner_id = $entCode "
				."and req_type=3 and req_status=2 $searchTimeSqlOfFurlough limit 1) as furlough_count "
				."from ("
				."select t_a.user_id, t_a.user_name, t_d.account as user_account, t_c.group_id, t_c.dep_name, sum(calcul_day) as calcul_days "
				.", sum(expected_count) as expected_counts, sum(expected_duration) as expected_durations "
				.", sum(real_count) as real_counts, sum(real_duration) as real_durations, sum(work_overtime_count) as work_overtime_counts "
				.", sum(work_overtime_duration) as work_overtime_durations, sum(abnormal_count) as abnormal_counts "
				."from eb_attend_daily_t t_a, employee_info_t t_b, department_info_t t_c, user_account_t t_d "
				."where t_a.user_id = t_b.emp_uid and t_a.user_id=t_d.user_id and t_b.group_id = t_c.group_id and t_c.ent_id = $entCode and owner_type=1 and owner_id = $entCode "
				;
		
		//非考勤专员和非部门经理的普通员工，仅查询自己的考勤记录
		if (isset($userId) && !$isAttendanceManager && empty($groupIds) && empty($memberUids))
			$sql .= "and t_a.user_id=$userId ";
		//查询部门成员的考勤记录
		if (!$isAttendanceManager && !empty($memberUids)) {
			$sql .= "and t_a.user_id in (".implode(',', $memberUids).") ";
		}
		if (!$isAttendanceManager && !empty($groupIds)) {
			$sql .= "and t_c.group_id in (".implode(',', $groupIds).") ";
		}			
		
		if (isset($searchGroupId))
			$sql .= "and t_c.group_id=$searchGroupId ";
		if (isset($searchUserId))
			$sql .= "and t_a.user_id=$searchUserId ";
		
		$sql .= "$searchTimeSql ";
		
		if (isset($userName))
			$sql .= "and t_a.user_name like '%$userName%' ";
		
		$sql .= "group by t_a.user_id, t_a.user_name, t_c.group_id, t_d.account, t_c.dep_name "
				.") TMP group by user_id, user_name, user_account, calcul_days, expected_counts, expected_durations, real_counts, real_durations, work_overtime_counts, work_overtime_durations, abnormal_counts";
		$orderby = "TMP.user_name, TMP.user_id";
		
		$limit = $formObj->getPerPage();
		$offset = ($formObj->getCurrentPage()-1)*$formObj->getPerPage();
		$checkDigits = $formObj->createCheckDigits();
		$conditions = array();
		$params = array();

		$sqlOfCount = 'select count(user_id) as record_count from ({$sql}) temp_tb';
		if ($forCount) { //仅查询数量
			$result = $this->simpleSearchForCount($sqlOfCount, $sql, $conditions, $params, $checkDigits);
			return array($result);
		} else {
			$results = array();
			//查询数量
			$result = $this->simpleSearchForCount($sqlOfCount, $sql, $conditions, $params, $checkDigits);
			array_push($results, $result);
			//查询列表
			$result = $this->simpleSearch($sql, $conditions, $params, $checkDigits, $orderby, $limit, $offset);
			array_push($results, $result);
				
			return $results;
		}
	}
	
	/**
	 * 考勤报告查询
	 * @param {string} $entCode 企业编号
	 * @param {string} $userId [可选] 考勤人员的用户编号
	 * @param {boolean} $isAttendanceManager 是否考勤专员；考勤专员可查询全部人员，非考勤专员按$groupUids列表限制条件
	 * @param {array} $groupIds 部门/群组编号列表
	 * @param {array} $memberUids 部门/群组内成员的用户编号列表
	 * @param {object} $formObj 封装查询条件的对象
	 * @param {boolean} $forCount 是否仅查询数量，默认false
	 * @return {boolean|array} false=查询失败，array数组 [0]=总数量result, [1]=结果列表array
	 */
	function collectedRecords2($entCode, $userId, $isAttendanceManager, array $groupIds, array $memberUids, $formObj, $forCount=false) {
		if (empty($entCode)) {
			log_err('$entCode is empty');
			return false;
		}
		
		//提取查询条件
		$searchTimeS = $formObj->search_time_s;
		$searchTimeE = $formObj->search_time_e;
		$userName = escapeQuotes($formObj->user_name);
		$searchGroupId = $formObj->search_group_id;
		$searchUserId = $formObj->search_user_id;
		//检查查询字段合法性
		if (isset($searchGroupId)) {
			if(!EBModelBase::checkDigit($searchGroupId, $errMsg, 'search_group_id')) {
				log_err($errMsg);
				return false;
			}
		}
		if (isset($searchUserId)) {
			if(!EBModelBase::checkDigit($searchUserId, $errMsg, 'search_user_id')) {
				log_err($errMsg);
				return false;
			}
		}
		
		/*
		select distinct TMP.user_name, TMP.user_id, TMP.user_account, max(dep_name) as dep_name, signin_counts, unsignin_counts, late_counts, signout_counts, unsignout_counts, leave_early_counts
				, work_overtime_counts, work_overtime_durations, work_outside_counts, work_outside_durations
				, (select count(att_req_id) from eb_attend_req_t where user_id=TMP.user_id and owner_type=1 and owner_id = 1000000000000030 
					and req_type=3 and req_status=2 and (attend_date>='2017-09-01' and attend_date<='2017-09-30 23:59:59.000') limit 1) as furlough_count 
		from (
			select t_a.user_id, t_a.user_name, t_d.account as user_account, t_c.group_id, t_c.dep_name, sum(signin_count) as signin_counts
				, sum(unsignin_count) as unsignin_counts, sum(late_count) as late_counts
				, sum(signout_count) as signout_counts, sum(unsignout_count) as unsignout_counts, sum(leave_early_count) as leave_early_counts
				, sum(work_overtime_count) as work_overtime_counts, sum(work_overtime_duration) as work_overtime_durations
				, sum(work_outside_count) as work_outside_counts, sum(work_outside_duration) as work_outside_durations
			from eb_attend_daily_t t_a, employee_info_t t_b, department_info_t t_c, user_account_t t_d
			where t_a.user_id = t_b.emp_uid and t_a.user_id=t_d.user_id and t_b.group_id = t_c.group_id and t_c.ent_id = 1000000000000030
			and owner_type=1 and owner_id = 1000000000000030
			-- and t_a.user_id=80
			-- and t_c.group_id in (999001)
			and (attend_date>='2017-09-01' and attend_date<='2017-09-30 23:59:59.000')
			-- and t_a.user_name like '%管理员%'
			group by t_a.user_id, t_a.user_name, t_d.account, t_c.group_id, t_c.dep_name
		) TMP group by user_id, user_name, user_account, signin_counts, unsignin_counts, late_counts, signout_counts, unsignout_counts, leave_early_counts
		, work_overtime_counts, work_overtime_durations, work_outside_counts, work_outside_durations
		order by TMP.user_name, TMP.user_id
		 */
		
		//时间范围
		$searchTimeSql = '';
		if (!empty($searchTimeS) || !empty($searchTimeE)) {
			$searchTimeSql = 'and (';
		
			if (!empty($searchTimeS))
				$searchTimeSql .= "attend_date>='".substr($searchTimeS, 0, 10)."' ";
				if (!empty($searchTimeE)) {
					if (!empty($searchTimeS))
						$searchTimeSql .= "and ";
					$searchTimeSql .= "attend_date<='$searchTimeE'";
				}
				
				$searchTimeSql .= ')';
		}
		
		$sql = "select distinct TMP.user_name, TMP.user_id, TMP.user_account, max(dep_name) as dep_name, signin_counts, unsignin_counts, late_counts, signout_counts, unsignout_counts, leave_early_counts "
				.", work_overtime_counts, work_overtime_durations, work_outside_counts, work_outside_durations "
				.", (select count(att_req_id) from eb_attend_req_t where user_id=TMP.user_id and owner_type=1 and owner_id = $entCode "
				."and req_type=3 and req_status=2 $searchTimeSql limit 1) as furlough_count "
				."from ("
				."select t_a.user_id, t_a.user_name, t_d.account as user_account, t_c.group_id, t_c.dep_name, sum(signin_count) as signin_counts "
				.", sum(unsignin_count) as unsignin_counts, sum(late_count) as late_counts "
				.", sum(signout_count) as signout_counts, sum(unsignout_count) as unsignout_counts, sum(leave_early_count) as leave_early_counts "
				.", sum(work_overtime_count) as work_overtime_counts, sum(work_overtime_duration) as work_overtime_durations "
				.", sum(work_outside_count) as work_outside_counts, sum(work_outside_duration) as work_outside_durations "
				."from eb_attend_daily_t t_a, employee_info_t t_b, department_info_t t_c, user_account_t t_d "
				."where t_a.user_id = t_b.emp_uid and t_a.user_id=t_d.user_id and t_b.group_id = t_c.group_id and t_c.ent_id = $entCode and owner_type=1 and owner_id = $entCode "
				;

		//非考勤专员和非部门经理的普通员工，仅查询自己的考勤记录
		if (isset($userId) && !$isAttendanceManager && empty($groupIds) && empty($memberUids))
			$sql .= "and t_a.user_id=$userId ";
		//查询部门成员的考勤记录
		if (!$isAttendanceManager && !empty($memberUids)) {
			$sql .= "and t_a.user_id in (".implode(',', $memberUids).") ";
		}
		if (!$isAttendanceManager && !empty($groupIds)) {
			$sql .= "and t_c.group_id in (".implode(',', $groupIds).") ";
		}

		if (isset($searchGroupId))
			$sql .= "and t_c.group_id=$searchGroupId ";
		if (isset($searchUserId))
			$sql .= "and t_a.user_id=$searchUserId ";

		$sql .= "$searchTimeSql ";

		if (isset($userName))
			$sql .= "and t_a.user_name like '%$userName%' ";

		$sql .= "group by t_a.user_id, t_d.account, t_a.user_name, t_c.group_id, t_c.dep_name "
				.") TMP group by user_id, user_name, user_account, signin_counts, unsignin_counts, late_counts, signout_counts, unsignout_counts, leave_early_counts "
				.", work_overtime_counts, work_overtime_durations, work_outside_counts, work_outside_durations ";
		$orderby = "TMP.user_name, TMP.user_id";

		$limit = $formObj->getPerPage();
		$offset = ($formObj->getCurrentPage()-1)*$formObj->getPerPage();
		$checkDigits = $formObj->createCheckDigits();
		$conditions = array();
		$params = array();
		
		$sqlOfCount = 'select count(user_id) as record_count from ({$sql}) temp_tb';
		if ($forCount) { //仅查询数量
			$result = $this->simpleSearchForCount($sqlOfCount, $sql, $conditions, $params, $checkDigits);
			return array($result);
		} else {
			$results = array();
			//查询数量
			$result = $this->simpleSearchForCount($sqlOfCount, $sql, $conditions, $params, $checkDigits);
			array_push($results, $result);
			//查询列表
			$result = $this->simpleSearch($sql, $conditions, $params, $checkDigits, $orderby, $limit, $offset);
			array_push($results, $result);

			return $results;
		}
	}
}