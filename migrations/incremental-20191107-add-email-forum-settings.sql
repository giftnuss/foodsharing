--ALTER TABLE `fs_theme_follower` CHANGE `infotype` `email_notification` BIT NOT NULL DEFAULT 0 AFTER bell_notification;


ALTER TABLE `fs_theme_follower` ADD `bell_notification` BIT NOT NULL DEFAULT 0 AFTER `infotype`;
