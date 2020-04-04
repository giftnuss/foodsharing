<?php

namespace Foodsharing\Modules\Profile;

use Carbon\Carbon;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreModel;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\ReportPermissions;

class ProfileXhr extends Control
{
	private $foodsaver;
	private $storeModel;
	private $bellGateway;
	private $mailboxGateway;
	private $regionGateway;
	private $profileGateway;
	private $storeGateway;
	private $reportPermissions;
	private $profilePermissions;

	public function __construct(
		ProfileView $view,
		StoreModel $storeModel,
		BellGateway $bellGateway,
		RegionGateway $regionGateway,
		MailboxGateway $mailboxGateway,
		ProfileGateway $profileGateway,
		StoreGateway $storeGateway,
		ReportPermissions $reportPermissions,
		ProfilePermissions $profilePermissions
	) {
		$this->view = $view;
		$this->storeModel = $storeModel;
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

	public function rate(): array
	{
		$rate = 1;
		if (isset($_GET['rate'])) {
			$rate = (int)$_GET['rate'];
		}

		$foodsharerId = (int)$_GET['id'];

		if ($foodsharerId > 0) {
			$type = (int)$_GET['type'];

			$message = '';
			if (isset($_GET['message'])) {
				$message = strip_tags($_GET['message']);
			}

			if (strlen($message) < 100) {
				return [
					'status' => 1,
					'script' => 'pulseError("Bitte gib mindestens einen 100 Zeichen langen Text zu Deiner Banane ein.");',
				];
			}

			$this->profileGateway->rate($foodsharerId, $rate, $type, $message, $this->session->id());

			$comment = '';
			if ($msg = $this->profileGateway->getRateMessage($foodsharerId, $this->session->id())) {
				$comment = $msg;
			}

			return [
				'status' => 1,
				'comment' => $comment,
				'title' => 'Nachricht hinterlassen',
				'script' => '$("#fs-profile-rate-comment").dialog("close");$(".vouch-banana").tooltip("close");pulseInfo("Banane wurde gesendet!");profile(' . $foodsharerId . ');',
			];
		}

		return [];
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
		$store = $this->storeModel->getBetriebBezirkID($_GET['storeId']);

		if ($this->session->isOrgaTeam() || $this->session->isAdminFor($store['bezirk_id'])) {
			if ($this->storeGateway->removeFetcher(
				$_GET['fsid'],
				$_GET['storeId'],
				Carbon::createFromTimestamp($_GET['date'])
			)) {
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
