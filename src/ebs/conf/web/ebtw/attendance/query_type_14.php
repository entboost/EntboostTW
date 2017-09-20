<div style="padding-left: 15px;">
	测试功能<br>
	<br>
	<br>
	强制：
	<select id="force">
		<option value="0" selected>否</option>
		<option value="1">是</option>
	</select>
	<br>
	<br>
	日期：<input type="text" value="2017-08-25" id="attend_date">
	<br>
	<br>
	验证key：<input type="text" value="" id="request_id">
	<br>
	<br>	
	<input type="button" value="作业测试" id="taskJobTest">
</div>
<script type="text/javascript">
	$('#taskJobTest').click(function() {
		var attendDate = $('#attend_date').val();
		var force = $('#force').val();
		var requestId = $('#request_id').val();
		callAjax(getServerUrl() + 'attendance/task_job.php', {task_job_type: 1, request_id:requestId, attend_date:attendDate, task_job_force:force, ent_codes:'1000000000000030'/*, return_seconds:5, task_job_force:'1'*/
			/*, ent_codes:'1000000000000030, 6127729100153865', attend_date:'2017-06-12'*/}, null, function(result) {
			logjs_info(result);
			if (result.code==0) {
				alert('执行成功');
			} else {
				alert('执行失败：' + result.msg);
			}
		}, function(XMLHttpRequest, textStatus, errorThrown) {
			alert(textStatus);
		});	
	});
</script>
<?php
