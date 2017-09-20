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
	{id:'dep_name', title:'部门', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-2 col-xs-2p5', columnClass:'grid-columnstyle-center border-1 col-xs-2 col-xs-2p5'},
	{id:'calcul_days', title:'考勤天数', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1', columnClass:'grid-columnstyle-center border-1 col-xs-1'},
	{id:'expected', title:'应出勤次/时', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-1p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-1p5'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return record.expected_counts + '/' + formatMinutesToHours(record.expected_durations, 1);
	}},
	{id:'real', title:'实出勤次/时', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-1p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-1p5'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			var content = record.real_counts + '/' + formatMinutesToHours(record.real_durations, 1);
			return resolutionHandler(value, record, column, grid, dataNo, columnNo, 1, content, record.real_counts + '次/' + formatMinutesToHours(record.real_durations, 1) + '小时');
	}},
	{id:'work_overtime', title:'加班次/时', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-1p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-1p5'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			var content = record.work_overtime_counts + '/' + formatMinutesToHours(record.work_overtime_durations, 1);
			return resolutionHandler(value, record, column, grid, dataNo, columnNo, 2, content, record.work_overtime_counts + '次/' + formatMinutesToHours(record.work_overtime_durations, 1) + '小时');
	}},
	{id:'furlough_count', title:'请假次数', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1', columnClass:'grid-columnstyle-center border-1 col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return resolutionHandler(value, record, column, grid, dataNo, columnNo, 3, value, value + '次');
	}},
	{id:'abnormal_counts', title:'异常考勤次数', type:'string', extra:false, headerClass: 'grid-headerstyle-center border-1 col-xs-1 col-xs-1p5', columnClass:'grid-columnstyle-center border-1 col-xs-1 col-xs-1p5'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return resolutionHandler(value, record, column, grid, dataNo, columnNo, 4, value, value + '次');
	}},
];

//定义函数：调整表格列规则
function alterdDtGridColumns(targetCount, objs, normal) {
	//var gridLen = dtGridColumns.length; //当前列数量
}

function createDtGridColumns() {
	return dtGridColumns;
}
