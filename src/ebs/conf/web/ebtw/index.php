<?php
$relative_path = '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="content-type" content="text/html" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title>恩布协同办公</title>
<script type="text/javascript" src="<?php if (isset($relative_path)) echo $relative_path; ?>dependents/jquery/jquery-1.9.1.min.js?v=1"></script>

<style type="text/css">
.clear {
	clear: both;
}
.title {
	text-align: center;
	padding: 30px 0;
	font-size: 28px;
}
.module-list {
	/*border: 1px solid #f00;*/
	text-align: center;
}
.module-list>div {
	display: inline-block;
	border: 1px solid #aaa;
}
.module-list .part1,
.module-list .part2 {
	display: inline-block;
	float: left;
	height: 110px;
}
.module-list .part1 {
	width: 130px;
	line-height: 110px;
	border: 1px solid #eee;
	background-color: #00a2e8;
	color: white;
	font-size: 22px;
	cursor: pointer;
	padding: 15px;
}
.module-list .part1:hover {
	color: #fef2a6;
}
.module-list .part2 {
	position: relative;
	width: 650px;
	text-align: left;
	padding: 10px 50px 15px 10px;
}
.module-list .part2>div {
	padding: 3px 0;
	line-height: 22px;
}
.clk-link {
	position: absolute;
	top : 8px;
	right: 15px;
	text-decoration: none;
	color: #00a2e8;
}
.clk-link:hover {
	text-decoration: underline;
}
.text-warning {
	color: #FF6347;
}
</style>
</head>
<body>
	<div class="title">恩布协同办公</div>
	
	<div class="module-list">
		<div>
			<div class="part1 open-subid" style="height: 90px; line-height: 90px;" data-subid="1002300110">工作台</div>
			<div class="part2" style="height: 90px;">
				<div><span class="text-warning">计划要做：</span>未完成计划、未读计划、重要程度，快速新建、标识完成、计划转任务。</div>
				<div><span class="text-warning">待办事项：</span>需要评审、评阅和审批待办事项，支持评审、评阅和审核，快捷处理待办。</div>
				<div><span class="text-warning">未完成任务：</span>实时了解个人和团队未完成任务、任务负责人、任务进度，重要程度，支持快速新建任务，标识关注任务，拆分子任务。</div>
				<a class="clk-link" href="eb-open-subid://1002300110,1">进入</a>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	
	<div class="module-list">
		<div>
			<div class="part1 open-subid" style="height: 70px; line-height: 70px;" data-subid="1002300115">考勤</div>
			<div class="part2" style="height: 70px;">
				<div><span class="text-warning">考勤与审批：</span>员工签到、签退，查看考勤情况及申请考勤审批。</div>
				<div><span class="text-warning">考勤统计：</span>通过报表查看员工工作时长、迟到早退、加班请假等情况，了解每天考勤明细。</div>
				<div><span class="text-warning">考勤设置：</span>功能强大的考勤规则设置，支持复杂的多重规则；灵活的假期设置。</div>
				<a class="clk-link" href="eb-open-subid://1002300115,1">进入</a>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	
	<div class="module-list">
		<div>
			<div class="part1 open-subid" style="height: 90px; line-height: 90px;" data-subid="1002300104">我的邮件</div>
			<div class="part2" style="height: 90px;">
				<div><span class="text-warning">邮件感知：</span>收到新邮件，客户端会实时收到新邮件通知提醒，再也不用担心错过重要邮件。</div>
				<div><span class="text-warning">鼠标拖拉上传邮件附件：</span>撰写邮件、回复邮件、邮件附件，从未有过的邮件体验。</div>
				<div><span class="text-warning">恩布工作台-文件-邮件附件：</span>轻松管理所有邮件附件，解决邮件附件太多、并且分布在不同邮件的问题。</div>
				<a class="clk-link" href="eb-open-subid://1002300104,1">进入</a>
			</div>
			<div class="clear"></div>
		</div>
	</div>	
	
	<div class="module-list">
		<div>
			<div class="part1 open-subid" data-subid="1002300111">计划</div>
			<div class="part2">
				<div><span class="text-warning">个人工作计划，计划转任务：</span>合理计划个人工作，添加工作事项，设置不同重要程度。</div>
				<div><span class="text-warning">计划附件、共享计划、评论与交流：</span>添加附件，把计划共享给其他同事，相关同事给计划添加评论/回复。</div>
				<div><span class="text-warning">计划评审、下级计划、团队计划：</span>申请评审，审批人实时收到需要评审消息提醒， 能查看下级计划，了解工作情况。</div>
				<a class="clk-link" href="eb-open-subid://1002300111,1">进入</a>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	
	<div class="module-list">
		<div>
			<div class="part1 open-subid" style="height: 90px; line-height: 90px;" data-subid="1002300112">任务</div>
			<div class="part2" style="height: 90px;">
				<div><span class="text-warning">新建工作任务：</span>设置截止时间，过期自动报警提醒；给同事或下级分配安排工作任务。</div>
				<div><span class="text-warning">任务进度、上报工时：</span>上报任务进度、工时，填写工作内容，实时汇报任务情况。</div>
				<div><span class="text-warning">关注任务、下级任务、团队任务：</span>关注指定重要任务，能查看下级任务，了解任务执行情况，合理安排工作。</div>
				<a class="clk-link" href="eb-open-subid://1002300112,1">进入</a>
			</div>
			<div class="clear"></div>
		</div>
	</div>

	<div class="module-list">
		<div>
			<div class="part1 open-subid" data-subid="1002300113">日报</div>
			<div class="part2">
				<div><span class="text-warning">工作总结，积累工作经验：</span>通过工作日报，总结下今天已经完成的工作，备注未完成工作。</div>
				<div><span class="text-warning">日报自动汇报：</span>日报工作内容，不需要手工填写，自动汇报功能，查看日报时会自动列出当天在系统中处理过的计划和任务。</div>
				<div><span class="text-warning">申请评阅日报，查看下级日报：</span>下级可以申请提交评阅日报给上级；部门经理、项目经理可以查看下级日报填写情况，包括未填写日报、逾期填写等。</div>
				<a class="clk-link" href="eb-open-subid://1002300113,1">进入</a>
			</div>
			<div class="clear"></div>
		</div>
	</div>
</body>
<script type="text/javascript">
$(document).ready(function() {
	$(document).on('click', '.open-subid', function(e) {
		var subid = $(this).attr('data-subid');
		if (subid!=undefined) {
			$('body').find('.open-subid-link').remove();
			var $linkElement =$('body').append('<a class="open-subid-link" style="display:none;" href="eb-open-subid://'+subid+',1">打开集成应用</a>').find('.open-subid-link');
			$linkElement[0].click(); //模拟点击
			$linkElement.remove();
		}
	});
});
</script>
</html>