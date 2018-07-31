CREATE TABLE `fs_post_reaction` (
  `post_id`     INT UNSIGNED NOT NULL,
  `time`        DATETIME NOT NULL,
  `foodsaver_id` INT       NOT NULL,
  `key`    VARCHAR(63),

  INDEX (`post_id`),
  UNIQUE KEY `post-foodsaver-key` (`post_id`, `foodsaver_id`, `key`)
)
  ENGINE = InnoDB;

ALTER TABLE `fs_post_reaction`
  ADD CONSTRAINT `fs_theme_post_idfk` FOREIGN KEY (`post_id`) REFERENCES `fs_theme_post` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;
