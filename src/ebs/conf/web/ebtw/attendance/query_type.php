<?php
//我的申请、考勤审批、考勤异常、工作时长、考勤汇总、考勤报表
?>
<div class="ptr-container mCustomScrollbar" data-mcs-theme="minimal-dark"><!-- dark-3 -->
	<div id="gridList" class="col-xs-12 dt-grid-container ebtw-right-gutter-no">
	</div>
</div>
<div id="gridToolBar" class="col-xs-12 dt-grid-toolbar-container"></div>

<script type="text/javascript">
//执行加载数据和渲染内容视图
function executeRenderContent() {
	loadDtGrid(createQueryParameter());
}

</script>