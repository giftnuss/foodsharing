<?php

namespace Foodsharing\Modules\Store;

use Carbon\Carbon;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Utility\Sanitizer;

class StoreXhr extends Control
{
	private $storeGateway;
	private $storePermissions;
	private $storeTransactions;
	private $sanitizerService;

	public function __construct(
		StoreView $view,
		StoreGateway $storeGateway,
		StorePermissions $storePermissions,
		StoreTransactions $storeTransactions,
		Sanitizer $sanitizerService
	) {
		$this->view = $view;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->storeTransactions = $storeTransactions;
		$this->sanitizerService = $sanitizerService;

		parent::__construct();

		if (!$this->session->may('fs')) {
			exit();
		}
	}

	public function savedate()
	{
		$storeId = (int)$_GET['bid'];
		if (!$this->storePermissions->mayAddPickup($storeId)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		if (strtotime($_GET['time']) > 0 && $_GET['fetchercount'] >= 0) {
			$fetchercount = (int)$_GET['fetchercount'];
			$time = $_GET['time'];
			if ($fetchercount > 8) {
				$fetchercount = 8;
			}

			if ($this->storeTransactions->changePickupSlots($storeId, Carbon::createFromTimeString($time), $fetchercount)) {
				$this->flashMessageHelper->success($this->translator->trans('pickup.edit.added'));

				return [
					'status' => 1,
					'script' => 'reload();'
				];
			}
		}
	}

	public function adddate()
	{
		$storeId = (int)$_GET['id'];
		if (!$this->storePermissions->mayAddPickup($storeId)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$dia = new XhrDialog();
		$dia->setTitle('Abholtermin eintragen');
		$dia->addContent($this->view->dateForm());
		$dia->addOpt('width', 280);
		$dia->setResizeable(false);
		$dia->addAbortButton();
		$dia->addButton('Speichern', 'saveDate();');

		$dia->addJs('

			function saveDate()
			{
				var date = $("#datepicker").datepicker( "getDate" );

				date = date.getFullYear() + "-" +
				    ("00" + (date.getMonth()+1)).slice(-2) + "-" +
				    ("00" + date.getDate()).slice(-2) + " " +
				    ("00" + $("select[name=\'time[hour]\']").val()).slice(-2) + ":" +
				    ("00" + $("select[name=\'time[min]\']").val()).slice(-2) + ":00";

				if($("#fetchercount").val() >= 0)
				{
					ajreq("savedate",{
						app:"betrieb",
						time:date,
						fetchercount:$("#fetchercount").val(),
						bid:' . $storeId . '
					});
				}
				else
				{
					pulseError("Du musst noch die Anzahl der Abholer/innen auswählen");
				}
			}

			$("#datepicker").datepicker({
				minDate: new Date()
			});
		');

		return $dia->xhrout();
	}

	public function signout()
	{
		$xhr = new Xhr();
		$status = $this->storeGateway->getUserTeamStatus($this->session->id(), $_GET['id']);
		if ($status === TeamStatus::Coordinator) {
			$xhr->addMessage($this->translator->trans('storeedit.team.cannot-leave'), 'error');
		} elseif ($status >= TeamStatus::Applied) {
			$storeId = intval($_GET['id']);
			$userId = $this->session->id();
			if (is_null($userId)) {
				return XhrResponses::PERMISSION_DENIED;
			}
			$this->storeTransactions->removeStoreMember($storeId, $userId);
			$this->storeGateway->addStoreLog($storeId, $userId, null, null, StoreLogAction::LEFT_STORE);
			$xhr->addScript('goTo("/?page=relogin&url=" + encodeURIComponent("/?page=dashboard") );');
		} else {
			$xhr->addMessage($this->translator->trans('store.not-in-team'), 'error');
		}
		$xhr->send();
	}

	public function bubble(): array
	{
		$storeId = $_GET['id'];
		if ($store = $this->storeGateway->getMyStore($this->session->id(), $storeId)) {
			$dia = $this->buildBubbleDialog($store, $storeId);

			return $dia->xhrout();
		}

		return [
				'status' => 1,
				'script' => 'pulseError("' . $this->translator->trans('store.error') . '");',
		];
	}

	private function buildBubbleDialog(array $store, int $storeId): XhrDialog
	{
		$teamStatus = $this->storeGateway->getUserTeamStatus($this->session->id(), $storeId);
		$store['inTeam'] = $teamStatus > TeamStatus::Applied;
		$store['pendingRequest'] = $teamStatus == TeamStatus::Applied;
		$dia = new XhrDialog();
		$dia->setTitle($store['name']);
		$dia->addContent($this->view->bubble($store));
		if (($store['inTeam']) || $this->storePermissions->mayEditStore($storeId)) {
			$dia->addButton($this->translator->trans('store.go'), 'goTo(\'/?page=fsbetrieb&id=' . (int)$store['id'] . '\');');
		}
		if ($store['team_status'] != 0 && (!$store['inTeam'] && (!$store['pendingRequest']))) {
			$dia->addButton($this->translator->trans('store.request.request'), 'wantToHelpStore(' . (int)$store['id'] . ',' . (int)$this->session->id() . ');return false;');
		} elseif ($store['team_status'] != 0 && (!$store['inTeam'] && ($store['pendingRequest']))) {
			$dia->addButton($this->translator->trans('store.request.withdraw'), 'withdrawStoreRequest(' . (int)$store['id'] . ',' . (int)$this->session->id() . ');return false;');
		}
		$modal = false;
		if (isset($_GET['modal'])) {
			$modal = true;
		}
		$dia->addOpt('modal', 'false', $modal);
		$dia->addOpt('resizeable', 'false', false);
		$dia->noOverflow();

		return $dia;
	}
}
