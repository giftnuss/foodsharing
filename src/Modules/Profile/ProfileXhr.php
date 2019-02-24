<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreModel;

class ProfileXhr extends Control
{
	private $foodsaver;
	private $storeModel;
	private $bellGateway;
	private $regionGateway;
	private $profileGateway;

	public function __construct(
		ProfileView $view,
		StoreModel $storeModel,
		BellGateway $bellGateway,
		RegionGateway $regionGateway,
		ProfileGateway $profileGateway
	) {
		$this->view = $view;
		$this->storeModel = $storeModel;
		$this->bellGateway = $bellGateway;
		$this->regionGateway = $regionGateway;
		$this->profileGateway = $profileGateway;

		parent::__construct();

		if (!$this->session->may()) {
			return array(
				'status' => 1,
				'script' => ''
			);
		}

		if (isset($_GET['id'])) {
			$this->profileGateway->setFsId($_GET['id']);
			$fs = $this->profileGateway->getData($_GET['id']);

			if (isset($fs['id'])) {
				$this->foodsaver = $fs;
				$this->foodsaver['mailbox'] = false;
				if ($this->session->may('orga') && (int)$fs['mailbox_id'] > 0) {
					$this->foodsaver['mailbox'] = $this->model->getVal('name', 'mailbox', $fs['mailbox_id']) . '@' . PLATFORM_MAILBOX_HOST;
				}

				/*
					* -1: no buddy
					*  0: requested
					*  1: buddy
				*/
				$this->foodsaver['buddy'] = $this->profileGateway->buddyStatus($this->foodsaver['id']);

				$this->view->setData($this->foodsaver);
			} else {
				$this->bellGateway->delBellsByIdentifier('new-fs-' . (int)$_GET['id']);

				return array(
					'status' => 0
				);
			}
		}
	}

	public function rate()
	{
		$rate = 1;
		if (isset($_GET['rate'])) {
			$rate = (int)$_GET['rate'];
		}

		$fsid = (int)$_GET['id'];

		if ($fsid > 0) {
			$type = (int)$_GET['type'];

			$message = '';
			if (isset($_GET['message'])) {
				$message = strip_tags($_GET['message']);
			}

			if (strlen($message) < 100) {
				return array(
					'status' => 1,
					'script' => 'pulseError("Bitte gib mindestens einen 100 Zeichen langen Text zu Deiner Banane ein.");'
				);
			}

			$this->profileGateway->rate($fsid, $rate, $type, $message);

			$comment = '';
			if ($msg = $this->profileGateway->getRateMessage($fsid)) {
				$comment = $msg;
			}

			return array(
				'status' => 1,
				'comment' => $comment,
				'title' => 'Nachricht hinterlassen',
				'script' => '$("#fs-profile-rate-comment").dialog("close");$(".vouch-banana").tooltip("close");pulseInfo("Banane wurde gesendet!");profile(' . (int)$fsid . ');'
			);
		}
	}

	public function history()
	{
		$bids = $this->regionGateway->getFsRegionIds($_GET['fsid']);
		if ($this->session->may() && ($this->session->may('orga') || $this->session->isBotForA($bids, false, false))) {
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

			$dia->addOpt('width', '400px');
			$dia->addOpt('height', '($(window).height()-100)', false);

			return $dia->xhrout();
		}
	}

	public function deleteFromSlot()
	{
		$betrieb = $this->storeModel->getBetriebBezirkID($_GET['bid']);

		if ($this->session->isOrgaTeam() || $this->session->isAdminFor($betrieb['bezirk_id'])) {
			if ($this->storeModel->deleteFetchDate($_GET['fsid'], $_GET['bid'], date('Y-m-d H:i:s', $_GET['date']))) {
				return array(
					'status' => 1,
					'script' => '
					pulseSuccess("Termin gelöscht");
					reload();'
				);
			}

			return array(
				'status' => 1,
				'script' => 'pulseError("Es ist ein Fehler aufgetreten!");'
			);
		}

		return array(
			'status' => 1,
			'script' => 'pulseError("Du kannst nur Termine aus Deinem eigenen Bezirk löschen.");'
		);
	}
}
