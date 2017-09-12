<?php
$Arr					= LoadData('app_id,app_password,user_no');

$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
if( $ExistsApp!==true ){
	$Json				= $ExistsApp;
	output($Json);
}
$AppID					= $Arr['app_id'];

$Ado->Execute("DELETE FROM big{$AppID}_user WHERE user_no='{$Arr['user_no']}'");
$Ado->Execute("DELETE FROM big{$AppID}_user_event WHERE user_no='{$Arr['user_no']}'");

$Json['status']			= true;
$Json['error']			= '';
output($Json);