<?php
//节假日设置
	$holInstance = HolidaySettingService::get_instance();
	$holResults = $holInstance->getAllHolidays($entCode, array());
	if ($holResults===false) {
		log_err('getAllHolidays error');
		return;
	}
	
	//获取假期设置适用对象列表
	if (count($holResults)>0) {
		$hoSettingIds = array();
		foreach ($holResults as $holEntity)
			array_push($hoSettingIds, $holEntity['hol_set_id']);
			
			$targetResults = $holInstance->getHolidayTargets($hoSettingIds, $entCode, array());
			if ($targetResults===false) {
				log_err('getHolidayTargets error');
				return;
			}
			
			foreach ($holResults as &$mholEntity) {
				foreach ($targetResults as $targetEntity) {
					if ($mholEntity['hol_set_id']===$targetEntity['hol_set_id']) {
						if (!array_key_exists('targets', $mholEntity))
							$mholEntity['targets'] = array();
						array_push($mholEntity['targets'], $targetEntity);
					}
				}
			}
	}
	
	//分析适用者显示内容
	foreach ($holResults as &$mmholEntity) {
		$mmholEntity['target_content'] = '';
		
		if (!empty($mmholEntity['targets'])) {
			$targets = $mmholEntity['targets'];
			
			//分类整理
			$tidiedTargets = array();
			foreach ($targets as $targetEntity) {
				$targetType = $targetEntity['target_type'];
				if (!isset($tidiedTargets[$targetType]))
					$tidiedTargets[$targetType] = array();
				
				array_push($tidiedTargets[$targetType], $targetEntity);
			}
		
			$finalTargetContent = '';
			//按适用类型生成适用范围显示
			for ($i=1; $i<=3; $i++) {
				if (!array_key_exists($i, $tidiedTargets))
					continue;
				
				$targetContent = '';
				$tidiedTargetGroup = $tidiedTargets[$i];
				if (!empty($tidiedTargetGroup)) {
					$group2 = array(); //用于适用类型'用户'
	
					foreach ($tidiedTargetGroup as $targetEntity) {
						if (strlen($targetContent)>0)
							$targetContent .= '、';
						
						$targetType = $i;
						switch ($targetType) {
							case 1: //企业
								$targetContent .= '全公司 - '.$targetEntity['target_name'];
								break;
							case 2: //部门、群组
								$targetContent .= $targetEntity['target_name'];
								break;
							case 3: //用户
								$extName = $targetEntity['ext_name'];
								if (!array_key_exists($extName, $group2))
									$group2[$extName] = array();
								array_push($group2[$extName], $targetEntity['target_name']);
								break;
						}
					}
	
					//进一步整理适用类型'用户'的数据
					if (!empty($group2)) {
						$j = 0;
						foreach ($group2 as $extName=>$values) {
							$targetContent .= strlen($extName)>0?($extName.'：'):'';
							for ($k=0; $k<count($values); $k++) {
								if ($k>0)
									$targetContent .= '、';
								$targetContent .= $values[$k];
							}
	
							if ($j<count($group2)-1)
								$targetContent .='<br>';
							$j++;
						}
					}
					
					if ($i<3)
						$targetContent .= '<br>';
					
					$finalTargetContent .= $targetContent;
				}
				
				$mmholEntity['target_content'] = $finalTargetContent;
			}
		}
	}
?>
<div class="ptr-container mCustomScrollbar" data-mcs-theme="minimal-dark"><!-- dark-3 -->
	<div id="contentList" class="col-xs-12 ebtw-right-gutter-no ebtw-padding-top ebtw-padding-bottom" style="postion: relative;">
		<div class="attendance-description">
			<div>节假日设置，用于过滤员工应出勤统计等数据</div>
		</div>
		<!-- 右上按钮 -->
		<div style="position: absolute; top: 0; right: 0;">
			<button id="btn_AddHoliday" type="button" class="btn btn-primary ebtw-btn-width ebtw-menu-input ebtw-menu-pull-1 pull-right"><span class="glyphicon glyphicon-plus"></span> 新建节假日</button>
		</div>		
		<div class="ebtw-padding-top">
		<table class="query-type-table" style="width:100%;">
			<thead>
				<tr>
				<th class="ebtw-align-left col-xs-2">节假日名称</th>
				<th class="ebtw-align-left col-xs-3">周期类型</th>
				<th class="ebtw-align-left col-xs-3">适用范围</th>
				<th class="ebtw-align-center col-xs-2">创建时间</th>
				<th class="ebtw-align-center col-xs-1">状态</th>
				<th class="ebtw-align-center col-xs-1">操作</th>
				</tr>
			</thead>
			<tr class="ebtw-hide">
				<td class="ebtw-align-center col-xs-10 ebtw-no-border row-toolbar" colspan="5">
					<div class="inner-toolbar">
						<div class="fa fa-plus add-attend-leave-type" title="添加节假日设置"></div>
					</div>
				</td>
				<td class="ebtw-align-left col-xs-1 ebtw-no-border"></td>
			</tr>
		</table>
		</div>
	</div>
</div>

<script type="text/javascript">
//重载界面
function reloadPage() {
	refreshPage();
}

$(document).ready(function() {
	var rowsJson = '<?php echo escapeQuotes(strictJson(json_encode($holResults)));?>';
	var rows = json_parse(rowsJson);

	//渲染行记录
	var $tbodyContainer = $('#contentList table.query-type-table>tbody');
	var glue = ' ~ ';
	for (var i=0; i<rows.length; i++) {
		var row = rows[i];
		var content = '';

		//假期周期性类型
		switch(parseInt(row.period)) {
			case 0: //一次性
				content = row.start_time.substr(0, 10);
				if (row.start_time.substr(0, 10)!==row.stop_time.substr(0, 10)) 
					content += glue + row.stop_time.substr(0, 10);
				break;
			case 1://每年
				content = '每年：' + specalDigitToPartDate(row.period_from);
				if (row.period_from!==row.period_to) 
					content += glue + specalDigitToPartDate(row.period_to);
				break;
			case 2://每月
				content = '每月：' + prefixInteger(row.period_from, 2) + '号';
					if (row.period_from!==row.period_to) 
						content += glue + prefixInteger(row.period_to, 2) + '号';				
				break;
			case 3://每周
				content = '每周：' + dictWeekName[row.period_from];
				if (row.period_from!==row.period_to) 
					content += glue + dictWeekName[row.period_to];				
				break;
		}

		//假期计算标识
		switch (parseInt(row.flag)) {
		case 0: //全天
			
			break;
		case 1: //上午
			content += ' [上午]';
			break;
		case 2: //下午
			content += ' [下午]';
			break;
		}
		
		row.content = content;
		$tbodyContainer.append(laytpl($('#attendance-holiday-row-script').html()).render(row));
	}
	
	//注册点击"添加考勤专员"按钮事件
	$('#btn_AddHoliday').click(function(e) {
		openAttendanceHoliday('0', function(){});
	});
	
	//注册点击事件-打开假期配置界面
	$('.sidepage-open-holiday').click(function(e) {
		var holId = $(this).parents('tr').attr('data-hol-set-id');
		openAttendanceHoliday(holId, function(){});
		
		stopPropagation(e);
	});

	//注册点击事件-启用、禁用、删除一个假期配置
	$('.attend-holiday-action').click(function(e) {
		var action = parseInt($(this).attr('data-holiday-action'));
		var holId = $(this).parents('tr').attr('data-hol-set-id');
		var title = '';
		var actionType = 0;
		var otherParameter = {hol_id:holId};
		
		switch (action) {
		case 0:
			title = '启用';
			actionType = 51;
			otherParameter = $.extend(otherParameter, {disable:0});
			break;
		case 1:
			title = '禁用';
			actionType = 51;
			otherParameter = $.extend(otherParameter, {disable:1});
			break;
		case -1:
			title = '删除';
			actionType = 52;
			break;
		}
		
		//询问确认后执行
		askForConfirmSubmit('真的要' + title + '吗？', title+'假期配置', null, function() {
			var loadIndex = layer.load(2);
			
			holidaySettingAction(actionType, otherParameter, function(code) {
				layer.close(loadIndex);
				//layer.msg(title + '成功');
				refreshPage();
			}, function(err) {
				layer.close(loadIndex);
				layer.msg(title + '失败', {icon:2});
			});
		});
		
		stopPropagation(e);
	});	
});
</script>