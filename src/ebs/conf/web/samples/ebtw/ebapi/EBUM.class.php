<?php
require_once dirname(__FILE__).'/../CUrl.class.php';

class EBUM
{
	//UM访问地址
	private $umUri;
	//LC应用编号
	private $appid;
	//应用在线key
	private $appOnlineKey;
	
	function __construct($server, $appid, $appOnlineKey) {
		$this->umUri = EB_HTTP_PREFIX . '://' . $server . REST_VERSION_STR;
		$this->appid = $appid;//EB_IM_APPID;
		$this->appOnlineKey = $appOnlineKey;
	}
	
 	/**
 	 * 第三方应用验证
 	 * @param {string} $authId 请求验证ID
 	 * @param {boolean} $logonWeb (可选) 验证成功，是否同时登录WEB，默认true
  	 * @param {string} $fromIp (可选) 用户客户端IP地址
 	 * @return {Array}
 	 */
	function eb_um_fauth($authId, $logonWeb=true, $fromIp=NULL) {
		log_info('eb_um_fauth, fromIp='.$fromIp.', logonWeb='.$logonWeb.', authId='.$authId.', appid='.$this->appid.', appOnlineKey='.$this->appOnlineKey);
		
		$url = $this->umUri."ebwebum.fauth";
		
		$data = array(
			"app_id" => $this->appid,
			"app_online_key" => $this->appOnlineKey,
			"auth_id" => $authId,
			"logon_web" => "1"
		);
		if (empty($logonWeb))
			$data["logon_web"] = "0";	
		if (!empty($fromIp))
			$data["from_ip"] = $fromIp;
			
		log_info('API:ebwebum.fauth, data:'.implode(',', $data));
		$contents = CUrl::doCurlPostRequest($url, $data);
		log_info(rtrim($contents));
// 		echo $contents, "<br>";
		$arr = json_decode($contents, true);
		return $arr;
	}
	
	/**
	 * 设置用户在线状态
	 * @param {string} $userId 用户编号(数字)
	 * @param {string} $userOnlineKey 用户在线KEY
	 * @param {int} $logonType 登录类型
	 * @param {string} $ebSid 会话编号
	 * @param {int} $lineState 在线状态
	 * @param {string} $acmKey 用户管理KEY
	 * @param {string} $userSignData 自定义数据
	 * @return {Array}
	 */
	function eb_um_setlinestate($userId, $userOnlineKey, $logonType, $ebSid=NULL, $lineState=5, $acmKey=NULL, $userSignData=NULL) {
		log_info('eb_um_setlinestate, userId='.$userId.', userOnlineKey='.$userOnlineKey.', eb_sid='.$ebSid);
		
		$url = $this->umUri."ebwebum.setlinestate";
		$data = array(
			"user_id" => $userId,
			"user_online_key" => $userOnlineKey,
			"logon_type"=>$logonType,
		);
		
		if (!empty($ebSid))
			$data["eb_sid"] = $ebSid;
		if (isset($lineState))
			$data["line_state"] = $lineState;
		if (!empty($acmKey))
			$data["acm_key"] = $acmKey;
		if (isset($usData))
			$data["us_data"] = $userSignData;
		
		log_info('API:ebwebum.setlinestate, data:'.implode(',', $data));
		$contents = CUrl::doCurlPostRequest($url, $data);
		log_info(rtrim($contents));
// 		echo $contents, "<br>";
		$arr = json_decode($contents, true);
		return $arr;
	}
	
	/**
	 * 用户下线
	 * @param {string} $ebSid 会话编号
	 * @param {string} $userId 用户编号(数字)
	 * @return {Array}
	 */
	function eb_um_logout($ebSid, $userId) {
		$url = $this->umUri."ebwebum.logout";
		$data = array(
			"eb_sid" => $ebSid,
			"user_id" => $userId,
		);
		 
		log_info('API:ebwebum.logout, data:'.implode(',', $data));
		$contents = CUrl::doCurlPostRequest($url, $data);
		log_info(rtrim($contents));
// 		echo $contents, "<br>";
		$arr = json_decode($contents, true);
		return $arr;
	}
}