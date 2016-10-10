//写入普通日志
function logjs_info(msg) {
	if (console) console.log(msg);
}
//写入错误日志
function logjs_err(msg) {
	if (console) console.log(msg);
}

//判断数据是否类型空值(未定义或空值)
function isTypeEmpty(data) {
	return (data===undefined || data===null);
}

//打印输出对象属性(方便调试)
function alertObject(obj) {
    var description = ""; 
    for(var i in obj){   
        var property=obj[i];   
        description+=i+" = "+property+"\n";  
    }
    alert(description); 
}
//打印输出对象(Json格式)
function alertObjectToJson(obj) {
	alert(JSON.stringify(obj));
}

//获取触发事件元素的坐标和尺寸
function getAbsPoint(e) {
    var x = e.offsetLeft;
    var y = e.offsetTop;
    var w = e.offsetWidth;
    var h = e.offsetHeight; 
    
    while(e=e.offsetParent) {
    	x += e.offsetLeft;  
    	y += e.offsetTop
    }
    //alert("x:"+x+","+"y:"+y);
    return [x, y, w, h];
}

//控制字符对照表
var ControlKeyMap = {
	8: 	{name:"Backspace",  local:"退格"},
	9: 	{name:"Tab",  local:"制表"},
	13: {name:"Enter", local:"回车"},
	32: {name:"Space", local:"空格"},
	33: {name:"PageUp", local:"上一页"},
	34: {name:"PageDown", local:"下一页"},
	35: {name:"End", local:"结尾"},
	36: {name:"Home", local:"开头"},
	37: {name:"Left", local:"向左"},
	38: {name:"Up", local:"向上"},
	39: {name:"Right", local:"向右"},
	40: {name:"Down", local:"向下"},
	46: {name:"Del", local:"删除"},
};

//控制字符
function keyOfEvent(e) {
	var e = e||event;
	var currKey = e.keyCode||e.which||e.charCode;
	return currKey;
//	if((currKey>7&&currKey<14)||(currKey>31&&currKey<47)) {
//		return true;
////		switch(currKey) {
////			case 8: keyName = "Backspace"; //退格 
////			break;
////			case 9: keyName = "Tab"; //制表 
////			break;
////			case 13:keyName = "Enter"; //回车 
////			break;
////			case 32:keyName = "Space"; //空格 
////			break;
////			case 33:keyName = "PageUp"; 
////			break;
////			case 34:keyName = "PageDown"; 
////			break;
////			case 35:keyName = "End"; 
////			break;
////			case 36:keyName = "Home";
////			break;
////			case 37:keyName = "Left"; //方向键左 
////			break;
////			case 38:keyName = "Up"; //方向键上 
////			break;
////			case 39:keyName = "Right"; //方向键右 
////			break;
////			case 40:keyName = "Down"; //方向键下 
////			break;
////			case 46:keyName = "Del"; //删除 
////			break;
////		}
//	}
//	return false;
}

/**
 * 消除<div>标签，并在标签原位置前插入替代字符串
 * @param htmlSrc {String} 源html字符串
 * @param replaceValue {String} 替代字符串(不允许使用"<div>"标签)
 * @returns {String}
 */
function replaceDivTag(htmlSrc, replaceValue) {
    if(!htmlSrc) return null;
    //var regexp = /<div[ ]*[^<>]*\/>|<div[ ]*>[^<>]*?<\/div>/i;
    var regexp = new RegExp("<div\\s*[^<>]*>([^<>]*?(<img\\s*[^<>]*?[\/]*>+[^<>]*(<\/img>)*)*?)*?</div>", "i");
    var retHtml =[];
    //alert($.toJSON(arry)+arry.length);

    var index = -1;
    var arrMactches = null;
    var temp ="";
    while(htmlSrc.length) {
        index =htmlSrc.search(regexp);
        arrMactches = htmlSrc.match(regexp);
        if(index >=0) {
            if(index!==0)
                retHtml.push(htmlSrc.substring(0, index));

            retHtml.push("\n");
            temp = htmlSrc.substring(index, index + arrMactches[0].length);
            temp = $(temp).html();
            //alert("html="+temp);
            retHtml.push(temp);

            htmlSrc =htmlSrc.substring(index + arrMactches[0].length);
        }
        else {
            retHtml.push(htmlSrc);
            htmlSrc ="";
        }
    }
    //alert(retHtml.join(""));
    return retHtml.join("");
}

/**
 * html格式文本转换为普通文本格式
 * @param html html格式的文本
 * @returns 处理完毕的普通文本
 */
function convertHtmlToTxt(html) {
    //处理掉全是换行标签的情况
    var trimContent = html.replace(/<br>/gmi,'\n');
    if($.trim(trimContent).length === 0) {
        return '';
    }
    //处理掉空白的div标签
    trimContent = trimContent.replace(/<div>\s*<\/div>|<div\s*\/>/gmi, '\n');
    
    //处理特殊字符
    trimContent = trimContent.replace(new RegExp('<br>', 'gmi'), '\n');
    trimContent = trimContent.replace(new RegExp('&nbsp;', 'gmi'), ' ');

    trimContent = replaceDivTag(trimContent, '\n');
    trimContent = $.trim(trimContent);
    
    return trimContent;
}

//控制字符转换为html标签
function controlCharactersToHtml(str) {
	if (typeof str !='string')
		return;
	var str = str.replace(/([\r\n])+/ig, '<br>');
	return str;
}

//光标移到到尾部，用于input、textarea
function cursorMoveToLastInEditor(element) {
    obj.focus();//解决ff不获取焦点无法定位问题
    if (window.getSelection) {//ie11 10 9 ff safari
        var max_Len=element.value.length;//text字符数
        element.setSelectionRange(max_Len, max_Len);
    } else if (document.selection) {//ie10 9 8 7 6 5
        var range = element.createTextRange();//创建range
        range.collapse(false);//光标移至最后
        range.select();//避免产生空格
    }
}

//光标移到到尾部，用于div(contenteditable="true")
function cursorMoveToLastInDiv(element) {
    if (window.getSelection) {//ie11 10 9 ff safari
    	element.focus(); //解决ff不获取焦点无法定位问题
        var range = window.getSelection();//创建range
        range.selectAllChildren(element);//range 选择obj下所有子内容
        range.collapseToEnd();//光标移至最后
    } else if (document.selection) {//ie10 9 8 7 6 5
        var range = document.selection.createRange();//创建选择对象
        //var range = document.body.createTextRange();
        range.moveToElementText(element);//range定位到obj
        range.collapse(false);//光标移至最后
        range.select();
    }
}

/**
 * 获取form表单序列化结果
 * （该函数用于封装Jquery的serialize()函数，解决"+"加号不正常的问题）
 * @param {object} $form 表单对象(JQuery对象)
 * @returns 序列号后的字符串
 */
//function serializeForm($form) {
//	var a = $form.serialize();
//	logjs_info(a);
//	var b = a.replace(/\\+/g,' ');
//	logjs_info(b);
//	return b;
//}

/**
 * 获取字符串字节长度
 * @param {string} sourceStr 待计算字符串
 * @return {number} 字节数
 */
function getStringBytesLength(sourceStr) {
	if (typeof sourceStr!='string' || sourceStr.length==0)
		return 0;
	
    var str = escape(sourceStr);
    for(var i = 0, length = 0;i < str.length; i++, length++) {
	    if(str.charAt(i) == "%") {
		    if(str.charAt(++i) == "u") {
			    i += 3;
			    length++;
		    }
	    	i++;
	    }
    }
    return length;
}

/**
 * 通俗化翻译字节数量
 * @param {number} size 字节数
 * @return {string}
 */
function popularByteSize(size) {
	size = parseInt(size);
	if (size==0)
		return '0字节';
	
	if (size>=1024*1024) {
		return (size/(1024*1024)).toFixed(2)+ 'MB';
	}
	if (size>=1024) {
		return (size/1024).toFixed(2) + 'KB';
	}
	return size + '字节';
}

/**
 * 获取等待上传的本地文件大小(字节数)
 * @param {object} fileInputObject 文件选择控件对象
 * @return {number} 文件大小
 */
function localUploadFileSize(fileInputObject) {
	if (checkIsIE() && !fileInputObject.files) { //IE浏览器
		var filePath = fileInputObject.value; //获得上传文件的绝对路径
		var fileSystem = new ActiveXObject("Scripting.FileSystemObject");
		var file = fileSystem.GetFile(filePath);
		return file.Size; //文件大小，单位：b
	} else {
		return fileInputObject.files[0].size;
	}
}

//识别当前浏览器是否IE
function checkIsIE() {
	return /msie/i.test(navigator.userAgent) && !window.opera;
}
