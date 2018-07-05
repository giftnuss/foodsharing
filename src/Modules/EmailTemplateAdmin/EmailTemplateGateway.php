<?php

namespace Foodsharing\Modules\EmailTemplateAdmin;

use Foodsharing\Modules\Core\BaseGateway;

class EmailTemplateGateway extends BaseGateway
{
	public function getOne_message_tpl($id): array
	{
		return $this->db->fetch('
			SELECT
			`id`,
			`language_id`,
			`name`,
			`subject`,
			`body`

			FROM 		`fs_message_tpl`

			WHERE 		`id` = :id',
			[':id' => $id]
		);
	}

	public function add_message_tpl($data): int
	{
		return $this->db->insert('fs_message_tpl', [
			'language_id' => $data['language_id'],
			'name' => strip_tags($data['name']),
			'subject' => strip_tags($data['subject']),
			'body' => strip_tags($data['body'])
		]);
	}
}
