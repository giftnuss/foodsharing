ALTER TABLE fs_foodsaver ADD `privacy_policy_accepted_date` DATETIME DEFAULT NULL after `anmeldedatum`;
ALTER TABLE fs_foodsaver_archive ADD `privacy_policy_accepted_date` DATETIME DEFAULT NULL after `anmeldedatum`;
ALTER TABLE fs_foodsaver ADD `privacy_notice_accepted_date` DATETIME DEFAULT NULL after `anmeldedatum`;
ALTER TABLE fs_foodsaver_archive ADD `privacy_notice_accepted_date` DATETIME DEFAULT NULL after `anmeldedatum`;
