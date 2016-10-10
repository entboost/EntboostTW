<?php
require_once dirname(__FILE__).'/SQLEntitys.php';

/**
 * SQL基础服务类
 */
abstract class AbstractService
{
// 	protected static $instance  = NULL;
	
	protected  $apAcc;
	protected  $connected;
	
	/**
	 * 数据库表名称
	 * @var string
	 */
	public $tableName;
	
	/**
	 * 数据库字段名称字符串(逗号分隔)
	 * @var string
	 */
	public $fieldNames;
	
	/**
	 * 主键字段名称
	 * @var string
	 */
	public $primaryKeyName;
	
	function __construct() {
		$this->apAcc = EBAPServerAccessor::get_instance();
		$this->connected = $this->apAcc->validAccessIMSession();
	}
	
// 	static function get_instance($className) {
// 		if(self::$instance==NULL) {
// 			$ref = new ReflectionClass($className);
// 			self::$instance = $ref->newInstance();
// 		}
// 		return self::$instance;
// 	}
// 	static function get_instance() {
// 		if(static::$instance==NULL) {
// 			$ref = new ReflectionClass(__CLASS__);
// 			static::$instance = $ref->newInstance();
// 		}
// 		return static::$instance;
// 	}
	
	/**
	 * 检测连接是否正常
	 * @return boolean
	 */
	protected function checkConnect() {
		if (!$this->connected) {
			$this->$connected = $this->apAcc->validAccessIMSession();
		}
		return $this->connected;
	}
	
	/**
	 * 创建查询SQL前段
	 * @param $fieldNames 默认NULL
	 * @return boolean|string 验证错误或sql前段
	 */
	protected function createPrefixOfSearchSql($fieldNames=NULL) {
		if (!empty($fieldNames) && !is_string($fieldNames)) {
			log_err('createPrefixOfSearchSql error, $fieldNames type is not string');
			return false;
		}
		return 'select ' . (empty($fieldNames)?$this->fieldNames:$fieldNames) .' from ' . $this->tableName;
	}
	
	/**
	 * 创建查询数量SQL前段
	 * @return string sql前段
	 */
	protected function createPrefixOfSearchForCountSql() {
		return 'select count(1) as record_count from ' . $this->tableName;
	}
			
	/**
	 * 创建插入SQL前段
	 */
	protected function createPrefixOfInsertSql() {
		return 'insert into ' . $this->tableName;
	}
	
	/**
	 * 创建更新SQL前段
	 * @return string
	 */
	protected function createPrefixOfUpdateSql() {
		return 'update ' . $this->tableName . ' set';
	}
	
	/**
	 * 创建删除SQL前段
	 * @return string
	 */
	protected function createPrefixOfDeleteSql() {
		return 'delete from ' . $this->tableName;
	}
	
	/**
	 * 处理sql语句末尾的and和 where字符
	 * @param string $sql
	 * @param string $outLastStr 输出末尾符合剔除规则的字符串
	 * @return string
	 */
	static function rTrimSql($sql, &$outLastStr=NULL) {
		$len = utf8_strlen($sql);
			
		//去掉多余的where
		$str_s = c_substr($sql, $len-5, 5);
		if ($str_s=='where') {
			$sql = c_substr($sql, 0, $len-5);
			if (isset($outLastStr))
				$outLastStr = $str_s;
		}
		
		//去掉多余的and
		$str_s = c_substr($sql, $len-3, 3);
		if ($str_s=='and') {
			$sql = c_substr($sql, 0, $len-3);
			if (isset($outLastStr))
				$outLastStr = $str_s;
		}
		
		//去掉多余的or
		$str_s = c_substr($sql, $len-2, 2);
		if ($str_s=='or') {
			$sql = c_substr($sql, 0, $len-2);
			if (isset($outLastStr))
				$outLastStr = $str_s;
		}
			
		//去掉多余的逗号(,)
		$str_s = c_substr($sql, $len-1, 1);
		if ($str_s==',') {
			$sql = c_substr($sql, 0, $len-1);
			if (isset($outLastStr))
				$outLastStr = $str_s;
		}
			
		return $sql;
	}
	
	/**
	 * 在最后一个and或or字符前插入右括号 
	 * @param string $str
	 */
	static function rightBracket(&$str) {
		$len = utf8_strlen($str);
		
		//and
		$str_s = c_substr($str, $len-3, 3);
		if ($str_s=='and') {
			$str = c_substr($str, 0, $len-3);
			$str .= ') and';
		}

		//or
		$str_s = c_substr($str, $len-2, 2);
		if ($str_s=='or') {
			$str = c_substr($str, 0, $len-2);
			$str .= ') or';
		}
	}
	
	/**
	 * 在字段前插入表别名前缀
	 * @param array|string $target 等待加前缀的目标对象
	 * @param string $prefix 表别名前缀
	 * @param string (可选) $exclude 除外 (仅对$target是字符串类型时有效)
	 * @return array|string 加前缀完毕的目标对象
	 */
	static function insertTableNameAliasPrefix($target, $prefix, $exclude=NULL) {
		if (empty($target))
			return;
		
		if (is_array($target)) { //数组
			$result = array();
			
			foreach($target as $key=>$sValue/*&$value*/) {
				if (!var_is_digit($key)) {
					if (is_object($sValue)) {
						//复制一个新对象，重要！ SQLParamComb已经实现深复制
						$value = clone $sValue;
					} else {
						$value = $sValue;
					}
					
					if ($value instanceof SQLParamComb) {
						$value->sqlParams = self::insertTableNameAliasPrefix($value->sqlParams, $prefix);
					} else {
						if (is_array($value)) { //处理同一个字段多种查询条件值的情况
							foreach ($value as $p/*&$p*/) {
								if (($p instanceof SQLParam) && !empty($p->name)) {
									$p->name = $prefix . $p->name;
								}
							}
						} else {
							if (($value instanceof SQLParam) && !empty($value->name)) {
								$value->name = $prefix . $value->name;
							}
						}
					}
					
					$result[$prefix.$key] = $value;
				} else {
					if ($sValue instanceof SQLParamComb) {
						if (is_object($sValue)) {
							//复制一个新对象，重要！ SQLParamComb已经实现深复制
							$value = clone $sValue;
						} else {
							$value = $sValue;
						}
						
						$value->sqlParams = self::insertTableNameAliasPrefix($value->sqlParams, $prefix);
						array_push($result, $value);
					} else {
						array_push($result, $prefix.$sValue);
					}
				}
			}
		} else if (is_string($target)) { //字符串
			$result = '';
			$arry = preg_split('/[,]+/', $target);
			foreach ($arry as $value) {
				if ($value==$exclude || $value==$exclude.' asc' || $value==$exclude.' desc')
					continue;
				$result = $result.$prefix.trim($value).',';
			}
			$result = substr($result, 0, strlen($result)-1);
		}
		
		return $result;
	}
	
	/**
	 * 检测参数值(字符串)是否数字
	 * @param {mixed} $variable 待检测变量
	 * @param {string} $outErrMsg 输出参数 错误信息
	 * @return boolean true=通过，false=不通过
	 */
	static function checkDigitParam($variable, &$outErrMsg, $variableName=NULL) {
// 		$result = var_is_digit($variable);
		if (!var_is_digit($variable)) {
// 			$msg = '\'' . $str . '\'' . 'is not a digit';
// 			log_err($msg);
// 			$outErrMsg = $msg;
			return checkDigitFailure($variable, $outErrMsg, $variable, $variableName);
		}
		return true;
// 		return $result;
	}
	
	/**
	 * 去除某些字段名
	 * @param array $someFieldNames	将被去除的多个字段名，例如：array('a', 'b')
	 * @param string $outFieldNames 字段字符串，例如：'a, b, c, d...'
	 */
	static function removeSomeFieldNames(array $someFieldNames, &$outFieldNames) {
		//在最后位置追加一个逗号
		$outFieldNames .= ',';
		//遍历删除匹配字段
		foreach ($someFieldNames as $fieldName) {
			$fieldName .= ',';
			$outFieldNames = preg_replace('/'.$fieldName.'( )?/iu', '', $outFieldNames);
		}
		
		$outFieldNames = rtrim($outFieldNames);
		//删除最后一个字符
		if (strlen($outFieldNames) > 0)
			$outFieldNames = substr($outFieldNames, 0, -1);
	}
	
	/**
	 * 获取去除某些字段名后剩下的字段名
	 * @param array $someFieldNames 将被去除的多个字段名，例如：array('a', 'b')
	 * @return string 剩余字段名
	 */
	function fieldNamesAfterRemovedSome($someFieldNames=NULL) {
		if (empty($someFieldNames))
			return $this->fieldNames;
		
		$fieldNames = $this->fieldNames;
		self::removeSomeFieldNames($someFieldNames, $fieldNames);
		return $fieldNames;
	}
	
	/**
	 * 处理order by排序语句
	 * @param string $orderBy
	 * @param string $sql
	 */
	static protected function orderBy($orderBy, &$sql) {
		if (!empty($orderBy)) {
			$sql = $sql. ' order by ' . $orderBy;
		}
	}
	
// 	/**
// 	 * 处理where条件
// 	 * @param string $key
// 	 * @param string $name
// 	 * @param array $params
// 	 * @param string $outWhere 输出where语句
// 	 * @param array $outConditions 输出对应值
// 	 * @param boolean $checkDigit， 是否检测数字合法性，默认=false不检测
// 	 * @return integer  true:处理成功；false:处理失败
// 	 */
// 	function processWhere($key, $name, &$params, &$outWhere, &$outConditions, $checkDigit=false) {
// 		if (array_key_exists($key, $params)) {
// 			$result = $this->contactParamOfQuery($name, $params[$key], $outWhere, $outConditions);
// 			if ($result==0)
// 				return false;
// 		}
// 		return true;
// 	}
	
	/**
	 * 拼接where语句
	 * @param array $querys
	 * @param string $outWhere 输出where语句
	 * @param array $outConditions 输出对应值
	 * @param array $checkDigits
	 * @param string $whereType where条件的拼接类型，见SQLParamComb::$TYPE_AND和SQLParamComb::$TYPE_OP，默认SQLParamComb::$TYPE_AND
	 * @param boolean $isRecursive 当前是否递归调用，默认false
	 * @param string $outErrMsg 输出检测错误信息
	 * @return boolean 处理结果
	 */
	function processWheres(array &$querys, &$outWhere, array &$outConditions, $checkDigits=NULL, $whereType=SQLParamComb_TYPE_AND, $isRecursive=false, &$outErrMsg=NULL) {
		if (empty($outWhere))
			$outWhere = ' where';
		
		if (!empty($querys) && count($querys)>0) {
			foreach ($querys as $key=>$value) {
				if ($value instanceof SQLParamComb) {
					$outWhere = $outWhere . ' (';
					if (!$this->processWheres($value->sqlParams, $outWhere, $outConditions, $checkDigits, $value->type, true, $outErrMsg))
						return false;
					$outWhere = $outWhere . ' ' . $whereType;
					self::rightBracket($outWhere);
				} else {
					//拼接查询条件
					$param = $querys[$key];
					$name = $key;
					
					if (is_array($param)) { //处理同一个字段多种查询条件值的情况
						foreach ($param as $p) {
							if (($p instanceof SQLParam) && !empty($p->name)) {
								$name = $p->name;
							}
							
							if ($this->contactParamOfQuery($name, $p, $outWhere, $outConditions, (isset($checkDigits)&&in_array($key, $checkDigits))?true:false, $whereType, $outErrMsg)==0)
								return false;
						}
					} else {
						if (($param instanceof SQLParam) && !empty($param->name)) {
							$name = $param->name;
						}
						
// 						echo $key, ' ';
// 						var_dump($checkDigits);
// 						echo "<br>";
						if ($this->contactParamOfQuery($name, $param, $outWhere, $outConditions, (isset($checkDigits)&&in_array($key, $checkDigits))?true:false, $whereType, $outErrMsg)==0)
							return false;
					}
				}
			}
		}
		
// 		if (!$isRecursive) {
			//去掉最后一个多余的where或and或or
			$outWhere = $this->rTrimSql($outWhere);
// 		}
		return true;
	}
	
	/**
	 * 拼接SET语句
	 * @param array $nameValues 名称和值的数组
	 * @param array $outSetStr SET语句输出
	 * @param array $outValues SET语句对应值输出
	 * @param array $checkDigits 数字检测开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param string $outErrMsg 输出检测错误信息
	 * @return boolean
	 */
	protected function processSets(&$nameValues, &$outSetStr, &$outValues, $checkDigits=NULL, &$outErrMsg) {
		foreach ($nameValues as $key=>$value) {
			//数字检测
			if ($checkDigits!=NULL && in_array($key, $checkDigits)/* array_key_exists($key, $checkDigits) && is_string($value)*/) {
				if (!$this->checkDigitParam($value, $outErrMsg, $key))
					return false;
			}
			
			$outSetStr = $outSetStr . ' ' . $key . '=?,';
			array_push($outValues, $value);
		}
		
		//去掉最后一个逗号
		$outSetStr = $this->rTrimSql($outSetStr);
		return true;
	}
	
	
	/**
	 * 处理Insert语句
	 * @param array $nameValues 插入字段名称 和 值
	 * @param string $primaryKeyName 主键名称
	 * @param string $outSql 输出 	SQL语句
	 * @param array $outValues 输出		插入值数组
	 * @param array $checkDigits 数字检测开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param string $outErrMsg 输出检测错误信息
	 * @return string 主键，等于0表示数字检测发送错误
	 */
	protected function processInsert(&$nameValues, $primaryKeyName, &$outSql, &$outValues, $checkDigits, &$outErrMsg) {
		$outSql = $this->createPrefixOfInsertSql();
		$bigId = $this->apAcc->nextBigId();
		
		if ($bigId!=='0') {
			if (!empty($primaryKeyName)) {
				$nameValues[$primaryKeyName] = $bigId;
				
				//强制对主键进行数字校验
				if (!empty($checkDigits) && is_array($checkDigits) && !in_array($primaryKeyName, $checkDigits))
					array_push($checkDigits, $primaryKeyName);
			}
			
			if (!$this->contactParamsOfInsert($nameValues, $outSql, $outValues, $checkDigits, $outErrMsg))
				$bigId = '0';
		}
		
		return $bigId;
	}
	
	/**
	 * query参数拼接处理
	 * @param string $name
	 * @param SQLParam|mixed $param
	 * @param string $where
	 * @param array $conditions 查询条件数组(已和问号占位符匹配)，例如array(123, 'absdfd')
	 * @param boolean $checkDigit， 是否检测数字合法性，默认=false不检测
	 * @param string $whereType where条件的拼接类型，见SQLParamComb::$TYPE_AND和SQLParamComb::$TYPE_OP，默认SQLParamComb::$TYPE_AND
	 * @param string $outErrMsg 输出检测错误信息
	 * @return int  当等于0表示数字检测不通过，当等于1表示参数是空对象，当等于2表示非SQLParam结构处理，当等于3表示SQLParam结构处理
	 */
	protected function contactParamOfQuery($name, $param, &$where, &$conditions, $checkDigit=false, $whereType=SQLParamComb_TYPE_AND, &$outErrMsg=NULL) {
		$result = 1;
		if (!is_bool($param) && !isset($param)) {
			return $result;
		}
		
		$where = $where . ' ' . $name . ' ';
		
		if ($param instanceof SQLParam) {
			$value = $param->value;
			
// 			if ($param->name =='completed_work') {
// 				echo $checkDigit?'true':'false',"\n";
// 				var_dump($param);
// 			}
			//检测数字合法性
			if (/*is_string($value) */$checkDigit && !$this->checkDigitParam($value, $outErrMsg, $param->name))
				return 0;
			
			$where = $where . $param->op . ' ? ' . $whereType;
			$result = 3;
		} else {
			$value = $param;
			
			//检测数字合法性
			if (/*is_string($value) */$checkDigit && !$this->checkDigitParam($value, $outErrMsg, $name))
				return 0;
			
			$where .= '= ? ' . $whereType;
			$result = 2;
		}
		
		array_push($conditions, $value);
		return $result;
	}
	
	/**
	 * insert参数拼接处理
	 * @param array $nameValues 插入字段名称 和 值
	 * @param string $outSql 输出SQL语句
	 * @param array $outValues 输出插入值数组
	 * @param array $checkDigits 数字检测开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param string $outErrMsg 输出检测错误信息
	 * @return boolean true:数字检测通过，false:数字检测不通过
	 */
	protected function contactParamsOfInsert(&$nameValues, &$outSql, &$outValues, $checkDigits, &$outErrMsg) {
		$outSql .= ' (';
		$mark = ' (';
		
		$i = 0;
		foreach ($nameValues as $key=>$value) {
			if ($checkDigits!=NULL && in_array($key, $checkDigits)/*array_key_exists($key, $checkDigits)*/ /*&& is_string($value)*/) {
				if (!$this->checkDigitParam($value, $outErrMsg, $key))
					return false;
			}
			
			$outSql = $outSql . $key . ',';
			$mark = $mark . '?,';
			$outValues[$i] = $value;
			$i++;
		}
		
		$mark = chop($mark, ',');
		$mark .= ')';
		$outSql = chop($outSql, ',');
		$outSql .= ')';
		$outSql = $outSql . ' values ' . $mark;
		
		return true;
	}
	
	/**
	 * 限制必须有where条件，防止操作权限过大
	 * @param string $sql
	 * @return boolean
	 */
	protected function mustHaveWhere($sql) {
		if (!is_string($sql)) {
			log_err('mustHaveWhere error, $sql is not a string');
			return false;
		}
		
		if (stripos($sql, ' where ')===false)
			return false;
		return true;
	}
	
	/**
	 * 表连接查询
	 * @param array $tableNameAlias 表别名数组，例如：array('表A'=>'t_a', '表B'=>'t_b', '表C'=>'t_c')
	 * @param string $prefixSql SQL语句前段
	 * @param array $conditions 查询条件数组(已和问号占位符匹配)，例如array(123, 'absdfd')
	 * @param array $paramsGroup 查询条件数组，与$tableNameAlias一一对应
	  	例如： array( '表A'=>array(
	 	 'a'=>123,
	 	 'b'=>'文档xx',
	 	 'c'=>new SQLParam('防fd', 'c', 'like'),
	 	 'time_s'=>SQLParam('2016-01-01 00:00:00', 'time', '>='),
	 	 new SQLParamComb(array('important'=>0, 'c'=>new SQLParam('防fd', 'c', 'like')), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('d'=>array(2,3)), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('status'=>0, 'is_deleted'=>0, new SQLParamComb(array('important'=>0, 'open_flag'=>0), SQLParamComb::$TYPE_OR)), SQLParamComb::$TYPE_AND)
	 	 ), '表B'=>array(...) )
	 * @param array $checkDigitsGroup 数字检测开关(多个)，与$tableNameAlias一一对应，例如：array('表A'=>array('a字段名', 'b字段名'), '表B'=>array(...))
	 * @param string $orderBy 排序字段，例如：a,b
	 * @param int $limit 单页最大记录数量，默认MAX_RECORDS_OF_PER_PAGE
	 * @param int $offset 偏移量
	 * @param string $whereType SQLParamComb::$TYPE_AND, SQLParamComb::TYPE_OR 		默认:$TYPE_AND
	 * @param string $outErrMsg 输出检测错误信息
	 * @return array|boolean false:查询失败，array:查询结果列表
	 */
	function joinSearch($tableNameAlias, $prefixSql, array $conditions, array $paramsGroup=NULL, $checkDigitsGroup=NULL, $orderBy=NULL, $limit=MAX_RECORDS_OF_PER_PAGE, $offset=0, $whereType=SQLParamComb_TYPE_AND, &$outErrMsg=NULL) {
		if (!$this->checkConnect()) {
			log_err('joinSearch error, cannot connect to ap server');
			return false;
		}
		
		$sql = $prefixSql;
		
		if (!empty($paramsGroup)) {
			foreach ($tableNameAlias as $tableName=>$alias) {
				$params = @$paramsGroup[$tableName];
				$params = self::insertTableNameAliasPrefix($params, $alias.'.');
				
				$checkDigits = $checkDigitsGroup[$tableName];
				$checkDigits = self::insertTableNameAliasPrefix($checkDigits, $alias.'.');
				
				//拼接Where语句
				if (!empty($params)) {
					$where = '';
					if (!preg_match('/( )+where/i', $sql)) //不存在where
						$where = ' where';
					else if (!preg_match('/( )+'.$whereType.'( )*$/i', $sql) && !preg_match('/( )+where( )*$/i', $sql)) //结尾不是and或or，并且结尾不是where
						$where = ' '.$whereType;
					
					if (empty($where)) {
						$sql = self::rTrimSql(trim($sql), $where);
					}
					
					if (!$this->processWheres($params, $where, $conditions, $checkDigits, $whereType, false, $outErrMsg))
						return false;
					
					$sql .= $where;
				}
			}
		}
		
		//拼接order by
		if (!empty($orderBy)) {
			$orderBy_s = get_magic_quotes_gpc()?$orderBy:addslashes($orderBy);
// 			if (!empty($tableNameAlias)) {
// 				$orderBy_s = self::insertTableNameAliasPrefix($orderBy_s, reset($tableNameAlias).'.');
// 			}
			$this->orderBy($orderBy_s, $sql);
		}
		
		//执行查询
		return $this->apAcc->sqlSelect($sql, $conditions, $limit, $offset);
	}
	
	/**
	 * 表连接查询记录数量
	 * @param array $tableNameAlias 表别名数组，例如：array('表A'=>'t_a', '表B'=>'t_b', '表C'=>'t_c')
	 * @param string $prefixSql SQL语句前段
	 * @param array $conditions 查询条件数组(已和问号占位符匹配)，例如array(123, 'absdfd')
	 * @param array $paramsGroup 查询条件数组，与$tableNameAlias一一对应
		 例如： array( '表A'=>array(
		 'a'=>123,
		 'b'=>'文档xx',
		 'c'=>new SQLParam('防fd', 'c', 'like'),
		 'time_s'=>SQLParam('2016-01-01 00:00:00', 'time', '>='),
		 new SQLParamComb(array('important'=>0, 'c'=>new SQLParam('防fd', 'c', 'like')), SQLParamComb::$TYPE_OR),
		 new SQLParamComb(array('d'=>array(2,3)), SQLParamComb::$TYPE_OR),
		 new SQLParamComb(array('status'=>0, 'is_deleted'=>0, new SQLParamComb(array('important'=>0, 'open_flag'=>0), SQLParamComb::$TYPE_OR)), SQLParamComb::$TYPE_AND)
		 ), '表B'=>array(...) )
	 * @param array $checkDigitsGroup 数字检测开关(多个)，与$tableNameAlias一一对应，例如：array('表A'=>array('a字段名', 'b字段名'), '表B'=>array(...))
	 * @param string $whereType SQLParamComb::$TYPE_AND, SQLParamComb::TYPE_OR 		默认:$TYPE_AND
	 * @param string $outErrMsg 输出检测错误信息
	 * @return boolean|int false:查询失败，int:结果记录数量
	 */
	function joinSearchForCount($tableNameAlias, $prefixSql, array $conditions, array $paramsGroup, $checkDigitsGroup=NULL, $whereType=SQLParamComb_TYPE_AND, &$outErrMsg=NULL) {
		if (!$this->checkConnect()) {
			log_err('joinSearchForCount error, cannot connect to ap server');
			return false;
		}
		$sql = $prefixSql;
		
		foreach ($tableNameAlias as $tableName=>$alias) {
			$params = @$paramsGroup[$tableName];
			$params = self::insertTableNameAliasPrefix($params, $alias.'.');
				
			$checkDigits = $checkDigitsGroup[$tableName];
			$checkDigits = self::insertTableNameAliasPrefix($checkDigits, $alias.'.');
				
			//拼接Where语句
			if (!empty($params)) {
				$where = '';
				if (!preg_match('/( )+where/i', $sql)) //不存在where
					$where = ' where';
				else if (!preg_match('/( )+'.$whereType.'( )*$/i', $sql) && !preg_match('/( )+where( )*$/i', $sql)) //结尾不是and或or，并且结尾不是where
					$where = ' '.$whereType;
	
				if (empty($where)) {
					$sql = self::rTrimSql(trim($sql), $where);
				}
				
				if (!$this->processWheres($params, $where, $conditions, $checkDigits, $whereType, false, $outErrMsg))
					return false;

				$sql .= $where;
			}
		}
		
		//执行查询
		return $this->apAcc->sqlSelect($sql, $conditions, 1);
	}
	
	/**
	 * 查询
	 * @param string fieldNames 查询返回字段名称，如：'a, b, ...'；填NULL表示返回所有字段
	 * @param array $params 查询条件数组，
	  	例如：array(
	 	 'a'=>123,
	 	 'b'=>'文档xx',
	 	 'c'=>new SQLParam('防fd', 'c', 'like'),
	 	 'time_s'=>SQLParam('2016-01-01 00:00:00', 'time', '>='),
	 	 new SQLParamComb(array('important'=>0, 'c'=>new SQLParam('防fd', 'c', 'like')), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('d'=>array(2,3)), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('status'=>0, 'is_deleted'=>0, new SQLParamComb(array('important'=>0, 'open_flag'=>0), SQLParamComb::$TYPE_OR)), SQLParamComb::$TYPE_AND)
	 	 )
	 * @param array $checkDigits 数字检测开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param string $orderBy 排序字段，例如：a,b
	 * @param integer $limit 单页最大记录数量，默认MAX_RECORDS_OF_PER_PAGE
	 * @param integer $offset 偏移量
	 * @param string $whereType SQLParamComb::$TYPE_AND, SQLParamComb::TYPE_OR 		默认:$TYPE_AND
	 * @param string $outErrMsg 输出检测错误信息
	 * @return array|boolean false:查询失败，array:查询结果列表
	 */
	function search($fieldNames, array $params, $checkDigits=NULL, $orderBy=NULL, $limit=MAX_RECORDS_OF_PER_PAGE, $offset=0, $whereType=SQLParamComb_TYPE_AND, &$outErrMsg=NULL) {
		if (!$this->checkConnect()) {
			log_err('search error, cannot connect to ap server');
			return false;
		}
		
		$sql = $this->createPrefixOfSearchSql($fieldNames);
		if ($sql===false) return false;
		
		$conditions = array();
		
		//拼接Where语句
		if (!empty($params)) {
			$where = '';
			
			if (!$this->processWheres($params, $where, $conditions, $checkDigits, $whereType, false, $outErrMsg)) {
				return false;
			}
			
			$sql .= $where;
		}
		//拼接order by
		if (!empty($orderBy))
			$this->orderBy(get_magic_quotes_gpc()?$orderBy:addslashes($orderBy), $sql);
			
		//执行查询
		return $this->apAcc->sqlSelect($sql, $conditions, $limit, $offset);
	}
	
	/**
	 * 简单查询
	 * @param string $sql 查询语句
	 * @param array $conditions 查询语句对应值
	 * @param array $params 查询条件数组，
	  	例如：array(
	 	 'a'=>123,
	 	 'b'=>'文档xx',
	 	 'c'=>new SQLParam('防fd', 'c', 'like'),
	 	 'time_s'=>SQLParam('2016-01-01 00:00:00', 'time', '>='),
	 	 new SQLParamComb(array('important'=>0, 'c'=>new SQLParam('防fd', 'c', 'like')), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('d'=>array(2,3)), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('status'=>0, 'is_deleted'=>0, new SQLParamComb(array('important'=>0, 'open_flag'=>0), SQLParamComb::$TYPE_OR)), SQLParamComb::$TYPE_AND)
	 	 )
	 * @param array $checkDigits 数字检测开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param string $orderBy 排序字段，例如：a,b
	 * @param integer $limit 单页最大记录数量，默认MAX_RECORDS_OF_PER_PAGE
	 * @param integer $offset 偏移量
	 * @param string $whereType SQLParamComb::$TYPE_AND, SQLParamComb::TYPE_OR 		默认:$TYPE_AND
	 * @param string $outErrMsg 输出检测错误信息
	 * @return array|boolean false:查询失败，array:查询结果列表
	 */
	function simpleSearch($sql, $conditions=NULL, array $params=NULL, $checkDigits=NULL, $orderBy=NULL, $limit=MAX_RECORDS_OF_PER_PAGE, $offset=0, $whereType=SQLParamComb_TYPE_AND, &$outErrMsg=NULL) {
		if (!$this->checkConnect()) {
			log_err('search2 error, cannot connect to ap server');
			return false;
		}
		
		if (!isset($conditions) || $conditions==NULL)
			$conditions = array();
		
		//拼接Where语句
		if (!empty($params)) {
			$where = '';
			
			if (!$this->processWheres($params, $where, $conditions, $checkDigits, $whereType, false, $outErrMsg)) {
				return false;
			}
				
			$sql .= ' '.$where;
		}
		//拼接order by
		if (!empty($orderBy))
			$this->orderBy(get_magic_quotes_gpc()?$orderBy:addslashes($orderBy), $sql);		
		
		//执行查询
		return $this->apAcc->sqlSelect($sql, $conditions, $limit, $offset);
	}
	
	/**
	 * 查询记录数量(简单查询)
	 * @param string $sql 查询语句
	 * @param array $conditions 查询语句对应值
	 * @param array $params 查询条件数组，
		 例如：array(
		 'a'=>123,
		 'b'=>'文档xx',
		 'c'=>new SQLParam('防fd', 'c', 'like'),
		 'time_s'=>SQLParam('2016-01-01 00:00:00', 'time', '>='),
		 new SQLParamComb(array('important'=>0, 'c'=>new SQLParam('防fd', 'c', 'like')), SQLParamComb::$TYPE_OR),
		 new SQLParamComb(array('d'=>array(2,3)), SQLParamComb::$TYPE_OR),
		 new SQLParamComb(array('status'=>0, 'is_deleted'=>0, new SQLParamComb(array('important'=>0, 'open_flag'=>0), SQLParamComb::$TYPE_OR)), SQLParamComb::$TYPE_AND)
		 )
	 * @param array $checkDigits 数字检测开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param string $whereType SQLParamComb::$TYPE_AND, SQLParamComb::TYPE_OR 		默认:$TYPE_AND
	 * @param string $outErrMsg 输出检测错误信息
	 * @return array|boolean false:查询失败，array:查询结果列表
	 */
	function simpleSearchForCount($sql, $conditions=NULL, array $params=NULL, $checkDigits=NULL, $whereType=SQLParamComb_TYPE_AND, &$outErrMsg=NULL) {
		if (!$this->checkConnect()) {
			log_err('search2 error, cannot connect to ap server');
			return false;
		}
		
		if (!isset($conditions) || $conditions==NULL)
			$conditions = array();
		
		//拼接Where语句
		if (!empty($params)) {
			$where = '';
			if (!$this->processWheres($params, $where, $conditions, $checkDigits, $whereType, false, $outErrMsg))
				return false;
					
				$sql .= ' '.$where;
		}
		
		//执行查询
		return $this->apAcc->sqlSelect($sql, $conditions, 1);
	}
	
	/**
	 * 查询记录数量
	 * @param array $params 查询条件数组，
		 例如：array(
		 'a'=>123,
		 'b'=>'文档xx',
		 'c'=>new SQLParam('防fd', 'c', 'like'),
		 'time_s'=>SQLParam('2016-01-01 00:00:00', 'time', '>='),
		 new SQLParamComb(array('important'=>0, 'c'=>new SQLParam('防fd', 'c', 'like')), SQLParamComb::$TYPE_OR),
		 new SQLParamComb(array('d'=>array(2,3)), SQLParamComb::$TYPE_OR),
		 new SQLParamComb(array('status'=>0, 'is_deleted'=>0, new SQLParamComb(array('important'=>0, 'open_flag'=>0), SQLParamComb::$TYPE_OR)), SQLParamComb::$TYPE_AND)
		 )
	 * @param array $checkDigits 数字检测开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param string $whereType SQLParamComb::$TYPE_AND, SQLParamComb::TYPE_OR 		默认:$TYPE_AND
	 * @param string $outErrMsg 输出检测错误信息
	 * @return boolean|int false:查询失败，int:结果记录数量
	 */
	function searchForCount(array $params, $checkDigits=NULL, $whereType=SQLParamComb_TYPE_AND, &$outErrMsg=NULL) {
		if (!$this->checkConnect()) {
			log_err('search for count error, cannot connect to ap server');
			return false;
		}
		
		$sql = $this->createPrefixOfSearchForCountSql();
		
		$conditions = array();
		
		//拼接Where语句
		if (!empty($params)) {
			$where = '';
			if (!$this->processWheres($params, $where, $conditions, $checkDigits, $whereType, false, $outErrMsg))
				return false;
			
			$sql .= $where;
		}
		
		//执行查询
		return $this->apAcc->sqlSelect($sql, $conditions, 1);
	}
	
	/**
	 * 插入一个
	 * @param array $params 例如：array('a'=>'电xxx', 'b'=>1, 'c'=>'2016-01-20 00:00:00')
	 * @param array $checkDigits 数字检测开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param string $primaryKey 主键名称，默认NULL
	 * @param string $outErrMsg 输出检测错误信息
	 * @return string $bigId(64位整数，字符串形式存储)
	 */
	function insertOne(array $params, $checkDigits, $primaryKey=NULL, &$outErrMsg=NULL) {
		if (empty($params)) {
			log_err('insertOne error, $params is empty');
			return false;
		}
		
		if (!$this->checkConnect()) {
			log_err('insertOne error, cannot connect to ap server');
			return false;
		}
		
		//array("plan_id"=>'2379375100020059', "abc"=>'xx佛的', .....)
		// array('insert into eb_plan_info_t (plan_id, plan_name, start_time, stop_time) values (?,?,?,?)', array($this->apAcc->nextBigId(), '1发\\动\'xc的', '2016-01-20 01:11:11', '2016-01-20 10:12:01'))
		$sql = '';
		$values = array();
		$bigId = $this->processInsert($params, empty($primaryKey)?$this->primaryKeyName:$primaryKey, $sql, $values, $checkDigits, $outErrMsg); //创建insert语句及相关参数
		if ($bigId!='0') {
			$arry = $this->apAcc->sqlExecute(array(array($sql, $values))); //执行SQL
			$results = $this->resolveExecutionResult($arry); //解析返回结果
			if (is_array($results) && $results[0]>0) {
				return $bigId;
			}
		}
		
		return false;
	}
	
	/**
	 * 更新记录
	 * @param array $sets 设置值的数组  例如：array('a'=>'电xxx', 'b'=>1, 'c'=>'2016-01-20 00:00:00')
	 * @param array $wheres 查询条件数组，
	 	例如：array(
	 	 'a'=>123,
	 	 'b'=>'文档xx',
	 	 'c'=>new SQLParam('防fd', 'c', 'like'),
	 	 'time_s'=>SQLParam('2016-01-01 00:00:00', 'time', '>='),
	 	 new SQLParamComb(array('important'=>0, 'c'=>new SQLParam('防fd', 'c', 'like')), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('d'=>array(2,3)), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('status'=>0, 'is_deleted'=>0, new SQLParamComb(array('important'=>0, 'open_flag'=>0), SQLParamComb::$TYPE_OR)), SQLParamComb::$TYPE_AND)
	 	 )
	 * @param array $setCheckDigits 数字检测开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param array $whereCheckDigits 数字检测开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param string $whereType where条件的拼接类型，见SQLParamComb::$TYPE_AND和SQLParamComb::$TYPE_OP，默认SQLParamComb::$TYPE_AND
	 * @param string $prefixSql SQL语句前段
	 * @param array $conditions 查询条件数组(已和问号占位符匹配)，例如array(123, 'absdfd')
	 * @param string $outErrMsg 输出检测错误信息
	 * @return boolean|array
	 */
	function update(array $sets, array $wheres, $setCheckDigits=NULL, $whereCheckDigits=NULL, $whereType=SQLParamComb_TYPE_AND, $prefixSql=NULL, $conditions=NULL, &$outErrMsg=NULL) {
		if (!$this->checkConnect()) {
			log_err('update error, cannot connect to ap server');
			return false;
		}
		
		if (empty($sets) || count($sets)==0 || empty($wheres) || count($wheres)==0) {
			log_err('update error, have not parameter or where\'s conditions');
			return false;
		}
		
		$sql = $prefixSql;
		if (empty($sql))
			$sql = $this->createPrefixOfUpdateSql();
		
		if (!isset($conditions))
			$conditions = array();
		
		//$sql = $this->createPrefixOfUpdateSql();
		//$conditions = array();
		
		$where = '';
		$values = array();
		$setStr = '';
		
		//拼接SET语句
		if (!$this->processSets($sets, $setStr, $values, $setCheckDigits, $outErrMsg))
			return false;

		//拼接Where语句
		if (!$this->processWheres($wheres, $where, $conditions, $whereCheckDigits, $whereType, false, $outErrMsg))
			return false;

		$sql = $sql . $setStr . $where;
		
		//检测是否有where条件，如没有则限制执行，防止操作权限过大
		if ($this->mustHaveWhere($sql)===false) {
			log_err('mustHaveWhere found');
			return false;
		}
		
		//合并变量参数
		$finalValues = array_merge($values, $conditions);
		
		$arry = $this->apAcc->sqlExecute(array(array($sql, $finalValues))); //执行SQL
		$results = $this->resolveExecutionResult($arry); //解析返回结果
		if (is_array($results) && $results[0]>=0) {
			return array(true, $results[0]);
		} else {
			return false;
		}
	}
	
	/**
	 * 删除记录
	 * @param array $params 查询条件数组，
	 	例如：array(
	 	 'a'=>123,
	 	 'b'=>'文档xx',
	 	 'c'=>new SQLParam('防fd', 'c', 'like'),
	 	 'time_s'=>SQLParam('2016-01-01 00:00:00', 'time', '>='),
	 	 new SQLParamComb(array('important'=>0, 'c'=>new SQLParam('防fd', 'c', 'like')), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('d'=>array(2,3)), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('status'=>0, 'is_deleted'=>0, new SQLParamComb(array('important'=>0, 'open_flag'=>0), SQLParamComb::$TYPE_OR)), SQLParamComb::$TYPE_AND)
	 	 )
	 * @param array $cCheckDigits 数字检测开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param string $whereType where条件的拼接类型，见SQLParamComb::$TYPE_AND和SQLParamComb::$TYPE_OP，默认SQLParamComb::$TYPE_AND
	 * @param string $prefixSql SQL语句前段
	 * @param array $conditions 查询条件数组(已和问号占位符匹配)，例如array(123, 'absdfd')
	 * @param string $outErrMsg 输出检测错误信息
	 * @return boolean|array
	 */
	function delete(array $params, $checkDigits=NULL, $whereType=SQLParamComb_TYPE_AND, $prefixSql=NULL, $conditions=NULL, &$outErrMsg=NULL) {
		if (!$this->checkConnect()) {
			log_err('delete error, cannot connect to ap server');
			return false;
		}
		
		$sql = $prefixSql;
		if (empty($sql))
			$sql = $this->createPrefixOfDeleteSql();
		
		if (!isset($conditions))
			$conditions = array();
		
		//拼接Where语句
		$where = '';
		if (!preg_match('/( )+where/i', $sql)) //不存在where
			$where = ' where';
		else if (!preg_match('/( )+'.$whereType.'( )*$/i', $sql) && !preg_match('/( )+where( )*$/i', $sql)) //结尾不是and或or，并且结尾不是where
			$where = ' '.$whereType;
	
		if (empty($where)) {
			$sql = self::rTrimSql(trim($sql), $where);
		}
		
		if (!$this->processWheres($params, $where, $conditions, $checkDigits, $whereType, false, $outErrMsg))
			return false;
		
		$sql .= $where;
		
		//检测是否有where条件，如没有则限制执行，防止操作权限过大
		if ($this->mustHaveWhere($sql)===false) {
			log_err('mustHaveWhere found');
			return false;
		}
		
		$arry = $this->apAcc->sqlExecute(array(array($sql, $conditions))); //执行SQL
		$results = $this->resolveExecutionResult($arry); //解析返回结果
		if (is_array($results) && $results[0]>=0) {
			return array(true, $results[0]);
		} else {
			return false;
		}
	}
	
	/**
	 * 执行多个SQL(非查询)
	 * @param array $elements MultiExecuteElement对象数组
	 * @param boolean $transaction 是否事务执行，true使用，false不使用；默认=false
	 * @param string $outErrMsg 输出检测错误信息
	 * @return boolean|mixed 如果全部执行失败，返回boolean类型的false值；否则返回各sql执行结果的array数组
	 */
	function multiExecute(array $elements, $transaction=false, &$outErrMsg=NULL) {
		//输入值初步校验
		if (empty($elements) || !is_array($elements) || count($elements)==0) {
			log_err('multiExecute error, $elements is empty');
			return false;
		}
		//检查与AP服务连接情况
		if (!$this->checkConnect()) {
			log_err('multiExecute error, cannot connect to ap server');
			return false;
		}
		
		//遍历解析并创建每一个SQL
		$sqls = array();
		foreach ($elements as $element) {
			if (!$element instanceof MultiExecuteElement) {
				log_err('multiExecute error, $element is not a MultiExecuteElement object');
				return false;
			}
			
			switch ($element->type) {
				case MultiExecuteElement::$TYPE_INSERT:
					//检查主键名称是否有值
					if (empty($element->primaryKeyName)) {
						log_err('multiExecute error, $primaryKeyName must not be empty for insert');
						return false;
					}
					
					//拼接Insert语句
					$sql = '';
					$values = array();
					$bigId = $this->processInsert($element->nameValues, $element->primaryKeyName, $sql, $values, $element->nameValueCheckDigits, $outErrMsg);
					if ($bigId=='0') {
						log_err('multiExecute-> processInsert error');
						return false;
					}
					
					//加入待执行队列
					array_push($sqls, array($sql, $values));
					
					break;
				case MultiExecuteElement::$TYPE_DELETE:
					$sql = $this->createPrefixOfDeleteSql();
					
					//拼接Where语句
					$conditions = array();
					$where = '';
					if (!$this->processWheres($element->wheres, $where, $conditions, $element->whereCheckDigits, $element->whereType, false, $outErrMsg))
						return false;
					
					$sql .= $where;
					
					//检测是否有where条件，如没有则限制执行，防止操作权限过大
					if ($this->mustHaveWhere($sql)===false) {
						log_err('multiExecute->mustHaveWhere found');
						return false;
					}
					
					//加入待执行队列
					array_push($sqls, array($sql, $conditions));
					
					break;
				case MultiExecuteElement::$TYPE_UPDATE:
					$sql = $this->createPrefixOfUpdateSql();
					
					$conditions = array();
					$where = '';
					$values = array();
					$setStr = '';
					
					//拼接SET语句
					if (!$this->processSets($element->nameValues, $setStr, $values, $element->nameValueCheckDigits, $outErrMsg))
						return false;
					
					//拼接Where语句
					if (!$this->processWheres($element->wheres, $where, $conditions, $element->whereCheckDigits, $element->whereType, false, $outErrMsg))
						return false;
					
					$sql = $sql . $setStr . $where;
					
					//检测是否有where条件，如没有则限制执行，防止操作权限过大
					if ($this->mustHaveWhere($sql)===false) {
						log_err('mustHaveWhere found');
						return false;
					}
					
					//合并变量参数
					$finalValues = array_merge($values, $conditions);
					
					//加入待执行队列
					array_push($sqls, array($sql, $finalValues));
					break;
				default:
					log_err('multiExecute error, miss type');
					return false;
			}
		}
		
		$arry = $this->apAcc->sqlExecute($sqls, $transaction); //执行SQL
		$results = $this->resolveExecutionResult($arry, $transaction); //解析返回结果
		return $results;
	}
	
	/**
	 * 解析SQL执行结果
	 * @param boolean|array $arry 执行的返回结果
	 * @param boolean $transaction 执行是否使用事务(解析返回结果时逻辑有所差别，非事务环境下允许部分执行成功)
	 * @return boolean|array 执行结果
	 */
	protected function resolveExecutionResult($arry, $transaction=false) {
		if (!isset($arry))
			return false;
		
		if ($arry===false)
			return false;
		
			//["code"=>"0","size"=>"1","lists"=>[["result"=>"0"]    ]] 事务环境下的成功
			//["code"=>"16","size"=>"2","lists":[["result"=>"1"], ["result"=>"-1"]    ]] 非事务下的成功和失败共存
			//["code"=>"15","error"=>"sql parameter count error."] 	SQL拼装错误
			if (array_key_exists('code', $arry)) {
				$code = $arry['code'];
				if ($code!=0) {
					//事务环境下，一个操作失败表示全部失败
					if ($transaction) {
						return false;
					} else {
						if (array_key_exists('lists', $arry))
							$arry = $arry['lists'];
						else 
							return false;
					}
				} else if ($transaction) {
					return true;
				}
			}
			
			//["result"=>"1", ...];
			$results = array();
			foreach ($arry as $value) {
				if (array_key_exists('result', $value)) {
					$result = $value['result'];
					array_push($results, $result);
				}
			}
			
			return $results;
	}
	
	/**
	 * 把查询条件参数列表里某个值转变为SQLParam结构
	 * @param array $wheres
	 * @param string $targetName 列表中对应字段名称
	 * @param string $fieldName 真实字段名称
	 * @param string $op 操作符，见SQLParam::$op
	 * @param string $targetValue
	 */
	static function changeToSQLParam(array &$wheres, $targetName, $fieldName, $op, $targetValue) {
		if (!empty($wheres) && (isset($wheres[$targetName]))) {
			$value = $wheres[$targetName];
			if ($value instanceof SQLParam) {
				$value->op = $op;
				if (isset($targetValue))
					$value->value = $targetValue;
			} else {
				$newParam= new SQLParam($value, $fieldName, $op);
				$wheres[$targetName] = $newParam;
				if (isset($targetValue))
					$newParam->value = $targetValue;
			}
		}
	}
	
	/**
	 * 把查询条件参数列表里某个值替换为SQLParamComb结构
	 * @param array $wheres
	 * @param string $targetName 列表中对应字段名称
	 * @param string $fieldName 真实字段名称
	 * @param SQLParamComb $comb SQLParamComb对象
	 */
	static function changeToSQLParamComb(&$wheres, $targetName, $fieldName, SQLParamComb $comb) {
		if (!empty($wheres) && !empty($wheres[$targetName])) {
			$wheres[$targetName] = $comb;
		}
	}
	/**
	 * 移除指定查询条件参数
	 * @param array $wheres
	 * @param string $targetName 列表中对应字段名称
	 */
	static function removeWhereCondition(&$wheres, $targetName) {
		if (!empty($wheres) && isset($wheres[$targetName])) {
			unset($wheres[$targetName]);
		}
	}
}