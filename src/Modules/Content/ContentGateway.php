<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Modules\Core\BaseGateway;

class ContentGateway extends BaseGateway
{

	public function getContent($id)
	{
		return $this->db->fetch('
				SELECT `title`, `body`
				FROM fs_content
				WHERE `id` = :id
			', [':id' => $id]);
	}

}
