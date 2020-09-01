<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\WeightHelper;

class StoreControl extends Control
{
	private $bellGateway;
	private $storeModel;
	private $storeGateway;
	private $storePermissions;
	private $storeTransactions;
	private $regionGateway;
	private $foodsaverGateway;
	private $identificationHelper;
	private $dataHelper;
	private $weightHelper;

	public function __construct(
		StoreModel $model,
		StorePermissions $storePermissions,
		StoreTransactions $storeTransactions,
		StoreView $view,
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		FoodsaverGateway $foodsaverGateway,
		RegionGateway $regionGateway,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		WeightHelper $weightHelper
	) {
		$this->storeModel = $model;
		$this->view = $view;
		$this->bellGateway = $bellGateway;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->storeTransactions = $storeTransactions;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;
		$this->weightHelper = $weightHelper;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}
	}

	public function index()
	{
		/* form methods below work with $g_data */
		global $g_data;

		if (isset($_GET['bid'])) {
			$regionId = (int)$_GET['bid'];
		} else {
			$regionId = $this->session->getCurrentRegionId();
		}

		if (!$this->session->isOrgaTeam() && $regionId == 0) {
			$regionId = $this->session->getCurrentRegionId();
		}
		if ($regionId > 0) {
			$region = $this->regionGateway->getRegion($regionId);
		} else {
			$region = ['name' => 'kompletter Datenbank'];
		}
		if ($this->identificationHelper->getAction('new')) {
			if ($this->storePermissions->mayCreateStore()) {
				$this->handle_add($this->session->id());

				$this->pageHelper->addBread($this->translator->trans('store.bread'), '/?page=fsbetrieb');
				$this->pageHelper->addBread($this->translator->trans('storeedit.add-new'));

				if (isset($_GET['id'])) {
					$g_data['foodsaver'] = $this->storeGateway->getStoreManagers($_GET['id']);
				}

				$chosenRegion = ($regionId > 0 && Type::isAccessibleRegion($this->regionGateway->getType($regionId))) ? $region : null;
				$this->pageHelper->addContent($this->view->betrieb_form($chosenRegion, 'betrieb', $this->storeGateway->getBasics_groceries(), $this->storeGateway->getBasics_chain(), $this->storeGateway->getStoreCategories(), $this->storeGateway->getStoreStateList(), $this->weightHelper->getWeightListEntries()));

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
					['name' => $this->translator->trans('bread.backToOverview'), 'href' => '/?page=fsbetrieb&bid=' . $regionId]
				]), $this->translator->trans('storeedit.actions')), CNT_RIGHT);
			} else {
				$this->flashMessageHelper->info('Zum Anlegen eines Betriebes musst Du Betriebsverantwortlicher sein');
				$this->routeHelper->go('?page=settings&sub=upgrade/up_bip');
			}
		} elseif ($id = $this->identificationHelper->getActionId('delete')) {
		} elseif ($id = $this->identificationHelper->getActionId('edit')) {
			$this->pageHelper->addBread($this->translator->trans('store.bread'), '/?page=fsbetrieb');
			$this->pageHelper->addBread($this->translator->trans('storeedit.bread'));
			$data = $this->storeModel->getOne_betrieb($id);

			$this->pageHelper->addTitle($data['name']);
			$this->pageHelper->addTitle($this->translationHelper->s('edit'));

			if ($this->storePermissions->mayEditStore($id)) {
				$this->handle_edit();

				$this->dataHelper->setEditData($data);

				$region = $this->storeModel->getValues(['id', 'name'], 'bezirk', $data['bezirk_id']);
				if (isset($_GET['id'])) {
					$g_data['foodsaver'] = $this->storeGateway->getStoreManagers($_GET['id']);
				}

				$this->pageHelper->addContent($this->view->betrieb_form($region, '', $this->storeGateway->getBasics_groceries(), $this->storeGateway->getBasics_chain(), $this->storeGateway->getStoreCategories(), $this->storeGateway->getStoreStateList(), $this->weightHelper->getWeightListEntries()));
			} else {
				$this->flashMessageHelper->info('Diesen Betrieb kannst Du nicht bearbeiten');
			}

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
				$this->routeHelper->pageLink('betrieb')
			]), $this->translator->trans('storeedit.actions')), CNT_RIGHT);
		} elseif (isset($_GET['id'])) {
			$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
		} else {
			$this->pageHelper->addBread($this->translator->trans('store.bread'), '/?page=fsbetrieb');

			$stores = $this->storeModel->listBetriebReq($regionId);

			$storesMapped = array_map(function ($store) {
				return [
					'id' => (int)$store['id'],
					'name' => $store['name'],
					// status COOPERATION_STARTING and COOPERATION_ESTABLISHED are the same (in cooperation), always return COOPERATION_STARTING
					'status' => $store['betrieb_status_id'] == CooperationStatus::COOPERATION_ESTABLISHED ? CooperationStatus::COOPERATION_STARTING : (int)$store['betrieb_status_id'],
					'added' => $store['added'],
					'region' => $store['bezirk_name'],
					'address' => $store['anschrift'],
					'city' => $store['stadt'],
					'zipcode' => $store['plz'],
					'geo' => $store['geo'],
				];
			}, $stores);

			$this->pageHelper->addContent($this->view->vueComponent('vue-storelist', 'store-list', [
				'regionName' => $region['name'],
				'regionId' => $regionId,
				'showCreateStore' => $this->storePermissions->mayCreateStore(),
				'stores' => $storesMapped
			]));
		}
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->submitted()) {
			$id = (int)$_GET['id'];
			$g_data['stadt'] = $g_data['ort'];
			$g_data['hsnr'] = '';
			$g_data['str'] = $g_data['anschrift'];

			if ($this->storeModel->update_betrieb($id, $g_data)) {
				$this->storeTransactions->setStoreNameInConversations($id, $g_data['name']);
				$this->flashMessageHelper->info($this->translationHelper->s('betrieb_edit_success'));
				$this->routeHelper->go('/?page=fsbetrieb&id=' . $id);
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}

	private function handle_add($coordinator)
	{
		global $g_data;
		if ($this->submitted()) {
			$g_data['status_date'] = date('Y-m-d H:i:s');

			if (!isset($g_data['bezirk_id'])) {
				$g_data['bezirk_id'] = $this->session->getCurrentRegionId();
			}
			if (!in_array($g_data['bezirk_id'], $this->session->listRegionIDs())) {
				$this->flashMessageHelper->error($this->translator->trans('storeedit.not-in-region'));
				$this->routeHelper->goPage();
			}

			if (isset($g_data['ort'])) {
				$g_data['stadt'] = $g_data['ort'];
			}
			$g_data['foodsaver'] = [$coordinator];
			if (isset($g_data['anschrift'])) {
				$g_data['str'] = $g_data['anschrift'];
			}
			$g_data['hsnr'] = '';

			if ($id = $this->storeModel->add_betrieb($g_data)) {
				$this->storeTransactions->setStoreNameInConversations($id, $g_data['name']);
				$this->storeGateway->add_betrieb_notiz([
					'foodsaver_id' => $this->session->id(),
					'betrieb_id' => $id,
					'text' => '{BETRIEB_ADDED}',
					'zeit' => date('Y-m-d H:i:s', (time() - 10)),
					'milestone' => 1
				]);

				if (isset($g_data['first_post']) && !empty($g_data['first_post'])) {
					$this->storeGateway->add_betrieb_notiz([
						'foodsaver_id' => $this->session->id(),
						'betrieb_id' => $id,
						'text' => $g_data['first_post'],
						'zeit' => date('Y-m-d H:i:s'),
						'milestone' => 0
					]);
				}

				$foodsaver = $this->foodsaverGateway->getFoodsaversByRegion($g_data['bezirk_id']);

				$bellData = Bell::create('store_new_title', 'store_new', 'fas fa-store-alt', [
					'href' => '/?page=fsbetrieb&id=' . (int)$id
				], [
					'user' => $this->session->user('name'),
					'name' => $g_data['name']
				], 'store-new-' . (int)$id);
				$this->bellGateway->addBell($foodsaver, $bellData);

				$this->flashMessageHelper->info($this->translationHelper->s('betrieb_add_success'));

				$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$id);
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}
}
