--ALTER TABLE `fs_theme_follower` CHANGE `infotype` `email_notification` BIT NOT NULL DEFAULT '0' AFTER theme_id;

ALTER TABLE `fs_theme_follower` ADD `bell_notification` BIT NOT NULL DEFAULT 1 AFTER `infotype`;
ALTER TABLE `fs_theme_follower` CHANGE `infotype` `infotype` TINYINT(1) NOT NULL DEFAULT 0;
