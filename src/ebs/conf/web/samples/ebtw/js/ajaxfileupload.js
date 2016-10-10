/**
 * 使用ajax上传文件
 */
;(function($, window){
   var ajaxFU = $.ajaxFU = {};
   $.extend(ajaxFU, {
       createUploadIframe: function (id, uri) {
	       //create frame
	       var frameId = 'jUploadFrame' + id;
	
	       var io = null;
	       if (window.ActiveXObject) {
	           //fix ie9 and ie 10 and ie11-------------
	           if ($.browser.version == "9.0" || $.browser.version == "10.0" || $.browser.version == "11.0") {
	               io = document.createElement('iframe');
	               io.id = frameId;
	               io.name = frameId;
	           } else if ($.browser.version == "6.0" || $.browser.version == "7.0" || $.browser.version == "8.0") {
	               io = document.createElement('<iframe id="' + frameId + '" name="' + frameId + '" />');
	           }
	       } else {
	           io = document.createElement('iframe');
	           io.id = frameId;
	           io.name = frameId;
	       }
	       
	       if (typeof uri == 'boolean') {
	           io.src = 'javascript:false';
	       } else if (typeof uri == 'string') {
	           io.src = uri;
	       }
	       
	       io.style.position = 'absolute';
	       io.style.top = '-1000px';
	       io.style.left = '-1000px';
	       
	//            io.document.domain ="entboost.com";
	//            alert(document.domain);
	//            alert(io.document.domain);
	//            alert(io.src);
	
	       document.body.appendChild(io);
	
	       return io;
       },
       createUploadForm: function (id, fileElementId, cloneElementToPostion, tag_name, tag_link, tag_sort, tag_status, tag_id, parameters) {
           //create form
           var formId = 'jUploadForm' + id;
           var fileId = 'jUploadFile' + id;
           //--
//        var tagNameId = 'tag_name' + id;  
//        var tagLinkId = 'tag_link' + id;  
//        var tagSortId = 'tag_sort' + id;  
//        var tagStatusId = 'tag_status' + id;  
//        var tagIdId = 'tag_id' + id;  
           //--end
           var form = $('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');
           if (parameters) {
        	   for (name in parameters) {
        		   var value = parameters[name];
        		   if (typeof value != 'undefined') {
	        		   var paramElement = '<input type="text" name="'+name+'" value="'+value+'">';
	        		   $(paramElement).appendTo(form);
        		   }
        	   }
           }
           
           var $oldElement = $('#' + fileElementId);
           if (cloneElementToPostion) {
        	   $oldElement.clone().insertBefore($oldElement);
//        	   var $newElement = $oldElement.clone();
//        	   $oldElement.before($newElement);
           }
           
           $oldElement.attr('id', fileId).appendTo(form);
//			form.append($oldElement);
//			logjs_info('want to upload file:'+$oldElement.val());
           
           //--
//        var tagNameElement = '<input type="text" name="tag_name" value="'+tag_name+'">';    
//        var tagLinkElement = '<input type="text" name="tag_link" value="'+tag_link+'">';  
//        var tagSortElement = '<input type="text" name="tag_sort" value="'+tag_sort+'">';  
//        var tagStatusElement = '<input type="text" name="tag_status" value="'+tag_status+'">';  
//        var tagIdElement = '<input type="text" name="tag_id" value="'+tag_id+'">';  
           //--end
           
           //--
//        $(tagNameElement).appendTo(form);  
//        $(tagLinkElement).appendTo(form);  
//        $(tagSortElement).appendTo(form);  
//        $(tagStatusElement).appendTo(form);  
//        $(tagIdElement).appendTo(form);  
           //--end
           //set attributes
           $(form).css('position', 'absolute');
           $(form).css('top', '-1200px');
           $(form).css('left', '-1200px');
           $(form).appendTo('body');
           return form;
       },
       ajaxFileUpload: function (s) {
			s = $.extend({}, $.ajaxSettings, s);
			var id = new Date().getTime();
			var frameId = 'jUploadFrame' + id;
			var formId = 'jUploadForm' + id;
			
			ajaxFU.createUploadForm(id, s.fileElementId, s.cloneElementToPostion, s.tag_name, s.tag_link, s.tag_sort, s.tag_status, s.tag_id, s.data);
			ajaxFU.createUploadIframe(id, s.secureuri);
			
			
			var io = $('#' + frameId);
			var form = $('#' + formId);
			$(form).attr('action', s.url);
			$(form).attr('method', 'POST');
			$(form).attr('target', frameId);
			if (form.encoding) {
				form.encoding = 'multipart/form-data';
			} else {
				form.enctype = 'multipart/form-data';
			}
			
			//定义函数：清除frame和from
			function clearFrameAndForm() {
                $(io).unbind();
	            setTimeout(function() {
                    try {
                        $(io).remove();
                        $(form).remove();
                    } catch (e) {
                    	logjs_info(e);
                        //ajaxFU.handleError(s, xml, null, e);
                    }
	            }, 100);
			}
			
	        var aOptions = $.extend({}, s);
	      //去除保留的选项(已用完)，防止意料之外结果
	        delete aOptions.secureuri;
	        delete aOptions.fileElementId;
	        delete aOptions.cloneElementToPostion;
	        delete aOptions.data;
	        
	        var success = aOptions.success;
	        if (success) {
	        	aOptions.success = function() {
	        		//success.apply(null, arguments);
	        		success.apply(s, arguments);
	        		clearFrameAndForm();
	        	}
	        }
	        
	        var error = aOptions.error;
	        if (error) {
	        	aOptions.error = function() {
	        		//error.apply(null, arguments);
	        		error.apply(s, arguments);
	        		clearFrameAndForm();
	        	}
	        }
	        
	        var submitOptions = $.extend({}, $.ajaxSettings, aOptions);
	        //logjs_info(submitOptions);
	        $(form).ajaxSubmit(submitOptions);	
			
			return {abort: function () {
				//取消正在上传
	    	   var jqxhr = $(form).data('jqxhr');
	    	   if (jqxhr) 
	    		   jqxhr.abort();
	    	   
	    	   clearFrameAndForm();
			}};
       },
   });
})(jQuery, window);