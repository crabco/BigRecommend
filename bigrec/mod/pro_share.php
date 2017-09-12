<?php
$Arr					= LoadData('app_id,app_key,pro_no,user_no');

$ExistsApp				= ExistsApp($Arr['app_id'],null, $Arr['app_key']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];

$Row					= array();
$Row['pro_no']			= $Arr['pro_no'];
$Row['event_type']		= 'share';
$Row['event_time']		= time();
$Row['user_no']			= $Arr['user_no'];
$Ado->AutoExecute("big{$AppID}_pro_event", $Row, "INSERT");

//更新商品分享次数
$Ado->Execute("UPDATE big{$AppID}_pro SET pro_share=pro_share+1 WHERE pro_no='{$Arr['pro_no']}'");

//如果用户不为空则更新用户最后活动时间
if( !empty($Arr['user_no'])){
	$Ado->AutoExecute("big{$AppID}_user", array('user_time_update'=>time()), "UPDATE", "user_no='{$Arr['user_no']}'");
}

$Json['status']			= true;
$Json['error']			= '';
output($Json);