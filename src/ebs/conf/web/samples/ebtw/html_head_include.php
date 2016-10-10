<meta charset="UTF-8">
<meta http-equiv="content-type" content="text/html" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<!-- js全局变量 -->
<script type="text/javascript">
<?php 
	echo 'var ebRestVersion = "'.EB_REST_VERSION.'";'."\n";
	echo 'var ebStaticVersion = "'.EB_STATIC_VERSION.'";'."\n";
	echo 'var user_id = "'.$_SESSION[USER_ID_NAME].'";'."\n";
	echo 'var logon_type = "'.$_SESSION[EB_LOGON_TYPE_NAME].'";'."\n";
	echo 'var acm_key = "'.$_SESSION[EB_UM_ACM_KEY_NAME].'";'."\n";
	echo 'var ebHttpPrefix = "'.EB_HTTP_PREFIX.'";'."\n";
	echo 'var lcServerAddr = "'.EB_IM_LC_SERVER_USED_BY_CLIENT.'";'."\n";
	echo 'var umServerAddr = "'.$_SESSION[EB_UM_ADDR_NAME].'";'."\n";
	echo 'var umServerAddrSSL = "'.$_SESSION[EB_UM_ADDR_SSL_NAME].'";'."\n";
	echo 'var umEbsid = "'.$_SESSION[EB_UM_SID_NAME].'";'."\n";
	echo 'var ajaxTimeout = '.AJAX_TIMEOUT.';'."\n";
	echo 'var ajaxUploadTimeout = '.AJAX_UPLOAD_TIMEOUT.';'."\n";
?>
</script>

<!-- jquery -->
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/jquery/jquery-1.9.1.min.js?v=1"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/jquery/jquery.browser.min.js?v=1"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/jquery/jquery.mousewheel-3.1.13.min.js?v=1"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/jquery/jquery.form.js?v=1"></script>

<!-- jquery ui -->
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/jquery/jquery-ui-1.10.4.min.js?v=1"></script>
<link rel="stylesheet" type="text/css" href="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/jquery/jquery-ui-1.10.4.min.css?v=1" />
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/jquery/jquery-ui-slider-pips-1.11.3.js?v=1"></script>
<link rel="stylesheet" type="text/css" href="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/jquery/jquery-ui-slider-pips-1.11.3.css?v=1" />

<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>js/common.js?v=9"></script>

<!-- json_parse -->
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/json_parse.js?v=1"></script>

<!-- mCustomScrollbar -->
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/mCustomScrollbar/jquery.mCustomScrollbar.min.js?v=1"></script>
<link rel="stylesheet" type="text/css" href="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/mCustomScrollbar/jquery.mCustomScrollbar.css?v=1" />

<!-- bootstrap -->
<link rel="stylesheet" type="text/css" href="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/bootstrap/css/bootstrap.min.css?v=1" />
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/bootstrap/js/bootstrap.min.js?v=1"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/bootstrap/js/bootstrap-hover-dropdown.min.js?v=1"></script>
<!--[if lt IE 9]>
	<script src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/bootstrap/plugins/ie/html5shiv.js?v=1"></script>
	<script src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/bootstrap/plugins/ie/respond.js?v=1"></script>
<![endif]-->
<!--[if lt IE 8]>
	<script src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/bootstrap/plugins/ie/json2.js?v=1"></script>
<![endif]-->

<!-- font-awesome -->
<link rel="stylesheet" type="text/css" href="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/fontAwesome/css/font-awesome.min.css?v=1" media="all" />

<!-- dtGrid -->
<link rel="stylesheet" type="text/css" href="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/dtGrid/jquery.dtGrid.css?v=2" />
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/dtGrid/jquery.dtGrid.js?v=2"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/dtGrid/i18n/en.js?v=2"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/dtGrid/i18n/zh-cn.js?v=2"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/dtGrid/i18n/zh-tw.js?v=2"></script>

<!-- datetimepicker -->
<link rel="stylesheet" type="text/css" href="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/datetimepicker/bootstrap-datetimepicker.min.css?v=1" />
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/datetimepicker/bootstrap-datetimepicker.js?v=1"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/datetimepicker/locales/bootstrap-datetimepicker.zh-CN.js?v=1"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/datetimepicker/locales/bootstrap-datetimepicker.zh-TW.js?v=1"></script>

<!-- select2 -->
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/select2/js/select2.min.js?v=1"></script>
<link rel="stylesheet" type="text/css" href="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/select2/css/select2.min.css?v=2" />

<!-- laytpl -->
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/laytpl.js?v=1"></script>

<!-- layer -->
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/layer/layer.js?v=1"></script>

<!-- zoom -->
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/zoom/zoom.min.js?v=2"></script>
<link rel="stylesheet" type="text/css" href="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/zoom/zoom.css?v=2" />

<script type="text/javascript">
layer.config({
  extend: 'extend/layer.ext.js'
});
</script>

<!-- ajaxfileupload -->
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>js/ajaxfileupload.js?v=1"></script>

<!-- 恩布WebIM跨域框架 -->
<script type="text/javascript">
var restApiReady = false; //标记RestApi访问渠道是否建立完毕
var pageStarted = false; //标记页面业务流程是否已经启动

//RestAPI访问架构加载完毕回调函数
function eb_restapi_fr_load_complete() {
    var jqEBM = $.jqEBMessenger;
    var options = jqEBM.options;
    var fn = jqEBM.fn;
	
    //控制连接到IM LC服务
    options.DOMAIN_URL = lcServerAddr;

   //iframe子页面日志窗口显示控制
    //var IFRAME_DEBUG = $.ebfn.getQueryStringRegExp(document.location.href, 'iframe_debug');
    var IFRAME_DEBUG = 'false';
    if(IFRAME_DEBUG && (IFRAME_DEBUG=='true' || IFRAME_DEBUG=='TRUE')) {
        options.IFRAME_DEBUG =IFRAME_DEBUG;
    }
    //http加密协议
    //var https_flag = $.ebfn.getQueryStringRegExp(document.location.href, 'https_flag');
    var https_flag = ebHttpPrefix=='https'?'true':'false';
    if(https_flag && (https_flag=='true' || https_flag=='TRUE')) {
        //http协议头
        options.HTTP_PREFIX ="https://";
    }
	
    var try_times = 0;//必须定义变量
    //载入跨域执行页面
    var url =options.HTTP_PREFIX + options.DOMAIN_URL + "/server_plugin_webim/iframe_domain.html?fr_name="
        + fn.domainURI(options.HTTP_PREFIX + options.DOMAIN_URL) + (options.IFRAME_DEBUG?"&debug=true":"") + "&v=" + jqEBM.STATIC_VERSION;
    fn.load_iframe(url,
        try_times,
        //访问渠道建立完毕
        function() {
    		restApiReady = true;
    		logjs_info('pageStarted='+pageStarted+', restApiReady='+restApiReady+', typeof start_page_after_restapi_ready='+typeof start_page_after_restapi_ready);
    		
            if (!pageStarted && typeof start_page_after_restapi_ready=='function') {
            	logjs_info('RestAPI ready...');
            	pageStarted = true;
            	start_page_after_restapi_ready();
            }
    	});
}

//用户登录(或自动登录)回调函数
function entboost_on_user_logon_success(uid) {
	logjs_info('entboost_on_user_logon_success=>uid:'+uid);
}
/* 用户服务器重启，网络超时等用户下线，调用 chrome 内嵌浏览器函数
      code=1 服务器停止、维护状态中
      code=2 服务器业务已经转移，需要重新登录
      code=3 服务器已经重启，需要重新登录
      code=4 服务器连接超时，有可能是本地网络问题
*/
function entboost_on_user_off_line(uid, code) {
	logjs_info('entboost_on_user_off_line=>code:'+code);
}

$(document).ready(function() {
	//修复textarea在内嵌Chrome浏览器的问题
	var checkKeys = ['Backspace', 'PageUp', 'PageDown', 'End', 'Home', 'Left', 'Up', 'Right', 'Down', 'Del']; //待定：等待进一步补充
	$(document).on('keydown', 'textarea', function(e) {
		keyObj = ControlKeyMap[keyOfEvent(e)];
		if (this.value.length==0 && window.entboost_on_first_keydown && (keyObj==undefined || $.inArray(keyObj.name, checkKeys)==-1)) {
			var cors = getAbsPoint(this);
			window.entboost_on_first_keydown(cors[0], cors[1], cors[2], cors[3]);
			logjs_info('entboost_on_first_keydown: '+cors);
		}
	});
});
</script>

<!-- ebtw -->
<link rel="stylesheet" type="text/css" id="ebtw_server_url" href="<?php if (isset($relative_path)) echo $relative_path; ?>css/ebtw.css?v=36" /><!-- 本行的版本号很特殊，一定要写数字 -->
<link rel="stylesheet" type="text/css" href="<?php if (isset($relative_path)) echo $relative_path; ?>css/ebtw2.css?v=38" />
<link rel="stylesheet" type="text/css" href="<?php if (isset($relative_path)) echo $relative_path; ?>css/ebtw3.css?v=35" />

<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>js/ptr_dictionary.js?v=22"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>js/eb_restapi_fr.js?v=21"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>js/ebtw.js?v=39"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>js/date_algorithm.js?v=28"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>js/ptr.js?v=45"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>js/ptr2.js?v=40"></script>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>js/sidepage.js?v=22"></script>

