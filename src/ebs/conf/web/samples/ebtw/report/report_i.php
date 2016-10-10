<!DOCTYPE html>
<html>
<head>
<title>报告</title>
<?php
	$relative_path = '../';
	require_once dirname(__FILE__).'/../html_head_include.php';
	require_once dirname(__FILE__).'/../html_base.php';
	
	//视图模式
	$ListMode = true; //列表
	if ('calendar'==@$_REQUEST['view_mode']) //日历
		$ListMode = false;
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
					<input type="text" style="" class="form-control ebtw-menu-input ebtw-menu-input-zindex" id="report_work" name="report_work" placeholder="工作内容">
					<span class="input-group-btn">
					<button type="button" id="" class="form-control btn btn-default ebtw-menu-input" title="查询"><span class="glyphicon glyphicon-search"></span></button>
				  	</span>
				</div>
      	 	</div>
		</div>
		
		<div class="col-xs-2 col-md-2">
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
		<div class="col-xs-12 ebtw-list-main ebtw-vertical-nopadding" >
			<div class="row">
				<!-- 左侧菜单 -->
				<div class="col-xs-2 col-md-2 ebtw-menu-pull-1 ebtw-left-r-gutter ebtw-menu-item"  onselectstart="javascript:return false;" style="-moz-user-select:none;">
					<div class="row" >
						<div class="col-xs-12">
							<a href="#" class="list-group-item ebtw-left-no-border">我的报告<span class="ebtw-badge">15</span></a>
							<a href="#" class="list-group-item ebtw-left-no-border">评阅报告<span class="ebtw-badge"></span></a>
							<a href="#" class="list-group-item ebtw-left-no-border active">下级报告<span class="ebtw-badge">7</span></a>
						</div>
					</div>
					
					<div class="row" ><div class="col-xs-12"><label>&nbsp;</label></div></div>
					
					<div class="row" >
						<div class="col-xs-12 ">
				            <div class="nav-group-head ebtw-left-no-border">报告分类
				                <span id="nav_toggle_class" class="ebtw-nav-toggle glyphicon glyphicon-chevron-up"></span>
				                <span id="nav_add_class" class="ebtw-nav-icon glyphicon glyphicon-plus" title="添加分类"></span>
				            </div>
				            <a href="#" class="list-group-item ebtw-left-no-border">分类一 <span id="nav_setting_class" class="ebtw-nav-icon glyphicon glyphicon-cog" title="编辑分类"></span></a>
							<a href="#" class="list-group-item ebtw-left-no-border">分类二 <span id="nav_setting_class" class="ebtw-nav-icon glyphicon glyphicon-cog" title="编辑分类"></span></a>
						</div>
					</div>					
				</div>
				
				<!-- 正文内容 -->
				<div class="col-xs-10 col-md-10">
					<!-- 正文列表 -->
					<div class="row">
					<?php
					if ($ListMode) {
					?>
						<div class="col-xs-12 report-list-page" id="report-list"></div>
					<?php
					} else {
					?>
						<div class="col-xs-12 report-calendar-page" id="report-calendar"></div>
					<?php
					}
					?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
</div>
<div id="sidepage" class="sidepage col-xs-12 ebtw-embed-row resizeMe" style="display: block; right: -750px; width: 700px;"></div>
<div id="btn_gotop" class="btn_gotop" title="返回顶端" style="display: none;"><span class="glyphicon glyphicon-arrow-up"></span></div>

<script type="text/javascript">
//RestAPI访问渠道建立后执行的页面业务流程
function start_page_after_restapi_ready() {
<?php
if ($ListMode) {
?>    
    loadEmbedPage('#report-list', getServerUrl() + "report/report_list_embed.php");//, param);
    var goBtnId = 'go_calendar';
    var goTarge = 'calendar';
<?php
} else {
?>    
    loadEmbedPage('#report-calendar', getServerUrl() + "report/report_calendar_embed.php");//, param);
    var goBtnId = 'go_list';
    var goTarge = 'list';
<?php
}
?>

	//注册事件-点击视图模式按钮
	$('#'+goBtnId).click(function(e) {
		location.href = replaceUrlParamVal(location.href, 'view_mode', goTarge);
	});
}

$(document).ready(function() {
	logjs_info('pageStarted='+pageStarted+', restApiReady='+restApiReady);
	
	//检测并执行页面业务流程
	if (!pageStarted && restApiReady) {
		logjs_info('page ready...');
		pageStarted = true;
		start_page_after_restapi_ready();
	}
});
</script>

</body>
</html>