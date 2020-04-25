<?php

namespace Foodsharing\Services;

use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\FoodSharePoint\FoodSharePointGateway;
use Foodsharing\Modules\Region\RegionGateway;

final class NotificationService
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
		SanitizerService $sanitizerService,
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

	public function newFoodSharePointPost(int $foodSharePointId)
	{
		if ($foodSharePoint = $this->foodSharePointGateway->getFoodSharePoint($foodSharePointId)) {
			$post = $this->foodSharePointGateway->getLastFoodSharePointPost($foodSharePointId);
			if ($followers = $this->foodSharePointGateway->getEmailFollower($foodSharePointId)) {
				$body = nl2br($post['body']);

				if (!empty($post['attach'])) {
					$attach = json_decode($post['attach'], true);
					if (isset($attach['image']) && !empty($attach['image'])) {
						foreach ($attach['image'] as $img) {
							$body .= '
							<div>
								<img src="' . BASE_URL . '/images/wallpost/medium_' . $img['file'] . '" />
							</div>';
						}
					}
				}

				foreach ($followers as $f) {
					$this->emailHelper->tplMail('foodSharePoint/new_message', $f['email'], [
						'link' => BASE_URL . '/?page=fairteiler&sub=ft&id=' . (int)$foodSharePointId,
						'name' => $f['name'],
						'anrede' => $this->translationHelper->genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'fairteiler' => $foodSharePoint['name'],
						'post' => $body
					]);
				}
			}

			if ($followers = $this->foodSharePointGateway->getInfoFollowerIds($foodSharePointId)) {
				$followersWithoutPostAuthor = array_diff($followers, [$post['fs_id']]);
				$bellData = Bell::create(
					'ft_update_title',
					'ft_update',
					'img img-recycle yellow',
					['href' => '/?page=fairteiler&sub=ft&id=' . $foodSharePointId],
					['name' => $foodSharePoint['name'], 'user' => $post['fs_name'], 'teaser' => $this->sanitizerService->tt($post['body'], 100)],
					'fairteiler-' . $foodSharePointId
				);
				$this->bellGateway->addBell($followersWithoutPostAuthor, $bellData);
			}
		}
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
