/**
 * 准备表格数据-考勤异常
 */

//定义表格
var dtGridColumns = [
	{id:'user_name', title:'姓名', type:'string', headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			var rData = {ptr_id:record.att_req_id, user_id:record.user_id, user_account:record.user_account
					, user_name:value.replace(/</g, "&lt;").replace(/>/g, "&gt;")/*过滤html标签符号*/};
			if (record.user_id!=logonUserId)
				rData.can_talk = true;
			return laytpl($('#dtGrid-first-column-script2').html()).render(rData);
	}},
	{id:'signinout_time_rec_state', title:'考勤时段-状态', type:'string', headerClass: 'grid-headerstyle-center col-xs-4', columnClass:'grid-columnstyle-left grid-rowstyle col-xs-4'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			var content = record.attend_date + ' ' + record.standard_signin_time.substr(0, 5) + '-' + record.standard_signout_time.substr(0, 5) + ' ' + record.rec_state_name;
			return '<div ' + ((record.valid_flag=='0')?'class="ebtw-txt-deleted"':'') + '>' + content + '</div>';
	}},
	{id:'req_time', title:'申请日期', type:'string', headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1 narrow'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			var reqTime = record.req_create_time;
			return reqTime?reqTime.substr(0,10):'';
		}},
	{id:'req_type', title:'审批类型', type:'string', codeTable:dictReqTypeOfAttendance, headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return value?dictReqTypeOfAttendance[value]:'未申请';
		}},
	{id:'req_content', title:'申请内容', type:'string', headerClass: 'grid-headerstyle-center col-xs-2', columnClass:'grid-columnstyle-left grid-rowstyle col-xs-2'},
	{id:'result_time', title:'审批时间', type:'string', headerClass: 'grid-headerstyle-center col-xs-2', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-2'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return (value=='')?'':value.substr(0,16);
		}},
	{id:'req_status', title:'审批结果', type:'string', headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return (record.valid_flag=='0')?('<div style="color:rgba(0,0,0,.3);">' + dictReqStatusOfAttendance[record.result_status] + '</div>'):(value?dictReqStatusOfAttendance[value]:'');
		}},
];

//定义函数：调整表格列规则
function alterdDtGridColumns(targetCount, objs, normal) {
	//var gridLen = dtGridColumns.length; //当前列数量
}

function createDtGridColumns() {
	return dtGridColumns;
}
