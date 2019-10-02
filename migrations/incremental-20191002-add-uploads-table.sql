CREATE TABLE `uploads` (
    `uuid`          CHAR(36) NOT NULL, -- string length of the uuid_v4 function in UploadsGateway.php
    `user_id`       INT(10) UNSIGNED NOT NULL,
    `sha256hash`    CHAR(64) NOT NULL,
    `mimeType`      VARCHAR(255) NOT NULL,
    `uploaded_at`   DATETIME NOT NULL,
    `lastaccess_at` DATETIME NOT NULL,
    `filesize`      INT(10) UNSIGNED NOT NULL,

    INDEX (`uuid`),
    UNIQUE KEY `uploads` (`uuid`)
)
ENGINE = InnoDB;
