CREATE TABLE `fs_poll` (
  `id` int(10) unsigned NOT NULL,
  `region_id` int(10) unsigned NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scope` tinyint(2) unsigned DEFAULT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`region_id`) REFERENCES `fs_bezirk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fs_poll_has_options` (
    `poll_id`      int(10) unsigned    NOT NULL,
    `option`       tinyint(2) unsigned NOT NULL,
    `option_text`  varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `upvotes`      int(10) signed      NOT NULL,
    `neutralvotes` int(10) signed      NOT NULL,
    `downvotes`    int(10) signed      NOT NULL,
    PRIMARY KEY (`poll_id`, `option`),
    FOREIGN KEY (`poll_id`) REFERENCES `fs_poll` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE `fs_foodsaver_has_poll` (
    `foodsaver_id` int(10) unsigned NOT NULL,
    `poll_id`      int(10) unsigned NOT NULL,
    `has_voted`    int(1)           NOT NULL DEFAULT '0',
    `time`         timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`foodsaver_id`, `poll_id`),
    FOREIGN KEY (`foodsaver_id`) REFERENCES `fs_foodsaver` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`poll_id`) REFERENCES `fs_poll` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
