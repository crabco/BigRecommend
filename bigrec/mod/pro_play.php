<?php
$Arr					= LoadData('app_id,app_key,pro_no,pro_play,pro_seq,user_no,pro_play_status');

$ExistsApp				= ExistsApp($Arr['app_id'],null, $Arr['app_key']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];

$Row					= array();
$Row['pro_no']			= $Arr['pro_no'];
$Row['event_type']		= ($Arr['pro_play']=='info')? "info":"play";
$Row['event_time']		= time();
$Row['pro_seq']			= $Arr['pro_seq'];
$Row['user_no']			= $Arr['user_no'];
$Row['event_time_sum']	= 0;
$Ado->AutoExecute("big{$AppID}_pro_event", $Row, "INSERT");
$Ado->Execute("UPDATE big{$AppID}_pro SET pro_info=pro_info+1 WHERE pro_no='{$Arr['pro_no']}'");

//如果用户不为空则更新用户最后活动时间
if( !empty($Arr['user_no'])){
	$Ado->AutoExecute("big{$AppID}_user", array('user_time_update'=>time()), "UPDATE", "user_no='{$Arr['user_no']}'");
}

$Json['status']			= true;
$Json['error']			= '';
output($Json);