/**
 * 一些特殊扩展
 * special-extend.js
 */

//扩展setTimeout全局函数
var __sto = setTimeout;
window.setTimeout = function(callback, timeout, param) {
	var args = Array.prototype.slice.call(arguments, 2); 
	var _cb = function() {
		callback.apply(null, args);
	}
	return __sto(_cb, timeout);
}
