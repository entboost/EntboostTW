<?php
require_once 'EBPlan.class.php';

/**
 * 计划-表单自动绑定类
 */
class EBPlanForm extends EBPlan
{
	/**
	 * 计划名称-模糊查询
	 * @var string
	 */
	public $plan_name_lk;
	
	/**
	 * 查询时间范围-起点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $search_time_s;
	/**
	 * 查询时间范围-终点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $search_time_e;	
	
	/**
	 * 开始时间范围-起点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $start_time_s;
	
	/**
	 * 开始时间范围-终点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $start_time_e;	
	
	/**
	 * 结束时间范围-起点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $stop_time_s;
	
	/**
	 * 结束时间范围-终点
	 * @var string 格式如：2016-01-20 11:00:01
	 */
	public $stop_time_e;
	
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
	 * 未完成， $status_uncomplete==1表示查询status<=5
	 * @var int
	 */
	public $status_uncomplete;
	
	/**
	 * 已废弃
	 * 评审中，$status_reviewing==1表示查询status=2
	 * @var int
	 */
	//public $status_reviewing;
	
	/**
	 * 任务编号
	 * @var string
	 */
	public $task_id;
	
	/**
	 * 查询主键编号
	 * @var string
	 */	
	public $pk_plan_id;
	/**
	 * 操作类型
	 * @var int
	 */
	public $op_type;
	
	/**
	 * {@inheritDoc}
	 * @see EBPlan::getOrderby()
	 */
	public function getOrderby() {
		$orderby = parent::getOrderby();
// 		//修正排序字段名，补充待定
// 		if (!empty($orderby)) {
// 			$orderby = preg_replace('/create_username/i', 'create_uid', $orderby);
// 		}
		return $orderby;
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBPlan::setValuesFromRequest()
	 */
	public function setValuesFromRequest($instance=NULL) {
		parent::setValuesFromRequest(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBPlan::createWhereConditions()
	 */
	public function createWhereConditions($instance=NULL) {
		return parent::createWhereConditions(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::removeKeepFields()
	 */
	public function removeKeepFields(&$fields) {
		parent::removeKeepFields($fields);
	
		unset($fields['task_id']);
		unset($fields['pk_plan_id']);
		unset($fields['op_type']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBPlan::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('status_uncomplete', 'status_reviewing');
		return array_merge($parentCheckDigits, $checkDigits);
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see EBPlan::validNotEmpty()
	 */
	public function validNotEmpty($fieldNames, &$outErrMsg, $instance=NULL) {
		return parent::validNotEmpty($fieldNames, $outErrMsg, $this, isset($instance)?$instance:$this);
	}
	
	/**
	 * 验证表单字段逻辑合法性
	 * @param $outJson 输出参数 验证不通过结果字符串(json封装)
	 * @param boolean $output	是否输出到页面，默认true
	 * @return boolean 验证结果：true=通过，false=不通过
	 */
	public function validFormFields(&$outJson, $output=true) {
		//验证from_type
		if (isset($this->from_type)) {
			if (!in_array($this->from_type, array('0', '1'))) {
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('from_type', $output);
				return false;
			}
			if ($this->from_type=='1') {
				//验证from_id必填
				if (!$this->validNotEmpty('from_id', $outErrMsg)) {
					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
			}
		}
		
		//验证period
		if (isset($this->period)) {
			if (!in_array($this->period, array('1', '2', '3', '4', '5', '6'))) {
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('period', $output);
				return false;
			}
		}
		
		//验证important
		if (isset($this->important)) {
			if (!in_array($this->important, array('0', '1', '2'))) {
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('important', $output);
				return false;
			}
		}
		
		//验证status
		if (isset($this->status)) {
			if (!in_array($this->status, array('0', '1', '2', '3', '4', '5', '6'))) {
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('status', $output);
				return false;
			}
		}
		
		//验证open_flag
		if (isset($this->open_flag)) {
			if (!in_array($this->open_flag, array('0', '1', '2'))) {
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('open_flag', $output);
				return false;
			}
		}
		
		return true;
	}
}