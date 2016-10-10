<?php
include dirname(__FILE__).'/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/include.php';
$relative_path = '';
?>
<!DOCTYPE html>
<html>
<head>
<?php
	require_once dirname(__FILE__).'/html_head_include.php';

	//视图模式
	$boardParamName = 'board';
	$workbenchMode = get_request_param('workbench_mode', $boardParamName); //默认看板模式
	if ('ptrnews'==$workbenchMode) { //ptrnews 最新动态
		echo '<title>最新动态-工作台</title>';
	} else if ('wkfiles'==$workbenchMode) { //wkfiles 文件
		echo '<title>文件-工作台</title>';
	} else { //board 看板
		echo '<title>我的看板-工作台</title>';
	}
?>
</head>
<body>
<div class="container-fluid">
	<div class="row" id="workbench-top"><div class="col-xs-12">&nbsp;</div></div>
	<div class="row" >
		<div class="col-xs-12 workbench-tab-wrap">
			<div class="col-xs-11 workbench-tab">
				<div class="workbench-tab-head" id="workbench-tab0"><span>我的看板</span><span class="workbench-tab-select"></span></div>
				<div class="workbench-tab-head" id="workbench-tab1"><span>最新动态</span><span class="workbench-tab-select"></span></div>
				<div class="workbench-tab-head" id="workbench-tab2"><span>文件</span><span class="workbench-tab-select"></span></div>
			</div>
			<div class="col-xs-1 workbench-toolbar">
			<?php if ($workbenchMode==$boardParamName) {?>
			<div class="action-item dropdown ebtw-color-foreground ebtw-color-foreground-h" title="新建">
			   <span class="glyphicon glyphicon-plus" data-toggle="dropdown"></span>
			   <ul class="dropdown-menu dropdown-menu-right">
			      <li><a tabindex="-1" href="javascript:ptrAddAction(1,null,null,{scroll:1});setCurrentShowedValues(1,'');">新建计划</a></li>
			      <li><a tabindex="-1" href="javascript:ptrAddAction(2);setCurrentShowedValues(3,'');">新建任务</a></li>
			      <li><a tabindex="-1" href="eb-open-subid://1002300113,1">填写日报</a></li>
			   </ul>
			</div>
			<?php } else if ($workbenchMode=='ptrnews') {?>
			<div class="action-item" title="刷新最新动态" onclick="javascript:refreshPage();">
			   <span class="glyphicon glyphicon-refresh"></span>
			</div>
			<?php } else {?>
			<div class="action-item" title="刷新当前页面" onclick="javascript:refreshPage();">
			   <span class="glyphicon glyphicon-refresh"></span>
			</div>	
			<?php }?>
			</div>
			<div id="workbench-tab-content" class="col-xs-12 <?php if ($workbenchMode!=$boardParamName) {?>ebtw-embed-left-row <?php }?>ebtw-embed-right-row ebtw-main-content-bg"></div>
		</div>
	</div>
</div>
<!-- <div style="height:4px;"></div> -->
<div id="sidepage" class="sidepage col-xs-12 ebtw-embed-row resizeMe" style="display: block; right: -750px; width: 700px;"></div>
<input type="hidden" id="workbench-tab-content-height-input" value="0">
<input type="hidden" id="workbench-current-showed-ptr" value="">
<script type="text/javascript">
var globalSubId = '<?php echo $SUB_IDS[$PTRType];?>';
var isWorkbench = true;
var boardParamName = '<?php echo $boardParamName;?>';

//RestAPI访问渠道建立后执行的页面业务流程
function start_page_after_restapi_ready() {
	loadPTRClassAndCreateClassMenu(1, true); //加载计划分类代码对照表
	loadPTRClassAndCreateClassMenu(2, true); //加载任务分类代码对照表
	
	<?php if ($workbenchMode==$boardParamName) {?>
		var workbench_mode = boardParamName;
		var defaultActiveNo = 0;
		var defaultUrl = getServerUrl() + "workbench_board.php";
	<?php } else if ($workbenchMode=='ptrnews') {?>
		var workbench_mode = 'ptrnews';
		var defaultActiveNo = 1;
		var defaultUrl = getServerUrl() + "workbench_ptrnews.php";		
	<?php } else {?>
		var workbench_mode = 'wkfiles';
		var defaultActiveNo = 2;
		var defaultUrl = getServerUrl() + "workbench_wkfiles.php";
	<?php }?>
	//注册tab标签并设置默认选中
	var loadingIconCssClass = 'div-centered-fluid';
	registerTab(defaultUrl, 'workbench', defaultActiveNo, 'large', loadingIconCssClass, {workbench_mode:workbench_mode},
		null,
		function(activeNo, prefix, url, param, loadingIconType) {
			if (defaultActiveNo==activeNo)
				loadTabContent(url, prefix, activeNo, loadingIconType, loadingIconCssClass, param);
			else {
				if (activeNo==0) {
					location.href = replaceUrlParamVal(location.href, 'workbench_mode', boardParamName);
				} else if (activeNo==1) {
					location.href = replaceUrlParamVal(location.href, 'workbench_mode', 'ptrnews');
				} else {
					location.href = replaceUrlParamVal(location.href, 'workbench_mode', 'wkfiles');
				}
			}
		});

	//定义函数：计算已占用高度
	function calculateRootHeight() {
		var rootHeight = $('#workbench-top').outerHeight(true) + $('.workbench-tab').outerHeight(true)
			+($workbenchTabWrap.outerHeight(true)-$workbenchTabWrap.height()) //.workbench-tab-wrap的border+padding+margin高度
			+1; //.workbench-tab-content margin-top=1
		return rootHeight;
	}
	//计算并保存列表区域最大高度
	$workbenchTabWrap = $('.workbench-tab-wrap');
	registerCalculateAdjustContainerHeight2($('#workbench-tab-content-height-input'), calculateRootHeight(), function(height){
		$('#workbench-tab-content').height(height);
	});

	//注册事件-点击空白位置关闭右侧页
	$('#workbench-tab-content').click(function(e) {
        if(parseInt($('#sidepage').css('right'))>=0)
        	closeSidepage();
 //       stopPropagation(e); 不可以阻止事件传递，否则bootstrap插件(例如dropdown)不正常
	});

	//注册点击计划、任务等链接的处理函数
	registerAssociateRedirect();
	//注册事件-点击“用户名称”发起聊天会话
	registerTalkToPerson(true);
	//注册事件-打开附件文件
	registerOpenResource();
}

$(document).ready(function() {
	logjs_info('workbench_i pageStarted='+pageStarted+', restApiReady='+restApiReady);
	
	//检测并执行页面业务流程
	if (!pageStarted && restApiReady) {
		logjs_info('workbench_i page ready...');
		pageStarted = true;
		start_page_after_restapi_ready();
	}
});
</script>
</body>
</html>