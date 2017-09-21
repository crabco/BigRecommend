<?php
$Arr					= LoadData('app_id,app_key,pro_no,pro_play,pro_seq,user_no,pro_play_status');

$ExistsApp				= ExistsApp($Arr['app_id'],null, $Arr['app_key']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];

$Row						= array();
$Row['pro_no']				= $Arr['pro_no'];
$Row['event_type']			= ($Arr['pro_play']=='info')? "info":"play";
$Row['event_time']			= date("Y-m-d H:i:s");
$Row['event_time_date']		= date("Y-m-d");
$Row['event_time_hour'] 	= date("H:i");
$Row['event_time_update']	= date("Y-m-d H:i:s");
$Row['pro_seq']				= $Arr['pro_seq'];
$Row['user_no']				= $Arr['user_no'];
// $Row['event_time_sum']	= 0;
$Row['user_ip']				= FunGetTrueIP();


$TimeOut					= date("Y-m-d H:i:s",time()-3600); //两小时算作新动作
$Where						= "event_type='{$Row['event_type']}' AND pro_no='{$Row['pro_no']}' AND user_no='{$Row['user_no']}' AND user_ip='{$Row['user_ip']}' AND event_time_update>'{$TimeOut}'";

if( $Arr['pro_play_status']=="sleep" ){
	$Event				= $Ado->GetRow("SELECT * FROM big{$AppID}_pro_event WHERE {$Where}");
}else{
	$Event				= array();
}

if( empty($Event) ){
	$Ado->AutoExecute("big{$AppID}_pro_event", $Row, "INSERT");
	$Ado->Execute("UPDATE big{$AppID}_pro SET pro_info=pro_info+1 WHERE pro_no='{$Arr['pro_no']}'");
}else{
	$Arr						= array();
	$NewTime					= time() - strtotime($Event['event_time_update']);
	
	$Arr['event_time_sum']		= $NewTime + $Event['event_time_sum'];
	$Arr['event_time_update']	= date("Y-m-d H:i:s");
	
	$Ado->AutoExecute("big{$AppID}_pro_event", $Arr, "UPDATE", $Where);
	$Ado->Execute("UPDATE big{$AppID}_pro SET pro_playtime=pro_playtime+{$NewTime} WHERE pro_no='{$Row['pro_no']}'");
}


//如果用户不为空则更新用户最后活动时间
if( !empty($Arr['user_no'])){
	$Ado->AutoExecute("big{$AppID}_user", array('user_time_update'=>time()), "UPDATE", "user_no='{$Arr['user_no']}'");
}

$Json['status']			= true;
$Json['error']			= '';
output($Json);