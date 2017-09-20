<?php

/**
 * 访问AP服务功能封装
 * @author nk
 *
 */
class APService
{
	private static $instance  = NULL;
	
	protected  $apAcc;
	protected  $connected;
	
	function __construct() {
		$this->apAcc = EBAPServerAccessor::get_instance();
		$this->connected = $this->apAcc->validAccessIMSession();
	}
	
	/**
	 * 检测连接是否正常
	 * @return boolean
	 */
	protected function checkConnect() {
		if (!$this->connected) {
			$this->$connected = $this->apAcc->validAccessIMSession();
		}
		return $this->connected;
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
	 * 默认检查
	 * @param string $descName
	 */
	protected function defaultCheck($descName) {
		if (!$this->checkConnect()) {
			log_err("APService $descName error, cannot connect to ap server");
			return false;
		}
		return true;
	}
	
	/**
	 * 单发/群发一个提醒消息(广播消息)
	 * @param string $title 消息标题
	 * @param string $content 消息内容，支持HTML格式，必须做URL encode
	 * @param string $targetId 接收提醒消息对象的唯一标识，见$targetType定义
	 * @param string $targetType 对象类型，默认"to_account"
	 * 	'to_account' = 发送给某个用户帐号，支持邮箱帐号，手机号码和用户ID
	 * 	'to_group_id'= 发送给某个群组（部门）下面所有成员
	 *  'to_enterprise_code' = 发送给整个企业ID 下面所有员工
	 * @return 返回执行结果
	 */
	public function sendBCMsg($title, $content, $targetId, $targetType='to_account') {
		if ($this->defaultCheck('sendBCMsg')) {
			return $this->apAcc->sendBCMsg(3, $title, $content, $targetId); //执行
		}
		return false;
	}
	
	/**
	 * 用于作业检查 id,key参数是否合法，只有检查一次有效
	 * @param string $id 检查ID
	 * @param string $key 检查KEY
	 * @return 返回执行结果
	 */
	public function checkidkey($id, $key) {
		if ($this->defaultCheck('checkidkey')) {
			return $this->apAcc->checkidkey($id, $key); //执行查询
		}
		return false;
	}
	
	/**
	 * 用于生成验证参数 id,key
	 * @return 返回执行结果
	 */
	function buildidkey() {
		if ($this->defaultCheck('buildidkey')) {
			return $this->apAcc->buildidkey(); //执行
		}
		return false;
	}
	
	/**
	 * 请求执行启动一个后台作业
	 * @param string $jobId 作业ID
	 * @param string $execParams 自定义作业参数，如果是HTTP GET/POST作业，需要自行处理成K=V&…格式的参数
	 * @return 返回执行结果
	 */
	function execjob($jobId, $execParams) {
		if ($this->defaultCheck('execjob')) {
			return $this->apAcc->execjob($jobId, $execParams); //执行
		}
		return false;
	}
	
	/**
	 * 查询作业执行情况
	 * @param string $jobExecId 作业执行ID
	 * @return 返回执行结果
	 */
	function getjobexecinfo($jobExecId) {
		if ($this->defaultCheck('getjobexecinfo')) {
			return $this->apAcc->getjobexecinfo($jobExecId); //执行查询
		}
		return false;
	}
	
	/**
	 * 设置系统业务相关数据
	 * @param string $attendDailyJobDate 每天考勤作业最后运行日期
	 * @return boolean|int 返回执行结果代码，false=执行失败
	 */
	function setSysinfo($attendDailyJobDate) {
		if (!isset($attendDailyJobDate))
			return false;
		
		if ($this->defaultCheck('setSysinfo')) {
			return $this->apAcc->setSysinfo('attend-daily-job-date', $attendDailyJobDate);
		}
		return false;
	}
	
	/**
	 * 查询系统业务相关数据
	 * @param string $attendDtartDate 是否返回'系统最早开始考勤的日期'
	 * @param string $attendDailyJobDate 是否返回'每天考勤作业最后运行日期'
	 * @return boolean|array 返回执行查询结果，false=执行失败
	 */
	function getSysinfo($attendDtartDate=true, $attendDailyJobDate=true) {
		if ($this->defaultCheck('getSysinfo')) {
			$keys = array();
			if ($attendDtartDate)
				array_push($keys, 'attend-start-date');
			if ($attendDailyJobDate)
				array_push($keys, 'attend-daily-job-date');
			
			return $this->apAcc->getSysinfo($keys);
		}
		return false;
	}
}