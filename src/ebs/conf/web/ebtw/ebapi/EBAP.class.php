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
		
		log_info('API:eb_ap_on, data:'.json_encode($data)); //implode(',', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
	}
	
	//应用注销下线
	function eb_ap_off($ebSid) {
		$url = $this->apUri."ebwebap.off";
		$data = array(
			"app_id" => $this->appid,
			"eb_sid" => $ebSid
		);
		
		log_info('eb_ap_off:'.json_encode($data)); //implode(',', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
	}
	
	//获取一个新的数字编号
	function nextBigId($ebSid) {
		$url = $this->apUri."ebwebap.nextbigid";
		
		$data = array(
				"app_id" => $this->appid,
				"eb_sid" => $ebSid
		);
		
		log_debug('API:nextBigId, data:'.json_encode($data)); //implode(',', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
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
			$pCount = count($params);
			for ($j=0; $j<$pCount; $j++) {
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
		
		log_debug("sqlExecute sqls'count = $count");
		
// 		print_r($data);echo '<br>';
		log_debug('sqlExecute:'.json_encode($data)); //implode(',', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
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
			$pCount = count($params);
			for ($i=0; $i<$pCount; $i++) {
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
		log_debug('sqlSelect:'.json_encode($data)); //implode('  |  ', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
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
		log_debug('sendBCMsg:'.json_encode($data)); //implode('  |  ', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
	}
	
	/**
	 * 用于作业检查 id,key参数是否合法，只有检查一次有效
	 * @param string $ebSid 会话编号
	 * @param string $id 检查ID
	 * @param string $key 检查KEY
	 * @return 返回查询结果
	 */
	function checkidkey($ebSid, $id, $key) {
		$url = $this->apUri."ebwebap.checkidkey";
		
		$data = array (
				"app_id" => $this->appid,
				"app_online_key" => $this->appOnlineKey,
				"eb_sid" => $ebSid,
				"id" => $id,
				"key" => $key
			);
		
		log_debug('checkidkey:'.json_encode($data)); //implode('  |  ', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
	}
	
	/**
	 * 用于生成验证参数 id,key
	 * @param string $ebSid 会话编号
	 * @return 返回执行结果
	 */
	function buildidkey($ebSid) {
		$url = $this->apUri."ebwebap.buildidkey";
	
		$data = array (
				"app_id" => $this->appid,
				"app_online_key" => $this->appOnlineKey,
				"eb_sid" => $ebSid
		);
	
		log_debug('buildidkey:'.json_encode($data)); //implode('  |  ', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
	}
	
	/**
	 * 请求执行启劢一个后台作业
	 * @param string $ebSid 会话编号
	 * @param string $jobId 作业ID
	 * @param string $execParams 自定义作业参数，如果是HTTP GET/POST作业，需要自行处理成K=V&…格式的参数
	 * @return 返回执行结果
	 */
	function execjob($ebSid, $jobId, $execParams) {
		$url = $this->apUri."ebwebap.execjob";
		
		$data = array (
				"app_id" => $this->appid,
				"app_online_key" => $this->appOnlineKey,
				"eb_sid" => $ebSid,
				"job_id" => $jobId,
				"exec_params" =>$execParams
		);
		
		log_debug('execjob:'.json_encode($data)); //implode('  |  ', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
	}
	
	/**
	 * 查询作业执行情况
	 * @param string $ebSid 会话编号
	 * @param string $jobExecId 作业执行ID
	 * @return 返回执行结果
	 */
	function getjobexecinfo($ebSid, $jobExecId) {
		$url = $this->apUri."ebwebap.getjobexecinfo";
	
		$data = array (
				"app_id" => $this->appid,
				"app_online_key" => $this->appOnlineKey,
				"eb_sid" => $ebSid,
				"job_exec_id" => $jobExecId
		);
		
		log_debug('getjobexecinfo:'.json_encode($data)); //implode('  |  ', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
	}
	
	/**
	 * 设置系统业务相关数据
	 * @param string $ebSid 会话编号
	 * @param {string} $key 属性字段名
	 * @param {string} $value 属性值
	 * @return 返回执行结果
	 */
	function setSysinfo($ebSid, $key, $value) {
		$url = $this->apUri."ebwebap.sysinfoset";
		
		$data = array (
				"app_id" => $this->appid,
				"app_online_key" => $this->appOnlineKey,
				"eb_sid" => $ebSid,
				$key => $value
		);
		
		log_debug('setSysinfo:'.json_encode($data)); //implode('  |  ', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
	}
	
	/**
	 * 查询系统业务相关数据
	 * @param string $ebSid 会话编号
	 * @param array $keys 字段属性名列表
	 * @return 返回查询结果
	 */
	function getSysinfo($ebSid, array $keys) {
		$url = $this->apUri."ebwebap.sysinfoget";
		
		$data = array (
				"app_id" => $this->appid,
				"app_online_key" => $this->appOnlineKey,
				"eb_sid" => $ebSid
		);
		foreach ($keys as $value) {
			$data[$value] = 1;
		}
		
		log_debug('getSysinfo:'.json_encode($data)); //implode('  |  ', $data)
		$contents = CUrl::doCurlPostRequest($url, $data);
		return $this->handleUrlResult($contents);
	}
}