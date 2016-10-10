<?php

class Pager {
	
	/**
	 * 是否出错
	 */
	public $isSuccess;
	
	/**
	 * 每页显示条数
	 */
	public $pageSize;
	
	/**
	 * 开始记录数
	 */
	public $startRecord;
	
	/**
	 * 当前页数
	 */
	public $nowPage;
	
	/**
	 * 记录总数
	 */
	public $recordCount;
	
	/**
	 * 总页数
	 */
	public $pageCount;
	
	/**
	 * 参数列表
	 */
	public $parameters;
	
	/**
	 * 快速查询参数列表
	 */
	public $fastQueryParameters;
	
	/**
	 * 高级查询列表
	 */
	public $advanceQueryConditions;
	
	/**
	 * 高级排序列表
	 */
	public $advanceQuerySorts;
	
	/**
	 * 显示数据集
	 */
	public $exhibitDatas;
	
	/**
	 * 是否导出：1-是，0-否
	 */
	public $isExport;
	
	/**
	 * 导出类型，支持excel、pdf、txt、cvs
	 */
	public $exportType;
	
	/**
	 * 导出文件名
	 */
	public $exportFileName;
	
	/**
	 * 导出列
	 */
	public $exportColumns;
	
	/**
	 * 全部数据导出
	 */
	public $exportAllData;
	
	/**
	 * 导出数据是否已被加工
	 */
	public $exportDataIsProcessed;
	
	/**
	 * 导出数据
	 */
	public $exportDatas;
	
}
?>