<?php
require_once 'EBClassification.class.php';

/**
 * 分类-表单自动绑定类
 */
class EBClassificationForm extends EBClassification
{
	/**
	 * 分类名称-模糊查询
	 * @var string
	 */
	public $class_name_lk;
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
	 * {@inheritDoc}
	 * @see EBClassification::setValuesFromRequest()
	 */
	public function setValuesFromRequest($instance=NULL) {
		parent::setValuesFromRequest(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBClassification::createWhereConditions()
	 */
	public function createWhereConditions($instance=NULL) {
		return parent::createWhereConditions(isset($instance)?$instance:$this);
	}
}