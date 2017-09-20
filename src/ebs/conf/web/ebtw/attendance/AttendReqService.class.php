<?php
require_once dirname(__FILE__).'/../AbstractService.class.php';

class AttendReqService extends AbstractService
{
	private static $instance  = NULL;

	function __construct() {
		parent::__construct();
		$this->primaryKeyName = 'att_req_id';
		$this->tableName = 'eb_attend_req_t';
		$this->fieldNames = 'att_req_id, owner_type, owner_id, user_id, user_name, create_time, last_time, attend_date'
							.', start_time, stop_time, req_duration, req_type, req_status, req_name, req_content, req_param_int';
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
	 * 获取已批准的请假申请，按申请创建时间先后排序
	 * @param {string} $entCode 企业编号
	 * @param {array} $groupCodes 群组编号列表
	 * @param {string} $userId 用户编号
	 * @param {string} $attendDate 考勤日期
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendReqsOfLeave($entCode, array $groupCodes, $userId, $attendDate) {
		if (!isset($entCode) && empty($groupCodes)) {
			log_err('getAttendReqsOfLeave error, $entCode and $groupCodes are empty');
			return false;
		}
		if (empty($userId)) {
			log_err('getAttendReqsOfLeave error, $userId is empty');
			return false;
		}
		if (empty($attendDate)) {
			log_err('getAttendReqsOfLeave error, $attendDate is empty');
			return false;
		}
		/*
		select * from eb_attend_req_t where req_type=3 and req_status=2
		and user_id=80
		and start_time <= '2017-08-23 23:59:59' and stop_time >= '2017-08-23 00:00:00'
		and owner_type=1 and owner_id=1000000000000030
		order by create_time
		 */
		$params = array('user_id'=>$userId, 'req_type'=>3, 'req_status'=>2
				, 'start_time'=>new SQLParam("$attendDate 23:59:59", 'start_time', SQLParam::$OP_LT_EQ), 'stop_time'=>new SQLParam("$attendDate 00:00:00", 'stop_time', SQLParam::$OP_GT_EQ));
		//归属条件
		if (isset($entCode)) {
			$params['owner_type'] = 1;
			$params['owner_id'] = $entCode;
		}
		if (!empty($groupCodes)) {
			$params['owner_type'] = 2;
			$params['owner_id'] = new SQLParam($groupCodes, 'owner_id', SQLParam::$OP_IN);
		}
		
		$limit = 1000;
		$orderBy = 'create_time';
		$checkDigits = array('owner_type', 'owner_id', 'user_id');
		return $this->search($this->fieldNames, $params, $checkDigits, $orderBy, $limit);		
	}
	
	/**
	 * 获取一个考勤审批申请记录
	 * @param {string} $reqId 考勤审批申请编号
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendReq($reqId) {
		if (empty($reqId)) {
			log_err('getAttendReq error, $reqId is empty');
			return false;
		}
		
		/*
		select t_a.*, t_d.account as user_account from eb_attend_req_t t_a, user_account_t t_d
		where t_a.user_id=t_d.user_id and att_req_id = 2017061615383302275
		 */
		$sql = "select t_a.*, t_d.account as user_account from eb_attend_req_t t_a, user_account_t t_d "
				."where t_a.user_id=t_d.user_id";
		return $this->simpleSearch($sql, null, array($this->primaryKeyName=>$reqId), array($this->primaryKeyName));
	}
	
	/**
	 * 获取考勤审批申请记录
	 * @param {string} $entCode 企业编号
	 * @param {array} $groupCodes 群组编号列表
	 * @param {string} $userId 用户编号
	 * @param {int} $reqStatus 审批状态
	 * @param {int} $reqType 申请类型
	 * @param {string} $searchTimeS [可选] 查询考勤日期开始
	 * @param {string} $searchTimeE [可选] 查询考勤日期结束
	 * @return {boolean|array} false=查询失败，array=结果列表
	 */
	function getAttendReqs($entCode, array $groupCodes, $userId, $reqStatus=NULL, $reqType=NULL, $searchTimeS=NULL, $searchTimeE=NULL) {
		if (!isset($entCode) && empty($groupCodes)) {
			log_err('getAttendReqs error, $entCode and $groupCodes are empty');
			return false;
		}
		if (empty($userId)) {
			log_err('getAttendReqs error, $userId is empty');
			return false;
		}
		
		$params = array('user_id'=>$userId);
		
		if (isset($entCode)) {
			$params['owner_type'] = 1;
			$params['owner_id'] = $entCode;
		}
		if (!empty($groupCodes)) {
			$params['owner_type'] = 2;
			$params['owner_id'] = new SQLParam($groupCodes, 'owner_id', SQLParam::$OP_IN);
		}
		if (isset($reqStatus))
			$params['req_status'] = $reqStatus;
		if (isset($reqType))
			$params['req_type'] = $reqType;
		////((start_time>='2017-05-01 01:00:00' and start_time<='2017-07-30 23:59:59.000') or (stop_time>='2017-05-01 01:00:00' and stop_time<='2017-07-30 23:59:59.000'))
		if (isset($searchTimeS) || isset($searchTimeE)) {
			$startTimeParams = array();
			if (isset($searchTimeS))
				$startTimeParams['start_time_s'] = new SQLParam($searchTimeS, 'start_time', SQLParam::$OP_GT_EQ);
			if (isset($searchTimeE))
				$startTimeParams['start_time_e'] = new SQLParam($searchTimeE, 'start_time', SQLParam::$OP_LT_EQ);
			
			$stopTimeParams = array();
			if (isset($searchTimeS))
				$stopTimeParams['stop_time_s'] = new SQLParam($searchTimeS, 'stop_time', SQLParam::$OP_GT_EQ);
			if (isset($searchTimeE))
				$stopTimeParams['stop_time_e'] = new SQLParam($searchTimeE, 'stop_time', SQLParam::$OP_LT_EQ);			
				
			$params['start_time_combine'] = new SQLParamComb(array(new SQLParamComb($startTimeParams), new SQLParamComb($stopTimeParams)), SQLParamComb::$TYPE_OR);
		}
		
		$limit = 1000;
		$orderBy = 'create_time desc';
		$checkDigits = array('owner_type', 'owner_id', 'user_id', 'req_type', 'req_status');
		return $this->search($this->fieldNames, $params, $checkDigits, $orderBy, $limit);
	}
	
	/**
	 * 获取与考勤审批关联的考勤记录
	 * @param {array} $reqIds 审批申请编号列表
	 * @return {boolean|array} false=查询失败，array数组
	 */
	function getAttendRecordsByReqId(array $reqIds) {
		if (empty($reqIds)) {
			log_err('$reqIds is empty');
			return false;
		}
		/*
		select TMP.*, t_e.att_rec_id0, t_e.att_rec_id0_state, t_e.att_rec_id1, t_e.att_rec_id1_state, t_e.att_rec_id2, t_e.att_rec_id2_state
			, t_e.att_rec_id3, t_e.att_rec_id3_state, t_e.att_rec_id4, t_e.att_rec_id4_state from (
			SELECT t_a.att_req_id, t_c.att_rec_id, t_c.create_time, t_c.last_time, t_c.attend_date, t_c.att_rul_id, t_c.att_tim_id, t_c.signin_time, t_c.signout_time, t_c.data_flag
				, t_c.req_signin_time, t_c.req_signout_time, t_c.work_duration, t_c.req_duration
				, t_d.name as tim_name, t_d.signin_time as standard_signin_time, t_d.signout_time as standard_signout_time, t_d.signin_ignore, t_d.signout_ignore
				, t_d.rest_duration as standard_rest_duration, t_d.work_duration as standard_work_duration
			from eb_attend_req_t t_a, eb_attend_req_item_t t_b, eb_attend_record_t t_c, eb_attend_time_t t_d
			where t_a.att_req_id = t_b.att_req_id and t_b.att_rec_id = t_c.att_rec_id and t_c.att_tim_id = t_d.att_tim_id
			and t_a.att_req_id in (2017061910581306876, 2017061615383302275, 2017061615383302275)
		) TMP left join eb_attend_daily_t t_e on(
			t_e.att_rec_id0=TMP.att_rec_id or t_e.att_rec_id1=TMP.att_rec_id or t_e.att_rec_id2=TMP.att_rec_id or t_e.att_rec_id3=TMP.att_rec_id or t_e.att_rec_id4=TMP.att_rec_id)
		order by TMP.attend_date, TMP.standard_signin_time, TMP.standard_signout_time
		*/
		
		//拼接查询语句
		$sql = 	'select TMP.*, t_e.att_rec_id0, t_e.att_rec_id0_state, t_e.att_rec_id1, t_e.att_rec_id1_state, t_e.att_rec_id2, t_e.att_rec_id2_state '
				.', t_e.att_rec_id3, t_e.att_rec_id3_state, t_e.att_rec_id4, t_e.att_rec_id4_state from ('
				.'SELECT t_a.att_req_id, t_c.att_rec_id, t_c.create_time, t_c.last_time, t_c.attend_date, t_c.att_rul_id, t_c.att_tim_id, t_c.signin_time, t_c.signout_time, t_c.data_flag '
				.', t_c.req_signin_time, t_c.req_signout_time, t_c.work_duration, t_c.req_duration '
				.', t_d.name as tim_name, t_d.signin_time as standard_signin_time, t_d.signout_time as standard_signout_time, t_d.signin_ignore, t_d.signout_ignore '
				.', t_d.rest_duration as standard_rest_duration, t_d.work_duration as standard_work_duration '
				.'from eb_attend_req_t t_a, eb_attend_req_item_t t_b, eb_attend_record_t t_c, eb_attend_time_t t_d '
				.'where t_a.att_req_id = t_b.att_req_id and t_b.att_rec_id = t_c.att_rec_id and t_c.att_tim_id = t_d.att_tim_id '
				."and t_a.att_req_id in (".implode(',', $reqIds).") "
				.') TMP left join eb_attend_daily_t t_e on('
				.'t_e.att_rec_id0=TMP.att_rec_id or t_e.att_rec_id1=TMP.att_rec_id or t_e.att_rec_id2=TMP.att_rec_id or t_e.att_rec_id3=TMP.att_rec_id or t_e.att_rec_id4=TMP.att_rec_id) '
				;
		
		$orderby = 'TMP.attend_date, TMP.standard_signin_time, TMP.standard_signout_time';
		$limit = 1000;
		return $this->simpleSearch($sql, null, null, null, $orderby, $limit);
	}
	
	/**
	 * 获取考勤审批申请记录
	 * @param {array} $recIds 考勤记录编号列表
	 * @param {int} $reqStatus [可选] 考勤审批申请状态，默认NULL
	 * @param {int} $reqType [可选] 考勤审批类型，默认NULL
	 * @return {boolean|array} false=查询失败，array数组
	 */
	function getAttendReqsByRecId(array $recIds, $reqStatus=NULL, $reqType=NULL) {
		if (empty($recIds)) {
			log_err('$recIds is empty');
			return false;
		}
		
		/*
		select t_a.*, t_b.att_rec_id, t_b.req_start_time, t_b.req_stop_time, t_b.req_duration as item_req_duration from eb_attend_req_t t_a join eb_attend_req_item_t t_b
		on (t_a.att_req_id = t_b.att_req_id)
		join (SELECT max(t_y.att_req_id) AS max_att_req_id, t_y.att_rec_id FROM eb_attend_req_t t_x, eb_attend_req_item_t t_y 
			where t_x.att_req_id = t_y.att_req_id and t_x.req_status=2 and t_x.req_type=3
			and t_y.att_rec_id in (2017070709142400470, 2017070810592800324) GROUP BY t_y.att_rec_id
		) TMP on (t_a.att_req_id=TMP.max_att_req_id and t_b.att_rec_id=TMP.att_rec_id)
		 where t_a.req_status=2 and t_a.req_type=3
		 and t_b.att_rec_id in (2017070709142400470, 2017070810592800324)
		 */
		$sql = "select t_a.*, t_b.att_rec_id, t_b.req_start_time, t_b.req_stop_time, t_b.req_duration as item_req_duration from eb_attend_req_t t_a join eb_attend_req_item_t t_b "
				."on (t_a.att_req_id = t_b.att_req_id) "
				."join (SELECT max(t_y.att_req_id) AS max_att_req_id, t_y.att_rec_id FROM eb_attend_req_t t_x, eb_attend_req_item_t t_y "
				."where t_x.att_req_id = t_y.att_req_id ";
		if (isset($reqStatus))
			$sql .= "and t_x.req_status=$reqStatus ";
		if (isset($reqType))
			$sql .= "and t_x.req_type=$reqType ";
		$sql .= "and t_y.att_rec_id in (".implode(',', $recIds).") GROUP BY t_y.att_rec_id "
				.") TMP on (t_a.att_req_id=TMP.max_att_req_id and t_b.att_rec_id=TMP.att_rec_id) "
				."where t_b.att_rec_id in (".implode(',', $recIds).") ";
		if (isset($reqStatus))
			$sql .= "and t_a.req_status=$reqStatus ";
		if (isset($reqType))
			$sql .= "and t_a.req_type=$reqType ";
		
		$limit = 1000;
		return $this->simpleSearch($sql, null, null, null, null, $limit);
	}
	
	/**
	 * 获取考勤审批申请记录
	 * @param {string} $entCode 企业编号
	 * @param {array} $groupCodes 群组编号列表
	 * @param {string} $userId 用户编号
	 * @param {array} $reqIAndRecIds 考勤审批申请编号和考勤记录编号分组列表
	 * @param {int} $reqStatus [可选] 审批状态
	 * @return {boolean|array} false=查询失败，array数组
	 */
	function getAttendReqsByReqIAndRecIds($entCode, array $groupCodes, $userId, array $reqIAndRecIds, $reqStatus=NULL) {
		if (empty($groupCodes) && !isset($entCode)) {
			log_err('$groupCodes and $entCode are all empty');
			return false;
		}
		if (empty($userId)) {
			log_err('$userId is empty');
			return false;
		}
		if (empty($reqIAndRecIds)) {
			log_err('$reqIAndRecIds is empty');
			return false;
		}
		/*
		select t_a.*, t_b.att_rec_id, t_b.req_start_time as item_req_start_time, t_b.req_stop_time as item_req_stop_time, t_b.req_duration as item_req_duration from eb_attend_req_t t_a join eb_attend_req_item_t t_b on (t_a.att_req_id=t_b.att_req_id)
		where t_a.user_id = 80 and (t_a.owner_type = 1 and t_a.owner_id = 1000000000000030)
		and ((t_a.att_req_id = 2017061615383302275 and t_b.att_rec_id = 2017061409580007361) or (t_a.att_req_id = 2017061910313705524 and t_b.att_rec_id = 2017052517490007195))
		and req_status = 2
		 */
		//归属条件
		$ownerSql = "";
		if (!empty($groupCodes)) {
			$ownerSql .= "(t_a.owner_id in (".implode(',', $groupCodes).") and t_a.owner_type = 2)";
		}
		if (isset($entCode)) {
			if (!empty($ownerSql))
				$ownerSql .= " or ";
			$ownerSql .= "(t_a.owner_id = $entCode and t_a.owner_type = 1)";
		}
		
		//拼接SQL
		$sql = "select t_a.*, t_b.att_rec_id, t_b.req_start_time as item_req_start_time, t_b.req_stop_time as item_req_stop_time, t_b.req_duration as item_req_duration from eb_attend_req_t t_a join eb_attend_req_item_t t_b on (t_a.att_req_id=t_b.att_req_id) "
				."where t_a.user_id = $userId and ($ownerSql) "
				."and ("
				;
		
		//考勤审批申请编号和考勤记录编号
		for ($i=0; $i<count($reqIAndRecIds); $i++) {
			$reqRecIdEntity = $reqIAndRecIds[$i];
			if ($i>0)
				$sql .= " or ";
			$sql .= "(t_a.att_req_id = ".$reqRecIdEntity['att_req_id']." and t_b.att_rec_id = ".$reqRecIdEntity['att_rec_id'].")";
		}
		$sql .= ") ";
		
		//审批状态
		if (isset($reqStatus))
			$sql .= "and req_status = $reqStatus ";
		
		//执行查询
		$limit = 1000;
		return $this->simpleSearch($sql, null, null, null, null, $limit);
	}
	
	/**
	 * 查询一个审批申请记录
	 * @param {string} $entCode 企业编号
	 * @param {array} $groupCodes 群组编号列表
	 * @param {string} $reqId 审批申请编号
	 * @param {int} $validFlag 审批人是否有效；1=有效，0=无效，默认1
	 * @return {boolean|array} false=查询失败，array数组
	 */
	function getAttendReqAndShareUser($entCode, array $groupCodes, $reqId, $validFlag=1) {
		if (empty($groupCodes) && !isset($entCode)) {
			log_err('$groupCodes and $entCode are all empty');
			return false;
		}
		if (empty($reqId)) {
			log_err('$reqId is empty');
			return false;
		}
		/*
		select t_a.*, t_b.share_id, t_b.from_id, t_b.from_type, t_b.share_uid, t_b.share_name, t_b.share_type, t_b.read_flag, t_b.read_time, t_b.valid_flag, t_b.result_status, t_b.result_time
		from eb_attend_req_t t_a, eb_share_user_t t_b
		where t_a.att_req_id = t_b.from_id and t_b.from_type = 11 and t_a.owner_type=1 and t_a.owner_id = 1000000000000030 and t_b.valid_flag = 1
		 */
		
		//归属条件
		$ownerSql = "";
		if (isset($groupCodes) && !empty($groupCodes)) {
			if (!empty($ownerSql)) {
				$ownerSql .= " or ";
			}
			
			$groupCodeSql = "";
			foreach ($groupCodes as $groupCode) {
				if (!empty($groupCodeSql)) {
					$groupCodeSql.=',';
				}
				$groupCodeSql.=$groupCode;
			}
			$ownerSql .= "(t_a.owner_id in ($groupCodeSql) and t_a.owner_type = 2)";
		}
		if (isset($entCode)) {
			if (!empty($ownerSql)) {
				$ownerSql .= " or ";
			}
			$ownerSql .= "(t_a.owner_id = $entCode and t_a.owner_type = 1)";
		}
		if (!empty($ownerSql)) {
			$ownerSql = " and $ownerSql";
		}
		
		//拼接查询语句
		$sql = 'select t_a.*, t_b.share_id, t_b.from_id, t_b.from_type, t_b.share_uid, t_b.share_name, t_b.share_type, t_b.read_flag, t_b.read_time, t_b.valid_flag, t_b.result_status, t_b.result_time '
				.'from eb_attend_req_t t_a, eb_share_user_t t_b '
				."where t_a.att_req_id = t_b.from_id and t_b.from_type = 11 $ownerSql and t_b.valid_flag = $validFlag "
				;
		
		$checkDigits = array($this->primaryKeyName);
		$params = array('att_req_id'=>new SQLParam($reqId, 't_a.att_req_id'));
		return $this->simpleSearch($sql, null, $params, $checkDigits, null, 1);
	}
	
	/**
	 * 获取考勤审批申请记录
	 * $userId和$shareUid至少有一个不能填空
	 * @param {string} $entCode 企业编号
	 * @param {array} $groupCodes 群组编号列表
	 * @param {string} $userId [可选] 申请人的用户编号
	 * @param {string} $shareUid [可选] 审批人的用户编号
	 * @param {object} $formObj 封装查询条件的对象
	 * @param {boolean} $forCount 是否仅查询数量，默认false
	 * @return {boolean|array} false=查询失败，array数组 [0]=总数量result, [1]=结果列表array
	 */
	function getAttendReqList($entCode, array $groupCodes, $userId, $shareUid, $formObj, $forCount=false) {
		if (empty($groupCodes) && !isset($entCode)) {
			log_err('$groupCodes and $entCode are all empty');
			return false;
		}
		if (empty($userId) && empty($shareUid)) {
			log_err('$userId and $shareUid are all empty');
			return false;
		}
		
		//提取查询条件
		$reqStatus = $formObj->req_status;
		$validFlag = $formObj->valid_flag;
		$recState = $formObj->search_rec_state;
		$reqType = $formObj->req_type;
		$searchTimeS = $formObj->search_time_s;
		$searchTimeE = $formObj->search_time_e;
		$userName = $formObj->user_name;
		
		/*
		select t_a.*, t_d.account as user_account, t_b.share_id, t_b.from_id, t_b.from_type, t_b.share_uid, t_b.share_name, t_b.share_type, t_b.read_flag, t_b.read_time, t_b.valid_flag, t_b.result_status, t_b.result_time 
		from eb_attend_req_t t_a, eb_share_user_t t_b, user_account_t t_d 
		where t_a.att_req_id = t_b.from_id and t_a.user_id=t_d.user_id and t_b.from_type = 11 and t_a.owner_type=1 and t_a.owner_id = 1000000000000030
		and t_a.user_id = 80 and share_uid = 888002 and t_a.user_name like '%系统管理员%'
		and t_a.att_req_id in (select x_1.att_req_id from eb_attend_req_item_t x_1 join eb_attend_daily_t x_2 on (
				(x_2.att_rec_id0 = x_1.att_rec_id and att_rec_id0_state&2=2) or (x_2.att_rec_id1 = x_1.att_rec_id and att_rec_id1_state&2=2) 
				or (x_2.att_rec_id2 = x_1.att_rec_id and att_rec_id2_state&2=2) or (x_2.att_rec_id3 = x_1.att_rec_id and att_rec_id3_state&2=2) or (x_2.att_rec_id4 = x_1.att_rec_id and att_rec_id4_state&2=2) 
			) where x_1.att_req_id=t_a.att_req_id and x_2.user_id=80)
		and req_type in (0,1,2,3,4)
		and ((req_type in (1,2) and t_a.attend_date>='2017-05-01' and t_a.attend_date<='2017-07-30 23:59:59.000')
			or (req_type in (3,4) and ((start_time>='2017-05-01 01:00:00' and start_time<='2017-07-30 23:59:59.000') or (stop_time>='2017-05-01 01:00:00' and stop_time<='2017-07-30 23:59:59.000'))))
		*/
		
		//归属条件
		$ownerSql = "";
		if (isset($groupCodes) && !empty($groupCodes)) {
			if (!empty($ownerSql)) {
				$ownerSql .= " or ";
			}
				
			$groupCodeSql = "";
			foreach ($groupCodes as $groupCode) {
				if (!empty($groupCodeSql)) {
					$groupCodeSql.=',';
				}
				$groupCodeSql.=$groupCode;
			}
			$ownerSql .= "(t_a.owner_id in ($groupCodeSql) and t_a.owner_type = 2)";
		}
		if (isset($entCode)) {
			if (!empty($ownerSql)) {
				$ownerSql .= " or ";
			}
			$ownerSql .= "(t_a.owner_id = $entCode and t_a.owner_type = 1)";
		}
		if (!empty($ownerSql)) {
			$ownerSql = " and $ownerSql";
		}
		
		//拼接查询语句
		$sql = "select t_a.*, t_d.account as user_account, t_b.share_id, t_b.from_id, t_b.from_type, t_b.share_uid, t_b.share_name, t_b.share_type, t_b.read_flag, t_b.read_time, t_b.valid_flag, t_b.result_status, t_b.result_time "
				."from eb_attend_req_t t_a, eb_share_user_t t_b, user_account_t t_d  "
				."where t_a.att_req_id = t_b.from_id and t_a.user_id=t_d.user_id and t_b.from_type = 11 $ownerSql ";
		if (!empty($userId)) {
			$sql .= "and t_a.user_id = $userId ";
		}
		if (!empty($shareUid)) {
			$sql .= "and share_uid = $shareUid ";
		}
		
		if (isset($recState)) {
			$sql .= "and t_a.att_req_id in (select x_1.att_req_id from eb_attend_req_item_t x_1 join eb_attend_daily_t x_2 on (";
			//(x_2.att_rec_id0 = x_1.att_rec_id and att_rec_id0_state&2=2) or (x_2.att_rec_id1 = x_1.att_rec_id and att_rec_id1_state&2=2)
			//or (x_2.att_rec_id2 = x_1.att_rec_id and att_rec_id2_state&2=2) or (x_2.att_rec_id3 = x_1.att_rec_id and att_rec_id3_state&2=2) or (x_2.att_rec_id4 = x_1.att_rec_id and att_rec_id4_state&2=2)
			for ($i=0; $i<5; $i++)
				$sql .= (($i>0)?" or ":"")."(x_2.att_rec_id$i = x_1.att_rec_id and att_rec_id$i"."_state&$recState=$recState ) ";
			
			$sql .= ") where x_1.att_req_id=t_a.att_req_id ";
			if (!empty($userId)) {
				$sql .= " and x_2.user_id=$userId ";
			}
			$sql .= ") ";
		}
		
		if (isset($reqType)) {
			$sql .= "and req_type =$reqType ";
		}
		
		if (isset($reqStatus)) {
			$sql .= "and req_status =$reqStatus ";
		}
		
		if (isset($validFlag)) {
			$sql .= "and valid_flag =$validFlag ";
		}
		
		//时间范围
		$searchTimeSql = '';
		if (!empty($searchTimeS) || !empty($searchTimeE)) {
			$searchTimeSql = 'and (';
			
			//补签、外勤
			$searchTimeSql .= "(req_type in (1,2) ";
			if (!empty($searchTimeS))
				$searchTimeSql .= "and t_a.attend_date>='".substr($searchTimeS, 0, 10)."'";
			if (!empty($searchTimeE))
				$searchTimeSql .= "and t_a.attend_date<='$searchTimeE'";
			$searchTimeSql .= ')';
			
			//请假、考勤
			$searchTimeSql .= "or (req_type in (3,4) and (";
			
			//(start_time>='2017-05-01 01:00:00' and start_time<='2017-07-30 23:59:59.000') or (stop_time>='2017-05-01 01:00:00' and stop_time<='2017-07-30 23:59:59.000')
			$searchTimeSql .= "(";
			if (!empty($searchTimeS))
				$searchTimeSql .= "start_time>='$searchTimeS' ";
			if (!empty($searchTimeE)) {
				if (!empty($searchTimeS))
					$searchTimeSql .= "and ";
				$searchTimeSql .= "start_time<='$searchTimeE' ";
			}
			
			$searchTimeSql .= ") or (";
			if (!empty($searchTimeS))
				$searchTimeSql .= "stop_time>='$searchTimeS' ";
			if (!empty($searchTimeE)) {
				if (!empty($searchTimeS))
					$searchTimeSql .= "and ";
				$searchTimeSql .= "stop_time<='$searchTimeE' ";
			}
				
			$searchTimeSql .= ')';
			$searchTimeSql .= '))';
		}
		if (!empty($searchTimeSql)) {
			$searchTimeSql .= ')';
		}
		$sql .= $searchTimeSql;
		
		//排序字段
		$orderby = $formObj->getOrderby();
		if (empty($orderby) || preg_match('/^signinout_time_rec_state/i', $orderby)) { //signinout_time_rec_state
			$orderby = 't_a.create_time desc';
		} else if (preg_match('/req_time/i', $orderby)) { //req_time
			if (preg_match('/asc$/i', $orderby)) {
				$orderby = 't_a.create_time asc';
			} else if (preg_match('/desc$/i', $orderby)) {
				$orderby = 't_a.create_time desc';
			} else {
				$orderby = 't_a.create_time';
			}
		} else if (preg_match('/^result_time/i', $orderby)) { //result_time
			$orderby = $this->insertTableNameAliasPrefix($orderby, 't_b.');
		} else { //user_name, req_content, req_status
			$orderby = $this->insertTableNameAliasPrefix($orderby, 't_a.');
		}
		
		$limit = $formObj->getPerPage();
		$offset = ($formObj->getCurrentPage()-1)*$formObj->getPerPage();
		$checkDigits = $formObj->createCheckDigits();
		
		$conditions = array();
		$params = array();
		if (!empty($userName))
			$params['user_name'] = new SQLParam("%$userName%", 't_a.user_name', SQLParam::$OP_LIKE); //申请人名称(支持模糊查询)
		
		$sqlOfCount = 'select count(att_req_id) as record_count from ({$sql}) temp_tb';
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
	 * 获取考勤记录与审批申请记录
	 * @param {string} $entCode 企业编号
	 * @param {array} $groupCodes 群组编号列表
	 * @param {string} $userId [可选] 申请人的用户编号
	 * @param {string} $shareUid [可选] 审批人的用户编号
	 * @param {boolean} $isAttendanceManager 是否考勤专员；考勤专员可查询全部人员，非考勤专员按$memberUids列表限制条件
	 * @param {array} $memberUids 部门/群组内成员的用户编号列表
	 * @param {object} $formObj 封装查询条件的对象
	 * @param {boolean} $forCount 是否仅查询数量，默认false
	 * @return {boolean|array} false=查询失败，array数组 [0]=总数量result, [1]=结果列表array
	 */
	function getAttendRecordsLJoinReq($entCode, array $groupCodes, $userId, $shareUid, $isAttendanceManager, array $memberUids, $formObj, $forCount=false) {
		if (empty($groupCodes) && !isset($entCode)) {
			log_err('$groupCodes and $entCode are all empty');
			return false;
		}
		
		//提取查询条件
		$reqStatus = $formObj->req_status;
		$validFlag = $formObj->valid_flag;
		$recAbnormalState = ($formObj->abnormal_rec_state==1)?ATTENDANCE_STATE_ABNORMAL_GROUP:null;
		$recState = $formObj->search_rec_state;
		$reqType = $formObj->req_type;
		$searchTimeS = $formObj->search_time_s;
		$searchTimeE = $formObj->search_time_e;
		$userName = $formObj->user_name;
		/*
		select t_a.*, t_d.account as user_account, t_x.name as tim_name, t_x.signin_time as standard_signin_time, t_x.signout_time as standard_signout_time
			, t_x.signin_ignore, t_x.signout_ignore, t_x.rest_duration as standard_rest_duration, t_x.work_duration as standard_work_duration
			,t_b.att_rec_id0, t_b.att_rec_id0_state, t_b.att_rec_id1, t_b.att_rec_id1_state, t_b.att_rec_id2, t_b.att_rec_id2_state
			, t_b.att_rec_id3, t_b.att_rec_id3_state, t_b.att_rec_id4, t_b.att_rec_id4_state, TMP.*
		from eb_attend_record_t t_a join eb_attend_time_t t_x on t_a.att_tim_id = t_x.att_tim_id
		join user_account_t t_d on t_a.user_id=t_d.user_id
		join eb_attend_daily_t t_b on ((
			(t_b.att_rec_id0 = t_a.att_rec_id and att_rec_id0_state&4=4 and att_rec_id0_state&$recAbnormalState<>0)
			or (t_b.att_rec_id1 = t_a.att_rec_id and att_rec_id1_state&4=4 and att_rec_id1_state&$recAbnormalState<>0)
			or (t_b.att_rec_id2 = t_a.att_rec_id and att_rec_id2_state&4=4 and att_rec_id2_state&$recAbnormalState<>0)
			or (t_b.att_rec_id3 = t_a.att_rec_id and att_rec_id3_state&4=4 and att_rec_id3_state&$recAbnormalState<>0)
			or (t_b.att_rec_id4 = t_a.att_rec_id and att_rec_id4_state&4=4 and att_rec_id4_state&$recAbnormalState<>0))
			and t_a.user_id=80 and t_a.owner_type=1 and t_a.owner_id = 1000000000000030)
		left join (select t_d.att_req_id, t_d.create_time as req_create_time, t_d.last_time as req_last_time, t_d.start_time as req_start_time, t_d.stop_time as req_stop_time
			, t_d.req_type, t_d.req_duration as req_req_duration, t_d.req_status, t_d.req_name, t_d.req_content, t_d.req_param_int
			, t_c.att_rec_id as tmp_att_rec_id, t_e.share_id, t_e.from_id, t_e.from_type, t_e.share_uid, t_e.share_name, t_e.share_type
			, t_e.read_flag, t_e.read_time, t_e.valid_flag, t_e.result_status, t_e.result_time
		from eb_attend_req_item_t t_c, eb_attend_req_t t_d, eb_share_user_t t_e
		where t_c.att_req_id=t_d.att_req_id and t_d.owner_type=1 and t_d.owner_id = 1000000000000030
		and t_d.att_req_id = t_e.from_id and t_e.from_type = 11 and t_d.user_id=80 and t_e.share_uid = 888002
		) TMP on t_a.att_rec_id=TMP.tmp_att_rec_id
		where req_type in (0,1,2,3,4)
		and ((concat(t_a.attend_date, concat(' ', t_x.signin_time))>='2017-05-01 01:00:00' and concat(t_a.attend_date, concat(' ', t_x.signin_time))<='2017-07-30 23:59:59.000')
			or (concat(t_a.attend_date, concat(' ', t_x.signout_time))>='2017-05-01 01:00:00' and concat(t_a.attend_date, concat(' ', t_x.signout_time))<='2017-07-30 23:59:59.000'))
		and t_a.user_name like '%系统管理员%'
		 */
		
		//规则归属条件
		$ownerSql = "";
		if (isset($groupCodes) && !empty($groupCodes)) {
			if (!empty($ownerSql)) {
				$ownerSql .= " or ";
			}
		
			$groupCodeSql = "";
			foreach ($groupCodes as $groupCode) {
				if (!empty($groupCodeSql)) {
					$groupCodeSql.=',';
				}
				$groupCodeSql.=$groupCode;
			}
			$ownerSql .= "(t_a.owner_id in ($groupCodeSql) and t_a.owner_type = 2)";
		}
		if (isset($entCode)) {
			if (!empty($ownerSql)) {
				$ownerSql .= " or ";
			}
			$ownerSql .= "(t_a.owner_id = $entCode and t_a.owner_type = 1)";
		}
		if (!empty($ownerSql)) {
			$ownerSql = " and $ownerSql";
		}
		$ownerSql2 = preg_replace('/t_a\./', 't_d.', $ownerSql);
		
		//拼接查询语句
		$sql = "select t_a.*, t_d.account as user_account, t_x.name as tim_name, t_x.signin_time as standard_signin_time, t_x.signout_time as standard_signout_time "
				.", t_x.signin_ignore, t_x.signout_ignore, t_x.rest_duration as standard_rest_duration, t_x.work_duration as standard_work_duration "
				.",t_b.att_rec_id0, t_b.att_rec_id0_state, t_b.att_rec_id1, t_b.att_rec_id1_state, t_b.att_rec_id2, t_b.att_rec_id2_state "
				.", t_b.att_rec_id3, t_b.att_rec_id3_state, t_b.att_rec_id4, t_b.att_rec_id4_state, TMP.* "
				."from eb_attend_record_t t_a join eb_attend_time_t t_x on t_a.att_tim_id = t_x.att_tim_id "
				."join user_account_t t_d on t_a.user_id=t_d.user_id "
				."join eb_attend_daily_t t_b on (("
				;
		/*
			(t_b.att_rec_id0 = t_a.att_rec_id and att_rec_id0_state&4=4 and att_rec_id0_state&$recAbnormalState<>0)
			or (t_b.att_rec_id1 = t_a.att_rec_id and att_rec_id1_state&4=4 and att_rec_id1_state&$recAbnormalState<>0)
			or (t_b.att_rec_id2 = t_a.att_rec_id and att_rec_id2_state&4=4 and att_rec_id2_state&$recAbnormalState<>0)
			or (t_b.att_rec_id3 = t_a.att_rec_id and att_rec_id3_state&4=4 and att_rec_id3_state&$recAbnormalState<>0)
			or (t_b.att_rec_id4 = t_a.att_rec_id and att_rec_id4_state&4=4 and att_rec_id4_state&$recAbnormalState<>0)
		 */
		for ($i=0; $i<5; $i++) {
			$sql.= ($i>0?' or ':'')."(t_b.att_rec_id$i = t_a.att_rec_id";
			if (isset($recState))
				$sql .= " and att_rec_id".$i."_state&$recState=$recState";
			if (isset($recAbnormalState))
				$sql .= " and att_rec_id".$i."_state&$recAbnormalState<>0";
			$sql.=")";
		}
		$sql .= ") ";
		
		//非考勤专员和非部门经理的普通员工，仅查询自己的考勤记录
		if (isset($userId) && !$isAttendanceManager && empty($memberUids)) {
			$sql .= "and t_a.user_id=$userId ";
		} //查询部门成员的考勤记录
		if (!$isAttendanceManager && !empty($memberUids)) {
			$sql .= "and t_a.user_id in (".implode(',', $memberUids).") ";
		}
		
// 		if (!empty($userId)) {
// 			$sql .= "and t_a.user_id=$userId ";
// 		} else if (!$isAttendanceManager) {
// 			if (empty($memberUids))
// 				$sql .= "and 1<>1 ";
// 			else
// 				$sql .= 'and t_a.user_id in ('.implode(',', $memberUids).') ';
// 		}
		
		$sql .= " $ownerSql) ";
		
		$sql .= "left join (select t_d.att_req_id, t_d.create_time as req_create_time, t_d.last_time as req_last_time, t_d.start_time as req_start_time, t_d.stop_time as req_stop_time "
				.", t_d.req_type, t_d.req_duration as req_req_duration, t_d.req_status, t_d.req_name, t_d.req_content, t_d.req_param_int "
				.", t_c.att_rec_id as tmp_att_rec_id, t_e.share_id, t_e.from_id, t_e.from_type, t_e.share_uid, t_e.share_name, t_e.share_type "
				.", t_e.read_flag, t_e.read_time, t_e.valid_flag, t_e.result_status, t_e.result_time "
				."from eb_attend_req_item_t t_c, eb_attend_req_t t_d, eb_share_user_t t_e "
				."where t_c.att_req_id=t_d.att_req_id $ownerSql2 "
				."and t_d.att_req_id = t_e.from_id and t_e.from_type = 11 "
				;

		//非考勤专员和非部门经理的普通员工，仅查询自己的考勤记录
		if (isset($userId) && !$isAttendanceManager && empty($memberUids)) {
			$sql .= "and t_d.user_id=$userId ";
		} //查询部门成员的考勤记录
		if (!$isAttendanceManager && !empty($memberUids)) {
			$sql .= 'and t_d.user_id in ('.implode(',', $memberUids).') ';
		}
// 		if (!empty($userId)) {
// 			$sql .= "and t_d.user_id=$userId ";
// 		} else if (!$isAttendanceManager) {
// 			if (empty($memberUids))
// 				$sql .= "and 1<>1 ";
// 			else
// 				$sql .= 'and t_d.user_id in ('.implode(',', $memberUids).') ';
// 		}
		
		if (!empty($shareUid)) {
			$sql .= "and t_e.share_uid=$shareUid ";
		}
		$sql .= ") TMP on t_a.att_rec_id=TMP.tmp_att_rec_id ";
		
		$existWhere = false;
		if (isset($reqType) && $reqType!=0) {
			$existWhere = true;
			$sql .= "where req_type =$reqType ";
		}
		//未申请
		if (isset($reqType) && $reqType==0) {
			$existWhere = true;
			$sql .= "where req_type is null ";
		}
		
// 		and ((concat(t_a.attend_date, concat(' ', t_x.signin_time))>='2017-05-01 01:00:00' and concat(t_a.attend_date, concat(' ', t_x.signin_time))<='2017-07-30 23:59:59.000')
// 			or (concat(t_a.attend_date, concat(' ', t_x.signout_time))>='2017-05-01 01:00:00' and concat(t_a.attend_date, concat(' ', t_x.signout_time))<='2017-07-30 23:59:59.000'))
		//时间范围
		$searchTimeSql = '';
		if (!empty($searchTimeS) || !empty($searchTimeE)) {
			$searchTimeSql = $existWhere?'and (':'where (';
			
			$searchTimeSql .= "(";
			if (!empty($searchTimeS))
				$searchTimeSql .= "#concat(t_a.attend_date, #concat(' ', t_x.signin_time))>='$searchTimeS' ";
			if (!empty($searchTimeE)) {
				if (!empty($searchTimeS))
					$searchTimeSql .= "and ";
				$searchTimeSql .= "#concat(t_a.attend_date, #concat(' ', t_x.signin_time))<='$searchTimeE'";
			}
			
			$searchTimeSql .= ") or (";
			if (!empty($searchTimeS))
				$searchTimeSql .= "#concat(t_a.attend_date, #concat(' ', t_x.signout_time))>='$searchTimeS' ";
			if (!empty($searchTimeE)) {
				if (!empty($searchTimeS))
					$searchTimeSql .= "and ";
				$searchTimeSql .= "#concat(t_a.attend_date, #concat(' ', t_x.signout_time))<='$searchTimeE'";
			}
		
			$searchTimeSql .= '))';
		}
		$sql .= $searchTimeSql;
		
		//排序字段
		$orderby = $formObj->getOrderby();
		if (preg_match('/^signinout_time_rec_state/i', $orderby)) { //signinout_time_rec_state
			if (preg_match('/asc$/i', $orderby)) {
				$orderby = 'create_time asc';
			} else if (preg_match('/desc$/i', $orderby)) {
				$orderby = 'create_time desc';
			} else {
				$orderby = 'create_time';
			}
		} else if (preg_match('/req_time/i', $orderby)) { //req_time
			if (preg_match('/asc$/i', $orderby)) {
				$orderby = 'TMP.req_create_time asc';
			} else if (preg_match('/desc$/i', $orderby)) {
				$orderby = 'TMP.req_create_time desc';
			} else {
				$orderby = 'TMP.req_create_time';
			}
		} else if (preg_match('/^user_name/i', $orderby)) { //user_name
			//$orderby = $this->insertTableNameAliasPrefix($orderby, 'TMP.');
		} else if (!empty($orderby)) { //result_time, req_content, req_status
			$orderby = $this->insertTableNameAliasPrefix($orderby, 'TMP.');
		}
		//没有排序字段时按默认排序
		if (empty($orderby))
			$orderby = 'create_time desc';
		
		$limit = $formObj->getPerPage();
		$offset = ($formObj->getCurrentPage()-1)*$formObj->getPerPage();
		$checkDigits = $formObj->createCheckDigits();
		
		$conditions = array();
		$params = array();
		if (!empty($userName))
			$params['user_name'] = new SQLParam("%$userName%", 't_a.user_name', SQLParam::$OP_LIKE); //申请人名称(支持模糊查询)
		
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
}