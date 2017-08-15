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


function ParseArray($Arr){
	
	$AppName		= array();
	$AppVal			= array();
	$AppSet			= array();
	
	if( !empty($Arr) ){
		foreach($Arr as $Vs=>$Rs){
			$AppName[]		= "`{$Vs}`";
			$AppVal[]		= "'{$Rs}'";
			$AppSet[]		= "`{$Vs}`='{$Rs}'";
		}
	}
	return array('name'=>$AppName,'value'=>$AppVal,'set'=>$AppSet);
}


/**
 * 同步内存表与记录表数据 big_app
 * @param unknown $AppID
 */
function SyncApp( $AppID=null ){
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

////获得访客真实ip
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
