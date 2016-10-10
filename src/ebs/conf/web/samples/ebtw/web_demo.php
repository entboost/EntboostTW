<!DOCTYPE html>
<html>
<head>
<title>恩布协同办公界面设计预览</title>
<?php
	$ECHO_MODE='html';
	require_once dirname(__FILE__).'/include.php';
	$relative_path = '';
	require_once dirname(__FILE__).'/html_head_include.php';
	require_once dirname(__FILE__).'/rootpath.php';
?>
<style type="text/css">
.demo-top {
	padding: 30px 0;
	height: 100px;
	text-align: center;
}
.demo-a {
	width: 150px;
	font-size: 20px;
	position: relative;
	left: 50%;
	margin-left: -75px;
}
.demo-a>a{
	display: block;

}
</style>
</head>
<body>
<div class="container">
	<div class="row">
		<div class="col-xs-12 demo-top"><h2>恩布协同办公界面设计预览</h2></div>
	</div>
	<div class="row">
		<div class="col-xs-12">
		<div class="demo-a">
			<a href="<?php echo $ROOT_URL;?>/workbench_i.php?workbench_mode=board" target="_blank">看板</a>
			<a href="<?php echo $ROOT_URL;?>/plan/plan_i.php?view_mode=list" target="_blank">计划</a>
			<a href="<?php echo $ROOT_URL;?>/task/task_i.php?view_mode=list" target="_blank">任务</a>
			<a href="<?php echo $ROOT_URL;?>/report/daily_i.php?view_mode=list" target="_blank">日报</a>
			<a href="<?php echo $ROOT_URL;?>/report/report_i.php?view_mode=list" target="_blank">报告</a>
		</div>
		</div>
	</div>
</div>
</body>
</html>