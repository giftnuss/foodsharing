CREATE TABLE `fs_reaction` (
  `target`     VARCHAR(63) NOT NULL,
  `time`        DATETIME NOT NULL,
  `foodsaver_id` INT       NOT NULL,
  `emoji`    VARCHAR(63),

  INDEX (`target`),
  UNIQUE KEY `target-foodsaver-emoji` (`target`, `foodsaver_id`, `emoji`)
)
  ENGINE = InnoDB;
