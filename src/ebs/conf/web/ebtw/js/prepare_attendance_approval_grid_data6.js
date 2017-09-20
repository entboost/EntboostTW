/**
 * 准备表格数据-工作时长
 */

//不显示扩展行
gridExtra = false;
//不显示行样式
gridRowStyle = false;

//定义表格
var dtGridColumns = [
	{id:'attend_date', title:'日期', type:'string', extra:false, rowspan:true, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-1p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-1p5 narrow'
	, resolution:function(value, record, column, grid, dataNo, columnNo) {
		var rData = {value:value};
		return laytpl($('#dtGrid-first-column-script3').html()).render(rData);
	}},
	{id:'user_name', title:'姓名', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1', columnClass:'grid-columnstyle-center border-1 col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			showValue = value.replace(/</g, "&lt;").replace(/>/g, "&gt;"); //过滤html标签符号
			
			if (record.user_id!=logonUserId)
				return '<span class="talk-to-person" data-talk-to-uid="'+record.user_id+'" title="'+record.user_account+'('+record.user_id+')">'+showValue+'</span>';
			else
				return showValue;
	}},
	{id:'dep_name', title:'部门', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-2 col-xs-2p5', columnClass:'grid-columnstyle-center border-1 col-xs-2 col-xs-2p5'},
	{id:'signinout_time', title:'考勤时段', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-2', columnClass:'grid-columnstyle-center border-1 col-xs-2'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return (record.standard_signin_time?record.standard_signin_time.substr(0, 5):'') + '-' + (record.standard_signout_time?record.standard_signout_time.substr(0, 5):'');
	}},
	{id:'signin_time', title:'签到时间', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1', columnClass:'grid-columnstyle-center border-1 col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			if (record.req_duration!='0' && record.req_signin_time.length>0) {
				return '<span class="ebtw-color-warning-2">' + record.req_signin_time.substr(11, 5) + '<span>';
			}
			return record.signin_time.substr(11, 5);
	}},
	{id:'signout_time', title:'签退时间', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1', columnClass:'grid-columnstyle-center border-1 col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			if (record.req_duration!='0' && record.req_signout_time.length>0) {
				return '<span class="ebtw-color-warning-2">' + record.req_signout_time.substr(11, 5) + '<span>';
			}
			return record.signout_time.substr(11, 5);
	}},
	{id:'work_duration', title:'工作时长', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1', columnClass:'grid-columnstyle-center border-1 col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			var duration = parseInt(value);
			if (record.req_duration!='0')
				duration = parseInt(record.req_duration);
			
			//格式化显示
			var hour = parseInt(duration/60);
			var minute = duration%60;
			return (hour>9?'':'0') + hour + ':' + (minute>9?'':'0') + minute;
	}},
	{id:'req_remark', title:'考勤备注', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-2', columnClass:'grid-columnstyle-center border-1 col-xs-2'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			if (record.req_status==2) {
				return dictReqTypeOfAttendance[record.req_type]+'审批通过';
			} else 
				return '';
	}},
];

//定义函数：调整表格列规则
function alterdDtGridColumns(targetCount, objs, normal) {
	//var gridLen = dtGridColumns.length; //当前列数量
}

function createDtGridColumns() {
	return dtGridColumns;
}
