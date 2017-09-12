<?php
$Arr					= LoadData('app_id,app_password,user_no,user_sex,user_age,user_phone,user_reco');

$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
if( $ExistsApp!==true ){
	$Json				= $ExistsApp;
	output($Json);
}
$AppID					= $Arr['app_id'];

unset($Arr['app_password'],$Arr['app_id']);
$Arr['user_time_create']	= date("Y-m-d H:i:s");

if( !preg_match($NoPreg, $Arr['user_no']) ){
	$Json['status']		= false;
	$Json['error']		= '用户序号非法,仅支持字母、数字、下横线、点（英文）等符号，最长32位';
	output($Json);
}

if( isset($Arr['user_sex'])&&$Arr['user_sex']!='男'&&$Arr['user_sex']!='女'&&$Arr['user_sex']!='保密' ){
	$Json['status']		= false;
	$Json['error']		= '用户性别错误';
	output($Json);
}

if( isset($Arr['user_age'])&&ceil($Arr['user_age'])>200&&ceil($Arr['user_age'])<1 ){
	$Json['status']		= false;
	$Json['error']		= '用户年龄错误';
	output($Json);
}

if( isset($Arr['user_phone'])&&!preg_match('/^[0-9\+]{11,15}$/i', $Arr['user_phone']) ){
	$Json['status']		= false;
	$Json['error']		= '用户手机号码错误';
	output($Json);
}

if( isset($Arr['user_reco']) && !empty($Arr['user_reco']) ){
	$UserRow			= $Ado->GetRow("SELECT user_reco FROM big{$AppID}_user WHERE user_no='{$Arr['user_no']}'");
	if( $UserRow['user_reco']!=$Arr['user_reco'] ){
		$Ado->Execute("INSERT INTO big{$AppID}_user_reco (reco_name,reco_sum) VALUES('{$Arr['user_reco']}',1) ON DUPLICATE KEY UPDATE reco_sum=reco_sum+1");
	}
}else{
	unset($Arr['user_reco']);
}

$Row					= ParseArray($Arr);
$RowName				= implode(",", $Row['name']);
$RowValue				= implode(",", $Row['value']);
$RowSet					= implode(",", $Row['set']);
$Ex						= $Ado->Execute("INSERT INTO big{$AppID}_user ({$RowName}) VALUES({$RowValue}) ON DUPLICATE KEY UPDATE {$RowSet}");

if( $Ex ){
	$Json['status']		= true;
	$Json['error']		= '';
	output($Json);
}else{
	$Json['status']		= false;
	$Json['error']		= '写入失败';
	output($Json);
}