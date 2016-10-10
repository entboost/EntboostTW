<?php
require_once dirname(__FILE__).'/authority_function.php';

	if (isset($PTRType))
		$subId = $SUB_IDS[$PTRType];
// 	else 
// 		log_err('$PTRType is not setted');
	//检测会话
	validateSession(false, null, 'invalidSessionCallback');
