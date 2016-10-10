<?php
require_once dirname(__FILE__).'/../common.php';
require_once dirname(__FILE__).'/../config.php';
require_once dirname(__FILE__).'/../Log.class.php';

abstract class EBModelBase
{
	private $pager;
	//private $orderby;
	
	/**
	 * 排序字段
	 * @var string
	 */
	private $request_order_by;
	
	/**
	 * 请求类型
	 * @var int
	 */
	public $request_query_type;
	
	/**
	 * 是否执行查询数量：1=是，其它值或不填=否
	 * @var int
	 */
	public $request_for_count;
	/**
	 * 是否获取最少字段：1=是，其它值或不填=否
	 * @var int
	 */
	public $request_fetch_minimum;
	/**
	 * 是否获取权限信息: 1=是，其它值或不填=否
	 * @var int
	 */
	public $fetch_authority_info;
	
	/**
	 * 获取分页查询对象
	 * @return int
	 */
	public function getPager() {
		return $this->pager;
	}
	/**
	 * 获取排序字段
	 * @return string
	 */
	public function getOrderby() {
		return $this->request_order_by;
	}
	/**
	 * 设置排序字段
	 * @return string
	 */
	public function setOrderby($orderby) {
		$this->request_order_by = $orderby;
	}
	
	/**
	 * 获取当前页码，从第一页开始
	 * @return int
	 */
	public function getCurrentPage() {
		$pager = $this->getPager();
		if (isset($pager))
			return $pager->nowPage;
		
		$currentPage = get_request_param(CURRENT_PAGE_NAME);
		if (empty($currentPage) || (is_string($currentPage) && !var_is_digit($currentPage))) {
			return 1;
		}
		return $currentPage;
	}
	
	/**
	 * 获取每页最大记录数量
	 * @return int
	 */
	public function getPerPage() {
		$pager = $this->getPager();
		if (isset($pager)) {
			return $pager->pageSize;
		}
		
		if (isset($this->{PER_PAGE_NAME})) {
			return $this->{PER_PAGE_NAME};
		}
		
		$perPage = get_request_param(PER_PAGE_NAME);
		if (empty($perPage) || (is_string($perPage) && !var_is_digit($perPage))) {
			return MAX_RECORDS_OF_PER_PAGE;
		}
		return $perPage;
	}
	
	/**
	 * 设置总记录数，内部将自动计算总分页数量和当前页面
	 * @param int $recordCount
	 */
	public function setRecordCount($recordCount) {
		$pager = $this->getPager();
		if (isset($pager)) {
			$pager->recordCount = $recordCount;
			$pager->pageCount = (int)(($pager->recordCount + $pager->pageSize-1)/$pager->pageSize); //计算总分页数
			
// 			//修正pager总分页数
// 			if ($calculatePageCount < $pager->pageCount) {
// 				$pager->pageCount = $calculatePageCount;
// 			}
			//修正当前分页
			if ($pager->nowPage > $pager->pageCount) {
				$pager->nowPage = $pager->pageCount;
			}
		}
	}
	
	/**
	 * 从HTTP请求读取值
	 * @param mixed $instance
	 */
	protected function setValuesFromRequest($instance) {
		foreach ($instance as $key1=>&$value1) { //注意这里的技巧&号一定不能少
			if(isset($_REQUEST[$key1]) && $key1!=PER_PAGE_NAME && $key1!=CURRENT_PAGE_NAME) {
				$value1=get_request_param($key1);
			}
		}
		
		//分页交互参数
 		$dtGridPager = get_request_param('dtGridPager');//@$_REQUEST['dtGridPager'];
		if (!empty($dtGridPager)) {
			//echo $dtGridPager;
			//$dtGridPager = '{"isExport":false,"pageSize":20,"startRecord":0,"nowPage":1,"recordCount":-1,"pageCount":-1,"parameters":{"start_time_s":"2016-04-04 00:00:00","start_time_e":"2016-04-10 23:59:59"},"fastQueryParameters":{},"advanceQueryConditions":[],"advanceQuerySorts":[]}';
			$this->pager = json_decode($dtGridPager);
			foreach ($this->pager->parameters as $key2=>$value2) {
				if (property_exists($instance, $key2))
					$instance->{$key2} = $value2;
			}
		}
		
		//排序参数
		//$sortParameter = '{"columnId":"create_time","sortType":1}';
		$sortParameter = get_request_param('sortParameter');//@$_REQUEST['sortParameter'];
		if (!empty($sortParameter)) {
			$orderby = json_decode($sortParameter);
			if ($orderby->sortType==1 || $orderby->sortType==2)
				$this->request_order_by = $orderby->columnId .' '. ($orderby->sortType==1?'asc':'desc');
		}
	}
	
	/**
	 * 创建查询条件参数列表
	 * @param mixed $instance
	 * @return array 查询条件参数列表，例如：array('a'=>123, 'b'=>'fdfds')
	 */
	protected function createWhereConditions($instance) {
		$whereConditons = array();
		foreach ($instance as $key=>$value) {
			if (isset($value) && $key!='pager' /*&& $key!='orderby'*/ && $key!='fetch_authority_info' && $key!=PER_PAGE_NAME && $key!=CURRENT_PAGE_NAME 
					&& $key!=REQUEST_ORDER_BY && $key!=REQUEST_QUERY_TYPE && $key!=REQUEST_FOR_COUNT && $key!=REQUEST_FETCH_MINIMUM) {
				if (is_array($value))
					$whereConditons[$key] = new SQLParamComb(array($key=>$value), SQLParamComb::$TYPE_OR);
				else
					$whereConditons[$key] = $value;
			}
		}
		
		return $whereConditons;
	}
	
	/**
	 * 创建字段-值参数列表
	 * @param mixed $instance
	 * @return array 字段-值参数列表，例如：array('a'=>123, 'b'=>'fdfds')
	 */
	protected function createFields($instance) {
		$fields = array();
		foreach ($instance as $key=>$value) {
			if (isset($value) && $key!='pager' /*&& $key!='orderby'*/ && $key!='fetch_authority_info' && $key!=PER_PAGE_NAME && $key!=CURRENT_PAGE_NAME 
					&& $key!=REQUEST_ORDER_BY && $key!=REQUEST_QUERY_TYPE && $key!=REQUEST_FOR_COUNT && $key!=REQUEST_FETCH_MINIMUM) {
				$fields[$key] = $value;
			}
		}
		
		return $fields;
	}
	
	/**
	 * 去除不允许request请求参数直接插入或更新的字段
	 * @param array $fields
	 */
	protected function removeKeepFields(&$fields) {
		
	}
	
	/**
	 * 创建数字校验条件列表
	 * @param mixed $instance
	 * @return array 查询条件参数列表，例如：array('a', 'b')
	 */
	protected function createCheckDigits($instance) {
		return array();
	}
	
	/**
	 * 验证指定字段值非空(支持多个)
	 * @param string $fieldNames 待验证字段名，逗号分隔，例如：'a,b,c'
	 * @param string $outErrMsg 输出参数 检测错误信息
	 * @param mixed $instance
	 * @return boolean 验证结果：true=通过，false=不通过
	 */
	protected function validNotEmpty($fieldNames, &$outErrMsg, $instance) {
		$outErrMsg = null;
		
		if (!isset($instance) || empty($fieldNames)) {
			//log_err('validNotEmpty error, $instance or $fieldNames is null');
			$outErrMsg = 'validNotEmpty error, $instance or $fieldNames is null';
			return false;
		}
		
		$fields = preg_split('/,/', $fieldNames);
		if (empty($fields)) {
			$outErrMsg = 'no field to valid notEmpty';
			return false;
		}
		
		foreach ($fields as $fieldName) {
			$value = $instance->{trim($fieldName)};
			if (empty($value) && $value!='0') {
				//$outErrMsg = $fieldName;
				return validNotEmptyFailure($value, $outErrMsg, $value, $fieldName);
			}
		}
		
		return true;
	}
	
	/**
	 * 验证指定字段值是数字(支持多个)
	 * 本函数已包含非空验证(空值返回false)
	 * @param string $fieldNames 待验证字段名，逗号分隔，例如：'a,b,c'
	 * @param string $outErrMsg 输出参数 检测错误信息
	 * @param mixed $instance
	 * @return boolean 验证结果：true=通过，false=不通过
	 */
	protected function validDigit($fieldNames, &$outErrMsg, $instance) {
		$outErrMsg = null;
		
		if (!isset($instance) || empty($fieldNames)) {
			log_err('validDigit error, $instance or $fieldNames is null');
			return false;
		}
		
		$fields = preg_split('/,/', $fieldNames);
		if (empty($fields)) {
			$outErrMsg = 'no field to valid digit';
			return false;	
		}
		
		foreach ($fields as $fieldName) {
			$value = $instance->{trim($fieldName)};
			if (!EBModelBase::checkDigit($value, $outErrMsg, $fieldName)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * 验证变量是否数字
	 * @param string $variable 待检测变量
	 * @param string $outErrMsg 输出参数 检测错误信息
	 * @param string $variableName 变量名
	 * @return boolean true=检测通过，false=检测不通过
	 */
	static function checkDigit($variable, &$outErrMsg, $variableName=NULL) {
		$outErrMsg = null;
		
		if (!isset($variable) || (empty($variable)&&$variable!='0'))
			return checkDigitFailure($variable, $outErrMsg, $variable , $variableName);
		if (!is_array($variable)) {
			if (!var_is_digit($variable))
				return checkDigitFailure($variable, $outErrMsg, $variable , $variableName);
		} else if (is_array($variable)) {
			foreach ($variable as $str) {
				if (!var_is_digit($str))
					return checkDigitFailure($variable, $outErrMsg, $str, $variableName.'=>'.$str);
			}
		}
		return true;
	}
}