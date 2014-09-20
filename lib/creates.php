<?php
$create = array();
$create[] =
'
CREATE TABLE IF NOT EXISTS `'.PREFIX.'autokennzeichen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(5) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
';

$create[] =
'
CREATE TABLE IF NOT EXISTS `'.PREFIX.'betrieb` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `betrieb_status_id` int(10) unsigned NOT NULL,
  `plz_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `kette_id` int(10) unsigned NOT NULL,
  `betrieb_kategorie_id` int(10) unsigned NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `str` varchar(120) DEFAULT NULL,
  `hsnr` varchar(20) DEFAULT NULL,
  `status_date` date DEFAULT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `ansprechpartner` varchar(60) DEFAULT NULL,
  `telefon` varchar(50) DEFAULT NULL,
  `fax` varchar(50) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `betrieb_FKIndex1` (`betrieb_kategorie_id`),
  KEY `betrieb_FKIndex2` (`kette_id`),
  KEY `betrieb_FKIndex3` (`bezirk_id`),
  KEY `betrieb_FKIndex4` (`plz_id`),
  KEY `betrieb_FKIndex5` (`betrieb_status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
';

$create[] =
'
DROP TABLE IF EXISTS `'.PREFIX.'betrieb_status`;
CREATE TABLE IF NOT EXISTS `'.PREFIX.'betrieb_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
';

$create[] =
'
DROP TABLE IF EXISTS `'.PREFIX.'bezirk`;
CREATE TABLE IF NOT EXISTS `'.PREFIX.'bezirk` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `region_id` int(10) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bezirk_FKIndex1` (`region_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
';

$create[] =
'
DROP TABLE IF EXISTS `'.PREFIX.'botschafter`;
CREATE TABLE IF NOT EXISTS `'.PREFIX.'botschafter` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`foodsaver_id`,`bezirk_id`),
  KEY `foodsaver_has_bezirk_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_bezirk_FKIndex2` (`bezirk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
';

$create[] =
'
DROP TABLE IF EXISTS `'.PREFIX.'bundesland`;
CREATE TABLE IF NOT EXISTS `'.PREFIX.'bundesland` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
';

$create[] =
'
DROP TABLE IF EXISTS `'.PREFIX.'foodsaver`;
CREATE TABLE IF NOT EXISTS `'.PREFIX.'foodsaver` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `autokennzeichen_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `photo` varchar(10) NOT NULL,
  `plz_id` int(10) unsigned NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `passwd` varchar(32) DEFAULT NULL,
  `name` varchar(120) DEFAULT NULL,
  `admin` tinyint(3) unsigned DEFAULT NULL,
  `nachname` varchar(120) DEFAULT NULL,
  `anschrift` varchar(120) DEFAULT NULL,
  `telefon` varchar(30) DEFAULT NULL,
  `handy` varchar(50) DEFAULT NULL,
  `geschlecht` tinyint(3) unsigned DEFAULT NULL,
  `geb_datum` date DEFAULT NULL,
  `'.PREFIX.'id` varchar(10) DEFAULT NULL,
  `anmeldedatum` datetime DEFAULT NULL,
  `orgateam` tinyint(3) unsigned DEFAULT \'0\',
  `active` tinyint(3) unsigned NOT NULL DEFAULT \'0\',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foodsaver_FKIndex1` (`plz_id`),
  KEY `foodsaver_FKIndex2` (`bezirk_id`),
  KEY `foodsaver_FKIndex4` (`autokennzeichen_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
';

$create[] =
'
CREATE TABLE IF NOT EXISTS `'.PREFIX.'kette` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
';

$create[] =
'
DROP TABLE IF EXISTS `'.PREFIX.'land`;
CREATE TABLE IF NOT EXISTS `'.PREFIX.'land` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(90) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
';

$create[] =
'
CREATE TABLE IF NOT EXISTS `'.PREFIX.'plz` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stadt_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `name` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plz_FKIndex1` (`bezirk_id`),
  KEY `plz_FKIndex2` (`stadt_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
';

$create[] =
'
CREATE TABLE IF NOT EXISTS `'.PREFIX.'region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
';

$create[] =
'
DROP TABLE IF EXISTS `'.PREFIX.'send_email`;
CREATE TABLE IF NOT EXISTS `'.PREFIX.'send_email` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `message` text,
  `zeit` datetime DEFAULT NULL,
  `recip` text,
  PRIMARY KEY (`id`),
  KEY `send_email_FKIndex1` (`foodsaver_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
';

$create[] =
'
DROP TABLE IF EXISTS `'.PREFIX.'stadtteil`;
CREATE TABLE IF NOT EXISTS `'.PREFIX.'stadtteil` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stadt_id` int(10) unsigned NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stadtteil_FKIndex1` (`stadt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
';

$create[] =
'
DROP TABLE IF EXISTS `'.PREFIX.'verantwortlicher`;
CREATE TABLE IF NOT EXISTS `'.PREFIX.'verantwortlicher` (
		`foodsaver_id` int(10) unsigned NOT NULL,
		`betrieb_id` int(10) unsigned NOT NULL,
		PRIMARY KEY (`foodsaver_id`,`betrieb_id`),
		KEY `foodsaver_has_betrieb_FKIndex1` (`foodsaver_id`),
		KEY `foodsaver_has_betrieb_FKIndex2` (`betrieb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
';

