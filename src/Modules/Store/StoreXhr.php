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
	private $storeModel;
	private $storeGateway;
	private $storePermissions;
	private $storeTransactions;
	private $sanitizerService;

	public function __construct(
		StoreModel $model,
		StoreView $view,
		StoreGateway $storeGateway,
		StorePermissions $storePermissions,
		StoreTransactions $storeTransactions,
		Sanitizer $sanitizerService
	) {
		$this->storeModel = $model;
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

	public function savebezirkids()
	{
		if (isset($_GET['ids']) && is_array($_GET['ids']) && count($_GET['ids']) > 0) {
			foreach ($_GET['ids'] as $b) {
				if ($this->storePermissions->mayEditStore($b['id']) && (int)$b['v'] > 0) {
					$this->storeGateway->updateStoreRegion($b['id'], $b['v']);
				}
			}
		}

		return ['status' => 1];
	}

	// see https://gitlab.com/foodsharing-dev/foodsharing/-/issues/885
	public function setbezirkids()
	{
		if (isset($_SESSION['client']['verantwortlich']) && is_array($_SESSION['client']['verantwortlich'])) {
			$ids = [];
			foreach ($_SESSION['client']['verantwortlich'] as $b) {
				$ids[] = (int)$b['betrieb_id'];
			}
			if (!empty($ids)) {
				if ($betriebe = $this->storeModel->q('SELECT id,name,bezirk_id,str,hsnr FROM fs_betrieb WHERE id IN(' . implode(',', $ids) . ') AND ( bezirk_id = 0 OR bezirk_id IS NULL)')) {
					$dia = new XhrDialog();

					$dia->setTitle('Fehlende Zuordnung');
					$dia->addContent($this->v_utils->v_info('Für folgende Betriebe wurde noch kein Bezirk zugeordnet. Bitte gib einen Bezirk an!'));
					$dia->addOpt('width', '650px');
					$dia->noOverflow();

					$bezirks = $this->session->getRegions();

					foreach ($bezirks as $key => $b) {
						if (!Type::isAccessibleRegion($b['type'])) {
							unset($bezirks[$key]);
						}
					}

					$cnt = '
					<div id="betriebetoselect">';
					foreach ($betriebe as $b) {
						$cnt .= $this->v_utils->v_form_select('b_' . $b['id'], [
							'label' => $b['name'] . ', ' . $b['str'] . ' ' . $b['hsnr'],
							'values' => $bezirks
						]);
					}
					$cnt .= '
					</div>';
					$dia->addJs('
						$("#savebetriebetoselect").on("click", function(ev){
							ev.preventDefault();

							var saveArr = new Array();

							$("#betriebetoselect select.input.select").each(function(){
								var $this = $(this);
								var value = parseInt($this.val());
								var id = parseInt($this.attr("id").split("b_")[1]);

								if(id > 0 && value > 0)
								{
									saveArr.push({
										id:id,
										v:value
									});
								}
							});

							if(saveArr.length > 0)
							{
								ajax.req("betrieb","savebezirkids",{
									data: {ids: saveArr},
									success: function(){
										pulseInfo("Erfolgreich gespeichert!");
										$("#' . $dia->getId() . '").dialog("close");
									}
								});
							}
						});
					');
					$dia->addContent($cnt);
					$dia->addContent($this->v_utils->v_input_wrapper('', '<a class="button" id="savebetriebetoselect" href="#">' . $this->translator->trans('button.save') . '</a>'));

					return $dia->xhrout();
				}
			}
		}
	}

	public function signout()
	{
		$xhr = new Xhr();
		$status = $this->storeGateway->getUserTeamStatus($this->session->id(), $_GET['id']);
		if ($status === TeamStatus::Coordinator) {
			$xhr->addMessage($this->translator->trans('storeedit.team.cannot-leave'), 'error');
		} elseif ($status >= TeamStatus::Applied) {
			$this->storeModel->signout($_GET['id'], $this->session->id());
			$this->storeGateway->addStoreLog($_GET['id'], $this->session->id(), null, null, StoreLogAction::LEFT_STORE);
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
			$dia->addButton($this->translator->trans('store.request.request'), 'betriebRequest(' . (int)$store['id'] . ');return false;');
		} elseif ($store['team_status'] != 0 && (!$store['inTeam'] && ($store['pendingRequest']))) {
			$dia->addButton($this->translator->trans('store.request.withdraw'), 'withdrawStoreRequest(' . (int)$this->session->id() . ',' . (int)$store['id'] . ');return false;');
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
