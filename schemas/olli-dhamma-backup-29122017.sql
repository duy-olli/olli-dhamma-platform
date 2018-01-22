# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.21)
# Database: olli-dhamma
# Generation Time: 2018-01-22 03:36:53 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table fly_dhamma
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fly_dhamma`;

CREATE TABLE `fly_dhamma` (
  `t_id` int(11) DEFAULT '0',
  `d_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `d_title` varchar(255) DEFAULT '',
  `d_author` varchar(150) DEFAULT '',
  `d_path` varchar(255) DEFAULT '',
  `d_seo_keyword` text,
  `d_seo_description` varchar(100) DEFAULT '',
  `d_date_created` int(10) DEFAULT '0',
  `d_date_modified` int(10) DEFAULT '0',
  PRIMARY KEY (`d_id`),
  KEY `t_id` (`t_id`),
  KEY `d_id` (`d_id`),
  KEY `d_title` (`d_title`),
  KEY `d_author` (`d_author`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table fly_topic
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fly_topic`;

CREATE TABLE `fly_topic` (
  `t_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `t_name` varchar(150) DEFAULT '',
  `t_description` varchar(255) DEFAULT '',
  `t_date_created` int(10) DEFAULT '0',
  `t_date_modified` int(10) DEFAULT '0',
  PRIMARY KEY (`t_id`),
  KEY `t_id` (`t_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table fly_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fly_user`;

CREATE TABLE `fly_user` (
  `u_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `u_screen_name` varchar(32) DEFAULT '',
  `u_full_name` varchar(100) DEFAULT '',
  `u_email` varchar(100) DEFAULT '',
  `u_address` varchar(255) DEFAULT '',
  `u_password` varchar(100) NOT NULL,
  `u_groupid` varchar(100) DEFAULT '0',
  `u_avatar` varchar(255) DEFAULT '',
  `u_gender` varchar(5) DEFAULT '0',
  `u_status` int(2) DEFAULT '0',
  `u_oauth_provider` varchar(20) DEFAULT '',
  `u_oauth_uid` varchar(50) DEFAULT '',
  `u_oauth_access_token` varchar(255) DEFAULT '',
  `u_onesignal_id` varchar(255) DEFAULT '',
  `u_state` int(2) DEFAULT '0',
  `u_dob` int(10) DEFAULT '0',
  `u_date_created` int(10) DEFAULT '0',
  `u_date_last_change_password` int(10) DEFAULT '0',
  `u_date_modified` int(10) DEFAULT '0',
  `u_mobile_number` varchar(20) DEFAULT '0',
  `u_is_verified` tinyint(1) DEFAULT '0',
  `u_verify_type` tinyint(1) DEFAULT '0',
  `u_total_coin` int(11) DEFAULT '0',
  PRIMARY KEY (`u_id`),
  KEY `u_email` (`u_email`),
  KEY `u_password` (`u_password`),
  KEY `u_status` (`u_status`),
  KEY `u_group` (`u_groupid`),
  KEY `u_mobile_number` (`u_mobile_number`),
  KEY `u_is_verified` (`u_is_verified`),
  KEY `u_gender` (`u_gender`),
  KEY `u_full_name` (`u_full_name`),
  KEY `u_id` (`u_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `fly_user` WRITE;
/*!40000 ALTER TABLE `fly_user` DISABLE KEYS */;

INSERT INTO `fly_user` (`u_id`, `u_screen_name`, `u_full_name`, `u_email`, `u_address`, `u_password`, `u_groupid`, `u_avatar`, `u_gender`, `u_status`, `u_oauth_provider`, `u_oauth_uid`, `u_oauth_access_token`, `u_onesignal_id`, `u_state`, `u_dob`, `u_date_created`, `u_date_last_change_password`, `u_date_modified`, `u_mobile_number`, `u_is_verified`, `u_verify_type`, `u_total_coin`)
VALUES
	(1,'Administrator','Administrator','admin@localhost.local','','$2y$10$b2lXYVA1K00xbUFJS0hIRO4KlKk9RwQChnMVQ/w8HGv6.NuiIh8Xy','administrator','','male',1,'','','','',0,NULL,1494560696,0,1516591359,'',1,1,23);

/*!40000 ALTER TABLE `fly_user` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
