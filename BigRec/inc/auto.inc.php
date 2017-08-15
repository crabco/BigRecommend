<?php
require_once dirname(__FILE__)."/config.inc.php";
require_once dirname(__FILE__)."/fun.inc.php";



/**
 * 模块不存在的自动处理机制 
 */
function __autoload( $ClassName ){

	$Fs		= SYSTEM_PATH ."/inc/" . $ClassName . ".class.php";
	if( !is_file($Fs) ){
		exit(json_encode( array('status'=>false,'error'=>'模块加载失败') ));
	} 
	require_once("$Fs");
}



/**
 * 初始化数据库连接
 */
if( ADOPdo::Start(DATABASE_HOST,DATABASE_USER,DATABASE_PASS,DATABASE_NAME)===false ){
	header("Location: error.php?code=mysql_connect_error&err=". urlencode("连接数据库出错,请联系管理员") );exit;
}



//如果打开了调试开关
if( SYSTEM_DEBUG==true ){
	ini_set("display_errors", "on");
	error_reporting(E_WARNING|E_ERROR);
	if( $_GET['error']=='all'||$_COOKIE['error']=='all' ){
		error_reporting(E_ALL);
	}
}else{
	ini_set("display_errors", "off");
	error_reporting(0);
}

//SESSION开关
if( !defined('SYSTEM_SESSION_OFF')||SYSTEM_SESSION_OFF!=true ){
	session_start();										//打开SESSION
}
date_default_timezone_set('PRC');