<?php
require_once dirname(__FILE__).'/../operaterecord/include.php';


//操作日志提醒消息内容格式定义
//"D"表示动态获取实际值
//"S"表示直接显示固定值
$Default_OperaterecordBCMsg_Content_Params = array('p1'=>array('value'=>'user_name', 'type'=>'D'), 'p2'=>array('value'=>'from_name', 'type'=>'D')); //默认变量映射
$OperaterecordBCMsg_Content_Rules = array(
	3=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>"评论/回复", 'content'=>"新评论/回复{{p4}}，评论人：{{p1}}\n{{p2}}"
			, 'p4'=>array('value'=>'(附件)', 'type'=>'S', 'condition'=>'NotEmpty:op_data'))), //新评论/回复{{(附件)}}，评论人：{{张三}}\n{{计划名称}}
			
	10=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'变更负责人',  'content'=>"新负责任务，提交人：{{p1}}\n{{p2}}")), //新负责任务，提交人：{{张三}}\n{{任务名称}}
	
	11=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'添加参与人',  'content'=>"新参与任务，提交人：{{p1}}\n{{p2}}")), //新参与任务，提交人：{{张三}}\n{{任务名称}}
	
	13=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'添加共享人',  'content'=>"新共享{{p4}}，提交人：{{p1}}\n{{p2}}"
			, 'p4'=>array('value1'=>'计划', 'value2'=>'任务', 'type'=>'S', 'by_from_type'=>true))), //新共享计划，提交人：{{张三}}\n{{计划名称}} //新共享任务，提交人：{{张三}}\n{{任务名称}}
			
	20=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'提交评审/评阅',  'content'=>"新{{p4}}，提交人：{{p1}}\n{{p2}}"
			, 'p4'=>array('value1'=>'评审计划', 'value3'=>'评阅日报', 'type'=>'S', 'by_from_type'=>true))), //新评审计划，提交人：{{张三}}\n{{计划名称}} //新评阅日报，提交人：{{张三}}\n{{日报名称}}
			
	21=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'评审/评阅已阅',  'content'=>"{{p4}}，{{p3}}：{{p1}}\n{{p2}}"
			, 'p4'=>array('value1'=>'计划评审人已阅', 'value3'=>'日报评阅人已阅', 'type'=>'S', 'by_from_type'=>true), 'p3'=>array('value1'=>'评审人', 'value3'=>'评阅人', 'type'=>'S', 'by_from_type'=>true))), //计划评审人已阅，评审人：{{张三}}\n{{计划名称}} //日报评阅人已阅，评阅人：{{张三}}\n{{日报名称}}
			
	22=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'评审通过/评阅回复',  'content'=>"{{p4}}，{{p3}}：{{p1}}\n{{p2}}"
			, 'p4'=>array('value1'=>'计划评审人已通过', 'value3'=>'日报评阅人已回复', 'type'=>'S', 'by_from_type'=>true), 'p3'=>array('value1'=>'评审人', 'value3'=>'评阅人', 'type'=>'S', 'by_from_type'=>true))), //计划评审人已通过，评审人：{{张三}}\n{{计划名称}} //日报评阅人已回复，评阅人：{{张三}}\n{{日报名称}}
			
	23=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'评审拒绝',  'content'=>"计划评审被拒绝，评审人：{{p1}}\n{{p2}}")), //计划评审被拒绝，评审人：{{张三}}\n{{计划名称}}
	
	24=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'评审撤销',  'content'=>"计划评审被撤销，提交人：{{p1}}\n{{p2}}", 'no_sub_id'=>true)), //计划评审被撤销，提交人：{{张三}}\n{{计划名称}}
	
	30=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'负责人已阅',  'content'=>"任务负责人已阅，负责人：{{p1}}\n{{p2}}")), //任务负责人已阅，负责人：{{张三}}\n{{任务名称}}
	
	31=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'上报进度',  'content'=>"{{p1}}，上报任务进度{{p4}}%\n{{p2}}"
			, 'p4'=>array('value'=>'op_data', 'type'=>'D'))), //{{张三}}，上报任务进度10%\n{{任务名称}}
	
	32=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'上报工时',  'content'=>"{{p1}}，上报任务工时{{p4}}小时/总耗时{{p3}}小时\n{{p2}}"
			, 'p4'=>array('value'=>'op_data', 'type'=>'D'), 'p3'=>array('value'=>'extend', 'type'=>'D'))), //{{张三}}，上报任务工时{{3}}小时/总耗时{{12.5}}小时\n{{任务名称}}
	
	33=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'标为中止',  'content'=>"任务被中止，中止人：{{p1}}\n{{p2}}")), //任务被中止，中止人：{{张三}}\n{{任务名称}}
	
	34=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'标为完成',  'content'=>"任务已完成，提交人：{{p1}}\n{{p2}}")), //任务已完成，提交人：{{张三}}\n{{任务名称}}
	
	35=>array_merge($Default_OperaterecordBCMsg_Content_Params, array('operate'=>'参与人已阅',  'content'=>"任务参与人已阅，参与人：{{p1}}\n{{p2}}")), //任务参与人已阅，参与人：{{张三}}\n{{任务名称}}	
);

//格式化操作日志提醒消息内容
function formatOperaterecordBCMsgContent(&$noSubId, $opId, $opType, $userId, $userName, $fromType, $fromId, $fromName=NULL, $opData=NULL, $opName=NULL, $remark=NULL, $opTime=NULL, $extend=NULL) {
	global $OperaterecordBCMsg_Content_Rules;
	$rule = $OperaterecordBCMsg_Content_Rules[$opType];
	if (empty($rule)) {
		log_err("no operaterecord bcmsg rule found for opType=$opType");
		return;
	}
	
	if (array_key_exists('no_sub_id', $rule))
		$noSubId = $rule['no_sub_id'];
	
	//3 新评论/回复(附件)，评论人：杨宏展\n[计划名称] 			没附件不显示“附件”
	$content = $rule['content'];
	$result = preg_match_all('/\{\{[A-Za-z0-9]+\}\}/', $content, $matchs);
	if ($result!==false) {
		foreach ($matchs[0] as $pNameWrap) {
			$pNameWrap = trim($pNameWrap);
			$pName = preg_replace('/\}\}/', '', preg_replace('/\{\{/', '', $pNameWrap));
			$pValue = $rule[$pName];			
// 			log_info('1111111111');
// 			log_info($pName);
// 			log_info($pValue);
			
			//触发条件，如没达到条件自动填空白
			if (array_key_exists('condition', $pValue)) {
				$condition = $pValue['condition'];
				if (!empty($condition) && preg_match('/NotEmpty:op_data/', $condition)) {
					if (empty($opData)) {
						$content = preg_replace("/$pNameWrap/", '', $content);
						continue;
					}
				}
			}
			
// 			log_info('222222222222222222');
			$type = $pValue['type'];
			if (empty($type) || $type=='S') { //静态
				if (array_key_exists('by_from_type', $pValue) && !empty($pValue['by_from_type'])) {
					$content = preg_replace("/$pNameWrap/", $pValue['value'.$fromType], $content);
				} else 
					$content = preg_replace("/$pNameWrap/", $pValue['value'], $content);
			} else { //动态
// 				log_info('333333333333333333333333');
				$value = $pValue['value'];
// 				log_info($value);
				switch ($value) {
					case 'user_id':
						$content = preg_replace("/$pNameWrap/", $userId, $content);
						break;
					case 'user_name':
						$content = preg_replace("/$pNameWrap/", $userName, $content);
						break;
					case 'from_name':
						$content = preg_replace("/$pNameWrap/", (string)$fromName, $content);
					case 'op_data':
						$content = preg_replace("/$pNameWrap/", (string)$opData, $content);
						break;
					case 'op_name':
						$content = preg_replace("/$pNameWrap/", $opName, $content);
						break;
					case 'remark':
						$content = preg_replace("/$pNameWrap/", $remark, $content);
						break;
					case 'op_time':
						$content = preg_replace("/$pNameWrap/", $opTime, $content);
						break;
					case 'extend':
						$content = preg_replace("/$pNameWrap/", $extend, $content);
						break;
						break;
				}
			}
		}
	}
	
	return $content;
}

/**
 * 发送提醒消息(用于写入操作日志后)
 * @param {string|array} $target 接收提醒消息对象的唯一标识，见$targetType定义；如array出现重复值(同一个人、同一个群主或同一个企业)，只发送一次
 * @param {string $opId 操作记录主键
 * @param {number} $opType 操作类型
 * @param {string} $userId 操作日志创建者的用户编号
 * @param {string} $userName 操作日志创建者的名称
 * @param {number} $fromType 文档类型：1=计划，2=任务，3=报告
 * @param {string} $fromId 文档编号
 * @param {string} $fromName 文档名称
 * @param {string} (可选) $opData
 * @param {string} (可选) $opName
 * @param {string} (可选) $remark
 * @param {string} (可选) $$opTime
 * @param {string} (可选) $extend
 * @param {string} (可选) $custom 自定义参数，将填入url链接的尾部
 * @param {string} $targetType 对象类型，默认"to_account"；与$target配合使用
 * 	'to_account' = 发送给某个用户帐号，支持邮箱帐号，手机号码和用户ID
 * 	'to_group_id'= 发送给某个群组（部门）下面所有成员
 *  'to_enterprise_code' = 发送给整个企业ID 下面所有员工
 * @return {boolean|int|array} 如果传入的$target不是数组，则返回值不会是数组；如果传入值是数组，返回值将会是数组从而返回每一个执行的结果：当全部发送调用都成功时, 元素[0]等于true
 * 
 * 样例：sendBCMsg_after_operaterecord('test3@entboost.com', '0', 10, '888001', '测试员工', 1, '2016080523591504067','测试的计划名')
 */
function sendBCMsg_after_operaterecord($target, $opId, $opType, $userId, $userName, $fromType, $fromId, $fromName, $opData=NULL, $opName=NULL, $remark=NULL, $opTime=NULL, $extend=NULL, $custom=NULL, $targetType='to_account') {
	log_info("start to sendBCMsg after operaterecord opType=$opType, opId=$opId, from_type=$fromType, form_id=$fromId");
	global $SUB_IDS;
	$subId = $SUB_IDS[$fromType];
	
	//按格式封装内容
	
	$msgContent = formatOperaterecordBCMsgContent($noSubId, $opId, $opType, $userId, $userName, $fromType, $fromId, $fromName, $opData, $opName, $remark, $opTime, $extend);
	if (isset($noSubId) && $noSubId===true)
		$subId = 0;
	//“[计划/任务编号] [计划/任务SUBID,如1002300111] [打开参数,用于用户点击带上参数,例如plan_id=xxxxxxx]”，此处的中括号忽略
	$content = "$fromId"." $subId ". urlencode('ptr_id='.$fromId.(!empty($custom)?"&$custom":""));
	
	$instance = APService::get_instance();
	if (is_array($target)) {
		$target = array_values(array_unique($target)); //过滤重复值
		$results = array();
		$results[0] = true; //标识是否所有调用消息都是成功
		
		foreach ($target as $targetId) {
			log_info($msgContent);
			$result = $instance->sendBCMsg($msgContent, $content, $targetId, $targetType);
			if ($result===false)
				$results[0] = false;
			$results[$targetId] = $result;
		}
		
		return $results;
	} else {
		log_info($msgContent);
		return $instance->sendBCMsg($msgContent, $content, $target, $targetType);
	}
}

/**
 * 查询获取一个指定的文档(计划、任务、报告)资料
 * @param {number} $fromType 文档类型：1=计划，2=任务，3=报告
 * @param {string} $fromId 文档编号
 * @param {boolean} $fetchAuthorityInfo 是否获取权限资料
 * @return {array}
 */
function get_onePtr_before_sendBCMsg($fromType, $fromId, $fetchAuthorityInfo=true) {
	if ($fromType==1)
		$json1 = get_plan($fromId, $fetchAuthorityInfo);
	else if ($fromType==2)
		$json1 = get_task($fromId, $fetchAuthorityInfo);
	else if ($fromType==3)
		$json1 = get_report($fromId, $fetchAuthorityInfo);

	if (!empty($json1)) {
		$results1 = get_results_from_json($json1, $tmpObj1);
		if (!empty($results1))
			return $results1[0];
	}
}

/**
 * 创建操作日志
 * @param {string} $fromId
 * @param {string} $fromName
 * @param {string|int} $fromType
 * @param {string|int} $opType
 * @param {string} $opData
 * @param {string} $opName
 * @param {string} $remark
 * @param {string} $opTime 
 * @param {boolean} $delay 延迟计入
 * @return {json字符串} 执行结果
 */
function create_operaterecord($fromId, $fromName=NULL, $fromType, $opType, $opData=NULL, $opName=NULL, $remark=NULL, $opTime=NULL, $delay=FALSE) {
	$oprFormObj = new EBOperateRecordForm();
	$oprFormObj->from_id = $fromId;
	$oprFormObj->from_name = $fromName;
	$oprFormObj->from_type = $fromType;
	$oprFormObj->op_type = $opType;
	$oprFormObj->op_data = $opData;
	$oprFormObj->op_name = $opName;
	$oprFormObj->op_time = $opTime;
	$oprFormObj->remark = $remark;
	
	$embed = 1;
	if ($delay)
		$now = date(DATE_TIME_FORMAT, strtotime('+1 second'));
	include dirname(__FILE__).'/../operaterecord/saveCreate.php';
	
	return $json;
}

/**
 * 更新操作日志
 * @param {string|int} $fromTypeOfwhere		查询条件
 * @param {string} $fromIdOfwhere			查询条件
 * @param {string|int} $opTypeOfwhere		查询条件
 * @param {string} (可选) $fromName			新的设置值
 * @param {string} (可选) $opData			新的设置值
 * @param {string} (可选) $opName			新的设置值
 * @param {string} (可选) $remark			新的设置值
 * @param {string} (可选) $opTime			新的设置值
 * @return {json字符串} 执行结果
 */
function update_operaterecords($fromTypeOfwhere, $fromIdOfwhere, $opTypeOfwhere, $fromName=NULL, $opData=NULL, $opName=NULL, $remark=NULL, $opTime=NULL) {
	$formObj = new EBOperateRecordForm();
	//查询条件
	$formObj->from_type = $fromTypeOfwhere;
	$formObj->from_id = $fromIdOfwhere;
	$formObj->op_type = $opTypeOfwhere;
	
	//设置值
	$formObj->from_name = $fromName;
	$formObj->op_data = $opData;
	$formObj->op_name = $opName;
	$formObj->remark = $remark;
	$formObj->op_time = $opTime;
	
	$embed = 1;
	include dirname(__FILE__).'/../operaterecord/saveUpdate.php';
	
	return $json;	
}

/**
 * 获取操作日志
 * @param {string|int} $fromTypeOfwhere		查询条件
 * @param {string} $fromIdOfwhere			查询条件
 * @param {string|int} $opTypeOfwhere		查询条件
 *  @return {json字符串} 查询结果
 */
function get_operaterecords($fromTypeOfwhere, $fromIdOfwhere, $opTypeOfwhere) {
	$formObj = new EBOperateRecordForm();
	//查询条件
	$formObj->op_type_class = 0;
	$formObj->from_type = $fromTypeOfwhere;
	$formObj->from_id = $fromIdOfwhere;
	$formObj->op_type = $opTypeOfwhere;
	
	$embed = 1;
	include dirname(__FILE__).'/../operaterecord/list.php';
	
	return $json;	
}

/**
 * 查询操作日志(以创建者编号、创建时间范围为查询条件)
 * @param {array} $fromTypes 文档类型数组，元素值：1=计划，2=任务，3=报告
 * @param {array} $opTypes 操作类型数组，默认NULL(查询全部类型)
 * @param {array} $datetimeAndUseridConditions 创建时间范围及创建者编号查询条件，
 * 			格式：array('123456|2016-05-31'=>array('create_time_s'=>'2016-05-31 00:00:00', 'create_time_e'=>'2016-05-31 23:23:59', 'user_id'=>'123456'), ...)
 */
function get_operaterecords_by_userid_and_createtime(array $fromTypes, $opTypes = NULL, array $datetimeAndUseridConditions) {
	$formObj = new EBOperateRecordForm();
	$formObj->from_type = $fromTypes;
	$formObj->op_type = $opTypes;
	$formObj->datetimeAndUseridConditions = $datetimeAndUseridConditions;
	
	$embed = 1;
	include dirname(__FILE__).'/../operaterecord/list.php';
	
	return $json;
}

/**
 * 统计操作日志数量
 * @param {array} $fromTypes 文档类型数组，元素值：1=计划，2=任务，3=报告
 * @param {array} $fromIds 文档编号数组，可填NULL，配合$createTimeS、$createTimeE和$userId进行查询
 * @param {array} $opTypes 操作类型数组，默认NULL(查询全部类型)
 * @param {array} $datetimeAndUseridConditions (可选) 创建时间范围及创建者编号查询条件，
 * 			格式：array('123456|2016-05-31'=>array('create_time_s'=>'2016-05-31 00:00:00', 'create_time_e'=>'2016-05-31 23:23:59', 'user_id'=>'123456'), ...)
 * @param {numbe} $isDeleted 删除标记
 * @return {json字符串} 查询结果
 */
function count_operaterecords(array $fromTypes, $fromIds = NULL, $opTypes = NULL, $datetimeAndUseridConditions = NULL, $isDeleted = 0) {
	$formObj = new EBOperateRecordForm();
	$formObj->classification_statistic = 1;
	$formObj->from_type = $fromTypes;
	$formObj->from_id = $fromIds;
	$formObj->is_deleted = $isDeleted;
	
	$formObj->op_type = $opTypes;
	$formObj->datetimeAndUseridConditions = $datetimeAndUseridConditions;
	
	$embed = 1;
	include dirname(__FILE__).'/../operaterecord/list.php';
	
	return $json;
}

/**
 * 补全列表记录里相关操作日志的统计数量(以每条文档数据同一创建者编号、创建时间范围为查询条件；以记录数组方式传人参数$results)
 * @param {string} $completingMode 补全方式：list=列表方式，数据将以数组方式填入；prop=属性方式，数据将以属性方式填入(即只填一条补全数据)
 * @param {array} $fromTypes 待补全的文档类型数组，元素值：1=计划，2=任务，3=报告
 * @param {array} $opTypes 操作类型数组
 * @param {array} $datetimeAndUseridConditions 用户编号、创建时间范围条件汇成的数组
 * @param {array} $countRules 统计规则，把多个op_type分成几个大分类，然后累加数量；例如：array('type1'=>array(3,4,5), 'type2'=>array(20,21,22,23))
 * @param {boolean} $keepOrignResults 是否保留输出分类统计原始结果
 * @param {array} $results 输出参数 列表数据记录
 * @param {boolean} $isQuery 输出参数 是否执行过查询
 */
function completing_list_count_of_operaterecords_using_resultsobj_by_userid_and_createtime($completingMode='list', $fromTypes, $opTypes, $datetimeAndUseridConditions, $countRules, $keepOrignResults, &$results, &$isQuery) {
	//执行查询相关操作日志资料
	$json1 = count_operaterecords($fromTypes, null, $opTypes, $datetimeAndUseridConditions);
	$isQuery = true;
	$results1 = get_results_from_json($json1, $tmpObj1);
	log_info('count_operaterecord for datetimeAndUseridConditions:'.implode(',', array_keys($datetimeAndUseridConditions)).'; result count:'.(empty($results1)?0:count($results1)) );
	
// 	if (empty($results1))
// 		return $json;
	
	//遍历并赋值相关字段
	foreach($results as &$ptr) {
		//log_info(json_encode($ptr));
		$datePart = substr($ptr->start_time, 0, 10);
		$key = $ptr->report_uid.'|'.$datePart;
	
		//提取统计原始记录
		if (!empty($results1)) {
			for ($i=0;$i<count($results1); $i++) {
				$ptr1 = $results1[$i];
	
				if ($key == $ptr1->user_id.'|'.$ptr1->create_date) {
					if ($completingMode=='prop') { //属性方式
						break;
					} else if ($completingMode=='list') { //列表方式
						if (!isset($ptr->oprs2))
							$ptr->oprs2 = array();
						$oprs = &$ptr->oprs2;
							
						$opType = $ptr1->op_type;
						if (!isset($oprs[$opType])) {
							$oprs[$opType] = array();
						}
						array_push($oprs[$opType], $ptr1);
					}
				}
			}
		}
		if (!isset($ptr->oprs2))
			$ptr->oprs2 = array();
	
		//进一步统计
		if (!empty($countRules)/* && !empty($ptr->oprs2)*/) {
			if (!isset($ptr->countedOprs))
				$ptr->countedOprs = array();
					
			foreach($countRules as $name=>$countRule) {
				$countedOpr = 0;

				if ($countRule==null) { //全部
					foreach ($ptr->oprs2 as $counts) {
						foreach($counts as $count)
							$countedOpr += $count->c;
					}
				} else { //部分
					foreach ($countRule as $opType) {
						if (array_key_exists($opType, $ptr->oprs2)) {
							$counts = $ptr->oprs2[$opType];
							foreach($counts as $count)
								$countedOpr += $count->c;
						}
					}
				}
				
				if (is_array($ptr->countedOprs))
					$ptr->countedOprs[$name] = $countedOpr;
				else
					$ptr->countedOprs->{$name} = $countedOpr;
			}
		}

		if ($keepOrignResults==false && isset($ptr->oprs2)) {
			unset($ptr->oprs2);
		}
	}
}

/**
 * 补全列表记录里相关操作日志的统计数量(以每条文档数据同一创建者编号、创建时间范围为查询条件)
 * @param {string} $completingMode 补全方式：list=列表方式，数据将以数组方式填入；prop=属性方式，数据将以属性方式填入(即只填一条补全数据)
 * @param {array} $fromTypes 待补全的文档类型数组，元素值：1=计划，2=任务，3=报告
 * @param {array} $opTypes 操作类型数组
 * @param {array} $countRules 统计规则，把多个op_type分成几个大分类，然后累加数量；例如：array('type1'=>array(3,4,5), 'type2'=>array(20,21,22,23))
 * @param {boolean} $keepOrignResults 是否保留输出分类统计原始结果
 * @param {string} $json 列表查询结果
 * @param {boolean} $isQuery 输出参数 是否执行过查询
 * @return {JSON字符串} 补全后的列表查询结果
 */
function completing_list_count_of_operaterecords_by_userid_and_createtime($completingMode='list', $fromTypes, $opTypes, $countRules, $keepOrignResults, $json, &$isQuery) {
	$isQuery = false;
	
	$results = get_results_from_json($json, $tmpObj); //从json字符串提取列表记录
	if (empty($results))
		return $json;
	
	//用户编号、创建时间范围条件汇成数组
	$datetimeAndUseridConditions = array();
	foreach ($results as $ptr) {
		//var_dump($ptr);
		$datePart = substr($ptr->start_time, 0, 10);
		$key = $ptr->report_uid.'|'.$datePart;
		if (!array_key_exists($key, $datetimeAndUseridConditions)) {
			$datetimeAndUseridConditions[$key] = array('create_time_s'=>$datePart.' 00:00:00', 'create_time_e'=>$datePart.' 23:59:59', 'user_id'=>$ptr->report_uid);
		}
	}
	
	completing_list_count_of_operaterecords_using_resultsobj_by_userid_and_createtime($completingMode, $fromTypes, $opTypes, $datetimeAndUseridConditions, $countRules, $keepOrignResults, $results, $isQuery);
	
	return json_encode($tmpObj);
}

/**
 * 补全列表记录里相关操作日志的统计数量(以记录数组方式传人参数$results)
 * @param {string} $completingMode 补全方式：list=列表方式，数据将以数组方式填入；prop=属性方式，数据将以属性方式填入(即只填一条补全数据)
 * @param {array} $fromTypes 待补全的文档类型数组，元素值：1=计划，2=任务，3=报告
 * @param {array} $opTypes 操作类型数组
 * @param {string} $pkFieldName 主键字段名
 * @param {array} $countRules 统计规则，把多个op_type分成几个大分类，然后累加数量；例如：array('type1'=>array(3,4,5), 'type2'=>array(20,21,22,23))
 * @param {boolean} $keepOrignResults 是否保留输出分类统计原始结果
 * @param {array} $results 输出参数 列表数据记录
 * @param {boolean} $isQuery 输出参数 是否执行过查询
 */
function completing_list_count_of_operaterecords_using_resultsobj($completingMode='list', $fromTypes, $opTypes, $pkFieldName, $countRules, $keepOrignResults, &$results, &$isQuery) {
	//编号汇成数组
	$fromIds = array();
	foreach ($results as $ptr)
		array_push($fromIds, $ptr->{$pkFieldName});
	
	//执行查询相关操作日志资料
	$json1 = count_operaterecords($fromTypes, $fromIds, $opTypes);
	$isQuery = true;
	$results1 = get_results_from_json($json1, $tmpObj1);
	log_info('count_operaterecord for fromIds:'.implode(',', $fromIds).'; result count:'.(empty($results1)?0:count($results1)) );
	
	// 	if (empty($results1))
		// 		return $json;
	
	//遍历并赋值相关字段
	foreach($results as &$ptr) {
		if (!empty($results1)) {
			//提取统计原始记录
			for ($i=0;$i<count($results1);$i++) {
				$ptr1 = $results1[$i];
				if ($ptr->{$pkFieldName}==$ptr1->from_id) {
					if ($completingMode=='prop') { //属性方式
// 						$ptr->su_share_id = $ptr1->share_id;
// 						$ptr->su_from_id = $ptr1->from_id;
// 						$ptr->su_from_type = $ptr1->from_type;
// 						$ptr->su_share_uid = $ptr1->share_uid;
// 						$ptr->su_share_name = $ptr1->share_name;
// 						$ptr->su_share_type = $ptr1->share_type;
// 						$ptr->su_valid_flag = $ptr1->valid_flag;
						break;
					} else if ($completingMode=='list') { //列表方式
						if (!isset($ptr->oprs))
							$ptr->oprs = array();
						$oprs = &$ptr->oprs;

						$opType = $ptr1->op_type;
						if (!isset($oprs[$opType])) {
							$oprs[$opType] = array();
						}
						array_push($oprs[$opType], $ptr1);
					}
				}
			}
		}
		if (!isset($ptr->oprs))
			$ptr->oprs = array();
	
		//进一步统计
		if (!empty($countRules) /*&& !empty($ptr->oprs)*/) {
			if (!isset($ptr->countedOprs))
				$ptr->countedOprs = array();
					
			foreach($countRules as $name=>$countRule) {
				if (is_array($ptr->countedOprs)) {
					if (array_key_exists($name, $ptr->countedOprs))
						$countedOpr = (int)$ptr->countedOprs[$name];
					else
						$countedOpr = 0;
				} else {
					if (property_exists($ptr->countedOprs, $name))
						$countedOpr = (int)$ptr->countedOprs->{$name};
					else
						$countedOpr = 0;					
				}

				if ($countRule==null) { //全部
					foreach ($ptr->oprs as $counts) {
						foreach($counts as $count)
							$countedOpr += $count->c;
					}
				} else { //部分
					foreach ($countRule as $opType) {
						if (array_key_exists($opType, $ptr->oprs)) {
							$counts = $ptr->oprs[$opType];
							foreach($counts as $count)
								$countedOpr += $count->c;
						}
					}
				}

				if (is_array($ptr->countedOprs))
					$ptr->countedOprs[$name] = $countedOpr;
				else
					$ptr->countedOprs->{$name} = $countedOpr;
			}
		}
		
		if ($keepOrignResults==false && isset($ptr->oprs)) {
			unset($ptr->oprs);
		}
	}
}

/**
 * 补全列表记录里相关操作日志的统计数量
 * @param {string} $completingMode 补全方式：list=列表方式，数据将以数组方式填入；prop=属性方式，数据将以属性方式填入(即只填一条补全数据)
 * @param {string|number} $fromType 待补全的文档类型：1=计划，2=任务，3=报告
 * @param {array} $opTypes 操作类型
 * @param {string} $pkFieldName 主键字段名
 * @param {array} $countRules 统计规则，把多个op_type分成几个大分类，然后累加数量；例如：array('type1'=>array(3,4,5), 'type2'=>array(20,21,22,23))
 * @param {boolean} $keepOrignResults 是否保留输出分类统计原始结果
 * @param {string} $json 列表查询结果
 * @param {boolean} $isQuery 输出参数 是否执行过查询
 * @return {JSON字符串} 补全后的列表查询结果
 */
function completing_list_count_of_operaterecords($completingMode='list', $fromType, $opTypes, $pkFieldName, $countRules, $keepOrignResults, $json, &$isQuery) {
	$pkFieldName = !empty($pkFieldName)?$pkFieldName:($fromType==1?'plan_id':($fromType==2?'task_id':'report_id'));
	$isQuery = false;
	
	$results = get_results_from_json($json, $tmpObj); //从json字符串提取列表记录
	if (empty($results))
		return $json;
	
	completing_list_count_of_operaterecords_using_resultsobj($completingMode, array($fromType), $opTypes, $pkFieldName, $countRules, $keepOrignResults, $results, $isQuery);
	
	return json_encode($tmpObj);
}

/**
 * 补全操作日志记录关联的文档(计划、任务、报告)资料
 * @param {string|object} $jsonOrObj 列表查询结果(参数引用方式)
 * @param {string|int} $fromType 1=计划，2=任务，3=报告
 * @param {string} $pkFieldName (可选) 记录主键名
 * @param {boolean} $isQuery 输出参数 是否执行过查询
 */
function completing_list_ptr_info(&$jsonOrObj, $fromType, $pkFieldName=NULL, &$isQuery) {
	if (empty($pkFieldName) && !empty($fromType))
		$pkFieldName = $fromType==1?'plan_id':($fromType==2?'task_id':'report_id');
		
	$isQuery = false;
	
	if (is_string($jsonOrObj)) {
		$results = get_results_from_json($jsonOrObj, $tmpObj); //从json字符串提取列表记录
	} else {
		$results = &$jsonOrObj->results;
	}
	if (empty($results))
		return $jsonOrObj;
	
	//编号汇成数组
	$fromIds = array();
	foreach ($results as $opr) {
		//过滤不符合类型的文档记录
		if (isset($opr->ptr_type) && $opr->ptr_type!=$fromType) {
			continue;
		}
		array_push($fromIds, $opr->from_id);
	}

	if (empty($fromIds)) {
		return $jsonOrObj;
	}
	
	//执行查询文档资料记录
	switch ($fromType) {
		case 1:
			$fieldNames = $fromType.' as ptr_type, plan_id as ptr_id, plan_name as ptr_name';
			$json1 = get_plans($fromIds, null, $fieldNames, false);
			$isQuery = true;
			break;
		case 2:
			
			$isQuery = true;
			break;
		case 3:
			break;
	}
	
	if (!empty($json1)) {
		$results1 = get_results_from_json($json1, $tmpObj1);
		log_info('get_ptrs for fromType:'.$fromType.', fromIds:'.implode(',', $fromIds).'; result count:'.(empty($results1)?0:count($results1)) );
		
		if (empty($results1))
			return $jsonOrObj;
		
		//遍历并赋值相关字段
		foreach($results as &$opr) {
			//过滤不符合类型的文档记录
			if ($opr->from_type!=$fromType)
				continue;
			for ($i=0;$i<count($results1);$i++) {
				$ptr1 = $results1[$i];
				if ($ptr1->ptr_id==$opr->from_id && $ptr1->ptr_type==$opr->from_type) {
					$opr->ptr_name = $ptr1->ptr_name;
				}
			}
			
		}
	}
	
	if (is_string($jsonOrObj))
		return json_encode($tmpObj); //重新生成JSON字符串
	else
		return $jsonOrObj;
}