
-- -------------------------------------------------
-- add constraint between fs_faq and fs_faq_category
-- -------------------------------------------------
ALTER TABLE `fs_faq`
  ADD CONSTRAINT `fs_faq_category_idfk_1` FOREIGN KEY (`faq_kategorie_id`) REFERENCES `fs_faq_category` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

-- --------------------------------------------------
-- ensure that not existing foodsavers do not break a
-- constriant between fs_faq and fs_foodsaver by
-- setting those id's to NULL
-- --------------------------------------------------
ALTER TABLE `fs_faq` MODIFY COLUMN `foodsaver_id` INT(10) UNSIGNED;

UPDATE `fs_faq`
SET `foodsaver_id`= NULL 
WHERE NOT `foodsaver_id` IN (Select `ID`from `fs_foodsaver`);

-- ----------------------------------------------
-- add constraint between fs_faq and fs_foodsaver
-- ----------------------------------------------
ALTER TABLE `fs_faq`
  ADD CONSTRAINT `fs_foodsaver_idfk_1` FOREIGN KEY (`foodsaver_id`) REFERENCES `fs_foodsaver` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;


