<!-- 标签页默认(空记录) -->
<script id="sidepage-tab-script-default" type="text/html">
<div class="col-xs-12 sidepage-tab-page-module">
	<span class="date-mark">&nbsp;<span style="display:inline-block;">&nbsp;<!--没有记录--></span></span>
	<ul>
		<li class="col-xs-12 sidepage-tab-page-list"><div>&nbsp;&nbsp;没有记录</div></li>
	</ul>
</div>
</script>

<!-- 标签页记录头 -->
<!--onselectstart="javascript:return false;" style="-moz-user-select:none;"-->
<script id="sidepage-tab-script-0" type="text/html">
<div class="col-xs-12 sidepage-tab-page-module" data-date="{{d.dateStr}}">
	<span class="date-mark">&nbsp;{{d.fDate}}&nbsp;<span class="glyphicon glyphicon-chevron-up"></span></span>
	<ul>
	</ul>
</div>
</script>
<!-- 标签页子记录 -->
<script id="sidepage-tab-script-1" type="text/html">
<li class="col-xs-12 sidepage-tab-page-list" {{#if(d.opType){}}data-op-type="{{d.opType}}"{{#}}} {{#if(d.opId){}}data-op-id="{{d.opId}}"{{#}}} {{#if(d.opData){}}data-op-data="{{d.opData}}"{{#}}} {{#if(d.opName){}}data-op-name="{{d.opName}}"{{#}}}
	{{#if(d.ptrType){}}data-ptr-type="{{d.ptrType}}"{{#}}} {{#if(d.ptrId){}}data-ptr-id="{{d.ptrId}}"{{#}}}>
	<div class="line-cover line-cover-left"></div>
	<div class="line-cover line-cover-right"></div>
	<div>
		<div class="sidepage-tab-page-headphoto" {{#if(d.userId){}}data-user-id="{{d.userId}}"{{#}}}>
			<img alt="" src="{{d.headPhoto}}" {{#if(d.talkToPerson){}}class="talk-to-person" data-talk-to-uid="{{d.userId}}"{{#}}} {{#if(d.userId && d.userAccount){}}title="{{d.userAccount}}({{d.userId}})"{{#}}}/>
			<div class="ebtw-clear"></div>
		</div>
		<div class="time-mark">
			<ul>
			<li data-type="hv" class="ebtw-hide" {{#if(d.canEdit){}}data-can-edit="{{d.canEdit}}"{{#}}} {{#if(d.canDelete){}}data-can-delete="{{d.canDelete}}"{{#}}}>
				{{#if(d.canEdit){}}
					<span class="fa fa-save tab-page-list-save ebtw-hide ebtw-color-info" title="点击保存">&nbsp;</span>
					<span class="fa fa-undo tab-page-list-undo ebtw-hide" title="点击取消">&nbsp;</span>
					<span class="fa fa-edit tab-page-list-edit" title="{{#if(d.opType==3){}}点击修改我的[评论/回复]{{#} else {}}点击编辑{{#}}}">&nbsp;</span>
				{{#}}}
				{{#if(d.canDelete){}}
					<span class="fa fa-remove tab-page-list-remove" title="{{#if(d.opType==3){}}删除该[评论/回复]{{#} else {}}点击删除{{#}}}"></span>
				{{#}}}
			</li>
			</ul>
		</div>
		<div class="sidepage-tab-page-detail">
			<div class="p-title"><span {{#if(d.talkToPerson){}}class="talk-to-person" data-talk-to-uid="{{d.userId}}"{{#}}} {{#if(d.userId && d.userAccount){}}title="{{d.userAccount}}({{d.userId}})"{{#}}}>{{d.userName}}</span>&nbsp;&nbsp;<span class="time-mark">{{d.fCreateTime}}</span></div>
			{{#if (d.operate || d.name || d.pMainShowPtrSource){}}
			<div class="p-main">{{#if(d.operate){}}{{d.operate}}{{#}}}{{# if(d.name) { }}{{d.name}} {{# } }}{{#if(d.pMainShowPtrSource){}}{{d.pMainShowPtrSource}}{{#}}}</div>
			{{#}}}
			{{#if(d.detail){}}
			<div class="p-detail">{{controlCharactersToHtml(d.detail)}}</div>
			{{#}}}
			{{#if(d.ptrSource) {}}
			<div class="p-source">{{d.ptrSource}}</div>
			{{#}}}
			{{#if(d.tail){}}
			<div class="p-tail">{{d.tail}}</div>
			{{#}}}
		</div>
		<div class="sidepage-tab-page-tails">
			{{#if (d.countOfDiscuss!=undefined) {}}
				<div class="discuss-count" title="点击查看所有评论/回复内容">{{d.countOfDiscuss}} 条评论</div>
			{{#}}}
		</div>
		{{#if(d.innerDiscuss){}}
		<!-- 评论 -->
		<div class="sidepage-tab-page-subtoolbar ebtw-hide" onselectstart="javascript:return false;" style="-moz-user-select:none;"><span class="fa fa-commenting-o toggle-discuss-area" title="点击评论/回复"></span></div>
		<div class="inner-discuss ebtw-hide"></div>
		{{#}}}
	</div>
</li>
</script>

<!-- 生成可以点击的文档名链接 -->
<script id="sidepage-tab-script-ptr-source" type="text/html">
<a href="#" title="点击查看" class="ptr_item" data-ptr-type="{{d.ptrType}}" data-ptr-id="{{d.ptrId}}" {{#if(d.ptrType==3){}}data-report-period="{{d.period}}"{{#}}}>{{d.fromName}}</a>
</script>

<!-- 生成可以点击的打开邮件应用(eb-open-subid://)的链接 -->
<script id="sidepage-tab-script-open-email-app" type="text/html">
<a href="eb-open-subid://1002300104" title="点击进入我的邮件" class=""><span {{#if(d.recycleBin){}}style="text-decoration:line-through;"{{#}}}>{{d.fromName}}</span></a>
</script>

<!-- 行内评论/回复 -->
<script id="sidepage-tab-script-inner-discuss" type="text/html">
<div class="sidepage-tab-page-discuss">
	<div class="triangle_border_up"><span></span></div> <!-- 向上的三角形 -->
	<textarea placeholder="输入评论内容，Ctrl+Enter提交"></textarea>
	<button type="button" class="btn btn-primary discuss-submit pull-right">提  交</button>
	<div class="sidepage-tab-page-attachment">
		<div class="m1 ebtw-file-upload">
			<div><span class="glyphicon glyphicon-paperclip"></span> 上传附件</div>
			<input type="file" class="file_upload_input" name="up_file"><!-- file控件name字段必要，否则不能上传文件 -->
		</div>
		<div class="m2 ebtw-file-upload-list">
			<ul></ul>
		</div>
		<div class="ebtw-clear"></div>
	</div>
</div>
</script>

<!-- 标签页子记录2 -->
<script id="sidepage-tab-script-2" type="text/html">
<li class="col-xs-12 sidepage-tab-page-list">
	<div>
		<div class="sidepage-tab-page-headphoto" {{#if(d.userId){}}data-user-id="{{d.userId}}"{{#}}}>
			<img alt="" src="{{d.headPhoto}}" {{#if(d.talkToPerson){}}class="talk-to-person" data-talk-to-uid="{{d.userId}}"{{#}}} {{#if(d.userId && d.userAccount){}}title="{{d.userAccount}}({{d.userId}})"{{#}}}/>
			<div class="ebtw-clear"></div>
		</div>
		<div class="time-mark">
			<ul>
			</ul>
		</div>
		
		<div class="sidepage-tab-page-detail">
			<div class="p-title"><span {{#if(d.talkToPerson){}}class="talk-to-person" data-talk-to-uid="{{d.userId}}"{{#}}} {{#if(d.userId && d.userAccount){}}title="{{d.userAccount}}({{d.userId}})"{{#}}}>{{d.userName}}</span>&nbsp;&nbsp;<span class="time-mark">{{d.fCreateTime}}</span></div>
			<span class="p-main associate_ptr">{{d.targetName}}{{# if(d.name) { }}：<span data-ptr-id="{{d.ptrId}}" data-ptr-type="{{d.ptrType}}" class="associate_title associate_redirect" title="{{d.tips}}">{{d.name}}</span> {{# } }}</span>
		</div>
		<div class="ebtw-clear"></div>
	</div>
</li>
</script>

<!-- 标签页成员记录头 -->
<script id="sidepage-tab-script-10" type="text/html">
<div class="col-xs-12 sidepage-tab-page-module" data-share-type="{{d.shareType}}">
	<span class="date-mark">&nbsp;{{d.shareTypeName}}&nbsp;<span class="glyphicon glyphicon-chevron-up"></span></span>
	<ul>
	</ul>
</div>
</script>
<!-- 标签页成员子记录 -->
<script id="sidepage-tab-script-11" type="text/html">
<li class="col-xs-12 sidepage-tab-page-list st-shareuser">
	{{# for(var i = 0, len = d.shares.length; i < len; i++) {
		var share = d.shares[i];
	}}
	<div>
		{{share.userInforHtml}}<!-- 成员头像占位 -->
		<div class="ebtw-clear"></div>
	</div>
	{{# } }}
	{{#if (d.canAdd){}}
	<div class="shareuser-actions">
		<div class="add-shareuser" title="邀请成员">
			<span class="glyphicon glyphicon-plus" ></span>
		</div>
	</div>
	{{#}}}
</li>
</script>
<!-- 标签页成员子记录：一个成员 -->
<script id="sidepage-tab-script-12" type="text/html">
	<div class="share_user" data-share-id="{{d.share_id}}">
		<div class="sidepage-tab-page-headphoto" {{#if(d.user_id){}}data-user-id="{{d.user_id}}"{{#}}} data-user-name="{{d.user_name}}">
			<div class="image-cover" {{#if(d.user_id && d.userAccount){}}title="{{d.userAccount}}({{d.user_id}})"{{#}}}>
				{{#if(d.canDelete){}}
				<div class="toolbar-mask ebtw-hide"><span class="glyphicon glyphicon-remove toolbar-mask-item person-remove"></span></div>
				{{#}}}
				<img alt="" src="{{d.headPhoto}}" {{#if(d.talkToPerson){}}class="talk-to-person"{{#}}} {{#if(d.user_id){}}data-talk-to-uid="{{d.user_id}}"{{#}}}/>
			</div>
			<div class="inforDetail"><div class="user-name"><span {{#if(d.talkToPerson){}}class="talk-to-person"{{#}}} {{#if(d.user_id){}}data-talk-to-uid="{{d.user_id}}"{{#}}} {{#if(d.user_id && d.userAccount){}}title="{{d.userAccount}}({{d.user_id}})"{{#}}}>{{d.user_name}}</span></div></div>
		</div>
		<div class="action-detail">
		{{#if(d.shareInvoiceTime){}}
			<div>添加时间：{{d.shareInvoiceTime.substr(0,16)}}</div>
			{{#if(d.read_flag==1){}}
			<div class="">查阅时间：{{d.readTime.substr(0,16)}}</div>
			{{#} else {}}
			<div class="ebtw-color-unread">未查阅</div>
			{{#}}}
		{{#}}}
		{{#if(d.shareFavoriteTime){}}
			<div>关注时间：{{d.shareFavoriteTime.substr(0,16)}}</div>
		{{#}}}
		</div>
	</div>
</script>

<!-- 标签页附件记录头 -->
<script id="sidepage-tab-script-20" type="text/html">
<div class="col-xs-12 sidepage-tab-page-module" data-atta-type="{{d.attaType}}">
	<span class="date-mark">&nbsp;{{d.attaTypeName}}&nbsp;<span class="glyphicon glyphicon-chevron-up"></span></span>
	<ul>
	</ul>
</div>
</script>
<!-- 标签页附件子记录 -->
<script id="sidepage-tab-script-21" type="text/html">
<li class="col-xs-12 sidepage-tab-page-list" data-op-type="0" {{#if(d.resourceId){}}data-resource-id="{{d.resourceId}}"{{#}}} {{#if(d.resourceName){}}data-resource-name="{{d.resourceName}}"{{#}}} >
	<div class="line-cover line-cover-left"></div>
	<div class="line-cover line-cover-right"></div>
	<div>
		<div class="sidepage-tab-page-headphoto" {{#if(d.userId){}}data-user-id="{{d.userId}}"{{#}}}>
			<img alt="" src="{{d.headPhoto}}" {{#if(d.talkToPerson){}}class="talk-to-person" data-talk-to-uid="{{d.userId}}"{{#}}} {{#if(d.userId && d.userAccount){}}title="{{d.userAccount}}({{d.userId}})"{{#}}}/>
			<div class="ebtw-clear"></div>
		</div>
		<div class="time-mark long-time">
			<ul>
			<li data-type="hv" class="ebtw-hide" {{#if(d.canEdit){}}data-can-edit="{{d.canEdit}}"{{#}}} {{#if(d.canDelete){}}data-can-delete="{{d.canDelete}}"{{#}}}">
				{{#if(d.canDelete){}}<span class="fa fa-remove tab-page-list-remove" title="点击删除"></span>{{#}}}
			</li>
			</ul>
		</div>
		<div class="sidepage-tab-page-detail long-time">
			<div class="p-title"><span {{#if(d.talkToPerson){}}class="talk-to-person" data-talk-to-uid="{{d.userId}}"{{#}}} {{#if(d.userId && d.userAccount){}}title="{{d.userAccount}}({{d.userId}})"{{#}}}>{{#if(d.userName){}}{{d.userName}}&nbsp;&nbsp;{{#}}}</span><span class="time-mark">{{d.fCreateTime}}</span></div>
			{{#if(d.operate || d.name){}}
			<div class="p-main">{{#if(d.operate){}}{{d.operate}}{{#}}}{{# if(d.name) { }}{{d.name}} {{# } }}</div>
			{{#}}}
			{{#if(d.detail){}}
			<div class="p-detail">{{controlCharactersToHtml(d.detail)}}</div>
			{{#}}}
			{{#if(d.ptrSource) {}}
			<div class="p-source ">{{d.ptrSource}}</div>
			{{#}}}
		</div>
		<div class="ebtw-clear"></div>
	</div>
</li>
</script>
