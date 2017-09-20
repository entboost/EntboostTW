<?php
include dirname(__FILE__).'/preferences.php';
$ECHO_MODE = 'html'; //输出类型
require_once dirname(__FILE__).'/include.php';
$relative_path = '';

	require_once dirname(__FILE__).'/html-script/board-script.php';
	require_once dirname(__FILE__).'/html-script/period-selector-script.php';
	require_once dirname(__FILE__).'/html-script/some-script.php';
	require_once dirname(__FILE__).'/html-script/select-option-script.php';
	require_once dirname(__FILE__).'/html-script/sidepage-tab-script.php';
	require_once dirname(__FILE__).'/html-script/report-script.php';
	
	$userId = $_SESSION[USER_ID_NAME]; //当前用户的编号
?>
<div class="col-xs-12 wkfiles-tab-wrap ebtw-no-border">
	<div class="col-xs-12 wkfiles-tab">
		<!-- <div class="wkfiles-tab-head" id="wkfiles-tab0"><span>全部(<span class="scount">0</span>)</span><span class="wkfiles-tab-select"></span></div> -->
		<div class="wkfiles-tab-head" id="wkfiles-tab1"><span>云盘文件(<span class="scount">0</span>)</span><span class="wkfiles-tab-select"></span></div>
		<div class="wkfiles-tab-head" id="wkfiles-tab2"><span>计划文件(<span class="scount">0</span>)</span><span class="wkfiles-tab-select"></span></div>
		<div class="wkfiles-tab-head" id="wkfiles-tab3"><span>任务文件(<span class="scount">0</span>)</span><span class="wkfiles-tab-select"></span></div>
		<div class="wkfiles-tab-head" id="wkfiles-tab4"><span>日报文件(<span class="scount">0</span>)</span><span class="wkfiles-tab-select"></span></div>
		<!-- <div class="wkfiles-tab-head" id="wkfiles-tab5"><span>报告文件(<span class="scount">0</span>)</span><span class="wkfiles-tab-select"></span></div> -->
		<div class="wkfiles-tab-head" id="wkfiles-tab6"><span>邮件附件(<span class="scount">0</span>)</span><span class="wkfiles-tab-select"></span></div>
	</div>
	
	<div id="wkfiles-tab-content" class="col-xs-12 ebtw-embed-row"></div>
</div>
<input type="hidden" id="wkfiles-content-height-input" value="0">
<script type="text/javascript">
var rootUrl = '<?php echo $ROOT_URL;?>';
var DefaultParameter = {from_id:0, flag:null, get_summary:1};
var TabBadgesDatasOfWKFiles = {
	1:{activeNo:1, parameter: $.extend({from_type:3, reserved_file_types:'my_cloud_files'}, DefaultParameter)}, //云盘文件
	2:{activeNo:2, parameter: $.extend({from_type:10 + 1, reserved_file_types:'plan_files'}, DefaultParameter)}, //计划文件
	3:{activeNo:3, parameter: $.extend({from_type:10 + 2, reserved_file_types:'task_files'}, DefaultParameter)}, //任务文件
	4:{activeNo:4, parameter: $.extend({from_type:10 + 3, reserved_file_types:'report_files'}, DefaultParameter)}, //日报文件
// 	5:{activeNo:5, parameter: $.extend({from_type:10 + 3}, DefaultParameter)}, //报告文件
	6:{activeNo:6, parameter: $.extend({from_type:20, reserved_file_types:'email_files'}, DefaultParameter)}, //邮件附件
};

$contentContainer = $('#wkfiles-tab-content');

$(document).ready(function() {
	//更新tab标签内容数量
	refreshTabBadges('workbench_2', 'wkfiles-tab', [
		//TabBadgesDatasOfWKFiles[0],`
		TabBadgesDatasOfWKFiles[1],
		TabBadgesDatasOfWKFiles[2],
		TabBadgesDatasOfWKFiles[3],
		TabBadgesDatasOfWKFiles[4],
// 		TabBadgesDatasOfWKFiles[5],
		TabBadgesDatasOfWKFiles[6],
	]);

	//定义函数：执行加载数据
	var executeLoadDataFunc = function(activeNo, url, param, successHandle, errorHandle) {
		//更新tab标签内容数量
		refreshTabBadges('workbench_2', 'wkfiles-tab', [
			TabBadgesDatasOfWKFiles[activeNo],
		]);
		
		//加载文件列表
		$.ebtw.listfile(param.from_type, param.from_id, param.flag, param.get_summary, 0, 100, function(result) {
			if (result.code=='0') {
				if (successHandle) {
					var datas = [];
					if (Object.prototype.toString.call(result.resources) === '[object Array]') {
						var datas = result.resources;
						successHandle(datas);
					} else {
						successHandle(datas);
					}
					
					var fromType = parseInt(param.from_type);
					if (datas.length >0 && fromType>10 && fromType<20) {
						fromType -= 10; //业务类型代码转换(restapi的fromType与业务数据表查询的fromType代码并不相同)
						
						//===补全相关业务资料===
						//业务编号汇集成查询条件
						var ptrIds = [];
						for (var i=0; i<datas.length; i++) {
							var data = datas[i];
							if (data.from_type==param.from_type) {
								ptrIds.push(data.from_id);
							}
							data.from_type = fromType;
						}
						$.unique(ptrIds);
						
						//查询业务资料
						var parameter = {request_query_type: 3, ptr_type:fromType, ptr_id:ptrIds};
						loadResults(getServerUrl() + "workbench_list.php", parameter, function(ptrs) {
							if (ptrs) {
								var ptrIdName;
								var ptrNameName;
								var ptrCreateUidName;
								switch(fromType) {
								case 1:
									ptrIdName = 'plan_id';
									ptrNameName = 'plan_name';
									ptrCreateUidName = 'create_uid';
									break;
								case 2:
									ptrIdName = 'task_id';
									ptrNameName = 'task_name';
									ptrCreateUidName = 'create_uid';
									break;
								case 3:
									ptrIdName = 'report_id';
									ptrNameName = 'completed_work';
									ptrCreateUidName = 'report_uid';
									break;
								}
								//匹配业务资料
								
								for (var j=0; j<ptrs.length; j++) {
									var ptr = ptrs[j];
									for (var i=0; i<datas.length; i++) {
										var data = datas[i];
										if (data.from_id==ptr[ptrIdName]) {
											data.period = ptr.period;
											data.ptr_id = ptr[ptrIdName];
											data.ptr_name = ptr[ptrNameName];
											if (data.ptr_name.length==0)
												data.ptr_name = '...';

											data.ptr_create_uid = ptr[ptrCreateUidName];
										}
									}
								}
							}
							
							//渲染文件列表界面
							loadSidepageTabData('<?php echo $userId;?>', null, null, null, $contentContainer, 0, SideTabTypes['att'], datas, rootUrl, param.reserved_file_types);
							if (typeof registerSomeEvent=='function')
								registerSomeEvent();
						}, function(err) {
						});
					} else {
						//渲染文件列表界面
						loadSidepageTabData('<?php echo $userId;?>', null, null, null, $contentContainer, 0/*fromType*/, SideTabTypes['att'], datas, rootUrl, param.reserved_file_types);			
						if (typeof registerSomeEvent=='function')
							registerSomeEvent();
					}
				}
			} else {
				if (errorHandle)
					errorHandle(result);
			}
		});
	};
	//注册tab标签并设置默认选中	
	registerTab(getServerUrl() + "wkfiles_tab_content.php", 'wkfiles', 1, 'small', null, null, function(activeNo) {
		var param = $.extend({}, TabBadgesDatasOfWKFiles[activeNo].parameter);
		param.get_summary = null;
		param.tab_type = activeNo;
		return param;
	}, null, {1: executeLoadDataFunc, 2: executeLoadDataFunc, 3: executeLoadDataFunc, 4: executeLoadDataFunc, /*5: executeLoadDataFunc, */6: executeLoadDataFunc});	
	
	//定义函数：计算已占用高度
	function calculateRootHeight() {
		var rootHeight = $('.wkfiles-tab').outerHeight(true)
			+($wkfilesTabWrap.outerHeight(true)-$wkfilesTabWrap.height()) //.wkfiles-tab-wrap的border+padding+margin高度
			;
		return rootHeight;
	}
	//计算并保存列表区域最大高度
	$wkfilesTabWrap = $('.wkfiles-tab-wrap');
	registerCalculateAdjustContainerHeight3($('#wkfiles-content-height-input'), $('#workbench-tab-content'), calculateRootHeight());
	
});
</script>
