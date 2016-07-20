-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 11. Okt 2015 um 18:21
-- Server Version: 5.5.44
-- PHP-Version: 5.4.45-0+deb7u1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Datenbank: `lmr_prod`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `basket_has_art`
--

CREATE TABLE IF NOT EXISTS `basket_has_art` (
  `basket_id` int(10) unsigned NOT NULL,
  `art_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`basket_id`,`art_id`),
  KEY `basket_has_art_FKIndex1` (`basket_id`),
  KEY `basket_has_art_FKIndex2` (`art_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `basket_has_types`
--

CREATE TABLE IF NOT EXISTS `basket_has_types` (
  `basket_id` int(10) unsigned NOT NULL,
  `types_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`basket_id`,`types_id`),
  KEY `basket_has_types_FKIndex1` (`basket_id`),
  KEY `basket_has_types_FKIndex2` (`types_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `flat_bezirk_all`
--
CREATE TABLE IF NOT EXISTS `flat_bezirk_all` (
`idlvl1` int(11) unsigned
,`lvl1` varchar(50)
,`idlvl2` int(11) unsigned
,`lvl2` varchar(50)
,`idlvl3` int(11) unsigned
,`lvl3` varchar(50)
,`idlvl4` int(10) unsigned
,`lvl4` varchar(50)
,`id` int(11) unsigned
,`name` varchar(50)
);
-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_abholen`
--

CREATE TABLE IF NOT EXISTS `fs_abholen` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `betrieb_id` int(10) unsigned NOT NULL,
  `dow` tinyint(3) unsigned NOT NULL,
  `time` time DEFAULT NULL,
  PRIMARY KEY (`foodsaver_id`,`betrieb_id`,`dow`),
  KEY `foodsaver_has_betrieb_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_betrieb_FKIndex2` (`betrieb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_abholer`
--

CREATE TABLE IF NOT EXISTS `fs_abholer` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `betrieb_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `confirmed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`foodsaver_id`,`betrieb_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_abholmengen`
--

CREATE TABLE IF NOT EXISTS `fs_abholmengen` (
  `id` int(11) NOT NULL,
  `menge` decimal(5,1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_abholzeiten`
--

CREATE TABLE IF NOT EXISTS `fs_abholzeiten` (
  `betrieb_id` int(10) unsigned NOT NULL,
  `dow` tinyint(3) unsigned NOT NULL,
  `time` time NOT NULL DEFAULT '00:00:00',
  `fetcher` tinyint(3) unsigned NOT NULL DEFAULT '4',
  PRIMARY KEY (`betrieb_id`,`dow`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_activity`
--

CREATE TABLE IF NOT EXISTS `fs_activity` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `zeit` datetime DEFAULT NULL,
  PRIMARY KEY (`foodsaver_id`),
  KEY `zeit` (`zeit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_answer`
--

CREATE TABLE IF NOT EXISTS `fs_answer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned NOT NULL,
  `text` text,
  `explanation` text NOT NULL,
  `right` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `answer_FKIndex1` (`question_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=464 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_application_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_application_has_wallpost` (
  `application_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`application_id`,`wallpost_id`),
  KEY `application_id` (`application_id`),
  KEY `wallpost_id` (`wallpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_autokennzeichen`
--

CREATE TABLE IF NOT EXISTS `fs_autokennzeichen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `land_id` int(10) unsigned NOT NULL,
  `name` varchar(5) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `land_id` (`land_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=581 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_basket`
--

CREATE TABLE IF NOT EXISTS `fs_basket` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `status` tinyint(2) unsigned DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `until` date NOT NULL,
  `fetchtime` datetime DEFAULT NULL,
  `description` text,
  `picture` varchar(150) DEFAULT NULL,
  `tel` varchar(50) NOT NULL DEFAULT '',
  `handy` varchar(50) NOT NULL DEFAULT '',
  `contact_type` varchar(20) NOT NULL DEFAULT '1',
  `location_type` tinyint(2) unsigned DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `lat` float(10,6) NOT NULL DEFAULT '0.000000',
  `lon` float(10,6) NOT NULL DEFAULT '0.000000',
  `bezirk_id` int(10) unsigned NOT NULL,
  `fs_id` int(10) NOT NULL DEFAULT '0',
  `appost` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `basket_FKIndex1` (`foodsaver_id`),
  KEY `bezirk_id` (`bezirk_id`),
  KEY `lat` (`lat`,`lon`),
  KEY `fs_id` (`fs_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22673 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_basket_anfrage`
--

CREATE TABLE IF NOT EXISTS `fs_basket_anfrage` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `basket_id` int(10) unsigned NOT NULL,
  `status` tinyint(2) unsigned DEFAULT NULL,
  `time` datetime NOT NULL,
  `appost` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`foodsaver_id`,`basket_id`),
  KEY `foodsaver_has_basket_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_basket_FKIndex2` (`basket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_basket_has_art`
--

CREATE TABLE IF NOT EXISTS `fs_basket_has_art` (
  `basket_id` int(10) unsigned NOT NULL,
  `art_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`basket_id`,`art_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_basket_has_types`
--

CREATE TABLE IF NOT EXISTS `fs_basket_has_types` (
  `basket_id` int(10) unsigned NOT NULL,
  `types_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`basket_id`,`types_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_basket_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_basket_has_wallpost` (
  `basket_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`basket_id`,`wallpost_id`),
  KEY `basket_has_wallpost_FKIndex1` (`basket_id`),
  KEY `basket_has_wallpost_FKIndex2` (`wallpost_id`),
  KEY `basket_id` (`basket_id`),
  KEY `wallpost_id` (`wallpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_bell`
--

CREATE TABLE IF NOT EXISTS `fs_bell` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `body` varchar(50) DEFAULT NULL,
  `vars` text,
  `attr` varchar(500) DEFAULT NULL,
  `icon` varchar(150) DEFAULT NULL,
  `identifier` varchar(40) DEFAULT NULL,
  `time` datetime NOT NULL,
  `closeable` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=103594 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_betrieb`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `betrieb_status_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `added` date NOT NULL,
  `plz` varchar(5) NOT NULL,
  `stadt` varchar(50) NOT NULL,
  `lat` varchar(20) NOT NULL,
  `lon` varchar(20) NOT NULL,
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
  `begin` date NOT NULL,
  `besonderheiten` text NOT NULL,
  `public_info` varchar(200) NOT NULL,
  `public_time` tinyint(2) NOT NULL,
  `ueberzeugungsarbeit` tinyint(4) NOT NULL,
  `presse` tinyint(4) NOT NULL,
  `sticker` tinyint(4) NOT NULL,
  `abholmenge` tinyint(4) NOT NULL,
  `team_status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '0 = Team Voll; 1 = Es werden noch Helfer gesucht; 2 = Es werden dringend Helfer gesucht',
  `prefetchtime` int(10) unsigned NOT NULL DEFAULT '1209600',
  `team_conversation_id` int(10) unsigned DEFAULT NULL,
  `springer_conversation_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `betrieb_FKIndex2` (`kette_id`),
  KEY `betrieb_FKIndex3` (`bezirk_id`),
  KEY `betrieb_FKIndex5` (`betrieb_status_id`),
  KEY `plz` (`plz`),
  KEY `team_status` (`team_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13523 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_betrieb_has_lebensmittel`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_has_lebensmittel` (
  `betrieb_id` int(10) unsigned NOT NULL,
  `lebensmittel_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`betrieb_id`,`lebensmittel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_betrieb_kategorie`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_kategorie` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=202 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_betrieb_notiz`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_notiz` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `betrieb_id` int(10) unsigned NOT NULL,
  `milestone` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `text` text,
  `zeit` datetime DEFAULT NULL,
  `last` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `betrieb_notitz_FKIndex1` (`betrieb_id`),
  KEY `betrieb_notiz_FKIndex2` (`foodsaver_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=243336 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_betrieb_status`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_betrieb_team`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_team` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `betrieb_id` int(10) unsigned NOT NULL,
  `verantwortlich` tinyint(3) unsigned DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '0',
  `stat_last_update` datetime NOT NULL,
  `stat_fetchcount` int(10) unsigned NOT NULL,
  `stat_first_fetch` date NOT NULL,
  `stat_last_fetch` datetime NOT NULL,
  `stat_add_date` date NOT NULL,
  PRIMARY KEY (`foodsaver_id`,`betrieb_id`),
  KEY `foodsaver_has_betrieb_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_betrieb_FKIndex2` (`betrieb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_bezirk`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned DEFAULT '0',
  `has_children` tinyint(4) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `teaser` text NOT NULL,
  `desc` text NOT NULL,
  `photo` varchar(200) NOT NULL,
  `master` int(10) unsigned NOT NULL DEFAULT '0',
  `mailbox_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  `email_pass` varchar(50) NOT NULL,
  `email_name` varchar(100) NOT NULL,
  `apply_type` tinyint(2) NOT NULL DEFAULT '2',
  `banana_count` tinyint(3) NOT NULL DEFAULT '0',
  `fetch_count` tinyint(3) NOT NULL DEFAULT '0',
  `week_num` tinyint(3) NOT NULL DEFAULT '0',
  `report_num` tinyint(3) NOT NULL,
  `stat_last_update` datetime NOT NULL,
  `stat_fetchweight` decimal(10,2) unsigned NOT NULL,
  `stat_fetchcount` int(10) unsigned NOT NULL,
  `stat_postcount` int(10) unsigned NOT NULL,
  `stat_betriebcount` int(7) unsigned NOT NULL,
  `stat_korpcount` int(7) unsigned NOT NULL,
  `stat_botcount` int(7) unsigned NOT NULL,
  `stat_fscount` int(7) unsigned NOT NULL,
  `stat_fairteilercount` int(7) unsigned NOT NULL,
  `conversation_id` int(10) unsigned NOT NULL DEFAULT '0',
  `moderated` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `type` (`type`),
  KEY `mailbox_id` (`mailbox_id`),
  KEY `master` (`master`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=964 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_bezirk_closure`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk_closure` (
  `bezirk_id` int(10) unsigned NOT NULL,
  `ancestor_id` int(10) unsigned NOT NULL,
  `depth` int(10) unsigned NOT NULL,
  KEY `ancestor_id` (`ancestor_id`),
  KEY `bezirk_id` (`bezirk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_bezirk_has_theme`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk_has_theme` (
  `theme_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `bot_theme` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`theme_id`,`bezirk_id`),
  KEY `bezirk_id` (`bezirk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_bezirk_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk_has_wallpost` (
  `bezirk_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`bezirk_id`,`wallpost_id`),
  KEY `bezirk_id` (`bezirk_id`),
  KEY `wallpost_id` (`wallpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_blog_entry`
--

CREATE TABLE IF NOT EXISTS `fs_blog_entry` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bezirk_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `active` tinyint(3) unsigned NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `teaser` varchar(500) DEFAULT NULL,
  `body` text,
  `time` datetime DEFAULT NULL,
  `picture` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_entry_FKIndex1` (`foodsaver_id`),
  KEY `blog_entry_FKIndex2` (`bezirk_id`),
  KEY `active` (`active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=207 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_botschafter`
--

CREATE TABLE IF NOT EXISTS `fs_botschafter` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`foodsaver_id`,`bezirk_id`),
  KEY `foodsaver_has_bezirk_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_bezirk_FKIndex2` (`bezirk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_buddy`
--

CREATE TABLE IF NOT EXISTS `fs_buddy` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `buddy_id` int(10) unsigned NOT NULL,
  `confirmed` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`foodsaver_id`,`buddy_id`),
  KEY `buddy_confirmed` (`confirmed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_bundesland`
--

CREATE TABLE IF NOT EXISTS `fs_bundesland` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `land_id` int(10) unsigned NOT NULL,
  `name` varchar(160) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `land_id` (`land_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_cache`
--

CREATE TABLE IF NOT EXISTS `fs_cache` (
  `id` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_contact`
--

CREATE TABLE IF NOT EXISTS `fs_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(155) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15030 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_content`
--

CREATE TABLE IF NOT EXISTS `fs_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  `title` varchar(120) DEFAULT NULL,
  `body` text,
  `last_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_conversation`
--

CREATE TABLE IF NOT EXISTS `fs_conversation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL,
  `start` datetime DEFAULT NULL,
  `last` datetime DEFAULT NULL,
  `last_foodsaver_id` int(10) unsigned DEFAULT NULL,
  `start_foodsaver_id` int(10) unsigned NOT NULL,
  `last_message_id` int(10) unsigned NOT NULL,
  `last_message` text NOT NULL,
  `member` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `conversation_last_fs_id` (`last_foodsaver_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=363348 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_document`
--

CREATE TABLE IF NOT EXISTS `fs_document` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `file` varchar(300) DEFAULT NULL,
  `body` text NOT NULL,
  `rolle` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_email_blacklist`
--

CREATE TABLE IF NOT EXISTS `fs_email_blacklist` (
  `email` varchar(255) NOT NULL,
  `since` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_email_status`
--

CREATE TABLE IF NOT EXISTS `fs_email_status` (
  `email_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `status` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`email_id`,`foodsaver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_event`
--

CREATE TABLE IF NOT EXISTS `fs_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(200) DEFAULT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `description` text,
  `bot` tinyint(2) unsigned DEFAULT '0',
  `online` tinyint(2) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `event_FKIndex1` (`location_id`),
  KEY `event_FKIndex2` (`bezirk_id`),
  KEY `event_FKIndex3` (`foodsaver_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2406 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_event_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_event_has_wallpost` (
  `event_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`event_id`,`wallpost_id`),
  KEY `event_id` (`event_id`),
  KEY `wallpost_id` (`wallpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_event_registration`
--

CREATE TABLE IF NOT EXISTS `fs_event_registration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `geb_datum` date NOT NULL,
  `ort` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `take_part` set('0','1','2','3') NOT NULL,
  `sleep_at` set('0','1','2') NOT NULL,
  `sleep_slots` int(11) NOT NULL,
  `sleep_need` tinyint(4) NOT NULL,
  `languages` set('0','1','2','3') NOT NULL,
  `nutrition` tinyint(4) NOT NULL,
  `special_nutrition` text NOT NULL,
  `other_languages` text NOT NULL,
  `translation_necessary` tinyint(4) NOT NULL,
  `already_foodsaver` tinyint(4) NOT NULL,
  `childcare` text NOT NULL,
  `comments` text NOT NULL,
  `foodsaver_id` int(11) DEFAULT NULL,
  `ip` varchar(60) NOT NULL,
  `signup_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `signup_lang` varchar(20) NOT NULL,
  `emailvalid` tinyint(4) NOT NULL,
  `languages_translate` set('0','1','2','3') NOT NULL,
  `other_languages_translate` text NOT NULL,
  `on_place` tinyint(4) NOT NULL,
  `admin_comment` text,
  `available_thursday` set('0') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=855 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_event_workshops`
--

CREATE TABLE IF NOT EXISTS `fs_event_workshops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `duration` int(11) NOT NULL,
  `allowed_attendants` int(11) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=47 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_event_workshop_registration`
--

CREATE TABLE IF NOT EXISTS `fs_event_workshop_registration` (
  `wid` int(11) NOT NULL DEFAULT '-1',
  `uid` int(11) NOT NULL,
  `wish` smallint(6) NOT NULL,
  `confirmed` tinyint(4) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`,`wish`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2921 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_fairteiler`
--

CREATE TABLE IF NOT EXISTS `fs_fairteiler` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bezirk_id` int(10) unsigned NOT NULL,
  `name` varchar(260) DEFAULT NULL,
  `picture` varchar(100) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `desc` text,
  `anschrift` varchar(260) DEFAULT NULL,
  `plz` varchar(5) DEFAULT NULL,
  `ort` varchar(100) DEFAULT NULL,
  `lat` varchar(100) DEFAULT NULL,
  `lon` varchar(100) DEFAULT NULL,
  `add_date` date DEFAULT NULL,
  `add_foodsaver` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fairteiler_FKIndex1` (`bezirk_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=517 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_fairteiler_follower`
--

CREATE TABLE IF NOT EXISTS `fs_fairteiler_follower` (
  `fairteiler_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `infotype` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`fairteiler_id`,`foodsaver_id`),
  KEY `fairteiler_verantwortlich_FKIndex1` (`fairteiler_id`),
  KEY `fairteiler_verantwortlich_FKIndex2` (`foodsaver_id`),
  KEY `type` (`type`),
  KEY `infotype` (`infotype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_fairteiler_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_fairteiler_has_wallpost` (
  `fairteiler_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fairteiler_id`,`wallpost_id`),
  KEY `fairteiler_has_wallpost_FKIndex1` (`fairteiler_id`),
  KEY `fairteiler_has_wallpost_FKIndex2` (`wallpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_faq`
--

CREATE TABLE IF NOT EXISTS `fs_faq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `faq_kategorie_id` int(10) unsigned NOT NULL,
  `name` varchar(500) DEFAULT NULL,
  `answer` text,
  PRIMARY KEY (`id`),
  KEY `faq_FKIndex1` (`foodsaver_id`),
  KEY `faq_kategorie_id` (`faq_kategorie_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_faq_category`
--

CREATE TABLE IF NOT EXISTS `fs_faq_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_fetchdate`
--

CREATE TABLE IF NOT EXISTS `fs_fetchdate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `betrieb_id` int(10) unsigned NOT NULL,
  `time` datetime DEFAULT NULL,
  `fetchercount` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fetchdate_FKIndex1` (`betrieb_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13802 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `autokennzeichen_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `position` varchar(255) NOT NULL DEFAULT '',
  `verified` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_pass` datetime NOT NULL,
  `new_bezirk` varchar(120) NOT NULL,
  `want_new` tinyint(4) NOT NULL DEFAULT '0',
  `mailbox_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rolle` tinyint(4) NOT NULL,
  `type` tinyint(4) DEFAULT '0',
  `plz` varchar(5) NOT NULL,
  `stadt` varchar(50) NOT NULL,
  `bundesland_id` int(11) NOT NULL,
  `lat` varchar(20) NOT NULL,
  `lon` varchar(20) NOT NULL,
  `photo` varchar(50) NOT NULL,
  `photo_public` tinyint(4) NOT NULL DEFAULT '0',
  `email` varchar(120) DEFAULT NULL,
  `passwd` varchar(32) DEFAULT NULL,
  `name` varchar(120) DEFAULT NULL,
  `admin` tinyint(3) unsigned DEFAULT NULL,
  `nachname` varchar(120) DEFAULT NULL,
  `anschrift` varchar(120) DEFAULT NULL,
  `telefon` varchar(30) DEFAULT NULL,
  `tox` varchar(255) NOT NULL DEFAULT '',
  `homepage` varchar(255) NOT NULL DEFAULT '',
  `github` varchar(255) NOT NULL DEFAULT '',
  `twitter` varchar(255) NOT NULL DEFAULT '',
  `handy` varchar(50) DEFAULT NULL,
  `geschlecht` tinyint(3) unsigned DEFAULT NULL,
  `geb_datum` date DEFAULT NULL,
  `fs_id` int(11) DEFAULT '0',
  `anmeldedatum` datetime DEFAULT NULL,
  `orgateam` tinyint(3) unsigned DEFAULT '0',
  `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `about_me_public` text NOT NULL,
  `newsletter` tinyint(1) NOT NULL DEFAULT '1',
  `token` varchar(25) NOT NULL,
  `infomail_message` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime NOT NULL,
  `stat_fetchweight` decimal(7,2) unsigned NOT NULL DEFAULT '0.00',
  `stat_fetchcount` int(10) unsigned NOT NULL DEFAULT '0',
  `stat_ratecount` int(10) unsigned NOT NULL DEFAULT '0',
  `stat_rating` decimal(4,2) unsigned NOT NULL DEFAULT '0.00',
  `stat_postcount` int(11) NOT NULL DEFAULT '0',
  `stat_buddycount` int(7) unsigned NOT NULL,
  `stat_bananacount` int(7) unsigned NOT NULL DEFAULT '0',
  `stat_fetchrate` decimal(6,2) NOT NULL DEFAULT '100.00',
  `sleep_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sleep_from` date NOT NULL,
  `sleep_until` date NOT NULL,
  `sleep_msg` text NOT NULL,
  `gcm` varchar(512) NOT NULL DEFAULT '',
  `iosid` varchar(512) NOT NULL DEFAULT '',
  `last_mid` date NOT NULL,
  `option` text NOT NULL,
  `beta` tinyint(1) NOT NULL DEFAULT '0',
  `fs_password` varchar(50) DEFAULT NULL,
  `quiz_rolle` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `contact_public` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `foodsaver_FKIndex2` (`bezirk_id`),
  KEY `foodsaver_FKIndex4` (`autokennzeichen_id`),
  KEY `plz` (`plz`),
  KEY `want_new` (`want_new`),
  KEY `bundesland_id` (`bundesland_id`),
  KEY `mailbox_id` (`mailbox_id`),
  KEY `newsletter` (`newsletter`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=119683 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver_archive`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_archive` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `autokennzeichen_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `position` varchar(255) NOT NULL DEFAULT '',
  `verified` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_pass` datetime NOT NULL,
  `new_bezirk` varchar(120) NOT NULL,
  `want_new` tinyint(4) NOT NULL DEFAULT '0',
  `mailbox_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rolle` tinyint(4) NOT NULL,
  `type` tinyint(4) DEFAULT '0',
  `plz` varchar(5) NOT NULL,
  `stadt` varchar(50) NOT NULL,
  `bundesland_id` int(11) NOT NULL,
  `lat` varchar(20) NOT NULL,
  `lon` varchar(20) NOT NULL,
  `photo` varchar(50) NOT NULL,
  `photo_public` tinyint(4) NOT NULL DEFAULT '0',
  `email` varchar(120) DEFAULT NULL,
  `passwd` varchar(32) DEFAULT NULL,
  `name` varchar(120) DEFAULT NULL,
  `admin` tinyint(3) unsigned DEFAULT NULL,
  `nachname` varchar(120) DEFAULT NULL,
  `anschrift` varchar(120) DEFAULT NULL,
  `telefon` varchar(30) DEFAULT NULL,
  `tox` varchar(255) NOT NULL DEFAULT '',
  `homepage` varchar(255) NOT NULL DEFAULT '',
  `github` varchar(255) NOT NULL DEFAULT '',
  `twitter` varchar(255) NOT NULL DEFAULT '',
  `handy` varchar(50) DEFAULT NULL,
  `geschlecht` tinyint(3) unsigned DEFAULT NULL,
  `geb_datum` date DEFAULT NULL,
  `fs_id` int(11) DEFAULT '0',
  `anmeldedatum` datetime DEFAULT NULL,
  `orgateam` tinyint(3) unsigned DEFAULT '0',
  `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `about_me_public` text NOT NULL,
  `newsletter` tinyint(1) NOT NULL DEFAULT '1',
  `token` varchar(25) NOT NULL,
  `infomail_message` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime NOT NULL,
  `stat_fetchweight` decimal(7,2) unsigned NOT NULL DEFAULT '0.00',
  `stat_fetchcount` int(10) unsigned NOT NULL DEFAULT '0',
  `stat_ratecount` int(10) unsigned NOT NULL DEFAULT '0',
  `stat_rating` decimal(4,2) unsigned NOT NULL DEFAULT '0.00',
  `stat_postcount` int(11) NOT NULL DEFAULT '0',
  `stat_buddycount` int(7) unsigned NOT NULL,
  `stat_bananacount` int(7) unsigned NOT NULL DEFAULT '0',
  `stat_fetchrate` decimal(6,2) NOT NULL DEFAULT '100.00',
  `sleep_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sleep_from` date NOT NULL,
  `sleep_until` date NOT NULL,
  `sleep_msg` text NOT NULL,
  `gcm` varchar(512) NOT NULL DEFAULT '',
  `iosid` varchar(512) NOT NULL DEFAULT '',
  `last_mid` date NOT NULL,
  `option` text NOT NULL,
  `beta` tinyint(1) NOT NULL DEFAULT '0',
  `fs_password` varchar(50) DEFAULT NULL,
  `quiz_rolle` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `contact_public` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `foodsaver_FKIndex2` (`bezirk_id`),
  KEY `foodsaver_FKIndex4` (`autokennzeichen_id`),
  KEY `plz` (`plz`),
  KEY `want_new` (`want_new`),
  KEY `bundesland_id` (`bundesland_id`),
  KEY `mailbox_id` (`mailbox_id`),
  KEY `newsletter` (`newsletter`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=119519 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver_archive2`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_archive2` (
  `id` int(10) unsigned NOT NULL,
  `autokennzeichen_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `verified` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_pass` datetime NOT NULL,
  `new_bezirk` varchar(120) NOT NULL,
  `want_new` tinyint(4) NOT NULL DEFAULT '0',
  `rolle` tinyint(4) NOT NULL,
  `type` tinyint(4) DEFAULT '0',
  `mailbox_id` int(10) unsigned NOT NULL DEFAULT '0',
  `plz` varchar(5) NOT NULL,
  `stadt` varchar(50) NOT NULL,
  `bundesland_id` int(11) NOT NULL,
  `lat` varchar(20) NOT NULL,
  `lon` varchar(20) NOT NULL,
  `photo` varchar(50) NOT NULL,
  `photo_public` tinyint(4) NOT NULL DEFAULT '0',
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
  `fs_id` int(11) DEFAULT '0',
  `anmeldedatum` datetime DEFAULT NULL,
  `orgateam` tinyint(3) unsigned DEFAULT '0',
  `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `about_me_public` text NOT NULL,
  `newsletter` tinyint(1) NOT NULL DEFAULT '1',
  `token` varchar(25) NOT NULL,
  `infomail_message` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime NOT NULL,
  `stat_fetchweight` decimal(7,2) unsigned NOT NULL DEFAULT '0.00',
  `stat_fetchcount` int(10) unsigned NOT NULL DEFAULT '0',
  `stat_ratecount` int(10) unsigned NOT NULL DEFAULT '0',
  `stat_rating` decimal(4,2) unsigned NOT NULL DEFAULT '0.00',
  `stat_postcount` int(11) NOT NULL DEFAULT '0',
  `stat_buddycount` int(7) unsigned NOT NULL,
  `stat_bananacount` int(7) unsigned NOT NULL DEFAULT '1',
  `stat_fetchrate` decimal(6,2) NOT NULL DEFAULT '100.00',
  `option` text NOT NULL,
  `sleep_status` tinyint(3) unsigned NOT NULL,
  `sleep_from` date NOT NULL,
  `sleep_until` date NOT NULL,
  `sleep_msg` text NOT NULL,
  `iosid` varchar(512) NOT NULL,
  `gcm` varchar(512) NOT NULL,
  `beta` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `foodsaver_FKIndex2` (`bezirk_id`),
  KEY `foodsaver_FKIndex4` (`autokennzeichen_id`),
  KEY `plz` (`plz`),
  KEY `want_new` (`want_new`),
  KEY `bundesland_id` (`bundesland_id`),
  KEY `newsletter` (`newsletter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver_has_bell`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_bell` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `bell_id` int(10) unsigned NOT NULL,
  `seen` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`foodsaver_id`,`bell_id`),
  KEY `foodsaver_has_bell_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_bell_FKIndex2` (`bell_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver_has_bezirk`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_bezirk` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `active` int(10) unsigned DEFAULT '0' COMMENT '0=beworben,1=aktiv,10=vielleicht',
  `added` datetime NOT NULL,
  `application` text NOT NULL,
  PRIMARY KEY (`foodsaver_id`,`bezirk_id`),
  KEY `foodsaver_has_bezirk_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_bezirk_FKIndex2` (`bezirk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver_has_contact`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_contact` (
  `foodsaver_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`foodsaver_id`,`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver_has_conversation`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_conversation` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `conversation_id` int(10) unsigned NOT NULL,
  `unread` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`foodsaver_id`,`conversation_id`),
  KEY `foodsaver_has_conversation_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_conversation_FKIndex2` (`conversation_id`),
  KEY `unread` (`unread`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver_has_event`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_event` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `event_id` int(10) unsigned NOT NULL,
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`foodsaver_id`,`event_id`),
  KEY `foodsaver_has_event_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_event_FKIndex2` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver_has_fairteiler`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_fairteiler` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `fairteiler_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`foodsaver_id`,`fairteiler_id`),
  KEY `foodsaver_has_fairteiler_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_fairteiler_FKIndex2` (`fairteiler_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver_has_fetchdate`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_fetchdate` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `fetchdate_id` int(10) unsigned NOT NULL,
  `confirmed` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`foodsaver_id`,`fetchdate_id`),
  KEY `foodsaver_has_fetchdate_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_fetchdate_FKIndex2` (`fetchdate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_wallpost` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  `usercomment` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`foodsaver_id`,`wallpost_id`),
  KEY `foodsaver_has_wallpost_FKIndex1` (`foodsaver_id`),
  KEY `foodsaver_has_wallpost_FKIndex2` (`wallpost_id`),
  KEY `foodsaver_id` (`foodsaver_id`),
  KEY `wallpost_id` (`wallpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_foodsaver_stats`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_stats` (
  `foodsaver_id` int(11) NOT NULL,
  `user_rating` decimal(10,2) DEFAULT NULL,
  `count_fetch` int(11) DEFAULT NULL,
  `count_forum` int(11) DEFAULT NULL,
  `count_vouch` int(11) DEFAULT NULL,
  `gerettet` decimal(10,2) DEFAULT NULL,
  `count_rating` int(11) DEFAULT NULL,
  PRIMARY KEY (`foodsaver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_fsreport_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_fsreport_has_wallpost` (
  `fsreport_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fsreport_id`,`wallpost_id`),
  KEY `fsreport_has_wallpost_FKIndex1` (`fsreport_id`),
  KEY `fsreport_has_wallpost_FKIndex2` (`wallpost_id`),
  KEY `fsreport_id` (`fsreport_id`),
  KEY `wallpost_id` (`wallpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_geoRegion`
--

CREATE TABLE IF NOT EXISTS `fs_geoRegion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(90) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=445 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_glocke`
--

CREATE TABLE IF NOT EXISTS `fs_glocke` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `msg` varchar(300) NOT NULL,
  `url` varchar(160) DEFAULT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=268228 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_glocke_read`
--

CREATE TABLE IF NOT EXISTS `fs_glocke_read` (
  `glocke_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `unread` tinyint(3) unsigned DEFAULT '1',
  PRIMARY KEY (`glocke_id`,`foodsaver_id`),
  KEY `glocke_has_foodsaver_FKIndex1` (`glocke_id`),
  KEY `glocke_has_foodsaver_FKIndex2` (`foodsaver_id`),
  KEY `glocke_read_unread` (`unread`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_ipblock`
--

CREATE TABLE IF NOT EXISTS `fs_ipblock` (
  `ip` varchar(20) NOT NULL,
  `context` varchar(10) NOT NULL,
  `start` datetime DEFAULT NULL,
  `duration` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ip`,`context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_item`
--

CREATE TABLE IF NOT EXISTS `fs_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_class_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `item_FKIndex2` (`user_id`),
  KEY `item_FKIndex3` (`item_class_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1112 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_item_class`
--

CREATE TABLE IF NOT EXISTS `fs_item_class` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_type_id` int(10) unsigned NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `desc` text,
  PRIMARY KEY (`id`),
  KEY `item_class_FKIndex1` (`item_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_item_request`
--

CREATE TABLE IF NOT EXISTS `fs_item_request` (
  `user_id` int(10) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`user_id`,`item_id`),
  KEY `user_has_item_FKIndex1` (`user_id`),
  KEY `user_has_item_FKIndex2` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_item_track`
--

CREATE TABLE IF NOT EXISTS `fs_item_track` (
  `user_id` int(10) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `time` datetime NOT NULL,
  `lat` varchar(100) DEFAULT NULL,
  `lon` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`item_id`,`time`),
  KEY `user_has_item_FKIndex1` (`user_id`),
  KEY `user_has_item_FKIndex2` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_item_type`
--

CREATE TABLE IF NOT EXISTS `fs_item_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_kette`
--

CREATE TABLE IF NOT EXISTS `fs_kette` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL,
  `logo` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=311 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_land`
--

CREATE TABLE IF NOT EXISTS `fs_land` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(90) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_language`
--

CREATE TABLE IF NOT EXISTS `fs_language` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `short` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_lebensmittel`
--

CREATE TABLE IF NOT EXISTS `fs_lebensmittel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_location`
--

CREATE TABLE IF NOT EXISTS `fs_location` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lon` decimal(11,8) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `street` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `location_unique` (`lat`,`lon`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1300 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_login`
--

CREATE TABLE IF NOT EXISTS `fs_login` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `agent` varchar(200) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_FKIndex1` (`foodsaver_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3016243 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_mailbox`
--

CREATE TABLE IF NOT EXISTS `fs_mailbox` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `member` tinyint(4) NOT NULL DEFAULT '0',
  `last_access` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`name`),
  KEY `member` (`member`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27039 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_mailbox_member`
--

CREATE TABLE IF NOT EXISTS `fs_mailbox_member` (
  `mailbox_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `email_name` varchar(120) NOT NULL,
  PRIMARY KEY (`mailbox_id`,`foodsaver_id`),
  KEY `mailbox_has_foodsaver_FKIndex1` (`mailbox_id`),
  KEY `mailbox_has_foodsaver_FKIndex2` (`foodsaver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_mailbox_message`
--

CREATE TABLE IF NOT EXISTS `fs_mailbox_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mailbox_id` int(10) unsigned NOT NULL,
  `folder` tinyint(3) unsigned DEFAULT '1',
  `sender` varchar(120) DEFAULT NULL,
  `to` text NOT NULL,
  `subject` varchar(120) DEFAULT NULL,
  `body` text,
  `body_html` text NOT NULL,
  `time` datetime DEFAULT NULL,
  `attach` text,
  `read` tinyint(3) unsigned DEFAULT NULL,
  `answer` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_message_folder` (`folder`),
  KEY `mailbox_message_FKIndex1` (`mailbox_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=66735 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_mailchange`
--

CREATE TABLE IF NOT EXISTS `fs_mailchange` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `newmail` varchar(200) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `token` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`foodsaver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_mail_error`
--

CREATE TABLE IF NOT EXISTS `fs_mail_error` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `send_mail_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_error_foodsaver_id` (`foodsaver_id`),
  KEY `mail_error_FKIndex1` (`send_mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_message`
--

CREATE TABLE IF NOT EXISTS `fs_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` int(10) unsigned DEFAULT NULL,
  `recip_id` int(10) unsigned DEFAULT NULL,
  `unread` tinyint(3) unsigned DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `msg` text,
  `time` datetime DEFAULT NULL,
  `attach` varchar(500) DEFAULT NULL,
  `recd` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `message_recip_id` (`recip_id`),
  KEY `message_sender_id` (`sender_id`),
  KEY `recd` (`recd`),
  KEY `unread` (`unread`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1490899 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_message_tpl`
--

CREATE TABLE IF NOT EXISTS `fs_message_tpl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` int(10) unsigned NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `subject` varchar(120) NOT NULL,
  `body` text,
  PRIMARY KEY (`id`),
  KEY `message_tpl_FKIndex1` (`language_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_msg`
--

CREATE TABLE IF NOT EXISTS `fs_msg` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `body` text,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_FKIndex1` (`foodsaver_id`),
  KEY `message_FKIndex2` (`conversation_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2384547 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_mumbleuser`
--

CREATE TABLE IF NOT EXISTS `fs_mumbleuser` (
  `foodsaver_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sha` varchar(160) NOT NULL,
  PRIMARY KEY (`foodsaver_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_partnerschaft`
--

CREATE TABLE IF NOT EXISTS `fs_partnerschaft` (
  `foodsaver_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `form` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`foodsaver_id`,`partner_id`,`form`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8765 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_pass_gen`
--

CREATE TABLE IF NOT EXISTS `fs_pass_gen` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`foodsaver_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_pass_request`
--

CREATE TABLE IF NOT EXISTS `fs_pass_request` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`foodsaver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_plz`
--

CREATE TABLE IF NOT EXISTS `fs_plz` (
  `plz` varchar(5) NOT NULL,
  `stadt_id` int(10) unsigned NOT NULL,
  `stadt_kennzeichen_id` int(10) unsigned NOT NULL,
  `bundesland_id` int(10) unsigned NOT NULL,
  `geoRegion_id` int(10) unsigned NOT NULL,
  `land_id` int(10) unsigned NOT NULL,
  `lat` varchar(60) DEFAULT NULL,
  `lon` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`plz`),
  KEY `plz_region_id2` (`geoRegion_id`),
  KEY `plz_bundesland_id` (`bundesland_id`),
  KEY `plz_stadtkennzeichen` (`stadt_kennzeichen_id`),
  KEY `plz_stadt_id` (`stadt_id`),
  KEY `land_id` (`land_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_pushqueue`
--

CREATE TABLE IF NOT EXISTS `fs_pushqueue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` int(10) unsigned NOT NULL,
  `recip_id` int(10) unsigned NOT NULL,
  `time` datetime NOT NULL,
  `message_id` int(10) unsigned NOT NULL,
  `title` varchar(80) NOT NULL DEFAULT '',
  `message` varchar(400) NOT NULL DEFAULT '',
  `data` text,
  `status` tinyint(2) unsigned DEFAULT NULL,
  `id_gcm` varchar(400) NOT NULL DEFAULT '',
  `id_apn` varchar(400) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `pushqueue_status` (`status`),
  KEY `pushqueue_message_id` (`message_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=31257 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_question`
--

CREATE TABLE IF NOT EXISTS `fs_question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `text` text,
  `duration` int(3) unsigned NOT NULL,
  `wikilink` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=115 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_question_has_quiz`
--

CREATE TABLE IF NOT EXISTS `fs_question_has_quiz` (
  `question_id` int(10) unsigned NOT NULL,
  `quiz_id` int(10) unsigned NOT NULL,
  `fp` tinyint(2) unsigned DEFAULT NULL,
  PRIMARY KEY (`question_id`,`quiz_id`),
  KEY `question_has_quiz_FKIndex1` (`question_id`),
  KEY `question_has_quiz_FKIndex2` (`quiz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_question_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_question_has_wallpost` (
  `question_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  `usercomment` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`question_id`,`wallpost_id`),
  KEY `question_has_wallpost_FKIndex1` (`question_id`),
  KEY `question_has_wallpost_FKIndex2` (`wallpost_id`),
  KEY `question_id` (`question_id`),
  KEY `wallpost_id` (`wallpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_quiz`
--

CREATE TABLE IF NOT EXISTS `fs_quiz` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `desc` text,
  `maxfp` smallint(4) unsigned NOT NULL,
  `questcount` smallint(4) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_quiz_session`
--

CREATE TABLE IF NOT EXISTS `fs_quiz_session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `quiz_id` int(10) unsigned NOT NULL,
  `status` tinyint(2) unsigned DEFAULT NULL,
  `quiz_index` tinyint(3) unsigned DEFAULT NULL,
  `quiz_questions` text,
  `quiz_result` text,
  `time_start` datetime DEFAULT NULL,
  `time_end` datetime DEFAULT NULL,
  `fp` decimal(5,2) DEFAULT NULL,
  `maxfp` tinyint(3) unsigned DEFAULT NULL,
  `quest_count` tinyint(3) unsigned DEFAULT NULL,
  `easymode` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `quiz_result_FKIndex1` (`quiz_id`),
  KEY `quiz_result_FKIndex2` (`foodsaver_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24198 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_rating`
--

CREATE TABLE IF NOT EXISTS `fs_rating` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `rater_id` int(10) unsigned NOT NULL,
  `ratingtype` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `rating` tinyint(4) DEFAULT NULL,
  `msg` text NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`foodsaver_id`,`rater_id`,`ratingtype`),
  KEY `fk_foodsaver_has_foodsaver_foodsaver1_idx` (`rater_id`),
  KEY `fk_foodsaver_has_foodsaver_foodsaver_idx` (`foodsaver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ratingtype 1+2 = bananen, 4+5 = betriebsmeldung';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_region`
--

CREATE TABLE IF NOT EXISTS `fs_region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_report`
--

CREATE TABLE IF NOT EXISTS `fs_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `reporter_id` int(10) unsigned DEFAULT NULL,
  `reporttype` tinyint(2) unsigned DEFAULT NULL,
  `betrieb_id` int(10) unsigned DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `committed` tinyint(2) unsigned DEFAULT '0',
  `msg` text,
  `tvalue` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `report_FKIndex1` (`foodsaver_id`),
  KEY `report_reporter` (`reporter_id`),
  KEY `report_betrieb` (`betrieb_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1817 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_send_email`
--

CREATE TABLE IF NOT EXISTS `fs_send_email` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `mailbox_id` int(10) unsigned NOT NULL,
  `mode` tinyint(4) NOT NULL DEFAULT '1',
  `complete` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(200) DEFAULT NULL,
  `message` text,
  `zeit` datetime DEFAULT NULL,
  `recip` text,
  `attach` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `send_email_FKIndex1` (`foodsaver_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=862 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_stadt`
--

CREATE TABLE IF NOT EXISTS `fs_stadt` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6499 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_stadtteil`
--

CREATE TABLE IF NOT EXISTS `fs_stadtteil` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stadt_id` int(10) unsigned NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stadtteil_FKIndex1` (`stadt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_stadt_kennzeichen`
--

CREATE TABLE IF NOT EXISTS `fs_stadt_kennzeichen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_theme`
--

CREATE TABLE IF NOT EXISTS `fs_theme` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `last_post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(260) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `active` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `sticky` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `theme_FKIndex1` (`foodsaver_id`),
  KEY `last_post_id` (`last_post_id`),
  KEY `active` (`active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17915 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_theme_follower`
--

CREATE TABLE IF NOT EXISTS `fs_theme_follower` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `theme_id` int(10) unsigned NOT NULL,
  `infotype` tinyint(1) NOT NULL,
  PRIMARY KEY (`foodsaver_id`,`theme_id`),
  KEY `infotype` (`infotype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_theme_post`
--

CREATE TABLE IF NOT EXISTS `fs_theme_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `theme_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `reply_post` int(10) unsigned NOT NULL DEFAULT '0',
  `body` text,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `theme_post_FKIndex1` (`foodsaver_id`),
  KEY `theme_post_FKIndex2` (`theme_id`),
  KEY `reply_post` (`reply_post`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=96886 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_upgrade_request`
--

CREATE TABLE IF NOT EXISTS `fs_upgrade_request` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `rolle` tinyint(3) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `time` datetime DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`foodsaver_id`,`rolle`,`bezirk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_user`
--

CREATE TABLE IF NOT EXISTS `fs_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fsid` int(10) unsigned NOT NULL DEFAULT '0',
  `user_type_id` int(10) unsigned NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `passwd` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `anschrift` varchar(260) DEFAULT NULL,
  `plz` varchar(5) DEFAULT NULL,
  `ort` varchar(260) DEFAULT NULL,
  `lat` varchar(50) NOT NULL,
  `lon` varchar(50) NOT NULL,
  `ansprechpartner` varchar(260) NOT NULL,
  `zeiten` text NOT NULL,
  `web` varchar(150) NOT NULL,
  `telefon` varchar(100) NOT NULL,
  `desc` text,
  `photo` varchar(50) DEFAULT NULL,
  `token` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `user_FKIndex1` (`user_type_id`),
  KEY `fsid` (`fsid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=888 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_usernotes_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_usernotes_has_wallpost` (
  `usernotes_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  `usercomment` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`usernotes_id`,`wallpost_id`),
  KEY `usernotes_has_wallpost_FKIndex1` (`usernotes_id`),
  KEY `usernotes_has_wallpost_FKIndex2` (`wallpost_id`),
  KEY `usernotes_id` (`usernotes_id`),
  KEY `wallpost_id` (`wallpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_user_type`
--

CREATE TABLE IF NOT EXISTS `fs_user_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fs_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_wallpost` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `body` text,
  `time` datetime DEFAULT NULL,
  `attach` text,
  PRIMARY KEY (`id`),
  KEY `wallpost_FKIndex1` (`foodsaver_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37554 ;

-- --------------------------------------------------------

--
-- Struktur des Views `flat_bezirk_all`
--
DROP TABLE IF EXISTS `flat_bezirk_all`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `flat_bezirk_all` AS select `t1`.`id` AS `idlvl1`,`t1`.`name` AS `lvl1`,`t2`.`id` AS `idlvl2`,`t2`.`name` AS `lvl2`,`t3`.`id` AS `idlvl3`,`t3`.`name` AS `lvl3`,`t4`.`id` AS `idlvl4`,`t4`.`name` AS `lvl4`,ifnull(`t4`.`id`,`t3`.`id`) AS `id`,ifnull(`t4`.`name`,`t4`.`name`) AS `name` from (((`fs_bezirk` `t1` left join `fs_bezirk` `t2` on((`t2`.`parent_id` = `t1`.`id`))) left join `fs_bezirk` `t3` on((`t3`.`parent_id` = `t2`.`id`))) left join `fs_bezirk` `t4` on((`t4`.`parent_id` = `t3`.`id`))) where ((`t1`.`id` in (1,63,106,110,155,261)) and (`t4`.`type` <> 7)) union select `t1`.`id` AS `idlvl1`,`t1`.`name` AS `lvl1`,`t2`.`id` AS `idlvl2`,`t2`.`name` AS `lvl2`,`t3`.`id` AS `idlvl3`,`t3`.`name` AS `lvl3`,NULL AS `idlvl4`,NULL AS `lvl4`,`t3`.`id` AS `id`,`t3`.`name` AS `name` from ((`fs_bezirk` `t1` left join `fs_bezirk` `t2` on((`t2`.`parent_id` = `t1`.`id`))) left join `fs_bezirk` `t3` on((`t3`.`parent_id` = `t2`.`id`))) where ((`t1`.`id` in (1,63,106,110,155,261)) and (`t3`.`type` <> 7)) union select `t1`.`id` AS `idlvl1`,`t1`.`name` AS `lvl1`,`t2`.`id` AS `idlvl2`,`t2`.`name` AS `lvl2`,NULL AS `idlvl3`,NULL AS `lvl3`,NULL AS `idlvl4`,NULL AS `lvl4`,`t2`.`id` AS `id`,`t2`.`name` AS `name` from (`fs_bezirk` `t1` left join `fs_bezirk` `t2` on((`t2`.`parent_id` = `t1`.`id`))) where ((`t1`.`id` in (1,63,106,110,155,261)) and (`t2`.`type` <> 7)) order by `lvl1`,`lvl2`,`lvl3`,`lvl4`;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `fs_bezirk`
--
ALTER TABLE `fs_bezirk`
  ADD CONSTRAINT `fs_bezirk_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `fs_bezirk` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fs_bezirk_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `fs_bezirk` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `fs_bezirk_closure`
--
ALTER TABLE `fs_bezirk_closure`
  ADD CONSTRAINT `fs_bezirk_closure_ibfk_1` FOREIGN KEY (`bezirk_id`) REFERENCES `fs_bezirk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fs_bezirk_closure_ibfk_2` FOREIGN KEY (`ancestor_id`) REFERENCES `fs_bezirk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fs_bezirk_closure_ibfk_3` FOREIGN KEY (`bezirk_id`) REFERENCES `fs_bezirk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fs_bezirk_closure_ibfk_4` FOREIGN KEY (`ancestor_id`) REFERENCES `fs_bezirk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
