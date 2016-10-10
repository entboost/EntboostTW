<?php 
include '../task/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once '../task/include.php';
	
	$output = true;
	
	//验证必填字段
	$tabType = get_request_param('tab_type');
	if (!isset($tabType)) {
		ResultHandle::fieldValidNotEmptyErrToJsonAndOutput('tab_type', true);
		return;
	}
	
	$ptrId = get_request_param('from_id');
	if (!isset($ptrId)){
		$ptrId = get_request_param('task_id');
	}
	
	if (!EBModelBase::checkDigit($ptrId, $outErrMsg, 'from_id or task_id')) {
		$json = ResultHandle::fieldValidNotDigitErrToJsonAndOutput('from_id or task_id', $output);
		return;
	}
	
	$embed = 1;
	if ($tabType==11 || $tabType==20 || $tabType==1 || $tabType==2 || $tabType==3) {
		include dirname(__FILE__).'/../operaterecord/list.php';
	} else if ($tabType==4 || $tabType==5) {
		include dirname(__FILE__).'/../shareuser/list.php';
	} else if ($tabType==8) { //关联计划
		$json = get_associate_plan_of_task($ptrId, true, get_request_param('request_order_by'));
	} else if ($tabType==9) { //附件
		//不通过php服务端，直接通过eb rest api获取
	}
	
	if (isset($json))
		$results = get_results_from_json($json, $tmpObj);
	
	$opTypeClass = get_request_param('op_type_class');
	$opType = get_request_param('op_type');
	
	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
?>
<div class="sidepage-tab-page-content">
	<div class="sidepage-tab-page-container mCustomScrollbar"  data-mcs-theme="dark-3">
		<div class="col-xs-12 sidepage-tab-page-header"></div>
		<!-- 预留位置 -->
		<div class="ebtw-clear"></div>
	</div>
	
	<div class="sidepage-tab-page-part2">
	<?php if ($opTypeClass==1) { //评论/回复?>
		<div class="sidepage-tab-page-discuss discuss2">
			<textarea placeholder="输入评论内容，Ctrl+Enter提交"></textarea>
			<button type="button" class="btn btn-primary discuss-submit pull-right">提  交</button>
			<!-- <button type="button" class="btn btn-default pull-right clear-content">清  空</button> -->
			<div class="sidepage-tab-page-attachment">
				<div class="m1 ebtw-file-upload">
					<div><span class="glyphicon glyphicon-paperclip"></span> 上传附件</div>
					<input type="file" class="file_upload_input" name="up_file"><!-- file控件name字段必要，否则不能上传文件 -->
				</div>
				 
				<div class="m2 ebtw-file-upload-list">
					<ul>
					</ul>
				</div>
				
				<div class="ebtw-clear"></div>
			</div>
		</div>
	<?php } else if ($opType==31 || (is_array($opType) && in_array(31, $opType) ) || $opType==32) { //上报进度、工时?>
		<div class="sidepage-tab-page-working-step working-step2"></div>
	<?php }?>
	</div>
</div>
<script type="text/javascript">
var resizeEventDiscuss2 = 'discuss2';
var registerSomeEventExecuted = false;
var tabType = <?php echo $tabType;?>;
var ptrId = '<?php echo $ptrId;?>';
var ptrType = <?php echo $PTRType;?>;	
var rootUrl = '<?php echo $ROOT_URL;?>';
var subTypeOfTabBadges = 'task_0';

//定义函数：计算已占用高度
function calculateRootHeight2() {
	var $element = $sidepageContainer.find('.sidepage-tab-page-part2');
	if ($element.css('display')=='none')
		return 0;
	
	return $element.outerHeight(true);
}

//定义函数：设置列表区域最大高度
function adjustSidepageTabPageContainerHeight(resizeEventSuffixName) {
	adjustContainerHeight3UsingE($('#content-height-input'), $sidepageContainer.find('.sidepage-tab-page-container'), calculateRootHeight2(), true, resizeEventSuffixName);
}

function registerSomeEvent() {
	registerSomeEventExecuted = true;

	//textarea自适应高度
	var executed = false;
	$sidepageContainer.find('.sidepage-tab-page-discuss.discuss2>textarea, .sidepage-tab-page-working-step.working-step2 textarea').autoHeight(function(oldH, newH) {
		if (oldH!=newH) {
			executed = true;
			adjustSidepageTabPageContainerHeight(resizeEventDiscuss2);
		}
	});

	//防止第一次执行时初始值与新值相等而导致列表区域没有设置设置高度
	if (!executed)
		adjustSidepageTabPageContainerHeight(resizeEventDiscuss2);

	//注册事件-点击日期标签 折叠/展开
	bindStretchClick($sidepageContainer.find('.date-mark'), null, function($This) {
		return $This.next().find("li>div")
	}, function($This, executeValues) {
		$This.parent().find('li:not(:first)').css('display', executeValues[0]);
	});

	//注册事件-标签页子项(编辑、删除等)管理操作
	registerSidepageTabItemActions($sidepageContainer, tabType, ptrId, ptrType, TabBadgesDatas, subTypeOfTabBadges);
	//注册事件-点击图片附件文件，自动打开浏览
	registerZoom('.attachment-link .open-resource[data-ext-type="2"]', 'data-open-url');
	//注册事件-管理关联用户操作(删除等)及对应工具栏
	registerDeleteShareUsersAction('<?php echo $userId;?>', $sidepageContainer, ptrId, ptrType);
	
	//负责人、参与人、共享人、关注人 
	var personTypes = <?php echo createShareUserTypesScript();?>;
	//注册事件-点击添加关联用户按钮
	registerAddShareUsersAction('<?php echo $userId;?>', $sidepageContainer, ptrId, ptrType, personTypes, rootUrl, function() {
		refreshTabBadges(subTypeOfTabBadges, 'sidepage-tab', [TabBadgesDatas[tabType], TabBadgesDatas[20]]); //刷新Tab角标数值
	});
}

$(document).ready(function() {
	var opTypeClass = '<?php echo $opTypeClass;?>';
	<?php
		if (is_array($opType)) {
			if (in_array('31', $opType)) {
				echo 'var opType = "31"';
			} else {
				echo 'var opType = "0"';
			}
			//echo "var opType = json_parse('".json_encode($opType)."')";
		} else
			echo 'var opType = "'.$opType.'"';
	?>
	
	//注册事件-清除评论的输入内容
// 	$sidepageContainer.find('.sidepage-tab-page-discuss .clear-content').click(function() {
// 		$(this).parent().find('textarea').val('').trigger('input');
// 	});
	
	//加载标签页数据
	var tabTypeDict = {11:SideTabTypes['opr'], 3:SideTabTypes['opr'], 1:SideTabTypes['opr'], 2:SideTabTypes['opr'], 4:SideTabTypes['su'], 5:SideTabTypes['su'], 8:SideTabTypes['ass'], 20:SideTabTypes['opr']};
	<?php if ($tabType!=9) {?>
		var datas = '<?php echo escapeQuotes(strictJson(json_encode($results?$results:'')));?>';
		var allowedActionsDict = {singlePtr:true, ptrId:ptrId, ptrType:ptrType};
		allowedActionsDict[ptrId] = allowedActions;
		loadSidepageTabData('<?php echo $userId;?>', taskCreateUid, allowedActionsDict, null, $sidepageContainer, ptrType, tabTypeDict[tabType], datas, rootUrl, tabType==4?[4]:(tabType==5?[2,3]:undefined));
	<?php }?>

	//自定义滚动条
	customScrollbarUsingE($sidepageContainer.find('.sidepage-tab-page-container'), 30, true);
	//滚动条移动至最底部
	setTimeout(function() {
		$sidepageContainer.find('.sidepage-tab-page-container').mCustomScrollbar('scrollTo', 'bottom');
	}, 200);
	
	//上报进度、上报工时
	if ( ( (opType==31 || $.inArray('31', opType)>-1) && $.inArray(22, allowedActions)>-1 && status<=2) || (opType==32 && $.inArray(23, allowedActions)>-1 && status<=3) ) {
		var $partContainer2 = $sidepageContainer.find('.sidepage-tab-page-part2');
		$partContainer2.css('display', 'block');
		$partContainer2.prepend('<div class="stretch-button full" title="隐藏"><span class="glyphicon glyphicon-resize-small"></span></div>');
// 		$partContainer2.prepend('<div class="stretch-button small" title="上报进度"><span class="glyphicon glyphicon-arrow-up"></span></div>');
		
		var $wsContainer = $sidepageContainer.find('.sidepage-tab-page-working-step');
		var defaultWorkTime = 0.5; //默认工时
    	var content = laytpl($('#actionbar-content-script-pre').html()).render({});
		if (opType==31) {
			content+= laytpl($('#actionbar-content-script-3').html()).render({percent:percentage});
		} else {
			content+= laytpl($('#actionbar-content-script-4').html()).render({default_work_time:defaultWorkTime, default_op_time:$.D_ALG.formatDate(new Date(), 'yyyy-mm-dd')});
		}
		content+= laytpl($('#actionbar-content-script-rear-2').html()).render({disableCancel:true});
		$wsContainer.append(content);
		
		//定义函数：绑定输入区事件
		function registerInputArea(actionType, step, pipsStep, max, value, unit) {
		   	//滑块
			$wsContainer.find("#ptr-slider").slider({animate:true, value:value, min:0, step:step, max:max, range:"min", change: function(e, ui) {
	 				$wsContainer.find('#ptr-slider-per').attr('data-per', ui.value).text(ui.value+(opType==31?'':' ')+unit);
	 	    	}
			}).slider("pips", {step:pipsStep, rest:"label"}).slider("float");
			
		   	//textarea自适应高度
		   	//$wsContainer.find('.ptr-slider-remark>textarea').autoHeight(); 前面代码已有实现调用
		  //初始化日期选择器
		   	if (actionType==32) {
				var dateFormat = 'yyyy-mm-dd';
			   	createDefaultDatetimePicker('#ptr-time-value', dateFormat, 2, 2, 4);
			   	createDefaultDatetimePicker('#ptr-time-addon', dateFormat, 2, 2, 4, 'ptr-time-value', dateFormat);
		   	}
			
			//注册事件-点击伸缩输入区按钮
		   	var $stretchButtonElement = $partContainer2.find('.stretch-button').click(function() {
			   	if ($(this).hasClass('full')) {
			   		$partContainer2.height(5);
			   		$(this).css('top', '-30px').css('right', '20px').attr('title', '上报进度').removeClass('full').addClass('small').children().removeClass('glyphicon-resize-small').addClass('glyphicon-arrow-up');
			   	} else {
				   	var height = $partContainer2.find('.sidepage-tab-page-working-step').outerHeight(true);
			   		$partContainer2.height(height);
			   		$(this).css('top', '0').css('right', '5px').attr('title', '隐藏').removeClass('small').addClass('full').children().removeClass('glyphicon-arrow-up').addClass('glyphicon-resize-small');
			   	}
		   		adjustSidepageTabPageContainerHeight(resizeEventDiscuss2);
			});
			//默认隐藏界面
			if (defaultActiveNo!=tabType)
				$stretchButtonElement.trigger('click');
		   	
		   	//注册事件-点击提交按钮
		   	$wsContainer.find('.actionbar-content-submit').click(function(e) {
				var opTime;
				var value = $wsContainer.find('.ptr-slider-value>#ptr-slider-per').attr('data-per');
				var remark = $.trim($wsContainer.find('.ptr-slider-remark>#ptr-slider-txt').val());
				if (remark.length==0) {
					layer.msg('缺少说明', {icon:5});
					$wsContainer.find('textarea[id="ptr-slider-txt"]').focus();
					return;
				}
				if (actionType==32) {
					opTime = $.trim($wsContainer.find('#ptr-time-value').val());
					if (opTime.length==0) {
						layer.msg('缺少工时日期', {icon:5});
						return;
					}
				}
				
				var type = (actionType==31)?22:23;
				var typeName = (type==22)?'进度':'工时';
				
				//询问确认后提交执行
				askForConfirmSubmit('真的要提交'+typeName+'吗？', '上报'+typeName, submitTaskPercentOrWorkTime, [type, ptrId, value, remark, opTime, function(result) {
					if (result!==false) {
						layer.msg('上报'+typeName+'成功');
						
						if (actionType==31) { //上报进度
							//当100%时，状态变为"已完成"
							if (value==100)
								status = 3;
							//缓存当前进度
							percentage = value;
						}
						//刷新界面
						refreshTabBadges(subTypeOfTabBadges, 'sidepage-tab', [TabBadgesDatas[tabType], TabBadgesDatas[20]]); //刷新Tab角标数值
						$sidepageContainer.parent().find('#sidepage-tab'+tabType).trigger('click'); //模拟点击刷新Tab内容页面

						refreshMainViewActually(false, ptrType, ptrId); //依据具体情况下刷新主视图
					}
				}]);
		   	});
		}
		
		//执行绑定输入区事件
		if (opType==31)
			registerInputArea(opType, 1, 5, 100, percentage, '%');
		else
			registerInputArea(opType, 0.5, 2, 24, defaultWorkTime, '小时');		
	}
	
	//评论/回复界面
	if (opTypeClass==1) {
		$sidepageContainer.find('.sidepage-tab-page-part2').css('display', 'block');

		//注册事件-点击新增/删除附件按钮
		var attaType = 3; //评论附件
		var onlyOne = true;
		var loadExist = false;
		var isEdit = false;
		var isOnlyView = false;
		registerAttachmentActions(isEdit, isOnlyView, ptrType, ptrId, attaType, '#sidepage-tab-content .ebtw-file-upload', function(result, resourceId) {
			//重新设置列表区域最大高度
			adjustSidepageTabPageContainerHeight(resizeEventDiscuss2);
		}, loadExist, onlyOne, '#sidepage-tab-content .ebtw-file-upload-list', '.attachment-remove', function() {
			//重新设置列表区域最大高度
			adjustSidepageTabPageContainerHeight(resizeEventDiscuss2);
		});

		//注册事件-“评论回复”[Ctrl+Enter回车]提交
		var doingDiscussSubmit = false;
		registerEnterKeyToWork($sidepageContainer, true, '.sidepage-tab-page-part2 .sidepage-tab-page-discuss.discuss2 textarea', function($textInputElement) {
			if (doingDiscussSubmit) {
				logjs_info('miss duplicate discuss submit');
				return;
			}
			
			doingDiscussSubmit = true;
			//触发点击“保存”事件
			$textInputElement.parent().find('.discuss-submit').trigger('click');
			setTimeout(function(){
				doingDiscussSubmit = false;
				}, 5000);
		});
		
		//点击提交评论
		$sidepageContainer.find('.discuss-submit').click(function(){
			//评论内容
			var content = $(this).prev('textarea').val().trim();
			if (!checkContentLength(0, 'discuss', content))
			    return false;
		    
			var $liElements = $sidepageContainer.find('.sidepage-tab-page-attachment .ebtw-file-upload-list li');
			var fileCount = $liElements.length;
			if (content.length==0 && fileCount==0) {
				layer.msg('请输入评论内容或选择附件', {icon:5});
				return;
			}
			
			if (fileCount>0) {
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
							refreshTabBadges(subTypeOfTabBadges, 'sidepage-tab', [TabBadgesDatas[tabType], TabBadgesDatas[9], TabBadgesDatas[20]]); //刷新Tab角标数值
							$sidepageContainer.parent().find('#sidepage-tab'+tabType).trigger('click'); //模拟点击刷新Tab内容页面
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
					refreshTabBadges(subTypeOfTabBadges, 'sidepage-tab', [TabBadgesDatas[tabType], TabBadgesDatas[20]]); //刷新Tab角标数值
					$sidepageContainer.parent().find('#sidepage-tab'+tabType).trigger('click'); //模拟点击刷新Tab内容页面
				}, function(reason) {
					layer.msg('发表评论内容失败', {icon: 2});
				});
			}
		});
	}
	
	<?php if ($tabType!=9) {?>
	if (!registerSomeEventExecuted)
		registerSomeEvent();
	<?php }?>
});
</script>