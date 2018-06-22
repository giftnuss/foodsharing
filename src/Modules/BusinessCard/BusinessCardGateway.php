<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\BaseGateway;

class BusinessCardGateway extends BaseGateway
{
	public function getMyData($fsId)
	{
		$stm = '
			SELECT 	fs.id,
					fs.`name`,
					fs.`geschlecht`,
					fs.`nachname`,
					fs.`plz`,
					fs.`stadt`,
					fs.`anschrift`,
					fs.`telefon`,
					fs.`handy`,
					fs.`verified`,
					fs.email

			FROM 	fs_foodsaver fs

			WHERE 	fs.id = :foodsaver_id
		';
		$fs = $this->db->fetch($stm, [':foodsaver_id' => $fsId]);

		$stm = 'SELECT mb.name FROM fs_mailbox mb, fs_foodsaver fs WHERE fs.mailbox_id = mb.id AND fs.id = :foodsaver_id';
		if (S::may('bieb') && $mailbox = $this->db->fetchValue($stm, [':foodsaver_id' => $fsId])) {
			$fs['email'] = $mailbox . '@' . DEFAULT_EMAIL_HOST;
		}

		$stm = '
			SELECT 	b.name,
					b.id,
					CONCAT(mb.`name`,"@","' . DEFAULT_EMAIL_HOST . '") AS email,
					mb.name AS mailbox

			FROM 	fs_bezirk b,
					fs_mailbox mb,
					fs_botschafter bot

			WHERE 	b.mailbox_id = mb.id
			AND 	bot.bezirk_id = b.id
			AND 	bot.foodsaver_id = :foodsaver_id
			AND 	b.type != 7
		';
		$fs['bot'] = $this->db->fetchAll($stm, [':foodsaver_id' => $fsId]);

		$stm = '
			SELECT 	b.name,
					b.id

			FROM 	fs_bezirk b,
					fs_foodsaver_has_bezirk fhb

			WHERE 	fhb.bezirk_id = b.id
			AND 	fhb.foodsaver_id = :foodsaver_id
			AND 	b.type != 7
			AND  b.type != 6
			AND  b.type != 5
		';
		$fs['fs'] = $this->db->fetchAll($stm, [':foodsaver_id' => $fsId]);

		if (S::may('bieb')) {
			$fs['sm'] = $fs['fs'];
		}

		return $fs;
	}
}
