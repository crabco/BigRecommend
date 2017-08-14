<?php

/**
 * 数据获取方法
 * @param unknown $Tags
 * @param string $Type
 */
function LoadData( $Tags,$Type='post' ){
	
	$Name		= explode(",", $Tags);
	
	if( !preg_match('/^[a-z0-9,]$/i', $Tags)||empty($Name) ){
		return array();
	}
	
	$Arr		= array();
	
	foreach($Name as $Rs){
		if( $Type=='post' && isset($_POST[$Rs]) ) 		$Arr[$Rs]	= $_POST[$Rs];
		if( $Type=='get' && isset($_GET[$Rs]) )  		$Arr[$Rs]	= $_GET[$Rs];
		if( $Type=='cookie' && isset($_COOKIE[$Rs]) )  	$Arr[$Rs]	= $_COOKIE[$Rs];
	}
	
	return $Arr;
}



function SyncApp( $AppID=null ){
	global $Ado;
	
	if( $AppID==null ){
		$Total	= $Ado->GetOne("SELECT COUNT(app_id) FROM big_app_cache");
		if( ceil($Total)<=0 ){
			$Ado->Execute("INSERT INTO big_app_cache SELECT * FROM big_app");
		}
	}
	
	if( ceil($AppID)>0 ){
		$AppRow			= $Ado->GetRow("SELECT * FROM big_app WHERE app_id='{$AppID}'");
		$AppName		= array();
		$AppVal			= array();
		if( !empty($AppRow) ){
			foreach($AppRow as $Vs=>$Rs){
				$AppName[]		= "`{$Vs}`";
				$AppVal[]		= "'{$Rs}'";
			}
			
			$Ado->Execute("INSERT big_app_cache (".implode(",", $AppName).") VALUES(".implode(",", $AppName).") ON DUPLICATE KEY UPDATE auto_name='app_id'");
		}
	}
}