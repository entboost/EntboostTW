/* =========================================================
 * entboost-ebtw.js
 * =========================================================
 * Copyright 2016 entboost
 *
 * ========================================================= */

/**
 * 自定义常用函数
 */
;(function($) {
	//实现字符串trim功能
　　 String.prototype.trim=function(){
　　    return this.replace(/(^\s*)|(\s*$)/g, "");
　　 }
　　 String.prototype.ltrim=function(){
　　    return this.replace(/(^\s*)/g,"");
　　 }
　　 String.prototype.rtrim=function(){
　　    return this.replace(/(\s*$)/g,"");
　　 }
	
	//自适应高度
	$.fn.extend({
	    autoHeight: function(callback){
	        return this.each(function(){
	            var $this = $(this);
	            if( !$this.attr('_initAdjustHeight') ){
	                $this.attr('_initAdjustHeight', $this.outerHeight());
	            }
	            _adjustH(this, callback).on('input', function(){
	                _adjustH(this, callback);
	            });
	        });
	        /**
	         * 重置高度
	         * @param {Object} elem
	         */
	        function _adjustH(elem, callback){
	            var $obj = $(elem);
	            var oldHeight = $obj.outerHeight();
	            var diff = $obj.innerHeight()-$obj.height();
	            var $tmp = $obj.css({height: $obj.attr('_initAdjustHeight'), 'overflow-y': 'hidden'})
	                    .height(elem.scrollHeight-diff);
	            if (callback!=undefined) {
	            	callback(oldHeight, $tmp.outerHeight());
	            }
	            return $tmp;
	        }
	    }
	});
	
	//协同办公功能对象
	$.ebtw = $.extend({}, {
		/**
		 * 获取指定用户头像链接
		 * @param {string|array} targetUid 目标用户编号(支持多个，字符串类型时以逗号分隔)
         * @param {function} successHandle 成功后回调函数
         * @param {function} errorHandle 失败后回调函数
		 */
		getHeadPhoto : function(targetUid, successHandle, errorHandle) {
			if (Object.prototype.toString.call(targetUid) == '[object Array]') {
				targetUid = targetUid.join(',');
			}
			$.ebwebum.getuserinfo(1, targetUid, null, function(returnResult) {
			   if (successHandle)
				   successHandle(returnResult.user_infos);
		   }, errorHandle);			
		},
       /**
        * 上传文件
		* @param {number} fromType 来源类型：1=企业资源2=群组资源3=个人云盘11=计划附件（配合flag 使用）12=任务附件（配合flag 使用）13=报告附件（配合flag 使用）
		* @param {string} fromId 来源ID（配合from_type 使用）
		* @param {number} flag 资源标识：0=普通（文档附件）3=评论（评论附件）
		* @param {string} name 资源名称
		* @param {string} description描述
        * @param {string} fileElementId 浏览文件的file控件id
        * @param {boolean} cloneElementToPostion (可选) 是否复制一个新的file对象放置在原file对象的位置，默认true
        * @param {function} successHandle 成功后回调函数
        * @param {function} errorHandle 失败后回调函数
        * @param {function} startHandle 上传开始的回调函数
        */
		sendfile : function(fromType, fromId, flag, name, description, fileElementId, cloneElementToPostion, successHandle, errorHandle, startHandle) {
		   //请求上传文件
			$.ebwebum.addresource(5, fromType, fromId, flag, name, description, 0, function(result) {
			   var resourceId = result.resource_id;
			   var cmServerAddr = result.cm_server;
			   var ebwebcm = $.eb.getCmAccessor(cmServerAddr);//.replace(/:\d+/, ':19012'));//, ebSid
			   
			   //执行上传文件
			   var jqxhr = ebwebcm.upload(resourceId, fileElementId, cloneElementToPostion, function(result){
				   if (successHandle)
					   successHandle(result, resourceId);
			   }, errorHandle);
			   
			   if (startHandle)
				   startHandle(jqxhr);
		   }, errorHandle);
		},
		/**
		 * 查询文件列表或数量
		 * @param {number} fromType 来源类型：1=企业资源2=群组资源3=个人云盘11=计划附件（配合flag 使用）12=任务附件（配合flag 使用）13=报告附件（配合flag 使用）
		 * @param {string} fromId 来源ID（配合from_type 使用）
		 * @param {number} flag 资源标识：0=普通（文档附件）3=评论（评论附件）；填入null则不区分flag
		 * @param {number} get_summary 获取摘要信息(例如数量)：1=获取摘要信息，其它=获取记录列表
		 * @param {number} offset 偏移量(从第几条记录开始)，用于分页；默认值-1(加载所有数据)
		 * @param {number} limit 限制返回列表的最大数量，用于分页；默认值30
		 * @param {function} successHandle 成功后回调函数
         * @param {function} errorHandle 失败后回调函数
		 */
		listfile : function(fromType, fromId, flag, get_summary, offset, limit, successHandle, errorHandle) {
			$.ebwebum.loadresource(5, fromType, fromId, flag, get_summary, offset, limit, successHandle, errorHandle);
		},
		
		/**
		 * 查询文件列表或数量(支持多个fromId、fromType、flag、type作为查询条件)
		 * @param {array} conditions 查询条件组合数组，例如：[{fromType:3, fromId:'12345', flag:-1}, ...]
		 * 		fromType 来源类型：1=企业资源2=群组资源3=个人云盘11=计划附件（配合flag 使用）12=任务附件（配合flag 使用）13=报告附件（配合flag 使用）
		 * 		fromId 来源ID（配合from_type 使用）
		 * 		flag 资源标识： -1=全部，0=普通（文档附件），3=评论（评论附件）；填入null则不区分flag(相当于-1)
		 * @param {number} get_summary 获取摘要信息(例如数量)：1=获取摘要信息，其它=获取记录列表
		 * @param {function} successHandle 成功后回调函数
         * @param {function} errorHandle 失败后回调函数
		 */		
		listfiles : function(conditions, get_summary, successHandle, errorHandle) {
			var newConditions = new Array();
			for (var i=0; i<conditions.length; i++) {
				var newCondition = $.extend({}, conditions[i]);
				newCondition.type = 5;
				newConditions.push(newCondition);
			}
			$.ebwebum.loadresources(newConditions, get_summary, successHandle, errorHandle);
		},
		/**
		 * 获取资源指定文件，并指定共享给某个用户
		 * @param {string} resourceId 资源编号
		 * @param {string} toShareUid 共享资源给对方的用户编号(user_id)，toShareUid=user_id 用于共享给自己
		 * @param {function} successHandle 成功后回调函数
         * @param {function} errorHandle 失败后回调函数
		 */
		getfile : function(resourceId, toShareUid, successHandle, errorHandle) {
			$.ebwebum.getresource(resourceId, toShareUid, 1, successHandle, errorHandle);
		},
		/**
		 * 生成共享访问文件链接
		 * @param url文件链接
		 */
		createShareUrl: function(url) {
			return $.ebwebum.createShareUrl(url);
		},
		/**
		 * 删除文件
		 * @param {string} resourceId 资源编号
		 * @param {function} successHandle 成功后回调函数
         * @param {function} errorHandle 失败后回调函数
		 */
		deletefile : function(resourceId, successHandle, errorHandle) {
			$.ebwebum.deleteresource(resourceId, successHandle, errorHandle);
		},
		
	});
	
})(jQuery);


/**
 * 页面加载完成后，自动执行的任务
 * @param $
 */
;(function($) {
	$(document).ready(function() {
		//下拉框美化
		$("select.normal").select2({
		  width:'110px',
		  theme: "default",
		  minimumResultsForSearch: Infinity
		});
		
    	//返回顶部按钮事件
    	$('#btn_gotop').click(function() {
    		scroll(0,0);
    	});
        $(window).scroll(function(){
            if ($(window).scrollTop()>50)
            	$("#btn_gotop").css("display","block");
            else
            	$("#btn_gotop").css("display","none");
        });
        
        //注册事件-移除grid操作行
        $("#gridList").hover(null, function () {
        	if ($.fn.DtGrid.current) {
        		if ($.fn.DtGrid.current.option.stopEvent==1) return; //判断阻止事件传递的状态
        	}
        	$(".actionbar-tr").remove();
        });

	});
	
})(jQuery);

/**
 * param 将要转为URL参数字符串的对象
 * key URL参数字符串的前缀
 * encode true/false 是否进行URL编码，默认为true
 * return URL参数字符串
 */
function ebtwUrlEncode (param, key, encode) {
	if(param==null) return '';
	
	var paramStr = '';
	var t = typeof (param);
	if (t == 'string' || t == 'number' || t == 'boolean') {
		paramStr += '&' + key + '=' + ((encode==null||encode)?encodeURIComponent(param):param);
	} else {
		for (var i in param) {
			var k = (key==null)?i:key + (param instanceof Array ?'[' + i + ']':'.' + i); 
			paramStr += ebtwUrlEncode(param[i], k, encode);  
		}
	}
  
	return paramStr;
}

/**
 * 调用ajax执行远程访问
 * @param url 远程访问路径
 * @param {object} parameters (可选) 访问远程服务提交参数
 * @param successHandle 执行成功后回调函数
 * @param errorHandle 执行失败后回调函数
 * @param async 是否异步执行，默认true
 * @param type 提交方法(get、post)，默认post
 */
function callAjax(url, parameter, successHandle, errorHandle, async, type) {
	if (typeof parameter == 'object') {
		parameter = ebtwUrlEncode(parameter);
		if (parameter.charAt(0)=='&')
			parameter = parameter.substr(1);
	}
	
	$.ajax({
		cache: false,
		timeout: ajaxTimeout,
		type:type||'POST',
		async: async===false?false:true,
		url: url,
		data: parameter,
		//dataType: 'text',
		contentType: "application/x-www-form-urlencoded; charset=utf-8",
		beforeSend: function(xhr) {xhr.setRequestHeader("__REQUEST_TYPE", "AJAX_REQUEST");},
		success:successHandle,
		error:errorHandle
	});
}

/**
 * ajax上传文件
 * @param {string} user_id 用户编号
 * @param {number|string} logon_type 登录类型
 * @param {string} acm_key 资源访问令牌
 * @param {string} resource_id 资源编号
 * @param {string} url 访问路径
 * @param {boolean} secureuri 跨域URI
 * @param {string} fileElementId 上传文件控件id
 * @param {boolean} cloneElementToPostion (可选) 是否复制一个新的file对象放置在原file对象的位置，默认true
 * @param {function} success_handle (可选) 上传成功后回调函数
 * @param {function} error_handle (可选) 上传失败后回调函数
 */
function ajaxFileUpload(user_id, logon_type, acm_key, resource_id, url, secureuri, fileElementId, cloneElementToPostion, success_handle, error_handle) {
    var parameter = {
    	user_id: user_id,
    	logon_type: logon_type,
    	acm_key: acm_key,
    	resource_id: resource_id
    };
    
    var i=0;
    var jqxhr = $.ajaxFU.ajaxFileUpload({
        url: url,
        secureuri: secureuri,
        fileElementId: fileElementId,
        cloneElementToPostion: (cloneElementToPostion===false)?false:true,
        dataType: 'json',
        data: parameter,
        cache: false,
        timeout: ajaxUploadTimeout,
        type: 'POST',
        contentType: "application/x-www-form-urlencoded; charset=utf-8", //对于上传文件，实际上并不生效
        success: function (data, status) {
            if (success_handle)
                success_handle(data, status);
        },
        error: function (s, xhr, status, e/*data, status, e*/) {
        	//只执行一次回调函数
        	i++;
        	if (i>1) {
        		logjs_info('miss return one ajaxFileUpload error, i = '+i);
        		return;
        	}
        	
            if (error_handle)
                error_handle(s, xhr, status, e);
        }
    });
    
    return jqxhr;
}

//阻止事件传递
function stopPropagation(e) {
	var e = e || window.event;
	if (e && e.stopPropagation)
		e.stopPropagation();
	else
		e.cancelBubble = true;
}

//获取当前服务端URL
function getServerUrl() {
    var url = $("#ebtw_server_url").attr("href");
    var path = url.toLowerCase().replace(/css\/ebtw.css(\?v=)*\d*$/g,'');
    return path;
}

//html转义
function html_encode(str) {
  var s = "";
  if (str.length == 0) return ""; 
  s = str.replace(/&/g, "&amp;");
  s = s.replace(/</g, "&lt;");
  s = s.replace(/>/g, "&gt;");
  s = s.replace(/ /g, "&nbsp;");
  s = s.replace(/\'/g, "&#39;");
  s = s.replace(/\"/g, "&quot;");
  s = s.replace(/\n/g, "<br>");
  return s;
}

//html转义还原
function html_decode(str) {
  var s = "";
  if (str.length == 0) return "";
  s = str.replace(/&amp;/g, "&");
  s = s.replace(/&lt;/g, "<");
  s = s.replace(/&gt;/g, ">");
  s = s.replace(/&nbsp;/g, " ");
  s = s.replace(/&#39;/g, "\'");
  s = s.replace(/&quot;/g, "\"");
  s = s.replace(/<br>/g, "\n");
  return s;
}

/**替换指定传入参数的值
 * @param {string} url 原URL
 * @param {string} paramName 参数名
 * @param {string} replaceWith 新值
 * @return {string} 被替换后的URL
 */
function replaceUrlParamVal(oUrl, paramName,replaceWith) {
    var re=eval('/('+ paramName+'[ ]*=)([^&]*)/gi');
    return oUrl.replace(re, paramName+'='+replaceWith);
}

/**
 * 刷新当前页面
 * @param {string} excludeParameters 除外的参数名，将去除这些参数再刷新页面 (如果多个可用逗号分隔)
 */
function refreshPage(excludeParameters) {
	var originUrl = location.href.replace(/#*$/ig, '');
	var url = originUrl;
	
	//过滤指定参数名
	if (typeof excludeParameters ==='string') {
		var eParams = excludeParameters.split(',');
		for (var i=0;i <eParams.length; i++) {
			var re =new RegExp("([&]?"+eParams[i]+"=[^&]*)");
			url = url.replace(re, '');
		}
	}
	
	if (url.length!=originUrl.length)
		location.href = url;
	else
		location.replace(url);
}

/**
 * 格式化分钟(分钟转换为小时)
 * @param {string|number} minutes 分钟
 * @returns {number} 小时；保留小数点后两位
 */
function formatMinutesToHours(minutes) {
	if (typeof minutes =='string')
		minutes = parseInt(minutes);
	return Math.round((parseInt(minutes)/60)*100)/100;
}

//会话失效重新验证处理
function reInitApp() {
	if (typeof globalSubId != 'undefined') {
		var url = 'eb-open-subid://'+globalSubId+',0';
		window.location.href = url;
	} else {
		alert('globalSubId is not exist');
	}
}

/**
 * 询问确认执行指定的函数
 * @param {string} content 询问对话框的内容 
 * @param {string} title 询问对话框的标题
 * @param {function} callback 等待执行的函数
 * @param {array} parameters 等待执行函数所需要的参数
 */
function askForConfirmSubmit(content, title, callback, parameters) {
	layer.confirm(content, {title:title?title:undefined, success:function(layero, index){
		//logjs_info(layero);
	}}, function(index) {
		layer.close(index);
		
		if (typeof callback=='function')
			callback.apply(null, parameters);
	});
}

//检查字段长度规则
//minSizeOfByte、minSizeOfChinese不设置时，相当于minSizeOfByte=1、minSizeOfChinese=1
Rules_of_check_content_length = {
	0: {
		discuss: {columnName:"评论内容", maxSizeOfByte: 2048/*, maxSizeOfChar:680*/, minSizeOfByte:0, minSizeOfChar:0},
	},
	1: {
		plan_name: {columnName:"计划事项", maxSizeOfByte: 128/*, maxSizeOfChar:40*/},
		remark: {columnName:"计划内容", maxSizeOfByte: 2048, maxSizeOfChar:680, minSizeOfByte:0, minSizeOfChar:0},
	},
	2: {
		task_name: {columnName:"任务标题", maxSizeOfByte: 128/*, maxSizeOfChar:40*/},
		remark: {columnName:"任务内容", maxSizeOfByte: 2048/*, maxSizeOfChar:680*/, minSizeOfByte:0, minSizeOfChar:0},
	},
	3: {
		completed_work: {columnName:"已完成工作", maxSizeOfByte: 128/*, maxSizeOfChar:40*/},
		uncompleted_work: {columnName:"未完成工作", maxSizeOfByte: 2048/*, maxSizeOfChar:680*/, minSizeOfByte:0, minSizeOfChar:0},
	}
};

/**
 * 检查内容长度(是否超长、太短)
 * @param {string} ptrType 文档类型：0=其它，1=计划，2=任务，3=日报
 * @param {string} fieldName 字段名
 * @param {string} content 内容
 * @param {string} (可选) replaceColumnName 替换的字段名，不填入时使用
 * @param {string} (可选) invalidTips 不符合规则时的提示语，不填入时使用默认提示
 */
function checkContentLength(ptrType, fieldName, content, replaceColumnName, invalidTips) {
	var rules = Rules_of_check_content_length[ptrType];
	if (!rules) {
		layer.msg('无法检查长度，找不到对应文档类型 '+ptrType+' 的规则', {icon: 2});
		return false;
	}
	
	var rule = rules[fieldName];
	if (!rules) {
		layer.msg('无法检查长度，找不到对应字段 "'+fieldName+'" 的规则', {icon: 2});
		return false;
	}
	
	rule = $.extend({}, rule) //复制一份;
	var columnName = replaceColumnName?replaceColumnName:rule.columnName;
	
	//检查字节最大长度
	if (typeof rule.maxSizeOfByte!='undefined' && getStringBytesLength(content) > rule.maxSizeOfByte) {
		layer.msg(invalidTips?invalidTips:('"'+columnName+'" 字段内容超长'), {icon: 5});
		return false;
	}
	//检查字符数最大长度
	if (typeof rule.maxSizeOfChar!='undefined' && typeof content!='undefined' && content.length > rule.maxSizeOfChar) {
		layer.msg(invalidTips?invalidTips:('"'+columnName+'" 字段内容超长'), {icon: 5});
		return false;
	}
	
	//默认规则不允许字符长度等于0
	if (typeof rule.minSizeOfByte=='undefined')
		rule.minSizeOfByte = 1;
	if (typeof rule.minSizeOfChar=='undefined')
		rule.minSizeOfChar = 1;
	
	//检查字节最小长度
	if (rule.minSizeOfByte>0 && getStringBytesLength(content) < rule.minSizeOfByte) {
		layer.msg(invalidTips?invalidTips:('"'+columnName+'" 字段内容太短'), {icon: 5});
		return false;
	}
	//检查字符数最小长度
	if (rule.minSizeOfChar>0 && typeof content!='undefined' && content.length < rule.minSizeOfChar) {
		layer.msg(invalidTips?invalidTips:('"'+columnName+'" 字段内容太短'), {icon: 5});
		return false;
	}	
	
	return true;
}

/**
 * 自定义layer prompt对话框
 * @param {string} title 对话框标题 
 * @param {object} parameter 参数
 * @param {boolean} enterSubmit 是否Enter回车提交
 * @param {boolean} withCtrl 是否Ctrl+Enter回车提交(当enterSubmit=true才有效)
 * @param {function} submitCallback 提交时回调函数
 */
function customPrompt(title, parameter, enterSubmit, withCtrl, submitCallback) {
	layer.open({type: 1,
		title: title,
		area: ['260px', '160px'], //宽高
		content: laytpl($('#custom-layer-prompt-script').html()).render(parameter),
		btn: ['确定', '取消'],
		yes: function(index, layero) {
			if (submitCallback) {
				if (submitCallback($(layero).find('.layui-layer-content input.custom-layer-input').val(), index, layero)!==false)
					layer.close(index);
			} else
				layer.close(index);
		},
	  	success: function(layero, index) {
	  		var textInputSelector = 'input.custom-layer-input';
	  		//设置输入框为焦点
	  		$(layero).find(textInputSelector).focus();
	  		
	  		if (enterSubmit) {
			//绑定回车符提交
				registerEnterKeyToWork($(layero).find('.layui-layer-content'), withCtrl, textInputSelector, function($textInputElement) {
					$textInputElement.parents('.layui-layer').find('.layui-layer-btn0').trigger('click');
				});
	  		}
		}
	});
}

/**
 * 注册事件-Enter 或 [Ctrl+Enter回车]触发指定动作(通过在回调函数内执行)
 * @param $container {object} jquery上级容器(JQuery对象)；如填入null或undefined，则使用全局容器$()
 * @param usingCtrlKey {boolean} 是否检测Ctrl键
 * @param textInputSelector {string} 输入框选择器
 * @param callback {function} 回调函数
 */
function registerEnterKeyToWork($container, usingCtrlKey, textInputSelector, callback) {
	var $textInputElement;
	if ($container)
		$textInputElement = $container.find(textInputSelector);
	else 
		$textInputElement = $(textInputSelector);
	
	$textInputElement.keydown(function(event) {
		if (event.keyCode==13) {
			var doit = false;
			if (usingCtrlKey==true && event.ctrlKey)
				doit = true;
			if (usingCtrlKey==false)
				doit =true;
			
			if (doit) {
		        if (event.preventDefault)
		        	event.preventDefault();
		        if (event.returnValue) 
		        	event.returnValue = false;
		        
				if (typeof callback=='function')
					callback($textInputElement);
			}
		}
	});
}

/**
 * 绑定点击伸缩按钮事件
 * @param {object} $element (必填) 伸缩按钮JQuery对象
 * @param {object} effectedTarget (可选) 显示/隐藏的目标容器
 * @param {function} fetchFn (可选) 回调获取目标容器的函数
 * @param {function} excuteFn (可选) 执行外部操作的回调函数
 * @return {object} $element本身对象
 */
function bindStretchClick($element, effectedTarget, fetchFn, excuteFn) {
	$element.unbind('click').bind('click', {effectedTarget:effectedTarget||fetchFn, excuteFn:excuteFn}, function(e) {
		$.proxy(function(e) {
			var status = $(this).attr('data-status');
			var sp = $(this).find('span.glyphicon');
			var values = [];
			if (status=='down')
				values = ['block', 'up', 'glyphicon-chevron-down', 'glyphicon-chevron-up'];
			else
				values = ['none', 'down', 'glyphicon-chevron-up', 'glyphicon-chevron-down'];
			
			var et = e.data.effectedTarget;
			//执行获取目标容器的回调函数
			if (typeof et == 'function')
				et = et($(this));
			
			et.css('display', values[0]);
			$(this).attr('data-status', values[1]);
			sp.removeClass(values[2]);
			sp.addClass(values[3]);
			
			//执行外部操作回调函数
			if (e.data.excuteFn)
				e.data.excuteFn($(this), values);
			
			//触发窗口缩放事件
			$(window).trigger('resize');
		}, $(this).children('.glyphicon-chevron-down, .glyphicon-chevron-up').length>0?this:$(this).children()[0])(e);
		
		//阻止事件传递
		stopPropagation(e);
	});
	
	return $element;
}

/**
 * 远程服务返回数据预处理函数
 * @param {string} type 数据封装格式，type='original'或type='pager'
 * @param {string} data 远程服务端返回的数据(通常是json格式，也可能是true和false)
 * @param {boolean} returnAll (可选) 如不发生错误，返回整个查询结果
 * @return {object, boolean} 数据对象或布尔类型
 */
function didLoadedDataPreprocess(type, datas, returnAll) {
	if (typeof datas == 'boolean')
		return datas;
	
	var obj = datas;
//	if (typeof obj =='object')
//		alertObjectToJson(obj);
	if (typeof datas == 'string') {
		datas = $.trim(datas);
//		if (datas.length==0) {
//			if (returnAll) {
//				datas = '[]';
//			} else if (type=="original") {
//				datas = '{"results":""}';
//			} else if (type=="pager") {
//				datas = '{"pager":""}';
//			}
//		}
		
		var result = datas.match(/^[\[\{]+[\s\S]*[\]\}]+$/ig);
		if (result==undefined || (result.length==0 && returnAll!=true)) {
			return false;
		}
		obj = $.parseJSON(result[0]);
	}
	
	if (obj.code==undefined || obj.code!=0) {
		if (obj.code==11) { //会话失效
//			if (typeof globalSubId != 'undefined') {
//				var url = 'eb-open-subid://'+globalSubId+',0';
//				window.location.href = url;
//			} else {
//				alert('globalSubId is not exist');
//			}
			reInitApp();
			
			if (type=="pager")
				return {isSuccess: true}; 
		}
		return false;
	}
	
	if (returnAll)
		return obj;
	
	if (type=="original") {
		return obj.results;
	} else if (type=="pager") {
		return obj.pager;
	}
}

/**
 * 远程加载列表数据
 * @param url远程访问路径
 * @param {object} parameters (可选) 访问远程服务提交参数
 * @param successHandle 成功时回调函数，格式：fn(results)，参数results是数组
 * @param errorHandle 失败时回调函数，格式：fn(result)，参数result = 'business'[逻辑错误]或'system'[系统错误]
 * @returns
 */
function loadResults(url, parameter, successHandle, errorHandle) {
	callAjax(url, parameter, function(datas) {
		var result = didLoadedDataPreprocess('original', datas, true);
		if (typeof result != 'boolean') {
			if (successHandle!=undefined)
				successHandle(result.results);
		} else if (errorHandle!=undefined) {
			errorHandle('business');
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle!=undefined) {
			errorHandle('system', textStatus);
		}
	});
}

/**
 * 从远程服务端加载字典表(同步执行)
 * @param url 远程访问路径
 * @param fn 回调函数，格式：fn(codeTable, elements)； 参数codeTable兼容gtGrid代码对照表对象，参数elements保存节点对象的数组
 * @return 是否成功
 */
function loadCodeTable(url, fn) {
	var bReturn = false;
	callAjax(url, null, function(datas) {
		var results = didLoadedDataPreprocess('original', datas);
		if (typeof results != 'boolean') {
			//转换为dtGrid兼容格式
			var codeTable = {};
			if (Object.prototype.toString.call(results) === '[object Array]') {
				for (var i=0; i<results.length; i++) {
					var element = results[i];
					codeTable[element.class_id] = element.class_name;
				}
			} else {
				results = [];
			}
			
			if (fn) fn(codeTable, results);
			bReturn = true;
		}
	}, function(XMLHttpRequest, textStatus, errorThrown){}, false, 'get');
	return bReturn;
}

/**
 * 加载记录数量
 * @param url 远程服务访问地址
 * @param {object} parameters (可选) 访问远程服务提交参数
 * @param successHandle 成功时回调函数，格式：fn(count)
 * @param errorHandle 失败时回调函数，格式：fn(result)
 */
function loadRecordCount(url, parameter, successHandle, errorHandle) {
	callAjax(url, parameter, function(datas) {
		var result = didLoadedDataPreprocess('original', datas, true);
		if (typeof result != 'boolean') {
			if (successHandle!=undefined)
				successHandle(result.count);
		} else if (errorHandle!=undefined) {
			errorHandle('business');
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle!=undefined) {
			errorHandle('system', textStatus);
		}
	});
}

/**
 * 获取一条暂存的自定义参数
 * @param {string} tempKey 临时数据主键
 * @param {function} successHandle 成功时回调函数，格式：fn(entity)
 * @param {function} errorHandle 失败时回调函数，格式：fn(result)
 */
function loadTempCustomParameter(tempKey, successHandle, errorHandle) {
	callAjax(getServerUrl()+'tempdata/get_one.php', {temp_key:tempKey}, function(datas) {
		var result = didLoadedDataPreprocess('original', datas, true);
		if (typeof result != 'boolean') {
			if (successHandle!=undefined) {
				if (result.results && result.results.length>0) {
					successHandle(result.results[0]);
					//successHandle(json_parse(result.results[0].str_value));
				} else 
					successHandle();
			}
		} else if (errorHandle!=undefined) {
			errorHandle('business');
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle!=undefined) {
			errorHandle('system', textStatus);
		}
	});	
}

/**
 * 加载dtGrid表格
 * @param {object} parameters (可选) 访问远程服务提交参数，如不填使用默认值
 * @param {boolean} reset (可选) 是否重置分页属性
 * @param {string} loadURL (可选) 访问远程服务URL，如不填使用默认值
 * @param {object} grid (可选) 指定dtGrid表格对象，如不填使用默认值
 */
function loadDtGrid(parameters, reset, loadURL, grid) {
	if (grid = grid||$.fn.DtGrid.current) {
		if (loadURL)
			grid.option.loadURL = loadURL;
		if (parameters)
			grid.parameters = parameters;
		if (reset!==false)
			grid.resetPager();
		
		grid.load();
	}
}

/**
 * 执行智能查询
 * @param dtGridCallback (可填null) dtGrid页面查询回调函数
 * @param loadEmbedPageCallback (可选) 嵌入式页面查询回调函数；如不填或填空或回调函数返回false，则执行默认查询；回调函数参数(url, containerSelector)，url=访问链接，containerSelector=目标容器选择器
 */
function executeQuery(dtGridCallback, loadEmbedPageCallback) {
	var $urlElement = $('#current_url');
	if ($urlElement.length>0) {
		if (typeof loadEmbedPageCallback==='function') {
			var isExcute = loadEmbedPageCallback($urlElement.val(), $urlElement.attr("data-container"));
			if (isExcute==false)
				loadEmbedPage($urlElement.attr("data-container"), $urlElement.val(), createQueryParameter());
		} else {
			loadEmbedPage($urlElement.attr("data-container"), $urlElement.val(), createQueryParameter());
		}
	} else {
		if (typeof dtGridCallback==='function')
			dtGridCallback();
	}
}

//注册左侧菜单事件
function registerLeftMenu(ptrType, refreshMenuBadgesCallback) {
	//定义函数：重建表格对象
	function rebuildDtGrid(sortColumnId, sortType) {
		var dtGridOption = $.fn.DtGrid.current.option;
		dtGridOption.columns = createDtGridColumns();
		var grid = $.fn.DtGrid.current = $.fn.DtGrid.init(dtGridOption);
		grid.sortParameter = {columnId : sortColumnId, sortType : sortType}; //默认按创建时间排序；排序类型：0-不排序；1-正序；2-倒序
	}
	
	//注册点击事件
	var menuSelector = '.query_type, .ptr_class_item, .recycle_bin';
	$(document).on('click', menuSelector, function() {
//		if ($(this).hasClass('active'))
//			return;
		
		if (!$(this).hasClass('active')) {
			//清空查询条件输入框/选择框内容
	    	if (typeof resetSearchContent=='function')
	    		resetSearchContent();
	    	if (typeof resetOtherSearchConditions=='function')
	    		resetOtherSearchConditions();
		}
		
    	//重置菜单激活状态
		$(menuSelector).removeClass('active');
		$(this).addClass('active');

		var $currentLeftMenu = $('#current_left_menu');
		
		var queryType = 1;
		
		var sort0 = 'create_time';
		var sort1 = 'su_create_time';
		if (ptrType==2) {
			sort0 = 'stop_time';
		}
		
		if ($(this).hasClass('query_type')) { //各种计划、任务、报告
			$('#recycle_bin_input').val("");
			$('#ptr_class_input').val("");
			queryType = $(this).attr('type');
			$currentLeftMenu.val('1_'+queryType);
			
			//删除标记
			$('#is_deleted').val(0);
			
			if (ptrType==1 || ptrType==2) {
				if (queryType==2 && ptrType==1) { //评审计划
					rebuildDtGrid(sort1, 2);
				} else {//其它计划
					rebuildDtGrid(sort0, 2);
				}
			} else if (ptrType==3) {
				var $urlElement = $('#current_url');
				var url = $urlElement.val();
				$urlElement.val(replaceUrlParamVal(url, 'request_query_type', queryType));
			}
		} else if ($(this).hasClass('recycle_bin')) { //回收站
			$('#recycle_bin_input').val(1);
			$('#ptr_class_input').val("");
			$currentLeftMenu.val('3_'+queryType);
			
			//删除标记
			$('#is_deleted').val(1);
			
			if (ptrType==1 || ptrType==2) {
				rebuildDtGrid(sort0, 2);
			}
		} else { //分类
			$('#recycle_bin_input').val("");
			$('#ptr_class_input').val($(this).attr('data-ptr-class-id')||"");
			$currentLeftMenu.val('2_'+queryType);
			
			//删除标记
			$('#is_deleted').val(0);
			
			if (ptrType==1 || ptrType==2) {
				rebuildDtGrid(sort0, 2);
			}
		}
		
		//切换状态查询条件备选项
		if (ptrType==1 && typeof switchStatusSelector == 'function')
			switchStatusSelector(queryType);
		
		//触发取消勾选日期范围控件
		$('.date-period-ctrl').trigger('click', [false, false]);
		
		//执行查询
		executeQuery(function() {
			if (typeof refreshMenuBadgesCallback =='function')
				refreshMenuBadgesCallback();
			
			loadDtGrid(createQueryParameter(), true, replaceUrlParamVal($.fn.DtGrid.current.option.loadURL, 'request_query_type', queryType));
		}, function(){
			if (typeof refreshMenuBadgesCallback =='function')
				refreshMenuBadgesCallback();
			
			return false;
		});
	});
}

/**
 * 初始化日期时间选择器(默认设置)
 * @param {string} selector 输入选择器字符串(支持多个-用逗号分隔)，例如：'#a1, #a2' 或 '.c1, .c2'等
 * @param {string} format 格式化字符串
 * @param {number} startView (选填)
 * @param {number} minView (选填)
 * @param {number} maxView (选填)
 * @param {string} linkField (选填)
 * @param {string} linkFormat (选填)
 */
function createDefaultDatetimePicker(selector, format, startView, minView, maxView, linkField, linkFormat) {
	createDatetimePicker(selector, format, startView, minView, maxView, linkField, linkFormat, 1, 1, 1, 1);
}

/**
 * 初始化日期时间选择器(默认设置)
 * @param {string} selector 输入选择器字符串(支持多个-用逗号分隔)，例如：'#a1, #a2' 或 '.c1, .c2'等
 * @param {string} format 格式化字符串
 * @param {number} startView (选填)
 * @param {number} minView (选填)
 * @param {number} maxView (选填)
 * @param {string} linkField (选填)
 * @param {string} linkFormat (选填)
 */
function createNoResetBtnDatetimePicker(selector, format, startView, minView, maxView, linkField, linkFormat) {
	createDatetimePicker(selector, format, startView, minView, maxView, linkField, linkFormat, 0, 1, 1, 1);
}

/**
 * 初始化日期时间选择器
 * @param {string} selector 输入选择器字符串(支持多个-用逗号分隔)，例如：'#a1, #a2' 或 '.c1, .c2'等
 * @param {string} format 格式化字符串
 * @param {number} startView
 * @param {number} minView
 * @param {number} maxView
 * @param {string} linkField
 * @param {string} linkFormat
 * @param {string} resetBtn
 * @param {string} todayBtn
 * @param {string} todayHighlight
 * @param {string} autoclose
 */
function createDatetimePicker(selector, format, startView, minView, maxView, linkField, linkFormat, resetBtn, todayBtn, todayHighlight, autoclose) {
	var params = {
		bootcssVer: 3,
		language: 'zh-CN',
	    format: format,
	    autoclose: autoclose,
	    todayHighlight: todayHighlight,
	    todayBtn:  todayBtn,
	    resetBtn:  resetBtn,
	    startView: startView,
	    minView: minView,
	    maxView: maxView,
	    minuteStep: 10,
	    linkField: linkField,
	    linkFormat: linkFormat,
	    //pickerPosition: 'input'
	};
	
	$(selector).datetimepicker(params);
}

//设置月、周日期范围
function setPeriodOfMW(type, year, mw, dateAry, startDate, endDate, callback) {
	if (dateAry.length==2) {
		var date0 = dateAry[0]=='object'?dateAry[0]:new Date(dateAry[0]);
		var date1 = dateAry[1]=='object'?dateAry[1]:new Date(dateAry[1]);
		
		var startYear = date0.getFullYear();
		var startDate_short = $.D_ALG.formatDate(date0, 'mm-dd');
		$('.input-year').val(startYear).attr('oldYear', startYear);
		
		if (callback) {
			if (type=='month') {
				callback();
			} else {
				callback(year, startYear, startDate_short);
			}
		}
		
		var reversedFormat = 'mm-dd yyyy';
		if (startDate==undefined) {
			startDate = $.D_ALG.formatDate(date0, reversedFormat);
		} else if (typeof startDate == 'object'){
			startDate = $.D_ALG.formatDate(startDate, reversedFormat);
		}
		$('.input-date-start').val(startDate).attr('oldDate', startDate);
		
		if (endDate==undefined) {
			endDate = $.D_ALG.formatDate(date1, reversedFormat);
		} else if (typeof endDate == 'object') {
			endDate = $.D_ALG.formatDate(endDate, reversedFormat);
		}
		$('.input-date-end').val(endDate, reversedFormat).attr('oldDate', endDate);
		
		//执行查询
		executeQuery(function(){
			loadDtGrid(createQueryParameter());
		});
	}
}
/**
 * 设置月日期范围
 * @param {number|string} year (必填) 年
 * @param {number|string} month (必填) 某年第几月，从0开始
 * @param {array-[object]} dateAry (必填) 日期范围
 * @param {object|string} startDate (可选) 开始日期，如不填将使用dateAry[0]的值
 * @param {object|string} endDate (可选) 结束日期，如不填将使用dateAry[1]的值
 */
function setPeriodOfMonth(year, month, dateAry, startDate, endDate) {
	setPeriodOfMW('month', year, month, dateAry, startDate, endDate, function(){
		$('.input-month').val(month+1).trigger('change');
	});
}
/**
 * 设置周日期范围
 * @param {number|string} year (必填) 年
 * @param {number|string} week (必填) 某年第几周，从0开始
 * @param {array-[string]} dayAry (必填) 日期范围
 * @param {object|string} startDate (可选) 开始日期，如不填将使用dayAry[0]的值
 * @param {object|string} endDate (可选) 结束日期，如不填将使用dateAry[1]的值
 */
function setPeriodOfWeek(year, week, dayAry, startDate, endDate) {
	setPeriodOfMW('week', year, week, dayAry, startDate, endDate, function(year, startYear, startDate_short){
		var showedWeek = week+1;
		if (year>startYear) {
			showedWeek = 53;
		} else if (year<startYear) {
			if (startDate_short=='01-01')
				showedWeek = 1;
			else 
				showedWeek = 2;
		}
		$('.input-week').val(showedWeek).trigger('change');
	});
}

//注册上下一周期按钮事件
function registerPeriodSelectAction(prevFunc, nextFunc) {
	//注册事件：点击上一周期
	$('.period-prev').unbind('click').bind('click', {added:-1}, function(e) {
		$('.date-period-ctrl').trigger('click', [true, false]); //触发勾选日期范围控件
		if (prevFunc) $.proxy(prevFunc(e), this);
	});
	//注册事件：点击下一周期
	$('.period-next').unbind('click').bind('click', {added:1}, function(e) {
		$('.date-period-ctrl').trigger('click', [true, false]); //触发勾选日期范围控件
		if (nextFunc) $.proxy(nextFunc(e), this);
	});
}

/**
 * 实现月日期范围工具栏
 */
function implementPeriodOfMonthSelector() {
	var now = new Date();
	var year = now.getFullYear();
 	var month = now.getMonth();
	var dateAry = [new Date(year, month, 1), new Date(year, month, $.D_ALG.getMonthDays(year, month))];
	setPeriodOfMonth(year, month, dateAry);
	
	function func(e) {
		var added = e.data.added
		var year = parseInt($('.input-year').val());
		var month = parseInt($('.input-month').val())-1;
		
		month += added;
		if (month<0||month>11)
			year+=added;
		month = (month+12)%12;
		
		var dateAry = [new Date(year, month, 1), new Date(year, month, $.D_ALG.getMonthDays(year, month))];
		setPeriodOfMonth(year, month, dateAry);
	}
	registerPeriodSelectAction(func, func);	
}

/**
 * 实现周日期范围工具栏
 */
function implementPeriodOfWeekSelector() {
	var now = new Date();//2007-1-1 2006-12-31 2006-12-24 '2016-4-3'
	var year = now.getFullYear();
 	var week = $.D_ALG.theWeekNumber(now);
	var dayAry = $.D_ALG.dateRange(year, week);
	setPeriodOfWeek(year, week, dayAry);
	
	function func(e) {
		var added = e.data.added;
		var year = parseInt($('.input-year').val());
		var week = parseInt($('.input-week').val())-1;
		var dayAry = $.D_ALG.dateRange(year, week+added);
		setPeriodOfWeek(year, week+added, dayAry);
	}
	registerPeriodSelectAction(func, func);
}

/**
 * 实现每天日期工具栏
 */
function implementPeriodOfDailySelector() {
	var format = 'yyyy 年 mm 月 dd 日';
    var $element = $('#datetimepicker_0');
    var now = $.D_ALG.formatDate(new Date(), format);
    $element.val(now).attr('oldDate', now);
    
    //执行查询
	executeQuery(function(){
		loadDtGrid(createQueryParameter());
	});
    
	function func(e) {
		var date = new Date($element.val().replace(/[年月]/ig, '-').replace(/[日 ]/ig,''));
		date.setDate(date.getDate()+e.data.added); 
		$element.val($.D_ALG.formatDate(date, format));
		$element.trigger('change');
	}
	registerPeriodSelectAction(func, func);
}

/**
 * 实现年选择变更
 */
function implementYearSelector(type) {
	$('.input-year').unbind('change').change(function(ev) {
		var $element = $(this);
		var oldVal = $element.attr('oldYear');
		if (oldVal==undefined) {
			oldVal = this.defaultValue;
			if (oldVal) $element.attr('oldYear', oldVal);
		}

		//设置延时是为了防止value被DatetimePicker自动覆盖
		//$.D_ALG.dateRange(year, week)返回开始日期的年份未必与year相同，可能是上一年
		setTimeout(function() {
			var checked = $('.date-period-ctrl span.glyphicon').attr('data-checked'); //获取勾选状态
			$('.date-period-ctrl').trigger('click', [true, false]);//触发勾选日期范围控件
			
			var year = $element.val();
			if (oldVal!=year || checked!=1) {
				//$element.attr('oldYear', year); //暂存最新值
				if (type==2) {
					var week = parseInt($('.input-week').val())-1;
					var dayAry = $.D_ALG.dateRange(year, week);
					setPeriodOfWeek(year, week, dayAry);
				} else {
					var month = parseInt($('.input-month').val())-1;
					var dateAry = [new Date(year, month, 1), new Date(year, month, $.D_ALG.getMonthDays(year, month))];
					setPeriodOfMonth(year, month, dateAry);
				}
			}
		}, 1);
	});
}

//日期颠倒格式mm-dd yyyy转换为yyyy-mm-dd
function reversedDate(dateStr) {
	if (dateStr && dateStr.length>=10) {
		//return dateStr.substr(0, 6)+dateStr.substr(6, 4);
		return dateStr.substr(6, 4) + '-' + dateStr.substr(0, 5);
	}
}

//实现每日日期选择变更事件
function implementDailyDateChange() {
	$('#datetimepicker_0').unbind('change').change(function(ev){
		var $element = $(this);
		var oldVal = $element.attr('oldDate');
		if (oldVal==undefined) {
			oldVal = this.defaultValue;
			//oldVal = this.value;
			$element.attr('oldDate', oldVal||'');
		}
		
		var checked = $('.date-period-ctrl span.glyphicon').attr('data-checked'); //获取勾选状态
		$('.date-period-ctrl').trigger('click', [true, false]);//触发勾选日期范围控件
		
		var dateStr = $element.val();
		if (oldVal!=dateStr || checked!=1) {
			$element.attr('oldDate', dateStr);
			//执行查询
			executeQuery(function(){
				loadDtGrid(createQueryParameter());
			});
		}
	});
}

/**
 * 实现日期范围开端的选择变更事件
 */
function implementDateStartChange(type) {
	$('.input-date-start, .input-date-end')
	.unbind('change')
	.change(function(ev, source) {
		var $element = $(this);
		var oldVal = $element.attr('oldDate');
		if (oldVal==undefined) {
			oldVal = this.defaultValue;
			//oldVal = this.value;
			$element.attr('oldDate', oldVal||'');
		}
		
		//设置延时是为了防止value被DatetimePicker自动覆盖
		//$.D_ALG.dateRange(year, week)返回开始日期未必与输入值相同，它是输入值所在周的星期一
		setTimeout(function() {
			var checked = $('.date-period-ctrl span.glyphicon').attr('data-checked'); //获取勾选状态
			$('.date-period-ctrl').trigger('click', [true, false]);//触发勾选日期范围控件
			
			var dateStr = $element.val();
			if (oldVal!=dateStr || checked!=1) {
				var date = new Date(reversedDate(dateStr));
				
				if ($(ev.target).hasClass('input-date-start')) { //开端
					var endDate = new Date(reversedDate($('.input-date-end').val()));
					
					if (source!='today' && endDate.getTime()<date.getTime()) {
						$element.val(oldVal);
					} else {
						var year = date.getFullYear();
						if (type==2) {
						 	var week = $.D_ALG.theWeekNumber(date);
							var dayAry = $.D_ALG.dateRange(year, week);
							setPeriodOfWeek(year, week, dayAry, source=='today'?null:date, source=='today'?null:endDate);
						} else {
							var month = date.getMonth();
							var dateAry = [new Date(year, month, 1), new Date(year, month, $.D_ALG.getMonthDays(year, month))];
							setPeriodOfMonth(year, month, dateAry, source=='today'?null:date, source=='today'?null:endDate);
						}
					}
				} else {
					var startDate = new Date(reversedDate($('.input-date-start').val()));
					if (date.getTime()<startDate.getTime()) {
						$element.val(oldVal);
					} else {
						var reversedFormat = 'mm-dd yyyy';
						$element.attr('oldDate', $.D_ALG.formatDate(date, reversedFormat));
						//执行查询
						executeQuery(function(){
							loadDtGrid(createQueryParameter());
						});
					}
				}
			}
		}, 1);
	});
}
//切换日期范围工具栏
function swithPeriodSelector(type, enableDatePeriod) {
    if (enableDatePeriod===true)
    	$('.date-period-ctrl').trigger('click', [true, false]); //触发勾选日期范围控件
    
	$('.period-type-switch').removeClass('active');
	$('#period-type-switch-'+type).addClass('active');
	
    //更新工具栏控件
    $('.period-prev').parent().next('.date-selector').remove();
	var html = laytpl($('#period-selector-script-'+type).html()).render({});
	$('.period-prev').parent().after(html);
	
   	//第N周(月)值改变事件
   	$('.input-week, .input-month').change(function() {
   		if ($(this).val().length==1)
   			$(this).width('10px');
   		else
   			$(this).width('17px');
   	});
   	
   	//日期选择器
   	createNoResetBtnDatetimePicker('#datetimepicker_0', 'yyyy 年 mm 月 dd 日', 2, 2, 4);
    createNoResetBtnDatetimePicker('#datetimepicker_1, #datetimepicker_4', 'yyyy', 4, 4, 4);
    createNoResetBtnDatetimePicker('#datetimepicker_2, #datetimepicker_3, #datetimepicker_5, #datetimepicker_6', 'mm-dd yyyy', 2, 2, 4);
	
    $('#period-selector-type').val(type);
    if (type==1) { //日
    	$('#btn-today').attr('title', '今天'+(PTR_TYPE==1?'计划':((PTR_TYPE==2)?'任务':''))).html('今天');
    	implementPeriodOfDailySelector(); //每日日期选择器
    	implementDailyDateChange(); //日期选择变更
    } else if (type==2) { //周
    	$('#btn-today').attr('title', '本周'+(PTR_TYPE==1?'计划':((PTR_TYPE==2)?'任务':''))).html('本周');
	    implementPeriodOfWeekSelector(); //周日期范围选择器
	    implementYearSelector(type); //年选择变更
	    implementDateStartChange(type); //开始日期选择变更
    } else if (type==3) { //月
    	$('#btn-today').attr('title', '本月'+(PTR_TYPE==1?'计划':((PTR_TYPE==2)?'任务':''))).html('本月');
    	implementPeriodOfMonthSelector(); //月日期范围选择器
	    implementYearSelector(type); //年选择变更
	    implementDateStartChange(type); //开始日期选择变更
    }
}

//实现日期范围工具栏
function implementPeriodSelector() {
    swithPeriodSelector(parseInt($('#period-selector-type').val()));
    $('.period-type-switch').click(function() {
    	//设置忽略搜索输入框内容
    	//$('#search-content-invalid').val(1);
    	//清空查询输入框内容
    	if (typeof resetSearchContent=='function')
    		resetSearchContent();
    	
        $(this).blur();
        if (!$(this).hasClass('active')) {
            var type = $(this).attr('id').match(/\d+$/i);
        	swithPeriodSelector(type, true);
        }
    });
}

//注册事件-今天按钮
function registerTodaySelector() {
	$('#btn-today').click(function() {
		//设置忽略搜索输入框内容
    	//$('#search-content-invalid').val(1);
    	//清空查询输入框内容
    	if (typeof resetSearchContent=='function')
    		resetSearchContent();
    	
		var type =  parseInt($('#period-selector-type').val());
		if (type==1) {
			var format = 'yyyy 年 mm 月 dd 日';
		    var $element = $('#datetimepicker_0');
		    $element.val($.D_ALG.formatDate(new Date(), format));
		    $element.trigger('change');
		} else {
			var reversedFormat = 'mm-dd yyyy';
			var $element = $('.input-date-start');
			$element.val($.D_ALG.formatDate(new Date(), reversedFormat));
			$element.trigger('change', 'today');	    
		}
		
		$(this).blur();
	});
}

//注册事件-勾选日期查询范围控件
function registerDatePeriodCtrl() {
    $('.date-period-ctrl')
    //鼠标悬停打钩效果
    .hover(function() {
    	var $element = $(this).find('span.glyphicon');
    	if ($element.attr('data-checked')==1) {
    		//$element.removeClass('glyphicon-check').addClass('glyphicon-unchecked'); 勾选状态不演示操作效果
    	} else
    		$element.removeClass('glyphicon-unchecked').addClass('glyphicon-check');
    }, function() {
    	var $element = $(this).find('span.glyphicon');
    	if ($element.attr('data-checked')==1) {
    		//$element.removeClass('glyphicon-unchecked').addClass('glyphicon-check'); 勾选状态不演示操作效果
    	} else
    		$element.removeClass('glyphicon-check').addClass('glyphicon-unchecked');
    })
    //点击勾选/取消勾选
    .click(function(e, toChecked, isExecuteQuery) {
		var $element = $(this).find('span.glyphicon');
		if (toChecked===true || (toChecked!==false && ($element.attr('data-checked')==undefined || $element.attr('data-checked')==0)) ) {
			$element.removeClass('glyphicon-unchecked').addClass('glyphicon-check').attr('title', '点击日期范围无效').attr('data-checked', 1);
			$('#date-period').removeClass('ebtw-unselect-color');
		} else {
			$element.removeClass('glyphicon-check').addClass('glyphicon-unchecked').attr('title', '点击日期范围有效').attr('data-checked', 0);
			$('#date-period').addClass('ebtw-unselect-color');
		}
		//执行查询		
		if (isExecuteQuery!==false) {
			executeQuery(function(){
				loadDtGrid(createQueryParameter());
			});			
		}
    });	
}

//创建常用查询参数对象
function createUsualQueryParameter() {
	var parameter = new Object();
	
	//状态
	var status = $('#status option:selected').val();
	if (status!=undefined && status.length>0) {
		if (status==-1) { //未完成状态
			parameter.status_uncomplete = 1;
		} else {
			parameter.status = status;
		}
	}
	
	//周期
	var period = $('#period option:selected').val();
	if (period!=undefined && period.length>0)
		parameter.period = period;
	
	//日期范围
	var type =  $('#period-selector-type').val();
	var datePeriodChecked = $('.date-period-ctrl span.glyphicon').attr('data-checked');
	if (type!=undefined && type.length>0 && datePeriodChecked==1) {
		var startDate, endDate;
		if (parseInt(type) == 1) {
			var dateStr = $('#datetimepicker_0').val().replace(/[年月]/ig, '-').replace(/[日 ]/ig,'');
			startDate = dateStr + ' 00:00:00';
			endDate = dateStr + ' 23:59:59';
		} else {
			startDate = reversedDate($('.input-date-start').val()) + ' 00:00:00';
			endDate = reversedDate($('.input-date-end').val()) + ' 23:59:59';
		}
		parameter.search_time_s = startDate;
		parameter.search_time_e = endDate;
//		parameter.start_time_s = startDate;
//		parameter.start_time_e = endDate;
	}
	
	return parameter;
}
/**
 * 显示右侧页
 * @param {string} (必填) url 访问链接
 * @param {object} (可选) param 提交访问的的参数，与url匹配使用
 * @param {string} (可选) method 提交方法，默认post
 * @param {function} successHandle {可选} 执行成功后回调函数
 * @param {function} errorHandle {可选} 执行失败后回调函数
 */
function showSidepage(url, param, method, successHandle, errorHandle) {
	$('#sidepage').css('top', $(document).scrollTop());
	$('#sidepage').addClass('div-block');
	$("#sidepage").html("<div class='loading div-centered'></div>");
	$("#sidepage").show().animate({right: 0}, "fast", function() {
		callAjax(url, param, function(data) {
			$('#sidepage').removeClass('div-block');
			$("#sidepage").html(data);
			
			if (successHandle)
				successHandle();
		}, function (xhr, err, msg) {
			if (errorHandle)
				errorHandle(err);
		}, true, method);
	});
}

//关闭右侧页
function closeSidepage() {
    //deleteUEditor();//清除编辑器
    $("#sidepage").animate({ right: "-750px", width: "700px" }, "fast", function () {
        $(this).html("");
    });
    
    //尝试隐藏所有行选中状态
    $(".ebtw-row-select").hide();
//    //尝试复位“暂存当前已打开右侧页”的状态变量
//    $('#workbench-current-showed-ptr').val('');
}

/**列表隐藏工具栏操作**/

//function actionbarItemMouseOver(){
//  $(this).addClass("hover");
//}

//function actionbarItemMouseOut() {
//  $(this).removeClass("hover");
//}

//function actionbarMouseOver() {
//  $(this).prev().css("backgroundColor", "#F7F7F7");
//  $(this).prev().children().addClass("grid-columnstyle-noborder");
//}
//
//function actionbarMouseOut() {
//  $(this).prev().css("backgroundColor", "transparent");
//  $(this).prev().children().removeClass("grid-columnstyle-noborder");
//}

/*
//移除操作行
$("#gridTask").hover(function () { }, function () {
  $(".actionbar-tr").remove();
})
//移除操作行
$("#gridReport").hover(function () { }, function () {
  $(".actionbar-tr").remove();
})*/

/**
 * 内容区域自适应高度
 * @param {string} containerCssClass 容器标识css class
 * @param {number} rootHeight 距离顶部高度
 */
function adjustContainerHeight(containerCssClass, rootHeight) {
	var $listContainer = $('.'+containerCssClass);
	var $titleDiv = $('.'+containerCssClass+'-title');
	var $rowBlank =  $listContainer.prev('.row-blank');
	var spaceHeight = $titleDiv.height() + ($titleDiv.length?parseInt($titleDiv.css('margin-bottom')):0) + 1 + $rowBlank.height();
	$listContainer.css('max-height', $(window).height()-rootHeight-spaceHeight);
	$(window).resize(function(e){
		spaceHeight = $titleDiv.height() + ($titleDiv.length?parseInt($titleDiv.css('margin-bottom')):0) + 1 + $rowBlank.height();
		$listContainer.css('max-height', $(window).height()-rootHeight-spaceHeight);
		//$listContainer.mCustomScrollbar('update');
	});
}

/**
 * 内容区域自适应高度-版本2
 * @param {string} cssSelector css选择器
 * @param {number} rootHeight 距离顶部高度
 * @param {boolean} (可选) alsoSetMinHeight 是否也设置为最小高度，默认是设置最大高度
 * @param {string} (可选) resizeEventSuffixName 窗口缩放事件后缀名称，例如：one、two；如不指定后缀名，将不解绑旧resize事件
 */
function adjustContainerHeight2(cssSelector, rootHeight, alsoSetMinHeight, resizeEventSuffixName) {
	adjustContainerHeight2UsingE($(cssSelector), rootHeight, alsoSetMinHeight, resizeEventSuffixName);
}

/**
 * 内容区域自适应高度-版本2
 * @param {object} (必填) $container jquery选择对象
 * @param {number} rootHeight 距离顶部高度
 * @param {boolean} (可选) alsoSetMinHeight 是否也设置为最小高度，默认是设置最大高度
 * @param {string} (可选) resizeEventSuffixName 窗口缩放事件后缀名称，例如：one、two；如不指定后缀名，将不解绑旧resize事件
 */
function adjustContainerHeight2UsingE($container, rootHeight, alsoSetMinHeight, resizeEventSuffixName) {
	var height = $(window).height()-rootHeight;
	$container.css('max-height', height);
	if (alsoSetMinHeight)
		$container.css('min-height', height);
	
	//解绑旧resize事件处理函数
	if (resizeEventSuffixName!=undefined) {
		$(window).unbind('resize.'+resizeEventSuffixName);
	}
	//绑定resize事件处理函数
	$(window).bind(resizeEventSuffixName?('resize.'+resizeEventSuffixName):'resize', function(e) {
		height = $(window).height()-rootHeight;
		$container.css('max-height', height);
		if (alsoSetMinHeight)
			$container.css('min-height', height);
		
		//$container.mCustomScrollbar('update');
	});
}

/**
 * 内容区域自适应高度-版本3
 * @param {number} (必填) $savedInElement 保存上层容器最大高度的对象
 * @param {object} (必填) $container 待调整的容器，jquery选择对象
 * @param {number} (必填) rootHeight 距离顶部高度
 * @param {boolean} (可选) alsoSetMinHeight 是否也设置为最小高度，默认是设置最大高度
 * @param {string} (可选) resizeEventSuffixName 窗口缩放事件后缀名称，例如：one、two；如不指定后缀名，将不解绑旧resize事件
 */
function adjustContainerHeight3UsingE($savedInElement, $container, rootHeight, alsoSetMinHeight, resizeEventSuffixName) {
	var height = parseInt($savedInElement.val())-rootHeight;
	$container.css('max-height', height);
	if (alsoSetMinHeight)
		$container.css('min-height', height);
	
	//解绑旧resize事件处理函数
	if (resizeEventSuffixName!=undefined) {
		$(window).unbind('resize.'+resizeEventSuffixName);
	}
	//绑定resize事件处理函数
	$(window).bind(resizeEventSuffixName?('resize.'+resizeEventSuffixName):'resize', function(e) {
		height = parseInt($savedInElement.val())-rootHeight;
		$container.css('max-height', height);
		if (alsoSetMinHeight)
			$container.css('min-height', height);
		
		//$container.mCustomScrollbar('update');
	});
}

/**
 * 注册 计算内容区域自适应高度并保存到指定对象功能-版本2
 * @param {object} (必填) $savedInElement 保存高度的对象，jquery选择对象(通常是input[:hidden]类型)
 * @param {number} (必填) rootHeight 距离顶部高度
 * @param {function} (可选) callback(height)执行保存或窗口缩放时调用的函数
 */
function registerCalculateAdjustContainerHeight2($savedInElement, rootHeight, callback) {
	var height = $(window).height()-rootHeight;
	$savedInElement.val(height);
	$(window).resize(function(e){
		height = $(window).height()-rootHeight;
		$savedInElement.val(height);
		if (callback)
			callback(height);
	});

	if (callback) {
		//callback.apply(null, [height]);
		callback(height);
	}
}

/**
 * 注册 计算内容区域自适应高度并保存到指定对象功能-版本3
 * @param {object} (必填) $savedInElement 保存高度的对象，jquery选择对象(通常是input[:hidden]类型)
 * @param {object} (必填) $container 容器，jquery选择对象
 * @param {number} (必填) defaultRootHeight 已占用高度初始值
 * @param {function} (可选) 容器缩放事件时请求获取已占用高度的函数；如不填将使用初始值
 * @return {number} 当前被记录的高度值
 */
function registerCalculateAdjustContainerHeight3($savedInElement, $container, rootHeight, fetchRootHeightFunc) {
	var value = $container.height() - rootHeight;
	$savedInElement.val(value);
	$(window).resize(function(e) {
		if (fetchRootHeightFunc) 
			rootHeight = fetchRootHeightFunc();
		$savedInElement.val($container.height() - rootHeight);
	});
	
	return value;
}

/**
 * 自定义滚动条
 * @param {string} (必填) cssSelector css选择器
 * @param {number} (可选) mouseWheelPixels 鼠标滚动像素
 * @param {boolean} (可选) enableScrollButtons 是否显示箭头，默认不显示
 */
function customScrollbar(cssSelector, mouseWheelPixels, enableScrollButtons) {
	customScrollbarUsingE($(cssSelector), mouseWheelPixels, enableScrollButtons);
}
/**
 * 自定义滚动条
 * @param {object} (必填) $container jquery选择对象
 * @param {number} (可选) mouseWheelPixels 鼠标滚动像素
 * @param {boolean} (可选) enableScrollButtons 是否显示箭头，默认不显示
 * @param {string} (可选) scrollbarPosition 滚动条位置："outside"=外部，"inside"或默认=内部
 * @param {object} (可选) additionalParam 附加参数
 */
function customScrollbarUsingE($container, mouseWheelPixels, enableScrollButtons, scrollbarPosition, additionalParam) {
	var parameter = {
			mouseWheelPixels: mouseWheelPixels||30,
			scrollInertia: 0,
			scrollButtons:{
				enable:enableScrollButtons||false,
				scrollType:"continuous",
				scrollSpeed:5,
				scrollAmount:10
			},
		};
	if (typeof scrollbarPosition=='string')
		parameter.scrollbarPosition = scrollbarPosition;
	
	$container.mCustomScrollbar($.extend(parameter, additionalParam));
}

/**
 * 加载嵌入页面内容
 * @param {string} (必填) containerSelector 被嵌入容器的选择器
 * @param {string} (必填) url 访问链接
 * @param {object} (可选) param 提交访问的的参数，与url匹配使用
 */
function loadEmbedPage(containerSelector, url, param) {
	var $element = $(containerSelector);
	$element.addClass('div-block');
	$element.html('<div class="loading div-centered div-centered-fluid"></div>');

	$element.show("fast", function() {
		callAjax(url, param, function(html) {
			$element.html(html);
			$element.removeClass('div-block');
		}, function (xhr, err, msg) {
			$element.removeClass('div-block');
		});
	});			
}

/**
 * 注册Tab标签
 * @param {string} (必填) url 访问链接
 * @param {string} prefix 标签前缀
 * @param {number} (可选) defaultActiveNo 默认激活序号
 * @param {string} (可选) loadingIconType 加载等待图标类型：填small或large
 * @param {string} (可选) loadingIconCssClass 加载等待图标附加css class
 * @param {object} (可选) param 提交访问的参数，与url匹配使用；与fetchParamFunc同时只有一个有效
 * @param {function} (可选) fetchParamFunc 回调获取提交访问时的参数；与param同时只有一个有效
 * @param {function} (可选) callback 点击tab标签时的回调函数[参数：activeNo, prefix, url, param, loadingIconType]；
 * @param {object} (可选) executeLoadDataCallbacks 执行加载数据的函数对照表，当本参数传入真实函数时，优先执行本参数的对应函数，不执行默认实现；
 * 例如：{0:callback0, 1:callback1}，回调参数：callback0(activeNo, url, param, successHandle, errorHandle）
 * 	如不填则执行默认加载函数
 */
function registerTab(url, prefix, defaultActiveNo, loadingIconType, loadingIconCssClass, param, fetchParamFunc, callback, executeLoadCallbacks) {
	//注册事件-标签切换
	$('.'+prefix+'-tab-head').click(function() {
		$('.'+prefix+'-tab-select').each(function(){
			$(this).parent().removeAttr('data-selected');
			$(this).css('display', 'none');
		});
		
		var id_str = $(this).attr('id');
		//var activeNo = id_str.charAt(id_str.length-1);
		var activeNo =id_str.match(/\d+$/)[0]; //提取字符串末尾的数字
		$(this).attr('data-selected', 1);
		$(this).find('.'+prefix+'-tab-select').css('display', 'block');
		
		if (callback!=undefined) {
			callback(activeNo, prefix, url, param, loadingIconType);
		} else {
			//加载标签内容
			loadTabContent(url, prefix, activeNo, loadingIconType, loadingIconCssClass, param, fetchParamFunc, executeLoadCallbacks);
		}
	});
	
	//触发点击一个默认标签的事件
	if ( (typeof defaultActiveNo == 'number') || defaultActiveNo!=undefined) {
		$('#'+prefix+'-tab'+defaultActiveNo).trigger('click');
	}
	
	//注册事件(鼠标悬停)-标签
	$('.'+prefix+'-tab-head').hover(function() {
		$(this).find('.'+prefix+'-tab-select').css('display', 'block');
	}, function() {
		var selected = $(this).attr('data-selected');
		if (!selected || selected!=1)
			$(this).find('.'+prefix+'-tab-select').css('display', 'none');
	});
}

/**
 * 加载Tab标签页内容
 * @param {string} (必填) url 访问链接
 * @param {string} (必填) prefix 标签前缀
 * @param {number} (必填) activeNo 激活序号
 * @param {string} (可选) loadingIconType 加载等待图标类型：填small或large
 * @param {string} (可选) loadingIconCssClass 加载等待图标附加css class
 * @param {object} (可选) param 提交访问的的参数，与url匹配使用；与fetchParamFunc同时只有一个有效
 * @param {function} (可选) fetchParamFunc 回调获取提交访问时的参数；与param同时只有一个有效
 * @param {object} (可选) executeLoadDataCallbacks 执行加载数据的函数对照表，当本参数传入真实函数时，优先执行本参数的对应函数，不执行默认实现；
 * 例如：{0:callback0, 1:callback1}，回调参数：callback0(activeNo, url, param, successHandle, errorHandle）
 */
function loadTabContent(url, prefix, activeNo, loadingIconType, loadingIconCssClass, param, fetchParamFunc, executeLoadDataCallbacks) {
	//回调获取提交的参数
	if (param==undefined && (typeof fetchParamFunc=='function')) {
		param = fetchParamFunc(activeNo);
	}
	
	var loadingIconClass = 'loading';
	var divCenteredClass = (loadingIconCssClass==undefined)?'':loadingIconCssClass;
	if (loadingIconType=='small') {
		loadingIconClass='loading2';
		divCenteredClass += ' div-centered-fluid';
	}
	var $element = $('#'+prefix+'-tab-content');
	$element.addClass('div-block');
	$element.html("<div class='"+loadingIconClass+" div-centered "+divCenteredClass+"'></div>");
	$element.show("fast", function() {
		callAjax(url, param, function(html) {
			$element.html(html);
			
			//执行加载数据回调函数
			if (executeLoadDataCallbacks && executeLoadDataCallbacks[activeNo]) {
				executeLoadDataCallbacks[activeNo](activeNo, url, param, function(data){
					$element.removeClass('div-block');
				}, function(err) {
					$element.removeClass('div-block');
				});
			} else {
				$element.removeClass('div-block');
			}	
		}, function (xhr, err, msg) {
			$element.removeClass('div-block');
		});
	});
}

//依据具体情况下刷新主视图
function refreshMainViewActually(scroll, ptrType, ptrId) {
	if ((typeof isWorkbench=='undefined' || isWorkbench==false) && typeof createQueryParameter=='function') {
		loadDtGrid(createQueryParameter(), false); //刷新Grid列表
	}
	if (typeof reloadCurrentBoardLane=='function') {
		reloadCurrentBoardLane(scroll); //重载当前泳道
	}
	if (typeof reloadBoardLaneItem=='function' && ptrType!=undefined && ptrId!=undefined) {
		reloadBoardLaneItem(ptrType, ptrId);
	}
	//滚动条滚到底部
//	if (scroll!=undefined && parseInt(scroll)>0 && typeof boardLaneScrollToBottom=='function') {
//		boardLaneScrollToBottom(scroll);
//	}
}

/**
 * 加载单个看板泳道
 * @param {string} parentType 父容器类型：'workbench'=工作台容器，'ptr'=普通容器
 * @param {number} laneNo 泳道编号
 * @param {string} laneSelector 泳道容器选择器
 * @param {string} ptrType 业务类型：1=计划，2=任务，1_2=计划与任务
 * @param {object} parameter (可选) 附加的访问参数
 */
function load_board_lane(parentType, laneNo, laneSelector, ptrType, parameter, loadedCallback) {
	var $lane = $(laneSelector);
	$lane.addClass('div-block');
	$lane.html('<div class="loading2 div-centered div-centered-fluid"></div>');
	
	var param = $.extend({}, {reserved_parent_type:parentType, reserved_lane_no:laneNo, reserved_ptr_type:ptrType}, parameter);
	loadedLaneParameters[laneNo] = parameter; //临时保存附加的访问参数
	callAjax(getServerUrl() + "board_lane.php", param, function(data) {
		$lane.removeClass('div-block');
		$lane.html(data);
		
		if (loadedCallback)
			loadedCallback(true);
	}, function (xhr, err, msg) {
		if (loadedCallback)
			loadedCallback(false);
	});
}
