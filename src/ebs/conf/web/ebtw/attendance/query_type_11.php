<?php
//考勤专员
	$udInstance = UserDefineService::get_instance();
	$depInstance = DepartmentInfoService::get_instance();
	
	//查询考勤专员列表
	$udResults = $udInstance->getAttendanceManagers($entCode, array(), null);
	if ($udResults===false) {
		log_err('getAttendanceManagers error');
		return;
	}
	
	if (count($udResults)>0) {
		//组装考勤专员的用户编号列表
		$empUids = array();
		foreach ($udResults as $udEntity)
			array_push($empUids, $udEntity['user_id']);
		//查询考勤专员所在的部门
		$depResults = $depInstance->getGroupInfosByUserid($entCode, $empUids);
		if ($udResults===false) {
			log_err('getAttendanceManagers error');
			return;
		}
		
		//给考勤专员列表补充部门数据
		foreach ($udResults as &$mudEntity) {
			foreach ($depResults as $depEntity) {
				if ($mudEntity['user_id']===$depEntity['user_id']) {
					if (!array_key_exists('groups', $mudEntity))
						$mudEntity['groups'] = array();
					array_push($mudEntity['groups'], $depEntity);
					break;
				}
			}
		}
	}
	
?>
<div class="ptr-container mCustomScrollbar" data-mcs-theme="minimal-dark"><!-- dark-3 -->
	<div id="contentList" class="col-xs-12 ebtw-right-gutter-no ebtw-padding-top ebtw-padding-bottom">
		<div id='attend-user-define-objahead' class="objahead-wrapper select-noborder ebtw-hide"></div>
		<div class="attendance-description">
			<div>考勤专员：默认能够查看、汇总、统计、审批，所有部门，所有员工考勤数据</div>
			<div>管理权限：包括管理考勤规则、管理请假类型和节假日设置</div>
		</div>
		<div class="ebtw-padding-top" style="min-height: 500px;">
		<table class="query-type-table" style="width:70%;">
			<thead>
				<tr>
				<th class="ebtw-align-center col-xs-1 col-xs-1p5">排序 </th>
				<th class="ebtw-align-left col-xs-2">姓名</th>
				<th class="ebtw-align-left col-xs-2 col-xs-2p5">部门</th>
				<th class="ebtw-align-left col-xs-2">权限</th>
				<th class="ebtw-align-center col-xs-1">状态</th>
				<th class="ebtw-align-left col-xs-3">操作</th>
				</tr>
			</thead>
			<tr>
				<td class="ebtw-align-center col-xs-9 ebtw-no-border row-toolbar" colspan="5">
					<div class="inner-toolbar">
						<div class="fa fa fa-refresh attend-user-define-refresh" title="刷新"></div>
						<div class="fa fa-plus add-attend-user-define" title="添加考勤专员"></div>
					</div>
				</td>
				<td class="ebtw-align-left col-xs-3 ebtw-no-border"></td>
			</tr>			
		</table>
		<div style="height: 300px;"></div>
		</div>
	</div>
</div>

<script type="text/javascript">
//检查考勤专员的数量
function checkAttendanceUserDefinesCount() {
	var $parent = $('#contentList .query-type-table');
	var $addBtn = $parent.find('.add-attend-user-define');
	var count = $parent.find('tr:has(input[name="user_id"])').length;
	if (count>=6)
		$addBtn.addClass('ebtw-hide');
	else 
		$addBtn.removeClass('ebtw-hide');
}

$(document).ready(function() {
	var udRowsJson = '<?php echo escapeQuotes(strictJson(json_encode($udResults)));?>';
	var udRows = json_parse(udRowsJson);

	//渲染行记录
	var $beforePosition = $('#contentList .query-type-table .row-toolbar').parent();
	for (var i=0; i<udRows.length; i++) {
		var udRow = udRows[i];
		//管理权限
		udRow.authority_management = (udRow.param_int&0x1)==0x1;
		//拼装部门名称
		udRow.dep_names = '';
		if (udRow.groups) {
			for (var j=0; j<udRow.groups.length; j++) {
				var group = udRow.groups[j];
				udRow.dep_names += group.dep_name+'<br>';
			}
		}
		//点击名字跳转对话
		if (udRow.user_id!=logonUserId)
			udRow.can_talk = true;
		
		$beforePosition.before(laytpl($('#attendance-user-define-row-script').html()).render(udRow));
	}

	//检查考勤专员的数量
	checkAttendanceUserDefinesCount();
	
	//创建考勤专员'自动搜索小部件'
	registerAutoloadWidget('#attend-user-define-objahead.objahead-wrapper', function(e, data_id, data_name, data_extprop, data_type) {
			//隐藏'自动搜索小部件'
			$(this).parents('.objahead-wrapper').addClass('ebtw-hide');
			//判断选中人员是否已经存在
			if ($('#contentList .query-type-table').find('input[name="user_id"][value="'+data_id+'"]').length>0) {
				layer.msg('考勤专员 '+data_name+'('+data_id+') 已存在', {icon:2});
				return;
			}
			//使用选中人员建立新行
			//data_type -= 10;
			var udRow = {new_ud:true, ud_id:'0', display_index:'0', user_id:data_id, user_name:data_name, user_account:data_extprop
					, dep_names:'', authority_management:true, disable:0};
			//点击名字跳转对话
			if (udRow.user_id!=logonUserId)
				udRow.can_talk = true;
			
			$beforePosition.before(laytpl($('#attendance-user-define-row-script').html()).render(udRow));
			
			//触发一次点击"保存"按钮事件
			$('#contentList .query-type-table tr:has(input[name="user_id"][value="'+data_id+'"])')
				.find('.attend-user-define-action[data-user-define-action="2"]').trigger('click', false);
		}, [{type:13, type_name:'用户', entity:'person', placeholder:'输入用户名称'}]
	);

	//注册点击"添加考勤专员"按钮事件
	$('.add-attend-user-define').click(function(e) {
		$oaWrapper = $('#attend-user-define-objahead.objahead-wrapper');
		//显示'自动搜索小部件'，并触发一次选择框的选中事件
		$oaWrapper.removeClass('ebtw-hide').find('select').trigger('select2:select');
		
		//调整显示位置
		var left= event.clientX+document.body.scrollLeft - $('#contentList').offset().left;
		var top = event.clientY+document.body.scrollTop - $('#contentList').offset().top;
		$oaWrapper.css('left', left).css('top', top);

		stopPropagation(e);
	});

	//阻止'自动搜索小部件'点击事件传递
	$('#attend-user-define-objahead.objahead-wrapper').click(function(e) {
		stopPropagation(e);
	});
	//点击右侧页面隐藏'自动搜索小部件'
	$('#contentList').click(function(e) {
		$('#attend-user-define-objahead.objahead-wrapper').addClass('ebtw-hide');
	});
	
	//注册点击"刷新"按钮事件
	$('.attend-user-define-refresh').click(function(e) {
		refreshPage();
	});

	//注册点击事件-保存、启用、禁用、删除一个考勤专员记录
	$(document).on('click', '.attend-user-define-action', function(e, refresh) {
		var action = parseInt($(this).attr('data-user-define-action'));
		var $parent = $(this).parents('tr');
		var udId = $parent.attr('data-ud-id');
		var title = '';
		var actionType = 0;
		var otherParameter = {ud_id:udId};
		
		switch (action) {
		case 0:
			title = '启用';
			actionType = 31;
			otherParameter = $.extend(otherParameter, {disable:0});
			break;
		case 1:
			title = '禁用';
			actionType = 31;
			otherParameter = $.extend(otherParameter, {disable:1});
			break;
		case -1:
			title = '删除';
			actionType = 32;
			break;
		case 2:
			title = '保存';
			actionType = 33;
			//排序号
			var displayIndex = $parent.find('input[name="display_index"]').val();
			if (displayIndex.length==0)
				displayIndex = '0';
			otherParameter.display_index = displayIndex;
			//用户编号
			var userId = $parent.find('input[name="user_id"]').val();
			if ($.trim(userId).length==0)
				return false;
			otherParameter.user_id = userId; 
			//是否有管理权限
			var $authCodeE = $parent.find('input[type="checkbox"][name="auth_code"]');
			if ($authCodeE.is(':checked'))
				otherParameter.authority_management = 1;
			else
				otherParameter.authority_management = 0; 
			//状态
			otherParameter.disable = $parent.find('input[name="disable"]').val();
			
			break;
		}

		//函数定义：点击"确认"执行函数
		var afterConfirm = function() {
			var loadIndex = layer.load(2);
			
			attendUserDefineAction(actionType, otherParameter, function(code, result) {
				layer.close(loadIndex);
				layer.msg(title + '成功');
				if (refresh!==false) {
					setTimeout(function() {
						refreshPage();	
					}, 600);
				} else {
					//更新对应行记录数据
					$parent.attr('data-ud-id', result.id).removeAttr('data-new-row');
					$parent.find('.attend-user-define-action').removeClass('ebtw-hide');
					//检查考勤专员的数量
					checkAttendanceUserDefinesCount();
				}
			}, function(err, code) {
				layer.close(loadIndex);
				if (code==3) {
					layer.msg('该考勤专员已存在', {icon:2});
					$parent.remove();
				}
				else 
					layer.msg(title + '失败', {icon:2});
				
				//检查考勤专员的数量
				checkAttendanceUserDefinesCount();
			});
		};

		if (action==2) { //保存不需要确认
			afterConfirm();
		} else {
			//询问确认后执行
			askForConfirmSubmit('真的要' + title + '吗？', title+'考勤专员', null, afterConfirm);
		}
		stopPropagation(e);
	});	
});
</script>