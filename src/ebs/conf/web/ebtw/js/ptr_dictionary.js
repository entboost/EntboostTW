/** 映射内容 */

var dictWeekName = {0:'星期天', 1:'星期一', 2:'星期二', 3:'星期三', 4:'星期四', 5:'星期五', 6:'星期六'};

//共用的
var dictImportant = {0:'普通', 1:'重要', 2:'紧急'}; //重要程度
var dictOpenFlag = {0:'上级', 1:'所有人', 2:'仅相关人'}; //开放属性
var dictImportantCss = {0:{gradeTab:'ebtw-grade-tab-important0', color:'ebtw-color-important0'}, 1:{gradeTab:'ebtw-grade-tab-important1', color:'ebtw-color-important1'}, 2:{gradeTab:'ebtw-grade-tab-important2', color:'ebtw-color-important2'}}; //重要程度样式

/*=== 计划 ===*/
var dictClassNameOfPlan = {0:'-'}; //计划分类
var dictStatusOfPlan = {0:'新建未阅', 1:'未处理', 2:'评审中', 3:'已废弃',/*3:'评审已阅',*/ 4:'评审通过' , 5:'评审拒绝' ,6:'已完成'}; //状态
var dictStatusColorOfPlan = {0:'#7b32a0', 1:'#7b32a0', 2:'#f79646',/* 3:'#f79646',*/ 4:'#00b050' , 5:'#ff0000' ,6:'#00a2e8'}; //状态对照颜色
var dictPeriodOfPlan = {1:'日计划', 2:'周计划' ,3:'月计划', 4:'季计划', 5:'年计划', 6:'自定义'}; //周期
//有效状态
var dictResultStatusNameOfPlan = $.extend({}, dictStatusOfPlan);
$.extend(dictResultStatusNameOfPlan, {0:'-'});

/*=== 任务 ===*/
var dictClassNameOfTask = {0:'-'}; //任务分类
var dictStatusOfTask = {0:'未查阅', 1:'未开始', 2:'进行中', 3:'已完成', 4:'已中止'}; //状态
var dictStatusColorOfTask = {0:'#7b32a0', 1:'#f79646', 2:'#00a2e8', 3:'#00b050', 4:'#ff0000'}; //状态对照颜色

/*=== 考勤 ===*/
var dictRecStateOfAttendance = {0:'', 1:'全勤', 2:'未签到', 4:'未签退', 8:'旷工', 16:'迟到', 32:'早退', 64:'加班', 128:'外勤', 256:'请假'}; //考勤状态
var dictReqTypeOfAttendance = {0:'未申请', 1:'补签', 2:'外勤', 3:'请假', 4:'加班'}; //审批类型名称
var dictReqStatusOfAttendance = {0:'默认', 1:'审批中', 2:'通过', 3:'不通过', 4:'回退'}; //审批状态名称
var dictReqStatusColorOfAttendance = {0:'#00b050', 1:'#f79646', 2:'#00b050', 3:'#ff0000', 4:'#7b32a0'}; //考勤审批状态颜色