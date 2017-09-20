
/**
 * 执行签到/签退
 * @param actionType 1=签到，2=签退
 * @param successHandle 成功回调函数
 * @param errorHandle 失败回调函数
 */
function attendSign(actionType, successHandle, errorHandle) {
	callAjax(getServerUrl() + 'attendance/attendance_action.php', {action_type: actionType}, null, function(result) {
		//var result = didLoadedDataPreprocess('original', data, true);
		//result = $.parseJSON(data);
		if (result.code!=1 && result.code!=2) { //code=1 错误，code=2没有权限
			if (successHandle)
				successHandle(actionType, result.code);
		} else if (errorHandle) {
			errorHandle(actionType, 'business');
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle(actionType, 'system', textStatus);
		}
	});
}

/**
 * 查询当前时间可执行"签到"还是"签退"
 * @param successHandle 成功回调函数
 * @param errorHandle 失败回调函数
 */
function getAttendSignType(successHandle, errorHandle) {
	callAjax(getServerUrl() + 'attendance/attendance_action.php', {action_type: 0}, null, function(result) {
		if (result.code==0) {
			if (successHandle)
				successHandle(result.signActionType);
		} else if (errorHandle) {
			errorHandle('business', result.code);
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle('system', textStatus);
		}
	});	
}

/**
 * 检测当前时间允许"签到"还是"签退"
 */
function checkAttendSignType() {
	getAttendSignType(function(actionType) {
		var Type_SignIn = 1;
		var Type_SignOut = 2;
	
		var changeSignType = function(signType, hide) {
			var $element = $('.attendSign[data-sign-type="' + signType + '"]');
			if (hide)
				$element.removeClass('ebtw-hide');
			else 
				$element.addClass('ebtw-hide');
		};
		
		//显示"签到/签退"按钮
		if (actionType==Type_SignIn) {
			changeSignType(Type_SignIn, true);
			changeSignType(Type_SignOut, false);
		} else {
			changeSignType(Type_SignOut, true);
			changeSignType(Type_SignIn, false);			
		}
	}, function(errorType, error) {
		if (console)
			console.log('error:' + error);
	});
}

/**
 * 提交签到或签退
 */
function submitAttendSign() {
	//防止密集点击
	$('.attendSign').unbind('click', submitAttendSign); //暂时解绑点击事件
	setTimeout(function() {
		$('.attendSign').bind('click', submitAttendSign); //3秒后再次绑定点击事件
	}, 3000);
	
	attendSign($(this).attr('data-sign-type'), function(actionType, code) {
		if (code==0)
			layer.msg((actionType==1?'签到':'签退') + '成功');
		else if (code==10001) {
			layer.msg('重复签到');
		} else if (code==10002) {
			layer.msg('此刻不允许签到', {icon:5});
		} else if (code==10003) {
			layer.msg('此刻不允许签退', {icon:5});
		}

		//检测当前时间允许"签到"还是"签退"
		checkAttendSignType();
	}, function(actionType, errorType) {
		layer.msg((actionType==1?'签到':'签退') + '失败', {icon:2});
	});	
}

/**
 * 创建时钟显示
 * @param selector 时钟位置JQuery选择器
 */
function clockWork(selector) {
	var $element = $(selector);
	var $eTime = $element.find('.sTime');
	var $eDate = $element.find('.sDate');
	setInterval(function() {
		var strArry = popularDateTime2(new Date());
		$eDate.html(strArry[0]);
		$eTime.html(strArry[1]);
	}, 500);	
}

/**
 * 执行考勤审批操作
 * @param actionType 4=审批通过，5=审批拒绝，6=审批回退(撤销)
 * @param {function} [可选] successHandle 成功回调函数
 * @param {function} [可选] errorHandle 失败回调函数
 */
function attendReqAction(actionType, reqId, successHandle, errorHandle) {
	callAjax(getServerUrl() + 'attendance/attendance_req.php', {action_type:10+actionType, req_id:reqId}, null, function(result) {
		if (result.code==0) {
			if (successHandle)
				successHandle(result.results);
		} else if (errorHandle) {
			errorHandle('business', result.code);
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle('system', textStatus);
		}
	});
}

/**
 * 执行考勤配置操作
 * @param {int} actionType 21=启用或禁止，22=删除
 * @param {object} otherParameter 其它提交参数
 * @param {function} [可选] successHandle 成功回调函数
 * @param {function} [可选] errorHandle 失败回调函数
 */
function attendSettingAction(actionType, otherParameter, successHandle, errorHandle) {
	var parameter = {action_type:actionType};
	if (otherParameter)
		parameter = $.extend(parameter, otherParameter);
	
	callAjax(getServerUrl() + 'attendance/attendance_setting.php', parameter, null, function(result) {
		if (result.code==0) {
			if (successHandle)
				successHandle(result.code);
		} else if (errorHandle) {
			errorHandle('business', result.code);
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle('system', textStatus);
		}
	});	
}

/**
 * 执行考勤专员操作
 * @param {int} actionType 31=启用或禁止，32=删除，33=保存(包括新增和更新)
 * @param {object} otherParameter 其它提交参数
 * @param {function} [可选] successHandle 成功回调函数
 * @param {function} [可选] errorHandle 失败回调函数
 */
function attendUserDefineAction(actionType, otherParameter, successHandle, errorHandle) {
	var parameter = {action_type:actionType};
	if (otherParameter)
		parameter = $.extend(parameter, otherParameter);
	
	callAjax(getServerUrl() + 'attendance/attendance_user_define.php', parameter, null, function(result) {
		if (result.code==0) {
			if (successHandle)
				successHandle(result.code, result);
		} else if (errorHandle) {
			errorHandle('business', result.code);
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle('system', textStatus);
		}
	});	
}

/**
 * 执行请假类型配置操作
 * @param {int} actionType 41=启用或禁止，42=删除，43=保存(包括新增和更新)
 * @param {object} otherParameter 其它提交参数
 * @param {function} [可选] successHandle 成功回调函数
 * @param {function} [可选] errorHandle 失败回调函数
 */
function attendLeaveTypeAction(actionType, otherParameter, successHandle, errorHandle) {
	var parameter = {action_type:actionType};
	if (otherParameter)
		parameter = $.extend(parameter, otherParameter);
	
	callAjax(getServerUrl() + 'attendance/attendance_leave_type.php', parameter, null, function(result) {
		if (result.code==0) {
			if (successHandle)
				successHandle(result.code, result);
		} else if (errorHandle) {
			errorHandle('business', result.code);
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle('system', textStatus);
		}
	});
}

/**
 * 执行假期配置操作
 * @param {int} actionType 51=启用或禁止，52=删除
 * @param {object} otherParameter 其它提交参数
 * @param {function} [可选] successHandle 成功回调函数
 * @param {function} [可选] errorHandle 失败回调函数
 */
function holidaySettingAction(actionType, otherParameter, successHandle, errorHandle) {
	var parameter = {action_type:actionType};
	if (otherParameter)
		parameter = $.extend(parameter, otherParameter);
	
	callAjax(getServerUrl() + 'attendance/attendance_holiday.php', parameter, null, function(result) {
		if (result.code==0) {
			if (successHandle)
				successHandle(result.code, result);
		} else if (errorHandle) {
			errorHandle('business', result.code);
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle('system', textStatus);
		}
	});	
}

/**
 * 获取考勤时间段
 * @param {string} attendDate 考勤日期
 * @param {int|string} reqId 审批申请编号，填0或null表示忽略本条件
 * @param {boolean} forReqUser 是否取申请审批用户的；当true时，reqId必须有效
 * @param {function} [可选] successHandle 成功回调函数
 * @param {function} [可选] errorHandle 失败回调函数
 */
function getRuleTimes(attendDate, reqId, forReqUser, successHandle, errorHandle) {
	callAjax(getServerUrl() + 'attendance/attendance_req.php', {action_type:forReqUser?9:10, attend_date:attendDate, req_id:reqId}, null, function(result) {
		if (result.code==0) {
			if (successHandle)
				successHandle(result.results);
		} else if (errorHandle) {
			errorHandle('business', result.code);
		}
	}, function(XMLHttpRequest, textStatus, errorThrown) {
		if (errorHandle) {
			errorHandle('system', textStatus);
		}
	});
}

/**
 * 填充请假类型select控件，并选中某项
 * @param {object} selectOptions select控件选项
 * @param {array} dicts 字典列表
 * @param {string} parentSelector 父元素选择器
 * @param {string} [可选] selectedDictId 待选中的编号
 * @param {string} [可选] selectedDictName 带选中的名称，如果selectedDictId填null，则使用本字段作为选中依据
 */
function fillFurloughTypeSelect(selectOptions, dicts, parentSelector, selectedDictId, selectedDictName) {
	var $element = $(parentSelector).find('select');
	//删除旧选项
	$element.children('option[value!="0"]').remove();
	
	if (typeof dicts !== 'undefined' && Object.prototype.toString.call(dicts) === '[object Array]') {
		for (var i=0; i<dicts.length; i++) {
			var dict = dicts[i];
			var content = '<option value="'+dict.dict_id+'">'+dict.dict_name+'</option>';
			$element.append(content);
		}
		
		//选中某选项
		if (selectedDictId!=null) {
			$element.find('option[value="'+selectedDictId+'"]').attr('selected', 'selected');
			$element.select2(selectOptions);
		} else if (selectedDictName!=null) {
			$element.find('option[value!="0"]').each(function() {
				if ($(this).html()==selectedDictName) {
					$(this).attr('selected', 'selected');
					$element.select2(selectOptions);
					return false;
				}
			});
		}
	}
}

/**
 * 依据考勤记录状态得出'需补充的签到签退情况'
 * @param {int} recState 考勤记录状态
 * @returns {int} 1=补充签到，2=补充签退，3=两者都补充
 */
function compensatedTimeTypeOfAttendRecState(recState) {
//	0x2=未签到
//	0x4=未签退
//	0x8=旷工
//	0x10=迟到
//	0x20=早退
//  0x1000=审批通过标识
	
	var result = 0;
	if ((recState & 0x8)==0x8) {
		result += 3;
	} else {
		if ((recState & 0x2)==0x2 || (recState & 0x10)==0x10) {
			result += 1;
		}
		if ((recState & 0x4)==0x4 || (recState & 0x20)==0x20) {
			result += 2;
		}
	}
	return result;
}

/**
 * 验证签到时间和签退时间的合法性
 * @param {int} compensatedTimeType 需补充的签到签退情况：1=补充签到，2=补充签退，3=两者都补充
 * @param {string} startTime 签到时间
 * @param {string} endTime 签退时间
 * @param {string} standardStartTime [可选] 标准签到时间
 * @param {string} standardEndTime [可选] 标准签退时间
 * @param {boolean|string} 验证结果：true=验证通过；string=验证不通过的原因描述
 */
function validateBetweenSigninoutTime(compensatedTimeType, startTime, endTime, standardStartTime, standardEndTime) {
	//判断空值
	if ((compensatedTimeType&1)==1 && (startTime==null || startTime.length==0))
		return '必须填入签到时间';
	if ((compensatedTimeType&2)==2 && (endTime==null || endTime.length==0))
		return '必须填入签退时间';
	
	//验证时段先后合法性
	if (compensatedTimeType==3 && new Date(startTime).getTime() > new Date(endTime).getTime()) {
		return '签到时间 必须早于 签退时间';
	}
	//限制最晚签到时间
	var yesterday = $.D_ALG.formatDate(calculateDate(new Date(), -1), 'yyyy-mm-dd');
	if ((compensatedTimeType&1)==1 && yesterday<startTime.substr(0, 10)) {
		return '签到时间超出限制范围';
	}
	//限制最晚签退时间
	if ((compensatedTimeType&2)==2 && yesterday<endTime.substr(0, 10)) {
		return '签退时间超出限制范围';
	}
	//检查时间跨度
	if (compensatedTimeType==3 && startTime.substr(0, 10)!=endTime.substr(0, 10)) {
		return '签到时间和签退时间必须在同一天';
	}
	//限制签到时间
	if ((compensatedTimeType&1)==1 && standardEndTime!=null && new Date(startTime).getTime() > new Date(standardEndTime).getTime()) {
		return '签到时间 不能晚于 标准签退时间[' + standardEndTime.substr(11,5) +']';
	}
	//限制签退时间
	if ((compensatedTimeType&2)==2 && standardStartTime!=null && new Date(endTime).getTime() < new Date(standardStartTime).getTime()) {
		return '签退时间 不能早于 标准签到时间[' + standardStartTime.substr(11,5) +']';
	}
	
	return true;
}

/**
 * 获取签到时间、签退时间
 * @param {object} $cklElement 带'.checkbox-list-item'标记的元素JQuery对象
 * @returns {Array} [0]=签到时间，[1]=签退时间，[2]=休息时长(分钟)
 */
function getSigninoutTimesInElement($cklElement) {
	var $ckboxElement = $cklElement.find('input[type="checkbox"]');
	var compensatedTimeType = parseInt($ckboxElement.attr('data-compensated-time-type'));
	var restDuration = parseInt($ckboxElement.attr('data-standard-rest-duration'));
	
	//定义函数
	var handleFunc = function(signType) {
		var $e = $cklElement.find('input[type="text"][sign-type="' + signType + '"]');
		var newSignTime = $e.val();
		if (newSignTime && newSignTime.length>0) {
			//08:22 2017-06-12
			newSignTime = newSignTime.substr(6, 10) + ' ' + newSignTime.substr(0, 5) + ':00';
			return newSignTime;
		}
	};
	
	//获取签到时间、签退时间
	var newSigninTime = handleFunc('in');
	var newSignoutTime = handleFunc('out');
	if(!newSigninTime && (compensatedTimeType&2)==2)
		newSigninTime = $ckboxElement.attr('data-real-signin-time');
	if(!newSignoutTime && (compensatedTimeType&1)==1)
		newSignoutTime = $ckboxElement.attr('data-real-signout-time');
	
	return [newSigninTime, newSignoutTime, restDuration];
}

/**
 * 初始化考勤时段时间选择器
 * @param {string} virtualDate 参考日期
 */
function createAttendTimeDatetimePicker(virtualDate) {
	var datetimeFormat = 'hh:ii yyyy-mm-dd';
	var startView = 1;
	var minView = 0;
	var maxView = 1;
	var minuteStep = 2;
	var initialDate = '00:00 '+virtualDate;
	var startDate = '00:00 '+virtualDate;
	var endDate = '23:59 '+virtualDate;
   	createDefaultDatetimePicker('.attend-setting-time .time-item input[name^="signin_time_"], .attend-setting-time .time-item input[name^="signout_time_"]'
   		   	, datetimeFormat, startView, minView, maxView, minuteStep, null, null, initialDate, startDate, endDate);	
}

/**
 * 创建一个空白的考勤时段对象
 * @param {int} rulIndex rule顺序号
 * @param {int} timIndex time顺序号
 * @returns {object} 考勤时段对象
 */
function createBlankAttendSettingTimeObject(rulIndex, timIndex) {
	var timObj = {newTime:true, rul_index:rulIndex, tim_index:timIndex, att_rul_id:'0'/*'r'+rulIndex*/, att_tim_id:'0'/*'t'+timIndex*/, signin_time:'09:00:00', signout_time: '18:00:00'
		, signin_ignore:'0', signout_ignore:'0', rest_duration:'120', work_duration:'420'};
	return timObj;
}

/**
 * 创建一个空白的考勤规则对象
 * @param {int} rulIndex 顺序号
 * @param {string} aSetId 考勤设置编号
 * @parma {boolean} initWeekWorkDay 是否初始化周工作日
 * @param [array] timeObjArray 考勤时段对象列表
 * @returns {object} 考勤规则对象
 */
function createBlankAttendSettingRuleObject(rulIndex, aSetId, initWeekWorkDay, timeObjArray) {
	var ruleObj = {newRule:true, rul_index:rulIndex, att_set_id:aSetId, att_rul_id:'0'/*'r'+rulIndex*/, work_day:initWeekWorkDay?'31':'0', total_work_duration:'0', times:timeObjArray};
	return ruleObj;
}

/**
 * 渲染一个考勤时段元素
 * @param {object} timeObj 考勤时段数据
 * @param {string} virtualDate 假设日期
 * @param $timesContainer 考勤时段父容器
 * @param {object} $beforePosition [可选] 插入位置(前置)
 * @param {object} $afterPosition  [可选] 追加位置(后置)
 */
function renderAttendTimeElement(timeObj, virtualDate, $timesContainer, $beforePosition, $afterPosition) {
	if (timeObj.new_field_matched)
		timeObj.new_field_matched = 1;
	else 
		timeObj.new_field_matched = 0;
	
	if (timeObj.signin_time)
		timeObj.signin_time_combined = timeObj.signin_time.substring(0, 5) + ' ' + virtualDate;
	else 
		timeObj.signin_time_combined = '';
	
	if (timeObj.signout_time)
		timeObj.signout_time_combined = timeObj.signout_time.substring(0, 5) + ' ' + virtualDate;
	else 
		timeObj.signout_time_combined = '';
	
	var timeHtml = laytpl($('#attendance-setting-time-script').html()).render(timeObj);
	if ($beforePosition)
		$beforePosition.before(timeHtml);
	else if ($afterPosition)
		$afterPosition.after(timeHtml);
	else 
		$timesContainer.append(timeHtml);
}

/**
 * 渲染一个考勤规则元素
 * @param {object} rule 规则数据
 * @param {string} virtualDate 假设日期
 * @param {object} $extPropContainer1 父容器，JQuery对象
 * @param {object} $beforePosition [可选] 插入位置(前置)
 * @param {object} $afterPosition  [可选] 追加位置(后置)
 */
function renderAttendRuleElement(rule, virtualDate, $extPropContainer1, $beforePosition, $afterPosition) {
	if (!$beforePosition && !$afterPosition) {
		logjs_err('$beforePosition and $afterPosition are all empty');
		return;
	}
	
	rule.work_day = parseInt(rule.work_day);
	if (rule.new_field_matched)
		rule.new_field_matched = 1;
	else 
		rule.new_field_matched = 0;
	
	var ruleHtml = laytpl($('#attendance-setting-rule-script').html()).render(rule);
	var assignedHtml = '<div data-assigned-id="' + rule.rul_index + '"></div>';
	var divideHtml = laytpl($('#attendance-setting-divide-script').html()).render({});
	
	if ($beforePosition) {
		$beforePosition.before(ruleHtml);
		$beforePosition.before(assignedHtml);
		$beforePosition.before(divideHtml);
	} else {
		$afterPosition.after(divideHtml);
		$afterPosition.after(assignedHtml);
		$afterPosition.after(ruleHtml);
	}
	
	//考勤时间段
	var $timesContainer = $extPropContainer1.find('div[data-assigned-id="' + rule.rul_index + '"]');
	var times = rule.times;
	if (typeof times !=='undefined') {
		for (var j=0; j<times.length; j++) {
			var timeObj = times[j];
			//渲染一个考勤时段元素
			renderAttendTimeElement(timeObj, virtualDate, $timesContainer);
		}
	}
	
	//调整(启用或禁用)周工作日选项
	for (var i=0; i<=6; i++) {
		adjustAttendSettingWeekday($extPropContainer1, i);
	}
}

/**
 * 转换考勤时间段标准签到、签退时间
 * @param {string} standardSignTime 签到、签退时间，格式如：09:00 2017-01-01
 * @returns {String}
 */
function translateAttendStandardSignTime(standardSignTime) {
	if (standardSignTime.length<16)
		return '';
	return standardSignTime.substr(6, 10) + ' ' + standardSignTime.substr(0, 5) + ':00';
}

/**
 * 调整考勤时段的工作时长和休息时长
 * @param {object} cElement 被触发的目标对象
  *@returns {boolean} 是否执行过调整
 */
function adjustAttendSettingTimeDuration(cElement) {
	var $cElement = $(cElement);
	var $aSetTimeContainer = $cElement.parents('.attend-setting-time');
	
	//提取几个相应的输入对象
	var $signinTimeE = $aSetTimeContainer.find('input[name^="signin_time_"]');
	var $signoutTimeE = $aSetTimeContainer.find('input[name^="signout_time_"]');
	var $restDurationE = $aSetTimeContainer.find('input[name^="rest_duration_"]');
	var $workDurationE = $aSetTimeContainer.find('input[name^="work_duration_"]');
	
	//为空白值输入对象设置初值
	if ($restDurationE.val().length==0)
		$restDurationE.val('0');
	if ($workDurationE.val().length==0)
		$workDurationE.val('0');
	//取值
	var signinTime = $signinTimeE.val();
	var signoutTime = $signoutTimeE.val();
	var restDuration = parseInt($restDurationE.val());
	var workDuration = parseInt($workDurationE.val());
	
	//时间输入不完整，不调整
	if (signinTime.length==0 || signoutTime.length==0)
		return false;
	
	signinTime = translateAttendStandardSignTime(signinTime);
	signoutTime = translateAttendStandardSignTime(signoutTime);
	//验证时间先后合法性
	if (new Date(signinTime).getTime() > new Date(signoutTime).getTime()) {
		layer.msg('签到时间 必须在 签退时间之前', {icon:5});
		return false;
	}
	
	//计算两个时间的差值(分钟)
	var minutes = diffMinutesBetweenTwoTimes(signinTime, signoutTime);
	
	//当休息时长负数，或当休息时长的值大于时间段的差值时，调整到合理的值
	if (minutes<restDuration || restDuration<0) {
		$restDurationE.val(minutes);
		restDuration = minutes;
	}
	//当工作时长负数，或当工作时长的值大于时间段的差值时，调整到合理的值
	if (minutes<workDuration || workDuration<0) {
		$workDurationE.val(minutes);
		workDuration = minutes;
	}
	
	if ($cElement.attr('name').indexOf('signin_time_')>-1 || $cElement.attr('name').indexOf('signout_time_')>-1 || $cElement.attr('name').indexOf('rest_duration_')>-1) {
		//调整工作时长的值
		$workDurationE.val(minutes - restDuration);
	} else if ($cElement.attr('name').indexOf('work_duration_')>-1) {
		//调整休息时长的值
		$restDurationE.val(minutes - workDuration);
	}
	
	return true;
}

/**
 * 计算一个考勤设置内多个考勤规则工作时长之和
 * @param {object} $form form表单对象，JQuery对象
 * @returns {int} 整个考勤设置内多考勤规则的工作时长之和
 */
function calculAttendAllWorkDuration($form) {
	var allWorkDuration = 0;
	$form.find('.attend-setting-rule').each(function() {
		var ruleIndex = parseInt($(this).attr('data-rule-index'));
		var wDayCount = $(this).find('.week_value:checked').length;
		allWorkDuration += calculAttendRuleTotalWorkDuration($form, ruleIndex) *wDayCount;
	});
	
	$form.find('input[name="all_duration"]').val(formatMinutes(allWorkDuration));
}

/**
 * 计算考勤规则内的总工作时长
 * @param {object} $form form表单对象，JQuery对象
 * @param {int} ruleIndex 考勤规则顺序号
 * @returns {int} 一个考勤规则内多个时间段的工作时长之和
 */
function calculAttendRuleTotalWorkDuration($form, ruleIndex) {
	var workDuration = 0;
	$form.find('div[data-assigned-id="' + ruleIndex + '"]').find('input[name^="work_duration_"]').each(function() {
		workDuration += parseInt(this.value);
	});
	
	$form.find('.attend-setting-rule[data-rule-index="' + ruleIndex + '"] input[name^="rule_duration_"]').val(formatMinutes(workDuration));
	return workDuration;
}

/**
 * 调整(启用或禁用)周工作日(同日)选项
 * @param {object} $container 父容器，JQuery对象
 * @param {int|string} weekDay checkbox的data-wday属性
 */
function adjustAttendSettingWeekday($container, weekDay) {
	var selector = 'input[type="checkbox"][data-wday="' + weekDay + '"].week_value';
	var checkedSelector = 'input:checked[type="checkbox"][data-wday="' + weekDay + '"].week_value';
	var notCheckedSelector = 'input:not(:checked)[type="checkbox"][data-wday="' + weekDay + '"].week_value';
	
	var checkedCount = $container.find(checkedSelector).length;
	if (checkedCount>0) {
		$container.find(checkedSelector).removeAttr("disabled");
		$container.find(notCheckedSelector).attr("disabled", true);
	} else {
		$container.find(selector).removeAttr("disabled");
	}
}

/**
 * 计算假期时长并刷新显示
 * @param $container 容器对象，JQuery对象
 */
function calculHolidayDuration($container) {
	var period = parseInt($container.find('input[type="radio"][name="period"]:checked').val());
	var flag = parseInt($container.find('input[type="radio"][name="flag"]:checked').val());
	var dayDiff = 0;
	var vStartTime, vStopTime;
	
	switch(period) {
	case 0: //一次性假期
		if ($container.find('#start-time').val().length>0)
			vStartTime = $container.find('#start-time').val() + ' 00:00:00';
		if ($container.find('#stop-time').val().length>0)
			vStopTime = $container.find('#stop-time').val() + ' 23:59:59';
		break;
	case 1: //每年假期
		var periodFrom = $container.find('#year-period-from').val();
		var periodTo = $container.find('#year-period-to').val();
		if (periodFrom.length>0)
			vStartTime 	= periodFrom.substr(6, 4) + '-' + periodFrom.substr(0, 5) + ' 00:00:00';
		if (periodTo.length>0)
			vStopTime 	= periodTo.substr(6, 4) + '-' + periodTo.substr(0, 5) +' 23:59:59';
		break;
	case 2: //每月假期
		var periodFrom = $container.find('#month-period-from').val();
		var periodTo = $container.find('#month-period-to').val();
		if (periodFrom.length>0)
			vStartTime 	= periodFrom.substr(3, 7) + '-' + periodFrom.substr(0, 2) + ' 00:00:00';
		if (periodTo.length>0)
			vStopTime 	= periodTo.substr(3, 7) + '-' + periodTo.substr(0, 2) +' 23:59:59';		
		break;
	case 3: //每周假期
		var periodFrom = $container.find('select[name="week_period_from"]').val();
		var periodTo = $container.find('select[name="week_period_to"]').val();
		if ($.trim(periodFrom).length>0 && $.trim(periodTo).length>0) {
			if (parseInt(periodFrom) > parseInt(periodTo)) {
				layer.msg('请留意假期开始-结束的先后顺序', {icon: 2});
				$container.find('#holiday_duration').html(0);
				return;
			}
			
			dayDiff = parseInt(periodTo) - parseInt(periodFrom) + 1;
			if (flag!=0)
				dayDiff *= 0.5;
			$container.find('#holiday_duration').html(dayDiff);			
		} else {
			$container.find('#holiday_duration').html(0);
		}
		break;
	}
	
	if (period!=3) {
		if (vStartTime && vStopTime) {
			vStartTime = new Date(vStartTime);
			vStopTime = new Date(vStopTime);
			if (vStartTime.getTime() > vStopTime.getTime()) {
				layer.msg('请留意假期开始-结束的先后顺序', {icon: 2});
				$container.find('#holiday_duration').html(0);				
				return;
			}
			
			var dayDiff = calculateDateDiff2(vStartTime, vStopTime);
			if (flag!=0)
				dayDiff *= 0.5;
			$container.find('#holiday_duration').html(dayDiff);
		} else {
			$container.find('#holiday_duration').html(0);
		}
	}
}

/**
 * 特殊的数字转换为日期(部分)，例如：1001转换为10-01，910转换为9-10
 * @param {int|string} digit
 * @returns {string}
 */
function specalDigitToPartDate(digit) {
	if (typeof digit ==='string')
		digit = parseInt(digit);
	
	return prefixInteger(parseInt(digit/100), 2) + '-' + prefixInteger((digit%100), 2);
}
