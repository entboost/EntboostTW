<!-- 实出勤、异常考勤、考勤明细 -->
<table class="sidepage-table">
	<thead>
		<tr>
		<th class="ebtw-align-center col-xs-3">考勤时段</th>
		<th class="ebtw-align-center col-xs-1">签到时间</th>
		<th class="ebtw-align-center col-xs-1">签退时间</th>
		<th class="ebtw-align-center col-xs-1 col-xs-2p5">考勤状态</th>
		<th class="ebtw-align-center col-xs-4">考勤信息</th>
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
			if (!empty($recEntity['req_signin_time']))
				echo '<span style="color:red;">'.substr($recEntity['req_signin_time'], 11, 5).'</span>';
			else if (!empty($recEntity['signin_time']))
				echo substr($recEntity['signin_time'], 11, 5);
			else 
				echo '-';
		?></td>
		<td class="ebtw-align-center col-xs-1"><?php 
			if (!empty($recEntity['req_signout_time']))
				echo '<span style="color:red;">'.substr($recEntity['req_signout_time'], 11, 5).'</span>';
			else if (!empty($recEntity['signout_time']))
				echo substr($recEntity['signout_time'], 11, 5);
			else 
				echo '-';
		?></td>
		<td class="ebtw-align-left col-xs-1 col-xs-2p5"><?php 
			$recStateAry = getAttendRecStateAndRecIdFieldName($recEntity, $recEntity['att_rec_id']);
			$recState = intval($recStateAry[0]);
			$recStateDic = splitAttendRecState($recState);
			$rec_state_name = '';
			foreach ($recStateDic as $subDic) {
				if (!empty($rec_state_name))
					$rec_state_name .= ('、'.$subDic[1]);
				else 
					$rec_state_name = $subDic[1];
			}
			echo $rec_state_name;
		?></td>
		<td class="ebtw-align-left col-xs-4"><?php
			$outContent2 = '';
			
			if (($recState&ATTEND_STATE_LATE)==ATTEND_STATE_LATE && !empty($recEntity['standard_signin_time']) && !empty($recEntity['signin_time'])) {
				$diffMinutes = diffMinutesBetweenTwoTimes($recEntity['attend_date'].' '.$recEntity['standard_signin_time'], $recEntity['signin_time']);
				$outContent2 = '迟到'.($diffMinutes>=60?(intval($diffMinutes/60).'时'.($diffMinutes%60==0?'':(($diffMinutes%60).'分 '))):($diffMinutes.'分 '));
			}
			if (($recState&ATTEND_STATE_LEFT_EARLY)==ATTEND_STATE_LEFT_EARLY && !empty($recEntity['standard_signout_time']) && !empty($recEntity['signout_time'])) {
				$diffMinutes = diffMinutesBetweenTwoTimes($recEntity['attend_date'].' '.$recEntity['standard_signout_time'], $recEntity['signout_time']);
				$outContent2 .= '早退'.($diffMinutes>=60?(intval($diffMinutes/60).'时'.($diffMinutes%60==0?'':(($diffMinutes%60).'分 '))):($diffMinutes.'分 '));
			}
			
			if (strlen($outContent2)>0)
				$outContent = '';
			else 
				$outContent = '-';
			
			if (!empty($recEntity['attend_req'])) {
				$reqEntity = $recEntity['attend_req'];
				$reqType = intval($reqEntity['req_type']);
				switch ($reqType) {
					case 1: //补签
					case 2: //外勤
						$outContent = ($reqType==1)?'补签：':'外勤：';
						if (!empty($reqEntity['item_req_start_time']))
							$outContent .= substr($reqEntity['item_req_start_time'], 11, 5);
						else 
							$outContent .= '-';
						$outContent .= ' - ';
						if (!empty($reqEntity['item_req_stop_time']))
							$outContent .= substr($reqEntity['item_req_stop_time'], 11, 5);
						else
							$outContent .= '-';
						
						if (!empty($reqEntity['item_req_duration']))
							$outContent .= '  '.number_format(floatval($reqEntity['item_req_duration'])/60, 1).'小时';
						break;
					case 4: //加班
						$outContent = '加班：';
						if (!empty($reqEntity['start_time']))
							$outContent .= substr($reqEntity['start_time'], 0, 16);
						else
							$outContent .= '-';
						$outContent .= ' - ';
						if (!empty($reqEntity['stop_time']))
							$outContent .= substr($reqEntity['stop_time'], 11, 5);
						else
							$outContent .= '-';
						
						if (!empty($reqEntity['req_duration']))
							$outContent .= '  '.number_format(floatval($reqEntity['req_duration'])/60, 1).'小时';
						break;
					case 3: //请假
						$outContent = '请假：';
						if (!empty($reqEntity['start_time']))
							$outContent .= substr($reqEntity['start_time'], 0, 16);
						else
							$outContent .= '-';
						$outContent .= ' - ';
						if (!empty($reqEntity['stop_time'])) {
							if (substr($reqEntity['start_time'], 0, 10)===substr($reqEntity['stop_time'], 0, 10))
								$outContent .= substr($reqEntity['stop_time'], 11, 5);
							else 
								$outContent .= substr($reqEntity['stop_time'], 0, 16);
						} else
							$outContent .= '-';
							break;
						break;							
				}
			}
			
			if (strlen($outContent)>1 && strlen($outContent2)>0)
				$outContent2 .= '<br>';
			
			echo $outContent2.$outContent;
		?></td>
	</tr>
	<?php }?>
</table>
