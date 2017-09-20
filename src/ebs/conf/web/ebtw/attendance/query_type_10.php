<?php
//考勤规则
	$aSetInstance = AttendSettingService::get_instance();
	
	//获取可管理的考勤设置列表
	$aSetResults = $aSetInstance->getManagedAttendSettings($entCode, array());
	if ($aSetResults===false) {
		log_err('getManagedAttendSettings error');
		return;
	}
	
	//获取考勤设置适用对象列表
	if (count($aSetResults)>0) {
		$attendSettingIds = array();
		foreach ($aSetResults as $aSetEntity)
			array_push($attendSettingIds, $aSetEntity['att_set_id']);
		
		$targetResults = $aSetInstance->getAttendSettingTargets($attendSettingIds, $entCode, array());
		if ($targetResults===false) {
			log_err('getAttendSettingTargets error');
			return;
		}
		
		foreach ($aSetResults as &$maSetEntity) {
			foreach ($targetResults as $targetEntity) {
				if ($maSetEntity['att_set_id']===$targetEntity['att_set_id']) {
					if (!array_key_exists('targets', $maSetEntity))
						$maSetEntity['targets'] = array();
					array_push($maSetEntity['targets'], $targetEntity);
				}
			}
		}
	}
?>
<div class="ptr-container mCustomScrollbar" data-mcs-theme="minimal-dark"><!-- dark-3 -->
	<div id="contentList" class="col-xs-12 ebtw-right-gutter-no ebtw-padding-top ebtw-padding-bottom">
		<div class="attendance-description">
			<div>如果用户有指定规则，就使用指定规则；如没有就使用部门（群组）规则</div>
			<div>如果部门有指定规则，就使用指定规则；如没有就使用企业默认规则</div>
		</div>
		<div class="ebtw-padding-top">
		<table class="query-type-table" style="width:70%;">
			<thead>
				<tr>
				<th class="ebtw-align-left col-xs-3">考勤规则 </th>
				<th class="ebtw-align-left col-xs-3">适用范围</th>
				<th class="ebtw-align-center col-xs-1">状态</th>
				<th class="ebtw-align-left col-xs-2">操作</th>
				</tr>
			</thead>
			<?php 
			foreach ($aSetResults as $aSetEntity) {
			?>
			<tr data-setting-id="<?php echoField($aSetEntity, 'att_set_id');?>">
				<td class="ebtw-align-left col-xs-3">
					<div class="ebtw-action sidepage-open-setting"><?php 
						if (!empty($aSetEntity['name']))
							echoField($aSetEntity, 'name');
						else
							echo '无标题';
					?></div>
				</td>
				<td class="ebtw-align-left col-xs-3"><?php 
					$hasTarget = false;
					if (!empty($aSetEntity['targets'])) {
						$targets = $aSetEntity['targets'];
						//分类整理
						$tidiedTargets = array();
						foreach ($targets as $targetEntity) {
							$targetType = $targetEntity['target_type'];
							if (!isset($tidiedTargets[$targetType]))
								$tidiedTargets[$targetType] = array();
							
							array_push($tidiedTargets[$targetType], $targetEntity);
						}
						
						//按适用类型进行渲染
						for ($i=1; $i<=3; $i++) {
							if (!array_key_exists($i, $tidiedTargets))
								continue;
							
							$tidiedTargetGroup = $tidiedTargets[$i];
							if (!empty($tidiedTargetGroup)) {
								$targetContent = '';
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
								
								if (!empty($targetContent))
									$hasTarget = true;
								echo $targetContent;
								if ($i<3)
									echo '<br>';
							}
						}
					}
					
					if (!$hasTarget) {
						echo '<span class="ebtw-color-error">[没有适用任何人]</span>';
					}
				?></td>
				<td class="ebtw-align-center col-xs-1"><?php 
					if ($aSetEntity['disable']==1)
						echo '禁用';
					else 
						echo '有效';
				?></td>
				<td class="ebtw-align-left col-xs-2">
					<?php if ($aSetEntity['disable']==1) {?>
						<div class="ebtw-action ebtw-action-ext attend-setting-action" data-setting-action="0">启用</div>
					<?php } else {?>
						<div class="ebtw-action ebtw-action-ext attend-setting-action" data-setting-action="1">禁用</div>
					<?php }?>
					&nbsp;<div class="ebtw-action ebtw-action-ext attend-setting-action" data-setting-action='-1'>删除</div>
				</td>
			</tr>
			<?php }?>
			<tr>
				<td class="ebtw-align-left col-xs-3 ebtw-no-border"></td>
				<td class="ebtw-align-left col-xs-3 ebtw-no-border"></td>
				<td class="ebtw-align-center col-xs-1 ebtw-no-border"></td>
				<td class="ebtw-align-left col-xs-2 ebtw-no-border"><div class="ebtw-action ebtw-action-ext sidepage-open-setting">添加</div></td>
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
	//注册点击事件-打开考勤规则配置界面
	$('.sidepage-open-setting').click(function(e) {
		var aSetId = $(this).parents('tr').attr('data-setting-id');
		openAttendanceSetting(aSetId, function(){});
		
		stopPropagation(e);
	});
	
	//注册点击事件-启用、禁用、删除一个考勤规则
	$('.attend-setting-action').click(function(e) {
		var action = parseInt($(this).attr('data-setting-action'));
		var aSetId = $(this).parents('tr').attr('data-setting-id');
		var title = '';
		var actionType = 0;
		var otherParameter = {set_id:aSetId};
		
		switch (action) {
		case 0:
			title = '启用';
			actionType = 21;
			otherParameter = $.extend(otherParameter, {disable:0});
			break;
		case 1:
			title = '禁用';
			actionType = 21;
			otherParameter = $.extend(otherParameter, {disable:1});
			break;
		case -1:
			title = '删除';
			actionType = 22;
			break;
		}
		
		//询问确认后执行
		askForConfirmSubmit('真的要' + title + '吗？', title+'考勤规则', null, function() {
			var loadIndex = layer.load(2);
			
			attendSettingAction(actionType, otherParameter, function(code) {
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