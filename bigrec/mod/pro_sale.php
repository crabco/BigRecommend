<?php
$Arr					= LoadData('app_id,app_key,pro_no,pro_play,pro_seq,user_no,pro_sale_no,pro_pay_name,pro_money,pro_sale_referer');

$ExistsApp				= ExistsApp($Arr['app_id'],null, $Arr['app_key']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];

if( !preg_match('/^[0-9]+(\.[0-9]+)?$/i',$Arr['pro_money']) ){
	output(array('status'=>false,'error'=>'金额格式错误'));
}
if( floatval($Arr['pro_money'])>9999.99 ){
	output(array('status'=>false,'error'=>'金额超过系统限制'));
}
if( empty($Arr['user_no'])){
	output(array('status'=>false,'error'=>'请输入购买用户'));
}

$Row					= array();
$Row['pro_no']			= $Arr['pro_no'];
$Row['event_type']		= 'sale';
$Row['event_time']		= time();
$Row['pro_seq']			= $Arr['pro_seq'];
$Row['user_no']			= $Arr['user_no'];
$Row['pro_sale_no']		= $Arr['pro_sale_no'];
$Row['pro_pay_name']	= $Arr['pro_pay_name'];
$Row['pro_money']		= $Arr['pro_money'];
$Row['pro_sale_referer']= $Arr['pro_sale_referer'];


$Event					= $Ado->GetOne("SELECT * FROM big{$AppID}_pro_event WHERE pro_sale_no='{$Row['pro_sale_no']}'");
if( !empty($Event) ){
	$Json['status']			= false;
	$Json['error']			= '订单编号不可重复.';
	output($Json);
}


$Ado->AutoExecute("big{$AppID}_pro_event", $Row, "INSERT");
$Ado->Execute("UPDATE big{$AppID}_pro SET pro_sale=pro_sale+{$Arr['pro_money']} WHERE pro_no='{$Arr['pro_no']}'");


//如果用户不为空则更新用户最后活动时间
if( !empty($Arr['user_no'])){
	$Time				= time();
	$Ado->Execute("UPDATE big{$AppID}_user SET user_time_update='{$Time}',user_sale=user_sale+{$Row['pro_money']} WHERE user_no='{$Arr['user_no']}'");
}

$Json['status']			= true;
$Json['error']			= '';
output($Json);