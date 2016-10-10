
//默认分页最大记录数
var defaultPageSize = 100;

//定义计划、任务、报告等访问目录
var PTR_RootFolder ={1:'plan', 2:'task', 3:'report'};
//定义计划、任务、报告等主键
var PTR_PKFieldName = {1:'plan_id', 2:'task_id', 3:'report_id'};

/**
 * 刷新菜单badge
 * @param url 远程服务访问地址
 * @param queryType 查询类型
 * @param {object} parameters (可选) 访问远程服务提交参数
 */
function refreshMenuBadge(url, queryType, parameter, extend) {
	loadRecordCount(url, parameter, function(count) {
		var $element;
		if (queryType==10)
			$element = $('.recycle_bin >span.ebtw-badge');
		else {
			if (extend && extend.reserved_query_of_calss) {
				$element = $('.ptr-class-menu>.ptr_class_item[data-ptr-class-id="'+extend.class_id+'"]>span.ebtw-badge');
			} else 
				$element = $('.query_type[type="' + queryType + '"] >span.ebtw-badge');
		}
		if (count==0)
			$element.html('');
		else 
			$element.html(count);
    }, function(errType){});
}

//定义(计划、任务、报告等)菜单项角标数值访问字典数据
var PTRMenuBadges ={
	plan:{ //计划
		1: {url:'is_deleted=0&request_query_type=1&request_for_count=1', queryType:1, parameters: {status_uncomplete:1}}, //个人计划
		2: {url:'is_deleted=0&request_query_type=2&request_for_count=1', queryType:2, parameters: {status:2}}, //等待我评审的计划
		3: {url:'is_deleted=0&request_query_type=3&request_for_count=1', queryType:3, parameters: {read_flag:0}}, //共享给我的未阅计划
		4: {url:'is_deleted=0&request_query_type=4&request_for_count=1', queryType:4, parameters: {status_uncomplete:1}}, //下级计划
		5: {url:'is_deleted=0&request_query_type=5&request_for_count=1', queryType:5, parameters: {status_uncomplete:1}}, //团队计划
		10: {url:'is_deleted=1&request_query_type=1&request_for_count=1', queryType:10}, //回收站
		100: {url:'request_query_type=1&request_for_count=1', queryType:1, parameters: {status_uncomplete:1, is_deleted:0}}, //计划分类
	},
	task:{ //任务
		1: {url:'request_query_type=1&request_for_count=1', queryType:1, parameters: {status_uncomplete:1}}, //提交的任务
		2: {url:'request_query_type=2&request_for_count=1', queryType:2, parameters: {status_uncomplete:1}}, //负责的任务
		3: {url:'request_query_type=3&request_for_count=1', queryType:3, parameters: {status_uncomplete:1}}, //参与的任务
		4: {url:'request_query_type=4&request_for_count=1', queryType:4, parameters: {status_uncomplete:1}}, //关注的任务
		5: {url:'request_query_type=5&request_for_count=1', queryType:5, parameters: {status_uncomplete:1}}, //共享的任务
		6: {url:'request_query_type=6&request_for_count=1', queryType:6, parameters: {status_uncomplete:1}}, //下级任务
		20: {url:'request_query_type=20&request_for_count=1', queryType:20, parameters: {status_uncomplete:1}}, //团队任务
		100: {url:'request_query_type=1&request_for_count=1', queryType:1, parameters: {status_uncomplete:1, is_deleted:0}}, //任务分类
	},
	report_daily:{ //日报
		1: {url:'request_query_type=1&request_for_count=1&daily=1', queryType:1, parameters: {this_month:1}}, //我提交的日报(本月创建的所有日报)
		2: {url:'request_query_type=2&request_for_count=1&daily=1', queryType:2, parameters: {status:1}}, //要我评阅的日报(1:提交评阅)
		3: {url:'request_query_type=3&request_for_count=1&daily=1', queryType:3}, //下级日报
	},
	report:{ //报告
		
	},
};
/**
 * 刷新计划菜单角标
 * @param {Array} items 菜单项数组，例如：[0,2,3]
 * @param {number} ptrType 类型：1=计划，2=任务，3=报告
 * @param {mixed} extend1 扩展参数1
 * @param {mixed} extend2 扩展参数2
 */
function refreshPTRMenuBadges(items, ptrType, extend1, extend2) {
	var folder = PTR_RootFolder[ptrType];
	for (var i=0; i<items.length; i++) {
		var item = PTRMenuBadges[extend1?(folder+'_'+extend1):folder][items[i]];
		refreshMenuBadge(getServerUrl()+folder+'/list.php?'+item.url, item.queryType, $.extend({}, item.parameters, extend2), extend2);
	}
}

//定义日志操作对照字典
var OperateRecordDict = {
	1:{name:'添加附件', fileResourceId:'op_data', fileName:'op_name'},
	2:{name:'删除附件', title:'op_name'},
	3:{/*name:'评论',*/ detail: 'remark', operate:'添加附件', fileResourceId:'op_data', fileName:'op_name', showModifyTime:true, canEdit:1, canDelete:1},
	4:{name:'删除评论', title:'op_name'},
	5:{name:'删除评论附件', title:'op_name'},
	10:{name:'指派负责人', title:'op_name'},
	11:{name:'添加参与人', title:'op_name'},
	12:{name:'删除参与人', title:'op_name'},
	13:{name:'添加共享人', title:'op_name'},
	14:{name:'删除共享人', title:'op_name'},
	15:{name:'创建IM临时讨论组', title:'op_name'},
	16:{name:'解散IM临时讨论组', title:'op_name'},
	20:{name1:'申请评审，评审人', name3:'申请评阅，评阅人', title:'op_data', title_1:'op_name', title_2:'op_account', conversion:function(opData, opName, opAccount, logonUserId) {
		//点击发起聊天的脚本
		var html  = '<span '+ (logonUserId!=opData?('class="talk-to-person" data-talk-to-uid="'+opData+'"'):'') +(opAccount?' title="'+opAccount+'('+opData+')"':'')+'>'+opName+'</span>';
		return html;
	}, detail: 'remark'},
	21:{name1:'评审人已查看', name2:'负责人已查看', name3:'评阅人已查看'},
	22:{name1:'评审通过', name3:'评阅回复', detail: 'remark'},
	23:{name:'评审拒绝', detail: 'remark'},
	24:{name:'撤销申请'},
	30:{name:'新负责人已阅'},
	31:{name:'上报进度', detail: 'remark', title:'op_data', extendTitle:'%', canEdit:1},
	32:{name:'上报工时', detail: 'remark', title:'op_data', extendTitle:'小时', conversion:function(value) {
		return formatMinutesToHours(value);
	}, opTime:true, opTimeTitle:'工作时间', opTimeFormat:'yyyy-mm-dd', canEdit:1},
	33:{name:'中止任务', detail: 'remark'},
	34:{name:'标为完成'},
	35:{name:'新参与人已阅'},
	50:{name:'新建计划', pMainShowPtrSource:true, operateConversion:function(userName, createTime, ptrLinkName) {
		if (userName!='我')
			userName = '['+userName+'] ';
		else 
			userName = '';
		if (typeof ptrLinkName!='undefined' && ptrLinkName.length>0) {
			return /*userName + createTime.substr(0,16) + */' 创建计划：' + ptrLinkName;
		} else {
			return /*userName + createTime.substr(0,16) + */' 创建了该计划';
		}
	}},
	51:{name:'修改计划', pMainShowPtrSource:true},
	52:{/*name:'计划转任务',*/ title:'op_name', id:'op_data', convParamUsingCallback:true, conversion:function(/*value*/) {
		var numargs = arguments.length;
		if (numargs.length==0)
			return '';
		
		var part0 = '计划转任务：';
		var value = arguments[0];
		
		if (typeof value == 'string')
			return part0+value;
		else if (typeof value == 'function') {
			var fromType = 2;
			return part0+value(fromType);
		}
	}},
	53:{/*name:'计划转任务',*/ title:'op_name', id:'op_data', convParamUsingCallback:true, conversion:function() {
		var numargs = arguments.length;
		if (numargs.length==0)
			return '';
		
		var part0 = '计划转任务，源于计划：';
		var value = arguments[0];
		
		if (typeof value == 'string')
			return part0+value;
		else if (typeof value == 'function') {
			var fromType = 1;
			return part0+value(fromType);
		}
	}},
	60:{name:'新建任务', pMainShowPtrSource:true, operateConversion:function(userName, createTime, ptrLinkName) {
		if (userName!='我')
			userName = '['+userName+'] ';
		else 
			userName = '';
		if (typeof ptrLinkName!='undefined' && ptrLinkName.length>0) {
			return /*userName + createTime.substr(0,16) + */' 创建任务：' + ptrLinkName;
		} else {
			return /*userName + createTime.substr(0,16) + */' 创建了该任务';
		}
	}},
	61:{name:'修改任务', pMainShowPtrSource:true},
	62:{name:'拆分子任务', title:'op_name'},
	70:{name:'新建日报', pMainShowPtrSource:true, operateConversion:function(userName, createTime, ptrLinkName) {
		if (userName!='我')
			userName = '['+userName+'] ';
		else 
			userName = '';
		if (typeof ptrLinkName!='undefined' && ptrLinkName.length>0) {
			return /*userName + createTime.substr(0,16) + */' 填写日报：' + ptrLinkName;
		} else {
			return /*userName + createTime.substr(0,16) + */' 填写日报';
		}
	}},
	71:{name:'修改日报'},	
};


//定义查询列表行快捷按钮字典
var ButtonDatas = {	
	//0:{dataType: 0, iconClass:'glyphicon glyphicon-pencil', name:'创建'},
	1:{dataType: 1, iconClass:'glyphicon glyphicon-arrow-up', name:'申请评审', name2:'提交评阅'},
	2:{dataType: 2, iconClass:'glyphicon glyphicon-edit', name:'编辑'},
	3:{dataType: 3, iconClass:'glyphicon glyphicon-remove', iconClass2:'glyphicon glyphicon-trash', name:'删除', name2:'移入回收站'},
	4:{dataType: 4, iconClass:'glyphicon glyphicon-ok', name:'评审通过', name2:'评阅回复'},
	5:{dataType: 5, iconClass:'glyphicon glyphicon-ban-circle', name:'评审拒绝'},
	6:{dataType: 6, iconClass:'glyphicon glyphicon-repeat', name:'撤销申请'},
	7:{dataType: 7, iconClass:'glyphicon glyphicon-arrow-up', name:'重新上报'},
	8:{dataType: 8, iconClass:'glyphicon glyphicon-repeat', name:'恢复'},
	9:{dataType: 9, iconClass:'glyphicon glyphicon-ok-circle', name:'标为完成'},
	10:{dataType: 10, iconClass:'glyphicon glyphicon-stop', name:'中止任务'},
	13:{dataType: 13, iconClass:'glyphicon glyphicon-heart-empty favorite', iconClass2:'glyphicon glyphicon-heart favorite', name:' 关注', name2:' 取消关注'},
	
	21:{dataType: 21, iconClass:'glyphicon glyphicon-random', name:'计划转任务'},
	22:{dataType: 22, iconClass:'glyphicon glyphicon-circle-arrow-up', name:'上报进度'},
	23:{dataType: 23, iconClass:'glyphicon glyphicon-time', name:'上报工时'},
	24:{dataType: 24, iconClass:'glyphicon glyphicon-ban-circle', name:'拆分子任务'},
};
/**
 * 按需创建快捷按钮
 * @param {number} colspan 跨行数
 * @param {array} allowedActions 操作权限
 * @param {array} types 快捷按钮dataType数组
 * @param {object} additionalDataMap 附加数据Map
 * @returns {Array}
 */
function createButtonDatas(colspan, allowedActions, types, additionalDataMap) {
	var btns = new Array();
	for (var i=0; i<types.length; i++) {
		var btn = ButtonDatas[types[i]];
		if (Object.prototype.toString.call(allowedActions) === '[object Array]' && $.inArray(btn.dataType, allowedActions)>=0) {
			if (additionalDataMap) {
				//logjs_info(types[i]);
				//logjs_info(additionalDataMap);
				if (types[i] in additionalDataMap) {
					btn['data'] = additionalDataMap[types[i]];
				}
			}
			btns.push($.extend({},btn));
		}
	}
	return {colspan: colspan, btns: btns};
}

//定义右侧标签页内容格式类型字典
var SideTabTypes ={
	'opr':1, //操作日志
	'ass':2, //关联计划、关联任务
	'su' :3, //关联用户
	'att':4, //附件
	'auto_rp_opr' :5, //自动汇报内容(相关计划、任务的操作日志)
};

/**
 * 获取文档(计划、报告)周期的提示语
 * @param {number} ptrType 文档类型：1=计划，3=报告
 * @param {number} period 周期
 * @param {date|string} startTime 开始时间
 * @param {date|string} (可选) stopTime 结束时间
 */
function getTipsOfPTRPeriod(ptrType, period, startTime, stopTime) {
	var ptrTypeName = ptrType==1?'计划':'报';
	var startTimeStr;
	var stopTimeStr;
	//startTime
	if(typeof startTime =='string') {
		startTimeStr = startTime;
		startTime = new Date(startTime);
	} else {
		startTimeStr = $.D_ALG.formatDate(startTime, 'yyyy-mm-dd hh:ii:ss');
	}
	//stopTime
	if(typeof stopTime =='string') {
		stopTimeStr = stopTime;
		stopTime = new Date(stopTime);
	} else {
		stopTimeStr = $.D_ALG.formatDate(stopTime, 'yyyy-mm-dd hh:ii:ss');
	}
	
	var part0;
	var part1;
	switch(period) {
	case 1: //2016-06-28 日计划
		part0 = startTimeStr.substr(0, 10);
		part1 = '日'+ptrTypeName;
		break;
	case 2: //2016年第27周(06月27日~07月03日) 周计划
		var weekNumber = $.D_ALG.theWeekNumber(startTime);
		var year = startTime.getFullYear();
		if (weekNumber==0) {
			weekNumber = 52;
			year--;
		}
		part0 = year + '年' + '第'+ weekNumber + '周('+$.D_ALG.formatDate(startTime, 'mm月dd日')+'~'+$.D_ALG.formatDate(stopTime, 'mm月dd日')+')';
		part1 = '周'+ptrTypeName;
		break;
	case 3: //2016年6月 月计划
		part0 = $.D_ALG.formatDate(startTime, 'yyyy年mm月');
		part1 = '月'+ptrTypeName;
		break;
	case 4: //2016年第2季度(04月01日~06月30日) 季计划
		part0 = startTimeStr.substr(0, 4) + '年' + '第' + (startTime.getMonth()/3+1) + '季度('+$.D_ALG.formatDate(startTime, 'mm月dd日')+'~'+$.D_ALG.formatDate(stopTime, 'mm月dd日')+')';
		part1 = '季'+ptrTypeName;
		break;
	case 5: //2016年 年计划
		part0 = $.D_ALG.formatDate(startTime, 'yyyy年');
		part1 = '年'+ptrTypeName;
		break;
	case 6: //2016-06-28 2016-06-29 自定义计划
		if (ptrType==1) { //只有计划才有自定义
			part0 = startTimeStr.substr(0, 10)+' '+stopTimeStr.substr(0, 10);
			part1 = '自定义计划';
		}
		break;
	}
	
	return [part0, part1];
}
//alert(getTipsOfPTRPeriod(1, 6, '2016-01-05 00:00:00', '2016-07-13 23:59:59'));

/**
 * 注册事件-新建分类
 * @param {number} classType 分类类型：1=计划，2=任务，3=报告
 * @param {string} btnSelector 新建按钮选择器
 * @param {function} completeFn 分类子菜单生成完毕后的回调函数
 * @param {function} deleteFn 删除分类成功后回调函数
 * @param {function} codeTableFn 加载对照表完毕后的回调函数
 * @param {function} addedFn 新建分类成功后回到函数
 */
function registerAddPTRClass(classType, btnSelector , completeFn, deleteFn, codeTableFn, addedFn) {
	$(btnSelector).unbind('click').bind('click', function(e) {
		//阻止事件传递
		stopPropagation(e);
		
		var activeClassId = $(this).parent().parent().find('a.active').attr('data-ptr-class-id');
		customPrompt('输入分类名称', {formType:0, maxlength:20, value:''}, true, false, function(text, index, layero) {
			text = $.trim(text);
			if (text.length>0) {
				var loadIndex = layer.load(2);
				//执行新建函数
				executeCreate(getServerUrl()+'classification/saveCreate.php', {class_type:classType, class_name:text}, function(id) {
					if (id) {
						layer.msg('新建分类成功');
						//重新加载
						loadPTRClassAndCreateClassMenu(classType, false, true, function(codeTable) {
							if (activeClassId!=undefined) {
								$headElement.parent().find('a.ptr_class_item[data-ptr-class-id="'+activeClassId+'"]').addClass('active');
								$('#ptr_class_input').val(activeClassId);
							}
							
							if(addedFn)
								addedFn(id, text, classType);
							
							if (completeFn)
								completeFn(codeTable);
						}, deleteFn, codeTableFn);
					}
					layer.close(loadIndex);
				}, function(error) {
					layer.msg('新建分类失败', {icon:2});
					layer.close(loadIndex);
				});
			} else {
				layer.msg('名称不符合要求');
				return false;
			}
		});
	});
}

/**
 * 加载分类代码对照表并生成分类子菜单
 * @param {int} classType 分类类型：1=计划，2=任务，3=报告
 * @param {boolean} justLoadCodeTable 是否只加载代码对照表
 * @param {boolean} extend 是否展开菜单，默认false(不展开)
 * @param {function} completeFn 分类子菜单生成完毕后的回调函数
 * @param {function} deleteFn 删除分类成功后回调函数
 * @param {function} codeTableFn 加载对照表完毕后的回调函数
 */
function loadPTRClassAndCreateClassMenu(classType, justLoadCodeTable, extend, completeFn, deleteFn, codeTableFn) {
	//注册事件-新建分类按钮
	if (!justLoadCodeTable)
		registerAddPTRClass(classType, '#nav_add_class', completeFn, deleteFn, codeTableFn);
	
	//加载分类代码对照表
	loadCodeTable(getServerUrl()+'classification/list.php?class_type='+classType+'&request_fetch_minimum=1&request_order_by=class_id' , function(codeTable, datas) {
		if (codeTableFn)
			codeTableFn(codeTable, datas);
		
		//生成分类代码对照表
		if (classType==1) {
			dictClassNameOfPlan = {0:dictClassNameOfPlan[0]==undefined?'-':dictClassNameOfPlan[0]};
			$.extend(dictClassNameOfPlan, codeTable);
		} else if (classType==2) {
			dictClassNameOfTask = {0:dictClassNameOfTask[0]==undefined?'-':dictClassNameOfTask[0]};
			$.extend(dictClassNameOfTask, codeTable);
		}
		
		if (!justLoadCodeTable) {
			$headElement = $('.nav-group-head');
			
			//删除旧分类菜单项
			$headElement.parent().find('a').remove();
			
			//生成分类菜单项
			var gettpl = $('#ptr-class-item-script').html();
			var html = laytpl(gettpl).render({datas:datas, classType:classType});
			$headElement.after(html).parent().find('.ptr_class_item').hover(function() {
				$(this).find('.ebtw-nav-icon').removeClass('ebtw-hide');
				$(this).find('.ebtw-badge').addClass('ebtw-hide');
			}, function() {
				$(this).find('.ebtw-nav-icon').addClass('ebtw-hide');
				$(this).find('.ebtw-badge').removeClass('ebtw-hide');
			})
			.off('click', '.ebtw-nav-icon')
			.on('click', '.ebtw-nav-icon', function(e) {
				stopPropagation(e); //阻止事件传递
				
				var classId = $(this).parent().attr('data-ptr-class-id');
				var activeClassId = $(this).parent().parent().find('a.active').attr('data-ptr-class-id');
				if ($(this).hasClass('ptr_class_delete')) {
					layer.confirm('真的要删除分类吗?', function(index) {
						var loadIndex = layer.load(2);
						//执行删除函数
						executeUpdate(getServerUrl()+'classification/delete.php', {class_id:classId}, function(affected) {
							if (affected>0) {
								layer.msg('删除分类成功');
								//重新加载
								loadPTRClassAndCreateClassMenu(classType, justLoadCodeTable, true, function(codeTable) {
									if (deleteFn)
										deleteFn(classId);
									
									if (activeClassId!=undefined && activeClassId!=classId) {
										$headElement.parent().find('a.ptr_class_item[data-ptr-class-id="'+activeClassId+'"]').addClass('active');
										$('#ptr_class_input').val(activeClassId);
									}
									
									if (completeFn)
										completeFn(codeTable);
								}, deleteFn, codeTableFn);
							}
							layer.close(loadIndex);
						}, function(error) {
							layer.msg('删除分类失败', {icon:2});
							layer.close(loadIndex);
						});
						
						layer.close(index);
					});
				} else if ($(this).hasClass('ptr_class_edit')) {
					customPrompt('编辑分类名称', {formType:0, maxlength:20, value:$.trim($(this).parent().find('.item-name').text())}, true, false, function(text, index, layero) {
						text = $.trim(text);
						if (text.length>0) {
							var loadIndex = layer.load(2);
							//执行删除函数
							executeUpdate(getServerUrl()+'classification/saveUpdate.php', {pk_class_id:classId, class_name:text}, function(affected) {
								if (affected>0) {
									layer.msg('编辑分类成功');
									//重新加载
									loadPTRClassAndCreateClassMenu(classType, justLoadCodeTable, true, function(codeTable) {
										if (activeClassId!=undefined) {
											$headElement.parent().find('a.ptr_class_item[data-ptr-class-id="'+activeClassId+'"]').addClass('active');
											$('#ptr_class_input').val(activeClassId);
										}
										
										if (completeFn)
											completeFn(codeTable);
									}, deleteFn, codeTableFn);
								}
								layer.close(loadIndex);
							}, function(error) {
								layer.msg('编辑分类失败', {icon:2});
								layer.close(loadIndex);
							});
						} else {
							layer.msg('名称不符合要求');
							return false;
						}
					});
				}
			});
			
			//展开/折叠控制
			function extendMenu(arrow, display) {
				if ($('.nav-group-head .ebtw-nav-toggle').attr('data-status')==arrow) {
					$menus = $headElement.parent().find('a');
					if ($menus.length>0) {
						$headElement.parent().find('a').css('display', display);
						if (arrow=='up')
							$headElement.css('border-width', '1px 1px 0px 1px');
					} else {
						$headElement.css('border-width', '1px 1px 1px 1px');
					}
				} else {
					$headElement.trigger('click');
				}
			}
			
			if (extend) {//展开
				extendMenu('up', 'block');
			} else //折叠
				extendMenu('down', 'none');
			
			if (completeFn)
				completeFn(codeTable);
		}
	});
}

/**
 * 注册事件-点击“用户名称”发起聊天会话
 * @param {boolean} stopEvent 是否阻止点击事件继续传递
 */
function registerTalkToPerson(stopEvent){
	$(document).off('click', '.talk-to-person').on('click', '.talk-to-person', function(e) {
		if ($(e.target).hasClass('t-action-item')) {
			if (stopEvent)
				stopPropagation(e);
			return;
		}
		
		var talkToUid = $(this).attr('data-talk-to-uid');
		if (talkToUid!=undefined) {
			$('body').find('.ebim-call-link').remove();
			var $linkElement =$('body').append('<a class="ebim-call-link" style="display:none;" href="ebim-call-account://'+talkToUid+'">发起会话</a>').find('.ebim-call-link');
			$linkElement[0].click(); //模拟点击
			$linkElement.remove();
		}
		if (stopEvent)
			stopPropagation(e);
	});	
}

/**
 * 提取对象内关联人员资料
 * @param {string|number} shareType 业务类型：1=评审人/评阅人，2=参与人，3=共享人，4=关注人，5=负责人
 * @param {object} entity 记录对象
 * @param {boolean} onlyOne 是否只获取一个，默认false；当onlyOne=true时，返回结果为object对象，否则返回结果为array数组
 * @return {object|array} 一个或多个关联人员资料，不存在则返回undefined
 */
function getShares(shareType, entity, onlyOne) {
	if (!isTypeEmpty(entity) && entity.hasOwnProperty('shares') && entity.shares.hasOwnProperty(shareType)) {
		var shares = entity.shares[shareType];
		if (onlyOne===true)
			return shares[0];
		
		return shares;
	}
}

/**
 * 填充分类select控件，并选中某项
 * @param {number} ptrType 业务类型：1=计划，2=任务等
 * @param {string} parentSelector 父元素选择器
 * @param {string} selectedClassId 待选中的分类编号
 */
function fillPtrClassSelect(ptrType, parentSelector, selectedClassId) {
	var $classElement = $(parentSelector).find('.ptr-class>select');
	//删除旧选项
	$classElement.children('option[value!="0"]').remove();
	
	//添加新选项
	var dictClassName = {};
	if (ptrType==1)
		dictClassName = dictClassNameOfPlan;
	else if (ptrType==2)
		dictClassName = dictClassNameOfTask;
	
	for (key in dictClassName) {
		if (key!=0) {
			var content = '<option value="'+key+'">'+dictClassName[key]+'</option>';
			$classElement.append(content);
		}
	}
	//选中某选项
	if (selectedClassId && selectedClassId.length>0) {
		$classElement.find('option[value="'+selectedClassId+'"]').attr('selected', 'selected');
		$classElement.select2({minimumResultsForSearch:Infinity});
		//$classElement.val(selectedClassId);
		//logjs_info($classElement.html());
	}
}

//保存创建计划
function createPlan(parameter, successHandle, errorHandle) {
	executeCreate(getServerUrl()+'plan/saveCreate.php', parameter, successHandle, errorHandle);
}

//保存-删除计划(移入回收站)
function saveDeleteStatePlan(ptrType, planId, successHandle, errorHandle) {
	executeUpdate(getServerUrl()+PTR_RootFolder[ptrType]+'/saveDeleteState.php', {plan_id:planId}, successHandle, errorHandle);
}
//保存-恢复计划
function restorePlan(planId, successHandle, errorHandle) {
	executeUpdate(getServerUrl()+'plan/saveDeleteState.php', {plan_id:planId, restore:1}, successHandle, errorHandle);
}
//真正删除一个(计划、任务、报告等)
function reallyDeletePtr(ptrType, id, successHandle, errorHandle) {
	var parameter = {};
	parameter[PTR_PKFieldName[ptrType]] = id;
	executeUpdate(getServerUrl()+PTR_RootFolder[ptrType]+'/delete.php', parameter, successHandle, errorHandle);
}

//执行查询单个记录操作
function executeQueryOne(url, parameter, successHandle, errorHandle) {
	callAjax(url, parameter, function(datas) {
		var result = didLoadedDataPreprocess('original', datas, true);
		if (typeof result != 'boolean') {
			if (successHandle)
				successHandle((result.results && result.results.length>0)?result.results[0]:undefined);
		} else if (errorHandle) {
			errorHandle('business');
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle('system', textStatus);
		}
	});
}

//执行创建操作
function executeCreate(url, parameter, successHandle, errorHandle) {
	callAjax(url, parameter, function(datas) {
		var result = didLoadedDataPreprocess('original', datas, true);
		if (typeof result != 'boolean') {
			if (successHandle)
				successHandle(result.id);
		} else if (errorHandle) {
			errorHandle('business');
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle('system', textStatus);
		}
	});
}

//执行更新操作
function executeUpdate(url, parameter, successHandle, errorHandle) {
	callAjax(url, parameter, function(datas) {
		var result = didLoadedDataPreprocess('original', datas, true);
		if (typeof result != 'boolean') {
			if (successHandle)
				successHandle(result.affected);
		} else if (errorHandle) {
			errorHandle('business');
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle('system', textStatus);
		}
	});
}

//执行删除操作
function executeDelete(url, parameter, successHandle, errorHandle) {
	callAjax(url, parameter, function(datas) {
		var result = didLoadedDataPreprocess('original', datas, true);
		if (typeof result != 'boolean') {
			if (successHandle)
				successHandle(result.affected);
		} else if (errorHandle) {
			errorHandle('business');
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle('system', textStatus);
		}
	});
}

/**
 * 创建操作日志
 * @param {number} opType 操作日志类型
 * @param {string} remark 备注/内容
 * @param {string} opData 操作数据
 * @param {string} opName 操作数据名称
 * @param {string} opTime 配合操作数据的时间字段
 * @param successHandle 执行成功后回调函数
 * @param errorHandle 执行失败后回调函数
 */
function createOperateRecord(fromId, fromType, opType, remark, opData, opName, opTime, successHandle, errorHandle) {
	var parameter = {op_type:opType};
	if (!isTypeEmpty(fromId))
		parameter.from_id = fromId;
	if (!isTypeEmpty(fromType))
		parameter.from_type = fromType;
	if (!isTypeEmpty(remark))
		parameter.remark = remark;
	if (!isTypeEmpty(opData))
		parameter.op_data = opData;
	if (!isTypeEmpty(opName))
		parameter.op_name = opName;
	if (!isTypeEmpty(opTime))
		parameter.op_time = opTime;
		
	executeCreate(getServerUrl()+'operaterecord/saveCreate.php', parameter, successHandle, errorHandle);
}

/**
 * 更新操作日志
 * @param {string} opId (查询条件) 操作日志主键
 * @param {string} fromId (查询条件) 业务(计划、任务、报告等)编号
 * @param {number} fromType (查询条件) 业务类型：1=计划，2=任务，3=报告
 * @param {number} opType (查询条件) 操作日志类型
 * @param {string} originOpData (查询条件) 旧(原始)操作数据
 * @param {string} remark (更新值) 备注/内容
 * @param {string} opData (更新值) 操作数据
 * @param {string} opName (更新值) 操作数据名称
 * @param successHandle 执行成功后回调函数
 * @param errorHandle 执行失败后回调函数
 */
function updateOperateRecord(opId, fromId, fromType, opType, originOpData, remark, opData, opName, successHandle, errorHandle) {
	var parameter = {op_type:opType};
	if (!isTypeEmpty(opId))
		parameter.pk_op_id = opId;
	if (!isTypeEmpty(fromId))
		parameter.from_id = fromId;
	if (!isTypeEmpty(fromType))
		parameter.from_type = fromType;
	if (!isTypeEmpty(originOpData))
		parameter.origin_op_data = originOpData;
	if (!isTypeEmpty(remark))
		parameter.remark = remark;
	if (!isTypeEmpty(opData))
		parameter.op_data = opData;
	if (!isTypeEmpty(opName))
		parameter.op_name = opName;
	
	executeUpdate(getServerUrl()+'operaterecord/saveUpdate.php', parameter, successHandle, errorHandle);
}

/**
 * 删除操作日志
 * @param {string} opId
 * @param successHandle 执行成功后回调函数
 * @param errorHandle 执行失败后回调函数
 */
function deleteOperateRecord(opId, successHandle, errorHandle) {
	executeUpdate(getServerUrl()+'operaterecord/saveDeleteState.php', {op_id:opId}, successHandle, errorHandle);
}

/**
 * 创建关联用户
 * @param {string} fromId (查询条件) 业务(计划、任务、报告等)编号
 * @param {number} fromType (查询条件) 业务类型：1=计划，2=任务，3=报告
 * @param {number} shareType 关联类型：1=评审/评阅人 2=参与人 3=共享人 4=关注人 5=负责人
 * @param {string} shareUid 关联用户的用户编号
 * @param {string} shareName 关联用户的名称
 * @param successHandle 执行成功后回调函数
 * @param errorHandle 执行失败后回调函数
 */
function createShareUser(fromId, fromType, shareType, shareUid, shareName, successHandle, errorHandle) {
	var parameter = {from_id:fromId, from_type:fromType, share_type: shareType, share_uid: shareUid, share_name:shareName};
	executeCreate(getServerUrl()+'shareuser/saveCreate.php', parameter, successHandle, errorHandle);
}

/**
 * 关联用户设置为失效(相等于删除关联用户)
 * @param {string} fromId (查询条件) 业务(计划、任务、报告等)编号
 * @param {number} fromType (查询条件) 业务类型：1=计划，2=任务，3=报告
 * @param {number} shareType 关联类型：1=评审/评阅人 2=参与人 3=共享人 4=关注人 5=负责人 6=审批人
 * @param {string} shareId 关联用户记录主键
 * @param successHandle 执行成功后回调函数
 * @param errorHandle 执行失败后回调函数
 */
function invalidShareUser(fromId, fromType, shareType, shareId, successHandle, errorHandle) {
	var parameter = {update_type:2, valid_flag:0, share_id:shareId, from_id:fromId, from_type:fromType, share_type: shareType};
	executeUpdate(getServerUrl()+'shareuser/saveUpdate.php', parameter, successHandle, errorHandle);
}

//操作分派
function ptrActionClick(ptrType, e, fn) {
  stopPropagation(e);
  
//	var srcEle = e.target || e.srcElement;
  var type = parseInt(e.data.type);
  var id = e.data.id;
  switch (type) {
      case 1: //上报(申请评审/评阅)
          break;
      case 2: //编辑
    	  ptrEditorAction(ptrType, id, fn);
          break;
      case 3: //删除
    	  ptrDeleteAction(ptrType, id, fn, fn, e.data.is_deleted);
          break;
      case 4: //通过
          break;
      case 5 ://不通过
          break;
      case 6://取消上报
          ptrCancelReportAction(ptrType, id, fn);
          break;
      case 7://重新上报
          break;
      case 8://恢复
    	  ptrRestoreAction(ptrType, id, fn, true);
    	  break;
      case 9://标为完成
    	  ptrCompleteAction(ptrType, id, fn, true);
    	  break;
      case 10://中止
    	  break;
      default: break;
  }
}

/**
 * 中止任务
 * @param ptrType 1=计划，2=任务，3=报告
 * @param id (计划、任务、报告)编号
 * @param fn 回调函数
 * @param remark 说明
 */
function ptrStopAction(ptrType, id, fn, remark) {
	if (remark==undefined || remark.length==0) {
		layer.msg('缺少说明', {icon:5});
		if (fn) fn(false);
		return;
	}
	
	//询问确认后提交
	askForConfirmSubmit('真的要中止任务吗？', '中止任务', taskAction,[10, id, remark, function(result) {
		layer.msg('中止任务成功');
		//loadDtGrid(createQueryParameter(), false);
		if (fn) fn(result);
	}, function(err) {
		layer.msg('中止任务失败', {icon:2});
		if (fn) fn(false);
	}]);
}

/**
 * 标为完成
 * @param ptrType 1=计划，2=任务，3=报告
 * @param id (计划、任务、报告)编号
 * @param fn 回调函数
 * @param {boolean} refreshBadges {可选} 成功执行后是否执行刷新角标数量，默认false
 */
function ptrCompleteAction(ptrType, id, fn, refreshBadges) {
	layer.confirm('真的要标为完成吗?', function(index) {
		var loadIndex = layer.load(2);
		
		if (ptrType==1) {
			approvalAction(5, ptrType, id, null, true, function(affected) {
				layer.close(loadIndex);
				layer.msg('标为完成成功');
				//loadDtGrid(createQueryParameter(), false);
				if (fn) fn(affected);
				//待定
				if (refreshBadges)
					refreshPTRMenuBadges([1], ptrType); //刷新菜单角标(badge)
			}, function(err){
				layer.close(loadIndex);
				layer.msg('标为完成失败', {icon:2});
				if (fn) fn(false);
			});
		} else if (ptrType==2) {
			taskAction(9, id, null, function(affected) {
				layer.close(loadIndex);
				layer.msg('标为完成成功');
				//loadDtGrid(createQueryParameter(), false);
				if (fn) fn(affected);
				//待定
				if (refreshBadges)
					refreshPTRMenuBadges([1,2,3], ptrType); //刷新菜单角标(badge)
			}, function(err) {
				layer.close(loadIndex);
				layer.msg('标为完成失败', {icon:2});
				if (fn) fn(false);
			});
		}
		
		layer.close(index);
	}, function() {
		if (fn) fn(-1);
	});	
}

//恢复已删除(计划等)
function ptrRestoreAction(ptrType, id, fn, refreshBadges) {
	if (ptrType==1) {
		restorePlan(id, function(affected) {
			layer.msg('恢复成功');
			//loadDtGrid(createQueryParameter(), false);
			if (fn) fn(affected);
			//待定
			if (refreshBadges)
				refreshPTRMenuBadges([1,2,3,4,5,10], ptrType); //刷新菜单角标(badge)
		}, function() {
			if (fn) fn(false);
		});
	}
}

//取消上报
function ptrCancelReportAction(ptrType, id, fn) {
	layer.confirm('真的要取消吗?', function(index) {
		var loadIndex = layer.load(2);
		approvalAction(4, ptrType, id, null, true, function(affected) {
			layer.close(loadIndex);
			layer.msg('取消上报成功');
			//loadDtGrid(createQueryParameter(), false);
			if (fn) fn(affected);
			//待定
			refreshPTRMenuBadges([1,2,3,4,5,10], ptrType); //刷新菜单角标(badge)
		}, function(){
			layer.close(loadIndex);
			if (fn) fn(false);
		});
		
		layer.close(index);
	}, function() {
		if (fn) fn();
	});
}

//删除(计划、任务、报告)
function ptrDeleteAction(ptrType, id, fn, cancelFun, isDeleted) {
	var confirmTitle = '删除确认';
	var confirmContent = '真的要删除吗?';
	if (ptrType==1) { //计划
		if (isDeleted==1) {
			var confirmTitle = '删除回收站记录';
			var confirmContent = '删除回收站记录后不可恢复，确定要删除吗？';
		} else {
			confirmTitle = '放入回收站';
			confirmContent = '删除记录并放入回收站，确定要删除吗？';			
		}
	}
	
	layer.confirm(confirmContent, {title:confirmTitle}, function(index) {
		var loadIndex = layer.load(2);
		var ptrFunc;
		if (ptrType==1) { //计划
			ptrFunc = 'saveDeleteStatePlan';
			if (isDeleted==1)
				ptrFunc ='reallyDeletePtr';
		} else if (ptrType==2) { //任务
			ptrFunc ='reallyDeletePtr';
		} else if (ptrType==3) { //日报/报告
			ptrFunc ='reallyDeletePtr';
		}
		
		//执行删除函数
		eval(ptrFunc)(ptrType, id, function(affected) {
			if (fn) fn(affected);
			layer.msg('删除成功');
			
			if (ptrType==1) {
				refreshPTRMenuBadges([1,2,3,4,5,10], ptrType); //刷新菜单角标(badge)
			} else if (ptrType==2) {
				refreshPTRMenuBadges([1,2,3,4,5,6,20], ptrType); //刷新菜单角标(badge)
			} else if (ptrType==3) {
				refreshPTRMenuBadges([1], ptrType, 'daily'); //刷新菜单角标(badge)
			}
			
			layer.close(loadIndex);
		}, function(){
			if (fn) fn(false);
			layer.close(loadIndex);
		});
		
		layer.close(index);
	}, function() {
		if (cancelFun) cancelFun();
	});
}

/**
 * 弹出右侧页面-编辑(计划、任务、报告)
 * @param ptrType 类型：1=计划，2=任务，3=报告
 * @param id
 * @param fn
 * @param additionalDatas 附加信息
 */
function ptrEditorAction(ptrType, id, fn, additionalDatas) {
	var parameter = {};
	if (id!=undefined) {
		if (ptrType==1)
			parameter.plan_id = id;
		else if (ptrType==2)
			parameter.task_id = id;
		else if (ptrType==3)
			parameter.report_id = id;
	}
	if (additionalDatas!=undefined) {
		$.extend(parameter, additionalDatas);
	}
	
	if (ptrType==3) { //报告
		openReportById('daily', 'e', id, fn, additionalDatas);
	} else { //其它
		$('#sidepage').css('top', $(document).scrollTop());
		$('#sidepage').addClass('div-block');
		$("#sidepage").html("<div class='loading div-centered'></div>");
		$("#sidepage").show().animate({right: 0}, "fast", function() {
			callAjax(getServerUrl() + PTR_RootFolder[ptrType] +"/sidepage_addOrEdit.php", parameter, function(data) {
	      	  $('#sidepage').removeClass('div-block');
	          $("#sidepage").html(data);
	          if (fn) fn();
	      }, function (xhr, err, msg) {
	    	  if (fn) fn(false);
	      });
		});		
	}
}

//新建(计划、任务、报告)
function ptrAddAction(ptrType, id, fn, additionalDatas) {
	ptrEditorAction(ptrType, id, fn, additionalDatas);
}

//弹出窗口-查看(计划、任务、报告)
function ptrDetailsAction(ptrType, ptrId, fn, additionalParams) {
	var parameter = $.extend({}, additionalParams)
	if (ptrId!=undefined) {
		if (ptrType==1)
			parameter.plan_id = ptrId;
		else if (ptrType==2)
			parameter.task_id = ptrId;
	}
	
	$('#sidepage').css('top', $(document).scrollTop());
	$('#sidepage').addClass('div-block');
	$("#sidepage").html("<div class='loading div-centered'></div>");  
    $("#sidepage").show().animate({right: 0}, "fast", function() {
        //deleteUEditor();//清除编辑器
    	callAjax(getServerUrl() + PTR_RootFolder[ptrType] + "/sidepage_details.php", parameter, function (data) {
			$('#sidepage').removeClass('div-block');
			$("#sidepage").html(data);
			if (fn) fn();
        }, function (xhr, err, msg) {
        	if (fn) fn(false);
        });
    });
}

/**
 * 创建右侧页工具栏
 * @param {number} ptrType 业务类型：1=计划，2=任务，3=报告
 * @param {number} status 状态
 * @param {array} allowedActions 权限控制参数
 * @param {boolean} isDeleted 本记录是否删除状态
 * @param {object} additionalDataMap 附加数据
 * @param {boolean} editMode 是否编辑模式，默认false
 */
function createSideToolbarButtons(ptrType, status, allowedActions, isDeleted, additionalDataMap, editMode) {
	var $sideToolbarStatus = $('.side-toolbar>div.side-toolbar-icon');//$('.side-toolbar>div.side-toolbar-status');
	var $toolbarScript = $('#side-toolbar-script');
	
	//编辑模式去除“编辑”按钮和“提交评阅”按钮(对于日报)
	if (editMode===true) {
		var finalAllowedActions = new Array();
		for (var i=0; i<allowedActions.length; i++) {
			if (allowedActions[i]!=2 && (allowedActions[i]!=1 && ptrType==3))
				finalAllowedActions.push(allowedActions[i]);
		}
		allowedActions = finalAllowedActions;
	}
	
	var status = parseInt(status);
    var buttonDatas;
    var buttonDataTypes;
	if (ptrType==1) { //计划
	    switch (status) {
		    case 0://新建未阅
		    case 1://未处理
		    	buttonDataTypes = isDeleted?[3,8]:[21,2,3,9];
		        break;
		    case 2://评审中
		    case 3:
		    	buttonDataTypes = isDeleted?[3,8]:[21];
		        break;
		    case 4://评审通过
		    	buttonDataTypes = isDeleted?[3,8]:[21,9];
		    	break;		        
		    case 5://评审拒绝
		    	buttonDataTypes = isDeleted?[3,8]:[21,2,3,9];
		    	break;
		    default://其它
		    	if (isDeleted)
		    		buttonDataTypes = [];
		        break;
	    }
	} else if (ptrType==2) { //任务
		var buttonDataTypes = [13];
		//编辑模式不显示"关注"按钮
		if (editMode===true)
			buttonDataTypes = [];
		
        switch (status) {
	        case 0://未查阅
	        case 1://未开始
	        	buttonDataTypes = buttonDataTypes.concat([2,3,9]);
	            break;
	        case 2://进行中
	        	buttonDataTypes = buttonDataTypes.concat([2,9,10]);
	            break;
	        case 3://已完成
	        	break;
	        case 4://已中止
	        	break;
	        default://其它
	            break;
        }
	} else if (ptrType==3) { //日报或报告
		switch (status) {
			case 0:
				buttonDataTypes = [];
				if (status==0)
					buttonDataTypes.push(1);
				
				buttonDataTypes = buttonDataTypes.concat([2,3]);
				break;
			case 1:
			case 2:
				buttonDataTypes = [2];
				break;
			default://其它
	            break;
		}
	}
	
    if (buttonDataTypes)  {
    	//创建按钮数据
    	buttonDatas = createButtonDatas(0, allowedActions, buttonDataTypes, additionalDataMap);
        
        if (ptrType==1 && !isDeleted) { //计划-删除按钮改名
			for (var i=0; i< buttonDatas.btns.length; i++) {
				var buttonData = buttonDatas.btns[i];
				if (buttonData.dataType==3) { //按钮从name“删除”改名为name2“移入回收站”
					buttonData.name = buttonData.name2;
				}
			}
        } else if (ptrType==2) { //任务
        	//关注/取消关注按钮区分
        	for (var i=0; i< buttonDatas.btns.length; i++) {
				var buttonData = buttonDatas.btns[i];
				if (buttonData.dataType==13 && buttonData.data && buttonData.data.already_favorite==1) { //如果已经关注，按钮从name“关注”改名为name2“取消关注”，图标从iconClass改为iconClass2
					buttonData.name = buttonData.name2;
					buttonData.iconClass = buttonData.iconClass2;
				}
			}
        } else if (ptrType==3) { //报告
        	for (var i=0; i< buttonDatas.btns.length; i++) {
				var buttonData = buttonDatas.btns[i];
				if (buttonData.dataType==1) { //按钮从name“申请评审”改名为name2“提交评阅”
					buttonData.name = buttonData.name2;
					buttonData.dataType = 2; //功能修改为"编辑"按钮
				}
			}
        }
        //创建工具栏
        $sideToolbarStatus.after(laytpl($toolbarScript.html()).render(buttonDatas));
    }
}

/**
 * //注册事件-右侧页点击工具栏按钮
 * @param {string} logonUserId 当前登录用户的编号
 * @param {number} ptrType 文档类型：1=计划，2=任务，3=报告
 * @param {string} ptrId (计划、任务、报告)编号
 * @param {function} fn (可选) 回调函数
 */
function registerSideToolbarActions(logonUserId, ptrType, ptrId, fn) {
    $('.side-toolbar-item>button').click(function(e) {
		$(this).blur();
		var actionType = parseInt($(this).attr('data-action-type'));
		switch(actionType) {
			case 2: //编辑
				var reserved_from_view_page = $(this).attr('data-from-view-page');
				ptrEditorAction(ptrType, ptrId, fn, (typeof reserved_from_view_page!=undefined)?{reserved_from_view_page:reserved_from_view_page}:undefined);
				break;
			case 3: //删除
				var reallyDelete = 1;
				if (ptrType==1) {
					//放入回收站，而非真正删除
					if ($(this).attr('data-is-deleted')==0)
						reallyDelete = 0;
				}
				
				ptrDeleteAction(ptrType, ptrId, function(result) {
					//回调函数
					if (fn) fn(result, actionType);
					
	        		if (result!==false) {
	        			closeSidepage(); //关闭右侧页
	        			
						if (ptrType==1||ptrType==2) {
							refreshMainViewActually(); //依据具体情况下刷新主视图
						} else if (ptrType==3){
							//do nothing
						}
	        		}
				}, null, reallyDelete);
				break;
			case 8: //恢复
				ptrRestoreAction(ptrType, ptrId, function(result) {
					//回调函数
					if (fn) fn(result, actionType);
					
	        		if (result!==false) {
						closeSidepage(); //关闭右侧页
						refreshMainViewActually(); //依据具体情况下刷新主视图
	        		}
				}, true);
				break;
			case 9: //标为完成
				if (ptrType==1 || ptrType==2) {
					ptrCompleteAction(ptrType, ptrId, function(result) {
						//回调函数
						if (fn) fn(result, actionType);
						
		        		if (result!==false) {
							closeSidepage(); //关闭右侧页
							refreshMainViewActually(); //依据具体情况下刷新主视图
		        		}
					}, true);
				}
				break;
			case 10: //中止
				if (ptrType==2) { //目前仅支持任务
					layer.prompt({
						title: '中止原因',
						maxlength: 100,
						formType: 0
					}, function(value, index, elem){
						ptrStopAction(ptrType, ptrId, function(result) {
							if (fn) fn(result, actionType); //回调函数
							
							if (result!==false) {
								closeSidepage(); //关闭右侧页
								refreshMainViewActually(); //依据具体情况下刷新主视图
							}
						}, value);
						
						layer.close(index);
					});
				}
				break
			case 13: //关注任务
				if (ptrType==2) { //目前仅支持任务
					var $element = $(this);
					var alreadyFavorite = $element.attr('data-already-favorite');
					var cancel = (alreadyFavorite==1)?true:false;
					var loadIndex = layer.load(2);
					saveFavorite(logonUserId, cancel, ptrType, ptrId, function(result) {
						if (cancel)
							$element.attr('data-already-favorite',0).children('i').removeClass('glyphicon-heart').addClass('glyphicon-heart-empty').next('span').html(' 关注');
						else
							$element.attr('data-already-favorite',1).children('i').removeClass('glyphicon-heart-empty').addClass('glyphicon-heart').next('span').html(' 取消关注');
						
						if (fn) fn(result, cancel, ptrType, ptrId); //回调函数
						
						layer.close(loadIndex);
					}, function() {
						layer.close(loadIndex);
					});
				}
				break;
			case 21: //计划转任务
				if (ptrType==1) {
					ptrTypeOfTask = 2;
					ptrEditorAction(ptrTypeOfTask, null, function(){
						
					}, {plan_id: ptrId, translate_to_task:1});
				}
				break;
		}
    });
}

//注册点击计划、任务等链接的处理函数
function registerAssociateRedirect() {
	$(document).on('click', '.associate_redirect', function(e) {
		//显示详情页面
		ptrDetailsAction($(this).attr('data-ptr-type'), $(this).attr('data-ptr-id'), function(){});
	});
}

/**
 * 重新组织右侧标签页数据
 * @param {string|Array} 原始数据
 * @return {Object} 重新组织后的数据，例如：{"2016-03-02":[...], "2016-03-01":[...]}
 */
function reorganizeSidePageTabData(datas) {
	if (typeof datas == 'string') {
		if (datas.length==0)
		datas = '""';		
		datas = json_parse(datas);
		//datas = $.parseJSON(datas);
	}
	
	if (Object.prototype.toString.call(datas) === '[object Array]') { //判断数组
		var results = new Object();
		for (var i=0; i<datas.length; i++) {
			var createTime = datas[i].create_time.substr(0, 10);
			if (results[createTime]) {
				results[createTime].push(datas[i]);
			} else {
				results[createTime] = new Array();
				results[createTime].push(datas[i]);
			}
		}
		return results;
	}
}

//通过业务类型代码获取名称
function getTypeName(from_type, period) {
	var typeName;
	
	switch(parseInt(from_type)) {
	case 1:
		typeName = '计划';
		break;
	case 2:
		typeName = '任务';
		break;
	case 3:
		typeName = '报告';
		if (period==1)
			typeName = '日报';
		break;
	}
	return typeName;
}

//获取默认头像URL
function getDefaultHeadPhoto(rootUrl) {
	return rootUrl + '/images/user.png';
}

//加载并刷新用户头像
function loadAndFreshHeadPhoto(userIds, $container) {
	//查询获取头像后更新视图
	$.ebtw.getHeadPhoto(userIds, function(userInfos){
		for (var i=0; i<userInfos.length; i++) {
			var userInfo = userInfos[i];
			if (userInfo.result!=0) { //没有真实头像
				continue;
			}
			$container.find('.sidepage-tab-page-headphoto[data-user-id="'+userInfo.user_id+'"] img').attr('src', userInfo.head_image_url);
		}
	}, function(err){});
}

//渲染界面：操作日志类型
function renderOprDatas(logonUserId, ptrCreateUid, ptrType, $container, datas, rootUrl) {
	var userIds = new Array(); //暂存用户编号
	//logjs_info(datas);
		
	for (var j=0;j<datas.length;j++) {
		var entity = datas[j];
		
		var headPhoto = getDefaultHeadPhoto(rootUrl);
		userIds.push(entity.user_id);
		var userName = entity.user_name||'未知用户';
		var talkToPerson = true;
		if (entity.user_id==logonUserId) {
			userName = '我';
			talkToPerson = false;
		}
		
		var renderData = {headPhoto:headPhoto, userId:entity.user_id, userName:userName, userAccount:entity.user_account, talkToPerson:talkToPerson, fCreateTime:popularDateTime(entity.create_time)/*entity.create_time.substr(11, 5)*/, name:'', detail:''};
		
		if (entity.hasOwnProperty('op_id'))
			renderData.opId = entity['op_id'];
		if (entity.hasOwnProperty('op_type'))
			renderData.opType = entity['op_type'];
			
		var dict = OperateRecordDict[entity.op_type];
		var attachmentResourceId;
		
		if (dict) {
			if (dict.fileResourceId && entity[dict.fileResourceId]/* && entity[dict.fileResourceId]!='0'*/) {
				if (entity.hasOwnProperty('op_data'))
					renderData.opData = entity['op_data'];
				if (entity.hasOwnProperty('op_name'))
					renderData.opName= entity['op_name'];
				
				attachmentResourceId = entity[dict.fileResourceId];
				if (attachmentResourceId && attachmentResourceId!='0') { //有效资源
					renderData.operate = dict.operate;
					renderData.name = laytpl($('#file-link-script').html()).render({resourceId:attachmentResourceId, name:entity[dict.fileName]});
				} else { //无效资源
					renderData.name = entity[dict.fileName];
				}
			} else {
				renderData.name = (dict.title && entity[dict.title] && dict.conversion && eval(dict.conversion)(dict.convParamUsingCallback?(function(fromType) {
					return $.trim(laytpl($('#sidepage-tab-script-ptr-source').html()).render({ptrType:fromType, ptrId:entity[dict.id], fromName:entity[dict.title]}));
				}):(entity[dict.title]), dict.title_1?entity[dict.title_1]:undefined, dict.title_2?entity[dict.title_2]:undefined, logonUserId) || entity[dict.title] || '') 
				+ (dict.extendTitle || '') 
				+ ((dict.opTime && entity['op_time'])?('&nbsp;&nbsp;&nbsp;'+dict.opTimeTitle+'：'+$.D_ALG.formatDate(new Date(entity['op_time']), dict.opTimeFormat)):'');						
			}
			
			renderData.operate = renderData.operate || dict.name || dict['name'+ptrType];
			renderData.detail = dict.detail && entity[dict.detail] || '';
			
			ptrCreateUid = entity.ptr_create_uid || ptrCreateUid; //文档的创建者
			var oprCreateUid = entity.user_id; //操作日志的创建者
			var principalUser; //任务的负责人(仅对于任务有效)
			if (entity.from_type==2)
				principalUser = getShares(5, entity, true); //获取负责人资料
			
			//来源于最新动态页面
			//来源于日报-自动汇报功能
			if (ptrType==0 || entity.query_mark_type==1) {
				var html = $.trim(laytpl($('#sidepage-tab-script-ptr-source').html()).render({ptrType:entity.from_type, ptrId:entity.from_id, fromName:(entity.from_name?entity.from_name:entity.ptr_name), period:entity.period}));
				if (dict.pMainShowPtrSource) {
					renderData.pMainShowPtrSource = html; //在p-main的div尾部追加显示来源
				} else {
					renderData.ptrSource = '查看'+ getTypeName(entity.from_type) + '：' + html;
				}
				
				//renderData.typeName = getTypeName(entity.from_type);
				renderData.ptrType = entity.from_type; 
				renderData.ptrId = entity.from_id;
				renderData.innerDiscuss = 1;
				if (entity.countedOprs!=undefined)
					renderData.countOfDiscuss = entity.countedOprs.discuss;
			}
			
			//=====权限控制==========
			//当前登录用户=操作日志的创建者
			if (logonUserId==oprCreateUid) {
				renderData.canEdit = 1; //编辑权限
				renderData.canDelete = 1; //删除权限
			} else {
				//当前登录用户=文档的创建者
				//负责人与创建者拥有相同权限(仅对于任务)
				if ((ptrCreateUid && logonUserId==ptrCreateUid) || (principalUser && logonUserId==principalUser.share_uid))
					renderData.canDelete = 1; //删除权限
			}
			//权限合并
			renderData.canEdit = dict.canEdit && renderData.canEdit; //编辑权限
			renderData.canDelete = dict.canDelete && renderData.canDelete; //删除权限
			
			//特殊处理“新建计划”、“新建任务”、“新建日报”操作的描述
			if (typeof renderData.operate=='string' && renderData.operate.length>0) {
				if (typeof dict.operateConversion=='function') {
					renderData.operate = dict.operateConversion(userName, entity.create_time, renderData.pMainShowPtrSource);
					
					if (renderData.name) delete renderData.name;
					if (renderData.pMainShowPtrSource) delete renderData.pMainShowPtrSource;
				} else {
					if (renderData.name || renderData.pMainShowPtrSource)
						renderData.operate += '：';
				}
			}
			
			//最后修改时间的描述
			if (entity.last_modify_time && dict.showModifyTime) {
				renderData.tail = '最后修改：'+entity.last_modify_time.substr(0,16)+' by  '+entity.user_name;
			}			
		} else {
			renderData.operate = '未知操作类型：'+entity.op_type;
		}
		
		var html = laytpl($('#sidepage-tab-script-1').html()).render(renderData);
		$container.append(html);
		
		if (attachmentResourceId && attachmentResourceId!='0') {
			//绑定点击下载附件事件
			registerAttamentItemClick($container.find('.attachment-link[data-resource-id="'+attachmentResourceId+'"]'));
		}
	}
	
	return userIds;
}

//加载标签页数据
function loadSidepageTabData(logonUserId, ptrCreateUid, allowedActionsDict, isDeleted, $container, ptrType, type, datas, rootUrl, additionalParams) {
	var $head = $container.find('.sidepage-tab-page-header');
	//$head.html('');//清除视图旧内容
	
	if (typeof datas=='string') {
		//json_parse比较好用，能自动处理控制字符
		datas = json_parse(datas);
		//datas = JSON.parse(datas);
		//datas = $.parseJSON(datas);
	}
	
	//处理空记录或异常记录情况
	if ((typeof datas == 'undefined' || Object.prototype.toString.call(datas) !== '[object Array]') || datas.length==0) {
		if (type!=SideTabTypes['su']) { // "非关联用户"标签页，不显示"没有记录"提示，进入下一步渲染界面
			$head.after(laytpl($('#sidepage-tab-script-default').html()).render({}));
			return;
		}
	}
	
	var userIds = new Array(); //暂存用户编号
	var headPhoto = getDefaultHeadPhoto(rootUrl);
	
	if (type==SideTabTypes['opr']) { //操作日志
		var results = reorganizeSidePageTabData(datas); //重新组织加载的数据
		var i=0;
		
		for (dateStr in results) {
			var html_0 = laytpl($('#sidepage-tab-script-0').html()).render({fDate:popularDate2(dateStr), dateStr:dateStr});
			if (i==0) $head.after(html_0);
			else $container.find('.sidepage-tab-page-module:last').after(html_0);
			
			var $part = $container.find('.sidepage-tab-page-module[data-date="'+dateStr+'"] ul');
			var arry = results[dateStr];
			
			userIds = userIds.concat(renderOprDatas(logonUserId, ptrCreateUid, ptrType, $part, arry, rootUrl));
			i++;
		}
	} else if (type==SideTabTypes['ass']) { //关联计划、关联任务
		var targetType = (ptrType==1)?2:1;
		var i=0;
		var results = reorganizeSidePageTabData(datas); //重新组织加载的数据
		
		for (dateStr in results) {
			var html_0 = laytpl($('#sidepage-tab-script-0').html()).render({fDate:popularDate(dateStr), dateStr:dateStr});
			if (i==0) $head.after(html_0);
			else $container.find('.sidepage-tab-page-module:last').after(html_0);
			
			var $part = $container.find('.sidepage-tab-page-module[data-date="'+dateStr+'"] ul');
			var arry = results[dateStr];
			
			for (var j=0;j<arry.length;j++) {
				var entity = arry[j];
				userIds.push(entity.create_uid);
				var userName = entity.create_name||'未知用户';
				var talkToPerson = true;
				if (entity.create_uid==logonUserId) {
					userName = '我';
					talkToPerson = false;
				}
				var renderData = {headPhoto:headPhoto, userId:entity.create_uid, userName:userName, userAccount:entity.user_account, talkToPerson:talkToPerson, fCreateTime:popularDateTime(entity.create_time), 
						nameField:((targetType==1)?'plan_name':'task_name'), ptrIdField:((targetType==1)?'plan_id':'task_id'), ptrType:targetType, 
						detail:''};
				
				renderData.targetName = (targetType==1)?'计划':'任务';
				renderData.name = (entity[renderData.nameField] || '');
				renderData.tips = '跳转到'+renderData.name;
				renderData.ptrId = entity[renderData.ptrIdField];
				
				var html = laytpl($('#sidepage-tab-script-2').html()).render(renderData);
				$part.append(html);
			}
			i++;
		}
	} else if (type==SideTabTypes['su']) { //关联用户
		var shareTypeNames = {}; //{2:'参与用户', 3:'共享用户', 4:'关注用户'};
		//重新组织数据
		//var shares = new Object();
		var shares = {}; //, 4:[]}; 关注用户不允许添加按钮
		if (ptrType==1) {
			shareTypeNames = {3:'共享用户'};
			shares = {3:[]};
		} else if (ptrType==2) {
			shareTypeNames = {2:'参与用户', 3:'共享用户', 4:'关注用户'};
			shares = {2:[], 3: [], 4: []};
			//根据附加参数过滤不显示的关联用户类型
			if (additionalParams!=undefined && Object.prototype.toString.call(additionalParams) === '[object Array]') {
				for (var i=0; i<additionalParams.length; i++) {
					var param = additionalParams[i];
					delete shareTypeNames[param];
					delete shares[param];
				}
			}
		}
		
		for (shareType in shareTypeNames) {
			for (var i=0; i<datas.length; i++) {
				var share = datas[i];
				if (shares[shareType]==undefined) {
					shares[shareType] = new Array();
				}
				if (share.share_type==shareType)
					shares[shareType].push(share);
			}
			//删除空数组属性
			//if (shares[shareType]!=undefined && shares[shareType].length==0)
			//	delete shares[shareType];
		}
		
		//渲染界面
		for (shareType in shares) {
			var subShares = shares[shareType];
			var html_0 = laytpl($('#sidepage-tab-script-10').html()).render({shareType:shareType ,shareTypeName:shareTypeNames[shareType]});
			$postion = $head.parent().children('.sidepage-tab-page-module:last'); 
			if ($postion.length>0)
				$postion.after(html_0);
			else
				$head.after(html_0);
			
			//操作权限控制
			var canAdd = false;
			var canDelete = false;
			if (allowedActionsDict && allowedActionsDict.singlePtr) {
				var allowedActions = allowedActionsDict[allowedActionsDict.ptrId];
				if (isDeleted!=1 && (shareType==2 || shareType==3) && allowedActions && $.inArray(26, allowedActions)>-1) {
					canAdd = true;
					canDelete = true;
				}
				/*暂时去掉"删除关注人"的功能
				if (isDeleted!=1 && allowedActions && shareType==4 && $.inArray(27, allowedActions)>-1) {
					canDelete = true;
				}*/
			}
			
			var $part = $container.find('.sidepage-tab-page-module[data-share-type="'+shareType+'"] ul');
			var renderData = new Array();
			if (Object.prototype.toString.call(subShares) === '[object Array]') {
				for (var j=0;j<subShares.length;j++) {
					var entity = subShares[j];
					userIds.push(entity.share_uid);
					
					var data = {share_id:entity.share_id, headPhoto:headPhoto, user_id:entity.share_uid, user_name:entity.share_name||'未知用户', read_flag:entity.read_flag, canDelete:canDelete};
					if (logonUserId!=entity.share_uid)
						data.talkToPerson = true;
					data.userAccount = entity.user_account;
					
					if (entity.read_time)
						data.readTime = entity.read_time;
					if (entity.share_type==2 || entity.share_type==3)
						data.shareInvoiceTime = entity.create_time;
					else if (entity.share_type==4)
						data.shareFavoriteTime = entity.create_time;
					
					renderData.push({userInforHtml:laytpl($('#sidepage-tab-script-12').html()).render(data)});
				}
			}
			
			var html = laytpl($('#sidepage-tab-script-11').html()).render({shares:renderData, canAdd:(shareType==2||shareType==3)?(canAdd&&true):false/*关注用户不允许添加按钮*/});
			$part.append(html);
		}
	} else if (type==SideTabTypes['att']) { //文件和附件
		var defaultRenderData = {};
		
		/**
		 * 定义函数：重新组织数据
		 * @param {array} datas 待处理的数据数组
		 * @param {object} attaTypeNames (可选) 显示分类，不填(或null)则按创建时间的月份排序；样例：{0:'草稿箱', 1:'已发送邮件', 2:'接收邮件', 3:'黑名单邮件', 4:'垃圾邮件', 5:'存档邮件', 21:'回收站邮件'}
		 * @param {boolean} removeEmptyAttaType (可选) 是否删除没有数据的分类：true=删除，false=不删除，不填(或null)=不删除；本参数只在attaTypeNames填入有效值才生效
		 * @param {string} innerSortType (可选) 每分类内部排序规则：asc=正序，desc=倒序，不填(或null)=自然顺序(不排序)
		 * @param {function} attaTypeMatchCallback (可选) 分类匹配回调函数，如不填则使用默认规则；函数参数((atta, attaType)
		 */
		var reorganizeFilesData = function(datas, attaTypeNames, removeEmptyAttaType, innerSortType, attaTypeMatchCallback) {
			var attas = new Object();
			
			//默认按创建时间的月份分类
			if (!attaTypeNames) {
				for (var i=0; i<datas.length; i++) {
					var atta = datas[i];
					var attaType = atta.create_time.substr(0,7);
					
					if (attas[attaType]==undefined) {
						attas[attaType] = new Array();
					}
					attas[attaType].push(atta);
				}
				
				attaTypeNames = new Object();
				for (attaType in attas) {
					attaTypeNames[attaType] = attaType;
				}
			} else { //指定分类值
				for (attaType in attaTypeNames) {
					for (var i=0; i<datas.length; i++) {
						var atta = datas[i];
						if (attas[attaType]==undefined) {
							attas[attaType] = new Array();
						}
						
						//判断使用回调函数户进行匹配
						if (attaTypeMatchCallback) {
							if (attaTypeMatchCallback(atta, attaType))
								attas[attaType].push(atta);
						} else { //默认规则匹配
							if (atta.flag==attaType)
								attas[attaType].push(atta);
						}
					}
					
					//删除空数组属性
					if (removeEmptyAttaType) {
						if (attas[attaType]!=undefined && attas[attaType].length==0)
							delete attas[attaType];
					}
				}
			}
			
			//分类内部排序
			if (innerSortType) {
				if (innerSortType=='asc') {
					for (attaType in attas) {//按时间正序排序
						attas[attaType].sort(function(a,b) {
							return a.create_time.localeCompare(b.create_time);
				        });
					}
				} else if (innerSortType=='desc') {//按时间倒序排序
					for (attaType in attas) {
						attas[attaType].sort(function(a,b) {
							return b.create_time.localeCompare(a.create_time);
				        });
					}
				}
				//其它情况不排序
			}
			
			return [attaTypeNames, attas];
		};
		
		var reorganizeResults; //重新组织数据后的临时保存结果集
		
		if (additionalParams=='my_cloud_files') { //云盘文件
			reorganizeResults = reorganizeFilesData(datas, null, false, 'desc');
			
			//云盘文件默认允许删除，因为目前业务上加载的都是自己的文件
			defaultRenderData = $.extend({canDelete:1}, defaultRenderData);
		} else if (additionalParams=='email_files') { //邮件附件
			//按邮件标识分类
			reorganizeResults = reorganizeFilesData(datas, {0:'草稿箱', 1:'已发送邮件', 2:'接收邮件', 3:'黑名单邮件', 4:'垃圾邮件', 5:'存档邮件', 21:'回收站邮件'}, true, 'desc', function(atta, attaType) {
				return (atta.email.content_flag==attaType);
			});
			
			//邮件附件默认允许删除，因为目前业务上加载的都是自己邮箱里的邮件
			defaultRenderData = $.extend({canDelete:1}, defaultRenderData);
		} else { //计划、任务、报告等其它类型的文件：plan_files task_files report_files
			//来源于工作台文件页面
			if (ptrType==0)
				reorganizeResults = reorganizeFilesData(datas, null, false, 'desc');
			else
				reorganizeResults = reorganizeFilesData(datas, {0:'文档附件', 3:'评论/回复附件'}, true, 'asc');
		}
		
		var attaTypeNames = reorganizeResults[0];
		var attas = reorganizeResults[1];
		
		//分类名排序，按创建时间倒序排序
		var attaTypeKeys = Object.keys(attas);
//		logjs_info(additionalParams+' '+ptrType);
		if (ptrType==0 && additionalParams!='email_files') { //来源于工作台文件页面，并且非邮件附件
			attaTypeKeys = attaTypeKeys.sort(function(a, b) {
	            return b.localeCompare(a);
			});
//			logjs_info(attaTypeKeys);
		}
		
		
		//渲染界面
		//for (attaType in attas) {
		for (var i=0; i<attaTypeKeys.length; i++) {
			var attaType = attaTypeKeys[i];
			
			var subAttas = attas[attaType];
			var html_0 = laytpl($('#sidepage-tab-script-20').html()).render({attaType:attaType ,attaTypeName:attaTypeNames[attaType]});
			$postion = $head.parent().children('.sidepage-tab-page-module:last'); 
			if ($postion.length>0)
				$postion.after(html_0);
			else
				$head.after(html_0);
			
			var $part = $container.find('.sidepage-tab-page-module[data-atta-type="'+attaType+'"] ul');
			
			//遍历执行渲染
			for (var j=0;j<subAttas.length;j++) {
				var entity = subAttas[j];
				attachmentResourceId = entity['resource_id'];
				//userIds.push(entity.create_user_id);
				var userName = entity.create_user_name||'未知用户';
				var talkToPerson = true;
				if (entity.create_user_id==logonUserId) {
					userName = '我';
					talkToPerson = false;
				}
				var renderData = $.extend({}, defaultRenderData, {headPhoto:headPhoto, userId:entity.create_uid, userName:userName, userAccount:entity.user_account, talkToPerson:talkToPerson, fCreateTime:popularDateTime(entity.create_time)
					, detail:'', resourceId:attachmentResourceId, resourceName:entity['name']});
				
				var linkData = {resourceId:attachmentResourceId, name:entity['name'], resourceSize:entity.size};
				if (typeof entity.online_view_url=='string' && entity.online_view_url.length>0 && $.inArray(entity.view_ext_type, ['1', '2', '3']>-1)) {
					linkData.openResource = true;
					linkData.online_view_url = entity.online_view_url;
					linkData.view_ext_type = entity.view_ext_type;
				}
				
				renderData.name = laytpl($('#file-link-script').html()).render(linkData);
				
				//来源于工作台文件页面
				if (ptrType==0) {
					if (entity.from_type)
						renderData.typeName = getTypeName(entity.from_type, entity.period);
					if (entity.ptr_name)
						renderData.fromName = entity.ptr_name;
					if (entity.from_type)
						renderData.ptrType = entity.from_type;
					if (entity.ptr_id)
						renderData.ptrId = entity.ptr_id;
					if (entity.period)
						renderData.period = entity.period;
					
					if (entity.ptr_id) {
						var html = $.trim(laytpl($('#sidepage-tab-script-ptr-source').html()).render({ptrType:entity.from_type, ptrId:entity.ptr_id, fromName:entity.ptr_name, period:entity.period}));
						renderData.ptrSource = '查看'+ renderData.typeName + '：' + html;
					}
				}
				
				//邮件附件
				//if (ptrType==20 && typeof entity.email=='object')
				if (additionalParams=='email_files') {
					var email = entity.email;
					
					switch(parseInt(email.content_flag)) {
					case 0: //草稿箱 （显示本人头像，名称显示“我”）
					case 1: //已发送邮件
						renderData.userName = undefined; //'我';
						renderData.userId = entity.create_user_id;
						userIds.push(renderData.userId);
						break;
					case 2: //已接收邮件 （显示邮件发送者头像，loadresource 的email对象，会增加返回 from_user_id ，0显示默认游客头像，名称显示格式：“name(yzh@entboost.com)”）
					case 3: //黑名单邮件 （因为from_user_id有可能是本人user_id，如果from_user_id =user_id，显示同0，其他显示同2）
					case 4: //垃圾邮件
					case 5: //存档邮件
					case 21: //回收站邮件(已删除邮件)
						var talkToPerson = true;
						var fromUserId = email.from_user_id;
						renderData.userName = email.from_name+(email.from_account.length>0?' ('+email.from_account+')':''); //email.from_name;
						if (fromUserId==logonUserId) {
							renderData.userName = '我';
							talkToPerson = false;
						}
						
						if (fromUserId && fromUserId!=0) {
							userIds.push(fromUserId);
							renderData.userAccount = email.from_account;
							renderData.talkToPerson = talkToPerson;
						}
						 
						renderData.userId = fromUserId;
						break;
					}
					renderData.ptrSource = '来自邮件：'+$.trim(laytpl($('#sidepage-tab-script-open-email-app').html()).render({fromName:email.subject, recycleBin:(email.content_flag!=21)?false:true}));
				} else {
					renderData.userId = entity.create_user_id;
					userIds.push(renderData.userId);
					
					//=====权限控制==========
					ptrCreateUid = entity.ptr_create_uid || ptrCreateUid; //文档的创建者
					var oprCreateUid = entity.create_user_id; //操作日志的创建者
					var principalUser; //任务的负责人(仅对于任务有效)
					if (entity.from_type==2 || entity.from_type==(10+2))
						principalUser = getShares(5, entity, true); //获取负责人资料
					
					//当前登录用户=操作日志的创建者
					//当前登录用户=文档的创建者
					//负责人与创建者拥有相同权限(仅对于任务)
					if (logonUserId==oprCreateUid || (ptrCreateUid && logonUserId==ptrCreateUid) || (principalUser && logonUserId==principalUser.share_uid)) {
						renderData.canDelete = 1; //删除权限
					}
				}
				
				var html = laytpl($('#sidepage-tab-script-21').html()).render(renderData);
				$part.append(html);
				
				if (attachmentResourceId) {
					//绑定点击下载附件事件
					registerAttamentItemClick($part.find('.attachment-link[data-resource-id="'+attachmentResourceId+'"]'));
				}
			}
		}
	} else if (type==SideTabTypes['auto_rp_opr']) { //自动汇报内容(计划、任务的相关日志)
		var oprSubTypeNames = {1:'计划相关', 2:'任务相关'};
		//重新组织数据
		var oprs = new Object();
		for (oprSubType in oprSubTypeNames) {
			for (var i=0; i<datas.length; i++) {
				var opr = datas[i];
				if (oprs[oprSubType]==undefined) {
					oprs[oprSubType] = new Array();
				}
				if (opr.from_type==oprSubType)
					oprs[oprSubType].push(opr);
			}
			//删除空数组属性
			if (oprs[oprSubType]!=undefined && oprs[oprSubType].length==0)
				delete oprs[oprSubType];
		}
		
		var i=0;
		for (oprSubType in oprs) {
			var html_0 = laytpl($('#sidepage-tab-script-0').html()).render({fDate:oprSubTypeNames[oprSubType], dateStr:oprSubType});
			if (i==0) $head.after(html_0);
			else $container.find('.sidepage-tab-page-module:last').after(html_0);
			
			var $part = $container.find('.sidepage-tab-page-module[data-date="'+oprSubType+'"] ul');
			var arry = oprs[oprSubType];
			
			userIds = userIds.concat(renderOprDatas(logonUserId, ptrCreateUid, ptrType, $part, arry, rootUrl));
			i++;
		}
	}
	
	//查询获取头像后更新视图
	if (userIds.length>0) {
		$.unique(userIds);
		loadAndFreshHeadPhoto(userIds, $container);
	}
}

/**
 * 更新标签页badge
 * @param {object} $container 标签页的父容器(JQuery对象)
 * @param {boolean} execute 是否执行查询；如果不执行查询将把数量设置为0
 * @param {string} url 远程服务访问地址
 * @param {string} prefix tab标签命名前缀
 * @param {number|string} activeNo 标签页编号
 * @param {object} parameters (可选) 访问远程服务提交参数
 */
function updateTabBadge($container, execute, url, prefix, activeNo, parameter) {
	var $element;
	if ($container)
		$element = $container.find('.'+prefix+'-head#'+prefix + activeNo + ' .scount');
	else 
		$element = $('.'+prefix+'-head#'+prefix + activeNo + ' .scount');
	
	if (execute===false) {
		$element.html(0);
	} else {
		loadRecordCount(url, parameter, function(count) {
			$element.html(count);
	    }, function(errType){});
	}
}

//获取文件资源数量
function listResourceFileCountCallback(activeNo, param, successHandle) {
	$.ebtw.listfile(param.from_type, param.ptr_id, param.flag, param.get_summary, null, null, function(result) {
		if (result.code=='0') {
			if (successHandle)
				successHandle(activeNo, result.count);
		}
	});
}
//获取自动汇报内容的记录数量
function autoReportCountCallback(url, activeNo, param, successHandle) {
	loadRecordCount(url, param, function(count) {
		if (successHandle)
			successHandle(activeNo, count);
    }, function(errType){});	
}

//标签页角标数值获取规则
var SidepageTabBadges ={
	"workbench_1":{ptrType:0, //工作台"最新动态"相关
		datas:{
			0 : {url:'workbench_list.php'},
			1 : {url:'workbench_list.php'},
			2 : {url:'workbench_list.php'},
			3 : {url:'workbench_list.php'},
			4 : {url:'workbench_list.php'},
		}
	},
	"workbench_2":{ptrType:0, //工作台"文件"相关
		datas:{
			0: {type:'callback', callback: listResourceFileCountCallback}, //全部文件
			1: {type:'callback', callback: listResourceFileCountCallback}, //云盘文件
			2: {type:'callback', callback: listResourceFileCountCallback}, //计划文件
			3: {type:'callback', callback: listResourceFileCountCallback}, //任务文件
			4: {type:'callback', callback: listResourceFileCountCallback}, //日报文件
			5: {type:'callback', callback: listResourceFileCountCallback}, //报告文件
			6: {type:'callback', callback: listResourceFileCountCallback}, //邮件附件
		}
	},
	"plan_0":{ptrType:1, //计划相关
		datas:{
			11: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:1, op_type_class:11, is_deleted:0}}, //编辑
			1: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:1, op_type_class:3, is_deleted:0}}, //评审
			2: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:1, op_type_class:1, op_type:3, is_deleted:0}}, //评论/回复
			3: {url:'task/list.php', fromIdName:'from_id', parameter: {from_type:1, request_query_type:7}}, //关联任务 from_type=1(计划转任务)
			4: {url:'shareuser/list.php', fromIdName:'from_id', parameter: {from_type:1, share_type:3, valid_flag:1}}, //成员
			5: {type:'callback', callback: listResourceFileCountCallback}, //附件数量
			20: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:1, op_type_class:0, is_deleted:0}}, //操作日志
		}
	},
	"task_0":{ptrType:2, //任务相关 
		datas:{
			11: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:2, op_type_class:11, is_deleted:0}}, //编辑
			1: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:2, op_type_class:0, is_deleted:0, op_type:[31,34]}}, //进度
			2: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:2, op_type_class:0, is_deleted:0, op_type:32}}, //工时
			3: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:2, op_type_class:1, op_type:3, is_deleted:0}}, //评论/回复
			4: {url:'shareuser/list.php', fromIdName:'from_id', parameter: {from_type:2, share_type:[2,3], valid_flag:1}}, //成员
			5: {url:'shareuser/list.php', fromIdName:'from_id', parameter: {from_type:2, share_type:[4], valid_flag:1}}, //关注
			8: {url:'plan/list.php', fromIdName:'task_id', parameter: {request_query_type:7, is_deleted:0}}, //关联计划
			9: {type:'callback', callback: listResourceFileCountCallback}, //附件数量
			20: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:2, op_type_class:0, is_deleted:0}}, //操作日志
		}
	},
	"report_0":{ptrType:3, //日报相关 
		datas:{
			11: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:3, op_type_class:11, is_deleted:0}}, //编辑
			1: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:3, op_type_class:3, is_deleted:0}}, //评阅
			2: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:3, op_type_class:1, op_type:3, is_deleted:0}}, //评论/回复
			3: {type:'callback', callback: autoReportCountCallback, url:'report/list.php'}, //自动汇报
			4: {type:'callback', callback: listResourceFileCountCallback}, //附件数量
			20: {url:'operaterecord/list.php', fromIdName:'from_id', parameter: {from_type:3, op_type_class:0, is_deleted:0}}, //操作日志
		}
	},
};
/**
 * 获取并更新标签页角标
 * @param {string} subType 子分类，例如：plan_0, task_0等，见上述SidepageTabBadges定义
 * @param {string} prefix tab标签命名前缀
 * @param {Array} items 菜单项数组，例如：[0,2,3]
 * @param {object} $container (可选) 标签页的父容器(JQuery对象)
 */
function refreshTabBadges(subType, prefix, targetItems, $container) {
	var ptr = SidepageTabBadges[subType];
	//var ptrType = ptr.ptrType;
	subItems = ptr.datas;
	
	for (var i=0; i<targetItems.length; i++) {
		var targetItem = targetItems[i];
		var item = subItems[targetItem.activeNo];
		if (targetItem.execute===false) {
			updateTabBadge($container, false, getServerUrl()+item.url, prefix, targetItem.activeNo);//, parameter);
			continue;
		}
		
		if (item.type!='callback') {
//			if (item.parameter==undefined)
//				continue;
			if (item.url==undefined)
				continue;
			
			var parameter = $.extend({}, item.parameter, targetItem.parameter);
			parameter.request_for_count = 1;
			parameter[item.fromIdName] = targetItem.ptr_id;
			
			updateTabBadge($container, true, getServerUrl()+item.url, prefix, targetItem.activeNo, parameter);
		} else {
			if (item.callback) {
				var parameter = new Array();
				if (item.url) {
					parameter.push(getServerUrl()+item.url);
				}
				parameter.push(targetItem.activeNo);
				var param = $.extend({}, targetItem, targetItem.parameter);
				delete param.activeNo;
				parameter.push(param);
				parameter.push(function(activeNo, count) {
					if ($container) {
						$container.find('.'+prefix+'-head#'+prefix + activeNo + ' .scount').html(count);
					} else {
						$('.'+prefix+'-head#'+prefix + activeNo + ' .scount').html(count);
					}
				});
				
				item.callback.apply(this, parameter);
			}
		}
	}
}

/**
 * 保存评论回复
 * @param {string|number} ptrId (计划、任务、报告)编号
 * @param {number} ptrType 类型：1=计划，2=任务，3=报告
 * @param content 评论内容
 * @param fileResourceId 附件资源编号
 * @param fileName 附件名称
 * @param successHandle 评论保存成功后回调函数
 * @param errorHandle 评论保存失败后回调函数
 */
function saveReplyOrDiscuss(ptrId, ptrType, content, fileResourceId, fileName, successHandle, errorHandle) {
	var parameter = {from_id:ptrId, from_type:ptrType, op_type:3, remark:content};
	if (fileResourceId)
		parameter.op_data = fileResourceId;
	if (fileName)
		parameter.op_name = fileName;
	
	//提交执行
	executeCreate(getServerUrl() + 'operaterecord/saveCreate.php', parameter, successHandle, errorHandle);
}

/**
 * 获取某个(计划、任务、报告)的评论回复数量
 * @param {string|number} ptrId (计划、任务、报告)编号
 * @param {number} ptrType 类型：1=计划，2=任务，3=报告
 * @param successHandle 成功后回调函数
 * @param errorHandle 失败后回调函数
 */
function getReplyOrDiscussCount(ptrId, ptrType, successHandle, errorHandle) {
	var parameter = {from_id:ptrId, from_type:ptrType, op_type:3, request_for_count:1};
	loadRecordCount(getServerUrl() + 'operaterecord/list.php', parameter, successHandle, errorHandle);
}

/**
 * 保存日报/报告的评阅回复
 * @param {string|number} ptrId 报告编号
 * @param content 内容
 * @param successHandle 保存成功后回调函数
 * @param errorHandle 保存失败后回调函数
 */
function saveReplayOfReviewReport(ptrId, content, successHandle, errorHandle) {
	var parameter = {pk_report_id:ptrId, op_type:22, remark:content};
	
	//提交执行
	executeUpdate(getServerUrl() + 'report/saveUpdate.php', parameter, successHandle, errorHandle);
}

/**
 * 任务上报进度/上报工时
 * @param type 类型：22=上报进度，23=上报工时
 * @param taskId 任务编号
 * @param value 进度：0-100，工时：N小时
 * @param remark 进度说明，工时说明
 * @param opTime 工时所在日期
 * @param {function} successHandle 执行成功后回调函数
 * @param {function} errorHandle 执行失败后回调函数
 */
function submitTaskPercentOrWorkTime(type, taskId, value, remark, opTime, successHandle, errorHandle) {
	var title = '';
	var opType = (type==22)?31:32; //op_type：31=上报进度，32=上报工时
	var parameter = {op_type: opType, pk_task_id:taskId, op_remark:remark};
	if (type==22) {
		title = '进度';
		parameter.percentage = value;
	} else if (type==23) {
		title = '工时';
		parameter.work_time = parseFloat(value)*60;
		parameter.op_time = opTime;
	}
	
	//提交执行
	executeUpdate(getServerUrl() + 'task/saveUpdate.php', parameter, function(result) {
		if (successHandle)
			successHandle(result);
	}, function(err){
		if (errorHandle)
			errorHandle(err);
		else 
			layer.msg('上报'+title+'失败', {icon:2});
	});
}

/**
 * 任务相关操作
 * @param type 类型：9=标记完成，10=中止任务
 * @param taskId 任务编号
 * @param remark 说明
 * @param {function} successHandle 执行成功后回调函数
 * @param {function} errorHandle 执行失败后回调函数
 */
function taskAction(type, taskId, remark, successHandle, errorHandle) {
	var opType = (type==10)?33:34; //op_type：33：中止任务，34：标为完成
	var title = (type==10)?'中止任务':'标记完成';
	var parameter = {op_type: opType, pk_task_id: taskId};
	if (type==10)
		parameter.op_remark = remark;
	
	//提交执行
	executeUpdate(getServerUrl() + 'task/saveUpdate.php', parameter, function(result) {
		if (successHandle)
			successHandle(result);
	}, function(err) {
		if (errorHandle)
			errorHandle(err);
		else 
			layer.msg(title+'任务失败', {icon:2});
	});
}

/**
 * 获取一个任务的资料
 * @param taskId 任务编号
 * @param {function} successHandle 执行成功后回调函数
 * @param {function} errorHandle 执行失败后回调函数
 */
function fetchOneTask(taskId, successHandle, errorHandle) {
	executeQueryOne(getServerUrl() + 'task/get_one.php', {task_id: taskId}, successHandle, errorHandle);
}

/**
 * 提交计划评审/提交报告评阅（不适用于任务）
 * @param {number} fromType 类型:1=计划、3=报告
 * @param {string} fromId (计划、报告)编号
 * @param {string} approvalUserId 评审人/评阅人编号
 * @param {string} approvalUserName 评审人/评阅人名称
 * @param {string} remark 备注
 * @param {function} successHandle 执行成功后回调函数
 * @param {function} errorHandle 执行失败后回调函数
 */
function submitApproval(fromType, fromId, approvalUserId, approvalUserName, remark, successHandle, errorHandle) {
	var parameter = {approval_action: 1, from_type:fromType, from_id:fromId, approval_user_id:approvalUserId, approval_user_name:approvalUserName, remark:remark};
	var title = (fromType==1)?'评审':'评阅';
	
	//提交执行
	executeCreate(getServerUrl() + 'shareuser/saveApproval.php', parameter, function(result) {
		if (successHandle)
			successHandle(result);
	}, function(err) {
		if (errorHandle)
			errorHandle(err);
		else 
			layer.msg('提交'+title+'失败', {icon:2});
	});
}

/**
 * 计划、报告相关操作（不适用于任务）
 * @param {number} actionType 操作类型：2=通过/回复，3=拒绝，4=取消，5=标为完成
 * @param {number} fromType 类型:1=计划、3=报告
 * @param {string} fromId (计划、报告)编号
 * @param {string} remark 备注
 * @param {boolean} createReadReocrd是否创建"评审人已阅"的操作日志
 * @param {function} successHandle 执行成功后回调函数
 * @param {function} errorHandle 执行失败后回调函数
 */
function approvalAction(actionType, fromType, fromId, remark, createReadReocrd, successHandle, errorHandle) {
	var title = (actionType==2)?(fromType==1?'评审通过':'评阅回复'):(actionType==3?'评审拒绝':(actionType==4?'撤销申请':'标为完成'));
	
	var parameter = {approval_action: actionType, from_type:fromType, from_id:fromId, remark:remark};
	if (fromType==1 && (actionType==2 || actionType==3) && createReadReocrd==false) {
		parameter.custom_param = 1; //表示不创建"评审人已阅"的操作日志
	}
	
	//提交执行
	executeUpdate(getServerUrl() + 'shareuser/saveApproval.php', parameter, function(result) {
		if (successHandle)
			successHandle(result);
	}, function(err) {
		if (errorHandle)
			errorHandle(err);
		else 
			layer.msg('"'+title+'"失败', {icon:2});
	});
}

/**
 * 标记"未读"状态为"已读"
 * @param {string|int} ptrType 文档类型：1=计划，2=任务，3=报告
 * @param {string} ptrId 文档编号
 * @param shareId {可选} 关联用户表记录的主键(当ptrType=3必填)；当本参数不为空时，将更新关联用户表的"已读"标记状态，否则将更新文档主表的"已读"标记状态
 * @param {function} successHandle {可选} 执行成功后回调函数
 * @param {function} errorHandle {可选} 执行失败后回调函数
 */
function markReadFlagToRead(ptrType, ptrId, shareId, successHandle, errorHandle) {
	var partUrl = 'shareuser/saveUpdate.php';
	var parameter = {};
	
	if (shareId) {
		parameter.share_id = shareId;
		parameter.from_type = ptrType;
		parameter.from_id = ptrId;
		parameter.update_type = 4;
		parameter.read_flag = 1;		
	}
//	else { //用于改变(计划或任务)的“未阅”状态
//		parameter.op_type = 200;
//		switch(parseInt(ptrType)) {
//		case 1:
//			partUrl = 'plan/saveUpdate.php';
//			parameter.pk_plan_id = ptrId;
//			break;
//		case 2:
//			partUrl = 'task/saveUpdate.php';
//			parameter.pk_task_id = ptrId;
//			break;
//		}		
//	}
	
	//提交执行
	executeUpdate(getServerUrl() + partUrl, parameter, successHandle, errorHandle);
}

/**
 * 关注或取消关注任务
 * @param {string} logonUserId 当前登录用户的编号
 * @param {boolean} cancel 是否取消关注
 * @param {int} ptrType 文档类型：2=任务 (只对于任务有效)
 * @param {string} ptrId 文档编号
 * @param {function} successHandle {可选} 执行成功后回调函数
 * @param {function} errorHandle {可选} 执行失败后回调函数
 */
function saveFavorite(logonUserId, cancel, ptrType, ptrId, successHandle, errorHandle) {
	if (ptrType!=2) {
		if (errorHandle)
			errorHandle();
		return;
	}
	
	var partUrl = 'shareuser/saveCreate.php';
	var parameter = {from_type: ptrType, from_id: ptrId, share_type:4};
	
	if (cancel) {
		partUrl = 'shareuser/saveUpdate.php';
		parameter.update_type = 2;
		parameter.valid_flag = 0;
		parameter.share_uid = logonUserId;
	}
	
	//提交执行
	executeUpdate(getServerUrl() + partUrl, parameter, successHandle, errorHandle);
}

/**
 * 更改文档的重要程度属性
 * @param {string|int} ptrType 文档类型：1=计划，2=任务
 * @param {string} ptrId 文档编号
 * @param {int} important 重要程度
 * @param {function} successHandle {可选} 执行成功后回调函数
 * @param {function} errorHandle {可选} 执行失败后回调函数
 */
function changeImportantField(ptrType, ptrId, important, successHandle, errorHandle) {
	var partUrl;
	var parameter = {important: important};
	if (ptrType==1) {
		partUrl = 'plan/saveUpdate.php';
		parameter.pk_plan_id = ptrId;
	} else if (ptrType==2) {
		partUrl = 'task/saveUpdate.php';
		parameter.pk_task_id = ptrId;
		parameter.op_type = 210;
	}
	
	//提交执行
	executeUpdate(getServerUrl() + partUrl, parameter, successHandle, errorHandle);
}

/**
 * 混合各部门成员用户资料(不再以部门进行分类)
 * @param {array|string} userAccounts 各部门内成员的用户资料列表；内部格式：[{"group_id": "999000", "group_name": "技术研发部", "members": [{"user_id": "888001","user_name": "测试员工","emp_id": "3355459500160041"}, ...]}, ...]
 * @return {array} 混合后的用户资料列表
 */
function blendUserAccountsOfGroups(datas) {
	var userAccounts;
	if (typeof datas=='string') {
		userAccounts = json_parse(datas);
	}
	
	//处理空记录或异常记录情况
	if (typeof userAccounts == 'undefined' || Object.prototype.toString.call(userAccounts) !== '[object Array]') {
		return;
	}
	
	//遍历混合用户资料
	var result ={};
	for (var i=0;i <userAccounts.length; i++) {
		for (var j=0;j <userAccounts[i].members.length; j++) {
			var userAccount = userAccounts[i].members[j]; 
			delete userAccount.emp_id;
			if (!result.hasOwnProperty(userAccount.user_id)) {
				result[userAccount.user_id] = userAccount;
			}
		}
	}
	
	//对象转换为数组
	var arry = [];
	for (var key in result) {
		arry.push(result[key]);
	}
	return arry;
	//return $.makeArray(result);
}

/**
 * 获取当前用户相同部门的成员列表
 * @param {function} successHandle 执行成功后回调函数
 * @param {function} errorHandle 执行失败后回调函数
 */
function fetchMemberList(successHandle, errorHandle) {
	loadResults(getServerUrl()+'useraccount/myPartners.php', null, function(results) {
		if (Object.prototype.toString.call(results) === '[object Array]') {
//			alertObjectToJson(results);
			if (successHandle) 
				successHandle(results);
		}
	}, function(error){
		if (errorHandle) 
			errorHandle(error);
	});
}

/**
 * 使用弹出界面选择人员
 * @param onlyOne 是否只选一个
 * @param {function} successHandle 执行成功后回调函数
 */
function selectPersonsUsingLayer(onlyOne, successHandle) {
	var loadIndex = layer.load(2);
	fetchMemberList(function(datas) {
		layer.open({type: 1,
			title: '选择人员',
			area: ['380px', '400px'], //宽高
			content: laytpl($('#select-persons-script').html()).render(datas),
			btn: ['确定', '取消'],
			yes: function(index, layero) {
				if (successHandle)
					successHandle(layero);
				
				layer.close(index);
			},
		  	success: function(layero, index) {
			  	var bottomGutter = 5;
			  	var mouseWheelPixels = 30;
			  	var enableScrollButtons = true;
			  	var height = $(layero).find('.layui-layer-content').height()-$(layero).find('.select-persons .head').outerHeight(true)-2;
			    $(layero).find('.select-persons .main-content').height(height);
			    $(layero).find('.groups-container').height(height-bottomGutter);
			    $(layero).find('.persons-container').height(height-bottomGutter);
			    
			  	//自定义滚动条
				customScrollbarUsingE($(layero).find('.groups-container'), mouseWheelPixels, enableScrollButtons);
				customScrollbarUsingE($(layero).find('.persons-container'), mouseWheelPixels, enableScrollButtons);
				//注册事件-点击部门子项
				$(layero).find('.person-group-item').click(function() {
					var groupId = $(this).attr('data-group-id');
					
					$(layero).find('.person-group-item').removeClass('active');
					$(layero).find('.person-group-item[data-group-id="'+groupId+'"]').addClass('active');
					
					$(layero).find('.persons-container').addClass('ebtw-hide');
					$(layero).find('.persons-container[data-group-id="'+groupId+'"]').removeClass('ebtw-hide');
				});
				//注册事件-点击勾选项
				if (onlyOne) {
					$(layero).find('input[type="checkbox"]').change(function() {
						var empId = $(this).attr('data-emp-id');
						if ($(this).prop('checked')==true) {
							$(layero).find('input[type="checkbox"][data-emp-id!="'+empId+'"]:checked').prop("checked",false);
						}
					});
				}
			}
		});
		
		layer.close(loadIndex);
	}, function() {
		layer.close(loadIndex);
		layer.msg('加载人员失败', {icon:2});
	});
}

//在视图重现(负责人、参与人、共享人)
function reappearPtrPersons(logonUserId, personType, shares, containerSelector) {
	if (typeof shares=='string' && shares.length>0)
		shares = json_parse(shares);
	
	//处理空记录或异常记录情况
	if ((typeof shares == 'undefined' || Object.prototype.toString.call(shares) !== '[object Array]'))
		return;
	
	//更新界面
	var $container = $(containerSelector);
	if (personType.only_one) {
		var person = shares[0];
		var data = {user_id:person.share_uid, user_name:person.share_name};
		data.user_account = person.user_account;
		if (logonUserId!=person.share_uid)
			data.talkToPerson = true;
		
		$container.prepend(laytpl($('#selected-user-script').html()).render(data));
	} else {
		for (var i=0; i<shares.length; i++) {
			var person = shares[i];
			if ($container.find('.selected-person[data-user-id="'+person.share_uid+'"]').length==0) {
				var data = {user_id:person.share_uid, user_name:person.share_name};
				data.user_account = person.user_account;
				if (logonUserId!=person.share_uid)
					data.talkToPerson = true;
				
				$container.prepend(laytpl($('#selected-user-script').html()).render(data));
			}
		}
	}
}

//注册事件-管理(添加-选择、删除)相关人员
function registerManagePtrPersons(logonUserId, personTypes) {
	$('.select-person-option').on('mouseover mouseout', '.selected-person', function(e) { //鼠标悬停事件
		if (e.type == "mouseover") {
			$(this).children('span.glyphicon:not(.unshow)').css('display', 'inline-block');
		} else if(e.type == "mouseout") {
			$(this).children('span.glyphicon:not(.unshow)').css('display', 'none');
		}
	}).on('click', '.selected-person>span.glyphicon-remove', function(e) { //点击删除按钮事件
		$(this).parent().remove();
	}).on('click', '.ptr-add-person', function(e) { //点击添加按钮事件
		var $This = $(this);
		var $parent = $This.parent();
		var shareType = $parent.attr('id');
		//弹出选择人员界面
		var onlyOne = personTypes[shareType].only_one || false;
		selectPersonsUsingLayer(onlyOne, function(layero) {
			var selectedPersons = new Array();
			$(layero).find('input[type="checkbox"]:checked').each(function(i) {
				selectedPersons.push({user_id:$(this).val(), user_name:$(this).attr('data-user-name'), user_account: $(this).attr('data-user-account')});
				if (onlyOne)
					return false;
			});
			
			//更新界面
			if (onlyOne) {
				if (selectedPersons.length>0) {
					var person = selectedPersons[0];
					$parent.find('.selected-person').remove();
					
					if (logonUserId!=person.user_id)
						person.talkToPerson = true;
					$This.before(laytpl($('#selected-user-script').html()).render(person));
				}
			} else {
				//helper_person
				for (var i=0; i<selectedPersons.length; i++) {
					var person = selectedPersons[i];
					if ($parent.find('.selected-person[data-user-id="'+person.user_id+'"]').length==0) {
						if (logonUserId!=person.user_id)
							person.talkToPerson = true;
						
						$This.before(laytpl($('#selected-user-script').html()).render(person));
					}
				}
			}
		});
	});
}

/**
 * 生成备选评审人、评阅人下拉菜单
 * @param {string} selector 选择器
 * @param {string} prefix 控件名称前缀
 * @param {string} defaultOptionName 默认选项名称
 * @param {function} clickItemHandle 点击菜单项回调函数
 * @param {function} successHandle 执行成功后回调函数
 * @param {function} errorHandle 执行失败后回调函数
 */
function createApprovalUserList(selector, prefix, defaultOptionName, clickItemHandle, successHandle, errorHandle) {
	var loadIndex = layer.load(2);
	loadResults(getServerUrl()+'useraccount/myManagers.php', null, function(results) {
		var datas = new Array();
		datas.push({userid:0, username:"", name:defaultOptionName});
		
		if (Object.prototype.toString.call(results) === '[object Array]') {
			for (var i=0;i<results.length;i++) {
				var result = results[i];
				datas.push({userid:result.user_id, username:result.username, name:result.username+'--'+result.dep_name});
			}
		}
		
		$(selector).append(laytpl($('#dropdown-menu-script').html()).render(datas));
		
		//选中人员事件
		$(selector+'>ul.dropdown-menu').on('click', 'li>a', function(e) {
			$parent = $(this).parents(selector);
			var $userIdElement = $parent.children('input[name="'+prefix+'_user_id"]');
			var $userNameElement = $parent.children('input[name="'+prefix+'_user_name"]');
			var oldUserId = $userIdElement.val();
			var oldUserName = $userNameElement.val();
			var newUserId = $(this).attr('data-userid');
			$userIdElement.val((newUserId=='0')?'':newUserId);
			//alert($userIdElement.parent().html());
			
			$userNameElement.val((newUserId=='0')?defaultOptionName:$(this).attr('data-username'));
//			$parent.children('input[name="approval_user_id"]').val($(this).attr('data-userid')==0?'':$(this).attr('data-userid'));
//			$parent.children('input[name="approval_user_name"]').val($(this).attr('data-username'));
			
			if (clickItemHandle)
				clickItemHandle(e, oldUserId, oldUserName, $userIdElement, $userNameElement);
		});
		
		if (successHandle) 
			successHandle();
		
		layer.close(loadIndex);
	}, function(error){
		if (errorHandle) 
			errorHandle(error);
		
		layer.close(loadIndex);
	});
}

/**
 * 注册事件-点击附件链接下载
 * @param {object} $attachment 附件子项元素(jquery对象)
 */
function registerAttamentItemClick($attachment) {
	$attachment.children('a').click(function() {
		var linkElement = this;
		if ($(linkElement).attr('href')!='#')
			return;
		
		var resourceId = $(this).parent().attr('data-resource-id');
		if (resourceId && resourceId!='0') {
			//获取文件信息
			$.ebtw.getfile(resourceId, user_id, function(result){
				if (result.resources && result.resources.length>0) {
					var resource = result.resources[0];
					if (resource.share_url_ssl || resource.share_url) {
						var share_url = (ebHttpPrefix=='https')?resource.share_url_ssl:resource.share_url;
						
						if (share_url) {
							//生成共享链接
							var url = $.ebtw.createShareUrl(share_url)+'&download=1';
							logjs_info('want to open share url='+url);
							//下载文件
							$(linkElement).attr('href', url);
							$(linkElement).unbind('click');
							linkElement.click(); //此处不能使用trigger('click')去触发，只能用dom元素的click函数
							//window.open (url, '_blank', 'height=100, width=400, top=0, left=0, toolbar=no, menubar=no, scrollbars=no, resizable=no,location=n o, status=no');
						}
					} else {
						logjs_info('no share_url for resource_id '+resourceId);
						layer.msg('文件链接不存在', {icon:2});
					}
				} else {
					layer.msg('创建链接失败', {icon:2});
				}
			}, function(result){
				if (result.code == $.jqEBMessenger.errCodeMap.RES_NOT_EXIST.code) {
					layer.msg('资源文件不存在', {icon:2});
				} else 
					layer.msg('获取文件信息失败', {icon:2});
			});
		}
	});
}

/**
 * 创建附件项
 * @param {string} attaContainerSelector 附件列表容器选择器
 * @param {object} data 创建html脚本的选项参数
 * @param {boolean} canDownload 是否允许下载(是否绑定点击链接事件) 
 * @return {object} 附件列表子项的对象(jquery对象)
 */
function createAttamentItem(attaContainerSelector, data, canDownload) {
	var $newAttachment = $(attaContainerSelector+' ul').append(laytpl($('#file-upload-list-item-script').html()).render(data)).find('li:last');
	if (canDownload)
		//注册事件-点击下载
		registerAttamentItemClick($newAttachment);
	
	return $newAttachment;
}

//执行上传文件(用于新建文档时上传文件)
function executeSendfile(fromType, ptrId, attaType, liElements, fileCount, i, title, isCreateOperateRecord, closeCallback) {
	i++;
	var attachment = liElements.shift();
	if (!attachment)
		return;
	
	var $attachment = $(attachment);
	var $fileElement = $attachment.find('input[type="file"]');
	var fileElementId = $fileElement.attr('id');
	logjs_info('want to upload file id='+fileElementId+', '+$fileElement.val());
	
	//执行上传文件
	$attachment.find('.resource-size').after(laytpl($('#img-of-uploading-script').html()).render({img:getServerUrl()+'images/loading2.gif'})); //添加“正在上传状态”的图标
	$attachment.find('.resource-size').after(laytpl($('#icon-of-stop-script').html()).render({moreClass:'attachment-stop'})); //添加“点击中止”的图标
	$attachment.find('.attachment-remove').remove(); //去除“删除”图标
	
	var name = $attachment.attr('data-atta-name');
	var jqxhr = $.ebtw.sendfile(fromType, ptrId, attaType, name, null, fileElementId, false, function(result, resourceId) { //success
		sendfileCallback(result, fromType, ptrId, isCreateOperateRecord, resourceId, $attachment, fileElementId, null);
		closeCallback(title, false, i, fileCount, name, result, resourceId);
		
		executeSendfile(fromType, ptrId, attaType, liElements, fileCount, i, title, isCreateOperateRecord, closeCallback);
	}, function(result) { //error
		sendfileCallback(result, fromType, ptrId, isCreateOperateRecord, null, $attachment, fileElementId, null);
		closeCallback(title, false, i, fileCount, name, result);
		
		executeSendfile(fromType, ptrId, attaType, liElements, fileCount, i, title, isCreateOperateRecord, closeCallback);
	}, function(jqxhr) { //start
		if (jqxhr) {
			$attachment.find('.attachment-stop').click(function() {
				jqxhr.abort();
			});
		}
	});
};

/**
 * 定义执行上传文件后的回调函数
 * @param {object} result 服务端执行后返回结果
 * @param {string} resourceId 资源编号
 * @param {object} $attachment 附件元素对象(jquery对象)
 * @param {string} fileElementId 文件控件id
 * @param {boolean} isCreateOperateRecord 是否创建“添加附件”的操作日志
 * @param {function} successHandle 执行成功后的回调函数
 */
function sendfileCallback(result, fromType, ptrId, isCreateOperateRecord, resourceId, $attachment, fileElementId, successHandle) {
	var fileName = $attachment.attr('data-atta-name');
	var codeMap = $.jqEBMessenger.errCodeMap;
	
	if (result.code==codeMap.OK.code) { //上传成功
		logjs_info('upload file success, id='+fileElementId+', '+fileName);
		
		//创建操作日志“添加附件”
		if (isCreateOperateRecord) {
			var opType = 1; //类型=“添加附件”
			var opFromType = parseInt(fromType-10);
			createOperateRecord(ptrId, opFromType, opType, null, resourceId, fileName, null, function(opId) {
				logjs_info('createOperateRecord success ptrId='+ptrId+', opFromType='+opFromType+', resourceId='+resourceId+', name='+fileName+', opId='+opId);
			}, function(err){
				logjs_err('createOperateRecord error ptrId='+ptrId+', opFromType='+opFromType+', resourceId='+resourceId+', name='+fileName);
			}); //创建操作日志
		}
		
		$attachment.attr('data-resource-id', resourceId);
		
		//添加删除按钮
		if ($attachment.find('span.attachment-remove').length==0) {
			$attachment.find('.resource-size').after(laytpl($('#icon-of-remove-script').html()).render({moreClass:'attachment-remove'}));
		}
		
		//绑定点击下载事件
		registerAttamentItemClick($attachment);
		
		//成功上传后回调函数
		if (successHandle)
			successHandle(result, resourceId);
	} else if (result.code==codeMap.ABORT.code) { //中止
		logjs_info('upload file stopped, id='+fileElementId+', '+fileName);
		$attachment.find('.resource-size').after(laytpl($('#icon-of-stopped-script').html()).render({moreClass:'attachment-stopped attachment-remove'}));
	} else { //其它错误
		logjs_info('upload file failure, id='+fileElementId+', '+fileName);
		$attachment.find('.resource-size').after(laytpl($('#icon-of-error-script').html()).render({moreClass:'attachment-error attachment-remove'}));
	}
	
	$attachment.find('.attachment-stop').remove();
	$attachment.find('.attachment-uploading').remove();
}

/**
 * 注册事件-(新增、删除、列表、下载等)管理附件操作
 * @param {boolean} editMode 是否编辑模式
 * @param {boolean} isOnlyView 是否只读
 * @param {number} ptrType 业务类型：1=计划，2=任务，3=报告
 * @param {string} ptrId 业务(计划、任务、报告)编号
 * @param {number} attaType 附件类型：0=本体(文档)附件，3=评论附件
 * @param {string} fileInputParentSelector 选择文件控件父元素选择器
 * @param {function} addFn 选择上传文件后回调函数，参数：(e, filePath, name, oldFileInputElement, newFileInputElement)
 * @param {boolean} loadExist 是否加载已有附件
 * @param {boolean} onlyOne 是否只允许上传一个附件
 * @param {string} attaContainerSelector 附件列表容器选择器
 * @param {string} delAttaSelector 删除附件按钮选择器
 * @param {function} delFn 点击删除按钮回调函数
 */
function registerAttachmentActions(editMode, isOnlyView, ptrType, ptrId, attaType, fileInputParentSelector, addFn, loadExist, onlyOne, attaContainerSelector, delAttaSelector, delFn) {
	var fromType = 10 + parseInt(ptrType);
	
	if (editMode && loadExist && attaContainerSelector) {
		//加载附件列表
		var loadIndex = layer.load(2); //正在加载
		$.ebtw.listfile(fromType, ptrId, attaType, 0, null, null, function(list) {
			if (list.resources) {
				var resources = list.resources;
				//排序(index,create_time)
				resources.sort(function(a, b){
					if (a.index!=b.index) {
						return a.index-b.index;
					}
		            return a.create_time.localeCompare(b.create_time);
				});
				
				//显示在视图
				for (key in resources) {
					var entity =  resources[key];
					
					var $attaElement = createAttamentItem(attaContainerSelector, {
						resourceId: entity.resource_id,
						resourceSize: entity.size,
						name: entity.name,
						iconRemove: !isOnlyView,
						iconStop: false,
					}, editMode);
				}
				//触发重刷界面
				$(window).trigger('resize');
			}
			layer.close(loadIndex);
		}, function(error) {
			logjs_info('加载文件列表失败：'+error);
			layer.close(loadIndex);
		});
	}
	
	if (!isOnlyView && fileInputParentSelector && attaContainerSelector) {
		//注册事件-点击上传附件按钮并选择文件
		$(fileInputParentSelector).off('change', '.file_upload_input').on('change', '.file_upload_input', function(e) {
			var filePath = $(this).val();
			if (filePath.length>0) {
				var resourceSize =localUploadFileSize($(this)[0]);
				if (resourceSize > 1024*1024*20) { //最大支持20M文件上传
					logjs_err('upload file exceeds the maximum length');
					layer.msg('文件超过最大长度(20M)', {icon:2});
					
					//复位file控件
					if(checkIsIE())
						$(this).replaceWith($(this).clone(true));
					else
						$(this).val('');
					
					return;
				}
				
				var name = filePath.match(/([^/\\])+$/ig);
				if (name && name.length)
					name = name[0];
				
				//重要技巧：复制file input以后，把旧的移动到列表里，新的放到原位置
				var $cloneFileInput = $(this).clone();
				$(this).before($cloneFileInput);
				
				var randomNumberOfId = Math.floor(Math.random() * 10000000);
				
				var options = {
						resourceId: '0',
						resourceSize: resourceSize,
						randomNumberOfId: randomNumberOfId,
						name: name,
						filePath: filePath,
						iconRemove: !editMode,
						iconStop: editMode,
						added: true,
					};
				if (editMode)
					options.img = getServerUrl()+'images/loading2.gif';
				var $newAttachment = createAttamentItem(attaContainerSelector, options, false);
				
				$newAttachment.children('.resource-size').after($(this));
				
				//创建file input的id，注意！！！务必在新旧元素交互位置以后再设置id，
				//否则用jquery通过id查找出来的file控件对象无法获取value
				var fileElementId = 'file_upload_input_'+randomNumberOfId;
				$(this).attr('id', fileElementId);
				var filePath = $(this).val();
				
				//仅允许上传一个附件
				if (onlyOne) {
					$cloneFileInput.attr('disabled', 'disabled');
					$(fileInputParentSelector).addClass('select-disabled');
				}
				
				if (editMode/* && addFn*/) { //编辑文档模式-立刻上传文件
					logjs_info('want to upload file immediately id='+fileElementId+', '+filePath);
					//执行上传文件
					var jqxhr = $.ebtw.sendfile(fromType, ptrId, attaType, name, null, fileElementId, false, function(result, resourceId) { //success
						sendfileCallback(result, fromType, ptrId, true, resourceId, $newAttachment, fileElementId, addFn);
					}, function(result) { //error
						sendfileCallback(result, fromType, ptrId, true, null, $newAttachment, fileElementId, null);
					}, function(jqxhr) { //start
						if (jqxhr) {
							$newAttachment.find('.attachment-stop').click(function() {
								jqxhr.abort();
							});
						}
					});
				} else { //新建文档模式-稍后上传文件(与文档一起提交)
					logjs_info('want to upload file later id='+fileElementId+', '+filePath);
					if (addFn)
						addFn();
				}
			}
		});
	}
	
	if (attaContainerSelector) {
		//定义函数：允许文件选择控件
		var enableFileInput = function() {
			if (onlyOne && fileInputParentSelector)
				$(fileInputParentSelector).removeClass('select-disabled').find('.file_upload_input').removeAttr('disabled');
		};
		
		//注册事件-点击删除按钮
		$(attaContainerSelector).off('click', delAttaSelector).on('click', delAttaSelector, function() {
			if (editMode) { //编辑模式-立刻提交到服务端执行删除
				var THIS = this;
				var $li = $(this).parents(attaContainerSelector+' li');
				var resourceId = $(this).parent().attr('data-resource-id');
				
				if (resourceId==0) { //删除未成功上传的文件
					$li.remove();
				} else { //删除已上传成功的文件
					layer.confirm('真的要删除文件吗?', function(index) {
						var loadIndex = layer.load(2);
						
						(function() {
							//var $li = $(this).parents(attaContainerSelector+' li');
							//var resourceId = $(this).parent().attr('data-resource-id');
							var fileName = $(this).parent().attr('data-atta-name');
							$.ebtw.deletefile(resourceId, function() {
								layer.msg('删除成功');
								$li.remove();
								enableFileInput();
								
								//创建操作日志“删除附件”(文档附件)
								var opTypeC = 2;
								createOperateRecord(ptrId, ptrType, opTypeC, null, resourceId, fileName, null, function(opId) {
									logjs_info('createOperateRecord success opType='+opTypeC+', ptrId='+ptrId+', opFromType='+ptrType+', resourceId='+resourceId+', fileName='+fileName+', opId='+opId);
								}, function(err) {
									logjs_err('createOperateRecord error opType='+opTypeC+', ptrId='+ptrId+', opFromType='+ptrType+', resourceId='+resourceId+', fileName='+fileName);
								});
								//修改操作日志“添加附件”，更新opData字段值为0
								var opTypeU = 1;
								//updateOperateRecord(opId, fromId, fromType, opType, originOpData, remark, opData, opName, successHandle, errorHandle)
								updateOperateRecord(null, ptrId, ptrType, opTypeU, resourceId, null, 0, null, function(result) {
									logjs_info('updateOperateRecord success opType='+opTypeU+', ptrId='+ptrId+', opFromType='+ptrType+', originOpData='+resourceId+', originOpName='+fileName);
								}, function(err) {
									logjs_err('updateOperateRecord error opType='+opTypeU+', ptrId='+ptrId+', opFromType='+ptrType+', originOpData='+resourceId+', originOpName='+fileName);
								});
								
								//成功删除后回调函数
								if (delFn)
									delFn(resourceId);
								
								layer.close(loadIndex);
							}, function() {
								layer.msg('删除失败', {icon: 5});
								layer.close(loadIndex);
							});
							
							layer.close(index);
						}).call(THIS);
					});
				}
			} else { //新建模式-仅删除视图元素
				var $li = $(this).parents(attaContainerSelector+' li');
				$li.remove();
				enableFileInput();
				
				if (delFn)
					delFn();
			}
		});
	}
}

//注册事件-点击查看(计划、任务)
function registerClickOpenPtr($container) {
	$container.off('click', '.ptr_item').on('click', '.ptr_item', function (e) {
	    var $itemElement = $(this);
	    var targetPtrType = $itemElement.attr('data-ptr-type');
		var targetPtrId = $itemElement.attr('data-ptr-id');
		
		ptrDetailsAction(targetPtrType, targetPtrId, function() {
			//加载详情页面成功后回调函数
		});
	});
}

//注册事件-打开附件文件
function registerOpenResource() {
	$('body').off('click', '.attachment-link .open-resource').on('click', '.attachment-link .open-resource', function() {
		var url = $(this).attr('data-open-url');
		var extType = $(this).attr('data-ext-type');
		switch(parseInt(extType)) {
		case 1: //pdf文档
		case 3: //office文档
			var loadIndex = layer.load(2);
			setTimeout(function() {
				window.open (url, '_blank', 'height=400,width=600,top=0,left=0');
				layer.close(loadIndex);
			}, 700);
			break;
		case 2: //图片文件
			//其它途径实现
			break;
		}
	});
}

//注册事件-标签页子项(编辑、删除等)管理操作
function registerSidepageTabItemActions($container, tabType, ptrId, ptrType, TabBadgesDatas, subTypeOfTabBadges, prefix) {
	//注册事件-鼠标在每记录上悬停
	$container.find('.sidepage-tab-page-list').has('li[data-can-edit="1"], li[data-can-delete="1"]').hover(function() {
		var $element = $(this).find('.time-mark li[data-type="hv"]');
		$element.removeClass('ebtw-hide'); //隐藏整个工具栏
	}, function() {
		var $element = $(this).find('.time-mark li[data-type="hv"]').has('.tab-page-list-save.ebtw-hide');
		$element = $element.length>0?$element:$(this).find('.time-mark li[data-type="hv"]').filter(':not(:has(.tab-page-list-save))');
		$element.addClass('ebtw-hide'); //隐藏整个工具栏
	});
	
	//注册事件-点击编辑按钮
	$container.find('.sidepage-tab-page-list').find('li[data-can-edit="1"] .tab-page-list-edit').click(function() {
		var $detailDiv = $(this).parents('.sidepage-tab-page-list').find('.sidepage-tab-page-detail .p-detail').attr('contenteditable', true).addClass('editmode');
		var originContent = $detailDiv.html();
		cursorMoveToLastInDiv($detailDiv[0]); //光标移到尾部
		$(this).addClass('ebtw-hide').parent().find('.tab-page-list-save, .tab-page-list-undo').removeClass('ebtw-hide') //隐藏编辑按钮，显示保存按钮
			.unbind('click').click(function() { //绑定点击事件
				var $element = $(this);

				//定义函数：退出编辑模式
				var exitEditModeFn = function(rollback) {
					$element.parent().find('.tab-page-list-save, .tab-page-list-undo').addClass('ebtw-hide');
					$element.parent().find('.tab-page-list-edit').removeClass('ebtw-hide');
					
					$detailDiv.attr('contenteditable', false).removeClass('editmode');
					if (rollback)
						$detailDiv.html(originContent);
				};
				
				if ($element.hasClass('tab-page-list-save')) { //保存编辑结果
					var opType = $element.parents('.sidepage-tab-page-list').attr('data-op-type');
					if (typeof opType!=='undefined') {
						var opId = $element.parents('.sidepage-tab-page-list').attr('data-op-id');
						var remark = convertHtmlToTxt(html_decode($detailDiv.html()));//convertHtmlToTxt($detailDiv.html());
						
						if (!checkContentLength(0, 'discuss', remark))
						    return false;
						
						var loadIndex = layer.load(2);
						//修改操作备注
						//updateOperateRecord(opId, fromId, fromType, opType, originOpData, remark, opData, opName, successHandle, errorHandle)
						updateOperateRecord(opId, null, null, opType, null, remark, null, null, function(result) {
							exitEditModeFn();
							layer.msg('保存编辑成功');
							layer.close(loadIndex);
						}, function(err) {
							layer.msg('保存编辑失败', {icon:2});
							layer.close(loadIndex);
						});
					} else {
						layer.msg('不支持编辑', {icon:2});
					}
				} else if ($element.hasClass('tab-page-list-undo')) { //取消编辑
					exitEditModeFn(true);
				}
			});
	});
	
	//注册事件-点击删除按钮
	$container.find('.sidepage-tab-page-list').find('li[data-can-delete="1"] .tab-page-list-remove').click(function() {
		var $element = $(this);
		
		layer.confirm('真的要删除吗?', function(index) {
			var opType = $element.parents('.sidepage-tab-page-list').attr('data-op-type');
			if (typeof opType!=='undefined') {
				var opId = $element.parents('.sidepage-tab-page-list').attr('data-op-id');
				
				//删除成功后写入操作日志
				var successCallback = function(opId, actionOpType, attaType, resourceId, resourceName, remark) {
					var newOpType;
					var needUpdateOperateRecord = false;
					var newRemark;
					var newOpData = 0;
					var opTypeU;
					switch(parseInt(actionOpType)) { //判断当前的操作日志类型
					case 0: //附件界面
						needUpdateOperateRecord = true;
						if (attaType==0) {
							opTypeU = 1;
							newOpType = 2;
						} else if (attaType==3) {
							opTypeU = 3;
							newOpType = 5;
						}
						break;
					case 1: //添加附件
						needUpdateOperateRecord = true;
						opTypeU = actionOpType;
						newOpType = 2;
						break;
					case 3: //评论/回复(包括内容和附件)
						newOpType = 4;
						break;
					default: //其它情况直接返回
						return;
						break;
					}
					opData = resourceId;
					opName = resourceName;
					//创建操作日志
					if (!isTypeEmpty(newOpType)) {
						//createOperateRecord(fromId, fromType, opType, remark, opData, opName, opTime, successHandle, errorHandle)
						createOperateRecord(ptrId, ptrType, newOpType, remark, opData, opName, null, function(newOpId){
							logjs_info('createOperateRecord success opType='+newOpType+', ptrId='+ptrId+', opFromType='+ptrType+', resourceId='+resourceId+', resourceName='+resourceName+', opId='+newOpId);
						}, function(err) {
							logjs_err('createOperateRecord error opType='+newOpType+', ptrId='+ptrId+', opFromType='+ptrType+', resourceId='+resourceId+', resourceName='+resourceName);
						});
					}

					//修改操作日志
					if (needUpdateOperateRecord) {
						//updateOperateRecord(opId, fromId, fromType, opType, originOpData, remark, opData, opName, successHandle, errorHandle)
						updateOperateRecord(opId, ptrId, ptrType, opTypeU, resourceId, newRemark, newOpData, null, function(result) {
							logjs_info('updateOperateRecord success opId = '+opId+', opType='+opTypeU+', ptrId='+ptrId+', opFromType='+ptrType+', originOpData='+resourceId+', originOpName='+resourceName);
						}, function(err) {
							logjs_err('updateOperateRecord error opId = '+opId+', opType='+opTypeU+', ptrId='+ptrId+', opFromType='+ptrType+', originOpData='+resourceId+', originOpName='+resourceName);
						});
					}
				};

				var refreshViews = function(msgArry, loadIndex, needReloadAttaTab) {
					layer.msg.apply(this, isTypeEmpty(msgArry)?['删除成功']:msgArry);
					layer.close(loadIndex);
					
					var reloadTabs = [];
					if (ptrType==0) { //工作台页面
						reloadTabs.push(TabBadgesDatas[tabType]);
					} else { //普通页面
						reloadTabs.push(TabBadgesDatas[20]);
						if (needReloadAttaTab)  
							reloadTabs.push(TabBadgesDatas[ptrType==1?5:9]); //附件标签数量
						if (tabType==0) 
							reloadTabs.push(TabBadgesDatas[ptrType==1?2:3]); //评论标签数量
						else  
							reloadTabs.push(TabBadgesDatas[tabType]);
					}
					
					refreshTabBadges(subTypeOfTabBadges, (prefix?prefix:'sidepage-tab'), reloadTabs); //刷新Tab角标数值
					$container.parent().find('#'+(prefix?prefix:'sidepage-tab')+tabType).trigger('click'); //模拟点击刷新Tab内容页面
				};

				var attaType = $element.parents('.sidepage-tab-page-module').attr('data-atta-type');
				var resourceId = $element.parents('.sidepage-tab-page-list').attr('data-op-data') || $element.parents('.sidepage-tab-page-list').attr('data-resource-id');
				var resourceName = $element.parents('.sidepage-tab-page-list').attr('data-op-name') || $element.parents('.sidepage-tab-page-list').attr('data-resource-name');
				
				if (resourceId && resourceId!='0') {
					//定义函数：执行删除"评论回复"操作日志
					var delFunc = function(resourceId, loadIndex) {
						if (!isTypeEmpty(opId)) {
							deleteOperateRecord(opId, function(result) {
								successCallback(opId, opType, attaType, resourceId, resourceName, null);
								refreshViews(null, loadIndex, true);					
							}, function(err) {
		 						successCallback(opId, opType, attaType, resourceId, resourceName, '删除附件文件成功，但未能成功删除对应的操作日志');
		 						refreshViews(['删除附件文件成功，但未能成功删除对应的操作日志', {icon: 5}], loadIndex, true);				 												
							});
						} else {
							successCallback(opId, opType, attaType, resourceId, resourceName, null);
							refreshViews(null, loadIndex, true);					
						}
					};
					
					//调用RestAPI删除远程资源文件
					var loadIndex = layer.load(2);
					$.ebtw.deletefile(resourceId, function(result) {
						delFunc(resourceId, loadIndex);
					}, function(err) {
						if (err.code==53) {
							logjs_err('resource is not exist, but it will delete the operate record');
							delFunc(resourceId, loadIndex);
						} else {
							layer.msg('删除资源文件失败', {icon:2});
							layer.close(loadIndex);
						}
					});
				} else {
					if (!isTypeEmpty(opId)) {
						var loadIndex = layer.load(2);
						deleteOperateRecord(opId, function(result) {
							successCallback(opId, opType, attaType, 0, null, null);
							refreshViews(null, loadIndex, false);
						}, function(err) {
							layer.msg('删除操作日志失败', {icon:2});
							layer.close(loadIndex);
						});
					} else {
						layer.msg('不能执行删除操作', {icon:2});
					}
				}
			} else {
				layer.msg('不支持删除操作', {icon:2});
			}
			
			layer.close(index);
		}, function() {
		});
	});	
}

//注册事件-管理关联用户操作(删除等)及对应工具栏
function registerDeleteShareUsersAction(logonUserId, $container, ptrId, ptrType, delFn) {
	//注册事件-成员标签页内，成员头像鼠标悬停
	$container.off('mouseover mouseout', '.share_user .sidepage-tab-page-headphoto .image-cover').on('mouseover mouseout', '.share_user .sidepage-tab-page-headphoto .image-cover', function(e) {
		if (e.type=='mouseover') { //鼠标悬停
			$(this).find('.toolbar-mask').removeClass('ebtw-hide');
		} else { //鼠标离开
			$(this).find('.toolbar-mask').addClass('ebtw-hide');
		}
	});
	
	//注册事件-成员标签页内，点击成员头像上的工具栏
	$container.off('click', '.share_user .sidepage-tab-page-headphoto .toolbar-mask-item').on('click', '.share_user .sidepage-tab-page-headphoto .toolbar-mask-item', function() {
		if($(this).hasClass('person-remove')) { //删除成员
			var $element = $(this);
			var shareType = $element.parents('.sidepage-tab-page-module').attr('data-share-type');
			var shareId = $element.parents('.share_user').attr('data-share-id');
			var shareUid = $element.parents('.sidepage-tab-page-headphoto').attr('data-user-id');
			var shareName = $element.parents('.sidepage-tab-page-headphoto').attr('data-user-name');
			var loadIndex = layer.load(2);
			invalidShareUser(ptrId, ptrType, shareType, shareId, function(result){
				var typeName = (shareType==2)?'参与人':(shareType==3?'共享人':(shareType==4?"关注人":"关联用户"));
				layer.msg('删除'+typeName+'成功');
				$element.parents('.share_user').parent().remove();
				
				if (delFn)
					delFn();
				
				//创建操作日志
				if (shareType==2||shareType==3) { //2 参与人，3 共享人
					var opType = (shareType==2)?12:14; //12=删除参与人，14=删除共享人
					createOperateRecord(ptrId, ptrType, opType, null, shareUid, shareName, null, function(opId) {
						logjs_info('createOperateRecord success ptrId='+ptrId+', opFromType='+ptrType+', user_id='+shareUid+', user_name='+shareName+', opId='+opId);
					}, function(err) {
						logjs_err('createOperateRecord error ptrId='+ptrId+', opFromType='+ptrType+', user_id='+shareUid+', user_name='+shareName);
					});
				}
				
				layer.close(loadIndex);
			}, function(err) {
				layer.close(loadIndex);
			});
		}
	});
}

//注册事件-点击添加关联用户按钮
function registerAddShareUsersAction(logonUserId, $container, ptrId, ptrType, personTypes, rootUrl, addFn) {
	//注册事件-点击添加(参与、共享、关注等)关联用户按钮
	$container.find('.sidepage-tab-page-list .add-shareuser').click(function() {
		var $element = $(this);
		var shareType = $element.parents('.sidepage-tab-page-module').attr('data-share-type');
		//弹出选择人员界面
		var onlyOne = personTypes[shareType].only_one || false;
		selectPersonsUsingLayer(onlyOne, function(layero) {
			var selectedPersons = new Array();
			$(layero).find('input[type="checkbox"]:checked').each(function(i) {
				selectedPersons.push({user_id:$(this).val(), user_name:$(this).attr('data-user-name'), user_account:$(this).attr('data-user-account')});
				if (onlyOne)
					return false;
			});
			//alertObjectToJson(selectedPersons);
			if (selectedPersons.length==0)
				return;
			
			//更新界面
			$parent = $element.parents('.sidepage-tab-page-list.st-shareuser');
			if (onlyOne) { //暂不支持
// 				var person = selectedPersons[0];
// 				$parent.find('.selected-person').remove();
// 				$This.before(laytpl($('#selected-user-script').html()).render(person));
			} else {
				//定义执行函数；如把内容直接放在循环语句中执行，会出现变量错乱
				//headPhoto = rootUrl + '/images/user.png';
				headPhoto = getDefaultHeadPhoto(rootUrl);
				var createFunc = function(persons, i, callback) {
					var person = persons[i];
					person.headPhoto = headPhoto;
					
					//var loadIndex = layer.load(2);
					createShareUser(ptrId, ptrType, shareType, person.user_id, person.user_name, function(shareId) {
						var data = $.extend({read_flag:0, share_id:shareId, canDelete:true}, person);
						if (logonUserId!=person.user_id)
							data.talkToPerson = true;
						data.userAccount = person.user_account;
						//logjs_info(person);
						if (shareType==2 || shareType==3)
							data.shareInvoiceTime = $.D_ALG.formatDate(new Date(), 'yyyy-mm-dd hh:ii');					
						
						$element.parent().before(laytpl('<div>'+$('#sidepage-tab-script-12').html()).render(data)+'</div>');
						//layer.close(loadIndex);
						
						//查询获取头像后更新视图
						loadAndFreshHeadPhoto(person.user_id, $container);
						
						//创建操作日志
						//shareType 2 参与人，3 共享人
						var opType = (shareType==2)?11:13; //11=添加参与人，13=添加共享人
						createOperateRecord(ptrId, ptrType, opType, null, person.user_id, person.user_name, null, function(opId) {
							logjs_info('createOperateRecord success ptrId='+ptrId+', opFromType='+ptrType+', user_id='+person.user_id+', user_name='+person.user_name+', opId='+opId);
						}, function(err) {
							logjs_err('createOperateRecord error ptrId='+ptrId+', opFromType='+ptrType+', user_id='+person.user_id+', user_name='+person.user_name);
						});
						
						if (callback) callback(shareId);
					}, function(err) {
						//layer.close(loadIndex);
						if (callback) callback(err);
					});
				};
				
				//过滤已添加的用户
				var persons = new Array();
				for (var i=0; i<selectedPersons.length; i++) {
					if ($parent.find('div[data-user-id="'+selectedPersons[i].user_id+'"]').length==0)
						persons.push(selectedPersons[i]);
				}
				
				var count = persons.length;
				if (count>0) {
					var loadIndex = layer.load(2);
					for (var i=0; i<persons.length; i++) {
						createFunc(persons, i, function() {
							count--;
							if (count==0) {
								if (addFn) addFn(count);
								layer.close(loadIndex);
							}
						});
					}
				}
			}
		});
	});
}

/**
 * 更新搜索栏结果状态；当输入框查询条件有效时，搜索栏显示内容调整
 * @param {object} $barElement 搜索栏对象(JQuery对象)
 * @param {string} (可选) content 搜索内容
 * @param {number} (可选) count 搜索结果数量 
 */
function updateSearchContentBar($barElement, content, count) {
	if (content) {
		$barElement.css('display', 'inline-block').find('.search-keyword').html(content);
		if (count>0) {
			$barElement.find('.no-result').removeAttr('style');
			$barElement.find('.has-result').css('display', 'inline-block').find('.result-count').html(count);
		} else {
			$barElement.find('.has-result').removeAttr('style')
			$barElement.find('.no-result').css('display', 'inline-block');
		}
	} else {
		$barElement.removeAttr('style'); //清空内置样式：本处等于恢复隐藏状态
	}
}

