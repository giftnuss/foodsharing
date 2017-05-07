DROP TABLE IF EXISTS fs_abholen;
DELETE FROM fs_abholzeiten WHERE betrieb_id NOT IN (SELECT id FROM fs_betrieb);
DELETE FROM fs_answer WHERE question_id NOT IN (SELECT id FROM fs_question);
DELETE FROM fs_apitoken WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_application_has_wallpost WHERE application_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_application_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
DELETE FROM fs_basket_anfrage WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_basket_anfrage WHERE basket_id NOT IN (SELECT id FROM fs_basket);
DELETE FROM fs_basket_has_wallpost WHERE basket_id NOT IN (SELECT id FROM fs_basket);
DELETE FROM fs_basket_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
DELETE FROM fs_bezirk_has_theme WHERE theme_id NOT IN (SELECT id FROM fs_theme);
DELETE FROM fs_bezirk_has_theme WHERE bezirk_id NOT IN (SELECT id FROM fs_bezirk);
DELETE FROM fs_bezirk_has_wallpost WHERE bezirk_id NOT IN (SELECT id FROM fs_bezirk);
DELETE FROM fs_bezirk_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
DELETE FROM fs_botschafter WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_botschafter WHERE bezirk_id NOT IN (SELECT id FROM fs_bezirk);
DELETE FROM fs_buddy WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_buddy WHERE buddy_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_email_status WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_email_status WHERE email_id NOT IN (SELECT id FROM fs_send_email);
DELETE FROM fs_event_has_wallpost WHERE event_id NOT IN (SELECT id FROM fs_event);
DELETE FROM fs_event_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
DELETE FROM fs_fairteiler_follower WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_fairteiler_follower WHERE fairteiler_id NOT IN (SELECT id FROM fs_fairteiler);
DELETE FROM fs_fairteiler_has_wallpost WHERE fairteiler_id NOT IN (SELECT id FROM fs_fairteiler);
DELETE FROM fs_fairteiler_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
DELETE FROM fs_foodsaver_has_bell WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_foodsaver_has_bell WHERE bell_id NOT IN (SELECT id FROM fs_bell);
DELETE FROM fs_foodsaver_has_bezirk WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_foodsaver_has_bezirk WHERE bezirk_id NOT IN (SELECT id FROM fs_bezirk);
DELETE FROM fs_foodsaver_has_contact WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_foodsaver_has_contact WHERE contact_id NOT IN (SELECT id FROM fs_contact);
DELETE FROM fs_foodsaver_has_conversation WHERE conversation_id NOT IN (SELECT id FROM fs_conversation);
DELETE FROM fs_foodsaver_has_event WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_foodsaver_has_event WHERE event_id NOT IN (SELECT id FROM fs_event);
DELETE FROM fs_foodsaver_has_wallpost WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_foodsaver_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
DELETE FROM fs_fsreport_has_wallpost WHERE fsreport_id NOT IN (SELECT id FROM fs_report);
DELETE FROM fs_fsreport_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
DELETE FROM fs_mailbox_member WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_mailbox_member WHERE mailbox_id NOT IN (SELECT id FROM fs_mailbox);
DELETE FROM fs_mailbox_message WHERE mailbox_id NOT IN (SELECT id FROM fs_mailbox);
DELETE FROM fs_mailchange WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver) OR `time` < '2017-04-01 00:00:00';
DELETE FROM fs_msg WHERE conversation_id NOT IN (SELECT ID FROM fs_conversation);
DELETE FROM fs_pass_gen WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_pass_gen WHERE bot_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_pass_request WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver) OR `time` < '2017-04-01 00:00:00';
DELETE FROM fs_question_has_quiz WHERE question_id NOT IN (SELECT id FROM fs_question);
DELETE FROM fs_question_has_quiz WHERE quiz_id NOT IN (SELECT id FROM fs_quiz);
DELETE FROM fs_question_has_wallpost WHERE question_id NOT IN (SELECT id FROM fs_question);
DELETE FROM fs_question_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
DELETE FROM fs_quiz_session WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_rating WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_rating WHERE rater_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_theme_follower WHERE theme_id NOT IN (SELECT id FROM fs_theme);
DELETE FROM fs_theme_follower WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_theme_post WHERE theme_id NOT IN (SELECT id FROM fs_theme);
DELETE FROM fs_usernotes_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);

ALTER TABLE fs_foodsaver MODIFY COLUMN last_login datetime NULL;
ALTER TABLE fs_foodsaver MODIFY COLUMN plz varchar(10) NULL;
ALTER TABLE fs_foodsaver MODIFY COLUMN stadt varchar(100) NULL;
ALTER TABLE fs_foodsaver MODIFY COLUMN lat varchar(20) NULL;
ALTER TABLE fs_foodsaver MODIFY COLUMN lon varchar(20) NULL;
ALTER TABLE fs_foodsaver MODIFY COLUMN photo varchar(50) NULL;
ALTER TABLE fs_foodsaver MODIFY COLUMN tox varchar(255) NULL;
ALTER TABLE fs_foodsaver MODIFY COLUMN homepage varchar(255) NULL;
ALTER TABLE fs_foodsaver MODIFY COLUMN github varchar(255) NULL;
ALTER TABLE fs_foodsaver MODIFY COLUMN twitter varchar(255) NULL;
ALTER TABLE fs_foodsaver DROP COLUMN fs_id;
ALTER TABLE fs_foodsaver MODIFY COLUMN newsletter tinyint(1) DEFAULT 0;
ALTER TABLE fs_conversation MODIFY COLUMN `name` varchar(40) NULL, MODIFY COLUMN `start_foodsaver_id` int unsigned NULL;
ALTER TABLE fs_betrieb MODIFY COLUMN lat varchar(20) NULL, MODIFY COLUMN lon varchar(20) NULL;
ALTER TABLE fs_betrieb MODIFY COLUMN kette_id int unsigned NULL, MODIFY COLUMN betrieb_kategorie_id int NULL;
ALTER TABLE fs_betrieb MODIFY COLUMN `name` varchar(120), MODIFY COLUMN str varchar(120);
ALTER TABLE fs_betrieb MODIFY COLUMN `begin` date NULL, MODIFY COLUMN besonderheiten text NULL, MODIFY COLUMN public_info varchar(200) NULL;
ALTER TABLE fs_contact MODIFY COLUMN id int unsigned;
ALTER TABLE fs_foodsaver_has_contact MODIFY COLUMN foodsaver_id int unsigned;
ALTER TABLE fs_foodsaver_has_contact MODIFY COLUMN contact_id int unsigned;
ALTER TABLE fs_verify_history MODIFY COLUMN fs_id int unsigned;
ALTER TABLE fs_verify_history MODIFY COLUMN bot_id int unsigned;
ALTER TABLE fs_verify_history MODIFY COLUMN change_status tinyint(1);
ALTER TABLE fs_foodsaver ADD COLUMN deleted_at datetime NULL;
ALTER TABLE fs_betrieb ADD COLUMN deleted_at datetime NULL;
ALTER TABLE fs_pass_gen MODIFY COLUMN bot_id int unsigned;
ALTER TABLE fs_pass_gen ADD INDEX(bot_id);

-- following SQL implements suggestions from database_fixup.md
INSERT IGNORE INTO fs_foodsaver (id, deleted_at)
SELECT id, '2000-01-01' FROM (
  (SELECT DISTINCT foodsaver_id as id FROM fs_abholer) UNION DISTINCT
  (SELECT DISTINCT foodsaver_id as id FROM fs_foodsaver_has_conversation) UNION DISTINCT
  (SELECT DISTINCT foodsaver_id as id FROM fs_betrieb_notiz) UNION DISTINCT
  (SELECT DISTINCT foodsaver_id as id FROM fs_betrieb_team) UNION DISTINCT
  (SELECT DISTINCT foodsaver_id as id FROM fs_event) UNION DISTINCT
  (SELECT DISTINCT foodsaver_id as id FROM fs_msg) UNION DISTINCT
  (SELECT DISTINCT bot_id as id FROM fs_pass_gen) UNION DISTINCT
  (SELECT DISTINCT foodsaver_id as id FROM fs_theme_post) UNION DISTINCT
  (SELECT DISTINCT foodsaver_id as id FROM fs_theme_follower) UNION DISTINCT
  (SELECT DISTINCT foodsaver_id as id FROM fs_wallpost)
) fs;

INSERT IGNORE INTO fs_betrieb (id, deleted_at)
SELECT id, '2000-01-01' FROM (
  (SELECT DISTINCT betrieb_id AS id FROM fs_abholer) UNION DISTINCT
  (SELECT DISTINCT betrieb_id AS id FROM fs_stat_abholmengen) UNION DISTINCT
  (SELECT DISTINCT betrieb_id AS id FROM fs_betrieb_notiz) UNION DISTINCT
  (SELECT DISTINCT betrieb_id AS id FROM fs_fetchdate) UNION DISTINCT
  (SELECT DISTINCT betrieb_id AS id FROM fs_betrieb_team)
) b;

ALTER TABLE fs_location ENGINE=InnoDB;

ALTER TABLE fs_abholzeiten ADD CONSTRAINT FOREIGN KEY (betrieb_id) REFERENCES fs_betrieb(id) ON DELETE CASCADE;

ALTER TABLE fs_answer ADD CONSTRAINT FOREIGN KEY (question_id) REFERENCES fs_question(id) ON DELETE CASCADE;

ALTER TABLE `fs_apitoken` CHANGE `foodsaver_id` `foodsaver_id` INT UNSIGNED NOT NULL;
ALTER TABLE fs_apitoken ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

ALTER TABLE fs_application_has_wallpost ADD CONSTRAINT FOREIGN KEY (application_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_application_has_wallpost ADD CONSTRAINT FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

ALTER TABLE fs_basket_anfrage ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_basket_anfrage ADD CONSTRAINT FOREIGN KEY (basket_id) REFERENCES fs_basket(id) ON DELETE CASCADE;

ALTER TABLE fs_basket_has_wallpost ADD CONSTRAINT FOREIGN KEY (basket_id) REFERENCES fs_basket(id) ON DELETE CASCADE;
ALTER TABLE fs_basket_has_wallpost ADD CONSTRAINT FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;


ALTER TABLE fs_bezirk_has_theme ADD CONSTRAINT FOREIGN KEY (bezirk_id) REFERENCES fs_bezirk(id) ON DELETE CASCADE;
ALTER TABLE fs_bezirk_has_theme ADD CONSTRAINT FOREIGN KEY (theme_id) REFERENCES fs_theme(id) ON DELETE CASCADE;

ALTER TABLE fs_bezirk_has_wallpost ADD CONSTRAINT FOREIGN KEY (bezirk_id) REFERENCES fs_bezirk(id) ON DELETE CASCADE;
ALTER TABLE fs_bezirk_has_wallpost ADD CONSTRAINT FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

ALTER TABLE fs_botschafter ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_botschafter ADD CONSTRAINT FOREIGN KEY (bezirk_id) REFERENCES fs_bezirk(id) ON DELETE CASCADE;

ALTER TABLE fs_buddy ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_buddy ADD CONSTRAINT FOREIGN KEY (buddy_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

ALTER TABLE fs_email_status ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_email_status ADD CONSTRAINT FOREIGN KEY (email_id) REFERENCES fs_send_email(id) ON DELETE CASCADE;

ALTER TABLE fs_event MODIFY COLUMN bezirk_id int unsigned NULL;
ALTER TABLE fs_event MODIFY COLUMN location_id int unsigned NULL;
UPDATE fs_event SET bezirk_id = NULL WHERE bezirk_id NOT IN (SELECT id FROM fs_bezirk);
UPDATE fs_event SET location_id = NULL WHERE location_id NOT IN (SELECT id FROM fs_location);
ALTER TABLE fs_event ADD CONSTRAINT FOREIGN KEY (bezirk_id) REFERENCES fs_bezirk(id) ON DELETE SET NULL;
ALTER TABLE fs_event ADD CONSTRAINT FOREIGN KEY (location_id) REFERENCES fs_location(id) ON DELETE CASCADE;

ALTER TABLE fs_event_has_wallpost ADD CONSTRAINT FOREIGN KEY (event_id) REFERENCES fs_event(id) ON DELETE CASCADE;
ALTER TABLE fs_event_has_wallpost ADD CONSTRAINT FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

ALTER TABLE fs_fairteiler MODIFY COLUMN bezirk_id int unsigned NULL;
UPDATE fs_fairteiler SET bezirk_id = NULL WHERE bezirk_id NOT IN (SELECT id FROM fs_bezirk);
ALTER TABLE fs_fairteiler ADD CONSTRAINT FOREIGN KEY (bezirk_id) REFERENCES fs_bezirk(id) ON DELETE SET NULL;

ALTER TABLE fs_fairteiler_follower ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_fairteiler_follower ADD CONSTRAINT FOREIGN KEY (fairteiler_id) REFERENCES fs_fairteiler(id) ON DELETE CASCADE;

ALTER TABLE fs_fairteiler_has_wallpost ADD CONSTRAINT FOREIGN KEY (fairteiler_id) REFERENCES fs_fairteiler(id) ON DELETE CASCADE;
ALTER TABLE fs_fairteiler_has_wallpost ADD CONSTRAINT FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

ALTER TABLE fs_foodsaver_has_bell ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_foodsaver_has_bell ADD CONSTRAINT FOREIGN KEY (bell_id) REFERENCES fs_bell(id) ON DELETE CASCADE;

ALTER TABLE fs_foodsaver_has_bezirk ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_foodsaver_has_bezirk ADD CONSTRAINT FOREIGN KEY (bezirk_id) REFERENCES fs_bezirk(id) ON DELETE CASCADE;

ALTER TABLE fs_foodsaver_has_contact ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_foodsaver_has_contact ADD CONSTRAINT FOREIGN KEY (contact_id) REFERENCES fs_contact(id) ON DELETE CASCADE;

ALTER TABLE fs_foodsaver_has_conversation ADD CONSTRAINT FOREIGN KEY (conversation_id) REFERENCES fs_conversation(id) ON DELETE CASCADE;

ALTER TABLE fs_foodsaver_has_event ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_foodsaver_has_event ADD CONSTRAINT FOREIGN KEY (event_id) REFERENCES fs_event(id) ON DELETE CASCADE;

DROP TABLE fs_foodsaver_has_fairteiler;
DROP TABLE fs_foodsaver_has_fetchdate;

ALTER TABLE fs_foodsaver_has_wallpost ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_foodsaver_has_wallpost ADD CONSTRAINT FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

ALTER TABLE fs_fsreport_has_wallpost ADD CONSTRAINT FOREIGN KEY (fsreport_id) REFERENCES fs_report(id) ON DELETE CASCADE;
ALTER TABLE fs_fsreport_has_wallpost ADD CONSTRAINT FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

ALTER TABLE fs_mailbox_member ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_mailbox_member ADD CONSTRAINT FOREIGN KEY (mailbox_id) REFERENCES fs_mailbox(id) ON DELETE CASCADE;

ALTER TABLE fs_mailbox_message ADD CONSTRAINT FOREIGN KEY (mailbox_id) REFERENCES fs_mailbox(id) ON DELETE CASCADE;

ALTER TABLE fs_mailchange ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

ALTER TABLE fs_msg ADD CONSTRAINT FOREIGN KEY (conversation_id) REFERENCES fs_conversation(id) ON DELETE CASCADE;

ALTER TABLE fs_pass_gen ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_pass_gen ADD CONSTRAINT FOREIGN KEY (bot_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

ALTER TABLE fs_pass_request ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

ALTER TABLE fs_question_has_quiz ADD CONSTRAINT FOREIGN KEY (question_id) REFERENCES fs_question(id) ON DELETE CASCADE;
ALTER TABLE fs_question_has_quiz ADD CONSTRAINT FOREIGN KEY (quiz_id) REFERENCES fs_quiz(id) ON DELETE CASCADE;

ALTER TABLE fs_question_has_wallpost ADD CONSTRAINT FOREIGN KEY (question_id) REFERENCES fs_question(id) ON DELETE CASCADE;
ALTER TABLE fs_question_has_wallpost ADD CONSTRAINT FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

ALTER TABLE fs_quiz_session ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

ALTER TABLE fs_rating ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_rating ADD CONSTRAINT FOREIGN KEY (rater_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

ALTER TABLE fs_stat_abholmengen ADD CONSTRAINT FOREIGN KEY (betrieb_id) REFERENCES fs_betrieb(id) ON DELETE CASCADE;

ALTER TABLE fs_theme_follower ADD CONSTRAINT FOREIGN KEY (theme_id) REFERENCES fs_theme(id) ON DELETE CASCADE;
ALTER TABLE fs_theme_follower ADD CONSTRAINT FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

ALTER TABLE fs_theme_post ADD CONSTRAINT FOREIGN KEY (theme_id) REFERENCES fs_theme(id) ON DELETE CASCADE;

ALTER TABLE fs_usernotes_has_wallpost ADD CONSTRAINT FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

RENAME TABLE fs_foodsaver_archive TO fs_foodsaver_archive4;
CREATE TABLE fs_foodsaver_archive LIKE fs_foodsaver;
