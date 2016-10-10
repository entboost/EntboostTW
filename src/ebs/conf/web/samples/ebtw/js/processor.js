;(function($, window) {
    var jqEBM = $.jqEBMessenger;
    if(!jqEBM.prc)
        jqEBM.prc ={};
    var processor = jqEBM.prc;
    var fn =jqEBM.fn;

    var clientInfo = jqEBM.clientInfo;
    var apiMap = jqEBM.apiMap;
    var chatMap = jqEBM.chatMap;
    var statecodeMap = jqEBM.statecodeMap;
    var errCodeMap = jqEBM.errCodeMap;
    var msgcodeMap = jqEBM.msgcodeMap;
    var connectMap = jqEBM.connectMap;
    var uidCallidMap = jqEBM.uidCallidMap;
    var EBUM = jqEBM.EBUM;
    var EBCM = jqEBM.EBCM;
    var options = jqEBM.options;

    /**
     * 注册事件处理器
     * @param eventHandle
     */
    jqEBM.registerEventHandle = function(eventHandle) {
        this.eventHandle =eventHandle;
    };

    processor.processNetworkError = function (req) {
    	var callback = jqEBM.hashMap[jqEBM.MESSAGE_CALLBACK_PREFIX + req.tag];
    	if (callback) {
    		callback(statecodeMap["NETWORK_ERROR"]);
    	}
//        switch (req.api) {
//            case apiMap["ebwebum.hb"]:
//                //当前心跳连接计数减少1
//                var um_key = options.UM_CONNECTMAP_KEY_PREFIX;
//                if (connectMap[um_key] && connectMap[um_key] > 0) {
//                    connectMap.reduce(um_key);
//                }
//
//                if (clientInfo.line_state != String(0)) {
//                    setTimeout("$.jqEBMessenger.EBUM.ebwebum_hb()", 2000);
//                }
//                break;
//            case apiMap["ebwebcm.hb"]:
//                //当前心跳连接计数减少1
//                var cm_key = options.CM_CONNECTMAP_KEY_PREFIX + req.url;
//                if (connectMap[cm_key] && connectMap[cm_key] > 0) {
//                    connectMap.reduce(cm_key);
//                }
//
//                if (req.pv) {
//                    req.pv = $.Base64.decode(req.pv);
//                    req.pv = $.evalJSON(req.pv);
//                    var call_info = chatMap.callInfoByChatId(req.pv.chat_id);
//                    if (call_info) {
//                        setTimeout("$.jqEBMessenger.EBCM.ebwebcm_hb('" + call_info.cm_url + "'," + clientInfo.my_uid + ")", 2000);
//                    }
//                    else {
//                        logjs_info("processNetworkError call_info not found for chat_id=" + req.pv.chat_id);
//                        logjs_info("processNetworkError chatMap===\n" + $.toJSON(chatMap));
//                    }
//                }
//                break;
//        }
    };

    /**
     * 处理接收到的数据
     * @param req 请求参数
     * @param jsonString 结果数据
     */
    processor.processReceiveData = function (req, jsonString) {
        var jsonData = json_parse(jsonString);
//        if (req.api != apiMap["ebwebum.loadorg"])
//            logjs_info("return pv:\n" + jsonString);
//        else {
//            logjs_info("loadorg 信息不打印");
//        }

        var try_times = 0;//重试次数，必须定义变量
        var pv = null;
        var callInfo = null;
        var caKey = null;
        var callAccountHandle = null;
        var eventHandle = jqEBM.eventHandle;
        var callback = jqEBM.hashMap[jqEBM.MESSAGE_CALLBACK_PREFIX + req.tag];
        
        switch (req.api) {
        	case apiMap["ebwebum.getuserinfo"]:
	        case apiMap["ebwebum.addresource"]:
	        case apiMap["ebwebum.deleteresource"]:
	        case apiMap["ebwebum.getresource"]:
	        case apiMap["ebwebum.loadresource"]:
	        case apiMap["ebwebum.editresource"]:
	        case apiMap["ebwebum.loadresources"]:
	        
		        if (jsonData.code != statecodeMap["EB_STATE_OK"]) {
		        	//logjs_info(jsonData.error);
		        }
            	
	        	if(callback)
	        		callback(jsonData.code, jsonData);
	        	break;
//            case apiMap["ebweblc.logonvisitor"]:
//                if (jsonData.code != statecodeMap["EB_STATE_OK"]) {
//                    logjs_info(jsonData.error);
//                    if(callback)
//                        callback(errCodeMap.LOGON_FAILURE);
//
//                    //处理code=60,待补充
//                    break;
//                }
//                clientInfo.user_type = 'visitor';
//                clientInfo.logon_type = jsonData.logon_type;
//
//                if ( $.ebMsg.options.HTTP_PREFIX =="https://")
//                    clientInfo.um_url = options.HTTP_PREFIX + jsonData.urls;
//                else
//                    clientInfo.um_url = options.HTTP_PREFIX + jsonData.url;
//
//                clientInfo.um_appname = jsonData.appname;
//                clientInfo.my_account = jsonData.account;
//                clientInfo.my_uid = jsonData.uid;
//                clientInfo.my_um_online_key = jsonData.online_key;
//                clientInfo.acm_key = jsonData.acm_key;
//                clientInfo.username = jsonData.username;
//                clientInfo.description = jsonData.description;
//                clientInfo.setting = jsonData.setting;
//                clientInfo.default_member_code = jsonData.default_member_code;
//
//                jqEBM.online(callback);
//                break;

//            case apiMap["ebweblc.logonaccount"]:
//                if (jsonData.code != statecodeMap["EB_STATE_OK"]) {
//                    logjs_info(jsonData.error);
//                    if(callback)
//                        callback(errCodeMap.LOGON_FAILURE);
//
//                    //处理code=60,待补充
//                    break;
//                }
//
//                //判断是否https
//                var httpsFlag = false;
//                if (options.HTTP_PREFIX =="https://")
//                    httpsFlag = true;
//
//                clientInfo.user_type = 'normal';
//                clientInfo.logon_type = jsonData.logon_type;
//                clientInfo.um_url = options.HTTP_PREFIX + httpsFlag?jsonData.urls:jsonData.url;
//                clientInfo.um_appname = jsonData.appname;
//                clientInfo.my_account = jsonData.account;
//                clientInfo.my_uid = jsonData.uid;
//                clientInfo.my_um_online_key = jsonData.online_key;
//                clientInfo.acm_key = jsonData.acm_key;
//                clientInfo.username = jsonData.username;
//                clientInfo.description = jsonData.description;
//                clientInfo.setting = jsonData.setting;
//                clientInfo.default_member_code = jsonData.default_member_code;
//
//                jqEBM.online(callback);
//                break;

//            case apiMap["ebwebum.online"]:
//                if (jsonData.code != statecodeMap["EB_STATE_OK"]) {
//                    logjs_info(jsonData.error);
//                    if(callback)
//                        callback(errCodeMap.ONLINE_FAILURE);
//                    break;
//                }
//
//                pv = $.evalJSON(req.pv);
//
//                if (msgcodeMap['EB_WM_LOGON_SUCCESS'] == jsonData.msg) {
//                    clientInfo.line_state = pv.line_state;
//
////                        if(callback)
////                            callback(errCodeMap.OK, clientInfo);
//
//                    //加载组织架构
////                        if (clientInfo.loadorg_state == "0") {
////                            clientInfo.loadorg_state = -1;
//                      EBUM.ebwebum_loadorg(callback);
////                        }
//
//                    //um心跳
//                    EBUM.ebwebum_hb();
//                }
//                break;

//            case apiMap["ebwebum.offline"]:
//                if (jsonData.code != statecodeMap["EB_STATE_OK"]) {
//                    logjs_info(jsonData.error);
//
//                    if(callback)
//                        callback(errCodeMap.OFFLINE_FAILURE);
//
//                    break;
//                }
//
//                if (msgcodeMap['EB_WM_LOGOUT'] == jsonData.msg) {
//                    logjs_info("下线成功");
//                    clientInfo.line_state =0;
//                    //重置属性
//                    //clientInfo.reset();
//
//                    if(callback)
//                        callback(errCodeMap.OK);
//                }
//                break;

//            case apiMap["ebwebum.callaccount"]:
//                if (jsonData.code == statecodeMap["EB_STATE_TIMEOUT_ERROR"]) {//timeout
//                    //待定处理
//                    //后台正常情况下不会出现
//                    if(callback)
//                        callback(errCodeMap.TIMEOUT);
//                    break;
//                }
//
//                //对方用户账号不存在
//                if (jsonData.code == statecodeMap["EB_STATE_ACCOUNT_NOT_EXIST"]) {
//                    if(callback)
//                        callback(errCodeMap.CALLACCOUNT_NOT_EXIST);
//                    break;
//                }
//
//                if (jsonData.code == statecodeMap["EB_STATE_PARAMETER_ERROR"]) {
//                    if(callback)
//                        callback(errCodeMap.PARAMETER_ERROR);
//                    break;
//                }
//
//                if (!jsonData.call_info) {
//                    logjs_info("系统返回call_info是空值");
//                    if(callback)
//                        callback(errCodeMap.createEntity("系统返回call_info是空值"));
//                    break;
//                }
//
//                if (jsonData.error) {
//                    logjs_info(jsonData.error);
//                    if(callback)
//                        callback(errCodeMap.CALLACCOUNT_FAILURE);
//                    break;
//                }
//
//                //缓存当次回调函数
//
//                caKey = jsonData.call_info.call_id + "-" + jsonData.call_info.from_uid;
//                logjs_info("call account caKey = " + caKey);
//                jqEBM.callAccountHandleMap[caKey] = callback;
//                //对方正在被呼叫状态设置为否
//                jqEBM.updateAccountBecalling(jsonData.call_info.call_id, jsonData.call_info.from_uid, false);
//
//                if (msgcodeMap['EB_WM_CALL_ALERTING'] == jsonData.msg) {
//                    this.processCallAlerting(req, jsonData);
//                }
//                break;

//            case apiMap["ebwebum.loadcontact"]:
//
//                break;

//            case apiMap["ebwebum.loadorg"]:
//                this.processLoadorg(jsonData);
// //               clientInfo.loadorg_state = 1;
//
//                //加载离线信息
//                EBUM.ebwebum_loadinfo(callback);
//
//                break;

//            case apiMap["ebwebum.loadinfo"]:
//                this.processLoadinfo(req, jsonData);
//                if(callback)
//                    callback(errCodeMap.OK, clientInfo);
//                break;
//
//            case apiMap["ebwebum.hangup"]:
//                if (jsonData.error)
//                    logjs_info(jsonData.error);
//                break;
//
//            case apiMap["ebwebcm.enter"]://本客户端cm_enter
//                if (jsonData.code != statecodeMap["EB_STATE_OK"]) {
//                    logjs_info(jsonData.error);
//                    if(eventHandle)
//                        eventHandle.onError(errCodeMap.createEntity("系统内部错误，cm_enter失败"));
//                    break;
//                }
//
//                if (msgcodeMap['CR_WM_ENTER_ROOM'] == jsonData.msg) {
//                    pv = $.evalJSON(req.pv);
//                    callInfo = chatMap[pv.call_id];
//                    EBCM.ebwebcm_hb(callInfo.cm_url, clientInfo.my_uid /*pv.chat_id*/);
//                }
//                break;
//
//            case apiMap["ebwebcm.exit"]:
//                if (jsonData.code != statecodeMap["EB_STATE_OK"]) {
//                    logjs_info(jsonData.error);
//                    if(eventHandle)
//                        eventHandle.onError(errCodeMap.createEntity("系统内部错误，cm_exit失败"));
//                    break;
//                }
//
//                if (msgcodeMap['CR_WM_EXIT_ROOM'] == jsonData.msg) {
//                    logjs_info("退出会话");
//                    pv = $.evalJSON(req.pv);
//
//                    callInfo = jqEBM.chatMap.callInfoByChatId(pv.chat_id);
//                    this.processMyCMExit(callInfo.call_id);
//                }
//                break;
//
//            case apiMap["ebwebcm.sendrich"]:
//                if (jsonData.error) {
//                    logjs_info(jsonData.error);
//                    if(callback)
//                        callback(errCodeMap.createEntity(jsonData.error));
//                }
//
//                //chat_id error会话已失效
//                if (jsonData.code == statecodeMap["EB_STATE_UNAUTH_ERROR"]) {
//                    logjs_info("ebwebcm.sendrich chat_id error, 即将尝试重新载入会话");
//                    pv = $.evalJSON(req.pv);
//                    callInfo = chatMap.callInfoByChatId(pv.chat_id);
//                    jqEBM.reloadChat(callInfo.call_id, null, callInfo.call_key);
//
//                    if(callback)
//                        callback(errCodeMap.CHAT_INVALID);
//                    break;
//                }
//
//                if (jsonData.code == statecodeMap["EB_STATE_OK"] && msgcodeMap["CR_WM_SEND_RICH"] == jsonData.msg) {
//                    logjs_info('消息发送成功');
//                    if(callback)
//                        callback(errCodeMap.OK);
//                }
//                break;
//
//            case apiMap["ebwebcm.sendfile"]://发送文件申请
//                if (jsonData.error) {
//                    logjs_info(jsonData.error);
//                    pv = $.evalJSON(req.pv);
//                    callInfo = chatMap.callInfoByChatId(pv.chat_id);
//                    if(callback)
//                        callback(errCodeMap.SENDFILE_REQUEST_FAILURE);
//                }
//                break;

//            case apiMap["ebwebum.hb"]:
//                //当前心跳连接计数减少1
//                var um_key = jqEBM.UM_CONNECTMAP_KEY_PREFIX;
//                logjs_info("processor connectMap[" + um_key + "]=" + connectMap[um_key]);
//                if (connectMap[um_key] && connectMap[um_key] > 0) {
//                    connectMap.reduce(um_key);
//                }
//
//                //没有返回数据
//                if (!jsonData) {
//                    logjs_info("um no return data");
//                }
////                    //有报错信息
////                    if (jsonData.error && jsonData.error != "timeout") {
////                        logjs_info("ebwebum.hb code=" + jsonData.code + ", error=" + jsonData.error);
////                        if(eventHandle)
////                            eventHandle.onError(errCodeMap.createEntity("um.hb error=" + jsonData.error));
////                    }
//
//                //sid uid error sessionid失效
//                if (jsonData.code == statecodeMap["EB_STATE_UNAUTH_ERROR"]) {
//                    //用户session失效
//                    logjs_info("sid uid error");
//                    //jqEBM.reloadLogon();
//
//                    if(eventHandle)
//                        eventHandle.onDisconnect();
//                    break;
//                }
//
//                //业务轮询结束返回，没有数据
//                if (jsonData.code == statecodeMap["EB_STATE_TIMEOUT_ERROR"]) {
//                    //没有数据
//                    if (jsonData.error == "timeout") {
//                        logjs_info("um_hb 没有数据，长轮询断开");
//                    }
//
//                    //对方不应答
//                    if (msgcodeMap['EB_WM_CALL_ERROR'] == jsonData.msg) {
//                        logjs_info("um_hb 对方没有应答呼叫");
//                        //$("#loading_message").append("<font color='red'>对方没有应答呼叫</font>");
//                        //待补充
//                        processor.processCallTimeout(req, jsonData);
//
//                        caKey = jsonData.call_info.call_id + "-" + jsonData.call_info.from_uid;
//                        logjs_info("umhb caKey = " + caKey);
//                        callAccountHandle = jqEBM.callAccountHandleMap[caKey];
//                        if(callAccountHandle)
//                            callAccountHandle(errCodeMap.CALLACCOUNT_NO_RESPONSE);
//
//                        delete jqEBM.callAccountHandleMap[caKey];
//                    }
//                }
//
//                //call error 对方拒绝呼叫
//                if (jsonData.code == statecodeMap["EB_STATE_USER_BUSY"]) {
//                    if (msgcodeMap['EB_WM_CALL_BUSY'] == jsonData.msg) {
//                        logjs_info("对方拒绝呼叫");
//                        //$("#loading_message").append("<font color='red'>对方拒绝呼叫</font>");
//                        //待补充
//                        processor.processCallReject(req, jsonData);
//
//                        caKey = jsonData.call_info.call_id + "-" + jsonData.call_info.from_uid;
//                        logjs_info("caKey = " + caKey);
//                        callAccountHandle = jqEBM.callAccountHandleMap[caKey];
//                        if(callAccountHandle)
//                            callAccountHandle(errCodeMap.CALLACCOUNT_REJECT);
//
//                        delete jqEBM.callAccountHandleMap[caKey];
//                    }
//                }
//
//                //有业务数据
//                if (msgcodeMap['EB_WM_CALL_CONNECTED'] == jsonData.msg) {
//                    //检查是否call_id已变更
//                    var new_call_info = jsonData.call_info;
//                    if (new_call_info.group_code == "0"  //只针对非群组会话
//                        && new_call_info.oc_id == "0") {
//                        var uid = new_call_info.from_uid;
//                        var exist_call_id = uidCallidMap[uid];
//                        logjs_info("EB_WM_CALL_CONNECTED exist_call_id=" + exist_call_id + ", new_call_id=" + new_call_info.call_id);
//                        if (exist_call_id && exist_call_id != new_call_info.call_id) {//有变更
//                            new_call_info.oc_id = exist_call_id;
//                            logjs_info("call_id 已发生变更, 设置call_info.oc_id=" + new_call_info.oc_id);
//                        }
//                    }
//                    processor.processCallConnected(req, jsonData);
//                }
//
//                //用户在另一客户端登录
//                if (msgcodeMap['EB_WM_ONLINE_ANOTHER_UM'] == jsonData.msg) {
//                    processor.processOnlineAnother(req, jsonData, clientInfo.my_uid);
//                }
//
//                //对方um主动挂断
//                if (msgcodeMap['EB_WM_CALL_HANGUP'] == jsonData.msg) {
//                    var local_call_info = chatMap[jsonData.call_info.call_id];
//                    if (jsonData.hangup == "0") {
//                        jqEBM.reloadChat(jsonData.call_info.call_id, jsonData.call_info.from_uid, local_call_info.call_key);
//                    } else if (jsonData.hangup == "1") {
//                        local_call_info.hangup = true;
//                        EBCM.ebwebcm_exit(jsonData.call_info.call_id, clientInfo.my_uid);
//                        EBUM.ebwebum_hangup(jsonData.call_info.call_id, false);
//                    }
//                }
//
//                if (clientInfo.line_state != "0") {
//                    EBUM.ebwebum_hb();
//                }
//                break;

//            case apiMap["ebwebcm.hb"]:
//                //当前心跳连接计数减少1
//                var cm_key = jqEBM.CM_CONNECTMAP_KEY_PREFIX + req.url;
//                logjs_info("connectMap[" + cm_key + "]=" + connectMap[cm_key]);
//                if (connectMap[cm_key] && connectMap[cm_key] > 0) {
//                    connectMap.reduce(cm_key);
//                }
//
//                //没有返回数据
//                if (!jsonData) {
//                    logjs_info("cm no return data");
//                }
//                //有报错信息
//                if (jsonData.error) {
//                    logjs_info("ebwebcm.hb code=" + jsonData.code + ", error=" + jsonData.error);
//                }
//
//                pv = $.evalJSON(req.pv);
//                callInfo = chatMap.callInfoByChatId(jsonData.chat_id);
//
//                //本地缓存找不到会话
//                if (!callInfo) {
//                    logjs_info("processReceiveData ebwebcm.hb call_info not found for chat_id=" + jsonData.chat_id);
//                    logjs_info("processReceiveData ebwebcm.hb chatMap===\n" + $.toJSON(chatMap));
//                    break;
//                }
//
//                //chat_id error 会话失效
//                if (jsonData.code == statecodeMap["EB_STATE_UNAUTH_ERROR"]) {
//                    //待定
//                    //fire 聊天会话失效事件
//                    logjs_info("ebwebcm.hb chat_id error, 即将尝试重新载入会话");
//                    jqEBM.reloadChat(callInfo.call_id, null, callInfo.call_key);
//                    break;
//                }
//
//                //业务轮询结束返回，没有数据
//                if (jsonData.code == statecodeMap["EB_STATE_TIMEOUT_ERROR"] && jsonData.error == "timeout") {
//                    logjs_info("cm_hb 没有数据，长轮询断开");
//                    EBCM.ebwebcm_hb(callInfo.cm_url, clientInfo.my_uid /*callInfo.chat_id*/);
//                    break;
//                }
//
//                switch (parseInt(jsonData.msg, 10)) {
//                    //对方离开会话
//                    case msgcodeMap["CR_WM_USER_EXIT_ROOM"]:
//                        logjs_info(jsonData.chat_id + ", exit room, uid:" + jsonData.from_uid);
//                        logjs_info($.toJSON(callInfo));
//
//                        this.processUserCMExit(req, jsonData);
//
//                        if (jsonData.hangup == "0") {
//                            //待定
//                            //fire 对方离开会话事件
//                            jqEBM.reloadChat(callInfo.call_id, jsonData.from_uid, callInfo.call_key);
//                        }
//                        break;
//
//                    //用户在另一客户端登录
//                    case msgcodeMap["EB_WM_ONLINE_ANOTHER_CM"]:
//                        this.processOnlineAnother(req, jsonData, clientInfo.my_uid);
//                        break;
//
//                    //对方进入会话事件
//                    case msgcodeMap["CR_WM_USER_ENTER_ROOM"]:
//                        logjs_info(jsonData.chat_id + ", enter room, uid:" + jsonData.from_uid);
//                        this.processUserCMEnter(req, jsonData);
//
//                        caKey = callInfo.call_id + "-" + jsonData.from_uid;
//                        logjs_info("CR_WM_USER_ENTER_ROOM caKey = " + caKey);
//                        callAccountHandle = jqEBM.callAccountHandleMap[caKey];
//                        if(callAccountHandle)
//                            callAccountHandle(errCodeMap.OK, {callInfo: callInfo, accountInfo: jqEBM.accountInfoMap[jsonData.from_uid]});
//
//                        delete jqEBM.callAccountHandleMap[caKey];
//                        break;
//
//                    //长时间没有对话，被服务器主动注销会话
//                    case msgcodeMap["CR_WM_EXIT_ROOM"]:
//                        //fire 聊天会话长事件没有业务内容，服务器主动注销会话
//                        if (jsonData.code = statecodeMap["EB_STATE_TIMEOUT_ERROR"]) {
//                            logjs_info("长时间没有对话，被服务器主动注销会话");
//                            //设置下线状态，防止下一个触发下一个um长连接
//                            //clientInfo.line_state = 0;
////                                //下线
////                                EBUM.ebwebum_offline();
//                            callInfo.hangup = true;
//                            EBUM.ebwebum_hangup(callInfo.call_id, true);
//                            this.processMyCMExit(callInfo.call_id);
//
//                            return;//直接返回，不再触发cm_hb长连接
//                        }
//                        break;
//
//                    //接收到消息
//                    case msgcodeMap['CR_WM_RECEIVE_RICH']:
//                        logjs_info("receive message, uid:" + jsonData.from_uid + ", from_account:" + jsonData.from_account + ", private=" + jsonData.private);
////                            var htmlStr = processor.processRichInfo(jsonData.rich_info);
////                            logjs_info(htmlStr);
//                        if(eventHandle) {
//                            eventHandle.onReceiveMessage(callInfo, jqEBM.accountInfoMap[jsonData.from_uid], jqEBM.processRichInfo(jsonData.rich_info));
//                        }
//                        break;
//
//                    //文件离线成功
//                    case msgcodeMap["CR_WM_SENDING_FILE"]:
//                        this.processSendingFile(req, jsonData);
//                        break;
//
//                    //对方接收文件成功
//                    case msgcodeMap["CR_WM_SENT_FILE"]:
//                        this.processSentFile(req, jsonData);
//                        break;
//
//                    //对方接收文件成功
//                    case msgcodeMap["CR_WM_CANCEL_FILE"]:
//                        this.processCancelReceiveFile(req, jsonData);
//                        break;
//
//                }
//
//                if (!jsonData.hangup || jsonData.hangup == "0") {//非挂断
//                    EBCM.ebwebcm_hb(callInfo.cm_url, clientInfo.my_uid /*callInfo.chat_id*/);
//                }
//
//                break;

        }
    }
})(jQuery, window);
