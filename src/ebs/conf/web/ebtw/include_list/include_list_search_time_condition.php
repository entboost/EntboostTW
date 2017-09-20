<?php
if (!empty($formObj->search_time_s) && !empty($formObj->search_time_e)) { //转换为范围条件
	AbstractService::removeWhereCondition($wheres, 'search_time_s');
	AbstractService::removeWhereCondition($wheres, 'search_time_e');
	array_push($wheres, new SQLParamComb(array(
			new SQLParamComb(array('start_time'=>array(new SQLParam($formObj->search_time_s, 'start_time', SQLParam::$OP_GT_EQ), new SQLParam($formObj->search_time_e, 'start_time', SQLParam::$OP_LT_EQ))), SQLParamComb::$TYPE_AND),
			new SQLParamComb(array('stop_time'=>array(new SQLParam($formObj->search_time_s, 'stop_time', SQLParam::$OP_GT_EQ), new SQLParam($formObj->search_time_e, 'stop_time', SQLParam::$OP_LT_EQ))), SQLParamComb::$TYPE_AND),
			new SQLParamComb(array('start_time'=>new SQLParam($formObj->search_time_s, 'start_time', SQLParam::$OP_LT_EQ), 'stop_time'=>new SQLParam($formObj->search_time_e, 'stop_time', SQLParam::$OP_GT_EQ)), SQLParamComb::$TYPE_AND),
			new SQLParamComb(array('create_time'=>array(new SQLParam($formObj->search_time_s, 'create_time', SQLParam::$OP_GT_EQ), new SQLParam($formObj->search_time_e, 'create_time', SQLParam::$OP_LT_EQ))), SQLParamComb::$TYPE_AND)
	), SQLParamComb::$TYPE_OR));
}