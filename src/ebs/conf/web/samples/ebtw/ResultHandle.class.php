<?php
require_once dirname(__FILE__).'/EBStateCode.class.php';

/**
 * 执行结果处理类
 *
 */
class ResultHandle
{
	/**
	 * 数据库执行失败结果封装为特定json格式
	 * @param mixed $result
	 * @param int $code 错误代码，默认EBStateCode::$EB_STATE_ERROR
	 * @param string $errMsg 错误信息，默认不填
	 * @return string json格式字符串
	 */
	static public function failureResultToJson($result, $code=1, $errMsg=NULL) {
		$rets = array();
		if (!isset($result)) {
			$rets['code'] = $code;
			if (isset($errMsg))
				$rets['msg'] = $errMsg;
			
			return json_encode($rets);
		}
		
		if ($result===false) {
			$rets['code'] = EBStateCode::$EB_STATE_ERROR;
			if (isset($errMsg))
				$rets['msg'] = $errMsg;
			
			return json_encode($rets);
		}
		
// 		if(is_array($result)) {
// 			$objArry = array();
// 			foreach ($result as $value) {
// 				$tempObj = self::arrayToObject($value);
// 				array_push($objArry, $tempObj);
// 			}
// 			$rets['results'] = $objArry;
// 		} else {
// 			$rets['result'] = is_bool($result)?(int)$result:$result;
// 		}
		
// 		return json_encode($rets);
	}
	
	/**
	 * 数组转对象
	 * @param array $arr
	 */
	static private function arrayToObject($arr){
		if(is_array($arr)){
			return (object)array_map(__FUNCTION__, $arr);
		}else{
			return $arr;
		}
	}
	
	/**
	 * 错误信息封装为json字符串
	 * @param string $outputMsg 输出信息描述
	 * @param string $logMsg	日志信息
	 * @param boolean $output	是否输出到页面，默认true
	 * @param int $code 状态代码，默认EBStateCode::$EB_STATE_ERROR
	 * @return string json字符串
	 */
	static function errorToJsonAndOutput($outputMsg, $logMsg=NULL, $output=true, $code=1) {
		if (isset($logMsg))
			log_err($logMsg);
		
		$json = json_encode(array('code'=>$code, 'msg'=>$outputMsg?:''));
		
		if ($output)
			echo $json;
		
		return $json;
	}
	
	/**
	 * 成功信息封装为json字符串
	 * @param string $outputMsg 输出信息描述
	 * @param array $others 附加输出信息
	 * @param string $logMsg	日志信息
	 * @param boolean $output	是否输出到页面，默认true
	 * @param int $code 状态代码，默认EBStateCode::$EB_STATE_OK
	 * @return string json字符串
	 */
	static function successToJsonAndOutput($outputMsg, $others, $logMsg=NULL, $output=true, $code=0) {
		if (isset($logMsg))
			log_info($logMsg);
		
		$outArry = array('code'=>$code);
		if (!empty($others))
			$outArry = array_merge($outArry, $others);
		$json = json_encode($outArry);
		
		if ($output)
			echo $json;
		
		return $json;
	}	
	
	/**
	 * '没有权限'错误封装为json字符串
	 * @param boolean $output 是否输出到页面，默认true
	 * @param int $code 错误代码，默认EBStateCode::$EB_STATE_NOT_AUTH_ERROR
	 * @param string $outputMsg 输出参数 错误描述
	 * @return string json字符串
	 */
	static function noAuthErrToJsonAndOutput($output=true, $code=2, &$outputMsg=NULL) {
		$outputMsg = 'no authority';
		//$outputMsg = 'record is not exist or no authority';
		return ResultHandle::errorToJsonAndOutput($outputMsg, $outputMsg, $output, $code);
	}
	
	/**
	 * 字段校验不通过的错误信息封装为json字符串
	 * @param string $fieldName 字段名
	 * @param boolean $output	是否输出到页面，默认true
	 * @param int $code 错误代码，默认EBStateCode::$EB_STATE_ERROR
	 * @return string json字符串
	 */
	static function fieldValidErrToJsonAndOutput($fieldName, $output=true, $code=1) {
		$outputMsg = 'field '.$fieldName.' is invalid';
		return ResultHandle::errorToJsonAndOutput($outputMsg, $outputMsg, $output, $code);
	}
	
	/**
	 * “字段”数字校验不通过的错误信息封装为json字符串
	 * @param string $fieldName 字段名
	 * @param boolean $output	是否输出到页面，默认true
	 * @param int $code 错误代码，默认EBStateCode::$EB_STATE_ERROR
	 * @return string json字符串
	 */
	static function fieldValidNotDigitErrToJsonAndOutput($fieldName, $output=true, $code=1) {
		$outputMsg = 'field '.$fieldName.' must be a digit';
		return ResultHandle::errorToJsonAndOutput($outputMsg, $outputMsg, $output, $code);
	}
	
	/**
	 * 数字校验不通过的错误信息封装为json字符串
	 * @param string $errMsg 错误信息
	 * @param boolean $output	是否输出到页面，默认true
	 * @param int $code 错误代码，默认EBStateCode::$EB_STATE_ERROR
	 * @return string json字符串
	 */
	static function validNotDigitErrToJsonAndOutput($errMsg, $output=true, $code=1) {
		return ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output, $code);
	}
	
	/**
	 * “字段”非空校验不通过的错误信息封装为json字符串
	 * @param string $fieldName 字段名
	 * @param boolean $output	是否输出到页面，默认true
	 * @param int $code 错误代码，默认EBStateCode::$EB_STATE_ERROR
	 * @return string json字符串
	 */
	static function fieldValidNotEmptyErrToJsonAndOutput($fieldName, $output=true, $code=1) {
		$outputMsg = 'field '.$fieldName.' cannot be empty';
		return ResultHandle::errorToJsonAndOutput($outputMsg, $outputMsg, $output, $code);
	}
	
	/**
	 * 非空校验不通过的错误信息封装为json字符串
	 * @param string $errMsg 错误信息
	 * @param boolean $output	是否输出到页面，默认true
	 * @param int $code 错误代码，默认EBStateCode::$EB_STATE_ERROR
	 * @return string json字符串
	 */
	static function validNotEmptyErrToJsonAndOutput($errMsg, $output=true, $code=1) {
		return ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output, $code);
	}	
	
	/**
	 * “字段”匹配校验不通过的错误信息封装为json字符串
	 * @param string $fieldName 字段名
	 * @param boolean $output	是否输出到页面，默认true
	 * @param int $code 错误代码，默认EBStateCode::$EB_STATE_ERROR
	 * @return string json字符串
	 */
	static function fieldValidNotMatchedErrToJsonAndOutput($fieldName, $output=true, $code=1) {
		$outputMsg = 'field '.$fieldName.' is not matched';
		return ResultHandle::errorToJsonAndOutput($outputMsg, $outputMsg, $output, $code);
	}
	
	/**
	 * 匹配校验不通过的错误信息封装为json字符串
	 * @param string $errMsg 错误信息
	 * @param boolean $output	是否输出到页面，默认true
	 * @param int $code 错误代码，默认EBStateCode::$EB_STATE_ERROR
	 * @return string json字符串
	 */
	static function validNotMatchedErrToJsonAndOutput($errMsg, $output=true, $code=1) {
		return ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output, $code);
	}
	
	/**
	 * 缺少主键错误信息封装为json字符串
	 * @param string $pkFieldName 字段名
	 * @param boolean $output	是否输出到页面，默认true
	 * @param int $code 错误代码，默认EBStateCode::$EB_STATE_ERROR
	 * @return string json字符串
	 */
	static function missedPrimaryKeyErrToJsonAndOutput($pkFieldName, $output=true, $code=1) {
		$outputMsg = 'miss primary key: '.$pkFieldName;
		return ResultHandle::errorToJsonAndOutput($outputMsg, $outputMsg, $output, $code);
	}
	
	/**
	 * 查询列表结果封装为json字符串，并输出到页面
	 * @param mixed $result 查询列表结果
	 * @param boolean $output 是否输出到页面，默认true
	 * @param string $errMsg 错误信息，默认不填
	 * @param $totalCount 总记录数
	 * @param $formObj 表单交互对象
	 * @return string json字符串
	 */
	static function listedResultToJsonAndOutput($result, $output=true, $errMsg=NULL, $totalCount=NULL, $formObj=NULL) {
		$json = ResultHandle::failureResultToJson($result, EBStateCode::$EB_STATE_ERROR, $errMsg);
		if (!empty($json)) {
			if ($output)
				echo $json;
			return $json;
		}
		
		$rets = array();
		if (empty($result) || is_array($result)) { //查询成功
			$rets['code'] = EBStateCode::$EB_STATE_OK;
			if (isset($totalCount))
				$rets['total'] = $totalCount;
			
			if (isset($formObj)) {
				//$formObj->setRecordCount($totalCount); //保存总记录数
				$pager = $formObj->getPager();
			}
			
			if (!empty($pager)) { //分页交互参数模式
				$pager->isSuccess = true;
				//$pager->recordCount = $totalCount?:0;
				//$pager->pageCount = (int)(($pager->recordCount+$pager->pageSize-1)/$pager->pageSize);
				$pager->exhibitDatas = $result;
				
				$rets['pager'] = $pager;
			} else { //普通参数模式
				$rets['results'] = $result;
			}
			
			$json = json_encode($rets);
			if ($output)
				echo $json;
		} else { //查询失败
			$rets['code'] = EBStateCode::$EB_STATE_ERROR;
			if (isset($errMsg))
				$rets['msg'] = $errMsg;
			
			$json = json_encode($rets);
			if ($output)
				echo $json;
		}
		return $json;
	}
	
	/**
	 * 查询记录数量结果封装为json字符串，并输出到页面
	 * @param mixed $result 查询列表结果
	 * @param boolean $output 是否输出到页面，默认true
	 * @param string $errMsg 错误信息，默认不填
	 * @param number $count 输出参数，记录数量
	 * @return string json字符串
	 */
	static function countedResultToJsonAndOutput($result, $output=true, &$errMsg=NULl, &$count) {
		if (isset($count))
			$count = 0;
			
		$json = ResultHandle::failureResultToJson($result, EBStateCode::$EB_STATE_ERROR, $errMsg);
		if (!empty($json)) {
			if ($output)
				echo $json;
			return $json;
		}
		
		$rets = array();
		if (!empty($result) && count($result)>0) {
			$rets['code'] = EBStateCode::$EB_STATE_OK;
			if (isset($count)) {
				$count = (int)$result[0]['record_count'];
				$rets['count'] = $count;
			} else {
				$rets['count'] = (int)$result[0]['record_count'];
			}
			
			$json = json_encode($rets);
			if ($output)
				echo $json;
		} else {
			$rets['code'] = EBStateCode::$EB_STATE_ERROR;
			if (isset($errMsg))
				$rets['msg'] = $errMsg;
			
			$json = json_encode($rets);
			if ($output)
				echo $json;
		}
		return $json;
	}
	
	/**
	 * 创建记录结果封装为json字符串，并输出到页面
	 * @param mixed $result 创建记录结果
	 * @param boolean $output 是否输出到页面，默认true
	 * @param string $errMsg 错误信息，默认不填
	 * @return string json字符串
	 */
	static function createdResultToJsonAndOutput($result, $output=true, &$errMsg=NULl) {
		$json = ResultHandle::failureResultToJson($result, EBStateCode::$EB_STATE_ERROR, $errMsg);
		if (!empty($json)) {
			if ($output)
				echo $json;
			return $json;
		}
		
		$rets = array();
		if ($result!='0') {
			$rets['code'] = EBStateCode::$EB_STATE_OK;
			$rets['id'] = $result;
			$json = json_encode($rets);
			if ($output)
				echo $json;
		} else {
			$rets['code'] = EBStateCode::$EB_STATE_ERROR;
			if (isset($errMsg))
				$rets['msg'] = $errMsg;
			
			$json = json_encode($rets);
			if ($output)
				echo $json;
		}
		return $json;
	}
	
	/**
	 * 更新记录结果封装为json字符串，并输出到页面
	 * @param mixed $result 更新记录结果
	 * @param boolean $output 是否输出到页面，默认true
	 * @param string $errMsg 错误信息，默认不填
	 * @return string json字符串
	 */
	static function updatedResultToJsonAndOutput($result, $output=true, $errMsg=NULl) {
		$json = ResultHandle::failureResultToJson($result, EBStateCode::$EB_STATE_ERROR, $errMsg);
		if (!empty($json)) {
			if ($output)
				echo $json;
			return $json;
		}
		
		$rets = array();
		if ($result[0]===true) {
			$rets['code'] = EBStateCode::$EB_STATE_OK;
			$rets['affected'] = (int)$result[1];
			$json = json_encode($rets);
			if ($output)
				echo $json;
		} else {
			$rets['code'] = EBStateCode::$EB_STATE_ERROR;
			if (isset($errMsg))
				$rets['msg'] = $errMsg;
			
			$json = json_encode($rets);
			if ($output)
				echo $json;
		}
		return $json;
	}
	
	/**
	 * 删除记录结果封装为json字符串，并输出到页面
	 * @param mixed $result 删除记录结果
	 * @param boolean $output 是否输出到页面，默认true
	 * @param string $errMsg 错误信息，默认不填
	 * @return string json字符串
	 */
	static function deletedResultToJsonAndOutput($result, $output=true, &$errMsg=NULl) {
		$json = ResultHandle::failureResultToJson($result, EBStateCode::$EB_STATE_ERROR, $errMsg);
		if (!empty($json)) {
			if ($output)
				echo $json;
			return $json;
		}
		
		$rets = array();
		if ($result[0]===true) {
			$rets['code'] = EBStateCode::$EB_STATE_OK;
			$rets['affected'] = (int)$result[1];
			$json = json_encode($rets);
			if ($output)
				echo $json;
		} else {
			$rets['code'] = EBStateCode::$EB_STATE_ERROR;
			if (isset($errMsg))
				$rets['msg'] = $errMsg;
			
			$json = json_encode($rets);
			if ($output)
				echo $json;
		}
		return $json;
	}
}