<?php

namespace Foodsharing\Services;

use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\FoodSharePoint\FoodSharePointGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Lib\Session;

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
					$this->emailHelper->tplMail('foodSharePoint/new_message', $f['email'], array(
						'link' => BASE_URL . '/?page=fairteiler&sub=ft&id=' . (int)$foodSharePointId,
						'name' => $f['name'],
						'anrede' => $this->translationHelper->genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'fairteiler' => $foodSharePoint['name'],
						'post' => $body
					));
				}
			}

			if ($followers = $this->foodSharePointGateway->getInfoFollowerIds($foodSharePointId)) {
				$followersWithoutPostAuthor = array_diff($followers, [$post['fs_id']]);
				$this->bellGateway->addBell(
					$followersWithoutPostAuthor,
					'ft_update_title',
					'ft_update',
					'img img-recycle yellow',
					array('href' => '/?page=fairteiler&sub=ft&id=' . $foodSharePointId),
					array('name' => $foodSharePoint['name'], 'user' => $post['fs_name'], 'teaser' => $this->sanitizerService->tt($post['body'], 100)),
					'fairteiler-' . $foodSharePointId
				);
			}
		}
	}

	public function sendEmailIfLastAdminLeftGroup($groupId)
	{
		$wasAdminForThisGroup = $this->session->isAdminFor($groupId);

		if ($wasAdminForThisGroup && count($this->foodsaverGateway->getBotschafter($groupId)) < 1) {
			$recipient = ['welcome@foodsharing.network', 'ags.bezirke@foodsharing.network'];
			$groupName = $this->regionGateway->getRegionName($groupId);
			$idStructure = $this->regionGateway->listRegionsIncludingParents([$groupId]);

			$idStructureList = [];
			foreach ($idStructure as $id) {
				$idStructureList[] = '' . $id . '  -  ' . $this->regionGateway->getRegionName($id) . '';
			}
			$idStructureList = implode('<br>', $idStructureList);

			$messageText = $this->translationHelper->sv('message_text_to_group_admin_workgroup', ['groupId' => $groupId, '$idStructureList' => $idStructureList, 'groupName' => $groupName]);

			$this->emailHelper->tplMail('general/workgroup_contact', $recipient, [
				'gruppenname' => $groupName,
				'message' => $messageText,
				'username' => $this->session->user('name'),
				'userprofile' => BASE_URL . '/profile/' . $this->session->id()
			], $this->session->user('email'));
		}
	}
}
