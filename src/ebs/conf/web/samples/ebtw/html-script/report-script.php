<!-- 报告：没有记录  -->
<script id="report-list-no-row-script" type="text/html">
			<div class="report-list-row">
				<div class="form-inline report-list-item">
					<div class="form-group datetime-box"></div>
					<div class="form-group content-box">
						<div class="content-box-toolbar">
							<div class="form-group content-box-toolbar-title"><span>信息提示</span></div>
							<div class="ebtw-clear"></div>
						</div>
						<div class="content-box-row">
							<div class="content-box-attr-absent">{{#if(d.message){}}{{d.message}}{{#} else {}}没有记录{{#}}}</div>
						</div>			
				
					</div>
				</div>
			</div>
</script>

<!-- 报告：空白  -->
<script id="report-list-empty-row-script" type="text/html">
<div class="report-list-row" data-ptrid="{{d.report_id}}">
	<div class="form-inline report-list-item {{#if(d.isWeekend){}}weekend{{#}}} {{#if(d.isNew){}}unreport{{#}}}">
		<div class="form-group datetime-box">
			{{#if(d.isOtherView){}}<span class="talk-to-person" title="{{d.user_account}}({{d.report_uid}})" data-talk-to-uid="{{d.report_uid}}">{{d.showedCreatorName}}</span>{{#} else {}}<span class="remark-txt">{{#if(d.isNew){}}未填写{{#}}}</span>{{#}}}
		</div>
		
		<div class="form-group content-box empty-report {{#if(d.isNew){}}new-rpt{{#}}} {{#if(d.isWeekend){}}weekend-rpt{{#}}}">
			<div class="content-box-toolbar empty-report">
				<div class="form-group content-box-toolbar-title {{#if(d.isNew){}}unreport{{#}}}"><span>{{d.formatedStartTimes[3]}} {{d.formatedStartTimes[1]}}</span></div>
				<div class="ebtw-clear"></div>
			</div>
			
			<div class="content-box-attr-name empty-report">日报未填写</div>
		</div>
	</div>
</div>
</script>

<!-- 日报：下级日报完成情况统计  -->
<script id="report-list-statistic-row-script" type="text/html">
<div class="report-list-row statistic">
	<span>{{d.formatedDateStrs[3]}}&nbsp;{{d.formatedDateStrs[1]}}&nbsp;(下级<span class="remark-txt">{{d.memberCount}}</span>人
		{{#if(d.weekend){}}
			{{#if(d.uncomplete!=d.memberCount){}}
				,&nbsp;<span class="remark-txt">{{d.memberCount-d.uncomplete}}</span>人提交日报)
			{{#}}}
		{{#} else {}}
			<span class="remark-txt">{{d.uncomplete}}</span>人未提交日报
		{{#}}}
		)
	</span>
</div>
</script>

<!-- 日报：列表记录  -->
<script id="daily-report-list-row-script" type="text/html">
			<div class="report-list-row" data-ptrid="{{d.report_id}}" {{#if(d.isEdit){}}data-is-edit="{{d.isEdit}}"{{#}}} {{#if(d.animate && d.width){}}style="left:-{{d.width}};"{{#}}}>
				<div class="form-inline report-list-item {{#if(d.isWeekend){}}weekend{{#}}} {{#if(d.isNew){}}unreport{{#}}}">
					<div class="left-box">
						<div class="form-group datetime-box">
						<div></div> {{#if(d.isOtherView){}}<span class="talk-to-person" title="{{d.user_account}}({{d.report_uid}})" data-talk-to-uid="{{d.report_uid}}">{{d.showedCreatorName}}</span>{{#} else {}}<span class="remark-txt">{{#if(d.isNew && !d.formatedStartTimes[4]){}}未填写{{#} else {}} {{#if(d.formatedStartTimes[0]==1){}}今天{{#}}} {{#if(d.formatedStartTimes[0]==-1){}}昨天{{#}}} {{#}}}</span>{{#}}}
						</div>
						<div class="blank-to-click"></div>
					</div>
					
					<div class="form-group content-box">
						<form>
						{{#if(d.isEdit){}}<input type="hidden" name="op_type" value="71">{{#}}}
							
						{{#if(d.report_id){}}<input type="hidden" name="pk_report_id" value="{{d.report_id}}">{{#}}}
						{{#if(d.start_time){}}<input type="hidden" name="start_time" value="{{d.start_time}}">{{#}}}
						{{#if(d.stop_time){}}<input type="hidden" name="stop_time" value="{{d.stop_time}}">{{#}}}
						
						<div class="content-box-toolbar">
							<div class="form-group content-box-toolbar-title can-open {{#if(d.isNew){}}unreport{{#}}}"
								title="点击进入详细内容" onclick="openReport('daily', {{#if(d.isNew){}}'a'{{#} else {}}'v'{{#}}}, '{{d.report_id}}' {{#if(d.isNew){}}, '{{d.start_time}}'{{#}}});"
								><span>{{d.formatedStartTimes[3]}} {{d.formatedStartTimes[1]}}</span></div>
							<div class="content-box-toolbar-wrap ebtw-hide" {{#if(d.canEdit){}}data-can-edit="{{d.canEdit}}"{{#}}}>
								<div class="form-group content-box-toolbar-btn"><span class="fa fa-arrows-alt" title="点击进入详细内容" onclick="openReport('daily', {{#if(d.isNew){}}'a'{{#} else {}}'v'{{#}}}, '{{d.report_id}}' {{#if(d.isNew){}}, '{{d.start_time}}'{{#}}});"></span></div>
								{{#if(d.canEdit){}}
								<div class="form-group content-box-toolbar-btn btn-edit"><span class="fa fa-edit" title="点击编辑"></span></div>
								<div class="form-group content-box-toolbar-btn btn-undo ebtw-hide"><span class="fa fa-undo" title="点击取消"></span></div>
								<div class="form-group content-box-toolbar-btn btn-save ebtw-hide"><span class="fa fa-save ebtw-color-info" title="点击保存"></span></div>
								{{#}}}
							</div>
							<div class="ebtw-clear"></div>
						</div>
						
						<div class="content-box-row">
							<div class="content-box-attr-name">已完成工作</div>
							<div class="content-box-attr-content-ex">
								<input type="hidden" name="completed_work" value='{{#if(d.completed_work){}}{{d.completed_work}}{{#}}}'>
								<div {{#if(d.canEdit){}}class="edit-e"{{#}}} name="completed_work" placeholder="填写当天已完成工作内容，回车换行">{{#if(d.completed_work){}}{{controlCharactersToHtml(d.completed_work)}}{{#}}}</div>{{#if(d.isNew && d.canEdit){}}<span class="required-mark">&nbsp;*</span>{{#}}}
							</div>
						</div>
						<div class="content-box-row">
							<div class="content-box-attr-name">未完成工作</div>
							<div class="content-box-attr-content-ex">
								<input type="hidden" name="uncompleted_work" value='{{#if(d.uncompleted_work){}}{{d.uncompleted_work}}{{#}}}'>
								<div {{#if(d.canEdit){}}class="edit-e"{{#}}} name="uncompleted_work" placeholder="填写当天未完成工作内容，回车换行">{{#if(d.uncompleted_work){}}{{controlCharactersToHtml(d.uncompleted_work)}}{{#}}}</div>
							</div>
						</div>
			            {{#if(d.review_user){}}
			            <div class="content-box-row">
			            	<div class="content-box-attr-name">评阅人</div>
	            			<div class="content-box-attr-content">
								{{#if(d.isNew || (d.canEdit && d.status==0)){}}
	            				<div class="dropdown review_user">
	            					<input type="hidden" {{#if(d.review_user){}}value="{{d.review_user.share_uid}}"{{#}}} name="old_review_user_id"/>
	            					<input type="hidden" {{#if(d.review_user){}}value="{{d.review_user.share_uid}}"{{#}}} name="review_user_id" class="edit-e"/>
	            					<input type="text" {{#if(d.review_user){}}value="{{d.review_user.share_name}}"{{#}}} name="review_user_name"
	            						readonly="readonly" class="form-control normal-readonly-style cursor-click {{#if(d.status!=0){}}disable-change{{#}}}" data-toggle="dropdown"/>
								</div>
								{{#} else {}}
									{{#if(d.talkToPerson && d.review_user){}}
										<span class="talk-to-person" data-talk-to-uid="{{d.review_user.share_uid}}" title="{{d.review_user.user_account}}({{d.review_user.share_uid}})">{{d.review_user.share_name}}</span>
									{{#} else {}}
										<span>{{#if(d.review_user){}}{{d.review_user.share_name}}{{#}}}</span>
									{{#}}}
								{{#}}}
								{{#if(d.review_user && d.status>=1 && d.logonUserId!=d.review_user.share_uid){}}<!--给提交人显示的内容-->
									&nbsp;&nbsp;
									{{#if(d.review_user.read_flag==1){}}
										{{#if(d.review_user.result_status!=0 && d.review_user.result_time){}}
											<span id="report_review_user" class="static-text ebtw-color-already-read">{{$.D_ALG.formatDate(new Date(d.review_user.result_time), 'yyyy-mm-dd hh:ii')}} 评阅人已回复</span>
										{{#} else {}}
											<span id="report_review_user" class="static-text ebtw-color-already-read">{{$.D_ALG.formatDate(new Date(d.review_user.read_time), 'yyyy-mm-dd hh:ii')}} 评阅人已阅</span>
										{{#}}}
									{{#} else {}}
										<span id="report_review_user" class="static-text ebtw-color-unread">评阅人未阅</span>
									{{#}}}
							 	{{#}}}
								{{#if(d.review_user && d.status>=1 && d.logonUserId==d.review_user.share_uid){}}<!--给评阅人显示的内容-->
									&nbsp;&nbsp;
									{{#if(d.status==1 && d.review_user.result_status==0 && !d.review_user.result_time){}}
										{{#if(d.review_user.read_flag==1){}}
											<span id="report_review_user" class="static-text ebtw-color-waitting-dealwith">等待评阅回复</span>
										{{#} else {}}
											<span id="report_review_user" class="static-text ebtw-color-unread">评阅人未阅</span>
										{{#}}}
										&nbsp;&nbsp;<button type="button" class="btn btn-primary btn-open-report-review">评阅回复</button>
									{{#} else if (d.review_user.result_status!=0 && d.review_user.result_time) {}}
											<span id="report_review_user" class="static-text ebtw-color-already-read">已经评阅回复 {{$.D_ALG.formatDate(new Date(d.review_user.result_time), 'yyyy-mm-dd hh:ii')}}</span>
									{{#}}}
								{{#}}}
	            			</div>
				            
							{{#if(false){}}
				            <!-- 
				            <div class="content-box-attr-content middle pull-right mood">
				               <span class="middle">今日心情 &nbsp;</span>
							   <label class="radio-inline">
							      <input type="radio" name="optionsRadiosinline" value="1" checked> 伤心
							   </label>
							   <label class="radio-inline">
							      <input type="radio" name="optionsRadiosinline"  value="2"> 难过
							   </label>		        
							   <label class="radio-inline">
							      <input type="radio" name="optionsRadiosinline"  value="3"> 努力
							   </label>	
							   <label class="radio-inline">
							      <input type="radio" name="optionsRadiosinline"  value="4"> 微笑
							   </label>	
							   <label class="radio-inline">
							      <input type="radio" name="optionsRadiosinline"  value="5"> 高兴
							   </label>
				            </div>
				            <div class="ebtw-clear"></div>
				             -->
							{{#}}}
		            	</div>
						{{#}}}
						
						{{#if(!d.isNew){}}
						<div class="content-box-row">
							<div class="content-box-attr-notice"><span class="light">{{d.create_time}}</span> &nbsp;{{#if(d.isExpired){}}<span class="warning">逾期提交</span>{{#}}}{{#if(d.last_modify_time){}}&emsp;&emsp;<span class="light">最后修改 {{d.last_modify_time}}</span>{{#}}}</div>
						</div>
						{{#}}}

						{{#if(d.isEdit){}}
					    <!-- 分隔线 -->
						<div class="div-divide-all">
							<div class="divide-line-all"></div>
						</div>
						
						<div class="content-box-row">
							<div class="content-box-tab onlyview">
								<div class="content-box-tab-head" data-target-tabtype="11">内容{{#if(d.countedOprs && d.countedOprs.edit!=undefined){}}(<span>{{d.countedOprs.edit}}</span>){{#}}}</div>
								<div class="content-box-tab-head" data-target-tabtype="2">评论/回复{{#if(d.countedOprs && d.countedOprs.discuss!=undefined){}}(<span>{{d.countedOprs.discuss}}</span>){{#}}}</div>
								<div class="content-box-tab-head" data-target-tabtype="1">评阅{{#if(d.countedOprs && d.countedOprs.review!=undefined){}}(<span>{{d.countedOprs.review}}</span>){{#}}}</div>
								<span class="content-box-tab-head-divide">|</span>
								<div class="content-box-tab-head" data-target-tabtype="3">自动汇报{{#if(d.countedOprs && d.countedOprs.plan_task!=undefined){}}(<span>{{d.countedOprs.plan_task}}</span>){{#}}}</div>
								<div class="content-box-tab-head" data-auto-report="1" data-target-tabtype="4">附件<span></span></div>
								<div class="content-box-tab-head" data-target-tabtype="20">操作日志{{#if(d.countedOprs && d.countedOprs.all!=undefined){}}(<span>{{d.countedOprs.all}}</span>){{#}}}</div>
							</div>
							<div class="ebtw-clear"></div>
						</div>
						{{#} else if(d.isNew) {}}
						<div class="content-box-row">
            				<div class="ebtw-file-upload-wrap">
                    			<div class="ebtw-file-upload {{#if(d.canEdit!=1 && d.isNew!=1){}}onlyview{{#}}}" style="float:right;">
                        		<span class="glyphicon glyphicon-paperclip"></span>
                       			<div id="file_upload" class="webuploader-container"><div class="webuploader-pick" onselectstart="javascript:return false;" style="-moz-user-select:none;">上传附件</div></div>
                        		{{#if(d.canEdit){}}<input type="file" class="file_upload_input" name="up_file"><!-- file控件name字段必要，否则不能上传文件 -->{{#}}}
                    			</div>
                			</div>
                			<div class="col-xs-9">
								<div class="ebtw-file-upload-list">
									<ul></ul>
								</div>
							</div>
		                </div>
						{{#}}}
						</form>
					</div>
				</div>
			</div>
</script>

<!-- 日报：内容  -->
<script id="daily-report-details-script" type="text/html">
			<div class="report-list-row" data-ptrid="{{d.report_id}}" {{#if(d.isEdit){}}data-is-edit="{{d.isEdit}}"{{#}}}>
				<div class="form-inline report-list-item">
					<div class="form-group content-box report-details">
						<form>
						<input type="hidden" name="op_type" value="71">
						
						{{#if(d.report_id){}}<input type="hidden" name="pk_report_id" value="{{d.report_id}}">{{#}}}
						{{#if(d.start_time){}}<input type="hidden" name="start_time" value="{{d.start_time}}">{{#}}}
						{{#if(d.stop_time){}}<input type="hidden" name="stop_time" value="{{d.stop_time}}">{{#}}}
						
						<div class="content-box-row">
							<div class="content-box-attr-name">已完成工作</div>
							<div class="content-box-attr-content-ex">
								<input type="hidden" name="completed_work" value='{{#if(d.completed_work){}}{{d.completed_work}}{{#}}}'>
								<div {{#if(d.canEdit){}}class="edit-e"{{#}}} {{#if(d.canEdit){}}contenteditable="true"{{#}}} name="completed_work" placeholder="填写当天已完成工作内容，回车换行">{{#if(d.completed_work){}}{{controlCharactersToHtml(d.completed_work)}}{{#}}}</div>{{#if(d.canEdit){}}<span class="required-mark">&nbsp;*</span>{{#}}}
							</div>
						</div>
						<div class="content-box-row">
							<div class="content-box-attr-name">未完成工作</div>
							<div class="content-box-attr-content-ex">
								<input type="hidden" name="uncompleted_work" value='{{#if(d.uncompleted_work){}}{{d.uncompleted_work}}{{#}}}'>
								<div {{#if(d.canEdit){}}class="edit-e"{{#}}} {{#if(d.canEdit){}}contenteditable="true"{{#}}} name="uncompleted_work" placeholder="填写当天未完成工作内容，回车换行">{{#if(d.uncompleted_work){}}{{controlCharactersToHtml(d.uncompleted_work)}}{{#}}}</div>
							</div>
						</div>
			            
						{{#if(d.review_user || d.canEdit){}}
			            <div class="content-box-row">
			            	<div class="content-box-attr-name">评阅人</div>
	            			<div class="content-box-attr-content">
								{{#if(d.isNew || (d.canEdit && d.status==0)){}}
	            				<div class="dropdown review_user">
	            					<input type="hidden" {{#if(d.review_user){}}value="{{d.review_user.share_uid}}"{{#}}} name="old_review_user_id"/>
	            					<input type="hidden" {{#if(d.review_user){}}value="{{d.review_user.share_uid}}"{{#}}} name="review_user_id" class="edit-e"/>
	            					<input type="text" value="{{#if(d.review_user){}}{{d.review_user.share_name}}{{#} else {}}--请选择评阅人--{{#}}}" name="review_user_name"
	            						readonly="readonly" class="form-control normal-readonly-style cursor-click {{#if(d.status!=0){}}disable-change{{#}}}" data-toggle="dropdown"/>
								</div>
								{{#} else {}}
									<span>{{#if(d.review_user){}}{{d.review_user.share_name}}{{#}}}</span>
								{{#}}}
								
								{{#if(d.review_user && d.status==1){}}
									&nbsp;&nbsp;
									{{#if(d.review_user.read_flag==1){}}
										<span id="report_review_user" class="static-text ebtw-color-already-read">{{$.D_ALG.formatDate(new Date(d.review_user.read_time), 'yyyy-mm-dd hh:ii')}} 评阅人已阅</span>
									{{#} else {}}
										<span id="report_review_user" class="static-text ebtw-color-unread">评阅人未阅</span>
									{{#}}}
									
									{{#if(d.logonUserId==d.review_user.share_uid && d.review_user.result_status==0 && !d.review_user.result_time){}}
										&nbsp;&nbsp;<button type="button" class="btn btn-primary switch-to-approval">评阅回复</button>
									{{#}}}
								{{#}}}
	            			</div>
				            
							{{#if(false){}}
				            <!-- 
				            <div class="content-box-attr-content middle pull-right mood">
				               <span class="middle">今日心情 &nbsp;</span>
							   <label class="radio-inline">
							      <input type="radio" name="optionsRadiosinline" value="1" checked> 伤心
							   </label>
							   <label class="radio-inline">
							      <input type="radio" name="optionsRadiosinline"  value="2"> 难过
							   </label>		        
							   <label class="radio-inline">
							      <input type="radio" name="optionsRadiosinline"  value="3"> 努力
							   </label>	
							   <label class="radio-inline">
							      <input type="radio" name="optionsRadiosinline"  value="4"> 微笑
							   </label>	
							   <label class="radio-inline">
							      <input type="radio" name="optionsRadiosinline"  value="5"> 高兴
							   </label>
				            </div>
				            <div class="ebtw-clear"></div>
				             -->
							{{#}}}
		            	</div>
						{{#}}}
						
						{{#if(!d.isNew){}}
						<div class="content-box-row">
							<div class="content-box-attr-notice"><span class="light">{{d.create_time}}</span> &nbsp;{{#if(d.isExpired){}}<span class="warning">逾期提交</span>{{#}}}{{#if(d.last_modify_time){}}&emsp;&emsp;<span class="light">最后修改 {{d.last_modify_time}}</span>{{#}}}</div>
						</div>
						{{#}}}
						
	    				<!-- 分隔线 -->
						<div class="col-xs-12 div-divide-top-pull {{#if(d.isOnlyView){}}waitting-for-show ebtw-hide{{#}}}" style="padding-left:5px; padding-right:5px;">
							<div class="divide-line col-xs-5"></div>
							<div class="col-xs-2 divide-text" id="divide-0" onselectstart="javascript:return false;">分隔线&nbsp;<span class="glyphicon glyphicon-chevron-up"></span></div>
							<div class="divide-line col-xs-5"></div>
						</div>
						<div class="content-box-row {{#if(d.isOnlyView){}}waitting-for-show ebtw-hide{{#}}}">
            				<div class="ebtw-file-upload-wrap">
                    			<div class="ebtw-file-upload {{#if(d.canEdit!=1 && d.isNew!=1){}}onlyview{{#}}}" style="float:right;">
                        		<span class="glyphicon glyphicon-paperclip"></span>
                       			<div id="file_upload" class="webuploader-container"><div class="webuploader-pick" onselectstart="javascript:return false;" style="-moz-user-select:none;">上传附件</div></div>
                        		{{#if(d.canEdit){}}<input type="file" class="file_upload_input" name="up_file"><!-- file控件name字段必要，否则不能上传文件 -->{{#}}}
                    			</div>
                			</div>
                			<div class="col-xs-9">
								<div class="ebtw-file-upload-list">
									<ul></ul>
								</div>
							</div>
		                </div>
						</form>
					</div>
				</div>
			</div>
</script>

<!-- 普通报告：记录  -->
<script id="other-report-list-row-script" type="text/html">

</script>
