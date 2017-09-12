<?php
$Arr					= LoadData('app_id,app_key,user_no,browse_no,browse_url,browse_referer');

$ExistsApp				= ExistsApp($Arr['app_id'],null, $Arr['app_key']);
if( $ExistsApp!==true )	output($ExistsApp);
$AppID					= $Arr['app_id'];

$Row					= array();
$Row['user_no']			= $Arr['user_no'];
$Row['user_ip']			= FunGetTrueIP();
$Row['user_os']			= FunGetOs();
$Row['user_brower']		= FunGetBrowse();

$Row['browse_no']		= $Arr['browse_no'];
$Row['browse_url']		= substr($Arr['browse_url'],255);
$Row['browse_referer']	= substr($Arr['browse_referer'],255);
$Row['browse_update']	= date("Y-m-d H:i:s");
$Row['browse_timelong']	= 10;
$Row['browse_time']		= date("Y-m-d H:i:s");

$BrowseRow				= array();
//如果浏览编号为空,则创建新序号
if( empty($Row['browse_no'])||!preg_match("/^[a-z0-9]{10,20}$/i",$Row['browse_no']) ){
	$Row['browse_no']	= time() . FunRandABC(9);
}else{
	$BrowseRow			= $Ado->GetRow("SELECT * FROM big{$AppID}_browse WHERE browse_no='{$Row['browse_no']}' AND browse_url='{$Row['browse_url']}'");
}

if( empty($BrowseRow) ){
	$Ado->AutoExecute("big{$AppID}_browse", $Row, "INSERT" );
}else{
	$Row					= $BrowseRow;
	$Row['browse_timelong']	= time() - strtotime($BrowseRow['browse_time']);
	$Ado->AutoExecute("big{$AppID}_browse", $Row, "UPDATE", "browse_no='{$Row['browse_no']}' AND browse_url='{$Row['browse_url']}'");
}

$Json['status']			= true;
$Json['error']			= '';
$Json['browse_no']		= $Row['browse_no'];
output($Json);