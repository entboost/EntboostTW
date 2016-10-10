<?php 
include dirname(__FILE__).'/../plan/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../plan/include.php';

$isEdit = isset($_REQUEST[$PTRIdFieldName]);
if ($isEdit) {
	$actionTitle = '编辑'; 
	$toolbarIcon = 'glyphicon-edit';
} else {
	$actionTitle = '新建';
	$toolbarIcon = 'glyphicon-plus';
}
?>
<div class="side-toolbar col-xs-12">
	<div class="side-toolbar-icon">
    	<span class="glyphicon <?php echo $toolbarIcon;?>"></span> <?php echo $actionTitle;?>计划
    </div>
    <div class="side-close">
        <span class="glyphicon glyphicon-remove" title="关闭"></span>
    </div>
    <div class="side-fullscreen" data-type="0">
        <span class="glyphicon glyphicon-fullscreen" title="全屏"></span>
    </div>
</div>
<?php 
if ($isEdit) {
	$embed = 1;
	include dirname(__FILE__).'/../plan/get_one.php';
	if (!isset($entity)) {
		echo '<div class="col-xs-12"><h4>记录不存在或没有访问权限</div>';
		return;
	}
} else {
	$entity = null;
}

//标记是否从查看详情页面跳转过来本页面
$fromViewPage = get_request_param('reserved_from_view_page', '0');

$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
?>

<div class="col-xs-12 ebtw-horizontal-nopadding-right sidepage-property-container mCustomScrollbar" data-mcs-theme="dark-3">
        <form class="form-horizontal">
        	<input type="hidden" name="status" value="<?php echoField($entity, 'status', '1');?>">
            <div class="form-group">
                <label  class="col-xs-2 control-label">计划周期</label>
                <div class="col-xs-10">
                    <label class="radio-inline">
                        <input type="radio" name="period" class="period-radio" id="day" value="1" <?php inputChecked($entity,'period',1,1);?>><span>日计划</span>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="period" class="period-radio" id="week" value="2" <?php inputChecked($entity,'period',2,1);?>><span>周计划</span>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="period" class="period-radio" id="month" value="3" <?php inputChecked($entity,'period',3,1);?>>月计划
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="period" class="period-radio" id="season" value="4" <?php inputChecked($entity,'period',4,1);?>>季计划
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="period" class="period-radio" id="year" value="5" <?php inputChecked($entity,'period',5,1);?>>年计划
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="period" class="period-radio" id="custom" value="6" <?php inputChecked($entity,'period',6,1);?>>自定义
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label  class="col-xs-2 control-label">时&emsp;&emsp;段</label>
                <div class="col-xs-8">
                	<?php 
                		if (!empty($entity)) {
                			$fDate1 = subStrOfDateTime($entity, 'start_time');
                			$fDate2 = subStrOfDateTime($entity, 'stop_time');
                		} else {
                			$fDate1 = $fDate2 = date("Y-m-d", time());
                		}
                	?>
                	<input type="hidden" name="start_time" value="<?php echo subStrOfDateTime($entity, 'start_time');?>">
                	<input type="hidden" name="stop_time" value="<?php echo subStrOfDateTime($entity, 'stop_time');?>">
                	
                	<!-- 日计划 -->
                    <div class="input-group period-item period-day plan-time filterbar-date-control">
                        <input id="period-day-date" type="text" class="form-control" readonly value="<?php echo $fDate1;?>">
                        <span id="period-day-date-addon" class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
					<!-- 自定义计划 -->

                    <div class="input-group period-item period-custom plan-time filterbar-date-control2">
                            <div class="input-group filterbar-date-control">
                                <input id="period-custom-start" type="text" class="form-control" readonly value="<?php echo $fDate1;?>">
                                <span id="period-custom-start-addon" class="input-group-addon time-icon"><span class="glyphicon glyphicon-calendar"></span></span>
                            </div>
                            <div class="filterbar-date-icon">&nbsp;<span class="glyphicon glyphicon-minus"></span>&nbsp;</div>
                            <div class="input-group filterbar-date-control">
                                <input id="period-custom-end" type="text" class="form-control" readonly value="<?php echo $fDate2;?>">
                                <span id="period-custom-end-addon" class="input-group-addon time-icon"><span class=" glyphicon glyphicon-calendar"></span></span>
                            </div>
                    </div>
                    
                    <div class="input-group period-item period-week filterbar-select-control">
                        <select class="form-control side-select">
                        </select>
                    </div>
                    
                    <div class="input-group period-item period-month filterbar-select-control">
                        <select class="form-control side-select">
                        </select>
                    </div>
                    <div class="input-group period-item period-season filterbar-select-control">
                        <select class="form-control side-select">
                        </select>
                    </div>
                    <div class="input-group period-item period-year filterbar-select-control">
                        <select class="form-control side-select">
                        </select>
                    </div>

                </div>
            </div>
            <!-- 标题 -->
            <div class="form-group row-ptr">
                <label  class="col-xs-2 control-label"><span style="color:red">*</span>计划事项</label>
                <div class="col-xs-9">
                    <input name="plan_name" class="form-control" type="text" placeholder="填写计划事项  (Enter回车保存)" value="<?php
                    	if (!empty($entity))
                    		echoField($entity, 'plan_name', '', true);
                    	else {
                    		echo escapeQuotesToHtml(get_request_param('reserved_new_ptr_name', '')); //聊天记录转新建计划自动填单
                    	}
                    	?>">
                </div>
            </div>
            <!-- 内容 -->
            <div class="form-group row-ptr">
                <label  class="col-xs-2 control-label">详细内容</label>
                <div class="col-xs-9">
                    <textarea name="remark" class="form-control" placeholder="填写详细内容 (Enter回车换行)"><?php echoField($entity, 'remark');?></textarea>
                </div>
            </div>
            <!-- 分隔线 -->
			<div class="col-xs-12 div-divide-top-pull">
				<div class="divide-line col-xs-5"></div>
				<div class="col-xs-2 divide-text" id="divide-1" onselectstart="javascript:return false;">分隔线&nbsp;<span class="glyphicon glyphicon-chevron-up"></span></div>
				<div class="divide-line col-xs-5"></div>
			</div>
			<div id="extend-properties1">
   			<!-- 重要程度 -->
            <div class="form-group row-ptr">
                <label class="col-xs-2 control-label">重要程度</label>
                <div class="col-xs-4">
                    <label class="radio-inline">
                        <input type="radio" name="important" class="important-radio" id="normal" value="0" <?php inputChecked($entity,'important',0,0);?>><span class="ebtw-color-important0">普通</span>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="important" class="important-radio" id="important" value="1" <?php inputChecked($entity,'important',1,0);?>><span class="ebtw-color-important1">重要</span>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="important" class="important-radio" id="urgent" value="2" <?php inputChecked($entity,'important',2,0);?>><span class="ebtw-color-important2">紧急</span>
                    </label>
                </div>
            </div>
            <!-- 分类 -->
            <div class="form-group row-ptr">
            	<label class="col-xs-2 control-label">分&emsp;&emsp;类</label>
				<div class="ptr-class">
	                <select class="form-control side-select" name="class_id">
	                	<option value="0">--选择分类--</option>
	                </select>
                </div>
                <div class="ptr-add-class"><span class="glyphicon glyphicon-plus"></span></div>
            </div>
            <!-- 评审人 -->
            <div class="form-group row-ptr form-inline">
	            <label class="col-xs-2 control-label">评审人</label>
	            <div class="col-xs-9">
	            	<div class="dropdown" id="approval_user">
	            		<?php 
		            		$approvalPerson = getShares(1, $entity, true);
	            		?>
	            		<input type="hidden" value="<?php echoField($approvalPerson, 'share_uid');?>" name="old_approval_user_id" />
	            		<input type="hidden" value="<?php echoField($approvalPerson, 'share_uid');?>" name="approval_user_id" />
	            		<input type="text" value="<?php 
	            			if (!empty($approvalPerson))
	            				echoField($approvalPerson, 'share_name', '', true);
	            			else 
	            				echo '--请选择评审人--';
	            			?>" name="approval_user_name" 
	            			readonly="readonly" class="form-control normal-readonly-style cursor-click" data-toggle="dropdown"/>
					</div>
	            </div>
            </div>
            </div>
            <!-- 分隔线 -->
			<div class="col-xs-12 div-divide-top-pull">
				<div class="divide-line col-xs-5"></div>
				<div class="col-xs-2 divide-text" id="divide-2" onselectstart="javascript:return false;">分隔线&nbsp;<span class="glyphicon glyphicon-chevron-up"></span></div>
				<div class="divide-line col-xs-5"></div>
			</div>
			
			<div id="extend-properties2">
            <div class="form-group row-ptr form-inline">
            	<div class="col-xs-2">
                    <div class="ebtw-file-upload" style="text-align: right;">
                        <span class="glyphicon glyphicon-paperclip"></span>
                        <div id="file_upload" class="webuploader-container"><div class="webuploader-pick" onselectstart="javascript:return false;" style="-moz-user-select:none;">上传附件</div></div>
                        <input type="file" class="file_upload_input" name="up_file"><!-- file控件name字段必要，否则不能上传文件 -->
                    </div>
                </div>
                <div class="col-xs-5">
					<div class="ebtw-file-upload-list">
						<ul></ul>
					</div>
				</div>
                <div class="col-xs-5">
                    <div class="ebtw-openflag">
                    	<label class="label-inline">开放：</label>
                        <label class="radio-inline">
                            <input type="radio" name="open_flag" value="0" <?php inputChecked($entity,'open_flag',0,0);?>>上级
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="open_flag" value="1" <?php inputChecked($entity,'open_flag',1,0);?>>所有人
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="open_flag" value="2" <?php inputChecked($entity,'open_flag',2,0);?>>限相关人
                        </label>
                        <div class="ebtw-clear"></div>
                    </div>
                    
                    <div class="sidepage-attachment-btn">
	                    <div class="sidepage-btn ">
	                        <button type="button" class="btn btn-default" id="btn_cancel">取 消</button>
	                        <button type="button" class="btn btn-primary" id="btn_save"><span class="glyphicon glyphicon-ok"></span> 保 存</button>
	                    </div>
	                    <div class="ebtw-clear"></div>
		            </div>
            	</div>
            </div>
            </div>
        </form>
 </div>

<input type="hidden" id="property-content-height-input" value="0">
<script type="text/javascript">
$(document).ready(function() {
	var $propertyContainer = $('.sidepage-property-container');
	var ptrType = <?php echo $PTRType;?>;
	<?php 
	if ($isEdit)
		echo 'var isEdit = true;';
	else
		echo 'var isEdit = false;';	
	?>
	
	<?php if ($isEdit) {?>
	var ptrId = '<?php echoField($entity, $PTRIdFieldName);?>';
	
	//创建工具栏
	var allowedActions = <?php echoField($entity, 'allowedActions', '[]');?>; //操作权限
    var isDeleted = <?php echoField($entity, "is_deleted");?>;
	createSideToolbarButtons(ptrType, <?php echoField($entity, "status");?>, allowedActions, isDeleted, {3:{deleted:isDeleted}}, true);
	
    //注册事件-点击工具栏按钮
    registerSideToolbarActions('<?php echo $userId;?>', ptrType, ptrId);
    <?php }?>
	
	//周期
	<?php if (!empty($entity)){ echo 'var targetDate ="'.subStrOfDateTime($entity, 'start_time').'";';} else {echo 'var targetDate = null;';}?>
	var html;
	//生成周日期备选项
	html = laytpl($('#select-option-week-script').html()).render(nextSeveralWeeks(5, -1, targetDate));
	$('.period-week>select').prepend(html).find('>option:eq(1)').attr('selected','selected');
	//生成月日期备选项
	html = laytpl($('#select-option-month-script').html()).render(nextSeveralMonths(8, -1, targetDate));
	$('.period-month>select').prepend(html).find('>option:eq(1)').attr('selected','selected');
	//生成季度日期备选项
	html = laytpl($('#select-option-season-script').html()).render(nextSeveralSeasons(5, -1, targetDate));
	$('.period-season>select').prepend(html).find('>option:eq(1)').attr('selected','selected');
	//生成年日期备选项
	html = laytpl($('#select-option-year-script').html()).render(nextSeveralYears(5, -1, targetDate));
	$('.period-year>select').prepend(html).find('>option:eq(1)').attr('selected','selected');
	
	//管理分类备选项
	var selectedClassId = '<?php if (!empty($entity)){ echo $entity->class_id; }?>';
	fillPtrClassSelect(ptrType,'form', selectedClassId);
	//注册事件-新建分类按钮
	registerAddPTRClass(ptrType, '.ptr-add-class', function(codeTable) { //分类菜单创建完毕
		for (classId in codeTable) {
			refreshPTRMenuBadges([100], ptrType, null, {reserved_query_of_calss:true, class_id:classId});
		}
	}, null, function(codeTable, datas) {
		//生成分类代码对照表
		dictClassNameOfPlan = {0:dictClassNameOfPlan[0]==undefined?'-':dictClassNameOfPlan[0]};
		$.extend(dictClassNameOfPlan, codeTable);
	}, function(classId, className, classType) {
		fillPtrClassSelect(ptrType, 'form', classId);
	});
	
	//生成备选评审人下拉菜单
	createApprovalUserList('#approval_user', 'approval', '--请选择评审人--');
	
	//下拉框美化
	var selectOptions = {theme: "default", minimumResultsForSearch: Infinity};
	selectOptions.width='255px';
	$(".period-week .side-select, .period-season .side-select").select2(selectOptions);
	selectOptions.width='105px';
	$(".period-month .side-select, .period-year .side-select").select2(selectOptions);
	selectOptions.width='130px';
	$(".ptr-class .side-select").select2(selectOptions);
	
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
	
	//textarea自适应高度
	$('textarea[name="remark"]').autoHeight();
	
	//注册事件-周期选择
	//控制显示/隐藏时间范围选择项
	$('input.period-radio').change(function() {
		$('.period-item').css('display','none');
		$('.period-'+$(this).attr('id')).css('display','inline-table');
	});
	<?php if (!empty($entity)){?>
		$('input.period-radio[value="<?php echo $entity->period; ?>"]').attr("checked",'checked').trigger('change');
	<?php } ?>
	
   	//初始化时间范围选择器-日
   	createDefaultDatetimePicker('#period-day-date', 'yyyy-mm-dd', 2, 2, 4); 
   	createDefaultDatetimePicker('#period-day-date-addon', 'yyyy-mm-dd', 2, 2, 4, 'period-day-date', 'yyyy-mm-dd');

  	//初始化时间范围选择器-自定义(开始)
   	createDefaultDatetimePicker('#period-custom-start', 'yyyy-mm-dd', 2, 2, 4); 
   	createDefaultDatetimePicker('#period-custom-start-addon', 'yyyy-mm-dd', 2, 2, 4, 'period-custom-start', 'yyyy-mm-dd');
	
  	//初始化时间范围选择器-自定义(结束)
   	createDefaultDatetimePicker('#period-custom-end', 'yyyy-mm-dd', 2, 2, 4); 
   	createDefaultDatetimePicker('#period-custom-end-addon', 'yyyy-mm-dd', 2, 2, 4, 'period-custom-end', 'yyyy-mm-dd');

	//注册事件-点击分隔线
	bindStretchClick($('#divide-1'), $('#extend-properties1'));
	bindStretchClick($('#divide-2'), $('#extend-properties2'));

	//注册事件-点击新增/删除附件按钮
	var attaType = 0; //文档(本体)附件
	var onlyOne = false;
	var isOnlyView = false;
	var loadExist = isEdit;
	registerAttachmentActions(isEdit, isOnlyView, ptrType, isEdit?ptrId:0, attaType, '#sidepage .ebtw-file-upload', function(result, resourceId) {
		if (isEdit)
			layer.msg('上传计划附件成功');
	}, loadExist, onlyOne, '#sidepage .ebtw-file-upload-list', '.attachment-remove', function() {
		
	});
	
	//注册事件-取消按钮
	$('#btn_cancel').click(function() {
		var fromViewPage = 0;
		<?php if ($isEdit) {?>
		var fromViewPage = <?php echo $fromViewPage;?>;
		<?php }?>
		
		if (fromViewPage==0)
			closeSidepage();
		else {
			ptrDetailsAction(ptrType, ptrId, null);
		}
	});

	//注册事件-保存按钮
	$('#btn_save').click(function() {
		var $form = $(this).parents('form');

		//计划事项
		var planName = $form.find('input[name="plan_name"]').val();
		if (!checkContentLength(ptrType, 'plan_name', planName && planName.trim()))
		    return false;
		//计划内容
		var remark = $form.find('textarea[name="remark"]').val();
		if (!checkContentLength(ptrType, 'remark', remark && remark.trim()))
		    return false;
		
		var $periodRadio = $form.find('input.period-radio:checked');
		var period = parseInt($periodRadio.val());
		var $startTime = $form.find('input[name="start_time"]');
		var $stopTime = $form.find('input[name="stop_time"]');
		switch(period) {
		case 1: //日计划
			var date = $form.find('#period-day-date').val();
			if (date.length==0) {
				layer.msg('时段必填', {icon:2});
				return false;
			}
			$startTime.val(date+' 00:00:00');
			$stopTime.val(date+' 23:59:59');
			break;
		case 6: //自定义计划
			var startDate = $form.find('#period-custom-start').val();
			var endDate = $form.find('#period-custom-end').val();
			if (startDate.length==0 || endDate.length==0) {
				layer.msg('时段必填', {icon:2});
				return false;
			}
			$startTime.val(startDate+' 00:00:00');
			$stopTime.val(endDate+' 23:59:59');			
			break;
		case 2: //周计划
		case 3: //月计划
		case 4: //季度计划
		case 5: //年计划
			var className = 'period-'+$periodRadio.attr('id');
			var $option = $form.find('.'+className+'>select>option:selected');
			var startDate = $option.attr('data-startdate');
			var endDate = $option.attr('data-enddate');
			if (startDate.length==0 || endDate.length==0) {
				layer.msg('时段必填', {icon:2});
				return false;
			}
			$startTime.val(startDate+' 00:00:00');
			$stopTime.val(endDate+' 23:59:59');
			break;
		}
		//验证时段先后合法性
		if (new Date($startTime.val()).getTime() > new Date($stopTime.val()).getTime()) {
			layer.msg('开始时间 必须在 结束时间之前', {icon:2});
			return false;
		}
		//验证新建计划结束日期合理性
		if (!isEdit) {
			var today = new Date();
			today.setDate(today.getDate()-1);
			var yesterday = new Date($.D_ALG.formatDate(today, 'yyyy-mm-dd 00:00:00'));
			if (new Date($stopTime.val()).getTime() < yesterday.getTime()) {
				layer.msg('结束时间不能早于昨天', {icon:2});
				return false;
			}
		}
		
		<?php 
		echo 'var actionTitle = "'.$actionTitle.'";';
		echo 'var page = "';
		if ($isEdit) {
			echo 'saveUpdate.php?pk_plan_id='.$pid.'";';
		} else { 
			echo 'saveCreate.php";';
		}
		?>
		var url = getServerUrl()+'plan/'+page;
		
		//执行保存
		var loadIndex = layer.load(2);
		//logjs_info($form.serialize());
		callAjax(url, $form.serialize(), function(datas) {
			var result = didLoadedDataPreprocess('original', datas, true);

			var completeUploads = new Array(); //暂存已完成的上传(无论失败与否)
			//检测关闭正在加载的界面
			function checkClose(title, close, i, total) {
				var scroll = <?php echo get_request_param('scroll','-1');?>;
				if (close) {
					layer.close(loadIndex);
					closeSidepage();
					layer.msg(title);
					
					refreshMainViewActually(scroll); //依据具体情况下刷新主视图
				}
					
				completeUploads.push(i);
				if (completeUploads.length==total) {
					layer.close(loadIndex);
					closeSidepage();
					layer.msg(title);

					refreshMainViewActually(scroll); //依据具体情况下刷新主视图
				}
			}
			
			if (typeof result != 'boolean') {
				var title='';
				if (isEdit) { //编辑
					if (result.affected==1)
						title = actionTitle+'计划成功';
					else 
						title = actionTitle+'计划没有效果';

					checkClose(title, true);
				} else { //新建
					title = actionTitle+'计划成功';				
					refreshPTRMenuBadges([1], 1); //刷新菜单角标(badge)
					
					//上传附件
					var $liElements = $form.find('.ebtw-file-upload-list li');
					var fileCount = $liElements.length;
					if (fileCount==0) {
						checkClose(title, true);
					} else {
						var fromType = 10 + parseInt(ptrType);
						var ptrId = result.id;
						
						//遍历上传文件
						var liElements = new Array();
						$liElements.each(function() {
							liElements.push(this);
						});
						var i=0;
						executeSendfile(fromType, ptrId, attaType, liElements, fileCount, i, title, true, checkClose);
					}
				}
			} else {
				layer.msg(actionTitle+'计划失败', {icon:2});
				layer.close(loadIndex);
			}
		}, function(XMLHttpRequest, textStatus, errorThrown) {
			layer.close(loadIndex);
			layer.msg(actionTitle+'计划失败', {icon:2});
		});
	});
});
</script>