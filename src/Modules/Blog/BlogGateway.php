<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Modules\Core\BaseGateway;

class BlogGateway extends BaseGateway
{
	public function update_blog_entry($id, $data)
	{
		$data_stripped = [
			'bezirk_id' => $data['bezirk_id'],
			'foodsaver_id' => $data['foodsaver_id'],
			'name' => strip_tags($data['name']),
			'teaser' => strip_tags($data['teaser']),
			'body' => strip_tags($data['body']),
			'time' => strip_tags($data['time']),
		];

		if (!empty($data['picture'])) {
			$data_stripped['picture'] = strip_tags($data['picture']);
		}

		return $this->db->update(
			'fs_blog_entry',
			$data_stripped,
			['id' => $id]
		);
	}
}
