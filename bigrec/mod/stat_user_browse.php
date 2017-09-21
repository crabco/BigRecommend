<?php
$Arr					= LoadData('app_id,app_key,app_password,p,s,date_start,date_end');

$ExistsApp				= ExistsApp($Arr['app_id'],$Arr['app_password'], $Arr['app_key']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];

$TimeStart				= $Arr['date_start'];
$TimeEnd				= $Arr['date_end'];

if( !preg_match('/20[0-9]{,2}-[0-9]{1,2}-[0-9]{1,2}/i', $TimeStart) ){
	$TimeStart			= null;
	$TimeEnd			= null;
	$BaseName			= "big{$AppID}_browse";
	$Where				= "1=1";
}else{
	$TimeStart		   .= " 00:00:00";
	$TimeEnd			= ( preg_match("/20[0-9]{,2}-[0-9]{1,2}-[0-9]{1,2}/i", $TimeEnd) )? $TimeEnd." 23:59:59" : date("Y-m-d H:i:s");
	$BaseName			= "big{$AppID}_browse_history";
	$Where				= "browse_update>='{$TimeStart}' AND browse_update<='{$TimeEnd}'";
}

$Page					= ( ceil($Arr['p'])>1 )? ceil($Arr['p']) : 1;
$Size					= ( ceil($Arr['s'])>1 )? ceil($Arr['s']) : 100;
$Lim					= ( $Page-1 ) * $Size;
$Json['totalCount']		= $Ado->GetOne("SELECT COUNT(*) FROM {$BaseName} WHERE {$Where}");
$Json['browse']			= $Ado->SelectLimit("SELECT * FROM {$BaseName} WHERE {$Where} ORDER BY browse_update DESC", $Size,$Lim);

$Json['status']			= true;
$Json['error']			= '';
output($Json);