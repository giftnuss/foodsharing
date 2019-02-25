<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;

class StoreControl extends Control
{
	private $bellGateway;
	private $storeGateway;
	private $regionGateway;
	private $foodsaverGateway;

	public function __construct(
		StoreModel $model,
		StoreView $view,
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		FoodsaverGateway $foodsaverGateway,
		RegionGateway $regionGateway
	) {
		$this->model = $model;
		$this->view = $view;
		$this->bellGateway = $bellGateway;
		$this->storeGateway = $storeGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;

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
			$bezirk_id = (int)$_GET['bid'];
		} else {
			$bezirk_id = $this->session->getCurrentBezirkId();
		}

		if (!$this->session->isOrgaTeam() && $bezirk_id == 0) {
			$bezirk_id = $this->session->getCurrentBezirkId();
		}
		if ($bezirk_id > 0) {
			$bezirk = $this->regionGateway->getBezirk($bezirk_id);
		} else {
			$bezirk = array('name' => 'kompletter Datenbank');
		}
		if ($this->func->getAction('new')) {
			if ($this->session->may('bieb')) {
				$this->handle_add($this->session->id(), $bezirk_id);

				$this->pageHelper->addBread($this->func->s('bread_betrieb'), '/?page=betrieb');
				$this->pageHelper->addBread($this->func->s('bread_new_betrieb'));

				if (isset($_GET['id'])) {
					$g_data['foodsaver'] = $this->model->getBetriebLeader($_GET['id']);
				}

				$this->pageHelper->addContent($this->view->betrieb_form($bezirk, 'betrieb', $this->model->getBasics_lebensmittel(), $this->model->getBasics_kette(), $this->model->get_betrieb_kategorie(), $this->model->get_betrieb_status()));

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					array('name' => $this->func->s('back_to_overview'), 'href' => '/?page=fsbetrieb&bid=' . $bezirk_id)
				)), $this->func->s('actions')), CNT_RIGHT);
			} else {
				$this->func->info('Zum Anlegen eines Betriebes musst Du Betriebsverantwortlicher sein');
				$this->routeHelper->go('?page=settings&sub=upgrade/up_bip');
			}
		} elseif ($id = $this->func->getActionId('delete')) {
			/*
			if($this->model->del_betrieb($id))
			{
				$this->func->info($this->func->s('betrieb_deleted'));
				$this->routeHelper->goPage();
			}
			*/
		} elseif ($id = $this->func->getActionId('edit')) {
			$this->pageHelper->addBread($this->func->s('bread_betrieb'), '/?page=betrieb');
			$this->pageHelper->addBread($this->func->s('bread_edit_betrieb'));
			$data = $this->model->getOne_betrieb($id);

			$this->pageHelper->addTitle($data['name']);
			$this->pageHelper->addTitle($this->func->s('edit'));

			if (($this->session->isOrgaTeam() || $this->storeGateway->isResponsible($this->session->id(), $id)) || $this->session->isAdminFor($data['bezirk_id'])) {
				$this->handle_edit();

				$this->func->setEditData($data);

				$bezirk = $this->model->getValues(array('id', 'name'), 'bezirk', $data['bezirk_id']);
				if (isset($_GET['id'])) {
					$g_data['foodsaver'] = $this->model->getBetriebLeader($_GET['id']);
				}

				$this->pageHelper->addContent($this->view->betrieb_form($bezirk, '', $this->model->getBasics_lebensmittel(), $this->model->getBasics_kette(), $this->model->get_betrieb_kategorie(), $this->model->get_betrieb_status()));
			} else {
				$this->func->info('Diesen Betrieb kannst Du nicht bearbeiten');
			}

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->func->pageLink('betrieb', 'back_to_overview')
			)), $this->func->s('actions')), CNT_RIGHT);
		} elseif (isset($_GET['id'])) {
			$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
		} else {
			$this->pageHelper->addBread($this->func->s('betrieb_bread'), '/?page=betrieb');

			if ($this->session->may('bieb')) {
				$this->pageHelper->addContent($this->v_utils->v_menu(array(
					array('href' => '/?page=betrieb&a=new&bid=' . (int)$bezirk_id, 'name' => 'Neuen Betrieb eintragen')
				), 'Aktionen'), CNT_RIGHT);
			}

			$stores = $this->model->listBetriebReq($bezirk_id);

			$storesMapped = array_map(function ($store) {
				return [
					'id' => (int)$store['id'],
					'name' => $store['name'],
					// status 3 and 5 are the same (in cooperation), always return 3
					'status' => $store['betrieb_status_id'] == 5 ? 3 : (int)$store['betrieb_status_id'],
					'added' => $store['added'],
					'region' => $store['bezirk_name'],
					'address' => $store['anschrift'],
				];
			}, $stores);

			$this->pageHelper->addContent($this->view->vueComponent('vue-storelist', 'store-list', [
				'regionName' => $bezirk['name'],
				'stores' => $storesMapped
			]));
		}
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->func->submitted()) {
			$g_data['stadt'] = $g_data['ort'];
			$g_data['hsnr'] = '';
			$g_data['str'] = $g_data['anschrift'];

			if ($this->model->update_betrieb($_GET['id'], $g_data)) {
				$this->func->info($this->func->s('betrieb_edit_success'));
				$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}

	private function handle_add($coordinator, $bezirk_id)
	{
		global $g_data;
		if ($this->func->submitted()) {
			$g_data['status_date'] = date('Y-m-d H:i:s');

			if (!isset($g_data['bezirk_id'])) {
				$g_data['bezirk_id'] = $this->session->getCurrentBezirkId();
			}
			if (!in_array($g_data['bezirk_id'], $this->session->listRegionIDs())) {
				$this->func->error($this->func->s('store.can_only_create_store_in_member_region'));
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

			if ($id = $this->model->add_betrieb($g_data)) {
				$this->storeGateway->add_betrieb_notiz(array(
					'foodsaver_id' => $this->session->id(),
					'betrieb_id' => $id,
					'text' => '{BETRIEB_ADDED}',
					'zeit' => date('Y-m-d H:i:s', (time() - 10)),
					'milestone' => 1
				));

				if (isset($g_data['first_post']) && !empty($g_data['first_post'])) {
					$this->storeGateway->add_betrieb_notiz(array(
						'foodsaver_id' => $this->session->id(),
						'betrieb_id' => $id,
						'text' => $g_data['first_post'],
						'zeit' => date('Y-m-d H:i:s'),
						'milestone' => 0
					));
				}

				$foodsaver = $this->foodsaverGateway->getFoodsaver($g_data['bezirk_id']);

				$this->bellGateway->addBell($foodsaver, 'store_new_title', 'store_new', 'img img-store brown', array(
					'href' => '/?page=fsbetrieb&id=' . (int)$id
				), array(
					'user' => $this->session->user('name'),
					'name' => $g_data['name']
				), 'store-new-' . (int)$id);

				$this->func->info($this->func->s('betrieb_add_success'));

				$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$id);
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}
}
