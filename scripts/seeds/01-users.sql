REPLACE INTO `fs_foodsaver` (
  id,
  bezirk_id,
  verified,
  last_pass,
  rolle,
  plz,
  stadt,
  lat,
  lon,
  email,
  passwd,
  name,
  nachname,
  anschrift,
  handy,
  anmeldedatum,
  active,
  newsletter,
  token,
  last_login,
  orgateam
) VALUES
(
  151030, -- id
  0, -- bezirk_id
  1, -- verified
  '0000-00-00 00:00:00', -- last_pass
  0, -- rolle
  '10557', -- plz
  'Berlin', -- stadt
  '52.5250839', -- lat
  '13.369402', -- lon
  'user1@example.com', -- email
  'bafc1bcb62e51692a32aeb717ccc8a42', -- passwd
  'User', -- name
  'One', -- nachname
  'Europaplatz 1', -- anschrift
  '+49132414', -- handy
  '2016-07-22 20:01:45', -- anmeldedatum
  1, -- active
  1, -- newsletter
  '57927ba974bc07.93176475', -- token
  '2016-07-22 20:14:18', -- last_login
  0
),
(
  151031, -- id
  0, -- bezirk_id
  1, -- verified
  '0000-00-00 00:00:00', -- last_pass
  0, -- rolle
  '10557', -- plz
  'Berlin', -- stadt
  '52.5250839', -- lat
  '13.369402', -- lon
  'user2@example.com', -- email
  'bafc1bcb62e51692a32aeb717ccc8a42', -- passwd
  'User', -- name
  'Two', -- nachname
  'Europaplatz 1', -- anschrift
  '+49132414', -- handy
  '2016-07-22 20:01:45', -- anmeldedatum
  1, -- active
  1, -- newsletter
  '57927ba974bc07.93176475', -- token
  '2016-07-22 20:14:18', -- last_login
  0
),
(
  151032, -- id
  241, -- bezirk_id
  1, -- verified
  '0000-00-00 00:00:00', -- last_pass
  3, -- rolle
  '10557', -- plz
  'Berlin', -- stadt
  '52.5250839', -- lat
  '13.369402', -- lon
  'userbot@example.com', -- email
  '7c8a4e4fcf07150c5afe439887b4e091', -- passwd
  'User', -- name
  'Two', -- nachname
  'Europaplatz 1', -- anschrift
  '+49132414', -- handy
  '2016-07-22 20:01:45', -- anmeldedatum
  1, -- active
  1, -- newsletter
  '57927ba974bc07.93176475', -- token
  '2016-07-22 20:14:18', -- last_login
  0
),
(
  151033, -- id
  241, -- bezirk_id
  1, -- verified
  '0000-00-00 00:00:00', -- last_pass
  4, -- rolle
  '10557', -- plz
  'Berlin', -- stadt
  '52.5250839', -- lat
  '13.369402', -- lon
  'userorga@example.com', -- email
  '7c8a4e4fcf07150c5afe439887b4e091', -- passwd
  'User', -- name
  'Orga', -- nachname
  'Europaplatz 1', -- anschrift
  '+49132414', -- handy
  '2016-07-22 20:01:45', -- anmeldedatum
  1, -- active
  1, -- newsletter
  '57927ba974bc07.93176475', -- token
  '2016-07-22 20:14:18', -- last_login
  1
);

REPLACE INTO `fs_botschafter` (`foodsaver_id`, `bezirk_id`) VALUES ('151032', '241');
REPLACE INTO `fs_foodsaver_has_bezirk` (`foodsaver_id`, `bezirk_id`, `active`) VALUES ('151032', '241', '1');

-- Insertions for Team pages
REPLACE INTO `fs_foodsaver_has_bezirk` (`foodsaver_id`, `bezirk_id`, `active`) VALUES ('151030', '1564', '1');
REPLACE INTO `fs_foodsaver_has_bezirk` (`foodsaver_id`, `bezirk_id`, `active`) VALUES ('151032', '1373', '1');
REPLACE INTO `fs_foodsaver_has_bezirk` (`foodsaver_id`, `bezirk_id`, `active`) VALUES ('151032', '1565', '1');
