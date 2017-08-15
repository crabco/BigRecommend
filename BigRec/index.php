<?php
require_once 'inc/auto.inc.php';

$Action				= strtolower(trim($_GET['act']));
$Json				= array();
$Ado				= ADOPdo::Start();
$NoPreg				= '/^[a-z0-9-_]{1,50}$/i';

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
		
		$AppOnly				= $Ado->GetOne("SELECT count(app_id) FROM big_app WHERE app_name='{$Arr['app_name']}'");
		if( ceil($AppOnly)>0 ){
			$Json['status']		= false;
			$Json['error']		= '重复的应用名称';
			break;
		}
		
		$Arr['app_reco_data']	= 7;
		$Arr['app_time_create']	= date("Y-m-d H:i:s");
		
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
		
		$Ex						= $Ado->AutoExecute('big_app', $Arr, 'INSERT');
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
		
		$AppRow					= $Ado->GetRow("SELECT * FROM big_app WHERE app_id='{$Arr['app_id']}'");
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
		
		if( isset($Arr['app_name']) && $Arr['app_name']!=$AppRow['app_name'] ){
			if( empty($Arr['app_name']) ){
				$Json['status']		= false;
				$Json['error']		= '应用名称重复';
				break;
			}
			
			$AppOnly				= $Ado->GetOne("SELECT count(app_id) FROM big_app WHERE app_name='{$Arr['app_name']}'");
			if( ceil($AppOnly)>0 ){
				$Json['status']		= false;
				$Json['error']		= '应用名称重复';
				break;
			}
		}
		
		if( isset($Arr['app_exp']) && empty($Arr['app_exp']) ){
			$Json['status']			= false;
			$Json['error']			= '应用简介不能为空';
			break;
		}
		
		if( isset($Arr['app_password']) && ( empty($Arr['app_password'])||strlen($Arr['app_password'])<6 ) ){
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
		
		$Ex						= $Ado->AutoExecute('big_app', $Arr, 'UPDATE', "app_id='{$Arr['app_id']}'");
		if( !$Ex ){
			$Json['status']		= false;
			$Json['error']		= '修改失败,数据未发生任何改变';
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
		unset($Arr['app_password']);
		$Arr['user_time_create']	= date("Y-m-d H:i:s");
		
		if( !preg_match($NoPreg, $Arr['user_no']) ){
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
		
		$Arr					= LoadData('app_id,app_password,val_no,val_name,val_cover,val_tags,val_show,val_grade');
		$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		unset($Arr['app_password']);
		
		if( isset($Arr['val_grade'])&&!empty($Arr['val_grade'])&&(ceil($Arr['val_grade'])<0||ceil($Arr['val_grade'])>9 ) ){
			$Json['status']		= false;
			$Json['error']		= '显示权重数据非法';
			break;
		}
		
		//添加标签资料
		$TagArr					= explode(",", $Arr['val_tags']);
		if( empty($TagArr) ){
			$Json['status']		= false;
			$Json['error']		= '资料必须要有标签';
			break;
		}else{
			$New				= array();
			foreach($TagArr as $Rs){
				$New			= array('app_id'=>$Arr['app_id'],'app_tags'=>$Rs);
				$Ado->AutoExecute('big_app_tags', $New, 'INSERT');
			}
		}
		
		$Arr['val_time_update']	= date("Y-m-d H:i:s");
		
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
		unset($Arr['app_password']);
		
		$Ado->Execute("DELETE FROM big_value WHERE val_no={$Arr['val_no']} AND app_id={$Arr['app_id']}");
		
		$Json['status']			= true;
		$Json['error']			= '';
		break;
	break;
	
	
	
	
	
	/**
	 * 2.1	自动推荐接口
	 */
	case "reco":
		$Arr					= LoadData('app_id,app_key,user_no,size','get');
		$Cok					= LoadData('bigrec_userno','cookie');
		
		$ExistsApp				= ExistsApp($Arr['app_id'],null,$Arr['app_key']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		
		unset($Arr['app_key']);
		
		//如果没有传输用户序号,则强制写入游客序号
		if( !preg_match($NoPreg, $Arr['user_no']) ){
			$UserNo				= 'u:'.FunRandABC(30);
			$Arr['user_no']		= $UserNo;
			setcookie('bigrec_userno',$UserNo,time()+86400*5);
		}else{
			$UserNo				= $Arr['user_no'];
		}
		
		$AppRow					= $Ado->GetRow("SELECT * FROM big_app_cache WHERE app_id={$Arr['app_id']}");
		
		
		//待选的ID池
		$AppIdElected			= array();
		//已经选择的ID池
		$AppIdChoice			= array();
		//真实需要获取的数量
		$loadSize				= ( preg_match('/^[0-9]+$/i',$Arr['size'])&&ceil($Arr['size'])>1&&ceil($Arr['size'])<101)? ceil($Arr['size']) : 10;
		//总共需要获取的记录数量
		$loadMax				= $loadSize * 100;
		
		//冷启动
		if( ceil($AppRow['app_total_declaration'])<10000 ){
			$TagTab				= $Ado->GetAll("SELECT * FROM big_app_tags WHERE app_id={$Arr['app_id']}");
			
			//标签池无任何资料
			if( empty($TagTab) ){
				$Json['status']			= true;
				$Json['error']			= '';
				$Json['val']			= array();
				break;
			}else{
				//有资料,则根据标签个数平均分配每个标签获取的条数
				$loadRows				= ceil( $loadMax/count($TagTab) );
				foreach($TagTab as $Rs){
					$RsAll				= $Ado->SelectLimit("SELECT val_no FROM big_value WHERE app_id={$Arr['app_id']} AND val_show='true' AND find_in_set('{$Rs}',val_tags) ORDER BY val_grade DESC",$loadRows);
					if( !empty($RsAll) ){
					foreach($RsAll as $Rss){
						$AppIdElected[]	= $Rss['val_no'];
					}
					}
				}
				//如果所有标签均查询完毕仍旧不够待选总数,则随机不足
				$loadRows				= $loadMax - count($AppIdElected);
				if( $loadRows>0 ){
					$RsAll				= $Ado->SelectLimit("SELECT val_no FROM big_value WHERE app_id={$Arr['app_id']} AND val_show='true' ORDER BY val_time_update DESC",$loadRows);
					if( !empty($RsAll) ){
						foreach($RsAll as $Rss){
							$AppIdElected[]	= $Rss['val_no'];
						}
					}
				}
			}
		}
		
		
		//待选池载入完毕,开始随机选择数据
		if( $loadSize>=count($AppIdElected) ){
			$AppIdChoice		= $AppIdElected;
		}else{
			while( count($AppIdChoice)<$loadSize  ){
				$ChoiceID		= $AppIdElected[ rand(0,count($AppIdElected)-1) ];
				if( !in_array($ChoiceID, $AppIdChoice) ){
					$AppIdChoice[]	= $ChoiceID;
				}
			}
		}
		
		
		
		//根据选择池加载资料列表
		$Tab					= $Ado->GetAll("SELECT * FROM big_value WHERE app_id={$Arr['app_id']} AND val_no IN (".FunToString($AppIdChoice,",","'").")");
		$Json['status']			= true;
		$Json['error']			= '';
		$Json['val']			= $Tab;
		
		$New					= array();
		if( !empty($Tab) ){
			foreach($AppIdChoice as $Rs){
				$Tr							= array();
				$Tr['app_id']				= $Arr['app_id'];
				$Tr['user_no']				= $Arr['user_no'];
				$Tr['val_no']				= $Rs;
				$Tr['user_ip']				= ip2long(FunGetTrueIP());
				$Tr['user_brower']			= FunGetBrowse();
				$Tr['user_os']				= FunGetOs();
				$Tr['user_time_create']		= date("Y-m-d H:i:s");
				$New[]						= $Tr;
			}
			$Ado->AutoExecute('big_reocmmend', $New, 'INSERT');
		}
		break;
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
		unset($Arr['app_key']);
		
	break;
	
	
	
	
	
	
	/**
	 * 2.3	访问上报接口
	 */
	case "up":
		$Arr					= LoadData('app_id,app_key,user_no,val_no','get');
		$Cok					= LoadData('bigrec_userno','cookie');
		
		$ExistsApp				= ExistsApp($Arr['app_id'],null,$Arr['app_key']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		unset($Arr['app_key']);
		
		if( !preg_match($NoPreg, $Arr['val_no']) ){
			$Json['status']		= false;
			$Json['error']		= '申报资料序号错误';
			break;
		}
		
		//如果没有传输用户序号,则强制写入游客序号
		if( !preg_match($NoPreg, $Arr['user_no'])&&!preg_match('/^u\:[a-z0-9]{30,30}$/i', $subject) ){
			$Json['status']		= false;
			$Json['error']		= '不允许不进行推荐立即执行上报措施';
			break;
		}
		
		$Arr['user_ip']				= ip2long(FunGetTrueIP());
		$Arr['user_brower']			= FunGetBrowse();
		$Arr['user_os']				= FunGetOs();
		$Arr['user_time_create']	= date("Y-m-d H:i:s");
		
		$Ex							= $Ado->AutoExecute('big_declaration', $Arr, 'INSERT');
		
		if( $Ex ){
			$Json['status']			= true;
			$Json['error']			= '';
			
			$Ado->Execute("UPDATE big_app_cache SET app_total_declaration=app_total_declaration+1 WHERE app_id={$Arr['app_id']}");
		}else{
			$Json['status']			= false;
			$Json['error']			= '写入失败';
		}
	break;
}

echo json_encode($Json);