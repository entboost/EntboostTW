<?php
//设置默认时区
date_default_timezone_set("PRC");
//格式化日期时间字符串
define('DATE_TIME_FORMAT', 'Y-m-d H:i:s');

define("WEB_ROOT", dirname(__FILE__) ); //设置当前文件(common.php)的路径所在目录为根目录
// define("DOC_ROOT", $_SERVER['DOCUMENT_ROOT']);

//访问恩布IM RestApi服务参数
define('EB_IM_LC_SERVER_USED_BY_SERVER', '127.0.0.1:80'); //lc服务地址-服务端使用
define('EB_IM_LC_SERVER_USED_BY_CLIENT', '192.168.1.98:80'); //lc服务地址-客户端使用

//define('EB_IM_LC_SERVER_USED_BY_SERVER', 'test-lc.entboost.com:808'); //lc服务地址-服务端使用
//define('EB_IM_LC_SERVER_USED_BY_CLIENT', 'test-lc.entboost.com:808'); //lc服务地址-客户端使用

define('EB_HTTP_PREFIX', 'http'); //访问恩布 RestApi使用的http协议: http 或 https
define('REST_VERSION_STR', '/rest.v03.'); //恩布 Rest Api版本访问字符串(用于PHP)
define('EB_REST_VERSION', '03'); //恩布Rest Api版本号
define('EB_STATIC_VERSION', '2016052606'); //访问恩布IM服务静态资源版本号(用于控制静态资源刷新)

//appid
define('EB_IM_APPID', '278573612921'); //appid
define('EB_IM_APPKEY', '54bd2191d854200eb8f0a94bf57272ae'); //appkey

//https证书路径
define("EB_CA_CERT", WEB_ROOT."/../ca/public.crt");

//同步锁文件根目录
$config_file_lock_path = WEB_ROOT."/../lock";

//定义日志配置
define('LOG_FILE_PATH', WEB_ROOT."/../logs"); //日志路径
define('LOG_FILE_PRE', 'ebtw_log_'); //日志文件名前缀
define('LOG_SWITCH', true); //日志开关
define('LOG_MAX_LEN', 1024*1024*5); //日志大小(字节)

//定义应用在线key保持文件
define('APP_ONLINE_KEY_VALID_FILE_PATH', WEB_ROOT."/../lock/app_online_key_valid.txt");
//定义应用在线key有效最大时长
define('APP_ONLINE_KEY_VALID_MAX_TIME', 60*60*24); //秒 - 24小时
//定义IM AP服务访问会话保持文件
define('AP_KEEP_ALIVE_FILE_PATH', WEB_ROOT."/../lock/ap_keep_alive.txt");
//定义AP会话保持的有效最大时长
define('AP_KEEP_ALIVE_MAX_TIME', 60*60*20); //秒 - 20小时

//SESSION文件保存路径
define('SESSION_SAVE_DIR', dirname(__FILE__)."/../session_save_dir/");
//SESSION在服务端保存周期
define('SESSION_EXPIRED_TIME', 60*60*24); //秒 - 24小时
//COOKIE保存周期
define('COOKIE_EXPIRED_TIME', 3600*24*1); //秒 - 1天

//用户已登录标记变量名
define('USER_LOGINED_NAME', 'USER_LOGINED');
//当前用户编号变量名
define('USER_ID_NAME', 'EBTW_USER_ID');
//当前用户账号变量名
define('USER_ACCOUNT_NAME', 'EBTW_USER_ACCOUNT');
//当前用户名称变量名
define('USER_NAME_NAME', 'EBTW_USER_NAME');

//保存登录类型logon_type的变量名
define('EB_LOGON_TYPE_NAME', 'EB_LOGON_TYPE');
//保存当前用户访问UM服务访问地址的变量名
define('EB_UM_ADDR_NAME', 'EB_UM_ADDR');
//保存当前用户访问UM服务访问地址(HTTPS)的变量名
define('EB_UM_ADDR_SSL_NAME', 'EB_UM_ADDR_SSL_');
//保存当前用户访问UM服务的令牌eb_sid的变量名
define('EB_UM_SID_NAME', 'EB_UM_SID');
//保存当前用户访问恩布资源服务的令牌acm_key的变量名
define('EB_UM_ACM_KEY_NAME', 'EB_UM_ACM_KEY');

//===request请求参数变量名===
//查询记录总数量
define('REQUEST_ORDER_BY', 'request_order_by');
//查询记录总数量
define('REQUEST_FOR_COUNT', 'request_for_count');
//获取最少字段
define('REQUEST_FETCH_MINIMUM', 'request_fetch_minimum');
//查询类型
define('REQUEST_QUERY_TYPE', 'request_query_type');
//查询全部记录时，默认返回最大记录数
define('MAX_RECORDS_OF_LOADALL', 1000);
//每页查询默认最大记录数，如请求参数没有指定就使用本值
define('MAX_RECORDS_OF_PER_PAGE', 20);
//当前第几页
define('CURRENT_PAGE_NAME', 'nowPage');
//每页返回最大记录数
define('PER_PAGE_NAME', 'pageSize');

//ajax普通请求超时时间
define('AJAX_TIMEOUT', 20*1000); //20秒
//ajax上传文件请求超时时间
define('AJAX_UPLOAD_TIMEOUT', 300*1000); //300秒

//===应用功能订购ID定义===
$SUB_IDS = array(
		0=>'1002300110', //工作台
		1=>'1002300111', //计划
		2=>'1002300112', //任务
		3=>'1002300113', //日报
		4=>'1002300114', //报告
);
