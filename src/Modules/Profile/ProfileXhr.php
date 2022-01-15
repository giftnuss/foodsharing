<?php

namespace Foodsharing\Modules\Profile;

use Carbon\Carbon;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Bell\BellType;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\PickupGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\ReportPermissions;

class ProfileXhr extends Control
{
	private $foodsaver;
	private BellGateway $bellGateway;
	private MailboxGateway $mailboxGateway;
	private RegionGateway $regionGateway;
	private ProfileGateway $profileGateway;
	private PickupGateway $pickupGateway;
	private StoreGateway $storeGateway;
	private ReportPermissions $reportPermissions;
	private ProfilePermissions $profilePermissions;

	public function __construct(
		ProfileView $view,
		BellGateway $bellGateway,
		RegionGateway $regionGateway,
		MailboxGateway $mailboxGateway,
		ProfileGateway $profileGateway,
		PickupGateway $pickupGateway,
		StoreGateway $storeGateway,
		ReportPermissions $reportPermissions,
		ProfilePermissions $profilePermissions
	) {
		$this->view = $view;
		$this->bellGateway = $bellGateway;
		$this->mailboxGateway = $mailboxGateway;
		$this->regionGateway = $regionGateway;
		$this->profileGateway = $profileGateway;
		$this->pickupGateway = $pickupGateway;
		$this->storeGateway = $storeGateway;
		$this->reportPermissions = $reportPermissions;
		$this->profilePermissions = $profilePermissions;

		parent::__construct();

		if (isset($_GET['id'])) {
			$fs = $this->profileGateway->getData($_GET['id'], $this->session->id(), $reportPermissions->mayHandleReports());

			if (isset($fs['id'])) {
				$this->foodsaver = $fs;
				$this->foodsaver['mailbox'] = false;
				if ((int)$fs['mailbox_id'] > 0 && $this->profilePermissions->maySeeEmailAddress($fs['id'])) {
					$this->foodsaver['mailbox'] = $this->mailboxGateway->getMailboxname(
							$fs['mailbox_id']
						) . '@' . PLATFORM_MAILBOX_HOST;
				}

				$this->foodsaver['buddy'] = $this->profileGateway->buddyStatus($this->foodsaver['id'], $this->session->id());

				$this->view->setData($this->foodsaver);
			} else {
				$this->bellGateway->delBellsByIdentifier(BellType::createIdentifier(BellType::NEW_FOODSAVER_IN_REGION, (int)$_GET['id']));
			}
		}
	}

	public function history(): array
	{
		if ($this->profilePermissions->maySeeHistory($_GET['fsid'])) {
			$dia = new XhrDialog();
			if ($_GET['type'] == 0) {
				$history = $this->profileGateway->getVerifyHistory($_GET['fsid']);
				$dia->setTitle($this->translator->trans('profile.nav.verificationHistory'));
				$dia->addContent($this->view->getHistory($history, $_GET['type']));
			}
			if ($_GET['type'] == 1) {
				$history = $this->profileGateway->getPassHistory($_GET['fsid']);
				$dia->setTitle($this->translator->trans('profile.nav.history'));
				$dia->addContent($this->view->getHistory($history, $_GET['type']));
			}
			$dia->noOverflow();

			return $dia->xhrout();
		}

		return [];
	}

	// used in ProfileView:fetchDates
	public function deleteAllDatesFromFoodsaver(): array
	{
		if ($this->session->may('orga') && $this->pickupGateway->deleteAllDatesFromAFoodsaver($_GET['fsid'])) {
			return [
				'status' => 1,
				'script' => '
				pulseSuccess(\'' . $this->translator->trans('profile.alldatesdeleted') . '\');
				reload();',
			];
		}

		return [
			'status' => 1,
			'script' => 'pulseError(\'' . $this->translator->trans('profile.cantdeleteall') . '\');',
		];
	}

	// used in ProfileView:fetchDates
	public function deleteSinglePickup(): array
	{
		$userId = intval($_GET['fsid']);
		$storeId = intval($_GET['storeId']);
		$storeRegion = $this->storeGateway->getStoreRegionId($storeId);
		$pickupDate = Carbon::createFromTimestamp($_GET['date']);

		if ($this->session->may('orga') || $this->session->isAdminFor($storeRegion)) {
			if ($this->pickupGateway->removeFetcher($userId, $storeId, $pickupDate)) {
				if ($this->session->id() === $userId) {
					$this->storeGateway->addStoreLog( // the user(bot/orga) removed their own pickup
						$storeId,
						$userId,
						null,
						$pickupDate,
						StoreLogAction::SIGN_OUT_SLOT,
						null,
						'Removed through user Profile.'
					);
				} else {
					$this->storeGateway->addStoreLog( // the user got kicked/the pickup got denied by a bot / orga
							$storeId,
							$this->session->id(),
							$userId,
							$pickupDate,
							StoreLogAction::REMOVED_FROM_SLOT,
							null,
							'Removed through user Profile.'
						);
				}

				return [
					'status' => 1,
					'script' => '
					pulseSuccess(\'' . $this->translator->trans('profile.deletesuccess') . '\');
					reload();',
				];
			}
		}

		return [
			'status' => 1,
			'script' => 'pulseError(\'' . $this->translator->trans('profile.cantdeletethis') . '\');',
		];
	}
}
