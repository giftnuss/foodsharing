<?php

namespace Foodsharing\Modules\FAQAdmin;

use Foodsharing\Modules\Core\BaseGateway;

class FAQGateway extends BaseGateway
{
	public function get_faq(): array
	{
		return $this->db->fetchAll('
			SELECT
			`id`,
			`foodsaver_id`,
			`faq_kategorie_id`,
			`name`,
			`answer`

			FROM 		`fs_faq`
			ORDER BY `name`'
		);
	}

	public function getOne_faq($id): array
	{
		return $this->db->fetch('
			SELECT
			`id`,
			`foodsaver_id`,
			`faq_kategorie_id`,
			`name`,
			`answer`

			FROM 		`fs_faq`

			WHERE 		`id` = :id',
			[':id' => $id]
		);
	}

	public function getBasics_faq_category(): array
	{
		return $this->db->fetchAll('
			SELECT 	 	`id`,
						`name`

			FROM 		`fs_faq_category`
			ORDER BY `name`'
		);
	}

	public function update_faq($id, $data)
	{
		return $this->db->update(
			'fs_faq',
			[
				'foodsaver_id' => $data['foodsaver_id'],
				'faq_kategorie_id' => $data['faq_kategorie_id'],
				'name' => strip_tags($data['name']),
				'answer' => strip_tags($data['answer'])
			],
			['id' => $id]
		);
	}

	public function getFaqIntern()
	{
		return $this->db->fetchAll('SELECT `id`, `answer`, `name` FROM `fs_faq`');
	}

	public function add_faq($data)
	{
		return $this->db->insert('fs_faq', [
				'foodsaver_id' => $data['foodsaver_id'],
				'faq_kategorie_id' => $data['faq_kategorie_id'],
				'name' => strip_tags($data['name']),
				'answer' => strip_tags($data['answer'])
			]);
	}

	public function del_faq($id)
	{
		return $this->db->delete('fs_faq', ['id' => $id]);
	}
}
