ALTER TABLE fs_foodsaver
  DROP COLUMN iosid,
  DROP COLUMN gcm;
ALTER TABLE fs_foodsaver_archive
  DROP COLUMN iosid,
  DROP COLUMN gcm;
DROP TABLE fs_pushqueue;
