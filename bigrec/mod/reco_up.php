<?php
$Arr					= LoadData('app_id,app_key,user_no,pro_no,pro_tags','get');
$Cok					= LoadData('bigrec_userno','cookie');

$ExistsApp				= ExistsApp($Arr['app_id'],null,$Arr['app_key']);
if( $ExistsApp!==true ){
	$Json				= $ExistsApp;
	output($Json);
}
$AppID					= $Arr['app_id'];
unset($Arr['app_key'],$Arr['app_id']);

if( !preg_match($NoPreg, $Arr['pro_no']) ){
	$Json['status']		= false;
	$Json['error']		= '申报资料序号错误';
	output($Json);
}

//如果没有传输用户序号,则强制写入游客序号
if( !preg_match($NoPreg, $Arr['user_no'])&&!preg_match($NoUserPreg, $Cok['bigrec_userno']) ){
	$Json['status']		= false;
	$Json['error']		= '不允许不进行推荐立即执行上报措施';
	output($Json);
}

//如果没有上报标签,则报错
if( empty($Arr['pro_tags']) ){
	$Json['status']		= false;
	$Json['error']		= '上报资料不全';
	output($Json);
}

if( !preg_match($NoPreg, $Arr['user_no']) && preg_match($NoUserPreg, $Cok['bigrec_userno']) ){
	$Arr['user_no']			= $Cok['bigrec_userno'];
}

$Arr['user_ip']				= ip2long(FunGetTrueIP());
$Arr['user_brower']			= FunGetBrowse();
$Arr['user_os']				= FunGetOs();
$Arr['user_time_create']	= date("Y-m-d H:i:s");

$Ex							= $Ado->AutoExecute("big{$AppID}_declaration", $Arr, 'INSERT');

if( $Ex ){
	$Json['status']			= true;
	$Json['error']			= '';
		
	$Ado->Execute("UPDATE big_app_cache SET app_total_declaration=app_total_declaration+1 WHERE app_id={$AppID}");
}else{
	$Json['status']			= false;
	$Json['error']			= '写入失败';
}

output($Json);