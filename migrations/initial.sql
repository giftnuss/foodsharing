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


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lmr_prod`
--

-- --------------------------------------------------------

--
-- Table structure for table `basket_has_art`
--

CREATE TABLE IF NOT EXISTS `basket_has_art` (
  `basket_id` INT(10) UNSIGNED NOT NULL,
  `art_id`    INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `basket_has_types`
--

CREATE TABLE IF NOT EXISTS `basket_has_types` (
  `basket_id` INT(10) UNSIGNED NOT NULL,
  `types_id`  INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_abholen`
--

CREATE TABLE IF NOT EXISTS `fs_abholen` (
  `foodsaver_id` INT(10) UNSIGNED    NOT NULL,
  `betrieb_id`   INT(10) UNSIGNED    NOT NULL,
  `dow`          TINYINT(3) UNSIGNED NOT NULL,
  `time`         TIME DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_abholer`
--

CREATE TABLE IF NOT EXISTS `fs_abholer` (
  `foodsaver_id` INT(10) UNSIGNED    NOT NULL,
  `betrieb_id`   INT(10) UNSIGNED    NOT NULL,
  `date`         DATETIME            NOT NULL,
  `confirmed`    TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_abholmengen`
--

CREATE TABLE IF NOT EXISTS `fs_abholmengen` (
  `id`    INT(11)       NOT NULL,
  `menge` DECIMAL(5, 1) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_abholzeiten`
--

CREATE TABLE IF NOT EXISTS `fs_abholzeiten` (
  `betrieb_id` INT(10) UNSIGNED    NOT NULL,
  `dow`        TINYINT(3) UNSIGNED NOT NULL,
  `time`       TIME                NOT NULL DEFAULT '00:00:00',
  `fetcher`    TINYINT(3) UNSIGNED NOT NULL DEFAULT '4'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_activity`
--

CREATE TABLE IF NOT EXISTS `fs_activity` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `zeit`         DATETIME DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_answer`
--

CREATE TABLE IF NOT EXISTS `fs_answer` (
  `id`          INT(10) UNSIGNED NOT NULL,
  `question_id` INT(10) UNSIGNED NOT NULL,
  `text`        TEXT,
  `explanation` TEXT             NOT NULL,
  `right`       TINYINT(1) UNSIGNED DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 464
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_apitoken`
--

CREATE TABLE IF NOT EXISTS `fs_apitoken` (
  `foodsaver_id` INT(11)      NOT NULL,
  `token`        VARCHAR(255) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_application_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_application_has_wallpost` (
  `application_id` INT(10) UNSIGNED NOT NULL,
  `wallpost_id`    INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_autokennzeichen`
--

CREATE TABLE IF NOT EXISTS `fs_autokennzeichen` (
  `id`      INT(10) UNSIGNED NOT NULL,
  `land_id` INT(10) UNSIGNED NOT NULL,
  `name`    VARCHAR(5)   DEFAULT NULL,
  `title`   VARCHAR(100) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 581
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_basket`
--

CREATE TABLE IF NOT EXISTS `fs_basket` (
  `id`            INT(10) UNSIGNED NOT NULL,
  `foodsaver_id`  INT(10) UNSIGNED NOT NULL,
  `status`        TINYINT(2) UNSIGNED       DEFAULT NULL,
  `time`          DATETIME                  DEFAULT NULL,
  `until`         DATE             NOT NULL,
  `fetchtime`     DATETIME                  DEFAULT NULL,
  `description`   TEXT,
  `picture`       VARCHAR(150)              DEFAULT NULL,
  `tel`           VARCHAR(50)      NOT NULL DEFAULT '',
  `handy`         VARCHAR(50)      NOT NULL DEFAULT '',
  `contact_type`  VARCHAR(20)      NOT NULL DEFAULT '1',
  `location_type` TINYINT(2) UNSIGNED       DEFAULT NULL,
  `weight`        FLOAT                     DEFAULT NULL,
  `lat`           FLOAT(10, 6)     NOT NULL DEFAULT '0.000000',
  `lon`           FLOAT(10, 6)     NOT NULL DEFAULT '0.000000',
  `bezirk_id`     INT(10) UNSIGNED NOT NULL,
  `fs_id`         INT(10)          NOT NULL DEFAULT '0',
  `appost`        TINYINT(2)       NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 38738
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_basket_anfrage`
--

CREATE TABLE IF NOT EXISTS `fs_basket_anfrage` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `basket_id`    INT(10) UNSIGNED NOT NULL,
  `status`       TINYINT(2) UNSIGNED       DEFAULT NULL,
  `time`         DATETIME         NOT NULL,
  `appost`       TINYINT(2)       NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_basket_has_art`
--

CREATE TABLE IF NOT EXISTS `fs_basket_has_art` (
  `basket_id` INT(10) UNSIGNED NOT NULL,
  `art_id`    INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_basket_has_types`
--

CREATE TABLE IF NOT EXISTS `fs_basket_has_types` (
  `basket_id` INT(10) UNSIGNED NOT NULL,
  `types_id`  INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_basket_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_basket_has_wallpost` (
  `basket_id`   INT(10) UNSIGNED NOT NULL,
  `wallpost_id` INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bell`
--

CREATE TABLE IF NOT EXISTS `fs_bell` (
  `id`         INT(10) UNSIGNED    NOT NULL,
  `name`       VARCHAR(50)                  DEFAULT NULL,
  `body`       VARCHAR(50)                  DEFAULT NULL,
  `vars`       TEXT,
  `attr`       VARCHAR(500)                 DEFAULT NULL,
  `icon`       VARCHAR(150)                 DEFAULT NULL,
  `identifier` VARCHAR(40)                  DEFAULT NULL,
  `time`       DATETIME            NOT NULL,
  `closeable`  TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 208493
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb` (
  `id`                       INT(10) UNSIGNED NOT NULL,
  `betrieb_status_id`        INT(10) UNSIGNED NOT NULL,
  `bezirk_id`                INT(10) UNSIGNED NOT NULL,
  `added`                    DATE             NOT NULL,
  `plz`                      VARCHAR(5)       NOT NULL,
  `stadt`                    VARCHAR(50)      NOT NULL,
  `lat`                      VARCHAR(20)      NOT NULL,
  `lon`                      VARCHAR(20)      NOT NULL,
  `kette_id`                 INT(10) UNSIGNED NOT NULL,
  `betrieb_kategorie_id`     INT(10) UNSIGNED NOT NULL,
  `name`                     VARCHAR(120)              DEFAULT NULL,
  `str`                      VARCHAR(120)              DEFAULT NULL,
  `hsnr`                     VARCHAR(20)               DEFAULT NULL,
  `status_date`              DATE                      DEFAULT NULL,
  `status`                   TINYINT(3) UNSIGNED       DEFAULT NULL,
  `ansprechpartner`          VARCHAR(60)               DEFAULT NULL,
  `telefon`                  VARCHAR(50)               DEFAULT NULL,
  `fax`                      VARCHAR(50)               DEFAULT NULL,
  `email`                    VARCHAR(60)               DEFAULT NULL,
  `begin`                    DATE             NOT NULL,
  `besonderheiten`           TEXT             NOT NULL,
  `public_info`              VARCHAR(200)     NOT NULL,
  `public_time`              TINYINT(2)       NOT NULL,
  `ueberzeugungsarbeit`      TINYINT(4)       NOT NULL,
  `presse`                   TINYINT(4)       NOT NULL,
  `sticker`                  TINYINT(4)       NOT NULL,
  `abholmenge`               TINYINT(4)       NOT NULL,
  `team_status`              TINYINT(2)       NOT NULL DEFAULT '1'
  COMMENT '0 = Team Voll; 1 = Es werden noch Helfer gesucht; 2 = Es werden dringend Helfer gesucht',
  `prefetchtime`             INT(10) UNSIGNED NOT NULL DEFAULT '1209600',
  `team_conversation_id`     INT(10) UNSIGNED          DEFAULT NULL,
  `springer_conversation_id` INT(10) UNSIGNED          DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 15906
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb_has_lebensmittel`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_has_lebensmittel` (
  `betrieb_id`      INT(10) UNSIGNED NOT NULL,
  `lebensmittel_id` INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb_kategorie`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_kategorie` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(50) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 266
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb_notiz`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_notiz` (
  `id`           INT(10) UNSIGNED    NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED    NOT NULL,
  `betrieb_id`   INT(10) UNSIGNED    NOT NULL,
  `milestone`    TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `text`         TEXT,
  `zeit`         DATETIME                     DEFAULT NULL,
  `last`         TINYINT(4)          NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 397095
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb_status`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_status` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(100) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 7
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_betrieb_team`
--

CREATE TABLE IF NOT EXISTS `fs_betrieb_team` (
  `foodsaver_id`     INT(10) UNSIGNED NOT NULL,
  `betrieb_id`       INT(10) UNSIGNED NOT NULL,
  `verantwortlich`   TINYINT(3) UNSIGNED       DEFAULT '0',
  `active`           INT(11)          NOT NULL DEFAULT '0',
  `stat_last_update` DATETIME         NOT NULL,
  `stat_fetchcount`  INT(10) UNSIGNED NOT NULL,
  `stat_first_fetch` DATE             NOT NULL,
  `stat_last_fetch`  DATETIME         NOT NULL,
  `stat_add_date`    DATE             NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bezirk`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk` (
  `id`                   INT(10) UNSIGNED        NOT NULL,
  `parent_id`            INT(11) UNSIGNED                 DEFAULT '0',
  `has_children`         TINYINT(4)              NOT NULL,
  `type`                 TINYINT(4)              NOT NULL DEFAULT '1',
  `teaser`               TEXT                    NOT NULL,
  `desc`                 TEXT                    NOT NULL,
  `photo`                VARCHAR(200)            NOT NULL,
  `master`               INT(10) UNSIGNED        NOT NULL DEFAULT '0',
  `mailbox_id`           INT(10) UNSIGNED        NOT NULL DEFAULT '0',
  `name`                 VARCHAR(50)                      DEFAULT NULL,
  `email`                VARCHAR(120)            NOT NULL,
  `email_pass`           VARCHAR(50)             NOT NULL,
  `email_name`           VARCHAR(100)            NOT NULL,
  `apply_type`           TINYINT(2)              NOT NULL DEFAULT '2',
  `banana_count`         TINYINT(3)              NOT NULL DEFAULT '0',
  `fetch_count`          TINYINT(3)              NOT NULL DEFAULT '0',
  `week_num`             TINYINT(3)              NOT NULL DEFAULT '0',
  `report_num`           TINYINT(3)              NOT NULL,
  `stat_last_update`     DATETIME                NOT NULL,
  `stat_fetchweight`     DECIMAL(10, 2) UNSIGNED NOT NULL,
  `stat_fetchcount`      INT(10) UNSIGNED        NOT NULL,
  `stat_postcount`       INT(10) UNSIGNED        NOT NULL,
  `stat_betriebcount`    INT(7) UNSIGNED         NOT NULL,
  `stat_korpcount`       INT(7) UNSIGNED         NOT NULL,
  `stat_botcount`        INT(7) UNSIGNED         NOT NULL,
  `stat_fscount`         INT(7) UNSIGNED         NOT NULL,
  `stat_fairteilercount` INT(7) UNSIGNED         NOT NULL,
  `conversation_id`      INT(10) UNSIGNED        NOT NULL DEFAULT '0',
  `moderated`            TINYINT(4)              NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 1330
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bezirk_closure`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk_closure` (
  `bezirk_id`   INT(10) UNSIGNED NOT NULL,
  `ancestor_id` INT(10) UNSIGNED NOT NULL,
  `depth`       INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bezirk_has_theme`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk_has_theme` (
  `theme_id`  INT(10) UNSIGNED    NOT NULL,
  `bezirk_id` INT(10) UNSIGNED    NOT NULL,
  `bot_theme` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bezirk_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_bezirk_has_wallpost` (
  `bezirk_id`   INT(10) UNSIGNED NOT NULL,
  `wallpost_id` INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_blog_entry`
--

CREATE TABLE IF NOT EXISTS `fs_blog_entry` (
  `id`           INT(10) UNSIGNED    NOT NULL,
  `bezirk_id`    INT(10) UNSIGNED    NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED    NOT NULL,
  `active`       TINYINT(3) UNSIGNED NOT NULL,
  `name`         VARCHAR(100) DEFAULT NULL,
  `teaser`       VARCHAR(500) DEFAULT NULL,
  `body`         TEXT,
  `time`         DATETIME     DEFAULT NULL,
  `picture`      VARCHAR(150)        NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 220
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_botschafter`
--

CREATE TABLE IF NOT EXISTS `fs_botschafter` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `bezirk_id`    INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_buddy`
--

CREATE TABLE IF NOT EXISTS `fs_buddy` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `buddy_id`     INT(10) UNSIGNED NOT NULL,
  `confirmed`    TINYINT(1) UNSIGNED DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bundesland`
--

CREATE TABLE IF NOT EXISTS `fs_bundesland` (
  `id`      INT(10) UNSIGNED NOT NULL,
  `land_id` INT(10) UNSIGNED NOT NULL,
  `name`    VARCHAR(160) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 17
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_cache`
--

CREATE TABLE IF NOT EXISTS `fs_cache` (
  `id`    VARCHAR(50) NOT NULL,
  `value` TEXT        NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_contact`
--

CREATE TABLE IF NOT EXISTS `fs_contact` (
  `id`    INT(11) NOT NULL,
  `name`  VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(155) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 18626
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_content`
--

CREATE TABLE IF NOT EXISTS `fs_content` (
  `id`       INT(10) UNSIGNED NOT NULL,
  `name`     VARCHAR(20)  DEFAULT NULL,
  `title`    VARCHAR(120) DEFAULT NULL,
  `body`     TEXT,
  `last_mod` DATETIME     DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 50
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_conversation`
--

CREATE TABLE IF NOT EXISTS `fs_conversation` (
  `id`                 INT(10) UNSIGNED NOT NULL,
  `locked`             TINYINT(1)       NOT NULL DEFAULT '0',
  `name`               VARCHAR(40)      NOT NULL,
  `start`              DATETIME                  DEFAULT NULL,
  `last`               DATETIME                  DEFAULT NULL,
  `last_foodsaver_id`  INT(10) UNSIGNED          DEFAULT NULL,
  `start_foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `last_message_id`    INT(10) UNSIGNED NOT NULL,
  `last_message`       TEXT             NOT NULL,
  `member`             TEXT             NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 682361
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_document`
--

CREATE TABLE IF NOT EXISTS `fs_document` (
  `id`    INT(10) UNSIGNED NOT NULL,
  `name`  VARCHAR(50)         DEFAULT NULL,
  `file`  VARCHAR(300)        DEFAULT NULL,
  `body`  TEXT             NOT NULL,
  `rolle` TINYINT(3) UNSIGNED DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 26
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_email_blacklist`
--

CREATE TABLE IF NOT EXISTS `fs_email_blacklist` (
  `email`  VARCHAR(255) NOT NULL,
  `since`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` TEXT         NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_email_status`
--

CREATE TABLE IF NOT EXISTS `fs_email_status` (
  `email_id`     INT(10) UNSIGNED NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `status`       TINYINT(3) UNSIGNED DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_event`
--

CREATE TABLE IF NOT EXISTS `fs_event` (
  `id`           INT(10) UNSIGNED NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `bezirk_id`    INT(10) UNSIGNED NOT NULL,
  `location_id`  INT(10) UNSIGNED NOT NULL,
  `public`       TINYINT(1)       NOT NULL DEFAULT '0',
  `name`         VARCHAR(200)              DEFAULT NULL,
  `start`        DATETIME         NOT NULL,
  `end`          DATETIME         NOT NULL,
  `description`  TEXT,
  `bot`          TINYINT(2) UNSIGNED       DEFAULT '0',
  `online`       TINYINT(2) UNSIGNED       DEFAULT '0'
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 4126
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_event_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_event_has_wallpost` (
  `event_id`    INT(10) UNSIGNED NOT NULL,
  `wallpost_id` INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_event_registration`
--

CREATE TABLE IF NOT EXISTS `fs_event_registration` (
  `id`                        INT(11)                  NOT NULL,
  `name`                      VARCHAR(255)             NOT NULL,
  `geb_datum`                 DATE                     NOT NULL,
  `ort`                       VARCHAR(255)             NOT NULL,
  `email`                     VARCHAR(255)             NOT NULL,
  `phone`                     VARCHAR(100)             NOT NULL,
  `take_part`                 SET ('0', '1', '2', '3') NOT NULL,
  `sleep_at`                  SET ('0', '1', '2')      NOT NULL,
  `sleep_slots`               INT(11)                  NOT NULL,
  `sleep_need`                TINYINT(4)               NOT NULL,
  `languages`                 SET ('0', '1', '2', '3') NOT NULL,
  `nutrition`                 TINYINT(4)               NOT NULL,
  `special_nutrition`         TEXT                     NOT NULL,
  `other_languages`           TEXT                     NOT NULL,
  `translation_necessary`     TINYINT(4)               NOT NULL,
  `already_foodsaver`         TINYINT(4)               NOT NULL,
  `childcare`                 TEXT                     NOT NULL,
  `comments`                  TEXT                     NOT NULL,
  `foodsaver_id`              INT(11)                           DEFAULT NULL,
  `ip`                        VARCHAR(60)              NOT NULL,
  `signup_date`               TIMESTAMP                NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `signup_lang`               VARCHAR(20)              NOT NULL,
  `emailvalid`                TINYINT(4)               NOT NULL,
  `languages_translate`       SET ('0', '1', '2', '3') NOT NULL,
  `other_languages_translate` TEXT                     NOT NULL,
  `on_place`                  TINYINT(4)               NOT NULL,
  `admin_comment`             TEXT,
  `available_thursday`        SET ('0')                         DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 994
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_event_workshops`
--

CREATE TABLE IF NOT EXISTS `fs_event_workshops` (
  `id`                 INT(11)      NOT NULL,
  `name`               VARCHAR(255) NOT NULL,
  `start`              TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `duration`           INT(11)      NOT NULL,
  `allowed_attendants` INT(11)      NOT NULL,
  `name_en`            VARCHAR(255) NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 47
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_event_workshop_registration`
--

CREATE TABLE IF NOT EXISTS `fs_event_workshop_registration` (
  `wid`       INT(11)     NOT NULL DEFAULT '-1',
  `uid`       INT(11)     NOT NULL,
  `wish`      SMALLINT(6) NOT NULL,
  `confirmed` TINYINT(4)  NOT NULL,
  `id`        INT(11)     NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 2921
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_fairteiler`
--

CREATE TABLE IF NOT EXISTS `fs_fairteiler` (
  `id`            INT(10) UNSIGNED NOT NULL,
  `bezirk_id`     INT(10) UNSIGNED NOT NULL,
  `name`          VARCHAR(260)        DEFAULT NULL,
  `picture`       VARCHAR(100)     NOT NULL,
  `status`        TINYINT(3) UNSIGNED DEFAULT NULL,
  `desc`          TEXT,
  `anschrift`     VARCHAR(260)        DEFAULT NULL,
  `plz`           VARCHAR(5)          DEFAULT NULL,
  `ort`           VARCHAR(100)        DEFAULT NULL,
  `lat`           VARCHAR(100)        DEFAULT NULL,
  `lon`           VARCHAR(100)        DEFAULT NULL,
  `add_date`      DATE                DEFAULT NULL,
  `add_foodsaver` INT(10) UNSIGNED    DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 707
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_fairteiler_follower`
--

CREATE TABLE IF NOT EXISTS `fs_fairteiler_follower` (
  `fairteiler_id` INT(10) UNSIGNED    NOT NULL,
  `foodsaver_id`  INT(10) UNSIGNED    NOT NULL,
  `type`          TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
  `infotype`      TINYINT(3) UNSIGNED NOT NULL DEFAULT '1'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_fairteiler_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_fairteiler_has_wallpost` (
  `fairteiler_id` INT(10) UNSIGNED NOT NULL,
  `wallpost_id`   INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_faq`
--

CREATE TABLE IF NOT EXISTS `fs_faq` (
  `id`               INT(10) UNSIGNED NOT NULL,
  `foodsaver_id`     INT(10) UNSIGNED NOT NULL,
  `faq_kategorie_id` INT(10) UNSIGNED NOT NULL,
  `name`             VARCHAR(500) DEFAULT NULL,
  `answer`           TEXT
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 17
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_faq_category`
--

CREATE TABLE IF NOT EXISTS `fs_faq_category` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(50) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 8
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_fetchdate`
--

CREATE TABLE IF NOT EXISTS `fs_fetchdate` (
  `id`           INT(10) UNSIGNED NOT NULL,
  `betrieb_id`   INT(10) UNSIGNED NOT NULL,
  `time`         DATETIME            DEFAULT NULL,
  `fetchercount` TINYINT(3) UNSIGNED DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 29601
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver` (
  `id`                 INT(10) UNSIGNED       NOT NULL,
  `autokennzeichen_id` INT(10) UNSIGNED       NOT NULL,
  `bezirk_id`          INT(10) UNSIGNED       NOT NULL,
  `position`           VARCHAR(255)           NOT NULL DEFAULT '',
  `verified`           TINYINT(3) UNSIGNED    NOT NULL DEFAULT '0',
  `last_pass`          DATETIME               NOT NULL,
  `new_bezirk`         VARCHAR(120)           NOT NULL,
  `want_new`           TINYINT(4)             NOT NULL DEFAULT '0',
  `mailbox_id`         INT(10) UNSIGNED       NOT NULL DEFAULT '0',
  `rolle`              TINYINT(4)             NOT NULL,
  `type`               TINYINT(4)                      DEFAULT '0',
  `plz`                VARCHAR(5)             NOT NULL,
  `stadt`              VARCHAR(50)            NOT NULL,
  `bundesland_id`      INT(11)                NOT NULL,
  `lat`                VARCHAR(20)            NOT NULL,
  `lon`                VARCHAR(20)            NOT NULL,
  `photo`              VARCHAR(50)            NOT NULL,
  `photo_public`       TINYINT(4)             NOT NULL DEFAULT '0',
  `email`              VARCHAR(120)                    DEFAULT NULL,
  `passwd`             VARCHAR(32)                     DEFAULT NULL,
  `name`               VARCHAR(120)                    DEFAULT NULL,
  `admin`              TINYINT(3) UNSIGNED             DEFAULT NULL,
  `nachname`           VARCHAR(120)                    DEFAULT NULL,
  `anschrift`          VARCHAR(120)                    DEFAULT NULL,
  `telefon`            VARCHAR(30)                     DEFAULT NULL,
  `tox`                VARCHAR(255)           NOT NULL DEFAULT '',
  `homepage`           VARCHAR(255)           NOT NULL DEFAULT '',
  `github`             VARCHAR(255)           NOT NULL DEFAULT '',
  `twitter`            VARCHAR(255)           NOT NULL DEFAULT '',
  `handy`              VARCHAR(50)                     DEFAULT NULL,
  `geschlecht`         TINYINT(3) UNSIGNED             DEFAULT NULL,
  `geb_datum`          DATE                            DEFAULT NULL,
  `fs_id`              INT(11)                         DEFAULT '0',
  `anmeldedatum`       DATETIME                        DEFAULT NULL,
  `orgateam`           TINYINT(3) UNSIGNED             DEFAULT '0',
  `active`             TINYINT(3) UNSIGNED    NOT NULL DEFAULT '0',
  `data`               TEXT                   NOT NULL,
  `about_me_public`    TEXT                   NOT NULL,
  `newsletter`         TINYINT(1)             NOT NULL DEFAULT '1',
  `token`              VARCHAR(25)            NOT NULL,
  `infomail_message`   TINYINT(1)             NOT NULL DEFAULT '1',
  `last_login`         DATETIME               NOT NULL,
  `stat_fetchweight`   DECIMAL(7, 2) UNSIGNED NOT NULL DEFAULT '0.00',
  `stat_fetchcount`    INT(10) UNSIGNED       NOT NULL DEFAULT '0',
  `stat_ratecount`     INT(10) UNSIGNED       NOT NULL DEFAULT '0',
  `stat_rating`        DECIMAL(4, 2) UNSIGNED NOT NULL DEFAULT '0.00',
  `stat_postcount`     INT(11)                NOT NULL DEFAULT '0',
  `stat_buddycount`    INT(7) UNSIGNED        NOT NULL,
  `stat_bananacount`   INT(7) UNSIGNED        NOT NULL DEFAULT '0',
  `stat_fetchrate`     DECIMAL(6, 2)          NOT NULL DEFAULT '100.00',
  `sleep_status`       TINYINT(3) UNSIGNED    NOT NULL DEFAULT '0',
  `sleep_from`         DATE                   NOT NULL,
  `sleep_until`        DATE                   NOT NULL,
  `sleep_msg`          TEXT                   NOT NULL,
  `gcm`                VARCHAR(512)           NOT NULL DEFAULT '',
  `iosid`              VARCHAR(512)           NOT NULL DEFAULT '',
  `last_mid`           DATE                   NOT NULL,
  `option`             TEXT                   NOT NULL,
  `beta`               TINYINT(1)             NOT NULL DEFAULT '0',
  `fs_password`        VARCHAR(50)                     DEFAULT NULL,
  `quiz_rolle`         TINYINT(1) UNSIGNED    NOT NULL DEFAULT '0',
  `contact_public`     TINYINT(4)             NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 151030
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_archive`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_archive` (
  `id`                 INT(10) UNSIGNED       NOT NULL,
  `autokennzeichen_id` INT(10) UNSIGNED       NOT NULL,
  `bezirk_id`          INT(10) UNSIGNED       NOT NULL,
  `position`           VARCHAR(255)           NOT NULL DEFAULT '',
  `verified`           TINYINT(3) UNSIGNED    NOT NULL DEFAULT '0',
  `last_pass`          DATETIME               NOT NULL,
  `new_bezirk`         VARCHAR(120)           NOT NULL,
  `want_new`           TINYINT(4)             NOT NULL DEFAULT '0',
  `mailbox_id`         INT(10) UNSIGNED       NOT NULL DEFAULT '0',
  `rolle`              TINYINT(4)             NOT NULL,
  `type`               TINYINT(4)                      DEFAULT '0',
  `plz`                VARCHAR(5)             NOT NULL,
  `stadt`              VARCHAR(50)            NOT NULL,
  `bundesland_id`      INT(11)                NOT NULL,
  `lat`                VARCHAR(20)            NOT NULL,
  `lon`                VARCHAR(20)            NOT NULL,
  `photo`              VARCHAR(50)            NOT NULL,
  `photo_public`       TINYINT(4)             NOT NULL DEFAULT '0',
  `email`              VARCHAR(120)                    DEFAULT NULL,
  `passwd`             VARCHAR(32)                     DEFAULT NULL,
  `name`               VARCHAR(120)                    DEFAULT NULL,
  `admin`              TINYINT(3) UNSIGNED             DEFAULT NULL,
  `nachname`           VARCHAR(120)                    DEFAULT NULL,
  `anschrift`          VARCHAR(120)                    DEFAULT NULL,
  `telefon`            VARCHAR(30)                     DEFAULT NULL,
  `tox`                VARCHAR(255)           NOT NULL DEFAULT '',
  `homepage`           VARCHAR(255)           NOT NULL DEFAULT '',
  `github`             VARCHAR(255)           NOT NULL DEFAULT '',
  `twitter`            VARCHAR(255)           NOT NULL DEFAULT '',
  `handy`              VARCHAR(50)                     DEFAULT NULL,
  `geschlecht`         TINYINT(3) UNSIGNED             DEFAULT NULL,
  `geb_datum`          DATE                            DEFAULT NULL,
  `fs_id`              INT(11)                         DEFAULT '0',
  `anmeldedatum`       DATETIME                        DEFAULT NULL,
  `orgateam`           TINYINT(3) UNSIGNED             DEFAULT '0',
  `active`             TINYINT(3) UNSIGNED    NOT NULL DEFAULT '0',
  `data`               TEXT                   NOT NULL,
  `about_me_public`    TEXT                   NOT NULL,
  `newsletter`         TINYINT(1)             NOT NULL DEFAULT '1',
  `token`              VARCHAR(25)            NOT NULL,
  `infomail_message`   TINYINT(1)             NOT NULL DEFAULT '1',
  `last_login`         DATETIME               NOT NULL,
  `stat_fetchweight`   DECIMAL(7, 2) UNSIGNED NOT NULL DEFAULT '0.00',
  `stat_fetchcount`    INT(10) UNSIGNED       NOT NULL DEFAULT '0',
  `stat_ratecount`     INT(10) UNSIGNED       NOT NULL DEFAULT '0',
  `stat_rating`        DECIMAL(4, 2) UNSIGNED NOT NULL DEFAULT '0.00',
  `stat_postcount`     INT(11)                NOT NULL DEFAULT '0',
  `stat_buddycount`    INT(7) UNSIGNED        NOT NULL,
  `stat_bananacount`   INT(7) UNSIGNED        NOT NULL DEFAULT '0',
  `stat_fetchrate`     DECIMAL(6, 2)          NOT NULL DEFAULT '100.00',
  `sleep_status`       TINYINT(3) UNSIGNED    NOT NULL DEFAULT '0',
  `sleep_from`         DATE                   NOT NULL,
  `sleep_until`        DATE                   NOT NULL,
  `sleep_msg`          TEXT                   NOT NULL,
  `gcm`                VARCHAR(512)           NOT NULL DEFAULT '',
  `iosid`              VARCHAR(512)           NOT NULL DEFAULT '',
  `last_mid`           DATE                   NOT NULL,
  `option`             TEXT                   NOT NULL,
  `beta`               TINYINT(1)             NOT NULL DEFAULT '0',
  `fs_password`        VARCHAR(50)                     DEFAULT NULL,
  `quiz_rolle`         TINYINT(1) UNSIGNED    NOT NULL DEFAULT '0',
  `contact_public`     TINYINT(4)             NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 150886
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_archive2`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_archive2` (
  `id`                 INT(10) UNSIGNED       NOT NULL,
  `autokennzeichen_id` INT(10) UNSIGNED       NOT NULL,
  `bezirk_id`          INT(10) UNSIGNED       NOT NULL,
  `verified`           TINYINT(3) UNSIGNED    NOT NULL DEFAULT '0',
  `last_pass`          DATETIME               NOT NULL,
  `new_bezirk`         VARCHAR(120)           NOT NULL,
  `want_new`           TINYINT(4)             NOT NULL DEFAULT '0',
  `rolle`              TINYINT(4)             NOT NULL,
  `type`               TINYINT(4)                      DEFAULT '0',
  `mailbox_id`         INT(10) UNSIGNED       NOT NULL DEFAULT '0',
  `plz`                VARCHAR(5)             NOT NULL,
  `stadt`              VARCHAR(50)            NOT NULL,
  `bundesland_id`      INT(11)                NOT NULL,
  `lat`                VARCHAR(20)            NOT NULL,
  `lon`                VARCHAR(20)            NOT NULL,
  `photo`              VARCHAR(50)            NOT NULL,
  `photo_public`       TINYINT(4)             NOT NULL DEFAULT '0',
  `email`              VARCHAR(120)                    DEFAULT NULL,
  `passwd`             VARCHAR(32)                     DEFAULT NULL,
  `name`               VARCHAR(120)                    DEFAULT NULL,
  `admin`              TINYINT(3) UNSIGNED             DEFAULT NULL,
  `nachname`           VARCHAR(120)                    DEFAULT NULL,
  `anschrift`          VARCHAR(120)                    DEFAULT NULL,
  `telefon`            VARCHAR(30)                     DEFAULT NULL,
  `handy`              VARCHAR(50)                     DEFAULT NULL,
  `geschlecht`         TINYINT(3) UNSIGNED             DEFAULT NULL,
  `geb_datum`          DATE                            DEFAULT NULL,
  `fs_id`              INT(11)                         DEFAULT '0',
  `anmeldedatum`       DATETIME                        DEFAULT NULL,
  `orgateam`           TINYINT(3) UNSIGNED             DEFAULT '0',
  `active`             TINYINT(3) UNSIGNED    NOT NULL DEFAULT '0',
  `data`               TEXT                   NOT NULL,
  `about_me_public`    TEXT                   NOT NULL,
  `newsletter`         TINYINT(1)             NOT NULL DEFAULT '1',
  `token`              VARCHAR(25)            NOT NULL,
  `infomail_message`   TINYINT(1)             NOT NULL DEFAULT '1',
  `last_login`         DATETIME               NOT NULL,
  `stat_fetchweight`   DECIMAL(7, 2) UNSIGNED NOT NULL DEFAULT '0.00',
  `stat_fetchcount`    INT(10) UNSIGNED       NOT NULL DEFAULT '0',
  `stat_ratecount`     INT(10) UNSIGNED       NOT NULL DEFAULT '0',
  `stat_rating`        DECIMAL(4, 2) UNSIGNED NOT NULL DEFAULT '0.00',
  `stat_postcount`     INT(11)                NOT NULL DEFAULT '0',
  `stat_buddycount`    INT(7) UNSIGNED        NOT NULL,
  `stat_bananacount`   INT(7) UNSIGNED        NOT NULL DEFAULT '1',
  `stat_fetchrate`     DECIMAL(6, 2)          NOT NULL DEFAULT '100.00',
  `option`             TEXT                   NOT NULL,
  `sleep_status`       TINYINT(3) UNSIGNED    NOT NULL,
  `sleep_from`         DATE                   NOT NULL,
  `sleep_until`        DATE                   NOT NULL,
  `sleep_msg`          TEXT                   NOT NULL,
  `iosid`              VARCHAR(512)           NOT NULL,
  `gcm`                VARCHAR(512)           NOT NULL,
  `beta`               TINYINT(2)             NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_bell`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_bell` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `bell_id`      INT(10) UNSIGNED NOT NULL,
  `seen`         TINYINT(1) UNSIGNED DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_bezirk`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_bezirk` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `bezirk_id`    INT(10) UNSIGNED NOT NULL,
  `active`       INT(10) UNSIGNED DEFAULT '0'
  COMMENT '0=beworben,1=aktiv,10=vielleicht',
  `added`        DATETIME         NOT NULL,
  `application`  TEXT             NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_contact`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_contact` (
  `foodsaver_id` INT(11) NOT NULL,
  `contact_id`   INT(11) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_conversation`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_conversation` (
  `foodsaver_id`    INT(10) UNSIGNED NOT NULL,
  `conversation_id` INT(10) UNSIGNED NOT NULL,
  `unread`          TINYINT(1) UNSIGNED DEFAULT '1'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_event`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_event` (
  `foodsaver_id` INT(10) UNSIGNED    NOT NULL,
  `event_id`     INT(10) UNSIGNED    NOT NULL,
  `status`       TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_fairteiler`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_fairteiler` (
  `foodsaver_id`  INT(10) UNSIGNED NOT NULL,
  `fairteiler_id` INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_fetchdate`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_fetchdate` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `fetchdate_id` INT(10) UNSIGNED NOT NULL,
  `confirmed`    TINYINT(1) UNSIGNED DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_has_wallpost` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `wallpost_id`  INT(10) UNSIGNED NOT NULL,
  `usercomment`  TINYINT(2)       NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_foodsaver_stats`
--

CREATE TABLE IF NOT EXISTS `fs_foodsaver_stats` (
  `foodsaver_id` INT(11) NOT NULL,
  `user_rating`  DECIMAL(10, 2) DEFAULT NULL,
  `count_fetch`  INT(11)        DEFAULT NULL,
  `count_forum`  INT(11)        DEFAULT NULL,
  `count_vouch`  INT(11)        DEFAULT NULL,
  `gerettet`     DECIMAL(10, 2) DEFAULT NULL,
  `count_rating` INT(11)        DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_fsreport_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_fsreport_has_wallpost` (
  `fsreport_id` INT(10) UNSIGNED NOT NULL,
  `wallpost_id` INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_geoRegion`
--

CREATE TABLE IF NOT EXISTS `fs_geoRegion` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(90) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 445
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_glocke`
--

CREATE TABLE IF NOT EXISTS `fs_glocke` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(150) DEFAULT NULL,
  `msg`  VARCHAR(300)     NOT NULL,
  `url`  VARCHAR(160) DEFAULT NULL,
  `time` DATETIME         NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 425246
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_glocke_read`
--

CREATE TABLE IF NOT EXISTS `fs_glocke_read` (
  `glocke_id`    INT(10) UNSIGNED NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `unread`       TINYINT(3) UNSIGNED DEFAULT '1'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_ipblock`
--

CREATE TABLE IF NOT EXISTS `fs_ipblock` (
  `ip`       VARCHAR(20) NOT NULL,
  `context`  VARCHAR(10) NOT NULL,
  `start`    DATETIME         DEFAULT NULL,
  `duration` INT(10) UNSIGNED DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_item`
--

CREATE TABLE IF NOT EXISTS `fs_item` (
  `id`            INT(10) UNSIGNED    NOT NULL,
  `item_class_id` INT(10) UNSIGNED    NOT NULL,
  `user_id`       INT(10) UNSIGNED    NOT NULL,
  `status`        TINYINT(3) UNSIGNED NOT NULL DEFAULT '1'
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 1112
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_item_class`
--

CREATE TABLE IF NOT EXISTS `fs_item_class` (
  `id`           INT(10) UNSIGNED NOT NULL,
  `item_type_id` INT(10) UNSIGNED NOT NULL,
  `name`         VARCHAR(120) DEFAULT NULL,
  `desc`         TEXT
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 12
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_item_request`
--

CREATE TABLE IF NOT EXISTS `fs_item_request` (
  `user_id` INT(10) UNSIGNED NOT NULL,
  `item_id` INT(10) UNSIGNED NOT NULL,
  `time`    DATETIME         NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_item_track`
--

CREATE TABLE IF NOT EXISTS `fs_item_track` (
  `user_id` INT(10) UNSIGNED NOT NULL,
  `item_id` INT(10) UNSIGNED NOT NULL,
  `time`    DATETIME         NOT NULL,
  `lat`     VARCHAR(100) DEFAULT NULL,
  `lon`     VARCHAR(100) DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_item_type`
--

CREATE TABLE IF NOT EXISTS `fs_item_type` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(120) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_kette`
--

CREATE TABLE IF NOT EXISTS `fs_kette` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(60) DEFAULT NULL,
  `logo` VARCHAR(30)      NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 398
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_land`
--

CREATE TABLE IF NOT EXISTS `fs_land` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(90) DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_language`
--

CREATE TABLE IF NOT EXISTS `fs_language` (
  `id`    INT(10) UNSIGNED NOT NULL,
  `name`  VARCHAR(50) DEFAULT NULL,
  `short` VARCHAR(10) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_lebensmittel`
--

CREATE TABLE IF NOT EXISTS `fs_lebensmittel` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(50) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 13
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_location`
--

CREATE TABLE IF NOT EXISTS `fs_location` (
  `id`     INT(10) UNSIGNED NOT NULL,
  `name`   VARCHAR(200)   DEFAULT NULL,
  `lat`    DECIMAL(10, 8) DEFAULT NULL,
  `lon`    DECIMAL(11, 8) DEFAULT NULL,
  `zip`    VARCHAR(10)    DEFAULT NULL,
  `city`   VARCHAR(100)   DEFAULT NULL,
  `street` VARCHAR(200)   DEFAULT NULL
)
  ENGINE = MyISAM
  AUTO_INCREMENT = 4284
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_login`
--

CREATE TABLE IF NOT EXISTS `fs_login` (
  `id`           INT(10) UNSIGNED NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `ip`           VARCHAR(30)  DEFAULT NULL,
  `agent`        VARCHAR(200) DEFAULT NULL,
  `time`         DATETIME     DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 6138537
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mailbox`
--

CREATE TABLE IF NOT EXISTS `fs_mailbox` (
  `id`          INT(10) UNSIGNED NOT NULL,
  `name`        VARCHAR(50)               DEFAULT NULL,
  `member`      TINYINT(4)       NOT NULL DEFAULT '0',
  `last_access` DATETIME         NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 28458
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mailbox_member`
--

CREATE TABLE IF NOT EXISTS `fs_mailbox_member` (
  `mailbox_id`   INT(10) UNSIGNED NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `email_name`   VARCHAR(120)     NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mailbox_message`
--

CREATE TABLE IF NOT EXISTS `fs_mailbox_message` (
  `id`         INT(10) UNSIGNED NOT NULL,
  `mailbox_id` INT(10) UNSIGNED NOT NULL,
  `folder`     TINYINT(3) UNSIGNED DEFAULT '1',
  `sender`     VARCHAR(120)        DEFAULT NULL,
  `to`         TEXT             NOT NULL,
  `subject`    VARCHAR(120)        DEFAULT NULL,
  `body`       TEXT,
  `body_html`  TEXT             NOT NULL,
  `time`       DATETIME            DEFAULT NULL,
  `attach`     TEXT,
  `read`       TINYINT(3) UNSIGNED DEFAULT NULL,
  `answer`     TINYINT(3) UNSIGNED DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 102410
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mailchange`
--

CREATE TABLE IF NOT EXISTS `fs_mailchange` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `newmail`      VARCHAR(200) DEFAULT NULL,
  `time`         DATETIME     DEFAULT NULL,
  `token`        VARCHAR(300) DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mail_error`
--

CREATE TABLE IF NOT EXISTS `fs_mail_error` (
  `id`           INT(10) UNSIGNED NOT NULL,
  `send_mail_id` INT(10) UNSIGNED NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_message`
--

CREATE TABLE IF NOT EXISTS `fs_message` (
  `id`        INT(10) UNSIGNED    NOT NULL,
  `sender_id` INT(10) UNSIGNED             DEFAULT NULL,
  `recip_id`  INT(10) UNSIGNED             DEFAULT NULL,
  `unread`    TINYINT(3) UNSIGNED          DEFAULT NULL,
  `name`      VARCHAR(150)                 DEFAULT NULL,
  `msg`       TEXT,
  `time`      DATETIME                     DEFAULT NULL,
  `attach`    VARCHAR(500)                 DEFAULT NULL,
  `recd`      TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 2570714
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_message_tpl`
--

CREATE TABLE IF NOT EXISTS `fs_message_tpl` (
  `id`          INT(10) UNSIGNED NOT NULL,
  `language_id` INT(10) UNSIGNED NOT NULL,
  `name`        VARCHAR(40) DEFAULT NULL,
  `subject`     VARCHAR(120)     NOT NULL,
  `body`        TEXT
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 30
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_msg`
--

CREATE TABLE IF NOT EXISTS `fs_msg` (
  `id`              INT(10) UNSIGNED NOT NULL,
  `conversation_id` INT(10) UNSIGNED NOT NULL,
  `foodsaver_id`    INT(10) UNSIGNED NOT NULL,
  `body`            TEXT,
  `time`            DATETIME DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 4362369
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_mumbleuser`
--

CREATE TABLE IF NOT EXISTS `fs_mumbleuser` (
  `foodsaver_id` INT(11)      NOT NULL,
  `name`         VARCHAR(100) NOT NULL,
  `sha`          VARCHAR(160) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_partnerschaft`
--

CREATE TABLE IF NOT EXISTS `fs_partnerschaft` (
  `foodsaver_id` INT(10) UNSIGNED    NOT NULL,
  `partner_id`   INT(10) UNSIGNED    NOT NULL,
  `form`         TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
  `time`         DATETIME                     DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 8765
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_pass_gen`
--

CREATE TABLE IF NOT EXISTS `fs_pass_gen` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `date`         DATETIME         NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_pass_request`
--

CREATE TABLE IF NOT EXISTS `fs_pass_request` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `name`         VARCHAR(50) DEFAULT NULL,
  `time`         DATETIME    DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_plz`
--

CREATE TABLE IF NOT EXISTS `fs_plz` (
  `plz`                  VARCHAR(5)       NOT NULL,
  `stadt_id`             INT(10) UNSIGNED NOT NULL,
  `stadt_kennzeichen_id` INT(10) UNSIGNED NOT NULL,
  `bundesland_id`        INT(10) UNSIGNED NOT NULL,
  `geoRegion_id`         INT(10) UNSIGNED NOT NULL,
  `land_id`              INT(10) UNSIGNED NOT NULL,
  `lat`                  VARCHAR(60) DEFAULT NULL,
  `lon`                  VARCHAR(60) DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_pushqueue`
--

CREATE TABLE IF NOT EXISTS `fs_pushqueue` (
  `id`         INT(10) UNSIGNED NOT NULL,
  `sender_id`  INT(10) UNSIGNED NOT NULL,
  `recip_id`   INT(10) UNSIGNED NOT NULL,
  `time`       DATETIME         NOT NULL,
  `message_id` INT(10) UNSIGNED NOT NULL,
  `title`      VARCHAR(80)      NOT NULL DEFAULT '',
  `message`    VARCHAR(400)     NOT NULL DEFAULT '',
  `data`       TEXT,
  `status`     TINYINT(2) UNSIGNED       DEFAULT NULL,
  `id_gcm`     VARCHAR(400)     NOT NULL DEFAULT '',
  `id_apn`     VARCHAR(400)     NOT NULL DEFAULT ''
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 35328
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_question`
--

CREATE TABLE IF NOT EXISTS `fs_question` (
  `id`       INT(10) UNSIGNED NOT NULL,
  `text`     TEXT,
  `duration` INT(3) UNSIGNED  NOT NULL,
  `wikilink` VARCHAR(250)     NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 115
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_question_has_quiz`
--

CREATE TABLE IF NOT EXISTS `fs_question_has_quiz` (
  `question_id` INT(10) UNSIGNED NOT NULL,
  `quiz_id`     INT(10) UNSIGNED NOT NULL,
  `fp`          TINYINT(2) UNSIGNED DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_question_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_question_has_wallpost` (
  `question_id` INT(10) UNSIGNED NOT NULL,
  `wallpost_id` INT(10) UNSIGNED NOT NULL,
  `usercomment` TINYINT(2)       NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_quiz`
--

CREATE TABLE IF NOT EXISTS `fs_quiz` (
  `id`         INT(10) UNSIGNED     NOT NULL,
  `name`       VARCHAR(200) DEFAULT NULL,
  `desc`       TEXT,
  `maxfp`      SMALLINT(4) UNSIGNED NOT NULL,
  `questcount` SMALLINT(4) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 4
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_quiz_session`
--

CREATE TABLE IF NOT EXISTS `fs_quiz_session` (
  `id`             INT(10) UNSIGNED NOT NULL,
  `foodsaver_id`   INT(10) UNSIGNED NOT NULL,
  `quiz_id`        INT(10) UNSIGNED NOT NULL,
  `status`         TINYINT(2) UNSIGNED       DEFAULT NULL,
  `quiz_index`     TINYINT(3) UNSIGNED       DEFAULT NULL,
  `quiz_questions` TEXT,
  `quiz_result`    TEXT,
  `time_start`     DATETIME                  DEFAULT NULL,
  `time_end`       DATETIME                  DEFAULT NULL,
  `fp`             DECIMAL(5, 2)             DEFAULT NULL,
  `maxfp`          TINYINT(3) UNSIGNED       DEFAULT NULL,
  `quest_count`    TINYINT(3) UNSIGNED       DEFAULT NULL,
  `easymode`       TINYINT(4)       NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 44736
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_rating`
--

CREATE TABLE IF NOT EXISTS `fs_rating` (
  `foodsaver_id` INT(10) UNSIGNED    NOT NULL,
  `rater_id`     INT(10) UNSIGNED    NOT NULL,
  `ratingtype`   TINYINT(2) UNSIGNED NOT NULL DEFAULT '1',
  `rating`       TINYINT(4)                   DEFAULT NULL,
  `msg`          TEXT                NOT NULL,
  `time`         DATETIME            NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COMMENT ='ratingtype 1+2 = bananen, 4+5 = betriebsmeldung';

-- --------------------------------------------------------

--
-- Table structure for table `fs_region`
--

CREATE TABLE IF NOT EXISTS `fs_region` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(100)              DEFAULT NULL,
  `type` TINYINT(4)       NOT NULL DEFAULT '1'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_report`
--

CREATE TABLE IF NOT EXISTS `fs_report` (
  `id`           INT(10) UNSIGNED NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `reporter_id`  INT(10) UNSIGNED    DEFAULT NULL,
  `reporttype`   TINYINT(2) UNSIGNED DEFAULT NULL,
  `betrieb_id`   INT(10) UNSIGNED    DEFAULT NULL,
  `time`         DATETIME            DEFAULT NULL,
  `committed`    TINYINT(2) UNSIGNED DEFAULT '0',
  `msg`          TEXT,
  `tvalue`       VARCHAR(300)        DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 3040
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_send_email`
--

CREATE TABLE IF NOT EXISTS `fs_send_email` (
  `id`           INT(10) UNSIGNED NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `mailbox_id`   INT(10) UNSIGNED NOT NULL,
  `mode`         TINYINT(4)       NOT NULL DEFAULT '1',
  `complete`     TINYINT(4)       NOT NULL DEFAULT '0',
  `name`         VARCHAR(200)              DEFAULT NULL,
  `message`      TEXT,
  `zeit`         DATETIME                  DEFAULT NULL,
  `recip`        TEXT,
  `attach`       VARCHAR(500)     NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 883
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_stadt`
--

CREATE TABLE IF NOT EXISTS `fs_stadt` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(100) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 6499
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_stadtteil`
--

CREATE TABLE IF NOT EXISTS `fs_stadtteil` (
  `id`       INT(10) UNSIGNED NOT NULL,
  `stadt_id` INT(10) UNSIGNED NOT NULL,
  `name`     VARCHAR(120) DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_stadt_kennzeichen`
--

CREATE TABLE IF NOT EXISTS `fs_stadt_kennzeichen` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(10) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 17
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_stat_abholmengen`
--

CREATE TABLE IF NOT EXISTS `fs_stat_abholmengen` (
  `betrieb_id` INT(10) UNSIGNED NOT NULL,
  `date`       DATETIME         NOT NULL,
  `abholmenge` DECIMAL(5, 1)    NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fs_theme`
--

CREATE TABLE IF NOT EXISTS `fs_theme` (
  `id`           INT(10) UNSIGNED    NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED    NOT NULL,
  `last_post_id` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `name`         VARCHAR(260)                 DEFAULT NULL,
  `time`         DATETIME                     DEFAULT NULL,
  `active`       TINYINT(2) UNSIGNED NOT NULL DEFAULT '1',
  `sticky`       TINYINT(1)          NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 29123
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_theme_follower`
--

CREATE TABLE IF NOT EXISTS `fs_theme_follower` (
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `theme_id`     INT(10) UNSIGNED NOT NULL,
  `infotype`     TINYINT(1)       NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_theme_post`
--

CREATE TABLE IF NOT EXISTS `fs_theme_post` (
  `id`           INT(10) UNSIGNED NOT NULL,
  `theme_id`     INT(10) UNSIGNED NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `reply_post`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `body`         TEXT,
  `time`         DATETIME                  DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 162599
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_upgrade_request`
--

CREATE TABLE IF NOT EXISTS `fs_upgrade_request` (
  `foodsaver_id` INT(10) UNSIGNED    NOT NULL,
  `rolle`        TINYINT(3) UNSIGNED NOT NULL,
  `bezirk_id`    INT(10) UNSIGNED    NOT NULL,
  `time`         DATETIME DEFAULT NULL,
  `data`         TEXT
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_user`
--

CREATE TABLE IF NOT EXISTS `fs_user` (
  `id`              INT(10) UNSIGNED NOT NULL,
  `fsid`            INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_type_id`    INT(10) UNSIGNED NOT NULL,
  `email`           VARCHAR(50)               DEFAULT NULL,
  `passwd`          VARCHAR(50)               DEFAULT NULL,
  `name`            VARCHAR(100)     NOT NULL,
  `anschrift`       VARCHAR(260)              DEFAULT NULL,
  `plz`             VARCHAR(5)                DEFAULT NULL,
  `ort`             VARCHAR(260)              DEFAULT NULL,
  `lat`             VARCHAR(50)      NOT NULL,
  `lon`             VARCHAR(50)      NOT NULL,
  `ansprechpartner` VARCHAR(260)     NOT NULL,
  `zeiten`          TEXT             NOT NULL,
  `web`             VARCHAR(150)     NOT NULL,
  `telefon`         VARCHAR(100)     NOT NULL,
  `desc`            TEXT,
  `photo`           VARCHAR(50)               DEFAULT NULL,
  `token`           VARCHAR(50)      NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 888
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_usernotes_has_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_usernotes_has_wallpost` (
  `usernotes_id` INT(10) UNSIGNED NOT NULL,
  `wallpost_id`  INT(10) UNSIGNED NOT NULL,
  `usercomment`  TINYINT(2)       NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_user_type`
--

CREATE TABLE IF NOT EXISTS `fs_user_type` (
  `id`   INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(150) DEFAULT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 3
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fs_wallpost`
--

CREATE TABLE IF NOT EXISTS `fs_wallpost` (
  `id`           INT(10) UNSIGNED NOT NULL,
  `foodsaver_id` INT(10) UNSIGNED NOT NULL,
  `body`         TEXT,
  `time`         DATETIME DEFAULT NULL,
  `attach`       TEXT
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 69130
  DEFAULT CHARSET = utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `basket_has_art`
--
ALTER TABLE `basket_has_art`
  ADD PRIMARY KEY (`basket_id`, `art_id`),
  ADD KEY `basket_has_art_FKIndex1` (`basket_id`),
  ADD KEY `basket_has_art_FKIndex2` (`art_id`);

--
-- Indexes for table `basket_has_types`
--
ALTER TABLE `basket_has_types`
  ADD PRIMARY KEY (`basket_id`, `types_id`),
  ADD KEY `basket_has_types_FKIndex1` (`basket_id`),
  ADD KEY `basket_has_types_FKIndex2` (`types_id`);

--
-- Indexes for table `fs_abholen`
--
ALTER TABLE `fs_abholen`
  ADD PRIMARY KEY (`foodsaver_id`, `betrieb_id`, `dow`),
  ADD KEY `foodsaver_has_betrieb_FKIndex1` (`foodsaver_id`),
  ADD KEY `foodsaver_has_betrieb_FKIndex2` (`betrieb_id`);

--
-- Indexes for table `fs_abholer`
--
ALTER TABLE `fs_abholer`
  ADD PRIMARY KEY (`foodsaver_id`, `betrieb_id`, `date`),
  ADD KEY `betrieb_id` (`betrieb_id`);

--
-- Indexes for table `fs_abholmengen`
--
ALTER TABLE `fs_abholmengen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_abholzeiten`
--
ALTER TABLE `fs_abholzeiten`
  ADD PRIMARY KEY (`betrieb_id`, `dow`, `time`);

--
-- Indexes for table `fs_activity`
--
ALTER TABLE `fs_activity`
  ADD PRIMARY KEY (`foodsaver_id`),
  ADD KEY `zeit` (`zeit`);

--
-- Indexes for table `fs_answer`
--
ALTER TABLE `fs_answer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answer_FKIndex1` (`question_id`);

--
-- Indexes for table `fs_apitoken`
--
ALTER TABLE `fs_apitoken`
  ADD KEY `foodsaver_id` (`foodsaver_id`);

--
-- Indexes for table `fs_application_has_wallpost`
--
ALTER TABLE `fs_application_has_wallpost`
  ADD PRIMARY KEY (`application_id`, `wallpost_id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_autokennzeichen`
--
ALTER TABLE `fs_autokennzeichen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `land_id` (`land_id`);

--
-- Indexes for table `fs_basket`
--
ALTER TABLE `fs_basket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `basket_FKIndex1` (`foodsaver_id`),
  ADD KEY `bezirk_id` (`bezirk_id`),
  ADD KEY `lat` (`lat`, `lon`),
  ADD KEY `fs_id` (`fs_id`);

--
-- Indexes for table `fs_basket_anfrage`
--
ALTER TABLE `fs_basket_anfrage`
  ADD PRIMARY KEY (`foodsaver_id`, `basket_id`),
  ADD KEY `foodsaver_has_basket_FKIndex1` (`foodsaver_id`),
  ADD KEY `foodsaver_has_basket_FKIndex2` (`basket_id`);

--
-- Indexes for table `fs_basket_has_art`
--
ALTER TABLE `fs_basket_has_art`
  ADD PRIMARY KEY (`basket_id`, `art_id`);

--
-- Indexes for table `fs_basket_has_types`
--
ALTER TABLE `fs_basket_has_types`
  ADD PRIMARY KEY (`basket_id`, `types_id`);

--
-- Indexes for table `fs_basket_has_wallpost`
--
ALTER TABLE `fs_basket_has_wallpost`
  ADD PRIMARY KEY (`basket_id`, `wallpost_id`),
  ADD KEY `basket_has_wallpost_FKIndex1` (`basket_id`),
  ADD KEY `basket_has_wallpost_FKIndex2` (`wallpost_id`),
  ADD KEY `basket_id` (`basket_id`),
  ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_bell`
--
ALTER TABLE `fs_bell`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_betrieb`
--
ALTER TABLE `fs_betrieb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `betrieb_FKIndex2` (`kette_id`),
  ADD KEY `betrieb_FKIndex3` (`bezirk_id`),
  ADD KEY `betrieb_FKIndex5` (`betrieb_status_id`),
  ADD KEY `plz` (`plz`),
  ADD KEY `team_status` (`team_status`);

--
-- Indexes for table `fs_betrieb_has_lebensmittel`
--
ALTER TABLE `fs_betrieb_has_lebensmittel`
  ADD PRIMARY KEY (`betrieb_id`, `lebensmittel_id`);

--
-- Indexes for table `fs_betrieb_kategorie`
--
ALTER TABLE `fs_betrieb_kategorie`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_betrieb_notiz`
--
ALTER TABLE `fs_betrieb_notiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `betrieb_notitz_FKIndex1` (`betrieb_id`),
  ADD KEY `betrieb_notiz_FKIndex2` (`foodsaver_id`);

--
-- Indexes for table `fs_betrieb_status`
--
ALTER TABLE `fs_betrieb_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_betrieb_team`
--
ALTER TABLE `fs_betrieb_team`
  ADD PRIMARY KEY (`foodsaver_id`, `betrieb_id`),
  ADD KEY `foodsaver_has_betrieb_FKIndex1` (`foodsaver_id`),
  ADD KEY `foodsaver_has_betrieb_FKIndex2` (`betrieb_id`);

--
-- Indexes for table `fs_bezirk`
--
ALTER TABLE `fs_bezirk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `type` (`type`),
  ADD KEY `mailbox_id` (`mailbox_id`),
  ADD KEY `master` (`master`);

--
-- Indexes for table `fs_bezirk_closure`
--
ALTER TABLE `fs_bezirk_closure`
  ADD KEY `ancestor_id` (`ancestor_id`),
  ADD KEY `bezirk_id` (`bezirk_id`);

--
-- Indexes for table `fs_bezirk_has_theme`
--
ALTER TABLE `fs_bezirk_has_theme`
  ADD PRIMARY KEY (`theme_id`, `bezirk_id`),
  ADD KEY `bezirk_id` (`bezirk_id`);

--
-- Indexes for table `fs_bezirk_has_wallpost`
--
ALTER TABLE `fs_bezirk_has_wallpost`
  ADD PRIMARY KEY (`bezirk_id`, `wallpost_id`),
  ADD KEY `bezirk_id` (`bezirk_id`),
  ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_blog_entry`
--
ALTER TABLE `fs_blog_entry`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_entry_FKIndex1` (`foodsaver_id`),
  ADD KEY `blog_entry_FKIndex2` (`bezirk_id`),
  ADD KEY `active` (`active`);

--
-- Indexes for table `fs_botschafter`
--
ALTER TABLE `fs_botschafter`
  ADD PRIMARY KEY (`foodsaver_id`, `bezirk_id`),
  ADD KEY `foodsaver_has_bezirk_FKIndex1` (`foodsaver_id`),
  ADD KEY `foodsaver_has_bezirk_FKIndex2` (`bezirk_id`);

--
-- Indexes for table `fs_buddy`
--
ALTER TABLE `fs_buddy`
  ADD PRIMARY KEY (`foodsaver_id`, `buddy_id`),
  ADD KEY `buddy_confirmed` (`confirmed`);

--
-- Indexes for table `fs_bundesland`
--
ALTER TABLE `fs_bundesland`
  ADD PRIMARY KEY (`id`),
  ADD KEY `land_id` (`land_id`);

--
-- Indexes for table `fs_cache`
--
ALTER TABLE `fs_cache`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_contact`
--
ALTER TABLE `fs_contact`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `fs_content`
--
ALTER TABLE `fs_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_conversation`
--
ALTER TABLE `fs_conversation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_last_fs_id` (`last_foodsaver_id`);

--
-- Indexes for table `fs_document`
--
ALTER TABLE `fs_document`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_email_status`
--
ALTER TABLE `fs_email_status`
  ADD PRIMARY KEY (`email_id`, `foodsaver_id`);

--
-- Indexes for table `fs_event`
--
ALTER TABLE `fs_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_FKIndex1` (`location_id`),
  ADD KEY `event_FKIndex2` (`bezirk_id`),
  ADD KEY `event_FKIndex3` (`foodsaver_id`);

--
-- Indexes for table `fs_event_has_wallpost`
--
ALTER TABLE `fs_event_has_wallpost`
  ADD PRIMARY KEY (`event_id`, `wallpost_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `wallpost_id` (`wallpost_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`, `wish`);

--
-- Indexes for table `fs_fairteiler`
--
ALTER TABLE `fs_fairteiler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fairteiler_FKIndex1` (`bezirk_id`);

--
-- Indexes for table `fs_fairteiler_follower`
--
ALTER TABLE `fs_fairteiler_follower`
  ADD PRIMARY KEY (`fairteiler_id`, `foodsaver_id`),
  ADD KEY `fairteiler_verantwortlich_FKIndex1` (`fairteiler_id`),
  ADD KEY `fairteiler_verantwortlich_FKIndex2` (`foodsaver_id`),
  ADD KEY `type` (`type`),
  ADD KEY `infotype` (`infotype`);

--
-- Indexes for table `fs_fairteiler_has_wallpost`
--
ALTER TABLE `fs_fairteiler_has_wallpost`
  ADD PRIMARY KEY (`fairteiler_id`, `wallpost_id`),
  ADD KEY `fairteiler_has_wallpost_FKIndex1` (`fairteiler_id`),
  ADD KEY `fairteiler_has_wallpost_FKIndex2` (`wallpost_id`);

--
-- Indexes for table `fs_faq`
--
ALTER TABLE `fs_faq`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faq_FKIndex1` (`foodsaver_id`),
  ADD KEY `faq_kategorie_id` (`faq_kategorie_id`);

--
-- Indexes for table `fs_faq_category`
--
ALTER TABLE `fs_faq_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_fetchdate`
--
ALTER TABLE `fs_fetchdate`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fetchdate_FKIndex1` (`betrieb_id`);

--
-- Indexes for table `fs_foodsaver`
--
ALTER TABLE `fs_foodsaver`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `foodsaver_FKIndex2` (`bezirk_id`),
  ADD KEY `foodsaver_FKIndex4` (`autokennzeichen_id`),
  ADD KEY `plz` (`plz`),
  ADD KEY `want_new` (`want_new`),
  ADD KEY `bundesland_id` (`bundesland_id`),
  ADD KEY `mailbox_id` (`mailbox_id`),
  ADD KEY `newsletter` (`newsletter`);

--
-- Indexes for table `fs_foodsaver_archive`
--
ALTER TABLE `fs_foodsaver_archive`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `foodsaver_FKIndex2` (`bezirk_id`),
  ADD KEY `foodsaver_FKIndex4` (`autokennzeichen_id`),
  ADD KEY `plz` (`plz`),
  ADD KEY `want_new` (`want_new`),
  ADD KEY `bundesland_id` (`bundesland_id`),
  ADD KEY `mailbox_id` (`mailbox_id`),
  ADD KEY `newsletter` (`newsletter`);

--
-- Indexes for table `fs_foodsaver_archive2`
--
ALTER TABLE `fs_foodsaver_archive2`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `foodsaver_FKIndex2` (`bezirk_id`),
  ADD KEY `foodsaver_FKIndex4` (`autokennzeichen_id`),
  ADD KEY `plz` (`plz`),
  ADD KEY `want_new` (`want_new`),
  ADD KEY `bundesland_id` (`bundesland_id`),
  ADD KEY `newsletter` (`newsletter`);

--
-- Indexes for table `fs_foodsaver_has_bell`
--
ALTER TABLE `fs_foodsaver_has_bell`
  ADD PRIMARY KEY (`foodsaver_id`, `bell_id`),
  ADD KEY `foodsaver_has_bell_FKIndex1` (`foodsaver_id`),
  ADD KEY `foodsaver_has_bell_FKIndex2` (`bell_id`);

--
-- Indexes for table `fs_foodsaver_has_bezirk`
--
ALTER TABLE `fs_foodsaver_has_bezirk`
  ADD PRIMARY KEY (`foodsaver_id`, `bezirk_id`),
  ADD KEY `foodsaver_has_bezirk_FKIndex1` (`foodsaver_id`),
  ADD KEY `foodsaver_has_bezirk_FKIndex2` (`bezirk_id`);

--
-- Indexes for table `fs_foodsaver_has_contact`
--
ALTER TABLE `fs_foodsaver_has_contact`
  ADD PRIMARY KEY (`foodsaver_id`, `contact_id`);

--
-- Indexes for table `fs_foodsaver_has_conversation`
--
ALTER TABLE `fs_foodsaver_has_conversation`
  ADD PRIMARY KEY (`foodsaver_id`, `conversation_id`),
  ADD KEY `foodsaver_has_conversation_FKIndex1` (`foodsaver_id`),
  ADD KEY `foodsaver_has_conversation_FKIndex2` (`conversation_id`),
  ADD KEY `unread` (`unread`);

--
-- Indexes for table `fs_foodsaver_has_event`
--
ALTER TABLE `fs_foodsaver_has_event`
  ADD PRIMARY KEY (`foodsaver_id`, `event_id`),
  ADD KEY `foodsaver_has_event_FKIndex1` (`foodsaver_id`),
  ADD KEY `foodsaver_has_event_FKIndex2` (`event_id`);

--
-- Indexes for table `fs_foodsaver_has_fairteiler`
--
ALTER TABLE `fs_foodsaver_has_fairteiler`
  ADD PRIMARY KEY (`foodsaver_id`, `fairteiler_id`),
  ADD KEY `foodsaver_has_fairteiler_FKIndex1` (`foodsaver_id`),
  ADD KEY `foodsaver_has_fairteiler_FKIndex2` (`fairteiler_id`);

--
-- Indexes for table `fs_foodsaver_has_fetchdate`
--
ALTER TABLE `fs_foodsaver_has_fetchdate`
  ADD PRIMARY KEY (`foodsaver_id`, `fetchdate_id`),
  ADD KEY `foodsaver_has_fetchdate_FKIndex1` (`foodsaver_id`),
  ADD KEY `foodsaver_has_fetchdate_FKIndex2` (`fetchdate_id`);

--
-- Indexes for table `fs_foodsaver_has_wallpost`
--
ALTER TABLE `fs_foodsaver_has_wallpost`
  ADD PRIMARY KEY (`foodsaver_id`, `wallpost_id`),
  ADD KEY `foodsaver_has_wallpost_FKIndex1` (`foodsaver_id`),
  ADD KEY `foodsaver_has_wallpost_FKIndex2` (`wallpost_id`),
  ADD KEY `foodsaver_id` (`foodsaver_id`),
  ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_foodsaver_stats`
--
ALTER TABLE `fs_foodsaver_stats`
  ADD PRIMARY KEY (`foodsaver_id`);

--
-- Indexes for table `fs_fsreport_has_wallpost`
--
ALTER TABLE `fs_fsreport_has_wallpost`
  ADD PRIMARY KEY (`fsreport_id`, `wallpost_id`),
  ADD KEY `fsreport_has_wallpost_FKIndex1` (`fsreport_id`),
  ADD KEY `fsreport_has_wallpost_FKIndex2` (`wallpost_id`),
  ADD KEY `fsreport_id` (`fsreport_id`),
  ADD KEY `wallpost_id` (`wallpost_id`);

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
  ADD PRIMARY KEY (`glocke_id`, `foodsaver_id`),
  ADD KEY `glocke_has_foodsaver_FKIndex1` (`glocke_id`),
  ADD KEY `glocke_has_foodsaver_FKIndex2` (`foodsaver_id`),
  ADD KEY `glocke_read_unread` (`unread`);

--
-- Indexes for table `fs_ipblock`
--
ALTER TABLE `fs_ipblock`
  ADD PRIMARY KEY (`ip`, `context`);

--
-- Indexes for table `fs_item`
--
ALTER TABLE `fs_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_FKIndex2` (`user_id`),
  ADD KEY `item_FKIndex3` (`item_class_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `fs_item_class`
--
ALTER TABLE `fs_item_class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_class_FKIndex1` (`item_type_id`);

--
-- Indexes for table `fs_item_request`
--
ALTER TABLE `fs_item_request`
  ADD PRIMARY KEY (`user_id`, `item_id`),
  ADD KEY `user_has_item_FKIndex1` (`user_id`),
  ADD KEY `user_has_item_FKIndex2` (`item_id`);

--
-- Indexes for table `fs_item_track`
--
ALTER TABLE `fs_item_track`
  ADD PRIMARY KEY (`user_id`, `item_id`, `time`),
  ADD KEY `user_has_item_FKIndex1` (`user_id`),
  ADD KEY `user_has_item_FKIndex2` (`item_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_FKIndex1` (`foodsaver_id`);

--
-- Indexes for table `fs_mailbox`
--
ALTER TABLE `fs_mailbox`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unique` (`name`),
  ADD KEY `member` (`member`);

--
-- Indexes for table `fs_mailbox_member`
--
ALTER TABLE `fs_mailbox_member`
  ADD PRIMARY KEY (`mailbox_id`, `foodsaver_id`),
  ADD KEY `mailbox_has_foodsaver_FKIndex1` (`mailbox_id`),
  ADD KEY `mailbox_has_foodsaver_FKIndex2` (`foodsaver_id`);

--
-- Indexes for table `fs_mailbox_message`
--
ALTER TABLE `fs_mailbox_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_message_folder` (`folder`),
  ADD KEY `mailbox_message_FKIndex1` (`mailbox_id`, `read`);

--
-- Indexes for table `fs_mailchange`
--
ALTER TABLE `fs_mailchange`
  ADD PRIMARY KEY (`foodsaver_id`);

--
-- Indexes for table `fs_mail_error`
--
ALTER TABLE `fs_mail_error`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mail_error_foodsaver_id` (`foodsaver_id`),
  ADD KEY `mail_error_FKIndex1` (`send_mail_id`);

--
-- Indexes for table `fs_message`
--
ALTER TABLE `fs_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_recip_id` (`recip_id`),
  ADD KEY `message_sender_id` (`sender_id`),
  ADD KEY `recd` (`recd`),
  ADD KEY `unread` (`unread`);

--
-- Indexes for table `fs_message_tpl`
--
ALTER TABLE `fs_message_tpl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_tpl_FKIndex1` (`language_id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `fs_msg`
--
ALTER TABLE `fs_msg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_FKIndex1` (`foodsaver_id`),
  ADD KEY `message_conversationTimeIndex` (`conversation_id`, `time`);

--
-- Indexes for table `fs_mumbleuser`
--
ALTER TABLE `fs_mumbleuser`
  ADD PRIMARY KEY (`foodsaver_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `fs_partnerschaft`
--
ALTER TABLE `fs_partnerschaft`
  ADD PRIMARY KEY (`foodsaver_id`, `partner_id`, `form`);

--
-- Indexes for table `fs_pass_gen`
--
ALTER TABLE `fs_pass_gen`
  ADD PRIMARY KEY (`foodsaver_id`, `date`);

--
-- Indexes for table `fs_pass_request`
--
ALTER TABLE `fs_pass_request`
  ADD PRIMARY KEY (`foodsaver_id`);

--
-- Indexes for table `fs_plz`
--
ALTER TABLE `fs_plz`
  ADD PRIMARY KEY (`plz`),
  ADD KEY `plz_region_id2` (`geoRegion_id`),
  ADD KEY `plz_bundesland_id` (`bundesland_id`),
  ADD KEY `plz_stadtkennzeichen` (`stadt_kennzeichen_id`),
  ADD KEY `plz_stadt_id` (`stadt_id`),
  ADD KEY `land_id` (`land_id`);

--
-- Indexes for table `fs_pushqueue`
--
ALTER TABLE `fs_pushqueue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pushqueue_status` (`status`),
  ADD KEY `pushqueue_message_id` (`message_id`);

--
-- Indexes for table `fs_question`
--
ALTER TABLE `fs_question`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_question_has_quiz`
--
ALTER TABLE `fs_question_has_quiz`
  ADD PRIMARY KEY (`question_id`, `quiz_id`),
  ADD KEY `question_has_quiz_FKIndex1` (`question_id`),
  ADD KEY `question_has_quiz_FKIndex2` (`quiz_id`);

--
-- Indexes for table `fs_question_has_wallpost`
--
ALTER TABLE `fs_question_has_wallpost`
  ADD PRIMARY KEY (`question_id`, `wallpost_id`),
  ADD KEY `question_has_wallpost_FKIndex1` (`question_id`),
  ADD KEY `question_has_wallpost_FKIndex2` (`wallpost_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_quiz`
--
ALTER TABLE `fs_quiz`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_quiz_session`
--
ALTER TABLE `fs_quiz_session`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_result_FKIndex1` (`quiz_id`),
  ADD KEY `quiz_result_FKIndex2` (`foodsaver_id`);

--
-- Indexes for table `fs_rating`
--
ALTER TABLE `fs_rating`
  ADD PRIMARY KEY (`foodsaver_id`, `rater_id`, `ratingtype`),
  ADD KEY `fk_foodsaver_has_foodsaver_foodsaver1_idx` (`rater_id`),
  ADD KEY `fk_foodsaver_has_foodsaver_foodsaver_idx` (`foodsaver_id`);

--
-- Indexes for table `fs_region`
--
ALTER TABLE `fs_region`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `fs_report`
--
ALTER TABLE `fs_report`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_FKIndex1` (`foodsaver_id`),
  ADD KEY `report_reporter` (`reporter_id`),
  ADD KEY `report_betrieb` (`betrieb_id`);

--
-- Indexes for table `fs_send_email`
--
ALTER TABLE `fs_send_email`
  ADD PRIMARY KEY (`id`),
  ADD KEY `send_email_FKIndex1` (`foodsaver_id`);

--
-- Indexes for table `fs_stadt`
--
ALTER TABLE `fs_stadt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_stadtteil`
--
ALTER TABLE `fs_stadtteil`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stadtteil_FKIndex1` (`stadt_id`);

--
-- Indexes for table `fs_stadt_kennzeichen`
--
ALTER TABLE `fs_stadt_kennzeichen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_stat_abholmengen`
--
ALTER TABLE `fs_stat_abholmengen`
  ADD UNIQUE KEY `betrieb_id` (`betrieb_id`, `date`);

--
-- Indexes for table `fs_theme`
--
ALTER TABLE `fs_theme`
  ADD PRIMARY KEY (`id`),
  ADD KEY `theme_FKIndex1` (`foodsaver_id`),
  ADD KEY `last_post_id` (`last_post_id`),
  ADD KEY `active` (`active`);

--
-- Indexes for table `fs_theme_follower`
--
ALTER TABLE `fs_theme_follower`
  ADD PRIMARY KEY (`foodsaver_id`, `theme_id`),
  ADD KEY `infotype` (`infotype`);

--
-- Indexes for table `fs_theme_post`
--
ALTER TABLE `fs_theme_post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `theme_post_FKIndex1` (`foodsaver_id`),
  ADD KEY `theme_post_FKIndex2` (`theme_id`),
  ADD KEY `reply_post` (`reply_post`);

--
-- Indexes for table `fs_upgrade_request`
--
ALTER TABLE `fs_upgrade_request`
  ADD PRIMARY KEY (`foodsaver_id`, `rolle`, `bezirk_id`);

--
-- Indexes for table `fs_user`
--
ALTER TABLE `fs_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_FKIndex1` (`user_type_id`),
  ADD KEY `fsid` (`fsid`);

--
-- Indexes for table `fs_usernotes_has_wallpost`
--
ALTER TABLE `fs_usernotes_has_wallpost`
  ADD PRIMARY KEY (`usernotes_id`, `wallpost_id`),
  ADD KEY `usernotes_has_wallpost_FKIndex1` (`usernotes_id`),
  ADD KEY `usernotes_has_wallpost_FKIndex2` (`wallpost_id`),
  ADD KEY `usernotes_id` (`usernotes_id`),
  ADD KEY `wallpost_id` (`wallpost_id`);

--
-- Indexes for table `fs_user_type`
--
ALTER TABLE `fs_user_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_wallpost`
--
ALTER TABLE `fs_wallpost`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallpost_FKIndex1` (`foodsaver_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fs_answer`
--
ALTER TABLE `fs_answer`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 464;
--
-- AUTO_INCREMENT for table `fs_autokennzeichen`
--
ALTER TABLE `fs_autokennzeichen`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 581;
--
-- AUTO_INCREMENT for table `fs_basket`
--
ALTER TABLE `fs_basket`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 38738;
--
-- AUTO_INCREMENT for table `fs_bell`
--
ALTER TABLE `fs_bell`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 208493;
--
-- AUTO_INCREMENT for table `fs_betrieb`
--
ALTER TABLE `fs_betrieb`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 15906;
--
-- AUTO_INCREMENT for table `fs_betrieb_kategorie`
--
ALTER TABLE `fs_betrieb_kategorie`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 266;
--
-- AUTO_INCREMENT for table `fs_betrieb_notiz`
--
ALTER TABLE `fs_betrieb_notiz`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 397095;
--
-- AUTO_INCREMENT for table `fs_betrieb_status`
--
ALTER TABLE `fs_betrieb_status`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 7;
--
-- AUTO_INCREMENT for table `fs_bezirk`
--
ALTER TABLE `fs_bezirk`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 1330;
--
-- AUTO_INCREMENT for table `fs_blog_entry`
--
ALTER TABLE `fs_blog_entry`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 220;
--
-- AUTO_INCREMENT for table `fs_bundesland`
--
ALTER TABLE `fs_bundesland`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 17;
--
-- AUTO_INCREMENT for table `fs_contact`
--
ALTER TABLE `fs_contact`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 18626;
--
-- AUTO_INCREMENT for table `fs_content`
--
ALTER TABLE `fs_content`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 50;
--
-- AUTO_INCREMENT for table `fs_conversation`
--
ALTER TABLE `fs_conversation`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 682361;
--
-- AUTO_INCREMENT for table `fs_document`
--
ALTER TABLE `fs_document`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 26;
--
-- AUTO_INCREMENT for table `fs_event`
--
ALTER TABLE `fs_event`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 4126;
--
-- AUTO_INCREMENT for table `fs_event_registration`
--
ALTER TABLE `fs_event_registration`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 994;
--
-- AUTO_INCREMENT for table `fs_event_workshops`
--
ALTER TABLE `fs_event_workshops`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 47;
--
-- AUTO_INCREMENT for table `fs_event_workshop_registration`
--
ALTER TABLE `fs_event_workshop_registration`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2921;
--
-- AUTO_INCREMENT for table `fs_fairteiler`
--
ALTER TABLE `fs_fairteiler`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 707;
--
-- AUTO_INCREMENT for table `fs_faq`
--
ALTER TABLE `fs_faq`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 17;
--
-- AUTO_INCREMENT for table `fs_faq_category`
--
ALTER TABLE `fs_faq_category`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 8;
--
-- AUTO_INCREMENT for table `fs_fetchdate`
--
ALTER TABLE `fs_fetchdate`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 29601;
--
-- AUTO_INCREMENT for table `fs_foodsaver`
--
ALTER TABLE `fs_foodsaver`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 151030;
--
-- AUTO_INCREMENT for table `fs_foodsaver_archive`
--
ALTER TABLE `fs_foodsaver_archive`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 150886;
--
-- AUTO_INCREMENT for table `fs_geoRegion`
--
ALTER TABLE `fs_geoRegion`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 445;
--
-- AUTO_INCREMENT for table `fs_glocke`
--
ALTER TABLE `fs_glocke`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 425246;
--
-- AUTO_INCREMENT for table `fs_item`
--
ALTER TABLE `fs_item`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 1112;
--
-- AUTO_INCREMENT for table `fs_item_class`
--
ALTER TABLE `fs_item_class`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 12;
--
-- AUTO_INCREMENT for table `fs_item_type`
--
ALTER TABLE `fs_item_type`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2;
--
-- AUTO_INCREMENT for table `fs_kette`
--
ALTER TABLE `fs_kette`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 398;
--
-- AUTO_INCREMENT for table `fs_land`
--
ALTER TABLE `fs_land`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fs_language`
--
ALTER TABLE `fs_language`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2;
--
-- AUTO_INCREMENT for table `fs_lebensmittel`
--
ALTER TABLE `fs_lebensmittel`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 13;
--
-- AUTO_INCREMENT for table `fs_location`
--
ALTER TABLE `fs_location`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 4284;
--
-- AUTO_INCREMENT for table `fs_login`
--
ALTER TABLE `fs_login`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 6138537;
--
-- AUTO_INCREMENT for table `fs_mailbox`
--
ALTER TABLE `fs_mailbox`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 28458;
--
-- AUTO_INCREMENT for table `fs_mailbox_message`
--
ALTER TABLE `fs_mailbox_message`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 102410;
--
-- AUTO_INCREMENT for table `fs_mail_error`
--
ALTER TABLE `fs_mail_error`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fs_message`
--
ALTER TABLE `fs_message`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2570714;
--
-- AUTO_INCREMENT for table `fs_message_tpl`
--
ALTER TABLE `fs_message_tpl`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 30;
--
-- AUTO_INCREMENT for table `fs_msg`
--
ALTER TABLE `fs_msg`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 4362369;
--
-- AUTO_INCREMENT for table `fs_partnerschaft`
--
ALTER TABLE `fs_partnerschaft`
  MODIFY `foodsaver_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 8765;
--
-- AUTO_INCREMENT for table `fs_pushqueue`
--
ALTER TABLE `fs_pushqueue`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 35328;
--
-- AUTO_INCREMENT for table `fs_question`
--
ALTER TABLE `fs_question`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 115;
--
-- AUTO_INCREMENT for table `fs_quiz`
--
ALTER TABLE `fs_quiz`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 4;
--
-- AUTO_INCREMENT for table `fs_quiz_session`
--
ALTER TABLE `fs_quiz_session`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 44736;
--
-- AUTO_INCREMENT for table `fs_region`
--
ALTER TABLE `fs_region`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fs_report`
--
ALTER TABLE `fs_report`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 3040;
--
-- AUTO_INCREMENT for table `fs_send_email`
--
ALTER TABLE `fs_send_email`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 883;
--
-- AUTO_INCREMENT for table `fs_stadt`
--
ALTER TABLE `fs_stadt`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 6499;
--
-- AUTO_INCREMENT for table `fs_stadtteil`
--
ALTER TABLE `fs_stadtteil`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fs_stadt_kennzeichen`
--
ALTER TABLE `fs_stadt_kennzeichen`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 17;
--
-- AUTO_INCREMENT for table `fs_theme`
--
ALTER TABLE `fs_theme`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 29123;
--
-- AUTO_INCREMENT for table `fs_theme_post`
--
ALTER TABLE `fs_theme_post`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 162599;
--
-- AUTO_INCREMENT for table `fs_user`
--
ALTER TABLE `fs_user`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 888;
--
-- AUTO_INCREMENT for table `fs_user_type`
--
ALTER TABLE `fs_user_type`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 3;
--
-- AUTO_INCREMENT for table `fs_wallpost`
--
ALTER TABLE `fs_wallpost`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 69130;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `fs_bezirk`
--
ALTER TABLE `fs_bezirk`
  ADD CONSTRAINT `fs_bezirk_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `fs_bezirk` (`id`)
  ON UPDATE CASCADE,
  ADD CONSTRAINT `fs_bezirk_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `fs_bezirk` (`id`)
  ON UPDATE CASCADE;

--
-- Constraints for table `fs_bezirk_closure`
--
ALTER TABLE `fs_bezirk_closure`
  ADD CONSTRAINT `fs_bezirk_closure_ibfk_1` FOREIGN KEY (`bezirk_id`) REFERENCES `fs_bezirk` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `fs_bezirk_closure_ibfk_2` FOREIGN KEY (`ancestor_id`) REFERENCES `fs_bezirk` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `fs_bezirk_closure_ibfk_3` FOREIGN KEY (`bezirk_id`) REFERENCES `fs_bezirk` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `fs_bezirk_closure_ibfk_4` FOREIGN KEY (`ancestor_id`) REFERENCES `fs_bezirk` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;

