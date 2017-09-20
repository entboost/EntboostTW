<?php
include dirname(__FILE__).'/../attendance/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../attendance/include.php';
require_once dirname(__FILE__).'/../attendance/attendance_functions.php';

	$isEdit = false;
	$canEdit = true;
	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
	$entCode = $_SESSION[USER_ENTERPRISE_CODE]; //企业编号
	
	$holId = get_request_param('hol_set_id', '0');
	$entity = null;
	$virtualYear = date('Y', time()); //参考年份
	
	if (!empty($holId)) { //编辑假期设置
		$isEdit = true;
		
		$holInstance = HolidaySettingService::get_instance();
		//获取设置记录
		$holResults = $holInstance->getOneRecordByPrimaryKey($holId);
		if ($holResults===false) {
			log_err('get holidaySetting record error');
			return;
		}
		if (count($holResults)==0) {
			log_err("not attendSetting record for $holId");
			return;
		}
		
		$entity  = $holResults[0];
		$holidaySettingIds = array($holId);
		
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
			log_err('getOneRecordByPrimaryKey error for get holidaySetting creator');
			return;
		}
		if (count($uaResult)>0)
			$entity['modify_user_name'] = $uaResult[0]['username'];
		
		//获取适用对象列表
		$targetResults = $holInstance->getHolidayTargets($holidaySettingIds, $entCode, array());
		if ($targetResults===false) {
			log_err('getHolidayTargets error');
			return;
		}
		$entity['targets'] = $targetResults;		
		
	} else { //新建假期设置
		$entity = array('hol_set_id'=>'0', 'period'=>'0', 'flag'=>'0');
	}
	log_warn($entity);
	$title =($isEdit?'查看':'新建').'节假日设置';
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
</div>

<div class="col-xs-12 ebtw-horizontal-nopadding-right sidepage-property-container mCustomScrollbar" data-mcs-theme="dark-3">
	<div style="padding: 5px 0;">
	<form class="form-horizontal" id="holiday_form">
		<input type="hidden" name="action_type" value="53">
		<input type="hidden" name="hol_id" value="<?php echoField($entity, 'hol_set_id', 0);?>">

		<div class="form-group">
			<label  class="col-xs-2 control-label">假期名称</label>
			<div class="col-xs-5">
				<input name="holiday_name" class="form-control" type="text" placeholder="填写节假日名称，如国庆放假" value="<?php echoField($entity, 'name');?>">
			</div>
		</div>
		
		<div class="form-group">
			<label  class="col-xs-2 control-label">假期类型</label>
            <div class="col-xs-10 sidepage-radio-line">
				<label class="radio-inline">
					<input type="radio" name="period" id="period-disposable" value="0" 
						<?php if (!$canEdit || (!empty($holId) && $entity['period']!=0)){?>disabled<?php }?> <?php inputChecked($entity,'period',0,0,true);?>><span>一次性假期</span>
				</label>
				<div class="tips-icon-wrap"><div class="form-control-static radio-inline-icon tips-icon fa fa-question" title="例如2016-11-23公司周年庆，放假一天"></div></div>
				<label class="radio-inline">
					<input type="radio" name="period" id="period-year" value="1" 
						<?php if (!$canEdit || (!empty($holId) && $entity['period']!=1)){?>disabled<?php }?> <?php inputChecked($entity,'period',1,1,true);?>><span>每年假期</span>
				</label>
				<div class="tips-icon-wrap"><div class="form-control-static radio-inline-icon tips-icon fa fa-question" title="例如10-01~10-07每年国庆放假"></div></div>
				<label class="radio-inline">
					<input type="radio" name="period" id="period-month" value="2" 
						<?php if (!$canEdit || (!empty($holId) && $entity['period']!=2)){?>disabled<?php }?> <?php inputChecked($entity,'period',2,2,true);?>><span>每月假期</span>
				</label>
				<div class="tips-icon-wrap"><div class="form-control-static radio-inline-icon tips-icon fa fa-question" title="例如每月1~3日放假"></div></div>
				<label class="radio-inline">
					<input type="radio" name="period" id="period-week" value="3" 
						<?php if (!$canEdit || (!empty($holId) && $entity['period']!=3)){?>disabled<?php }?> <?php inputChecked($entity,'period',3,3,true);?>><span>每周假期</span>
				</label>
				<div class="tips-icon-wrap"><div class="form-control-static radio-inline-icon tips-icon fa fa-question" title="例如每周几至周几放假"></div></div>		
			</div>
		</div>
		
		<div class="form-group">
			<label  class="col-xs-2 control-label">假期计算</label>
			<div class="col-xs-10 sidepage-radio-line">
				<label class="radio-inline">
					<input type="radio" name="flag" id="flag-full-day" value="0" 
						<?php if (!$canEdit){?>disabled<?php }?> <?php inputChecked($entity,'flag',0,0,true);?>><span>全天</span>
				</label>
				<div class="tips-icon-wrap"><div class="form-control-static radio-inline-icon tips-icon fa fa-question" title="例如国庆放假，按天放假"></div></div>
				<label class="radio-inline">
					<input type="radio" name="flag" id="flag-forenoon" value="1" 
						<?php if (!$canEdit){?>disabled<?php }?> <?php inputChecked($entity,'flag',1,1,true);?>><span>上午</span>
				</label>
				<div class="tips-icon-wrap"><div class="form-control-static radio-inline-icon tips-icon fa fa-question" title="例如某些单位3月8日妇女节放假半天"></div></div>
				<label class="radio-inline">
					<input type="radio" name="flag" id="flag-afternoon" value="2" 
						<?php if (!$canEdit){?>disabled<?php }?> <?php inputChecked($entity,'flag',2,2,true);?>><span>下午</span>
				</label>
				<div class="tips-icon-wrap"><div class="form-control-static radio-inline-icon tips-icon fa fa-question" title="例如某些单位3月8日妇女节放假半天"></div></div>			
			</div>
		</div>
		<?php 
		$startTime = '';
		$stopTime = '';
		if (!empty($entity)) {
			if (!empty($entity['start_time']))
				$startTime = substr($entity['start_time'], 0, 10);
			if (!empty($entity['stop_time']))
				$stopTime = substr($entity['stop_time'], 0, 10);
		}
		?>
		<!-- 一次性假期 -->
		<div class="form-group <?php inputChecked($entity,'period',0,999,true, true, 'ebtw-hide');?>" id="time_range">
			<label  class="col-xs-2 control-label">假期开始</label>
			<div class="col-xs-1">
				<div class="input-group select-time filterbar-date-control-small" style="display: inline-table;">
					<div class="input-group filterbar-date-control-small">
						<input type="hidden" name="start_time" value="">
						<input id="start-time" type="text" class="form-control" readonly value="<?php echo $startTime; ?>">
					</div>
				</div>
			</div>
			
			<label  class="col-xs-2 control-label">假期结束</label>
			<div class="col-xs-1">
				<div class="input-group select-time filterbar-date-control-small" style="display: inline-table;">
					<div class="input-group filterbar-date-control-small">
						<input type="hidden" name="stop_time" value="">
						<input id="stop-time" type="text" class="form-control" readonly value="<?php echo $stopTime; ?>">
					</div>
				</div>
			</div>
		</div>
		<!-- 每年假期 -->
		<div class="form-group <?php inputChecked($entity,'period',1,999,true, true, 'ebtw-hide');?>" id="year_time_range">
			<label  class="col-xs-2 control-label">假期开始</label>
			<div class="col-xs-1">
				<input type="hidden" name="year_period_from" value="">
				<div class="input-group select-time filterbar-date-control-short" style="display: inline-table;">
					<div class="input-group filterbar-date-control-short">
						<input id="year-period-from" type="text" class="form-control" readonly value="">
					</div>
				</div>
			</div>
			
			<label  class="col-xs-1 col-xs-1p5 control-label">假期结束</label>
			<div class="col-xs-1">
				<input type="hidden" name="year_period_to" value="">
				<div class="input-group select-time filterbar-date-control-short" style="display: inline-table;">
					<div class="input-group filterbar-date-control-short">
						<input id="year-period-to" type="text" class="form-control" readonly value="">
					</div>
				</div>
			</div>
		</div>
		<!-- 每月假期 -->
		<div class="form-group <?php inputChecked($entity,'period',2,999,true, true, 'ebtw-hide');?>" id="month_time_range">
			<label  class="col-xs-2 control-label">假期开始</label>
			<div class="col-xs-1 col-xs-1p5">
				<input type="hidden" name="month_period_from" value="">
				<div class="input-group select-time filterbar-date-control-small" style="display: inline-table;">
					<span class="holiday-period-inline" style="padding-right: 5px;">每月</span>
					<div class="input-group filterbar-date-control-shortest">
						<input id="month-period-from" type="text" class="form-control" readonly value="">
					</div>
					<span class="holiday-period-inline" style="padding-left: 5px;">日</span>
				</div>
			</div>
			
			<label  class="col-xs-1 col-xs-1p5 control-label" style="padding-right: 0;">假期结束</label>
			<div class="col-xs-1 col-xs-1p5">
				<input type="hidden" name="month_period_to" value="">
				<div class="input-group select-time filterbar-date-control-small" style="display: inline-table;">
					<span class="holiday-period-inline" style="padding-right: 5px;">每月</span>
					<div class="input-group filterbar-date-control-shortest">
						<input id="month-period-to" type="text" class="form-control" readonly value="">
					</div>
					<span class="holiday-period-inline" style="padding-left: 5px;">日</span>
				</div>
			</div>
		</div>
		<!-- 每周假期 -->
		<div class="form-group <?php inputChecked($entity,'period',3,999,true, true, 'ebtw-hide');?>" id="week_time_range">
			<label  class="col-xs-2 control-label">假期开始</label>
			<div class="col-xs-1">
				<select name="week_period_from" class="filterbar-date-control-short">
					<option value="">----</option>
					<option value="0">周日</option>
					<option value="1">周一</option>
					<option value="2">周二</option>
					<option value="3">周三</option>
					<option value="4">周四</option>
					<option value="5">周五</option>
					<option value="6">周六</option>
				</select>
			</div>
			
			<label  class="col-xs-1 col-xs-1p5 control-label">假期结束</label>
			<div class="col-xs-1">
				<select name="week_period_to" class="filterbar-date-control-short">
					<option value="">----</option>
					<option value="0">周日</option>
					<option value="1">周一</option>
					<option value="2">周二</option>
					<option value="3">周三</option>
					<option value="4">周四</option>
					<option value="5">周五</option>
					<option value="6">周六</option>
				</select>
			</div>
		</div>
        
		<div class="form-group form-inline">
			<label  class="col-xs-2 control-label">假期时长</label>
			<div class="col-xs-5">
				<div class="form-control-static" style="padding-top: 0;">
					<span id="holiday_duration" class="attend-duration"><?php echoField($entity, 'holiday_duration', 0)?></span>
				</div>&nbsp;天
			</div>
		</div>
				
		<div class="col-xs-12 div-divide-top-pull">
			<div class="divide-line-all2"></div>
		</div>
		
		<div class="form-group row-ptr form-inline">
			<input type="hidden" name="holiday_set_targets" value=""><!-- 适用范围对象，格式如："1,1000000000000030;2,999001;3,80" -->
			<label class="col-xs-2 control-label">适用范围</label>
			<div class="col-xs-9" style="position: relative;">
				<div id="holiday_target" class="form-control-static select-target-option" style="display: block;">
					<div class="ptr-add-target"><span class="glyphicon glyphicon-plus"></span>添加适用范围</div>
				</div>
	            <div id='holiday-objahead' class="objahead-wrapper select-noborder ebtw-hide"></div>
			</div>
		</div>
		
		<div class="col-xs-12 div-divide-top-pull">
			<div class="divide-line-all2"></div>
		</div>
		
		<div class="sidepage-inner modified-description">
			<span class="sidepage-inner-text inner-tips"><?php if (!empty($entity['modify_time']) && !empty($entity['modify_user_name'])) {?>
				最后修改：<?php echo substr($entity['modify_time'], 0, 16);?> by <?php echo $entity['modify_user_name']; }?>
			</span>
		</div>
		
		<div class="form-group row-ptr form-inline">
                <div class="col-xs-12">
                    <div class="sidepage-attachment-btn">
	                    <div class="sidepage-btn ">
	                        <button type="button" class="btn btn-default" id="btn_cancel">关 闭</button>
                        	<button type="button" class="btn btn-primary" id="btn_save"><span class="glyphicon glyphicon-ok"></span> 保存</button>
	                    </div>
	                    <div class="ebtw-clear"></div>
		            </div>
            	</div>
            </div>
	</form>
	</div>
	<div style="height: 230px;"></div>
</div>

<input type="hidden" id="property-content-height-input" value="0">
<script type="text/javascript">
var virtualYear = '<?php echo $virtualYear;?>'; //参考日期
var holEntity;

$(document).ready(function() {
	var ptrType = PTR_TYPE;
	
	var entityJson = '<?php echo escapeQuotes(strictJson(json_encode($entity)));?>';
	var holEntity = json_parse(entityJson);
	
	var $propertyContainer = $('.sidepage-property-container');
	var isEdit = <?php echo $isEdit?'true':'false';?>;
	var canEdit = <?php echo $canEdit?'true':'false';?>;
	var logonUserId = '<?php echo $userId;?>';
	var $form = $('form#holiday_form');

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
	//初始化日期选择器
   	createDefaultDatetimePicker('#start-time, #stop-time', 'yyyy-mm-dd', 2, 2, 4); //一次性假期
   	createDefaultDatetimePicker('#year-period-from, #year-period-to', 'mm-dd yyyy', 2, 2, 2, null, null, null, null, '01-01 '+virtualYear, '12-31 '+virtualYear); //每年假期
   	createDefaultDatetimePicker('#month-period-from, #month-period-to', 'dd yyyy-mm', 2, 2, 2, null, null, null, null, '01 '+virtualYear+'-01', '31 '+virtualYear+'-01'); //每月假期

   	//时间段控件赋值
	if (holEntity) {
		if (holEntity.period==0) { //一次性假期
			
		} else if (holEntity.period==1) { //每年假期
			$form.find('#year-period-from').val(specalDigitToPartDate(holEntity.period_from) + ' ' + virtualYear);
			$form.find('#year-period-to').val(specalDigitToPartDate(holEntity.period_to) + ' ' + virtualYear);
		} else if (holEntity.period==2) { //每月假期
			$form.find('#month-period-from').val(prefixInteger(holEntity.period_from, 2) + ' ' + virtualYear + '-01');
			$form.find('#month-period-to').val(prefixInteger(holEntity.period_to, 2) + ' ' + virtualYear + '-01');
		} else if (holEntity.period==3) { //每周假期
			$form.find('select[name="week_period_from"]:has(option[value="'+holEntity.period_from+'"])').val(holEntity.period_from);
			$form.find('select[name="week_period_to"]:has(option[value="'+holEntity.period_to+'"])').val(holEntity.period_to);
		}
	}
	//计算假期时长
	calculHolidayDuration($form);
	
	//美化下拉选择框
   	$form.find('select[name="week_period_from"], select[name="week_period_to"]').select2({minimumResultsForSearch:Infinity});

	//时间段变更事件
	$form.on('changeDate', '#start-time, #stop-time, #year-period-from, #year-period-to, #month-period-from, #month-period-to', function(e) {
		calculHolidayDuration($form); //计算假期时长
	});

	//注册事件-'假期计算'选项变更事件
	$form.find('input[type="radio"][name="flag"]').on('change', function(e) {
		calculHolidayDuration($form); //计算假期时长
	});
	
	$form.find('select[name="week_period_from"], select[name="week_period_to"]').on('change', function() {
		calculHolidayDuration($form); //计算假期时长
	});
   	
	//注册事件-点击假期类型选项
   	$form.find('input[type="radio"][name="period"]').on('change', function(e) {
   		$form.find('#time_range').addClass('ebtw-hide');
   		$form.find('#year_time_range').addClass('ebtw-hide');
   		$form.find('#month_time_range').addClass('ebtw-hide');
   		$form.find('#week_time_range').addClass('ebtw-hide');
		if (e.target.value==0) //一次性假期
			$form.find('#time_range').removeClass('ebtw-hide');
		else if (e.target.value==1) //每年假期
			$form.find('#year_time_range').removeClass('ebtw-hide');
		else if (e.target.value==2) //每月假期
			$form.find('#month_time_range').removeClass('ebtw-hide');
		else if (e.target.value==3) //每周假期
			$form.find('#week_time_range').removeClass('ebtw-hide');

		//计算假期时长
		calculHolidayDuration($form);
   	});
   	//$form.find('input[type="radio"][name="period"][checked="true"]').trigger('change');
	
	//创建考勤适用对象'自动搜索小部件'
	registerAutoloadWidget('#holiday-objahead.objahead-wrapper', function(e, data_id, data_name, data_extprop, data_type) {
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
	reappearAttendTargets(logonUserId, targetsJson, '#holiday_target', canEdit);
	//管理考勤范围对象
	registerManageAttendTargets('#holiday_target', function(e) {
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

	//注册事件-取消按钮
	$('#btn_cancel').click(function() {
		closeSidepage();
	});

	//注册事件-保存按钮
	$('#btn_save').click(function() {
		//假期配置名称
		var holName = $form.find('input[name="holiday_name"]').val();
		if (!checkContentLength(ptrType, 'holiday_name', holName && holName.trim()))
		    return false;
		
		var period = parseInt($form.find('input[type="radio"][name="period"]:checked').val());
		switch (period) {
		case 0: //一次性假期
			var startTime = $form.find('#start-time').val();
			var stopTime = $form.find('#stop-time').val();
			//时间段输入不完整
			if (startTime.length==0 || stopTime.length==0) {
				layer.msg('请填入假期开始、假期结束的日期', {icon: 2});
				return false;
			}
			if (new Date(startTime).getTime()>new Date(stopTime).getTime()) {
				layer.msg('请留意假期开始-结束的先后顺序', {icon: 2});
				return false;
			}
			$form.find('input[name="start_time"]').val(startTime+' 00:00:00');
			$form.find('input[name="stop_time"]').val(stopTime+' 23:59:59');
			
			break;
		case 1: //每年假期
			var startTime = $form.find('#year-period-from').val();
			var stopTime = $form.find('#year-period-to').val();
			//时间段输入不完整
			if (startTime.length==0 || stopTime.length==0) {
				layer.msg('请填入假期开始、假期结束的日期', {icon: 2});
				return false;
			}
			startTime = startTime.substr(6, 4) + '-' + startTime.substr(0, 5);
			stopTime = stopTime.substr(6, 4) + '-' + stopTime.substr(0, 5);
			if (new Date(startTime).getTime()>new Date(stopTime).getTime()) {
				layer.msg('请留意假期开始-结束的先后顺序', {icon: 2});
				return false;
			}
			$form.find('input[name="year_period_from"]').val(startTime.substr(5, 5));
			$form.find('input[name="year_period_to"]').val(stopTime.substr(5, 5));
			
			break;
		case 2: //每月假期
			var startTime = $form.find('#month-period-from').val();
			var stopTime = $form.find('#month-period-to').val();
			//时间段输入不完整
			if (startTime.length==0 || stopTime.length==0) {
				layer.msg('请填入假期开始、假期结束的日期', {icon: 2});
				return false;
			}
			startTime = startTime.substr(3, 7) + '-' + startTime.substr(0, 2);
			stopTime = stopTime.substr(3, 7) + '-' + stopTime.substr(0, 2);
			if (new Date(startTime).getTime()>new Date(stopTime).getTime()) {
				layer.msg('请留意假期开始-结束的先后顺序', {icon: 2});
				return false;
			}
			$form.find('input[name="month_period_from"]').val(startTime.substr(8, 2));
			$form.find('input[name="month_period_to"]').val(stopTime.substr(8, 2));
					
			break;
		case 3: //每周假期
			var periodFrom = $form.find('select[name="week_period_from"]').val();
			var periodTo = $form.find('select[name="week_period_to"]').val();
			if (periodFrom.length==0 || periodTo.length==0) {
				layer.msg('请选择假期开始和假期结束日期', {icon: 2});
				return false;
			}
			if (parseInt(periodFrom) > parseInt(periodTo)) {
				layer.msg('请留意假期开始-结束的先后顺序', {icon: 2});
				return false;
			}
			break;
		}
		
		//考勤适用对象
		//格式如："1,1000000000000030;2,999001;3,80"
		var holiday_set_targets = [];
		$form.find('#holiday_target .selected-target').each(function() {
			holiday_set_targets.push($(this).attr('data-target-type') + ',' + $(this).attr('data-target-id'));
		});
		$form.find('[name="holiday_set_targets"]').val(holiday_set_targets.join(';'));

		//定义函数：提交保存假期配置
		var subFunc = function() {
			//询问确认后执行
			askForConfirmSubmit('真的要保存假期配置吗？', '保存假期配置', null, function() {
				var loadIndex = layer.load(2);
				callAjax(getServerUrl() + 'attendance/attendance_holiday.php', $form.serialize(), null, function(result) {
					layer.close(loadIndex);
					
					if (result.code==0) {
						closeSidepage();
						layer.msg('保存成功');
						
						//1秒后刷新页面
						setTimeout(function() {
							refreshMainViewActually(); //依据具体情况下刷新主视图
						}, 600);
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
		if (holiday_set_targets.length==0) {
			askForConfirmSubmit('缺少【假期适用对象】，确定要继续吗？', '缺少假期适用对象', null, subFunc);
		} else {
			subFunc();
		}
	});
});
</script>