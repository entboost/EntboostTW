<?php
require_once dirname(__FILE__).'/ebapi/EBLC.class.php';
require_once dirname(__FILE__).'/ebapi/EBAP.class.php';

/**
 * 恩布AP服务访问类
 *
 */
class EBAPServerAccessor
{
	//单例模式
	private static $instance  = NULL;
	
	//LC服务访问实例
	private $lc;
	//应用编号
	private $appid;
	//应用密钥
	private $appkey;
	
	//应用在线key保持文件路径
	private $app_online_key_valid_file_path;
	//应用在线key有效最大时长
	private $app_online_key_valid_max_time;
	
	//AP服务访问会话保持文件路径
	private $ap_keep_alive_file_path;
	//AP服务访问会话有效最大时长
	private $ap_keep_alive_max_time;
	
	//同步锁
	private $lock;
	
	private $date_format;
	
	//AP服务访问地址, string
	private $appServer;
	private $appServerSsl;
	//应用在线key
	private $appOnlineKey;
	//应用会话ID
	private $ebSid;
	//AP实例
	private $ap;
	
	/**
	 * 构造函数
	 */
	function __construct() {
		$this->appid = EB_IM_APPID;
		$this->appkey = EB_IM_APPKEY;
		
		$this->app_online_key_valid_file_path = APP_ONLINE_KEY_VALID_FILE_PATH;
		$this->app_online_key_valid_max_time = APP_ONLINE_KEY_VALID_MAX_TIME;
		$this->ap_keep_alive_file_path = AP_KEEP_ALIVE_FILE_PATH;
		$this->ap_keep_alive_max_time = AP_KEEP_ALIVE_MAX_TIME;
		
		$this->date_format = DATE_TIME_FORMAT;
		
		$this->lock = new File_Lock("APServerAccessor.lock");
	}
	
	/**
	 * 析构函数
	 */
	function __destruct() {
		$this->lock->close();
	}
	
	/**
	 * 单例模式
	 * PHP没有多线程，static只相对于当前连接有效
	 */
	public static function get_instance() {
		if(!self::$instance instanceof self) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	//获取应用在线KEY
	public function getAppOnlineKey() {
		return $this->appOnlineKey;
	}
	
	/**
	 * 检查并执行与IM服务端的初始化
	 * @param string $file_path
	 * @param int $max_time
	 * @param int $alive_type
	 * @param boolean $force
	 * @param boolean &$outNeedAlive
	 * @param int &$outCode
	 * @return boolean 与IM服务端连接状态
	 */
	private function checkAlive($file_path, $max_time, $alive_type=1, $force = false, &$outNeedAlive, &$outCode)
	{
		$result = false;;
		$outNeedAlive = false;
		$outCode = -1;
		
		$handle = @fopen($file_path, 'a+b');
		
		if (!$force) {
			fseek($handle, 0);
			$row0 = fgets($handle); //time
			if ($row0===false) { //数据不存在
				$outNeedAlive = true;
			} else {
				$aliveTime = strtotime($row0);
				$second =time()-$aliveTime; //相差秒数
				
				//如果相差秒数大于最大值，标记需要执行心跳
				if ($second >= $max_time) {
					$outNeedAlive = true;
				} else {
					$result = true;
					if ($alive_type==1) { //应用初始化
						$row1 = fgets($handle); //app_online_key
						if (!empty($row1))
							$this->appOnlineKey = trim($row1);
						
						$row2 = fgets($handle); //app_server
						if (!empty($row2))
							$this->appServer = trim($row2);
						
						$row3 = fgets($handle);
						if (!empty($row3))
							$this->appServerSsl = trim($row3);
					} else { //AP上线
						$row1 = fgets($handle); //ebSid
						if (!empty($row1))
							$this->ebSid = trim($row1);
					}
				}
			}
		} else {
			$outNeedAlive = true;
		}
		
		//执行与IM服务端初始化工作
		if ($outNeedAlive) {
			if ($alive_type==1) {
				$outCode = $this->initApp($handle);
				if ($outCode==0)
					$result = true;
			} else {
				$outCode = $this->appOn($handle);
				if ($outCode==0)
					$result = true;
			}
		}
		
		//关闭文件
		fclose($handle);
		
		return $result;
	}
	
	/**
	 * 检测IMSession是否有效
	 * 待定：需要考虑IM服务重启后断线重连
	 */
	function validAccessIMSession() {
		//锁定
		$this->lock->writeLock();
		
		$outNeedAlive = false; //识别是否执行过重新初始化
		$outCode = -1; //访问AP服务端执行结果代码
		//检查app_online_key
		$result1 = $this->checkAlive($this->app_online_key_valid_file_path, $this->app_online_key_valid_max_time, 1, false, $outNeedAlive, $outCode);
		
		if ($result1) {
			//检查eb_sid
			if ($outNeedAlive) {
				$result2 = $this->checkAlive($this->ap_keep_alive_file_path, $this->ap_keep_alive_max_time, 2, true, $outNeedAlive, $outCode);
			} else {
				$result2 = $this->checkAlive($this->ap_keep_alive_file_path, $this->ap_keep_alive_max_time, 2, false, $outNeedAlive, $outCode);
			}
			
			//重新初始化应用 和 APP上线
			if ($outCode==11 || $outCode==44) {
				log_err('app_on error, code = ' . $outCode);
				$this->removeEBAPInstance();
				
				$result1 = $this->checkAlive($this->app_online_key_valid_file_path, $this->app_online_key_valid_max_time, 1, true, $outNeedAlive, $outCode);
				
				if ($result1)
					$result2 = $this->checkAlive($this->ap_keep_alive_file_path, $this->ap_keep_alive_max_time, 2, true, $outNeedAlive, $outCode);
			}
		} else {
			log_err('get app_online_key error, code = ' . $outCode . ', needAlive = ' . $outNeedAlive);
			$result2 = false;
		}
		
		//解锁
		$this->lock->unlock();
		
		return $result1 && $result2;
	}

	/**
	 * IM应用初始化
	 * @return int 结果：code=0表示成功
	 */
	private function initApp($handle) {
		if ($this->lc==null)
			$this->lc = new EBLC(EB_IM_LC_SERVER_USED_BY_SERVER, $this->appid, $this->appkey);
		
		$arry = $this->lc->eb_lc_authAppid();
		
		$code = -1;
		if (is_array($arry)) {
			$code = $arry['code'];
			if ($code==0) {
				$this->appOnlineKey = $arry['app_online_key'];
				$this->appServer = $arry['app_server'];
				if (array_key_exists('app_server_ssl', $arry))
					$this->appServerSsl = $arry['app_server_ssl'];
				else 
					$this->appServerSsl = null;
				
				//写入保持文件
				fseek($handle, 0);
				ftruncate($handle, 0);
				
				fwrite($handle, date($this->date_format));
				
				fwrite($handle, chr(13).chr(10));//换行
				fwrite($handle, $this->appOnlineKey);
				
				fwrite($handle, chr(13).chr(10)); //换行
				fwrite($handle, $this->appServer);
				
				if ($this->appServerSsl) {
					fwrite($handle, chr(13).chr(10)); //换行
					fwrite($handle, $this->appServerSsl);
				}
			}
		}
		return $code;
	}
	
	//获取AP应用访问实例
	private function getEBAPInstance() {
		if ($this->ap==null) {
			$server = $this->appServer;
			if (EB_HTTP_PREFIX=='https')
				$server = $this->appServerSsl;
			
			$this->ap = new EBAP($server, $this->appid, $this->appOnlineKey);
		}
		
		return $this->ap;
	}
	
	//清除AP应用访问实例(避免旧数据缓存BUG)
	private function removeEBAPInstance() {
		$this->ap = null;
	}
	
	/**
	 * IM AP上线
	 * @return int 结果：code=0表示成功
	 */
	private function appOn($handle) {
		$ap = $this->getEBAPInstance();
		$arry = $ap->eb_ap_on();
		
		$code = -1;
		if (is_array($arry)) {
			$code = $arry['code'];
			if ($code==0) {
				$this->ebSid = $arry['eb_sid'];
				
				//写入保持文件
				fseek($handle, 0);
				ftruncate($handle, 0);
				
				fwrite($handle, date($this->date_format));
				
				fwrite($handle, chr(13).chr(10));//换行
				fwrite($handle, $this->ebSid);
			}
		}
		
		return $code;
	}
	
	/**
	 * IM AP下线
	 * @return int 结果：code=0表示成功
	 */
	private function appOff() {
		$ap = $this->getEBAPInstance();
		$arry = $ap->eb_ap_off($this->ebSid);
		
		$code = -1;
		if (is_array($arry)) {
			$code = $arry['code'];
		}
		
		return $code;
	}
	
	//检测会话超时并重新初始化应用
	public function checkAndInitApp($arry, $descName='noname') {
		$retry = false;
	
		if (is_array($arry)) {
			//锁定
			$this->lock->writeLock();
				
			//检测会话过期
			$code = $arry['code'];
			if ($code==11 || $code==44) {
				log_err($descName.' error, code = ' . $code .', going to reInit...');
				
				//重新初始化应用
				$handle = @fopen($this->app_online_key_valid_file_path, 'a+b');
				$code = $this->initApp($handle);
				fclose($handle);
	
				//重新执行应用上线
				$this->removeEBAPInstance();
				if ($code==0) {
					$handle = @fopen($this->ap_keep_alive_file_path, 'a+b');
					$code = $this->appOn($handle);
					fclose($handle);
				}
	
				if ($code==0)
					$retry = true;
			}
				
			//解锁
			$this->lock->unlock();
		}
	
		return $retry;
	}
	
	/**
	 * 生成唯一的大数字
	 * @return string (64位整数，字符串形式存储)
	 */
	function nextBigId() {
		$ap = $this->getEBAPInstance();
		$arry = $ap->nextBigId($this->ebSid);
		
		if ($this->checkAndInitApp($arry)) {
			//再次执行业务api
			$arry = $ap->nextBigId($this->ebSid);
		}
		
		$bigId = '0';
		if (is_array($arry)) {
			$code = $arry['code'];
			if ($code==0)
				$bigId = $arry['bigid'];
		}
		
		return $bigId;
	}
	
	/**
	 * 执行多个SQL(非查询)
	 * @param array $sqls 数据库执行脚本数组 array(array(sql0, array(p0, p1, p2)),...)
	 * ，p0只支持string、整数、浮点数
	 * @param boolean $transaction 是否事务执行，true:使用，false:不使用；默认false
	 * @return boolean|mixed 返回boolean类型的false值；返回各sql执行结果的array数组
	 */
	function sqlExecute($sqls, $transaction=false) {
		$ap = $this->getEBAPInstance();
		$arry = $ap->sqlExecute($this->ebSid, $sqls, $transaction);
		
		if ($this->checkAndInitApp($arry, 'sqlExecute')) {
			//再次执行业务api
			$arry = $ap->sqlExecute($this->ebSid, $sqls, $transaction);
		}
		
		//["code":"0","size":"1","lists":[["result":"1"]    ]]
		if (is_array($arry)) {
			$code = $arry['code'];
			//code不等于0，发生错误
			if ($code!=0) {
				log_err('sqlExecute occur error, code = '.$code);
				return $arry;
			}
			
			if (!$transaction) {
				if (array_key_exists('lists', $arry))
					$arry = $arry['lists'];
			}
		}
		
		return $arry;
	}
	
	/**
	 * 执行一个查询sql语句
	 * @param string $sql 查询脚本
	 * @param array $params 格式：array(p0, p1, p2)，p0只支持string、整数、浮点数
	 * @param integer limit 返回的最大记录数，默认10
	 * @param integer offset 偏移量，默认0
	 * @param integer $getResult 0=只返回记录条数不返回记录集，1=返回记录条数和记录集；默认=1
	 * @return mixed 如果查询失败，返回boolean类型的false值；否则返sql查询结果的array数组
	 */
	function sqlSelect($sql, $params, $limit=10, $offset=0, $getResult=1) {
		$ap = $this->getEBAPInstance();
		$arry = $ap->sqlSelect($this->ebSid, $sql, $params, $limit, $offset, $getResult);
		
		if ($this->checkAndInitApp($arry, 'sqlSelect')) {
			//再次执行业务api
			$arry = $ap->sqlSelect($this->ebSid, $sql, $params, $limit, $offset, $getResult);
		}
		
		if (is_array($arry)) {
			$code = $arry['code'];
			//code不等于0，发生错误
			if ($code!=0) {
				log_err('sqlSelect error, code = '.$code);
				return false;
			}
			
			$arry = $arry['results'];
		}
		
		return $arry;
	}
	
	/**
	 * 单发/群发一个提醒消息(广播消息)
	 * @param @param int $type 消息类型
	 * @param string $title 消息标题
	 * @param string $content 消息内容，支持HTML格式，必须做URL encode
	 * @param string $targetId 接收提醒消息对象的唯一标识，见$targetType定义
	 * @param string $targetType 对象类型，默认"to_account"
	 * 	'to_account' = 发送给某个用户帐号，支持邮箱帐号，手机号码和用户ID
	 * 	'to_group_id'= 发送给某个群组（部门）下面所有成员
	 *  'to_enterprise_code' = 发送给整个企业ID 下面所有员工
	 * @return 返回执行结果
	 */
	function sendBCMsg($type, $title, $content, $targetId, $targetType='to_account') {
		$ap = $this->getEBAPInstance();
		$targetObject = new stdClass();
		$targetObject->{$targetType} = $targetId;
		$arry = $ap->sendBCMsg($this->ebSid, $targetObject, $type, $title, $content);
		
		if ($this->checkAndInitApp($arry, 'sqlSelect')) {
			//再次执行业务api
			$arry = $ap->sendBCMsg($this->ebSid, $targetObject, $type, $title, $content);
		}
		
		$code = -1;
		if (is_array($arry)) {
			$code = $arry['code'];
			//code不等于0，发生错误
			if ($code!=0) {
				log_err('sendBCMsg error, code = '.$code);
				return false;
			}
		}
		
		return $code;
	}
}

