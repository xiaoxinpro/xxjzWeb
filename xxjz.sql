-- Adminer 4.6.3 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `xxjz_account`;
CREATE TABLE `xxjz_account` (
  `acid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `acmoney` double(9,2) unsigned NOT NULL,
  `acclassid` int(11) NOT NULL,
  `actime` int(11) NOT NULL,
  `acremark` varchar(255) NOT NULL,
  `jiid` int(11) NOT NULL,
  `zhifu` int(11) NOT NULL,
  `fid` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`acid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `xxjz_account_class`;
CREATE TABLE `xxjz_account_class` (
  `classid` int(11) NOT NULL AUTO_INCREMENT,
  `classname` varchar(255) NOT NULL,
  `classtype` int(1) NOT NULL,
  `ufid` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '255',
  PRIMARY KEY (`classid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `xxjz_account_funds`;
CREATE TABLE `xxjz_account_funds` (
  `fundsid` int(11) NOT NULL AUTO_INCREMENT,
  `fundsname` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '255',
  PRIMARY KEY (`fundsid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `xxjz_account_image`;
CREATE TABLE `xxjz_account_image` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `acid` int(11) unsigned DEFAULT NULL,
  `name` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  `size` int(11) unsigned NOT NULL,
  `ext` varchar(8) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `savepath` varchar(32) NOT NULL,
  `savename` varchar(32) NOT NULL,
  `time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `xxjz_account_transfer`;
CREATE TABLE `xxjz_account_transfer` (
  `tid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `money` double(9,2) unsigned NOT NULL,
  `source_fid` int(11) unsigned NOT NULL,
  `target_fid` int(11) unsigned NOT NULL,
  `time` int(11) unsigned NOT NULL,
  `mark` varchar(255) NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `xxjz_user`;
CREATE TABLE `xxjz_user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `utime` int(11) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `xxjz_user` (`uid`, `username`, `password`, `email`, `utime`) VALUES
(1, 'admin',  '7fef6171469e80d32c0559f88b377245', 'xxjz@xxgzs.org', 1564901873);

DROP TABLE IF EXISTS `xxjz_user_config`;
CREATE TABLE `xxjz_user_config` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `config_name` varchar(32) NOT NULL,
  `config_key` varchar(32) NOT NULL,
  `config_value` varchar(32) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `xxjz_user_login`;
CREATE TABLE `xxjz_user_login` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `login_name` varchar(32) NOT NULL,
  `login_id` varchar(32) NOT NULL,
  `login_key` varchar(32) NOT NULL,
  `login_token` varchar(32) NOT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `xxjz_user_push`;
CREATE TABLE `xxjz_user_push` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `push_name` varchar(32) NOT NULL DEFAULT 'Weixin',
  `push_id` varchar(64) NOT NULL,
  `push_mark` varchar(32) DEFAULT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


-- 2020-06-30 13:13:36