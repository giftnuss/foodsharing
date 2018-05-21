<?php

namespace Foodsharing\Modules\Legal;

use Foodsharing\Modules\Core\BaseGateway;

class LegalGateway extends BaseGateway
{
	const PP_CONTENT = 28;

	public function getPpVersion()
	{
		return $this->db->fetchValue('SELECT `last_mod` FROM fs_content WHERE id = :content_id', [':content_id' => self::PP_CONTENT]);
	}

	public function getPp()
	{
		return $this->db->fetchValue('SELECT `body` FROM fs_content WHERE id = :content_id', ['content_id' => self::PP_CONTENT]);
	}
}
