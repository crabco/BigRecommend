<?php
$Arr					= LoadData('app_name,app_exp,app_password,app_key,app_stat_type');

if( empty($Arr['app_name'])||empty($Arr['app_exp'])||empty($Arr['app_password'])||empty($Arr['app_key']) ){
	$Json['status']		= false;
	$Json['error']		= '关键数据缺失';
	output($Json);
}

$AppOnly				= $Ado->GetOne("SELECT count(app_id) FROM big_app WHERE app_name='{$Arr['app_name']}'");
if( ceil($AppOnly)>0 ){
	$Json['status']		= false;
	$Json['error']		= '重复的应用名称';
	output($Json);
}

$Arr['app_reco_data']	= 7;
$Arr['app_time_create']	= date("Y-m-d H:i:s");

if( isset($Arr['app_password'])&& ( empty($Arr['app_password'])||strlen($Arr['app_password'])<5 ) ){
	$Json['status']			= false;
	$Json['error']			= '应用管理密码不能为空且不能少于5位';
	output($Json);
}

if( isset($Arr['app_key'])&& ( empty($Arr['app_key'])||strlen($Arr['app_key'])<5 ) ){
	$Json['status']			= false;
	$Json['error']			= '应用提交密钥不能为空且不能少于5位';
	output($Json);
}

if( $Arr['app_stat_type']!='public' ){
	$Arr['app_stat_type']	= 'private';
}

$Ex							= $Ado->AutoExecute('big_app', $Arr, 'INSERT');
$AppID						= $Ado->InsertID();

if( !$Ex || empty($AppID) ){
	$Json['status']			= false;
	$Json['error']			= '创建失败';
	output($Json);
}else{
	$Json['status']			= true;
	$Json['error']			= '';
	$Json['app_id']			= $AppID;
	AppSync($AppID);
}

/**
 * 开始创建数据库
 */
$NewBase					= file_get_contents("inc/gli_bigrecommend.sql");
$NewBase					= str_replace("`big_", "`big{$AppID}_", $NewBase);
$Ado->Execute($NewBase);
$Ado->Execute("DROP TABLES IF EXISTS big{$AppID}_app,big{$AppID}_app_cache;");

output($Json);
