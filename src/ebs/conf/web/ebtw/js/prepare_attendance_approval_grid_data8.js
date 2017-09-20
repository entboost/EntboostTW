/**
 * 准备表格数据-工作时长
 */

//不显示扩展行
gridExtra = false;
//不显示行样式
gridRowStyle = false;

//定义函数：单元格渲染
var resolutionHandler = function(value, record, column, grid, dataNo, columnNo, subType, content, content1, extClass) {
	var search_time_s, search_time_e;
	if (grid.pager.extDatas) {
		search_time_s = grid.pager.extDatas.search_time_s;
		search_time_e = grid.pager.extDatas.search_time_e;
	}
	
	return laytpl($('#dtGrid-column-sidepage-open-script').html()).render({
		content:content, ptrType:PTR_TYPE, subType:subType, extClass:extClass, extData1:record.user_id, extData2:record.user_name
		, extData3:search_time_s, extData4:search_time_e, extData5:content1});	
};

//定义表格
var dtGridColumns = [
	{id:'user_name', title:'姓名', type:'string', extra:false, rowspan:true, rowspanColumnIds:'user_name, user_id', headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-1p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-1p5 narrow'
	, resolution:function(value, record, column, grid, dataNo, columnNo) {
		var userName = value.replace(/</g, "&lt;").replace(/>/g, "&gt;"); //过滤html标签符号
		var rData = {ptr_id:0, user_id:record.user_id, user_name:userName, user_account:record.user_account};
		if (record.user_id!=logonUserId)
			rData.can_talk = true;
		return laytpl($('#dtGrid-first-column-script2').html()).render(rData);
	}},
	{id:'dep_name', title:'部门', type:'string', extra:false, headerStyle: 'border-right:2px solid #aaa !important', headerClass: 'grid-headerstyle-center border-1 col-xs-2 col-xs-2p5', columnClass:'grid-columnstyle-center border-1 col-xs-2 col-xs-2p5'},
	{id:'signin_counts', title:'签到', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-0p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-0p5'},
	{id:'late_counts', title:'迟到', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-0p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-0p5'},
	{id:'unsignin_counts', title:'未签到', type:'string', extra:false, headerStyle: 'border-right:2px solid #aaa !important', headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-0p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-0p5'},
	{id:'signout_counts', title:'签退', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-0p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-0p5'},
	{id:'leave_early_counts', title:'早退', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-0p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-0p5'},
	{id:'unsignout_counts', title:'未签退', type:'string', extra:false, headerStyle: 'border-right:2px solid #aaa !important', headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-0p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-0p5'},
	{id:'work_outside_counts', title:'外勤', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-0p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-0p5'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return resolutionHandler(value, record, column, grid, dataNo, columnNo, 5, value, value + '次/' + formatMinutesToHours(record.work_outside_durations, 1) + '小时');
	}},
	{id:'furlough_count', title:'请假', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-0p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-0p5'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return resolutionHandler(value, record, column, grid, dataNo, columnNo, 6, value, value + '次');
	}},
	{id:'work_overtime_counts', title:'加班', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-0p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-0p5'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return resolutionHandler(value, record, column, grid, dataNo, columnNo, 7, value, record.work_overtime_counts + '次/' + formatMinutesToHours(record.work_overtime_durations, 1) + '小时');
	}},
	{id:'x', title:'', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-1p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-1p5'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return resolutionHandler(value, record, column, grid, dataNo, columnNo, 8, '查看明细', null, true);
		}},
];

//定义函数：调整表格列规则
function alterdDtGridColumns(targetCount, objs, normal) {
	//var gridLen = dtGridColumns.length; //当前列数量
}

function createDtGridColumns() {
	return dtGridColumns;
}
