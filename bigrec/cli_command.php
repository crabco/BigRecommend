<?php
/**
 * 本文件是CLI模式下的定时统计程序入口文件
 * CLI模式下不支持$_SESSION以及COOKIE
 * 本文件需要定时执行
 */
//关闭SESSION
define('SYSTEM_SESSION_OFF', TRUE);
require_once 'inc/auto.inc.php';

AppLog("启动中......\r\n");


$Ado	= ADOPdo::Start();
AppLog("连接数据库成功.\r\n");


//主统计进程启动时间
$SumAppTime		= "H:00:00";		//用户数据统计
$SumRecoTime	= "H:01:00";		//用户推荐统计
$SumBrowse		= "H:i:00";			//用户浏览数据迁移


//开始启动监听程序的同步数据
AppCommand("执行第一次缓存同步.");
AppSync();

//开始执行第一次统计
AppCommand("执行第一次数据统计.");
AppSum();

AppCommand("注入监听器时间.");

//开始进入主循环
while(1==1){
	$Log		= "等待...";
	
	if( $Log!=$Logs )	AppCommand("{$Log}");
	$Logs		= $Log;
	
	//统计所有APP的数据
	if( date("H:i:s")==date($SumAppTime) ){
		AppCommand("开始进入统计...");
		AppSum();
		AppCommand("统计结束.");
	}
	
	//核算经销商的推荐个数
	if( date("H:i:s")==date($SumRecoTime) ){
		AppCommand("开始统计经销商数据...");
		AppSumReco();
		AppCommand("统计经销商数据结束.");
	}
	
	
	//用户浏览数据迁移
	if( date("H:i:s")==date($SumRecoTime) ){
		AppCommand("开始迁移用户浏览数据...");
		AppSumBrowse();
		AppCommand("迁移结束.");
	}
	
	usleep(500000);
}
