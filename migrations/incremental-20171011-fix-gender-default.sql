UPDATE fs_foodsaver
  SET geschlecht = 0 WHERE geschlecht IS NULL;
ALTER TABLE fs_foodsaver
  MODIFY COLUMN geschlecht TINYINT(3) NOT NULL DEFAULT 0;
