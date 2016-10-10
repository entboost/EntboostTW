;(function ($, window) {
    var jqEBM =$.jqEBMessenger;

    //当前已重登次数
    jqEBM.current_relogon_times = 0;
    //当前会话已重连次数
    jqEBM.current_reloadchat_times = 0;
    //当前浏览器类型
    jqEBM.AGENT = window.navigator.userAgent.toLowerCase();
    var ifrMessenger = window.ifrMessenger;
    ifrMessenger.listen(function (jsonString) {
        var jsonMsg = json_parse(jsonString);
        var req = jqEBM.hashMap[jsonMsg.tag.toString()];

//      if(req.api!=apiMap.get("ebwebum.loadorg")) {//loadorg返回信息信息太多，不详细打印
//          logjs_info("client server return=========\n"+jsonString);
//      } else {//打印loadorg简要信息
//        logjs_info("client server return tag:"+jsonMsg.tag+", status:"+jsonMsg.status);
//      }

        if(jsonMsg.status =='success') {
            if(req.pv)
                req.pv = $.Base64.decode(req.pv);
            if(!jsonMsg.data) {
            	logjs_info("服务器返回空数据，tag="+jsonMsg.tag+", url="+req.url+", req.pv:\n"+req.pv);
            }
            jqEBM.prc.processReceiveData(req, jsonMsg.data?$.Base64.decode(jsonMsg.data):null);
        }
        else {
        	logjs_info("网络错误，"+JSON.stringify(jsonMsg.xhr));
            jqEBM.prc.processNetworkError(req);
            var errorCallback =jqEBM.hashMap[jqEBM.MESSAGE_CALLBACK_PREFIX+jsonMsg.tag];
            if(errorCallback)
                errorCallback($.jqEBMessenger.errCodeMap.NETWORK_ERROR);
        }

        delete jqEBM.hashMap[req.tag] ;
        delete jqEBM.hashMap[jqEBM.MESSAGE_CALLBACK_PREFIX + jsonMsg.tag];
    });

    /**
     * 发送跨域指令
     * @param api
     * @param url
     * @param parameter
     * @param parameterJson2kv
     * @param timeout
     * @param errorCallback
     * @returns {Boolean}
     */
    ifrMessenger.sendMessage =function(api, url, parameter, parameterJson2kv, timeout, callback) {
        var tag = jqEBM.generateId();
        var jsonMsg ={
            api: api,
            url: url,
            tag: tag,
            timeout: timeout==undefined?jqEBM.options.TIMEOUT:timeout,
            submitType: 'POST',
            json2kv:(parameterJson2kv==null)?true:parameterJson2kv,
            //pv: $.parseJSON(BASE64.encoder(parameter))
            pv: parameter? $.Base64.encode(parameter):null
        };

        jqEBM.hashMap[tag] = jsonMsg;
        if(callback) {
            jqEBM.hashMap[jqEBM.MESSAGE_CALLBACK_PREFIX + tag] = callback;
        }

        //logjs_info("send=========\n"+JSON.stringify(jsonMsg));
        //logjs_info("pv:\n"+parameter);
        var iframe_name =jqEBM.fn.domainURI(url);
        //logjs_info('iframe_name:'+iframe_name);
        ifrMessenger.targets[iframe_name].send(JSON.stringify(jsonMsg));
    };
})(jQuery, window);
