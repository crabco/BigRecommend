<?php
require_once 'inc/auto.inc.php';

$Action				= strtolower(trim($_GET['act']));
$Json				= array();
$Ado				= ADOPdo::Start();

SyncApp();

switch($Action){
	/**
	 * 1.1	应用创建接口
	 */
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
	
	
	
	
	/**
	 * 1.2	应用修改接口
	 */
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
	
	
	
	
	
	
	/**
	 * 1.3	用户资料添加|修改接口
	 */
	case "user_modify":
		$Arr					= LoadData('app_id,app_password,user_no,user_sex,user_age,user_phone');
		
		$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		
		if( !preg_match('/^[a-z0-9-_\.,]{1,32}$/i', $Arr['user_no']) ){
			$Json['status']		= false;
			$Json['error']		= '用户序号非法,仅支持字母、数字、下横线、点（英文）等符号，最长32位';
			break;
		}
		
		if( isset($Arr['user_sex'])&&$Arr['user_sex']!='男'&&$Arr['user_sex']!='女'&&$Arr['user_sex']!='保密' ){
			$Json['status']		= false;
			$Json['error']		= '用户性别错误';
			break;
		}
		
		if( isset($Arr['user_age'])&&ceil($Arr['user_age'])>200&&ceil($Arr['user_age'])<1 ){
			$Json['status']		= false;
			$Json['error']		= '用户年龄错误';
			break;
		}
		
		if( isset($Arr['user_phone'])&&!preg_match('/^[0-9\+]{11,15}$/i', $Arr['user_phone']) ){
			$Json['status']		= false;
			$Json['error']		= '用户手机号码错误';
			break;
		}
		
		$Row					= ParseArray($Arr);
		$RowName				= implode(",", $Row['name']);
		$RowValue				= implode(",", $Row['value']);
		$RowSet					= implode(",", $Row['set']);
		$Ex						= $Ado->Execute("INSERT INTO big_user ({$RowName}) VALUES({$RowValue}) ON DUPLICATE KEY UPDATE {$RowSet}");
		
		if( $Ex ){
			$Json['status']		= true;
			$Json['error']		= '';
			break;
		}else{
			$Json['status']		= false;
			$Json['error']		= '写入失败';
			break;
		}
		
	break;
	
	
	
	
	
	/**
	 * 1.4	用户资料移除接口
	 */
	case "user_remove":
		$Arr					= LoadData('app_id,app_password,user_no');
		
		$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		
		$Ado->Execute("DELETE FROM big_user WHERE user_no={$Arr['user_no']} AND app_id={$Arr['app_id']}");
		
		$Json['status']			= true;
		$Json['error']			= '';
		break;
		
	break;
	
	
	
	
	
	
	/**
	 * 1.5	统计资料添加|修改接口
	 */
	case "data_modify":
		
		$Arr					= LoadData('app_id,app_password,val_no,val_name,val_cover,val_tags,val_show');
		$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		
		$Row					= ParseArray($Arr);
		$RowName				= implode(",", $Row['name']);
		$RowValue				= implode(",", $Row['value']);
		$RowSet					= implode(",", $Row['set']);
		$Ex						= $Ado->Execute("INSERT INTO big_value ({$RowName}) VALUES({$RowValue}) ON DUPLICATE KEY UPDATE {$RowSet}");
		
		if( $Ex ){
			$Json['status']		= true;
			$Json['error']		= '';
			break;
		}else{
			$Json['status']		= false;
			$Json['error']		= '写入失败';
			break;
		}
		
	break;
	
	
	
	
	
	/**
	 * 1.6	统计资料移除接口
	 */
	case "data_remove":
		$Arr					= LoadData('app_id,app_password,val_no');
		
		$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		
		$Ado->Execute("DELETE FROM big_value WHERE val_no={$Arr['val_no']} AND app_id={$Arr['app_id']}");
		
		$Json['status']			= true;
		$Json['error']			= '';
		break;
	break;
	
	
	
	
	
	/**
	 * 2.1	自动推荐接口
	 */
	case "reco":
		$Arr					= LoadData('app_id,app_key,user_no,val_no');
		
		$ExistsApp				= ExistsApp($Arr['app_id'],null,$Arr['app_key']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		
		
		
	break;
	
	
	
	
	
	/**
	 * 2.2	访问统计接口
	 */
	case "stat":
		$Arr					= LoadData('app_id,app_key,user_no,val_no');
		
		$ExistsApp				= ExistsApp($Arr['app_id'],null,$Arr['app_key']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		
		
	break;
	
	
	
	
	
	
	/**
	 * 2.3	访问上报接口
	 */
	case "up":
		$Arr					= LoadData('app_id,app_key,user_no,val_no');
		
		$ExistsApp				= ExistsApp($Arr['app_id'],null,$Arr['app_key']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		
		
		$Json['status']			= true;
		$Json['error']			= '';
		break;
	break;
}

echo json_encode($Json);