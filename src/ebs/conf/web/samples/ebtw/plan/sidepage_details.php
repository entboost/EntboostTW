<?php
include dirname(__FILE__).'/../plan/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../plan/include.php';
$embed = 1;
include dirname(__FILE__).'/../plan/get_one.php';
?>

<div class="side-toolbar col-xs-12">
        <div class="side-toolbar-icon side-toolbar-status">
            <span class="glyphicon glyphicon-eye-open"></span> <!--状态预留位置-->
        </div>
        <div class="side-close">
            <span class="glyphicon glyphicon-remove" title="关闭"></span>
        </div>
        <div class="side-fullscreen" data-type="0">
            <span class="glyphicon glyphicon-fullscreen" title="全屏"></span>
        </div>
</div>

<?php 
if (!isset($entity)) {
	echo '<div class="col-xs-12"><h4>记录不存在或没有访问权限</div>';
	return;
}

$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
?>
<script type="text/javascript">
var ptrType = <?php echo $PTRType;?>;
var ptrId = '<?php echoField($entity, $PTRIdFieldName);?>';
</script>

<div class="col-xs-12 ebtw-horizontal-nopadding sidepage-property-container mCustomScrollbar" data-mcs-theme="dark-3">
	<div id="properties-content">
	   	<!-- 标题 -->
	    <div id="title" class="ebtw-details-title col-xs-12 <?php if ($entity->is_deleted==1) echo 'ebtw-txt-deleted';?>"><?php echoField($entity, "plan_name");?></div>
	    <!-- 内容 -->
	    <div class="ebtw-details-content ebtw-details-row col-xs-12">
	       	<span><?php echo controlCharactersToHtml($entity->remark);?></span>
	    </div>
	    <!-- 创建、最后修改时间 -->
		<div class="col-xs-12 ebtw-details-row">
	        <div class="col-xs-3 ebtw-embed-row">
	            <span class="ebtw-attr-title-s"><?php echo subStrOfDateTime2($entity, "create_time");?></span>
	        </div>
	        <?php if (!empty($entity->last_modify_time)) {?>
	        <div class="col-xs-5 ebtw-embed-row">
	            <span class="ebtw-attr-title-s line-right">最后修改：<?php echo subStrOfDateTime2($entity, "last_modify_time");?></span>
	        </div>
	        <?php }?>
		</div>
	    <!-- 分隔线 -->
		<div class="col-xs-12 div-divide-top-pull">
			<div class="divide-line col-xs-5"></div>
			<div class="col-xs-2 divide-text" id="divide-0" onselectstart="javascript:return false;">分隔线&nbsp;<span class="glyphicon glyphicon-chevron-up"></span></div>
			<div class="divide-line col-xs-5"></div>
		</div>
	    <!-- 下一模块 -->
	    <div class="col-xs-12 ebtw-details-module">
		    <div class="col-xs-12 ebtw-details-row">
		        <div class="col-xs-5 ebtw-embed-row">
		            <span class="ebtw-attr-name">计划周期</span>
		            <span class="ebtw-attr-content period-value"></span>
		        </div>
		        <div class="col-xs-6 ebtw-embed-row">
		            <span class="ebtw-attr-name">时段</span>
		            <span class="ebtw-attr-content">
		            	<?php
		            		if ($entity->status<=1 && strtotime($entity->start_time)<time())
		            			$warningOfNotStart = true;
		            		if ($entity->status!=6 && strtotime($entity->stop_time)<time())
		            			$warningOfExpired = true;
		            		
		            		if (!empty($warningOfNotStart) || !empty($warningOfExpired)) {
		            			echo '<span class="';
		            			if (!empty($warningOfExpired))
		            				echo 'ebtw-color-warning-2';
		            			else if (!empty($warningOfNotStart))
		            				echo 'ebtw-color-warning-1';
		            			echo '">';
		            		}
		            		echo subStrOfDateTime($entity, "start_time");
		            	?> ~ <?php 
		            		echo subStrOfDateTime($entity, "stop_time");
		            		if (!empty($warningOfNotStart) || !empty($warningOfExpired)) {
		            			if (!empty($warningOfExpired))
		            				echo ' (逾期未完成)';
		            			else if (!empty($warningOfNotStart))
		            				echo ' (未开始)';
		            			echo '</span>';		
		            		}
		            	?>
		            </span>
		        </div>
		    </div>
			
		    <div class="col-xs-12 ebtw-details-row">
		        <div class="col-xs-5 ebtw-embed-row">
		            <span class="ebtw-attr-name">重要程度</span>
		            <span class="ebtw-attr-content important-value"></span>
		        </div>
		        <div class="col-xs-6 ebtw-embed-row">
		            <span class="ebtw-attr-name">分类</span>
		            <span class="ebtw-attr-content class-value"></span>
		        </div>
		    </div>
		    
			<div class="col-xs-12 ebtw-details-row">
		        <div class="col-xs-5 ebtw-embed-row">
		            <span class="ebtw-attr-name">创建人</span>
		            <span class="ebtw-attr-content">
		            <?php if ($entity->create_uid!=$userId) {?>
		            	<span class="talk-to-person" data-talk-to-uid='<?php echo $entity->create_uid;?>' title='<?php if (!empty($entity->create_account)) echo "$entity->create_account($entity->create_uid)";?>'><?php echoField($entity, "create_name");?></span>
		            <?php } else {?>
		            	<?php echoField($entity, "create_name");?>
		            <?php }?>
		            </span>
		        </div>
		        <?php $approvalUser = getShares(1, $entity, true);?>
		        <div class="col-xs-6 ebtw-embed-row">
		        	<span class="ebtw-attr-name">评审人</span>
		        	<?php if ($approvalUser && $approvalUser->share_uid!=$userId) {?>
		            	<span class="ebtw-attr-content talk-to-person" data-talk-to-uid='<?php echo $approvalUser->share_uid;?>' title='<?php echo "$approvalUser->user_account($approvalUser->share_uid)";?>' id="plan_approval_user">
		            <?php } else {?>
		            	<span class="ebtw-attr-content" id="plan_approval_user">
		            <?php }?>
		            <?php
		            	if (!empty($approvalUser)) {
			            	echoField($approvalUser, "share_name");
			            	if ($entity->status==2 && ($approvalUser->share_uid==$userId||$entity->create_uid==$userId)) { //评审中，仅提交评审的用户和评审人可见此状态
			            		echo '&nbsp;';
			            		if ($approvalUser->read_flag==0) { //未阅
			            			echo '<span class="ebtw-color-unread"> 评审人未阅</span>';
			            			if ($approvalUser->share_uid==$userId) { //当前登录用户是评审人
			            			?>
            						<script type="text/javascript">
            						//更新已读状态，并刷新视图
            						markReadFlagToRead(ptrType, ptrId, '<?php echoField($approvalUser, 'share_id');?>', function(result) {
            							var readTime = $.D_ALG.formatDate(new Date(), 'yyyy-mm-dd hh:ii');
            							$('#plan_approval_user .ebtw-color-unread').after('<span class="ebtw-color-already-read"> '+readTime+' 已阅</span>').remove();
            							if (typeof clearMarkReadElement=='function')
            								clearMarkReadElement(ptrType, ptrId, '<?php echoField($approvalUser, 'share_id');?>');
            						}, function(err) {
                						//do nothing
            						});
            						</script>
			            			<?php
			            			}
			            		} else { //已阅
			            			echo '<span class="ebtw-color-already-read"> '.subStrOfDateTime2($approvalUser, 'read_time').' 已阅</span>';
			            		}
			            	}
		            	}
		            ?></span>
		        </div>
		    </div>
		    
		    <div class="col-xs-12 ebtw-details-row">
		    	<div class="col-xs-5 ebtw-embed-row">
		    		<span class="ebtw-attr-name">开放对象</span>
		    		<span class="ebtw-attr-content open_flag-value"></span>
		    	</div>
		    </div>
	    </div>
	    <div class="ebtw-clear"></div>
    </div>
	<!-- 日志面板 -->
	<div class="col-xs-12">
		<div class="col-xs-12 sidepage-tab-wrap">
			<div class="col-xs-12 sidepage-tab">
				<div class="sidepage-tab-head" id="sidepage-tab11"><span>内容(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab2"><span>评论/回复(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab1"><span>评审(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab3"><span>关联任务(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab4"><span>共享(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<span class="sidepage-tab-head-divide">|</span>
				<div class="sidepage-tab-head" id="sidepage-tab5"><span>附件(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab20"><span>操作日志(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="ebtw-clear"></div>
			</div>
			<div id="sidepage-tab-content" class="col-xs-12 ebtw-embed-row"></div>
		</div>
	</div>
</div>
<input type="hidden" id="property-content-height-input" value="0">
<input type="hidden" id="content-height-input" value="0">
<script type="text/javascript">
var planCreateUid = '<?php echoField($entity, 'create_uid');?>';
var isDeleted = '<?php echoField($entity, 'is_deleted');?>';
var allowedActions = <?php echoField($entity, 'allowedActions', '[]');?>; //操作权限
var approvalUserStr = '<?php echoShares($entity, 1, true);?>'; //获取当前用作为评审人的资料
var approvalUser = json_parse(approvalUserStr.length==0?'""':approvalUserStr);
var defaultActiveNo = <?php echo get_request_param('reserved_active_no', '11')?>; //默认激活标签编号

var TabBadgesDatas = {
	11:{activeNo:11, ptr_id:ptrId},
	2:{activeNo:2, ptr_id:ptrId},
	1:{activeNo:1, ptr_id:ptrId},
	3:{activeNo:3, ptr_id:ptrId},
	4:{activeNo:4, ptr_id:ptrId},
	5:{activeNo:5, ptr_id:ptrId, from_type:10 + parseInt(ptrType), flag:null, get_summary:1},
	20:{activeNo:20, ptr_id:ptrId},	
};

var $sidepageContainer = $('.sidepage-tab-wrap>#sidepage-tab-content');

<?php
//设置共享人已读标识
$shareType = 3; //共享人
$shareUser = getShares($shareType, $entity, true, $userId);
if (!empty($shareUser) && ($shareUser->read_flag==0)) { //未阅
?>
	//更新已读状态，并刷新视图
	markReadFlagToRead(ptrType, ptrId, '<?php echoField($shareUser, 'share_id');?>', function(result) {
		refreshPTRMenuBadges([3], ptrType); //刷新菜单角标(badge)
	});
<?php }

//“新建未阅”状态改变为“未处理”状态
if ($entity->status==0 && $entity->create_uid==$userId) {
	$formObj = new EBPlanForm();
	$formObj->pk_plan_id = $entity->plan_id;
	$formObj->status = 1;
	$formObj->op_type = 200;
	
	$embed = 1;
	include dirname(__FILE__).'/../plan/saveUpdate.php';
}
?>

$(document).ready(function() {
	var $propertyContainer = $('.sidepage-property-container');
	
	//标题栏
	var status = <?php echoField($entity, "status");?>;
	var $sideToolbarStatus = $('.side-toolbar-status');
	$sideToolbarStatus.append(dictStatusOfPlan[status]).css('color', dictStatusColorOfPlan[status]);
	
	//创建工具栏
    var isDeleted = <?php echoField($entity, "is_deleted");?>;
	createSideToolbarButtons(ptrType, <?php echoField($entity, "status");?>, allowedActions, isDeleted, {2:{reserved_from_view_page:1}, 3:{deleted:isDeleted}});
    
    //注册事件-点击工具栏按钮
    registerSideToolbarActions('<?php echo $userId;?>', ptrType, ptrId);
	
	//周期
    $('.period-value').html(dictPeriodOfPlan[<?php echoField($entity, "period"); ?>]);
    //重要程度
    var important = <?php echoField($entity, "important"); ?>;
    $('.important-value').html(dictImportant[important]).addClass(dictImportantCss[important].color);
    //分类
    $('.class-value').html(dictClassNameOfPlan['<?php echoField($entity, "class_id"); ?>']);
    //开放属性
    $('.open_flag-value').html(dictOpenFlag[<?php echoField($entity, "open_flag"); ?>]);
	
	//更新tab标签内容数量
	refreshTabBadges('plan_0', 'sidepage-tab', [
		TabBadgesDatas[11], //内容
		TabBadgesDatas[2], //评论
		TabBadgesDatas[1], //评审
		TabBadgesDatas[3], //关联任务
		TabBadgesDatas[4], //共享(关联用户)
		TabBadgesDatas[5], //附件
		TabBadgesDatas[20], //操作日志		
	]);
	
	//注册tab标签并设置默认选中
	registerTab(getServerUrl() + "plan/sidepage_tab_conetnt.php", 'sidepage', defaultActiveNo, 'small', null, null, function(activeNo) {
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
			$.extend(param, {request_query_type:7, request_order_by:orderBy});
			break;
		case 4:
			$.extend(param, {share_type:3, valid_flag:1, request_order_by:'share_name'});
			break;
		case 5: //附件列表
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
	{5: function(activeNo, url, param, successHandle, errorHandle) { //附件列表
		//加载资源列表
		$.ebtw.listfile(param.from_type, param.from_id, param.flag, param.get_summary, null, null, function(result) {
			if (result.code=='0') {
				if (successHandle) {
					var datas = [];
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
					loadSidepageTabData('<?php echo $userId;?>', planCreateUid, allowedActionsDict, isDeleted, $sidepageContainer, param.from_type, SideTabTypes['att'], datas, '<?php echo $ROOT_URL;?>');
					if (typeof registerSomeEvent=='function')
						registerSomeEvent();
				}
			} else {
				if (errorHandle)
					errorHandle(result);
			}
		});
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
	
	//定义函数：计算已占用高度
	function calculateRootHeight() {
		var rootHeight = $('.side-toolbar').outerHeight(true) + $('#properties-content').outerHeight(true)+$('.sidepage-tab').outerHeight(true)
			+($sidepageTabWrap.outerHeight(true)-$sidepageTabWrap.height()-1) //.sidepage-tab-wrap的border+padding+margin高度
			+$sidepageDiv.outerHeight(true)-$sidepageDiv.height() //#sidepage border+padding+margin高度
			+6; //距离底部高度
		return rootHeight;
	}
	//计算并保存列表区域高度
	$sidepageDiv = $('#sidepage');
	$sidepageTabWrap = $('.sidepage-tab-wrap');
	registerCalculateAdjustContainerHeight3($('#content-height-input'), $sidepageDiv, calculateRootHeight(), calculateRootHeight);
	
	//注册事件-点击分隔线
	bindStretchClick($('#divide-0'), $('#divide-0').parent().next());

	//注册事件-点击查看(计划、任务)
	registerClickOpenPtr($sidepageContainer);
});
</script>
