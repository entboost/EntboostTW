<?php
require_once dirname(__FILE__).'/../CUrl.class.php';

class EBAP
{
	//AP访问地址
	private $apUri;
	//LC应用编号
	private $appid;
	//应用在线key
	private $appOnlineKey;
	
	function __construct($server, $appid, $appOnlineKey) {
		$this->apUri = EB_HTTP_PREFIX . '://' . $server . REST_VERSION_STR;
		$this->appid = $appid;//EB_IM_APPID;
		$this->appOnlineKey = $appOnlineKey;
	}
	
	//应用登记上线
	function eb_ap_on($ebSid=null) {
		log_info('eb_ap_on, appid='.$this->appid.', app_online_key='.$this->appOnlineKey.', eb_sid='.$ebSid);
		
		$url = $this->apUri."ebwebap.on";
		$data = array (
			"app_id" => $this->appid,
			"app_online_key" => $this->appOnlineKey
		);
		if (!empty($ebSid))
			$data["eb_sid"] = $ebSid;
		
		log_info('API:eb_ap_on, data:'.implode(',', $data));
		$contents = CUrl::doCurlPostRequest($url, $data);
		log_info(rtrim($contents));
// 		echo $contents, "<br>";
		$arr = json_decode($contents, true);
		return $arr;
	}
	
	//应用注销下线
	function eb_ap_off($ebSid) {
		$url = $this->apUri."ebwebap.off";
		$data = array(
			"app_id" => $this->appid,
			"eb_sid" => $ebSid
		);
		
		log_info('eb_ap_off:'.implode(',', $data));
		$contents = CUrl::doCurlPostRequest($url, $data);
		log_info(rtrim($contents));
		$arr = json_decode($contents, true);
		return $arr;
	}
	
	//获取一个新的数字编号
	function nextBigId($ebSid) {
		$url = $this->apUri."ebwebap.nextbigid";
		
		$data = array(
				"app_id" => $this->appid,
				"eb_sid" => $ebSid
		);
		
		log_info('API:nextBigId, data:'.implode(',', $data));
		$contents = CUrl::doCurlPostRequest($url, $data);
		log_info(rtrim($contents));
		
		$arr = json_decode($contents, true);
		return $arr;
	}
	
	/**
	 * 执行多个更新的sql语句
	 * @param string $ebSid 会话编号
	 * @param array $sqls 数据库执行脚本数组 array(array(sql0, array(p0, p1, p2)),...)
	 * ，p0只支持string、整数、浮点数
	 * @param boolean $transaction 是否事务执行，true:使用，false:不使用
	 * @return mixed 如果全部执行失败，返回boolean类型的false值；否则返回各sql执行结果的array数组
	 */
	function sqlExecute($ebSid, $sqls, $transaction) {
		$count = count($sqls);
		if ($count==0) {
			log_err('sqlExecute error, $sqls\'s count is 0');
			return false;
		}
		
		$url = $this->apUri."ebwebap.sqlexecute";
		
		$data = array(
			"app_id" => $this->appid,
			"transaction" => (int)$transaction,
			"eb_sid" => $ebSid
		);
		
		//array(array(sql0, array(p0, p1, p2)),...)
		for ($i=0; $i<$count; $i++) {
			$sqlKVs = $sqls[$i];
			$sql = $sqlKVs[0];
			$params = $sqlKVs[1];
			
			$data['s'.$i] = $sql;
			//array(p0, p1, p2)
			for ($j=0; $j<count($params); $j++) {
				$p = $params[$j];
				if (is_string($p)) {
					$data['s'.$i.'_p'.$j] = '\'' . $p . '\'';
				} else if (is_bool($p)) {
					$data['s'.$i.'_p'.$j] = (int)$p;
				} else {
					$data['s'.$i.'_p'.$j] = $p;
				}
			}
		}
		
		log_info('sqlExecute sqls count = ' . $count);
		
// 		print_r($data);echo '<br>';
		log_info('sqlExecute:'.implode(',', $data));
		$contents = CUrl::doCurlPostRequest($url, $data);
		log_info(rtrim($contents));
// 		print_r($contents);echo '<br>';
		$arr = json_decode($contents, true);
		return $arr;
	}
	
	/**
	 * 执行一个查询sql语句
	 * @param string $ebSid 会话编号
	 * @param array $sql 查询脚本
	 * @param array $params 格式：array(p0, p1, p2)，p0只支持string、整数、浮点数
	 * @param integer limit 返回的最大记录数，最大MAX_RECORDS_OF_LOADALL=1000
	 * @param integer offset 偏移量，默认0
	 * @param integer $getResult 0=只返回记录条数不返回记录集，1=返回记录条数和记录集
	 * @return mixed 如果查询失败，返回boolean类型的false值；否则返sql查询结果的array数组
	 */
	function sqlSelect($ebSid, $sql, $params, $limit, $offset, $getResult) {
		$url = $this->apUri."ebwebap.sqlselect";
		
		$fixedSql = $sql;
		$fixedLimit = $limit;
		if ($limit>MAX_RECORDS_OF_LOADALL)
			$fixedLimit = MAX_RECORDS_OF_LOADALL;
		
		if ($fixedLimit>0)
			$fixedSql = $fixedSql . ' limit ' . $fixedLimit;
		if ($offset>0)
			$fixedSql = $fixedSql . ' offset ' . $offset;
			
		$data = array(
				"app_id" => $this->appid,
				"eb_sid" => $ebSid,
				"s"		 => $fixedSql,
				"get_result" => $getResult
		);
		
		if (is_array($params)) {
			for ($i=0; $i<count($params); $i++) {
				$p = $params[$i];
				if (is_string($p)) {
					$data['p'.$i] = '\'' . $p . '\'';
				} else if (is_bool($p)){
					$data['p'.$i] = (int)$p;
				} else {
					$data['p'.$i] = $p;
				}
			}
		}
		
// 		print_r($data);echo '<br>';
		log_info('sqlSelect:'.implode('  |  ', $data));
		$contents = CUrl::doCurlPostRequest($url, $data);
		log_info(rtrim($contents));
// 		print_r($contents);echo '<br>';
		$arr = json_decode($contents, true);
		return $arr;
	}
	
	/**
	 * 单发/群发一个提醒消息(广播消息)
	 * @param string $ebSid 会话编号
	 * @param object $targetObject 发送对象，例如：{to_account:'test@entboost.com'}, {to_account:888001} ，{to_group_id:1234}, {to_enterprise_code:3333}
	 * @param int $type 消息类型
	 * @param string $title 消息标题
	 * @param string $content 消息内容，支持HTML格式，必须做URL encode
	 * @return 返回执行结果
	 */
	function sendBCMsg($ebSid, $targetObject, $type, $title, $content) {
		$url = $this->apUri."ebwebap.sendbcmsg";
		
		$data = array_merge(array (
				"app_id" => $this->appid,
				"app_online_key" => $this->appOnlineKey,
				"eb_sid" => $ebSid,
				"type" => $type,
				"title" => $title,
				"content" => $content
			), objectToArray($targetObject));
		
		// 		print_r($data);echo '<br>';
		// log_info(array_keys($data));
		log_info('sendBCMsg:'.implode('  |  ', $data));
		$contents = CUrl::doCurlPostRequest($url, $data);
		log_info(rtrim($contents));
		// 		print_r($contents);echo '<br>';
		$arr = json_decode($contents, true);
		return $arr;
	}
}