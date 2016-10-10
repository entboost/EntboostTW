<?php
require_once dirname(__FILE__).'/authority_function.php';
require_once dirname(__FILE__).'/html_base.php';
require_once dirname(__FILE__).'/EBAPServerAccessor.class.php';

	//提取第三方应用验证参数
	$authId = get_request_param('authid');
	$fk = get_request_param('fk');
	$color = get_request_param('color');
	$umServerAddr = get_request_param('ums');
	$userId = get_request_param('uid');
	$fromType = get_request_param('from_type');
	$ptrId = get_request_param('ptr_id', '0');
	$tabType = get_request_param('tab_type', '-1');
	$fromIp = null;//clientIP();
	
	if (empty($authId) || empty($fk) || empty($umServerAddr) || empty($userId)) {
		echo '<h3>缺少必要参数';
		return;
	}
	
	//fk=md5([eb_fk_v1]authid;user_ip_address;appid)
	$calculateFk = md5('[eb_fk_v1]'.$authId.';'.$fromIp.';'.EB_IM_APPID);
	if ($calculateFk!=$fk) {
		log_err('fk is not equal, calculateFk='.$calculateFk.', fk='.$fk);
		//echo '<h3>验证fk不通过';
		//return;
	}
	
	$subId = 0;
	$apAcc = EBAPServerAccessor::get_instance();
	if ($apAcc->validAccessIMSession()) {
		$appOnlineKey = $apAcc->getAppOnlineKey();
		$accessor = EBUMAccessor::get_instance($apAcc, $appOnlineKey, $userId, $umServerAddr);
		//执行第三方验证
		$results = $accessor->umFauth($authId, $fromIp);
		
		if ($results!==false && is_array($results)) {
			$subId = $results['sub_id'];
			$postData = $results['post_data'];
			$maxWidth = 128;
			if(mb_strwidth($postData, 'utf8')>$maxWidth)
				$postData = mb_strimwidth($postData, 0, $maxWidth, '', 'utf8');
			
// 			$userId = $results['user_id'];
// 			$account = $results['account'];
// 			$userName = $results['user_name'];
			//设置在线状态
			$results1 = $accessor->umSetlinestate($userId);

			if ($results1!==false && is_array($results1)) {
				validateSession(true, 'fAuthCallback', null, $accessor->umServer, $accessor->umServerSsl, $accessor->logonType, $accessor->acmKey, $accessor->ebSid, $accessor->userId, $accessor->account, $accessor->userName, $fromType, array($ptrId, $tabType, $postData)); //检测会话
				return;
			} else {
				log_err('$accessor->umSetlinestate 失败, userId='.$userId);
			}
		} else {
			log_err('$accessor->umFauth 失败, $authId='.$authId.', fromIp='.$fromIp);
		}
	} else {
		log_err('$apAcc->validAccessIMSession() 失败');
	}

	echo '<h3>第三方应用验证失败';