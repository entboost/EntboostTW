<?php
/**
 * 定义考勤一周每天的值
 * @var array
 */
$WEEK_VALUES = array(/*周日*/"0"=>64, /*周一*/"1"=>1, /*周二*/"2"=>2, /*周三*/"3"=>4, /*周四*/"4"=>8, /*周五*/"5"=>16, /*周六*/"6"=>32);
/**
 * 定义考勤一周每天的名称
 * @var array
 */
$WEEK_NAMES = array("0"=>'周日', "1"=>'周一', "2"=>'周二', "3"=>'周三', "4"=>'周四', "5"=>'周五', "6"=>'周六');

//定义出勤状态
/**
 * 默认状态
 * @var int
 */
define('ATTEND_STATE_DEFAULT', 		0x0);
/**
 * 全勤(通常)
 * @var int
 */
define('ATTEND_STATE_NORMAL', 		0x1);
/**
 * 未签到
 * @var int
 */
define('ATTEND_STATE_UNSIGNIN', 	0x2);
/**
 * 未签退
 * @var int
 */
define('ATTEND_STATE_UNSIGNOUT',	0x4);
/**
 * 旷工(缺席)
 * @var int
 */
define('ATTEND_STATE_ABSENTEEISM', 	0x8);
/**
 * 迟到
 * @var int
 */
define('ATTEND_STATE_LATE', 		0x10);
/**
 * 早退
 * @var int
 */
define('ATTEND_STATE_LEFT_EARLY', 	0x20);
/**
 * 加班
 * @var int
 */
define('ATTEND_STATE_WORK_OVERTIME', 	0x40);
/**
 * 外勤
 * @var int
 */
define('ATTEND_STATE_WORK_OUTSIDE', 0x80);
/**
 * 请假
 * @var int
 */
define('ATTEND_STATE_FURLOUGH', 	0x100);
/**
 * 补签
 * @var int
 */
define('ATTEND_STATE_RESIGN', 	0x200);
/**
 * 审批通过标识
 * @var int
 */
define('ATTEND_STATE_APPROVE_PASS', 0x1000);


/**
 * 异常的考勤状态(复合状态)
 * @var int
 */
define('ATTENDANCE_STATE_ABNORMAL_GROUP', ATTEND_STATE_UNSIGNIN|ATTEND_STATE_UNSIGNOUT|ATTEND_STATE_ABSENTEEISM|ATTEND_STATE_LATE|ATTEND_STATE_LEFT_EARLY);
/**
 * 可认为是"非异常"的考勤状态(复合状态)，通过申请审批的考勤状态
 * @var int
 */
define('ATTENDANCE_STATE_NOT_ABNORMAL_GROUP', ATTEND_STATE_WORK_OVERTIME|ATTEND_STATE_WORK_OUTSIDE|ATTEND_STATE_FURLOUGH|ATTEND_STATE_RESIGN);


/**
 * 定义出勤状态字典
 * @var array
 */
$ATTENDANCE_STATE_ARRAY = array(ATTEND_STATE_DEFAULT=>'默认状态', ATTEND_STATE_NORMAL=>'全勤', ATTEND_STATE_UNSIGNIN=>'未签到', ATTEND_STATE_UNSIGNOUT=>'未签退'
	, ATTEND_STATE_ABSENTEEISM=>'旷工', ATTEND_STATE_LATE=>'迟到', ATTEND_STATE_LEFT_EARLY=>'早退', ATTEND_STATE_WORK_OVERTIME=>'加班'
	, ATTEND_STATE_WORK_OUTSIDE=>'外勤', ATTEND_STATE_FURLOUGH=>'请假', ATTEND_STATE_RESIGN=>'补签', ATTEND_STATE_APPROVE_PASS=>'审批通过'
);

/**
 * 定义考勤审批状态字典
 * @var array
 */
$ATTENDANCE_REQ_STATE_ARRY = array(0=>'默认状态', 1=>'进行中', 2=>'通过', 3=>'不通过', 4=>'回退');

//定义操作类型
/**
 * 查询应采取的操作："签到"或"签退"
 * @var int
 */
define("ACTION_TYPE_SIGN_QUERY", 0);
/**
 * 签到
 * @var int
 */
define("ACTION_TYPE_SIGN_IN", 1);
/**
 * 签退
 * @var int
 */
define("ACTION_TYPE_SIGN_OUT", 2);

/**
 * 时间段查询-非本人
 * @var int
 */
define("ACTION_TYPE_REQ_TIME_RANGE_OTHER", 9);
/**
 * 时间段查询-本人
 * @var int
 */
define("ACTION_TYPE_REQ_TIME_RANGE", 10);
/**
 * 提交考勤审批
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_REQ", 11);
/**
 * 考勤审批通过
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_REQ_PASS", 14);
/**
 * 考勤审批不通过
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_REQ_REJECT", 15);
/**
 * 考勤审批撤销
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_REQ_REVOKE", 16);


/**
 * 禁用或启用考勤规则
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_SETTING_DISABLE_ENABLE", 21);
/**
 * 删除考勤规则
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_SETTING_DELETE", 22);
/**
 * 保存考勤规则
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_SETTING_SAVE", 23);

/**
 * 禁用或启用考勤专员
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_USER_DEFINE_DISABLE_ENABLE", 31);
/**
 * 删除考勤专员
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_USER_DEFINE_DELETE", 32);
/**
 * 保存考勤专员
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_USER_DEFINE_SAVE", 33);

/**
 * 禁用或启用请假类型
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_LEAVE_TYPE_DISABLE_ENABLE", 41);
/**
 * 删除请假类型
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_LEAVE_TYPE_DELETE", 42);
/**
 * 保存请假类型
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_LEAVE_TYPE_SAVE", 43);

/**
 * 禁用或启用假期设置
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_HOLIDAY_DISABLE_ENABLE", 51);
/**
 * 删除假期设置
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_HOLIDAY_DELETE", 52);
/**
 * 保存假期设置
 * @var int
 */
define("ACTION_TYPE_ATTENDANCE_HOLIDAY_SAVE", 53);
