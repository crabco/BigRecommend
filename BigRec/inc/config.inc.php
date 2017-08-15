<?php

define("DATABASE_HOST", "localhost");					//数据库地址
define("DATABASE_USER", "big_user");				   	//数据库帐号
define("DATABASE_PASS", "big_pass");				    //数据库密码
define("DATABASE_NAME", "gli_bigrecommend");			//数据库名称



$SelfPath				= substr(dirname(__FILE__),0,-4);
//系统根目录  
define('SYSTEM_PATH',	$SelfPath);

//是否开启调试模式
define('SYSTEM_DEBUG', true);
