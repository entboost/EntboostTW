<?php
//(计划、任务、报告等)用户操作权限定义
$PTR_ACTION_TYPE = array(
	0=>array('dataType'=>0, 'name'=>'查看'),
	1=>array('dataType'=>1, 'name'=>'申请评审/评阅'),
	2=>array('dataType'=>2, 'name'=>'编辑'),
	3=>array('dataType'=>3, 'name'=>'删除'),
	4=>array('dataType'=>4, 'name'=>'评审通过/评阅回复'),
	5=>array('dataType'=>5, 'name'=>'评审拒绝'),
	6=>array('dataType'=>6, 'name'=>'撤销申请'),
	7=>array('dataType'=>7, 'name'=>'重新申请'),
	8=>array('dataType'=>8, 'name'=>'恢复'),
	9=>array('dataType'=>9, 'name'=>'标为完成'),
	10=>array('dataType'=>10, 'name'=>'中止'),
	11=>array('dataType'=>11, 'name'=>'评论/回复'), //待定：未控制
	12=>array('dataType'=>12, 'name'=>'变更共享状态'),
	13=>array('dataType'=>13, 'name'=>'关注/取消关注'), //目前仅对任务有效
	14=>array('dataType'=>14, 'name'=>'修改重要程度'),
		
	21=>array('dataType'=>21, 'name'=>'计划转任务'),
	22=>array('dataType'=>22, 'name'=>'上报进度'),
	23=>array('dataType'=>23, 'name'=>'上报工时'),
	24=>array('dataType'=>24, 'name'=>'拆分子任务'), //未实行功能
	25=>array('dataType'=>25, 'name'=>'选择负责人'),
	26=>array('dataType'=>26, 'name'=>'变更参与人、共享人'),
	27=>array('dataType'=>27, 'name'=>'删除关注人的关系'),
);

//共享类型定义
$PTR_SHARE_TYPE = array(
		0=>new stdClass(), //创建人 (所有)
		1=>new stdClass(), //评审/评阅人 (计划/报告)
		2=>new stdClass(), //参与人 (任务)
		3=>new stdClass(), //共享人 (计划/任务)
		4=>new stdClass(), //关注人 (任务)
		5=>new stdClass(), //负责人	 (任务)
	);

//(计划、任务、报告等)用户数据权限定义
$PTR_DATA_CONTROL = new stdClass();
$PTR_DATA_CONTROL->{1} = new stdClass(); //计划
$PTR_DATA_CONTROL->{2} = new stdClass(); //任务
$PTR_DATA_CONTROL->{3} = new stdClass(); //报告

/**
 * 删除数组内指定值
 * @param {array} $arry 目标数组
 * @param {string} $element_values 将要被删除的值，多个使用逗号分隔
 */
function remove_elements_of_array(&$arry, $element_values) {
	$values = explode(',', $element_values);
	$i=0;
	while($i<count($arry)) {
		$value = $arry[$i];
		foreach ($values as $targetValue) {
			if ($value==$targetValue) {
				array_splice($arry, $i, 1);
				$i--;
				break;
			}
		}
		$i++;
	}
}

//遍历创建权限规则
foreach ($PTR_DATA_CONTROL as $ptrType=>&$ptrConf) {
	if (!isset($ptrConf->shareTypes)) {
		$ptrConf->shareTypes = new stdClass();
	}
	
	$defaultShareTypeConf = array(0,11,12); //默认权限
	if ($ptrType==2) //目前仅任务支持"关注"功能
		$defaultShareTypeConf = array_merge($defaultShareTypeConf, array(13));
	
	$ptrConf->shareTypes->flag = 1;
	switch ($ptrType) {
		case 1: //计划
			$ptrConf->shareTypes->{0} = array_keys($PTR_ACTION_TYPE); //创建人
			remove_elements_of_array($ptrConf->shareTypes->{0}, '4,5,10,22,23,24,25'); //反向定制权限
			
			$ptrConf->shareTypes->{1} = array_merge($defaultShareTypeConf, array(4,5)); //评审人
			$ptrConf->shareTypes->{3} = $defaultShareTypeConf; //共享人
			
			break;
		case 2: //任务
			$ptrConf->shareTypes->{0} = array_merge($defaultShareTypeConf, array(2,3,9,10,14,22,23,24,25,26,27)); //创建人
			$ptrConf->shareTypes->{2} = array_merge($defaultShareTypeConf, array(23)); //参与人
			$ptrConf->shareTypes->{3} = $defaultShareTypeConf; //共享人
			$ptrConf->shareTypes->{4} = $defaultShareTypeConf; //关注人
			$ptrConf->shareTypes->{5} = array_merge($defaultShareTypeConf, array(9,10,14,22,23,24,26,27)); //负责人
			break;
		case 3: //报告
			$ptrConf->shareTypes->{0} = array_merge($defaultShareTypeConf, array(1,2,3)); //创建人
			$ptrConf->shareTypes->{1} = array_merge($defaultShareTypeConf, array(4/*,5*/)); //评阅人
			break;
	}
}

/**
 * 获取权限代码数组
 * @param {string|int} $ptrType 文档类型：1=计划，2=任务，3=报告
 * @param {string|int} $shareType 共享类型：0=创建人，1=评审/评阅人，2=参与人，3=共享人，4=关注人，5=负责人
 * @param {string} $flag 特殊标记 (可选)，可用于识别日报和普通报告
 */
function getPtrDataControlCodes($ptrType, $shareType, $flag=NULL) {
	global $PTR_DATA_CONTROL;
	if (property_exists($PTR_DATA_CONTROL, $ptrType)) {
		$ptr = $PTR_DATA_CONTROL->{$ptrType};
		$shareTypes = $ptr->shareTypes;
		if (property_exists($shareTypes, $shareType)) {
			if((isset($flag) && $shareTypes->flag==$flag) || !isset($flag)) {
// 				log_info('%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%');
// 				log_info($shareTypes->{$shareType});
				return $shareTypes->{$shareType};
			}
		}
	}
}

//log_info(getPtrDataControlCodes(1,1));
