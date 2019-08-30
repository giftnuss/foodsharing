ALTER TABLE `fs_msg` ADD `is_htmlentity_encoded` TINYINT(1) NOT NULL DEFAULT '1' AFTER `time`;
ALTER TABLE `fs_conversation` ADD `last_message_is_htmlentity_encoded` TINYINT(1) NOT NULL DEFAULT '1' AFTER `last_message`;
