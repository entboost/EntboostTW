<?php
require_once dirname(__FILE__).'/common.php';
require_once dirname(__FILE__).'/ebapi/EBLC.class.php';
require_once dirname(__FILE__).'/ebapi/EBUM.class.php';
require_once dirname(__FILE__).'/EBAPServerAccessor.class.php';

/**
 * 恩布UM服务访问类
 *
 */
class EBUMAccessor
{
	//UM实例数组，以userId为索引
	private static $instances  = NULL;
	//同步锁
	private static $umLock = NULL;
	
// 	//LC服务访问实例
// 	private $lc;
	//应用编号
	private $appid;
	//应用密钥
	private $appkey;
	//应用在线key
	private $appOnlineKey;
	
	//同步锁
	private $lock;
	
	//private $date_format;
	
	//UM服务访问地址, string
	public $umServer;
	public $umServerSsl;
	
	//AP实例
	private $ap;
	//UM实例
	private $um;
	//UM会话ID
	public $ebSid;
	//用户编号
	public $userId;
	//用户账号
	public $account;
	//用户名
	public $userName;
	//UM管理KEY
	public $acmKey;
	//登录类型
	public $logonType;
	//用户在线EY
	public $userOnlineKey;
	
	/**
	 * 构造函数
	 */
	function __construct($ap, $appOnlineKey, $userId, $umServer=NULL, $umServerSsl=NULL) {
		$this->appid = EB_IM_APPID;
		$this->appkey = EB_IM_APPKEY;
		$this->ap = $ap;
		$this->appOnlineKey = $appOnlineKey;
		$this->userId = $userId;
		$this->umServer = $umServer;
		$this->umServerSsl = $umServerSsl;
		
		//$this->date_format = DATE_TIME_FORMAT;
		$this->lock = new File_Lock("UMAccessor_".$this->userId.".lock");
	}
	
	/**
	 * 析构函数
	 */
	function __destruct() {
		$this->lock->close();
	}
	
	/**
	 * 获取UM实例
	 * PHP没有多线程，static只相对于当前连接有效
	 * @param {EBAPServerAccessor} $ap AP访问实例
	 * @param {string} $appOnlineKey 应用在线KEY
	 * @param {string} $userId 用户编号
	 * @param {string} $umServerAddr UM服务端访问地址
	 */
	public static function get_instance($ap, $appOnlineKey, $userId, $umServerAddr) {
		if (empty(self::$umLock))
			self::$umLock = new File_Lock("UMAccessor.lock");
		
		self::$umLock->writeLock(); //锁定
		if (empty($instances)) {
			$instances = array();
		}
		if (empty($instances[$userId])) {
			$instances[$userId] = new self($ap, $appOnlineKey, $userId, (EB_HTTP_PREFIX=='http')?$umServerAddr:null, (EB_HTTP_PREFIX=='https')?$umServerAddr:null);
		}
		$instance = $instances[$userId];
		self::$umLock->unlock(); //解锁
		
		return $instance;
	}
	
	//获取UM访问实例
	private function getEBUMInstance() {
		if (empty($this->um)) {
			$server = $this->umServer;
		if (EB_HTTP_PREFIX=='https')
			$server = $this->umServerSsl;
			$this->um = new EBUM($server, $this->appid, $this->appOnlineKey);
		}
		return $this->um;
	}
	
	//清除AP应用访问实例(避免旧数据缓存BUG)
	private function removeEBUMInstance() {
		$this->um = null;
	}
	
	/**
	 * 第三方应用验证
	 * @param {string} $authId 请求验证ID
  	 * @param {string} $fromIp (可选) 用户客户端IP地址
	 * @return {boolean|Array} 失败时返回false，其它情况返回数组
	 */
	function umFauth($authId, $fromIp=NULL) {
		$um = $this->getEBUMInstance();
		$arry = $um->eb_um_fauth($authId, true, $fromIp);
		
		if ($this->ap->checkAndInitApp($arry)) {
			//再次执行业务api
			$arry = $um->eb_um_fauth($authId, true, $fromIp);
		}
		
		if (is_array($arry)) {
			$code = $arry['code'];
			if ($code==0) {
				$this->userId = $arry['user_id'];
				$this->account = $arry['account'];
				$this->userName = $arry['user_name'];				
				$this->logonType = $arry['logon_type'];
				$this->acmKey = $arry['acm_key'];
				$this->userOnlineKey = $arry['user_online_key'];
				
				$results = array();
				array_deepclone($arry, $results);
				//unset($results['code']);
				return $results;
			}
		}
		return false;
	}
	
	/**
	 * 设置用户在线状态
	 * @param {string} $userId 用户编号(数字)
	 * @param {string} $userSignData 自定义数据
	 * @return {Array}
	 */
	function umSetlinestate($userId, $userSignData=NULL) {
		$um = $this->getEBUMInstance();
		$arry = $um->eb_um_setlinestate($userId, $this->userOnlineKey, $this->logonType, $this->ebSid, 5, $this->acmKey, $userSignData);
		
		if ($this->ap->checkAndInitApp($arry)) {
			//再次执行业务api
			$arry = $um->eb_um_setlinestate($userId, $this->userOnlineKey, $this->logonType, $this->ebSid, 5, $this->acmKey, $userSignData);
		}
		
		if (is_array($arry)) {
			$code = $arry['code'];
			if ($code==0) {
				$this->ebSid = $arry['eb_sid'];
				
				$results = array();
				array_deepclone($arry, $results);
				//unset($results['code']);
				return $results;
			}
		}
		return false;
	}
	
	/**
	 * 用户下线
	 * @param {string} $userId 用户编号(数字)
	 * @return boolean
	 */
	function umLogout() {
		$um = $this->getEBUMInstance();
		$arry = $um->eb_um_logout($this->ebSid, $this->userId);
		
		if ($this->ap->checkAndInitApp($arry)) {
			//再次执行业务api
			$arry = $um->eb_um_logout($this->ebSid, $this->userId);
		}
		
		if (is_array($arry)) {
			$code = $arry['code'];
			if ($code==0) {
				$this->ebSid = null;
				$this->acmKey = null;
				$this->userOnlineKey = null;
				return true;
			}
		}
		return false;
	}
}