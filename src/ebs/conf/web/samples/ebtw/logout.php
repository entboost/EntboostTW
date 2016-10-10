<?php
//即使是注销时，也必须首先开始会话才能访问会话变量
session_start();

//使用一个会话变量检查登录状态
if(isset($_SESSION[USER_ID_NAME])){
	//要清除会话变量，将$_SESSION超级全局变量设置为一个空数组
// 	$_SESSION = array();
	//如果存在一个会话cookie，通过将到期时间设置为之前一周从而将其删除
	if(isset($_COOKIE[session_name()])){
		setcookie(session_name(),'',time() - COOKIE_EXPIRED_TIME);
	}
	//清除内存的SESSION变量
	session_unset();
	//删除当前用户对应的session文件以及释放session ID
	session_destroy();
}

//location首部使浏览器重定向到另一个页面
$home_url = 'login.php';
header('Location:'.$home_url);
