<?php
$Arr					= LoadData('app_id,app_key,app_password,pro_no,show_type,date_start,date_end');

$ExistsApp				= ExistsApp($Arr['app_id'],$Arr['app_key'],$Arr['app_password']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];
unset($Arr['app_id'],$Arr['app_key'],$Arr['app_password']);

if( !preg_match('/20[0-9]{,2}-[0-9]{1,2}-[0-9]{1,2}/i', $Arr['date_start']) ){
	$Json['status']		= false;
	$Json['error']		= '查询时间格式错误';
	output($Json);
}
$TimeStart				= strtotime($Arr['date_start']." 00:00:00");

if( !empty($Arr['date_end']) ){
	if(!preg_match('/20[0-9]{,2}-[0-9]{1,2}-[0-9]{1,2}/i', $Arr['date_end']) ){
		$Json['status']		= false;
		$Json['error']		= '查询截至时间格式错误';
		output($Json);
	}else{
		$TimeEnd		= strtotime($Arr['date_end']." 00:00:00");
	}
}else{
	$TimeEnd			= time();
}
if( $TimeStart<time()-86400*365 ){
	$Json['status']		= false;
	$Json['error']		= '最远历史记录为1年';
	output($Json);
}
if( $TimeStart>$TimeEnd||$TimeEnd-$TimeStart>86400*60 ){
	$Json['status']		= false;
	$Json['error']		= '查询开始时间必须先于且仅能先于结束前60天.';
	output($Json);
}
$ProIds					= array();
if( !empty($Arr['pro_no']) ){
	$ProArr				= explode(",", $Arr['pro_no']);
	$ProArray			= ParseArray($ProArr);
	$ProIds				= $ProArray['value'];
}

$Where					= ( empty($ProIds) )? "1=1" : "pro_no IN (".implode(",", $ProIds).")";

switch( strtolower($Arr['show_type']) ){
	case "info":
		$Pro			= array();
		$Pro['total']	= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_pro_event WHERE event_type='info' AND event_time>={$TimeStart} AND event_time<={$TimeEnd} AND {$Where}");
		
		
		$Json['info']	= $Pro;
	break;
}
output($Json);