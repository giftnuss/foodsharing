ALTER TABLE fs_foodsaver
  MODIFY COLUMN last_pass DATETIME NULL;
ALTER TABLE fs_foodsaver
  MODIFY COLUMN mailbox_id INT UNSIGNED NULL;
ALTER TABLE fs_foodsaver
  MODIFY COLUMN sleep_from DATE NULL;
ALTER TABLE fs_foodsaver
  MODIFY COLUMN sleep_until DATE NULL;
ALTER TABLE fs_foodsaver
  MODIFY COLUMN sleep_msg TEXT NULL;
ALTER TABLE fs_foodsaver
  DROP COLUMN last_mid;
ALTER TABLE fs_betrieb_team
  MODIFY COLUMN stat_last_update DATETIME NULL;
ALTER TABLE fs_betrieb_team
  MODIFY COLUMN stat_first_fetch DATE NULL;
ALTER TABLE fs_betrieb_team
  MODIFY COLUMN stat_last_fetch DATE NULL;
ALTER TABLE fs_betrieb_team
  MODIFY COLUMN stat_add_date DATE NULL;
ALTER TABLE fs_betrieb_team
  DROP PRIMARY KEY;
ALTER TABLE fs_betrieb_team
  ADD COLUMN id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY;
ALTER TABLE fs_betrieb_team
  ADD UNIQUE KEY (foodsaver_id, betrieb_id);
ALTER TABLE fs_conversation
  MODIFY COLUMN last_message TEXT NULL;
ALTER TABLE fs_conversation
  MODIFY COLUMN member TEXT NULL;
ALTER TABLE fs_foodsaver_has_conversation
  DROP PRIMARY KEY;
ALTER TABLE fs_foodsaver_has_conversation
  ADD COLUMN id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY;
ALTER TABLE fs_foodsaver_has_conversation
  ADD UNIQUE KEY (foodsaver_id, conversation_id);
ALTER TABLE fs_abholer
  DROP PRIMARY KEY;
ALTER TABLE fs_abholer
  ADD COLUMN id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY;
ALTER TABLE fs_abholer
  ADD UNIQUE KEY (foodsaver_id, betrieb_id, `date`);
RENAME TABLE
    fs_foodsaver_archive TO fs_foodsaver_archive5;
CREATE TABLE fs_foodsaver_archive
  LIKE fs_foodsaver;
