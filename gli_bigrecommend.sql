/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : gli_bigrecommend

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2017-08-10 14:51:18
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `big_app`
-- ----------------------------
DROP TABLE IF EXISTS `big_app`;
CREATE TABLE `big_app` (
  `app_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用自增序号',
  `app_exp` varchar(200) DEFAULT NULL COMMENT '应用简介',
  `app_password` varchar(50) DEFAULT NULL COMMENT '应用管理密钥',
  `app_key` varchar(50) DEFAULT NULL COMMENT '应用提交密钥',
  `app_reco_data` int(3) unsigned DEFAULT NULL COMMENT '本应用单统计周期的天数',
  `app_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '应用创建时间',
  PRIMARY KEY (`app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用记录表';

-- ----------------------------
-- Records of big_app
-- ----------------------------

-- ----------------------------
-- Table structure for `big_app_cache`
-- ----------------------------
DROP TABLE IF EXISTS `big_app_cache`;
CREATE TABLE `big_app_cache` (
  `app_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用自增序号',
  `app_exp` varchar(200) DEFAULT NULL COMMENT '应用简介',
  `app_password` varchar(50) DEFAULT NULL COMMENT '应用管理密钥',
  `app_key` varchar(50) DEFAULT NULL COMMENT '应用提交密钥',
  `app_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '应用创建时间',
  `app_reco_data` int(3) unsigned DEFAULT NULL COMMENT '本应用单统计周期的天数',
  PRIMARY KEY (`app_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='应用记录表的缓存表，任何修改或者移除应用的时候需要同步跟新该表，当该表为空，则直接从big_app复制数据到该表';

-- ----------------------------
-- Records of big_app_cache
-- ----------------------------

-- ----------------------------
-- Table structure for `big_declaration`
-- ----------------------------
DROP TABLE IF EXISTS `big_declaration`;
CREATE TABLE `big_declaration` (
  `app_id` int(10) unsigned DEFAULT NULL COMMENT '应用序号',
  `user_id` int(11) unsigned DEFAULT NULL COMMENT '推荐的用户序号',
  `val_no` varchar(32) DEFAULT NULL COMMENT '推荐的资料序号',
  `user_ip` int(10) DEFAULT NULL COMMENT '用户当前的IP地址，通过IPLONG方法转换为数字',
  `user_brower` varchar(20) DEFAULT NULL COMMENT '用户的浏览器',
  `user_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '资料创建时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户访问申报记录表';

-- ----------------------------
-- Records of big_declaration
-- ----------------------------

-- ----------------------------
-- Table structure for `big_reocmmend`
-- ----------------------------
DROP TABLE IF EXISTS `big_reocmmend`;
CREATE TABLE `big_reocmmend` (
  `app_id` int(10) unsigned DEFAULT NULL COMMENT '应用序号',
  `user_id` int(11) unsigned DEFAULT NULL COMMENT '推荐的用户序号',
  `val_no` varchar(32) DEFAULT NULL COMMENT '推荐的资料序号',
  `user_ip` int(10) DEFAULT NULL COMMENT '用户当前的IP地址，通过IPLONG方法转换为数字',
  `user_brower` varchar(20) DEFAULT NULL COMMENT '用户的浏览器',
  `user_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '资料创建时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='推荐记录表';

-- ----------------------------
-- Records of big_reocmmend
-- ----------------------------

-- ----------------------------
-- Table structure for `big_report`
-- ----------------------------
DROP TABLE IF EXISTS `big_report`;
CREATE TABLE `big_report` (
  `app_id` int(10) unsigned DEFAULT NULL COMMENT '应用序号',
  `stat_data` date DEFAULT NULL COMMENT '本次统计周期的最后一天',
  `totalUser` int(11) DEFAULT NULL,
  `totlaVal` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='每一个统计周期的记录';

-- ----------------------------
-- Records of big_report
-- ----------------------------

-- ----------------------------
-- Table structure for `big_report_declaration`
-- ----------------------------
DROP TABLE IF EXISTS `big_report_declaration`;
CREATE TABLE `big_report_declaration` (
  `report_id` int(11) unsigned DEFAULT NULL COMMENT '报表序号',
  `app_id` int(10) unsigned DEFAULT NULL COMMENT '应用序号',
  `user_id` int(11) unsigned DEFAULT NULL COMMENT '推荐的用户序号',
  `val_no` varchar(32) DEFAULT NULL COMMENT '推荐的资料序号',
  `user_ip` int(10) DEFAULT NULL COMMENT '用户当前的IP地址，通过IPLONG方法转换为数字',
  `user_brower` varchar(20) DEFAULT NULL COMMENT '用户的浏览器',
  `user_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '资料创建时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='报表中的-推荐记录表';

-- ----------------------------
-- Records of big_report_declaration
-- ----------------------------

-- ----------------------------
-- Table structure for `big_report_reocmmend`
-- ----------------------------
DROP TABLE IF EXISTS `big_report_reocmmend`;
CREATE TABLE `big_report_reocmmend` (
  `report_id` int(11) unsigned DEFAULT NULL COMMENT '报表序号',
  `app_id` int(10) unsigned DEFAULT NULL COMMENT '应用序号',
  `user_id` int(11) unsigned DEFAULT NULL COMMENT '推荐的用户序号',
  `val_no` varchar(32) DEFAULT NULL COMMENT '推荐的资料序号',
  `user_ip` int(10) DEFAULT NULL COMMENT '用户当前的IP地址，通过IPLONG方法转换为数字',
  `user_brower` varchar(20) DEFAULT NULL COMMENT '用户的浏览器',
  `user_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '资料创建时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='报表统计中的-推荐记录表';

-- ----------------------------
-- Records of big_report_reocmmend
-- ----------------------------

-- ----------------------------
-- Table structure for `big_user`
-- ----------------------------
DROP TABLE IF EXISTS `big_user`;
CREATE TABLE `big_user` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户列表自增序号',
  `app_id` int(10) unsigned DEFAULT NULL COMMENT '应用序号',
  `user_no` varchar(32) DEFAULT NULL COMMENT '用户序号',
  `user_type` enum('apply','auto') DEFAULT 'auto' COMMENT '用户类型，apply为申请添加的固定用户,auto为游客',
  `user_sex` enum('男','女','保密') DEFAULT '保密' COMMENT '用户性别',
  `user_age` int(3) unsigned DEFAULT '0' COMMENT '用户年龄',
  `user_phone` varchar(20) DEFAULT NULL COMMENT '用户手机',
  `user_time_create` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '资料添加时间',
  PRIMARY KEY (`user_id`),
  KEY `user_no` (`app_id`,`user_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用用户记录表';

-- ----------------------------
-- Records of big_user
-- ----------------------------

-- ----------------------------
-- Table structure for `big_value`
-- ----------------------------
DROP TABLE IF EXISTS `big_value`;
CREATE TABLE `big_value` (
  `app_id` int(10) unsigned DEFAULT NULL COMMENT '应用序号',
  `val_no` varchar(32) DEFAULT NULL COMMENT '资料编号',
  `val_name` varchar(50) DEFAULT NULL COMMENT '资料名称',
  `val_tags` tinytext COMMENT '资料标签,英文逗号分割多个',
  `val_show` enum('true','false') DEFAULT 'true' COMMENT '资料是否参与统计',
  KEY `val_no` (`app_id`,`val_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of big_value
-- ----------------------------
