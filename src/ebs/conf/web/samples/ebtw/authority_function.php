<?php
require_once dirname(__FILE__).'/common.php';
require_once dirname(__FILE__).'/config.php';
require_once dirname(__FILE__).'/Log.class.php';
require_once dirname(__FILE__).'/EBUMAccessor.php';
require_once dirname(__FILE__).'/ResultHandle.class.php';
require_once dirname(__FILE__).'/rootpath.php';
require_once dirname(__FILE__).'/tempdata/tempdata.php';

//获取客户端ip
function clientIP() {
	$cIP = getenv('REMOTE_ADDR');
	$cIP1 = getenv('HTTP_X_FORWARDED_FOR');
	$cIP2 = getenv('HTTP_CLIENT_IP');
	$cIP1 ? $cIP = $cIP1 : null;
	$cIP2 ? $cIP = $cIP2 : null;
	return $cIP;
}

//清除当前会话和cookie
function clearSessionAndCookie() {
	unset($_SESSION[USER_LOGINED_NAME]);
	unset($_SESSION[USER_ID_NAME]);
	unset($_SESSION[USER_ACCOUNT_NAME]);
	unset($_SESSION[USER_NAME_NAME]);
	unset($_SESSION[UM_EB_ACM_KEY_NAME]);
	unset($_SESSION[UM_EB_SID_NAME]);
}

/**
 * 会话失效回调函数
 * @param {string} $sessionId 会话编号
 */
function invalidSessionCallback($sessionId) {
	global $ECHO_MODE;
// 	log_info($ECHO_MODE);
	if ($ECHO_MODE=='html') { //HTML模式
// 		header('Location: eb-open-subid://'.$subId.',0');
// 		exit;//在每个重定向之后都必须加上“exit",避免发生错误后，继续执行。
		global $subId;
		if (isset($subId)) {
			$url = 'eb-open-subid://'.$subId.',0';
			log_info($url);
			echo '<script type="text/javascript">window.location.href = "'.$url.'";</script>';
		} else {
			log_err('invalidSessionCallback sessionId='.$sessionId.', but miss subId');
		}
	} else { //JSON模式
		//EB_STATE_UNAUTH_ERROR=11
		$outputMsg = $logMsg = 'session is invalid, sessionId='.$sessionId;
		ResultHandle::errorToJsonAndOutput($outputMsg, $logMsg, true, 11);
	}
	exit;
}

/**
 * 第三方验证成功后回调函数
 * @param {string|int} $fromType 业务功能类型
 */
function fAuthCallback($fromType, $ptrId, $tabType, $postData) {
	switch((int)$fromType) {
		case 0:
			$url = '/workbench_i.php?workbench_mode=board';
			break;
		case 1:
			$url = '/plan/plan_i.php?view_mode=list';
			break;
		case 2:
			$url = '/task/task_i.php?view_mode=list';
			break;
		case 3:
			$url = '/report/daily_i.php?view_mode=list';
			break;
		case 4:
			$url = '/report/report_i.php?view_mode=list';
			break;
	}
	if (!empty($url)) {
		global $ROOT_URL;
		$url = $ROOT_URL.$url;
		
		$tempData = array();
		$accessTempType = 0; // 0=默认(无效)，1=自动打开查询详情页面，2=新建计划或任务
		if (!empty($ptrId)&&$ptrId!='0') {
			$tempData['open_ptr_id'] = $ptrId;
			$accessTempType = 1;
			//$url.="&open_ptr_id=$ptrId";
		}
		if (!empty($tabType)&&$tabType!='-1') {
			$tempData['switch_tab_type'] = $tabType;
			$accessTempType = 1;
			//$url.="&switch_tab_type=$tabType";
		}
		if (!empty($postData)) {
			$tempData['post_data'] = $postData;
			$accessTempType = 2;
		}
		
		//暂存临时数据
		if (!empty($tempData)) {
			$strValue = json_encode($tempData);
			
			$tempKey = create_tempdata($strValue, $accessTempType);
			log_info('fAuthCallback->create_tempdata result='.$tempKey."\n tempData=".$strValue.', accessTempType='.$accessTempType);
			
			if (!empty($tempKey)) {
				$url.="&access_temp_key=$tempKey";
			}
		}
		
		//header('Location: '.$url);
		echo '<script type="text/javascript">window.location.href = "'.$url.'";</script>';
		exit;
	} else {
		echo '<h3>第三方应用成功验证后，无法自动跳转到业务页面';
		exit;
	}
}

/**
 * 验证session会话
 * @param {boolean} $fAuth (可选) 是否刚刚成功执行第三方验证，默认false
 * @param {string} $fAuthCallback (可选) 第三方登录成功时的回调函数名，默认NULL
 * @param {string} $invalidSessionCallback (可选) 会话失效的回调函数名，默认NULL
 * @param {string} $umAddr (可选) 当前用户所属UM服务地址
 * @param {string} $umAddrSSL (可选) 当前用户所属UM服务地址[https]
 * @param {number|string} $logonType 登录类型
 * @param {string} $acmKey (可选) 当前用户访问恩布资源的令牌
 * @param {string} $umEbSid (可选) 当前用户访问UM的会话编号
 * @param {string} $userId (可选) 用户编号(数字)，默认NULL
 * @param {string} $account (可选) 用户登录账号，默认NULL
 * @param {string} $userName (可选) 用户名，默认NULL
 * @param {string} $fromType (可选) 业务类型，默认0；0=工作台，1=计划，2=任务，3=日报，4=报告...
 * @param {array} $customs (可选) 自定义参数，将附加到跳转的URL尾部
 */
function validateSession($fAuth=false, $fAuthCallback=NULL, $invalidSessionCallback=NULL, $umAddr=NULL, $umAddrSSL=NULL, $logonType=NULL, $acmKey=NULL, $umEbSid=NULL, $userId=NULL, $account=NULL, $userName=NULL, $fromType=0, $customs=NULL) {
	mkdirs(SESSION_SAVE_DIR); //如路径不存在，则创建
	session_save_path(SESSION_SAVE_DIR);
	
	$sessionId = @$_COOKIE[session_name()]; //从cookie获取 Session ID
	//log_info('$sessionId='.$sessionId);
	if (!empty($sessionId)) {
		//设置获得的 Session ID
		session_id($sessionId);
	}
	
	//session在服务端保存周期
	session_set_cookie_params(SESSION_EXPIRED_TIME);
	
	//session环境访问开始
	if (!isset($_SESSION))
		session_start();
	
	if (empty($sessionId)) {
		$sessionId = session_id();//获取新的session ID
		log_info('new sessionId=' . $sessionId);
		//不支持cookie保存会话
		//setcookie(session_name(), $sessionId, time() + COOKIE_EXPIRED_TIME); //cookie在客户端保存1周；待定：此语句应放在登录成功后的处理模块中
	}
	
	if ($fAuth) { //第三方验证成功
		$_SESSION[USER_LOGINED_NAME] = true;
		$_SESSION[EB_LOGON_TYPE_NAME] = $logonType;
		$_SESSION[EB_UM_ACM_KEY_NAME] = $acmKey;
		$_SESSION[EB_UM_ADDR_NAME] = $umAddr;
		$_SESSION[EB_UM_ADDR_SSL_NAME] = $umAddrSSL;
		$_SESSION[EB_UM_SID_NAME] = $umEbSid;
		$_SESSION[USER_ID_NAME] = $userId;
		$_SESSION[USER_ACCOUNT_NAME] = $account;
		$_SESSION[USER_NAME_NAME] = $userName;
		
		log_info('sessionId='.$sessionId.' fauth success, fromType='.$fromType);
		if (!empty($fAuthCallback))
			call_user_func_array($fAuthCallback, empty($customs)?array($fromType):array_merge(array($fromType), $customs));
	} else if(empty($_SESSION[USER_LOGINED_NAME])) { //未登录
		log_info('sessionId='.$sessionId.' is not logined');
		
		if (!empty($invalidSessionCallback))
			call_user_func_array($invalidSessionCallback, array($sessionId));
		
		//仅为测试而留
// 		$_SESSION[USER_LOGINED_NAME] = true;
// 		$_SESSION[EB_LOGON_TYPE_NAME] = 65536;
// 		$_SESSION[EB_UM_ACM_KEY_NAME] = 'test-acm-key'; //$acmKey;
// 		$_SESSION[EB_UM_ADDR_NAME] = 'test-um-ip'; //$umAddr;
// 		$_SESSION[EB_UM_ADDR_SSL_NAME] = 'test-um-ip-ssl'; //$umAddrSSL;
// 		$_SESSION[EB_UM_SID_NAME] = 'test-um-sid';//$umEbSid;
// 		$_SESSION[USER_ID_NAME] = 888001;
// 		$_SESSION[USER_ACCOUNT_NAME] = 'test@entboost.com';
// 		$_SESSION[USER_NAME_NAME] = '测试用户';
	}
}