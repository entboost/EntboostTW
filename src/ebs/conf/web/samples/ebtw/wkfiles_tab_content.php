<?php 
include dirname(__FILE__).'/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/include.php';	

	$output = true;
	
	//验证必填字段
	$tabType = get_request_param('tab_type');
	if (!isset($tabType)) {
		ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('tab_type', $output);
		return;
	}
	
	$embed = 1;
	if ($tabType==0 || $tabType==1 || $tabType==2 || $tabType==3 || $tabType==4 || $tabType==5 || $tabType==6) { //各种协同办公功能的附件(文件)
		//不通过php服务端，直接通过eb rest api获取
	}
	
// 	if (isset($json)) {
// 		$results = get_results_from_json($json, $tmpObj);
// 	} else {
		$results = null;
// 	}
 	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
?>
<div class="sidepage-tab-page-content">
	<div class="sidepage-tab-page-container mCustomScrollbar"  data-mcs-theme="dark-3">
		<div class="col-xs-12 sidepage-tab-page-header"></div>
		<!-- 预留位置 -->
		<div class="ebtw-clear"></div>
	</div>
</div>
<script type="text/javascript">
var resizeEventDiscuss2 = 'discuss2';
var registerSomeEventExecuted = false;
var tabType = <?php echo $tabType;?>;
var ptrId = 0;
var ptrType = 0;
var rootUrl = '<?php echo $ROOT_URL;?>';
var subTypeOfTabBadges = 'workbench_2';

//定义函数：计算已占用高度
function calculateRootHeight2() {
	var height = 0;	
// 	var $element2 = $contentContainer.find('.sidepage-tab-page-part2');
// 	var $element3 = $contentContainer.find('.sidepage-tab-page-part3');
	
// 	if ($element2.css('display')!='none')
// 		height += $element2.outerHeight(true);
// 	if ($element3.css('display')!='none')
// 		height += $element3.outerHeight(true);
	
	//避免第一次执行出现滚动条
	if (!registerSomeEventExecuted) {
		height += 20;
	}
	
	return height;
}

//定义函数：设置列表区域最大高度
function adjustSidepageTabPageContainerHeight(resizeEventSuffixName) {
	adjustContainerHeight3UsingE($('#wkfiles-content-height-input'), $contentContainer.find('.sidepage-tab-page-container'), calculateRootHeight2(), true, resizeEventSuffixName);
	registerSomeEventExecuted = true;
}

function registerSomeEvent() {
//	var executed = false;
	//textarea自适应高度
// 	$contentContainer.find('.sidepage-tab-page-discuss.discuss2>textarea').autoHeight(function(oldH, newH) {
// 		if (oldH!=newH) {
// 			executed = true;
// 			adjustSidepageTabPageContainerHeight(resizeEventDiscuss2);
// 		}
// 	});
	//防止第一次执行时初始值与新值相等而导致列表区域没有设置设置高度
//	if (!executed)
	
	adjustSidepageTabPageContainerHeight(resizeEventDiscuss2);
	
	//注册事件-点击日期标签 折叠/展开
	bindStretchClick($contentContainer.find('.date-mark'), null, function($This) {
		return $This.next().find("li>div")
	}, function($This, executeValues) {
		$This.parent().find('li:not(:first)').css('display', executeValues[0]);
	});
	
	//注册事件-标签页子项(编辑、删除等)管理操作
	registerSidepageTabItemActions($contentContainer, tabType, ptrId, ptrType, TabBadgesDatasOfWKFiles, subTypeOfTabBadges, 'wkfiles-tab');
	//注册事件-点击图片附件文件，自动打开浏览
	registerZoom('.attachment-link .open-resource[data-ext-type="2"]', 'data-open-url');
}

$(document).ready(function() {
	//自定义滚动条
	customScrollbarUsingE($contentContainer.find('.sidepage-tab-page-container'), 30, true);
	
	//加载标签页数据
	var tabTypeDict = {0:SideTabTypes['att'], 1:SideTabTypes['att'], 2:SideTabTypes['att'], 3:SideTabTypes['att'], 4:SideTabTypes['att']/*, 5:SideTabTypes['att']*/, 6:SideTabTypes['att']};
	
	if (!registerSomeEventExecuted)
		registerSomeEvent();
	
  	//注册事件-点击查看(计划、任务、报告)
    $contentContainer.off('click', '.ptr_item').on('click', '.ptr_item', function (e) {
        var $itemElement = $(this);
        var ptrType = $itemElement.attr('data-ptr-type');
		var ptrId = $itemElement.attr('data-ptr-id');
		
		$showedPtrIdElement = $('#workbench-current-showed-ptr');
		if ($showedPtrIdElement.val()!=ptrId) {
			if (ptrType==1 || ptrType==2) { //计划、任务
				ptrDetailsAction(ptrType, ptrId, function() {
					//加载详情页面成功后回调函数
					$showedPtrIdElement.val(ptrId);
				});
			} else if (ptrType==3) { //报告
				var period = $itemElement.attr('data-report-period');
				if (period==1) { //日报
					openReport('daily', 'v', ptrId, function() {
						//加载详情页面成功后回调函数
						$showedPtrIdElement.val(ptrId);
					});
				} else { //其它周期类型
				}
			}
		} else {
			$showedPtrIdElement.val('');
			closeSidepage();
		}
    });
});
</script>