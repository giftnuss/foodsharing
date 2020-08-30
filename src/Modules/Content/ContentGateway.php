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

	public function list($filter): array
	{
		return $this->db->fetchAllByCriteria('fs_content', ['id', 'name'], $filter);
	}

	public function getDetail($id): array
	{
		return $this->db->fetchByCriteria('fs_content', ['id', 'name', 'title', 'body', 'last_mod'], ['id' => $id]);
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
}
