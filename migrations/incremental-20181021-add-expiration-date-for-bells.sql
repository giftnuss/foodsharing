ALTER TABLE `fs_bell` ADD `expiration` DATE NULL DEFAULT NULL AFTER `closeable`;
ALTER TABLE `fs_bell` ADD index  `expiration` (expiration);
