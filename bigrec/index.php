<?php
require_once 'inc/auto.inc.php';
header("Access-Control-Allow-Origin: *");


$Action				= strtolower(trim($_GET['act']));
$Json				= array();
$Ado				= ADOPdo::Start();
$NoPreg				= '/^[a-z0-9-_]{1,50}$/i';
$NoUserPreg			= '/^u-[a-z0-9]{30,30}$/i';

//检测监听程序是否在运行中
AppCommandIs();

switch($Action){
	/**
	 * 2.1	应用创建接口
	 */
	case "app_create":
		include_once 'mod/app_create.php';
	break;
	
	/**
	 * 2.2	应用修改接口
	 */
	case "app_modify":
		include_once 'mod/app_modify.php';
	break;
	
	/**
	 * 4.1.1 用户资料添加|修改接口
	 */
	case "user_modify":
		include_once 'mod/user_modify.php';
	break;
	
	/**
	 * 4.1.2 用户资料移除接口
	 */
	case "user_remove":
		include_once 'mod/user_remove.php';
	break;
	
	/**
	 * 4.1.3 用户登录事件上报接口
	 * 
	 */
	case "user_login":
		include_once 'mod/user_login.php';
	break;
	
	/**
	 * 4.1.4 用户浏览事件上报接口
	 *
	 */
	case "user_browse":
		include_once 'mod/user_browse.php';
		break;
	
	/**
	 * 4.2.1 商品添加|修改上报接口
	 */
	case "pro_modify":
		include_once 'mod/pro_modify.php';
	break;
	
	/**
	 * 4.2.2 商品移除上报接口
	 */
	case "pro_remove":
		include_once 'mod/pro_remove.php';
	break;
	
	/**
	 * 4.2.3 商品分享上报
	 */
	case "pro_share":
		include_once 'mod/pro_share.php';
	break;
	
	/**
	 * 4.2.4	商品查看上报
	 */
	case "pro_play":
		include_once 'mod/pro_play.php';
	break;
	
	/**
	 * 4.2.5	商品购买上报
	 */
	case "pro_sale":
		include_once 'mod/pro_sale.php';
	break;
	
	/**
	 * 5.1.0	商品智能推荐接口
	 */
	case "reco":
		include_once 'mod/reco.php';
	break;
	
	/**
	 * 5.1.1	智能推荐结果反馈接口
	 */
	case "reco_up":
		include_once 'mod/reco_up.php';
	break;
	
	/**
	 * 5.2	用户对象－统计信息获取接口
	 */
	case "stat_user":
		include_once 'mod/stat_user.php';
	break;
	
	/**
	 * 5.3	用户对象－购买排行榜查询接口
	 */
	case "stat_user_sale":
		include_once 'mod/stat_user_sale.php';
	break;
	
	/**
	 * 5.4	商品对象－统计信息获取接口
	 */
	case "stat_pro":
		include_once 'mod/stat_pro.php';
	break;
		
	/**
	 * 5.5	商品对象－销量排行榜查询接口
	 */
	case "stat_pro_sale":
		include_once 'mod/stat_pro_sale.php';
	break;
	
	/**
	 * 5.6	商品对象－浏览排行榜查询接口
	 */
	case "stat_pro_info":
		include_once 'mod/stat_pro_info.php';
	break;
	
	/**
	 * 5.7	商品对象－内容查看时长排行榜接口
	 */
	case "stat_pro_playtime":
		include_once 'mod/stat_pro_playtime.php';
	break;
}