-- Adminer 4.3.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `xxjz_account`;
CREATE TABLE `xxjz_account` (
  `acid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `acmoney` double(9,2) unsigned NOT NULL,
  `acclassid` int(8) NOT NULL,
  `actime` int(11) NOT NULL,
  `acremark` varchar(50) NOT NULL,
  `jiid` int(8) NOT NULL,
  `zhifu` int(8) NOT NULL,
  PRIMARY KEY (`acid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `xxjz_account_class`;
CREATE TABLE `xxjz_account_class` (
  `classid` int(5) NOT NULL AUTO_INCREMENT,
  `classname` varchar(20) NOT NULL,
  `classtype` int(1) NOT NULL,
  `ufid` int(11) NOT NULL,
  PRIMARY KEY (`classid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `xxjz_user`;
CREATE TABLE `xxjz_user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(24) NOT NULL,
  `password` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `utime` int(11) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `xxjz_user` (`uid`, `username`, `password`, `email`, `utime`) VALUES
(1,	'admin',	'7fef6171469e80d32c0559f88b377245',	'xxjz@xxgzs.org',	1545994150);

DROP TABLE IF EXISTS `xxjz_user_login`;
CREATE TABLE `xxjz_user_login` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `login_name` varchar(32) NOT NULL,
  `login_id` varchar(32) NOT NULL,
  `login_key` varchar(32) NOT NULL,
  `login_token` varchar(32) NOT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- 2018-12-28 10:50:50
