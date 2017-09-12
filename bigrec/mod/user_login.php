<?php
$Arr					= LoadData('app_id,app_password,user_no');

$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];

$Row					= array();
$Row['user_no']			= $Arr['user_no'];
$Row['event_type']		= 'login';
$Row['event_time']		= time();
$Ado->AutoExecute("big{$AppID}_user_event", $Row, "INSERT");
$Ado->AutoExecute("big{$AppID}_user", array('user_time_update'=>time(),'user_time_login'=>time()), "UPDATE", "user_no='{$Arr['user_no']}'");

$Json['status']			= true;
$Json['error']			= '';
output($Json);