DELETE a FROM `fs_fetchdate` a INNER JOIN fs_fetchdate b ON a.betrieb_id = b.betrieb_id AND a.time = b.time AND b.id > a.id;
ALTER TABLE fs_fetchdate
    ADD UNIQUE KEY (betrieb_id, time);
