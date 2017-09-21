<?php
$Arr					= LoadData('app_id,app_key,app_password,pro_no,show_type,date_start,date_end,p,s');

$ExistsApp				= ExistsApp($Arr['app_id'],$Arr['app_password'],$Arr['app_key']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];
unset($Arr['app_id'],$Arr['app_key'],$Arr['app_password']);

if( !preg_match('/20[0-9]{2,2}-[0-9]{1,2}-[0-9]{1,2}/i', $Arr['date_start']) ){
	$Json['status']		= false;
	$Json['error']		= '查询时间格式错误.';
	output($Json);
}
$TimeStart				= $Arr['date_start']." 00:00:00";

if( !empty($Arr['date_end']) ){
	if(!preg_match('/20[0-9]{2,2}-[0-9]{1,2}-[0-9]{1,2}/i', $Arr['date_end']) ){
		$Json['status']		= false;
		$Json['error']		= '查询截至时间格式错误';
		output($Json);
	}else{
		$TimeEnd		= $Arr['date_end']." 23:59:59";
	}
}else{
	$TimeEnd			= date("Y-m-d H:i:s");
}

if( strtotime($TimeStart)<time()-86400*365 ){
	$Json['status']		= false;
	$Json['error']		= '最远历史记录为1年';
	output($Json);
}
if( $TimeStart>$TimeEnd|| strtotime($TimeEnd)-strtotime($TimeStart)>86400*60 ){
	$Json['status']		= false;
	$Json['error']		= '查询开始时间必须先于且仅能先于结束前60天.';
	output($Json);
}


$ProIds					= array();
if( !empty($Arr['pro_no']) ){
	$ProArr				= explode(",", urldecode($Arr['pro_no']) );
	$ProArray			= ParseArray($ProArr);
	$ProIds				= $ProArray['value'];
	$ProNos				= $ProArray['no'];
}
if( empty($ProIds)||empty($ProNos) ){
	$Json['status']		= false;
	$Json['error']		= '请输入要查询的商品序号.';
	output($Json);
}

$Where					= ( empty($ProIds) )? "1=1" : "pro_no IN (".implode(",", $ProIds).")";

switch( strtolower($Arr['show_type']) ){
	case "info":
		$Pro			= array();
		$Pro['total']	= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_pro_event WHERE event_type='info' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND {$Where}");
		
		//开始计算每个产品的
		foreach($ProNos as $ProNo){
			$Pro[$ProNo]['total']	= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_pro_event WHERE event_type='info' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND pro_no='{$ProNo}'");
			$SQL					= "SELECT pro_no,event_time,COUNT(pro_no) AS event_sum FROM big{$AppID}_pro_event WHERE event_type='info' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND pro_no='{$ProNo}'  GROUP BY substr(event_time_hour,0,2)";
			$ProHour				= $Ado->GetAll($SQL);
			if(!empty($ProHour)){
			foreach($ProHour as $Rs){
				$TimeNo							= substr($Rs['event_time'],0,13);
				$Pro[$ProNo]['hour'][$TimeNo]	= $Rs['event_sum'];
			}
			}
		}
		$Json['info']	= $Pro;
	break;
	
	
	case "play":
		$Pro			= array();
		$Pro['total']	= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_pro_event WHERE event_type='play' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND {$Where}");
		
		//开始计算每个产品的
		foreach($ProNos as $ProNo){
			$Pro[$ProNo]['total']	= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_pro_event WHERE event_type='play' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND pro_no='{$ProNo}'");
			$SQL					= "SELECT pro_no,event_time,COUNT(pro_no) AS event_sum FROM big{$AppID}_pro_event WHERE event_type='play' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND pro_no='{$ProNo}'  GROUP BY substr(event_time_hour,0,2)";
			$ProHour				= $Ado->GetAll($SQL);
			if(!empty($ProHour)){
				foreach($ProHour as $Rs){
					$TimeNo							= substr($Rs['event_time'],0,13);
					$Pro[$ProNo]['hour'][$TimeNo]	= $Rs['event_sum'];
				}
			}
		}
		$Json['play']	= $Pro;
	break;
	
	case "playtime":
		$Pro			= array();
		$Pro['total']	= $Ado->GetOne("SELECT SUM(event_time_sum) FROM big{$AppID}_pro_event WHERE event_type='play' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND {$Where}");
		
		//开始计算每个产品的
		foreach($ProNos as $ProNo){
			$Pro[$ProNo]['total']	= $Ado->GetOne("SELECT SUM(event_time_sum) FROM big{$AppID}_pro_event WHERE event_type='play' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND pro_no='{$ProNo}'");
			$SQL					= "SELECT pro_no,event_time,SUM(event_time_sum) AS event_sum FROM big{$AppID}_pro_event WHERE event_type='play' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND pro_no='{$ProNo}'  GROUP BY substr(event_time_hour,0,2)";
			$ProHour				= $Ado->GetAll($SQL);
			if(!empty($ProHour)){
				foreach($ProHour as $Rs){
					$TimeNo							= substr($Rs['event_time'],0,13);
					$Pro[$ProNo]['hour'][$TimeNo]	= $Rs['event_sum'];
				}
			}
		}
		$Json['playtime']	= $Pro;
	break;
	
	
	case "share":
		$Pro			= array();
		$Pro['total']	= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_pro_event WHERE event_type='share' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND {$Where}");
		
		//开始计算每个产品的
		foreach($ProNos as $ProNo){
			$Pro[$ProNo]['total']	= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_pro_event WHERE event_type='share' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND pro_no='{$ProNo}'");
			$SQL					= "SELECT pro_no,event_time,COUNT(pro_no) AS event_sum FROM big{$AppID}_pro_event WHERE event_type='share' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND pro_no='{$ProNo}'  GROUP BY substr(event_time_hour,0,2)";
			$ProHour				= $Ado->GetAll($SQL);
			if(!empty($ProHour)){
				foreach($ProHour as $Rs){
					$TimeNo							= substr($Rs['event_time'],0,13);
					$Pro[$ProNo]['hour'][$TimeNo]	= $Rs['event_sum'];
				}
			}
		}
		$Json['share']	= $Pro;
	break;
	

	case "sale":
		$Pro			= array();
		$Pro['total']	= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_pro_event WHERE event_type='sale' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND {$Where}");
	
		//开始计算每个产品的
		foreach($ProNos as $ProNo){
			$Pro[$ProNo]['total']	= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_pro_event WHERE event_type='sale' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND pro_no='{$ProNo}'");
			$SQL					= "SELECT pro_no,event_time,COUNT(pro_no) AS event_sum FROM big{$AppID}_pro_event WHERE event_type='sale' AND event_time>='{$TimeStart}' AND event_time<='{$TimeEnd}' AND pro_no='{$ProNo}'  GROUP BY substr(event_time_hour,0,2)";
			$ProHour				= $Ado->GetAll($SQL);
			if(!empty($ProHour)){
				foreach($ProHour as $Rs){
					$TimeNo							= substr($Rs['event_time'],0,13);
					$Pro[$ProNo]['hour'][$TimeNo]	= $Rs['event_sum'];
				}
			}
		}
		$Json['sale']	= $Pro;
	break;
}
output($Json);






