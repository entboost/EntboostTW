<!-- 分类菜单项 -->
<script id="ptr-class-item-script" type="text/html">
{{# if(d.datas) {
	for(var i = 0, len = d.datas.length; i < len; i++){ }}
	<a href="#" style="display: none;" class="list-group-item ebtw-left-no-border ptr_class_item" data-ptr-class-id="{{ d.datas[i].class_id }}"><span class="item-name">{{d.datas[i].class_name}}</span>
		<span class="ebtw-nav-icon glyphicon glyphicon-remove ptr_class_delete ebtw-hide" title="删除分类"> </span>
		<span class="ebtw-nav-icon glyphicon glyphicon-edit ptr_class_edit ebtw-hide" title="编辑分类"> </span>
		<span class="ebtw-badge" title="未完成{{#if(d.classType==1){}}计划{{#} else if(d.classType==2){}}任务{{#}}}数量"></span>
	</a>
{{# } } }}
</script>

<!-- 修改重要程度弹出菜单 -->
<script id="ptr-change-important-level-menu-script" type="text/html">
<div {{#if(d.importantDict){}} class="important-level dropdown {{#if(d.importantDict.visibility===false){}}can-hide ebtw-invisible{{#}}}" {{#}}}>
	<div class="{{d.extendCssClass}} {{#if(d.importantDict){}}{{d.importantDict.css}}{{#}}}" {{#if(d.importantDict){}}data-toggle="dropdown"{{#}}} {{#if(d.importantDict){}}title="{{d.importantDict.title}}"{{#}}}></div>
	{{#if(d.importantDict && d.canEdit){}}
	<ul class="dropdown-menu dropdown-menu-left dropdown-menu-small">
		{{#for(var important in d.importantDict.importants){}}
		<li data-important="{{important}}"><a tabindex="-1" href="#">改为{{d.importantDict.importants[important]}}</a></li>
		{{#}}}
	</ul>
	{{#}}}
</div>
</script>

<!-- dtGrid第一列数据 -->
<script id="dtGrid-first-column-script" type="text/html">
<div class="ebtw-row-select"></div>
<div class="ebtw-title-container ebtw-title-container-ex">
	<div class="ebtw-title-content">
		{{#if(d.importantGradeTabHtml){}}{{d.importantGradeTabHtml}}{{#}}}<!-- 重要程度颜色块 -->
		<div data-ptr-id="{{d.ptrId}}" class="ptr-title force-break {{#if(d.alreadyDeleted){}}ebtw-txt-deleted{{#}}}">{{d.value}}</div>
	</div>
</div>
</script>

<!-- 查询列表快捷按钮 -->
<script id="actionbar-tr-script" type="text/html">
{{# if(d.btns.length>0) { }}
<tr class='actionbar-tr'>
	<td colspan='{{ d.colspan }}' style='border:none;background:none;padding-top:4px !important;padding-bottom:4px !important;'>
	<div id='actionbar' class='actionbar'>
	{{# for(var i = 0, len = d.btns.length; i < len; i++) {
		var reallyDelete = true;
		if('data' in d.btns[i] && d.btns[i].data.deleted==0)
			reallyDelete = false;
	}}
		<button class='actionbar-item btn btn-default ' type='button' {{# if('data' in d.btns[i] && d.btns[i].data.deleted!=undefined) { }} data-is-deleted='{{d.btns[i].data.deleted}}' {{# } }} data-type='{{ d.btns[i].dataType }}'><span class="{{#if(reallyDelete||d.btns[i].iconClass2==undefined){}} {{d.btns[i].iconClass }} {{#} else {}}{{d.btns[i].iconClass2}}{{#}}}"></span> {{ d.btns[i].name }}</button>
	{{# } }}
	</div>
	</td>
</tr>
{{# } }}
</script>
<!-- 右侧页工具栏快捷按钮 -->
<script id="side-toolbar-script" type="text/html">
{{# for(var i = 0, len = d.btns.length; i < len; i++) {
	var reallyDelete = true;
	if('data' in d.btns[i] && d.btns[i].data.deleted==0)
		reallyDelete = false;
}}
	<div class="side-toolbar-item">
		<button class="btn btn-default" type="button" {{#if(d.btns[i].data && d.btns[i].data.already_favorite!=undefined){}}data-already-favorite="{{d.btns[i].data.already_favorite}}"{{#}}} {{# if('data' in d.btns[i] && d.btns[i].data.deleted!=undefined) { }} data-is-deleted="{{d.btns[i].data.deleted}}" {{# } }} data-action-type="{{ d.btns[i].dataType }}" {{#if(d.btns[i].data && d.btns[i].data.reserved_from_view_page){}}data-from-view-page="{{d.btns[i].data.reserved_from_view_page}}"{{#}}}><i class="{{#if(reallyDelete||d.btns[i].iconClass2==undefined){}} {{d.btns[i].iconClass }} {{#} else {}}{{d.btns[i].iconClass2}}{{#}}}"></i><span> {{ d.btns[i].name }}</span></button>
	</div>
{{# } }}
</script>

<!-- 查询列表快捷按钮 - 输入区 -->
<script id="actionbar-content-script-pre" type="text/html">
<div class="form-inline actionbar-content" {{#if(d.type){}}data-type="{{d.type}}{{#}}}">
	<div class="form-group col-xs-12">
</script>

<script id="actionbar-content-script-rear" type="text/html">
		<button class="actionbar-content-item btn btn-primary actionbar-content-submit first" type="button"><span class="glyphicon glyphicon-ok"></span> 提交</button>
		<!--<button class="actionbar-content-item btn btn-default actionbar-content-close" type="button">取消</button>-->
	</div>
</div>
</script>

<script id="actionbar-content-script-rear-2" type="text/html">
		<div class="ebtw-clear"></div>
	</div>
	<div class="col-xs-11" style="padding-right:0px;">
		<button class="actionbar-content-item btn btn-primary actionbar-content-submit pull-right" type="button"><span class="glyphicon glyphicon-ok"></span> 提交</button>
		{{#if (!d.disableCancel) {}}
		<button class="actionbar-content-item btn btn-default actionbar-content-close pull-right" type="button">取消</button>
		{{#}}}
	</div>
	<div class="ebtw-clear"></div>
</div>
</script>

<script id="actionbar-content-script-1" type="text/html">
<div class="unit-wrap">
	<label class="normal" for="request_for_person">评审人：</label>
	<div class="dropdown" id="approval_user" style="display:inline-block;">
		<input type="hidden" name="approval_user_id" />
		<input data-toggle="dropdown" name="approval_user_name" class="form-control input-single input-single-sm normal-readonly-style cursor-click dropdown-toggle" type="text" placeholder="--请选择评审人--" readonly="readonly">
	</div>
</div>
<div class="unit-wrap">
	<!--<label class="small" for="request_remark">备注：</label>-->
	<input class="form-control input-single input-single-md" type="text" name="approval_remark" placeholder="请输入申请说明 (Enter回车提交，Esc取消)">
</div>
</script>

<script id="actionbar-content-script-2" type="text/html">
<div class="unit-wrap">
	<!--<label class="large" for="discuss_content">{{d.name}}：</label>-->
	<input class="form-control input-single input-single-md" type="text" id="discuss_content" placeholder="请输入{{#if(d.type==4){}}评审通过{{#} else {}}评审拒绝{{#}}}意见 (Enter回车提交，Esc取消)" name="approval_remark">
</div>
</script>

<script id="actionbar-content-script-3" type="text/html">
<div class="slider-wrap">
	<div id="ptr-slider" class="col-xs-11 slider ptr-slider"></div>
	<div class="col-xs-1 ptr-slider-value ebtw-color-foreground ebtw-horizontal-nopadding-right"><span id="ptr-slider-per" data-per="{{d.percent}}">{{d.percent}}%</span></div>
	<div class="ebtw-clear"></div>
</div>
<div class="row">
	<div class="col-xs-12 ptr-slider-remark">
		<textarea id="ptr-slider-txt" class="col-xs-11" placeholder="进度说明"></textarea>
	</div>
</div>
</script>

<script id="actionbar-content-script-4" type="text/html">
<div class="slider-wrap">
	<div id="ptr-time">
		<div class="op-time filterbar-date-control">
			<div class="input-group">
				<span id="ptr-time-addon" class="input-group-addon time-icon"><span class="glyphicon glyphicon-calendar"></span></span>
				<input id="ptr-time-value" type="text" class="form-control" readonly value="{{d.default_op_time}}">
			</div>
		</div>
	</div>
	<div id="ptr-slider" class="col-xs-7 col-xs-7p5 slider ptr-slider"></div>
	<div class="col-xs-1 col-xs-1p5 ptr-slider-value ebtw-color-foreground ebtw-horizontal-nopadding-right"><span id="ptr-slider-per" data-per="{{d.default_work_time}}"> {{d.default_work_time}} 小时</span></div>
	<div class="ebtw-clear"></div>
</div>
<div class="row">
	<div class="col-xs-12 ptr-slider-remark">
		<textarea id="ptr-slider-txt" class="col-xs-11" placeholder="工作内容"></textarea>
	</div>
</div>
</script>

<script id="actionbar-content-script-5" type="text/html">
<div class="row">
	<div class="col-xs-12 ptr-abort-remark">
		<textarea class="col-xs-11" placeholder="中止原因"></textarea>
	</div>
</div>
</script>

<!-- 快速新建 -->
<script id="quick-addptr-script" type="text/html">
<tr class="actionbar-tr">
	<td colspan="{{ d.colspan }}" style="background:none;">
		<div class="form-inline actionbar-content">
			<div class="form-group col-xs-12">
				<div class="unit-wrap">
					<input class="form-control input-single input-single-md" type="text" id="request_for_planName" placeholder="输入计划事项 (Enter回车保存，Esc取消)">
				</div>
				<button class="actionbar-content-item btn btn-primary actionbar-content-submit first" type="button"><span class="glyphicon glyphicon-ok"></span> 提交</button>
				<!--<button class="actionbar-content-item btn btn-default actionbar-content-close" type="button">取消</button>-->
    		</div>
    		<div class="ebtw-clear"></div>
		</div>
	</td>
</tr>
</script>

<!-- 弹出菜单 -->
<script id="dropdown-menu-script" type="text/html">
<ul class="dropdown-menu dropdown-menu-middle">
{{# for(var i = 0, len = d.length; i < len; i++){ }}
   <li><a tabindex="-1" href="#" data-userid="{{d[i].userid}}" data-username="{{d[i].username}}">{{d[i].name}}</a></li>
{{# } }}
</ul>
</script>

<!-- 自定义进度条 -->
<script id="custom-progressbar-script" type="text/html">
<div style="width:100%; border: 1px solid rgba(0,0,0,0.1); position:relative; height:20px;">
	<div style="position:absolute; background-color:transparent; width:100%; z-index:2; {{# if(d.percentage>=100) {}}color:#fff;{{#}}}">{{d.percentage}}%</div>
	<div style="position:absolute; width:{{d.percentage}}%; height:18px; background-color: {{# if(d.percentage>=100) {}}#5cb85c{{#} else {}}#5bc0de{{#}}};"></div>
</div>
</script>

<!-- 自定义 layer prompt界面 -->
<script id="custom-layer-prompt-script" type="text/html">
<div class="custom-layer-input-content">
	<input class="custom-layer-input" value="{{#if(d.value){}}{{d.value}}{{#}}}" maxlength="{{#if(d.maxlength){}}{{d.maxlength}}{{#}}}">
</div>
</script>

<!-- 选择人员及输入文本界面 -->
<script id="input-content-and-select-person-script" type="text/html">
<div class="input-content-and-select-person">
	{{#if(d.part1){}}
	<div class="person">
		<label class="">{{d.personTitle}}</label>
		<div class="">
	    	<div class="dropdown" id="{{d.prefix}}_user">
				<input type="hidden" value="" name="{{d.prefix}}_user_id" />
				<input type="text" value="{{}}" name="{{d.prefix}}_user_name" readonly="readonly" class="form-control normal-readonly-style cursor-click" data-toggle="dropdown"/>
				<ul class="dropdown-menu dropdown-menu-middle">
					<li><a tabindex="-1" href="#" data-userid="0" data-username="">--清空内容--</a></li>
					{{#for(var i=0; i<d.persons.length; i++) {}}
					<li><a tabindex="-1" href="#" data-userid="{{d.persons[i].user_id}}" data-username="{{d.persons[i].username}}">{{d.persons[i].name}}</a></li>
					{{#}}}
				</ul>
			</div>
		</div>
	</div>
	{{#}}}
	{{#if(d.part2){}}
	<div class="content">
		<label class="">{{d.contentTitle}}</label>
		<textarea {{#if(d.contentPlaceholder){}}placeholder="{{d.contentPlaceholder}}"{{#}}} name="{{d.prefix}}_content"></textarea>
	</div>
	{{#}}}
</div>
</script>

<!-- 已选择人员  -->
<script id="selected-user-script" type="text/html">
	<div class="selected-person {{#if(d.talkToPerson){}}talk-to-person{{#}}}" {{#if(d.user_id){}}data-talk-to-uid="{{d.user_id}}"{{#}}} data-user-id="{{d.user_id}}" data-user-name="{{d.user_name}}"><span {{#if(d.user_id && d.user_account){}}title="{{d.user_account}}({{d.user_id}})"{{#}}}>{{d.user_name}}</span> <span class="t-action-item glyphicon glyphicon-remove"></span></div>
</script>

<!-- 选择人员界面  -->
<script id="select-persons-script" type="text/html">
<div class="select-persons">
	<div class="col-xs-12 head">&nbsp;</div>
	<div class="col-xs-12">
		<div class="col-xs-12 main-content">
			{{# if(d.length>0) {}}
			<div class="row">
				<div class="col-xs-6">
					<div class="row" >
						<div class="groups-container mCustomScrollbar" data-mcs-theme="dark-3">
							<div class="col-xs-12 ebtw-menu-pull-1 ebtw-menu-item ebtw-horizontal-nopadding">
							{{# for(var i = 0, len = d.length; i < len; i++){ }}
								<a href="#" class="list-group-item ebtw-left-no-border person-group-item {{# if(i==0){}}active{{#}}}" data-group-id="{{d[i].group_id}}">{{d[i].group_name}}</a>
							{{# } }}
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-6 ebtw-right-gutter-no" style="padding-left:30px;">
					{{# for(var i = 0; i < d.length; i++){ }}
					<div class="persons-container mCustomScrollbar {{# if(i!=0){}}ebtw-hide{{#}}}" data-group-id="{{d[i].group_id}}" data-mcs-theme="dark-3">
						{{# var members = d[i].members;
							for(var j = 0, len = members.length; j < len; j++){ }}
							<div class="checkbox" title="{{members[j].user_account}}({{members[j].user_id}})"><label><input type="checkbox" value="{{members[j].user_id}}" data-emp-id="{{members[j].emp_id}}" data-user-name="{{members[j].user_name}}" data-user-account="{{members[j].user_account}}"> {{members[j].user_name}}</label></div>
							{{# } }}
					</div>
					{{# } }}
				</div>
			</div>
			{{# } else {}}
			<div class="col-xs-12">&nbsp;</div>
			<div class="col-xs-12" style="text-align:center;">没有可用的记录</div>
			{{# } }}
		</div>
	</div>
</div>
</script>

<!-- 点击删除图标 -->
<script id="icon-of-remove-script" type="text/html">
&nbsp;&nbsp;<span class="glyphicon glyphicon-remove {{#if(d.moreClass){ }} {{d.moreClass}} {{#}}}" title="点击删除"></span>
</script>

<!-- 点击中止图标 -->
<script id="icon-of-stop-script" type="text/html">
&nbsp;<span class="glyphicon glyphicon-stop {{#if(d.moreClass){ }} {{d.moreClass}} {{#}}}" title="点击中止"></span>
</script>

<!-- 错误图标 -->
<script id="icon-of-error-script" type="text/html">
&nbsp;&nbsp;<span class="glyphicon glyphicon-remove-sign {{#if(d.moreClass){ }} {{d.moreClass}} {{#}}}" style="color:#f00;" title="上传失败"></span>
</script>

<!-- 停止图标 -->
<script id="icon-of-stopped-script" type="text/html">
&nbsp;&nbsp;<span class="glyphicon glyphicon-ban-circle {{#if(d.moreClass){ }} {{d.moreClass}} {{#}}}" style="color:#f00;" title="已中止"></span>
</script>

<!-- 正在上传状态的图标 -->
<script id="img-of-uploading-script" type="text/html">
&nbsp;<img class="attachment-uploading" src="{{d.img}}" title="正在上传...">
</script>

<!-- 附件链接  -->
<script id="file-link-script" type="text/html">
<span class="attachment-link" data-resource-id="{{d.resourceId}}">
<a href="#" title="点击下载">{{d.name}}</a>
&nbsp;<div class="resource-size">{{#if(d.resourceSize){}}{{popularByteSize(d.resourceSize)}}{{#}}}</div>
{{#if(d.openResource){}}
&nbsp;<div class="open-resource" title="点击在线浏览" {{#if (d.online_view_url){}}data-open-url="{{d.online_view_url}}"{{#}}} {{#if (d.view_ext_type){}}data-ext-type="{{d.view_ext_type}}{{#}}}"><span class="fa 
	{{#if (d.view_ext_type==1){}}fa-file-pdf-o{{#} else if (d.view_ext_type==2){}}fa-file-image-o{{#} else if (d.view_ext_type==3){}}fa-file-o {{#}}}
	"></span></div>
{{#}}}
</span>
</script>

<!-- 附件列表子项  -->
<script id="file-upload-list-item-script" type="text/html">
<li class="force-break" {{#if(d.added){}}data-added="1"{{#}}} data-resource-id="{{d.resourceId}}" {{#if(d.name){}}data-atta-name="{{d.name}}"{{#}}} {{#if(d.randomNumberOfId){}}data-random-number-of-id="{{d.randomNumberOfId}}"{{#}}}>
<a href="#">[{{d.name}}]</a>
&nbsp;<div class="resource-size">{{#if(d.resourceSize){}}{{popularByteSize(d.resourceSize)}}{{#}}}</div>
{{#if(d.iconRemove){ }}&nbsp;&nbsp;<span class="glyphicon glyphicon-remove attachment-remove" title="点击删除"></span>{{#}}}
{{#if(d.iconStop){ }}&nbsp;<span class="glyphicon glyphicon-stop attachment-stop" title="点击中止"></span>{{#}}}
{{#if(d.img){ }}&nbsp;<img class="attachment-uploading" src="{{d.img}}" title="正在上传...">{{#}}}
</li>
</script>
