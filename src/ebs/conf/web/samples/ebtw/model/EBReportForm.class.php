<?php
require_once 'EBReport.class.php';

/**
 * 报告-表单自动绑定类
 */
class EBReportForm extends EBReport
{
	/**
	 * 报告类型
	 * 0 普通报告 表示查询period <> 1
	 * 1 日报 表示查询period = 1
	 * @var int
	 */
	public $daily;
	
	/**
	 * 工作内容-模糊查询
	 * 包括已完成工作和未完成工作
	 * @var string
	 */
	public $report_work_lk;	
	
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
	 * 未完成， $overdue==1表示查询create_time > stop_time
	 * @var int
	 */
	public $overdue;
	
	/**
	 * 1=当前月创建的日报
	 * 其他情况=不受本字段限制
	 * @var int
	 */
	public $this_month;
	
	/**
	 * {@inheritDoc}
	 * @see EBReport::setValuesFromRequest()
	 */
	public function setValuesFromRequest($instance=NULL) {
		parent::setValuesFromRequest(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBReport::createWhereConditions()
	 */
	public function createWhereConditions($instance=NULL) {
		return parent::createWhereConditions(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBReport::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array(/*'status_uncomplete'*/);
		return array_merge($parentCheckDigits, $checkDigits);
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see EBReport::validNotEmpty()
	 */
	public function validNotEmpty($fieldNames, &$outErrMsg, $instance=NULL) {
		return parent::validNotEmpty($fieldNames, $outErrMsg, isset($instance)?$instance:$this);
	}
	
	/**
	 * 验证表单字段逻辑合法性
	 * @param $outJson 输出 验证不通过结果字符串(json封装)
	 * @param boolean $output	是否输出到页面，默认true
	 * @return boolean 验证结果：true=通过，false=不通过
	 */
	public function validFormFields(&$outJson, $output=true) {
		//验证period
		if (isset($this->period)) {
			if (!in_array($this->period, array('1', '2', '3', '4', '5'))) {
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('period', $output);
				return false;
			}
		}
	
		//验证self_mood
		if (isset($this->self_mood)) {
			if (!in_array($this->self_mood, array('1', '2', '3', '4', '5'))) {
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('self_mood', $output);
				return false;
			}
		}
		
		//验证status
		if (isset($this->status)) {
			if (!in_array($this->status, array('0', '1', '2', '3'))) {
				$outJson = ResultHandle::fieldValidNotMatchedErrToJsonAndOutput('status', $output);
				return false;
			}
		}
	
		return true;
	}
}