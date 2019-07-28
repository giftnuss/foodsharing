RENAME TABLE
    fs_abholmengen TO fs_fetchweight;

ALTER TABLE `fs_fetchweight` CHANGE `menge` `weight` DECIMAL(5,1) NOT NULL;