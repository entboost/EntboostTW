<?php
require_once 'EBOperateRecord.class.php';

class EBOperateRecordForm extends EBOperateRecord
{
	/**
	 * 来源名称-模糊查询
	 * @var string
	 */
	public $from_name_lk;
	/**
	 * 操作用户名称-模糊查询
	 * @var string
	 */
	public $user_name_lk;
	/**
	 * 创建时间范围-起点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $create_time_s;
	/**
	 * 创建时间范围-终点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $create_time_e;
	/**
	 * 最后修改时间范围-起点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $last_modify_time_s;
	/**
	 * 最后修改时间范围-终点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $last_modify_time_e;
	/**
	 * 操作类型的分类
	 * $op_type_class=1表示：	评论/回复动态：op_type>=3 AND op_type<=4
	 * $op_type_class=2表示：	成员：op_type>=10 AND op_type<=16
	 * $op_type_class=3表示：	评审：op_type>=20 AND op_type<=24
	 * $op_type_class=4表示：	进度：op_type=31 OR op_type=32
	 * @var int
	 */
	public $op_type_class;
	
	/**
	 * 创建时间范围及创建者编号查询条件
	 * 格式：array(array('create_time_s'=>'2016-05-31 00:00:00', 'create_time_e'=>'2016-05-31 23:23:59', 'user_id'=>'123456'), ...)
	 * @var array
	 */
	public $datetimeAndUseridConditions;
	
	/**
	 * 是否分类统计查询
	 * @var int
	 */
	public $classification_statistic;
	/**
	 * 是否最新动态查询
	 * @var int
	 */
	public $ptrnews;
	
	/**
	 * {@inheritDoc}
	 * @see EBOperateRecord::setValuesFromRequest()
	 */
	public function setValuesFromRequest($instance=NULL) {
		parent::setValuesFromRequest(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBOperateRecord::createWhereConditions()
	 */
	public function createWhereConditions($instance=NULL) {
		return parent::createWhereConditions(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBOperateRecord::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('op_type_class');
		return array_merge($parentCheckDigits, $checkDigits);
	}
	
	/**
	 * 验证op_type逻辑合法性
	 * @param $outJson 输出参数 验证不通过结果字符串(json封装)
	 * @param boolean $output	是否输出到页面，默认true
	 * @return boolean 验证结果：true=通过，false=不通过
	 */
	public function validOpType(&$outJson, $output=true) {
		if (!$this->validDigit('op_type', $outErrMsg)) {
			$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
			return false;
		}
		
		$opType = (int)$this->op_type;
		if ($opType<0 || $opType>100) { //更具体的数值，待定
			$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('op_type', $output);
			return false;
		}
		
		//解析校验各种操作情况
		switch ($opType) {
			case 1: //添加附件（op_data=文件ID，op_name=文件名称）
				//非空检测
				if (!$this->validNotEmpty('op_data, op_name', $outErrMsg)) {
					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
				//数字检测
				if (!$this->validDigit('op_data', $outErrMsg)) {
					$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
				
				break;
				
			case 2: //删除附件（op_name=文件名称）
				//非空检测
				if (!$this->validNotEmpty('op_name', $outErrMsg)) {
					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
				
				break;
			case 3: //评论备注（op_data=文件ID，op_name=文件名称，remark=备注(支持修改)）
				$isEmpty1 = !$this->validNotEmpty('op_data, op_name', $outErrMsg1);
				$isEmpty2 = !$this->validNotEmpty('remark', $outErrMsg2);
				if ($isEmpty1 && $isEmpty2) {
					$errMsg = '(op_data, op_name) or remark can not all be empty in same time';
					$outJson = ResultHandle::errorToJsonAndOutput($errMsg, $errMsg, $output);
					return false;
				}
				
				break;
			case 4: //删除评论备注(内容)
				break;
			case 5: //删除评论附件
				if (!$this->validNotEmpty('op_data, op_name', $outErrMsg)) {
					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}		
				break;
			case 10: //变更负责人（op_data=负责人ID，op_name‐负责人名称）
			case 11: //添加参与人（op_data=参与人ID，op_name=参与人名称）
			case 12: //删除参与人（op_data=参与人ID，op_name=参与人名称）
			case 13: //添加共享人（op_data=共享人ID，op_name=共享人名称）
			case 14: //删除共享人（op_data=共享人ID，op_name=共享人名称）
				if (!$this->validNotEmpty('op_data, op_name', $outErrMsg)) {
					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
				break;
			case 15: //创建IM临时讨论组（op_data=讨论组ID，op_name=讨论组名称）
			case 16: //解散IM临时讨论组（op_data=讨论组ID，op_name=讨论组名称）
				if (!$this->validNotEmpty('op_data, op_name', $outErrMsg)) {
					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
				break;
				
			case 20: //提交评审/评阅（op_data=评审人ID，op_name=评审人名称）
				if (!$this->validNotEmpty('op_data, op_name', $outErrMsg)) {
					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
				break;
			case 21: //评审/评阅已阅
				break;
			case 22: //评审通过/评阅回复（remark=备注）
			case 23: //评审拒绝（remark=备注）
			case 24: //取消上报
				break;
				
			case 30: //新负责人已阅
				break;
			case 31: //上报进度（op_data=进度百分比0‐100，remark=工作内容(支持修改)）
			case 32: //上报工时（op_data=工时分钟，remark=工作内容(支持修改)）
// 				if (!$this->validNotEmpty('op_data', $outErrMsg)) {
// 					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
// 					return false;
// 				}
				if (!$this->validDigit('op_data', $outErrMsg)) {
					$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
				if (($opType==31 && (int)$this->op_data>100) || ($opType==32 && (int)$this->op_data>24*60) || (int)$this->op_data<0) {
					$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('op_data', $output);
					return false;
				}
				//上报工时必须填内容和操作时间
				if ($opType==32) {
					if (!$this->validNotEmpty('op_time, remark', $outErrMsg)) {
						$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
						return false;
					}
// 					if (!$this->validNotEmpty('remark', $outErrMsg)) {
// 						$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
// 						return false;
// 					}
				}
				break;
			case 33: //标为中止（remark=备注）
				if (!$this->validNotEmpty('remark', $outErrMsg)) {
					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
				break;
			case 34: //标为完成
				break;
				
			case 50: //新建计划
			case 51: //修改计划
				break;
			case 52: //计划转任务（op_data=任务ID，op_name=任务名称）
				if (!$this->validNotEmpty('op_data, op_name', $outErrMsg)) {
					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
				break;
			
			case 60: //新建任务
			case 61: //修改任务
				break;
			case 62: //拆分子任务（op_data=子任务ID，op_name=子任务名称）
				if (!$this->validNotEmpty('op_data, op_name', $outErrMsg)) {
					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
				break;
		}
		
		return true;
	}
	
}