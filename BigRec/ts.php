<?php
ini_set("display_errors", "off");
error_reporting(0);
IF( $_GET['act']=='new_data' ){
	for($i=1;$i<10;$i++){
		$No			= "a00".rand(0,10000);
		$Name		= "产品-".$No;
		$Tag		= array('视频','食品','饭','菜','炒菜','炒饭','鸡蛋','果汁');
		$TagName	= array();
		$TagName[]	= $Tag[ rand(0,count($Tag)-1) ];
		$TagName[]	= $Tag[ rand(0,count($Tag)-1) ];
		$TagName[]	= $Tag[ rand(0,count($Tag)-1) ];
		
		file_get_contents("http://sea.com/bigrec/?act=data_modify&app_id=1&app_password=123456&val_no={$No}&val_name=".urlencode($Name)."&val_cover=&val_tags=".urlencode(implode(",", $TagName))."&val_show=true");
	}
}

if( $_GET['act']=='up'){
	for($i=1;$i<10;$i++){
		$No			= "a00".rand(0,10000);
		$Name		= "产品-".$No;
		$Tag		= array('视频','食品','饭','菜','炒菜','炒饭','鸡蛋','果汁');
		$TagName	= array();
		$TagName[]	= $Tag[ rand(0,count($Tag)-1) ];
		$TagName[]	= $Tag[ rand(0,count($Tag)-1) ];
		$TagName[]	= $Tag[ rand(0,count($Tag)-1) ];
		file_get_contents("http://sea.com/bigrec/?act=up&app_id=1&app_key=123412&val_no={$No}&user_no=1&val_tags=".urlencode(implode(",", $TagName))."");
	}
}
?>
<html>
<title>测试页面 </title>
<meta charset="utf-8"/>
<head>
<script src="http://libs.baidu.com/jquery/2.1.1/jquery.min.js"></script>
</head>
<body>
推荐内容
<div id="reco">
</div>
<script language="javascript">
function reco(){
	$("#reco").html('');
	$.get("./?act=reco&app_id=1&app_key=123412&user_no=<?php echo $_GET['user_no']?>",function(Json){
		var ValTab = '';
		for(var i=0;i<Json['val'].length;i++){
			var Val	= Json['val'][i];
			ValTab += "<a href='javascript:' onclick=\"reco_up('"+Val['val_no']+"','"+Val['val_tags']+"')\">"+Val['val_name']+"</a><br>";
		}

		ValTab += "<br>推荐方式:";
		
		var Val	= Json['info']["choice"];
		for(var i=0;i<Val.length;i++){
			ValTab += Val[i] + "<br>";
		}
		$('#reco').html(ValTab);
	},'json');
}

function reco_up(ID,Tag){
	URL = "./?act=up&app_id=1&app_key=123412&user_no=&val_no="+ID+"&val_tags="+Tag;
	$.get(URL,function(){
		
	},'json');
}

reco();
</script>
</body>
</html>