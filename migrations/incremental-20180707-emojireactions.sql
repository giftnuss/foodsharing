CREATE TABLE `fs_reaction` (
  `target`     VARCHAR(63) NOT NULL,
  `time`        DATETIME NOT NULL,
  `foodsaver_id` INT       NOT NULL,
  `key`    VARCHAR(63),

  INDEX (`target`),
  UNIQUE KEY `target-foodsaver-key` (`target`, `foodsaver_id`, `key`)
)
  ENGINE = InnoDB;
