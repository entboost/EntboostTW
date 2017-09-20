<?php
include dirname(__FILE__).'/../attendance/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../attendance/include.php';
require_once dirname(__FILE__).'/../attendance/attendance_functions.php';

	$isEdit = false;
	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
	$entCode = $_SESSION[USER_ENTERPRISE_CODE]; //企业编号
	
	$aSetId = get_request_param('att_set_id', '0');
	$entity = null;
	$virtualDate = date('Y-m-d', time()); //参考日期
	
	if (!empty($aSetId)) { //编辑考勤规则
		$isEdit = true;
		
		$aSetInstance = AttendSettingService::get_instance();
		//获取设置记录
		$aSetResults = $aSetInstance->getOneRecordByPrimaryKey($aSetId);
		if ($aSetResults===false) {
			log_err('get attendSetting record error');
			return;
		}
		if (count($aSetResults)==0) {
			log_err("not attendSetting record for $aSetId");
			return;
		}
		
		$entity  = $aSetResults[0];
		$entity['total_work_duration'] = 0;
		$attendSettingIds = array($aSetId);
		
		//获取创建者或修改者的信息及修改时间
		$modifyUid = $entity['create_uid'];
		$modifyTime = $entity['create_time'];
		if (!empty($entity['last_uid'])) {
			$modifyUid = $entity['last_uid'];
			$modifyTime = $entity['last_time'];
		}
		$entity['modify_time'] = $modifyTime;
		
		$uaResult = UserAccountService::get_instance()->getOneRecordByPrimaryKey($modifyUid);
		if ($uaResult===false) {
			log_err('getOneRecordByPrimaryKey error for get attendSetting creator');
			return;
		}
		if (count($uaResult)>0)
			$entity['modify_user_name'] = $uaResult[0]['username'];
		
		
		//获取适用对象列表
		$targetResults = $aSetInstance->getAttendSettingTargets($attendSettingIds, $entCode, array());
		if ($targetResults===false) {
			log_err('getAttendSettingTargets error');
			return;
		}
		$entity['targets'] = $targetResults;
		
		//获取规则列表
		$ruleResults = $aSetInstance->getAttendRules($attendSettingIds);
		if ($ruleResults===false) {
			log_err('getAttendRules error');
			return;
		}
		
		if (!empty($ruleResults)) {
			//封装规则编号条件
			$ruleIds = array();
			foreach ($ruleResults as $ruleEntity)
				array_push($ruleIds, $ruleEntity['att_rul_id']);
			
			//获取考勤时间段列表
			$timeResults = $aSetInstance->getAttendTimes($ruleIds);
			if ($timeResults===false) {
				log_err('getAttendTimes error');
				return;
			}
			
			//遍历匹配：rule与多个time关联
			foreach ($ruleResults as &$mruleEntity) {
				//获取规则顺序号
				$ruleIndexes = getAttendRuleIndex($entity, $mruleEntity['att_rul_id']);
				if ($ruleIndexes===false) {
					log_err('getAttendRuleIndex error');
					return;
				}
				if (count($ruleIndexes)==0)
					continue;
				if ($ruleIndexes[1]===true) {
					log_info("miss an old attendRule ".$mruleEntity['att_rul_id']);
					continue;
				}
				
				$mruleEntity['rul_index'] = $ruleIndexes[0];
				$mruleEntity['new_field_matched'] = $ruleIndexes[2]; //是否在new字段匹配
				
				foreach ($timeResults as $timeEntity) {
					//获取时间段顺序号
					$timeIndexes = getAttendTimeIndex($mruleEntity, $timeEntity['att_tim_id']);
					if ($timeIndexes===false) {
						log_err('getAttendTimeIndex error');
						return;
					}
					if (count($timeIndexes)==0)
						continue;
					if ($timeIndexes[1]===true) {
						log_info("miss an old attendTime ".$timeEntity['att_tim_id']);
						continue;
					}
					
					$timeEntity['rul_index'] = $mruleEntity['rul_index'];
					$timeEntity['tim_index'] = $timeIndexes[0];
					$timeEntity['new_field_matched'] = $timeIndexes[2]; //是否在new字段匹配
					
					//匹配rul_id
					if ($mruleEntity['att_rul_id']===$timeEntity['att_rul_id']) {
						if (!array_key_exists('times', $mruleEntity))
							$mruleEntity['times'] = array();
						
						//$mruleEntity['times'][$timeEntity['att_tim_id']] = $timeEntity;
						array_push($mruleEntity['times'], $timeEntity);
					}
				}
				
				//考勤时间段记录排序
				usort($mruleEntity['times'], 'attendTimeSort');
				
				//计算本规则的总工作时长
				$mruleEntity['total_work_duration'] = 0;
				if (array_key_exists('times', $mruleEntity)) {
					$totalWorkDuration = 0;
					foreach ($mruleEntity['times'] as $timeEntity)
						$totalWorkDuration += intval($timeEntity['work_duration']);
					
					$mruleEntity['total_work_duration'] = $totalWorkDuration;
				}
				
				if (!array_key_exists('rules', $entity))
					$entity['rules'] = array();
				
				//$entity['rules'][$mruleEntity['att_rul_id']] = $mruleEntity;
				array_push($entity['rules'], $mruleEntity);
			}
			
			//考勤时间段记录排序
			usort($entity['rules'], 'attendRuleSort');
			
			//计算本考勤设置的总工作时长
			if (array_key_exists('rules', $entity)) {
				$totalWorkDuration = 0;
				foreach ($entity['rules'] as $ruleEntity)
					$totalWorkDuration += intval($ruleEntity['total_work_duration']);
				
				$entity['total_work_duration'] = $totalWorkDuration;				
			}
		}
	} else { //新建考勤规则
		$entity = array('att_set_id'=>'0', 'is_default'=>'0');
	}
	//log_warn($entity);
	$title =($isEdit?'修改':'新建').'考勤规则';
?>
<div class="side-toolbar col-xs-12">
	<div class="side-toolbar-icon">
		<span class="glyphicon <?php if ($isEdit) echo 'glyphicon-edit'; else echo 'glyphicon-eye-open';?>"></span>
		<span class="action-title"><?php echo $title;?></span>
		<span class="action-title3"></span>
    </div>
    <div class="side-close">
        <span class="glyphicon glyphicon-remove" title="关闭"></span>
    </div>
    <div class="side-fullscreen" data-type="0">
        <span class="glyphicon glyphicon-fullscreen" title="全屏"></span>
    </div>
    <div class="side-save">
        <span class="glyphicon glyphicon-floppy-disk" title="保存"></span>
    </div>
</div>

<div class="col-xs-12 ebtw-horizontal-nopadding-right sidepage-property-container mCustomScrollbar" data-mcs-theme="dark-3">
	<div style="padding: 5px 0;">
	<form class="form-horizontal" id="attend_setting_form">
		<input type="hidden" name="action_type" value="23">
		<input type="hidden" name="set_id" value="<?php echoField($entity, 'att_set_id', 0);?>">
		
		<div class="form-group row-ptr">
			<label  class="col-xs-2 control-label">规则名称</label>
			<div class="col-xs-5">
				<input name="att_set_name" class="form-control" type="text" placeholder="输入考勤规则名称" value="<?php echoField($entity, 'name');?>">
			</div>
		</div>
		<div class="form-group row-ptr form-inline">
			<input type="hidden" name="att_set_targets" value=""><!-- 适用范围对象，格式如："1,1000000000000030;2,999001;3,80" -->
			<label class="col-xs-2 control-label">适用对象</label>
			<div class="col-xs-9" style="position: relative;">
				<div id="attend_target" class="form-control-static select-target-option" style="display: block;">
					<div class="ptr-add-target"><span class="glyphicon glyphicon-plus"></span>添加适用对象</div>
				</div>
	            <div id='approver-objahead' class="objahead-wrapper select-noborder ebtw-hide"></div>
			</div>
		</div>
		<div class="form-group row-ptr form-inline attend-setting">
			<label  class="col-xs-2 control-label">总时长</label>
			<div class="col-xs-5">
				<div class="form-control-static"><input name="all_duration" class="form-control attend-setting-width-4" type="text" value="" readonly></div>&nbsp;&nbsp;&nbsp;
				<label class="checkbox attend-setting-checkbox">
					<input type="checkbox" name="is_default" value="1" <?php if ($entity['is_default']==1) echo 'checked';?>>&nbsp;默认考勤规则
				</label>
				&nbsp;<div class="form-control-static tips-icon fa fa-question" title="如果用户在多个考勤规则下，默认使用该考勤规则"></div>
			</div>
		</div>
        
	    <!-- 分隔线 -->
		<div class="col-xs-12 div-divide-top-pull">
			<div class="divide-line col-xs-5"></div>
			<div class="col-xs-2 divide-text" id="divide-1" onselectstart="javascript:return false;">分隔线&nbsp;<span class="glyphicon glyphicon-chevron-up"></span></div>
			<div class="divide-line col-xs-5"></div>
		</div>
		
		<div id="extend-properties1">
			<!-- <input type="hidden" name="selected_rules" value=""> --><!-- 保存当前的attend-setting-rule的顺序号和编号，格式如："1,123;2,567;3,999" -->
			<div class="row-ptr sidepage-inner modified-description">
				<span class="sidepage-inner-text inner-tips"><?php if (!empty($entity['modify_time']) && !empty($entity['modify_user_name'])) {?>
					最后修改：<?php echo substr($entity['modify_time'], 0, 16);?> by <?php echo $entity['modify_user_name']; }?>
				</span>
			</div>
		</div>
	</form>
	</div>
	<div style="height: 100px;"></div>
</div>

<input type="hidden" id="property-content-height-input" value="0">
<script type="text/javascript">
var virtualDate = '<?php echo $virtualDate;?>'; //参考日期
var rules = []; //考勤规则列表

$(document).ready(function() {
	var ptrType = PTR_TYPE;
	
	var $propertyContainer = $('.sidepage-property-container');
	var isEdit = <?php echo $isEdit?'true':'false';?>;
	var canEdit = true;
	var logonUserId = '<?php echo $userId;?>';
	var $form = $('form#attend_setting_form');
	var $extPropContainer1 = $form.find('#extend-properties1');
	
<?php 
	if (array_key_exists('rules', $entity)) {
?>
	var rulesJson = '<?php echo escapeQuotes(strictJson(json_encode($entity['rules'])));?>';
	rules = json_parse(rulesJson);
<?php } else {?>
	var rulIndex = 1;
	rules = [createBlankAttendSettingRuleObject(rulIndex, 0, true, [createBlankAttendSettingTimeObject(rulIndex, 1)])];
<?php }?>
	
	//属性容器自适应高度
	function calculatePropertyContentHeight() {
		$('#property-content-height-input').val($('body').outerHeight()-$('.side-toolbar').outerHeight()-10);
	}
	$(window).bind('resize', function(e) {
		calculatePropertyContentHeight();
	});
	calculatePropertyContentHeight();
	adjustContainerHeight3UsingE($('#property-content-height-input'), $propertyContainer, 10, true, 'resize_property_container');
   	
	//自定义滚动条
	customScrollbarUsingE($propertyContainer, 30, true);
	//注册事件-点击分隔线
	bindStretchClick($('#divide-1'), $('#extend-properties1'));

	//在界面展现考勤规则及相关的考勤时间段
	if (rules.length>0) {
		var $beforePosition = $extPropContainer1.children('.modified-description');
		//渲染考勤规则
		for (var i=0; i<rules.length; i++) {
			var rule = rules[i];
			renderAttendRuleElement(rule, virtualDate, $extPropContainer1, $beforePosition);
		}
	}
	
	//计算总工作时长
	calculAttendAllWorkDuration($form);
	//初始化考勤时段时间选择器
	createAttendTimeDatetimePicker(virtualDate);
	//自动调整工作时长和休息时长的值
	$form.on('changeDate', '.attend-setting-time input[name^="signin_time_"], .attend-setting-time input[name^="signout_time_"]', function(e) {
		adjustAttendSettingTimeDuration(this);
		//计算总工作时长
		calculAttendAllWorkDuration($form);
	}).on('change', '.attend-setting-time input[name^="rest_duration_"], .attend-setting-time input[name^="work_duration_"]', function(e) {
		adjustAttendSettingTimeDuration(this);
		//计算总工作时长
		calculAttendAllWorkDuration($form);
	});
	
	//周工作日选择项变更事件
	$form.on('change', 'input[type="checkbox"].week_value', function() {
		//调整(启用或禁用)其它周工作日(同日)选项
		adjustAttendSettingWeekday($form, $(this).attr('data-wday'));
		//计算总工作时长
		calculAttendAllWorkDuration($form);
	});
	
	//创建考勤适用对象'自动搜索小部件'
	registerAutoloadWidget('#approver-objahead.objahead-wrapper', function(e, data_id, data_name, data_extprop, data_type) {
			//隐藏'自动搜索小部件'
			$parent = $(this).parents('.objahead-wrapper').addClass('ebtw-hide').prev('.select-target-option');
			
			//插入选中人员
			data_type -= 10;
			$addBtnElement = $parent.children('.ptr-add-target');
			var selectedTargets = new Array({target_id:data_id, target_name:data_name, target_type:data_type, ext_name:'', user_account:data_extprop});
			for (var i=0; i<selectedTargets.length; i++) {
				var target = selectedTargets[i];
				target.canEdit = canEdit;
				
				if ($parent.find('.selected-target[data-target-id="'+target.target_id+'"][data-target-type="'+target.target_type+'"]').length==0) {
					if (target.target_type==3 && logonUserId!=target.target_id)
						target.talkToPerson = true;
					
					$addBtnElement.before(laytpl($('#attendance-target-script').html()).render(target));
				}
			}
		}, [{type:11, type_name:'企业', entity:'enterprise', placeholder:'输入企业名称', show:true}
			, {type:12, type_name:'部门', entity:'group', placeholder:'输入部门名称'}, {type:13, type_name:'用户', entity:'person', placeholder:'输入用户名称'}]
	);

	//重现考勤适用对象
	var targetsJson = '<?php if (isset($entity['targets'])) echo escapeQuotes(strictJson(json_encode($entity['targets']))); else echo '[]';?>';
	reappearAttendTargets(logonUserId, targetsJson, '#attend_target', canEdit);
	//管理考勤范围对象
	registerManageAttendTargets('#attend_target', function(e) {
		$oaWrapper = $(this).parent().nextAll('.objahead-wrapper');
		var $targetE = $('.ptr-add-target');
		var $targetParent = $targetE.parent();
		
		//计算显示位置
		var left = $targetE.offset().left - $targetParent.offset().left + 15;
		var top = $targetE.offset().top - $targetParent.offset().top - 7;
		if (top<0) top = 0;

		//显示'自动搜索小部件'，并触发一次选择框的选中事件
		$oaWrapper.css('top', top).removeClass('ebtw-hide').find('select').trigger('select2:select');

		//再次调整显示位置
		var positionWidth = $targetParent.offset().left + $targetParent.width() - $targetE.offset().left;
		if (positionWidth >= $oaWrapper.width())
			$oaWrapper.css('left', left);
		else
			$oaWrapper.css('left', $targetParent.width()-$oaWrapper.width());
	});
	
	//阻止'自动搜索小部件'点击事件传递
	$form.find('.objahead-wrapper').click(function(e) {
		stopPropagation(e);
	});
	//点击右侧页面隐藏'自动搜索小部件'
	$('#sidepage').click(function(e) {
		$('.objahead-wrapper').addClass('ebtw-hide');
	});
	
	//点击'新建规则'按钮
	$form.on('click', '.add-attend-rule', function() {
		var rulIndex = parseInt($(this).parents('.attend-setting-rule').attr('data-rule-index'));
		var newRulIndex = 0;
		//尝试往后检索插入位置
		for(var i=rulIndex+1; i<=7; i++) {
			if ($form.find('.attend-setting-rule[data-rule-index="' + i + '"]').length==0) {
				newRulIndex = i;
				break;
			}
		}
		//如果后面已满，尝试往前检索插入位置
		if (newRulIndex==0) {
			for(var i=1; i<rulIndex; i++) {
				if ($form.find('.attend-setting-rule[data-rule-index="' + i + '"]').length==0) {
					newRulIndex = i;
					break;
				}
			}
		}
		
		//允许创建一条新规则
		if (newRulIndex>0) {
			var aSetId = $form.find('input[name="set_id"]').val();
			var rule = createBlankAttendSettingRuleObject(newRulIndex, aSetId, false, [createBlankAttendSettingTimeObject(newRulIndex, 1)]);	
			var $beforePosition = null;
			var $afterPosition = null;
			if (newRulIndex>rulIndex)
				$afterPosition = $extPropContainer1.find('.attend-setting-rule[data-rule-index="' + (newRulIndex-1) + '"]').next().next();
			else {
				for (var i=1; i<rulIndex; i++) {
					$beforePosition = $extPropContainer1.find('.attend-setting-rule[data-rule-index="' + (newRulIndex+i) + '"]');
					if ($beforePosition.length>0)
						break;
				}
			}
			//渲染这个考勤规则元素
			renderAttendRuleElement(rule, virtualDate, $extPropContainer1, $beforePosition, $afterPosition);
			
			//达到7个考勤规则，隐藏'新建'按钮
			if ($form.find('.attend-setting-rule').length>=7)
				$form.find('.add-attend-rule').addClass('ebtw-hide');
			//初始化考勤时段时间选择器
			createAttendTimeDatetimePicker(virtualDate);
			//计算总工作时长
			calculAttendAllWorkDuration($form);
		}
	});

	//点击'新建考勤时段'按钮
	$form.on('click', '.add-attend-time', function() {
		var timIndex = parseInt($(this).parents('.attend-setting-time').attr('data-tim-index'));
		var newTimIndex = 0;
		$timesContainer = $(this).parents('div[data-assigned-id]');
		var rulIndex = $timesContainer.attr('data-assigned-id');
		
		//尝试往后检索插入位置
		for(var i=timIndex+1; i<=4; i++) {
			if ($timesContainer.find('.attend-setting-time[data-tim-index="' + i + '"]').length==0) {
				newTimIndex = i;
				break;
			}
		}
		//如果后面已满，尝试往前检索插入位置
		if (newTimIndex==0) {
			for(var i=1; i<timIndex; i++) {
				if ($timesContainer.find('.attend-setting-time[data-tim-index="' + i + '"]').length==0) {
					newTimIndex = i;
					break;
				}
			}
		}
		
		//允许创建一条新考勤时段
		if (newTimIndex>0) {
			var timeObj = createBlankAttendSettingTimeObject(rulIndex, newTimIndex);
			
			var $beforePosition = null;
			var $afterPosition = null;
			if (newTimIndex>timIndex) {
				$afterPosition = $timesContainer.find('.attend-setting-time[data-tim-index="' + (newTimIndex-1) + '"]');
			} else {
				for (var i=1; i<timIndex; i++) {
					$beforePosition = $timesContainer.find('.attend-setting-time[data-tim-index="' + (newTimIndex+i) + '"]');
					if ($beforePosition.length>0)
						break;
				}
			}

			//渲染这个考勤时段元素
			renderAttendTimeElement(timeObj, virtualDate, $timesContainer, $beforePosition, $afterPosition);
			//达到4个考勤时段，隐藏'新建'按钮
			if ($timesContainer.find('.attend-setting-time').length>=4)
				$timesContainer.find('.add-attend-time').addClass('ebtw-hide');
			//初始化考勤时段时间选择器
			createAttendTimeDatetimePicker(virtualDate);			
			//计算总工作时长
			calculAttendAllWorkDuration($form);
		}
	});

	//点击'删除规则'按钮
	$form.on('click', '.del-attend-rule', function() {
		var $ruleE = $(this).parents('.attend-setting-rule');
		var rulIndex = $ruleE.attr('data-rule-index');
		$ruleE.find('input[name^="att_rul_del_"]').val(1);
		$form.find('[data-assigned-id="' + rulIndex + '"]').remove();
		
		//如果是新建的记录，直接删除规则元素
		if ($ruleE.find('.new-rule').length>0) {
			$ruleE.remove();
		} else {//如果是已存在的记录，显示即将删除状态
			//$ruleE.find('.control-label').remove();
			$ruleE.find('.rule-properties').remove();
			$ruleE.find('.delete-mark').removeClass('ebtw-hide');
		}

		//少于7个考勤规则，显示'新建'按钮
		if ($form.find('.attend-setting-rule').length<7)
			$form.find('.add-attend-rule').removeClass('ebtw-hide');
		//计算总工作时长
		calculAttendAllWorkDuration($form);
		//调整(启用或禁用)周工作日选项
		for (var i=0; i<=6; i++)
			adjustAttendSettingWeekday($extPropContainer1, i);
	});
	
	//点击'删除时间段'按钮
	$form.on('click', '.del-attend-time', function() {
		var $timeE = $(this).parents('.attend-setting-time');
		var timIndex = $timeE.attr('data-time-index');
		$timeE.find('input[name^="att_tim_del_"]').val(1);
		$timesContainer = $(this).parents('div[data-assigned-id]');
		
		//如果是新建的记录，直接删除规则元素
		if ($timeE.find('.new-time').length>0) {
			$timeE.remove();
		} else {//如果是已存在的记录，显示即将删除状态
			//$timeE.find('.control-label').remove();
			$timeE.find('.time-properties').remove();
			$timeE.find('.delete-mark').removeClass('ebtw-hide');
		}

		//少于4个考勤规则，显示'新建'按钮
		if ($timesContainer.find('.attend-setting-time').length<4)
			$timesContainer.find('.add-attend-time').removeClass('ebtw-hide');
		//计算总工作时长
		calculAttendAllWorkDuration($form);
	});
	
	//点击全局保存按钮
	$('.side-save').click(function(e) {
		//考勤规则名称
		var aSetName = $form.find('input[name="att_set_name"]').val();
		if (!checkContentLength(ptrType, 'attend_setting_name', aSetName && aSetName.trim()))
		    return false;

		//考勤适用对象
		//格式如："1,1000000000000030;2,999001;3,80"
		var att_set_targets = [];
		$form.find('#attend_target .selected-target').each(function() {
			att_set_targets.push($(this).attr('data-target-type') + ',' + $(this).attr('data-target-id'));
		});
		$form.find('[name="att_set_targets"]').val(att_set_targets.join(';'));
		
		//考勤规则
		var $ruleElements = $form.find('.attend-setting-rule');
		
		//验证考勤规则数量
		if ($ruleElements.length>7) {
			layer.msg('最多只能设置7个考勤规则', {icon: 2});
			return false;
		}
		
		//遍历处理每个考勤规则
		var weekDays = []; //暂存周工作日情况
		var overWeekDay = false; //是否有重复的周工作日设置
		var overAttendTimes = false; //考勤时段数量是否超过最大限制
		var validResult = true; //验证结果

		//遍历验证每一个考勤规则
		$ruleElements.each(function() {
			$ruleElement = $(this);
			var ruleIndex = $ruleElement.attr('data-rule-index');
			var rulId = $ruleElement.attr('data-rule-id');

			//检查是否等待删除的考勤规则
			if ($ruleElement.find('input[type="hidden"][name="att_rul_del_'+ruleIndex+'"]').val()==1) {
				logjs_info('to be delete ruleIndex = ' + ruleIndex);
				return true;
			}
			
			//检查周工作日数据
			$ruleElement.find('.week_value:checked').each(function() {
				var wDay = $(this).attr('data-wday');
				if ($.inArray(wDay, weekDays)>0) {
					overWeekDay = true;
					return false;
				}
				weekDays.push(wDay);
			});
			if (overWeekDay)
				return false;
			
			var $timeElements = $ruleElement.nextAll('[data-assigned-id="'+ruleIndex+'"]').find('.attend-setting-time');
			//检查考勤时段数据
			if ($timeElements.length>4) {
				overAttendTimes = true;
				return false;
			}
			
			//遍历验证每一个考勤时间段
			for (var i=0; i<$timeElements.length; i++) {
				var $timeE1 = $($timeElements[i]);
				var timIndex = $timeE1.attr('data-tim-index');

				//检查是否等待删除的考勤时间段
				if ($timeE1.find('input[type="hidden"][name="att_tim_del_'+ruleIndex+'_'+timIndex+'"]').val()==1) {
					logjs_info('to be delete ruleIndex = ' + ruleIndex + ', timIndex = ' + timIndex);
					continue;
				}
				
				var signinTime = translateAttendStandardSignTime($timeE1.find('input[name^="signin_time_"]').val());
				var signoutTime = translateAttendStandardSignTime($timeE1.find('input[name^="signout_time_"]').val());
				//时间段输入不完整
				if (signinTime.length==0 || signoutTime.length==0) {
					validResult = false;
					layer.msg('[考勤规则'+ruleIndex+'][考勤时段'+timIndex+']的 签到时间、签退时间都不可以填空', {icon:5});
					return false;
				}
				
				var signinTimeStamp1 = new Date(signinTime).getTime();
				var signoutTimeStamp1 = new Date(signoutTime).getTime();
				//验证时间先后合法性
				if (signinTimeStamp1 >signoutTimeStamp1) {
					validResult = false;
					layer.msg('[考勤规则'+ruleIndex+'][考勤时段'+timIndex+']的 签到时间 必须在 签退时间之前', {icon:5});
					return false;
				}
				
				//验证时间段交叉
				for (var j=i+1; j<$timeElements.length; j++) {
					var $timeE2 = $($timeElements[j]);
					var timIndex2 = $timeE2.attr('data-tim-index');
					
					//检查是否等待删除的考勤时间段
					if ($timeE2.find('input[type="hidden"][name="att_tim_del_'+ruleIndex+'_'+timIndex2+'"]').val()==1)
						continue;
					
					var signinTime2 = translateAttendStandardSignTime($timeE2.find('input[name^="signin_time_"]').val());
					var signoutTime2 = translateAttendStandardSignTime($timeE2.find('input[name^="signout_time_"]').val());
					//时间段输入不完整
					if (signinTime2.length==0 || signoutTime2.length==0)
						continue;
					
					var signinTimeStamp2 = new Date(signinTime2).getTime();
					var signoutTimeStamp2 = new Date(signoutTime2).getTime();
					if ((signinTimeStamp1>signinTimeStamp2 && signinTimeStamp1<signoutTimeStamp2)
							|| (signoutTimeStamp1>signinTimeStamp2 && signoutTimeStamp1<signoutTimeStamp2)
							|| (signinTimeStamp1==signinTimeStamp2) || signoutTimeStamp1==signoutTimeStamp2) {
						validResult = false;
						layer.msg('[考勤时段'+timIndex+'][考勤时段'+timIndex2+']发生交叉', {icon:5});
						return false;
					}
				}
			}
		});

		if (!validResult)
			return false;
		
		//验证是否有重复周工作日
		if (overWeekDay) {
			layer.msg('不可以有重复的周工作日', {icon: 2});
			return false;
		}
		//验证考勤时段数量是否超过最大限制
		if (overAttendTimes) {
			layer.msg('每个考勤规则最多4个考勤时段', {icon: 2});
			return false;
		}

		//定义函数：提交保存考勤规则
		var subFunc = function() {
			//询问确认后执行
			askForConfirmSubmit('真的要保存考勤规则吗？', '保存考勤规则', null, function() {
				var loadIndex = layer.load(2);
				callAjax(getServerUrl() + 'attendance/attendance_setting.php', $form.serialize(), null, function(result) {
					layer.close(loadIndex);
					
					if (result.code==0) {
						closeSidepage();
						layer.msg('保存成功');

						//1秒后刷新页面
						setTimeout(function() {
							refreshMainViewActually(); //依据具体情况下刷新主视图
						}, 1000);
					} else {
						layer.msg('保存失败', {icon:2});
					}
				}, function(XMLHttpRequest, textStatus, errorThrown) {
					layer.close(loadIndex);
					layer.msg('保存失败', {icon:2});
				});
			});
		};
		
		//缺少考勤适用对象的提示
		if (att_set_targets.length==0) {
			askForConfirmSubmit('缺少【考勤适用对象】，确定要继续吗？', '缺少考勤适用对象', null, subFunc);
		} else {
			subFunc();
		}
	});
});
</script>