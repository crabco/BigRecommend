<?php
$Arr					= LoadData('app_id,app_key,app_password,p,s');

$ExistsApp				= ExistsApp($Arr['app_id'],$Arr['app_key'],$Arr['app_password']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];
unset($Arr['app_id'],$Arr['app_key'],$Arr['app_password']);

//======================================================================================
$AppRow			= $Ado->GetRow("SELECT * FROM big_app_cache WHERE app_id='{$AppID}'");

$Json['User']	= array();

$Page			= ( ceil($Arr['p'])>1 )? ceil($Arr['p']) : 1;
$Size			= ( ceil($Arr['s'])>1 && ceil($Arr['s'])<1000 )? ceil($Arr['s']) : 100;
$Lim			= ( $Page-1 )*$Size;
$User			= $Ado->SelectLimit("SELECT user_no,user_sex,user_age,user_phone,user_sale,user_time_login AS user_login FROM big{$AppID}_user ORDER BY user_sale DESC", $Size,$Lim);

if( !empty($User) ){
foreach($User as $Rs){
	$Json['User'][$Rs['user_no']]	= $Rs;
}
}

output($Json);