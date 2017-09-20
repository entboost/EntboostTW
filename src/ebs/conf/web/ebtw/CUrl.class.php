<?php
require_once dirname(__FILE__).'/common.php';
require_once dirname(__FILE__).'/config.php';
require_once dirname(__FILE__).'/Log.class.php';

class CUrl
{
	/**
	 * @desc 封装curl的调用接口，post的请求方式
	 */
	static function doCurlPostRequest($url, $data = array(), $timeout = 15) {
		if ($url == "" || $data == "" || $timeout <= 0) {
			log_err('doCurlPostRequest error, parameters is invalid');
			return false;
		}
		
// 		echo $url, '<br>';
		
		$con = curl_init($url);
		$header = array ("Connection: close", "Expect:");
		//$header = array ("Connection: keep-alive");
		curl_setopt($con, CURLOPT_HEADER, false);
		curl_setopt($con, CURLOPT_HTTPHEADER, $header);
		curl_setopt($con, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($con, CURLOPT_POST, true);
		curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($con, CURLOPT_TIMEOUT, (int)$timeout);
	
// 		$cookie_file = UNSAFE_COOKIE_FILE;
// 		//指定保存cookie的文件
// 		curl_setopt($con, CURLOPT_COOKIEJAR, $cookie_file);
// 		//指定发送给服务器的cookie文件
// 		curl_setopt($con, CURLOPT_COOKIEFILE, $cookie_file);
		
		//证书验证
		curl_setopt($con, CURLOPT_SSL_VERIFYPEER, true);   // 只信任CA颁布的证书
		curl_setopt($con, CURLOPT_CAINFO, EB_CA_CERT); // CA根证书（用来验证的网站证书是否是CA颁布）
		curl_setopt($con, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
		 
		$output = curl_exec($con);
		
// 		print_r($output.'<br>');
		//echo 'http_code:',curl_getinfo($con,CURLINFO_HTTP_CODE); //获取http状态码
		
		curl_close($con);
		return $output;
	}
	
	/**
	 * @desc 封装curl的调用接口，get的请求方式
	 */
	static function doCurlGetRequest($url, $data = array(), $timeout = 15) {
		if ($url == "" || $timeout <= 0) {
			log_err('doCurlGetRequest error, parameters is invalid');
			return false;
		}
		 
		if (sizeof($data)>0) {
			$url = $url . '?' . http_build_query($data);
		}
// 		echo $url, '<br>';
	
		$con = curl_init();
		
		$header = array ("Connection: close");
		curl_setopt($con, CURLOPT_URL, $url);
		curl_setopt($con, CURLOPT_HEADER, false);
		curl_setopt($con, CURLOPT_HTTPHEADER, $header);
		curl_setopt($con, CURLOPT_TIMEOUT, (int)$timeout);
		curl_setopt($con, CURLOPT_RETURNTRANSFER, TRUE);
		 
// 		$cookie_file = UNSAFE_COOKIE_FILE;
// 		//指定保存cookie的文件
// 		curl_setopt($con, CURLOPT_COOKIEJAR, $cookie_file);
// 		//指定发送给服务器的cookie文件
// 		curl_setopt($con, CURLOPT_COOKIEFILE, $cookie_file);
		 
		//证书验证
		curl_setopt($con, CURLOPT_SSL_VERIFYPEER, true);   // 只信任CA颁布的证书
		curl_setopt($con, CURLOPT_CAINFO, EB_CA_CERT); // CA根证书（用来验证的网站证书是否是CA颁布）
		curl_setopt($con, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
	
		$output = curl_exec($con);
	
//		echo 'http_code:',curl_getinfo($con,CURLINFO_HTTP_CODE); //获取http状态码
		curl_close($con);
		return $output;
	}
	
	/**
	 * @desc 封装curl的调用接口，post的请求方式
	 */
	static function uploadFile($url, $data, $timeout = 300) {
		if ($url == "" || $data == "" || $timeout <= 0) {
			log_err('uploadFile error, parameters is invalid');
			return false;
		}
		
// 		echo $url, '<br>';
	
		$con = curl_init();
		 
		$header = array("Connection: close", "Expect:");
		//$header = array ("Connection: keep-alive");
		//curl_setopt($con, CURLOPT_URL,"http://localhost/up.php");
		curl_setopt($con, CURLOPT_URL,$url);
		curl_setopt($con, CURLOPT_HEADER, false);
		curl_setopt($con, CURLOPT_HTTPHEADER, $header);
		curl_setopt($con, CURLOPT_POST, true);
		curl_setopt($con, CURLOPT_POSTFIELDS, $data);
		curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($con, CURLOPT_TIMEOUT, (int)$timeout);
	
// 		$cookie_file = UNSAFE_COOKIE_FILE;
// 		//指定保存cookie的文件
// 		curl_setopt($con, CURLOPT_COOKIEJAR, $cookie_file);
// 		//指定发送给服务器的cookie文件
// 		curl_setopt($con, CURLOPT_COOKIEFILE, $cookie_file);
		 
		//证书验证
		curl_setopt($con, CURLOPT_SSL_VERIFYPEER, true);   // 只信任CA颁布的证书
		curl_setopt($con, CURLOPT_CAINFO, EB_CA_CERT); // CA根证书（用来验证的网站证书是否是CA颁布）
		curl_setopt($con, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
		 
		$output = curl_exec($con);
		
		curl_close($con);
		return $output;
	}
}