<?php
$Arr					= LoadData('app_id,app_password,pro_no,pro_name,pro_cover,pro_tags,pro_show,pro_grade');
$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
if( $ExistsApp!==true ){
	$Json				= $ExistsApp;
	output($Json);
}
$AppID					= $Arr['app_id'];
unset($Arr['app_password'],$Arr['app_id']);

if( isset($Arr['pro_grade'])&&!empty($Arr['pro_grade'])&&(ceil($Arr['pro_grade'])<0||ceil($Arr['pro_grade'])>9 ) ){
	$Json['status']		= false;
	$Json['error']		= '显示权重数据非法';
	output($Json);
}

//添加标签资料
$TagArr					= explode(",", $Arr['pro_tags']);
if( empty($TagArr) ){
	$Json['status']		= false;
	$Json['error']		= '资料必须要有标签';
	output($Json);
}else{
	$New				= array();
	foreach($TagArr as $Rs){
		$New			= array('app_tags'=>$Rs);
		$Ado->AutoExecute("big{$AppID}_app_tags", $New, 'INSERT');
	}
}

$Arr['pro_time_update']	= date("Y-m-d H:i:s");

$Row					= ParseArray($Arr);
$RowName				= implode(",", $Row['name']);
$RowValue				= implode(",", $Row['value']);
$RowSet					= implode(",", $Row['set']);
$Ex						= $Ado->Execute("INSERT INTO big{$AppID}_pro ({$RowName}) VALUES({$RowValue}) ON DUPLICATE KEY UPDATE {$RowSet}");

if( $Ex ){
	$Json['status']		= true;
	$Json['error']		= '';
	output($Json);
}else{
	$Json['status']		= false;
	$Json['error']		= '写入失败';
	output($Json);
}