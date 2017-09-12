<?php
$Arr					= LoadData('app_id,app_key,app_password,p,s');

$ExistsApp				= ExistsApp($Arr['app_id'],$Arr['app_key'],$Arr['app_password']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];
unset($Arr['app_id'],$Arr['app_key'],$Arr['app_password']);

//======================================================================================

$Json['pro']	= array();

$Page			= ( ceil($Arr['p'])>1 )? ceil($Arr['p']) : 1;
$Size			= ( ceil($Arr['s'])>1 && ceil($Arr['s'])<1000 )? ceil($Arr['s']) : 100;
$Lim			= ( $Page-1 )*$Size;
$User			= $Ado->SelectLimit("SELECT * FROM big{$AppID}_pro ORDER BY pro_info DESC", $Size,$Lim);

if( !empty($User) ){
	foreach($User as $Rs){
		$Json['pro'][$Rs['pro_no']]	= $Rs;
	}
}

output($Json);