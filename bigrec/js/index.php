<?php
ini_set("display_errors", "off");

$AppID			= $_GET['app_id'];
$AppKey			= $_GET['app_key'];
$AppPassword	= $_GET['app_password'];
$AppURL			= $_SERVER['HTTP_HOST'];
header("Content-Type: application/x-javascript");
?>
var bigrec_appid 	= '<?php echo $AppID?>';
var bigrec_appkey	= '<?php echo $AppKey?>';
var bigrec_apppass	= '<?php echo $AppPassword?>';
var bigrec_url		= 'http://<?php echo $AppURL?>/bigrec/';

//开始加载默认JQUERY
if( typeof jQuery=='undefined' ){
	document.write("<script type='text/javascript' src='"+bigrec_url+"js/jquery.min.js'></script>");
}

function iswhere( Type ){
	var err = "";
	if( bigrec_appid=="" ){
		console.log('bigrec:无应用序号,程序无法工作');
		return false;
	}
	
	if( Type=='key' && bigrec_appkey=='' ){
		console.log('bigrec:无应用KEY，程序无法工作');
		return false;
	}
	
	if( Type=='pass' && bigrec_apppass=='' ){
		console.log('bigrec:无应用Pass，程序无法工作');
		return false;
	}
	return true;
}



//user_no,user_sex,user_age,user_phone,user_reco
function bigrec_usermodify(InData){
	if( iswhere('pass')!=true ) return ;
	if( typeof InData!='object' ){
		console.log('bigrec:数据类型错误');return;
	}
	
	var Push = {};
	Push['app_id']			= bigrec_appid;
	Push['app_password']	= bigrec_apppass;
	Push['user_no']			= InData['user_no'];
	Push['user_sex']		= InData['user_sex'];
	Push['user_age']		= InData['user_age'];
	Push['user_phone']		= InData['user_phone'];
	Push['user_reco']		= InData['user_reco'];
	
	var PushURL				= bigrec_url + "?act=user_modify";
	 $.post(PushURL,Push,function(Json){
	    if( Json.status==false ) console.log("bigrec:"+Json.error);
	 },'json');
}

function bigrec_userremove(InData){
	if( iswhere('pass')!=true ) return ;
	if( typeof InData!='object' ){
		console.log('bigrec:数据类型错误');return;
	}
	
	var Push = {};
	Push['app_id']			= bigrec_appid;
	Push['app_password']	= bigrec_apppass;
	Push['user_no']			= InData['user_no'];
	
	var PushURL				= bigrec_url + "?act=user_remove";
	 $.post(PushURL,Push,function(Json){
	    if( Json.status==false ) console.log("bigrec:"+Json.error);
	 },'json');
}

function bigrec_userlogin(InData){
	if( iswhere('pass')!=true ) return ;
	if( typeof InData!='object' ){
		console.log('bigrec:数据类型错误');return;
	}
	
	var Push = {};
	Push['app_id']			= bigrec_appid;
	Push['app_password']	= bigrec_apppass;
	Push['user_no']			= InData['user_no'];
	
	var PushURL				= bigrec_url + "?act=user_login";
	 $.post(PushURL,Push,function(Json){
	    if( Json.status==false ) console.log("bigrec:"+Json.error);
	 },'json');
}


function bigrec_userbrowse(InData){
	if( iswhere('key')!=true ) return ;
	
	var Push = {};
	Push['app_id']			= bigrec_appid;
	Push['app_key']			= bigrec_appkey;
	Push['user_no']			= InData['user_no'];
	Push['browse_url']		= location.href;
	Push['browse_referer']	= document.referrer;
	
	var BrowseNo			= sessionStorage.getItem("bigrec_browseno");
	Push['browse_no']		= BrowseNo;
	
	var PushURL				= bigrec_url + "?act=user_browse";
	$.post(PushURL,Push,function(Json){
	   if( Json.status==false ){
	   	   console.log("bigrec:"+Json.error);
	   }else{
	       if( Json['browse_no']!=undefined && Json['browse_no'].length>0 ){
	       	  sessionStorage.setItem("bigrec_browseno",Json['browse_no']);
	       }
	   }
	},'json');
	window.onbeforeunload = function(event){ bigrec_userbrowse(InData); }
}




//pro_no,pro_name,pro_cover,pro_tags,pro_grade
function bigrec_promodify(InData){
	if( iswhere('pass')!=true ) return ;
	if( typeof InData!='object' ){
		console.log('bigrec:数据类型错误');return;
	}
	
	var Push = {};
	Push['app_id']			= bigrec_appid;
	Push['app_password']	= bigrec_apppass;
	Push['pro_no']			= InData['pro_no'];
	Push['pro_name']		= InData['pro_name'];
	Push['pro_cover']		= InData['pro_cover'];
	Push['pro_tags']		= InData['pro_tags'];
	Push['pro_grade']		= InData['pro_grade'];
	
	var PushURL				= bigrec_url + "?act=pro_modify";
	 $.post(PushURL,Push,function(Json){
	    if( Json.status==false ) console.log("bigrec:"+Json.error);
	 },'json');
}

function bigrec_proremove(InData){
	if( iswhere('pass')!=true ) return ;
	if( typeof InData!='object' ){
		console.log('bigrec:数据类型错误');return;
	}
	
	var Push = {};
	Push['app_id']			= bigrec_appid;
	Push['app_password']	= bigrec_apppass;
	Push['pro_no']			= InData['pro_no'];
	
	var PushURL				= bigrec_url + "?act=pro_remove";
	 $.post(PushURL,Push,function(Json){
	    if( Json.status==false ) console.log("bigrec:"+Json.error);
	 },'json');
}

//pro_no,share_name,user_no
function bigrec_proshare(InData){
	if( iswhere('key')!=true ) return ;
	if( typeof InData!='object' ){
		console.log('bigrec:数据类型错误');return;
	}
	
	var Push = {};
	Push['app_id']			= bigrec_appid;
	Push['app_key']			= bigrec_appkey;
	Push['pro_no']			= InData['pro_no'];
	Push['share_name']		= InData['share_name'];
	Push['user_no']			= InData['user_no'];
	
	var PushURL				= bigrec_url + "?act=pro_share";
	 $.post(PushURL,Push,function(Json){
	    if( Json.status==false ) console.log("bigrec:"+Json.error);
	 },'json');
}


//pro_no,pro_play,pro_seq,user_no
function bigrec_proplay(InData){
	if( iswhere('key')!=true ) return ;
	if( typeof InData!='object' ){
		console.log('bigrec:数据类型错误');return;
	}
	
	var Push = {};
	Push['app_id']			= bigrec_appid;
	Push['app_key']			= bigrec_appkey;
	Push['pro_no']			= InData['pro_no'];
	Push['pro_play']		= InData['pro_play'];
	Push['pro_seq']			= InData['pro_seq'];
	Push['user_no']			= InData['user_no'];
	Push['pro_play_status']	= 'new';
	
	var PushURL				= bigrec_url + "?act=pro_play";
	if( typeof bigrec_settime!='undefined' ) clearInterval(bigrec_settime);
	
	$.post(PushURL,Push,function(Json){
	   if( Json.status==false ) console.log("bigrec:"+Json.error);
	   Push['pro_play_status']	= 'sleep';
	},'json');
	
	window.bigrec_settime	= setInterval( function(){
								$.post(PushURL,Push,function(Json){
								   if( Json.status==false ) console.log("bigrec:"+Json.error);
								},'json')},1000*60);
}

//pro_no,pro_sale_no,pro_pay_name,pro_money,user_no,pro_seq,pro_sale_referer
function bigrec_prosale(InData){
	if( iswhere('key')!=true ) return ;
	if( typeof InData!='object' ){
		console.log('bigrec:数据类型错误');return;
	}
	
	var Push = {};
	Push['app_id']			= bigrec_appid;
	Push['app_key']			= bigrec_appkey;
	Push['pro_no']			= InData['pro_no'];
	Push['pro_sale_no']		= InData['pro_sale_no'];
	Push['pro_pay_name']	= InData['pro_pay_name'];
	Push['pro_money']		= InData['pro_money'];
	Push['user_no']			= InData['user_no'];
	Push['pro_seq']			= InData['pro_seq'];
	Push['pro_sale_referer']= InData['pro_sale_referer'];
	
	var PushURL				= bigrec_url + "?act=pro_sale";
	 $.post(PushURL,Push,function(Json){
	    if( Json.status==false ) console.log("bigrec:"+Json.error);
	 },'json');
}

