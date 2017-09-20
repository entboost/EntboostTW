<?php 
//$ECHO_MODE = 'html'; //输出类型
//require_once dirname(__FILE__).'/include.php';
	
// 	$userId = $_SESSION[USER_ID_NAME];
	$userAccount = $_SESSION[USER_ACCOUNT_NAME];
?>
<div class="ptr-container">
	<div id="contentList" class="col-xs-12 ebtw-right-gutter-no">
		<div class="col-xs-12 board">
			<div class="row">
				<div class="col-xs-4 board-lane" id="board-lane-wrap1">
					<!-- <div class="board-lane-page board-lane-page2 mCustomScrollbar-type2" data-mcs-theme="dark-3" id="board-lane1"></div> -->
				</div>
				<div class="col-xs-4 board-lane" id="board-lane-wrap2">
					<!-- <div class="board-lane-page board-lane-page2 mCustomScrollbar-type2" data-mcs-theme="dark-3" id="board-lane2"></div> -->
				</div>
				<div class="col-xs-4 board-lane" id="board-lane-wrap3">
					<!-- <div class="board-lane-page board-lane-page2 mCustomScrollbar-type2" data-mcs-theme="dark-3" id="board-lane3"></div> -->
				</div>
			</div>
		</div>
	</div>
</div>
<!-- 暂存看板内容区域高度 -->
<input type="hidden" id="board-content-height-input" value="0">
<!-- 暂存当前已被打开ID所在的泳道号 -->
<input type="hidden" id="current_showed_laneno" value="">
<!-- 暂存当前已被打开ID -->
<input type="hidden" id="current_showed_ptrid" value="">

<script type="text/javascript" src="../js/special-extend.js"></script>
<script type="text/javascript">
var parentType = 'ptr';
var relativePath = 'attendance/';
var boardLanePrefix = '#board-lane';

//定义函数：计算已占用高度
function calculateRootHeight() {
	var rootHeight = 0 //$boardLaneToolbar.outerHeight(true)+$boardLaneControl.outerHeight(true)
		//+($boardLaneListContainer.outerHeight(true)-$boardLaneListContainer.height()) //$boardLaneListContainer的border+padding+margin高度
		+5; //给最底部留空隙
	return rootHeight;
}
//定义函数：设置列表区域最大高度
function adjustboardLaneContainerHeight($boardLaneContainer, resizeEventSuffixName) {
	adjustContainerHeight3UsingE($('#board-content-height-input'), $boardLaneContainer, calculateRootHeight(), false, resizeEventSuffixName
			, function(height, $container){
			//调整滚动框最大高度
			$container.children('.mCustomScrollBox').css('max-height', height);
		});
}

//暂存文档编号和泳道号
function setCurrentShowedValues(laneNo, ptrId) {
	$('#current_showed_laneno').val(laneNo);
	$('#current_showed_ptrid').val(ptrId);
}
//清空已暂存的文档编号和泳道号
function resetCurrentShowedValues() {
	$('#current_showed_laneno').val('');
	$('#current_showed_ptrid').val('');
}
//重载当前泳道
function reloadCurrentBoardLane(scroll) {
	var laneNo = $('#current_showed_laneno').val();
	var ptrType = $('#current_showed_ptrid').val();
	if (laneNo)
		eval('load_board_laneX(' + laneNo + ',' + ptrType + ',' + scroll + ');'); //执行重新加载本泳道界面的函数
}

//加载泳道界面
function load_board_laneX(laneNo, recState, scroll) {
	var paramObj = loadedLaneParameters[laneNo][recState];
	if (paramObj) {
		executeAttendanceLoadBoardSubLane(paramObj.parentType, paramObj.relativePath, paramObj.laneNo, paramObj.laneSelector, paramObj.ptrType
				, paramObj.param.exclude_normal_rec_state, paramObj.param.req_type, paramObj.param.attend_date_start, paramObj.param.attend_date_end, paramObj.title
				, function(laneNo, recState, laneSelector) {
				adjustboardLaneContainerHeight($(boardLanePrefix + laneNo), laneNo+'.'+recState);
				/*
				if (scroll==laneNo)
					eval('board_lane_scroll_to_bottom'+laneNo+'();');
				*/
			});
	}
}

//执行加载数据和渲染内容视图
function executeRenderContent() {
	var param = createQueryParameter();
	
	//加载3个泳道
	for (var laneNo=1; laneNo<=3; laneNo++) {
		$('#board-lane-wrap'+laneNo).html(laytpl($('#board-lane-script2').html()).render({laneNo:laneNo}));
		load_board_lane(parentType, relativePath, laneNo, boardLanePrefix+laneNo, laneNo, param/*{request_query_type:1, request_order_by:'create_time', pageSize:pageSize}*/);
	}
}

//用于临时保存加载泳道时使用的附加参数对象
var loadedLaneParameters = {};
//标识自定义滚动条是否准备好
var customScrollbarsReady = {1:{status:false, count:0}, 2:{status:false, count:0}, 3:{status:false, count:0}};

var userId = '<?php echo $userId;?>'; //当前用户编号
var userAccount = '<?php echo $userAccount;?>'; //当前用户账户

//ready事件后执行的函数
function runAfterReady(pagefirstRun) {
	//定义函数：计算已占用高度
	function calculateRootHeight() {
		var rootHeight = $board.outerHeight(true)-$board.height() //.board的border+padding+margin高度
			+$boardLane.outerHeight(true)-$boardLane.height()
			+$boardLanePage.outerHeight(true)-$boardLanePage.height()
			;
		return rootHeight;
	}
	
	//计算并保存列表区域最大高度
	$board = $('.board');
	$boardLane = $('.board-lane');
	$boardLanePage = $('.board-lane-page');
	
	if (pagefirstRun) {
		registerCalculateAdjustContainerHeight3($('#board-content-height-input'), $('.ptr-container'), calculateRootHeight());
		//executeRenderContent(); //执行加载数据和渲染内容视图
		//页面默认以'月'作为查询跨度，并勾选有效
		$('#period-type-switch-3').trigger('click');
		
	    //注册点击事件-新建申请
	    $("#btn_AddAttendApproval").click(function (e) {
	    	var recId = 0;
			openAttendanceReq(recId, null, null, function() {
				
	        });
	    	stopPropagation(e);
	    });
		
		$wrapContainer = $('#board-lane-wrap1, #board-lane-wrap2, #board-lane-wrap3');
	  	//注册事件-点击查看考勤审批申请情况
	    $wrapContainer.on('click', '.board-lane-list-container .board-lane-item:not(.board-lane-item-empty)', function (e) {
	        var recId = $(this).attr('data-rec-id');
	        var recStateClass = $(this).parents('.board-lane-list[data-recstate-class]').attr('data-recstate-class');
	    	openAttendanceReq(recId, null, (recStateClass==64)?4:null, function() {});
			
			stopPropagation(e);
	    });
		
	    //注册事件-子菜单
	    $wrapContainer.on('click', '.board-lane-list-container .board-lane-list .sub-menus li', function(e) {
			var $laneItemElement =$(this).parents('.board-lane-item');
			var type = parseInt($(this).attr('data-type'));
			var recId = $laneItemElement.attr('data-rec-id');
			var recStateClass = $(this).parents('.board-lane-list[data-recstate-class]').attr('data-recstate-class');
			
			//审批申请、撤销、重新申请
			if (type==1 || type==6 || type==7)
				openAttendanceReq(recId, null, (recStateClass==64)?4:null, function() {});
			
	    	stopPropagation(e);
	    });
	}
}

$(document).ready(function() {

});

</script>