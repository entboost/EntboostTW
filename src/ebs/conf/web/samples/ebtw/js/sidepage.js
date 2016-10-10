/**
 * 右侧页面常用函数与注册事件
 */

$(document).ready(function() {
	//注册事件-关闭右侧页
	$(document).on('click', '.side-close', function () {
	    closeSidepage();
	});
	
	//注册事件-全屏/还原右侧页
	$(document).on('click', '.side-fullscreen', function () {
	    var me = $(this);
	    if (me.data('type') == "0") {
	        $("#sidepage").animate({ width: "100%" }, "fast", function () {
	            me.find(".glyphicon").removeClass("glyphicon-fullscreen").addClass("glyphicon-resize-small");
	            me.data("type", "1").find("span").attr("title", "还原");
	            //触发resize事件
	            $(window).trigger('resize');
	        });
	    } else {
	        $("#sidepage").animate({ right: "0", width: "700px" }, "fast", function () {
	            me.find(".glyphicon").removeClass("glyphicon-resize-small").addClass("glyphicon-fullscreen");
	            me.data("type", "0").find("span").attr("title", "全屏");
	            //触发resize事件
	            $(window).trigger('resize');
	            /*
	            if ($("#umeditor").length > 0) {
	                $("#umeditor").css("width","100%");
	            }*/
	        });
	    }
	    
	    /*
	    if (dw_st != undefined && dw_st != null) {
	        setTimeout(function () { plotSubtaskTree(); }, 1000);
	    }*/
	});
});