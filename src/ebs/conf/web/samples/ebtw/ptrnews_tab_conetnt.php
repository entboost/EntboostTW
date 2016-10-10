<?php 
include dirname(__FILE__).'/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/include.php';	
	//验证必填字段
	$tabType = get_request_param('tab_type');
	if (!isset($tabType)) {
		ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('tab_type');
		return;
	}
	
	$embed = 1;
	if ($tabType==0 || $tabType==1 || $tabType==2 || $tabType==3 || $tabType==4) { //操作日志
		include dirname(__FILE__).'/workbench_list.php';
	}
	
	if (isset($json)) {
		$results = get_results_from_json($json, $tmpObj);
	} else {
		$results = null;
	}
	
	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
	$userAccount = $_SESSION[USER_ACCOUNT_NAME]; //当前用户的账号
?>
<div class="sidepage-tab-page-content" data-tab-type="<?php echo $tabType;?>">
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
var subTypeOfTabBadges = 'workbench_1';

//定义函数：计算已占用高度
function calculateRootHeight2() {
	var height = 0;	
// 	var $element2 = $contentContainer.find('.sidepage-tab-page-part2');
// 	var $element3 = $contentContainer.find('.sidepage-tab-page-part3');
	
// 	if ($element2.css('display')!='none')
// 		height += $element2.outerHeight(true);
// 	if ($element3.css('display')!='none')
// 		height += $element3.outerHeight(true);
	
	return height;
}

//定义函数：设置列表区域最大高度
function adjustSidepageTabPageContainerHeight(resizeEventSuffixName) {
	adjustContainerHeight3UsingE($('#ptrnews-content-height-input'), $contentContainer.find('.sidepage-tab-page-container'), calculateRootHeight2(), true, resizeEventSuffixName);
}

function registerSomeEvent() {
	registerSomeEventExecuted = true;
	
	//textarea自适应高度
	var executed = false;
	$contentContainer.find('.sidepage-tab-page-discuss.discuss2>textarea').autoHeight(function(oldH, newH) {
		if (oldH!=newH) {
			executed = true;
			adjustSidepageTabPageContainerHeight(resizeEventDiscuss2);
		}
	});
	//防止第一次执行时初始值与新值相等而导致列表区域没有设置设置高度
	if (!executed)
		adjustSidepageTabPageContainerHeight(resizeEventDiscuss2);
	
	//注册事件-点击日期标签 折叠/展开
	bindStretchClick($contentContainer.find('.date-mark'), null, function($This) {
		return $This.next().find("li>div")
	}, function($This, executeValues) {
		$This.parent().find('li:not(:first)').css('display', executeValues[0]);
	});
	
	//注册事件-标签页子项(编辑、删除等)管理操作
	registerSidepageTabItemActions($contentContainer, tabType, ptrId, ptrType, TabBadgesDatasOfPTRNews, subTypeOfTabBadges, 'ptrnews-tab');
	//注册事件-点击图片附件文件，自动打开浏览
	registerZoom('.attachment-link .open-resource[data-ext-type="2"]', 'data-open-url');
	
	//注册事件-鼠标在每记录上悬停："评论回复"按钮显示/隐藏
	$contentContainer.find('.sidepage-tab-page-list').hover(function() {
		$(this).find('.sidepage-tab-page-subtoolbar').removeClass('ebtw-hide'); //隐藏整个尾部工具栏
	}, function() {
		var $element = $(this).find('.sidepage-tab-page-subtoolbar');
		var $discussElement =  $element.next('.inner-discuss.ebtw-hide');
		if ($discussElement.length>0) {
			 $element.addClass('ebtw-hide'); //隐藏整个尾部工具栏	
		}
	});
	
	//刷新Tab角标数值
	refreshTabBadges(subTypeOfTabBadges, 'ptrnews-tab', [TabBadgesDatasOfPTRNews[tabType]]);
}

$(document).ready(function() {
	//加载标签页数据
	var tabTypeDict = {0:SideTabTypes['opr'], 1:SideTabTypes['opr'], 2:SideTabTypes['opr'], 3:SideTabTypes['opr']/*, 4:SideTabTypes['opr']*/};
	var datas = '<?php echo escapeQuotes(strictJson(json_encode($results?$results:'')));?>';
	loadSidepageTabData('<?php echo $userId;?>', null, null, null, $contentContainer, ptrType, tabTypeDict[tabType], datas, rootUrl);

	//自定义滚动条
	customScrollbarUsingE($contentContainer.find('.sidepage-tab-page-container'), 30, true);
	//滚动条移动至最底部
// 	setTimeout(function() {
// 		$contentContainer.find('.sidepage-tab-page-container').mCustomScrollbar('scrollTo', 'bottom');
// 	}, 200);
	
	//注册事件-显示/隐藏评论界面
	$contentContainer.find('.toggle-discuss-area').click(function() {
		var $toggleBtn = $(this);
		var $parent = $(this).parents('.sidepage-tab-page-list');
		var $element = $parent.find('.inner-discuss');
		if ($element.hasClass('ebtw-hide')) {
			$element.removeClass('ebtw-hide').append(laytpl($('#sidepage-tab-script-inner-discuss').html()).render({}));
			
	 		$parent.find('textarea').autoHeight() //textarea自适应高度
	 		//三角形边框跟随变色
	 		.focus(function() { //聚焦
	 			$(this).prev('.triangle_border_up').css('border-color','transparent transparent rgb(0,162,232)');
		 	})
			.blur(function() { //失去焦点
 				$(this).prev('.triangle_border_up').css('border-color','transparent transparent rgb(224,224,224)');
		 	})
	 		.hover(function() { //鼠标悬停
	 			if (!$(this).is(':focus'))
					$(this).prev('.triangle_border_up').css('border-color','transparent transparent rgba(0,0,0,.25)');
			}, function() { //鼠标离开
				if (!$(this).is(':focus'))
					$(this).prev('.triangle_border_up').css('border-color','transparent transparent rgba(0,0,0,.1)');
			});
// 			.each(function(i, element) { //评论输入框textarea自适应宽度
// 				var diff = $(element).outerWidth(true)-$(element).width();
// 				$(element).width($(element).parent().width()+1-diff);
// 				$(window).resize(function(e) {
// 					diff = $(element).outerWidth(true)-$(element).width();
// 					$(element).width($(element).parent().width()+1-diff);
// 				});
// 			});
			
			//输入框成为焦点
			$parent.find('.inner-discuss .sidepage-tab-page-discuss textarea').focus();
			
			//检测文档类型和文档编号完整性
		 	var opId = $parent.attr('data-op-id');
		 	var ptrType = $parent.attr('data-ptr-type');
		 	var ptrId = $parent.attr('data-ptr-id');
			if (opId.length==0 || !ptrType || ptrType.length==0 || !ptrId || ptrId.length==0) {
				logjs_err('inner discuss condition error');
				return;
			}
			
		 	//注册事件-点击新增/删除附件按钮
		 	var attaType = 3; //评论附件
		 	var onlyOne = true;
		 	var loadExist = false;
		 	var isEdit = false;
		 	var isOnlyView = false;
		 	var selectorPrefix = '#ptrnews-tab-content .sidepage-tab-page-list[data-op-id="'+opId+'"]';
		 	registerAttachmentActions(isEdit, isOnlyView, ptrType, ptrId, attaType, selectorPrefix+' .ebtw-file-upload', function(result, resourceId) {
		 		//重新设置列表区域最大高度
		 		//adjustSidepageTabPageContainerHeight(resizeEventDiscuss2);
		 	}, loadExist, onlyOne, selectorPrefix+' .ebtw-file-upload-list', '.attachment-remove', function() {
		 		//重新设置列表区域最大高度
		 		//adjustSidepageTabPageContainerHeight(resizeEventDiscuss2);
		 	});
			
			//注册事件-点击提交评论
			$parent.find('.discuss-submit').click(function() {
				//评论内容
				var content = $(this).prev('textarea').val().trim();
				if (!checkContentLength(0, 'discuss', content))
				    return false;
				
				var $liElements = $parent.find('.sidepage-tab-page-attachment .ebtw-file-upload-list li');
				var fileCount = $liElements.length;
				if (content.length==0 && fileCount==0) {
					layer.msg('请输入评论内容或选择附件', {icon:5});
					return;
				}

				//定义函数：发表评论成功后的处理
				var successFunc = function() {
					getReplyOrDiscussCount(ptrId, ptrType, function(count) {
						$parent.parent().find('.sidepage-tab-page-list[data-ptr-id="'+ptrId+'"][data-ptr-type="'+ptrType+'"] .discuss-count').html(count+' 条评论');
					}); //获取评论数量，并刷新界面
					$toggleBtn.trigger('click'); //触发模拟点击，关闭评论界面
					refreshTabBadges(subTypeOfTabBadges, 'ptrnews-tab', [TabBadgesDatasOfPTRNews[0], TabBadgesDatasOfPTRNews[1]]); //刷新Tab角标数值
				};
				
				if (fileCount>0) { //有附件
					var i=0;
					var fromType = 10 + parseInt(ptrType);
					var title = '';
					var liElements = new Array();
					$liElements.each(function(){
						liElements.push(this);
					});
					
					executeSendfile(fromType, ptrId, attaType, liElements, fileCount, i, title, false, function(title, close, i, total, fileName, result, resourceId) {
						var codeMap = $.jqEBMessenger.errCodeMap;
						if (result.code==codeMap.OK.code) {
							saveReplyOrDiscuss(ptrId, ptrType, content, resourceId, fileName, function(result) {
								layer.msg('发表评论成功');
								successFunc();
							}, function(reason) {
								layer.msg('发表评论内容失败', {icon: 2});
							});
						} else {
							layer.msg('上传评论附件失败', {icon: 2});
						}
					});
				} else { //只有评论内容
					saveReplyOrDiscuss(ptrId, ptrType, content, null, null, function(result) {
						layer.msg('发表评论成功');
						successFunc();
					}, function(reason) {
						layer.msg('发表评论内容失败', {icon: 2});
					});
				}
			});

			//注册事件-“评论回复”[Ctrl+Enter回车]提交
			var doingDiscussSubmit = false;
			registerEnterKeyToWork($parent, true, '.inner-discuss .sidepage-tab-page-discuss textarea', function(){
				if (doingDiscussSubmit) {
					logjs_info('miss duplicate discuss submit');
					return;
				}
				
				doingDiscussSubmit = true;
				//触发点击“保存”事件
				$parent.find('.discuss-submit').trigger('click');
				setTimeout(function(){
					doingDiscussSubmit = false;
					}, 5000);
			});
		} else {
			$element.addClass('ebtw-hide').html('');
		}
	});

	if (!registerSomeEventExecuted)
		registerSomeEvent();

  	//注册事件-点击查看(计划、任务、报告)
    $contentContainer.off('click', '.ptr_item').on('click', '.ptr_item', function (e, activeDiscussTab) {
        var $itemElement = $(this);
        var ptrType = $itemElement.attr('data-ptr-type');
		var ptrId = $itemElement.attr('data-ptr-id');

		$showedPtrIdElement = $('#workbench-current-showed-ptr');
		if ($showedPtrIdElement.val().length==0 || ($showedPtrIdElement.val()!=ptrId && $showedPtrIdElement.val().length>0)) {
			if (ptrType==1 || ptrType==2) { //计划、任务
				ptrDetailsAction(ptrType, ptrId, function() {
					//加载详情页面成功后回调函数
					$showedPtrIdElement.val(ptrId);
				}, activeDiscussTab?{reserved_active_no:(ptrType==1?2:3)}:null);
			} else if (ptrType==3) { //报告
// 				var period = $itemElement.attr('data-report-period');
// 				if (period==1) { //日报
// 					openReportById('daily', 'v', ptrId, function() {
// 						//加载详情页面成功后回调函数
// 						$showedPtrIdElement.val(ptrId);
// 					}, activeDiscussTab?{reserved_active_no:2}:null);
// 				} else { //其它周期类型
// 				}
			}
		} else {
			$showedPtrIdElement.val('');
			closeSidepage();
		}

		stopPropagation(e);
    });

    //注册事件-点击评论数量名条
    var discussCountSelector = '.sidepage-tab-page-content[data-tab-type="<?php echo $tabType;?>"] .sidepage-tab-page-tails .discuss-count';
    $contentContainer.off('click', discussCountSelector).on('click', discussCountSelector, function(e) {
    	var ptrId = $(this).parents('.sidepage-tab-page-list').attr('data-ptr-id');
        $(this).parents('.sidepage-tab-page-list').find('.ptr_item[data-ptr-id="'+ptrId+'"]').trigger('click', [true]);
    });
    
});
</script>