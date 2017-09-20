;(function($, window) {
	var ifrMessenger = window.ifrMessenger;
    var jqEBM =$.jqEBMessenger;

    $.extend(jqEBM.fn, {
    	//截取域名(包括端口号)
    	domainURIOfOrigin: function(url) {
    		var domain = url.match(/^([(http)|(https)|(ftp)]+:+)?\/*(?:[^@]+@)?([^:\/#]+:?\d*)?/);
    		return domain[0];
    	},
        /**
         * 截取域名(包括端口号)并把点号换成下划线
         * @param url
         * @returns
         */
        domainURI: function(url) {
            //var durl=/http:\/\/([^\/]+)\//i;
        	logjs_info("domainURI url=" + url);
            //var domain = url.match('^(?:(?:https?|ftp):)/*(?:[^@]+@)?([^:/#]+)');
            var domain = url.match(/^(?:(?:https?|ftp):)\/*(?:[^@]+@)?([^:\/#]+:?\d*)/);
            //logjs_info(domain);
            //logjs_info(domain);
            return domain[1].replace(/\.|\:/g, "_");
        },
        /**
         * 创建Rest Api 地址
         * @param serverUrl {string} 服务器地址
         * @param version {string} 版本号
         * @param apiName {string} Api名
         * @returns Rest api {string} URL地址
         */
        createRestUrl: function(serverUrl, version, apiName) {
            return [
                serverUrl,
                "/rest.",
                "v",
                version,
                ".",
                apiName
            ].join("");
        },
        /**
         * 执行iframe加载各不同域引擎页面
         * @param iframe1
         * @param domain_var
         * @param url
         * @param onloadCallback
         * @param timeoutHandler
         */
        execute_load_frame: function (iframe1, domain_var, url, onloadCallback, timeoutHandler) {
            this.iframeOnload(iframe1,
                jqEBM.options.TIMEOUT, //timeout
                function () { //sucess
                    //iframeMap.put(domain_var, iframe1);
                    $(iframe1).attr("state", "1");
                    ifrMessenger.addTarget(iframe1.contentWindow, domain_var);
                    logjs_info(domain_var + ' ==>iframe created.');
                    onloadCallback();
                },
                function () { //timeout
                    //$(iframe1).remove();
                	logjs_info("加载超时, " + url);
                    $(iframe1).attr("state", "0");
                    if (timeoutHandler)
                        timeoutHandler();
            });
            $(iframe1).attr("src", url);
        },
        //通过URL地址创建iframe,如存在则返回现成的
        create_iframe: function (url, onloadCallback, timeoutHandler) {
            var domain_var = this.domainURI(url);

            var iframe1 = $("#" + domain_var)[0];//iframeMap.get(domain_var);
            if (!iframe1) {
                var iframe_str = [
                    "<iframe id='",
                    domain_var,
                    "' style='display:",
                    (jqEBM.options.IFRAME_DEBUG ? "block" : "none"),
                    ";width:800px;height:250px;'></iframe>"
                ].join("");
                $(iframe_str).prependTo('body');
                iframe1 = $("#" + domain_var)[0];
                this.execute_load_frame(iframe1, domain_var, url, onloadCallback, timeoutHandler);
            } else {
                var state = $(iframe1).attr("state");
                if (!state || state != "1") {//之前加载不成功
                	logjs_info("重新加载, " + url);
                    this.execute_load_frame(iframe1, domain_var, url, onloadCallback, timeoutHandler);
                } else {
                    if (onloadCallback)
                        onloadCallback();
                }
            }
        },
        //加载iframe，成功后调用约定回调函数
        //参数url: 访问地址
        //参数try_times: 第几次重试
        //参数callbackFun: 访问成功回调函数
        load_iframe: function (url, try_times, callbackFun) {
        	logjs_info('try times=' + try_times + ', url:' + url);
            try_times++;
            this.create_iframe(url,
                callbackFun,
                function () {
            		logjs_info('load timeout url: ' + url);
                    if (try_times < jqEBM.options.MAX_RETRY_TIMES) //递归调用
                        jqEBM.fn.load_iframe(url, try_times, callbackFun);
                });
        },
        //iframe加载完毕
        iframeOnload: function (iframe, timeout, onloadCallback, timeoutHandler) {
            var bTimeout =false;
            var kill = setTimeout(function(){
                bTimeout =true;
                timeoutHandler();
            }, timeout);
            if (iframe.attachEvent){
                iframe.attachEvent("onload", function(){
                    if(bTimeout)
                        return;

                    clearTimeout(kill);
                    if(onloadCallback)
                        onloadCallback();
                });
            } else {
                iframe.onload = function() {
                    if(bTimeout)
                        return;

                    clearTimeout(kill);
                    if(onloadCallback)
                        onloadCallback();
                };
            }
        }
    });
})(jQuery, window);