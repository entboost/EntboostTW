/**
 * 准备表格数据
 */

//定义表格
var dtGridColumns = [
	{id:'task_name', title:'任务标题', type:'string', headerClass: 'grid-headerstyle-left col-xs-5', columnClass:'grid-columnstyle-left grid-rowstyle col-xs-5'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			//解析当前左侧菜单选中情况
			var currentLeftMenu = $('#current_left_menu').val();
			var values = currentLeftMenu.split('_');
			
			var important = parseInt(record.important);
			var data = {/*important:dictImportant[important],*/ ptrId:record.task_id, value:value.replace(/</g, "&lt;").replace(/>/g, "&gt;")/*过滤html标签符号*/};
			data.importantGradeTabHtml = createModifyImportantMenuScript(important, 'ebtw-grade-tab '+dictImportantCss[important].gradeTab, 'DtGrid', ((values[0]==1 && (values[1]==1 || values[1]==2)) || values[0]==2));
			return laytpl($('#dtGrid-first-column-script').html()).render(data);
	}},
	{id:'percentage', title:'进度', type:'string', headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			var content = laytpl($('#custom-progressbar-script').html()).render({percentage:parseInt(value)>100?100:value});
			return content;
	}},
	{id:'status', title:'状态', type:'string', codeTable:dictStatusOfTask, headerClass: 'grid-headerstyle-center col-xs-1 col-xs-1p5', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1 col-xs-1p5'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
		var warning = false;
		//如果负责人未读，就显示红色的感叹号"!"
		if (record.shares) {
			var shareType = 5;
			var shareUsers = record.shares[shareType];
			if (shareUsers!=undefined && shareUsers.length>0) {
				var shareUser = shareUsers[0];
				if (shareUser.read_flag==0 && (logonUserId==record.create_uid || logonUserId==shareUser.share_uid)) { //仅提交人和负责人可见此状态
					warning = true;
				}
			}
		}
		
		var html = '<span';
		if (warning)
			html += ' title="负责人未阅"';
		html += '><span style="color:'+dictStatusColorOfTask[value]+';'+(record.status==0?'font-weight:bold;':'')+'">'+dictStatusOfTask[value]+'</span>';
		if (warning)
			html += ' <span style="font-weight:bold;color:red;">!</span>';
		html+='</span>';
		
		return html;
	}},
	{id:'stop_time', title:'截止时间', /*type:'date', format:'yyyy-MM-dd hh:mm',*/ headerClass: 'grid-headerstyle-center col-xs-2', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-2', hideType:'xs'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			var startTime = new Date(record.start_time);
			var stopTime = new Date(record.stop_time);
			var now = new Date();
			
			//任务未完成，并且 当前时间>结束时间
			if (record.status<=2 && now.getTime()>stopTime.getTime())
				return '<span class="ebtw-color-warning-2" title="任务过期未完成">'+value.substr(0,16)+'</span>';
			
			//开始时间>当前时间
			return (startTime.getTime()>now.getTime())?('<span class="ebtw-color-warning-1" title="任务未到开始时间">'+value.substr(0,16)+'</span>'):(value.substr(0,16));
		}},
	{id:'principal_name', title:'负责人', type:'string', headerClass: 'grid-headerstyle-center col-xs-1 col-xs-1p5', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1 col-xs-1p5', hideType:'xs'},
	{id:'work_time', title:'总耗时', type:'string', headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			return formatMinutesToHours(value) +'小时';
	}},
];

//定义函数：调整表格列规则
function alterdDtGridColumns(obj, normal, queryType) {
	if (normal) {
		dtGridColumns[4].id='principal_name';
		dtGridColumns[4].title='负责人';
		dtGridColumns[4].resolution = function(value, record, column, grid, dataNo, columnNo) {
			var share = getShares(5, record, true);
			if (share) {
				if (share.share_uid!=logonUserId)
					return '<span class="talk-to-person" data-talk-to-uid="'+share.share_uid+'" title="'+share.user_account+'('+share.share_uid+')">'+share.share_name+'</span>';
				
				return share.share_name;
			} else 
				return '';
		}
	} else {
		dtGridColumns[4].id='create_name';
		dtGridColumns[4].title='提交人';
		dtGridColumns[4].resolution = function(value, record, column, grid, dataNo, columnNo) {
			if (record.create_uid!=logonUserId)
				return '<span class="talk-to-person" data-talk-to-uid="'+record.create_uid+'" title="'+record.user_account+'('+record.create_uid+')">'+record.create_name+'</span>';
			
			return record.create_name;
		}
//		if (dtGridColumns[4].resolution)
//			delete(dtGridColumns[4].resolution);
	}
}

function createDtGridColumns() {
	//解析当前左侧菜单选中情况
	var currentLeftMenu = $('#current_left_menu').val();
	var values = currentLeftMenu.split('_');
	//var obj;
	if (values[0]==1) {
		switch(parseInt(values[1])) {
		case 1: //提交的任务
		case 3: //参与的任务
		case 4: //关注的任务
		case 5: //共享的任务
		case 6: //下级的任务
		case 20: //团队的任务
			alterdDtGridColumns(null,/*obj,*/ true);
			break;
		case 2: //负责的任务
			alterdDtGridColumns(null,/*obj*/ false);
			break;
		}
	}
	
	return dtGridColumns;
}
