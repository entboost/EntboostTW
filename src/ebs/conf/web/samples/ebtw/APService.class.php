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
		if (!$this->checkConnect()) {
			log_err('APService sendBCMsg error, cannot connect to ap server');
			return false;
		}
		
		//执行查询
		return $this->apAcc->sendBCMsg(3, $title, $content, $targetId);
	}
}