
//var s = "sdfdf#date_to_str(111, ABC, 'abc')fsdjfdj";
//(#[a-zA-Z0-9_]+)\(([^()]+)*\)
//var aaa = s.match(/(#[a-zA-Z0-9_]+)\(([^()]+)*\)/);
//var s = "sss,ABC,'abc'";
// /^(\d+\,)+\d+$/
//var aaa = s.match(/^([^,]+[,]+)+?[^,]+$/);
//alert(aaa);

/**
 * 补全日报列表数据
 * @param {string} userId 当前用户编号
 * @param {array} existentDatas 实际存在的日报数据列表
 * @param {boolean} matchExpectedDay 是否检测期望日期匹配情况：false=不检测(禁止创建空白行)，true=检测(允许创建空白行)
 * @param {date} expectedDay 期望日期
 * @param {array} mysubordinates 下级人员列表
 * @return {array} [0]=补全后的日报数据列表，[1]=下级人员日报完成情况统计
 */
function completingReportDatas(userId, existentDatas, matchExpectedDay, expectedDay, mysubordinates) {
	//var matched = false;
	var targetDatas =new Array();
	var shortExpectedDay;
	var statistic; //完成情况统计
	var subCount = 1;
	
	if (expectedDay) {
		shortExpectedDay = $.D_ALG.formatDate(expectedDay, 'yyyy-mm-dd');
	}
	if (mysubordinates && shortExpectedDay) {
		subCount = mysubordinates.length;
		var formatedDateStrs = popularDate(expectedDay, true);
		statistic = {formatedDateStrs:formatedDateStrs, memberCount:subCount, uncomplete:subCount, weekend:formatedDateStrs[4]};
	}
	for (var i=0; i<subCount; i++) {
		var matched = false;
		var userAccount = (mysubordinates && mysubordinates.length>0)?mysubordinates[i]:undefined;
		
		for (var j=0; j<existentDatas.length; j++) {
			if (!matchExpectedDay || shortExpectedDay==existentDatas[j].start_time.substr(0, 10)) {
				var newData = $.extend({}, existentDatas[j]);
				
				if (mysubordinates) {
					if (userAccount.user_id!=newData.report_uid)
						continue;
					
					newData.showedCreatorName = userAccount.user_name;
					if (statistic)
						statistic.uncomplete--;
				} else if (newData.report_uid!=userId) {
					newData.showedCreatorName = newData.create_name;
				}
				
				targetDatas.push(newData);
				matched = true;
				
				if (matchExpectedDay && !mysubordinates) {
					existentDatas.splice(j, 1);
					break;
				}
			}
		}
		
		if (matchExpectedDay && !matched) {
			targetDatas.push(createEmptyReportData(userAccount?userAccount.user_id:null, userAccount?userAccount.user_account:null, userAccount?userAccount.user_name:null, expectedDay, null, mysubordinates?true:false));
		}
	}

	var results = [targetDatas];
	if (statistic) {
		//处理周末的情况：如果没有任何下级成员填写日报，就不显示每个成员的记录
		if (statistic.weekend && statistic.memberCount==statistic.uncomplete) {
			results = [new Array()];
		}
		//存入统计对象
		results.push(statistic);
	}
	
	return results;
}

//从统计下级人员日报完成情况，创建视图列表行数据对象
function createStatisticReportData(statistic) {
	return $.extend({statistic:true}, statistic);
}

//创建空白的日报/报告数据对象
function createEmptyReportData(reportUid, reportAccount, showedCreatorName, expectedDay, reportId, isBlank) {
	var today = $.D_ALG.formatDate(new Date(), 'yyyy-mm-dd 00:00:00');	
	var obj = {report_id:reportId || ('-'+Math.floor(Math.random() * 10000000))/*随机负值*/,
		report_uid: reportUid,
		user_account: reportAccount,
		showedCreatorName: showedCreatorName,
		status:0,
		start_time:$.D_ALG.formatDate(expectedDay, 'yyyy-mm-dd 00:00:00'),
		stop_time:$.D_ALG.formatDate(expectedDay, 'yyyy-mm-dd 23:59:59'),
		create_time:today,
		allowedActions: [0], //赋予创建的权限代码
	};
	if (isBlank===true)
		obj.isBlank = 1;
		
	return obj;
}

//渲染日报行记录
function createReportListRow(logonUserId, $container, ptrType, data, isOnlyView, isDetailsView, $afterElement, animate, width) {
	if (data.statistic) { //统计数据视图
		var newElement = laytpl($('#report-list-statistic-row-script').html()).render(data);
		if ($afterElement)
			$afterElement.after(newElement);
		else
			$container.append(newElement);
	} else { //详细内容视图
		var ptrId = data.report_id;
		
		data.logonUserId = logonUserId;
		data.formatedStartTimes = popularDate(data.start_time, true, 'mm-dd');
		data.isWeekend = data.formatedStartTimes[4];
		data.isExpired = new Date(data.stop_time).valueOf()+(12*60*60*1000) < new Date(data.create_time).valueOf(); //超过12小时算逾期提交
		data.isOnlyView = isOnlyView;
		data.isNew = ptrId.match('-')?1:0;
		data.canEdit = (data.status!=3?1:0); //已经"评阅回复"的状态不允许修改
		data.canEdit = data.canEdit && (isOnlyView===true?0:1);
		
		data.canEdit = data.canEdit && ((data.allowedActions?(($.inArray(2, data.allowedActions)>-1)?data.canEdit:0 ):0) || (!isOnlyView&&data.isNew)); //验证创建和编辑权限 

		data.isEdit = (!isTypeEmpty(ptrId) && ptrId!=0 && !ptrId.match('-'))?1:0; //待定;
		data.review_user = getShares(1, data, true);
		if (data.review_user && data.review_user.share_uid!=logonUserId)
			data.talkToPerson = 1;
		
		data.animate = animate;
		data.width = width;
		
		var scriptSelector = (data.isBlank==1)?'#report-list-empty-row-script':((isDetailsView===true)?'#daily-report-details-script':'#daily-report-list-row-script');
		var newElement = laytpl($(scriptSelector).html()).render(data);
		if ($afterElement)
			$afterElement.after(newElement);
		else
			$container.append(newElement);
		
		//注册事件-点击新增/删除附件按钮
		var attaType = 0; //文档(本体)附件
		var onlyOne = false;
		var isEdit = (data.isEdit==1);
		var loadExist = (isDetailsView===true)?true:false;//isEdit;
		registerAttachmentActions(isEdit, isOnlyView, ptrType, isEdit?ptrId:0, attaType, '.report-list-row[data-ptrid="'+ptrId+'"] .ebtw-file-upload', function(result, resourceId) {
			if (isEdit)
				layer.msg('上传日报附件成功');
		}, loadExist, onlyOne, '.report-list-row[data-ptrid="'+ptrId+'"] .ebtw-file-upload-list', '.attachment-remove', function() {
			
		});
	}
}

/**
 * 读取各日报/报告的附件数量并更新视图
 * @param datas 日报/报告数组
 * @param $container 视图容器对象(JQuery对象)
 */
function getReportAttamentCountsAndRefreshView(datas, $container) {
	var conditions = new Array();
	for (var i=0; i<datas.length; i++) {
		var data = datas[i];
		if (data.statistic!=true && data.isNew!=1) {
			var condition = {fromType: 10+3, fromId: data.report_id, flag:-1};
			conditions.push(condition);
		}
	}
	
	//读取数量
	$.ebtw.listfiles(conditions, 1, function(result) {
		//更新视图
		for (var i=0; i<result.resources.length; i++) {
			var resource = result.resources[i];
			var $element = $container.find('.report-list-row[data-ptrid="'+resource.from_id+'"] .content-box-tab .content-box-tab-head[data-auto-report="1"]>span');
			$element.parent().append('(<span>'+resource.resource_count+'</span>)');
			$element.remove();
		}
	}, function(err) {
		logjs_err(err);
	});	
}

/**
 * 进入日报/报告详情界面
 * 样例：openReport('daily','v', reportId, dailyStartTime)
 * @param {string} reportType (必填) 报告类型： 'daily'=日报，'report'=普通报告
 * @param {string} action (必填) 操作类型：'v'=查看，'a'=新建，'e'=编辑
 * @param {string} reportId (可选，智能参数) 报告编号
 * @param {string|date} dailyStartTime (可选，智能参数) 日报开始时间；当reportType='daily'且action='a'时有效
 */
function openReport(reportType, action) {
	var argsLen = arguments.length;
	if (argsLen<3) {
		alert('参数数量不匹配，至少需要3个');
		return;
	}
	
	var parameter = {report_type:reportType, view_mode:action};
	if (argsLen==4) {
		if (arguments[2])
			parameter.report_id = arguments[2];
		if (arguments[3])
			parameter.daily_start_time = arguments[3];
	} else {
		var arg = arguments[2];
		if (arg) {
			if (typeof arg ==='number') {
					parameter.report_id = arg;
			} else if (typeof arg ==='object' && arg instanceof Date) {
				parameter.daily_start_time = $.D_ALG.formatDate(arg, 'yyyy-mm-dd hh:ii:ss');
			} else if (typeof arg ==='string') {
				if(arg.match(/[-]?\d+/ig) && !arg.match(/^[0-9]{4}-[0-9]{2}-[0-9]{2}/ig)) {
					parameter.report_id = arg;
				} else {
					parameter.daily_start_time = arg;
				}
			}
		}
	}
	
	showSidepage(getServerUrl() + "report/sidepage_report.php", parameter);
}

/**
 * 进入日报/报告详情界面
 * 样例：openReport('daily','v', reportId...)
 * @param {string} reportType (必填) 报告类型： 'daily'=日报，'report'=普通报告
 * @param {string} action (必填) 操作类型：'v'=查看，'a'=新建，'e'=编辑
 * @param {string} reportId (必填) 报告编号
 * @param {function} callback (可选) 成功打开页面后的回调函数
 * @param {object} additionalParams (可选) 附加参数
 */
function openReportById(reportType, action, reportId, callback, additionalParams) {
	var parameter = {report_type:reportType, view_mode:action, report_id:reportId};
	parameter = $.extend(parameter, additionalParams);
	showSidepage(getServerUrl() + "report/sidepage_report.php", parameter, 'post', callback);
}

//重载日报列表某一行
function reloadReportListRow(logonUserId, isDelete, $form, $container, $reportIdElement, newReportId, oldReportId, closeTitle, checkClose) {
	var refreshRow = function(data, newReportId) {
		var $oldRowElement = $form.parents('.report-list-row');
		//动画切换新旧元素
		var width = $oldRowElement.width()+'px';
		$oldRowElement.animate({left:'-'+width}, 'slow', function() {
			//隐藏旧元素
			$oldRowElement.css('display', 'none');
			
			//创建新元素
			createReportListRow(logonUserId, $container, 3, data, false, false, $oldRowElement, true, width);
			//删除旧元素
			$oldRowElement.remove();
			//读取日报的附件数量并更新视图
			getReportAttamentCountsAndRefreshView([data], $container);
			
			//content-box宽度调整
			var $newRowElement = $container.find('.report-list-row[data-ptrid="'+newReportId+'"]');
			var $element = $newRowElement.find('.report-list-item .content-box');
			$element.css('width', ($element.parent().width()-85)+'px');
			$newRowElement.animate({left:'0px'}, 'slow', function() {
				checkClose(closeTitle, true);
				//绑定点击日报列表界面左侧空白位置事件
				bindDailyReportLeftBlankClick($container, newReportId);
			});
		});
	};
	
	if (!isDelete) {
		executeQueryOne(getServerUrl()+'report/get_one.php', {report_id:newReportId}, function(data) {
			if (data)
				refreshRow(data, newReportId);
			else
				checkClose('新建日报成功，但加载数据失败', true);
		}, function(err) {
			//恢复report_id元素
			$reportIdElement.val(oldReportId);
			$form.prepend($reportIdElement);
			$reportIdElement.parents('report-list-row').attr('data-ptrid', oldReportId);
			
			checkClose(closeTitle, true);						
		});
	} else {
		//创建一个空行，并刷新行视图
		var expectedDay = $form.find('input[name="start_time"]').val();
		var data = createEmptyReportData(null, null, null, new Date(expectedDay));
		refreshRow(data, data.report_id);
	}
}

/**
 * 绑定点击日报列表界面左侧空白位置事件：关闭右侧页
 * @param {object} $container 容器(JQuery对象)
 * @param {string} reportId (可选) 报告编号；如填入非空值，将只绑定指定报告编号行记录的空白区域
 */
function bindDailyReportLeftBlankClick($container, reportId) {
	var part = '.report-list-row';
	if (typeof reportId =='string')
		part = part + '[data-ptrid=' + reportId + ']';
	
	$container.find(part+' .left-box .blank-to-click').each(function() {
		var rowHeight = $(this).parents('.report-list-item').height();
		var datetimeBoxHeight = $(this).prev('.datetime-box').outerHeight(true);
		$(this).height(rowHeight-datetimeBoxHeight);
	}).click(function(){
		closeSidepage();
	});
}

/**
 * 点击保存报告
 * @param {string} 当前用户的编号
 * @param {boolean} isEdit 是否编辑
 * @param {object} $form 表单对象(JQuery对象)
 * @param {object} $container 容器(JQuery对象)
 * @param {number} ptrType 文档类型：3=日报或报告
 * @param {function} exitEditModeFn (可选) 退出编辑模式函数
 * @param {function} successHandle 执行成功后的回调函数；如填入本回调函数，内部默认的函数将不执行
 * @param {boolean} reloadAfterSuccess 执行成功后是否执行重载视图行记录
 */
function saveReportAction(logonUserId, isEdit, $form, $container, ptrType, exitEditModeFn, successHandle, reloadAfterSuccess) {
	var actionTitle = '';
	var page = '';
	var $reportIdElement = $form.find('input[name="pk_report_id"]');
	
	//已完成工作
	var completedWorkContent = $.trim($form.find('input[name="completed_work"]').val());
	if (!checkContentLength(ptrType, 'completed_work', completedWorkContent))
	    return;
	//未完成工作
	var uncompletedWorkContent = $.trim($form.find('input[name="uncompleted_work"]').val());
	if (!checkContentLength(ptrType, 'uncompleted_work', uncompletedWorkContent))
	    return;	
	
	var oldPtrId = $reportIdElement.val();
	var ptrId = $reportIdElement.val();
	
	if (isEdit) {
		actionTitle = '编辑';
		page = 'saveUpdate.php';
	} else {
		actionTitle = '新建';
		page = 'saveCreate.php';
		
		//暂时移除该元素
		$reportIdElement.remove();
	}
	var url = getServerUrl()+'report/'+page;
	
	//执行保存
	var loadIndex = layer.load(2);
	callAjax(url, $form.serialize(), function(returnDatas) {
		var result = didLoadedDataPreprocess('original', returnDatas, true);
		var title='';
		var completeUploads = new Array(); //暂存已完成的上传(无论失败与否)
		
		//定义函数：检测关闭正在加载的界面
		function checkClose(closeTitle, close, i, total, p1, p2, p3, saveSuccessFn) { //参数p1、p2、p3在本函数实现内并没有含义，只为最后一个参数提供兼容位置
			//直接关闭，不做任何检测
			if (close) {
				layer.close(loadIndex);
				
				if (exitEditModeFn)
					exitEditModeFn();
				layer.msg(closeTitle);
			}

			//检测是否关闭
			completeUploads.push(i);
			if (completeUploads.length==total) {
				if (saveSuccessFn) {
					saveSuccessFn(ptrId, oldPtrId, closeTitle, true);
				} else {
					layer.close(loadIndex);
					
					if (exitEditModeFn)
						exitEditModeFn();
					layer.msg(closeTitle);
				}
			}
		}
		
		if (typeof result != 'boolean') {
			//查询单个日报记录，并更新界面
			var saveSuccessFn = function(newReportId, oldReportId, closeTitle, close) {
				if (successHandle)
					successHandle();
				
				var $listContainer = $('.report-list-container');
				if ($listContainer.length>0 && reloadAfterSuccess) {
					var $listForm = $listContainer.find('.report-list-row[data-ptrid="'+oldReportId+'"] form');
					var $listReportIdElement = $listForm.find('input[name="pk_report_id"]');
					reloadReportListRow(logonUserId, false, $listForm, $listContainer, $listReportIdElement, newReportId, oldReportId, closeTitle, checkClose);
				} else {
					checkClose(closeTitle, true);
				}
				
				//刷新菜单角标(badge)
				refreshPTRMenuBadges([1], 3, 'daily');
			};
			
			if (isEdit) { //编辑
				if (result.affected==1)
					title = actionTitle+'日报成功';
				else 
					title = actionTitle+'日报没有效果';
				
				saveSuccessFn(ptrId, oldPtrId, title, true);
				//checkClose(title, true);
				//refreshPTRMenuBadges([1, 2], 3, 'daily'); //刷新菜单角标(badge)
			} else { //新建
				ptrId = result.id;
				title = actionTitle+'日报成功';
				
				//上传附件
				var $liElements = $form.find('.ebtw-file-upload-list li');
				var fileCount = $liElements.length;
				if (fileCount==0) {
					//checkClose(title, true);
					saveSuccessFn(ptrId, oldPtrId, title, true);
				} else {
					var fromType = 10 + parseInt(ptrType);
					
					//遍历上传文件
					var liElements = new Array();
					$liElements.each(function(){
						liElements.push(this);
					});
					var i=0;
					var attaType = 0;
					executeSendfile(fromType, ptrId, attaType, liElements, fileCount, i, title, true, function(closeTitle, close, i, total, p1, p2, p3) {
						checkClose(closeTitle, close, i, total, p1, p2, p3, function() {
							saveSuccessFn(ptrId, oldPtrId, closeTitle, close);
						});
					});
				}
				//refreshPTRMenuBadges([1, 2], 3, 'daily'); //刷新菜单角标(badge)
			}
		} else {
			layer.close(loadIndex);
			layer.msg(actionTitle+'日报失败', {icon:2});
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (!isEdit) {
			//恢复report_id元素
			$form.prepend($reportIdElement);
		}
		
		layer.close(loadIndex);
		layer.msg(actionTitle+'日报失败', {icon:2});
	});
}

/**
 * 创建修改重要程度弹出菜单的Html脚本
 * @param {int} important 重要程度
 * @param {string} 附加的Css Class
 * @param {string} useFor 用于何处：'BoardLane'=看板泳道，'DtGrid'=DtGrid列表视图
 * @param {number|boolean} canEdit 是否允许弹出修改菜单
 * @return {string} 生成的Html脚本
 */
function createModifyImportantMenuScript(important, extendCssClass, useFor, canEdit) {
	var data = {important:important};
	data.importantDict = (useFor=='BoardLane')?dictOfImportantInBoardLane(data.important):((useFor=='DtGrid')?dictOfImportantInGtGrid(data.important):{});
	data.canEdit = canEdit;
	data.extendCssClass = extendCssClass;
	
	return laytpl($('#ptr-change-important-level-menu-script').html()).render(data);
}

//获取"重要程度"显示属性的描述对象(用于DtGrid列表视图)
function dictOfImportantInGtGrid(important) {
	if (important==undefined)
		return;
	
	var importants = {0:'普通', 1:'重要', 2:'紧急'};
	
	switch(parseInt(important)) {
	case 0:
		delete importants[0];
		return {title:'普通', css:''/*'ebtw-grade-tab-important0'*/, importants:importants};
		break;
	case 1:
		delete importants[1];
		return {title:'重要', css:''/*'ebtw-grade-tab-important1'*/, importants:importants};
		break;
	case 2:
		delete importants[2];
		return {title:'紧急', css:''/*'ebtw-grade-tab-important2'*/, importants:importants};
		break;
	}
}

//获取"重要程度"显示属性的描述对象(用于看板泳道)
function dictOfImportantInBoardLane(important) {
	if (important==undefined)
		return;
	
	var importants = {0:'普通', 1:'重要', 2:'紧急'};
	
	switch(parseInt(important)) {
	case 0:
		delete importants[0];
		return {title:'普通', css:'swatches-color-important0', visibility:false, importants:importants};
		break;
	case 1:
		delete importants[1];
		return {title:'重要', css:'swatches-color-important1', importants:importants};
		break;
	case 2:
		delete importants[2];
		return {title:'紧急', css:'swatches-color-important2', importants:importants};
		break;
	}
}

//获取"评审、评阅"显示属性的描述对象(用于看板泳道)
function dictOfApproveInBoardLane(ptrType) {
	if (ptrType==1) {
		return {title:'评审计划', backgroundColor:'rgb(0,112,192)'};
	} else if (ptrType==3) {
		return {title:'评阅报告', backgroundColor:'rgb(0,176,80)'};
	}
}

//获取任务归属"我提交的、我负责的、我参与的"显示属性的描述对象(用于看板泳道)
function dictOfOwnerOrSharerInBoardLane(shareType) {
	switch(parseInt(shareType)) {
	case 0: //我提交的
		return {title:'我提交的', backgroundColor:'rgb(0,112,192)'};
		break;
	case 2: //我参与的
		return {title:'我参与的', backgroundColor:'rgb(118,146,60)'};
		break;
	case 5: //我负责的
		return {title:'我负责的', backgroundColor:'rgb(112,48,160)'};
		break;
	}
}

//获取“任务进度”显示属性的描述对象(用于看板泳道)
function dictOfProgress(ptrType, data) {
	if (ptrType==2) {
		var expired = new Date(data.stop_time).getTime()<new Date().getTime()
		return {status:data.status, workTime:formatMinutesToHours(data.work_time), percentage:data.percentage, css:expired?'swatches-color-danger':'swatches-color-info'};
	}
}

//创建看板泳道功能子菜单数据
function menuItemsInInBoardLane(ptrType, data, allowedActions) {
	var menuItems = new Array();
	var status = parseInt(data.status);
	var buttonDatas;
	
	switch(parseInt(ptrType)) {
	case 1: //计划
        switch (status) {
        case 0://新建未阅
        case 1://未处理
        	buttonDatas = createButtonDatas(0, allowedActions, [2,21,1/*,3*/]);
			for (var i=0; i< buttonDatas.btns.length; i++) {
				var buttonData = buttonDatas.btns[i];
				if (buttonData.dataType==3) {
					buttonData.name = buttonData.name2;
				}
			}
            break;
        case 2://评审中
        case 3:
        	buttonDatas = createButtonDatas(0, allowedActions, [21,4,5,6]);
            break;
        case 4://评审通过
        	buttonDatas = createButtonDatas(0, allowedActions, [21]);
        	break;
        case 5://评审拒绝
        	buttonDatas = createButtonDatas(0, allowedActions, [2,21,7]);
        	break;
        default://其它
//        	if (isDeleted)
//        		buttonDatas = createButtonDatas(0, allowedActions, [3,8]);
            break;
        }
		break;
	case 2: //任务
        switch (status) {
        case 0://未查阅
        case 1://未开始
        	buttonDatas = createButtonDatas(0, allowedActions, [23,22,3]);
            break;
        case 2://进行中
        	buttonDatas = createButtonDatas(0, allowedActions, [23,22,10]);
            break;
        case 3://已完成
        	buttonDatas = createButtonDatas(0, allowedActions, [23]);
        	break;
        case 4://已中止
        	break;
        default://其它
            break;
        }
		break;
	case 3: //报告
		switch (status) {
		case 0://默认（未提交评阅）
			buttonDatas = createButtonDatas(0, allowedActions, [3]);
			break;
		case 1://提交评阅未读
		case 2://提交评阅已读
			buttonDatas = createButtonDatas(0, allowedActions, [3,4]);
			if (status==2) {
				buttonDatas = createButtonDatas(0, allowedActions, [4]);
			}
			for (var i=0; i< buttonDatas.btns.length; i++) {
				var buttonData = buttonDatas.btns[i];
				if (buttonData.dataType==4) {
					buttonData.name = buttonData.name2;
				}
			}
			break;
		default://其它
            break;
		}		
		break;
	}
	
	if (buttonDatas)
		return buttonDatas.btns;
}

/**
 * 预处理一个泳道数据
 * @param {string} userId 当前用户编号
 * @param {string|int} ptrType 文档类型：1=计划，2=任务, 3=报告，其它组合(例如：1_3、1_2等)
 * @param {object} data 数据对象
 */
function processingBoardLaneData(userId, ptrType, data) {
	var ptrTypeMatched = false;
	if (typeof ptrType ==='string') {
		if (isNaN(ptrType)/*ptrType.match(/[_]+/ig)*/) { //多种类型组合，以下划线_连接(非纯数字)
			ptrTypes = ptrType.split('_');
			for(var i=0;i<ptrTypes.length;i++) {
				if (ptrTypes[i]==1 && data.plan_id!=undefined) {
					ptrType = 1;
					ptrTypeMatched = true;
					break;
				}
				if (ptrTypes[i]==2 && data.task_id!=undefined) {
					ptrType = 2;
					ptrTypeMatched = true;
					break;
				}
				if (ptrTypes[i]==3 && data.report_id!=undefined) {
					ptrTypeMatched = true;
					ptrType = 3;
					break;
				}
			}
		} else {
			ptrTypeMatched = true;
		}
	}
	
	if (ptrTypeMatched) {
		if (data.ptr_type==undefined)
			data.ptr_type = ptrType;
	} else {
		ptrType = data.ptr_type;
	}
	
	//重要程度
	data.importantDict = dictOfImportantInBoardLane(data.important);
	//检测进度
	data.dictOfProgress = dictOfProgress(ptrType, data);
	//验证操作权限：编辑
	if (data.allowedActions && $.inArray(2, data.allowedActions)>-1) {
		data.canEdit = 1;
	}
	
	//创建子菜单数据
	data.menuItems = menuItemsInInBoardLane(ptrType, data, data.allowedActions);
	
	switch(parseInt(ptrType)) {
	case 1: //计划
		if (data.plan_id!=undefined)
			data.ptr_id = data.plan_id;
		if (data.plan_name!=undefined)
			data.ptr_name = data.plan_name;
		//验证是否有"标为完成"的操作权限和是否符合操作场景
		if ($.inArray(9, data.allowedActions)>-1)
			data.canComplete = ($.inArray(parseInt(data.status), [0,1,4,5])>-1)?1:0;
		break;
	case 2: //任务
		if (data.task_id!=undefined)
			data.ptr_id = data.task_id;
		if (data.task_name!=undefined)
			data.ptr_name = data.task_name;
		//验证是否有"标为完成"的操作权限和是否符合操作场景
		if ($.inArray(9, data.allowedActions)>-1)
			data.canComplete = ($.inArray(parseInt(data.status), [0,1,2])>-1)?1:0;
		
		//'关注'操作相关
		data.canFavorite = 1; //只有任务才有"关注"的操作
		if (data.shares!=undefined && data.shares['4']) {
			var shares = data.shares['4'];
			for (var i=0; i<shares.length; i++) {
				var share = shares[i];
				if (share.share_uid==userId)
					data.favorite = 1;
			}
		}
		break;
	case 3: //报告
		if (data.report_id!=undefined)
			data.ptr_id = data.report_id;
		if (data.completed_work!=undefined)
			data.ptr_name = data.completed_work;
		break;
	}
	
	return data;
}

/**
 * 输入内容及选择人员对话框
 * @param {string} prefix 前缀
 * @param {string} title 对话框标题
 * @param {object} parameter 参数对象
 * @param {number} enablePart (可选) 界面显示控制码：0、NULL或不填=显示全部，1=显示第一部分，2=显示第二部分
 * @param {number} requiredPart (可选) 控制输入内容是否必需的控制代码：0、NULL或不填=不检测，1=第一部分必需，2=第二部分必需，3=全部必需
 * @param {function} (可选) successHandle 执行成功后回调函数
 * @param {function} (可选) errorHandle 执行失败后回调函数
 */
function promptContentAndSelectPersonUsingLayer(prefix, title, parameter, enablePart, requiredPart, successHandle, errorHandle) {
	//定义函数：显示输入选择界面
	var promptFunc = function(prefix, persons, selectedHandle, cancelHandle) {
		for (var i=0; i<persons.length; i++) {
			var person = persons[i];
			person.name = person.username+'--'+person.dep_name
		}
		
		var data = {prefix:prefix, personTitle:(parameter.personTitle||'选择人员'), persons:persons, contentTitle:(parameter.contentTitle||'说明内容'), contentPlaceholder:(parameter.contentPlaceholder||'说明内容')};
		if (!enablePart) {
			data.part1 = data.part2 = 1;
		} else if (enablePart==1) {
			data.part1 = 1;
		} else if (enablePart==2) {
			data.part2 = 1;
		}
		
		layer.open({type: 1,
			title: title,
			area: ['380px', '300px'], //宽高
			content: laytpl($('#input-content-and-select-person-script').html()).render(data),
			btn: ['确定', '取消'],
			yes: function(index, layero) {
				var selectedUserId = data.part1?($(layero).find('input[name="'+prefix+'_user_id"]').val()):undefined;
				var selectedUserName = data.part1?($(layero).find('input[name="'+prefix+'_user_name"]').val()):undefined;
				var inputedContent = data.part2?($(layero).find('textarea[name="'+prefix+'_content"]').val()):undefined;  //convertHtmlToTxt();
				
				if (requiredPart==3) {
					if (selectedUserId.length==0) {
						layer.msg('"'+data.personTitle+'"不能为空', {icon:2});
						return false;
					}					
					if (inputedContent.length==0) {
						layer.msg('"'+data.contentTitle+'"不能为空', {icon:2});
						return false;
					}
				} else if (requiredPart==2) {
					if (inputedContent.length==0) {
						layer.msg('"'+data.contentTitle+'"不能为空', {icon:2});
						return false;
					}
				} else if (requiredPart==1) {
					if (selectedUserId.length==0) {
						layer.msg('"'+data.personTitle+'"不能为空', {icon:2});
						return false;
					}
				}
				
				layer.close(index);
				
				if (selectedHandle)
					selectedHandle(inputedContent, selectedUserId, selectedUserName);
			},
		  	success: function(layero, index) {
		  		var selector = '#'+prefix+'_user';
				//注册事件-选中人员
		  		$(layero).find(selector+'>ul.dropdown-menu').on('click', 'li>a', function(e) {
					var $parent = $(this).parents(selector);
					var $userIdElement = $parent.children('input[name="'+prefix+'_user_id"]');
					var $userNameElement = $parent.children('input[name="'+prefix+'_user_name"]');
					var oldUserId = $userIdElement.val();
					var oldUserName = $userNameElement.val();
					var newUserId = $(this).attr('data-userid');
					$userIdElement.val((newUserId=='0')?'':newUserId);
					
					$userNameElement.val($(this).attr('data-username'));
				});
			}
		});		
	};
	
	//加载“我的上级”列表
	var loadIndex = layer.load(2);
	loadResults(getServerUrl()+'useraccount/myManagers.php', null, function(results) {
		layer.close(loadIndex);
		promptFunc(prefix, results, successHandle, errorHandle);
	}, function(error) {
		layer.close(loadIndex);
		if (errorHandle) 
			errorHandle(error);
	});
}
