<?php
include dirname(__FILE__).'/../report/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../report/include.php';

	$viewMode = @$_REQUEST['view_mode']; //视图模式
	$userId = $_SESSION[USER_ID_NAME];
	$userName = $_SESSION[USER_NAME_NAME];

	if ($viewMode!='a') {
		$embed = 1;
		include dirname(__FILE__).'/../report/get_one.php';
	} else {
		$startTime = @$_REQUEST['daily_start_time'];
	}
	
	//标记是否从查看详情页面跳转过来本页面
	$fromViewPage = get_request_param('reserved_from_view_page', '0');
	
	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
	$userAccount = $_SESSION[USER_ACCOUNT_NAME]; //当前用户的账号
?>
<div class="side-toolbar col-xs-12">
    <div class="side-toolbar-icon side-toolbar-status">
    	<span class="glyphicon glyphicon-eye-open ebtw-color-primary"></span>
    	<?php if ($viewMode=='a') {?>
    	&nbsp;<?php echo substr($startTime, 0, 10);?>
    	<?php } else {?>
    	&nbsp;<?php if ($userId!=$entity->report_uid) {echoField($entity, 'create_name');}?>&nbsp;<?php echo subStrOfDateTime($entity, 'start_time');?>
    	<?php }?>
    </div>
	
    <div class="side-close">
    	<span class="glyphicon glyphicon-remove" title="关闭"></span>
    </div>
    <div class="side-fullscreen" data-type="0">
    	<span class="glyphicon glyphicon-fullscreen" title="全屏"></span>
    </div>
    <?php if ($viewMode=='e' || $viewMode=='a') {?>
    <?php if ($viewMode=='e') {?>
    <div class="side-cancel" data-type="0">
        <span class="glyphicon glyphicon-floppy-remove" title="取消"></span>
    </div>
    <?php }?>
    <div class="side-save" data-type="0">
        <span class="glyphicon glyphicon-floppy-saved" title="保存"></span>
    </div>
    <?php }?>
</div>

<?php 
if ($viewMode!='a' && !isset($entity)) {
	echo '<div class="col-xs-12"><h4>记录不存在或没有访问权限</div>';
	return;
}
?>
<div class="col-xs-12 ebtw-horizontal-nopadding sidepage-main-content mCustomScrollbar" data-mcs-theme="dark-3">
	<div id="properties-content" class="report-details-container">
	</div>
	<!-- 日志面板 -->
	<div class="col-xs-12">
		<div class="col-xs-12 sidepage-tab-wrap">
			<div class="col-xs-12 sidepage-tab">
				<div class="sidepage-tab-head" id="sidepage-tab11"><span>内容(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab2"><span>评论/回复(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab1"><span>评阅(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<span class="sidepage-tab-head-divide">|</span>
				<div class="sidepage-tab-head" id="sidepage-tab3"><span>自动汇报(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab4" data-auto-report="1"><span>附件(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab20"><span>操作日志(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="ebtw-clear"></div>
			</div>
			<div id="sidepage-tab-content" class="col-xs-12 ebtw-embed-row"></div>
		</div>
	</div>
</div>
<input type="hidden" id="property-content-height-input" value="0">
<input type="hidden" id="content-height-input" value="0" >
<script type="text/javascript">
var viewMode = '<?php echo $viewMode;?>';
var ptrType = <?php echo $PTRType;?>;

<?php if (isset($entity)) {?>
var ptrId = '<?php echoField($entity, $PTRIdFieldName);?>';
var reportUid = '<?php echoField($entity, 'report_uid');?>';
var createName = '<?php echoField($entity, 'create_name');?>';
var startTime = '<?php echoField($entity, 'start_time');?>';
var stopTime = '<?php echoField($entity, 'stop_time');?>';

var allowedActions = <?php echoField($entity, 'allowedActions', '[]');?>; //操作权限
var approvalUserStr = '<?php echoShares($entity, 1, true);?>'; //获取当前用作为评阅人的资料
var approvalUser = json_parse(approvalUserStr.length==0?'""':approvalUserStr);

var TabBadgesDatas = {
		11:{activeNo:11, ptr_id:ptrId},
		2:{activeNo:2, ptr_id:ptrId},		
		1:{activeNo:1, ptr_id:ptrId},
		3:{activeNo:3, report_id:ptrId, daily:1, request_query_type:7, request_for_count:1, report_uid:reportUid, start_time:startTime, stop_time:stopTime},
		4:{activeNo:4, ptr_id:ptrId, from_type:10 + parseInt(ptrType), flag:-1, get_summary:1},
		20:{activeNo:20, ptr_id:ptrId},
	};
<?php } else {?>
var ptrId = '<?php echo $_REQUEST['report_id'];?>';
var reportUid ='<?php echo $userId;?>';
var reportAccount = '<?php echo $userAccount;?>';
var createName = '<?php echo $userName;?>';
var startTime = '<?php echo substr($startTime, 0, 19);?>';
var stopTime = $.D_ALG.formatDate(new Date(startTime), 'yyyy-mm-dd 23:59:59');
var TabBadgesDatas = {
		11:{activeNo:11, execute:false},
		2:{activeNo:2, execute:false},		
		1:{activeNo:1, execute:false},
		3:{activeNo:3, daily:1, request_query_type:7, request_for_count:1, report_uid:reportUid, start_time:startTime, stop_time:stopTime},
		4:{activeNo:4, execute:false},
		20:{activeNo:20, execute:false},
	};
<?php }?>

var $sidepageContainer = $('.sidepage-tab-wrap>#sidepage-tab-content');

$(document).ready(function() {
	var $propertyContainer = $('.sidepage-main-content');
	var $container = $('.report-details-container');
	
<?php if (isset($entity)) {?>
	//创建工具栏
	createSideToolbarButtons(ptrType, <?php echoField($entity, "status");?>, allowedActions, false, {1:{reserved_from_view_page:1}, 2:{reserved_from_view_page:1}, 3:{}}, (viewMode=='e'||viewMode=='a'));

    //注册事件-点击工具栏按钮
    registerSideToolbarActions('<?php echo $userId;?>', ptrType, ptrId, function(affected, actionType) {
        //删除操作
		if (parseInt(affected)>0 && actionType==3) {
			//删除相应资源文件
			var $fileElements = $container.find('.ebtw-file-upload-list li');
			$fileElements.each(function() {
				var resourceId = $(this).attr('data-resource-id');
				$.ebtw.deletefile(resourceId, function(result){
					logjs_err('deletefile success, resource_id='+resourceId);
					}, function(err){
						logjs_err('deletefile error, resource_id='+resourceId);
					});
			});
			
			//更新列表视图
			var $listContainer = $('.report-list-container');
			if ($listContainer.length>0) {
				var $listForm = $listContainer.find('.report-list-row[data-ptrid="'+ptrId+'"] form');
				var $listReportIdElement = $listForm.find('input[name="pk_report_id"]');
				var closeTitle = '删除日报成功';
				var checkClose = null;
				//在列表视图删除一行记录
				reloadReportListRow('<?php echo $userId;?>', true, $listForm, $listContainer, $listReportIdElement, ptrId, ptrId, closeTitle, function(closeTitle, close){
						layer.msg(closeTitle);
					});
			} else {
				checkClose(closeTitle, true);
			}
		}
    });
	
	var data = json_parse('<?php echo escapeQuotes(strictJson(json_encode($entity)));?>');
<?php
	$reviewUser = getShares(1, $entity, true);
	if (!empty($reviewUser) && $reviewUser->share_uid==$userId) {
		if ($entity->status==1) { //评阅中
			if ($reviewUser->read_flag==0) { //未阅
				?>
            	//更新已读状态，并刷新视图
            	markReadFlagToRead(ptrType, ptrId, '<?php echoField($reviewUser, 'share_id');?>', function(result) {
            		var readTime = $.D_ALG.formatDate(new Date(), 'yyyy-mm-dd hh:ii');
            		$propertyContainer.find('#report_review_user.ebtw-color-unread').removeClass('ebtw-color-unread').addClass('ebtw-color-already-read').html(readTime+' 评阅人已阅');
            		if (typeof clearMarkReadElement=='function')
            			clearMarkReadElement(ptrType, ptrId, '<?php echoField($reviewUser, 'share_id');?>');
            	}, function(err) {
                	//do nothing
            	});
            <?php
            }
	    }
	}
} else {?>
	var data = createEmptyReportData(reportUid, reportAccount, createName, new Date(startTime), ptrId);
	var allowedActions = [];
<?php }?>
	
	createReportListRow('<?php echo $userId;?>', $container, ptrType, data, viewMode=='v', true);
	
	if (viewMode=='v') {
		//查询附件数量
		setTimeout(function(){
			var fromType = 10+3; //报告相关附件
			var flag = 0; //文档附件
			var getSummary = 1; //仅获取数量
			$.ebtw.listfile(fromType, ptrId, flag, getSummary, null, null, function(result) {
				if (result.code=='0') {
					//有附件才显示附件栏
					if (result.count>0) {
						$container.find('.waitting-for-show').removeClass('ebtw-hide'); //显示附件栏
						$(window).trigger('resize'); //触发窗口缩放事件，让窗口再次自适应高度
					}
				}
			});
		}, 100);
	}
	
	//绑定点击关联人员事件
	$container.on('click', 'input[name="review_user_name"][data-toggle="dropdown"]:not(.disable-change)', function() {
		$(this).parents('.content-box').find('.content-box-toolbar .btn-edit:not(.ebtw-hide)').trigger('click', this);
		
		//本元素只接受一次click事件
		//$(this).unbind('click');
		var alreadyOpened = $(this).attr('data-already-opened');
		$(this).attr('data-already-opened', 1);

		if (alreadyOpened!=1) {
			var ptrId = $(this).parents('.report-list-row').attr('data-ptrid');
			//生成备选评阅人下拉菜单
			createApprovalUserList('.report-list-row[data-ptrid="'+ptrId+'"] .review_user', 'review', '--请选择评阅人--', function(event, oldUserId, oldUserName, $userIdElement, $userNameElement) {
//					if ($userIdElement.val()!=oldUserId)
//						$userIdElement.trigger('change');
			});
		}
	});
	
	//绑定点击上传附件按钮事件
	$container.on('click', '.content-box-row .ebtw-file-upload-wrap', function() {
		$(this).parents('.content-box').find('.content-box-toolbar .btn-edit:not(.ebtw-hide)').trigger('click', this);
	});

	//content-box自适应宽度
	var $elements = $container.find('>.report-list-row .report-list-item .content-box');
	$elements.css('width', $elements.parent().width()-5);
	$(window).resize(function(e){
		var $elements = $container.find('>.report-list-row .report-list-item .content-box');
		$elements.css('width', $elements.parent().width()-5);
	});	
	
	//更新tab标签内容数量
	refreshTabBadges('report_0', 'sidepage-tab', [
		TabBadgesDatas[11], //内容
		TabBadgesDatas[2], //评论
		TabBadgesDatas[1], //评阅
		TabBadgesDatas[3], //自动汇报
		TabBadgesDatas[4], //附件
		TabBadgesDatas[20], //操作日志
	]);
	
	//注册tab标签并设置默认选中
	var defaultActiveNo = <?php echo get_request_param('reserved_active_no', '11')?>; //默认激活标签编号
	if (viewMode=='a')
		defaultActiveNo = 3;
	
	registerTab(getServerUrl() + "report/sidepage_tab_conetnt.php", 'sidepage', defaultActiveNo, 'small', null, null, function(activeNo) {
		var orderBy = 'create_time';
		var param = {tab_type:activeNo, from_type:ptrType, from_id:ptrId, pageSize:defaultPageSize};
		switch(parseInt(activeNo)) {
		case 11:
			$.extend(param, {op_type_class:11, is_deleted:0, request_order_by:orderBy});
			break;
		case 2:
			$.extend(param, {op_type_class:1, op_type:3, is_deleted:0, request_order_by:orderBy});
			break;			
		case 1:
			$.extend(param, {op_type_class:3, is_deleted:0, request_order_by:orderBy});
			break;
		case 3:
			param = {tab_type:activeNo, is_deleted:0, request_order_by:orderBy, pageSize:defaultPageSize, user_id:reportUid, start_time:startTime, stop_time:stopTime};
			break;
		case 4: //附件列表
			param.from_type = 10 + parseInt(ptrType);
			param.flag = null; //加载普通附件和评论(回复)附件
			param.get_summary = 0; //获取记录列表
			break;
		case 20:
			$.extend(param, {op_type_class:0, is_deleted:0, request_order_by:orderBy});
			break;			
		}
		return param;
	},
	null,
	{4: function(activeNo, url, param, successHandle, errorHandle) { //附件列表
		if (parseInt(param.from_id)>0) {
			//加载资源列表
			$.ebtw.listfile(param.from_type, param.from_id, param.flag, param.get_summary, null, null, function(result) {
				if (result.code=='0') {
					if (successHandle) {
						var datas = '""';
						if (Object.prototype.toString.call(result.resources) === '[object Array]') {
							var datas = result.resources;
							successHandle(datas);
						} else {
							successHandle(datas);
						}

						var ptrId = param.from_id;
						var ptrType = parseInt(param.from_type);
						ptrType = (ptrType>10 && ptrType<20)?(ptrType-10):ptrType;
						var allowedActionsDict = {singlePtr:true, ptrId:ptrId, ptrType:ptrType};
						allowedActionsDict[ptrId] = allowedActions;
						
						loadSidepageTabData('<?php echo $userId;?>', reportUid, allowedActionsDict, null, $sidepageContainer, param.from_type, SideTabTypes['att'], datas, '<?php echo $ROOT_URL;?>');
						if (typeof registerSomeEvent=='function')
							registerSomeEvent();
					}
				} else {
					if (errorHandle)
						errorHandle(result);
				}
			});
		} else {
			var datas = new Array();
			if (successHandle) {
				successHandle(datas);
			}
			
			var ptrId = param.from_id;
			var ptrType = parseInt(param.from_type);
			ptrType = (ptrType>10 && ptrType<20)?(ptrType-10):ptrType;
			var allowedActionsDict = {singlePtr:true, ptrId:ptrId, ptrType:ptrType};
			allowedActionsDict[ptrId] = allowedActions;
			
			loadSidepageTabData('<?php echo $userId;?>', reportUid, allowedActionsDict, null, $sidepageContainer, param.from_type, SideTabTypes['att'], datas, '<?php echo $ROOT_URL;?>');
			if (typeof registerSomeEvent=='function')
				registerSomeEvent();
		}
	}});

	//属性容器自适应高度
	function calculatePropertyContentHeight() {
		$('#property-content-height-input').val($('body').outerHeight()-$('.side-toolbar').outerHeight());
	}
	$(window).bind('resize', function(e) {
		calculatePropertyContentHeight();
	});
	calculatePropertyContentHeight();
	adjustContainerHeight3UsingE($('#property-content-height-input'), $propertyContainer, 10, true, 'resize_property_container');
	
	//自定义滚动条
	customScrollbarUsingE($propertyContainer, 30, true);
	
// 	//div自适应高度
// 	$propertyContainer.find('div.edit-e').autoHeight(function(oldH, newH) {
// 		if (oldH != newH) {
// 			logjs_info($("#mCSB_3_container").parent().scrollTop());
// 			//customScrollbarUsingE($propertyContainer, 30, true);
// 		}
// 	});
	
	//定义函数：计算已占用高度
	function calculateRootHeight() {
		var rootHeight = $('.side-toolbar').outerHeight(true) + $('#properties-content').outerHeight(true)+$('.sidepage-tab').outerHeight(true)
			+($sidepageTabWrap.outerHeight(true)-$sidepageTabWrap.height()-1) //.sidepage-tab-wrap的border+padding+margin高度
			+$sidepageDiv.outerHeight(true)-$sidepageDiv.height() //#sidepage border+padding+margin高度
			+$listRow.outerHeight(true)-$listRow.height()
			//+$divideDiv.outerHeight(true)-$divideDiv.height()
			+6; //距离底部高度
		return rootHeight;
	}
	//计算并保存列表区域高度
	$sidepageDiv = $('#sidepage');
	$listRow = $sidepageDiv.find('.report-list-row');
	$sidepageTabWrap = $sidepageDiv.find('.sidepage-tab-wrap');
	//$divideDiv = $sidepageDiv.find('.div-divide-all:eq(0)');
	var value = registerCalculateAdjustContainerHeight3($('#content-height-input'), $sidepageDiv, calculateRootHeight(), calculateRootHeight);

	//注册事件-点击分隔线
	bindStretchClick($('#divide-0'), $('#divide-0').parent().next());

	//注册事件-点击查看(计划、任务)
	registerClickOpenPtr($sidepageContainer);
	
	//注册事件-点击切换至"评阅"标签页
	$container.on('click', 'button.switch-to-approval', function() {
		$(this).parents('.report-details-container').parent().find('.sidepage-tab-head#sidepage-tab1').trigger('click');
	});
	
	//注册事件-保存按钮
	$('.side-save').click(function() {
		var isEdit = (viewMode=='e');
		var $form = $container.find('form');
		var $detailDivs = $form.find('.content-box-row .edit-e')
		
		//准备要保存的值
		$detailDivs.each(function(){
			$(this).prev('input[type="hidden"]').val(convertHtmlToTxt(html_decode($(this).html())));//convertHtmlToTxt($(this).html()));
		});

// 		//已完成工作
// 		var completedWork = $form.find('input[type="hidden"][name="completed_work"]').val();
// 		if (!checkContentLength(ptrType, 'completed_work', completedWork && completedWork.trim()))
// 		    return false;
// 		//未完成工作
// 		var uncompletedWork = $form.find('input[type="hidden"][name="uncompleted_work"]').val();
// 		if (!checkContentLength(ptrType, 'uncompleted_work', uncompletedWork && uncompletedWork.trim()))
// 		    return false;
		
		//执行保存
		saveReportAction('<?php echo $userId;?>', isEdit, $form, $container, ptrType, null, function() {
			closeSidepage();

			if (typeof reloadCurrentBoardLane=='function') {
				reloadCurrentBoardLane();
			}
		}, (typeof isWorkbench=='undefined' || isWorkbench==false)?true:false);
	});

	//注册事件-取消按钮
	$('.side-cancel').click(function() {
		var fromViewPage = 0;
		<?php if ($viewMode=='e') {?>
		var fromViewPage = <?php echo $fromViewPage;?>;
		<?php }?>
		
		if (fromViewPage==0)
			closeSidepage();
		else {
			openReportById('daily', 'v', ptrId, null, {});
		}
	});
});
</script>
