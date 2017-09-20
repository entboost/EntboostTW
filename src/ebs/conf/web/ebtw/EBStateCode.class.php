<?php

class EBStateCode
{
	/**
	 * 成功
	 * @var int
	 */
	static public $EB_STATE_OK		= 0;
	/**
	 * 其它错误
	 * @var int
	 */
	static public $EB_STATE_ERROR	= 1;
	/**
	 * 没有权限
	 * @var int
	 */
	static public $EB_STATE_NOT_AUTH_ERROR	= 2;
	/**
	 * 已经存在
	 * @var int
	 */
	static public $EB_STATE_ALEADY_EXIST_ERROR	= 3;	
	
	/**
	 * 未完成，等待继续
	 * @var int
	 */
	static public $EB_STATE_CONTINUE = 9999;
	
	/**
	 * 重复签到
	 * @var int
	 */
	static public $EB_STATE_ALEADY_SIGN_IN = 10001;
	/**
	 * 此刻不允许签到
	 * @var int
	 */
	static public $EB_STATE_DISABLE_SIGN_IN = 10002;
	/**
	 * 此刻不允许签退
	 * @var int
	 */
	static public $EB_STATE_DISABLE_SIGN_OUT = 10003;
}