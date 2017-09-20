<?php
$PHP_SELF=$_SERVER['PHP_SELF'];
// echo 'xxxxx'.$PHP_SELF;
// $DOC = $_SERVER['SCRIPT_NAME'];
// echo 'bbb'.$DOC;
//$SERVER_PORT = $_SERVER['SERVER_PORT'];
$SERVER_PORT = '';
if (preg_match('/^HTTP\//i', $_SERVER['SERVER_PROTOCOL'])) { //http协议
	$HTTP_PREFIX = 'http';
// 	if ($SERVER_PORT==80)
// 		$SERVER_PORT = '';
// 	else
// 		$SERVER_PORT = ':'.$SERVER_PORT;
} else { //https协议
	$HTTP_PREFIX = 'https';
// 	if ($SERVER_PORT==443)
// 		$SERVER_PORT = '';
// 	else
// 		$SERVER_PORT = ':'.$SERVER_PORT;
}
//$PHP_SELF

//$ROOT_URL = $HTTP_PREFIX.'://'.$_SERVER['HTTP_HOST'].$SERVER_PORT.substr($PHP_SELF,0,strrpos($PHP_SELF,'/')+1);
// echo $_SERVER['DOCUMENT_ROOT'].'<br>';
// echo __FILE__.'<br>';
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$documentRoot = str_replace("\\", "/", $documentRoot);
if($documentRoot[strlen($documentRoot)-1] == '/')
	$documentRoot = substr($documentRoot,0,-1);
//var_dump($documentRoot);echo '<br>';
	
$fileFolder = dirname(__FILE__);
//var_dump($fileFolder);echo '<br>';
$fileFolder = str_replace("\\", "/", $fileFolder);
//var_dump($fileFolder);
$relativeFolder = str_replace($documentRoot, '', $fileFolder);
$relativeFolder = substr($relativeFolder,1);

//echo $relativeFolder.'<br>';
//echo basename(__FILE__).'<br>';
$ROOT_URL = $HTTP_PREFIX.'://'.$_SERVER['HTTP_HOST'].$SERVER_PORT.'/'.$relativeFolder;