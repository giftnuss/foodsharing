DROP TABLE fs_abholen;
ALTER TABLE fs_foodsaver MODIFY COLUMN last_login datetime NULL;
ALTER TABLE fs_conversation MODIFY COLUMN `name` varchar(40) NULL, MODIFY COLUMN `start_foodsaver_id` int unsigned NULL;
ALTER TABLE fs_betrieb MODIFY COLUMN lat varchar(20) NULL, MODIFY COLUMN lon varchar(20) NULL;
ALTER TABLE fs_betrieb MODIFY COLUMN kette_id int unsigned NULL, MODIFY COLUMN betrieb_kategorie_id int NULL;
ALTER TABLE fs_betrieb MODIFY COLUMN `name` varchar(120), MODIFY COLUMN str varchar(120);
ALTER TABLE fs_betrieb MODIFY COLUMN `begin` date NULL, MODIFY COLUMN besonderheiten text NULL, MODIFY COLUMN public_info varchar(200) NULL;

-- following SQL implements suggestions from database_fixup.md
INSERT IGNORE INTO fs_foodsaver (id) SELECT foodsaver_id FROM fs_abholer;
INSERT IGNORE INTO fs_foodsaver (id) SELECT foodsaver_id FROM fs_foodsaver_has_conversation;
INSERT IGNORE INTO fs_betrieb (id) SELECT betrieb_id FROM fs_abholer;
INSERT IGNORE INTO fs_foodsaver (id) SELECT foodsaver_id FROM fs_betrieb_notiz;
INSERT IGNORE INTO fs_betrieb (id) SELECT betrieb_id FROM fs_betrieb_notiz;
INSERT IGNORE INTO fs_foodsaver (id) SELECT foodsaver_id FROM fs_betrieb_team;
INSERT IGNORE INTO fs_betrieb (id) SELECT betrieb_id FROM fs_betrieb_team;
INSERT IGNORE INTO fs_foodsaver (id) SELECT foodsaver_id FROM fs_event;
INSERT IGNORE INTO fs_betrieb (id) SELECT betrieb_id FROM fs_fetchdate;
INSERT IGNORE INTO fs_foodsaver (id) SELECT DISTINCT foodsaver_id FROM fs_msg;
INSERT IGNORE INTO fs_foodsaver (id) SELECT bot_id FROM fs_pass_gen;

DELETE FROM fs_abholzeiten WHERE betrieb_id NOT IN (SELECT id FROM fs_betrieb);
ALTER TABLE fs_abholzeiten ADD CONSTRAINT fk_betrieb_id FOREIGN KEY (betrieb_id) REFERENCES fs_betrieb(id) ON DELETE CASCADE;

DELETE FROM fs_answer WHERE question_id NOT IN (SELECT id FROM fs_question);
ALTER TABLE fs_answer ADD CONSTRAINT fk_question_id FOREIGN KEY (question_id) REFERENCES fs_question(id) ON DELETE CASCADE;

DELETE FROM fs_apitoken WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
ALTER TABLE `fs_apitoken` CHANGE `foodsaver_id` `foodsaver_id` INT UNSIGNED NOT NULL;
ALTER TABLE fs_apitoken ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

DELETE FROM fs_application_has_wallpost WHERE application_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_application_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
ALTER TABLE fs_application_has_wallpost ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (application_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_application_has_wallpost ADD CONSTRAINT fk_wallpost_id FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

DELETE FROM fs_basket_anfrage WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_basket_anfrage WHERE basket_id NOT IN (SELECT id FROM fs_basket);
ALTER TABLE fs_basket_anfrage ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_basket_anfrage ADD CONSTRAINT fk_basket_id FOREIGN KEY (basket_id) REFERENCES fs_basket(id) ON DELETE CASCADE;

DELETE FROM fs_basket_has_wallpost WHERE basket_id NOT IN (SELECT id FROM fs_basket);
DELETE FROM fs_basket_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
ALTER TABLE fs_basket_has_wallpost ADD CONSTRAINT fk_basket_id FOREIGN KEY (basket_id) REFERENCES fs_basket(id) ON DELETE CASCADE;
ALTER TABLE fs_basket_has_wallpost ADD CONSTRAINT fk_wallpost_id FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;


DELETE FROM fs_bezirk_has_theme WHERE theme_id NOT IN (SELECT id FROM fs_theme);
DELETE FROM fs_bezirk_has_theme WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
ALTER TABLE fs_bezirk_has_theme ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_bezirk_has_theme ADD CONSTRAINT fk_theme_id FOREIGN KEY (theme_id) REFERENCES fs_theme(id) ON DELETE CASCADE;

DELETE FROM fs_bezirk_has_wallpost WHERE bezirk_id NOT IN (SELECT id FROM fs_bezirk);
DELETE FROM fs_bezirk_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
ALTER TABLE fs_bezirk_has_wallpost ADD CONSTRAINT fk_bezirk_id FOREIGN KEY (bezirk_id) REFERENCES fs_bezirk(id) ON DELETE CASCADE;
ALTER TABLE fs_bezirk_has_wallpost ADD CONSTRAINT fk_wallpost_id FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

DELETE FROM fs_botschafter WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_botschafter WHERE bezirk_id NOT IN (SELECT id FROM fs_bezirk);
ALTER TABLE fs_botschafter ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_botschafter ADD CONSTRAINT fk_bezirk_id FOREIGN KEY (bezirk_id) REFERENCES fs_bezirk(id) ON DELETE CASCADE;

DELETE FROM fs_email_status WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_email_status WHERE email_id NOT IN (SELECT id FROM fs_email);
ALTER TABLE fs_email_status ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_email_status ADD CONSTRAINT fk_email_id FOREIGN KEY (email_id) REFERENCES fs_email(id) ON DELETE CASCADE;

ALTER TABLE fs_event MODIFY COLUMN bezirk_id int unsigned NULL;
ALTER TABLE fs_event MODIFY COLUMN location_id int unsigned NULL;
UPDATE fs_event SET bezirk_id = NULL WHERE bezirk_id NOT IN (SELECT id FROM fs_bezirk);
ALTER TABLE fs_event ADD CONSTRAINT fk_bezirk_id FOREIGN KEY (bezirk_id) REFERENCES fs_bezirk(id) ON DELETE SET NULL;
ALTER TABLE fs_event ADD CONSTRAINT fk_location_id FOREIGN KEY (location_id) REFERENCES fs_location(id) ON DELETE CASCADE;

DELETE FROM fs_event_has_wallpost WHERE event_id NOT IN (SELECT id FROM fs_event);
DELETE FROM fs_event_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
ALTER TABLE fs_event_has_wallpost ADD CONSTRAINT fk_event_id FOREIGN KEY (event_id) REFERENCES fs_event(id) ON DELETE CASCADE;
ALTER TABLE fs_event_has_wallpost ADD CONSTRAINT fk_wallpost_id FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

ALTER TABLE fs_fairteiler MODIFY COLUMN bezirk_id int unsigned NULL;
UPDATE fs_fairteiler SET bezirk_id = NULL WHERE bezirk_id NOT IN (SELECT id FROM fs_bezirk);
ALTER TABLE fs_fairteiler ADD CONSTRAINT fk_bezirk_id FOREIGN KEY (bezirk_id) REFERENCES fs_bezirk(id) ON DELETE SET NULL;

DELETE FROM fs_fairteiler_follower WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_fairteiler_follower WHERE fairteiler_id NOT IN (SELECT id FROM fs_fairteiler);
ALTER TABLE fs_fairteiler_follower ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_fairteiler_follower ADD CONSTRAINT fk_fairteiler_id FOREIGN KEY (fairteiler_id) REFERENCES fs_fairteiler(id) ON DELETE CASCADE;

DELETE FROM fs_fairteiler_has_wallpost WHERE fairteiler_id NOT IN (SELECT id FROM fs_fairteiler);
DELETE FROM fs_fairteiler_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
ALTER TABLE fs_fairteiler_has_wallpost ADD CONSTRAINT fk_fairteiler_id FOREIGN KEY (fairteiler_id) REFERENCES fs_fairteiler(id) ON DELETE CASCADE;
ALTER TABLE fs_fairteiler_has_wallpost ADD CONSTRAINT fk_wallpost_id FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

ALTER TABLE fs_foodsaver ADD COLUMN deleted_at datetime NULL;

RENAME TABLE fs_foodsaver_archive TO fs_foodsaver_archive3;
CREATE TABLE fs_foodsaver_archive LIKE fs_foodsaver;

DELETE FROM fs_foodsaver_has_bell WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_foodsaver_has_bell WHERE bell_id NOT IN (SELECT id FROM fs_bell);
ALTER TABLE fs_foodsaver_has_bell ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_foodsaver_has_bell ADD CONSTRAINT fk_bell_id FOREIGN KEY (bell_id) REFERENCES fs_bell(id) ON DELETE CASCADE;

DELETE FROM fs_foodsaver_has_bezirk WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_foodsaver_has_bezirk WHERE bezirk_id NOT IN (SELECT id FROM fs_bezirk);
ALTER TABLE fs_foodsaver_has_bezirk ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_foodsaver_has_bezirk ADD CONSTRAINT fk_bezirk_id FOREIGN KEY (bezirk_id) REFERENCES fs_bezirk(id) ON DELETE CASCADE;

DELETE FROM fs_foodsaver_has_contact WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_foodsaver_has_contact WHERE contact_id NOT IN (SELECT id FROM fs_contact);
ALTER TABLE fs_foodsaver_has_contact ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_foodsaver_has_contact ADD CONSTRAINT fk_contact_id FOREIGN KEY (contact_id) REFERENCES fs_contact(id) ON DELETE CASCADE;

DELETE FROM fs_foodsaver_has_conversation WHERE conversation_id NOT IN (SELECT id FROM fs_conversation);
ALTER TABLE fs_foodsaver_has_conversation ADD CONSTRAINT fk_conversation_id FOREIGN KEY (conversation_id) REFERENCES fs_conversation(id) ON DELETE CASCADE;

DELETE FROM fs_foodsaver_has_event WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_foodsaver_has_event WHERE event_id NOT IN (SELECT id FROM fs_event);
ALTER TABLE fs_foodsaver_has_event ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_foodsaver_has_event ADD CONSTRAINT fk_event_id FOREIGN KEY (event_id) REFERENCES fs_event(id) ON DELETE CASCADE;

DROP TABLE fs_foodsaver_has_fairteiler;
DROP TABLE fs_foodsaver_has_fetchdate;

DELETE FROM fs_foodsaver_has_wallpost WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_foodsaver_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
ALTER TABLE fs_foodsaver_has_wallpost ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_foodsaver_has_wallpost ADD CONSTRAINT fk_wallpost_id FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

DELETE FROM fs_fsreport_has_wallpost WHERE fsreport_id NOT IN (SELECT id FROM fs_report);
DELETE FROM fs_fsreport_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
ALTER TABLE fs_fsreport_has_wallpost ADD CONSTRAINT fk_fsreport_id FOREIGN KEY (fsreport_id) REFERENCES fs_report(id) ON DELETE CASCADE;
ALTER TABLE fs_fsreport_has_wallpost ADD CONSTRAINT fk_wallpost_id FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

DELETE FROM fs_mailbox_member WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
DELETE FROM fs_mailbox_member WHERE mailbox_id NOT IN (SELECT id FROM fs_mailbox);
ALTER TABLE fs_mailbox_member ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;
ALTER TABLE fs_mailbox_member ADD CONSTRAINT fk_mailbox_id FOREIGN KEY (mailbox_id) REFERENCES fs_mailbox(id) ON DELETE CASCADE;

DELETE FROM fs_mailbox_message WHERE mailbox_id NOT IN (SELECT id FROM fs_mailbox);
ALTER TABLE fs_mailbox_message ADD CONSTRAINT fk_mailbox_id FOREIGN KEY (mailbox_id) REFERENCES fs_mailbox(id) ON DELETE CASCADE;

DELETE FROM fs_mailchange WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver) OR `time` < '2017-04-01 00:00:00';
ALTER TABLE fs_mailchange ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

DELETE FROM fs_msg WHERE conversation_id NOT IN (SELECT ID FROM fs_conversation);
ALTER TABLE fs_msg ADD CONSTRAINT fk_conversation_id FOREIGN KEY (conversation_id) REFERENCES fs_conversation(id) ON DELETE CASCADE;

DELETE FROM fs_pass_gen WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
ALTER TABLE fs_pass_gen ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

DELETE FROM fs_pass_request WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver) OR `time` < '2017-04-01 00:00:00';
ALTER TABLE fs_pass_request ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

DELETE FROM fs_question_has_quiz WHERE question_id NOT IN (SELECT id FROM fs_question);
DELETE FROM fs_question_has_quiz WHERE quiz_id NOT IN (SELECT id FROM fs_quiz);
ALTER TABLE fs_question_has_quiz ADD CONSTRAINT fk_question_id FOREIGN KEY (question_id) REFERENCES fs_question(id) ON DELETE CASCADE;
ALTER TABLE fs_question_has_quiz ADD CONSTRAINT fk_quiz_id FOREIGN KEY (quiz_id) REFERENCES fs_quiz(id) ON DELETE CASCADE;

DELETE FROM fs_question_has_wallpost WHERE question_id NOT IN (SELECT id FROM fs_question);
DELETE FROM fs_question_has_wallpost WHERE wallpost_id NOT IN (SELECT id FROM fs_wallpost);
ALTER TABLE fs_question_has_wallpost ADD CONSTRAINT fk_question_id FOREIGN KEY (question_id) REFERENCES fs_question(id) ON DELETE CASCADE;
ALTER TABLE fs_question_has_wallpost ADD CONSTRAINT fk_wallpost_id FOREIGN KEY (wallpost_id) REFERENCES fs_wallpost(id) ON DELETE CASCADE;

DELETE FROM fs_quiz_session WHERE foodsaver_id NOT IN (SELECT id FROM fs_foodsaver);
ALTER TABLE fs_quiz_session ADD CONSTRAINT fk_foodsaver_id FOREIGN KEY (foodsaver_id) REFERENCES fs_foodsaver(id) ON DELETE CASCADE;

