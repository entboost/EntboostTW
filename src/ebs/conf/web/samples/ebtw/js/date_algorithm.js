/**
 * 定义日期算法插件
 * 根据某年某周获取一周的日期：如开始日期规定为星期一到星期日为一周
 */
;(function($){
	$.D_ALG =$.date_algorithm = {
		validParts: function () {
			return /hh?|HH?|p|P|ii?|ss?|dd?|DD?|mm?|MM?|yy(?:yy)?/g;
		},
		parseFormat: function (format) {
			// IE treats \0 as a string end in inputs (truncating the value),
			var separators = format.replace(this.validParts(), '\0').split('\0'),
				parts = format.match(this.validParts());
			if (!separators || !separators.length || !parts || parts.length == 0) {
				throw new Error("Invalid date format.");
			}
			return {separators: separators, parts: parts};
		},
		//格式化日期对象，输出字符串
		formatDate: function (date, format) {
			if (date == null) {
				return '';
			}
			var language = 'zh-CN';
			var dates = $.fn.datetimepicker.dates;
			var val = {
					// year
					yy:   date.getFullYear().toString().substring(2),
					yyyy: date.getFullYear(),
					// month
					m:    date.getMonth() + 1,
					M:    dates[language].monthsShort[date.getMonth()],
					MM:   dates[language].months[date.getMonth()],
					// day
					d:    date.getDate(),
					D:    dates[language].daysShort[date.getDay()],
					DD:   dates[language].days[date.getDay()],
					p:    (dates[language].meridiem.length == 2 ? dates[language].meridiem[date.getHours() < 12 ? 0 : 1] : ''),
					// hour
					h:    date.getHours(),
					// minute
					i:    date.getMinutes(),
					// second
					s:    date.getSeconds()				
	//				// year
	//				yy:   date.getUTCFullYear().toString().substring(2),
	//				yyyy: date.getUTCFullYear(),
	//				// month
	//				m:    date.getUTCMonth() + 1,
	//				M:    dates[language].monthsShort[date.getUTCMonth()],
	//				MM:   dates[language].months[date.getUTCMonth()],
	//				// day
	//				d:    date.getUTCDate(),
	//				D:    dates[language].daysShort[date.getUTCDay()],
	//				DD:   dates[language].days[date.getUTCDay()],
	//				p:    (dates[language].meridiem.length == 2 ? dates[language].meridiem[date.getUTCHours() < 12 ? 0 : 1] : ''),
	//				// hour
	//				h:    date.getUTCHours(),
	//				// minute
	//				i:    date.getUTCMinutes(),
	//				// second
	//				s:    date.getUTCSeconds()
			};
			
			if (dates[language].meridiem.length == 2) {
				val.H = (val.h % 12 == 0 ? 12 : val.h % 12);
			} else {
				val.H = val.h;
			}
			val.HH = (val.H < 10 ? '0' : '') + val.H;
			val.P = val.p.toUpperCase();
			val.hh = (val.h < 10 ? '0' : '') + val.h;
			val.ii = (val.i < 10 ? '0' : '') + val.i;
			val.ss = (val.s < 10 ? '0' : '') + val.s;
			val.dd = (val.d < 10 ? '0' : '') + val.d;
			val.mm = (val.m < 10 ? '0' : '') + val.m;
			
			format = this.parseFormat(format);
			
			var date = [],
				seps = $.extend([], format.separators);
			for (var i = 0, cnt = format.parts.length; i < cnt; i++) {
				if (seps.length) {
					date.push(seps.shift());
				}
				date.push(val[format.parts[i]]);
			}
			
			if (seps.length) {
				date.push(seps.shift());
			}
			return date.join('');
		},
		isInOneYear : function(_year,_week) {
			if(typeof year!='number' && typeof week!='number' && (year == undefined || year == '' || week == undefined || week == '')) {
				return false;
			}
			var theYear = this.getXDate(_year,_week,1).getFullYear();
			if(theYear != _year) {
				return false; 
			}
			return true; 
		},
		// 这个方法将取得某年(year)第几周(weeks)的星期几(weekDay)的日期
		getXDate : function(year,weeks,weekDay) {
			// 用指定的年构造一个日期对象，并将日期设置成这个年的1月1日
			// 因为计算机中的月份是从0开始的,所以有如下的构造方法
			var date = new Date(year,"0","1");
			
			// 取得这个日期对象 date 的长整型时间 time
			var time = date.getTime();
			
			// 将这个长整形时间加上第N周的时间偏移
			// 因为第一周就是当前周,所以有:weeks-1,以此类推
			// 7*24*3600000 是一星期的时间毫秒数,(JS中的日期精确到毫秒)
			time+=(weeks-1)*7*24*3600000;
			
			// 为日期对象 date 重新设置成时间 time
			date.setTime(time);
//			alert(this.formatDate(date, 'yyyy-mm-dd hh:ii:ss'));
			return this.inWeekDate(date, weekDay);
		},
		// 这个方法将取得某日期(nowDate) 所在周的星期几(weekDay)的日期
		inWeekDate : function(nowDate,weekDay) {
			// 0是星期日,1是星期一,...
			weekDay%=7;
			var day = nowDate.getDay();
			var time = nowDate.getTime();
			var sub = weekDay-day;
			if(sub <= 0) {
				sub += 7;
			}
			time+=sub*24*3600000;
			nowDate.setTime(time);
			return nowDate;
		},
		/**
		 * 获取某年第N周日期范围，一周是从星期一到星期日
		 * @param {string|number} year 年
		 * @param {string|number} week 第几周，0表示第一周，1表示第二周，...52表示最后一周
		 * @return {array} 时间范围数组
		 */
		dateRange : function(year, week) {
			if(typeof year!='number' && typeof week!='number' && (year == undefined || year == '' || week == undefined || week == '')) {
				return ""; 
			}
			
			var format = 'yyyy-mm-dd';
			var beginDate = this.getXDate(year, week, 1);
			var finalDate = this.formatDate(this.getXDate(year, 52, 7), 'mm-dd'); //某年最后一周的最后一日
			endDate = this.getXDate(year, (finalDate=='12-31')?week:week + 1, 7); //当12月25日是周一，则下一年1月1日是周一
			
			return [this.formatDate(beginDate, format), this.formatDate(endDate, format)];
		},
//		dateRange : function(year, week) {
//			if(isInOneYear(_year,_week)) {
//				var showDate = this.getDateRange(year, week);
//			} else {
//				alert(_year+"年无"+_week+"周，请重新选择");
//			}
//		}
		//判断年份是否为润年
		isLeapYear : function(year) {
			return (year%400 == 0) || (year%4 == 0 && year%100 != 0);
		},
		//获取某一年份的某一月份的天数，month从0开始
		getMonthDays : function(year, month) {
			return [31, null, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month] || (this.isLeapYear(year) ? 29 : 28);
		},
		//计算某日期处于该年的第几周；注意：0表示第一周，1表示第二周，以此类推
		theWeekNumber : function(date) {
		    var year = date.getFullYear();
		    var totalDays = (new Date(year, 0, 1).getDay() || 7)-1;
		    
		    var days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]; 
		    //判断是否为闰年，针对2月的天数进行计算
		    if ((year%4==0 && year%100!=0) || (year%400==0))
		        days[1] = 29;
		    
		    var curMonth = date.getMonth();
		    if (curMonth == 0) {
		        totalDays += date.getDate();
		    } else {
		        for (var count = 1; count <= curMonth; count++) {
		            totalDays += days[count - 1];
		        }
		        totalDays += date.getDate();
		    }
		    //得到第几周
		    var week = Math.floor( (totalDays+7-1) / 7) - 1;
		    return week;
		}		
	};
})(jQuery);


/**
 * 判断两个时间是否在同一天
 * @param {date} date1
 * @param {date} date2
 * @return {boolean}
 */
function isInSameDay(date1, date2) {
	var format = 'yyyy-mm-dd';
	if ($.D_ALG.formatDate(date1, format) == $.D_ALG.formatDate(date2, format))
		return true;
	return false;
}

/**
 * 判断指定时间是否在今天
 * {date|string} date 指定的时间
 * @return {boolean}
 */
function isToday(date) {
	if (typeof date === 'string')
		date = new Date(date);
	
	var now = new Date();
	var format = 'yyyy-mm-dd';
	if ($.D_ALG.formatDate(now, format)==$.D_ALG.formatDate(date, format)) {
		return true;
	}
	
	return false;
}
/**
 * 判断指定时间是否在昨天
 * {date|string} date 指定的时间
 * @return {boolean}
 */
function isYesterday(date) {
	if (typeof date === 'string')
		date = new Date(date);
	
	var now = new Date();
	now.setDate(now.getDate()-1);
	
	var format = 'yyyy-mm-dd';
	if ($.D_ALG.formatDate(now, format)==$.D_ALG.formatDate(date, format)) {
		return true;
	}
	
	return false;	
}

/**
 * 判断指定日期是否周末
 * @param {string} date 指定日期
 * @returns {Boolean}
 */
function isWeekend(date) {
	if (typeof date==='string')
		date = new Date(date);
	
	return (date.getDay()==0||date.getDay()==6);
}

/**
 * 计算两个日期相差的天数(包括：只要两个时间不在同一天，但不足24小时也计算为1天)
 * @param date1 开始时间
 * @param date2 结束时间，必须大于date1
 * @returns {number|undefined} 如果date1>date2，返回undefined
 */
function calculateDateDiff(date1, date2) {
	var diff=date2.getTime()-date1.getTime(); //时间差的毫秒数
	if (diff<0)
		return;
	
	var oneDayMS = 24*3600*1000; 
	var c = diff/oneDayMS;
	var days=Math.floor(c);
	
	if (c-days==0) {
		return days;
	} else {
		var date3 = new Date(date1.getTime()+days*oneDayMS);
		if (isInSameDay(date2, date3))
			return days;
		return days+1;
	}
}

/**
 * 计算剩余时间并以'N天M小时X分钟'格式返回
 * @param targetTime 目标时间
 * @return {string} 以'N天M小时X分钟'格式表示的剩余时间
 */
function calculateRemainTimeToPopular(targetTime) {
	var date1=new Date(); //开始时间
	var date2=targetTime; //结束时间
	var date3=date2.getTime()-date1.getTime(); //时间差的毫秒数
	if (date3<=0)
		return '0';
	
	var str = '';
	//计算出相差天数
	var days=Math.floor(date3/(24*3600*1000));
	if (days>0) 
		str = str + days + '天';
	//计算出小时数
	var leave1=date3%(24*3600*1000); //计算天数后剩余的毫秒数
	var hours=Math.floor(leave1/(3600*1000));
	if (hours>0)
		str = str + hours + '小时';
	//计算相差分钟数
	var leave2=leave1%(3600*1000); //计算小时数后剩余的毫秒数
	var minutes=Math.floor(leave2/(60*1000));
	if (minutes>0)
		str = str + minutes + '分钟';
	//计算相差秒数
	var leave3=leave2%(60*1000); //计算分钟数后剩余的毫秒数
	var seconds=Math.round(leave3/1000);
	if (str.length==0) {
		if (seconds>0)
			str = '1分钟';
		else 
			str = '0';
	}
	
	return str;
}

/**
 * 通俗化翻译日期-1
 * @param {date|string} date 指定的时间
 * @param {boolean} multiple (可选) 是否以数组形式返回格式化后的各部分内容
 * @param {string} outFormat (可选) 格式化输出的日期格式，不传入时使用格式：yyyy-mm-dd
 * @return {string|array} 返回字符串或数组；数组元素含义：[0]=今日(1)、昨日(-1)、其它(0){number}，[1]=周几{string}，[2]=按传入格式参数(outputFormat)处理后的日期{string}，
 * 		[3]=按传入格式参数yyyy-mm-dd处理后的日期{string}，[4]=是否周末(周六或周日){boolean}
 */
function popularDate(date, multiple, outputFormat) {
	if (typeof date === 'string')
		date = new Date(date);
	
	var now = new Date();
	var format = 'yyyy-mm-dd';
	var result = '';
	var today = false;
	var yesterday = isYesterday(date);
	
	if (isToday(date)/*$.D_ALG.formatDate(now, format)==$.D_ALG.formatDate(date, format)*/) {
		result = '今天';
		today = true;
	} else {
		result = $.D_ALG.formatDate(date, outputFormat||format);
	}
	
	
	
	var weeks = ['日', '一', '二', '三', '四', '五', '六'];
	if (multiple) {
		return [(today?1:(yesterday?-1:0)), '周' + weeks[date.getDay()], $.D_ALG.formatDate(date, outputFormat||format), $.D_ALG.formatDate(date, format), (date.getDay()==0||date.getDay()==6)];
	} else {
		return result + ' / 周' + weeks[date.getDay()];
	}
}

/**
 * 通俗化翻译日期-2
 * @param {date|string} dateTime 指定的时间
 * @return {string}
 */
function popularDate2(dateTime) {
	var now = new Date();
	if (typeof dateTime=='string')
		dateTime = new Date(dateTime);
	
	var weeks = ['日', '一', '二', '三', '四', '五', '六'];
	var weekName = weeks[dateTime.getDay()];	
	
	//判断是否同一天
	if (isInSameDay(dateTime, now)) {
		return '今天/周' + weekName;
	}
	//判断是否昨天
	if (calculateDateDiff(dateTime, now)==1) {
		return '昨天/周' + weekName;
	}
	
	var msecOfOneDay = 24*3600000; //一天的总毫秒数
	var diff = now.getTime()-dateTime.getTime();
	var overDay = Math.floor(diff/msecOfOneDay);
	var overMonth = Math.floor(overDay/30);
	var overYear = Math.floor(overDay/365);
	
	//判断同一年
	if (dateTime.getFullYear()==now.getFullYear()) {
		var over = overMonth>0?(overMonth+'月'):(overDay+'天')
		return '('+over+'前) '+$.D_ALG.formatDate(dateTime, 'mm月dd日 ')+'周' + weekName;
	}
	return '('+overYear+'年前) '+$.D_ALG.formatDate(dateTime, 'yyyy年mm月dd日 ')+'周' + weekName;
}
//alert(popularDate2('2015-06-12 22:12:00'));

/**
 * 通俗化翻译日期-3
 * @param {date|string} dateTime 指定的时间
 * @return {string}
 */
function popularDate3(dateTime) {
	var now = new Date();
	if (typeof dateTime=='string')
		dateTime = new Date(dateTime);
	
	var weeks = ['日', '一', '二', '三', '四', '五', '六'];
	var weekName = weeks[dateTime.getDay()];	
	
	//判断是否同一天
	if (isInSameDay(dateTime, now)) {
		return $.D_ALG.formatDate(dateTime, 'hh:ii');
	}
	//判断是否昨天
	if (calculateDateDiff(dateTime, now)==1) {
		return '昨天 ' + $.D_ALG.formatDate(dateTime, 'hh:ii');
	}
	
	var msecOfOneDay = 24*3600000; //一天的总毫秒数
	var diff = now.getTime()-dateTime.getTime();
	var showOverDay = Math.ceil(diff/msecOfOneDay);
	var overDay = Math.floor(diff/msecOfOneDay);
	var overMonth = Math.floor(overDay/30);
	var overYear = Math.floor(overDay/365);
	
	//判断同一年
	if (dateTime.getFullYear()==now.getFullYear()) {
		var over = overMonth>0?(overMonth+'月'):(showOverDay+'天')
		return '('+over+'前) '+$.D_ALG.formatDate(dateTime, 'mm月dd日 ')+'周' + weekName;
	}
	return '('+overYear+'年前) '+$.D_ALG.formatDate(dateTime, 'yyyy年mm月dd日 ')+'周' + weekName;
}

/**
 * 通俗化翻译日期时间
 * @param {date|string} dateTime 指定的日期时间
 * @return {string} 日期表述
 */
function popularDateTime(dateTime) {
	var now = new Date();
	if (typeof dateTime=='string')
		dateTime = new Date(dateTime);
	//判断是否同一天
	if (isInSameDay(dateTime, now)) {
		return $.D_ALG.formatDate(dateTime, 'hh:ii');
	}
	//判断是否昨天
	if (calculateDateDiff(dateTime, now)==1) {
		return '昨天 '+$.D_ALG.formatDate(dateTime, 'hh:ii');
	}
	//判断同一年
	if (dateTime.getFullYear()==now.getFullYear()) {
		return $.D_ALG.formatDate(dateTime, 'mm月dd日 hh:ii');
	}
	return $.D_ALG.formatDate(dateTime, 'yyyy年mm月dd日 hh:ii');
}


/**
 * 获取前(或后)几日的日期范围(包括当日)
 * @param {boolean} asc true=上升顺序(相当于后几天)；false=下降顺序(相当于前几天)
 * @param {number} count 个数
 * @param {number} floatIndex (可选) 浮动天数：0(默认)=当天，-1=当天的上一天，1=当天的下一天，以此类推
 * @param {string|object} targetDate (可选) 参考日期，默认当前日期
 * @returns {Array} 
 */
function severalDays(asc, count, floatIndex, targetDate) {
	var results = new Array();
	var now = targetDate || new Date();
	now = (typeof now =='string')?new Date(now):now;
	now = new Date($.D_ALG.formatDate(now, 'yyyy-mm-dd 00:00:00'));
	
	if (floatIndex) 
		now.setDate(now.getDate()+floatIndex); //日期加减天数
	
	var step = asc?1:-1;
	results.push(new Date(now));
	for (var i = (asc?0:count-1); asc?(i<count-1):(i>0);  asc?i++:i--) {
		now.setDate(now.getDate()+step);
		results.push(new Date(now));
	}
	
	return results;
}

/**
 * 获取下几年的日期范围
 * @param {number} count 个数
 * @param {number} floatIndex (可选) 浮动年个数：0(默认)=当前年，-1=当前年的上一年，1=当前年的下一年，以此类推
 * @param {string|object} targetDate (可选) 参考日期，默认当前日期
 * @returns {Array}
 */
function nextSeveralYears(count, floatIndex, targetDate) {
	var results = new Array();
	var now = targetDate || new Date();
	now = (typeof now =='string')?new Date(now):now;
	var year = now.getFullYear();
	year+= (floatIndex || 0);
	
	for (var i=0; i<count; i++) {
		var obj = new Object();
		obj.year = year;
		obj.dates = [year+'-01-01', year+'-12-31'];
		results[i] = obj;
		
		year++;
	}
	return results;
}
/**
 * 获取下几个季度的日期范围
 * @param {number} count 个数
 * @param {number} floatIndex (可选) 浮动季度个数：0(默认)=当前季度，-1=当前季度的上一季度，1=当前季度的下一季度，以此类推
 * @param {string|object} targetDate (可选) 参考日期，默认当前日期
 * @returns {Array}
 */
function nextSeveralSeasons(count, floatIndex, targetDate) {
	var results = new Array();
	var now = targetDate || new Date();
	now = (typeof now =='string')?new Date(now):now;
	var year = now.getFullYear();
	var month = now.getMonth();
	var season = parseInt(month/3);
	if (floatIndex!=undefined) {
		season+=floatIndex;
		if (floatIndex>0) {
			for (;season>=4;) {
				season-=4;
				year++;
			}
		} else {
			for(;season<0;) {
				season+=4;
				year--;
			}
		}
	}
	
	for (var i=0; i<count; i++) {
		var startMonth = season*3;
		var endMonth = startMonth+2;
		var days = $.D_ALG.getMonthDays(year, endMonth);
		var obj = new Object();
		var startDate = new Date(year+'-'+(startMonth+1+'-1'));
		var endDate = new Date(year+'-'+(endMonth+1+'-'+days));
		var dateAry = [$.D_ALG.formatDate(startDate, 'yyyy-mm-dd'), $.D_ALG.formatDate(endDate, 'yyyy-mm-dd')];
		
		obj.year = startDate.getFullYear();
		obj.season = season;
		obj.dates = dateAry;
		obj.dates2 = [$.D_ALG.formatDate(startDate, 'mm月dd日'), $.D_ALG.formatDate(endDate, 'mm月dd日')];
		results[i] = obj;
		
		season++;
		(season/4==1)?(year++,season=0):0;
	}
	return results;
}
/**
 * 获取下几个月份的日期范围
 * @param {number} count 个数
 * @param {number} floatIndex (可选) 浮动月份个数：0(默认)=当前月，-1=当前月的上一月，1=当前月的下一月，以此类推
 * @param {string|object} targetDate (可选) 参考日期，默认当前日期
 * @returns {Array}
 */
function nextSeveralMonths(count, floatIndex, targetDate) {
	var results = new Array();
	var now = targetDate || new Date();
	now = (typeof now =='string')?new Date(now):now;
	var year = now.getFullYear();
	var month = now.getMonth();
	if (floatIndex!=undefined) {
		month+=floatIndex;
		if (floatIndex>0) {
			for (;month>=12;) {
				month-=12;
				year++;
			}
		} else {
			for(;month<0;) {
				month+=12;
				year--;
			}
		}
	}
	
	for (var i=0; i<count; i++) {
		var days = $.D_ALG.getMonthDays(year, month);
		var obj = new Object();
		var startDate = new Date(year+'-'+(month+1+'-1'));
		var endDate = new Date(year+'-'+(month+1+'-'+days));
		var dateAry = [$.D_ALG.formatDate(startDate, 'yyyy-mm-dd'), $.D_ALG.formatDate(endDate, 'yyyy-mm-dd')];
		
		obj.year = startDate.getFullYear();
		obj.month = startDate.getMonth();
		obj.dates = dateAry;
		obj.dates2 = [$.D_ALG.formatDate(startDate, 'yyyy年mm月dd日'), $.D_ALG.formatDate(endDate, 'yyyy年mm月dd日')];
		results[i] = obj;
		
		month++;
		(month/12==1)?(year++,month=0):0;
	}
	return results;
}
/**
 * 获取下几个星期的日期范围
 * @param {number} count 个数
 * @param {number} floatIndex (可选) 浮动周数：0(默认)=当前周，-1=当前周的上一周，1=当前周的下一周，以此类推
 * @param {string|object} targetDate (可选) 参考日期，默认当前日期
 * @returns {Array}
 */
function nextSeveralWeeks(count, floatIndex, targetDate) {
	var results = new Array();
	var now = targetDate || new Date();
	now = (typeof now =='string')?new Date(now):now;
	var year = now.getFullYear();
	var week = $.D_ALG.theWeekNumber(now);
	if (floatIndex!=undefined) {
		week+=floatIndex;
		if (floatIndex>0) {
			for (;week>=53;) {
				week-=53;
				year++;
			}
		} else {
			for(;week<0;) {
				week+=53;
				year--;
			}
		}
	}
	
	for (var i=0; i<count; i++) {
		var dateAry = $.D_ALG.dateRange(year, week);
		var obj = new Object();
		var startDate = new Date(dateAry[0]);
		var endDate = new Date(dateAry[1]);
		var start_s = $.D_ALG.formatDate(startDate, 'mm-dd');
		obj.year = startDate.getFullYear();
		obj.week = (week==0&&start_s!='01-01')?52:((week==0&&year==obj.year)?0:(week==0?1:week));
		obj.dates = dateAry;
		obj.dates2 = [$.D_ALG.formatDate(startDate, 'mm月dd日'), $.D_ALG.formatDate(endDate, 'mm月dd日')];
		results[i] = obj;
		
		var end_s = $.D_ALG.formatDate(endDate, 'mm-dd');
		week++;
		week%=53;
		week==0?year++:0;
		(week==0&&end_s!='12-31')?week++:0;
	}
	return results;
}
