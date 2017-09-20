<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class AttendRecordService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'att_rec_id';
		$this->tableName = 'eb_attend_record_t';
		$this->fieldNames = 'att_rec_id, owner_type, owner_id, user_id, user_name, create_time, last_time, attend_date, att_rul_id, att_tim_id'
				.', signin_time, req_signin_time, signout_time, req_signout_time, signin_from, signout_from, signin_address, signout_address, work_duration, req_duration, data_flag';
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
	 * 获取考勤记录及相关的审批记录(最新一条审批)
	 * @param {string} $userId 用户编号
	 * @param {string} $recId 考勤记录编号
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getRecordIncludeReqByRecId($userId, $recId) {
		/*
		select TMP3.*, t_e.att_req_id, t_e.create_time as req_create_time, t_e.last_time as req_last_time, t_e.start_time as req_start_time, t_e.stop_time as req_stop_time, t_e.req_duration as req_req_duration, t_e.attend_date as req_attend_date, req_type, req_status, req_name, req_content, req_param_int from (
			select TMP2.*, t_d.* from (
				select TMP.*, t_c.signin_time as standard_signin_time, t_c.signout_time as standard_signout_time from (
					select t_b.att_rec_id, t_b.owner_type, t_b.owner_id, t_b.user_id, t_b.user_name, t_d.account as user_account, t_b.create_time, t_b.att_rul_id, t_b.att_tim_id, t_b.signin_time, t_b.signout_time, t_b.req_signin_time, t_b.req_signout_time, t_b.work_duration, t_b.req_duration, t_a.attend_date
						, t_a.att_rec_id0, t_a.att_rec_id0_state, t_a.att_rec_id1, t_a.att_rec_id1_state, t_a.att_rec_id2, t_a.att_rec_id2_state, t_a.att_rec_id3, t_a.att_rec_id3_state, t_a.att_rec_id4, t_a.att_rec_id4_state
					from eb_attend_daily_t t_a , eb_attend_record_t t_b, user_account_t t_d
					where t_a.user_id=t_d.user_id and (t_a.att_rec_id0 = t_b.att_rec_id or t_a.att_rec_id1 = t_b.att_rec_id or t_a.att_rec_id2 = t_b.att_rec_id or t_a.att_rec_id3 = t_b.att_rec_id or t_a.att_rec_id4 = t_b.att_rec_id)
						and t_b.att_rec_id = 2017052517490007195 and t_b.user_id =80
					) TMP left join eb_attend_time_t t_c on TMP.att_tim_id = t_c.att_tim_id
					where TMP.user_id = 80 and TMP.att_rec_id =2017052517490007195
			) TMP2 left join (SELECT max(t_y.att_req_id) AS max_att_req_id, att_rec_id as req_att_rec_id FROM eb_attend_req_t t_x, eb_attend_req_item_t t_y where t_x.att_req_id = t_y.att_req_id and att_rec_id='2017052517490007195' GROUP BY att_rec_id) t_d on TMP2.att_rec_id = t_d.req_att_rec_id
		) TMP3 left join eb_attend_req_t t_e on TMP3.max_att_req_id = t_e.att_req_id
		 */
		
		$sql = "select TMP3.*, t_e.att_req_id, t_e.create_time as req_create_time, t_e.last_time as req_last_time, t_e.start_time as req_start_time, t_e.stop_time as req_stop_time, t_e.req_duration as req_req_duration, t_e.attend_date as req_attend_date, req_type, req_status, req_name, req_content, req_param_int from ("
				."select TMP2.*, t_d.* from ("
				."select TMP.*, t_c.signin_time as standard_signin_time, t_c.signout_time as standard_signout_time from ("
				."select t_b.att_rec_id, t_b.owner_type, t_b.owner_id, t_b.user_id, t_b.user_name, t_d.account as user_account, t_b.create_time, t_b.att_rul_id, t_b.att_tim_id, t_b.signin_time, t_b.signout_time, t_b.req_signin_time, t_b.req_signout_time, t_b.work_duration, t_b.req_duration, t_a.attend_date "
				.", t_a.att_rec_id0, t_a.att_rec_id0_state, t_a.att_rec_id1, t_a.att_rec_id1_state, t_a.att_rec_id2, t_a.att_rec_id2_state, t_a.att_rec_id3, t_a.att_rec_id3_state, t_a.att_rec_id4, t_a.att_rec_id4_state "
				."from eb_attend_daily_t t_a , eb_attend_record_t t_b, user_account_t t_d "
				."where t_a.user_id=t_d.user_id and (t_a.att_rec_id0 = t_b.att_rec_id or t_a.att_rec_id1 = t_b.att_rec_id or t_a.att_rec_id2 = t_b.att_rec_id or t_a.att_rec_id3 = t_b.att_rec_id or t_a.att_rec_id4 = t_b.att_rec_id) "
				."and t_b.att_rec_id=$recId and t_b.user_id =$userId "
				.") TMP left join eb_attend_time_t t_c on TMP.att_tim_id = t_c.att_tim_id "
				."where TMP.user_id = $userId and TMP.att_rec_id=$recId "
				.") TMP2 left join (SELECT max(t_y.att_req_id) AS max_att_req_id, att_rec_id as req_att_rec_id FROM eb_attend_req_t t_x, eb_attend_req_item_t t_y where t_x.att_req_id = t_y.att_req_id and att_rec_id='$recId' GROUP BY att_rec_id) t_d on TMP2.att_rec_id = t_d.req_att_rec_id "
				.") TMP3 left join eb_attend_req_t t_e on TMP3.max_att_req_id = t_e.att_req_id "
				;
		
		$result = $this->simpleSearch($sql);
		return $result;
	}
	
	/**
	 * 获取考勤记录(版本0)
	 * @param {string} $entCode 企业编号
	 * @param {string} $groupCode 群组编号(用于owner条件)
	 * @param {string} $userId [可选] 考勤人员的用户编号
	 * @param {boolean} $isAttendanceManager 是否考勤专员；考勤专员可查询全部人员，非考勤专员按$groupUids列表限制条件
	 * @param {array} $groupIds 部门/群组编号列表
	 * @param {array} $memberUids 部门/群组内成员的用户编号列表
	 * @param {object} $formObj 封装查询条件的对象
	 * @param {boolean} $forCount 是否仅查询数量，默认false
	 * @return {boolean|array} false=查询失败，array数组 [0]=总数量result, [1]=结果列表array
	 */
	function getAttendRecord0($entCode, $groupCode, $userId, $isAttendanceManager, array $groupIds, array $memberUids, $formObj, $forCount=false) {
		if (empty($groupCode) && !isset($entCode)) {
			log_err('$groupCode and $entCode are all empty');
			return false;
		}
		
		//提取查询条件
		$searchTimeS = $formObj->search_time_s;
		$searchTimeE = $formObj->search_time_e;
		$userName = $formObj->user_name;
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
		
		//特殊字符专员，防SQL注入
		if (!empty($userName))
			$userName = addslashes($userName);
		
		/*
		select distinct TMP.user_name, TMP.user_id, TMP.user_account, max(dep_name) as dep_name
					, TMP.att_rec_id, TMP.owner_type, TMP.owner_id, TMP.create_time, TMP.last_time, TMP.attend_date
					, TMP.att_rul_id, TMP.att_tim_id, TMP.signin_time, TMP.signin_from, TMP.signin_address, TMP.signout_time, TMP.signout_from, TMP.signout_address
					, TMP.data_flag, TMP.req_signin_time, TMP.req_signout_time, TMP.work_duration, TMP.req_duration
					, t_d.name as tim_name, t_d.signin_time as standard_signin_time, t_d.signout_time as standard_signout_time, t_d.signin_ignore, t_d.signout_ignore
					, t_d.work_duration as standard_work_duration, t_d.rest_duration as standard_rest_duration from (
			select t_a.*, t_c.group_id, t_c.dep_name, t_d.account as user_account from eb_attend_record_t t_a, employee_info_t t_b, department_info_t t_c, user_account_t t_d
			where t_a.user_id = t_b.emp_uid and t_a.user_id=t_d.user_id and t_b.group_id = t_c.group_id and t_c.ent_id<>0
				and (t_a.work_duration<>0 or t_a.req_duration<>0)
				and t_a.owner_type=1 and t_a.owner_id=1000000000000030
				and t_a.user_id=80
				and t_c.group_id in (999001)
				and (t_a.attend_date>='2017-05-01' and t_a.attend_date<='2017-07-30 23:59:59.000')
		) TMP left join eb_attend_time_t t_d on (TMP.att_tim_id = t_d.att_tim_id)
		where 1=1 and TMP.group_id=999001 and TMP.user_id=80 and TMP.user_name like '%管理员%'
		group by TMP.user_name, TMP.user_id, TMP.user_account, TMP.att_rec_id, TMP.owner_type, TMP.owner_id, TMP.create_time, TMP.last_time, TMP.attend_date
			, TMP.att_rul_id, TMP.att_tim_id, TMP.signin_time, TMP.signin_from, TMP.signin_address, TMP.signout_time, TMP.signout_from, TMP.signout_address
			, TMP.data_flag, TMP.req_signin_time, TMP.req_signout_time, TMP.work_duration, TMP.req_duration
			, t_d.name, t_d.signin_time, t_d.signout_time, t_d.signin_ignore, t_d.signout_ignore, t_d.work_duration, t_d.rest_duration
		order by TMP.user_name, TMP.user_id
		 */
		//规则归属条件
		$ownerSql = "";
		if (!empty($groupCode)) {
			$ownerSql .= "(t_a.owner_id = $groupCode and t_a.owner_type = 2)";
		}
		if (!empty($entCode)) {
			if (!empty($ownerSql)) {
				$ownerSql .= " or ";
			}
			$ownerSql .= "(t_a.owner_id = $entCode and t_a.owner_type = 1)";
		}
		if (!empty($ownerSql)) {
			$ownerSql = " and ($ownerSql)";
		}
		
		$sql = "select distinct TMP.user_name, TMP.user_id, TMP.user_account, max(dep_name) as dep_name "
				.", TMP.att_rec_id, TMP.owner_type, TMP.owner_id, TMP.create_time, TMP.last_time, TMP.attend_date "
				.", TMP.att_rul_id, TMP.att_tim_id, TMP.signin_time, TMP.signin_from, TMP.signin_address, TMP.signout_time, TMP.signout_from, TMP.signout_address "
				.", TMP.data_flag, TMP.req_signin_time, TMP.req_signout_time, TMP.work_duration, TMP.req_duration "
				.", t_d.name as tim_name, t_d.signin_time as standard_signin_time, t_d.signout_time as standard_signout_time, t_d.signin_ignore, t_d.signout_ignore "
				.", t_d.work_duration as standard_work_duration, t_d.rest_duration as standard_rest_duration from ("
				."select t_a.*, t_c.group_id, t_c.dep_name, t_d.account as user_account from eb_attend_record_t t_a, employee_info_t t_b, department_info_t t_c, user_account_t t_d "
				."where t_a.user_id = t_b.emp_uid and t_a.user_id=t_d.user_id and t_b.group_id = t_c.group_id and t_c.ent_id<>0 "
				."and (t_a.work_duration<>0 or t_a.req_duration<>0) $ownerSql "
				;
		
		//权限相关的查询条件
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
			
// 		and (t_a.attend_date>='2017-05-01' and t_a.attend_date<='2017-07-30 23:59:59.000')
		//时间范围
		$searchTimeSql = '';
		if (!empty($searchTimeS) || !empty($searchTimeE)) {
			$searchTimeSql = 'and (';
			
			if (!empty($searchTimeS))
				$searchTimeSql .= "t_a.attend_date>='".substr($searchTimeS, 0, 10)."' ";
			if (!empty($searchTimeE)) {
				if (!empty($searchTimeS))
					$searchTimeSql .= "and ";
				$searchTimeSql .= "t_a.attend_date<='$searchTimeE'";
			}
			
			$searchTimeSql .= ')';
		}
		$sql .= $searchTimeSql;
		
		$sql .= ") TMP left join eb_attend_time_t t_d on (TMP.att_tim_id = t_d.att_tim_id) where 1=1 ";
		
		//外部查询条件
		if (!empty($searchGroupId))
			$sql .= "and TMP.group_id=$searchGroupId ";
		if (!empty($searchUserId))
			$sql .= "and TMP.user_id= $searchUserId ";
		if (!empty($userName))
			$sql .= "and TMP.user_name like '%$userName%' "; //申请人名称(支持模糊查询)
		
		$sql .= "group by TMP.user_name, TMP.user_id, TMP.user_account, TMP.att_rec_id, TMP.owner_type, TMP.owner_id, TMP.create_time, TMP.last_time, TMP.attend_date "
				.", TMP.att_rul_id, TMP.att_tim_id, TMP.signin_time, TMP.signin_from, TMP.signin_address, TMP.signout_time, TMP.signout_from, TMP.signout_address "
				.", TMP.data_flag, TMP.req_signin_time, TMP.req_signout_time, TMP.work_duration, TMP.req_duration "
				.", t_d.name, t_d.signin_time, t_d.signout_time, t_d.signin_ignore, t_d.signout_ignore, t_d.work_duration, t_d.rest_duration ";
			
		//排序字段
		$orderby = $formObj->getOrderby();
		//没有排序字段时按默认排序
		$fixedOrder = ', TMP.user_name, TMP.user_id, t_d.signin_time';
		if (preg_match('/^attend_date/i', $orderby)) {
			if (preg_match('/asc$/i', $orderby)) {
				$orderby = "TMP.attend_date asc $fixedOrder";
			} else if (preg_match('/desc$/i', $orderby)) {
				$orderby = "TMP.attend_date desc $fixedOrder";
			} else {
				$orderby = "TMP.attend_date $fixedOrder";
			}
		} else {
			$orderby = "TMP.attend_date desc $fixedOrder";
		}
		
		$limit = $formObj->getPerPage();
		$offset = ($formObj->getCurrentPage()-1)*$formObj->getPerPage();
		$checkDigits = $formObj->createCheckDigits();
		
		$conditions = array();
		$params = array();
		
		$sqlOfCount = 'select count(att_rec_id) as record_count from ({$sql}) temp_tb';
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
	 * 获取考勤记录(版本1)
	 * $entCode与$groupCode二选一，有一个填null
	 * @param {string} $entCode 企业编号
	 * @param {string} $groupCode 群组编号
	 * @param {string} $userId 用户编号
	 * @param {string} $occurredDate 发生日期(不包括时间部分)，格式如：2017-05-04
	 * @param {string} $attRulId [可选] 考勤规则编号
	 * @param {string} $attTimId [可选] 考勤时间段编号
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendRecord($entCode, $groupCode, $userId, $occurredDate, $attRulId=NULL, $attTimId=NULL) {
		if (!isset($entCode) && !isset($groupCode)) {
			log_err('getAttendRecord error, $entCode and $groupCode are empty');
			return false;
		}
		if (!isset($userId) || !isset($occurredDate)) {
			log_err('getAttendRecord error, $userId or $occurredDate is empty');
			return false;
		}
		
		$limit = 1000;
		$checkDigits =array('owner_type, owner_id, user_id, att_rul_id, att_tim_id');
		$orderBy = 'create_time';
		$params = array('user_id'=>$userId, 'attend_date'=>$occurredDate
				/*'create_time_X'=>new SQLParamComb(array('create_time_s'=>new SQLParam("$occurredDate 00:00:00", 'create_time', '>='),
									'create_time_e'=>new SQLParam("$occurredDate 23:59:59.999", 'create_time', '<=')), SQLParamComb::$TYPE_AND)*/);
		if (isset($entCode)) {
			$params['owner_type'] = 1;
			$params['owner_id'] = $entCode;
		}
		if (isset($groupCode)) {
			$params['owner_type'] = 2;
			$params['owner_id'] = $groupCode;
		}
		if (isset($attRulId))
			$params['att_rul_id'] = $attRulId;
		if (isset($attTimId))
			$params['att_tim_id'] = $attTimId;
		
		$result = $this->search($this->fieldNames, $params, $checkDigits, $orderBy, $limit);
		return $result;
	}
	
	/**
	 * 获取考勤记录(版本2)
	 * $entCode与$groupCode二选一，有一个填null
	 * @param {string} $entCode 企业编号
	 * @param {string} $groupCode 群组编号
	 * @param {string} $userId 用户编号
	 * @param {string} $occurredDate 发生日期(不包括时间部分)，格式如：2017-05-04
	 * @param {array}  $ruleIdsAndTimeIds [可选] 规则编号和时间段编号的值对列表，例如：array(array(1, 1), array(1,2)...)
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendRecord2($entCode, $groupCode, $userId, $occurredDate, $ruleIdsAndTimeIds=NULL) {
		if (!isset($entCode) && !isset($groupCode)) {
			log_err('getAttendRecord2 error, $entCode and $groupCode are empty');
			return false;
		}
		if (!isset($userId) || !isset($occurredDate)) {
			log_err('getAttendRecord2 error, $userId or $occurredDate is empty');
			return false;
		}
		
		$limit = 100;
		$checkDigits =array('owner_type, owner_id, user_id, att_rul_id, att_tim_id');
		$orderBy = 'create_time';
		$params = array('user_id'=>$userId, 'attend_date'=>$occurredDate
				/*'create_time_X'=>new SQLParamComb(array('create_time_s'=>new SQLParam("$occurredDate 00:00:00", 'create_time', '>='),
						'create_time_e'=>new SQLParam("$occurredDate 23:59:59.999", 'create_time', '<=')), SQLParamComb::$TYPE_AND)*/);
		if (isset($entCode)) {
			$params['owner_type'] = 1;
			$params['owner_id'] = $entCode;
		}
		if (isset($groupCode)) {
			$params['owner_type'] = 2;
			$params['owner_id'] = $groupCode;
		}
		if (isset($ruleIdsAndTimeIds) && is_array($ruleIdsAndTimeIds) && count($ruleIdsAndTimeIds)>0) {
			//(att_rul_id = 1 and att_tim_id=1)
			$sqlParams = array();
			foreach ($ruleIdsAndTimeIds as $rt) {
				array_push($sqlParams, new SQLParamComb(array('att_rul_id'=>$rt[0], 'att_tim_id'=>$rt[1])));
			}
			$params['rt'] = new SQLParamComb($sqlParams, SQLParamComb::$TYPE_OR);
		}
		
		$result = $this->search($this->fieldNames, $params, $checkDigits, $orderBy, $limit);
		return $result;
	}
	
	/**
	 * 获取考勤记录(版本3)
	 * $entCode与$groupCode二选一，有一个填null
	 * @param {string} $entCode 企业编号
	 * @param {string} $groupCode 群组编号
	 * @param {string} $userId 用户编号
	 * @param {string} $occurredDate 发生日期(不包括时间部分)，格式如：2017-05-04
	 * @param {array}  $ruleIdsAndTimeIds [可选] 规则编号和时间段编号的值对列表，例如：array(array(1, 1), array(1,2)...)
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendRecord3($entCode, $groupCode, $userId, $occurredDate, $ruleIdsAndTimeIds=NULL) {
		if (!isset($entCode) && !isset($groupCode)) {
			log_err('getAttendRecord3 error, $entCode and $groupCode are empty');
			return false;
		}
		if (!isset($userId) || !isset($occurredDate)) {
			log_err('getAttendRecord3 error, $userId or $occurredDate is empty');
			return false;
		}
		/*
		select t_b.att_rec_id, t_b.owner_type, t_b.owner_id, t_b.user_id, t_b.user_name, t_b.create_time, t_b.attend_date, t_b.att_rul_id, t_b.att_tim_id, t_b.signin_time, t_b.signout_time, t_b.req_signin_time, t_b.req_signout_time, t_b.work_duration, t_b.req_duration
			, t_b.signin_from, t_b.signin_address, t_b.signout_from, t_b.signout_address
			, t_a.att_rec_id0, t_a.att_rec_id0_state, t_a.att_rec_id1, t_a.att_rec_id1_state, t_a.att_rec_id2, t_a.att_rec_id2_state
			, t_a.att_rec_id3, t_a.att_rec_id3_state, t_a.att_rec_id4, t_a.att_rec_id4_state
			, t_c.work_day, t_c.flexible_work, t_d.name as tim_name, t_d.signin_time as standard_signin_time, t_d.signin_ignore
			, t_d.signout_time as standard_signout_time, t_d.signout_ignore, t_d.rest_duration as standard_rest_duration, t_d.work_duration as standard_work_duration
		from eb_attend_daily_t t_a , eb_attend_record_t t_b, eb_attend_rule_t t_c, eb_attend_time_t t_d
		where (t_a.att_rec_id0 = t_b.att_rec_id or t_a.att_rec_id1 = t_b.att_rec_id or t_a.att_rec_id2 = t_b.att_rec_id or t_a.att_rec_id3 = t_b.att_rec_id or t_a.att_rec_id4 = t_b.att_rec_id)
		and t_b.att_rul_id = t_c.att_rul_id and t_b.att_tim_id = t_d.att_tim_id
		and t_b.user_id = 80 and t_b.attend_date = '2017-05-09' and t_b.owner_type=1 and t_b.owner_id=1000000000000030 and ((t_b.att_rul_id=1 and t_b.att_tim_id=1) or (t_b.att_rul_id=1 and t_b.att_tim_id=3))
		 */
		$sql = "select t_b.att_rec_id, t_b.owner_type, t_b.owner_id, t_b.user_id, t_b.user_name, t_b.create_time, t_b.attend_date, t_b.att_rul_id, t_b.att_tim_id, t_b.signin_time, t_b.signout_time, t_b.req_signin_time, t_b.req_signout_time, t_b.work_duration, t_b.req_duration "
			.", t_b.signin_from, t_b.signin_address, t_b.signout_from, t_b.signout_address "
			.", t_a.att_rec_id0, t_a.att_rec_id0_state, t_a.att_rec_id1, t_a.att_rec_id1_state, t_a.att_rec_id2, t_a.att_rec_id2_state, t_a.att_rec_id3, t_a.att_rec_id3_state, t_a.att_rec_id4, t_a.att_rec_id4_state "
			.", t_c.work_day, t_c.flexible_work, t_d.name as tim_name, t_d.signin_time as standard_signin_time, t_d.signin_ignore "
			.", t_d.signout_time as standard_signout_time, t_d.signout_ignore, t_d.rest_duration as standard_rest_duration, t_d.work_duration as standard_work_duration "
			."from eb_attend_daily_t t_a , eb_attend_record_t t_b, eb_attend_rule_t t_c, eb_attend_time_t t_d "
			."where (t_a.att_rec_id0 = t_b.att_rec_id or t_a.att_rec_id1 = t_b.att_rec_id or t_a.att_rec_id2 = t_b.att_rec_id or t_a.att_rec_id3 = t_b.att_rec_id or t_a.att_rec_id4 = t_b.att_rec_id) "
			."and t_b.att_rul_id = t_c.att_rul_id and t_b.att_tim_id = t_d.att_tim_id "
			."and t_b.user_id=$userId and t_b.attend_date=? "
			;
		$conditions = array($occurredDate);
		
		if (!empty($entCode)) {
			$sql .= " and t_b.owner_type=1 and t_b.owner_id=$entCode";
		}
		if (!empty($ruleIdsAndTimeIds)) {
			$sql .= " and (";
			$tmpSql = '';
			foreach ($ruleIdsAndTimeIds as $ruleIdAndTimeId) {
				if (!empty($tmpSql))
					$tmpSql .= ' or ';
				$tmpSql .= '(t_b.att_rul_id='.$ruleIdAndTimeId[0].' and t_b.att_tim_id='.$ruleIdAndTimeId[1].')';
			}
			$sql .= $tmpSql;
			$sql .= ")";
		}
		
		$result = $this->simpleSearch($sql, $conditions);
		return $result;
	}
	
	/**
	 * 获取考勤记录(版本4)
	 * @param {string} $entCode 企业编号
	 * @param {string} $groupCode 群组编号
	 * @param {string} $targetUserId 目标用户编号
	 * @param {string} $startTime 开始时间
	 * @param {string} $stopTime 结束时间
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendRecord4($entCode, $groupCode, $targetUserId, $startTime, $stopTime) {
		if (!isset($entCode) && !isset($groupCode)) {
			log_err('getAttendRecord4 error, $entCode and $groupCode are empty');
			return false;
		}
		if (!isset($targetUserId)) {
			log_err('getAttendRecord4 error, $targetUserId is empty');
			return false;
		}
		if (!isset($startTime) && !isset($stopTime)) {
			log_err('getAttendRecord4 error, $startTime and $stopTime are empty');
			return false;
		}
		
		//规则归属条件
		$ownerSql = "";
		if (!empty($groupCode)) {
			if (!empty($ownerSql)) {
				$ownerSql .= " or (t_a.owner_id =$groupCode and t_a.owner_type = 2)";
			}
		}
		if (!empty($entCode)) {
			if (!empty($ownerSql)) {
				$ownerSql .= " or ";
			}
			$ownerSql .= "(t_a.owner_id = $entCode and t_a.owner_type = 1)";
		}
		
		$startAttendDate = substr($startTime, 0, 10);
		$stopAttendDate = substr($stopTime, 0, 10);
		
		/*
		select * from (
		SELECT t_a.*, t_b.name as tim_name, t_b.signin_time as standard_signin_time, t_b.signout_time as standard_signout_time, t_b.signin_ignore, t_b.signout_ignore
		, t_b.rest_duration as tim_rest_duration, t_b.work_duration as tim_work_duration
		from eb_attend_record_t t_a, eb_attend_time_t t_b where t_a.att_tim_id = t_b.att_tim_id
		and t_a.attend_date >='2017-06-12' and t_a.attend_date <='2017-06-14 23:59:59.999'
		and user_id=80 and ((t_a.owner_id=1000000000000030 and t_a.owner_type=1) or (t_a.owner_id=999001 and t_a.owner_type=2))
		) TMP where concat(attend_date, concat(' ', standard_signin_time))>= '2017-06-12 12:00:00' and concat(attend_date, concat(' ', standard_signout_time))<= '2017-06-14 12:00:00'
		order by attend_date, standard_signin_time
		 */
		$sql = "select * from ("
			."SELECT t_a.*, t_b.name as tim_name, t_b.signin_time as standard_signin_time, t_b.signout_time as standard_signout_time, t_b.signin_ignore, t_b.signout_ignore "
			.", t_b.rest_duration as tim_rest_duration, t_b.work_duration as tim_work_duration "
			."from eb_attend_record_t t_a, eb_attend_time_t t_b where t_a.att_tim_id = t_b.att_tim_id "
			."and t_a.attend_date >='$startAttendDate' and t_a.attend_date <='$stopAttendDate 23:59:59' "
			."and user_id=$targetUserId and ($ownerSql) "
			.") TMP where #concat(attend_date, #concat(' ', standard_signin_time))>= '$startTime' and #concat(attend_date, #concat(' ', standard_signout_time))<= '$stopTime' "
			;
		
		$orderBy = 'attend_date, standard_signin_time';
		$limit = 1000;
		$result = $this->simpleSearch($sql, null, null, null, $orderBy, $limit);
		return $result;
	}
	
	/**
	 * 获取考勤记录(版本5)
	 * @param {array} $recIds 考勤记录编号列表
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendRecord5($recIds) {
		if (empty($recIds)) {
			log_err('$recIds is empty');
			return false;
		}
		/*
		select t_a.att_dai_id, t_a.att_rec_id0, t_a.att_rec_id0_state, t_a.att_rec_id1, t_a.att_rec_id1_state, t_a.att_rec_id2, t_a.att_rec_id2_state, t_a.att_rec_id3, t_a.att_rec_id3_state, t_a.att_rec_id4, t_a.att_rec_id4_state
			, t_b.* from eb_attend_daily_t t_a , eb_attend_record_t t_b
		where (t_a.att_rec_id0 = t_b.att_rec_id or t_a.att_rec_id1 = t_b.att_rec_id or t_a.att_rec_id2 = t_b.att_rec_id or t_a.att_rec_id3 = t_b.att_rec_id or t_a.att_rec_id4 = t_b.att_rec_id)
		and t_b.att_rec_id in (2017050910205100114, 2017050916143700273, 2017051609522800188)
		 */
		//考勤记录编号查询条件
		$recIdSql = implode(',', $recIds);
		//拼接查询语句
		$sql = "select t_a.att_dai_id, t_a.att_rec_id0, t_a.att_rec_id0_state, t_a.att_rec_id1, t_a.att_rec_id1_state, t_a.att_rec_id2, t_a.att_rec_id2_state, t_a.att_rec_id3, t_a.att_rec_id3_state, t_a.att_rec_id4, t_a.att_rec_id4_state "
				.", t_b.* from eb_attend_daily_t t_a , eb_attend_record_t t_b "
				."where (t_a.att_rec_id0 = t_b.att_rec_id or t_a.att_rec_id1 = t_b.att_rec_id or t_a.att_rec_id2 = t_b.att_rec_id or t_a.att_rec_id3 = t_b.att_rec_id or t_a.att_rec_id4 = t_b.att_rec_id) "
				."and t_b.att_rec_id in ($recIdSql)"
				;
		
		$result = $this->simpleSearch($sql);
		return $result;
	}
	
	/**
	 * 获取考勤记录(版本6)
	 * $entCode与$groupCode二选一，有一个填null
	 * @param {string} $entCode 企业编号
	 * @param {string} $groupCode 群组编号
	 * @param {string} $userId 用户编号
	 * @param {string} $occurredDate 发生日期(不包括时间部分)，格式如：2017-05-04
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendRecord6($entCode, $groupCode, $userId, $occurredDate) {
		if (!isset($entCode) && !isset($groupCode)) {
			log_err('getAttendRecord6 error, $entCode and $groupCode are empty');
			return false;
		}
		if (!isset($userId) || !isset($occurredDate)) {
			log_err('getAttendRecord6 error, $userId or $occurredDate is empty');
			return false;
		}
	
		/*
		select t_a.*, t_b.att_rec_id0, t_b.att_rec_id0_state, t_b.att_rec_id1, t_b.att_rec_id1_state, t_b.att_rec_id2, t_b.att_rec_id2_state, t_b.att_rec_id3, t_b.att_rec_id3_state, t_b.att_rec_id4, t_b.att_rec_id4_state 
		from eb_attend_record_t t_a left join eb_attend_daily_t t_b on (
			t_a.user_id = t_b.user_id and t_a.attend_date = t_b.attend_date and (t_a.att_rec_id = t_b.att_rec_id0 
				or t_a.att_rec_id = t_b.att_rec_id1 or t_a.att_rec_id = t_b.att_rec_id2 or t_a.att_rec_id = t_b.att_rec_id3 or t_a.att_rec_id = t_b.att_rec_id4))
		where t_a.owner_type = 1 and t_a.owner_id = 1000000000000030
		and t_a.user_id = 80 and t_a.attend_date = '2017-07-27'
		 */
		$sql = "select t_a.*, t_b.att_rec_id0, t_b.att_rec_id0_state, t_b.att_rec_id1, t_b.att_rec_id1_state, t_b.att_rec_id2, t_b.att_rec_id2_state, t_b.att_rec_id3, t_b.att_rec_id3_state, t_b.att_rec_id4, t_b.att_rec_id4_state "
				."from eb_attend_record_t t_a left join eb_attend_daily_t t_b on ("
				."t_a.user_id = t_b.user_id and t_a.attend_date = t_b.attend_date and (t_a.att_rec_id = t_b.att_rec_id0 "
				."or t_a.att_rec_id = t_b.att_rec_id1 or t_a.att_rec_id = t_b.att_rec_id2 or t_a.att_rec_id = t_b.att_rec_id3 or t_a.att_rec_id = t_b.att_rec_id4)) "
				;
		
		$limit = 1000;
		$checkDigits =array('owner_type, owner_id, user_id');
		$orderBy = 't_a.create_time';
		$params = array('user_id'=>new SQLParam($userId, 't_a.user_id'));
		if (isset($entCode)) {
			$params['owner_type'] = new SQLParam(1, 't_a.owner_type');
			$params['owner_id'] = new SQLParam($entCode, 't_a.owner_id');
		}
		if (isset($groupCode)) {
			$params['owner_type'] = new SQLParam(2, 't_a.owner_type');
			$params['owner_id'] = new SQLParam($groupCode, 't_a.owner_id');
		}
		if (isset($occurredDate))
			$params['attend_date'] = new SQLParam($occurredDate, 't_a.attend_date');
	
		$result = $this->simpleSearch($sql, null, $params, $checkDigits, $orderBy, $limit);
		return $result;
	}
	
	/**
	 * 获取考勤记录(版本7)
	 * @param {string} $entCode 企业编号
	 * @param {string} $groupCode 群组编号
	 * @param {string} $userId 用户编号
	 * @param {boolean} $checkDuration 检查工作时长(work_duration和req_duration)；默认false
	 * @param {int} $recState [可选] 考勤状态
	 * @param {int} $recStateCalculaMode [可选] 考勤状态计算模式；1：与运算后等于状态值，2：与运算后不等于0
	 * @param {string} $searchTimeS [可选] 查询考勤日期开始
	 * @param {string} $searchTimeE [可选] 查询考勤日期结束
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendRecord7($entCode, $groupCode, $userId, $checkDuration=false, $recState=NULL, $recStateCalculaMode=NULL, $searchTimeS=NULL, $searchTimeE=NULL) {
		if (!isset($entCode) && !isset($groupCode)) {
			log_err('getAttendRecord7 error, $entCode and $groupCode are empty');
			return false;
		}
		if (empty($userId)) {
			log_err('getAttendRecord7 error, $userId is empty');
			return false;
		}
		
		if (isset($searchTimeS))
			$searchTimeS = escapeQuotes($searchTimeS);
		if (isset($searchTimeE))
			$searchTimeE = escapeQuotes($searchTimeE);
		
		/*
		select * from (
			select t_a.*, t_c.signin_time as standard_signin_time, t_c.signin_ignore, t_c.signout_time as standard_signout_time, t_c.signout_ignore, t_c.work_duration as standard_work_duration, t_c.rest_duration as standard_rest_duration
				, t_b.att_rec_id0, t_b.att_rec_id0_state, t_b.att_rec_id1, t_b.att_rec_id1_state, t_b.att_rec_id2, t_b.att_rec_id2_state, t_b.att_rec_id3, t_b.att_rec_id3_state, t_b.att_rec_id4, t_b.att_rec_id4_state
			from eb_attend_record_t t_a left join eb_attend_time_t t_c on (t_a.att_tim_id=t_c.att_tim_id) 
			left join eb_attend_daily_t t_b on (t_a.user_id = t_b.user_id and t_a.attend_date = t_b.attend_date and (t_a.att_rec_id = t_b.att_rec_id0 
					or t_a.att_rec_id = t_b.att_rec_id1 or t_a.att_rec_id = t_b.att_rec_id2 or t_a.att_rec_id = t_b.att_rec_id3 or t_a.att_rec_id = t_b.att_rec_id4))
			where (t_a.owner_type = 1 and t_a.owner_id = 1000000000000030) and t_a.user_id = 80
			and (t_a.work_duration>0 or t_a.req_duration>0)
			and (t_a.attend_date>='2017-05-01' and t_a.attend_date<='2017-07-30 23:59:59.999')
		) TMP left join (SELECT max(t_y.att_req_id) AS max_att_req_id, att_rec_id as req_att_rec_id FROM eb_attend_req_t t_x, eb_attend_req_item_t t_y 
											where t_x.att_req_id = t_y.att_req_id and t_x.user_id = 80 and (t_x.owner_type = 1 and t_x.owner_id = 1000000000000030) GROUP BY att_rec_id) t_d on (TMP.att_rec_id = t_d.req_att_rec_id)
		where ((att_rec_id0=att_rec_id and (att_rec_id0_state&2)=2) or (att_rec_id1=att_rec_id and (att_rec_id1_state&2)=2) or (att_rec_id2=att_rec_id and (att_rec_id2_state&2)=2)
						 or (att_rec_id3=att_rec_id and (att_rec_id3_state&2)=2) or (att_rec_id4=att_rec_id and (att_rec_id4_state&2)=2))
		order by attend_date desc, standard_signin_time
		 */
		//归属条件
		$ownerSql1 = "";
		$ownerSql2 = "";
		if (isset($entCode)) {
			$ownerSql1 = "(t_a.owner_type = 1 and t_a.owner_id = $entCode)";
			$ownerSql2 = "(t_x.owner_type = 1 and t_x.owner_id = $entCode)";
		}
		if (isset($groupCode)) {
			if (!empty($ownerSql1)) {
				$ownerSql1 .= ' or ';
				$ownerSql2 .= ' or ';
			}
			$ownerSql1 .= "(t_a.owner_type = 2 and t_a.owner_id = $groupCode)";
			$ownerSql2 .= "(t_x.owner_type = 2 and t_y.owner_id = $groupCode)";
		}
		
		//拼接SQL
		$sql = "select * from ( "
				."select t_a.*, t_c.signin_time as standard_signin_time, t_c.signin_ignore, t_c.signout_time as standard_signout_time, t_c.signout_ignore, t_c.work_duration as standard_work_duration, t_c.rest_duration as standard_rest_duration "
				.", t_b.att_rec_id0, t_b.att_rec_id0_state, t_b.att_rec_id1, t_b.att_rec_id1_state, t_b.att_rec_id2, t_b.att_rec_id2_state, t_b.att_rec_id3, t_b.att_rec_id3_state, t_b.att_rec_id4, t_b.att_rec_id4_state "
				."from eb_attend_record_t t_a left join eb_attend_time_t t_c on (t_a.att_tim_id=t_c.att_tim_id) "
				."left join eb_attend_daily_t t_b on (t_a.user_id = t_b.user_id and t_a.attend_date = t_b.attend_date and (t_a.att_rec_id = t_b.att_rec_id0 "
				."or t_a.att_rec_id = t_b.att_rec_id1 or t_a.att_rec_id = t_b.att_rec_id2 or t_a.att_rec_id = t_b.att_rec_id3 or t_a.att_rec_id = t_b.att_rec_id4)) "
				."where ($ownerSql1) and t_a.user_id = $userId "
				;
		//工作时长
		if ($checkDuration)
			$sql .= "and (t_a.work_duration>0 or t_a.req_duration>0) ";
		
		//时间范围
		if (isset($searchTimeS) || isset($searchTimeE)) {
			$sql .="and (";
			if (isset($searchTimeS))
				$sql .= "t_a.attend_date>='".substr($searchTimeS, 0 ,10)."' ";
			if (isset($searchTimeE)) {
				if (isset($searchTimeS))
					$sql .= ' and ';
				$sql .= "t_a.attend_date<='$searchTimeE' ";
			}
			
			$sql .=")";
		}
		
		$sql .= ") TMP left join (SELECT max(t_y.att_req_id) AS max_att_req_id, att_rec_id as req_att_rec_id FROM eb_attend_req_t t_x, eb_attend_req_item_t t_y "
				."where t_x.att_req_id = t_y.att_req_id and t_x.user_id = $userId and ($ownerSql2) GROUP BY att_rec_id) t_d on (TMP.att_rec_id = t_d.req_att_rec_id) "
				;
		
		//考勤状态
		if (isset($recState) && isset($recStateCalculaMode) && in_array($recStateCalculaMode, array(1,2))) {
// 			where ((att_rec_id0=att_rec_id and (att_rec_id0_state&2)=2) or (att_rec_id1=att_rec_id and (att_rec_id1_state&2)=2) or (att_rec_id2=att_rec_id and (att_rec_id2_state&2)=2)
// 					or (att_rec_id3=att_rec_id and (att_rec_id3_state&2)=2) or (att_rec_id4=att_rec_id and (att_rec_id4_state&2)=2))
			$sql .="where (";
			for ($i=0; $i<5; $i++) {
				if ($i>0)
					$sql .= ' or ';
				$sql .= "(att_rec_id".$i."=att_rec_id and (att_rec_id".$i."_state&$recState)";
				if ($recStateCalculaMode==1)
					$sql .= "=$recState)";
				else if ($recStateCalculaMode==2)
					$sql .= "<>0)";
			}
			$sql .=")";
		}
		
		$limit = 1000;
		$orderBy = 'attend_date desc, standard_signin_time';
		return $this->simpleSearch($sql, null, null, null, $orderBy, $limit);
	}
	
	/**
	 * 更新申请的字段
	 * @param {string} $recId 考勤记录编号
	 * @param {string} $reqSigninTime 申请的签到时间
	 * @param {string} $reqSignoutTime 申请的签退时间
	 * @param {int} $reqDuration 申请的工作时长(分钟)
	 * @return {boolean|array} false=查询失败，array数组
	 */
	public function updateReqFields($recId, $reqSigninTime, $reqSignoutTime, $reqDuration) {
		$sets = array();
		if (isset($reqSigninTime))
			$sets['req_signin_time'] = $reqSigninTime;
		if (isset($reqSignoutTime))
			$sets['req_signout_time'] = $reqSignoutTime;
		if (isset($reqDuration))
			$sets['req_duration'] = $reqDuration;
			
		$wheres = array('att_rec_id'=>$recId);
		$setCheckDigits = array('req_duration');
		$whereCheckDigits = array('att_rec_id');
		
		return $this->update($sets, $wheres, $setCheckDigits, $whereCheckDigits);
	}
	
}