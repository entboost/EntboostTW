<!-- select控件备选项 -->

<script id="select-option-week-script" type="text/html">
{{# for(var i = 0, len = d.length; i < len; i++){ }}
	<option value="{{d[i].week}}" data-startdate="{{d[i].dates[0]}}" data-enddate="{{d[i].dates[1]}}">{{d[i].year}}年第{{d[i].week+1}}周 ({{d[i].dates2[0]}}-{{d[i].dates2[1]}})</option>
{{# } }}
</script>

<script id="select-option-month-script" type="text/html">
{{# for(var i = 0, len = d.length; i < len; i++){ }}
	<option value="{{d[i].month}}" data-startdate="{{d[i].dates[0]}}" data-enddate="{{d[i].dates[1]}}">{{d[i].year}}年{{d[i].month+1}}月</option>
{{# } }}
</script>

<script id="select-option-season-script" type="text/html">
{{# for(var i = 0, len = d.length; i < len; i++){ }}
	<option value="{{d[i].season}}" data-startdate="{{d[i].dates[0]}}" data-enddate="{{d[i].dates[1]}}">{{d[i].year}}年第{{d[i].season+1}}季度 ({{d[i].dates2[0]}}-{{d[i].dates2[1]}})</option>
{{# } }}
</script>

<script id="select-option-year-script" type="text/html">
{{# for(var i = 0, len = d.length; i < len; i++){ }}
	<option value="{{d[i].year}}" data-startdate="{{d[i].dates[0]}}" data-enddate="{{d[i].dates[1]}}">{{d[i].year}}年</option>
{{# } }}
</script>