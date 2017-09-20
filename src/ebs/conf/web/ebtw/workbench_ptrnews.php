<?php
include dirname(__FILE__).'/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/include.php';
$relative_path = '';

	require_once dirname(__FILE__).'/html-script/board-script.php';
	require_once dirname(__FILE__).'/html-script/period-selector-script.php';
	require_once dirname(__FILE__).'/html-script/some-script.php';
	require_once dirname(__FILE__).'/html-script/select-option-script.php';
	require_once dirname(__FILE__).'/html-script/sidepage-tab-script.php';
	require_once dirname(__FILE__).'/html-script/report-script.php';
?>
<div class="col-xs-12 ptrnews-tab-wrap ebtw-no-border">
	<div class="col-xs-12 ptrnews-tab">
		<div class="ptrnews-tab-head" id="ptrnews-tab0"><span>操作日志(<span class="scount">0</span>)</span><span class="ptrnews-tab-select"></span></div>
		<div class="ptrnews-tab-head" id="ptrnews-tab1"><span>评论/回复(<span class="scount">0</span>)</span><span class="ptrnews-tab-select"></span></div>
		<div class="ptrnews-tab-head" id="ptrnews-tab2"><span>计划(<span class="scount">0</span>)</span><span class="ptrnews-tab-select"></span></div>
		<div class="ptrnews-tab-head" id="ptrnews-tab3"><span>任务(<span class="scount">0</span>)</span><span class="ptrnews-tab-select"></span></div>
		<!-- <div class="ptrnews-tab-head" id="ptrnews-tab4"><span>报告(<span class="scount">0</span>)</span><span class="ptrnews-tab-select"></span></div> -->
	</div>
	
	<div id="ptrnews-tab-content" class="col-xs-12 ebtw-embed-row"></div>
</div>
<input type="hidden" id="ptrnews-content-height-input" value="0">
<script type="text/javascript">
var DefaultParameter = {request_query_type:2, count_discuss:1, pageSize:100}; //defaultPageSize
var TabBadgesDatasOfPTRNews = {
	0:{activeNo:0, parameter: $.extend({}, DefaultParameter, {op_type:[3,31,33,34,50,53,60,62,70], ptr_unique:1})},
	1:{activeNo:1, parameter: $.extend({}, DefaultParameter, {op_type:[3], ptr_unique:1/*, count_discuss:0*/})},
	2:{activeNo:2, parameter: $.extend({}, DefaultParameter, {op_type:[34,50,53], from_type:1})},
	3:{activeNo:3, parameter: $.extend({}, DefaultParameter, {op_type:[31,33,34,50,53,60,62], from_type:2})},
	//4:{activeNo:4, parameter: $.extend({}, DefaultParameter, {op_type:[70]})},
};

$contentContainer = $('#ptrnews-tab-content');

$(document).ready(function() {
	//更新tab标签内容数量
	refreshTabBadges('workbench_1', 'ptrnews-tab', [
		TabBadgesDatasOfPTRNews[0],
		TabBadgesDatasOfPTRNews[1],
		TabBadgesDatasOfPTRNews[2],
		TabBadgesDatasOfPTRNews[3],
		//TabBadgesDatasOfPTRNews[4],
	]);
	
	//注册tab标签并设置默认选中
	registerTab(getServerUrl() + "ptrnews_tab_conetnt.php", 'ptrnews', 1, 'small', null, null, function(activeNo) {
		return $.extend({tab_type:activeNo}, TabBadgesDatasOfPTRNews[activeNo].parameter);
	});
	
	//定义函数：计算已占用高度
	function calculateRootHeight() {
		var rootHeight = $('.ptrnews-tab').outerHeight(true)
			+($ptrnewsTabWrap.outerHeight(true)-$ptrnewsTabWrap.height()) //.ptrnews-tab-wrap的border+padding+margin高度
			;
		return rootHeight;
	}
	//计算并保存列表区域最大高度
	$ptrnewsTabWrap = $('.ptrnews-tab-wrap');
	registerCalculateAdjustContainerHeight3($('#ptrnews-content-height-input'), $('#workbench-tab-content'), calculateRootHeight());
});
</script>
