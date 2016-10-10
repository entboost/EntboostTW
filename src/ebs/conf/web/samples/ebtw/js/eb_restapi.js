/**
 * 恩布rest api访问封装
 */
;(function($) {
    var ifrMessenger = window.ifrMessenger;
    var jqEBM =$.jqEBMessenger;
    var options = jqEBM.options;
	
	$.eb = {
		//恩布rest api版本
		//rest_api_version : 'rest.v03.',
		//生成访问api的url
		createApiUrlUsingAddr: function(addr, api) {
			//return addr + '/' + this.rest_api_version + api;
			return jqEBM.fn.createRestUrl(options.HTTP_PREFIX + addr, jqEBM.API_VERSION, api);
		},
		//默认错误处理函数
		defaultErrorHandle : function(code, errorHandle, additionalData) {
			if (code==11) { //会话失效，触发重新验证处理
				reInitApp();
			} else { //普通错误
				if (errorHandle)  {
					var param = {code:code};
					if (additionalData)
						param.additionalData = additionalData;
					errorHandle(param);
				}
			}
		},
		//发送跨域信息(用于跨域调用恩布RestAPI)
		sendCrossDomainMessage : function(accessor, apiName, parameter, successHandle, errorHandle) {
			//清除空值属性
			for (key in parameter) {
				if (typeof parameter[key] ==='undefined' || parameter[key]===null)
					delete parameter[key];
			}
			
            ifrMessenger.sendMessage(jqEBM.apiMap[apiName],
        		accessor.createApiUrl(apiName),
                JSON.stringify(parameter),
                true,
                ajaxTimeout,
                function(code, param) {
	                if(code == jqEBM.errCodeMap.OK.code) {
	                    if (successHandle) 
	                    	successHandle(param);
	                } else {
	                	$.eb.defaultErrorHandle(code, errorHandle);
	                }
            	}
            );
		},
	};
	
	//访问恩布LC服务对象
	$.ebweblc = $.extend({}, {
		serverAddr: lcServerAddr,
		user_id: user_id,
		logon_type: logon_type,
		acm_key: acm_key,
		//生成访问api的url
		createApiUrl: function(api) {
			return $.eb.createApiUrlUsingAddr(this.serverAddr, api);
		},
	});
	
	//当前用户访问恩布UM服务对象
	$.ebwebum = $.extend({}, {
		serverAddr: umServerAddr,
		eb_sid: umEbsid,
		//生成访问api的url
		createApiUrl: function(api) {
			return $.eb.createApiUrlUsingAddr(this.serverAddr, api);
		},
		//生成云盘资源共享访问地址
		createShareUrl: function(url) {
			return url+'&acm_key='+$.ebweblc.acm_key+'&logon_type='+$.ebweblc.logon_type;
		},
		/**
		 * 获取用户信息或资料
		 * @param {number} type 数据类型
		 * @param {string} param1 参数1，配合type使用；type=1表示用于获取头像的UID
		 * @param {string} param2 参数2，配合type使用
		 * @param {function} successHandle 执行成功后回调函数
         * @param {function} errorHandle 执行失败后回调函数
		 */
		getuserinfo: function(type, param1, param2, successHandle, errorHandle) {
			var parameter = {
				eb_sid : this.eb_sid,
				user_id : $.ebweblc.user_id,
				type : type,
				param1 : param1,
				param2 : param2
			};
			$.eb.sendCrossDomainMessage(this, "ebwebum.getuserinfo", parameter, successHandle, errorHandle);			
		},
		/**
		 * 添加云盘资源
		 * @param {number} type 资源类型：1=云笔记4=目录资源5=文件资源
		 * @param {number} fromType 来源类型：1=企业资源2=群组资源3=个人云盘11=计划附件（配合flag 使用）12=任务附件（配合flag 使用）13=报告附件（配合flag 使用）
		 * @param {string} fromId 来源ID（配合from_type 使用）
		 * @param {number} flag 资源标识：0=普通（文档附件）3=评论（评论附件）
		 * @param {string} name 资源名称
		 * @param {string} description描述
		 * @param {string} parentResourceId 上级目录资源ID，0=存放根目录
		 * @param {function} successHandle 执行成功后回调函数
         * @param {function} errorHandle 执行失败后回调函数
		 */
		addresource: function(type, fromType, fromId, flag, name, description, parentResourceId, successHandle, errorHandle) {
			var parameter = {
				eb_sid : this.eb_sid,
				user_id : $.ebweblc.user_id,
				name : name,
				type : type,
				flag : flag,
				from_id : fromId,
				from_type : fromType,
				description : description,
				parent_resource_id : parentResourceId
			};
			$.eb.sendCrossDomainMessage(this, "ebwebum.addresource", parameter, successHandle, errorHandle);
		},
		/**
		 * 删除云盘资源
		 * @param {string} resourceId 资源编号
		 * @param {function} successHandle 执行成功后回调函数
         * @param {function} errorHandle 执行失败后回调函数
		 */
		deleteresource : function(resourceId, successHandle, errorHandle) {
			var parameter = {
				eb_sid : this.eb_sid,
				user_id : $.ebweblc.user_id,
				resource_id : resourceId
			};
			$.eb.sendCrossDomainMessage(this, "ebwebum.deleteresource", parameter, successHandle, errorHandle);
		},
		/**
		 * 获取资源，指定单个共享资源
		 * @param {string} resourceId 资源编号
		 * @param {string} toShareUid 共享资源给对方的用户编号(user_id)，配合shareType使用；toShareUid=user_id 用于共享给自己
		 * @param {string} shareType 共享类型：0=不共享1=临时共享（用户主动下线无效）
		 * @param {function} successHandle 执行成功后回调函数
         * @param {function} errorHandle 执行失败后回调函数
		 */
		getresource : function(resourceId, toShareUid, shareType, successHandle, errorHandle) {
			var parameter = {
				eb_sid : this.eb_sid,
				user_id : $.ebweblc.user_id,
				resource_id : resourceId,
				to_share_uid : toShareUid,
				share_type : shareType
			};
			$.eb.sendCrossDomainMessage(this, "ebwebum.getresource", parameter, successHandle, errorHandle);
		},

		/**
		 * 加载指定云盘资源列表或数量
		 * @param {number} type 资源类型：1=云笔记4=目录资源5=文件资源
		 * @param {number} fromType 来源类型：1=企业资源2=群组资源3=个人云盘11=计划附件（配合flag 使用）12=任务附件（配合flag 使用）13=报告附件（配合flag 使用）
		 * @param {string} fromId 来源ID（配合from_type 使用）
		 * @param {number} flag 资源标识： -1=全部，0=普通（文档附件），3=评论（评论附件）；填入null则不区分flag(相当于-1)
		 * @param {number} get_summary 获取摘要信息(例如数量)：1=获取摘要信息，其它=获取记录列表
		 * @param {number} offset 偏移量(从第几条记录开始)，用于分页；默认值-1(加载所有数据)
		 * @param {number} limit 限制返回列表的最大数量，用于分页；默认值30
		 * @param {function} successHandle 执行成功后回调函数
         * @param {function} errorHandle 执行失败后回调函数
		 */
		loadresource : function(type, fromType, fromId, flag, get_summary, offset, limit, successHandle, errorHandle) {
			var parameter = {
				eb_sid : this.eb_sid,
				user_id : $.ebweblc.user_id,
				type : type,
				flag : flag,
				get_summary: get_summary,
				offset: offset,
				limit: limit,
				from_id : fromId,
				from_type : fromType
			};
			$.eb.sendCrossDomainMessage(this, "ebwebum.loadresource", parameter, successHandle, errorHandle);
		},
		
		/**
		 * 加载指定云盘资源列表或数量(支持多个fromId、fromType、flag、type作为查询条件)
		 * @param {array} conditions 查询条件组合数组，例如：[{type:5, fromType:3, fromId:'12345', flag:-1}, ...]
		 * 		type 资源类型：1=云笔记4=目录资源5=文件资源
		 * 		fromType 来源类型：1=企业资源2=群组资源3=个人云盘11=计划附件（配合flag 使用）12=任务附件（配合flag 使用）13=报告附件（配合flag 使用）
		 * 		fromId 来源ID（配合from_type 使用）
		 * 		flag 资源标识： -1=全部，0=普通（文档附件），3=评论（评论附件）；填入null则不区分flag(相当于-1)
		 * @param {number} get_summary 获取摘要信息(例如数量)：1=获取摘要信息，其它=获取记录列表
		 * @param {function} successHandle 执行成功后回调函数
         * @param {function} errorHandle 执行失败后回调函数
		 */
		loadresources : function(conditions, get_summary, successHandle, errorHandle) {
			var parameter = {
					eb_sid : this.eb_sid,
					user_id : $.ebweblc.user_id,
					get_summary: get_summary,
//					type : type,
//					flag : flag,
//					from_id : fromId,
//					from_type : fromType
				};
			for (var i=0; i<conditions.length; i++) {
				var condition = conditions[i];
				parameter['type'+i] = condition.type;
				parameter['flag'+i] = condition.flag;
				parameter['from_id'+i] = condition.fromId;
				parameter['from_type'+i] = condition.fromType;
			}
			$.eb.sendCrossDomainMessage(this, "ebwebum.loadresources", parameter, successHandle, errorHandle);
		},
		
		/**
		 * 修改云盘资源信息
		 * @param {string} resourceId 资源编号
		 * @param {string} name 资源名称
		 * @param {string} description 描述
		 * @param {string} parentResourceId 上级目录的资源编号，用于移动到指定目录
		 * @param {function} successHandle 执行成功后回调函数
         * @param {function} errorHandle 执行失败后回调函数
		 */
		editresource : function(resourceId, name, description, parentResourceId, successHandle, errorHandle) {
			var parameter = {
				eb_sid : this.eb_sid,
				user_id : $.ebweblc.user_id,
				resource_id : resourceId,
				parent_resource_id : parentResourceId,
				name : name,
				description : description
			};
			$.eb.sendCrossDomainMessage(this, "ebwebum.editresource", parameter, successHandle, errorHandle);
		},
	});
	
	//访问恩布CM服务对象
	$.ebwebcm_template = $.extend({}, {
		serverAddr: '',
		eb_sid: '',
		//生成访问api的url
		createApiUrl: function(api) {
			return $.eb.createApiUrlUsingAddr(this.serverAddr, api);
		},
		/**
		 * 上传文件
		 * @param {string} resourceId 资源编号
         * @param {string} fileElementId 浏览文件的file控件id
         * @param {boolean} cloneElementToPostion (可选) 是否复制一个新的file对象放置在原file对象的位置，默认true
         * @param {function} successHandle 上传成功后回调函数
         * @param {function} errorHandle 上传失败后回调函数
		 */
		upload: function(resourceId, fileElementId, cloneElementToPostion, successHandle, errorHandle) {
			var lc = $.ebweblc;
			var url = this.createApiUrl('ebwebcm.upload');
			var secureuri = false;
			//var secureuri = jqEBM.fn.domainURIOfOrigin(url)+'/eb_cross.html';
			//logjs_info('secureuri='+secureuri);
			
			//执行上传文件
			var jqxhr = ajaxFileUpload(lc.user_id, lc.logon_type, lc.acm_key, resourceId, url, secureuri, fileElementId, cloneElementToPostion, function(data, status){
				var code = data.code;
				if(code == jqEBM.errCodeMap.OK.code) {
					if (successHandle) 
						successHandle(data);
                } else {
                	$.eb.defaultErrorHandle(code, errorHandle);
                }
			}, function(s, data, status, e) {
				//logjs_info('ajax error ====== s');
				//logjs_info(s);
				//logjs_info(data);
				logjs_info('upload status:' + status);
				//logjs_info(e);
				//logjs_info('ajax error ====== e');
				var code = status=='abort'?jqEBM.errCodeMap.ABORT.code:jqEBM.errCodeMap.NETWORK_ERROR.code;
				$.eb.defaultErrorHandle(code, errorHandle, status);
			});
			
			return jqxhr;
		},
	});
	
	/**
	 * 获取一个访问CM服务端的对象，如不存在则自动创建
	 * @param {string} addr 访问地址：ip地址(域名)+端口地址
	 * @param {string} ebSid {可选} 恩布http访问会话编号
	 */
	$.eb.getCmAccessor = function(addr, ebSid) {
		//保存访问CM服务端的对象列表，以ip地址(域名)+端口地址作为索引
		if ($.ebwebcms==undefined)
			$.ebwebcms = {};
		
		var cm = $.ebwebcms[addr];
		if (cm==undefined) {
			cm = $.ebwebcms[addr] = $.extend({}, $.ebwebcm_template);
			cm.serverAddr = addr;
			cm.eb_sid = ebSid;
		}
		
		return cm;
	};
	
})(jQuery);