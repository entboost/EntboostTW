<?php

/**
 * 严格过滤处理JSON字符串
 * @param {string} $str
 */
function strictJson($str) {
	return str_replace("\\", "\\\\", $str);
}

/**
 * 单/双引号转义
 * @param {string} $str 待处理字符串
 * @param {boolean} $single 是否单引号，默认单引号
 * @return {string}
 */
function escapeQuotes($str, $single=true) {
	if (!isset($str))
		return;
		
	if ($single)
		$chr = '\'';
	else 
		$chr ='\"';
	$temp = str_replace($chr, '\\'.$chr, $str);
// 	log_info('=========================================');
// 	log_info($temp);
	return $temp;
}

/**
 * 单/双引号转义(适用于输出到html页面，尤其是input控件的value值)
 * @param {string} $str 待处理字符串
 * @return {string}
 */
function escapeQuotesToHtml($str) {
	$str_temp = str_replace("\'", '&#39;', $str);
	$str_temp = str_replace("\"", '&#34;', $str);
	
	return $str_temp;
}

/**
 * 控制字符转义
 * @param {string} $str
 * @return string 如果传入空值或非字符串，将返回空字符串""
 */
// function escapeControlCharacters($str) {
// 	if (empty($str) || !is_string($str))
// 		return '';
// 	return preg_replace('/([\r\n])+/i', '\n', $str);
// }

/**
 * 控制字符转换为html标签
 * @param {string} $str
 * @return string 如果传入空值或非字符串，将返回空字符串""
 */
function controlCharactersToHtml($str) {
	if (empty($str) || !is_string($str))
		return '';
	
	return preg_replace('/([\r\n])+/i', '<br>', $str);
}

/**
 * 从json数据提取记录列表对象
 * @param {string} $json JSON字符串
 * @param {object} $outObj 输出参数 经过JSON转换的对象
 * @return {array} 列表数组
 */
function get_results_from_json($json, &$outObj) {
	$results = null;
	if (!empty($json)) {
		$outObj = json_decode($json);
		if ($outObj->code==0 && (isset($outObj->pager)||isset($outObj->results)) ) {
			if (isset($outObj->pager))
				$results = $outObj->pager->exhibitDatas;
			else
				$results = $outObj->results;
		}
		
		if ($outObj->code!=0)
			log_err($json);
	}
	
	return $results;
}

/**
 * 从json数据提取第一个对象
 * @param {string} $json JSON字符串
 * @param {object} $outEntity 输出参数 记录对象；注意：如果不存在第一个对象，则本参数的值将不改变
 * @param {object} $outObj 输出参数 经过JSON转换的对象
 * @return {object} 记录对象
 */
function get_first_entity_from_json($json, &$outEntity, &$outObj) {
	$results = get_results_from_json($json, $outObj);
	if(!empty($results)) {
		$outEntity = $results[0];
		return $outEntity;
	}
	return null;
}

/**
 * 从json数据提取指定字段值
 * @param {string} $json JSON字符串
 * @param {$fieldName} 字段名
 * @param {object} $outObj 输出 经过JSON转换的对象
 * @return {mixed}
 */
function get_field_value_from_json($json, $fieldName, &$outObj) {
	$outObj = json_decode($json);
	if ($outObj->code==0 && isset($outObj->{$fieldName})) {
		return $outObj->{$fieldName};
	}
}

/**
 * 把记录的指定字段赋值给对象字段
 * @param {array} $record 记录关联数组，数组元素只接受字符串类型
 * @param {object} $object 目标实例对象
 * @param {string} $fieldName 记录字段名
 * @param {string} [可选] $objFieldName 对象字段名，如填空则与$fieldName相同名
 * @param {int} $toType [可选] 转换为目标类型：'int','float','double','string'；如填空则认为是字符串
 * @return boolean 是否成功
 */
function copyFieldOfRecordToObject($record, &$object, $fieldName, $objFieldName=NULL, $toType=NULL) {
	if (!is_array($record) || empty($object) || empty($fieldName))
		return false;
	
	$value = $record[$fieldName];
	if (isset($toType) && isset($value)) {
		if ($toType==='int')
			$value = intval($value);
		else if ($toType==='float')
			$value = floatval($value);
		else if ($toType==='double')
			$value = doubleval($value);
		else if ($toType==='string') {
			//忽略
		} else {
			return false;
		}
	}
	
	if (isset($objFieldName))
		$object->{$objFieldName} = $value;
	else 
		$object->{$fieldName} = $value;
	
	return true;
}

/**
 * 比较两个时间相差的分钟数
 * @param {string} $time1 时间1
 * @param {string} $time2 时间2
 * @return int 相差的分钟数
 */
function diffMinutesBetweenTwoTimes($time1, $time2) {
	$second1 = strtotime($time1);
	$second2 = strtotime($time2);
	
	if ($second1 < $second2) {
		$tmp = $second2;
		$second2 = $second1;
		$second1 = $tmp;
	}
	return (int)(($second1 - $second2)/60);
}

/**
 * 比较后输出html脚本，默认输出"单选项选中"脚本
 * @param {object|array} $entity 对象或关联数组
 * @param {string} $fieldName 字段名
 * @param {mixed} $value 待比较值
 * @param {mixed} $defaultValue [可选] 默认比较值
 * @param {boolean} $nullFieldSet [可选] 当字段空值时，是否使用$value与$defaultValue比较后来决定输出checked(选中)；默认否
 * @param {boolean} $notEqual [可选] 是否使用'不等于'进行比较，默认null('等于'比较) 
 * @param {string} $echoText [可选] 可指定的输出(echo)文本
 */
function inputChecked($entity, $fieldName, $value, $defaultValue=0, $nullFieldSet=false, $notEqual=null, $echoText=null) {
	$checkedStr = isset($echoText)?$echoText:'checked=true';;
	
	if (empty($entity)) {
		if ($notEqual===true) {
			if ($value!=$defaultValue)
				echo $checkedStr;
		} else {
			if ($value==$defaultValue)
				echo $checkedStr;
		}
		return;
	}
	
	if (is_object($entity)) {
		if (property_exists($entity, $fieldName) && $entity->{$fieldName}!=null) {
			if ($notEqual===true) {
				if ($entity->{$fieldName}!=$value)
					echo $checkedStr;
			} else {
				if ($entity->{$fieldName}==$value)
					echo $checkedStr;
			}
			return;
		}
		
		if ($nullFieldSet!==false) {
			if ($notEqual===true) {
				if ($value!=$defaultValue)
					echo $checkedStr;				
			} else {
				if ($value==$defaultValue)
					echo $checkedStr;
			}
			return;
		}
		
		return;
	}
	
	if (is_array($entity)) {
		if (array_key_exists($fieldName, $entity) && $entity[$fieldName]!=null) {
			if ($notEqual===true) {
				if ($entity[$fieldName]!=$value)
					echo $checkedStr;				
			} else {
				if ($entity[$fieldName]==$value)
					echo $checkedStr;
			}
			return;
		}
		
		if ($nullFieldSet!==false) {
			if ($notEqual===true) {
				if ($value!=$defaultValue)
					echo $checkedStr;				
			} else {
				if ($value==$defaultValue)
					echo $checkedStr;
			}
			return;
		}
	}
}

/**
 * 输出字段值
 * @param {array|object} $entity 记录实例
 * @param {string} $fieldName 字段名
 * @param {mixed} $defaultValue 默认输出值，当记录实例不存在或者指定字段不存在时，输出该值；默认情况下不输出
 * @param {boolean} $escapeQuotes 是否进行单双引号转义，默认不转义
 */
function echoField($entity, $fieldName, $defaultValue=NULL, $escapeQuotes=false) {
	if(!empty($entity)) {
		if (is_object($entity)) {
			if (!property_exists($entity, $fieldName)) {
				if (isset($defaultValue))
					echo $defaultValue;
				return;
			}
			$field = $entity->{$fieldName};
			
			if (is_array($field) || is_object($field))
				echo json_encode($field);
			else if (is_bool($field))
				echo $field?'true':'false';
			else {
				if ($escapeQuotes===true)
					echo escapeQuotesToHtml($field);
				else
					echo $field;
			}
			return;
		}
		
		if (is_array($entity)) {
			if (!array_key_exists($fieldName, $entity)) {
				if (isset($defaultValue))
					echo $defaultValue;
				return;
			}
			$field = $entity[$fieldName];
				
			if (is_array($field) || is_object($field))
				echo json_encode($field);
			else if (is_bool($field))
				echo $field?'true':'false';
			else {
				if ($escapeQuotes===true)
					echo escapeQuotesToHtml($field);
				else
					echo $field;
			}
		}
	} else if (isset($defaultValue)){
		echo $defaultValue;
	}
}

//输出权限数组代码
// function echoAllowedActions($entity) {
// 	if (!empty($entity) && !empty($entity->allowedActions))
// 		echo json_encode(array_values((array)$entity->allowedActions));
// 	else
// 		echo '[]';	
// }

//截取日期时间字符串前半段
function subStrOfDateTime($entity, $fieldName) {
	if(!empty($entity))
		return substr($entity->{$fieldName}, 0, 10);
		return '';
}
//截取日期时间字符串(去除秒)
function subStrOfDateTime2($entity, $fieldName) {
	if(!empty($entity))
		return substr($entity->{$fieldName}, 0, 16);
		return '';
}
//截取日期时间字符串(去除毫秒)
function subStrOfDateTime3($entity, $fieldName) {
	if(!empty($entity))
		return substr($entity->{$fieldName}, 0, 19);
		return '';
}
//比较两个数组差异
function custom_array_diff($array1, $array2) {
	if (!isset($array1))
		return;
	if (!isset($array2))
		return $array1;
	return array_diff($array1, $array2);
}

/**
 * 验证日期字符串是否合法
 * @param {string} $dateStr 日期字符串 ，格式如：2017-01-01
 * @return {boolean} true=合法，false=不合法
 */
function validateDateString($dateStr) {
	$regex = '/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/i';
	if(!preg_match($regex, $dateStr))
		return false;
	return true;
}
/**
 * 验证日期时间字符串是否合法
 * @param {string} $dateTimeStr 日期时间字符串，格式如：2017-01-01 00:00:00
 * @return {boolean} true=合法，false=不合法
 */
function validateDateTimeString($dateTimeStr) {
	$regex = '/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) (([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/i';
	if(!preg_match($regex, $dateTimeStr))
		return false;
	return true;
}

/**
 * 获取给定时间相关的时间
 * @param {string} $date 指定时间(字符串格式)
 * @return {object} 一些特殊的时间(或日期)，每个元素为字符串格式；first_day=指定时间当月的第一天，last_day=指定时间当月的最后一天
 */
function getSepcialDaysOfMonth($date) {
	$firstday = date('Y-m-01', strtotime($date));
	$lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
	
	$obj = new stdClass();
	$obj->first_day = $firstday;
	$obj->last_day = $lastday;
	return $obj;
}
/**
 * 提取对象内关联人员资料
 * @param {string|number} $shareType 业务类型：1=评审人/评阅人，2=参与人，3=共享人，4=关注人，5=负责人，6=考勤审批人
 * @param {object} $entity 记录对象
 * @param {boolean} $onlyOne (可选) 是否只获取一个，默认false；当$onlyOne=true时，返回结果为object对象，否则返回结果为array数组
 * @param {string} $shareUid (可选) 指定获取关联人员的编号，默认NULL(不作为条件)
 * @param {number} $validFlag (可选) 指定获取关联人员的有效标识，默认NULL(不作为条件)
 * @return {object|array} 一个或多个关联人员资料，不存在则返回NULL
 */
function getShares($shareType, $entity, $onlyOne=false, $shareUid=NULL, $validFlag=NULL) {
	if (empty($entity))
		return null;
	
	if (is_object($entity)) {
		if (property_exists($entity, "shares") && property_exists($entity->shares, $shareType)) {
			$shares = $entity->shares->{$shareType}; //复制一个数组
			
			//通过"有效标识"过滤记录
			if ($validFlag!=NULL) {
				foreach ($shares as &$mshare) {
					if ($mshare->valid_flag!=$validFlag)
						unset($mshare); //删除不符合"有效标识"条件的
				}
			}
			
			if ($shareUid==NULL) { //没有指定具体关联人员
				if (!$onlyOne)
					return $shares;
				
				if (!empty($shares)) {
					//返回第一个元素
					reset($shares);
					return current($shares);
					//return $shares[0];
				}
			} else { //指定具体关联人员
				if (!$onlyOne) {
					foreach ($shares as &$mshare1) {
						if (empty($mshare1->share_uid) || $mshare1->share_uid!=$shareUid)
							unset($mshare1);
					}
					return $shares;
				}
				
				//遍历匹配指定的关联人员
				foreach ($shares as $share1) {
					if (!empty($share1->share_uid) && $share1->share_uid===$shareUid)
						return $share1;
				}
			}
		}
	} else if (is_array($entity)) {
		if (array_key_exists('shares', $entity) && array_key_exists($shareType, $entity['shares'])) {
			$shares = $entity['shares'][$shareType]; //复制一个数组
			
			//通过"有效标识"过滤记录
			if ($validFlag!=NULL) {
				foreach ($shares as &$mshare) {
					if ($mshare->valid_flag!=$validFlag)
						unset($mshare); //删除不符合"有效标识"条件的
				}
			}
			
			if ($shareUid==NULL) { //没有指定具体关联人员
				if (!$onlyOne)
					return $shares;
		
					if (!empty($shares)) {
						//返回第一个元素
						reset($shares);
						return current($shares);
						//return $shares[0];
					}
			} else { //指定具体关联人员
				if (!$onlyOne) {
					foreach ($shares as &$mshare1) {
						if (empty($mshare1->share_uid) || $mshare1->share_uid!=$shareUid)
							unset($mshare1);
					}
					return $shares;
				}
		
				//遍历匹配指定的关联人员
				foreach ($shares as $share1) {
					if (!empty($share1->share_uid) && $share1->share_uid==$shareUid)
						return $share1;
				}
			}
		}		
	}
	
	return null;
}

/**
 * 输出负责人、参与人、共享人、考勤审批人等关联人员的属性值
 * @param {string} $fieldName 字段名
 * @param {object|array} $entity 记录对象或关联数组
 * @param {string|number} $shareType 共享类型
 * @param {number} $count 最大个数，默认1000
 */
function echoShareFields($fieldName, $entity, $shareType, $count=1000) {
	if (is_object($entity)) {
		if(isset($entity->shares) && isset($entity->shares->{$shareType})) {
			$shares = $entity->shares->{$shareType};
			$str = '';
			for($i=0; ($i<count($shares) && $i<$count); $i++) {
				$str = $str.$shares[$i]->{$fieldName}.',';
			}
			$str = preg_replace('/,$/i', '', $str);
			echo $str;
		}
	} else if (is_array($entity)) {
		if(isset($entity['shares']) && isset($entity['shares'][$shareType])) {
			$shares = $entity['shares'][$shareType];
			$str = '';
			for($i=0; ($i<count($shares) && $i<$count); $i++) {
				$str = $str.$shares[$i]->{$fieldName}.',';
			}
			$str = preg_replace('/,$/i', '', $str);
			echo $str;
		}
	}
}
/**
 * 输出负责人、参与人、共享人、考勤审批人等关联人员的资料
 * @param {object|array} $entity 记录对象或关联数组
 * @param {string|number} $shareType 共享类型
 * @param {string} $shareUid (可选) 共享用户的编号，默认NULL
 */
function echoShares($entity, $shareType, $shareUid=NULL) {
	log_warn($entity);
	if (is_object($entity)) {
		if(isset($entity) && isset($entity->shares) && isset($entity->shares->{$shareType})) {
			$shares = $entity->shares->{$shareType};
	
			if (empty($shareUid)) {
				echo str_replace("\'", "\\\'", json_encode($shares));
			} else if (isset($shares)) {
				foreach ($shares as $share) {
					if ($share->share_uid===$shareUid) {
						echo str_replace("\'", "\\\'", json_encode($share));;
						break;
					}
				}
			}
		}
	} else if (is_array($entity)) {
		if(isset($entity['shares']) && isset($entity['shares'][$shareType])) {
			$shares = $entity['shares'][$shareType];
		
			if (empty($shareUid)) {
				echo str_replace("\'", "\\\'", json_encode($shares));
			} else if (isset($shares)) {
				foreach ($shares as $share) {
					if ($share->share_uid===$shareUid) {
						echo str_replace("\'", "\\\'", json_encode($share));;
						break;
					}
				}
			}
		}		
	}
}
// function echoShares($entity, $shareType, $onlyOne=false) {
// 	if(isset($entity) && isset($entity->shares) && isset($entity->shares->{$shareType})) {
// 		$shares = $entity->shares->{$shareType};
		
// 		if (!$onlyOne) {
// 			echo str_replace('\'', '\\\'', json_encode($shares));
// 		} else if (isset($shares) && count($shares)>0) {
// 			//定义函数：按(有效标记和创建时间)的倒序返回比较值
// 			function sortByFlagAndCrTime($a, $b) {
// 				if ($a->valid_flag==$b->valid_flag) {
// 					if ($a->create_time==$b->create_time) {
// 						return 0;
// 					} else {
// 						return ($a->create_time > $b->create_time)?-1:1;
// 					}
// 				} else {
// 					return ($a->valid_flag > $b->valid_flag)?-1:1;
// 				}
// 			}
// 			usort($shares, 'sortByFlagAndCrTime');
// 			echo str_replace('\'', '\\\'', json_encode($shares[0]));; 
// 		}
// 	}
// }

//创建关联用户(负责人、参与人、共享人、关注人)对象变量脚本
function createShareUserTypesScript() {
	$shareTypeOfPrincipal = 5;
	$shareTypeOfHelper = 2;
	$shareTypeOfSharer = 3;
	$shareTypeOfAttention = 4;
	echo '{';
				echo $shareTypeOfPrincipal, ':', '{share_type:', $shareTypeOfPrincipal,', shareTypeName: "principal_person", only_one:true},';
				echo $shareTypeOfHelper, ':', '{share_type:', $shareTypeOfHelper,', shareTypeName: "helper_person"},';
				echo $shareTypeOfSharer, ':', '{share_type:', $shareTypeOfSharer,', shareTypeName: "sharer_person"},';
				echo $shareTypeOfAttention, ':', '{share_type:', $shareTypeOfAttention,', shareTypeName: "attention_person"},';
	echo '}';
}

/**
 * 更新(计划、任务、报告、考勤申请审批人)关联人员
 * @param {string|int} $fromType 1=计划，2=任务，3=报告，11=考勤审批
 * @param {string} $fromId (计划、任务、报告、考勤审批申请)的编号
 * @param {string} $fromName (计划、任务、报告)的名称
 * @param {string|int} $shareType 共享类型
 * @param {string} $oldPerson 原关联人员，如：'70,91'
 * @param {string} $newPerson 新关联人员，如：'70,100,105'
 * @param {array} $operaterecordTypes 目标日志类型数组(最多两个元素)，分别在删除添加新关联人员和旧关联人员时写入日志，如：array(11, 12)、array(10)
 * @param {array} $usersMap 用户编号与用户名称对照表
 * @param {string} $userId 当前登录用户编号
 */
function updatePtrAssociatePerson($fromType, $fromId, $fromName, $shareType, $oldPerson, $newPerson, $operaterecordTypes, $usersMap, $userId) {
	$oldShareUids = array_values(array_unique(preg_split('/[\s,]+/', $oldPerson, -1, PREG_SPLIT_NO_EMPTY))); //旧关联人
	$shareUids = array_values(array_unique(preg_split('/[\s,]+/', $newPerson, -1, PREG_SPLIT_NO_EMPTY))); //新关联人
	//比较新旧相关人员差异
	// $toDel = distinguish(array('a', 'b'), array('a', 'c'));
	// $toAdd = distinguish(array('a', 'c'), array('a', 'b'));
	$toDel = custom_array_diff($oldShareUids, $shareUids);
	$toAdd = custom_array_diff($shareUids, $oldShareUids);
	
	//删除旧关联人
	if (!empty($toDel)) {
		log_info('want to remove associate persons:'.json_encode($toDel).' of fromType='.$fromType.', fromId='.$fromId.', $shareType='.$shareType);
		foreach ($toDel as $shareUid) {
			$json2 = delete_shareuser($fromType, $fromId, $shareType, $shareUid);
			$affected= get_field_value_from_json($json2, 'affected', $tmpObj2);
			if (!empty($affected)) {
				if (count($operaterecordTypes)>1)
					create_operaterecord($fromId, $fromName, $fromType, $operaterecordTypes[1], $shareUid, $usersMap[$shareUid]);
					log_info('delete old person for $PTRType='.$fromType.', id='.$fromId.', $shareType='.$shareType.', $shareUid='.$shareUid.', $affected='.$affected);
			}
		}
	}
	
	//添加新关联人
	if (!empty($toAdd)) {
		log_info('want to add associate persons:'.json_encode($toAdd).' of fromType='.$fromType.', fromId='.$fromId.', $shareType='.$shareType);
		foreach ($toAdd as $shareUid) {
			$readFlag = ($shareUid===$userId)?1:0;
			$json2 = create_shareuser($fromType, $fromId, $shareType, $shareUid, $usersMap[$shareUid], $readFlag);
			$shareId = get_field_value_from_json($json2, 'id', $tmpObj2);
			if (!empty($shareId)) {
				if (count($operaterecordTypes)>0)
					create_operaterecord($fromId, $fromName, $fromType, $operaterecordTypes[0], $shareUid, $usersMap[$shareUid]);
				log_info('add person for $PTRType='.$fromType.', id='.$fromId.', $shareType='.$shareType.', $shareUid='.$shareUid.', $readFlag='.$readFlag);
			}
		}
	}
}
