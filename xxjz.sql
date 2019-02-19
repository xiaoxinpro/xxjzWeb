-- Adminer 4.3.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `xxjz_account`;
CREATE TABLE `xxjz_account` (
  `acid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `acmoney` double(9,2) unsigned NOT NULL,
  `acclassid` int(11) NOT NULL,
  `actime` int(11) NOT NULL,
  `acremark` varchar(64) NOT NULL,
  `jiid` int(11) NOT NULL,
  `zhifu` int(11) NOT NULL,
  `fid` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`acid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `xxjz_account_class`;
CREATE TABLE `xxjz_account_class` (
  `classid` int(11) NOT NULL AUTO_INCREMENT,
  `classname` varchar(24) NOT NULL,
  `classtype` int(1) NOT NULL,
  `ufid` int(11) NOT NULL,
  PRIMARY KEY (`classid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `xxjz_account_funds`;
CREATE TABLE `xxjz_account_funds` (
  `fundsid` int(11) NOT NULL AUTO_INCREMENT,
  `fundsname` varchar(24) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`fundsid`)
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
(1,	'admin',	'7fef6171469e80d32c0559f88b377245',	'xxjz@xxgzs.org',	1550554411);

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


-- 2019-02-19 05:35:32
