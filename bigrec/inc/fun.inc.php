<?php

/**
 * 数据获取方法
 * @param unknown $Tags
 * @param string $Type
 */
function LoadData( $Tags,$Type='post' ){
	
	$Name		= explode(",", $Tags);
	
	if( !preg_match('/^[a-z0-9_,]+$/i', $Tags)||empty($Name) ){
		return array();
	}
	
	$Arr		= array();
	
	foreach($Name as $Rs){
		if( $Type=='post' && isset($_POST[$Rs]) ) 		$Arr[$Rs]	= $_POST[$Rs];
		if( $Type=='get' && isset($_GET[$Rs]) )  		$Arr[$Rs]	= $_GET[$Rs];
		if( $Type=='cookie' && isset($_COOKIE[$Rs]) )  	$Arr[$Rs]	= $_COOKIE[$Rs];
		
		//临时测试,如果POST没数据，开始搜索GET数据
		if( $Type=='post'&&!isset($_POST[$Rs]) ){
			$Arr[$Rs]	= $_GET[$Rs];
		}
	}
	
	return $Arr;
}

/**
 * 解析数组的方法
 * @param unknown $Arr
 */
function ParseArray($Arr){
	
	$AppName		= array();
	$AppVal			= array();
	$AppSet			= array();
	$AppNo			= array();
	
	if( !empty($Arr) ){
		foreach($Arr as $Vs=>$Rs){
			if( empty($Rs) )continue;
			$AppName[]		= "`{$Vs}`";
			$AppVal[]		= "'{$Rs}'";
			$AppSet[]		= "`{$Vs}`='{$Rs}'";
			$AppNo[]		= $Rs;
		}
	}
	return array('name'=>$AppName,'value'=>$AppVal,'set'=>$AppSet,"no"=>$AppNo);
}

/**
 * 判断程序是否在运行中
 */
function AppCommandIs(){
	global $Ado;
	
	$AppRow	= $Ado->GetOne("SELECT COUNT(*) FROM big_command");
	if( ceil($AppRow)<=0 ){
		output( array('status'=>false,'error'=>'系统暂未运行,请启动监听程序') );
	}
	
	$AppRow	= $Ado->GetRow("SELECT * FROM big_command");
	
	//如果日志有数据并且更新时间少于6分钟以内,则退出本进程
	if( empty($AppRow) || ceil($AppRow['command_time'])<time()-60*10 ){
		output( array('status'=>false,'error'=>'系统暂未运行,请启动监听程序') );
	}
	return true;
}


/**
 * 同步内存表与记录表数据 big_app
 * @param unknown $AppID
 */
function AppSync( $AppID=null ){
	global $Ado;
	
	if( $AppID==null ){
		$Total			= $Ado->GetOne("SELECT COUNT(app_id) FROM big_app_cache");
		if( ceil($Total)<=0 ){
			$Ado->Execute("INSERT INTO big_app_cache SELECT * FROM big_app");
			$Total		= $Ado->GetOne("SELECT COUNT(app_id) FROM big_app_cache");
			for($i=0;$i<$Total;$i+500){
				$Tab	= $Ado->SelectLimit("SELECT app_id FROM big_app_cache", 500, $i);
				if(!empty($Tab)){
				foreach($Tab as $Rs){
					$ID		= $Rs['app_id'];
					
					//更新所有申报条数
					$Sum	= $Ado->GetOne("SELECT count(app_id) FROM big_declaration WHERE app_id={$ID}");
					$Sum	= $Sum + ceil($Ado->GetOne("SELECT count(app_id) FROM big_report_declaration WHERE app_id={$ID}"));
					$Ado->Execute("UPDATE big_app_cache SET app_total_declaration={$Sum} WHERE app_id={$ID}");
				}
				}
			}
		}
	}
	
	
	
	if( ceil($AppID)>0 ){
		$AppRow			= $Ado->GetRow("SELECT * FROM big_app WHERE app_id='{$AppID}'");
		$AppName		= array();
		$AppVal			= array();
		$AppSet			= array();
		if( !empty($AppRow) ){
			foreach($AppRow as $Vs=>$Rs){
				$AppName[]		= "`{$Vs}`";
				$AppVal[]		= "'{$Rs}'";
				$AppSet[]		= "`{$Vs}`='{$Rs}'";
			}
			
			$Ado->Execute("INSERT big_app_cache (".implode(",", $AppName).") VALUES(".implode(",", $AppVal).") ON DUPLICATE KEY UPDATE ".implode(",", $AppSet));
		}
	}
}


/**
 * 开始所有应用的统计任务
 */
function AppSum(){
	global $Ado;
	$App	= $Ado->GetAll("SELECT * FROM big_app_cache");
	foreach($App as $AppRs){
		$AppID						= $AppRs['app_id'];
		$Arr						= array();
		$Arr['app_total_user']		= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_user");
		$Arr['app_total_sale']		= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_user WHERE user_sale>0");
		$Arr['app_total_man']		= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_user WHERE user_sex='男'");
		$Arr['app_total_woman']		= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_user WHERE user_sex='女'");
		$Arr['app_total_reco']		= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_user WHERE user_reco<>''");
		$Arr['app_total_active']	= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_user WHERE user_time_update>".time()-86400*30);

		$Arr['app_total_active_man']		= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_user WHERE user_sex='男' AND user_time_update>".time()-86400*30);
		$Arr['app_total_active_woman']		= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_user WHERE user_sex='女' AND user_time_update>".time()-86400*30);

		$Ado->AutoExecute("big_app", $Arr, "UPDATE","app_id={$AppID}");
		$Ado->AutoExecute("big_app_cache", $Arr, "UPDATE","app_id={$AppID}");
	}
	sleep(1);
}


/**
 * 统计经销商数据
 */
function AppSumReco(){
	global $Ado;
	$App	= $Ado->GetAll("SELECT * FROM big_app_cache");
	foreach($App as $AppRs){
		$AppID				= $AppRs['app_id'];
		$RecoSum			= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_user_reco");
		$Lim				= 0;
		while($Lim<$RecoSum){
			$RecoAll		= $Ado->SelectLimit("SELECT * FROM big{$AppID}_user_reco", 100,$Lim);
			if( !empty($RecoAll) ){
			foreach($RecoAll as $Rs){
				$RecoName		= $Rs['reco_name'];
				$Rs['reco_sum']	= $Ado->GetOne("SELECT COUNT(*) FROM big{$AppID}_user WHERE user_reco='{$RecoName}'");
				$Ado->AutoExecute("big{$AppID}_user_reco", $Rs, "UPDATE", "reco_name='{$RecoName}'");
			}
			}
			$Lim			= $Lim+100;
		}
	}
	sleep(1);
}


/**
 * 迁移用户浏览数据历史记录
 */
function AppSumBrowse(){
	global $Ado;
	$App	= $Ado->GetAll("SELECT * FROM big_app_cache");
	$Time	= date("Y-m-d H:00:00",time()-7200);			//两小时前
	
	foreach($App as $AppRs){
		$AppID				= $AppRs['app_id'];
		$Ado->Execute("INSERT INTO big{$AppID}_browse_history SELECT * FROM big{$AppID}_browse WHERE browse_update<'{$Time}'");
		$Ado->Execute("DELETE FROM big{$AppID}_browse WHERE browse_update<'{$Time}'");
	}
	sleep(1);
}

function AppLog($Log){
	if( PHP_OS=='WINNT' ){
		echo iconv("utf-8", "gb2312", $Log."\r\n");
	}else{
		echo ($Log."\r\n");
	}
}

/**
 * 更新监听程序的时间
 */
function AppCommand( $Log=null ){
	global $Ado,$CliCommandAppRow;
	
	if( !defined('AppCommandID') ){
		define('AppCommandID', time() );
	}
	
	if( empty($CliCommandAppRow) ){
		$AppRow				= $Ado->GetRow("SELECT * FROM big_command");
		$CliCommandAppRow	= $AppRow;
	}else{
		$AppRow				= $CliCommandAppRow;
	}
	
	//如果日志有数据并且更新时间少于6分钟以内,则退出本进程
	if( !empty($AppRow) && strval($AppRow['command_id'])!=strval(AppCommandID) && ceil($AppRow['command_time'])>time()-60*10 ){
		exit('Repetitive process');
	}
	
	//如果数据不为空并且ID不等于本次数据,则替换本次数据
	if( !empty($AppRow) && strval($AppRow['command_id'])!=strval(AppCommandID) ){
		$Arr				= array('command_id'=>AppCommandID,'command_time'=>time(),'command_log'=>$Log);
		$Ado->Execute("DELETE FROM big_command WHERE 1=1");
		$Ado->AutoExecute('big_command', $Arr, "INSERT");
	}else{
		$Arr				= array('command_id'=>AppCommandID,'command_time'=>time());
		if( !empty($Log) ){
			$Arr['command_log']	= $Log;
			AppLog($Log);
		}
		
		if( empty($AppRow) ){
			$Ado->AutoExecute('big_command', $Arr, "INSERT");
		}else{
			$Ado->AutoExecute('big_command', $Arr, "UPDATE", "1=1");
		}
	}
	$CliCommandAppRow	= $Arr;
}


/**
 * 根据KEY与PASSWORD判断是否正确
 * @param unknown $AppID
 * @param unknown $AppPass
 * @param unknown $AppKey
 */
function ExistsApp($AppID,$AppPass=null,$AppKey=null){
	global $Ado;
	
	if( !preg_match('/^[0-9]+$/i', $AppID) ){
		$Json['status']		= false;
		$Json['error']		= '不存在的应用';
		return $Json;
	}
	$AppRow					= $Ado->GetRow("SELECT * FROM big_app_cache WHERE app_id='{$AppID}'");
	if( empty($AppRow) ){
		$Json['status']		= false;
		$Json['error']		= '不存在的应用.';
		return $Json;
	}
	
	if( !empty($AppPass) && md5($AppRow['app_password'])!=md5($AppPass) ){
		$Json['status']		= false;
		$Json['error']		= '管理密码错误';
		return $Json;
	}
	
	if( !empty($AppKey) && md5($AppRow['app_key'])!=md5($AppKey) ){
		$Json['status']		= false;
		$Json['error']		= '提交密钥错误错误';
		return $Json;
	}
	
	if( empty($AppPass)&&empty($AppKey) ){
		$Json['status']		= false;
		$Json['error']		= '访问密钥错误';
		return $Json;
	}
	
	return true;
}



/**
 * 创建字母数字的随机字符
 * @param 生成的长度 $MaxInt
 * @return string
 */
function FunRandABC($Long){
	$LongText	= '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$RunText	= '';
	$ForInt		= 0;
	$MaxInt		= ceil($MaxInt);

	while($ForInt<$Long){
		$LongLen	= strlen($LongText);
		$LongId		= rand(0, $LongLen-1);
		$RunText   .= substr($LongText, $LongId,1);
		$ForInt++;
	}

	return $RunText;
}


/**
 * 获取用户的浏览器版本
 */
function FunGetBrowse(){
	if(!empty($_SERVER['HTTP_USER_AGENT'])){
    	$br = $_SERVER['HTTP_USER_AGENT'];
    	
    	if (preg_match('/MSIE/i',$br)) {    
        	$br = 'MSIE';
        }elseif (preg_match('/Firefox/i',$br)) {
     		$br = 'Firefox';
	    }elseif (preg_match('/Chrome/i',$br)) {
	     	$br = 'Chrome';
	    }elseif (preg_match('/Safari/i',$br)) {
	     	$br = 'Safari';
	    }elseif (preg_match('/Opera/i',$br)) {
	        $br = 'Opera';
	    }else {
	        $br = 'Other';
	    }
    	return $br;
   }else{
   		return "";
   } 
}



/**
 * 获取用户的操作系统版本
 * @return string
 */
function FunGetOs(){
	if(!empty($_SERVER['HTTP_USER_AGENT'])){
		$OS 	= $_SERVER['HTTP_USER_AGENT'];
		if (preg_match('/win/i',$OS)) {
			$OS = 'Windows';
		}elseif (preg_match('/mac/i',$OS)) {
			$OS = 'MAC';
		}elseif (preg_match('/linux/i',$OS)) {
			$OS = 'Linux';
		}elseif (preg_match('/unix/i',$OS)) {
			$OS = 'Unix';
		}elseif (preg_match('/bsd/i',$OS)) {
			$OS = 'BSD';
		}else {
			$OS = 'Other';
		}
		return $OS;
	}else{
		return "";
	}
}

/**
 * 获取用户的真实IP
 */
function FunGetTrueIP(){
	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
		$ip = $_SERVER["HTTP_CLIENT_IP"];
	}
	if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){ //获取代理ip
		$ips = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
	}
	if($ip){
		$ips = array_unshift($ips,$ip);
	}
	$count = count($ips);
	for($i=0;$i<$count;$i++){
		if(!preg_match("/^(10|172\.16|192\.168)\./i",$ips[$i])){//排除局域网ip
			$ip = $ips[$i];
			break;
		}
	}
	$tip 	= empty($_SERVER['REMOTE_ADDR']) ? $ip : $_SERVER['REMOTE_ADDR'];
	return $tip;
}


////获得本服务器的外部IP
function FunGetTrueIPLocation() {
	$mip 	= file_get_contents("http://city.ip138.com/city0.asp");
	if($mip){
		preg_match("/\[.*\]/",$mip,$sip);
		$p = array("/\[/","/\]/");
		return preg_replace($p,"",$sip[0]);
	}else{
		return "";
	}
}



////根据ip获得访客所在地地名
function FunGetIpAddress( $ip='' ){
	
	if(empty($ip)){
		$ip = FunGetTrueIP();
	}
	
	$ipadd 	= file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?ip=".$ip);//根据新浪api接口获取
	
	if($ipadd){
		$charset = iconv("gbk","utf-8",$ipadd);
		preg_match_all("/[\x{4e00}-\x{9fa5}]+/u",$charset,$ipadds);
		return $ipadds;   //返回一个二维数组
	}else{
		return "";
	}
}




/**
 * 将数组合并为串化数据
 * @param unknown $Row
 * @param string $Exp
 * @param string $Flot
 * @return string
 */
function FunToString($Row,$Exp="",$Flot=""){
	$Exp	= ( empty($Exp) )? "," : $Exp;
	$Flot	= ( empty($Flot) )? "" : $Flot;
	foreach($Row as $Vs=>$Rs){
		$Row[$Vs]	= "{$Flot}{$Rs}{$Flot}";
	}
	$Text	= implode($Exp, $Row);
	return $Text;
}




/**
 * 直接输出内容
 */
function output($Json,$Exit=true){
	echo json_encode($Json);
	if( $Exit==true ){
		exit;
	}
}


