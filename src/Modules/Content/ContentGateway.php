<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Modules\Core\BaseGateway;

class ContentGateway extends BaseGateway
{
	public function get($id): array
	{
		return $this->db->fetch('
				SELECT `title`, `body`
				FROM fs_content
				WHERE `id` = :id
			', [':id' => $id]
		);
	}

	public function list(): array
	{
		return $this->db->fetchAll('
			SELECT 	 	`id`,
						`name`
			FROM 		`fs_content`
			ORDER BY `name`'
		);
	}

	public function getDetail($id): array
	{
		return $this->db->fetch('
			SELECT
			`id`,
			`name`,
			`title`,
			`body`,
			`last_mod`

			FROM 		`fs_content`

			WHERE 		`id` = :id',
			[':id' => $id]
		);
	}

	public function create($data): int
	{
		return $this->db->insert('fs_content', [
			'name' => strip_tags($data['name']),
			'title' => strip_tags($data['title']),
			'body' => $data['body'],
			'last_mod' => $data['last_mod']
		]);
	}

	public function update($id, $data): int
	{
		return $this->db->update('fs_content', [
			'name' => strip_tags($data['name']),
			'title' => strip_tags($data['title']),
			'body' => $data['body'],
			'last_mod' => $data['last_mod']
		], ['id' => $id]);
	}

	public function delete($id): int
	{
		return $this->db->delete('fs_content', ['id' => $id]);
	}

	public function listFaq($cat_ids): array
	{
		$stm = '
			SELECT 
				`id`,
				`name`,
				`answer`

			FROM 
				fs_faq
				
			WHERE 
				`faq_kategorie_id` IN(' . implode(',', $cat_ids) . ')
		';

		return $this->db->fetchAll($stm);
	}
}
