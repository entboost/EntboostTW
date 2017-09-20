;(function ($, window) {
    var errCodeMap = $.ebMsg.errCodeMap = $.jqEBMessenger.errCodeMap = {

        getEntityByCode: function(code) {
           for(var name in this) {
               if(this[name].code == code) {
                   return this[name];
               }
           }
           return this["UNKNOWN"];
        },
        codeSeq:10000, //自定义code基数
        createEntity: function(msg) {
            return {
                code: this.codeSeq++,
                msg: msg
            };
        },
        
        ABORT:{code:-3, msg:"中止"},
        NETWORK_ERROR: {code:-2, msg:"网络错误"},
        UNKNOWN_ERROR: {code:-1, msg:"未知错误"},
        OK: {code:0, msg:"成功"},

        LOGON_FAILURE: {code:2, msg:"登录失败"},
        ONLINE_FAILURE: {code:3, msg:"设置在线失败"},
        OFFLINE_FAILURE: {code:4, msg:"下线失败"},
        TIMEOUT: {code:5, msg:"超时"},
        PARAMETER_ERROR: {code: 6, msg:"参数错误"},

        CALLACCOUNT_FAILURE: {code:11, msg:"呼叫对方失败"},
        CALLACCOUNT_NOT_EXIST: {code:12, msg:"被叫方不存在"},
        CALLACCOUNT_NO_RESPONSE: {code:13, msg:"对方没有应答"},
        CALLACCOUNT_REJECT: {code:14, msg:"对方拒绝通话"},

        CHAT_INVALID: {code:21, msg:"聊天会话失效，等待重新载入"},
        SESSION_INVALID: {code:22, msg:"当前用户会话失效，等待重登"},
        CALLID_INVALID: {code:23, msg:"填入的CallId不正确"},
        CALLID_NOT_EXIST: {code:24, msg:"CallId不存在"},
        NOBODY_IN_CALL: {code:25, msg:"会话中没有对话方"},
        CALL_NOT_READY: {code:26, msg:"会话没有准备好"},
        NOT_LOGON: {code:27, msg:"当前用户未登录"},

        CONTENT_TOO_LONG: {code: 30, msg:"内容过长"},
        SENDFILE_REQUEST_FAILURE: {code:31, msg:"请求发送文件失败"},
        UPLOAD_FILE_EMPTY: {code:32, msg:"没有指定上传文件"},
        UPLOAD_FILE_REJECT: {code:33, msg:"对方取消或拒绝接收文件"},
        
        RES_NOT_EXIST: {code:53, msg:"资源不存在"},

        //code=10000以上，自定义错误信息
    };

    $.ebMsg.eventHandle = $.jqEBMessenger.eventHandle =function() {
        function EventHandle() {
        }

//        //表情资源加载完毕
//        EventHandle.prototype.onLoadEmotions = function(emotions) {};
//        //组织架构加载完毕
//        EventHandle.prototype.onLoadEntArchitecture = function(entArchi) {};
//        //个人群组加载完毕
//        EventHandle.prototype.onLoadGroup = function(groups) {};
//        //联系人加载完毕
//        EventHandle.prototype.onLoadContact = function(contacts) {};
//
//        //接收到信息
//        EventHandle.prototype.onReceiveMessage = function (callInfo, accountInfo, richInfo) {};
//
//        //聊天会话结束
//        EventHandle.prototype.onTalkOver = function(callId) {};
//
//        //服务器断线
//        EventHandle.prototype.onDisconnect = function() {};
//
//        //聊天会话ID变更
//        EventHandle.prototype.onChangeCallId = function(existCallId, newCallId) {};
//
//        //其它错误
//        EventHandle.prototype.onError = function(error) {};

        return new EventHandle();
    }();

})(jQuery, window);