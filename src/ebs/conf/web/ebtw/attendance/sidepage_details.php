<?php
include dirname(__FILE__).'/../attendance/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../attendance/include.php';
require_once dirname(__FILE__).'/../attendance/attendance_functions.php';

	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
	$entCode = $_SESSION[USER_ENTERPRISE_CODE]; //企业编号
	
	//子类型
	$subType = intval(get_request_param('sub_type', '0'));
	if (!in_array($subType, array(1,2,3,4,5,6,7,8))) {
		log_err("sub_type $subType is not matched");
		return;
	}
	
	//用户编号、用户名
	$targetUserId = get_request_param('user_id', '0');
	$targetUserName = get_request_param('user_name', '');
	if (empty($targetUserId)) {
		log_err("user_id $targetUserId is invalid");
		return;
	}
	
	//时间范围
	$searchTimeS = get_request_param('search_time_s');
	$searchTimeE = get_request_param('search_time_e');
	//简要
	$brief = get_request_param('content', '');
	
	//查询参数
	$recState = null;
	$recStateCalculaMode = null;
	$checkDuration = false;
	
	//标题
	$title = $targetUserName.'：'.($searchTimeS?substr($searchTimeS, 0, 10):'-').' 至 '.($searchTimeE?substr($searchTimeE, 0, 10):'-').' ';
	switch ($subType) {
		case 1:
			$title .= "实出勤";
			$checkDuration = true;
			break;
		case 2:
		case 7:
			$title .= "加班";
			$recState = ATTEND_STATE_WORK_OVERTIME;
			$recStateCalculaMode = 1;
			break;
		case 3:
		case 6:
			$title .= "请假";
			$recState = ATTEND_STATE_FURLOUGH;
			$recStateCalculaMode = 1;
			break;
		case 4:
			$title .= "异常考勤";
			$recState = ATTENDANCE_STATE_ABNORMAL_GROUP;
			$recStateCalculaMode = 2;
			break;
		case 5:
			$title .= "外勤";
			$recState = ATTEND_STATE_WORK_OUTSIDE;
			$recStateCalculaMode = 1;
			break;
		case 8:
			$title .= "考勤";
			break;
	}
	$title .= '明细 '.$brief;
	
	$recInstance = AttendRecordService::get_instance();
	$reqInstance = AttendReqService::get_instance();
	
	if (in_array($subType, array(3,6))) { //请假
		$reqStatus = 2; //审批通过
		$reqType = 3; //请假申请
		$reqResults = $reqInstance->getAttendReqs($entCode, array(), $targetUserId, $reqStatus, $reqType, $searchTimeS, $searchTimeE);
		if ($reqResults===false) {
			log_err('getAttendReqs error');
			return;
		}
	} else { //其它
		//查询考勤记录
		$recResults = $recInstance->getAttendRecord7($entCode, null, $targetUserId, $checkDuration, $recState, $recStateCalculaMode, $searchTimeS, $searchTimeE);
		if ($recResults===false) {
			log_err('getAttendRecord7 error');
			return;
		}
		
		//封装'考勤审批申请编号'和'考勤记录编号'查询参数
		$reqIAndRecIds = array();
		foreach ($recResults as $recEntity) {
			if (!empty($recEntity['max_att_req_id']) && !empty($recEntity['req_att_rec_id']))
				array_push($reqIAndRecIds, array('att_req_id'=>$recEntity['max_att_req_id'], 'att_rec_id'=>$recEntity['req_att_rec_id']));
		}
		//查询关联的考勤审批申请记录
		if (!empty($reqIAndRecIds)) {
			$reqResults = $reqInstance->getAttendReqsByReqIAndRecIds($entCode, array(), $targetUserId, $reqIAndRecIds, 2);
			if ($reqResults===false) {
				log_err('getAttendReqsByReqIAndRecIds error');
				return;
			}
			
			//遍历匹配关联的考勤审批申请记录
			foreach ($recResults as &$mrecEntity) {
				foreach ($reqResults as $reqEntity) {
					if ($mrecEntity['max_att_req_id']===$reqEntity['att_req_id'] && $mrecEntity['req_att_rec_id']===$reqEntity['att_rec_id']) {
						$mrecEntity['attend_req'] = $reqEntity;
						break;
					}
				}
			}
		}
		log_info($recResults);
	}
?>
<div class="side-toolbar col-xs-12">
<div class="side-toolbar-icon">
<span class="glyphicon glyphicon-eye-open"></span>
<span class="action-title"><?php echo $title;?></span>
    	<span class="action-title3"></span>
    </div>
    <div class="side-close">
        <span class="glyphicon glyphicon-remove" title="关闭"></span>
    </div>
    <div class="side-fullscreen" data-type="0">
        <span class="glyphicon glyphicon-fullscreen" title="全屏"></span>
    </div>
</div>

<div class="col-xs-12 ebtw-horizontal-nopadding-right sidepage-property-container mCustomScrollbar" data-mcs-theme="dark-3">
	<div style="padding: 15px 15px;">
	<?php 
		if (in_array($subType, array(1,4,8))) //实出勤、异常考勤、考勤明细
			include dirname(__FILE__).'/../attendance/sidepage_details_table.php';
		else if ($subType==5) //外勤
			include dirname(__FILE__).'/../attendance/sidepage_details_table2.php';
		else if (in_array($subType, array(2,7)))//加班
			include dirname(__FILE__).'/../attendance/sidepage_details_table3.php';
		else if (in_array($subType, array(3,6)))//请假
			include dirname(__FILE__).'/../attendance/sidepage_details_table4.php';
	?>
	</div>
</div>

<input type="hidden" id="property-content-height-input" value="0">
<script type="text/javascript">
$(document).ready(function() {
	var $propertyContainer = $('.sidepage-property-container');

	//属性容器自适应高度
	function calculatePropertyContentHeight() {
		$('#property-content-height-input').val($('body').outerHeight()-$('.side-toolbar').outerHeight()-10);
	}
	$(window).bind('resize', function(e) {
		calculatePropertyContentHeight();
	});
	calculatePropertyContentHeight();
	adjustContainerHeight3UsingE($('#property-content-height-input'), $propertyContainer, 10, true, 'resize_property_container');
   	
	//自定义滚动条
	customScrollbarUsingE($propertyContainer, 30, true);
	
});
</script>