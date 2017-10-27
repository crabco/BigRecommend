/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : gli_bigrecommend

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2017-10-27 13:23:57
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `big_app_tags`
-- ----------------------------
DROP TABLE IF EXISTS `big_app_tags`;
CREATE TABLE `big_app_tags` (
  `app_tags` varchar(100) DEFAULT NULL COMMENT '标签名称',
  UNIQUE KEY `tag_only` (`app_tags`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='应用标签池';

-- ----------------------------
-- Records of big_app_tags
-- ----------------------------

-- ----------------------------
-- Table structure for `big_browse`
-- ----------------------------
DROP TABLE IF EXISTS `big_browse`;
CREATE TABLE `big_browse` (
  `user_no` varchar(50) DEFAULT NULL,
  `user_ip` varchar(20) DEFAULT NULL COMMENT '用户的IP地址',
  `user_os` varchar(20) DEFAULT NULL COMMENT '用户操作系统',
  `user_brower` varchar(20) DEFAULT NULL COMMENT '用户的浏览器',
  `browse_no` varchar(20) DEFAULT NULL COMMENT '本页面本用户的唯一编号',
  `browse_url` varchar(255) DEFAULT NULL COMMENT ' 浏览地址',
  `browse_referer` varchar(255) DEFAULT NULL COMMENT '访问地址来源',
  `browse_update` datetime DEFAULT NULL COMMENT '最后一次刷新时间',
  `browse_timelong` int(11) unsigned DEFAULT NULL COMMENT '本次访问停留时间长度,秒',
  `browse_time` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '浏览时间',
  KEY `browse_no` (`browse_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of big_browse
-- ----------------------------

-- ----------------------------
-- Table structure for `big_browse_history`
-- ----------------------------
DROP TABLE IF EXISTS `big_browse_history`;
CREATE TABLE `big_browse_history` (
  `user_no` varchar(50) DEFAULT NULL,
  `user_ip` varchar(20) DEFAULT NULL COMMENT '用户的IP地址',
  `user_os` varchar(20) DEFAULT NULL COMMENT '用户操作系统',
  `user_brower` varchar(20) DEFAULT NULL COMMENT '用户的浏览器',
  `browse_no` varchar(20) DEFAULT NULL COMMENT '本页面本用户的唯一编号',
  `browse_url` varchar(255) DEFAULT NULL COMMENT ' 浏览地址',
  `browse_referer` varchar(255) DEFAULT NULL COMMENT '访问地址来源',
  `browse_time` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '浏览时间',
  `browse_update` datetime DEFAULT NULL COMMENT '最后一次刷新时间',
  `browse_timelong` int(11) unsigned DEFAULT NULL COMMENT '本次访问停留时间长度,秒',
  KEY `browse_no` (`browse_no`),
  KEY `browse_update` (`browse_update`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of big_browse_history
-- ----------------------------

-- ----------------------------
-- Table structure for `big_command`
-- ----------------------------
DROP TABLE IF EXISTS `big_command`;
CREATE TABLE `big_command` (
  `command_id` int(11) unsigned DEFAULT '0' COMMENT '当前监听程序的序号',
  `command_time` int(11) unsigned DEFAULT '0' COMMENT '当前监听程序的最后更新时间',
  `command_log` varchar(255) DEFAULT NULL COMMENT '最后一条日志'
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='监听程序的数据交换空间';

-- ----------------------------
-- Records of big_command
-- ----------------------------

-- ----------------------------
-- Table structure for `big_declaration`
-- ----------------------------
DROP TABLE IF EXISTS `big_declaration`;
CREATE TABLE `big_declaration` (
  `user_no` varchar(50) DEFAULT NULL COMMENT '推荐的用户序号',
  `pro_no` varchar(32) DEFAULT NULL COMMENT '推荐的资料序号',
  `pro_tags` tinytext COMMENT '推荐标签',
  `user_ip` int(10) DEFAULT NULL COMMENT '用户当前的IP地址，通过IPLONG方法转换为数字',
  `user_brower` varchar(20) DEFAULT NULL COMMENT '用户的浏览器',
  `user_os` varchar(20) DEFAULT NULL COMMENT '用户操作系统',
  `user_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '资料创建时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户访问申报记录表';

-- ----------------------------
-- Records of big_declaration
-- ----------------------------

-- ----------------------------
-- Table structure for `big_pro`
-- ----------------------------
DROP TABLE IF EXISTS `big_pro`;
CREATE TABLE `big_pro` (
  `pro_no` varchar(32) DEFAULT NULL COMMENT '资料编号',
  `pro_name` varchar(50) DEFAULT NULL COMMENT '资料名称',
  `pro_cover` varchar(255) DEFAULT NULL COMMENT '资料封面图',
  `pro_tags` tinytext COMMENT '资料标签,英文逗号分割多个',
  `pro_grade` int(2) unsigned DEFAULT '0' COMMENT '资料显示权重,0-9，当显示为9时强制推荐,不建议超过10个',
  `pro_show` enum('true','false') DEFAULT 'true' COMMENT '资料是否参与统计',
  `pro_exp` tinytext COMMENT '商品简介',
  `pro_html` text COMMENT '商品介绍，支持HTML格式数据，不支持JAVASCRIPT',
  `pro_money` decimal(12,2) unsigned DEFAULT '0.00' COMMENT '商品售价,不能为空',
  `pro_sell` tinyint(1) unsigned DEFAULT '0' COMMENT '商品是否处于上架状态，默认为不上架（0）',
  `pro_sale` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '该商品总共被出售的交易金额',
  `pro_info` int(11) unsigned DEFAULT '0' COMMENT '商品被查看总次数',
  `pro_playtime` int(11) unsigned DEFAULT '0' COMMENT '商品被查看的总计时长',
  `pro_share` int(11) unsigned DEFAULT '0' COMMENT '商品被分享的总次数',
  `pro_time_update` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '资料添加\\更新时间',
  `pro_val1` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val2` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val3` varchar(200) DEFAULT NULL,
  `pro_val4` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val5` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val6` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val7` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val8` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val9` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val10` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val11` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val12` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val13` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val14` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val15` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val16` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val17` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val18` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val19` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  `pro_val20` varchar(200) DEFAULT NULL COMMENT '商品补充属性',
  UNIQUE KEY `pro_no` (`pro_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of big_pro
-- ----------------------------

-- ----------------------------
-- Table structure for `big_pro_event`
-- ----------------------------
DROP TABLE IF EXISTS `big_pro_event`;
CREATE TABLE `big_pro_event` (
  `pro_no` varchar(50) DEFAULT NULL,
  `event_type` varchar(10) DEFAULT NULL COMMENT '事件类型',
  `event_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '事件发生时间',
  `event_time_date` varchar(10) DEFAULT '2017-01-01' COMMENT '事件时间，年月日',
  `event_time_hour` varchar(8) DEFAULT '00:00' COMMENT '事件时间，小时分',
  `event_time_update` datetime DEFAULT NULL COMMENT '最后一次更新时间',
  `pro_seq` varchar(50) DEFAULT NULL COMMENT '商品下级分类',
  `user_no` varchar(50) DEFAULT NULL COMMENT '可能涉及的用户序号',
  `user_ip` varchar(20) DEFAULT NULL COMMENT '访问用户的IP',
  `event_time_sum` int(10) unsigned DEFAULT '0' COMMENT '该事件持续时长',
  `pro_sale_no` varchar(50) DEFAULT NULL COMMENT '涉及第三方订单的订单号',
  `pro_pay_name` varchar(50) DEFAULT NULL COMMENT '涉及第三方名称',
  `pro_money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '涉及第三方交易的金额',
  `pro_sale_referer` varchar(50) DEFAULT NULL COMMENT '涉及第三方交易的引入渠道',
  KEY `pro_sale_no` (`pro_sale_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品事件记录表';

-- ----------------------------
-- Records of big_pro_event
-- ----------------------------

-- ----------------------------
-- Table structure for `big_reocmmend`
-- ----------------------------
DROP TABLE IF EXISTS `big_reocmmend`;
CREATE TABLE `big_reocmmend` (
  `user_no` varchar(50) DEFAULT NULL COMMENT '推荐的用户序号',
  `pro_no` varchar(32) DEFAULT NULL COMMENT '推荐的资料序号',
  `user_ip` int(10) DEFAULT NULL COMMENT '用户当前的IP地址，通过IPLONG方法转换为数字',
  `user_brower` varchar(20) DEFAULT NULL COMMENT '用户的浏览器',
  `user_os` varchar(20) DEFAULT NULL COMMENT '用户操作系统',
  `user_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '资料创建时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='推荐记录表';

-- ----------------------------
-- Records of big_reocmmend
-- ----------------------------

-- ----------------------------
-- Table structure for `big_user`
-- ----------------------------
DROP TABLE IF EXISTS `big_user`;
CREATE TABLE `big_user` (
  `user_no` varchar(32) DEFAULT NULL COMMENT '用户序号',
  `user_nickname` varchar(10) DEFAULT NULL COMMENT '用户昵称',
  `user_name` varchar(50) DEFAULT NULL COMMENT '用户登录帐号,具体规则需根据开启用户增强功能选择决定',
  `user_password` varchar(50) DEFAULT NULL COMMENT '用户密码,必须６－５0位长度，建议数字+字母大小写的组合.',
  `user_pic` varchar(100) DEFAULT NULL COMMENT '用户头像',
  `user_type` enum('apply','auto') DEFAULT 'auto' COMMENT '用户类型，apply为申请添加的固定用户,auto为游客',
  `user_sex` enum('男','女','保密') DEFAULT '保密' COMMENT '用户性别',
  `user_age` int(3) unsigned DEFAULT '0' COMMENT '用户年龄',
  `user_phone` varchar(20) DEFAULT NULL COMMENT '用户手机',
  `user_reco` varchar(50) DEFAULT NULL COMMENT '用户推荐来源名称',
  `user_sale` decimal(12,2) unsigned DEFAULT '0.00' COMMENT '用户购买交易产生的总金额',
  `user_time_update` int(11) unsigned DEFAULT '0' COMMENT '用户最后活跃时间',
  `user_time_login` int(11) unsigned DEFAULT '0' COMMENT '用户最后登录时间',
  `user_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '资料添加时间',
  UNIQUE KEY `user_no` (`user_no`) USING BTREE,
  KEY `user_time_update` (`user_time_update`),
  KEY `user_sale` (`user_sale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用用户记录表';


-- ----------------------------
-- Table structure for `big_user_event`
-- ----------------------------
DROP TABLE IF EXISTS `big_user_event`;
CREATE TABLE `big_user_event` (
  `user_no` varchar(50) DEFAULT NULL,
  `event_type` varchar(10) DEFAULT NULL COMMENT '事件类型,login为登录事件',
  `event_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '事件发生时间',
  `event_time_date` varchar(10) DEFAULT '2017-01-01' COMMENT '事件时间，年月日',
  `event_time_hour` varchar(8) DEFAULT '00:00' COMMENT '事件时间，小时分',
  KEY `user_no` (`user_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户事件记录表';

-- ----------------------------
-- Table structure for `big_user_reco`
-- ----------------------------
DROP TABLE IF EXISTS `big_user_reco`;
CREATE TABLE `big_user_reco` (
  `reco_name` varchar(50) NOT NULL DEFAULT '' COMMENT '推荐渠道的名称',
  `reco_sum` int(10) unsigned DEFAULT '0' COMMENT '推荐用户的总数',
  PRIMARY KEY (`reco_name`),
  UNIQUE KEY `reco_name` (`reco_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户推荐记录表';

-- ----------------------------
-- Table structure for `big_app`
-- ----------------------------
DROP TABLE IF EXISTS `big_app`;
CREATE TABLE `big_app` (
  `app_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用自增序号',
  `app_name` varchar(200) DEFAULT NULL COMMENT '应用名称',
  `app_exp` varchar(200) DEFAULT NULL COMMENT '应用简介',
  `app_password` varchar(50) DEFAULT NULL COMMENT '应用管理密钥',
  `app_key` varchar(50) DEFAULT NULL COMMENT '应用提交密钥',
  `app_reco_data` int(3) unsigned DEFAULT '30' COMMENT '本应用单统计周期的天数,默认30天',
  `app_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '应用创建时间',
  `app_stat_type` enum('public','private') DEFAULT 'private' COMMENT '该应用的统计资料是否公开',
  `app_total_declaration` int(11) unsigned DEFAULT '0' COMMENT '当前应用共计申报条数',
  `app_total_user` int(11) unsigned DEFAULT '0' COMMENT '当前应用用户总数量',
  `app_total_sale` int(11) unsigned DEFAULT '0' COMMENT '购买过产品的总共人数',
  `app_total_man` int(11) unsigned DEFAULT '0' COMMENT '所有用户中的男性个数',
  `app_total_woman` int(11) unsigned DEFAULT '0' COMMENT '所有用户中的女性个数',
  `app_total_reco` int(11) unsigned DEFAULT '0' COMMENT '通过分销商引入用户数量',
  `app_total_active` int(11) unsigned DEFAULT '0' COMMENT '３０天内活跃用户总数,对用户活跃度的定义为有任何操作与上报记录（排除管理端操作记录）',
  `app_total_active_man` int(11) unsigned DEFAULT '0' COMMENT '活跃用户中的男性个数',
  `app_total_active_woman` int(11) unsigned DEFAULT '0' COMMENT '活跃用户中的女性个数',
  `super_user_off` enum('on','off') DEFAULT 'off' COMMENT '是否已经打开用户增强功能',
  `user_sechost` varchar(50) DEFAULT NULL COMMENT '登录、退出登录后允许跳转地址的安全域名,以上跳转地址必须在以上域名范围内。',
  `user_login_only` int(1) unsigned DEFAULT '1' COMMENT '是否允许用户在同一平台多用户登录,默认为1，允许.0为不允许',
  `user_login_long` int(1) unsigned DEFAULT '0' COMMENT '是否开通永久登录功能，默认为0，0为不开通。1为开通.',
  `user_accounts` varchar(100) DEFAULT NULL COMMENT '注册帐号的格式要求，支持email,phone,user,qq，支持英文逗号分割多个类型支持',
  `user_loginout_url` varchar(100) DEFAULT NULL COMMENT '用户退出，超时退出后，系统跳转地址，如果为空则自动弹出登录窗口',
  `super_pro_off` enum('on','off') DEFAULT 'off' COMMENT '该应用是否已经打开了增强商品功能',
  `pay_name` varchar(20) DEFAULT NULL COMMENT '用于提现的银行名称',
  `pay_dename` varchar(20) DEFAULT NULL COMMENT '用于提现的银行详细支行名称',
  `pay_number` varchar(20) DEFAULT NULL COMMENT '用于提现的银行帐号',
  `pay_user` varchar(20) DEFAULT NULL COMMENT '用于提现的主体名称（账户名）',
  `pay_password` varchar(20) DEFAULT NULL COMMENT '提现支付密钥',
  `pay_info_val` varchar(255) DEFAULT NULL COMMENT '产品补充属性的中文名称,格式为{val1:商品编号,val1:商品类别,val3-20},最大支持20个补充属性。',
  PRIMARY KEY (`app_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='应用记录表';

-- ----------------------------
-- Table structure for `big_app_cache`
-- ----------------------------
DROP TABLE IF EXISTS `big_app_cache`;
CREATE TABLE `big_app_cache` (
  `app_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用自增序号',
  `app_name` varchar(200) DEFAULT NULL COMMENT '应用名称',
  `app_exp` varchar(200) DEFAULT NULL COMMENT '应用简介',
  `app_password` varchar(50) DEFAULT NULL COMMENT '应用管理密钥',
  `app_key` varchar(50) DEFAULT NULL COMMENT '应用提交密钥',
  `app_reco_data` int(3) unsigned DEFAULT NULL COMMENT '本应用单统计周期的天数',
  `app_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '应用创建时间',
  `app_stat_type` enum('public','private') DEFAULT 'private' COMMENT '该应用的统计资料是否公开',
  `app_total_declaration` int(11) unsigned DEFAULT '0' COMMENT '当前应用共计申报条数',
  `app_total_user` int(11) unsigned DEFAULT '0' COMMENT '当前应用用户总数量',
  `app_total_sale` int(11) unsigned DEFAULT '0' COMMENT '购买过产品的总共人数',
  `app_total_man` int(11) unsigned DEFAULT '0' COMMENT '所有用户中的男性个数',
  `app_total_woman` int(11) unsigned DEFAULT '0' COMMENT '所有用户中的女性个数',
  `app_total_reco` int(11) unsigned DEFAULT '0' COMMENT '通过分销商引入用户数量',
  `app_total_active` int(11) unsigned DEFAULT '0' COMMENT '３０天内活跃用户总数,对用户活跃度的定义为有任何操作与上报记录（排除管理端操作记录）',
  `app_total_active_man` int(11) unsigned DEFAULT '0' COMMENT '活跃用户中的男性个数',
  `app_total_active_woman` int(11) unsigned DEFAULT '0' COMMENT '活跃用户中的女性个数',
  `super_user_off` enum('on','off') DEFAULT 'off' COMMENT '是否已经打开用户增强功能',
  `user_sechost` varchar(50) DEFAULT NULL COMMENT '登录、退出登录后允许跳转地址的安全域名,以上跳转地址必须在以上域名范围内。',
  `user_login_only` int(1) unsigned DEFAULT '1' COMMENT '是否允许用户在同一平台多用户登录,默认为1，允许.0为不允许',
  `user_login_long` int(1) unsigned DEFAULT '0' COMMENT '是否开通永久登录功能，默认为0，0为不开通。1为开通.',
  `user_accounts` varchar(100) DEFAULT NULL COMMENT '注册帐号的格式要求，支持email,phone,user,qq，支持英文逗号分割多个类型支持',
  `user_loginout_url` varchar(100) DEFAULT NULL COMMENT '用户退出，超时退出后，系统跳转地址，如果为空则自动弹出登录窗口',
  `super_pro_off` enum('on','off') DEFAULT 'off' COMMENT '该应用是否已经打开了增强商品功能',
  `pay_name` varchar(20) DEFAULT NULL COMMENT '用于提现的银行名称',
  `pay_dename` varchar(20) DEFAULT NULL COMMENT '用于提现的银行详细支行名称',
  `pay_number` varchar(20) DEFAULT NULL COMMENT '用于提现的银行帐号',
  `pay_user` varchar(20) DEFAULT NULL COMMENT '用于提现的主体名称（账户名）',
  `pay_password` varchar(20) DEFAULT NULL COMMENT '提现支付密钥',
  `pay_info_val` varchar(255) DEFAULT NULL COMMENT '产品补充属性的中文名称,格式为{val1:商品编号,val1:商品类别,val3-20},最大支持20个补充属性。',
  PRIMARY KEY (`app_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='应用记录表的缓存表，任何修改或者移除应用的时候需要同步跟新该表，当该表为空，则直接从big_app复制数据到该表';

-- ----------------------------
-- Records of big_app_cache
-- ----------------------------

-- ----------------------------
-- Table structure for `big_command`
-- ----------------------------
DROP TABLE IF EXISTS `big_command`;
CREATE TABLE `big_command` (
  `command_id` int(11) unsigned DEFAULT '0' COMMENT '当前监听程序的序号',
  `command_time` int(11) unsigned DEFAULT '0' COMMENT '当前监听程序的最后更新时间',
  `command_log` varchar(255) DEFAULT NULL COMMENT '最后一条日志'
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='监听程序的数据交换空间';

-- ----------------------------
-- Records of big_command
-- ----------------------------

-- ----------------------------
-- Table structure for `big_order`
-- ----------------------------
DROP TABLE IF EXISTS `big_order`;
CREATE TABLE `big_order` (
  `order_no` varchar(50) NOT NULL COMMENT '订单编号',
  `user_no` varchar(50) DEFAULT NULL COMMENT '购买用户序号',
  `user_address` tinytext COMMENT '用户收货地址,格式为：{user_name:xxx,phone:xxx,address:xxx,post_code:xxx}',
  `order_money` decimal(12,2) unsigned DEFAULT '0.00' COMMENT '订单总价',
  `order_status` tinyint(2) unsigned DEFAULT '0' COMMENT '订单状态,\r\n0创建订单等待付款，\r\n1付款中,无法修改订单信息（无法通过本接口改变，系统将自动根据付款信息改变）\r\n2为已经付款,等待发货（无法通过本接口改变，系统将自动根据付款信息改变）\r\n3已经发货,等待收货,\r\n4主动收货,等待评价\r\n5自动收货,等待评价\r\n6已经评价\r\n10 订单取消\r\n11订单删除\r\n21退货申请（原因与说明写入order_dename,order_decode）\r\n23开始退货，等待收货\r\n24退货收货',
  `order_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '订单创建时间',
  `order_time_update` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '订单最后一次更新时间',
  `order_ dename` varchar(20) DEFAULT NULL COMMENT '发货-快递或者物流公司名称',
  `order_decode` varchar(50) DEFAULT NULL COMMENT '发货-快递单号',
  `order_rdewhy` varchar(20) DEFAULT NULL COMMENT '退货-退货原因',
  `order_rdeexp` tinytext COMMENT '退货-退货原因详情',
  `order_ rdename` varchar(20) DEFAULT NULL COMMENT '退货-快递或者物流公司名称',
  `order_rdecode` varchar(50) DEFAULT NULL COMMENT '退货-快递单号'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='增强商品-订单列表';

-- ----------------------------
-- Records of big_order
-- ----------------------------

-- ----------------------------
-- Table structure for `big_order_event`
-- ----------------------------
DROP TABLE IF EXISTS `big_order_event`;
CREATE TABLE `big_order_event` (
  `log_time` int(11) unsigned DEFAULT NULL COMMENT '发生事件时间',
  `log_event` varchar(200) DEFAULT NULL COMMENT '发生事件说明',
  `order_status` tinyint(2) unsigned DEFAULT '0' COMMENT '发生后订单状态'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单状态变更记录表';

-- ----------------------------
-- Records of big_order_event
-- ----------------------------

-- ----------------------------
-- Table structure for `big_order_pro`
-- ----------------------------
DROP TABLE IF EXISTS `big_order_pro`;
CREATE TABLE `big_order_pro` (
  `order_no` varchar(32) DEFAULT NULL,
  `pro_no` varchar(50) DEFAULT NULL COMMENT '商品编号',
  `pro_money` decimal(12,2) unsigned DEFAULT '0.00' COMMENT '商品售价',
  `pro_count` int(11) unsigned DEFAULT '1' COMMENT '商品数量',
  `pro_name` varchar(50) DEFAULT NULL COMMENT '商品名称',
  `pro_cover` varchar(100) DEFAULT NULL COMMENT '商品图片地址'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单商品详情列表';

-- ----------------------------
-- Records of big_order_pro
-- ----------------------------
