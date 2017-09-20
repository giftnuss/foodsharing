ALTER TABLE `fs_pass_gen`
  ADD `bot_id` INT(10) NULL
  AFTER `date`;

CREATE TABLE `fs_verify_history` (
  `fs_id`         INT(10)  NOT NULL,
  `date`          DATETIME NOT NULL,
  `bot_id`        INT(10)  NOT NULL
  COMMENT 'ID from BOT who change the verify status',
  `change_status` INT(11)  NOT NULL
  COMMENT '0 = person gets unverify        1= person get verified',
  INDEX (`fs_id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fs_verify_history`
--
