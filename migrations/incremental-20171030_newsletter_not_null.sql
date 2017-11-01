UPDATE fs_foodsaver
  SET newsletter = 0 WHERE newsletter IS NULL;
ALTER TABLE fs_foodsaver
  MODIFY COLUMN newsletter TINYINT(1) NOT NULL DEFAULT 0;
