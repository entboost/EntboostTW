<!-- 外勤 -->
<table class="sidepage-table">
	<thead>
		<tr>
		<th class="ebtw-align-center col-xs-3">考勤时段</th>
		<th class="ebtw-align-center col-xs-1">外勤开始</th>
		<th class="ebtw-align-center col-xs-1">外勤结束</th>
		<th class="ebtw-align-center col-xs-1">外勤时长</th>
		<th class="ebtw-align-center col-xs-4">外勤备注</th>
		</tr>
	</thead>
	<?php foreach($recResults as $recEntity) {?>
	<tr>
		<td class="ebtw-align-center col-xs-3"><?php 
			if (!empty($recEntity['standard_signin_time']) && !empty($recEntity['standard_signout_time']))
				echo $recEntity['attend_date'].' '.substr($recEntity['standard_signin_time'], 0, 5).'-'.substr($recEntity['standard_signout_time'], 0, 5);
			else {
				echo $recEntity['attend_date'].' '.$WEEK_NAMES[date('w', strtotime($recEntity['attend_date']))];
			}
		?></td>
		<td class="ebtw-align-center col-xs-1"><?php 
			if (!empty($recEntity['attend_req'])) {
				if (!empty($recEntity['attend_req']['item_req_start_time']))
					echo substr($recEntity['attend_req']['item_req_start_time'], 11, 5);
			}
		?></td>
		<td class="ebtw-align-center col-xs-1"><?php 
			if (!empty($recEntity['attend_req'])) {
				if (!empty($recEntity['attend_req']['item_req_stop_time']))
					echo substr($recEntity['attend_req']['item_req_stop_time'], 11, 5);
			}
		?></td>
		<td class="ebtw-align-center col-xs-1"><?php
			if (!empty($recEntity['attend_req'])) {
				$duration = intval($recEntity['attend_req']['item_req_duration']);
				//格式化显示
				$hour = intval($duration/60);
				$minute = $duration%60;
				echo ($hour>9?'':'0').$hour.':'.($minute>9?'':'0').$minute;
			}
		?></td>
		<td class="ebtw-align-left col-xs-4"><?php
			if (!empty($recEntity['attend_req'])) {
				echo $recEntity['attend_req']['req_content'];
			}
		?></td>
	</tr>
	<?php }?>
</table>
