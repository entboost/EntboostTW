<?php
require_once dirname(__FILE__).'/common.php';
//require_once dirname(__FILE__).'/EBAPServerAccessor.class.php';

/**
 * SQL参数类
 */
class SQLParam
{
	/**
	 * 模糊查询like
	 * @var string
	 */
	static public $OP_LIKE 	= 'like';
	/**
	 * 等号=
	 * @var string
	 */
	static public $OP_EQ 	= '=';
	/**
	 * 大于号>
	 * @var string
	 */
	static public $OP_GT 	= '>';
	/**
	 * 大于等于号>=
	 * @var string
	 */
	static public $OP_GT_EQ = '>=';
	/**
	 * 小于号<
	 * @var string
	 */
	static public $OP_LT 	= '<';
	/**
	 * 小于等于号<=
	 * @var string
	 */
	static public $OP_LT_EQ = '<=';

	/**
	 * 操作符：'like', '=', '>', '>=', '<', '<=', NULL 默认使用'='
	 * @var string
	 */
	public $op;
	/**
	 * 值
	 * @var mixed
	 */
	public $value;
	/**
	 * 名称
	 * @var string
	 */
	public $name;

	/**
	 *
	 * @param mixed $value 值
	 * @param string $name 真实字段名，如不填则使用where参数队列的key
	 * @param string $op 操作符：'like', '=', '>', '>=', '<', '<=', NULL；见SQLParam::$OP_LIKE...等定义
	 */
	function __construct($value, $name=NULL, $op='=') {
		$this->value = $value;
		$this->name = $name;
		$this->op = $op;
	}
	
	function __clone() {
		if (is_object($this->value))
			$this->value = clone $this->value;
	}
}

//SQL参数组合拼接类型
const SQLParamComb_TYPE_AND = 'and';
const SQLParamComb_TYPE_OR = 'or';

/**
 * SQL参数组合类，主要用于带or的参数组合
 */
class SQLParamComb
{
	static public $TYPE_AND 	= SQLParamComb_TYPE_AND;
	static public $TYPE_OR 		= SQLParamComb_TYPE_OR;
	/**
	 * 操作符 SQLParamComb::$TYPE_AND, SQLParamComb::$TYPE_OR 	默认SQLParamComb::$TYPE_AND
	 * @var string
	 */
	public $type;

	/**
	 * 通用数据类型, SQLParam对象, SQLParamComb对象的列表
	 * @var array
	 */
	public $sqlParams;

	/**
	 * 构造函数
	 * @param array  元素类型：SQLParam参数类 或 通用数据类型(数字、字符串)
	 * 	 例如：array(
	 	 'a'=>123,
	 	 'b'=>'文档xx',
	 	 'c'=>new SQLParam('防fd', 'c', 'like'),
	 	 'time_s'=>SQLParam('2016-01-01 00:00:00', 'time', '>='),
	 	 new SQLParamComb(array('important'=>0, 'c'=>new SQLParam('防fd', 'c', 'like')), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('d'=>array(2,3)), SQLParamComb::$TYPE_OR),
	 	 new SQLParamComb(array('status'=>0, 'is_deleted'=>0, new SQLParamComb(array('important'=>0, 'open_flag'=>0), SQLParamComb::$TYPE_OR)), SQLParamComb::$TYPE_AND)
	 	 )
	 * @param int $type 类型：SQLParamComb::TYPE_OR, SQLParamComb::$TYPE_AND ，默认$TYPE_AND
	 */
	function __construct(array $sqlParams, $type=SQLParamComb_TYPE_AND) {
		$this->sqlParams = $sqlParams;
		$this->type = $type;
		// 	 	$arguments = func_get_args();
		// 	 	$this->op = @array_shift($arguments);
		// 	 	$this->sqlParams = $arguments;
	}
	
	function __clone() {
		array_deepclone($this->sqlParams, $this->sqlParams);
	}
}


/**
 * 单次执行多个SQL语句功能的数据节点定义
 */
class MultiExecuteElement
{
	static public $TYPE_INSERT = 1;
	static public $TYPE_DELETE = 2;
	static public $TYPE_UPDATE = 3;
	/**
	 * 类型:1=insert, 2=delete, 3=update
	 * @var int
	 */
	public $type;
	/**
	 * 查询条件
	 * @var array
	 */
	public $wheres;
	/**
	 * where条件的拼接类型，见SQLParamComb::$TYPE_AND和SQLParamComb::$TYPE_OP
	 * @var int 默认SQLParamComb:$TYPE_AND
	 */
	public $whereType;
	/**
	 * 控制查询条件数字校验
	 * @var array
	 */
	public $whereCheckDigits;
	/**
	 * 值和名称，update或insert使用；
	 * @var array
	 */
	public $nameValues;
	/**
	 * 控制值和名称的数字校验
	 * @var array
	 */
	public $nameValueCheckDigits;
	/**
	 * 主键名称，使用insert操作时必填
	 * @var string
	 */
	public $primaryKeyName;

	/**
	 * 构造函数
	 * @param int $type 类型:insert=MultiExecuteElement::$TYPE_INSERT, delete=MultiExecuteElement::$TYPE_DELETE, update=MultiExecuteElement::$TYPE_UPDATE
	 * @param array $wheres 查询条件的数组，
	 例如：array(
	 'a'=>123,
	 'b'=>'文档xx',
	 'c'=>new SQLParam('防fd', 'c', 'like'),
	 'time_s'=>SQLParam('2016-01-01 00:00:00', 'time', '>='),
	 new SQLParamComb(array('important'=>0, 'c'=>new SQLParam('防fd', 'c', 'like')), SQLParamComb::$TYPE_OR),
	 new SQLParamComb(array('status'=>0, 'is_deleted'=>0, new SQLParamComb(array('important'=>0, 'open_flag'=>0), SQLParamComb::$TYPE_OR)), SQLParamComb::$TYPE_AND)
	 )
	 * @param array $whereCheckDigits 查询条件的数字校验开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param array $nameValues 值和名称的数组，例如：array('a'=>111, 'b'=>'fsfsdf')
	 * @param array $nameValueCheckDigits 值和名称的数字校验开关(多个)，例如：array('a字段名', 'b字段名')
	 * @param string $primaryKeyName 主键名称；insert操作必填，其它操作无用
	 * @param string $whereType where条件的拼接类型，见SQLParamComb::$TYPE_AND和SQLParamComb::$TYPE_OP，默认SQLParamComb::$TYPE_AND
	 */
	function __construct($type, $wheres, $whereCheckDigits=NULL, $nameValues=NULL, $nameValueCheckDigits=NULL, $primaryKeyName=NULL, $whereType=SQLParamComb_TYPE_AND) {
		$this->type = $type;
		$this->wheres = $wheres;
		$this->whereCheckDigits = $whereCheckDigits;
		$this->nameValues = $nameValues;
		$this->nameValueCheckDigits = $nameValueCheckDigits;
		$this->primaryKeyName = $primaryKeyName;
		$this->whereType = $whereType;
	}
	
	function __clone() {
		array_deepclone($this->wheres, $this->wheres);
		array_deepclone($this->whereCheckDigits, $this->whereCheckDigits);
		array_deepclone($this->nameValues, $this->nameValues);
		array_deepclone($this->nameValueCheckDigits, $this->nameValueCheckDigits);
	}
}