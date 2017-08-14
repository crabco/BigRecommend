<?php
require_once 'inc/auto.inc.php';

$Action				= strtolower(trim($_GET['act']));
$Json				= array();
$Ado				= ADOPdo::Start();

SyncApp();

switch($Action){
	case "app_create":
		$Arr					= LoadData('app_name,app_exp,app_password,app_key');
		
		if( empty($Arr['app_name'])||empty($Arr['app_exp'])||empty($Arr['app_password'])||empty($Arr['app_key']) ){
			$Json['status']		= false;
			$Json['error']		= '关键数据缺失';
			break;
		}
		
		$AppOnly				= $Ado->GetOne("SELECT count(app_id) FROM bid_app WHERE app_name='{$Arr['app_name']}'");
		if( ceil($AppOnly)>0 ){
			$Json['status']		= false;
			$Json['error']		= '重复的应用名称';
			break;
		}
		
		$Arr['app_reco_data']	= 7;
		$Arr['app_time_create']	= time();
		
		$Ex						= $Ado->AutoExecute('bid_app', $Arr, 'INSERT');
		$AppID					= $Ado->InsertID();
		if( !$Ex || empty($AppID) ){
			$Json['status']		= false;
			$Json['error']		= '创建失败';
		}else{
			$Json['status']		= true;
			$Json['error']		= '';
			$Json['app_id']		= $AppID;
			SyncApp($AppID);
		}
		
	break;
	
	
	
	case "app_modify":
		$Arr					= LoadData('app_id,app_pass,app_name,app_exp,app_password,app_key,app_reco_data');
		
		if( empty($Arr['app_id'])||empty($Arr['app_key']) ){
			$Json['status']		= false;
			$Json['error']		= '关键数据缺失';
			break;
		}
		
		$AppRow					= $Ado->GetRow("SELECT * FROM bid_app WHERE app_id='{$Arr['app_id']}'");
		if( empty($AppRow) ){
			$Json['status']		= false;
			$Json['error']		= '不存在的数据';
			break;
		}
		
		if( md5($Arr['app_pass'])!=md5($AppRow['app_password']) ){
			$Json['status']		= false;
			$Json['error']		= '管理密码错误';
			break;
		}
		
		unset($Arr['app_pass']);
		
		if( isset($Arr['app_name']) ){
			if( empty($Arr['app_name']) ){
				$Json['status']		= false;
				$Json['error']		= '应用名称重复';
				break;
			}
			
			$AppOnly				= $Ado->GetOne("SELECT count(app_id) FROM bid_app WHERE app_name='{$Arr['app_name']}'");
			if( ceil($AppOnly)>0 ){
				$Json['status']		= false;
				$Json['error']		= '应用名称重复';
				break;
			}
		}
		
		if( isset($Arr['app_exp'])&&empty($Arr['app_exp']) ){
			$Json['status']			= false;
			$Json['error']			= '应用简介不能为空';
			break;
		}
		
		if( isset($Arr['app_password'])&& ( empty($Arr['app_password'])||strlen($Arr['app_password'])<6 ) ){
			$Json['status']			= false;
			$Json['error']			= '应用管理密码不能为空且不能少于6位';
			break;
		}
		
		if( isset($Arr['app_key'])&& ( empty($Arr['app_key'])||strlen($Arr['app_key'])<6 ) ){
			$Json['status']			= false;
			$Json['error']			= '应用提交密钥不能为空且不能少于6位';
			break;
		}
		
		if( ceil($Arr['app_reco_data'])<1||ceil($Arr['app_reco_data'])>90 ){
			$Json['status']			= false;
			$Json['error']			= '统计周期天数只能大于1且小于90天';
			break;
		}
		
		
		$Ex						= $Ado->AutoExecute('bid_app', $Arr, 'UPDATE', "app_id='{$Arr['app_id']}'");
		if( !$Ex ){
			$Json['status']		= false;
			$Json['error']		= '修改失败';
		}else{
			$Json['status']		= true;
			$Json['error']		= '';
			SyncApp($Arr['app_id']);
		}
	break;
	
	
	
	
	
	case "user_modify":
		$Arr					= LoadData('app_id,app_pass,user_no,user_sex,user_age,user_phone');
		$AppRow					= $Ado->GetRow("SELECT * FROM big_app_cache WHERE app_id='{$Arr['app_id']}'");
		
		
		
		
		
		
		
	break;
}

echo json_encode($Json);