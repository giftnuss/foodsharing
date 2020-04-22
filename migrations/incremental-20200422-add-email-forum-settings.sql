-- It would be better to rename `infotype` to `email_notification` after checking where/if it still is implemented.
-- This would need a hotfix and should probably be done after release

ALTER TABLE `fs_theme_follower` ADD `bell_notification` TINYINT(1) NOT NULL DEFAULT 1 AFTER `infotype`;
ALTER TABLE `fs_theme_follower` CHANGE `infotype` `infotype` TINYINT(1) NOT NULL DEFAULT 0;