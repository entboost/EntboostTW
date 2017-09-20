<?php
require_once 'EBModelBase.class.php';

class EBOperateRecord extends EBModelBase
{
	/**
	 * 编号(数字)
	 * @var string
	 */
	public $op_id;
	/**
	 * 来源标识
	 * 1 计划（from_id=plan_id）
	 * 2 任务（from_id=task_id）
	 * @var int
	 */
	public $from_type;
	/**
	 * 来源ID(数字)，配合fromType
	 * @var string
	 */
	public $from_id;
	/**
	 * 来源名称
	 * 1 计划标题
	 * 2 任务标题
	 * @var string
	 */
	public $from_name;
	/**
	 * 操作者用户编号(数字)
	 * @var string
	 */
	public $user_id;
	/**
	 * 操作者名称
	 * @var string
	 */
	public $user_name;
	
	/**
	 * 操作类型
	 * 1：添加附件（op_data=文件ID，op_name=文件名称）
	 * 2：删除附件（op_name=文件名称）
	 * 3：评论备注（op_data=文件ID，op_name=文件名称，remark=备注，支持修改）
	 * 4：删除评论备注
	 * 10：变更负责人（op_data=负责人ID，op_name‐负责人名称）
	 * 11：添加参与人（op_data=参与人ID，op_name=参与人名称）
	 * 12：删除参与人（op_data=参与人ID，op_name=参与人名称）
	 * 13：添加共享人（op_data=共享人ID，op_name=共享人名称）
	 * 14：删除共享人（op_data=共享人ID，op_name=共享人名称）
	 * 15：创建IM临时讨论组（op_data=讨论组ID，op_name=讨论组名称）
	 * 16：解散IM临时讨论组（op_data=讨论组ID，op_name=讨论组名称）
	 * 20：提交评审/评阅（op_data=评审人ID，op_name=评审人名称）
	 * 21：评审/评阅已阅
	 * 22：评审通过/评阅回复（remark=备注）
	 * 23：评审拒绝（remark=备注）
	 * 24: 取消上报 
	 * 30：新负责人已阅
	 * 31：上报进度（op_data=进度百分比0‐100，remark=工作内容，支持修改）
	 * 32：上报工时（op_data=工时分钟，remark=工作内容，支持修改）
	 * 33：标为中止（remark=备注）
	 * 34：标为完成
	 * 50：新建计划
	 * 51：修改计划
	 * 52：计划转任务（op_data=任务ID，op_name=任务名称）
	 * 60：新建任务
	 * 61：修改任务
	 * 62：拆分子任务（op_data=子任务ID，op_name=子任务名称）
	 * @var int
	 */
	public $op_type;
	
	/**
	 * 操作数据ID，配合op_type
	 * 进度：百分比0－100
	 * 工时：工时分钟
	 * 附件：file_list_t‐>fl_id
	 * (数字)
	 * @var string
	 */
	public $op_data;
	/**
	 * 操作数据名称，配合op_type
	 * 附件：文件名称
	 * 人员：人员名称
	 * @var string
	 */
	public $op_name;
	/**
	 * 操作时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $op_time;
	/**
	 * 备注
	 * @var string
	 */
	public $remark;
	/**
	 * 创建时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $create_time;
	/**
	 * 更新时间，格式如：2016-01-20 11:20:01
	 * @var string
	 */
	public $last_modify_time;
	/**
	 * 修改次数
	 * @var int
	 */
	public $modify_count;
	/**
	 * 删除标识
	 * 0 正常数据
	 * 1 删除放入回收站
	 * @var int
	 */
	public $is_deleted;
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::setValuesFromRequest()
	 */
	public function setValuesFromRequest($instance=NULL) {
		parent::setValuesFromRequest(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createWhereConditions()
	 */
	public function createWhereConditions($instance=NULL) {
		return parent::createWhereConditions(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createFields()
	 */
	public function createFields($instance=NULL) {
		return parent::createFields(isset($instance)?$instance:$this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::removeKeepFields()
	 */
	public function removeKeepFields(&$fields) {
		parent::removeKeepFields($fields);
	
		unset($fields['op_id']);
		unset($fields['user_id']);
		unset($fields['user_name']);
		unset($fields['create_time']);
		unset($fields['last_modify_time']);
		unset($fields['modify_count']);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::createCheckDigits()
	 */
	public function createCheckDigits($instance=NULL) {
		$parentCheckDigits = parent::createCheckDigits(isset($instance)?$instance:$this);
		$checkDigits = array('op_id', 'from_type', 'from_id', 'user_id', 'op_type', 'op_data', 'modify_count', 'is_deleted');
		return array_merge($parentCheckDigits, $checkDigits);
	}
	
	/**
	 * {@inheritDoc}
	 * @see EBModelBase::validNotEmpty()
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
}