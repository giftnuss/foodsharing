<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;

class StoreControl extends Control
{
	private $bellGateway;

	public function __construct(StoreModel $model, StoreView $view, BellGateway $bellGateway)
	{
		$this->model = $model;
		$this->view = $view;
		$this->bellGateway = $bellGateway;

		parent::__construct();

		if (!S::may()) {
			$this->func->goLogin();
		}
	}

	public function index()
	{
		/* form methods below work with $g_data */
		global $g_data;
		$bezirk_id = $this->func->getGet('bid');

		if (!isset($_GET['bid'])) {
			$bezirk_id = $this->func->getBezirkId();
		} else {
			$bezirk_id = (int)$_GET['bid'];
		}

		if (!$this->func->isOrgaTeam() && $bezirk_id == 0) {
			$bezirk_id = $this->func->getBezirkId();
		}
		if ($bezirk_id > 0) {
			$bezirk = $this->model->getBezirk($bezirk_id);
		} else {
			$bezirk = array('name' => 'kompletter Datenbank');
		}
		if ($this->func->getAction('new')) {
			if (S::may('bieb')) {
				$this->handle_add(S::id(), $bezirk_id);

				$this->func->addBread($this->func->s('bread_betrieb'), '/?page=betrieb');
				$this->func->addBread($this->func->s('bread_new_betrieb'));

				if (isset($_GET['id'])) {
					$g_data['foodsaver'] = $this->model->getBetriebLeader($_GET['id']);
				}

				$this->func->addContent($this->view->betrieb_form($bezirk, 'betrieb', $this->model->getBasics_lebensmittel(), $this->model->getBasics_kette(), $this->model->get_betrieb_kategorie(), $this->model->get_betrieb_status()));

				$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					array('name' => $this->func->s('back_to_overview'), 'href' => '/?page=fsbetrieb&bid=' . $bezirk_id)
				)), $this->func->s('actions')), CNT_RIGHT);
			} else {
				$this->func->info('Zum Anlegen eines Betriebes musst Du Betriebsverantwortlicher sein');
				$this->func->go('?page=settings&sub=upgrade/up_bip');
			}
		} elseif ($id = $this->func->getActionId('delete')) {
			/*
			if($this->model->del_betrieb($id))
			{
				$this->func->info($this->func->s('betrieb_deleted'));
				$this->func->goPage();
			}
			*/
		} elseif ($id = $this->func->getActionId('edit')) {
			$this->func->addBread($this->func->s('bread_betrieb'), '/?page=betrieb');
			$this->func->addBread($this->func->s('bread_edit_betrieb'));
			$data = $this->model->getOne_betrieb($id);

			$this->func->addTitle($data['name']);
			$this->func->addTitle($this->func->s('edit'));

			if (($this->func->isOrgaTeam() || $this->model->isVerantwortlich($id)) || $this->func->isBotFor($data['bezirk_id'])) {
				$this->handle_edit();

				$this->func->setEditData($data);

				$bezirk = $this->model->getValues(array('id', 'name'), 'bezirk', $data['bezirk_id']);
				if (isset($_GET['id'])) {
					$g_data['foodsaver'] = $this->model->getBetriebLeader($_GET['id']);
				}

				$this->func->addContent($this->view->betrieb_form($bezirk, '', $this->model->getBasics_lebensmittel(), $this->model->getBasics_kette(), $this->model->get_betrieb_kategorie(), $this->model->get_betrieb_status()));
			} else {
				$this->func->info('Diesen Betrieb kannst Du nicht bearbeiten');
			}

			$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->func->pageLink('betrieb', 'back_to_overview')
			)), $this->func->s('actions')), CNT_RIGHT);
		} elseif (isset($_GET['id'])) {
			$this->func->go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
		} else {
			$this->func->addBread($this->func->s('betrieb_bread'), '/?page=betrieb');

			if (S::may('bieb')) {
				$this->func->addContent($this->v_utils->v_menu(array(
					array('href' => '/?page=betrieb&a=new&bid=' . (int)$bezirk_id, 'name' => 'Neuen Betrieb eintragen')
				), 'Aktionen'), CNT_RIGHT);
			}

			if ($stores = $this->model->listBetriebReq($bezirk_id)) {
				$storesRows = array();
				foreach ($stores as $b) {
					$status = $this->v_utils->v_getStatusAmpel($b['betrieb_status_id']);

					$storesRows[] = [
						['cnt' => '<a class="linkrow ui-corner-all" href="/?page=betrieb&id=' . $b['id'] . '">' . $b['name'] . '</a>'],
						['cnt' => $b['str'] . ' ' . $b['hsnr']],
						['cnt' => ($b['added'])],
						['cnt' => $b['bezirk_name']],
						['cnt' => $status],
						['cnt' => $this->v_utils->v_toolbar(['id' => $b['id'], 'types' => ['comment', 'edit', 'delete'], 'confirmMsg' => 'Soll ' . $b['name'] . ' wirklich unwiderruflich gel&ouml;scht werden?'])
						]];
				}

				$table = $this->v_utils->v_tablesorter([
					['name' => 'Name'],
					['name' => 'Anschrift'],
					['name' => 'eingetragen'],
					['name' => $this->func->s('bezirk')],
					['name' => 'Status', 'width' => 50],
					['name' => 'Aktionen', 'sort' => false, 'width' => 75]
				], $storesRows, ['pager' => true]);

				$this->func->addJs('$("#comment").dialog({title:"Kommentar zum Betrieb"});');

				$this->func->addContent($this->v_utils->v_field($table, 'Alle Betriebe aus dem Bezirk ' . $bezirk['name']));
			} else {
				$this->func->info('Es sind noch keine Betriebe eingetragen');
			}
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
				$this->func->go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
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
				$g_data['bezirk_id'] = $this->func->getBezirkId();
			}

			$g_data['stadt'] = $g_data['ort'];
			$g_data['foodsaver'] = [$coordinator];
			$g_data['str'] = $g_data['anschrift'];
			$g_data['hsnr'] = '';

			if ($id = $this->model->add_betrieb($g_data)) {
				$this->model->add_betrieb_notiz(array(
					'foodsaver_id' => $this->func->fsId(),
					'betrieb_id' => $id,
					'text' => '{BETRIEB_ADDED}',
					'zeit' => date('Y-m-d H:i:s', (time() - 10)),
					'milestone' => 1
				));

				if (isset($g_data['first_post']) && !empty($g_data['first_post'])) {
					$this->model->add_betrieb_notiz(array(
						'foodsaver_id' => $this->func->fsId(),
						'betrieb_id' => $id,
						'text' => $g_data['first_post'],
						'zeit' => date('Y-m-d H:i:s'),
						'milestone' => 0
					));
				}

				$foodsaver = $this->model->getFoodsaver($g_data['bezirk_id']);

				$this->bellGateway->addBell($foodsaver, 'store_new_title', 'store_new', 'img img-store brown', array(
					'href' => '/?page=fsbetrieb&id=' . (int)$id
				), array(
					'user' => S::user('name'),
					'name' => $g_data['name']
				), 'store-new-' . (int)$id);

				$this->func->info($this->func->s('betrieb_add_success'));

				$this->func->go('/?page=fsbetrieb&id=' . (int)$id);
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}
}
