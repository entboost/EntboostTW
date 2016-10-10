<!--  看板 -->

<!-- 看板：头部 -->
<script id="board-lane-toolbar-script" type="text/html">
	<div class="form-group">{{d.title}}</div>
	{{#if(false && !isNaN(d.ptr_type)){}}<div class="form-group action-item btn-AddPTR" data-ptr-type="{{d.ptr_type}}"><span class="glyphicon glyphicon-plus"></span></div>{{#}}}
	<div class="form-group action-item" onclick="load_board_lane{{d.lane_no}}({{d.lane_no}});"><span class="glyphicon glyphicon-refresh"></span></div>
	<div class="form-group count-badge"></div>
	<div class="ebtw-clear"></div>
</script>

<!-- 看板：空白行记录 -->
<script id="board-lane-empty-item-script" type="text/html">
<div class="board-lane-item board-lane-item-empty" data-ptrid="-1">
	<div class="form-inline lane-item-row">
		<div class="form-group lane-item-text" style="line-height:20px;">没有记录</div>
	</div>
</div>
</script>

<!-- 看板：进度状态颜色块 -->
<script id="board-lane-swatches-item-progress-script" type="text/html">
{{#if(d.dictOfProgress){}}
	<div class="col-xs-2 swatches-item-progress" title="{{#if(d.dictOfProgress.status<=1 || d.dictOfProgress.percentage==0){}}未开始{{#} else {}}进度{{d.dictOfProgress.percentage}}%，总耗时{{d.dictOfProgress.workTime}}小时{{#}}}">
		<div class="{{d.dictOfProgress.css}}" style="width:{{d.dictOfProgress.percentage}}%;"></div>
	</div>
{{#}}}
</script>

<!-- 看板：行记录 -->
<script id="board-lane-item-script" type="text/html">
		<div class="board-lane-item" data-ptr-id="{{d.ptr_id}}" data-ptr-type="{{d.ptr_type}}" {{#if(d.ptr_type==3){}}data-report-period="{{d.period}}"{{#}}}>
			<div class="form-inline lane-item-row swatches">
				{{#if(d.importantSwatchHtml){}}{{d.importantSwatchHtml}}{{#}}}<!-- 重要程度颜色块 -->
				
				{{#if(d.dictOfProgressHtml){}}{{d.dictOfProgressHtml}}{{#}}}
				
				{{#if (d.floatSwatchItems){}}
					{{# for(var i=0;i<d.floatSwatchItems.length;i++){}}
						<div class="swatches-item-float {{#if(d.floatSwatchItems[i].backgroundCss){}}{{d.floatSwatchItems[i].backgroundCss}}{{#}}}" style="right:{{i*(58+10)+2}}px; {{#if(d.floatSwatchItems[i].backgroundColor){}}background-color:{{d.floatSwatchItems[i].backgroundColor}};{{#}}}">{{d.floatSwatchItems[i].title}}</div>
					{{#}}}
				{{#}}}
				<div class="ebtw-clear"></div>
			</div>
			<div class="ebtw-clear"></div>
			<div class="form-inline lane-item-row">
				<div class="form-group lane-item-icon mark-read" title="未阅" {{#if(d.su_share_id){}}data-share-id="{{d.su_share_id}}"{{#}}}><div {{#if(d.su_read_flag==0 || d.read_flag==0){}}class="radius-point"{{#}}}></div></div><!-- radius-point-none -->
				<div class="form-group lane-item-text ptr_title view-ptr">{{d.ptr_name}}</div>
				<div class="ebtw-clear"></div>
			</div>
			<div class="form-inline lane-item-row lane-item-actionbar-wrap">
				<div class="form-group lane-item-datetime" title="{{#if(d.period_tips){}}{{d.period_tips}}{{#} else {}}{{d.start_time.substr(0, 16)}} ~ {{d.stop_time.substr(0, 16)}}{{#}}}">{{d.show_time}}</div>
				{{#if(d.showCreateName!==false) {}}
				<div class="form-group lane-item-person {{#if(d.talkToPerson){}}talk-to-person{{#}}}" {{#if(d.talkToPerson){}}data-talk-to-uid="{{d.personUid}}"{{#}}} {{#if(d.personUid){}}title="{{d.personAccount}}({{d.personUid}})"{{#}}}>{{d.personName}}</div>
				{{#}}}
				<div class="form-group lane-item-actionbar {{#if(!d.favorite){}}ebtw-invisible{{#}}}">
					{{#if(d.canComplete){}}
					<div class="ebtw-invisible complete"><span class="glyphicon glyphicon-unchecked unchecked" title="点击将{{#if(d.ptr_type==1){}}计划{{#} else if(d.ptr_type==2) {}}任务{{#}}}标为完成"></span></div>
					{{#}}}
					
					{{#if(d.menuItems){}}
					<div class="ebtw-invisible sub-menus dropdown {{#if(d.is_last_row){}}dropup{{#}}}">
					   <div class="glyphicon glyphicon-th-large action-menu-toggle" data-toggle="dropdown"></div>
						{{#if(d.menuItems.length>0){}}
						<ul class="dropdown-menu dropdown-menu-right dropdown-menu-small">
							{{#for(var i=0; i<d.menuItems.length; i++){}}
					    	<li data-type="{{d.menuItems[i].dataType}}"><a tabindex="-1" href="#">{{d.menuItems[i].name}}</a></li>
							{{#}}}
						</ul>
						{{#}}}
					</div>
					{{#}}}
					
					{{#if(d.canFavorite) {}}
					<div class="favorite {{#if(d.favorite){}}always{{#}}}"><span class="glyphicon {{#if(d.favorite){}}glyphicon-heart{{#}else{}}glyphicon-heart-empty{{#}}}" title="{{#if(d.favorite){}}取消关注{{#}else{}}点击关注该任务{{#}}}"></span></div>
					{{#}}}
				</div>
				<div class="ebtw-clear"></div>
			</div>
		</div>
</script>