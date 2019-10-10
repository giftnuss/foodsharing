
-- 51.874 sha1 passwords (used before 2014-12-12)
ALTER TABLE fs_foodsaver DROP IF EXISTS passwd;
ALTER TABLE fs_foodsaver_archive DROP IF EXISTS passwd;
ALTER TABLE fs_foodsaver_archive2 DROP IF EXISTS passwd;
ALTER TABLE fs_foodsaver_archive4 DROP IF EXISTS passwd;
ALTER TABLE fs_foodsaver_archive5 DROP IF EXISTS passwd;

-- 105.898 md5 passwords (used before 2018-02)
ALTER TABLE fs_foodsaver DROP IF EXISTS fs_password;
ALTER TABLE fs_foodsaver_archive DROP IF EXISTS fs_password;
ALTER TABLE fs_foodsaver_archive2 DROP IF EXISTS fs_password;
ALTER TABLE fs_foodsaver_archive4 DROP IF EXISTS fs_password;
ALTER TABLE fs_foodsaver_archive5 DROP IF EXISTS fs_password;
