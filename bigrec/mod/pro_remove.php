<?php
$Arr					= LoadData('app_id,app_password,pro_no');

$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
if( $ExistsApp!==true ){
	$Json				= $ExistsApp;
	output($Json);
}
$AppID					= $Arr['app_id'];
unset($Arr['app_password']);

$Ado->Execute("DELETE FROM big{$AppID}_pro WHERE pro_no='{$Arr['pro_no']}'");
$Ado->Execute("DELETE FROM big{$AppID}_pro_event WHERE pro_no='{$Arr['pro_no']}'");

$Json['status']			= true;
$Json['error']			= '';
output($Json);