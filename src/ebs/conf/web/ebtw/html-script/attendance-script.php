<!-- 右侧页时间段 radio模式 -->
<script id="attend-rule-times-radio-script" type="text/html">
	<label class="radio-inline">
		<input type="radio" name="att_tim_id" class="rule-time-radio" {{#if(d.disabled){}}disabled="disabled"{{#}}} value="{{d.att_tim_id}}" 
			{{#if(d.att_rec_id!=null){}}data-att-rec-id="{{d.att_rec_id}}"{{#}}} data-att-rul-id="{{d.att_rul_id}}" {{#if(d.rt_checked){}}checked="true"{{#}}}>
		<span>{{d.standard_signin_time.substr(0, 5)}}-{{d.standard_signout_time.substr(0, 5)}} {{d.rec_state_name}}</span>
	</label>
</script>

<!-- 右侧页时间段 checkbox模式 -->
<script id="attend-rule-times-checkbox-script" type="text/html">
<div class="checkbox-list-item" id="rule-time-item{{d.index}}">
	<label class="checkbox {{#if(d.rt_disabled){}}disabled-operation{{#}}}">
		<input type="checkbox" name="rec_id_{{d.index}}" data-index="{{d.index}}" {{#if (!d.canEdit) {}}disabled="disabled"{{#}}} class="rule-time-checkbox" value="{{d.att_rec_id}}" data-att-rul-id="{{d.att_rul_id}}" data-att-tim-id="{{d.att_tim_id}}" 
			data-rec-state="{{d.rec_state}}" data-standard-rest-duration="{{d.standard_rest_duration}}" data-compensated-time-type="{{d.compensated_time_type}}"
			data-standard-signin-time="{{d.standard_signin_time}}" data-standard-signout-time="{{d.standard_signout_time}}"
			data-real-signin-time ="{{#if (d.signin_time){}}{{d.signin_time}}{{#}}}" data-real-signout-time ="{{#if (d.signout_time){}}{{d.signout_time}}{{#}}}"
			{{#if(d.rt_checked){}}checked="true"{{#}}} {{#if(d.rt_disabled){}}disabled="disabled"{{#}}}>
		<span>{{d.standard_signin_time.substr(0,5)}}-{{d.standard_signout_time.substr(0,5)}} {{d.rec_state_name}}
			(<span title="实际签到时间">{{#if(d.signin_time && d.signin_time.length>16){}}{{d.signin_time.substr(11,5)}}{{#} else {}}-{{#}}}</span> 
			~ <span title="实际签退时间">{{#if(d.signout_time && d.signout_time.length>16){}}{{d.signout_time.substr(11,5)}}{{#} else {}}-{{#}}}</span>)
		</span>
	</label>
{{#if(d.compensated_time_type==1 || d.compensated_time_type==3){
	var reqStartTime = '';
	if(d.req_start_time && d.req_start_time.length>16) {
		reqStartTime = d.req_start_time.substr(11, 5) + ' ' + d.req_start_time.substr(0, 10);
	}
	}}
	<div class="inline select-time ebtw-hide">
		<div>签到时间&nbsp;</div>
		<div><input name="signin_time_{{d.index}}" type="text" class="form-control input-date-time-short" readonly
			value="{{reqStartTime}}" o_value="{{reqStartTime}}" sign-type="in"></div>
	</div>
{{#}
if(d.compensated_time_type==2 || d.compensated_time_type==3){
	var reqStopTime = '';
	if(d.req_stop_time && d.req_stop_time.length>16){
		reqStopTime = d.req_stop_time.substr(11, 5) + ' ' + d.req_stop_time.substr(0, 10);
	}
	}}
	<div class="inline select-time ebtw-hide">
		<div>签退时间&nbsp;</div>
		<div><input name="signout_time_{{d.index}}" type="text" class="form-control input-date-time-short" readonly
			value="{{reqStopTime}}" o_value="{{reqStopTime}}" sign-type="out"></div>
	</div>
{{#}}}
{{#if (d.compensated_time_type!=0){}}
	<div class="inline">
		<div title="实际工作时长=考勤时间段时长-休息时长   0.5小时=30分钟">实际工作时长&nbsp;</div>
		<div class="req-duration"><input name="req_duration_{{d.index}}" type="text" placeholder="0.0" class="form-control "
			value="{{#if(d.req_duration!=null){}}{{d.req_duration}}{{#}}}" {{#if (!d.canEdit) {}}readonly{{#}}}
			t_value="" o_value="" onblur="digital_onBlur(this);" onkeypress="digital_onkeyPress(this);" onkeyup="digital_onkeyUp(this);"></div>
		<div>小时</div>
	</div>
{{#}}}
</div>
</script>

<!-- 考勤规则适用对象 -->
<script id="attendance-target-script" type="text/html">
<div class="selected-target {{#if(d.talkToPerson){}}talk-to-person{{#}}}" {{#if(d.talkToPerson){}}data-talk-to-uid="{{d.target_id}}"{{#}}} 
		data-target-id="{{d.target_id}}" data-target-type="{{d.target_type}}" data-target-name="{{d.target_name}}" data-ext-name="{{d.ext_name}}">
	{{# if (d.target_type==1) {}}
		<span title="{{d.target_name}}({{d.target_id}})">全公司</span>
	{{#} else if (d.target_type==2) {}}
		<span title="{{d.target_name}}({{d.target_id}})">{{d.target_name}}</span>
	{{#} else {}}
		<span title="{{#if (d.user_account){}}{{d.user_account}}{{#}}}({{d.target_id}})">{{# if(d.ext_name.length>0){}}{{d.ext_name}}：{{#}}}{{d.target_name}}</span>
	{{#}}}
	{{#if(d.canEdit){}}<span class="t-action-item glyphicon glyphicon-remove"></span>{{#}}}
</div>
</script>

<!-- 考勤设置-规则 -->
<script id="attendance-setting-rule-script" type="text/html">
<div class="form-group row-ptr form-inline attend-setting-rule" data-rule-index="{{d.rul_index}}" data-rule-id="{{d.att_rul_id}}" data-new-field-matched="{{d.new_field_matched}}">
	<input type="hidden" name="rule_index[]" value="{{d.rul_index}}">
	<input type="hidden" name="att_rul_id_{{d.rul_index}}" value="{{d.att_rul_id}}">
	<input type="hidden" name="att_rul_del_{{d.rul_index}}" value="0">
	
	<label class="col-xs-2 control-label">
		<div>考勤规则{{d.rul_index}}</div>
		{{#if (d.newRule) {}}
		<div class="new-rule ebtw-color-error">新记录-未保存！</div>
		{{#}}}
		{{#if (d.new_field_matched) {}}
		<div class="ebtw-color-info">变更明天生效</div>
		{{#}}}
	</label>
	<div class="col-xs-10 delete-mark ebtw-color-urgent ebtw-hide">即将删除！</div>
	<div class="col-xs-10 rule-properties">
		<div class="sidepage-inner-toolbar">
			<div class="fa fa-times del-attend-rule" title="删除考勤规则{{d.rul_index}}"></div>
			<div class="fa fa-plus add-attend-rule" title="新建考勤规则"></div>
		</div>
		<div class="rule-item">
			<div>工作日设置：</div>
			<div><label class="checkbox"><input type="checkbox" name="week_value_{{d.rul_index}}[]" class="week_value" value="1" data-wday="1" {{#if ((d.work_day&1)==1){}}checked{{#}}}>&nbsp;周一</label></div>
			<div><label class="checkbox"><input type="checkbox" name="week_value_{{d.rul_index}}[]" class="week_value" value="2" data-wday="2" {{#if ((d.work_day&2)==2){}}checked{{#}}}>&nbsp;周二</label></div>
			<div><label class="checkbox"><input type="checkbox" name="week_value_{{d.rul_index}}[]" class="week_value" value="4" data-wday="3" {{#if ((d.work_day&4)==4){}}checked{{#}}}>&nbsp;周三</label></div>
			<div><label class="checkbox"><input type="checkbox" name="week_value_{{d.rul_index}}[]" class="week_value" value="8" data-wday="4" {{#if ((d.work_day&8)==8){}}checked{{#}}}>&nbsp;周四</label></div>
			<div><label class="checkbox"><input type="checkbox" name="week_value_{{d.rul_index}}[]" class="week_value" value="16" data-wday="5" {{#if ((d.work_day&16)==16){}}checked{{#}}}>&nbsp;周五</label></div>
			<div><label class="checkbox"><input type="checkbox" name="week_value_{{d.rul_index}}[]" class="week_value" value="32" data-wday="6" {{#if ((d.work_day&32)==32){}}checked{{#}}}>&nbsp;周六</label></div>
			<div><label class="checkbox"><input type="checkbox" name="week_value_{{d.rul_index}}[]" class="week_value" value="64" data-wday="0" {{#if ((d.work_day&64)==64){}}checked{{#}}}>&nbsp;周日</label></div>
			<div class="ebtw-clear"></div>
		</div>
		<div class="rule-item">
			<div>每日工作时长：</div>
			<div><input name="rule_duration_{{d.rul_index}}" class="form-control attend-setting-width-4" type="text" value="{{d.total_work_duration}}" readonly>&nbsp;&nbsp;&nbsp;</div>
			<div><label class="checkbox"><input type="checkbox" name="flexible_work_{{d.rul_index}}" class="" value="1" {{#if (d.flexible_work==1){}}checked{{#}}}>&nbsp;弹性工作制</label></div>
			<div style="padding-left: 5px;"><div class="tips-icon fa fa-question" title="弹性工作制，达到考勤工作时长，不算为迟到或早退；否则，严格按照考勤时间段，计算迟到和早退"></div></div>			
		</div>
	</div>
</div>
</script>

<!-- 考勤设置-时间段 -->
<script id="attendance-setting-time-script" type="text/html">
<div class="form-group row-ptr form-inline attend-setting-time" data-rul-index="{{d.rul_index}}" data-tim-index="{{d.tim_index}}" data-tim-id="{{d.att_tim_id}}" data-new-field-matched="{{d.new_field_matched}}">
	<input type="hidden" name="tim_index_{{d.rul_index}}[]" value="{{d.tim_index}}">
	<input type="hidden" name="att_tim_id_{{d.rul_index}}_{{d.tim_index}}" value="{{d.att_tim_id}}">
	<input type="hidden" name="att_tim_del_{{d.rul_index}}_{{d.tim_index}}" value="0">
	
	<label class="col-xs-2 control-label ebtw-font-normal">
		<div>考勤时段{{d.tim_index}}</div>
		{{#if (d.newTime) {}}
		<div class="new-time ebtw-color-error"></div>
		{{#}}}
		{{#if (d.new_field_matched) {}}
		<div class="ebtw-color-info">变更明天生效</div>
		{{#}}}
	</label>
	<div class="col-xs-10 delete-mark ebtw-color-urgent ebtw-hide">即将删除！</div>
	<div class="col-xs-10 time-properties">
		<div class="row">
			<div class="col-xs-4"><input name="att_time_name_{{d.rul_index}}_{{d.tim_index}}" class="form-control full-width" type="text" placeholder="输入考勤时段名称，如'上午'" {{#if(d.name){}}value="{{d.name}}"{{#}}}></div>
			<div class="col-xs-4 div-divide-bottom-pull">
				<div class="divide-line-all3"></div>
			</div>
			<div class="col-xs-2">
				<div class="sidepage-inner-toolbar">
					<div class="fa fa-times del-attend-time" title="删除考勤时段{{d.tim_index}}"></div>
					<div class="fa fa-plus add-attend-time" title="新建考勤时段"></div>
				</div>
			</div>
		</div>
		<div class="time-item">
			<div>签到时间：</div>
			<div><input name="signin_time_{{d.rul_index}}_{{d.tim_index}}" type="text" class="form-control attend-setting-normal-background attend-setting-width-2" readonly value="{{d.signin_time_combined}}"></div>
			<div style="padding-left: 15px;">不计迟到：</div>
			<div><input name="signin_ignore_{{d.rul_index}}_{{d.tim_index}}" type="text" class="form-control attend-setting-width-1" value="{{d.signin_ignore}}"
					t_value="" o_value="" onblur="digital_onBlur(this);" onkeypress="digital_onkeyPress(this);" onkeyup="digital_onkeyUp(this);">&nbsp;分钟</div>
			<div style="padding-left: 15px;">休息时长：</div>
			<div><input name="rest_duration_{{d.rul_index}}_{{d.tim_index}}" type="text" class="form-control attend-setting-width-2" value="{{d.rest_duration}}"
					t_value="" o_value="" onblur="digital_onBlur(this);" onkeypress="digital_onkeyPress(this);" onkeyup="digital_onkeyUp(this);">&nbsp;分钟</div>
			<div class="ebtw-clear"></div>
		</div>
		<div class="time-item">
			<div>签退时间：</div>
			<div><input name="signout_time_{{d.rul_index}}_{{d.tim_index}}" type="text" class="form-control attend-setting-normal-background attend-setting-width-2" readonly value="{{d.signout_time_combined}}"></div>
			<div style="padding-left: 15px;">不计早退：</div>
			<div><input name="signout_ignore_{{d.rul_index}}_{{d.tim_index}}" type="text" class="form-control attend-setting-width-1" value="{{d.signout_ignore}}"
					t_value="" o_value="" onblur="digital_onBlur(this);" onkeypress="digital_onkeyPress(this);" onkeyup="digital_onkeyUp(this);">&nbsp;分钟</div>
			<div style="padding-left: 15px;">工作时长：</div>
			<div><input name="work_duration_{{d.rul_index}}_{{d.tim_index}}" type="text" class="form-control attend-setting-width-2" value="{{d.work_duration}}"
					t_value="" o_value="" onblur="digital_onBlur(this);" onkeypress="digital_onkeyPress(this);" onkeyup="digital_onkeyUp(this);">&nbsp;分钟</div>
		</div>
	</div>
</div>
</script>

<!-- 考勤设置-分隔线 -->
<script id="attendance-setting-divide-script" type="text/html">
<div class="col-xs-12 div-divide-top-pull">
	<div class="divide-line-all2"></div>
</div>
</script>

<!-- 考勤专员配置行记录 -->
<script id="attendance-user-define-row-script" type="text/html">
<tr data-ud-id="{{d.ud_id}}" {{#if (d.new_ud) {}}data-new-row="1"{{#}}}>
	<td class="ebtw-align-center col-xs-1 col-xs-1p5">
		<input type="hidden" name="user_id" value="{{d.user_id}}">
		<input type="text" class="full-input" name="display_index" value="{{d.display_index}}"
			t_value="" o_value="" onblur="digital_onBlur(this);" onkeypress="digital_onkeyPress(this);" onkeyup="digital_onkeyUp(this);">
	</td>
	<td class="ebtw-align-left col-xs-2">
		<div class="{{#if(d.can_talk){}}talk-to-person{{#}}}" data-talk-to-uid="{{d.user_id}}" title="{{#if(d.user_account){}}{{d.user_account}}{{#}}}({{d.user_id}})">{{d.user_name}}</div>
	</td>
	<td class="ebtw-align-left col-xs-2 col-xs-2p5">{{d.dep_names}}</td>
	<td class="ebtw-align-left col-xs-2">
		<label class="checkbox attend-user-define-checkbox">
			<input type="checkbox" name="auth_code" value="1" {{#if (d.authority_management){}}checked{{#}}}>&nbsp;管理权限
		</label>				
	</td>
	<td class="ebtw-align-center col-xs-1">
		<input type="hidden" name="disable" value="{{d.disable}}">
		{{#if (d.disable==1) {}}禁用{{#} else {}}有效{{#}}}
	</td>
	<td class="ebtw-align-left col-xs-3">
		<div class="ebtw-action ebtw-action-ext attend-user-define-action" data-user-define-action="2">保存</div>
		
		{{#if (d.disable==1) {}}
		<div class="ebtw-action ebtw-action-ext attend-user-define-action {{#if (d.new_ud) {}}ebtw-hide{{#}}}" data-user-define-action="0">启用</div>
		{{#} else {}}
		<div class="ebtw-action ebtw-action-ext attend-user-define-action {{#if (d.new_ud) {}}ebtw-hide{{#}}}" data-user-define-action="1">禁用</div>
		{{#}}}
		
		&nbsp;<div class="ebtw-action ebtw-action-ext attend-user-define-action {{#if (d.new_ud) {}}ebtw-hide{{#}}}" data-user-define-action='-1'>删除</div>
	</td>
</tr>
</script>

<!-- 请假类型配置行记录 -->
<script id="attendance-leave-type-row-script" type="text/html">
<tr data-dict-id="{{d.dict_id}}">
	<td class="ebtw-align-center col-xs-1 col-xs-1p5">
		<input type="text" class="full-input" name="display_index" value="{{d.display_index}}"
			t_value="" o_value="" onblur="digital_onBlur(this);" onkeypress="digital_onkeyPress(this);" onkeyup="digital_onkeyUp(this);">	
	</td>
	<td class="ebtw-align-left col-xs-3">
		<input type="text" class="full-input full-input-left" name="dict_name" value="{{d.dict_name}}">
	</td>
	<td class="ebtw-align-center col-xs-1">
		<input type="hidden" name="disable" value="{{d.disable}}">
		<span class="leave-type-status">{{#if (d.disable==1) {}}禁用{{#} else {}}有效{{#}}}</span>
	</td>
	<td class="ebtw-align-left col-xs-5">
		<div class="ebtw-action ebtw-action-ext attend-leave-type-action" data-leave-type-action="2">保存</div>
		<div class="ebtw-action ebtw-action-ext attend-leave-type-action {{#if (d.dict_id==0 || d.disable==0) {}}ebtw-hide{{#}}}" data-leave-type-action="0">启用</div>
		<div class="ebtw-action ebtw-action-ext attend-leave-type-action {{#if (d.dict_id==0 || d.disable==1) {}}ebtw-hide{{#}}}" data-leave-type-action="1">禁用</div>
		&nbsp;<div class="ebtw-action ebtw-action-ext attend-leave-type-action" data-leave-type-action='-1'>删除</div>
	</td>
</tr>
</script>

<!-- 假期配置行记录 -->
<script id="attendance-holiday-row-script" type="text/html">
<tr data-hol-set-id="{{d.hol_set_id}}">
	<td class="ebtw-align-left col-xs-2">
		<div class="ebtw-action sidepage-open-holiday">
			{{#if (d.name) {}}
				{{d.name}}
			{{# } else {}}
				无标题
			{{#}}}
		</div>
	</td>
	<td class="ebtw-align-left col-xs-3">{{d.content}}</td>
	<td class="ebtw-align-left col-xs-3">{{#if (d.target_content!=undefined && d.target_content.length==0) {}}<span class="ebtw-color-error">[没有适用任何人]</span>{{#}}}{{d.target_content}}</td>
	<td class="ebtw-align-center col-xs-2">{{d.create_time.substr(0, 16)}}</td>	
	<td class="ebtw-align-center col-xs-1">
		<input type="hidden" name="disable" value="{{d.disable}}">
		{{#if (d.disable==1) {}}禁用{{#} else {}}有效{{#}}}
	</td>
	<td class="ebtw-align-center col-xs-1">
		<div class="ebtw-action ebtw-action-ext attend-holiday-action {{#if (d.dict_id==0 || d.disable==0) {}}ebtw-hide{{#}}}" data-holiday-action="0">启用</div>
		<div class="ebtw-action ebtw-action-ext attend-holiday-action {{#if (d.dict_id==0 || d.disable==1) {}}ebtw-hide{{#}}}" data-holiday-action="1">禁用</div>
		&nbsp;<div class="ebtw-action ebtw-action-ext attend-holiday-action" data-holiday-action='-1'>删除</div>
	</td>
</tr>
</script>
