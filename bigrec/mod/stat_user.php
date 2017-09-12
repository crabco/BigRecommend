<?php
$Arr					= LoadData('app_id,app_key,app_password,date_start,date_end');

$ExistsApp				= ExistsApp($Arr['app_id'],$Arr['app_key'],$Arr['app_password']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];
unset($Arr['app_id'],$Arr['app_key'],$Arr['app_password']);

//======================================================================================
$AppRow						= $Ado->GetRow("SELECT * FROM big_app_cache WHERE app_id='{$AppID}'");
$Json['status']				= true;
$Json['error']				= '';

$User						= array();
$User['total']				= ceil($AppRow['app_total_user']);
$User['total_sale']			= ceil($AppRow['app_total_sale']);
$User['total_man']			= ceil($AppRow['app_total_man']);
$User['total_woman']		= ceil($AppRow['app_total_woman']);
$User['total_reco']			= ceil($AppRow['app_total_reco']);
$User['total_active']		= ceil($AppRow['app_total_active']);
$User['total_active_man']	= ceil($AppRow['app_total_active_man']);
$User['total_active_woman']	= ceil($AppRow['app_total_active_woman']);

$EndTime					= time() - 86400*30;
$User['total_active_view']	= $Ado->SelectLimit("SELECT user_no,user_time_login as login_time FROM big{$AppID}_user WHERE user_time_update>{$EndTime} ORDER BY user_time_update DESC",100);
$User['total_reco_view']	= $Ado->SelectLimit("SELECT * FROM big{$AppID}_user_reco ORDER BY reco_sum DESC",100);

$Json['user']				= $User;
output($Json);
