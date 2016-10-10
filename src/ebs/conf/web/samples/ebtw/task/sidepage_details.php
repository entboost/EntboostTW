<?php
include dirname(__FILE__).'/../task/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../task/include.php';
$embed = 1;
include dirname(__FILE__).'/../task/get_one.php';
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
	    <div id="title" class="ebtw-details-title col-xs-12"><?php echoField($entity, "task_name");?></div>
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
		        <div class="col-xs-11 ebtw-embed-row">
		            <span class="ebtw-attr-name">时段</span>
		            <span class="ebtw-attr-content"><?php echo subStrOfDateTime2($entity, "start_time");?> ~ <?php echo subStrOfDateTime2($entity, "stop_time");?></span>
		            <span class="ebtw-attr-content remain-time">&nbsp;剩余</span>
		            <span class="ebtw-attr-content not-achieve-start-time ebtw-hide ebtw-color-warning-1">&nbsp;任务未到开始时间</span>
		            <span class="ebtw-attr-content expire-stop-time ebtw-hide ebtw-color-warning-2">&nbsp;任务过期未完成</span>
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
		            <?php if ($entity->create_uid!=$userId) {?>
		            	<span class="talk-to-person" data-talk-to-uid='<?php echo $entity->create_uid;?>' title='<?php if (!empty($entity->create_account)) echo "$entity->create_account($entity->create_uid)";?>'><?php echoField($entity, "create_name");?></span>
		            <?php } else {?>
		            	<?php echoField($entity, "create_name");?>
		            <?php }?>
		        </div>
		        <?php $principalUser = getShares(5, $entity, true);?>
		        <div class="col-xs-6 ebtw-embed-row">
		            <span class="ebtw-attr-name">负责人</span>
		        	<?php if ($principalUser && $principalUser->share_uid!=$userId) {?>
		            	<span class="ebtw-attr-content talk-to-person" data-talk-to-uid='<?php echo $principalUser->share_uid;?>' title='<?php echo "$principalUser->user_account($principalUser->share_uid)";?>' id="task_charge_user">
		            <?php } else {?>
		            	<span class="ebtw-attr-content" id="task_charge_user">
		            <?php }?>		            
		            <?php
		            	if (!empty($principalUser)) {
			            	echoField($principalUser, "share_name");
			            	if ($principalUser->share_uid==$userId||$entity->create_uid==$userId) { //仅提交人和负责人可见此状态
			            		echo '&nbsp;';
			            		if ($principalUser->read_flag==0) { //未阅
			            			echo '<span class="ebtw-color-unread"> 负责人未阅</span>';
			            			if ($principalUser->share_uid==$userId) { //当前登录用户是负责人
			            			?>
            						<script type="text/javascript">
            						//更新已读状态，并刷新视图
            						markReadFlagToRead(ptrType, ptrId, '<?php echoField($principalUser, 'share_id');?>', function(result) {
            							var readTime = $.D_ALG.formatDate(new Date(), 'yyyy-mm-dd hh:ii');
            							$('#task_charge_user .ebtw-color-unread').after('<span class="ebtw-color-already-read">： '+readTime+' 负责人已阅</span>').remove();
            							if (typeof clearMarkReadElement=='function')
            								clearMarkReadElement(ptrType, ptrId, '<?php echoField($principalUser, 'share_id');?>');
            						}, function(err) {
                						//do nothing
            						});
            						</script>
			            			<?php
			            			}
			            		} else { //已阅
			            			echo '<span class="ebtw-color-already-read">： '.subStrOfDateTime2($principalUser, 'read_time').' 负责人已阅</span>';
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
				<div class="sidepage-tab-head" id="sidepage-tab3"><span>评论/回复(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab1"><span>进度(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab2"><span>工时(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab4"><span>成员(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab5"><span>关注(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<!-- 
				<div class="sidepage-tab-head" id="sidepage-tab6"><span>子任务(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<div class="sidepage-tab-head" id="sidepage-tab7"><span>父任务(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				 -->
				<div class="sidepage-tab-head" id="sidepage-tab8"><span>关联计划(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
				<span class="sidepage-tab-head-divide">|</span>
				<div class="sidepage-tab-head" id="sidepage-tab9"><span>附件(<span class="scount">0</span>)</span><span class="sidepage-tab-select"></span></div>
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
var userId = '<?php echo $userId;?>';
var taskCreateUid = '<?php echoField($entity, 'create_uid');?>';
var status = <?php echoField($entity, 'status');?>;
var percentage = <?php echoField($entity, 'percentage', '0');?>; //当前进度
var allowedActions = <?php echoField($entity, 'allowedActions', '[]');?>; //操作权限
<?php 
//"开放所有人"的任务允许任何人关注
if (!empty($entity) && $entity->open_flag==1) {
?>
	if ($.inArray(13, allowedActions)<0)
		allowedActions.push(13);
<?php
}
?>
	
var defaultActiveNo = <?php echo get_request_param('reserved_active_no', '11')?>; //默认激活标签编号

var TabBadgesDatas = {
	11:{activeNo:11, ptr_id:ptrId}, //内容
	3:{activeNo:3, ptr_id:ptrId}, //评论/回复
	1:{activeNo:1, ptr_id:ptrId}, //进度
	2:{activeNo:2, ptr_id:ptrId}, //工时
	4:{activeNo:4, ptr_id:ptrId}, //成员
	5:{activeNo:5, ptr_id:ptrId}, //关注
	8:{activeNo:8, ptr_id:ptrId}, //关联计划
	9:{activeNo:9, ptr_id:ptrId, from_type:10 + parseInt(ptrType), flag:null, get_summary:1}, //附件
	20:{activeNo:20, ptr_id:ptrId}, //操作日志
};

var $sidepageContainer = $('.sidepage-tab-wrap>#sidepage-tab-content');

<?php
//设置 参与人(2)、共享人(3)、关注人(4)已读标识
$shareTypes = array(2,3,4);
foreach ($shareTypes as $shareType) {
	$shareUser = getShares($shareType, $entity, true, $userId);
	if (!empty($shareUser) && ($shareUser->read_flag==0)) { //未阅
	?>
		markReadFlagToRead(ptrType, ptrId, '<?php echoField($shareUser, 'share_id');?>'); //更新已读状态
	<?php 
	}
}?>

$(document).ready(function() {
	var $propertyContainer = $('.sidepage-property-container');
	
	//标题栏
	var status = <?php echoField($entity, "status");?>;
	//var $sideToolbarStatus = $('.side-toolbar-status');
	$('.side-toolbar-status').append(dictStatusOfTask[status]).css('color', dictStatusColorOfTask[status])
	.append('&emsp;进度 '+<?php echoField($entity, "percentage");?>+'%')
	.append('&emsp;总耗时 '+formatMinutesToHours(<?php echoField($entity, "work_time");?>)+'小时');
	
	//获取当前用户是否已关注本任务
	<?php 
		$favoriteUser = getShares(4, $entity, true, $userId, 1);
		if (!empty($favoriteUser))
			echo 'var alreadyFavorite = 1;';
		else 
			echo 'var alreadyFavorite = 0;';
	?>
	//创建工具栏
	createSideToolbarButtons(ptrType, <?php echoField($entity, "status");?>, allowedActions, null, {2:{reserved_from_view_page:1}, 13:{already_favorite:alreadyFavorite}});
	
    //注册事件-点击工具栏按钮
    registerSideToolbarActions('<?php echo $userId;?>', ptrType, ptrId);
	
	//剩余时间
	var remainTime = calculateRemainTimeToPopular(new Date('<?php echoField($entity, "stop_time");?>'));
	if (remainTime=='0')
		$('.remain-time').html('&emsp;已到期');
	else
		$('.remain-time').append(remainTime.replace(/(\d+)+/ig, '<span class="ebtw-color-warning-2">$1</span>'));
	//任务未到开始时间
	var startTime = new Date('<?php echoField($entity, "start_time");?>');
	var stopTime = new Date('<?php echoField($entity, "stop_time");?>');
	var status = <?php echoField($entity, "status");?>;
	var now = new Date();
	if (startTime.getTime()>now.getTime())
		$('.not-achieve-start-time').removeClass('ebtw-hide');
	//任务过期未完成
	if (status<=2 && now.getTime()>stopTime.getTime())
		$('.expire-stop-time').removeClass('ebtw-hide');
	
    //重要程度
    var important = <?php echoField($entity, "important"); ?>;
    $('.important-value').html(dictImportant[important]).addClass(dictImportantCss[important].color);
    //分类
    $('.class-value').html(dictClassNameOfTask['<?php echoField($entity, "class_id"); ?>']);
    //开放属性
    $('.open_flag-value').html(dictOpenFlag[<?php echoField($entity, "open_flag"); ?>]);
	
	//更新tab标签内容数量
	refreshTabBadges('task_0', 'sidepage-tab', [
		TabBadgesDatas[11],
		TabBadgesDatas[3],
		TabBadgesDatas[1],
		TabBadgesDatas[2],
		TabBadgesDatas[4],
		TabBadgesDatas[5],
		TabBadgesDatas[8],
		TabBadgesDatas[9],
		TabBadgesDatas[20],
	]);
	
	//注册tab标签并设置默认选中
	registerTab(getServerUrl() + "task/sidepage_tab_conetnt.php", 'sidepage', defaultActiveNo, 'small', null, null, function(activeNo) {
		var param;
		var tabType = parseInt(activeNo);
		switch(tabType) {
		case 11:case 20:case 1:case 2:case 3:
			param = {tab_type:activeNo, from_type:ptrType, from_id:ptrId, op_type_class:0, is_deleted:0, request_order_by:'create_time', pageSize:defaultPageSize};
			if (tabType==1) {
				param.op_type = [31,34];
				param.request_order_by = 'create_time desc';
			} else if (tabType==2) {
				param.op_type = 32;
				param.request_order_by = 'create_time desc';
			} else if (tabType==3) {
				param.op_type_class = 1;
				param.op_type = 3
			} else if (tabType==11) {
				param.op_type_class = 11;
			};
			break;
		case 4:case 5:
			param = {tab_type:activeNo, from_type:ptrType, from_id:ptrId, share_type:[2,3], valid_flag:1, request_order_by:'share_name', pageSize:defaultPageSize};
			if (tabType==5)
				param.share_type = [4];
			break;
		case 8:
			param = {tab_type:activeNo, task_id:ptrId, request_query_type:7, is_deleted:0, request_order_by:'create_time', pageSize:defaultPageSize};
			break;
		case 9:
			param = {tab_type:activeNo, from_type:10 + parseInt(ptrType), from_id:ptrId, flag:null, get_summary:0, pageSize:defaultPageSize};
			break;
		}
		return param;
	},
	null,
	{9: function(activeNo, url, param, successHandle, errorHandle) { //附件列表
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

					//补全关联用户资料
					for (var i=0; i<datas.length; i++) {
						var data = datas[i];
						data.shares = json_parse('<?php echoField($entity, "shares", "");?>');
					}
					
					loadSidepageTabData(userId, taskCreateUid, allowedActionsDict, null, $sidepageContainer, param.from_type, SideTabTypes['att'], datas, '<?php echo $ROOT_URL;?>');
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
