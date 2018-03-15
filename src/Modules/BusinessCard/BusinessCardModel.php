<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Model;

class BusinessCardModel extends Model
{
	public function getMyData()
	{
		$fs = $this->qRow('
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

			WHERE 	fs.id = ' . (int)$this->func->fsId() . '
		');

		if (S::may('bieb')) {
			if ($mailbox = $this->qOne('SELECT mb.name FROM fs_mailbox mb, fs_foodsaver fs WHERE fs.mailbox_id = mb.id AND fs.id = ' . (int)$this->func->fsId())) {
				$fs['email'] = $mailbox . '@' . DEFAULT_EMAIL_HOST;
			}
		}

		$fs['bot'] = $this->q('
			SELECT 	b.name,
					b.id,
					CONCAT(mb.`name`,"@","' . DEFAULT_EMAIL_HOST . '") AS email,
					mb.name AS mailbox
					
			FROM 	fs_bezirk b,
					fs_mailbox mb,
					fs_botschafter bot
				
			WHERE 	b.mailbox_id = mb.id
			AND 	bot.bezirk_id = b.id
			AND 	bot.foodsaver_id = ' . (int)$this->func->fsId() . '
			AND 	b.type != 7
		');

		$fs['fs'] = $this->q('
			SELECT 	b.name,
					b.id
			
			FROM 	fs_bezirk b,
					fs_foodsaver_has_bezirk fhb
		
			WHERE 	fhb.bezirk_id = b.id
			AND 	fhb.foodsaver_id = ' . (int)$this->func->fsId() . '
			AND 	b.type != 7
		');

		return $fs;
	}
}
