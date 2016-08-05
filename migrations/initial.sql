-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 20, 2016 at 12:33 PM
-- Server version: 5.5.49-0+deb8u1-log
-- PHP Version: 5.6.23-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lmr_prod`
--

-- --------------------------------------------------------

--
-- Table structure for table `basket_has_art`
--

CREATE TABLE IF NOT EXISTS `basket_has_art` (
  `basket_id` int(10) unsigned NOT NULL,
  `art_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `basket_has_types`
--

CREATE TABLE IF NOT EXISTS `basket_has_types` (
  `basket_id` int(10) unsigned NOT NULL,
  `types_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_abholen`
--

CREATE TABLE IF NOT EXISTS `fs_abholen` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `betrieb_id` int(10) unsigned NOT NULL,
  `dow` tinyint(3) unsigned NOT NULL,
  `time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_abholer`
--

CREATE TABLE IF NOT EXISTS `fs_abholer` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `betrieb_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `confirmed` tinyint(3) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_abholmengen`
--

CREATE TABLE IF NOT EXISTS `fs_abholmengen` (
  `id` int(11) NOT NULL,
  `menge` decimal(5,1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_abholzeiten`
--

CREATE TABLE IF NOT EXISTS `fs_abholzeiten` (
  `betrieb_id` int(10) unsigned NOT NULL,
  `dow` tinyint(3) unsigned NOT NULL,
  `time` time NOT NULL DEFAULT '00:00:00',
  `fetcher` tinyint(3) unsigned NOT NULL DEFAULT '4'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_activity`
--

CREATE TABLE IF NOT EXISTS `fs_activity` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `zeit` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_answer`
--

CREATE TABLE IF NOT EXISTS `fs_answer` (
`id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `text` text,
  `explanation` text NOT NULL,
  `right` tinyint(1) unsigned DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=464 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_apitoken`
--

CREATE TABLE IF NOT EXISTS `fs_apitoken` (
  `foodsaver_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_application_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_application_has_wallpost` (
  `application_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_autokennzeichen`
--

CREATE TABLE IF NOT EXISTS `fs_autokennzeichen` (
`id` int(10) unsigned NOT NULL,
  `land_id` int(10) unsigned NOT NULL,
  `name` varchar(5) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=581 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_basket`
--

CREATE TABLE IF NOT EXISTS `fs_basket` (
`id` int(10) unsigned NOT NULL,
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
  `appost` tinyint(2) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=38738 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_basket_anfrage`
--

CREATE TABLE IF NOT EXISTS `fs_basket_anfrage` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `basket_id` int(10) unsigned NOT NULL,
  `status` tinyint(2) unsigned DEFAULT NULL,
  `time` datetime NOT NULL,
  `appost` tinyint(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_basket_has_art`
--

CREATE TABLE IF NOT EXISTS `fs_basket_has_art` (
  `basket_id` int(10) unsigned NOT NULL,
  `art_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_basket_has_types`
--

CREATE TABLE IF NOT EXISTS `fs_basket_has_types` (
  `basket_id` int(10) unsigned NOT NULL,
  `types_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_basket_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_basket_has_wallpost` (
  `basket_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bell`
--

CREATE TABLE IF NOT EXISTS `fs_bell` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `body` varchar(50) DEFAULT NULL,
  `vars` text,
  `attr` varchar(500) DEFAULT NULL,
  `icon` varchar(150) DEFAULT NULL,
  `identifier` varchar(40) DEFAULT NULL,
  `time` datetime NOT NULL,
  `closeable` tinyint(1) unsigned NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=208493 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb` (
`id` int(10) unsigned NOT NULL,
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
  `springer_conversation_id` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=15906 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb_has_lebensmittel`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_has_lebensmittel` (
  `betrieb_id` int(10) unsigned NOT NULL,
  `lebensmittel_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb_kategorie`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_kategorie` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=266 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb_notiz`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_notiz` (
`id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `betrieb_id` int(10) unsigned NOT NULL,
  `milestone` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `text` text,
  `zeit` datetime DEFAULT NULL,
  `last` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=397095 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb_status`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_status` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb_team`
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
  `stat_add_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bezirk`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk` (
`id` int(10) unsigned NOT NULL,
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
  `moderated` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=1330 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bezirk_closure`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk_closure` (
  `bezirk_id` int(10) unsigned NOT NULL,
  `ancestor_id` int(10) unsigned NOT NULL,
  `depth` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bezirk_has_theme`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk_has_theme` (
  `theme_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `bot_theme` tinyint(3) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bezirk_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk_has_wallpost` (
  `bezirk_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_blog_entry`
--

CREATE TABLE IF NOT EXISTS `fs_blog_entry` (
`id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `active` tinyint(3) unsigned NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `teaser` varchar(500) DEFAULT NULL,
  `body` text,
  `time` datetime DEFAULT NULL,
  `picture` varchar(150) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=220 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_botschafter`
--

CREATE TABLE IF NOT EXISTS `fs_botschafter` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_buddy`
--

CREATE TABLE IF NOT EXISTS `fs_buddy` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `buddy_id` int(10) unsigned NOT NULL,
  `confirmed` tinyint(1) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bundesland`
--

CREATE TABLE IF NOT EXISTS `fs_bundesland` (
`id` int(10) unsigned NOT NULL,
  `land_id` int(10) unsigned NOT NULL,
  `name` varchar(160) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_cache`
--

CREATE TABLE IF NOT EXISTS `fs_cache` (
  `id` varchar(50) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_contact`
--

CREATE TABLE IF NOT EXISTS `fs_contact` (
`id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(155) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=18626 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_content`
--

CREATE TABLE IF NOT EXISTS `fs_content` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(20) DEFAULT NULL,
  `title` varchar(120) DEFAULT NULL,
  `body` text,
  `last_mod` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_conversation`
--

CREATE TABLE IF NOT EXISTS `fs_conversation` (
`id` int(10) unsigned NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(40) NOT NULL,
  `start` datetime DEFAULT NULL,
  `last` datetime DEFAULT NULL,
  `last_foodsaver_id` int(10) unsigned DEFAULT NULL,
  `start_foodsaver_id` int(10) unsigned NOT NULL,
  `last_message_id` int(10) unsigned NOT NULL,
  `last_message` text NOT NULL,
  `member` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=682361 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_document`
--

CREATE TABLE IF NOT EXISTS `fs_document` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `file` varchar(300) DEFAULT NULL,
  `body` text NOT NULL,
  `rolle` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_email_blacklist`
--

CREATE TABLE IF NOT EXISTS `fs_email_blacklist` (
  `email` varchar(255) NOT NULL,
  `since` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_email_status`
--

CREATE TABLE IF NOT EXISTS `fs_email_status` (
  `email_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `status` tinyint(3) unsigned DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_event`
--

CREATE TABLE IF NOT EXISTS `fs_event` (
`id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(200) DEFAULT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `description` text,
  `bot` tinyint(2) unsigned DEFAULT '0',
  `online` tinyint(2) unsigned DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=4126 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_event_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_event_has_wallpost` (
  `event_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_event_registration`
--

CREATE TABLE IF NOT EXISTS `fs_event_registration` (
`id` int(11) NOT NULL,
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
  `available_thursday` set('0') DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=994 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_event_workshops`
--

CREATE TABLE IF NOT EXISTS `fs_event_workshops` (
`id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `duration` int(11) NOT NULL,
  `allowed_attendants` int(11) NOT NULL,
  `name_en` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_event_workshop_registration`
--

CREATE TABLE IF NOT EXISTS `fs_event_workshop_registration` (
  `wid` int(11) NOT NULL DEFAULT '-1',
  `uid` int(11) NOT NULL,
  `wish` smallint(6) NOT NULL,
  `confirmed` tinyint(4) NOT NULL,
`id` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2921 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_fairteiler`
--

CREATE TABLE IF NOT EXISTS `fs_fairteiler` (
`id` int(10) unsigned NOT NULL,
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
  `add_foodsaver` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=707 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_fairteiler_follower`
--

CREATE TABLE IF NOT EXISTS `fs_fairteiler_follower` (
  `fairteiler_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `infotype` tinyint(3) unsigned NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_fairteiler_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_fairteiler_has_wallpost` (
  `fairteiler_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_faq`
--

CREATE TABLE IF NOT EXISTS `fs_faq` (
`id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `faq_kategorie_id` int(10) unsigned NOT NULL,
  `name` varchar(500) DEFAULT NULL,
  `answer` text
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_faq_category`
--

CREATE TABLE IF NOT EXISTS `fs_faq_category` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_fetchdate`
--

CREATE TABLE IF NOT EXISTS `fs_fetchdate` (
`id` int(10) unsigned NOT NULL,
  `betrieb_id` int(10) unsigned NOT NULL,
  `time` datetime DEFAULT NULL,
  `fetchercount` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=29601 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver` (
`id` int(10) unsigned NOT NULL,
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
  `contact_public` tinyint(4) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=151030 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_archive`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_archive` (
`id` int(10) unsigned NOT NULL,
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
  `contact_public` tinyint(4) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=150886 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_archive2`
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
  `beta` tinyint(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_bell`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_bell` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `bell_id` int(10) unsigned NOT NULL,
  `seen` tinyint(1) unsigned DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_bezirk`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_bezirk` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `active` int(10) unsigned DEFAULT '0' COMMENT '0=beworben,1=aktiv,10=vielleicht',
  `added` datetime NOT NULL,
  `application` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_contact`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_contact` (
  `foodsaver_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_conversation`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_conversation` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `conversation_id` int(10) unsigned NOT NULL,
  `unread` tinyint(1) unsigned DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_event`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_event` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `event_id` int(10) unsigned NOT NULL,
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_fairteiler`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_fairteiler` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `fairteiler_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_fetchdate`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_fetchdate` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `fetchdate_id` int(10) unsigned NOT NULL,
  `confirmed` tinyint(1) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_wallpost` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  `usercomment` tinyint(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_stats`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_stats` (
  `foodsaver_id` int(11) NOT NULL,
  `user_rating` decimal(10,2) DEFAULT NULL,
  `count_fetch` int(11) DEFAULT NULL,
  `count_forum` int(11) DEFAULT NULL,
  `count_vouch` int(11) DEFAULT NULL,
  `gerettet` decimal(10,2) DEFAULT NULL,
  `count_rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_fsreport_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_fsreport_has_wallpost` (
  `fsreport_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_geoRegion`
--

CREATE TABLE IF NOT EXISTS `fs_geoRegion` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(90) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=445 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_glocke`
--

CREATE TABLE IF NOT EXISTS `fs_glocke` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `msg` varchar(300) NOT NULL,
  `url` varchar(160) DEFAULT NULL,
  `time` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=425246 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_glocke_read`
--

CREATE TABLE IF NOT EXISTS `fs_glocke_read` (
  `glocke_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `unread` tinyint(3) unsigned DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_ipblock`
--

CREATE TABLE IF NOT EXISTS `fs_ipblock` (
  `ip` varchar(20) NOT NULL,
  `context` varchar(10) NOT NULL,
  `start` datetime DEFAULT NULL,
  `duration` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_item`
--

CREATE TABLE IF NOT EXISTS `fs_item` (
`id` int(10) unsigned NOT NULL,
  `item_class_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=1112 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_item_class`
--

CREATE TABLE IF NOT EXISTS `fs_item_class` (
`id` int(10) unsigned NOT NULL,
  `item_type_id` int(10) unsigned NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `desc` text
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_item_request`
--

CREATE TABLE IF NOT EXISTS `fs_item_request` (
  `user_id` int(10) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_item_track`
--

CREATE TABLE IF NOT EXISTS `fs_item_track` (
  `user_id` int(10) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `time` datetime NOT NULL,
  `lat` varchar(100) DEFAULT NULL,
  `lon` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_item_type`
--

CREATE TABLE IF NOT EXISTS `fs_item_type` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(120) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_kette`
--

CREATE TABLE IF NOT EXISTS `fs_kette` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(60) DEFAULT NULL,
  `logo` varchar(30) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=398 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_land`
--

CREATE TABLE IF NOT EXISTS `fs_land` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(90) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_language`
--

CREATE TABLE IF NOT EXISTS `fs_language` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `short` varchar(10) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_lebensmittel`
--

CREATE TABLE IF NOT EXISTS `fs_lebensmittel` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_location`
--

CREATE TABLE IF NOT EXISTS `fs_location` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lon` decimal(11,8) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `street` varchar(200) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=4284 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_login`
--

CREATE TABLE IF NOT EXISTS `fs_login` (
`id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `agent` varchar(200) DEFAULT NULL,
  `time` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6138537 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mailbox`
--

CREATE TABLE IF NOT EXISTS `fs_mailbox` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `member` tinyint(4) NOT NULL DEFAULT '0',
  `last_access` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=28458 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mailbox_member`
--

CREATE TABLE IF NOT EXISTS `fs_mailbox_member` (
  `mailbox_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `email_name` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mailbox_message`
--

CREATE TABLE IF NOT EXISTS `fs_mailbox_message` (
`id` int(10) unsigned NOT NULL,
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
  `answer` tinyint(3) unsigned DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=102410 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mailchange`
--

CREATE TABLE IF NOT EXISTS `fs_mailchange` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `newmail` varchar(200) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `token` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mail_error`
--

CREATE TABLE IF NOT EXISTS `fs_mail_error` (
`id` int(10) unsigned NOT NULL,
  `send_mail_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_message`
--

CREATE TABLE IF NOT EXISTS `fs_message` (
`id` int(10) unsigned NOT NULL,
  `sender_id` int(10) unsigned DEFAULT NULL,
  `recip_id` int(10) unsigned DEFAULT NULL,
  `unread` tinyint(3) unsigned DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `msg` text,
  `time` datetime DEFAULT NULL,
  `attach` varchar(500) DEFAULT NULL,
  `recd` tinyint(2) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=2570714 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_message_tpl`
--

CREATE TABLE IF NOT EXISTS `fs_message_tpl` (
`id` int(10) unsigned NOT NULL,
  `language_id` int(10) unsigned NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `subject` varchar(120) NOT NULL,
  `body` text
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_msg`
--

CREATE TABLE IF NOT EXISTS `fs_msg` (
`id` int(10) unsigned NOT NULL,
  `conversation_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `body` text,
  `time` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4362369 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mumbleuser`
--

CREATE TABLE IF NOT EXISTS `fs_mumbleuser` (
  `foodsaver_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sha` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_partnerschaft`
--

CREATE TABLE IF NOT EXISTS `fs_partnerschaft` (
`foodsaver_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `form` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `time` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8765 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_pass_gen`
--

CREATE TABLE IF NOT EXISTS `fs_pass_gen` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_pass_request`
--

CREATE TABLE IF NOT EXISTS `fs_pass_request` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_plz`
--

CREATE TABLE IF NOT EXISTS `fs_plz` (
  `plz` varchar(5) NOT NULL,
  `stadt_id` int(10) unsigned NOT NULL,
  `stadt_kennzeichen_id` int(10) unsigned NOT NULL,
  `bundesland_id` int(10) unsigned NOT NULL,
  `geoRegion_id` int(10) unsigned NOT NULL,
  `land_id` int(10) unsigned NOT NULL,
  `lat` varchar(60) DEFAULT NULL,
  `lon` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_pushqueue`
--

CREATE TABLE IF NOT EXISTS `fs_pushqueue` (
`id` int(10) unsigned NOT NULL,
  `sender_id` int(10) unsigned NOT NULL,
  `recip_id` int(10) unsigned NOT NULL,
  `time` datetime NOT NULL,
  `message_id` int(10) unsigned NOT NULL,
  `title` varchar(80) NOT NULL DEFAULT '',
  `message` varchar(400) NOT NULL DEFAULT '',
  `data` text,
  `status` tinyint(2) unsigned DEFAULT NULL,
  `id_gcm` varchar(400) NOT NULL DEFAULT '',
  `id_apn` varchar(400) NOT NULL DEFAULT ''
) ENGINE=InnoDB AUTO_INCREMENT=35328 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_question`
--

CREATE TABLE IF NOT EXISTS `fs_question` (
`id` int(10) unsigned NOT NULL,
  `text` text,
  `duration` int(3) unsigned NOT NULL,
  `wikilink` varchar(250) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_question_has_quiz`
--

CREATE TABLE IF NOT EXISTS `fs_question_has_quiz` (
  `question_id` int(10) unsigned NOT NULL,
  `quiz_id` int(10) unsigned NOT NULL,
  `fp` tinyint(2) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_question_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_question_has_wallpost` (
  `question_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  `usercomment` tinyint(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_quiz`
--

CREATE TABLE IF NOT EXISTS `fs_quiz` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `desc` text,
  `maxfp` smallint(4) unsigned NOT NULL,
  `questcount` smallint(4) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_quiz_session`
--

CREATE TABLE IF NOT EXISTS `fs_quiz_session` (
`id` int(10) unsigned NOT NULL,
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
  `easymode` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=44736 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_rating`
--

CREATE TABLE IF NOT EXISTS `fs_rating` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `rater_id` int(10) unsigned NOT NULL,
  `ratingtype` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `rating` tinyint(4) DEFAULT NULL,
  `msg` text NOT NULL,
  `time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ratingtype 1+2 = bananen, 4+5 = betriebsmeldung';

-- --------------------------------------------------------

--
-- Table structure for table `fs_region`
--

CREATE TABLE IF NOT EXISTS `fs_region` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_report`
--

CREATE TABLE IF NOT EXISTS `fs_report` (
`id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `reporter_id` int(10) unsigned DEFAULT NULL,
  `reporttype` tinyint(2) unsigned DEFAULT NULL,
  `betrieb_id` int(10) unsigned DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `committed` tinyint(2) unsigned DEFAULT '0',
  `msg` text,
  `tvalue` varchar(300) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3040 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_send_email`
--

CREATE TABLE IF NOT EXISTS `fs_send_email` (
`id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `mailbox_id` int(10) unsigned NOT NULL,
  `mode` tinyint(4) NOT NULL DEFAULT '1',
  `complete` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(200) DEFAULT NULL,
  `message` text,
  `zeit` datetime DEFAULT NULL,
  `recip` text,
  `attach` varchar(500) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=883 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_stadt`
--

CREATE TABLE IF NOT EXISTS `fs_stadt` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6499 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_stadtteil`
--

CREATE TABLE IF NOT EXISTS `fs_stadtteil` (
`id` int(10) unsigned NOT NULL,
  `stadt_id` int(10) unsigned NOT NULL,
  `name` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_stadt_kennzeichen`
--

CREATE TABLE IF NOT EXISTS `fs_stadt_kennzeichen` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(10) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_stat_abholmengen`
--

CREATE TABLE IF NOT EXISTS `fs_stat_abholmengen` (
  `betrieb_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `abholmenge` decimal(5,1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_theme`
--

CREATE TABLE IF NOT EXISTS `fs_theme` (
`id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `last_post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(260) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `active` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `sticky` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=29123 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_theme_follower`
--

CREATE TABLE IF NOT EXISTS `fs_theme_follower` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `theme_id` int(10) unsigned NOT NULL,
  `infotype` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_theme_post`
--

CREATE TABLE IF NOT EXISTS `fs_theme_post` (
`id` int(10) unsigned NOT NULL,
  `theme_id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `reply_post` int(10) unsigned NOT NULL DEFAULT '0',
  `body` text,
  `time` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=162599 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_upgrade_request`
--

CREATE TABLE IF NOT EXISTS `fs_upgrade_request` (
  `foodsaver_id` int(10) unsigned NOT NULL,
  `rolle` tinyint(3) unsigned NOT NULL,
  `bezirk_id` int(10) unsigned NOT NULL,
  `time` datetime DEFAULT NULL,
  `data` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_user`
--

CREATE TABLE IF NOT EXISTS `fs_user` (
`id` int(10) unsigned NOT NULL,
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
  `token` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=888 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_usernotes_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_usernotes_has_wallpost` (
  `usernotes_id` int(10) unsigned NOT NULL,
  `wallpost_id` int(10) unsigned NOT NULL,
  `usercomment` tinyint(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_user_type`
--

CREATE TABLE IF NOT EXISTS `fs_user_type` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(150) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_wallpost` (
`id` int(10) unsigned NOT NULL,
  `foodsaver_id` int(10) unsigned NOT NULL,
  `body` text,
  `time` datetime DEFAULT NULL,
  `attach` text
) ENGINE=InnoDB AUTO_INCREMENT=69130 DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `basket_has_art`
--
ALTER TABLE `basket_has_art`
 ADD PRIMARY KEY (`basket_id`,`art_id`), ADD KEY `basket_has_art_FKIndex1` (`basket_id`), ADD KEY `basket_has_art_FKIndex2` (`art_id`);

--
-- Indexes for table `basket_has_types`
--
ALTER TABLE `basket_has_types`
 ADD PRIMARY KEY (`basket_id`,`types_id`), ADD KEY `basket_has_types_FKIndex1` (`basket_id`), ADD KEY `basket_has_types_FKIndex2` (`types_id`);

--
-- Indexes for table `fs_abholen`
--
ALTER TABLE `fs_abholen`
 ADD PRIMARY KEY (`foodsaver_id`,`betrieb_id`,`dow`), ADD KEY `foodsaver_has_betrieb_FKIndex1` (`foodsaver_id`), ADD KEY `foodsaver_has_betrieb_FKIndex2` (`betrieb_id`);

--
-- Indexes for table `fs_abholer`
--
ALTER TABLE `fs_abholer`
 ADD PRIMARY KEY (`foodsaver_id`,`betrieb_id`,`date`), ADD KEY `betrieb_id` (`betrieb_id`);

--
-- Indexes for table `fs_abholmengen`
--
ALTER TABLE `fs_abholmengen`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_abholzeiten`
--
ALTER TABLE `fs_abholzeiten`
 ADD PRIMARY KEY (`betrieb_id`,`dow`,`time`);

--
-- Indexes for table `fs_activity`
--
ALTER TABLE `fs_activity`
 ADD PRIMARY KEY (`foodsaver_id`), ADD KEY `zeit` (`zeit`);

--
-- Indexes for table `fs_answer`
--
ALTER TABLE `fs_answer`
 ADD PRIMARY KEY (`id`), ADD KEY `answer_FKIndex1` (`question_id`);

--
-- Indexes for table `fs_apitoken`
--
ALTER TABLE `fs_apitoken`
 ADD KEY `foodsaver_id` (`foodsaver_id`);

--
-- Indexes for table `fs_application_has_wallpost`
--
ALTER TABLE `fs_application_has_wallpost`
 ADD PRIMARY KEY (`application_id`,`wallpost_id`), ADD KEY `application_id` (`application_id`), ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_autokennzeichen`
--
ALTER TABLE `fs_autokennzeichen`
 ADD PRIMARY KEY (`id`), ADD KEY `land_id` (`land_id`);

--
-- Indexes for table `fs_basket`
--
ALTER TABLE `fs_basket`
 ADD PRIMARY KEY (`id`), ADD KEY `basket_FKIndex1` (`foodsaver_id`), ADD KEY `bezirk_id` (`bezirk_id`), ADD KEY `lat` (`lat`,`lon`), ADD KEY `fs_id` (`fs_id`);

--
-- Indexes for table `fs_basket_anfrage`
--
ALTER TABLE `fs_basket_anfrage`
 ADD PRIMARY KEY (`foodsaver_id`,`basket_id`), ADD KEY `foodsaver_has_basket_FKIndex1` (`foodsaver_id`), ADD KEY `foodsaver_has_basket_FKIndex2` (`basket_id`);

--
-- Indexes for table `fs_basket_has_art`
--
ALTER TABLE `fs_basket_has_art`
 ADD PRIMARY KEY (`basket_id`,`art_id`);

--
-- Indexes for table `fs_basket_has_types`
--
ALTER TABLE `fs_basket_has_types`
 ADD PRIMARY KEY (`basket_id`,`types_id`);

--
-- Indexes for table `fs_basket_has_wallpost`
--
ALTER TABLE `fs_basket_has_wallpost`
 ADD PRIMARY KEY (`basket_id`,`wallpost_id`), ADD KEY `basket_has_wallpost_FKIndex1` (`basket_id`), ADD KEY `basket_has_wallpost_FKIndex2` (`wallpost_id`), ADD KEY `basket_id` (`basket_id`), ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_bell`
--
ALTER TABLE `fs_bell`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_betrieb`
--
ALTER TABLE `fs_betrieb`
 ADD PRIMARY KEY (`id`), ADD KEY `betrieb_FKIndex2` (`kette_id`), ADD KEY `betrieb_FKIndex3` (`bezirk_id`), ADD KEY `betrieb_FKIndex5` (`betrieb_status_id`), ADD KEY `plz` (`plz`), ADD KEY `team_status` (`team_status`);

--
-- Indexes for table `fs_betrieb_has_lebensmittel`
--
ALTER TABLE `fs_betrieb_has_lebensmittel`
 ADD PRIMARY KEY (`betrieb_id`,`lebensmittel_id`);

--
-- Indexes for table `fs_betrieb_kategorie`
--
ALTER TABLE `fs_betrieb_kategorie`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_betrieb_notiz`
--
ALTER TABLE `fs_betrieb_notiz`
 ADD PRIMARY KEY (`id`), ADD KEY `betrieb_notitz_FKIndex1` (`betrieb_id`), ADD KEY `betrieb_notiz_FKIndex2` (`foodsaver_id`);

--
-- Indexes for table `fs_betrieb_status`
--
ALTER TABLE `fs_betrieb_status`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_betrieb_team`
--
ALTER TABLE `fs_betrieb_team`
 ADD PRIMARY KEY (`foodsaver_id`,`betrieb_id`), ADD KEY `foodsaver_has_betrieb_FKIndex1` (`foodsaver_id`), ADD KEY `foodsaver_has_betrieb_FKIndex2` (`betrieb_id`);

--
-- Indexes for table `fs_bezirk`
--
ALTER TABLE `fs_bezirk`
 ADD PRIMARY KEY (`id`), ADD KEY `parent_id` (`parent_id`), ADD KEY `type` (`type`), ADD KEY `mailbox_id` (`mailbox_id`), ADD KEY `master` (`master`);

--
-- Indexes for table `fs_bezirk_closure`
--
ALTER TABLE `fs_bezirk_closure`
 ADD KEY `ancestor_id` (`ancestor_id`), ADD KEY `bezirk_id` (`bezirk_id`);

--
-- Indexes for table `fs_bezirk_has_theme`
--
ALTER TABLE `fs_bezirk_has_theme`
 ADD PRIMARY KEY (`theme_id`,`bezirk_id`), ADD KEY `bezirk_id` (`bezirk_id`);

--
-- Indexes for table `fs_bezirk_has_wallpost`
--
ALTER TABLE `fs_bezirk_has_wallpost`
 ADD PRIMARY KEY (`bezirk_id`,`wallpost_id`), ADD KEY `bezirk_id` (`bezirk_id`), ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_blog_entry`
--
ALTER TABLE `fs_blog_entry`
 ADD PRIMARY KEY (`id`), ADD KEY `blog_entry_FKIndex1` (`foodsaver_id`), ADD KEY `blog_entry_FKIndex2` (`bezirk_id`), ADD KEY `active` (`active`);

--
-- Indexes for table `fs_botschafter`
--
ALTER TABLE `fs_botschafter`
 ADD PRIMARY KEY (`foodsaver_id`,`bezirk_id`), ADD KEY `foodsaver_has_bezirk_FKIndex1` (`foodsaver_id`), ADD KEY `foodsaver_has_bezirk_FKIndex2` (`bezirk_id`);

--
-- Indexes for table `fs_buddy`
--
ALTER TABLE `fs_buddy`
 ADD PRIMARY KEY (`foodsaver_id`,`buddy_id`), ADD KEY `buddy_confirmed` (`confirmed`);

--
-- Indexes for table `fs_bundesland`
--
ALTER TABLE `fs_bundesland`
 ADD PRIMARY KEY (`id`), ADD KEY `land_id` (`land_id`);

--
-- Indexes for table `fs_cache`
--
ALTER TABLE `fs_cache`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_contact`
--
ALTER TABLE `fs_contact`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `fs_content`
--
ALTER TABLE `fs_content`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_conversation`
--
ALTER TABLE `fs_conversation`
 ADD PRIMARY KEY (`id`), ADD KEY `conversation_last_fs_id` (`last_foodsaver_id`);

--
-- Indexes for table `fs_document`
--
ALTER TABLE `fs_document`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_email_status`
--
ALTER TABLE `fs_email_status`
 ADD PRIMARY KEY (`email_id`,`foodsaver_id`);

--
-- Indexes for table `fs_event`
--
ALTER TABLE `fs_event`
 ADD PRIMARY KEY (`id`), ADD KEY `event_FKIndex1` (`location_id`), ADD KEY `event_FKIndex2` (`bezirk_id`), ADD KEY `event_FKIndex3` (`foodsaver_id`);

--
-- Indexes for table `fs_event_has_wallpost`
--
ALTER TABLE `fs_event_has_wallpost`
 ADD PRIMARY KEY (`event_id`,`wallpost_id`), ADD KEY `event_id` (`event_id`), ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_event_registration`
--
ALTER TABLE `fs_event_registration`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_event_workshops`
--
ALTER TABLE `fs_event_workshops`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_event_workshop_registration`
--
ALTER TABLE `fs_event_workshop_registration`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uid` (`uid`,`wish`);

--
-- Indexes for table `fs_fairteiler`
--
ALTER TABLE `fs_fairteiler`
 ADD PRIMARY KEY (`id`), ADD KEY `fairteiler_FKIndex1` (`bezirk_id`);

--
-- Indexes for table `fs_fairteiler_follower`
--
ALTER TABLE `fs_fairteiler_follower`
 ADD PRIMARY KEY (`fairteiler_id`,`foodsaver_id`), ADD KEY `fairteiler_verantwortlich_FKIndex1` (`fairteiler_id`), ADD KEY `fairteiler_verantwortlich_FKIndex2` (`foodsaver_id`), ADD KEY `type` (`type`), ADD KEY `infotype` (`infotype`);

--
-- Indexes for table `fs_fairteiler_has_wallpost`
--
ALTER TABLE `fs_fairteiler_has_wallpost`
 ADD PRIMARY KEY (`fairteiler_id`,`wallpost_id`), ADD KEY `fairteiler_has_wallpost_FKIndex1` (`fairteiler_id`), ADD KEY `fairteiler_has_wallpost_FKIndex2` (`wallpost_id`);

--
-- Indexes for table `fs_faq`
--
ALTER TABLE `fs_faq`
 ADD PRIMARY KEY (`id`), ADD KEY `faq_FKIndex1` (`foodsaver_id`), ADD KEY `faq_kategorie_id` (`faq_kategorie_id`);

--
-- Indexes for table `fs_faq_category`
--
ALTER TABLE `fs_faq_category`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_fetchdate`
--
ALTER TABLE `fs_fetchdate`
 ADD PRIMARY KEY (`id`), ADD KEY `fetchdate_FKIndex1` (`betrieb_id`);

--
-- Indexes for table `fs_foodsaver`
--
ALTER TABLE `fs_foodsaver`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`), ADD KEY `foodsaver_FKIndex2` (`bezirk_id`), ADD KEY `foodsaver_FKIndex4` (`autokennzeichen_id`), ADD KEY `plz` (`plz`), ADD KEY `want_new` (`want_new`), ADD KEY `bundesland_id` (`bundesland_id`), ADD KEY `mailbox_id` (`mailbox_id`), ADD KEY `newsletter` (`newsletter`);

--
-- Indexes for table `fs_foodsaver_archive`
--
ALTER TABLE `fs_foodsaver_archive`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`), ADD KEY `foodsaver_FKIndex2` (`bezirk_id`), ADD KEY `foodsaver_FKIndex4` (`autokennzeichen_id`), ADD KEY `plz` (`plz`), ADD KEY `want_new` (`want_new`), ADD KEY `bundesland_id` (`bundesland_id`), ADD KEY `mailbox_id` (`mailbox_id`), ADD KEY `newsletter` (`newsletter`);

--
-- Indexes for table `fs_foodsaver_archive2`
--
ALTER TABLE `fs_foodsaver_archive2`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`), ADD KEY `foodsaver_FKIndex2` (`bezirk_id`), ADD KEY `foodsaver_FKIndex4` (`autokennzeichen_id`), ADD KEY `plz` (`plz`), ADD KEY `want_new` (`want_new`), ADD KEY `bundesland_id` (`bundesland_id`), ADD KEY `newsletter` (`newsletter`);

--
-- Indexes for table `fs_foodsaver_has_bell`
--
ALTER TABLE `fs_foodsaver_has_bell`
 ADD PRIMARY KEY (`foodsaver_id`,`bell_id`), ADD KEY `foodsaver_has_bell_FKIndex1` (`foodsaver_id`), ADD KEY `foodsaver_has_bell_FKIndex2` (`bell_id`);

--
-- Indexes for table `fs_foodsaver_has_bezirk`
--
ALTER TABLE `fs_foodsaver_has_bezirk`
 ADD PRIMARY KEY (`foodsaver_id`,`bezirk_id`), ADD KEY `foodsaver_has_bezirk_FKIndex1` (`foodsaver_id`), ADD KEY `foodsaver_has_bezirk_FKIndex2` (`bezirk_id`);

--
-- Indexes for table `fs_foodsaver_has_contact`
--
ALTER TABLE `fs_foodsaver_has_contact`
 ADD PRIMARY KEY (`foodsaver_id`,`contact_id`);

--
-- Indexes for table `fs_foodsaver_has_conversation`
--
ALTER TABLE `fs_foodsaver_has_conversation`
 ADD PRIMARY KEY (`foodsaver_id`,`conversation_id`), ADD KEY `foodsaver_has_conversation_FKIndex1` (`foodsaver_id`), ADD KEY `foodsaver_has_conversation_FKIndex2` (`conversation_id`), ADD KEY `unread` (`unread`);

--
-- Indexes for table `fs_foodsaver_has_event`
--
ALTER TABLE `fs_foodsaver_has_event`
 ADD PRIMARY KEY (`foodsaver_id`,`event_id`), ADD KEY `foodsaver_has_event_FKIndex1` (`foodsaver_id`), ADD KEY `foodsaver_has_event_FKIndex2` (`event_id`);

--
-- Indexes for table `fs_foodsaver_has_fairteiler`
--
ALTER TABLE `fs_foodsaver_has_fairteiler`
 ADD PRIMARY KEY (`foodsaver_id`,`fairteiler_id`), ADD KEY `foodsaver_has_fairteiler_FKIndex1` (`foodsaver_id`), ADD KEY `foodsaver_has_fairteiler_FKIndex2` (`fairteiler_id`);

--
-- Indexes for table `fs_foodsaver_has_fetchdate`
--
ALTER TABLE `fs_foodsaver_has_fetchdate`
 ADD PRIMARY KEY (`foodsaver_id`,`fetchdate_id`), ADD KEY `foodsaver_has_fetchdate_FKIndex1` (`foodsaver_id`), ADD KEY `foodsaver_has_fetchdate_FKIndex2` (`fetchdate_id`);

--
-- Indexes for table `fs_foodsaver_has_wallpost`
--
ALTER TABLE `fs_foodsaver_has_wallpost`
 ADD PRIMARY KEY (`foodsaver_id`,`wallpost_id`), ADD KEY `foodsaver_has_wallpost_FKIndex1` (`foodsaver_id`), ADD KEY `foodsaver_has_wallpost_FKIndex2` (`wallpost_id`), ADD KEY `foodsaver_id` (`foodsaver_id`), ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_foodsaver_stats`
--
ALTER TABLE `fs_foodsaver_stats`
 ADD PRIMARY KEY (`foodsaver_id`);

--
-- Indexes for table `fs_fsreport_has_wallpost`
--
ALTER TABLE `fs_fsreport_has_wallpost`
 ADD PRIMARY KEY (`fsreport_id`,`wallpost_id`), ADD KEY `fsreport_has_wallpost_FKIndex1` (`fsreport_id`), ADD KEY `fsreport_has_wallpost_FKIndex2` (`wallpost_id`), ADD KEY `fsreport_id` (`fsreport_id`), ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_geoRegion`
--
ALTER TABLE `fs_geoRegion`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_glocke`
--
ALTER TABLE `fs_glocke`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_glocke_read`
--
ALTER TABLE `fs_glocke_read`
 ADD PRIMARY KEY (`glocke_id`,`foodsaver_id`), ADD KEY `glocke_has_foodsaver_FKIndex1` (`glocke_id`), ADD KEY `glocke_has_foodsaver_FKIndex2` (`foodsaver_id`), ADD KEY `glocke_read_unread` (`unread`);

--
-- Indexes for table `fs_ipblock`
--
ALTER TABLE `fs_ipblock`
 ADD PRIMARY KEY (`ip`,`context`);

--
-- Indexes for table `fs_item`
--
ALTER TABLE `fs_item`
 ADD PRIMARY KEY (`id`), ADD KEY `item_FKIndex2` (`user_id`), ADD KEY `item_FKIndex3` (`item_class_id`), ADD KEY `status` (`status`);

--
-- Indexes for table `fs_item_class`
--
ALTER TABLE `fs_item_class`
 ADD PRIMARY KEY (`id`), ADD KEY `item_class_FKIndex1` (`item_type_id`);

--
-- Indexes for table `fs_item_request`
--
ALTER TABLE `fs_item_request`
 ADD PRIMARY KEY (`user_id`,`item_id`), ADD KEY `user_has_item_FKIndex1` (`user_id`), ADD KEY `user_has_item_FKIndex2` (`item_id`);

--
-- Indexes for table `fs_item_track`
--
ALTER TABLE `fs_item_track`
 ADD PRIMARY KEY (`user_id`,`item_id`,`time`), ADD KEY `user_has_item_FKIndex1` (`user_id`), ADD KEY `user_has_item_FKIndex2` (`item_id`);

--
-- Indexes for table `fs_item_type`
--
ALTER TABLE `fs_item_type`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_kette`
--
ALTER TABLE `fs_kette`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_land`
--
ALTER TABLE `fs_land`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_language`
--
ALTER TABLE `fs_language`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_lebensmittel`
--
ALTER TABLE `fs_lebensmittel`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_location`
--
ALTER TABLE `fs_location`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_login`
--
ALTER TABLE `fs_login`
 ADD PRIMARY KEY (`id`), ADD KEY `login_FKIndex1` (`foodsaver_id`);

--
-- Indexes for table `fs_mailbox`
--
ALTER TABLE `fs_mailbox`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email_unique` (`name`), ADD KEY `member` (`member`);

--
-- Indexes for table `fs_mailbox_member`
--
ALTER TABLE `fs_mailbox_member`
 ADD PRIMARY KEY (`mailbox_id`,`foodsaver_id`), ADD KEY `mailbox_has_foodsaver_FKIndex1` (`mailbox_id`), ADD KEY `mailbox_has_foodsaver_FKIndex2` (`foodsaver_id`);

--
-- Indexes for table `fs_mailbox_message`
--
ALTER TABLE `fs_mailbox_message`
 ADD PRIMARY KEY (`id`), ADD KEY `email_message_folder` (`folder`), ADD KEY `mailbox_message_FKIndex1` (`mailbox_id`);

--
-- Indexes for table `fs_mailchange`
--
ALTER TABLE `fs_mailchange`
 ADD PRIMARY KEY (`foodsaver_id`);

--
-- Indexes for table `fs_mail_error`
--
ALTER TABLE `fs_mail_error`
 ADD PRIMARY KEY (`id`), ADD KEY `mail_error_foodsaver_id` (`foodsaver_id`), ADD KEY `mail_error_FKIndex1` (`send_mail_id`);

--
-- Indexes for table `fs_message`
--
ALTER TABLE `fs_message`
 ADD PRIMARY KEY (`id`), ADD KEY `message_recip_id` (`recip_id`), ADD KEY `message_sender_id` (`sender_id`), ADD KEY `recd` (`recd`), ADD KEY `unread` (`unread`);

--
-- Indexes for table `fs_message_tpl`
--
ALTER TABLE `fs_message_tpl`
 ADD PRIMARY KEY (`id`), ADD KEY `message_tpl_FKIndex1` (`language_id`), ADD KEY `name` (`name`);

--
-- Indexes for table `fs_msg`
--
ALTER TABLE `fs_msg`
 ADD PRIMARY KEY (`id`), ADD KEY `message_FKIndex1` (`foodsaver_id`), ADD KEY `message_FKIndex2` (`conversation_id`);

--
-- Indexes for table `fs_mumbleuser`
--
ALTER TABLE `fs_mumbleuser`
 ADD PRIMARY KEY (`foodsaver_id`), ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `fs_partnerschaft`
--
ALTER TABLE `fs_partnerschaft`
 ADD PRIMARY KEY (`foodsaver_id`,`partner_id`,`form`);

--
-- Indexes for table `fs_pass_gen`
--
ALTER TABLE `fs_pass_gen`
 ADD PRIMARY KEY (`foodsaver_id`,`date`);

--
-- Indexes for table `fs_pass_request`
--
ALTER TABLE `fs_pass_request`
 ADD PRIMARY KEY (`foodsaver_id`);

--
-- Indexes for table `fs_plz`
--
ALTER TABLE `fs_plz`
 ADD PRIMARY KEY (`plz`), ADD KEY `plz_region_id2` (`geoRegion_id`), ADD KEY `plz_bundesland_id` (`bundesland_id`), ADD KEY `plz_stadtkennzeichen` (`stadt_kennzeichen_id`), ADD KEY `plz_stadt_id` (`stadt_id`), ADD KEY `land_id` (`land_id`);

--
-- Indexes for table `fs_pushqueue`
--
ALTER TABLE `fs_pushqueue`
 ADD PRIMARY KEY (`id`), ADD KEY `pushqueue_status` (`status`), ADD KEY `pushqueue_message_id` (`message_id`);

--
-- Indexes for table `fs_question`
--
ALTER TABLE `fs_question`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_question_has_quiz`
--
ALTER TABLE `fs_question_has_quiz`
 ADD PRIMARY KEY (`question_id`,`quiz_id`), ADD KEY `question_has_quiz_FKIndex1` (`question_id`), ADD KEY `question_has_quiz_FKIndex2` (`quiz_id`);

--
-- Indexes for table `fs_question_has_wallpost`
--
ALTER TABLE `fs_question_has_wallpost`
 ADD PRIMARY KEY (`question_id`,`wallpost_id`), ADD KEY `question_has_wallpost_FKIndex1` (`question_id`), ADD KEY `question_has_wallpost_FKIndex2` (`wallpost_id`), ADD KEY `question_id` (`question_id`), ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_quiz`
--
ALTER TABLE `fs_quiz`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_quiz_session`
--
ALTER TABLE `fs_quiz_session`
 ADD PRIMARY KEY (`id`), ADD KEY `quiz_result_FKIndex1` (`quiz_id`), ADD KEY `quiz_result_FKIndex2` (`foodsaver_id`);

--
-- Indexes for table `fs_rating`
--
ALTER TABLE `fs_rating`
 ADD PRIMARY KEY (`foodsaver_id`,`rater_id`,`ratingtype`), ADD KEY `fk_foodsaver_has_foodsaver_foodsaver1_idx` (`rater_id`), ADD KEY `fk_foodsaver_has_foodsaver_foodsaver_idx` (`foodsaver_id`);

--
-- Indexes for table `fs_region`
--
ALTER TABLE `fs_region`
 ADD PRIMARY KEY (`id`), ADD KEY `type` (`type`);

--
-- Indexes for table `fs_report`
--
ALTER TABLE `fs_report`
 ADD PRIMARY KEY (`id`), ADD KEY `report_FKIndex1` (`foodsaver_id`), ADD KEY `report_reporter` (`reporter_id`), ADD KEY `report_betrieb` (`betrieb_id`);

--
-- Indexes for table `fs_send_email`
--
ALTER TABLE `fs_send_email`
 ADD PRIMARY KEY (`id`), ADD KEY `send_email_FKIndex1` (`foodsaver_id`);

--
-- Indexes for table `fs_stadt`
--
ALTER TABLE `fs_stadt`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_stadtteil`
--
ALTER TABLE `fs_stadtteil`
 ADD PRIMARY KEY (`id`), ADD KEY `stadtteil_FKIndex1` (`stadt_id`);

--
-- Indexes for table `fs_stadt_kennzeichen`
--
ALTER TABLE `fs_stadt_kennzeichen`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_stat_abholmengen`
--
ALTER TABLE `fs_stat_abholmengen`
 ADD UNIQUE KEY `betrieb_id` (`betrieb_id`,`date`);

--
-- Indexes for table `fs_theme`
--
ALTER TABLE `fs_theme`
 ADD PRIMARY KEY (`id`), ADD KEY `theme_FKIndex1` (`foodsaver_id`), ADD KEY `last_post_id` (`last_post_id`), ADD KEY `active` (`active`);

--
-- Indexes for table `fs_theme_follower`
--
ALTER TABLE `fs_theme_follower`
 ADD PRIMARY KEY (`foodsaver_id`,`theme_id`), ADD KEY `infotype` (`infotype`);

--
-- Indexes for table `fs_theme_post`
--
ALTER TABLE `fs_theme_post`
 ADD PRIMARY KEY (`id`), ADD KEY `theme_post_FKIndex1` (`foodsaver_id`), ADD KEY `theme_post_FKIndex2` (`theme_id`), ADD KEY `reply_post` (`reply_post`);

--
-- Indexes for table `fs_upgrade_request`
--
ALTER TABLE `fs_upgrade_request`
 ADD PRIMARY KEY (`foodsaver_id`,`rolle`,`bezirk_id`);

--
-- Indexes for table `fs_user`
--
ALTER TABLE `fs_user`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`), ADD KEY `user_FKIndex1` (`user_type_id`), ADD KEY `fsid` (`fsid`);

--
-- Indexes for table `fs_usernotes_has_wallpost`
--
ALTER TABLE `fs_usernotes_has_wallpost`
 ADD PRIMARY KEY (`usernotes_id`,`wallpost_id`), ADD KEY `usernotes_has_wallpost_FKIndex1` (`usernotes_id`), ADD KEY `usernotes_has_wallpost_FKIndex2` (`wallpost_id`), ADD KEY `usernotes_id` (`usernotes_id`), ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_user_type`
--
ALTER TABLE `fs_user_type`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_wallpost`
--
ALTER TABLE `fs_wallpost`
 ADD PRIMARY KEY (`id`), ADD KEY `wallpost_FKIndex1` (`foodsaver_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fs_answer`
--
ALTER TABLE `fs_answer`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=464;
--
-- AUTO_INCREMENT for table `fs_autokennzeichen`
--
ALTER TABLE `fs_autokennzeichen`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=581;
--
-- AUTO_INCREMENT for table `fs_basket`
--
ALTER TABLE `fs_basket`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=38738;
--
-- AUTO_INCREMENT for table `fs_bell`
--
ALTER TABLE `fs_bell`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=208493;
--
-- AUTO_INCREMENT for table `fs_betrieb`
--
ALTER TABLE `fs_betrieb`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15906;
--
-- AUTO_INCREMENT for table `fs_betrieb_kategorie`
--
ALTER TABLE `fs_betrieb_kategorie`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=266;
--
-- AUTO_INCREMENT for table `fs_betrieb_notiz`
--
ALTER TABLE `fs_betrieb_notiz`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=397095;
--
-- AUTO_INCREMENT for table `fs_betrieb_status`
--
ALTER TABLE `fs_betrieb_status`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `fs_bezirk`
--
ALTER TABLE `fs_bezirk`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1330;
--
-- AUTO_INCREMENT for table `fs_blog_entry`
--
ALTER TABLE `fs_blog_entry`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=220;
--
-- AUTO_INCREMENT for table `fs_bundesland`
--
ALTER TABLE `fs_bundesland`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `fs_contact`
--
ALTER TABLE `fs_contact`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=18626;
--
-- AUTO_INCREMENT for table `fs_content`
--
ALTER TABLE `fs_content`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=50;
--
-- AUTO_INCREMENT for table `fs_conversation`
--
ALTER TABLE `fs_conversation`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=682361;
--
-- AUTO_INCREMENT for table `fs_document`
--
ALTER TABLE `fs_document`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=26;
--
-- AUTO_INCREMENT for table `fs_event`
--
ALTER TABLE `fs_event`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4126;
--
-- AUTO_INCREMENT for table `fs_event_registration`
--
ALTER TABLE `fs_event_registration`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=994;
--
-- AUTO_INCREMENT for table `fs_event_workshops`
--
ALTER TABLE `fs_event_workshops`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=47;
--
-- AUTO_INCREMENT for table `fs_event_workshop_registration`
--
ALTER TABLE `fs_event_workshop_registration`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2921;
--
-- AUTO_INCREMENT for table `fs_fairteiler`
--
ALTER TABLE `fs_fairteiler`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=707;
--
-- AUTO_INCREMENT for table `fs_faq`
--
ALTER TABLE `fs_faq`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `fs_faq_category`
--
ALTER TABLE `fs_faq_category`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `fs_fetchdate`
--
ALTER TABLE `fs_fetchdate`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=29601;
--
-- AUTO_INCREMENT for table `fs_foodsaver`
--
ALTER TABLE `fs_foodsaver`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=151030;
--
-- AUTO_INCREMENT for table `fs_foodsaver_archive`
--
ALTER TABLE `fs_foodsaver_archive`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=150886;
--
-- AUTO_INCREMENT for table `fs_geoRegion`
--
ALTER TABLE `fs_geoRegion`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=445;
--
-- AUTO_INCREMENT for table `fs_glocke`
--
ALTER TABLE `fs_glocke`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=425246;
--
-- AUTO_INCREMENT for table `fs_item`
--
ALTER TABLE `fs_item`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1112;
--
-- AUTO_INCREMENT for table `fs_item_class`
--
ALTER TABLE `fs_item_class`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `fs_item_type`
--
ALTER TABLE `fs_item_type`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `fs_kette`
--
ALTER TABLE `fs_kette`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=398;
--
-- AUTO_INCREMENT for table `fs_land`
--
ALTER TABLE `fs_land`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fs_language`
--
ALTER TABLE `fs_language`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `fs_lebensmittel`
--
ALTER TABLE `fs_lebensmittel`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `fs_location`
--
ALTER TABLE `fs_location`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4284;
--
-- AUTO_INCREMENT for table `fs_login`
--
ALTER TABLE `fs_login`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6138537;
--
-- AUTO_INCREMENT for table `fs_mailbox`
--
ALTER TABLE `fs_mailbox`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=28458;
--
-- AUTO_INCREMENT for table `fs_mailbox_message`
--
ALTER TABLE `fs_mailbox_message`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=102410;
--
-- AUTO_INCREMENT for table `fs_mail_error`
--
ALTER TABLE `fs_mail_error`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fs_message`
--
ALTER TABLE `fs_message`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2570714;
--
-- AUTO_INCREMENT for table `fs_message_tpl`
--
ALTER TABLE `fs_message_tpl`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT for table `fs_msg`
--
ALTER TABLE `fs_msg`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4362369;
--
-- AUTO_INCREMENT for table `fs_partnerschaft`
--
ALTER TABLE `fs_partnerschaft`
MODIFY `foodsaver_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8765;
--
-- AUTO_INCREMENT for table `fs_pushqueue`
--
ALTER TABLE `fs_pushqueue`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=35328;
--
-- AUTO_INCREMENT for table `fs_question`
--
ALTER TABLE `fs_question`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=115;
--
-- AUTO_INCREMENT for table `fs_quiz`
--
ALTER TABLE `fs_quiz`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `fs_quiz_session`
--
ALTER TABLE `fs_quiz_session`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=44736;
--
-- AUTO_INCREMENT for table `fs_region`
--
ALTER TABLE `fs_region`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fs_report`
--
ALTER TABLE `fs_report`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3040;
--
-- AUTO_INCREMENT for table `fs_send_email`
--
ALTER TABLE `fs_send_email`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=883;
--
-- AUTO_INCREMENT for table `fs_stadt`
--
ALTER TABLE `fs_stadt`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6499;
--
-- AUTO_INCREMENT for table `fs_stadtteil`
--
ALTER TABLE `fs_stadtteil`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fs_stadt_kennzeichen`
--
ALTER TABLE `fs_stadt_kennzeichen`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `fs_theme`
--
ALTER TABLE `fs_theme`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=29123;
--
-- AUTO_INCREMENT for table `fs_theme_post`
--
ALTER TABLE `fs_theme_post`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=162599;
--
-- AUTO_INCREMENT for table `fs_user`
--
ALTER TABLE `fs_user`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=888;
--
-- AUTO_INCREMENT for table `fs_user_type`
--
ALTER TABLE `fs_user_type`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `fs_wallpost`
--
ALTER TABLE `fs_wallpost`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=69130;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `fs_bezirk`
--
ALTER TABLE `fs_bezirk`
ADD CONSTRAINT `fs_bezirk_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `fs_bezirk` (`id`) ON UPDATE CASCADE,
ADD CONSTRAINT `fs_bezirk_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `fs_bezirk` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `fs_bezirk_closure`
--
ALTER TABLE `fs_bezirk_closure`
ADD CONSTRAINT `fs_bezirk_closure_ibfk_1` FOREIGN KEY (`bezirk_id`) REFERENCES `fs_bezirk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `fs_bezirk_closure_ibfk_2` FOREIGN KEY (`ancestor_id`) REFERENCES `fs_bezirk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `fs_bezirk_closure_ibfk_3` FOREIGN KEY (`bezirk_id`) REFERENCES `fs_bezirk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `fs_bezirk_closure_ibfk_4` FOREIGN KEY (`ancestor_id`) REFERENCES `fs_bezirk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

