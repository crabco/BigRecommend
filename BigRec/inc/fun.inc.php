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


function ParseArray($Arr){
	$AppName		= array();
	$AppVal			= array();
	$AppSet			= array();
	if( !empty($Arr) ){
		foreach($AppRow as $Vs=>$Rs){
			$AppName[]		= "`{$Vs}`";
			$AppVal[]		= "'{$Rs}'";
			$AppSet[]		= "`{$Vs}`='{$Rs}'";
		}
	}
	
	return array('name'=>$AppName,'value'=>$AppVal);
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
		$AppSet			= array();
		if( !empty($AppRow) ){
			foreach($AppRow as $Vs=>$Rs){
				$AppName[]		= "`{$Vs}`";
				$AppVal[]		= "'{$Rs}'";
				$AppSet[]		= "`{$Vs}`='{$Rs}'";
			}
			
			$Ado->Execute("INSERT big_app_cache (".implode(",", $AppName).") VALUES(".implode(",", $AppName).") ON DUPLICATE KEY UPDATE ".implode(",", $AppSet));
		}
	}
}




function ExistsApp($AppID,$AppPass=null,$AppKey=null){
	global $Ado;
	
	if( !preg_match('/^[0-9]+$/i', $AppID) ){
		$Json['status']		= false;
		$Json['error']		= '不存在的应用';
		return $Json;
	}
	$AppRow					= $Ado->GetRow("SELECT * FROM big_app_cache WHERE app_id='{$AppID}'");
	if( empty($AppRow) ){
		$Json['status']		= false;
		$Json['error']		= '不存在的应用';
		return $Json;
	}
	
	if( !empty($AppPass) && md5($AppRow['app_pass'])!=md5($AppPass) ){
		$Json['status']		= false;
		$Json['error']		= '管理密码错误';
		return $Json;
	}
	
	if( !empty($AppKey) && md5($AppRow['app_key'])!=md5($AppKey) ){
		$Json['status']		= false;
		$Json['error']		= '提交密钥错误错误';
		return $Json;
	}
	
	if( empty($AppPass)&&empty($AppKey) ){
		$Json['status']		= false;
		$Json['error']		= '访问密钥错误';
		return $Json;
	}
	
	return true;
}