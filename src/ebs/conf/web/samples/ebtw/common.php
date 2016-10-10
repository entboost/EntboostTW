<?php
require_once dirname(__FILE__).'/string.php';

/**
 * 对象转数组,使用get_object_vars返回对象属性组成的数组
 * @param {object|mixed} $obj
 * @return array
 */
function objectToArray($obj) {
	$arr = is_object($obj)?get_object_vars($obj):$obj;
	if(is_array($arr)){
		return array_map(__FUNCTION__, $arr);
	}else{
		return $arr;
	}
}

/**
 * 创建目录函数
 * @param string $dir 目录绝对路径
 */
function mkdirs($dir)
{
	if(!is_dir($dir)){
		if(!mkdirs(dirname($dir))){
			exit('不能创建目录');}
			if(!mkdir($dir,0777)){
				exit('不能创建目录2');}
	}
	return true;
}

/**
 * 判断变量值是否数字
 * @param mixed $var
 * @return boolean
 */
function var_is_digit($var) {
	if (!isset($var))
		return false;
	if (is_numeric($var))
		return true;
// 	ctype_digit($str);
	if (is_string($var)) {
		$matches = preg_match('/^(-?\d+[.]?\d+)$/i', $var);
		if(empty($matches))
			return false;
		return true;
	}
	return false;
}

/**
 * 验证不通过时的处理
 * @param {mixed} $variable 变量
 * @param {string} $outErrMsg 输出参数 错误信息
 * @param {string} $extendMsg 附加描述
 * @param {mixed} $variableValue 变量值
 * @param {string} $variableName 变量名
 * @return boolean
 */
function validFailure($variable, &$outErrMsg, $extendMsg, $variableValue=NULL, $variableName=NULL) {
	$msg = (isset($variableName)?("variable \"".$variableName."\"'s "):'').'value =' . (isset($variableValue)?(is_array($variableValue)?'variableValue Array':$variableValue):(is_array($variable)?'variable Array':$variable)) .(!empty($extendMsg)?( ', '.$extendMsg):'');
	//log_err($msg);
	$outErrMsg = $msg;
	return false;
}

/**
 * 验证变量数字不通过时的处理
 * @param {mixed} $variable 变量
 * @param {string} $outErrMsg 输出参数 错误信息
 * @param {mixed} $variableValue 变量值
 * @param {string} $variableName 变量名
 * @return boolean
 */
function checkDigitFailure($variable, &$outErrMsg, $variableValue=NULL, $variableName=NULL) {
	return validFailure($variable, $outErrMsg, 'is not a digit', $variableValue, $variableName);
// 	$msg = (isset($variableName)?('variable '.$variableName.'\'s '):'').'value =' . (isset($variableValue)?$variableValue:$variable) . ', ' . 'is not a digit';
// 	log_err($msg);
// 	$outErrMsg = $msg;
// 	return false;
}

/**
 * 验证变量非空不通过时的处理
 * @param {mixed} $variable 变量
 * @param {string} $outErrMsg 输出参数 错误信息
 * @param {mixed} $variableValue 变量值
 * @param {string} $variableName 变量名
 * @return boolean
 */
function validNotEmptyFailure($variable, &$outErrMsg, $variableValue=NULL, $variableName=NULL) {
	return validFailure($variable, $outErrMsg, 'is empty', $variableValue, $variableName);
}

/**
 * 对数组进行深层复制
 * @param array $source 源数组
 * @param array $outDest 新数组
 */
function array_deepclone(array $source, &$outDest) {
	if (empty($source))
		return NULL;
	
	$outDest = $source;
	foreach ($outDest as $key=>&$value) {
		if (is_array($value))
			array_deepclone($value, $value);
		else if(is_object($value)) {
			$value = clone $value;
		}
	}
}

/**
 * 预定义的字符转换为HTML实体
 * @param string $string
 * @return string 转换后的字符串
 */
function my_htmlspecialchars($string) {
	return htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8', TRUE);
}

/**
 * 从request获取指定参数值，并自动处理HTML特殊字符
 * @param {string} $paramName 参数名
 * @param {mixed} $defaultValue (可选) 默认值，指定参数名不存在时返回的值，可以是任意类型
 * @return {string|array|mixed} 参数值
 */
function get_request_param($paramName, $defaultValue=NULL) {
	$str = @$_REQUEST[$paramName];
	
	if (isset($str)) {
		if (is_array($str)) {
			$results = array();
			foreach($str as $element) {
				array_push($results, my_htmlspecialchars($element));
			}
			return $results;
		}
		return my_htmlspecialchars($str);
	}
	if (isset($defaultValue))
		return $defaultValue;
	else
		return;
}

/**
 * 文件同步锁
 */
class File_Lock
{
	private $name;

	private $handle;

	private $mode;

	function __construct($filename, $mode = 'a+b') {
		global $php_errormsg;
		$this->name = $filename;
		$path = dirname($this->name);
		if ($path == '.' || !is_dir($path)) {
			global $config_file_lock_path;
			$this->name = str_replace(array("/", "\\"), array("_", "_"), $this->name);
			if ($config_file_lock_path == null) {
				$this->name = dirname(__FILE__) . "/lock/" . $this->name;
			} else {
				$this->name = $config_file_lock_path . "/" . $this->name;
			}
		}
		//如果目录不存在，则创建
		mkdirs(dirname($this->name));
		
		$this->mode = $mode;
		
		$this->handle = @fopen($this->name, $mode);
		
// 		$handle = fopen('c:/123.txt', 'a');
// 		fwrite($handle, 'File_Lock __construct handle:'.$this->handle. chr(13));
		
		if ($this->handle == false) {
			throw new Exception($php_errormsg);
		}
	}

	public function close()
	{
		if ($this->handle !== null ) {
// 			$handle = fopen('c:/123.txt', 'a');
// 			fwrite($handle, 'close handle:'.$this->handle. chr(13));
			
			@fclose($this->handle);
			$this->handle = null;
		}
	}

	public function __destruct()
	{
// 		$handle = fopen('c:/123.txt', 'a');
// 		fwrite($handle, 'File_Lock __destruct:'.$this->handle. chr(13));
		
		$this->close();
	}

	public function lock($lockType, $nonBlockingLock = false)
	{
		if ($nonBlockingLock) {
			return flock($this->handle, $lockType | LOCK_NB);
		} else {
			return flock($this->handle, $lockType);
		}
	}

	public function readLock()
	{
		return $this->lock(LOCK_SH);
	}

	public function writeLock($wait = 20.0)
	{
		$startTime = microtime(true);
		$canWrite = false;
		$maxTry = 500;
		$i = 0;
		do {
// 			$handle = fopen('c:/123.txt', 'a');
// 			fwrite($handle, 'handle:'.$this->handle. chr(13));
			//fwrite($handle, 'want to fopen:'.$this->name. chr(13));
			
			$canWrite = flock($this->handle, LOCK_EX);
			if(!$canWrite) {
				usleep(rand(10, 1000));
			}
			$i++;
		} while ((!$canWrite) && ((microtime(true) - $startTime) < $wait) && $i<$maxTry);
	}

	/**
	 * if you want't to log the number under multi-thread system,
	 * please open the lock, use a+ mod. then fopen the file will not
	 * destroy the data.
	 *
	 * this function increment a delt value , and save to the file.
	 *
	 * @param int $delt
	 * @return int
	 */
	public function increment($delt = 1)
	{
		$n = $this->get();
		$n += $delt;
		$this->set($n);
		return $n;
	}

	public function get()
	{
		fseek($this->handle, 0);
		return (int)fgets($this->handle);
	}

	public function set($value)
	{
		ftruncate($this->handle, 0);
		return fwrite($this->handle, (string)$value);
	}

	public function unlock()
	{
		if ($this->handle !== null ) {
			return flock($this->handle, LOCK_UN);
		} else {
			return true;
		}
	}
}
