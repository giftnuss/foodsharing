<?php

namespace Foodsharing\Modules\Legal;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;

class LegalGateway extends BaseGateway
{
	public function getPpVersion()
	{
		return $this->db->fetchValue('SELECT `last_mod` FROM fs_content WHERE id = :content_id', [':content_id' => ContentId::PRIVACY_POLICY_CONTENT]);
	}

	public function getPp()
	{
		return $this->db->fetchValue('SELECT `body` FROM fs_content WHERE id = :content_id', ['content_id' => ContentId::PRIVACY_POLICY_CONTENT]);
	}

	public function getPnVersion()
	{
		return $this->db->fetchValue('SELECT `last_mod` FROM fs_content WHERE id = :content_id', [':content_id' => ContentId::PRIVACY_NOTICE_CONTENT]);
	}

	public function getPn()
	{
		return $this->db->fetchValue('SELECT `body` FROM fs_content WHERE id = :content_id', ['content_id' => ContentId::PRIVACY_NOTICE_CONTENT]);
	}

	public function agreeToPp($fsId, $ppVersion)
	{
		if ($ppVersion == $this->getPpVersion()) {
			$this->db->update('fs_foodsaver', ['privacy_policy_accepted_date' => $ppVersion], ['id' => $fsId]);
		}
	}

	public function agreeToPn($fsId, $pnVersion)
	{
		if ($pnVersion == $this->getPnVersion()) {
			$this->db->update('fs_foodsaver', ['privacy_notice_accepted_date' => $pnVersion], ['id' => $fsId]);
		}
	}

	public function downgradeToFoodsaver($fsId)
	{
		$this->db->update('fs_foodsaver', ['rolle' => 1], ['id' => $fsId]);
		$this->db->delete('fs_botschafter', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_quiz_session', ['foodsaver_id' => $fsId, 'quiz_id' => 3]);
		$this->db->update('fs_betrieb_team', ['verantwortlich' => 0], ['foodsaver_id' => $fsId]);
	}
}
