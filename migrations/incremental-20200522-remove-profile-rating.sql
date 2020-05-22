-- data upkeep for !1530
-- PK was previously: PRIMARY KEY (`foodsaver_id`, `rater_id`, `ratingtype`)
ALTER TABLE fs_rating DROP PRIMARY KEY;
ALTER TABLE fs_rating DROP IF EXISTS rating;
ALTER TABLE fs_rating DROP IF EXISTS ratingtype;
ALTER TABLE fs_rating ADD PRIMARY KEY(`foodsaver_id`, `rater_id`);
