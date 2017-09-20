<?php
include dirname(__FILE__).'/../attendance/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/../attendance/include.php';
require_once dirname(__FILE__).'/../attendance/attendance_functions.php';

$recId = get_request_param('rec_id', '0'); //考勤记录编号
$reqId = get_request_param('req_id', '0'); //考勤审批申请编号
$selectReqType = get_request_param('select_req_type'); //要求预选中的考勤申请类型(仅在新建审批申请有效)

$userId = $_SESSION[USER_ID_NAME];
$entity = null;
$actionTitle2 = '';
$actionTitle3 = '';
$shareType = 6;
$canEdit = false;

if (empty($recId) && empty($reqId)) {
	$actionTitle = '新建审批：';
	$toolbarIcon = 'glyphicon-plus';
	$canEdit = true;
} else {
	if (empty($reqId)) { //通过rec_id获取考勤记录及相关的审批记录
		$result = AttendRecordService::get_instance()->getRecordIncludeReqByRecId($userId, $recId);
		if ($result===false) {
			log_err('getRecordIncludeReqByRecId error');
			return;
		}
		
		if (count($result)>0) {
			$entity = $result[0];
			$reqId = $entity['att_req_id'];
			
			if (!empty($reqId)) {
				//获取考勤审批的关联资料
				getAttendReqAssociatedInfo($reqId, $shareType, $entity, $reqRecs);
				//审批状态
				$actionTitle = '审批'.$ATTENDANCE_REQ_STATE_ARRY[$entity['req_status']].'：';
				$toolbarIcon = 'glyphicon-eye-open';
			} else {
				$actionTitle = '新建审批：';
				$toolbarIcon = 'glyphicon-plus';
				$canEdit = true;
			}
			
			//标题2
			if (array_key_exists('standard_signin_time', $entity)) {
				$actionTitle2 = $entity['attend_date'].' '.substr($entity['standard_signin_time'], 0, 5).'-'.substr($entity['standard_signout_time'], 0, 5)." 考勤段";
			} else 
				$actionTitle2 = '';
			
			//解析考勤状态名称
			if (array_key_exists('att_rec_id0_state', $entity)) {
				$arry = getAttendRecStateAndRecIdFieldName($entity, $recId);
				if (!empty($arry)) {
					$recState = intval($arry[0]);
					$recStateDic = splitAttendRecState($recState);
					foreach ($recStateDic as $subDic) {
						if (empty($actionTitle3))
							$actionTitle3 = $subDic[1];
						else
							$actionTitle3 .= ('、'.$subDic[1]);
					}
				}
			} else
				$actionTitle3 = '';
		} else {
			$actionTitle = '新建审批：';
			$toolbarIcon = 'glyphicon-plus';
			$canEdit = true;
		}
	} else { //通过req_id获取考勤审批记录
		//验证是否数字
		if (!EBModelBase::checkDigit($reqId, $errMsg)) {
			log_err($errMsg);
			return;
		}
		
		$reqInstance = AttendReqService::get_instance();
		$result = $reqInstance->getAttendReq($reqId);
		if ($result===false) {
			log_err('get attendReq error');
			return;
		}
		
		if (count($result)>0) {
			$entity = $result[0];
			//映射需要兼容的几个字段
			$entity['req_create_time'] = $entity['create_time'];
			$entity['req_last_time'] = $entity['last_time'];
			$entity['req_start_time'] = $entity['start_time'];
			$entity['req_stop_time'] = $entity['stop_time'];
			$entity['req_req_duration'] = $entity['req_duration'];
			$entity['req_attend_date'] = $entity['attend_date'];
			//获取考勤审批的关联资料
			getAttendReqAssociatedInfo($reqId, $shareType, $entity, $reqRecs);
			//审批状态
			$actionTitle = '审批'.$ATTENDANCE_REQ_STATE_ARRY[$entity['req_status']].'：';
			$toolbarIcon = 'glyphicon-eye-open';
		}
	}
	
	if (!empty($reqId) && $userId==$entity['user_id'] && in_array($entity['req_status'], array(0, 3, 4)))
		$canEdit = true;
}
?>

<div class="side-toolbar col-xs-12">
	<div class="side-toolbar-icon">
    	<span class="glyphicon <?php echo $toolbarIcon;?>"></span>
    	<span class="action-title"><?php echo $actionTitle;?></span><span class="action-title2"><?php echo $actionTitle2;?></span>
    	<span class="action-title3"><?php echo $actionTitle3;?></span>
    </div>
    <div class="side-close">
        <span class="glyphicon glyphicon-remove" title="关闭"></span>
    </div>
    <div class="side-fullscreen" data-type="0">
        <span class="glyphicon glyphicon-fullscreen" title="全屏"></span>
    </div>
</div>

<div class="col-xs-12 ebtw-horizontal-nopadding-right sidepage-property-container mCustomScrollbar" data-mcs-theme="dark-3">
        <form class="form-horizontal">
        	<input type="hidden" name="req_id" value="<?php echoField($entity, 'att_req_id');?>">
        	<input type="hidden" name="action_type" value="11">
            <div class="form-group">
                <label  class="col-xs-2 control-label">审批类型</label>
                <div class="col-xs-10">
                    <label class="radio-inline">
                        <input type="radio" name="req_type" class="req-type-radio" id="replenish-sign" value="1" <?php if (!$canEdit || (!empty($reqId) && $entity['req_type']!=1)){?>disabled<?php }?> <?php inputChecked($entity,'req_type',1,1,true);?>><span>补签</span>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="req_type" class="req-type-radio" id="work-outside" value="2" <?php if (!$canEdit || (!empty($reqId) && $entity['req_type']!=2)){?>disabled<?php }?> <?php inputChecked($entity,'req_type',2,1);?>><span>外勤</span>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="req_type" class="req-type-radio" id="furlough" value="3" <?php if (!$canEdit || (!empty($reqId) && $entity['req_type']!=3)){?>disabled<?php }?> <?php inputChecked($entity,'req_type',3,1);?>><span>请假</span>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="req_type" class="req-type-radio" id="work-over-time" value="4" <?php if (!$canEdit || (!empty($reqId) && $entity['req_type']!=4)){?>disabled<?php }?> <?php inputChecked($entity,'req_type',4,1);?>><span>加班</span>
                    </label>
                </div>
            </div>
            
            <?php
                $startTime = '';
                $endTime = '';
                $attendDate = '';
                if (!empty($entity)) {
                	$attendDate = $entity['attend_date'];
                	//分析产生startTime和endTime的值
                	if (empty($reqId)) { //新建审批时
                		//默认使用签到签退标准时间段
                		$showedSigninTime = $entity['standard_signin_time'];
                		$showedSignoutTime = $entity['standard_signout_time'];
                		
                		//否则使用实际签到签退时间段
                		if (empty($showedSigninTime)) {
                			$showedSigninTime = $entity['signin_time'];
                			if (!empty($showedSigninTime))
                				$showedSigninTime = substr($showedSigninTime, 11, 5);
                		}
                		if (empty($showedSignoutTime)) {
                			$showedSignoutTime = $entity['signout_time'];
                			if (!empty($showedSignoutTime))
                				$showedSignoutTime = substr($showedSignoutTime, 11, 5);                			
                		}
                			
                		$startTime = $attendDate.' '.(!empty($showedSigninTime)?substr($showedSigninTime, 0, 5):'00:00');
                		$endTime = $attendDate.' '.(!empty($showedSignoutTime)?substr($showedSignoutTime, 0, 5):'00:00');
                	} else { //审批已存在时
                		if (!empty($entity['req_start_time']))
                			$startTime = substr($entity['req_start_time'], 0, 16);
                		if (!empty($entity['req_stop_time']))
                			$endTime = substr($entity['req_stop_time'], 0, 16);
                	}
                }
             ?>
            <div class="form-group" id="attend_date_field">
            	<label  class="col-xs-2 control-label" id="attend_date_label">补签日期</label>
                <div class="col-xs-4">
                    <div class="input-group period-item period-custom select-time filterbar-date-control3" style="display: inline-table;">
                    	<div class="input-group filterbar-date-control-small">
                        	<input id="attend_date" name="attend_date" type="text" class="form-control" readonly value="<?php echo $attendDate; ?>">
                    	</div>
                    </div>
                </div>
            </div>
            
            <div class="form-group" id="time_range">
                <label  class="col-xs-2 control-label" id="start_time_label">开始时间</label>
                <div class="col-xs-3">
                	<input type="hidden" name="start_time" value="">
                    <div class="input-group period-item period-custom select-time filterbar-date-control3" style="display: inline-table;">
                    	<div class="input-group filterbar-date-control1P5">
                        	<input id="period-custom-start" type="text" class="form-control" readonly value="<?php echo $startTime; ?>">
                            <span id="period-custom-start-addon" class="input-group-addon time-icon"><span class="glyphicon glyphicon-calendar"></span></span>
                    	</div>
                    </div>
                </div>
                
                <label  class="col-xs-2 control-label" id="end_time_label">结束时间</label>
                <div class="col-xs-3">
                	<input type="hidden" name="stop_time" value="">
                     <div class="input-group period-item period-custom select-time filterbar-date-control3" style="display: inline-table;">
                     	<div class="input-group filterbar-date-control1P5">
                        	<input id="period-custom-end" type="text" class="form-control" readonly value="<?php echo $endTime; ?>">
                            <span id="period-custom-end-addon" class="input-group-addon time-icon"><span class=" glyphicon glyphicon-calendar"></span></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="req_name" value="">
            
            <div class="form-group ebtw-hide" id="furlough_type">
                <label  class="col-xs-2 control-label">请假类型</label>
                <div class="col-xs-8">
	                <select class="form-control side-select" name="furlough_id" <?php if (!$canEdit){?>disabled="disabled"<?php }?>>
	                	<option value="0">--选择请假类型--</option>
	                </select>
                </div>
            </div>
            <div class="form-group ebtw-hide" id="req_duration">
                <label  class="col-xs-2 control-label">加班时长</label>
                <div class="col-xs-8 sidepage-inner">
                	<div style="width:80px;">
                		<input name="req_duration" class="form-control" type="text" placeholder="格式 0.0" <?php if (!$canEdit) {?>readonly<?php }?>
                			value="<?php if (isset($entity['req_req_duration'])) { echo number_format((intval($entity['req_req_duration'])/60), 1, '.', '');} ?>" 
                			t_value="" o_value="" onblur="digital_onBlur(this);" onkeypress="digital_onkeyPress(this);" onkeyup="digital_onkeyUp(this);">
                	</div>
					<div class="sidepage-inner-text">小时</div>
					<div class="sidepage-inner-text inner-tips">备注：0.5小时=30分钟</div>
                </div>
            </div>
            
            <div class="form-group ebtw-hide" id="rule-times">
           		<input type="hidden" name="rec_ids" value="">
           		<!-- <input type="hidden" name="att_rul_id" value=""> -->
            	<label  class="col-xs-2 control-label">考勤记录</label>
            	<div class="col-xs-10 rule-time checkbox-list"></div>
            </div>
            <!-- 内容 -->
            <div class="form-group row-ptr">
                <label  class="col-xs-2 control-label" id='req_content_label'>申请内容</label>
                <div class="col-xs-9">
                    <textarea name="req_content" class="form-control" placeholder="填写详细内容 (Enter回车换行)" <?php if (!$canEdit){?>readonly<?php }?>><?php echoField($entity, 'req_content');?></textarea>
                </div>
            </div>
			
			<?php
			if ($entity!=null) {
				$approvalUser = getShares(6, $entity, true);
				if ($entity['req_status']==1 && !empty($approvalUser) && $approvalUser->read_flag==0 && $approvalUser->share_uid===$userId) {
					?>
            		<script type="text/javascript">
            			//更新已读状态
            			markReadFlagToRead(11, '<?php echoField($entity, 'att_req_id');?>', '<?php echoField($approvalUser, 'share_id');?>');
            		</script>
					<?php
				}
			}
			?>
            <!-- 审批人 -->
            <input type="hidden" name="old_approver_person" value="<?php echoShareFields('share_uid', $entity, $shareType);?>">
            <input type="hidden" name="approver_person" value="">
            <div class="form-group row-ptr form-inline">
	            <label class="col-xs-2 control-label">审批人</label>
	            <div class="col-xs-9" style="position: relative;">
	            	<div id="approver_person" class="form-control-static select-person-option">
	            		<?php if ($canEdit) {?>
	            		<div class="ptr-add-person"><span class="glyphicon glyphicon-plus"></span>选择审批人</div>
	            		<?php }?>
					</div>
	            	<div id='approver-objahead' class="objahead-wrapper select-noborder ebtw-hide"></div>
	            </div>
            </div>
            
            <!-- 申请人 -->
            <?php if (!empty($entity) && array_key_exists('user_id', $entity)) {?>
            <div class="form-group row-ptr form-inline">
	            <label class="col-xs-2 control-label">申请人</label>
	            <div class="col-xs-9">
	            	<div id="req_person" class="form-control-static select-person-option">
						<div class="selected-person <?php if ($userId!==$entity['user_id']) {?>talk-to-person<?php }?>" 
							data-talk-to-uid="<?php echoField($entity, 'user_id');?>" data-user-id="<?php echoField($entity, 'user_id');?>" data-user-name="<?php echoField($entity, 'user_name');?>">
							<span title="<?php echoField($entity, 'user_account');?>(<?php echoField($entity, 'user_id');?>)"><?php echoField($entity, 'user_name');?></span>
						</div>					
					</div>
	            </div>
            </div>
            <?php }?>
            
            <!-- 分隔线 -->
			<div class="col-xs-12 div-divide-top-pull">
				<div class="divide-line col-xs-5"></div>
				<div class="col-xs-2 divide-text" id="divide-1" onselectstart="javascript:return false;">分隔线&nbsp;<span class="glyphicon glyphicon-chevron-up"></span></div>
				<div class="divide-line col-xs-5"></div>
			</div>
			
			<div id="extend-properties1">
	            <div class="form-group row-ptr form-inline">
	            	<div class="col-xs-2">
	                    <div class="ebtw-file-upload <?php if (!$canEdit) {?>select-disabled<?php }?>" style="text-align: right;">
	                        <span class="glyphicon glyphicon-paperclip"></span>
	                        <div id="file_upload" class="webuploader-container"><div class="webuploader-pick" onselectstart="javascript:return false;" style="-moz-user-select:none;">上传附件</div></div>
	                        <input type="file" class="file_upload_input" name="up_file" <?php if (!$canEdit) {?>disabled="disabled"<?php }?>><!-- file控件name字段必要，否则不能上传文件 -->
	                    </div>
	                </div>
	                <div class="col-xs-5">
						<div class="ebtw-file-upload-list">
							<ul></ul>
						</div>
					</div>
	                <div class="col-xs-5">
	                    <div class="sidepage-attachment-btn">
		                    <div class="sidepage-btn ">
		                        <button type="button" class="btn btn-default" id="btn_cancel">关 闭</button>
		                        <?php if (empty($reqId) || ((($entity['req_status']==3 || $entity['req_status']==4)) && $userId==$entity['user_id'])) {?>
		                        <button type="button" class="btn btn-primary" id="btn_save"><span class="glyphicon glyphicon-ok"></span> 提交审批</button>
		                        <?php }?>
		                        <?php if (!empty($entity) && $entity['req_status']==1) {
		                        	if ($userId==$entity['user_id']) {
		                        	?>
		                        	<button type="button" class="btn btn-primary" id="btn_revoke"><span class="glyphicon glyphicon-ok"></span> 撤销申请</button>
		                        	<?php }
		                        	$share = getShares($shareType, $entity, true, $userId, 1);
		                        	if (!empty($share)) {
		                        	?>
		                        	<button type="button" class="btn btn-warning" id="btn_reject"><span class="glyphicon glyphicon-remove"></span> 拒绝</button>
		                        	<button type="button" class="btn btn-primary" id="btn_pass"><span class="glyphicon glyphicon-ok"></span> 通过</button>
		                        	<?php	
		                        	}
		                        }?>
		                    </div>
		                    <div class="ebtw-clear"></div>
			            </div>
	            	</div>
	            </div>
            </div>
            <div style="height: 150px;"></div>
        </form>
</div>

<input type="hidden" id="property-content-height-input" value="0">
<script type="text/javascript">
$(document).ready(function() {
	var $propertyContainer = $('.sidepage-property-container');
	var selectedAttTimId = <?php echoField($entity, 'att_tim_id', 0);?>; //选中的考勤时间段行关联编号(在新建审批申请时有效)
	var reqId = '<?php echo empty($reqId)?0:$reqId; ?>';
	var selectReqType = <?php echo isset($selectReqType)?$selectReqType:'0';?>;
	var ptrType = 5; //业务类型：5=考勤
	var isEdit = (reqId==0?false:true);
	var canEdit = <?php echo $canEdit?'true':'false';?>;
	
	var reqRecs = new Array(); //选中的考勤时间段列表(在非新建审批申请时有效)
	<?php 
	if (!empty($reqRecs)) {
		foreach ($reqRecs as $reqRec) {
		?>
			reqRecs.push({att_rec_id:'<?php echoField($reqRec, 'att_rec_id'); ?>', req_duration:'<?php echoField($reqRec, 'req_duration'); ?>'
				, req_start_time:'<?php echoField($reqRec, 'req_start_time'); ?>', req_stop_time:'<?php echoField($reqRec, 'req_stop_time'); ?>'});
		<?php
		}
	}
	?>
	
	//与时间输入控件相关参数
	var datetimeFormat = 'yyyy-mm-dd hh:ii';
	var startView = 2;
	var minView = 0;
	var maxView = 4;
	var minuteStep = 2;

	<?php if($canEdit/*empty($recId) && empty($reqId)*/) {?>
	//补签日期
	createDefaultDatetimePicker('#attend_date', 'yyyy-mm-dd', startView, 2, maxView);
  	//初始化时间范围选择器-自定义(开始)
   	createDefaultDatetimePicker('#period-custom-start', datetimeFormat, startView, minView, maxView, minuteStep);
   	createDefaultDatetimePicker('#period-custom-start-addon', datetimeFormat, startView, minView, maxView, minuteStep, 'period-custom-start', datetimeFormat, $('#period-custom-start').val());
   	//初始化时间范围选择器(结束)
   	createDefaultDatetimePicker('#period-custom-end', datetimeFormat, startView, minView, maxView, minuteStep); 
   	createDefaultDatetimePicker('#period-custom-end-addon', datetimeFormat, startView, minView, maxView, minuteStep, 'period-custom-end', datetimeFormat, $('#period-custom-end').val());
   	<?php }?>
	
   	//补签日期控件变化事件
   	var rtSelectorPfix = 'input[type="radio"][name="req_type"][checked="true"]';
   	$('#attend_date').on('changeDate', function(e) {
   		var time = $(this).val();
   		if (time.length>=10 && $(rtSelectorPfix+'[value="1"], ' + rtSelectorPfix+'[value="2"]').length>0) {
			var attendDate = time.substr(0, 10);
			//获取考勤时间段
			getRuleTimes(attendDate, reqId, reqId==0?false:true, function(results) {
				var scriptHtml = $('#attend-rule-times-checkbox-script').html();
				var $ruleTimeElement = $('#rule-times .rule-time');
				$ruleTimeElement.html('');
				//复制一个数组
				var tmpReqRecs = reqRecs.concat();
				
				for(var i=0; i<results.length; i++) {
					var entity = results[i];
					entity.index = i;
					entity.canEdit = canEdit; //是否允许勾选操作

					//签到时间与签退时间初始值
					entity.req_start_time = attendDate + ' ' + entity.standard_signin_time;
					entity.req_stop_time = attendDate + ' ' + entity.standard_signout_time;
					entity.req_duration = formatMinutesToHours(calculateMinuteBetweenDateTimes(new Date(entity.req_start_time), new Date(entity.req_stop_time)) - parseInt(entity.standard_rest_duration), 1);
					
					if (isEdit) {
						var j = tmpReqRecs.length;
						while(j--) {
							var reqRec = tmpReqRecs[j];
							if (entity.att_rec_id===reqRec.att_rec_id) {
								tmpReqRecs.splice(j, 1); //删除元素
								entity.rt_checked = true; //勾选状态
								
								entity.req_start_time = reqRec.req_start_time;
								entity.req_stop_time = reqRec.req_stop_time;
								entity.req_duration = formatMinutesToHours(reqRec.req_duration, 1);
							}
						}
					} else {
						if (entity.att_tim_id==selectedAttTimId)
							entity.rt_checked = true; //勾选状态
					}
					
					//rt_disabled
					if (entity.rec_state!=null) {
						entity.compensated_time_type = compensatedTimeTypeOfAttendRecState(entity.rec_state);
						if (entity.compensated_time_type==0)
							entity.rt_disabled = true;
					}
					
					//考勤状态名称
					entity.rec_state_name = '';
					var recStateArry = entity.rec_state_arry;
					if (recStateArry && recStateArry.length>0) {
						for (var j=0; j<recStateArry.length; j++) {
							if (j>0)
								entity.rec_state_name += '、';
							entity.rec_state_name += recStateArry[j][1];
						}
					}
					
					$ruleTimeElement.append(laytpl(scriptHtml).render(entity));
					
					//初始化签到时间、签退时间选择器
					if (canEdit) {
						var datetimeFormat = 'hh:ii yyyy-mm-dd';
						var startView = 1;
						var minView = 0;
						var maxView = 1;
						var minuteStep = 2;
						var initialDate = '00:00 '+attendDate;
						var startDate = '00:00 '+attendDate;
						var endDate = '23:59 '+attendDate;
						var selector = '#rule-time-item'+entity.index+' input[name="{signtype}_time_'+entity.index+'"]';
						var signinSelector = selector.replace(/\{signtype\}/ig, 'signin');
						var signoutSelector = selector.replace(/\{signtype\}/ig, 'signout');
					   	createDefaultDatetimePicker(signinSelector, datetimeFormat, startView, minView, maxView, minuteStep, null, null, initialDate, startDate, endDate);
					   	createDefaultDatetimePicker(signoutSelector, datetimeFormat, startView, minView, maxView, minuteStep, null, null, initialDate, startDate, endDate);
	
						//签到时间、签退时间输入值变更事件
					   	$(signinSelector+','+signoutSelector).on('changeDate', function(ev) {
							var $element = $(ev.target);
							if ($element.attr('o_value')!=$element.val()) {
								$element.attr('o_value', $element.val());
								var $rElement = $element.parents('.checkbox-list-item');
								var $rduElement = $rElement.find('.req-duration input[type="text"]');
								var signinoutTimes = getSigninoutTimesInElement($rElement);
								var newSigninTime = signinoutTimes[0];
								var newSignoutTime = signinoutTimes[1];
								var restDuration = signinoutTimes[2];
								
								//根据输入值重新计算并更新申请的工作时长
								if (newSigninTime && newSignoutTime) {
									var reqDuration = calculateMinuteBetweenDateTimes(new Date(newSigninTime), new Date(newSignoutTime))- parseInt(restDuration);
									$rduElement.val(formatMinutesToHours(reqDuration<0?0:reqDuration, 1));
								} else
									$rduElement.val('0');
							}
						});
					}
				}
				
				//注册时间段勾选事件
			   	$('#rule-times .checkbox-list input[type="checkbox"]').on('change', function(e) {
					if (this.checked) {
						$(this).parent().nextAll().removeClass('ebtw-hide');
					} else {
						$(this).parent().nextAll().addClass('ebtw-hide');
					}
			   	}).trigger('change');
			}, function() {
			});
   		}
   	});
   	<?php 
   	if (!empty($attendDate)) {
   	?>
	   	//仅'补签'和'外勤'申请与时间段有关联
	   	if ($(rtSelectorPfix+'[value="1"], ' + rtSelectorPfix+'[value="2"]').length>0) {
	   		$('#attend_date').trigger('changeDate');	
	   	}
   	<?php
   	}
   	?>
	
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
   	$('textarea[name="req_content"]').autoHeight();
   	
	//下拉框select2
	var selectOptions = {theme: "default", minimumResultsForSearch:Infinity, width:'150px'};
	$("#furlough_type .side-select").select2(selectOptions);
	
	//创建审批人'自动搜索小部件'
	registerAutoloadWidget('#approver-objahead.objahead-wrapper', function(e, user_id, user_name, user_account, type) {
			//隐藏'自动搜索小部件'
			$parent = $(this).parents('.objahead-wrapper').addClass('ebtw-hide').prev('.select-person-option');
			//清除旧已选人员
			$parent.children('.selected-person').remove();
			
			//插入选中人员
			$addBtnElement = $parent.children('.ptr-add-person');
			var selectedPersons = new Array({user_id:user_id, user_name:user_name, user_account:user_account});
			for (var i=0; i<selectedPersons.length; i++) {
				var person = selectedPersons[i];
				person.canEdit = canEdit;
				if ($parent.find('.selected-person[data-user-id="'+person.user_id+'"]').length==0) {
					if (logonUserId!=person.user_id)
						person.talkToPerson = true;
					
					$addBtnElement.before(laytpl($('#selected-user-script').html()).render(person));
				}
			}
		}, [{type:1, type_name:'部门经理', entity:'person', placeholder:'输入姓名', show:true}, {type:2, type_name:'考勤专员', entity:'person', placeholder:'输入姓名'}]);

	//在视图重现审批人
	var personTypes = {approver_person:{share_type:<?php echo $shareType; ?>, only_one:true}};
	<?php if (!empty($entity)) {?>
		reappearPtrPersons(logonUserId, personTypes.approver_person, '<?php echoShares($entity, $shareType);?>', '#approver_person', canEdit);
	<?php }?>
	//管理审批人
	registerManagePtrPersons2('#approver_person', function(e) {
		//显示'自动搜索小部件'，并触发一次用户类型选择框的选中事件
		$(this).parent().nextAll('.objahead-wrapper').removeClass('ebtw-hide').find('select').trigger('select2:select');
	});
	//阻止'自动搜索小部件'点击事件传递
	$('.objahead-wrapper').click(function(e) {
		stopPropagation(e);
	});
	//点击右侧页面隐藏'自动搜索小部件'
	$('#sidepage').click(function(e) {
		$('.objahead-wrapper').addClass('ebtw-hide');
	});
   	
   	//审批类型事件
   	$('input[type="radio"][name="req_type"]').on('change', function(e) {
		if (e.target.value==1 || e.target.value==2) { //补签
			$('#start_time_label').text('签到时间');
			$('#end_time_label').text('签退时间');	
			$('#attend_date_field').removeClass('ebtw-hide');
			$('#time_range').addClass('ebtw-hide');
			$('#rule-times').removeClass('ebtw-hide');

			if (e.target.value==1)
				$('#attend_date_label').text('补签日期');
			else 
				$('#attend_date_label').text('外勤日期');
		} else {
			$('#attend_date_field').addClass('ebtw-hide');
			$('#time_range').removeClass('ebtw-hide');
			$('#rule-times').addClass('ebtw-hide');
		}
		
		if (e.target.value==3) { //请假
			$('#start_time_label').text('请假开始');
			$('#end_time_label').text('请假结束');
			$('#furlough_type').removeClass('ebtw-hide');
			
			//加载请假类型列表
			var selectedFurloughId, selectedFurloughName = '<?php echoField($entity, 'req_name')?>';
			loadDictionaryInfos(1, function(datas) {
				fillFurloughTypeSelect(selectOptions, datas, '#furlough_type', selectedFurloughId, selectedFurloughName);
			}, function() {
			});
		} else {
			$('#furlough_type').addClass('ebtw-hide');
		}

		if (e.target.value==4) { //加班
			$('#start_time_label').text('加班开始');
			$('#end_time_label').text('加班结束');
			$('#req_content_label').text('工作内容');
			
			$('#req_duration').removeClass('ebtw-hide');
		} else {
			$('#req_content_label').text('申请内容');
			$('#req_duration').addClass('ebtw-hide');
		}
   	});
   	$('input[type="radio"][name="req_type"][checked="true"]').trigger('change');
	
   	//在新建审批申请时，自动切换到某个指定申请类型
   	if (reqId==0 && canEdit && selectReqType!=0) {
   		$('input[type="radio"][name="req_type"][value="'+selectReqType+'"]').attr("checked",true).trigger('change');
   	}
	
	//注册事件-点击分隔线
	bindStretchClick($('#divide-1'), $('#extend-properties1'));

	//注册事件-点击新增/删除附件按钮
	var fromType = 14;
	var attaType = 0; //文档(本体)附件
	var onlyOne = false;
	var loadExist = isEdit;
	var isOnlyView = !canEdit;
	registerAttachmentActions(isEdit, isOnlyView, fromType, ptrType, isEdit?reqId:0, attaType, '#sidepage .ebtw-file-upload', function(result, resourceId) {
		if (isEdit)
			layer.msg('上传附件成功');
	}, loadExist, onlyOne, '#sidepage .ebtw-file-upload-list', '.attachment-remove', function() {
		
	});
	
	//注册事件-取消按钮
	$('#btn_cancel').click(function() {
		closeSidepage();
	});

	//注册事件-拒绝按钮
	$('#btn_pass, #btn_reject, #btn_revoke' ).click(function() {
		var actionType = 4;
		var title = '通过';
		if (this.id=='btn_reject') {
			actionType = 5;
			title = '拒绝';
		} else if (this.id=='btn_revoke') {
			actionType = 6;
			title = '撤销';
		}
		//询问确认后执行
		askForConfirmSubmit('真的要' + title + '吗？', '审批' + title, null, null, attendReqAction, [actionType, reqId, function(result) {
			layer.msg(title = '成功');
			loadDtGrid(createQueryParameter());
			closeSidepage();
		}, function(err) {
			layer.msg(title + '失败', {icon:2});
		}]);
	});
	
	//注册事件-保存按钮
	$('#btn_save').click(function() {
		var $form = $(this).parents('form');
		var reqType = $form.find('input[type="radio"][name="req_type"]:checked').val(); //申请类型

		//时段
		$form.find('input[name="start_time"]').val('');
		$form.find('input[name="stop_time"]').val('');
		if (reqType==3 || reqType==4) { //请假、加班
			var startDate = $form.find('#period-custom-start').val();
			var endDate = $form.find('#period-custom-end').val();
			if (startDate.length==0 || endDate.length==0) {
				layer.msg('时段必填', {icon:5});
				return false;
			}
			startDate += ':00';
			endDate += ':00';

			//验证两个时间是否相等
			if (startDate===endDate) {
				layer.msg('开始时间、结束时间不能相等', {icon:5});
				return false;
			}
			//验证时段先后合法性
			if (new Date(startDate).getTime() > new Date(endDate).getTime()) {
				layer.msg('开始时间 必须早于 结束时间', {icon:5});
				return false;
			}
			
			if (reqType==4) {
				//检查结束时间
				var yesterday = $.D_ALG.formatDate(calculateDate(new Date(), -1), 'yyyy-mm-dd');
				if (yesterday<endDate.substr(0, 10)) {
					layer.msg('结束时间必须在今天之前', {icon:5});
					return false;
				}
				
				//检查加班时间跨度
				if (startDate.substr(0, 10)!=endDate.substr(0, 10)) {
					layer.msg('开始时间和结束时间必须在同一天', {icon:5});
					return false;
				}
			}
			
			$form.find('input[name="start_time"]').val(startDate);
			$form.find('input[name="stop_time"]').val(endDate);
		}

		//考勤记录
		$form.find('input[name="rec_ids"]').val('');
		if (reqType==1 || reqType==2) { //补签、外勤
			var attendDate = $('#attend_date').val();
			var rec_ids = '';
			var $rtElements = $form.find('#rule-times input[type="checkbox"]');//:checked

			//定义函数：获取补签时间范围
			var getSigninoutTimes = function($rtElement, reFormat=true) {
				var index = $rtElement.attr('data-index');
				var startTime 	= $rtElement.parents('.checkbox-list-item').find('input[name="signin_time_'+index+'"]').val();
				var endTime 	= $rtElement.parents('.checkbox-list-item').find('input[name="signout_time_'+index+'"]').val();
				//转换为合适的时间格式，原格式：hh:ii yyyy-mm-dd
				if (reFormat) {
					if (startTime!=null && startTime.length>0)
						startTime = startTime.substr(6, 10) + ' ' + startTime.substr(0, 5) + ':00';
					if (endTime!=null && endTime.length>0)
						endTime = endTime.substr(6, 10) + ' ' + endTime.substr(0, 5) + ':00';
				}
				
				return [startTime, endTime]; 
			};
			
			if ($rtElements.length>0) {
				//遍历验证每一个时间段合法性
				for (var i=0; i<$rtElements.length; i++) {
					if (!$rtElements[i].checked)
						continue;
					var $rtElement = $($rtElements[i]);
					
					//提取时间范围
					var bTimes = getSigninoutTimes($rtElement);
					startTime = bTimes[0];
					endTime = bTimes[1];
					
					//验证时间范围的合理性
					var recState = parseInt($rtElement.attr('data-rec-state'));
					var vldResult = validateBetweenSigninoutTime(compensatedTimeTypeOfAttendRecState(recState), startTime, endTime
							, attendDate + ' ' +$rtElement.attr('data-standard-signin-time'), attendDate + ' ' +$rtElement.attr('data-standard-signout-time'));
					if (vldResult!==true) {
						layer.msg(vldResult, {icon:5});
						return false;
					}

					//验证工作时长合理性
					var $cklElement = $rtElement.parents('.checkbox-list-item');
					var $rduElement = $cklElement.find('.req-duration input[type="text"]');
					var signinoutTimes = getSigninoutTimesInElement($cklElement);
					var newSigninTime = signinoutTimes[0];
					var newSignoutTime = signinoutTimes[1];
					var restDuration = signinoutTimes[2];
					//申请工作时长不能超过签到时间与签退时间之差
					if (newSigninTime && newSignoutTime) {
						var signDuration = calculateMinuteBetweenDateTimes(new Date(newSigninTime), new Date(newSignoutTime));
						var reqDuration = parseFloat($rduElement.val())*60;
						if (reqDuration==0) {
							layer.msg('申请的工作时长 必须大于0', {icon:5});
							return false;
						}
						if (signDuration < reqDuration) {
							layer.msg('申请的工作时长 不能超过 签到时间与签退时间之差', {icon:5});
							return false;
						}
					} else {
						layer.msg('签到时间、签退时间不能空白', {icon:5});
						return false;
					}
				}
				
				//按时间段先后排序
				if ($rtElements.length>1) {
					$rtElements.sort(function(a, b) {
						$rtElement1 = $(a);
						$rtElement2 = $(b);
						//提取时间范围
						var startTime1 = new Date(attendDate+' '+$rtElement1.attr('data-standard-signin-time')).getTime();
						var startTime2 = new Date(attendDate+' '+$rtElement2.attr('data-standard-signin-time')).getTime();
						
						return (startTime1-startTime2);
					});
				}

				//遍历验证每一个时间段之间的关系是否合理
				for (var i=0; i<$rtElements.length; i++) {
					if (!$rtElements[i].checked)
						continue;
					var $rtElement = $($rtElements[i]);
					
					//把考勤记录编号封装在一个字段
					var recId = $rtElement.val();
					if (rec_ids.length>0)
						rec_ids += ',';
					rec_ids += recId;

					//提取时间范围
					var bTimes = getSigninoutTimes($rtElement);
					startTime = bTimes[0];
					endTime = bTimes[1];
					
					//验证与前一个时间段是否相交
					if (i>0) {
						var $prevRtElement = $($rtElements[i-1]);
						var prevStandardSignoutTime = attendDate + ' ' +$prevRtElement.attr('data-standard-signout-time');
						var prevStdSignoutTimestamp = new Date(prevStandardSignoutTime).getTime();
						if (startTime!=null && startTime.length>0 && new Date(startTime).getTime() < prevStdSignoutTimestamp) {
							layer.msg('签到时间 不能包含在 前一个时间段', {icon:5});
							return false;
						}
						if (endTime!=null && endTime.length>0 && new Date(endTime).getTime() < prevStdSignoutTimestamp) {
							layer.msg('签退时间 不能包含在 前一个时间段', {icon:5});
							return false;
						}
					}
					//验证与后一个时间段是否相交
					if (i<$rtElements.length-1) {
						var $nextRtElement = $($rtElements[i+1]);
						var nextStandardSigninTime = attendDate + ' ' +$nextRtElement.attr('data-standard-signin-time');
						var nextStdSigninTimestamp = new Date(nextStandardSigninTime).getTime();
						if (startTime!=null && startTime.length>0 && new Date(startTime).getTime() > nextStdSigninTimestamp) {
							layer.msg('签到时间 不能包含在 下一个时间段', {icon:5});
							return false;
						}
						if (endTime!=null && endTime.length>0 && new Date(endTime).getTime() > nextStdSigninTimestamp) {
							layer.msg('签退时间 不能包含在 下一个时间段', {icon:5});
							return false;
						}
					}
				}
				$form.find('input[name="rec_ids"]').val(rec_ids);
			}
			
			if (rec_ids.length==0) {
				layer.msg('至少勾选一个考勤记录', {icon:5});
				return false;
			}
		}
		
		//请假类型
		$form.find('input[name="req_name"]').val('');
		if (reqType==3) { //请假
			var $selElement = $form.find('select[name="furlough_id"]');
			var $selOption = $selElement.find('option:selected[value!="0"]');
			if ($selOption.length==0) {
				layer.msg('必须选择一个请假类型', {icon:5});
				return false;
			}
			$form.find('input[name="req_name"]').val($selOption.text());
		}

		//加班时长
		var $reqDurationElement = $form.find('input[name="req_duration"]');
		if (reqType==4) { //加班
			if ($reqDurationElement.val().length==0) {
				layer.msg('必须输入加班时长', {icon:5});
				return false;
			}
			
			var reqDuration = parseFloat($reqDurationElement.val());
			if (reqDuration<=0) {
				layer.msg('加班时长必须大于0', {icon:5});
				return false;
			}
			if (reqDuration*60*60*1000 > (new Date(endDate).getTime()-new Date(startDate).getTime())) {
				layer.msg('加班时长不能大于时间段差值', {icon:5});
				return false;
			}
		} else {
			$reqDurationElement.val('');
		}
		
		//申请内容
		var reqContent = $form.find('textarea[name="req_content"]').val();
		if (!checkContentLength(ptrType, 'req_content', reqContent && reqContent.trim(), reqType==4?'工作内容':null))
		    return false;
		
	    //检查审批人
	    if ($('#approver_person .selected-person').length==0) {
			layer.msg('必须选择一个审批人', {icon:5});
			return false;
		}
		disposeSharePerson('approver_person', $form);

		//提交到服务端
		var url = getServerUrl() + 'attendance/attendance_req.php';
		var loadIndex = layer.load(2);
		callAjax(url, $form.serialize(), null, function(datas) {
			var result = didLoadedDataPreprocess('original', datas, true);
			
			var completeUploads = new Array(); //暂存已完成的上传(无论失败与否)
			//检测关闭正在加载的界面
			function checkClose(title, close, i, total) {
				if (close) {
					layer.close(loadIndex);
					closeSidepage();
					layer.msg(title);
					
					refreshMainViewActually(); //依据具体情况下刷新主视图
					return;
				}
					
				completeUploads.push(i);
				if (completeUploads.length==total) {
					layer.close(loadIndex);
					closeSidepage();
					layer.msg(title);
					
					refreshMainViewActually(); //依据具体情况下刷新主视图
				}
			}
						
			if (result.code==0) {
				var title = '提交审批成功';
				if (isEdit) { //编辑
					checkClose(title, true);
				} else { //新建
					//上传附件
					var $liElements = $form.find('.ebtw-file-upload-list li');
					var fileCount = $liElements.length;
					if (fileCount==0) {
						checkClose(title, true);
					} else {
						var fromType = 14;
						var ptrId = result.id;
						
						//遍历上传文件
						var liElements = new Array();
						$liElements.each(function(){
							liElements.push(this);
						});
						var i=0;
						executeSendfile(fromType, ptrType, ptrId, attaType, liElements, fileCount, i, title, true, checkClose);
					}
				}
			} else {
				layer.msg('提交审批失败', {icon:2});
				layer.close(loadIndex);
			}
		}, function(XMLHttpRequest, textStatus, errorThrown) {
			layer.msg('提交审批失败', {icon:2});
			layer.close(loadIndex);
		});
		
	});
});
</script>
