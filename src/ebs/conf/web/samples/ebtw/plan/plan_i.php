<?php
include dirname(__FILE__).'/../plan/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../plan/include.php';
$relative_path = '../';
?>
<!DOCTYPE html>
<html>
<head>
<title>计划</title>
<?php
	require_once dirname(__FILE__).'/../html_head_include.php';
	
	//请求业务类型
	$query_type = get_request_param(REQUEST_QUERY_TYPE);
	if (empty($query_type))
		$query_type = 1;
	
	//视图模式
	$ListMode = true; //列表
	if ('board'==@$_REQUEST['view_mode']) //看板
		$ListMode = false;
	
	//自动打开指定计划详情页面
	$accessTempKey = get_request_param('access_temp_key');
		
	require_once dirname(__FILE__).'/../html-script/period-selector-script.php';
	require_once dirname(__FILE__).'/../html-script/some-script.php';
	require_once dirname(__FILE__).'/../html-script/select-option-script.php';
	require_once dirname(__FILE__).'/../html-script/sidepage-tab-script.php';
	
	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
?>
</head>
<body>
<div class="container-fluid zoom-container">
   <div class="row" id="ptr-top"><div class="col-xs-12">&nbsp;</div></div>
	
	<!-- 顶上菜单 -->
	<div class="row" id="ptr-menu-top">
		<div class="col-xs-2">&nbsp;</div>
		<div class="col-xs-8">
		 	<div class="form-inline">
				<select class="form-control normal" id="status" name="status">
				 <option value="">所有状态</option>
	      		</select>
	      		
				<select class="form-control normal" id="period" name="period">
				 <option value="">所有周期</option>
		         <option value="1">日计划</option>
		         <option value="2">周计划</option>
		         <option value="3">月计划</option> 
		         <option value="4">季计划</option>
		         <option value="5">年计划</option>
		         <option value="6">自定义</option>
	      		</select>
	      		
	      		<div class="input-group">
				  <input type="text" style="" class="form-control ebtw-menu-input ebtw-menu-input-zindex" id="plan_name" name="plan_name" placeholder="计划事项">
				  <span class="input-group-btn">
				  <button type="button" id="search" class="form-control ebtw-btn ebtw-menu-input btn-search" title="查询"><span class="glyphicon glyphicon-search"></span></button>
				  </span>
				</div>
				&nbsp;&nbsp;
				<div class="input-group">
					<div class="search-content-bar">
						&nbsp;'<span class="search-keyword"></span>'<!-- 关键词 -->
						&nbsp;<span class="no-result">搜索没有记录</span>
						&nbsp;<span class="has-result">搜索到 <span class="result-count"></span> 个记录</span>
					</div>
				</div>				
      	 	</div>
		</div>
		
		<div class="col-xs-2">
			<div class="form-inline">
				<div class="input-group pull-right ebtw-right-gutter">
					<span class="input-group-btn">
					  <button type="button" id="go_board" class="form-control btn btn-default ebtw-menu-input <?php if(!$ListMode){ ?>active<?php } ?>" title="看板"><span class="glyphicon glyphicon-list-alt"></span></button>
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
	
	<div class="row">
		<div class="col-xs-12 ebtw-list-main ebtw-horizontal-nopadding ebtw-main-content-bg" >
			<div class="row">
				<input type="hidden" id="current_left_menu" value="1_<?php echo $query_type;?>">
				<input type="hidden" id="is_deleted" value="0">
				<!-- 左侧菜单 -->
				<div class="col-xs-2 ebtw-menu-pull-1 ebtw-horizontal-nopadding-right ebtw-menu-item" id="content-left-side" onselectstart="javascript:return false;" style="-moz-user-select:none;">
					<div class="row" >
						<div class="col-xs-12">
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==1) echo 'active';?> query_type" type="1"><span class="item-name">个人计划</span><span class="ebtw-badge" title="未完成数量"></span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==2) echo 'active';?> query_type" type="2"><span class="item-name">评审计划</span><span class="ebtw-badge" title="需要评审回复数量"></span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==3) echo 'active';?> query_type" type="3"><span class="item-name">共享计划</span><span class="ebtw-badge" title="共享未阅数量"></span></a>
						</div>
					</div>
					
					<div class="row" ><div class="col-xs-12 menu-inner-divide"></div></div>
					
					<div class="row" >
						<div class="col-xs-12">
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==4) echo 'active';?> query_type" type="4"><span class="item-name">下级计划</span><span class="ebtw-badge" title="下级未完成数量"></span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==5) echo 'active';?> query_type" type="5"><span class="item-name">团队计划</span><span class="ebtw-badge" title="团队未完成数量"></span></a>
						</div>
					</div>					
					
					<div class="row" ><div class="col-xs-12 menu-inner-divide"></div></div>
					
					<div class="row" >
						<div class="col-xs-12 ptr-class-menu">
				            <div class="nav-group-head ebtw-left-no-border">&nbsp;
				                <span id="nav_toggle_class" class="ebtw-nav-toggle" data-status="down" title="点击显示计划分类"><span class="glyphicon glyphicon-chevron-down"></span>&nbsp;计划分类</span>
				                <span id="nav_add_class" class="ebtw-nav-icon glyphicon glyphicon-plus" title="添加分类"></span>
				            </div>
							<input type="hidden" id="ptr_class_input" value="">
						</div>
					</div>
					
					<div class="row" ><div class="col-xs-12 menu-inner-divide"></div></div>
					
					<div class="row" >
						<div class="col-xs-12 ">
							<a href="#" class="list-group-item ebtw-left-no-border recycle_bin"><span>回收站</span> <span class="ebtw-badge" title="已删除计划数量"></span><span id="nav_empty_trash_plan" class="ebtw-nav-icon glyphicon glyphicon-trash ebtw-hide" title="清空回收站"></span></a>
							<input type="hidden" id="recycle_bin_input" value="">
						</div>
					</div>
				</div>
				
				<!-- 正文内容 -->
				<div class="col-xs-10 ebtw-horizontal-padding-right-double" id="content-right-side">
				<?php
				if ($ListMode) {
				?>
					<!-- 正文工具栏 -->
					<div class="row" id="content-menu" onselectstart="javascript:return false;" style="-moz-user-select:none;">
						<!-- 左上按钮 -->
						<div class="col-xs-5">
							<div class="form-inline ebtw-menu-pull-1">
								<div class="input-group ebtw-menu-pull-top">
									<span class="input-group-btn">
									  <button type="button" id="period-type-switch-1" class="form-control btn btn-default ebtw-menu-input period-type-switch" title="日计划">日</button>
									</span>
									<span class="input-group-btn">
									  <button type="button" id="period-type-switch-2" class="form-control btn btn-default ebtw-menu-input period-type-switch" title="周计划">周</button>
									</span>
									<span class="input-group-btn">
									  <button type="button" id="period-type-switch-3" class="form-control btn btn-default ebtw-menu-input period-type-switch" title="月计划">月</button>
									</span>
								</div>
								<div class="input-group">&nbsp;</div>
								<div class="input-group">
									<span class="input-group-btn">
									  <button type="button" id="btn-today" class="form-control btn btn-default" title="本周计划">本周</button>
									</span>
								</div>
								<div class="input-group">&nbsp;</div>
								<div class="input-group ebtw-menu-pull-top">
									<span class="input-group-btn">
									  <button type="button" id="btn_Quick_AddPTR" class="form-control btn btn-default ebtw-menu-input" title="快速新建计划"><span class="glyphicon glyphicon-plus"></span> 快速新建</button>
									</span>
								</div>								
							</div>
						</div>
						
						<!-- 中间日期选择工具条 -->
						<div class="col-xs-5">
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
						
						<!-- 右上按钮 -->
						<div class="col-xs-2">							
							<button id="btn_AddPTR" type="button" class="btn btn-primary ebtw-btn-width ebtw-menu-input ebtw-menu-pull-1 ebtw-right-overline pull-right"><span class="glyphicon glyphicon-plus"></span> 新建计划</button>
						</div>
					</div>
					
					<!-- 正文列表 -->
					<div class="row">
						<div class="ptr-container mCustomScrollbar" data-mcs-theme="minimal-dark"><!-- dark-3 -->
							<div id="gridList" class="col-xs-12 dt-grid-container ebtw-right-gutter-no"></div>
						</div>
						<div id="gridToolBar" class="col-xs-12 dt-grid-toolbar-container"></div>
					</div>
					<?php
					} else {
					?>
					<div class="row">
						<div class="col-xs-12 board-page" id="board"></div>
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
var logonUserId = '<?php echo $userId;?>';
var globalSubId = '<?php echo $SUB_IDS[$PTRType];?>';
<?php 
if (!empty($accessTempKey)) echo "var accessTempKey = \"$accessTempKey\";";
?>

//主键字段名
var ID_NAME = 'plan_id';
var PTR_TYPE = <?php echo $PTRType;?>;

//创建查询参数
function createQueryParameter() {
	var parameter = createUsualQueryParameter();
	//删除标记
	var isDeleted = $('#is_deleted').val();
	if (isDeleted!=undefined && isDeleted.length>0) {
		parameter.is_deleted = isDeleted;
	}
	
	//计划事项
	var searchContent = $('#plan_name').val();
	if (searchContent!=undefined && $.trim(searchContent).length>0) {
		parameter.plan_name_lk = $.trim(searchContent);
	}

	//计划分类
	var ptrClass = $('#ptr_class_input').val();
	if (ptrClass!=undefined && ptrClass.length>0) {
		parameter.class_id = ptrClass;
	}

	//回收站
	var recycle_bin = $('#recycle_bin_input').val();
	if (recycle_bin!=undefined && recycle_bin.length>0 && recycle_bin=='1') {
		parameter.is_deleted = 1;
	}
	
	return parameter;
}

//清空查询输入框内容
function resetSearchContent() {
	$('#plan_name').val('');
}
//复位其它查询条件输入框
function resetOtherSearchConditions() {
	$('select#period').val('').select2({minimumResultsForSearch:Infinity}); //"周期"
}

//切换状态查询条件备选项
function switchStatusSelector(queryType) {
	var optionHtml = '<option value="">所有状态</option>';
	if (queryType==2) { //审批计划
		optionHtml = optionHtml+'<option value="2">评审中</option>'
			+'<option value="4">评审通过</option>'
			+'<option value="5">评审拒绝</option>';
	} else {
		optionHtml = optionHtml+'<option value="-1">未完成</option>'
			+'<option value="2">评审中</option>'
			+'<option value="4">评审通过</option>'
			+'<option value="5">评审拒绝</option>'
			+'<option value="6">已完成</option>';
	}
	$('select#status').html('').html(optionHtml);
	
	if (queryType==2)
		$('select#status').val(queryType).select2({minimumResultsForSearch:Infinity}); //默认选中“评审中”
	else
		$('select#status').val('').select2({minimumResultsForSearch:Infinity}); //{minimumResultsForSearch:Infinity} Hiding the search box
}

//RestAPI访问渠道建立后执行的页面业务流程
function start_page_after_restapi_ready() {
	//切换状态查询条件备选项
	switchStatusSelector(<?php echo $query_type;?>);

	//注册事件：点击菜单-分类根目录 折叠/展开
	bindStretchClick($('.nav-group-head'), null, function($This) {
		return $This.parent().parent().children("a");
	}, function($This, executeValues) {
		if (executeValues[1]=='up')
			$This.attr('title', '点击隐藏计划分类');
		else 
			$This.attr('title', '点击显示计划分类');
			
		//隐藏/显示底部框线
		$This.parent().css('border-width', (executeValues[1]=='up'&&$This.parent().next('a').length>0)?'1px 1px 0px 1px':'1px 1px 1px 1px');
	});
	
	//加载计划分类代码对照表并生成分类子菜单
	loadPTRClassAndCreateClassMenu(PTR_TYPE, false, true, function(codeTable) { //分类菜单创建完毕
		for (classId in codeTable) {
			refreshPTRMenuBadges([100], PTR_TYPE, null, {reserved_query_of_calss:true, class_id:classId});
		}
	}, function(classId) {
		if (classId ==$('#ptr_class_input').val()) {
			var $element = $('.ptr_class_item:eq(0)');
			if ($element.length==1) {
				$element.trigger('click');
			} else {
				$('.query_type:eq(0)').trigger('click');
			}
		}
	});
	//加载任务分类代码对照表
	loadPTRClassAndCreateClassMenu(2, true);
	
	//注册左侧菜单事件
	registerLeftMenu(PTR_TYPE, function() {
		var params = $('#current_left_menu').val().split('_');
		var queryType = params[1];
		if (params[0]==1 && parseInt(queryType)<=5) {
			refreshPTRMenuBadges([queryType], PTR_TYPE);
		} else if(params[0]==3) {
			refreshPTRMenuBadges([10], PTR_TYPE);
		}

		//判断是否显示"快速新建"按钮
		if (queryType==1)
			$('#btn_Quick_AddPTR').removeClass('ebtw-invisible');
		else 
			$('#btn_Quick_AddPTR').addClass('ebtw-invisible');
	});
	//刷新菜单角标(badge)
	refreshPTRMenuBadges([1,2,3,4,5,10], PTR_TYPE);

	//"回收站"菜单鼠标悬停事件
	$('.recycle_bin').hover(function() {
		$(this).find('.ebtw-nav-icon').removeClass('ebtw-hide');
		$(this).find('.ebtw-badge').addClass('ebtw-hide');
	}, function() {
		$(this).find('.ebtw-nav-icon').addClass('ebtw-hide');
		$(this).find('.ebtw-badge').removeClass('ebtw-hide');
	});
	
	//清空回收站
	$('#nav_empty_trash_plan').click(function(e) {
		layer.confirm('清空回收站后不可恢复，确定要清空吗？', {title:'清空回收站'}, function(index) {
			var loadIndex = layer.load(2);
			//执行删除函数
			executeDelete(getServerUrl()+'plan/emptyTrash.php', null, function(affected){
				refreshPTRMenuBadges([10], PTR_TYPE); //刷新回收站计划数量
				loadDtGrid(createQueryParameter()); //刷新列表视图
				
				layer.close(loadIndex);
			}, function(){
				layer.close(loadIndex);
			});
			layer.close(index);
		}, function() {
			
		});
		
		//阻止事件传递
		stopPropagation(e);
	});

    //注册点击事件-新建计划
    $("#btn_AddPTR").click(function (e) {
    	ptrAddAction(PTR_TYPE);
    	stopPropagation(e);
    });
	
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
	//状态、周期下拉框选择变更
	$('#status, #period').change(function() {
		loadDtGrid(createQueryParameter());
	});
	$('#search').click(function() {
		$(this).blur();
		loadDtGrid(createQueryParameter());
	});

	//注册事件-"计划事项"输入框 [Enter回车]执行查询
	$('input[id="plan_name"]').keydown(function(event) {
		if (event.keyCode==13) {
	        if (event.preventDefault)
	        	event.preventDefault();
	        if (event.returnValue) 
	        	event.returnValue = false;
			
			//触发点击“查询”事件
			$('#search').trigger('click');
		}
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
	
	//加载表格 
    $.getScript(getServerUrl()+"js/prepare_plan_grid_data.js", function() {
    	$.ebtw.currentPTRUrl = getServerUrl()+'plan/list.php?request_query_type='+<?php echo $query_type;?>;
        $.getScript(getServerUrl()+"js/load_grid.js");
    });
	
	var goBtnId = 'go_board';
	var goTarge = 'board';
<?php } else { ?>
	//计算并保存列表区域最大高度
	$boardPage = $('.board-page');
	registerCalculateAdjustContainerHeight2($('#ptr-content-height-input'), calculateRootHeight()+($boardPage.outerHeight(true)-$boardPage.height()), function(height){
		$('#board').height(height);
		//设置内容区域高度左侧与右侧相等
		$('#content-left-side').height($('#content-right-side').height());
	});
	
	//加载看板页面
	loadEmbedPage('#board', getServerUrl() + "workbench_board.php");//, param);
	
	var goBtnId = 'go_list';
	var goTarge = 'list';
<?php } ?>

	//注册事件-注册点击计划、任务等链接的处理函数
	registerAssociateRedirect();
	//注册事件-点击“用户名称”发起聊天会话
	registerTalkToPerson(true);
	//注册事件-打开附件文件
	registerOpenResource();
	
	//注册事件-点击视图模式按钮
	$('#'+goBtnId).click(function(e) {
		location.href = replaceUrlParamVal(location.href, 'view_mode', goTarge);
		$(this).blur();
	});

	//注册事件-点击空白位置或切换左侧菜单关闭右侧页
	$('#content-left-side, #board').click(function(e) {
        if(parseInt($('#sidepage').css('right'))>=0)
        	closeSidepage();
 //       stopPropagation(e); 不可以阻止事件传递，否则bootstrap插件(例如dropdown)不正常
	});
}

$(document).ready(function() {
	logjs_info('plan_i pageStarted='+pageStarted+', restApiReady='+restApiReady);
	
	//检测并执行页面业务流程
	if (!pageStarted && restApiReady) {
		logjs_info('plan_i page ready...');
		pageStarted = true;
		start_page_after_restapi_ready();
	}
});
</script>
</body>
</html>