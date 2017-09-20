<?php
require_once dirname(__FILE__).'/../CUrl.class.php';

class EBLC
{
	//LC访问地址
	private $lcUri;
	//LC应用编号
	private $appid;
	//LC应用密钥
	private $appkey;
	
	function __construct($server, $appid, $appkey) {
		$this->lcUri = EB_HTTP_PREFIX . '://' . $server . REST_VERSION_STR;
		$this->appid = $appid;//EB_IM_APPID;
		$this->appkey = $appkey;//EB_IM_APPKEY;
	}
	
	/**
	 * 处理URL访问结果
	 * @param boolean|string $contents 返回内容
	 * @return boolean|array
	 */
	private function handleUrlResult($contents) {
		if ($contents===false)
			return $contents;
		log_debug(rtrim($contents));
		$arr = json_decode($contents, true);
		return $arr;
	}
	
	//appid初始化
	function eb_lc_authAppid() {
		log_info('eb_lc_authAppid, app_id='.$this->appid.', appkey='.$this->appkey);
		
		$url = $this->lcUri."ebweblc.authappid";
		$appid = $this->appid;
		$appwd = md5($this->appid . $this->appkey);
		$data = array(
			"app_id" => $appid,
			"app_password" => $appwd
		);
		
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
		/*
		log_info(rtrim($contents));
// 		echo $contents, "<br>";
		$arr = json_decode($contents, true);
// 		$appOnlineKey = $arr["app_online_key"];
// 		$umUrl = $arr["url"];
		
		return $arr;*/
	}
}