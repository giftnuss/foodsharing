DROP TABLE fs_autokennzeichen;
ALTER TABLE fs_foodsaver
  DROP COLUMN autokennzeichen_id;
ALTER TABLE fs_foodsaver_archive
  DROP COLUMN autokennzeichen_id;
