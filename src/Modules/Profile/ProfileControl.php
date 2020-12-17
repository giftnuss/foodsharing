<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Mails\MailsGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\ReportPermissions;

final class ProfileControl extends Control
{
	private $foodsaver;
	private MailsGateway $mailsGateway;
	private RegionGateway $regionGateway;
	private ProfileGateway $profileGateway;
	private BasketGateway $basketGateway;
	private MailboxGateway $mailboxGateway;
	private ReportPermissions $reportPermissions;
	private ProfilePermissions $profilePermissions;

	public function __construct(
		MailsGateway $mailsGateway,
		ProfileView $view,
		RegionGateway $regionGateway,
		ProfileGateway $profileGateway,
		BasketGateway $basketGateway,
		MailboxGateway $mailboxGateway,
		ReportPermissions $reportPermissions,
		ProfilePermissions $profilePermissions
	) {
		$this->view = $view;
		$this->mailsGateway = $mailsGateway;
		$this->regionGateway = $regionGateway;
		$this->profileGateway = $profileGateway;
		$this->basketGateway = $basketGateway;
		$this->mailboxGateway = $mailboxGateway;
		$this->reportPermissions = $reportPermissions;
		$this->profilePermissions = $profilePermissions;

		parent::__construct();

		if (!$profileId = $this->uriInt(2)) {
			$this->routeHelper->goPage('dashboard');
		}

		$viewerId = $this->session->id() ?? -1; // -1 carries special meaning for `profileGateway:getData`
		$data = $this->profileGateway->getData($profileId, $viewerId, $this->reportPermissions->mayHandleReports());
		$isRemoved = (!$data) || isset($data['deleted_at']);

		if (!$this->session->may()) {
			$this->profilePublic($data);

			return;
		}

		if ($isRemoved) {
			$this->flashMessageHelper->error($this->translator->trans('profile.notFound'));
			$this->routeHelper->goPage('dashboard');
		}

		$this->foodsaver = $data;
		$this->foodsaver['buddy'] = $this->profileGateway->buddyStatus($this->foodsaver['id'], $viewerId);
		if ($this->profilePermissions->maySeeBounceWarning($profileId)) {
			$this->foodsaver['emailIsBouncing'] = $this->mailsGateway->emailIsBouncing($this->foodsaver['email']);
		}
		$this->foodsaver['basketCount'] = $this->basketGateway->getAmountOfFoodBaskets($this->foodsaver['id']);
		if ((int)$this->foodsaver['mailbox_id'] > 0 && $this->profilePermissions->maySeeEmailAddress($profileId)) {
			$this->foodsaver['mailbox'] = $this->mailboxGateway->getMailboxname($this->foodsaver['mailbox_id'])
				. '@' . PLATFORM_MAILBOX_HOST;
		}

		$this->view->setData($this->foodsaver);

		// FOODSHARING/profile/12345/ | FOODSHARING/profile/12345/notes
		// ^uriStr(0)  ^(1)    ^(2)   | ^uriStr(0)  ^(1)    ^(2)  ^(3)
		$subpage = $this->uriStr(3);

		if ($subpage === 'notes') {
			$this->orgaTeamNotes();
		} else {
			$this->profile();
		}
	}

	private function orgaTeamNotes(): void
	{
		$fsId = $this->foodsaver['id'];
		$this->pageHelper->addBread($this->foodsaver['name'], '/profile/' . $fsId);
		if ($this->profilePermissions->maySeeUserNotes($fsId)) {
			$userNotes = $this->wallposts('usernotes', $fsId);
			$storeList = $this->profileGateway->listStoresOfFoodsaver($fsId);
			$this->view->userNotes($userNotes, $storeList);
		} else {
			$this->routeHelper->go('/profile/' . $fsId);
		}
	}

	public function profile(): void
	{
		$fsId = $this->foodsaver['id'];
		$regionId = $this->foodsaver['bezirk_id'];

		$mayAdmin = $this->profilePermissions->mayAdministrateUserProfile($fsId, $regionId);
		$maySeePickups = $this->profilePermissions->maySeePickups($fsId);

		$wallPosts = $this->wallposts('foodsaver', $fsId);
		$userStores = $mayAdmin ? $this->profileGateway->listStoresOfFoodsaver($fsId) : [];
		$fetchDates = $maySeePickups ? $this->profileGateway->getNextDates($fsId, 50) : [];

		$this->view->profile($wallPosts, $userStores, $fetchDates);
	}

	private function profilePublic(array $profileData): void
	{
		$isVerified = $profileData['verified'] ?? 0;
		$initials = ($profileData['name'] ?? '?')[0] . '.';
		$regionId = $profileData['bezirk_id'] ?? null;
		$regionName = ($regionId === null) ? '?' : $this->regionGateway->getRegionName($regionId);
		$this->pageHelper->addContent(
			$this->view->vueComponent('profile-public', 'PublicProfile', [
				'canPickUp' => $isVerified > 0,
				'fromRegion' => $regionName,
				'fsId' => $profileData['id'] ?? '',
				'initials' => $initials,
			])
		);
	}

	// this is required even if empty.
	public function index(): void
	{
	}
}
