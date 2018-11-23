<?php

namespace Foodsharing\Modules\EmailTemplateAdmin;

use Foodsharing\Modules\Core\BaseGateway;

class EmailTemplateAdminGateway extends BaseGateway
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

	public function getBasics_message_tpl(): array
	{
		$stm = '
			SELECT 	 	`id`,
						`name`
			
			FROM 		`fs_message_tpl`
			ORDER BY  	`name`';

		return $this->db->fetchAll($stm);
	}

	public function del_message_tpl($id): int
	{
		return $this->db->delete('fs_message_tpl', ['id' => (int)$id]);
	}

	public function update_message_tpl($id, $data): int
	{
		return $this->db->update('fs_message_tpl',
			[
				'language_id' => (int)$data['language_id'],
				'name' => strip_tags($data['name']),
				'subject' => strip_tags($data['subject']),
				'body' => $data['body']
			],
			['id' => (int)$id]
		);
	}
}
