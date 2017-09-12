<?php
$Arr					= LoadData('app_id,app_pass,app_name,app_exp,app_password,app_key,app_reco_data,app_stat_type');

if( empty($Arr['app_id'])||empty($Arr['app_key']) ){
	$Json['status']		= false;
	$Json['error']		= '关键数据缺失';
	output($Json);
}

$AppRow					= $Ado->GetRow("SELECT * FROM big_app WHERE app_id='{$Arr['app_id']}'");
if( empty($AppRow) ){
	$Json['status']		= false;
	$Json['error']		= '不存在的数据';
	output($Json);
}

if( md5($Arr['app_pass'])!=md5($AppRow['app_password']) ){
	$Json['status']		= false;
	$Json['error']		= '管理密码错误';
	output($Json);
}

unset($Arr['app_pass']);

if( isset($Arr['app_name']) && $Arr['app_name']!=$AppRow['app_name'] ){
	if( empty($Arr['app_name']) ){
		$Json['status']		= false;
		$Json['error']		= '应用名称重复';
		output($Json);
	}
		
	$AppOnly				= $Ado->GetOne("SELECT count(app_id) FROM big_app WHERE app_name='{$Arr['app_name']}'");
	if( ceil($AppOnly)>0 ){
		$Json['status']		= false;
		$Json['error']		= '应用名称重复';
		output($Json);
	}
}

if( isset($Arr['app_exp']) && empty($Arr['app_exp']) ){
	$Json['status']			= false;
	$Json['error']			= '应用简介不能为空';
	output($Json);
}

if( isset($Arr['app_password']) && ( empty($Arr['app_password'])||strlen($Arr['app_password'])<5 ) ){
	$Json['status']			= false;
	$Json['error']			= '应用管理密码不能为空且不能少于5位';
	output($Json);
}

if( isset($Arr['app_key'])&& ( empty($Arr['app_key'])||strlen($Arr['app_key'])<5 ) ){
	$Json['status']			= false;
	$Json['error']			= '应用提交密钥不能为空且不能少于5位';
	output($Json);
}

if( ceil($Arr['app_reco_data'])<1||ceil($Arr['app_reco_data'])>90 ){
	$Json['status']			= false;
	$Json['error']			= '统计周期天数只能大于1且小于90天';
	output($Json);
}

if( isset($Arr['app_stat_type']) && $Arr['app_stat_type']!='public' ){
	$Arr['app_stat_type']	= 'private';
}

$Ex						= $Ado->AutoExecute('big_app', $Arr, 'UPDATE', "app_id='{$Arr['app_id']}'");
if( !$Ex ){
	$Json['status']		= false;
	$Json['error']		= '修改失败,数据未发生任何改变';
}else{
	$Json['status']		= true;
	$Json['error']		= '';
	AppSync($Arr['app_id']);
}
output($Json);