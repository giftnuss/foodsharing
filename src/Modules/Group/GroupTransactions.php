<?php

namespace Foodsharing\Modules\Group;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\FoodSharePoint\FoodSharePointGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TranslationHelper;

/**
 * Groups are the super category in which Regions and WorkGroups belong. GroupTransactions contains the common logic
 * of Regions and WorkGroups.
 */
final class GroupTransactions
{
	private $bellGateway;
	private $foodSharePointGateway;
	private $sanitizerService;
	private $emailHelper;
	private $translationHelper;
	private $regionGateway;
	private $foodsaverGateway;
	private $session;

	public function __construct(
		BellGateway $bellGateway,
		FoodSharePointGateway $foodSharePoint,
		Sanitizer $sanitizerService,
		EmailHelper $emailHelper,
		TranslationHelper $translationHelper,
		RegionGateway $regionGateway,
		Session $session,
		FoodsaverGateway $foodsaverGateway
	) {
		$this->bellGateway = $bellGateway;
		$this->foodSharePointGateway = $foodSharePoint;
		$this->sanitizerService = $sanitizerService;
		$this->emailHelper = $emailHelper;
		$this->translationHelper = $translationHelper;
		$this->regionGateway = $regionGateway;
		$this->session = $session;
		$this->foodsaverGateway = $foodsaverGateway;
	}

	public function sendEmailIfGroupHasNoAdmin(int $groupId): void
	{
		if (count($this->foodsaverGateway->getAdminsOrAmbassadors($groupId)) < 1) {
			$recipient = ['welcome@foodsharing.network', 'ags.bezirke@foodsharing.network', 'beta@foodsharing.network'];
			$groupName = $this->regionGateway->getRegionName($groupId);
			$idStructure = $this->regionGateway->listRegionsIncludingParents([$groupId]);

			$idStructureList = '';
			foreach ($idStructure as $key => $id) {
				$idStructureList .= str_repeat('---', $key + 1) . '> <b>' . $id . '</b>  -  ' . $this->regionGateway->getRegionName($id) . '<br>';
			}

			$messageText = $this->translationHelper->sv('message_text_to_group_admin_workgroup', ['groupId' => $groupId, 'idStructureList' => $idStructureList, 'groupName' => $groupName]);

			$this->emailHelper->tplMail('general/workgroup_contact', $recipient, [
				'gruppenname' => $groupName,
				'message' => $messageText,
				'username' => $this->session->user('name'),
				'userprofile' => BASE_URL . '/profile/' . $this->session->id()
			], $this->session->user('email'));
		}
	}
}
