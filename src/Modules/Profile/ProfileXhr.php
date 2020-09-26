<?php

namespace Foodsharing\Modules\Profile;

use Carbon\Carbon;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Region\RegionGateway;
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
	private StoreGateway $storeGateway;
	private ReportPermissions $reportPermissions;
	private ProfilePermissions $profilePermissions;

	public function __construct(
		ProfileView $view,
		BellGateway $bellGateway,
		RegionGateway $regionGateway,
		MailboxGateway $mailboxGateway,
		ProfileGateway $profileGateway,
		StoreGateway $storeGateway,
		ReportPermissions $reportPermissions,
		ProfilePermissions $profilePermissions
	) {
		$this->view = $view;
		$this->bellGateway = $bellGateway;
		$this->mailboxGateway = $mailboxGateway;
		$this->regionGateway = $regionGateway;
		$this->profileGateway = $profileGateway;
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
				$this->bellGateway->delBellsByIdentifier('new-fs-' . (int)$_GET['id']);
			}
		}
	}

	public function history(): array
	{
		if ($this->profilePermissions->maySeeHistory($_GET['fsid'])) {
			$dia = new XhrDialog();
			if ($_GET['type'] == 0) {
				$history = $this->profileGateway->getVerifyHistory($_GET['fsid']);
				$dia->setTitle('Verifizierungshistorie');
				$dia->addContent($this->view->getHistory($history, $_GET['type']));
			}
			if ($_GET['type'] == 1) {
				$history = $this->profileGateway->getPassHistory($_GET['fsid']);
				$dia->setTitle('Passhistorie');
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
		if ($this->session->isOrgaTeam() && $this->storeGateway->deleteAllDatesFromAFoodsaver($_GET['fsid'])) {
			return [
				'status' => 1,
				'script' => '
				pulseSuccess("Alle Termine gelöscht");
				reload();',
			];
		}

		return [
			'status' => 1,
			'script' => 'pulseError("Du kannst nicht alle Termine löschen!");',
		];
	}

	// used in ProfileView:fetchDates
	public function deleteSinglePickup(): array
	{
		$userId = $_GET['fsid'];
		$storeId = $_GET['storeId'];
		$storeRegion = $this->storeGateway->getStoreRegionId($storeId);
		$pickupDate = Carbon::createFromTimestamp($_GET['date']);

		if ($this->session->isOrgaTeam() || $this->session->isAdminFor($storeRegion)) {
			if ($this->storeGateway->removeFetcher($userId, $storeId, $pickupDate)) {
				return [
					'status' => 1,
					'script' => '
					pulseSuccess("Einzeltermin gelöscht");
					reload();',
				];
			}
		}

		return [
			'status' => 1,
			'script' => 'pulseError("Du kannst keine Einzeltermine löschen!");',
		];
	}
}
