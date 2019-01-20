CREATE TABLE IF NOT EXISTS `fs_email_bounces` (
                                          `email` VARCHAR(255)    NOT NULL,
                                          `bounced_at`   DATETIME        NOT NULL,
                                          `bounce_category` VARCHAR(255) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

ALTER TABLE `fs_email_bounces` ADD index  `email` (email);
