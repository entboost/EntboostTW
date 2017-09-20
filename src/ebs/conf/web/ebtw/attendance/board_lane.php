<?php 
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/include.php';

$parentType = get_request_param('reserved_parent_type');
$laneNo = get_request_param('reserved_lane_no');
$ptrType = get_request_param('reserved_ptr_type');
if (!isset($parentType) || !isset($laneNo) || !isset($ptrType)) {
	echo '<div class="col-xs-12">错误：缺少访问参数</div>';
	return;
}

//考勤日期范围开始
$attendDateStart = get_request_param("search_time_s", '');
if (!empty($attendDateStart) && strlen($attendDateStart)>=10)
	$attendDateStart = substr($attendDateStart, 0, 10);
//考勤日期范围结束
$attendDateEnd = get_request_param("search_time_e", '');
if (!empty($attendDateEnd) && strlen($attendDateEnd)>=10)
	$attendDateEnd = substr($attendDateEnd, 0, 10);

$laneNo = intval($laneNo);
//$offsetLaneNo = 2*($laneNo-1);
$subNo = $laneNo + 2*($laneNo-1);

//分析查询条件并执行查询数据
if ($parentType=='ptr') {
?>
	<!-- 头部 -->
	<div class="form-inline board-lane-toolbar"></div>
	<!-- 记录 -->
	<div class="board-lane-list-container">
		<div class="board-lane-list" id="board-lane-sub<?php echo $subNo;?>"></div>
	</div>
	<!-- 尾部 -->
	<div class="board-lane-tail"></div>
	
	<!-- 头部 -->
	<div class="form-inline board-lane-toolbar"></div>
	<!-- 记录 -->
	<div class="board-lane-list-container">
		<div class="board-lane-list" id="board-lane-sub<?php echo $subNo + 1;?>"></div>
	</div>
	<!-- 尾部 -->
	<div class="board-lane-tail"></div>
	
	<?php if ($laneNo<3) {?>
	<!-- 头部 -->
	<div class="form-inline board-lane-toolbar"></div>
	<!-- 记录 -->
	<div class="board-lane-list-container">
		<div class="board-lane-list" id="board-lane-sub<?php echo $subNo + 2;?>"></div>
	</div>
	<!-- 尾部 -->
	<div class="board-lane-tail"></div>
	<?php }?>
	
<script type="text/javascript">
//页面加载完毕时执行
$(document).ready(function() {
	var parentType = '<?php echo $parentType;?>';
	var laneNo = <?php echo $laneNo;?>;
	var subNo = <?php echo $subNo;?>;
	var boardLaneSub = '#board-lane-sub';

	var attendDateStart = '<?php echo $attendDateStart;?>';
	var attendDateEnd = '<?php echo $attendDateEnd;?>';
	$boardLaneContainer = $(boardLanePrefix + laneNo);
<?php
	switch ($laneNo) {
		case 1: //第一列：未签到、未签退、旷工
			?>
			executeAttendanceLoadBoardSubLane(parentType, relativePath, laneNo, boardLaneSub+subNo, <?php echo ATTEND_STATE_UNSIGNIN;?>
				, 1, null, attendDateStart, attendDateEnd, '未签到', function(laneNo, recState, laneSelector) {
				adjustboardLaneContainerHeight($(boardLanePrefix + laneNo), laneNo+'.'+recState);
			});
			executeAttendanceLoadBoardSubLane(parentType, relativePath, laneNo, boardLaneSub+(subNo+1), <?php echo ATTEND_STATE_UNSIGNOUT;?>
				, 1, null, attendDateStart, attendDateEnd, '未签退', function(laneNo, recState, laneSelector) {
				adjustboardLaneContainerHeight($(boardLanePrefix + laneNo), laneNo+'.'+recState);
			});
			executeAttendanceLoadBoardSubLane(parentType, relativePath, laneNo, boardLaneSub+(subNo+2), <?php echo ATTEND_STATE_ABSENTEEISM;?>
				, 1, null, attendDateStart, attendDateEnd, '旷工', function(laneNo, recState, laneSelector) {
				adjustboardLaneContainerHeight($(boardLanePrefix + laneNo), laneNo+'.'+recState);
			});
			<?php
			break;
		case 2: //第二列：迟到、早退、加班
			?>
			executeAttendanceLoadBoardSubLane(parentType, relativePath, laneNo, boardLaneSub+subNo, <?php echo ATTEND_STATE_LATE;?>
				, 1, null, attendDateStart, attendDateEnd, '迟到', function(laneNo, recState, laneSelector) {
				adjustboardLaneContainerHeight($(boardLanePrefix + laneNo), laneNo+'.'+recState);
			});
			executeAttendanceLoadBoardSubLane(parentType, relativePath, laneNo, boardLaneSub+(subNo+1), <?php echo ATTEND_STATE_LEFT_EARLY;?>
				, 1, null, attendDateStart, attendDateEnd, '早退', function(laneNo, recState, laneSelector) {
				adjustboardLaneContainerHeight($(boardLanePrefix + laneNo), laneNo+'.'+recState);
			});
			executeAttendanceLoadBoardSubLane(parentType, relativePath, laneNo, boardLaneSub+(subNo+2), <?php echo ATTEND_STATE_WORK_OVERTIME;?>
				, 0, null, attendDateStart, attendDateEnd, '加班', function(laneNo, recState, laneSelector) {
				adjustboardLaneContainerHeight($(boardLanePrefix + laneNo), laneNo+'.'+recState);
			});
			<?php
			break;
		case 3: //第三列：外勤、请假
			?>
			executeAttendanceLoadBoardSubLane(parentType, relativePath, laneNo, boardLaneSub+subNo, <?php echo ATTEND_STATE_WORK_OUTSIDE;?>
				, 0, null, attendDateStart, attendDateEnd, '外勤', function(laneNo, recState, laneSelector) {
				adjustboardLaneContainerHeight($(boardLanePrefix + laneNo), laneNo+'.'+recState);
			});
			executeAttendanceLoadBoardSubLane(parentType, relativePath, laneNo, boardLaneSub+(subNo+1), <?php echo ATTEND_STATE_FURLOUGH;?>
				, 0, null, attendDateStart, attendDateEnd, '请假', function(laneNo, recState, laneSelector) {
				adjustboardLaneContainerHeight($(boardLanePrefix + laneNo), laneNo+'.'+recState);
			});
			<?php
			break;
	}
}
?>
	//=======设置高度相关=========
	//执行设置列表区域最大高度
	if ($('#board-content-height-input').length>0) {
		adjustboardLaneContainerHeight($boardLaneContainer, laneNo);
	}
	//=======设置高度相关 end =========
	//自定义滚动条
	customScrollbarUsingE($boardLaneContainer, 30, true, 'outside', {callbacks:{
		onScroll: function() {
			//logjs_info('release <?php echo $laneNo;?>');
		},
		onScrollStart: function() {
			//logjs_info('start <?php echo $laneNo;?>');
		},
		onInit: function(){
			//logjs_info('init <?php echo $laneNo;?>');
			//设置自定义滚动条初始化完毕状态
			customScrollbarsReady[<?php echo $laneNo;?>].status = true;
		}}
	});
});
</script>