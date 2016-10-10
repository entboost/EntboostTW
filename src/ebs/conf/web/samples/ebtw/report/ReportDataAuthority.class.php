<?php
require_once dirname(__FILE__).'/../DataAuthority.class.php';

class ReportDataAuthority extends DataAuthority
{
	/**
	 * 使用表连接方式查询验证是否存在符合条件的报告记录(通常用于检验是否有数据权限)
	 * @param boolean|array $outExistRows false查询失败或查询结果数组
	 * @param string $userId 用户编号(数字)
	 * @param array $wheres 查询条件数组
	 * @param array $checkDigits 数字校验数组
	 * @param AbstractService $instance 执行任务的服务对象
	 * @param boolean $output 是否输出到页面
	 * @param string $outErrMsg 输出参数 检测错误信息
	 * @param string $json 输出参数 JSON字符串，验证结果
	 * @return boolean 验证结果：false=不通过，true=通过
	 */
	function isRowExists_sharedReport(&$outExistRows, $userId, $wheres, $checkDigits=NULL, $instance=NULL, $output=true, &$outErrMsg=NULL, &$json=NULL) {
		$tableNameAlias = array('eb_report_info_t'=>'t_a', 'eb_share_user_t'=>'t_b');
		$conditions = array($userId);
		$paramsGroup = array('eb_report_info_t'=>$wheres);
		$shareUserFormObj = new EBShareUserForm();
		$checkDigitsGroup = array('eb_report_info_t'=>$checkDigits, 'eb_share_user_t'=>$shareUserFormObj->createCheckDigits());
		
		$fieldNames = 't_a.report_id, t_a.modify_count';
		$fieldNames1 = 't_b.from_id as su_from_id, t_b.from_type as su_from_type, t_b.create_time as su_create_time';
		$fieldNames = $fieldNames . ', ' . $fieldNames1;
		
		$prefixSql = 'select ' . $fieldNames . ' from eb_report_info_t t_a, eb_share_user_t t_b where t_a.report_id=t_b.from_id and t_b.share_type=1 and t_b.from_type=3 and t_b.share_uid = ?';
		
		return parent::isRowExists_usingJoinSearch($outExistRows, $tableNameAlias, $prefixSql, $conditions, $paramsGroup, $checkDigitsGroup, $instance?:$this->instance, 1, SQLParamComb_TYPE_AND, $output, $outErrMsg, $json);
	}
}