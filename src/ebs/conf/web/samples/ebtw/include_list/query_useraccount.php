<?php
$tmpObj = json_decode($json);
if ($tmpObj->code==0 && intval($tmpObj->total)>0) {
	if (isset($tmpObj->results)) {
		$tmpResults = $tmpObj->results;
	} else if (isset($tmpObj->pager)) {
		$tmpResults = $tmpObj->pager->exhibitDatas;
	}
	if (!empty($tmpResults)) {
		//过滤重复条件
		$emptyObject = new stdClass();
		foreach ($tmpResults as $value) {
			$emptyObject->{$value->{$fieldName}} = $value->{$fieldName};
		}
		$idAry = get_object_vars($emptyObject);
		
		//组合查询条件
		$inWhere = 'where user_id in (';
		foreach ($idAry as $value) {
			$inWhere .= $value.',';
		}
		$inWhere = preg_replace('/,$/',')',$inWhere);
		$sql = 'select user_id, account, username from user_account_t '.$inWhere;

		$uaInstance = UserAccountService::get_instance();
		$result1 = $uaInstance->simpleSearch($sql, null, NULL, NULL, NULL, count($tmpResults), 0);
		$json1 = ResultHandle::listedResultToJsonAndOutput($result1, false);
		$tmpObj1 = json_decode($json1);
		if ($tmpObj1->code==0 && !empty($tmpObj1->results)) {
			foreach($tmpObj1->results as $user) {
				foreach($tmpResults as &$ptr) {
					if ($user->user_id==$ptr->{$fieldName})
						$ptr->{$targetFieldName} = $user->username;
				}
			}
			
			$json = json_encode($tmpObj);
		}
	}
}