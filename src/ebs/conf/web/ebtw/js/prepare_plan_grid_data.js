/**
 * 准备表格数据
 */

//定义表格
var dtGridColumns = [
	{id:'plan_name', title:'计划事项', type:'string', headerClass: 'grid-headerstyle-left col-xs-4', columnClass:'grid-columnstyle-left grid-rowstyle col-xs-4'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			//解析当前左侧菜单选中情况
			var currentLeftMenu = $('#current_left_menu').val();
			var values = currentLeftMenu.split('_');
			
			var important = parseInt(record.important);
			var data = {/*important:dictImportant[important],*/ ptrId:record.plan_id, value:value.replace(/</g, "&lt;").replace(/>/g, "&gt;")/*过滤html标签符号*/};
			data.importantGradeTabHtml = createModifyImportantMenuScript(important, 'ebtw-grade-tab '+dictImportantCss[important].gradeTab, 'DtGrid', ((values[0]==1 && values[1]==1) || values[0]==2));
			if (record.su_valid_flag==0 || record.is_deleted==1)
				data.alreadyDeleted = true;
			return laytpl($('#dtGrid-first-column-script').html()).render(data);
	}},
	//{}, //预留位置
	//{}, //预留位置
	{id:'status', title:'状态', type:'string', codeTable:dictStatusOfPlan, headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			//下列情况显示红色的感叹号"!"
			var warning = false;
			var shareType;
			if (record.shares) {
				//共享人未读
				shareType = 3;
				var shareUsers = record.shares[shareType];
				if (shareUsers!=undefined && shareUsers.length>0) {
					var shareUser = shareUsers[0];
					if (shareUser.read_flag==0 && (logonUserId==shareUser.share_uid)) //共享人可见此状态
						warning = true;
				}
				
				//status="评审中"，评审人未读
				if (record.status==2) {
					shareType = 1;
					var shareUsers = record.shares[shareType];
					if (shareUsers!=undefined && shareUsers.length>0) {
						var shareUser = shareUsers[0];
						if (shareUser.read_flag==0 && (logonUserId==record.create_uid || logonUserId==shareUser.share_uid)) //仅提交评审的用户和评审人可见此状态
							warning = true;
					}
				}
			}
			
			var html = '<span';
			if (warning)
				html += ' title="'+(shareType==3?'共享人':'评审人')+'未阅"';
			html += '><span style="color:'+(record.su_valid_flag==0?'rgba(0,0,0,.3)':dictStatusColorOfPlan[value])+';'+(record.status==0?'font-weight:bold;':'')+'">'+dictStatusOfPlan[value]+'</span>';
			if (warning)
				html += ' <span style="font-weight:bold;color:red;">!</span>';
			html+='</span>';
			
			return html;
	}},
	{id:'period', title:'周期', type:'string', codeTable:dictPeriodOfPlan, headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1'},
	{id:'start_time', title:'开始时间',/* type:'date', format:'yyyy-MM-dd',*/ headerClass: 'grid-headerstyle-center col-xs-1 col-xs-1p5', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1 col-xs-1p5', hideType:'xs'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			var startTime = new Date(value);
			//开始时间>当前时间
			return (startTime.getTime()>new Date().getTime())?('<span class="ebtw-color-warning-1" title="计划未开始">'+value.substr(0,10)+'</span>'):(value.substr(0,10));
		}},
	{id:'stop_time', title:'结束时间',/* type:'date', format:'yyyy-MM-dd',*/ headerClass: 'grid-headerstyle-center col-xs-1 col-xs-1p5', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1 col-xs-1p5', hideType:'xs'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			var stopTime = new Date(value);
			var now = new Date();
			//计划过期未完成时，“结束时间”<当前时间
			return (record.status!=6 && stopTime.getTime()<now.getTime())?('<span class="ebtw-color-warning-2" title="计划过期未完成">'+value.substr(0,10)+'</span>'):(value.substr(0,10));
		}},
	{id:'create_time', title:'创建时间', type:'date', format:'yyyy-MM-dd hh:mm', headerClass: 'grid-headerstyle-center col-xs-2', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-2', hideType:'sm|xs'},
];

//定义函数：调整表格列规则
function alterdDtGridColumns(targetCount, objs, normal) {
	var gridLen = dtGridColumns.length; //当前列数量
	//alert(gridLen);
	switch(targetCount) {
		case 6:
			if (gridLen==8) {
				dtGridColumns[0].columnClass = dtGridColumns[0].columnClass.replace(/col-xs-3/ig, 'col-xs-5');
				dtGridColumns[0].headerClass = dtGridColumns[0].headerClass.replace(/col-xs-3/ig, 'col-xs-5'); 
				dtGridColumns.splice(1,2); //删除第三、四个元素
			} else if (gridLen==7) {
				dtGridColumns[0].columnClass = dtGridColumns[0].columnClass.replace(/col-xs-4/ig, 'col-xs-5');
				dtGridColumns[0].headerClass = dtGridColumns[0].headerClass.replace(/col-xs-4/ig, 'col-xs-5');
				dtGridColumns.splice(1,1); //删除第三个元素
				if (objs.length>0)
					dtGridColumns[1] = objs[0];
			}
			break;
		case 7:
			if (gridLen==8) {
				dtGridColumns[0].columnClass = dtGridColumns[0].columnClass.replace(/col-xs-3/ig, 'col-xs-4');
				dtGridColumns[0].headerClass = dtGridColumns[0].headerClass.replace(/col-xs-3/ig, 'col-xs-4');
				dtGridColumns.splice(1,1); //删除第三个元素
				if (objs.length>0)
					dtGridColumns[1] = objs[0];
			} else if (gridLen==6) {
				dtGridColumns[0].columnClass = dtGridColumns[0].columnClass.replace(/col-xs-5/ig, 'col-xs-4');
				dtGridColumns[0].headerClass = dtGridColumns[0].headerClass.replace(/col-xs-5/ig, 'col-xs-4');
				dtGridColumns.splice(1,0,objs[0]); //在第二个元素前插入一个
			} else {
				if (objs.length>0)
					dtGridColumns[1] = objs[0];
			}
			break;
		case 8:
			if (gridLen==7) {
				dtGridColumns[0].columnClass = dtGridColumns[0].columnClass.replace(/col-xs-4/ig, 'col-xs-3');
				dtGridColumns[0].headerClass = dtGridColumns[0].headerClass.replace(/col-xs-4/ig, 'col-xs-3');
				dtGridColumns.splice(1,0,objs[0]); //在第二个元素前插入一个
				dtGridColumns[2] = objs[1];
			} else if (gridLen==6) {
				dtGridColumns[0].columnClass = dtGridColumns[0].columnClass.replace(/col-xs-5/ig, 'col-xs-3');
				dtGridColumns[0].headerClass = dtGridColumns[0].headerClass.replace(/col-xs-5/ig, 'col-xs-3');
				dtGridColumns.splice(1,0,objs[0],objs[1]); //在第二个元素前插入一个
			}
			break;
	}
	
	var timeColIndex = dtGridColumns.length-1;
	if (normal) {
		dtGridColumns[timeColIndex].id='create_time';
		dtGridColumns[timeColIndex].title='创建时间';
	} else {
		dtGridColumns[timeColIndex].id='su_create_time';
		dtGridColumns[timeColIndex].title='上报时间';
	}
}

function createDtGridColumns() {
	//解析当前左侧菜单选中情况
	var currentLeftMenu = $('#current_left_menu').val();
	var values = currentLeftMenu.split('_');
	var obj = {id:'class_id', title:'分类', type:'string',/* codeTable:dictClassNameOfPlan,*/ headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1'
		, resolution:function(value, record, column, grid, dataNo, columnNo) {
			//直接codeTable做参数已经不能满足动态增加新分类的需求，故使用回调函数实现
			return dictClassNameOfPlan[value];
		}};
	
	if (values[0]==1) {
		switch(parseInt(values[1])) {
		case 1: //个人计划
			alterdDtGridColumns(7, [obj], true);
			break;
		case 2: //评审计划
			var obj1 = {id:'create_name', title:'上报人', type:'string', headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1'
				, resolution: function(value, record, column, grid, dataNo, columnNo) {
					if (record.create_uid!=logonUserId)
						return '<span class="talk-to-person" data-talk-to-uid="'+record.create_uid+'" title="'+record.user_account+'('+record.create_uid+')">'+value+'</span>';
					return value;
				}};
			var obj2 = {id:'su_result_status', title:'处理', type:'string', codeTable:dictResultStatusNameOfPlan, headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1'
				, resolution:function(value, record, column, grid, dataNo, columnNo) {
					if (record.su_valid_flag==0 && record.su_result_status==0) {
						return '<span style="">已失效</span>';
					}
					return dictResultStatusNameOfPlan[value];
			}};
			alterdDtGridColumns(8, [obj1, obj2], false);
			break;
		case 3: //共享计划
		case 4: //下级计划
		case 5: //团队计划
			var obj1 = {id:'create_name', title:'创建人', type:'string', headerClass: 'grid-headerstyle-center col-xs-1', columnClass:'grid-columnstyle-center grid-rowstyle col-xs-1'
				, resolution:function(value, record, column, grid, dataNo, columnNo) {
					if (record.create_uid!=logonUserId)
						return '<span class="talk-to-person" data-talk-to-uid="'+record.create_uid+'" title="'+record.user_account+'('+record.create_uid+')">'+value+'</span>';
					
					return value;
				}};
			alterdDtGridColumns(7, [obj1], true);
			break;
		}
	} else if (values[0]==2) { //分类
		alterdDtGridColumns(6, [], true);
	} else { //回收站
		alterdDtGridColumns(7, [obj], true);
	}
	
	return dtGridColumns;
}
