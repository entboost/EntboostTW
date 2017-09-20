<?php
include dirname(__FILE__).'/../attendance/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../attendance/include.php';
require_once dirname(__FILE__).'/../attendance/attendance_functions.php';
$relative_path = '../';
?>
<!DOCTYPE html>
<html>
<head>
<title>考勤</title>
<?php
	require_once dirname(__FILE__).'/../html_head_include.php';
	
	//请求业务类型
	$query_type = get_request_param(REQUEST_QUERY_TYPE);
	if (empty($query_type))
		$query_type = 1;
	
	//视图模式
	$ListMode = true; //列表
	if ('board'===@$_REQUEST['view_mode']) //看板
		$ListMode = false;
	
	//自动打开指定考勤审批详情页面
	$accessTempKey = get_request_param('access_temp_key');
	
	require_once dirname(__FILE__).'/../html-script/board-script.php';
	require_once dirname(__FILE__).'/../html-script/period-selector-script.php';
	require_once dirname(__FILE__).'/../html-script/some-script.php';
	require_once dirname(__FILE__).'/../html-script/select-option-script.php';
	require_once dirname(__FILE__).'/../html-script/sidepage-tab-script.php';
	require_once dirname(__FILE__).'/../html-script/attendance-script.php';	
	
	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
	$entCode = $_SESSION[USER_ENTERPRISE_CODE]; //企业编号
	$isEntManager = $_SESSION[IS_ENTERPRISE_MANAGER]; //是否企业管理者
	
	//获取考勤管理人员(包括考勤专员和部门经理)的权限情况
	$groupCodes = array();
	$authorityResult = getAttendanceManageAuthority($entCode, $groupCodes, $userId);
	$authorityResult2 = getAttendanceManageAuthority($entCode, $groupCodes, $userId, true);
	if ($authorityResult===true || (gettype($authorityResult)=='array' && count($authorityResult)>0) || $isEntManager)
		$isManager = true;
	else 
		$isManager = false;
?>
</head>
<body>
<div class="container-fluid zoom-container">
	<div class="row" id="ptr-top"><div class="col-xs-12">&nbsp;</div></div>
	<!-- 考勤工具栏 -->
	<div class="ptr-large-toolbar">
		<div class="datetime">
			<div class="sTime"></div>
			<div class="sDate"></div>
		</div>
		<div class="action-button btn-primary attendSign ebtw-hide" data-sign-type="<?php echo ACTION_TYPE_SIGN_IN;?>" onselectstart="javascript:return false;" style="-moz-user-select:none;">签到</div>
		<div class="action-button btn-warning attendSign ebtw-hide" data-sign-type="<?php echo ACTION_TYPE_SIGN_OUT;?>" onselectstart="javascript:return false;" style="-moz-user-select:none;">签退</div>
	</div>
	
	<!-- 顶上菜单 -->
	<div class="row" id="ptr-menu-top">
		<div class="col-xs-2">&nbsp;</div>
		<div class="col-xs-8">
		 	<div class="form-inline">
		 	<?php if ($query_type>=2 && $query_type<=9){?>
		 		<?php if(in_array($query_type, array(2,3,4,5))) {?>
				<select class="form-control normal" id="rec_state" name="rec_state">
				 <option value="">所有状态</option>
				 <option value="2">未签到</option>
		         <option value="4">未签退</option>
		         <option value="8">旷工</option> 
		         <option value="16">迟到</option>
		         <option value="32">早退</option>
	      		</select>
	      		
				<select class="form-control normal" id="req_type" name="req_type">
				 <option value="">所有类型</option>
		         <option value="1">补签</option>
		         <option value="2">外勤</option>
		         <option value="3">请假</option> 
		         <option value="4">加班</option>
		         <?php if ($query_type==4) {?>
		         <option value="0">未申请</option>
		         <?php }?>
	      		</select>
	      		<?php } else if (in_array($query_type, array(6,7,8,9)) && $isManager) {
	      			$groupType = 0;
	      			$managerUid = null;
	      			if ($isEntManager || $authorityResult===true) { //企业管理者或考勤专员
	      				//忽略
	      			} else if (gettype($authorityResult)=='array' && count($authorityResult)>0) {
	      				$managerUid = $userId;
	      			}
	      			$groupResults = DepartmentInfoService::get_instance()->getGroupInfos($entCode, $groupType, $managerUid);
	      			?>
				<select class="form-control normal" id="search_group_id" name="search_group_id">
				 <option value="">所有部门</option>
				 <?php 
				 	if (!empty($groupResults)) {
				 		foreach ($groupResults as $group) {
				 ?>
				 <option value="<?php echoField($group, 'group_id')?>"><?php echoField($group, 'dep_name')?></option>
		         <?php }
				 	}?>
	      		</select>
	      		
				<select class="form-control normal" id="search_user_id" name="search_user_id">
				 <option value="">所有员工</option>
	      		</select>
	      		<div class="input-group">
				  <input type="text" style="" class="form-control ebtw-menu-input ebtw-menu-input-zindex" id="employee_name" name="employee_name" placeholder="搜索员工">
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
				<?php }?>
			<?php }?>
      	 	</div>
		</div>
		
		<div class="col-xs-2">
			<div class="form-inline">
				<div class="input-group pull-right ebtw-right-gutter">
					<!-- 
					<span class="input-group-btn">
					  <button type="button" id="go_board" class="form-control btn btn-default ebtw-menu-input <?php if(!$ListMode){ ?>active<?php } ?>" title="看板"><span class="glyphicon glyphicon-list-alt"></span></button>
					</span> -->
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
				<input type="hidden" id="current_url" value="" data-container="">
				<input type="hidden" id="current_left_menu" value="1_<?php echo $query_type;?>">
				<!-- 左侧菜单 -->
				<div class="col-xs-2 ebtw-menu-pull-1 ebtw-horizontal-nopadding-right ebtw-menu-item" id="content-left-side" onselectstart="javascript:return false;" style="-moz-user-select:none;">
					<div class="row" >
						<div class="col-xs-12">
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==1) echo 'active';?> query_type" type="1"><span class="item-name">我的考勤</span><span class="ebtw-badge" title="我的考勤异常数量"></span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==2) echo 'active';?> query_type" type="2"><span class="item-name">我的申请</span><span class="ebtw-badge" title="我的未被处理的审批申请数量"></span></a>
							<?php 
							if (!empty($isManager)) {
							?>							
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==3) echo 'active';?> query_type" type="3"><span class="item-name">考勤审批</span><span class="ebtw-badge" title="待审批的数量"></span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==4) echo 'active';?> query_type" type="4"><span class="item-name">考勤异常</span><span class="ebtw-badge" title="未申请的考勤异常的数量"></span></a>
							<?php }?>
						</div>
					</div>
					
					<div class="row" ><div class="col-xs-12 menu-inner-divide"></div></div>
					
					<div class="row" >
						<div class="col-xs-12">
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==6) echo 'active';?> query_type" type="6"><span class="item-name">工作时长</span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==7) echo 'active';?> query_type" type="7"><span class="item-name">考勤汇总</span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==8) echo 'active';?> query_type" type="8"><span class="item-name">考勤报表</span></a>
						</div>
					</div>					
					
					<div class="row" ><div class="col-xs-12 menu-inner-divide"></div></div>
					
					<?php if ($authorityResult2===true || $isEntManager) { //有管理权限的考勤专员或系统管理员可见?>
					<div class="row" >
						<div class="col-xs-12">
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==10) echo 'active';?> query_type" type="10"><span class="item-name">考勤规则</span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==11) echo 'active';?> query_type" type="11"><span class="item-name">考勤专员</span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==12) echo 'active';?> query_type" type="12"><span class="item-name">请假类型</span></a>
							<a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==13) echo 'active';?> query_type" type="13"><span class="item-name">节假日设置</span></a>
							<!-- <a href="#" class="list-group-item ebtw-left-no-border <?php if ($query_type==14) echo 'active';?> query_type" type="14"><span class="item-name">日结作业测试</span></a> -->
						</div>
					</div>
					<?php }?>
				</div>
				
				<!-- 正文内容 -->
				<div class="col-xs-10 ebtw-horizontal-padding-right-double" id="content-right-side">
				<?php
				if ($ListMode) {
				?>
					<?php if ($query_type>=1 && $query_type<=9){?>
					<!-- 正文工具栏 -->
					<div class="row" id="content-menu" onselectstart="javascript:return false;" style="-moz-user-select:none;">
						<!-- 左上按钮 -->
						<div class="col-xs-5">
							<div class="form-inline ebtw-menu-pull-1">
								<div class="input-group ebtw-menu-pull-top">
									<span class="input-group-btn">
									  <button type="button" id="period-type-switch-1" class="form-control btn btn-default ebtw-menu-input period-type-switch" title="日考勤">日</button>
									</span>
									<span class="input-group-btn">
									  <button type="button" id="period-type-switch-2" class="form-control btn btn-default ebtw-menu-input period-type-switch" title="周考勤">周</button>
									</span>
									<span class="input-group-btn">
									  <button type="button" id="period-type-switch-3" class="form-control btn btn-default ebtw-menu-input period-type-switch" title="月考勤">月</button>
									</span>
								</div>
								<div class="input-group">&nbsp;</div>
								<div class="input-group">
									<span class="input-group-btn">
									  <button type="button" id="btn-today" class="form-control btn btn-default" title="本周计划">本周</button>
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
						
						<?php if ($query_type==1){?>
						<!-- 右上按钮 -->
						<div class="col-xs-2">							
							<button id="btn_AddAttendApproval" type="button" class="btn btn-primary ebtw-btn-width ebtw-menu-input ebtw-menu-pull-1 ebtw-right-overline pull-right"><span class="glyphicon glyphicon-plus"></span> 新建审批</button>
						</div>
						<?php }?>
					</div>
					<?php }?>
					
					<!-- 正文列表 -->
					<div class="row">
					<?php 
						if ($query_type==1)
							include dirname(__FILE__)."/query_type_1.php";
						else if ($query_type>=2 && $query_type<=9)
							include dirname(__FILE__)."/query_type.php";
						else
							include dirname(__FILE__)."/query_type_$query_type.php";
					?>
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
<div id="sidepage" class="sidepage col-xs-12 ebtw-embed-row resizeMe" style="display: block; right: -850px; width: 800px;"></div>
<div id="btn_gotop" class="btn_gotop" title="返回顶端" style="display: none;"><span class="glyphicon glyphicon-arrow-up"></span></div>

<input type="hidden" id="ptr-content-height-input" value="0">

<script type="text/javascript">
var logonUserId = '<?php echo $userId;?>';
var globalSubId = '<?php echo $SUB_IDS[$PTRType];?>';
<?php 
if (!empty($accessTempKey)) echo "var accessTempKey = \"$accessTempKey\";";
?>

var PTR_TYPE = <?php echo $PTRType;?>;
var queryType = <?php echo $query_type?>;

//dtGrid显示扩展行
var gridExtra = true;
//dtGrid行样式
var gridRowStyle = true;

//创建查询参数
function createQueryParameter() {
	var parameter = createUsualQueryParameter();

	//考勤状态
	var recState = $('#rec_state').val();
	if (recState!=undefined && recState.length>0) {
		parameter.search_rec_state = recState;
	}
	//审批类型
	var reqType = $('#req_type').val();
	if (reqType!=undefined && reqType.length>0) {
		parameter.req_type = reqType;
	}
	//搜索员工
	var searchContent = $('#employee_name').val();
	if (searchContent!=undefined && $.trim(searchContent).length>0) {
		parameter.user_name = $.trim(searchContent);
	}

	//部门
	var sGroupId = $('#search_group_id').val();
	if (sGroupId!=undefined && sGroupId.length>0) {
		parameter.search_group_id = sGroupId;
	}
	
	//员工
	var sUserId = $('#search_user_id').val();
	if (sUserId!=undefined && sUserId.length>0) {
		parameter.search_user_id = sUserId;
	}
	
	return parameter;
}

//清空查询输入框内容
function resetSearchContent() {
	$('#employee_name').val('');
}

//复位其它查询条件输入框
function resetOtherSearchConditions() {
	$('select#rec_state').val('').select2({minimumResultsForSearch:Infinity}); //考勤状态
	$('select#req_type').val('').select2({minimumResultsForSearch:Infinity}); //审批类型
	$('select#search_group_id').val('').select2({minimumResultsForSearch:Infinity}); //部门
	$('select#search_user_id').val('').select2({minimumResultsForSearch:Infinity}); //员工
}

//RestAPI访问渠道建立后执行的页面业务流程
function start_page_after_restapi_ready() {
	//注册左侧菜单事件
	registerLeftMenu2(PTR_TYPE, function() {
		var params = $('#current_left_menu').val().split('_');
		var queryType = params[1];
		if (params[0]==1 && (queryType==1 || queryType==3)) {
			refreshPTRMenuBadges([queryType], PTR_TYPE);
		}
	});
	//刷新菜单角标(badge)
	refreshPTRMenuBadges([1,3], PTR_TYPE);
	
    //注册点击事件-新建计划
    $("#btn_AddAttendApproval").click(function (e) {
    	//ptrAddAction(PTR_TYPE);
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

	//标记首次打开页面
	var pagefirstRun = true;
<?php
if ($ListMode) {
?>	
	var url = getServerUrl() + "attendance/attendance_i.php?view_mode=list&request_query_type=1";
	$('#current_url').val(url);

<?php if ($query_type>=1 && $query_type<=9) {?>
	//日期范围工具栏
	implementPeriodSelector(executeRenderContent, false);
	//注册事件-"今天"按钮
    registerTodaySelector();
	
	//注册事件-勾选日期查询范围控件
	registerDatePeriodCtrl(false, false, executeRenderContent);
<?php }?>
	
	//计算并保存列表区域最大高度
	$ptrContainer = $('.ptr-container');
	//校准高度
	var adjustedHeight = 0;
	if (queryType>=2 && queryType<=9)
		adjustedHeight = 50;
	//自适应高度
	registerCalculateAdjustContainerHeight2($('#ptr-content-height-input'), (calculateRootHeight() + ($ptrContainer.outerHeight(true)-$ptrContainer.height()) + adjustedHeight/*$('#gridToolBar').outerHeight(true)*/), function(height) {
		$ptrContainer.height(height);
		//logjs_info(''+$ptrContainer.height()+', '+$('#content-right-side').height());
		
		//执行预设函数
		if (typeof runAfterReady ==='function')
			runAfterReady(pagefirstRun);

		setTimeout(function() {
			//设置内容区域高度左侧与右侧相等
			$('#content-left-side').height($('#content-right-side').height());
		}, 1);
		
		pagefirstRun =false;
	});

<?php if ($query_type>=2 && $query_type<=9) {?>
	//考勤状态、审批类型、员工下拉框选择变更
	$('#rec_state, #req_type, #search_user_id').change(function() {
		loadDtGrid(createQueryParameter());
	});
	//部门下拉框选择变更
	$('#search_group_id').change(function() {
		//清除员工下拉框旧的内容
		$('select#search_user_id>option[value!=""]').remove();
		$('select#search_user_id').val('').select2({minimumResultsForSearch:Infinity});
		
		//执行查询列表
		loadDtGrid(createQueryParameter());
		
		//加载成员列表
		var groupId = $(this).val();
		if (groupId.length>0) { //选中某一个部门
			var loadIndex = layer.load(2);
			organizationSearch(1, groupId, function(results) {
				var $option = $('select#search_user_id>option[value=""]');
				for (var i=results.length-1; i>=0; i--) {
					var entity = results[i];
					$option = $option.after('<option value="'+entity.emp_uid+'">'+entity.username+'</option>');
				}
				
				layer.close(loadIndex);
			}, function(error){
				layer.close(loadIndex);
			});
		} else { //选中"所有部门"
			$('select#search_user_id>option[value!=""]').remove();
			$('select#search_user_id').val('').select2({minimumResultsForSearch:Infinity});
		}
	});
	
	//点击搜索员工
	$('#search').click(function() {
		$(this).blur();
		loadDtGrid(createQueryParameter());
	});
	
	//注册事件-"搜索员工"输入框 [Enter回车]执行查询
	$('input[id="employee_name"]').keydown(function(event) {
		if (event.keyCode==13) {
	        if (event.preventDefault)
	        	event.preventDefault();
	        if (event.returnValue) 
	        	event.returnValue = false;
			
			//触发点击“查询”事件
			$('#search').trigger('click');
		}
	});
	
	//加载表格
    $.getScript(getServerUrl()+"js/prepare_attendance_approval_grid_data<?php if (!in_array($query_type, array(2,3))) echo $query_type;?>.js", function() {
    	$.ebtw.currentPTRUrl = getServerUrl()+'attendance/list.php?request_query_type='+<?php echo $query_type;?>;
		<?php if (in_array($query_type, array(1,2,3,4,6,7,8))) { //'我的申请'、'考勤审批'、'考勤异常'、'工作时长'、'考勤汇总'、'考勤报表' 页面默认以'月'作为查询跨度，并勾选有效?>
    	$('#period-type-switch-3').trigger('click');
    	<?php }?>
        $.getScript(getServerUrl()+"js/load_grid.js");
    });
<?php }?>
	
	//自定义滚动条
	customScrollbarUsingE($ptrContainer, 30, true);

	var goBtnId = 'go_board';
	var goTarge = 'board';
<?php } else { ?>
	//计算并保存列表区域最大高度
	$boardPage = $('.board-page');
	registerCalculateAdjustContainerHeight2($('#ptr-content-height-input'), calculateRootHeight()+($boardPage.outerHeight(true)-$boardPage.height())+$('#gridToolBar').outerHeight(true), function(height){
		$('#board').height(height);
		
		//执行预设函数
		if (typeof runAfterReady ==='function')
			runAfterReady(pagefirstRun);
		
		setTimeout(function() {
			//设置内容区域高度左侧与右侧相等
			$('#content-left-side').height($('#content-right-side').height());
		}, 1);
		
		pagefirstRun =false;
	});
	
	//加载看板页面
	//loadEmbedPage('#board', getServerUrl() + "workbench_board.php");//, param);
	
	var goBtnId = 'go_list';
	var goTarge = 'list';
<?php } ?>

	//注册事件-注册点击计划、任务等链接的处理函数
	registerAssociateRedirect();
	//注册事件-点击“用户名称”发起聊天会话
	registerTalkToPerson(true);
	//注册事件-打开附件文件
	registerOpenResource();

	//创建时钟显示
	clockWork('.ptr-large-toolbar .datetime');
	//检测当前时间允许"签到"还是"签退"
	checkAttendSignType();
	//处理点击"签到/签退"按钮事件
	$('.attendSign').bind('click', submitAttendSign);	
	
	//注册事件-点击打开右侧详情界面：考勤明细
	$(document).on('click', '.sidepage-open', function() {
		var $This = $(this);
		if ($This.attr('data-ptrtype')==5) {
			openAttendanceDetail($This.attr('data-subtype'), $This.attr('extData1'), $This.attr('extData2'), $This.attr('extData3'), $This.attr('extData4'), $This.attr('extData5'), function(){});
		}
	});
	
	//注册事件-点击视图模式按钮
	$('#'+goBtnId).click(function(e) {
		location.href = replaceUrlParamVal(location.href, 'view_mode', goTarge);
		$(this).blur();
	});
	
	//注册事件-点击空白位置或切换左侧菜单关闭右侧页
	$('#content-left-side, #content-right-side, #board').click(function(e) {
	    if(parseInt($('#sidepage').css('right'))>=0) {
	    	closeSidepage();
	    }
	//       stopPropagation(e); 不可以阻止事件传递，否则bootstrap插件(例如dropdown)不正常
	});

	//获取临时暂存的参数，并打开考勤审批详情页面
	if (typeof accessTempKey!='undefined' && accessTempKey!=null) {
		loadTempCustomParameter(accessTempKey, function(customData) {
			if (customData) {
				var accessTempType = customData.int_value;
				var entity = json_parse(customData.str_value);
				//logjs_info(entity);
				if (accessTempType==1) {  // 1=自动打开查询详情页面
	    			var openPtrId = entity.open_ptr_id;
	    			//var switchTabType = entity.switch_tab_type;
	    			//打开详情页面
	    			openAttendanceReq(null, openPtrId, null, function() {});
				}
			} else {
				logjs_info('can not load tempdata for key:'+accessTempKey);
			}
    		//清空临时访问参数
    		accessTempKey = null;
		});
	}
}

$(document).ready(function() {
	logjs_info('attendance_i pageStarted='+pageStarted+', restApiReady='+restApiReady);
	
	//检测并执行页面业务流程
	if (!pageStarted && restApiReady) {
		logjs_info('attendance_i page ready...');
		pageStarted = true;
		start_page_after_restapi_ready();
	}
});
</script>
</body>
</html>