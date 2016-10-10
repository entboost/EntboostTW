<?php 
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/include.php';
	
	$parentType = get_request_param('reserved_parent_type');
	$laneNo = get_request_param('reserved_lane_no');
	$ptrType = get_request_param('reserved_ptr_type');
	if (!isset($parentType) || !isset($laneNo) || !isset($ptrType)) {
		echo '<div class="col-xs-12">错误：缺少访问参数</div>';
		return;
	}
	
	//处理ptrType不是纯数字的情况
// 	if (!var_is_digit($ptrType)) {
// 		$ptrType = 0;
// 	}
	
	//分析查询条件并执行查询数据
	if ($parentType=='workbench') {
		switch ((int)$laneNo) {
			case 1: //计划要做(我的计划-未完成状态)
				$laneTitle = '计划要做';
				$embed = 1;
				include dirname(__FILE__).'/plan/list.php';
				break;
			case 2: //待办事宜，等待当前用户进行评审的计划、评阅的报告
				//待定
				$laneTitle = '待办事项';
				$embed = 1;
				include dirname(__FILE__).'/workbench_list.php';
				break;
			case 3: //未完成任务(我提交的、我负责的、我参与的)
				$laneTitle = '未完成任务';
				$embed = 1;
				include dirname(__FILE__).'/task/list.php';
				break;
		}
	} else if ($parentType=='ptr') {
		
	}
	
	if (isset($json))
		$results = get_results_from_json($json, $tmpObj);
?>
<!-- 头部 -->
<div class="form-inline board-lane-toolbar"></div>
<!-- 记录 -->
<div class="board-lane-list-container mCustomScrollbar" data-mcs-theme="dark-3">
	<div class="board-lane-list"></div>
</div>
<?php if($ptrType=='1') {?>
<!-- 快速新建计划 -->
<div class="board-lane-item board-lane-control">
	<div class="form-inline lane-item-row">
		<textarea placeholder="输入计划事项 (Enter回车提交)" name="plan_name"></textarea>
	</div>
	<div class="form-inline lane-item-row">
		<button type="button" class="btn btn-primary btn-save">新  建</button>
	</div>
	<div class="ebtw-clear"></div>
</div>
<?php } else {?>
<div class="board-lane-tail">
</div>
<?php }?>
<script type="text/javascript">

//重新加载本泳道界面
function load_board_lane<?php echo $laneNo;?>(scroll) {
	var parentType = '<?php echo $parentType;?>';
	var laneNo = <?php echo $laneNo;?>;
	var laneSelector = '#board-lane'+laneNo;
	var ptrType = '<?php echo $ptrType;?>';
	
	var parameter = loadedLaneParameters[laneNo];
	load_board_lane(parentType, laneNo, laneSelector, ptrType, parameter, function(result) {
		if (result && scroll==laneNo) {
			eval('board_lane_scroll_to_bottom'+laneNo+'();');
		}
	});
}
//滚动条滚动到底部
function board_lane_scroll_to_bottom<?php echo $laneNo;?>() {
	var laneNo = <?php echo $laneNo;?>;
	var laneSelector = '#board-lane'+laneNo;
	$boardLaneContainer = $(laneSelector);
	$boardLaneListContainer = $boardLaneContainer.find('.board-lane-list-container');

	//递归等待滚动条初始化完毕状态，然后执行滚动动作
	var readyObj = customScrollbarsReady[<?php echo $laneNo;?>];
	if (readyObj.status===false) {
		//logjs_info('waitting for idle '+ '<?php echo $laneNo;?>');
		readyObj.count++;
		if (readyObj.count<100) //最长等待100次(100*100=10秒)
			setTimeout(board_lane_scroll_to_bottom<?php echo $laneNo;?>, 100);
	} else {
		//logjs_info('going to scroll to bottom <?php echo $laneNo;?>');
		$boardLaneListContainer.mCustomScrollbar('scrollTo', 'bottom');
	}
}

$(document).ready(function() {
	var parentType = '<?php echo $parentType;?>';
	var laneNo = <?php echo $laneNo;?>;
	var laneSelector = '#board-lane'+laneNo;
	var ptrType = '<?php echo $ptrType;?>';
	
	$boardLaneContainer = $(laneSelector);
	$boardLaneListContainer = $boardLaneContainer.find('.board-lane-list-container');
	
	//创建头部工具栏
	var data = {title:'<?php echo $laneTitle; ?>' ,lane_no:laneNo, ptr_type:ptrType};
	$boardLaneContainer.find('.board-lane-toolbar').append(laytpl($('#board-lane-toolbar-script').html()).render(data));

<?php if ($tmpObj->code==0) {?>
	var total = <?php echo $tmpObj->total;?>;
	var datas = '<?php echo escapeQuotes(strictJson(json_encode(!empty($results)?$results:array())));?>';
	datas = json_parse(datas);
	
	//处理空记录或异常记录情况
	if ((typeof datas !== 'undefined' && Object.prototype.toString.call(datas) === '[object Array]')) {
		//渲染看板列表
		for (var i=0;i <datas.length; i++) {
			var data = datas[i];
			
			var showTime;
			data.personName = data.create_name; 
			
			if (parentType=='workbench') { //工作台
				switch(laneNo) { //泳道
				case 1: //未完成的计划
					data.showCreateName = false;
					showTime = data.create_time;
					//检测是否已阅的状态
					if (data.status==0 && data.from_type>0) {
						data.read_flag = 0;
					}
					
					//大色块：标记“计划周期”和“计划状态”
					data.floatSwatchItems = [{backgroundColor:'rgb(0,112,192)', title:dictPeriodOfPlan[data.period]}, {backgroundColor:dictStatusColorOfPlan[data.status], title:dictStatusOfPlan[data.status]}];
					break;
				case 2: //待办事宜
					showTime = data.su_create_time;
					data.talkToPerson = data.create_uid;
					data.personUid = data.create_uid;
					data.personAccount = data.create_account;
					
					//大色块：标记“评审计划”或“评阅报告”
					data.floatSwatchItems = [dictOfApproveInBoardLane(data.ptr_type)];
					break;
				case 3: //未完成的任务
					showTime = data.create_time;
					//检测是否已阅的状态
					if (data.status==0 && data.from_type>0) {
						data.read_flag = 0;
					}
					
					var share = getShares(5, data, true);
					if (share) {
						if (share.share_uid==userId) {
							data.personUid = userId;
							data.personAccount = userAccount;
							data.personName = '我';
						} else {
							data.talkToPerson = share.share_uid;
							data.personUid = share.share_uid;
							data.personAccount = share.user_account;
							data.personName = share.share_name;
						}
					}

					//大色块：标记“我负责的”、“我参与的”和“我提交的”
					data.floatSwatchItems = [dictOfOwnerOrSharerInBoardLane(data.su_share_type)];
					break;
				}
			} else if (parentType=='ptr') { //普通
				//待定
			}
			
			if (showTime)
				data.show_time = popularDate3(showTime); //$.D_ALG.formatDate(new Date(showTime), 'hh:ii')+ '' +popularDate2(showTime);
			
			//鼠标悬停显示tips内容
			if (data.period) {
				var tipParts = getTipsOfPTRPeriod(ptrType, parseInt(data.period), data.start_time, data.stop_time);
				data.period_tips = tipParts[0]+' '+tipParts[1];
			}
			
			data = processingBoardLaneData(userId, ptrType, data);
			data.is_last_row = (i>=0 && i==datas.length-1)?true:false;

			//重要程度显示及其修改相关
			if (data.important!=undefined) {
				//工作台且(第一泳道或(第三泳道创建人或负责人))
				var showImpotantMenu = (parentType=='workbench' && (laneNo==1 || (laneNo==3&&(data.create_uid==userId || data.personUid==userId))));
				data.importantSwatchHtml = createModifyImportantMenuScript(data.important, 'col-xs-2 swatches-item', 'BoardLane', showImpotantMenu);
			}
			
			data.dictOfProgressHtml = laytpl($('#board-lane-swatches-item-progress-script').html()).render(data);
			$boardLaneContainer.find('.board-lane-list').append(laytpl($('#board-lane-item-script').html()).render(data));
		}
		
		//处理仅有一行记录的情况
		if (datas.length==1) {
			//解决当高度很小时，弹出菜单被遮挡的问题
			$boardLaneListContainer.addClass('short_height');
		}
		
		//显示空白行
		if (datas.length==0) {
			$boardLaneContainer.find('.board-lane-list').append(laytpl($('#board-lane-empty-item-script').html()).render({}));
		}
		
		//显示记录数
		total = (parentType=='workbench' && laneNo==3)?datas.length:total;
		$boardLaneContainer.find('.board-lane-toolbar .count-badge').html(total);

		//滚动到最底
		board_lane_scroll_to_bottom<?php echo $laneNo;?>();
	}
	
	//注册事件-鼠标在每记录上悬停
	$boardLaneListContainer.on('mouseover', '.board-lane-item', function() {
		$(this).find('.swatches div.can-hide.ebtw-invisible').removeClass('ebtw-invisible'); //显示被隐藏的"重要程度"色块
		
		//显示右侧底部的工具栏
		$(this).find('.lane-item-actionbar').removeClass('ebtw-invisible') //整个工具栏
			.find('div:not(.favorite)').removeClass('ebtw-invisible'); //图标
	}).on('mouseout', '.board-lane-item', function() {
		$(this).find('.swatches div.can-hide').addClass('ebtw-invisible'); //隐藏"重要程度"为"普通"的色块

		//隐藏右侧底部的工具栏
		$(this).find('.lane-item-actionbar').filter(':not(:has(.always))').addClass('ebtw-invisible'); //整个工具栏
		$(this).find('.lane-item-actionbar').find('div:not(.favorite)').addClass('ebtw-invisible'); //图标
	});
	
	//启动鼠标悬停弹出下拉菜单
	//待定：dropdownHover这个函数比较特殊，应该不支持用on来绑定事件，所以每次刷新某行时，需要调用一次。
	$boardLaneListContainer.find('.action-menu-toggle').dropdownHover(/*{delay:500}*/);
	
	//注册事件-点击标记已读
	$boardLaneListContainer.on('click', '.lane-item-row .mark-read .radius-point', function(e) {
		var $This =  $(this);
		var $laneItemElement =$This.parents('.board-lane-item');
		
		var realPtrType = $laneItemElement.attr('data-ptr-type');
		var ptrId = $laneItemElement.attr('data-ptr-id');
		var shareId = $This.parent().attr('data-share-id');
		
		markReadFlag(realPtrType, ptrId, shareId, $This.parent());
		
		stopPropagation(e);
	});

	//注册事件-修改重要程度属性
	$boardLaneListContainer.on('click', '.lane-item-row .important-level li', function(e) {
		var $This =  $(this);
		var $laneItemElement =$This.parents('.board-lane-item');
		var realPtrType = $laneItemElement.attr('data-ptr-type');
		var ptrId = $laneItemElement.attr('data-ptr-id');
		var important = $(this).attr('data-important');
		
		var loadIndex = layer.load(2);
		changeImportantField(realPtrType, ptrId, important, function(result) {
			$laneItemElement.find('.swatches .important-level').before(createModifyImportantMenuScript(important, 'col-xs-2 swatches-item', 'BoardLane', true)).remove(); //替换旧元素
			layer.close(loadIndex);
		}, function(err) {
			layer.close(loadIndex);
		});
		
		stopPropagation(e);
	});
	
	//点击“用户名称”发起聊天会话
	//废弃(由顶层父级页面实现此功能)
// 	$boardLaneListContainer.on('click', '.talk-to-person', function(e) {
// 		var talkToUid = $(this).attr('data-talk-to-uid');
// 		if (talkToUid!=undefined) {
// 			$('body').find('.ebim-call-link').remove();
// 			var $linkElement =$('body').append('<a class="ebim-call-link" style="display:none;" href="ebim-call-account://'+talkToUid+'">发起会话</a>').find('.ebim-call-link');
// 			$linkElement[0].click(); //模拟点击
// 			$linkElement.remove();
// 		}
// 		stopPropagation(e);
// 	});
	
  	//注册事件-点击查看(计划、任务、报告)
    $boardLaneListContainer.on('click', '.board-lane-item', function (e) {
        //检测是否有选中文本，以判断用户操作意向
    	var sText = window.getSelection?window.getSelection():document.selection.createRange().text;
    	 
    	//忽略下一步操作
    	if(sText!=null && sText!='') {
    		stopPropagation(e);
    		return;
    	}
        
    	if ($(e.target).parent().hasClass('important-level')) { //点击"重要程度"色块触发的事件
			//默认不处理
    	} else if ($(e.target).parent().hasClass('sub-menus')) { //点击右侧工具栏“子菜单”图标触发的事件
			//默认不处理
        } else if ($(e.target).hasClass('talk-to-person')){ //点击“用户名称”发起聊天会话的事件
			//默认不处理
        } else { //处理为查看属性动作
	        var $laneItemElement = $(this);
	        var ptrType = $laneItemElement.attr('data-ptr-type');
			var ptrId = $laneItemElement.attr('data-ptr-id');
			
			var $radiusPointElement = $laneItemElement.find('.mark-read .radius-point');
			var $markReadElement;
			var shareId;
			if ($radiusPointElement.length>0) {
				$markReadElement =  $radiusPointElement.parent();
				shareId =  $markReadElement.attr('data-share-id');
			}

			$showedPtrIdElement = $('#workbench-current-showed-ptr');
			if ($showedPtrIdElement.val()!=ptrId) {
				if (ptrType==1 || ptrType==2) { //计划、任务
					ptrDetailsAction(ptrType, ptrId, function() {
						//加载详情页面成功后回调函数
						$showedPtrIdElement.val(ptrId);
					});
				} else if (ptrType==3) { //报告
					var period = $laneItemElement.attr('data-report-period');
					if (period==1) { //日报
						openReportById('daily', 'v', ptrId, function() {
							//加载详情页面成功后回调函数
							$showedPtrIdElement.val(ptrId);
						}/*, {reserved_active_no:2}*/);
						
// 						openReport('daily', 'v', ptrId, function() {
// 							$showedPtrIdElement.val(ptrId);
// 						});
					} else { //其它周期类型
						
					}
				}
				
				setCurrentShowedValues(laneNo, ptrId);
			} else {
				$showedPtrIdElement.val('');
				closeSidepage();
			}
			
			stopPropagation(e);
    	}
    });
	
    //注册事件-点击新建(计划、任务)
//     $boardLaneContainer.find('.btn-AddPTR').click(function (e) {
//         var ptrType = $(this).attr('data-ptr-type');
//         ptrAddAction(ptrType);
		
//         setCurrentShowedValues(laneNo, '');
		
//         stopPropagation(e);
//     });
  	
    //注册事件-点击关注/取消关注
    $boardLaneListContainer.on('click', '.board-lane-list .favorite', function(e) {
		var $laneItemElement =$(this).parents('.board-lane-item');
		var $element = $(this).find('span.glyphicon');
		var ptrType = $laneItemElement.attr('data-ptr-type');
		var ptrId = $laneItemElement.attr('data-ptr-id');
		var cancel = $element.hasClass('glyphicon-heart')?true:false; //是否取消关注
		
		var loadIndex = layer.load(2);
		saveFavorite(userId, cancel, ptrType, ptrId, function() {
			if (cancel)
				$element.attr('title', '点击关注该任务').removeClass('glyphicon-heart').addClass('glyphicon-heart-empty').parent().removeClass('always');
			else
				$element.attr('title', '取消关注').removeClass('glyphicon-heart-empty').addClass('glyphicon-heart').parent().addClass('always');
			layer.close(loadIndex);
		}, function() {
			layer.close(loadIndex);
		});
		
		stopPropagation(e);
    });
	
	//注册事件-打钩按钮(计划标为完成)
    $boardLaneListContainer.on('mouseover', '.board-lane-list .glyphicon-unchecked.unchecked', function(e) { //鼠标悬停
		$(this).removeClass('glyphicon-unchecked');
		$(this).addClass('glyphicon-check');
    }).on('mouseout', '.board-lane-list .glyphicon-check.unchecked', function(e) { //鼠标离开
		$(this).removeClass('glyphicon-check');
		$(this).addClass('glyphicon-unchecked');
    }).on('click', '.board-lane-list .complete', function(e) { //点击按钮
		var $laneItemElement =$(this).parents('.board-lane-item');
		var ptrType = $laneItemElement.attr('data-ptr-type');
		var ptrId = $laneItemElement.attr('data-ptr-id');
		ptrCompleteAction(ptrType, ptrId, function(result) {
    		if (result!==false && result!=-1) {
				eval('load_board_lane'+laneNo+'(<?php echo $laneNo;?>)'); //刷新本泳道
    		}
		}, false);
		
		stopPropagation(e);
    });
	
    //注册事件-子菜单
    $boardLaneListContainer.on('click', '.board-lane-list .sub-menus li', function(e) {
		var $laneItemElement =$(this).parents('.board-lane-item');
		var ptrType = $laneItemElement.attr('data-ptr-type');
		var ptrId = $laneItemElement.attr('data-ptr-id');
		var type = parseInt($(this).attr('data-type'));

		//定义函数：成功执行后的默认回调函数
		var defaultSuccessHandle = function(result) {
			if (result!==false) {
				reloadCurrentBoardLane();
			}
			resetCurrentShowedValues();
		};
		
		switch(type) {
		case 2: //编辑
// 			var $radiusPointElement = $laneItemElement.find('.mark-read .radius-point');
// 			var $markReadElement;
// 			var shareId;
// 			if ($radiusPointElement.length>0) {
// 				$markReadElement =  $radiusPointElement.parent();
// 				shareId =  $markReadElement.attr('data-share-id');
// 			}
			
			//$showedPtrIdElement = $('#workbench-current-showed-ptr');
			if (ptrType==1 /*|| ptrType==2*/) { //计划、任务
				ptrEditorAction(ptrType, ptrId, function() {
					//加载编辑页面成功后回调函数
					//$showedPtrIdElement.val(ptrId);
				});
			}
			//setCurrentShowedValues(laneNo, ptrId);
			break;
		case 3: //删除
			setCurrentShowedValues(laneNo, ptrId);
			ptrDeleteAction(ptrType, ptrId, defaultSuccessHandle, null, 0);
			break;
		case 21: //计划转任务
				ptrTypeOfTask = 2;
				ptrEditorAction(ptrTypeOfTask, null, function() {
					setCurrentShowedValues(3);
				}, {plan_id: ptrId, translate_to_task:1});
			break;
		case 1: //申请评审/评阅
		case 7: //重新上报
			var prefix ='approval';
			var title = '提交评审';
			var personTitle = '评审人';
			var contentPlaceholder = '想说点什么？';
			if (ptrType==3) {
				title = '提交评阅';
				personTitle = '评阅人';
				//contentPlaceholder = '请输入评阅内容';
			}
			promptContentAndSelectPersonUsingLayer(prefix, title, {personTitle:personTitle, contentTitle:'备注', contentPlaceholder:contentPlaceholder}, 0, 3, function(remark, selectedUserId, selectedUserName) {
				setCurrentShowedValues(laneNo, ptrId);
				
				var loadIndex = layer.load(2);
				submitApproval(ptrType, ptrId, selectedUserId, selectedUserName, remark, function(result) {
					layer.close(loadIndex);
					layer.msg(title+'成功');
					defaultSuccessHandle(result);
				}, function(err) {
					layer.close(loadIndex);
					layer.msg(title+'失败', {icon:2});
				});
			});
			break;
		case 4: //评审通过/评阅回复
		case 5: //评审拒绝
			var prefix ='approval';
			var title = (type==4)?'评审通过':'评审拒绝';
			var contentPlaceholder = (type==4)?'想说点什么？':'拒绝的原因是？';
			if (ptrType==3) {
				title = '评阅回复';
				//contentPlaceholder = '请输入评阅内容';
			}
			promptContentAndSelectPersonUsingLayer(prefix, title, {contentTitle:'备注', contentPlaceholder:contentPlaceholder}, 2, 2, function(remark) {
				setCurrentShowedValues(laneNo, ptrId);
				
				var loadIndex = layer.load(2);
				approvalAction(type==4?2:3, ptrType, ptrId, remark, false, function(result) {
					layer.close(loadIndex);
					layer.msg(title+'成功');
					defaultSuccessHandle(result);
				}, function(err) {
					layer.close(loadIndex);
					layer.msg(title+'失败', {icon:2});
				});
			});
			break;
		case 6: //取消上报计划
			if (ptrType==1) {
				setCurrentShowedValues(laneNo, ptrId);
				ptrCancelReportAction(ptrType, ptrId, defaultSuccessHandle);
			}
			break;
		case 10: //中止任务
			promptContentAndSelectPersonUsingLayer(prefix, title, {contentTitle:'备注', contentPlaceholder:"中止的原因是？"}, 2, 2, function(remark) {
				setCurrentShowedValues(laneNo, ptrId);
				ptrStopAction(ptrType, ptrId, defaultSuccessHandle, remark);
			});
			break;
		case 22: //上报进度
		case 23: //上报工时
			ptrDetailsAction(ptrType, ptrId, function() {
				//加载详情页面成功后回调函数
			}, {reserved_active_no:type-21});
			break;
		}
		
    	stopPropagation(e);
    });
    	
<?php }?>
	
	$boardLaneToolbar = $boardLaneContainer.find('.board-lane-toolbar');
	$boardLaneControl = $boardLaneContainer.find('.board-lane-control');
	
	//注册事件-点击保存"快速新建计划"
	$boardLaneControl.find('.btn-save').click(function() {
		var $inputElement = $(this).parents('.board-lane-control').find('textarea[name="plan_name"]');
		var inputVal = $.trim($inputElement.val());
		if (inputVal.length==0) {
			layer.msg('请输入内容', {icon:2});
			$inputElement.focus();
		} else {
    		var formatedTime = $.D_ALG.formatDate(new Date(), 'yyyy-mm-dd');
    		var loadIndex = layer.load(2);
    		//执行创建新计划
    		createPlan({plan_name:inputVal, start_time:formatedTime+' 00:00:00', stop_time:formatedTime+' 23:59:59', status:1}, function(newPtrId) {
    			layer.close(loadIndex);
				eval('load_board_lane'+laneNo+'(<?php echo $laneNo;?>)');
    		}, function(err) {
    			layer.close(loadIndex);
    		});
		}
	});
	//注册事件-“快速新建计划”[Enter回车]提交
	$boardLaneControl.find('textarea[name="plan_name"]').keydown(function(event) {
		if (event.keyCode==13/* && event.ctrlKey*/) {
	        if (event.preventDefault)
	        	event.preventDefault();
	        if (event.returnValue) 
	        	event.returnValue = false;

			//触发点击“保存”事件
			$(this).parents('.board-lane-control').find('.btn-save').trigger('click');
		}
	});
	//设置默认输入焦点
	$boardLaneControl.find('textarea[name="plan_name"]').focus();;
	
	//=======设置高度相关=========
	
	//定义函数：计算已占用高度
	function calculateRootHeight() {
		var rootHeight = $boardLaneToolbar.outerHeight(true)+$boardLaneControl.outerHeight(true)
			+($boardLaneListContainer.outerHeight(true)-$boardLaneListContainer.height()) //$boardLaneListContainer的border+padding+margin高度
			+5; //给最底部留空隙
		return rootHeight;
	}	
	//定义函数：设置列表区域最大高度
	function adjustboardLaneListContainerHeight(resizeEventSuffixName) {
		adjustContainerHeight3UsingE($('#board-content-height-input'), $boardLaneListContainer, calculateRootHeight(), false, resizeEventSuffixName);
	}
	//textarea自适应高度
	$boardLaneContainer.find('textarea').autoHeight(function(oldH, newH) {
		if (oldH!=newH) {
			adjustboardLaneListContainerHeight(laneNo);
			//logjs_info('adjustboardLaneListContainerHeight:'+laneNo);
		}
	});
	//执行设置列表区域最大高度
	if ($('#board-content-height-input').length) {
		adjustboardLaneListContainerHeight(laneNo);
	}
	//=======设置高度相关 end =========
	
	
	//自定义滚动条
	customScrollbarUsingE($boardLaneListContainer, 30, true, 'outside', {callbacks:{
		onScroll: function() {
			//logjs_info('release <?php echo $laneNo;?>');
		},
		onScrollStart: function() {
			//logjs_info('start <?php echo $laneNo;?>');
		},
		onInit: function(){
			//logjs_info('init <?php echo $laneNo;?>');
			//设置自定义滚动条初始化完毕状态
			customScrollbarsReady[<?php echo $laneNo;?>].status = true;
		}}
	});
});
</script>