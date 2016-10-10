;(function($, window) {
    var jqEBM = $.jqEBMessenger;

    //客户端信息
    $.extend(jqEBM, { clientInfo: {
        user_type: '',
        logon_type: '',
        um_url: '',
        um_appname: '',
        my_account: '',
        my_uid: 0,
        my_um_online_key: '',
        acm_key: '',
        username: '',
        description: '',
        setting: '',
        default_member_code: '',

        //um在线状态
        line_state: 0,
        //加载组织架构状态：0=未加载，-1=加载中，1=加载完毕
//            loadorg_state: 0,
        //标记是否有给出重call的链接
//            recall_flag: false,


        //被另外登录踢下线
        tickoff: false,

        //重置
        reset: function () {
            this.user_type = '';
            this.logon_type = '';
            this.um_url = '';
            this.um_appname = '';
            //		this.my_account ='';
            //		this.my_uid =0;
            this.my_um_online_key = '';
            //		this.acm_key ='';
            this.username = '';
            this.description = '';
            this.setting = '';
            this.default_member_code = '';

            this.line_state = 0;
            //this.loadorg_state =0;

            this.tickoff = false;
            }
        }
    });

    //自增ID
    $.extend(jqEBM, {staticId :1});

    jqEBM.generateId = function() {
        jqEBM.staticId++;
        return jqEBM.staticId;
    };

    //跨域请求参数缓存
    jqEBM.hashMap = {};

    //api对照表
    jqEBM.apiMap = {
        "ebweblc.logonvisitor": 1,
        "ebweblc.logonaccount": 2,
        
        "ebwebum.online": 11,
        "ebwebum.offline": 12,
        "ebwebum.callaccount": 13,
        "ebwebum.hb": 14,
        "ebwebum.loadcontact": 15,
        "ebwebum.loadorg": 16,
        "ebwebum.loadinfo": 17,
        "ebwebum.hangup": 18,
        
        "ebwebcm.enter": 21,
        "ebwebcm.exit": 22,
        "ebwebcm.sendtext": 23,
        "ebwebcm.sendrich": 24,
        "ebwebcm.sendfile": 25,
        "ebwebcm.hb": 26,
        
        "ebwebum.getuserinfo":40,
        
        "ebwebum.addresource":50,
        "ebwebum.deleteresource":51,
        "ebwebum.getresource":52,
        "ebwebum.loadresource":53,
        "ebwebum.editresource":54,
        "ebwebum.loadresources":55,
        
        "ebwebcm.upload":60,
    };

    //uid与call_id关系表(只针对于非群组)
    jqEBM.uidCallidMap = {};

    //账号信息
    jqEBM.createAccountInfo = function (uid) {
            var accountInfo = {};
            accountInfo.uid = uid;
            accountInfo.from_account = "";
            accountInfo.mobile = "";
            accountInfo.fInfo = {
                type: -1,
                empCode: "",
                name: "",
                mobile: "",
                telphone: "",
                email: "",
                title: "",
                departmentName: "",
                entpriseName: ""
            };
            return accountInfo;
    };

    //accountInfo对照表,uid是主键
    jqEBM.accountInfoMap = {};

    //会话信息
    jqEBM.createCallInfo = function () {
            var callInfo = {
                call_id: 0,
                accounts: {},
                //[{uid:123, offline:true, incall:true, becalling:false}] incall等于true表示在会话中，becalling表示正在被呼叫(callaccount)
                group_code: 0,
                //state ='unknown';
                cm_url: '',
                cm_appname: '',
                chat_id: 0,
                chat_key: '',
                hangup: false, //会话已被注销
                create_time: new Date(),
                call_key: null, //呼叫来源KEY，实现企业被呼叫限制

                /**
                 * 取会话中第一个人员账号信息
                 */
                firstAccount: function () {
                    for (var uid in callInfo.accounts) {
                        return callInfo.accounts[uid];
                    }
                },

                getUids: function () {
                    var uids =[];
                    for (var uid in callInfo.accounts) {
                        uids.push(uid);
                    }
                    return uids;
                },

                getAccounts: function () {
                    return callInfo.accounts;
                }
            };
            return callInfo;
        };

    //会话对照表
    jqEBM.chatMap = {
        callInfoByChatId: function (chat_id) {
            if (!chat_id)
                return null;
            var list = this.names();
            if (list) {
                for (var i = 0; i < list.length; i++) {
                    var callInfo = this[list[i]];
                    if (callInfo.chat_id && callInfo.chat_id == chat_id)
                        return callInfo;
                }
            }

            return null;
        },
        names: function () {
            var list = [];
            var i = 0;
            for (var key in this) {
                if (key != 'names' && key != 'callInfoByChatId') {
                    list[i] = key;
                    i++;
                }
            }
            return list;
        }
    };

    //um连接缓存表关键名
    jqEBM.UM_CONNECTMAP_KEY_PREFIX = "ebwebum.hb_";
        //cm连接缓存表关键名
    jqEBM.CM_CONNECTMAP_KEY_PREFIX = "ebwebcm.hb_";
    //连接缓存表
    jqEBM.connectMap = {
        increase: function (key) {//value增长1
            if (!this[key]) {
                this[key] = 1;
            } else {
                this[key]++;
            }
            //logjs_info("increase, [" + key + "]=" + this[key]);
        },
        reduce: function (key) {//value减少1
            if (this[key]) {
                this[key]--;
            }
           //logjs_info("reduce, [" + key + "]=" + this[key]);
        }
    };

    //msg code对照表
    jqEBM.msgcodeMap = {
        "EB_WM_LOGON_SUCCESS": 267, //10B online成功
        "EB_WM_ONLINE_ANOTHER_UM": 270, //同账号在另外一个客户端登录UM
        "EB_WM_ONLINE_ANOTHER_CM": 4402,  //同账号在另外一个客户端登录CM
        "EB_WM_LOGOUT": 271,
        "EB_WM_CALL_INCOMING": 513,
        "EB_WM_CALL_ALERTING": 514,
        "EB_WM_CALL_BUSY": 515,
        "EB_WM_CALL_HANGUP": 516,
        "EB_WM_CALL_ERROR": 517,
        "EB_WM_CALL_CONNECTED": 518,
        "CR_WM_ENTER_ROOM": 4353,  //本端cm_enter返回事件
        "CR_WM_EXIT_ROOM": 4354,  //本端cm_exit返回事件
        "CR_WM_USER_ENTER_ROOM": 4355, //对方cm_enter返回事件
        "CR_WM_USER_EXIT_ROOM": 4356, //对方cm_exit返回事件
        "CR_WM_SEND_RICH": 4369, //发送富文本信息返回事件
        "CR_WM_RECEIVE_RICH":4370, //接收到富文本信息
        "CR_WM_SENDING_FILE": 4371, //发送离线文件到服务器消息
        "CR_WM_SENT_FILE": 4372, //对方接收文件成功
        "CR_WM_CANCEL_FILE": 4373 //对方拒绝或取消接收文件
    };

    //state code对照表
    jqEBM.statecodeMap = {
    	"ABORT":-3,
    	"NETWORK_ERROR":-2,
    	"UNKNOWN_ERROR":-1,
        "EB_STATE_OK": 0,
        "EB_STATE_ERROR": 1,
        "EB_STATE_TIMEOUT_ERROR": 5,
        "EB_STATE_USER_BUSY": 8,
        "EB_STATE_UNAUTH_ERROR": 11,
        "EB_STATE_PARAMETER_ERROR": 15,
        "EB_STATE_FILE_ALREADY_EXIST": 18,
        "EB_STATE_FILE_BIG_LONG": 19,
        "EB_STATE_ACCOUNT_NOT_EXIST": 20,
        "EB_STATE_RES_NOT_EXIST": 53,
        "EB_STATE_NO_UM_SERVER": 60,
    };

    //upload_attachment缓存
    jqEBM.uploadAttachmentMap = {};

    //callAccount 回调函数缓存
    jqEBM.callAccountHandleMap = {};

    //图片mime type对照表
    jqEBM.pictureMimeTypeMap = {
        "image/gif": 1,
        "image/jpeg": 1,
        "image/pjpeg": 1,
        "image/bmp": 1,
        "image/png": 1,
        "image/x-png": 1
    };

    //api版本号
    jqEBM.API_VERSION = ebRestVersion;//"03";
    //静态资源版本号
    jqEBM.STATIC_VERSION = ebStaticVersion;
    //跨域调用引擎回调前缀
    jqEBM.MESSAGE_CALLBACK_PREFIX = 'callback_';
    //聊天服务名称
    jqEBM.APPNAME_CM = "POPChatManager";
    //调用最大超时时间(毫秒)
    //jqEBM.options.TIMEOUT = ajaxTimeout;

})(jQuery, window);

