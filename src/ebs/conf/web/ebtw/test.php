<?php 
require_once dirname(__FILE__).'/usual_function.php';

	$oldPerson = 'a';
	$newPerson = 'a';
	
	$old = preg_split('/[\s,]+/', $oldPerson, -1, PREG_SPLIT_NO_EMPTY);
	$new = preg_split('/[\s,]+/', $newPerson, -1, PREG_SPLIT_NO_EMPTY);
	var_dump($old);
	var_dump($new);
	echo '<br>';
	
	$oldShareUids = array_values(array_unique($old)); //旧关联人
	$shareUids = array_values(array_unique($new)); //新关联人
	var_dump($oldShareUids);
	var_dump($shareUids);
	echo '<br>';
	
	//比较新旧相关人员差异
	// $toDel = distinguish(array('a', 'b'), array('a', 'c'));
	// $toAdd = distinguish(array('a', 'c'), array('a', 'b'));
	$toDel = custom_array_diff($oldShareUids, $shareUids);
	$toAdd = custom_array_diff($shareUids, $oldShareUids);
	var_dump($toDel);
	var_dump($toAdd);
?>