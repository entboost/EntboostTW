<?php
include dirname(__FILE__).'/../report/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../report/include.php';
$relative_path = '../';
?>
<!DOCTYPE html>
<html>
<head>
<title>日报</title>
<?php
	require_once dirname(__FILE__).'/../html_head_include.php';
	
	//请求业务类型
	$query_type = get_request_param(REQUEST_QUERY_TYPE);
	if (empty($query_type))
		$query_type = 1;	
	
	//视图模式
	$ListMode = true; //列表
	if ('calendar'==@$_REQUEST['view_mode']) //日历
		$ListMode = false;
	
	//自动打开指定日报详情页面
	$accessTempKey = get_request_param('access_temp_key');
	
	require_once dirname(__FILE__).'/../html-script/period-selector-script.php';
	require_once dirname(__FILE__).'/../html-script/some-script.php';
	require_once dirname(__FILE__).'/../html-script/select-option-script.php';
	require_once dirname(__FILE__).'/../html-script/sidepage-tab-script.php';
	require_once dirname(__FILE__).'/../html-script/report-script.php';
?>
</head>
<body style="overflow-y:hidden;">
<div class="container-fluid">
   <div class="row" id="ptr-top"><div class="col-xs-12">&nbsp;</div></div>

	<!-- 顶上菜单 -->
	<div class="row" >
		<div class="col-xs-2">&nbsp;</div>
		<div class="col-xs-8">
		 	<div class="form-inline">
	      		<div class="input-group">
	      		  <!-- <input type="hidden" id="search-content-invalid" value=""> --><!-- 标识是否使用输入的搜索关键字，当value=1时不使用 -->
				  <input type="text" style="" class="form-control ebtw-menu-input ebtw-menu-input-zindex" id="report_work" name="report_work" placeholder="工作内容">
				  <span class="input-group-btn">
				  <button type="button" id="search" class="form-control btn btn-default ebtw-menu-input btn-search" title="查询"><span class="glyphicon glyphicon-search"></span></button>
				  </span>
				</div>
				&nbsp;&nbsp;
				<div class="input-group">
					<div class="search-content-bar">
						&nbsp;'<span class="search-keyword"></span>'<!-- 关键词 -->
						&nbsp;<span class="no-result">搜索没有记录</span>
						&nbsp;<span class="has-result">搜索到 <span class="result-count">N</span> 个记录</span>
					</div>
				</div>
      	 	</div>
		</div>
		
		<div class="col-xs-2">
			<div class="form-inline">
				<div class="input-group pull-right ebtw-right-gutter">
					<span class="input-group-btn">
					  <button type="button" id="go_calendar" class="form-control btn btn-default ebtw-menu-input <?php if(!$ListMode){ ?>active<?php } ?>" title="日历"><span class="glyphicon glyphicon-calendar"></span></button>
					</span>
					<span class="input-group-btn">
					  <button type="button" id="go_list" class="form-control btn btn-default ebtw-menu-input <?php if($ListMode){ ?>active<?php } ?>" title="列表"><span class="glyphicon glyphicon-list"></span></button>
					</span>
					<span class="input-group-btn">
					  <button type="button" class="form-control btn btn-default ebtw-menu-input" title="刷新" onclick="javascript:refreshPage();"><span class="glyphicon glyphicon-refresh"></span></button>
					</span>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row ebtw-main-content-bg">
		<div class="col-xs-12 ebtw-list-main ebtw-vertical-nopadding ebtw-horizontal-nopadding" >
			<div class="row">
				<input type="hidden" id="current_url" value="" data-container=""> <!-- not for dtGrid -->
				<input type="hidden" id="current_left_menu" value="1_<?php echo $query_type;?>">
				<!-- 左侧菜单 -->
				<div class="col-xs-2 ebtw-menu-pull-1 ebtw-horizontal-nopadding-right ebtw-menu-item" id="content-left-side" onselectstart="javascript:return false;" style="-moz-user-select:none;">
					<div class="row" >
						<div class="col-xs-12">
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==1) echo 'active';?> query_type" type="1"><span class="item-name">我的日报</span><span class="ebtw-badge badge_info" title="本月已填日报数量"></span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==2) echo 'active';?> query_type" type="2"><span class="item-name">评阅日报</span><span class="ebtw-badge" title="需要评阅回复日报数量"></span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==3) echo 'active';?> query_type" type="3"><span class="item-name">下级日报</span><span class="ebtw-badge"></span></a>
						</div>
					</div>
				</div>
				
				<!-- 正文内容 -->
				<div class="col-xs-10" id="content-right-side">
				<?php
				if ($ListMode) {
				?>
					<!-- 正文工具栏 -->
					<div class="row" onselectstart="return false" style="-moz-user-select:none;">
						<!-- 左上按钮 -->
						<div class="col-xs-6 col-md-4">
							<div class="form-inline ebtw-menu-pull-1">
								<div class="input-group ebtw-menu-pull-top">
									<span class="input-group-btn">
									  <button type="button" id="period-type-switch-1" class="form-control btn btn-default ebtw-menu-input period-type-switch" title="日">日</button>
									</span>
									<span class="input-group-btn">
									  <button type="button" id="period-type-switch-2" class="form-control btn btn-default ebtw-menu-input period-type-switch" title="周">周</button>
									</span>
									<span class="input-group-btn">
									  <button type="button" id="period-type-switch-3" class="form-control btn btn-default ebtw-menu-input period-type-switch" title="月">月</button>
									</span>
								</div>
								<div class="input-group">&nbsp;</div>
								<div class="input-group">
									<span class="input-group-btn">
									  <button type="button" id="btn-today" class="form-control btn btn-default" title="今天计划">今天</button>
									</span>
								</div>							
							</div>
						</div>
						
						<!-- 中间日期选择工具条 -->
						<div class="col-md-5">
							<div class="form-inline">
								<div class="input-group ebtw-menu-input-height-lg ebtw-unselect-color" id="date-period">
									<input type="hidden" id="period-selector-type" value="2">
									<div class="input-group-btn">
										<span class="ebtw-btn ebtw-menu-input ebtw-no-border btn-check date-period-ctrl" title="点击日期范围有效"><span class="glyphicon glyphicon-unchecked"></span></span>
									</div>
									<div class="input-group-btn">
									  <span class="ebtw-btn ebtw-menu-input ebtw-no-border btn-left period-prev" title="上一周期"><span class="glyphicon glyphicon-chevron-left"></span></span>
									</div>
									 <!--预留位置-->
									<span class="input-group-btn">
									  <span class="ebtw-btn ebtw-menu-input ebtw-no-border btn-right period-next" title="下一周期"><span class="glyphicon glyphicon-chevron-right"></span></span>
									</span>
								</div>
							</div>
						</div>
						
					</div>
					
					<!-- 正文列表 -->
					<div class="row">
						<div class="ptr-container mCustomScrollbar" data-mcs-theme="minimal-dark"><!-- dark-3 -->
							<!-- <div id="gridList" class="col-xs-12 dt-grid-container ebtw-right-gutter-no"></div> -->
							<div class="col-xs-12 report-list-page-container" id="report-list"></div>
						</div>
					</div>
					<?php
					} else {
					?>
					<div class="row">
						<div class="col-xs-12 report-calendar-page" id="report-calendar"></div>
					</div>
					<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
	
</div>
<div id="sidepage" class="sidepage col-xs-12 ebtw-embed-row resizeMe" style="display: block; right: -750px; width: 700px;"></div>
<div id="btn_gotop" class="btn_gotop" title="返回顶端" style="display: none;"><span class="glyphicon glyphicon-arrow-up"></span></div>

<input type="hidden" id="ptr-content-height-input" value="0">

<script type="text/javascript">
var globalSubId = '<?php echo $SUB_IDS[$PTRType];?>';
<?php 
if (!empty($accessTempKey)) echo "var accessTempKey = \"$accessTempKey\";";
?>
//var dictImportant ={}; //重要程度
//var dictStatusOfPlan = {}; //状态
//var dictStatusColorOfPlan = {}; //状态对照颜色
//var dictPeriodOfPlan = {}; //周期
//var dictClassNameOfPlan = {0:'-'}; //计划分类
//var dictClassNameOfTask = {0:'-'}; //任务分类
var dictStatusOfReport = {0:'默认', 1:'提交评阅未阅', 2:'提交评阅已阅', 3:'评阅已回复'}; //报告状态
var dictPeriodOfReport = {1:'日报', 2:'周报', 3:'月报', 4:'季报', 5:'年报'}; //报告周期
var dictClassNameOfReport = {0:'-'}; //报告分类

//主键字段名
var ID_NAME = 'report_id';
var PTR_TYPE = <?php echo $PTRType;?>;

//创建查询参数
function createQueryParameter() {
	var parameter = createUsualQueryParameter();
	
	//工作内容
	var searchContent = $('#report_work').val();
	if (searchContent!=undefined && $.trim(searchContent).length>0) {
		parameter.report_work_lk = $.trim(searchContent);
	}
	
	return parameter;
}

//清空查询输入框内容
function resetSearchContent() {
	$('#report_work').val('');
}

//RestAPI访问渠道建立后执行的页面业务流程
function start_page_after_restapi_ready() {
//	//加载报告分类代码对照表(日报没有分类)
//	loadPTRClassAndCreateClassMenu(PTR_TYPE, true);
	//加载计划分类代码对照表
	loadPTRClassAndCreateClassMenu(1, true);
	//加载任务分类代码对照表
	loadPTRClassAndCreateClassMenu(2, true);
		
	//注册左侧菜单事件
	registerLeftMenu(PTR_TYPE, function() {
		var params = $('#current_left_menu').val().split('_');
		var queryType = params[1];
		if (params[0]==1 && parseInt(queryType)<=2) {
			refreshPTRMenuBadges([queryType], PTR_TYPE, 'daily');
		}
	});
	//刷新菜单角标(badge)
	refreshPTRMenuBadges([1,2], PTR_TYPE, 'daily');
	
	$ebtwListMain = $('.ebtw-list-main');
	//定义函数：计算已占用高度
	function calculateRootHeight() {
		var rootHeight = $('#ptr-top').outerHeight(true) + $('#ptr-menu-top').outerHeight(true) + $('#content-menu').outerHeight(true)
			+($ebtwListMain.outerHeight(true)-$ebtwListMain.height())
			+1;
		return rootHeight;
	}

<?php
if ($ListMode) {
?>  
	var url = getServerUrl() + "report/daily_list_embed.php?daily=1&request_query_type=1&request_order_by=start_time desc&pageSize="+defaultPageSize;
	$('#current_url').val(url).attr('data-container', '#report-list');
	
	$('#search').click(function() {
    	//设置使用搜索输入框内容
    	//$('#search-content-invalid').val('');
    	
		$(this).blur();
		executeQuery();
		//loadEmbedPage($('#current_url').attr("data-container"), $('#current_url').val(), createQueryParameter());
	});

	//日期范围工具栏
	implementPeriodSelector();
	//注册事件-"今天"按钮
    registerTodaySelector();
	
	//注册事件-勾选日期查询范围控件
	registerDatePeriodCtrl();
	
	//计算并保存列表区域最大高度
	$ptrContainer = $('.ptr-container');
	registerCalculateAdjustContainerHeight2($('#ptr-content-height-input'), (calculateRootHeight()+($ptrContainer.outerHeight(true)-$ptrContainer.height())+50), function(height){
		$ptrContainer.height(height);
		//设置内容区域高度左侧与右侧相等
		$('#content-left-side').height($('#content-right-side').height());
	});

	//自定义滚动条
	customScrollbarUsingE($ptrContainer, 30, true);
	
	//loadEmbedPage($('#current_url').attr("data-container"), $('#current_url').val(), createQueryParameter());
    
	var goBtnId = 'go_calendar';
	var goTarge = 'calendar';
<?php
} else {
?>  
	var url = getServerUrl() + "report/daily_calendar_embed.php?daily=1&request_query_type=1&request_order_by=start_time desc&pageSize="+defaultPageSize;
	$('#current_url').val(url).attr('data-container', '#report-calendar');

	executeQuery();
	//loadEmbedPage($('#current_url').attr("data-container"), $('#current_url').val(), createQueryParameter());
	
	var goBtnId = 'go_list';
	var goTarge = 'list';
<?php
}
?>

	//注册事件-点击“用户名称”发起聊天会话
	registerTalkToPerson(true);
	//注册事件-打开附件文件
	registerOpenResource();

	//注册事件-点击视图模式按钮
	$('#'+goBtnId).click(function(e) {
		location.href = replaceUrlParamVal(location.href, 'view_mode', goTarge);
	});

	//注册事件-点击空白位置或切换左侧菜单关闭右侧页
	$('#content-left-side, #board').click(function(e) {
        if(parseInt($('#sidepage').css('right'))>=0)
        	closeSidepage();
 //       stopPropagation(e); 不可以阻止事件传递，否则bootstrap插件(例如dropdown)不正常
	});	
}

$(document).ready(function() {
	logjs_info('daily_i pageStarted='+pageStarted+', restApiReady='+restApiReady);
	
	//检测并执行页面业务流程
	if (!pageStarted && restApiReady) {
		logjs_info('daily_i page ready...');
		pageStarted = true;
		start_page_after_restapi_ready();
	}
});
</script>

</body>
</html>