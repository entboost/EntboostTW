<?php

/**
 * 统计中文字符串长度(只支持UTF-8编码)
 * @param $str 要计算长度的字符串
 */
function utf8_strlen($str)
{
	if(empty($str)) {
		return 0;
	}
	if(function_exists('mb_strlen')){
		return mb_strlen($str,'utf-8');
	} else {
		preg_match_all("/./u", $str, $ar);
		return count($ar[0]);
	}
}

/**
 * 中文字符截取，支持gb2312,gbk,utf-8,big5
 * @param string $str 要截取的字串
 * @param int $start 截取起始位置
 * @param int $length 截取长度
 * @param string $charset utf-8|gb2312|gbk|big5 编码
 */
function c_substr($str, $start, $length, $charset="utf-8")
{
	if(function_exists("mb_substr")) {
		if(mb_strlen($str, $charset) <= $length) 
			return $str;
		$slice = mb_substr($str, $start, $length, $charset);
	} else {
		$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312']  = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']     = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']    = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		
		preg_match_all($re[$charset], $str, $match);
		if(count($match[0]) <= $length)
			return $str;
		$slice = join("",array_slice($match[0], $start, $length));
	}

	return $slice;
}