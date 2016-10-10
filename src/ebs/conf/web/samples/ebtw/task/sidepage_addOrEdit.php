<?php 
include dirname(__FILE__).'/../task/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../task/include.php';

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
    	<span class="glyphicon <?php echo $toolbarIcon;?>"></span> <?php echo $actionTitle;?>任务
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
	include dirname(__FILE__).'/../task/get_one.php';
	if (!isset($entity)) {
		echo '<div class="col-xs-12"><h4>记录不存在或没有访问权限</div>';
		return;
	}
} else {
	$entity = null;
	
	//计划转任务：获取指定计划的数据
	$translateToTask = get_request_param('translate_to_task');
	$planId = get_request_param('plan_id');
	
	if ($translateToTask==1 && !empty($planId)) {
		if (!EBModelBase::checkDigit($planId, $outErrMsg)) {
			$json = ResultHandle::errorToJsonAndOutput($outputMsg, $outputMsg, $output);
			return;
		}
		$jsonPlan = get_plan($planId);
		get_first_entity_from_json($jsonPlan, $plan, $tmpObj1);
	}
}

//标记是否从查看详情页面跳转过来本页面
$fromViewPage = get_request_param('reserved_from_view_page', '0');

$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
$userName = $_SESSION[USER_NAME_NAME]; //当前用户的名称
$userAccount = $_SESSION[USER_ACCOUNT_NAME]; //当前用户的账号
?>

<div class="col-xs-12 ebtw-horizontal-nopadding-right sidepage-property-container mCustomScrollbar" data-mcs-theme="dark-3">
        <form class="form-horizontal">
        	<?php if ($isEdit) {?>
        	<input type="hidden" name="op_type" value="61">
        	<?php }?>
        	<input type="hidden" name="status" value="<?php echoField($entity, 'status', '1');?>">
            <div class="form-group">
                <label  class="col-xs-2 control-label">时&emsp;&emsp;段</label>
                <div class="col-xs-10">
                	<?php 
                		if (!empty($entity)) {
                			$fDate1 = subStrOfDateTime2($entity, 'start_time');
                			$fDate2 = subStrOfDateTime2($entity, 'stop_time');
                		} else {
                			$fDate1 = $fDate2 = date("Y-m-d", time());
                			$fDate1 .= ' 00:00';
                			$fDate2 .= ' 23:59';
                		}
                	?>
                	<input type="hidden" name="start_time" value="<?php echo subStrOfDateTime2($entity, 'start_time');?>">
                	<input type="hidden" name="stop_time" value="<?php echo subStrOfDateTime2($entity, 'stop_time');?>">
                	
                    <div class="input-group period-custom task-time filterbar-date-control3">
                            <div class="input-group filterbar-date-control1P5">
                                <input id="period-custom-start" type="text" class="form-control" readonly value="<?php echo $fDate1;?>">
                                <span id="period-custom-start-addon" class="input-group-addon time-icon"><span class="glyphicon glyphicon-calendar"></span></span>
                            </div>
                            <div class="filterbar-date-icon">&nbsp;<span class="glyphicon glyphicon-minus"></span>&nbsp;</div>
                            <div class="input-group filterbar-date-control1P5">
                                <input id="period-custom-end" type="text" class="form-control" readonly value="<?php echo $fDate2;?>">
                                <span id="period-custom-end-addon" class="input-group-addon time-icon"><span class=" glyphicon glyphicon-calendar"></span></span>
                            </div>
                    </div>
                </div>
            </div>
            <?php 
            if (!empty($planId)) {
            	$planType = 1;
            	echo '<input type="hidden" name="from_type" value="'.$planType.'">';
            	echo '<input type="hidden" name="from_id" value="'.$planId.'">';
            }
            ?>
            <!-- 标题 -->
            <div class="form-group row-ptr">
                <label  class="col-xs-2 control-label"><span style="color:red">*</span>任务标题</label>
                <div class="col-xs-9">
                    <input name="task_name" class="form-control" type="text" placeholder="填写任务标题  (Enter回车保存)" value="<?php 
                    	if (isset($plan)) {
                    		echo escapeQuotesToHtml($plan->plan_name);
                    	} else if (!empty($entity)) {
                    		echoField($entity, 'task_name', '', true);
                    	} else {
                    		echo escapeQuotesToHtml(get_request_param('reserved_new_ptr_name', '')); //聊天记录转新建任务自动填单
                    	}
                    	?>">
                </div>
            </div>
            <!-- 内容 -->
            <div class="form-group row-ptr">
                <label  class="col-xs-2 control-label">详细内容</label>
                <div class="col-xs-9">
                    <textarea name="remark" class="form-control" placeholder="填写详细内容 (Enter回车换行)"><?php if (isset($plan)) echo $plan->remark; else echoField($entity, 'remark');?></textarea>
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
            <!-- 相关人员 -->
            <input type="hidden" name="old_principal_person" value="<?php echoShareFields('share_uid', $entity, 5, 1);?>">
            <input type="hidden" name="old_helper_person" value="<?php echoShareFields('share_uid', $entity, 2);?>">
            <input type="hidden" name="old_sharer_person" value="<?php echoShareFields('share_uid', $entity, 3);?>">
            
            <input type="hidden" name="principal_person" value="">
            <input type="hidden" name="helper_person" value="">
            <input type="hidden" name="sharer_person" value="">
            <!-- 负责人 -->
            <div class="form-group row-ptr form-inline">
	            <label class="col-xs-2 control-label">负责人</label>
	            <div class="col-xs-9">
	            	<div id="principal_person" class="form-control-static select-person-option">
	            		<div class="ptr-add-person"><span class="glyphicon glyphicon-plus"></span>选择负责人</div>
					</div>
	            </div>
            </div>
            <!-- 参与人 -->
            <div class="form-group row-ptr form-inline">
	            <label class="col-xs-2 control-label">参与人</label>
	            <div class="col-xs-9">
	            	<div id="helper_person" class="form-control-static select-person-option">
	            		<div class="ptr-add-person"><span class="glyphicon glyphicon-plus"></span>添加参与人</div>
					</div>
	            </div>
            </div>
            <!-- 共享人 -->
            <div class="form-group row-ptr form-inline">
	            <label class="col-xs-2 control-label">共享人</label>
	            <div class="col-xs-9">
	            	<div id="sharer_person" class="form-control-static select-person-option">
	            		<div class="ptr-add-person"><span class="glyphicon glyphicon-plus"></span>添加共享人</div>
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
	var logonUserId = '<?php echo $userId;?>';
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
	createSideToolbarButtons(ptrType, <?php echoField($entity, "status");?>, allowedActions, false, null, true);

    //注册事件-点击工具栏按钮
    registerSideToolbarActions('<?php echo $userId;?>', ptrType, ptrId);
    <?php }?>

	//管理分类备选项
	var selectedClassId = '<?php if (!empty($entity)){ echo $entity->class_id; }?>';
	fillPtrClassSelect(ptrType, 'form', selectedClassId);
	//注册事件-新建分类按钮
	registerAddPTRClass(ptrType, '.ptr-add-class', function(codeTable) { //分类菜单创建完毕
		for (classId in codeTable) {
			refreshPTRMenuBadges([100], ptrType, null, {reserved_query_of_calss:true, class_id:classId});
		}
	}, null, function(codeTable, datas) {
		//生成分类代码对照表
		dictClassNameOfTask = {0:dictClassNameOfTask[0]==undefined?'-':dictClassNameOfTask[0]};
		$.extend(dictClassNameOfTask, codeTable);
	}, function(classId, className, classType) {
		fillPtrClassSelect(ptrType, 'form', classId);
	});

	//负责人、参与人、共享人
	<?php 
	$shareTypeOfPrincipal = 5;
	$shareTypeOfHelper = 2;
	$shareTypeOfSharer = 3;
	?>
	var personTypes = {principal_person:{share_type:<?php echo $shareTypeOfPrincipal; ?>, only_one:true}, 
			helper_person:{share_type:<?php echo $shareTypeOfHelper; ?>}, 
			sharer_person:{share_type:<?php echo $shareTypeOfSharer; ?>}};

	<?php if (!$isEdit) {?>
		//新建任务时，默认当前用户为负责人
		reappearPtrPersons(logonUserId, personTypes.principal_person, [{share_uid:logonUserId, share_name:'<?php echo $userName;?>', user_account:'<?php echo $userAccount;?>'}], '#principal_person');
	<?php } else {?>
		//在视图重现(负责人、参与人、共享人)
		reappearPtrPersons(logonUserId, personTypes.principal_person, '<?php echoShares($entity, $shareTypeOfPrincipal);?>', '#principal_person');
		reappearPtrPersons(logonUserId, personTypes.helper_person, '<?php echoShares($entity, $shareTypeOfHelper);?>', '#helper_person');
		reappearPtrPersons(logonUserId, personTypes.sharer_person, '<?php echoShares($entity, $shareTypeOfSharer);?>', '#sharer_person');
	<?php }?>
	
	//管理(负责人、参与人、共享人)
	registerManagePtrPersons(logonUserId, personTypes);
	
	//下拉框美化
	var selectOptions = {theme: "default", minimumResultsForSearch: Infinity};
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

  	//初始化时间范围选择器-自定义(开始)
   	createDefaultDatetimePicker('#period-custom-start', 'yyyy-mm-dd hh:ii', 2, 0, 4);
   	createDefaultDatetimePicker('#period-custom-start-addon', 'yyyy-mm-dd hh:ii', 2, 0, 4, 'period-custom-start', 'yyyy-mm-dd hh:ii');
	
  	//初始化时间范围选择器-自定义(结束)
   	createDefaultDatetimePicker('#period-custom-end', 'yyyy-mm-dd hh:ii', 2, 0, 4); 
   	createDefaultDatetimePicker('#period-custom-end-addon', 'yyyy-mm-dd hh:ii', 2, 0, 4, 'period-custom-end', 'yyyy-mm-dd hh:ii');

	//注册事件-点击分隔线
	bindStretchClick($('#divide-1'), $('#extend-properties1'));
	bindStretchClick($('#divide-2'), $('#extend-properties2'));

	//注册事件-点击新增/删除附件按钮
	var attaType = 0; //文档(本体)附件
	var onlyOne = false;
	var loadExist = isEdit;
	var isOnlyView = false;
	registerAttachmentActions(isEdit, isOnlyView, ptrType, isEdit?ptrId:0, attaType, '#sidepage .ebtw-file-upload', function(result, resourceId) {
		if (isEdit)
			layer.msg('上传任务附件成功');
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
		
		//任务标题
		var taskName = $form.find('input[name="task_name"]').val();
		if (!checkContentLength(ptrType, 'task_name', taskName && taskName.trim()))
		    return false;
		//任务内容
		var remark = $form.find('textarea[name="remark"]').val();
		if (!checkContentLength(ptrType, 'remark', remark && remark.trim()))
		    return false;		
		
		//时段
		var startDate = $form.find('#period-custom-start').val();
		var endDate = $form.find('#period-custom-end').val();
		if (startDate.length==0 || endDate.length==0) {
			layer.msg('时段必填', {icon:5});
			return false;
		}
		startDate += ':00';
		endDate += ':59';
		//验证时段先后合法性
		if (new Date(startDate).getTime() > new Date(endDate).getTime()) {
			layer.msg('开始时间 必须在 结束时间之前', {icon:5});
			return false;
		}
		$form.find('input[name="start_time"]').val(startDate);
		$form.find('input[name="stop_time"]').val(endDate);			

		//验证必须有负责人
		if ($('#principal_person .selected-person').length==0) {
			layer.msg('必须选择一个负责人', {icon:5});
			return false;
		}
		
		//负责人、参与人、共享人
		var disposePerson = function(personFieldName) {
			var personStr = '';
			$('#'+personFieldName+' .selected-person').each(function() {
				var userId = $(this).attr('data-user-id');
				personStr += (userId+',');
			});
			personStr = personStr.length>0?personStr.substring(0, personStr.length-1):personStr;
			$form.find('input[name="'+personFieldName+'"]').val(personStr);
		};
		disposePerson('principal_person');
		disposePerson('helper_person');
		disposePerson('sharer_person');
		
		<?php 
		echo 'var actionTitle = "'.$actionTitle.'";';
		echo 'var page = "';
		if ($isEdit) {
			echo 'saveUpdate.php?pk_task_id='.$pid.'";';
		} else { 
			echo 'saveCreate.php";';
		}
		?>
		
		var url = getServerUrl()+'task/'+page;
		
		//执行保存
		var loadIndex = layer.load(2);
		callAjax(url, $form.serialize(), function(datas) {
			var result = didLoadedDataPreprocess('original', datas, true);

			var completeUploads = new Array(); //暂存已完成的上传(无论失败与否)
			//检测关闭正在加载的界面
			function checkClose(title, close, i, total) {
				if (close) {
					layer.close(loadIndex);
					closeSidepage();
					layer.msg(title);
					
					refreshMainViewActually(); //依据具体情况下刷新主视图
				}
					
				completeUploads.push(i);
				if (completeUploads.length==total) {
					layer.close(loadIndex);
					closeSidepage();
					layer.msg(title);
					
					refreshMainViewActually(); //依据具体情况下刷新主视图
				}
			}
			
			if (typeof result != 'boolean') {
				var title='';
				if (isEdit) { //编辑
					if (result.affected==1)
						title = actionTitle+'任务成功';
					else 
						title = actionTitle+'任务没有效果';

					checkClose(title, true);
				} else { //新建
					//layer.msg(actionTitle+'任务成功，编号:'+result.id);
					title = actionTitle+'任务成功';
					refreshPTRMenuBadges([1,2,3], 2); //刷新菜单角标(badge)

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
						$liElements.each(function(){
							liElements.push(this);
						});		
						var i=0;
						executeSendfile(fromType, ptrId, attaType, liElements, fileCount, i, title, true, checkClose);
					}
				}
			} else {
				layer.msg(actionTitle+'任务失败', {icon:2});
				layer.close(loadIndex);
			}
		}, function(XMLHttpRequest, textStatus, errorThrown) {
			layer.close(loadIndex);
			layer.msg(actionTitle+'任务失败', {icon:2});
		});
	});
});
</script>