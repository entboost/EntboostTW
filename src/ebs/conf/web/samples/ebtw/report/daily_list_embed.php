<?php 
include dirname(__FILE__).'/../report/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../report/include.php';

	$userId = $_SESSION[USER_ID_NAME];
	//嵌入查询列表页面
	$embed = 1;
	include dirname(__FILE__).'/../report/list.php';
	
	if (isset($json))
		$results = get_results_from_json($json, $tmpObj);
?>

<div class="col-xs-12 report-list">
<div class="col-xs-12 report-list-wrap ebtw-horizontal-nopadding ebtw-vertical-nopadding">
	<div class="report-list-container-outer">
		<div class="report-list-container mCustomScrollbar" data-mcs-theme="minimal-dark">
			<div class="report-list-row blank-row"></div>
		</div>
	</div>	
</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	var ptrType = <?php echo $PTRType;?>;
	var userId = '<?php echo $userId;?>';
	var existentDatas = '<?php echo escapeQuotes(strictJson(json_encode($results?$results:'')));?>';
	
	if (typeof existentDatas==='string') {
		if (existentDatas == '""')
			existentDatas = '[]';
		//已存在的日报记录
		existentDatas = json_parse(existentDatas);
	}

	//更新搜索栏结果状态
	var parameter = createQueryParameter();
	updateSearchContentBar($('.search-content-bar'), parameter.report_work_lk, existentDatas.length);
	
	//渲染界面
	if (Object.prototype.toString.call(existentDatas) === '[object Array]') {
		var $container = $('.report-list-container');
		
		var leftMenuValue = $('#current_left_menu').val();
		if (existentDatas.length==0 && leftMenuValue=='1_2') { //"下级日报"使用非占位模式(记录不存在时不占位置)
			$container.append(laytpl($('#report-list-no-row-script').html()).render({}));
		} else {
			var canCreateEmpty = parameter.report_work_lk?false:true; //当"工作内容"查询条件有效时，不允许创建空白行
			var datas =new Array();
			
			if (!canCreateEmpty || leftMenuValue=='1_2') { //只保留有真正数据的行，不需要创建空白行
				datas = datas.concat(completingReportDatas(userId, existentDatas, false)[0]);
			} else {
				var periodType = $('.period-type-switch.active').attr('id');
				periodType = periodType.substr(periodType.length-1, 1); //'period-type-switch-1';

				//日期范围条件
				var searchTimeEffect = false;
				if ($('.date-period-ctrl span[data-checked="1"]').length>0 && parameter.search_time_s && parameter.search_time_e) {
					searchTimeEffect = true;
				}
				
				var maxExpectedDays = searchTimeEffect?90:7; //periodType==2
				if (periodType==1) maxExpectedDays=1;
				if (periodType==3) maxExpectedDays= searchTimeEffect?90:31;
				var countOfExpectedDays = maxExpectedDays;
				
				if (searchTimeEffect) {
					countOfExpectedDays = calculateDateDiff(new Date(parameter.search_time_s), new Date(parameter.search_time_e))+1;
					if (countOfExpectedDays>maxExpectedDays)
						countOfExpectedDays = maxExpectedDays;
				}
				
				var expectedDays = severalDays(false, countOfExpectedDays, 0, searchTimeEffect?new Date(parameter.search_time_e):new Date()); //期望的日报日期
				//logjs_info(expectedDays.length);
				//过滤超出日期范围的日期，待定：确认是否有必要过滤
				if (searchTimeEffect) {
					var s = new Date(parameter.search_time_s).getTime();
					for (var i=expectedDays.length-1; i>=0; i--) {
						if (expectedDays[i].getTime()<s) {
							expectedDays.splice(i, 1);
							i--;
						}
					}
				}
				//logjs_info(expectedDays.length);
				
				//去除大于今天的期望日期，数组日期元素本来已经是从大到小排列
				var todayStr = $.D_ALG.formatDate(new Date(), 'yyyy-mm-dd');
				var eptDay = expectedDays.shift();
				while(eptDay) {
					var eptDayStr = $.D_ALG.formatDate(eptDay, 'yyyy-mm-dd');
					var compareResult = eptDayStr.localeCompare(todayStr); 
					//logjs_info(eptDayStr+'|'+todayStr+'|'+compareResult);
					if (compareResult>0) {
						eptDay = expectedDays.shift();
						continue;
					} else/* if (compareResult==0)*/ {
						expectedDays.unshift(eptDay)
					}
					break;
				}
				//logjs_info(expectedDays.length);

				//补全相关资料
				if (leftMenuValue=='1_1') { //我的日报
					for (var i=0; i<expectedDays.length; i++) {
						datas = datas.concat(completingReportDatas(userId, existentDatas, true, expectedDays[i])[0]);
					}
				} else if (leftMenuValue=='1_3') { //下级日报
					//获取下级成员的用户资料
					var mysubordinates;
					<?php if (isset($tmpObj->mysubordinates)) {?>
					mysubordinates = '<?php echo escapeQuotes(strictJson(json_encode($tmpObj->mysubordinates)));?>';
					<?php }?>
					//混合部门成员的用户资料
					mysubordinates = blendUserAccountsOfGroups(mysubordinates);
					
					if (mysubordinates) {
						for (var i=0; i<expectedDays.length; i++) {
							var results = completingReportDatas(userId, existentDatas, true, expectedDays[i], mysubordinates);
							var statistic = results.length>1?results[1]:undefined;
							if (statistic)
								datas.push(createStatisticReportData(statistic));

							if (!isWeekend(expectedDays[i]))
								datas = datas.concat(results[0]);
							else { //周末不显示未填写的日报行
								for(var j=0;j <results[0].length; j++) {
									var data = results[0][j];
									if (data.isBlank==1)
										continue;
									datas.push(data);
								}
							}
						}
					}
				}
			}

			if (datas.length==0) {
				$container.append(laytpl($('#report-list-no-row-script').html()).render({message:'没有记录'/*'没有下级成员'*/}));
			} else {
				//遍历渲染界面
				for (var i=0; i<datas.length; i++) {
					var data = datas[i];
					//传人当前用户的编号
					data.logonUserId = userId;
					//标记是否非本人视角(看他人日报视角)
					if (leftMenuValue!='1_1')
						data.isOtherView = true;
					
					createReportListRow(userId, $container, ptrType, data); //创建显示行
				}
				
				//读取各日报的附件数量并更新视图
				getReportAttamentCountsAndRefreshView(datas, $container);

				//创建左侧可点击空白区域，绑定点击事件：点击关闭右侧页
				setTimeout(function() {
					bindDailyReportLeftBlankClick($container);
				}, 1);
				
				//注册事件-鼠标在每记录上悬停
				$container.on('mouseover', '.report-list-row', function() {
					var $element = $(this).find('.content-box-toolbar-wrap');
					$element.removeClass('ebtw-hide'); //显示整个工具栏
				}).on('mouseout', '.report-list-row', function() {
					var $element = $(this).find('.content-box-toolbar-wrap').has('.btn-save.ebtw-hide');
					$element = $element.length>0?$element:$(this).find('.content-box-toolbar-wrap').filter(':not(:has(.btn-save))');
					$element.addClass('ebtw-hide'); //隐藏整个工具栏
				});
				
				//注册事件-点击编辑按钮
				$container.on('click', '.report-list-row div[data-can-edit="1"] .btn-edit', function(e, which) {
					var $detailDivs = $(this).parents('.report-list-row').find('.content-box-row .edit-e').attr('contenteditable', true)/*.addClass('editmode')*/;
					var originContents = new Array();
					for (var i=0; i<$detailDivs.length; i++) {
						originContents.push($($detailDivs[i]).html());
					}
					
					var $originReviewUserIdE = $(this).parents('.report-list-row').find('input[name="review_user_id"]');
					var $originReviewUserNameE = $(this).parents('.report-list-row').find('input[name="review_user_name"]');
					var originReviewUserId = $originReviewUserIdE.val();
					var originReviewUserName = $originReviewUserNameE.val();

					var $uploadListContainer = $(this).parents('.report-list-row').find('.ebtw-file-upload-list');
					
					var whichElement = which||$detailDivs[0];
					if (!($(whichElement).attr('data-toggle') == 'dropdown' || $(whichElement).hasClass('ebtw-file-upload-wrap'))) { // 1.排除选择关联人员的输入框；2.排除上传附件按钮
						cursorMoveToLastInDiv(whichElement); //光标移到第一个编辑框尾部
					}
					
					$(this).addClass('ebtw-hide').parent().find('.btn-save, .btn-undo').removeClass('ebtw-hide') //隐藏编辑按钮，显示保存按钮
						.unbind('click').click(function() { //绑定点击事件
							var $element = $(this);
							
							//定义函数：退出编辑模式
							var exitEditModeFn = function(rollback) {
								$element.parent().find('.btn-save, .btn-undo').addClass('ebtw-hide');
								$element.parent().find('.btn-edit').removeClass('ebtw-hide');
								
								$detailDivs.attr('contenteditable', false)/*.removeClass('editmode')*/;

								//恢复原始值
								if (rollback) {
									for (var i=0; i<$detailDivs.length; i++) {
										$($detailDivs[i]).html(originContents[i]);
									}
									
									$originReviewUserIdE.val(originReviewUserId);
									$originReviewUserNameE.val(originReviewUserName);

									$uploadListContainer.find('li[data-added="1"]').remove();
								}
							};
							
							if ($element.hasClass('btn-save')) { //保存编辑结果
								//准备要保存的值
								$detailDivs.each(function(){
									$(this).prev('input[type="hidden"]').val(convertHtmlToTxt(html_decode($(this).html())));//convertHtmlToTxt($(this).html()));
								});
								
								var $form = $(this).parents('.report-list-row form');
								//var $ptrIdInput = $form.find('input[name="pk_report_id"]');
								var isEdit = $(this).parents('.report-list-row[data-is-edit="1"]').length>0?true:false;
								
								saveReportAction(userId, isEdit, $form, $container, ptrType, exitEditModeFn, null, true);
							} else if ($element.hasClass('btn-undo')) { //取消编辑
								exitEditModeFn(true);
							}
						});
				});
				
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
//	 						if ($userIdElement.val()!=oldUserId)
//	 							$userIdElement.trigger('change');
						});
					}
				});
				
				//注册事件-点击编辑框
				$container.on('click', '.content-box-row div.edit-e:not([contenteditable="true"])', function() {
					$(this).parents('.content-box').find('.content-box-toolbar .btn-edit:not(.ebtw-hide)').trigger('click', this);
				});
				
				//注册事件-点击上传附件按钮
				$container.on('click', '.content-box-row .ebtw-file-upload-wrap', function() {
					$(this).parents('.content-box').find('.content-box-toolbar .btn-edit:not(.ebtw-hide)').trigger('click', this);
				});

				//注册事件-点击小标签标题
				$container.on('click', '.content-box-row .content-box-tab-head', function() {
					var tabType = $(this).attr('data-target-tabtype');
					var $itemElement = $(this).parents('.report-list-item'); 
					//var isNew = $itemElement.hasClass('unreport');
					var reportId = $itemElement.find('input[type="hidden"][name="pk_report_id"]').val();
					//var startTime = $itemElement.find('input[type="hidden"][name="start_time"]').val();
					//logjs_info(isNew + ' ' + reportId + ' ' + startTime);
					if (parseInt(reportId)>0)
						openReportById('daily', 'v', reportId, null, {reserved_active_no:tabType});
				});
				
				//注册事件-点击打开查看日报详情页面，并切换详情页面内的小标签至“评阅”
				$container.on('click', '.btn-open-report-review', function() {
					var tabType = 1;
					var reportId = $(this).parents('.report-list-item').find('input[type="hidden"][name="pk_report_id"]').val();
					if (parseInt(reportId)>0)
						openReportById('daily', 'v', reportId, null, {reserved_active_no:tabType});
				});
			}
		}
	}

	//获取临时暂存的参数，并打开日报详情页面
	if (typeof accessTempKey!='undefined' && accessTempKey!=null) {
		loadTempCustomParameter(accessTempKey, function(customData) {
			if (customData) {
				var accessTempType = customData.int_value;
				var entity = json_parse(customData.str_value);
				
				if (accessTempType==1) {  // 1=自动打开查询详情页面
	    			var openPtrId = entity.open_ptr_id;
	    			var switchTabType = entity.switch_tab_type;
	    			//打开详情页面
	    			openReportById('daily','v', openPtrId, function(){}, (typeof switchTabType!='undefined' && switchTabType!=null)?{reserved_active_no:switchTabType}:undefined);
				}
			} else {
				logjs_info('can not load tempdata for key:'+accessTempKey);
			}
    		//清空临时访问参数
    		accessTempKey = null;
		});
	}
	
	//textarea自适应高度
	//$('.content-box-attr-content-ex>textarea').autoHeight();

	//content-box自适应宽度
	var $elements = $container.find('.report-list-row .report-list-item .content-box');
	$elements.css('width', $elements.parent().width()-85);
	$(window).resize(function(e){
		var $elements = $container.find('.report-list-row .report-list-item .content-box');
		$elements.css('width', $elements.parent().width()-85);
	});
	
	//设置内容区域最大高度
	adjustContainerHeight('report-list-container', 100);
	
	//自定义滚动条
	customScrollbar(".report-list-container");
	
});
</script>