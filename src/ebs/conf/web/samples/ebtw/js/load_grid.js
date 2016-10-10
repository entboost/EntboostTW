/**
 * 执行加载表格
 */
var dtGridOption = {
	lang : "zh-cn",
	id : "PTR",	
	loadURL: '',
	ajaxLoad : true,
	loadAll: false,
	columns : createDtGridColumns(), //dtGridColumns,
    gridContainer: 'gridList',
    toolbarContainer: "gridToolBar",
	pageSize : 20,
	pageSizeLimit : [10, 20, 50, 100],
	tableClass : "table table-hover table-responsive grid-tablestyle",
	tools : "refresh",
	sortByRemote : true, //点击排序从服务端查询获取数据
	defaultCodeTableValue: '-',
	//exportFileName : '用户列表',
	onPageBarComplete: function(grid) {
    	//分页下拉框美化
    	$("select.change-page-size").select2({
    	  width:'55px',
    	  theme: "default",
    	  minimumResultsForSearch: Infinity
    	});
	},
	onCellMouseOver: function (value, record, column, grid, dataNo, colunmNo, cell, row, extraCell, e) {
        $(cell).css("background", "none"); //去除单元格显亮效果
    },
    onRowMouseOver: function (value, record, column, grid, dataNo, colunmNo, cell, row, extraRow, e) {
    	var $row = $(row);
    	$row.children().addClass("grid-rowstyle");
    	$row.siblings().css("backgroundColor", "transparent");
    	$row.css("backgroundColor", "#F7F7F7");
    	
    	//验证操作权限
    	var allowedActions =record.allowedActions;
    	if (allowedActions == undefined || Object.prototype.toString.call(allowedActions) !== '[object Array]') return;
    	
    	//判断阻止事件传递的状态
    	if (grid.option.stopEvent==1) return;
    	
        var count = $row.find('td').length;
        $(".actionbar-tr").remove();
        
        $actScript = $('#actionbar-tr-script');
        var isDeleted = record.is_deleted!=undefined?parseInt(record.is_deleted):0;
        var additionalDataMap = {3:{deleted:isDeleted}};
        var status = parseInt(record.status);
        var buttonDatas;
        var buttonDataTypes;
        if (PTR_TYPE==1) { //计划
	        switch (status) {
	            case 0://新建未阅
	            case 1://未处理
	            	buttonDataTypes = isDeleted?[3,8]:[1,2,3,9];
	                break;
	            case 2://评审中
	            case 3:
	            	if (record.su_valid_flag==0) //记录无效
	            		buttonDataTypes = isDeleted?[3,8]:[];
	            	else
	            		buttonDataTypes = isDeleted?[3,8]:[4,5,6];
	                break;
	            case 4://评审通过
	            	buttonDataTypes = isDeleted?[3,8]:[9];
	            	break;	                
	            case 5://评审拒绝
	            	buttonDataTypes = isDeleted?[3,8]:[2,3,7,9];
	            	break;
	            default://其它
	            	if (isDeleted)
	            		buttonDataTypes = [3,8];
	                break;
	        }
        } else if (PTR_TYPE==2) { //任务
	        switch (status) {
            case 0://未查阅
            case 1://未开始
            	buttonDataTypes = [2,22,23,9]; //3(删除功能按钮)被去掉了
                break;
            case 2://进行中
            	buttonDataTypes = [2,22,23,9,10];
                break;
            case 3://已完成
            	buttonDataTypes = [23];
            	break;
            case 4://已中止
            	break;
            default://其它
                break;
	        }
        }
        
        if (buttonDataTypes)  {
        	//创建按钮数据
        	buttonDatas = createButtonDatas(count, allowedActions, buttonDataTypes, additionalDataMap);
	        //计划-删除按钮改名
	        if (PTR_TYPE==1 && !isDeleted) {
				for (var i=0; i< buttonDatas.btns.length; i++) {
					var buttonData = buttonDatas.btns[i];
					if (buttonData.dataType==3) { //按钮从name“删除”改名为name2“移入回收站”
						buttonData.name = buttonData.name2;
					}
				}
	        }        
	        //创建工具栏
	        $row.after(laytpl($actScript.html()).render(buttonDatas));
        }
        
        $actionbarTr = $(".actionbar-tr");
        //添加样式
        $actionbarTr.css("background", "none").css("borderBottom", "1px solid #ddd").css('cursor', 'pointer');
        if ($row.next().attr("class") == "actionbar-tr") 
        	$row.children().addClass("grid-columnstyle-noborder");
        
        //==注册事件==
        //鼠标悬停事件
        $actionbarTr.hover(function() {
			$(this).prev().css("backgroundColor", "#FAFAFA");
			$(this).prev().children().addClass("grid-columnstyle-noborder");
			$(this).prev().children().css("borderBottom", "none");
        }, function() {
			$(this).prev().css("backgroundColor", "transparent");
			$(this).prev().children().removeClass("grid-columnstyle-noborder");
			$(this).prev().children().css("borderBottom", "1px solid #ddd");
        });
        //模拟点击行
        $actionbarTr.click(function(){
        	$(this).prev().find('td[columnno="0"]').trigger('click');
        });
        
        //点击工具栏按钮
        $(".actionbar-item").on("click", function(e) {
        	var type = $(this).attr('data-type');
        	var $parent = $(this).parent().parent();
        	
    		//已创建输入区域的工具栏按钮，再次点击关闭输入区域
        	var $existActionbarContent = $parent.find('.actionbar-content[data-type="'+$(this).attr('data-type')+'"]');
			if ($existActionbarContent.length>0) {
				$existActionbarContent.remove();
				stopPropagation(e);
				grid.option.stopEvent=0; //释放事件传递的状态
				return;
			}
        	
        	//清除旧输入区
        	$parent.find('.actionbar-content').remove();
        	//设置阻止事件传递的状态
        	grid.option.stopEvent = 1;
        	
        	if (type==1 || type==4 || type==5 || type==7) { //1=申请评审，4=通过，5=拒绝(不通过)，7=重新申请评审
        		if ($parent.find('.actionbar-content').length==0) {
                	//创建输入区
                	var content = laytpl($('#actionbar-content-script-pre').html()).render({type:type});
        			if (type==1 || type==7) {
        				content+= laytpl($('#actionbar-content-script-1').html()).render({});
        			} else {
        				content+= laytpl($('#actionbar-content-script-2').html()).render({type:type});
        			}
        			content+= laytpl($('#actionbar-content-script-rear').html()).render({});
        			$parent.append(content);
        			
        			//申请评审
        			if (type==1 || type==7) {
        				//选择评审人
        				var $toggleElement = $parent.find('.dropdown-toggle');
        				createApprovalUserList('#approval_user', 'approval', '--请选择评审人--', function(e) {
        					$toggleElement.dropdown('toggle');
        				}, function() {
        					$toggleElement.dropdown();
        				});
        			}
        		}
        	} else if (type==22 || type==23) { //22=上报进度，23=上报工时
        		if ($parent.find('.actionbar-content').length==0) {
                	//创建输入区
        			var defaultWorkTime = 0.5; //默认工时
                	var content = laytpl($('#actionbar-content-script-pre').html()).render({type:type});
        			if (type==22) {
        				content+= laytpl($('#actionbar-content-script-3').html()).render({percent:record.percentage});
        			} else {
        				content+= laytpl($('#actionbar-content-script-4').html()).render({default_work_time:defaultWorkTime, default_op_time:$.D_ALG.formatDate(new Date(), 'yyyy-mm-dd')});
        			}
        			content+= laytpl($('#actionbar-content-script-rear-2').html()).render({});
        			$parent.append(content);
        			
        			//定义函数：绑定输入区事件
        			function registerInputArea(actionType, step, pipsStep, max, value, unit) {
        			   	//滑块
        				$parent.find("#ptr-slider").slider({animate:true, value:value, min:0, step:step, max:max, range:"min", change: function(e, ui) {
    			 				$parent.find('#ptr-slider-per').attr('data-per', ui.value).text(' '+ui.value+' '+unit);
    			 	    	}
        				}).slider("pips", {step:pipsStep, rest:"label"}).slider("float");
        				
        			   	//textarea自适应高度
        			   	$parent.find('.ptr-slider-remark>textarea').autoHeight();
        			  //初始化日期选择器
        			   	if (actionType==23) {
	        				var dateFormat = 'yyyy-mm-dd';
	        			   	createDefaultDatetimePicker('#ptr-time-value', dateFormat, 2, 2, 4);
	        			   	createDefaultDatetimePicker('#ptr-time-addon', dateFormat, 2, 2, 4, 'ptr-time-value', dateFormat);
        			   	}
        			}
        			//执行绑定输入区事件
        			if (type==22)
        				registerInputArea(type, 1, 5, 100, record.percentage, '%');
        			else
        				registerInputArea(type, 0.5, 2, 24, defaultWorkTime, '小时');
        		}
        	} else if (type==10) { //10=中止
        		//创建输入区
            	var content = laytpl($('#actionbar-content-script-pre').html()).render({type:type});
    			content+= laytpl($('#actionbar-content-script-5').html()).render({});
    			content+= laytpl($('#actionbar-content-script-rear-2').html()).render({});
    			$parent.append(content);
    			//textarea自适应高度
			   	$parent.find('.ptr-abort-remark>textarea').autoHeight();
        	}
        	
        	//关闭或提交输入区内容
        	$('.actionbar-content-close, .actionbar-content-submit').click(function(e) {
        		stopPropagation(e); //阻止本次事件传递
        		
        		var $THIS = $(this);
        		
        		//定义函数：清理输入区
        		var clearActionbar = function($THIS) {
            		grid.option.stopEvent=0; //释放事件传递的状态
            		$THIS.parents('.actionbar-content').remove(); //删除输入区视图
            		//$THIS.parent().parent().filter('.actionbar-content').remove(); //删除输入区视图
        		};
        		
        		//点击提交按钮
        		if ($THIS.hasClass('actionbar-content-submit')) {
        			actionbarSubmit($parent, record[ID_NAME], record, type, row, function(result) {
        				clearActionbar($THIS);
        			});
        		} else {
        			clearActionbar($THIS);
        		}
        	});
        	
        	//自动获取输入焦点
        	//$('.actionbar-content input:first').focus(); //使用日期控件时有BUG
        	
    		//阻止输入区点击事件传递
    		$('.actionbar-content').click(function(e) {
    			stopPropagation(e);
    		});
    		
    		var selectDiv = $row.find(".ebtw-row-select");
    		if (type==2) { //编辑按钮
	    		//选中当前行
	        	if (selectDiv.css('display')=='none') { //当前不是选中状态
	        		$(".ebtw-row-select").hide();//隐藏全部行选中状态 
	                selectDiv.show();//显示当前行选中状态 
	        	} 
    		} else {
    			selectDiv.hide(); //反选
        		closeSidepage(); //关闭右侧页面
    		}
    		
    		//执行下一步操作
        	e.data = {id:record[ID_NAME], record:record, type:type, row:row};
        	if ($(this).attr('data-is-deleted')!=undefined)
        		e.data.is_deleted = $(this).attr('data-is-deleted');
        	
        	ptrActionClick(PTR_TYPE, e, function(result) {
        		//刷新列表
        		if (result!=undefined && result && result!=-1)
        			loadDtGrid(createQueryParameter(), false);
        		//释放事件传递的状态
        		grid.option.stopEvent=0;
        	});
        });
    },
    onRowMouseOut: function (value, record, column, grid, dataNo, colunmNo, cell, row, extraRow, e) {
        $(row).children().removeClass("grid-columnstyle-noborder").removeClass("grid-rowstyle");
        $(row).css("backgroundColor", "transparent");

    	if (grid.option.stopEvent==1) return; //判断阻止事件传递的状态
    },
    onRowClick: function (value, record, column, grid, dataNo, colunmNo, cell, row, extraRow, e) {
    	if (grid.option.stopEvent==1) return; //判断阻止事件传递的状态
    	
        //检测是否有选中文本，以判断用户操作意向
    	var sText = window.getSelection?window.getSelection():document.selection.createRange().text;
    	//忽略下一步操作
    	if(sText!=null && sText!='')
    		return;
    	
    	if ($(e.target).hasClass('talk-to-person')) //点击打开聊天对话框的事件
    		return;
    	
    	if ($(e.target).parents().hasClass('important-level')) //点击"重要程度"色块触发的事件； 此处的parents()与parent()效果是有区别的，少了"s"后选择弹出菜单没响应
    		return;
    	
    	var selectDiv = $(row).find(".ebtw-row-select");
    	if (selectDiv.css('display')=='none') { //当前不是选中状态
    		$(".ebtw-row-select").hide();//隐藏全部行选中状态 
            selectDiv.show();//显示当前行选中状态 
            ptrDetailsAction(PTR_TYPE, record[ID_NAME], function(){}); //显示详情页面； ptrId样例：record[ID_NAME] '3790272800186869' '3790270100163757'
    	} else { //当前已经是选中状态
    		selectDiv.hide(); //反选
    		closeSidepage(); //关闭右侧页面
    	}
    	
        //阻止事件冒泡传递
    	stopPropagation(e);
    },
    onGridComplete : function(grid) {
    	//在正文空白位置插入占位框，用于点击关闭右侧页
    	var $CustomScrollBoxContent = $('.ptr-container>.mCustomScrollBox');
    	var emptyHeight = $CustomScrollBoxContent.height() - $CustomScrollBoxContent.find('.mCSB_container').height();
    	$CustomScrollBoxContent.find('#dtGrid-empty-postion').remove(); //删除旧的占位框
    	$CustomScrollBoxContent.append('<div id="dtGrid-empty-postion" style="width:100%;height:'+emptyHeight+'px;"></div>'); //插入新的占位框
    	//绑定点击事件
    	$CustomScrollBoxContent.find('#dtGrid-empty-postion').click(function(e) {
    		closeSidepage(); //关闭右侧页
    		stopPropagation(e); //阻止事件传递
    	});
    	
    	//更新搜索栏结果状态
    	var parameter = createQueryParameter();
    	updateSearchContentBar($('.search-content-bar'), (PTR_TYPE==1)?parameter.plan_name_lk:parameter.task_name_lk, grid.exhibitDatas.length);
    	
    	//获取临时暂存的参数，并打开计划或任务详情页面
    	if (typeof accessTempKey!='undefined' && accessTempKey!=null) {
    		loadTempCustomParameter(accessTempKey, function(customData) {
    			if (customData) {
    				var accessTempType = customData.int_value;
    				var entity = json_parse(customData.str_value);
    				
    				if (accessTempType==1) {  // 1=自动打开查询详情页面
    	    			var openPtrId = entity.open_ptr_id;
    	    			var switchTabType = entity.switch_tab_type;
    	    			//打开详情页面
    	        		ptrDetailsAction(PTR_TYPE, openPtrId, function(){}, (typeof switchTabType!='undefined' && switchTabType!=null)?{reserved_active_no:switchTabType}:undefined);
    				} else if (accessTempType==2) { // 2=新建计划或任务
    					ptrAddAction(PTR_TYPE, null, function(){}, {reserved_new_ptr_name:entity.post_data});
    				}
    			} else {
    				logjs_info('can not load tempdata for key:'+accessTempKey);
    			}
        		//清空临时访问参数
        		accessTempKey = null;
    		});
    	}
    },
    //即将刷新视图事件
    onWillRefreshView: function () {
    	$('.ptr-container').mCustomScrollbar("scrollTo","top"); //滚动条回顶
    },
    //远程服务返回数据预处理函数
    didLoadedDataPreprocess: didLoadedDataPreprocess
};

/**
 * 点击提交按钮处理函数
 * @param $rootElement 父级jquery对象
 * @param id 记录主键
 * @param record 记录对象
 * @param type 操作类型
 * @param row dtGrid行元素
 * @param {function} fn 执行后回调函数
 * @param {object} data 附加参数
 */
function actionbarSubmit($rootElement, id, record, type, row, fn, data) {
	if (type==1 || type==7) {//申请评审 或 重新提交申请
		var approval_user_id = $rootElement.find('input[name="approval_user_id"]').val();
		if (approval_user_id.length==0) {
			layer.msg('请选择评审人', {icon:2});
			return;
		}
		//询问确认后提交
		askForConfirmSubmit('真的要提交审批吗？', '提交审批', submitApproval, [PTR_TYPE, id, approval_user_id,$rootElement.find('input[name="approval_user_name"]').val(), $rootElement.find('input[name="approval_remark"]').val(), 
				function(result) {
					layer.msg('提交评审成功');
					if (fn) fn(result);
					loadDtGrid(createQueryParameter(), false);
			}]);
	} else if (type==4 || type==5) { //4=通过，5=拒绝
		var title = (type==4)?'评审通过':'评审拒绝';
		//询问确认后提交
		askForConfirmSubmit('真的要'+title+'吗？', title, approvalAction, [type==4?2:3, PTR_TYPE, id, $rootElement.find('input[name="approval_remark"]').val(), false, function(result) {
			layer.msg(title);
			if (fn) fn(result);
			loadDtGrid(createQueryParameter(), false);
		}]);
	} else if (PTR_TYPE==2 && (type==22 || type==23)) { //22=上报进度，23=上报工时
		var opTime;
		var value = $rootElement.find('.ptr-slider-value>#ptr-slider-per').attr('data-per');
		var remark = $.trim($rootElement.find('.ptr-slider-remark>#ptr-slider-txt').val());
		if (remark.length==0) {
			layer.msg('缺少说明', {icon:5});
			return;
		}
		if (type==23) {
			opTime = $.trim($rootElement.find('#ptr-time-value').val());
			if (opTime.length==0) {
				layer.msg('缺少工时日期', {icon:5});
				return;
			}
		}
		
		var title = (type==22?'进度':'工时');
		//询问确认后提交
		askForConfirmSubmit('真的要提交'+title+'吗？', '提交'+title, submitTaskPercentOrWorkTime,[type, id, value, remark, opTime, function(result) {
			if (result!==false) {
				layer.msg('上报'+(type==22?'进度':'工时')+'成功');
				if (fn) fn(result);
				loadDtGrid(createQueryParameter(), false);
			}
		}]);
	} else if (PTR_TYPE==2 && type==10) { //中止
		var remark = $.trim($rootElement.find('.ptr-abort-remark>textarea').val());
		ptrStopAction(PTR_TYPE, id, function(result) {
			if (result!==false) {
				//layer.msg('中止任务成功');
				if (fn) fn(result);
				loadDtGrid(createQueryParameter(), false);
			}
		}, remark);
	}
}


//页面加载完毕后执行
$(document).ready(function() {
	//加载表格
	dtGridOption.loadURL = $.ebtw.currentPTRUrl;
	var grid = $.fn.DtGrid.current = $.fn.DtGrid.init(dtGridOption);
	grid.sortParameter = {columnId : 'create_time', sortType : 2}; //默认按创建时间排序；排序类型：0-不排序；1-正序；2-倒序
	loadDtGrid(createQueryParameter(), true, $.ebtw.currentPTRUrl);
	
	//注册事件-修改重要程度属性
	$(document).on('click', '.ebtw-title-content .important-level li', function(e) {
		var $This =  $(this);
		var $parent =$This.parents('.ebtw-title-content');
		var ptrId = $parent.find('.ptr-title').attr('data-ptr-id');
		var important = $(this).attr('data-important');
		var loadIndex = layer.load(2);
		changeImportantField(PTR_TYPE, ptrId, important, function(result) {
			$parent.find('.important-level').before(createModifyImportantMenuScript(important, 'ebtw-grade-tab '+dictImportantCss[important].gradeTab, 'DtGrid', true)).remove(); //替换旧元素
			layer.close(loadIndex);
		}, function(err) {
			layer.close(loadIndex);
		});
		
		//阻止事件冒泡传递
		stopPropagation(e);
	});
	
	if (PTR_TYPE==1) {
		//注册事件-点击快速新建按钮
		$('#btn_Quick_AddPTR').click(function() {
			//取消焦点
			$(this).blur();
			
			//检测是否已存在编辑行
			if ($('#dt_grid_PTR .actionbar-content').length>0) 
				return;
			
			//必须重新获取，因为对象可能由于内页切换而已经变更失效
			var grid = $.fn.DtGrid.current;
			
			//设置阻止事件传递的状态
	    	grid.option.stopEvent = 1;
	    	
	    	//最前端插入新行
			var content = laytpl($('#quick-addptr-script').html()).render({colspan:$("#dt_grid_PTR thead>tr>th").length});
			$("#dt_grid_PTR tbody").prepend(content); //$("#dt_grid_PTR tbody>tr:eq(0)");
			
			var $row = $('.actionbar-tr');
			
			//添加样式
			$row.siblings().css("backgroundColor", "transparent");
			$row.css("backgroundColor", "#FAFAFA").css("border-bottom", "1px solid #ddd");
			
	    	//关闭或提交输入区内容
	    	$('.actionbar-content-submit').click(function(e) {
	    		if ($(this).hasClass('actionbar-content-submit')) {
		    		var planName = $('#request_for_planName').val();
		    		if (!checkContentLength(PTR_TYPE, 'plan_name', planName && planName.trim()))
		    			return false;
		    		
		    		//保存
		    		var formatedTime = $.D_ALG.formatDate(new Date(), 'yyyy-mm-dd');
		    		createPlan({plan_name:planName, start_time:formatedTime+' 00:00:00', stop_time:formatedTime+' 23:59:59', status:1}, function(id) {
		    			grid.option.stopEvent=0; //打开事件传递的状态
		    			
						$(this).parents('.actionbar-tr').remove(); //删除输入区
						refreshPTRMenuBadges([1], PTR_TYPE); //刷新菜单角标(badge)
						loadDtGrid(createQueryParameter(), false); //刷新列表
		    		}, function(errType) {
		    			
		    		});
	    		}
	    	});
	    	
	    	//自动获取输入焦点
	    	$('.actionbar-content input:first').focus();
		});
	}
	
	//注册事件-“快速新建”、“申请评审”...等：[Enter回车]提交，Esc取消
	var enterSelector = ['.actionbar-content input[id="request_for_planName"]', '.actionbar-content input[name="approval_remark"]']; //Enter回车
	var cancelSelector = enterSelector.concat([/*'.actionbar-content .ptr-slider-remark>textarea[id="ptr-slider-txt"]'*/]); //Esc取消
	
	$(document)/*.on('keydown', enterSelector.join(','), function(event) { //捕获在输入框回车Enter
		var keyCode = event.keyCode;
		if (keyCode==13) { //&& event.ctrlKey
			//阻止字符响应到输入框
			if (event.preventDefault)
	        	event.preventDefault();
	        if (event.returnValue) 
	        	event.returnValue = false;
	        
			//触发点击“提交”事件
			$('.actionbar-content-submit').trigger('click');
		}
	})*/.on('keydown', function(event) { //捕获回车Enter
		var keyCode = event.keyCode;
		if (keyCode==13/* && event.ctrlKey*/) {
			//存在输入区域
			if ($(enterSelector.join(',')).length>0) {
				//阻止字符响应到输入框
				if (event.preventDefault)
		        	event.preventDefault();
		        if (event.returnValue) 
		        	event.returnValue = false;
		        
			    //判断layer弹出层是否存在
				if ($('.layui-layer-shade').length>0)
					$('.layui-layer .layui-layer-btn0').trigger('click'); //模拟点击弹出层的"确认"按钮
				else
					$('.actionbar-content-submit').trigger('click'); //触发点击列表的“提交”事件			
			}
		}	
	}).on('keydown', function(event) { //捕获Esc取消
		var keyCode = event.keyCode;
		if (keyCode==27/* && event.ctrlKey*/) {
			//判断layer弹出层是否存在
			if ($('.layui-layer-shade').length>0) {
				$('.layui-layer .layui-layer-btn1').trigger('click'); //模拟点击弹出层的"取消"按钮
				return;
			}
			
			var $relatedElements = $(cancelSelector.join(',')); 
	        if ($relatedElements.length>0) {
				//阻止字符响应到输入框
				if (event.preventDefault)
		        	event.preventDefault();
		        if (event.returnValue) 
		        	event.returnValue = false;
		        
				var grid = $.fn.DtGrid.current; //必须重新获取，因为对象可能由于内页切换而已经变更失效
				grid.option.stopEvent=0; //打开事件传递的状态
				
				$relatedElements.each(function() {
					$(this).parents('.actionbar-tr').remove(); //删除输入区
				});
	        }
		}
	});
});
