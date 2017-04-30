INSERT INTO `fs_conversation` (`id`, `locked`, `name`, `start`, `last`, `last_foodsaver_id`, `start_foodsaver_id`, `last_message_id`, `last_message`, `member`) VALUES ('1', '1', 'betrieb bla', NULL, NULL, NULL, NULL, NULL, '', ''), ('2', '1', 'springer bla', NULL, NULL, NULL, NULL, NULL, '', '');
INSERT INTO `fs_abholer` (`foodsaver_id`, `betrieb_id`, `date`, `confirmed`) VALUES
(151032, 1, '2017-04-15 09:00:00', 1),
(151032, 1, '2017-04-19 08:00:00', 1),
(151032, 1, '2017-06-19 09:00:00', 1),
(151032, 1, '2017-06-20 09:00:00', 1);
INSERT INTO `fs_betrieb` (`id`, `betrieb_status_id`, `bezirk_id`, `added`, `plz`, `stadt`, `lat`, `lon`, `kette_id`, `betrieb_kategorie_id`, `name`, `str`, `hsnr`, `status_date`, `status`, `ansprechpartner`, `telefon`, `fax`, `email`, `begin`, `besonderheiten`, `public_info`, `public_time`, `ueberzeugungsarbeit`, `presse`, `sticker`, `abholmenge`, `team_status`, `prefetchtime`, `team_conversation_id`, `springer_conversation_id`) VALUES
(1, 0, 241, '2017-01-03', '', '', '', '', 0, 0, 'asd', '', '', '2017-01-03', 0, '', '', '', '', '0000-00-00', '', '', 0, 0, 0, 0, 0, 1, 1209600, 1, 2);
INSERT INTO `fs_betrieb_team` (`foodsaver_id`, `betrieb_id`, `verantwortlich`, `active`, `stat_last_update`, `stat_fetchcount`, `stat_first_fetch`, `stat_last_fetch`, `stat_add_date`) VALUES
(151031, 1, 0, 1, '0000-00-00 00:00:00', 0, '0000-00-00', '0000-00-00 00:00:00', '0000-00-00'),
(151032, 1, 1, 1, '0000-00-00 00:00:00', 0, '0000-00-00', '0000-00-00 00:00:00', '0000-00-00');
INSERT INTO `fs_foodsaver_has_conversation` (`foodsaver_id`, `conversation_id`, `unread`) VALUES ('151031', '1', '0'), ('151032', '1', '0'), ('151032', '2', '0');
