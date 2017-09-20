<?php
set_time_limit(3600); //设置最大超时时间

include dirname(__FILE__).'/../attendance/preferences.php';
require_once dirname(__FILE__).'/../attendance/include_nosession.php';
require_once dirname(__FILE__).'/../attendance/attendance_functions.php';
require_once dirname(__FILE__).'/../tempdata/tempdata.php';

	$output = true;
//待定：权限补充

//定义作业类型
/**
 * 考勤日结作业
 * @var int
 */
define("TASK_JOB_TYPE_DAILY_ATTENDANCE", 1);
	
	log_info(json_encode($_REQUEST));
	//{"task_job_type":"1","id":"2017051812160400236","key":"883451967d0b23d377b3248129dc6596","return_seconds":"0"}
	
	$Task_Job_Type_Name = 'task_job_type'; //作业类型字段名
	$Task_Job_Force_Name = 'task_job_force'; //是否强制执行
	$Task_Job_ReturnSeconds_Name = 'return_seconds'; //作业参考运行时间长度(秒)；超过时间长度的作业将以"待续(EB_STATE_CONTINUE)"的状态结束
	$Task_Job_Request_Id_Name = 'request_id'; //请求ID，用于安全验证
	
	//验证request_id
	$requestId = get_request_param($Task_Job_Request_Id_Name);
	if (empty($requestId)) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($Task_Job_Request_Id_Name, $output);
		return;
	}
	$tempData = get_tempdata($requestId);
	if ($tempData===false || count($tempData)==0) {
		$errMsg = "valid $Task_Job_Request_Id_Name error";
		ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
		return;
	}
	delete_tempdata($requestId);
	
	//检查作业类型
	$taskJobType = get_request_param($Task_Job_Type_Name);
	if (!isset($taskJobType) || !in_array($taskJobType, array(TASK_JOB_TYPE_DAILY_ATTENDANCE))) {
		ResultHandle::fieldValidNotMatchedErrToJsonAndOutput($Task_Job_Type_Name, $output);
		return;
	}
	
	//作业参考运行时间
	$returnSeconds = get_request_param($Task_Job_ReturnSeconds_Name, '0');
	$returnSeconds = intval($returnSeconds);
	
	//当前时间戳
	$now = time();
	
	log_info("start task job type $taskJobType, returnSeconds $returnSeconds");
	
	//各种日结作业
	if ($taskJobType==TASK_JOB_TYPE_DAILY_ATTENDANCE) { //考勤日结作业
		$Attend_Date_Name = 'attend_date'; //考勤日结作业日期字段名
		$Entcode_Name = 'ent_codes'; //企业编码的字段名，多个用逗号分隔
		
		//作业日期
		$attendDate = get_request_param($Attend_Date_Name);
		if (isset($attendDate)) { //传入的日期，检查合法性
			if (!validateDateString($attendDate)) {
				ResultHandle::fieldValidNotMatchedErrToJsonAndOutput("$Attend_Date_Name $attendDate", $output);
				return;
			}
		}
		
		$isCalculatedDate = false; //标记考勤日结日期是否由计算而来
		$attendDates = array();
		if (!isset($attendDate)) { //不带作业日期参数
			$isCalculatedDate = true;
 			$sysinfoResult = APService::get_instance()->getSysinfo(true, true);
 			if ($sysinfoResult===false) {
 				$errMsg = 'getSysinfo error';
 				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
 				return;
 			}
 			
 			//读取考勤日结作业最后一次运行日期
			$startDate = $sysinfoResult['attend-daily-job-date'];
			if (strlen($startDate)==0) {
				$startDate = $sysinfoResult['attend-start-date'];
				if (strlen($startDate)==0) {
					$startDate = '2017-05-10';
				}
			}
			
			$endDate = date('Y-m-d', strtotime("-1 day")); //昨天日期
			$attendDates = calculateDates($startDate, $endDate);
			log_info("$Attend_Date_Name is not in parameters, use [$startDate] - [$endDate]");
		} else {
			array_push($attendDates, $attendDate);
		}
		log_debug('attendDates:'.json_encode($attendDates));
		if (count($attendDates)==0) {
			$errMsg = "no attendDate";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		$entCodes = get_request_param($Entcode_Name);
		$entCodesFromDB = false; //标记企业编号是否从数据库读取而来
		
		//获取日结作业的企业编号
		if (isset($entCodes)) {
			//检查输入值是否合法
			if (!EBModelBase::checkDigits($entCodes, $outErrMsg)) {
				ResultHandle::fieldValidNotDigitErrToJsonAndOutput("$Task_Job_Entcode_Name '$entCodes'", $output);
				return;
			}
			
			$entCodes = preg_split('/,/', $entCodes);
			log_info("entCodes in param ".json_encode($entCodes));
		} else {
			$entCodesFromDB = true;
		}
		
		if (empty($entCodes) && !$entCodesFromDB) {
			$errMsg = "no entcode";
			ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
			return;
		}
		
		$taskJobForce = get_request_param($Task_Job_Force_Name, '0');
		$taskJobForce = ($taskJobForce=='1')?true:false;
		//$taskJobForce = true; //for test
		if ($taskJobForce==true) {
			log_info("force execute, clear records for attendDate ".json_encode($attendDates).", entCodes ".(!empty($entCodes)?json_encode($entCodes):''));
			$daiInstance = AttendDailyService::get_instance();
			
			if ($entCodesFromDB)
				$result = $daiInstance->invalidRecords($attendDates, null, true);
			else
				$result = $daiInstance->invalidRecords($attendDates, $entCodes);
			
			if ($result===false) {
				$errMsg = "invalidRecords error";
				ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
				return;
			}
		}
		
		$result = '';
		while (count($attendDates)>0) {
			$attendDate = $attendDates[0]; //取第一个元素
			array_splice($attendDates, 0 ,1); //去掉第一个元素
			
			//按指定考勤日期运行日结作业任务
			$result = executeDailyAttendanceTaskJob($output, $taskJobType, $now, $Attend_Date_Name, $attendDate, $isCalculatedDate, $Entcode_Name, $entCodes, $entCodesFromDB, $returnSeconds, $taskJobForce);
			if ($result===EBStateCode::$EB_STATE_CONTINUE || $result===false) {
				return;
			}
			
			if (!$isCalculatedDate) {
				break;
			}
			
			//写入作业最后运行日期
			//企业编号从数据库读取，表示作业包含全部企业
			if ($entCodesFromDB) {
				$setResult = APService::get_instance()->setSysinfo($attendDate);
				if ($setResult===false) {
					$errMsg = "setSysinfo attendDailyJobDate error";
					ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return;
				}
			}
		}
		echo $result;
	} else {
		$errMsg = "not found task job type $taskJobType";
		ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
	}
	