<!-- 加班 -->
<table class="sidepage-table">
	<thead>
		<tr>
		<th class="ebtw-align-center col-xs-2">加班开始</th>
		<th class="ebtw-align-center col-xs-2">加班结束</th>
		<th class="ebtw-align-center col-xs-1">加班时长</th>
		<th class="ebtw-align-center col-xs-4">加班内容</th>
		<th class="ebtw-align-center col-xs-2">审批状态</th>
		</tr>
	</thead>
	<?php foreach($recResults as $recEntity) {?>
	<tr>
		<td class="ebtw-align-center col-xs-2"><?php 
			if (!empty($recEntity['attend_req'])) {
				if (!empty($recEntity['attend_req']['start_time']))
					echo substr($recEntity['attend_req']['start_time'], 0, 16);
			}
		?></td>
		<td class="ebtw-align-center col-xs-2"><?php 
			if (!empty($recEntity['attend_req'])) {
				if (!empty($recEntity['attend_req']['stop_time']))
					echo substr($recEntity['attend_req']['stop_time'], 0, 16);
			}
		?></td>
		<td class="ebtw-align-center col-xs-1"><?php
			if (!empty($recEntity['attend_req'])) {
				$duration = intval($recEntity['attend_req']['req_duration']);
				//格式化显示
				$hour = intval($duration/60);
				$minute = $duration%60;
				echo ($hour>9?'':'0').$hour.':'.($minute>9?'':'0').$minute;
			} else {
				echo '00:00';	
			}
		?></td>
		<td class="ebtw-align-left col-xs-4"><?php
			if (!empty($recEntity['attend_req'])) {
				echo $recEntity['attend_req']['req_content'];
			}
		?></td>
		<td class="ebtw-align-center col-xs-2"><?php 
			echo '审批通过';
		?></td>		
	</tr>
	<?php }?>
</table>
