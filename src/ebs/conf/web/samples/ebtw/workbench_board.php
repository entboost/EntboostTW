<?php 
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/include.php';

	require_once dirname(__FILE__).'/html-script/board-script.php';
	require_once dirname(__FILE__).'/html-script/period-selector-script.php';
	require_once dirname(__FILE__).'/html-script/some-script.php';
	require_once dirname(__FILE__).'/html-script/select-option-script.php';
	require_once dirname(__FILE__).'/html-script/sidepage-tab-script.php';
	require_once dirname(__FILE__).'/html-script/report-script.php';
	
	$userId = $_SESSION[USER_ID_NAME];
	$userAccount = $_SESSION[USER_ACCOUNT_NAME];
?>
<div class="col-xs-12 board">
	<div class="row">
		<div class="col-xs-4 board-lane">
			<div class="board-lane-page" id="board-lane1"></div>
		</div>
		<div class="col-xs-4 board-lane">
			<div class="board-lane-page" id="board-lane2"></div>
		</div>
		<div class="col-xs-4 board-lane">
			<div class="board-lane-page" id="board-lane3"></div>
		</div>
	</div>
</div>
<!-- 暂存看板内容区域高度 -->
<input type="hidden" id="board-content-height-input" value="0">
<!-- 暂存当前已被打开ID所在的泳道号 -->
<input type="hidden" id="current_showed_laneno" value="">
<!-- 暂存当前已被打开ID -->
<input type="hidden" id="current_showed_ptrid" value="">

<script type="text/javascript">
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
	//var ptrType = $('#current_showed_ptrid').val();
	if (laneNo)
		eval('load_board_lane'+laneNo+'('+scroll+');'); //执行重新加载本泳道界面的函数
}
//泳道滚动条滚动到底部
// function boardLaneScrollToBottom(laneNo) {
// 	if (parseInt(laneNo)>0)
// 		eval('board_lane_scroll_to_bottom'+laneNo+'();');
// }

//用于临时保存加载泳道时使用的附加参数对象
var loadedLaneParameters = {};
//标识自定义滚动条是否准备好
var customScrollbarsReady = {1:{status:false, count:0}, 2:{status:false, count:0}, 3:{status:false, count:0}};

var userId = '<?php echo $userId;?>'; //当前用户编号
var userAccount = '<?php echo $userAccount;?>'; //当前用户账户

/**
 * 标记已读状态
 * @param ptrType 文档类型
 * @param ptrId 文档编号
 * @param shareId 关联用户表主键
 * @param $markReadElement 标记元素对象(JQuery对象)
 */
function markReadFlag(ptrType, ptrId, shareId, $markReadElement) {
	var loadIndex = layer.load(2);
	markReadFlagToRead(ptrType, ptrId, shareId, function(result){
		if ($markReadElement)
			$markReadElement.children().removeClass('radius-point');
		layer.close(loadIndex);
	}, function(err) {
		layer.close(loadIndex);
	});
}

/**
 * 清除已读状态标记对象
 * @param ptrType 文档类型
 * @param ptrId 文档编号
 * @param shareId 关联用户表主键
 */
function clearMarkReadElement(ptrType, ptrId, shareId) {
	var $markReadElement = $('.board-lane-item[data-ptr-type="'+ptrType+'"][data-ptr-id="'+ptrId+'"] .lane-item-icon.mark-read[data-share-id="'+shareId+'"]');
	$markReadElement.children().removeClass('radius-point');
}

//重新加载泳道某一个子项部分内容
function reloadBoardLaneItem(ptrType, ptrId) {
	if (ptrType==2) { //未完成的任务
		var $boardLane = $('#board-lane3');
		var $boardLaneItem = $boardLane.find('.board-lane-item[data-ptr-id="'+ptrId+'"][data-ptr-type="'+ptrType+'"]');
		if ($boardLaneItem.length) {
			fetchOneTask(ptrId, function(entity) {
				var dictOfProgressHtml = laytpl($('#board-lane-swatches-item-progress-script').html()).render({dictOfProgress:dictOfProgress(ptrType, entity)});
				$boardLaneItem.find('.swatches-item-progress').before(dictOfProgressHtml).remove();
			});
		}
	}
}

$(document).ready(function() {
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
	registerCalculateAdjustContainerHeight3($('#board-content-height-input'), $('#workbench-tab-content').length?$('#workbench-tab-content'):$('.board-page'), calculateRootHeight());
	
	//加载3个泳道
	var pageSize = 300;/*defaultPageSize*/
	//未完成计划
	load_board_lane('workbench', 1, '#board-lane1', '1', {request_query_type:1, request_order_by:'create_time', is_deleted:0, status_uncomplete:1, pageSize:pageSize});
	//评审计划、评阅报告
	load_board_lane('workbench', 2, '#board-lane2', '1_3', {request_query_type:1, request_order_by:'su_create_time', pageSize:pageSize});
	//未完成任务(包括我提交的 我负责的 我参与的)
	load_board_lane('workbench', 3, '#board-lane3', '2', {request_query_type:8, request_order_by:'create_time', status_uncomplete:1, pageSize:pageSize});
});
</script>