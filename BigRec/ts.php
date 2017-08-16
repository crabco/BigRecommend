<?php

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

echo '1';