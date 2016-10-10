<?php
require_once 'EBTask.class.php';

/**
 * 任务-表单自动绑定类
 */
class EBTaskForm extends EBTask
{
	/**
	 * 任务名称-模糊查询
	 * @var string
	 */
	public $task_name_lk;
	
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
	 * 未完成， $status_uncomplete==1表示查询status<=2
	 * @var int
	 */
	public $status_uncomplete;
	
	/**
	 * {@inheritDoc}
	 * @see EBTask::setValuesFromRequest()
	 */
	public function setValuesFromRequest($instance=NULL) {
		parent::setValuesFromRequest(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBTask::createWhereConditions()
	 */
	public function createWhereConditions($instance=NULL) {
		return parent::createWhereConditions(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBTask::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('status_uncomplete');
		return array_merge($parentCheckDigits, $checkDigits);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBTask::validNotEmpty()
	 */
	public function validNotEmpty($fieldNames, &$outErrMsg, $instance=NULL) {
		return parent::validNotEmpty($fieldNames, $outErrMsg, isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::validDigit()
	 */
	public function validDigit($fieldNames, &$outErrMsg, $instance=NULL) {
		return parent::validDigit($fieldNames, $outErrMsg, isset($instance)?$instance:$this);
	}
	
	/**
	 * 验证表单字段逻辑合法性
	 * @param $outJson 输出 验证不通过结果字符串(json封装)
	 * @param boolean $output	是否输出到页面，默认true
	 * @return boolean 验证结果：true=通过，false=不通过
	 */
	public function validFormFields(&$outJson, $output=true) {
		//验证from_type
		if (isset($this->from_type)) {
			if (!in_array($this->from_type, array('0', '1', '2'))) { //0：新建 1：计划转任务（from_id=任务编号）2：拆分子任务（from_id=父任务编号）
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('from_type', $output);
				return false;
			}
			if ($this->from_type=='1'||$this->from_type=='2') {
				//验证from_id必填
				if (!$this->validNotEmpty('from_id', $outErrMsg)) {
					$outJson = ResultHandle::validNotEmptyErrToJsonAndOutput($outErrMsg, $output);
					return false;
				}
			}
		}
		
		//数字验证from_id
		if (isset($this->from_id)) {
			if (!$this->validDigit('from_id', $outErrMsg)) {
				$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
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
			if (!in_array($this->status, array('0', '1', '2', '3', '4'))) {
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
	
		//验证percentage
		if (isset($this->percentage)) {
			$percentage = $this->percentage;
			if (!$this->validDigit('percentage', $outErrMsg)) {
				$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
				return false;
			}
			if ((int)$percentage<0 || (int)$percentage>100) {
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('percentage', $output);
				return false;
			}
		}
		
		//验证work_time
		if (isset($this->work_time)) {
			$work_time = $this->work_time;
			if (!$this->validDigit('work_time', $outErrMsg)) {
				$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
				return false;
			}
			if ((int)$work_time<0) {
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('work_time', $output);
				return false;
			}
		}
		
		//验证im_group_id
		if (isset($this->im_group_id)) {
			$im_group_id = $this->im_group_id;
			if (!$this->validDigit('im_group_id', $outErrMsg)) {
				$outJson = ResultHandle::validNotDigitErrToJsonAndOutput($outErrMsg, $output);
				return false;
			}
			if ((int)$im_group_id<=0) {
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('im_group_id', $output);
				return false;
			}
		}
		
		return true;
	}
}