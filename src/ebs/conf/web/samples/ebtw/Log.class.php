<?php
/**
 * 日志处理类
 */
 
class Log
{
	//单例模式
	private static $instance    = NULL;
	//文件句柄
	private static $handle      = NULL;
	//日志开关
	private $log_switch     	= NULL;
	//日志相对目录
	private $log_file_path      = NULL;
	//日志文件最大长度，超出长度重新建立文件
	private $log_max_len        = NULL;
	//日志文件前缀,入 log_0
	private $log_file_pre       = NULL;
	//日志文件扩展名
	private $log_file_extend_name;
	//日志文件锁
	private $lock;
	
	/**
	 * 构造函数
	 */
	function __construct()
	{
		//注意：以下是配置文件中的常量
		$this->log_file_path     = LOG_FILE_PATH . '/';
		$this->log_file_pre		= LOG_FILE_PRE;
		$this->log_switch     = LOG_SWITCH;
		$this->log_max_len    = LOG_MAX_LEN;
		
		$this->lock = new File_Lock("LogFile.lock");
		$this->log_file_extend_name = '.txt';
		
// 		$handle = fopen('c:/123.txt', 'a');
// 		fwrite($handle, 'Log __construct:'. chr(13));		
	}
	
	/**
	 * 析构函数
	 */
	function __destruct() 
	{
// 		$handle = fopen('c:/123.txt', 'a');
// 		fwrite($handle, 'Log __destruct:'. chr(13));
		
		$this->close();
// 		parent::__destruct();
	}
	
	/**
	 * 单例模式
	 */
	public static function get_instance()
	{
		if(!self::$instance instanceof self){
			self::$instance = new self;
		}
		return self::$instance;
	}
	 
	/**
	 * 日志记录
	 *
	 * @param int $type  0 -> 记录(INFO LOG) / 1 -> 错误(ERROR LOG)
	 * @param string $desc
	 * @param string $time
	 */
	public function log($type, $desc, $time)
	{
		if($this->log_switch){
			
			$this->lock->writeLock();
			
			if(self::$handle == NULL){
				$filename = $this->log_file_pre . $this->get_max_log_file_suf() . $this->log_file_extend_name;
				$filepath = $this->log_file_path . $filename;
				
				//目录不存在就创建
				$dir_name=dirname($filepath);
				if(!file_exists($dir_name))
					mkdirs($dir_name);
				
				self::$handle = fopen($filepath, 'a');
			}
			
			switch($type){
				case 0:
					fwrite(self::$handle, $time. ' INFO:' . $desc. chr(13));
					break;
				case 1:
					fwrite(self::$handle, $time. ' ERR:' . $desc. chr(13));
					break;
				default:
					fwrite(self::$handle, $time. ' INFO:' . $desc. chr(13));
					break;
			} 
			
			$this->lock->unlock();
		}
	}
	 
	/**
	 * 获取当前日志的最新文档的后缀
	 */
	private function get_max_log_file_suf()
	{
		$log_file_suf = null;
		if(is_dir($this->log_file_path)){
			if($dh = opendir($this->log_file_path)){
				while(($file = readdir($dh)) != FALSE){
					if($file != '.' && $file != '..'){
						if(filetype($this->log_file_path . $file) == 'file'){
// 							$rs = split('_', $file);
							$rs = explode('_', str_replace($this->log_file_extend_name, '', $file));
							if($log_file_suf < $rs[count($rs)-1]){
								$log_file_suf = $rs[count($rs)-1];
							}
						}
					}
				}
				 
				if($log_file_suf == NULL){
					$log_file_suf = 0;
				}
				//截断文件
				$file_path = $this->log_file_path . $this->log_file_pre . $log_file_suf . $this->log_file_extend_name;
				if( file_exists($file_path) && filesize($file_path) >= $this->log_max_len){
					$log_file_suf = intval($log_file_suf) + 1;
				}
				 
				return $log_file_suf;
			}
		}
		 
		return 0;
	}
	 
	/**
	 * 关闭文件句柄
	 */
	private function close()
	{
		//$this->lock->writeLock(); //这个地方不必加锁(不会出现并发情况)，而且加锁以后有些情况下会报错(lock对象比log对象先销毁)
		if (self::$handle!=NULL) {
			fclose(self::$handle);
			self::$handle = NULL;
		}
		//$this->lock->unlock();
	}
}

/**
 * 写入日志
 * @param string|object $message 内容
 * @param int $level 日志级别，0代表普通记录，1代表错误记录
 */
function clog_usinglevel($message, $level=0) {
	$L = Log::get_instance();
	
	if (is_array($message))
		$message = "Array:".json_encode($message);
	else if (is_object($message))
		$message = "Class ".get_class($message).":".json_encode($message);
	
	$L->log($level, $message, date('Y-m-d H:i:s'));
}

/**
 * 写入普通日志
 * @param string|object $message 内容
 */
function log_info($message) {
	clog_usinglevel($message, 0);
}

/**
 * 写入错误日志
 * @param string|object $message 内容
 */
function log_err($message) {
	clog_usinglevel($message, 1);
}

