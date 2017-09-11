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

function bigrec_usermodify(user_no,user_sex,user_age,user_phone){
	
}

function bigrec_userremove(user_no){
	
}

function bigrec_userlogin(user_no){
	
}

function bigrec_promodify(pro_no,pro_name,pro_cover,pro_tags,pro_grade){
	
}

function bigrec_proremove(pro_no){
	
}

function bigrec_proshare(pro_no,share_name){
	
}

function bigrec_proplay(pro_no,pro_seq){
	
}

function bigrec_prosale(pro_no,pro_seq){
	
}

