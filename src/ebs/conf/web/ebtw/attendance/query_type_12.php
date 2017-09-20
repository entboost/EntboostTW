<?php
//请假类型
	$dicInstance = DictionaryInfoService::get_instance();
	
	//查询请假类型列表
	$dicResults = $dicInstance->getHolidayInfos($entCode, array(), null);
	if ($dicResults===false) {
		log_err('getHolidayInfos error');
		return;
	}
	
?>
<div class="ptr-container mCustomScrollbar" data-mcs-theme="minimal-dark"><!-- dark-3 -->
	<div id="contentList" class="col-xs-12 ebtw-right-gutter-no ebtw-padding-top ebtw-padding-bottom">
		<div class="attendance-description">
			<div>请假类型：按指定排序显示，最多可以设置16个请假类型</div>
		</div>
		<div class="ebtw-padding-top">
		<table class="query-type-table" style="width:60%;">
			<thead>
				<tr>
				<th class="ebtw-align-center col-xs-1 col-xs-1p5">排序</th>
				<th class="ebtw-align-left col-xs-3">请假类型</th>
				<th class="ebtw-align-center col-xs-1">状态</th>
				<th class="ebtw-align-left col-xs-5">操作</th>
				</tr>
			</thead>
			<tr>
				<td class="ebtw-align-center col-xs-7 ebtw-no-border row-toolbar" colspan="3">
					<div class="inner-toolbar">
						<div class="fa fa fa-refresh attend-leave-type-refresh" title="刷新"></div>
						<div class="fa fa-plus add-attend-leave-type" title="添加请假类型"></div>
					</div>
				</td>
				<td class="ebtw-align-left col-xs-5 ebtw-no-border"></td>
			</tr>			
		</table>
		</div>
	</div>
</div>

<script type="text/javascript">
//检查请假类型的数量
function checkAttendanceLeaveTypeCount() {
	var $parent = $('#contentList .query-type-table');
	var $addBtn = $parent.find('.add-attend-leave-type');
	var count = $parent.find('tr:has(input[name="dict_name"])').length;
	if (count>=16)
		$addBtn.addClass('ebtw-hide');
	else 
		$addBtn.removeClass('ebtw-hide');
}

$(document).ready(function() {
	var dicRowsJson = '<?php echo escapeQuotes(strictJson(json_encode($dicResults)));?>';
	var dicRows = json_parse(dicRowsJson);

	//渲染行记录
	var $beforePosition = $('#contentList .query-type-table .row-toolbar').parent();
	for (var i=0; i<dicRows.length; i++) {
		var dicRow = dicRows[i];
		$beforePosition.before(laytpl($('#attendance-leave-type-row-script').html()).render(dicRow));
	}

	//检查请假类型的数量
	checkAttendanceLeaveTypeCount();

	//注册点击"添加考勤专员"按钮事件
	$('.add-attend-leave-type').click(function(e) {
		//建立新行
		var dictRow = {dict_id:'0', display_index:'0', dict_name:'', disable:0};
		$beforePosition.before(laytpl($('#attendance-leave-type-row-script').html()).render(dictRow));
		//检查请假类型的数量
		checkAttendanceLeaveTypeCount();
	});

	//注册点击"刷新"按钮事件
	$('.attend-leave-type-refresh').click(function(e) {
		refreshPage();
	});

	//注册点击事件-保存、启用、禁用、删除一个请假类型记录
	$(document).on('click', '.attend-leave-type-action', function(e, refresh) {
		var action = parseInt($(this).attr('data-leave-type-action'));
		var $parent = $(this).parents('tr');
		var dictId = $parent.attr('data-dict-id');
		//未保存的行，直接界面删除
		if (action==-1 && dictId==0) {
			$parent.remove();
			return true;
		}
		
		var title = '';
		var actionType = 0;
		var otherParameter = {dict_id:dictId};
		
		switch (action) {
		case 0:
			title = '启用';
			actionType = 41;
			otherParameter = $.extend(otherParameter, {disable:0});
			break;
		case 1:
			title = '禁用';
			actionType = 41;
			otherParameter = $.extend(otherParameter, {disable:1});
			break;
		case -1:
			title = '删除';
			actionType = 42;
			break;
		case 2:
			title = '保存';
			actionType = 43;
			//排序号
			var displayIndex = $parent.find('input[name="display_index"]').val();
			if (displayIndex.length==0)
				displayIndex = '0';
			otherParameter.display_index = displayIndex;
			//请假类型
			var dictName = $parent.find('input[name="dict_name"]').val();
			if ($.trim(dictName).length==0) {
				layer.msg('请假类型不能填空', {icon:2});
				return false;
			}			
			otherParameter.dict_name = dictName;
			//状态
			otherParameter.disable = $parent.find('input[name="disable"]').val();
			
			break;
		}
		
		//函数定义：点击"确认"执行函数
		var afterConfirm = function() {
			var loadIndex = layer.load(2);
			
			attendLeaveTypeAction(actionType, otherParameter, function(code, result) {
				//更新对应行记录数据
				if (dictId==0 && actionType==43) { //新建记录
					$parent.attr('data-dict-id', result.id);
					$parent.find('.attend-leave-type-action[data-leave-type-action="0"]').addClass('ebtw-hide');
					$parent.find('.attend-leave-type-action[data-leave-type-action="1"]').removeClass('ebtw-hide');
				} else {
					if (actionType==42) { //删除
						$parent.remove();
					} if (actionType==41) { //启用或禁用
						var status = (action==0)?'有效':'禁用';
						$parent.find('.leave-type-status').html(status);
						$parent.find('.attend-leave-type-action[data-leave-type-action="'+((action==0)?0:1)+'"]').addClass('ebtw-hide');
						$parent.find('.attend-leave-type-action[data-leave-type-action="'+((action==0)?1:0)+'"]').removeClass('ebtw-hide');
					}
				}

				layer.close(loadIndex);
				layer.msg(title + '成功');
				//检查请假类型的数量
				checkAttendanceLeaveTypeCount();
			}, function(err, code) {
				layer.close(loadIndex);
				if (code==3) {
					layer.msg('不可以有重复的请假类型', {icon:2});
					//$parent.remove();
				}
				else 
					layer.msg(title + '失败', {icon:2});
				
				//检查请假类型的数量
				checkAttendanceLeaveTypeCount();
			});
		};

		if (action==-1) { //删除操作需要确认
			//询问确认后执行
			askForConfirmSubmit('真的要' + title + '吗？', title+'请假类型', null, afterConfirm);
		} else {
			afterConfirm();
		}
		stopPropagation(e);
	});	
});
</script>