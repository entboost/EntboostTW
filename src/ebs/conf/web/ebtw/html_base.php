<?php
if (!isset($ECHO_MODE) || $ECHO_MODE=='html') {
	header("Content-type: text/html; charset=utf-8"); //设置Content-type和字符编码
	$ECHO_MODE='html'; //输出类型
} else {
	header("Content-type: application/json; charset=utf-8"); //设置Content-type和字符编码
	$ECHO_MODE='json'; //输出类型
}