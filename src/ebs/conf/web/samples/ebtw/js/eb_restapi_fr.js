/**
 * 加载访问恩布IM RestAPI跨域框架
 */
//;(function ($, window) {
$(document).ready(function() {
	$.getScript(getServerUrl()+"js/base64.js?v=1", function() {
		$.getScript(getServerUrl()+"js/ifrMessenger.js?v=1", function() {
			//定义跨域接收器
			window.ifrMessenger = new  window.IfrMessenger('parent');
		    $.getScript(getServerUrl()+"js/foundation.js?v=1", function() {
		        $.getScript(getServerUrl()+"js/configure.js?v=1", function() {
			        $.getScript(getServerUrl()+"js/domainCtrl.js?v=1", function() {
				        $.getScript(getServerUrl()+"js/imGlobal.js?v=1", function() {
				        	$.getScript(getServerUrl()+"js/EventHandle.js?v=1", function() {
					        	$.getScript(getServerUrl()+"js/processor.js?v=1", function() {
						        	$.getScript(getServerUrl()+"js/eb_restapi.js?v=1", function() {
						        		eb_restapi_fr_load_complete();
						        	});
					        	});
				        	});
				        });
				    });
			    });
		    });
		});
	});
});
//})(jQuery, window);