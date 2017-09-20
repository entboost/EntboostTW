<!-- 请假 -->
<table class="sidepage-table">
	<thead>
		<tr>
		<th class="ebtw-align-center col-xs-2">请假开始</th>
		<th class="ebtw-align-center col-xs-2">请假结束</th>
		<th class="ebtw-align-center col-xs-1">请假类型</th>
		<th class="ebtw-align-center col-xs-4">请假备注</th>
		<th class="ebtw-align-center col-xs-2">审批状态</th>
		</tr>
	</thead>
	<?php foreach($reqResults as $reqEntity) {?>
	<tr>
		<td class="ebtw-align-center col-xs-2"><?php 
			if (!empty($reqEntity['start_time']))
				echo substr($reqEntity['start_time'], 0, 16);
		?></td>
		<td class="ebtw-align-center col-xs-2"><?php 
			if (!empty($reqEntity['stop_time']))
				echo substr($reqEntity['stop_time'], 0, 16);
		?></td>
		<td class="ebtw-align-center col-xs-1"><?php 
			if (!empty($reqEntity['req_name']))
				echo $reqEntity['req_name'];
		?></td>		
		<td class="ebtw-align-left col-xs-4"><?php
			echo $reqEntity['req_content'];
		?></td>
		<td class="ebtw-align-center col-xs-2"><?php 
			echo '审批'.$ATTENDANCE_REQ_STATE_ARRY[$reqEntity['req_status']];
		?></td>
	</tr>
	<?php }?>
</table>
