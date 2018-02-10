<?php
/**
 * Created by IntelliJ IDEA.
 * User: matthias
 * Date: 10.02.18
 * Time: 22:31.
 */

namespace Foodsharing\Modules\Store;

use Foodsharing\Lib\Session\S;

class StoreControl
{
	public function __construct()
	{
		if (!S::may()) {
			goLogin();
		}

		global $g_view_utils;

		$this->model = new StoreModel();
		$this->view = new StoreView();
		$this->v_utils = $g_view_utils;

		if (!S::may()) {
			goLogin();
		}

		parent::__construct();
	}

	public function index()
	{
		$bezirk_id = getGet('bid');

		if (!isset($_GET['bid'])) {
			$bezirk_id = getBezirkId();
		} else {
			$bezirk_id = (int)$_GET['bid'];
		}

		if (!isOrgaTeam() && $bezirk_id == 0) {
			$bezirk_id = getBezirkId();
		}
		if ($bezirk_id > 0) {
			$bezirk = $this->model->getBezirk($bezirk_id);
		} else {
			$bezirk = array('name' => 'kompletter Datenbank');
		}
		if (getAction('new')) {
			if (S::may('bieb')) {
				$this->handle_add($bezirk_id);

				addBread(s('bread_betrieb'), '/?page=' . $page);
				addBread(s('bread_new_betrieb'));

				if (isset($_GET['id'])) {
					$g_data['foodsaver'] = $this->model->getBetriebLeader($_GET['id']);
				}

				addContent($this->view->betrieb_form($bezirk, $page, $this->model->getBasics_lebensmittel(), $this->model->getBasics_foodsaver(), db_get_betrieb_kategorie(), db_get_betrieb_status()));

				addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					array('name' => s('back_to_overview'), 'href' => '/?page=fsbetrieb&bid=' . $bezirk_id)
				)), s('actions')), CNT_RIGHT);
			} else {
				info('Zum Anlegen eines Betriebes musst Du Betriebsverantwortlicher sein');
				go('?page=settings&sub=upgrade/up_bip');
			}
		} elseif ($id = getActionId('delete')) {
			/*
			if($this->model->del_betrieb($id))
			{
				info(s('betrieb_deleted'));
				goPage();
			}
			*/
		} elseif ($id = getActionId('edit')) {
			addBread(s('bread_betrieb'), '/?page=betrieb');
			addBread(s('bread_edit_betrieb'));
			$data = $this->model->getOne_betrieb($id);

			addTitle($data['name']);
			addTitle(s('edit'));

			if ((isOrgaTeam() || $this->model->isVerantwortlich($id)) || isBotFor($data['bezirk_id'])) {
				$this->handle_edit();

				setEditData($data);

				$bezirk = $this->model->getValues(array('id', 'name'), 'bezirk', $data['bezirk_id']);
				if (isset($_GET['id'])) {
					$g_data['foodsaver'] = $this->model->getBetriebLeader($_GET['id']);
				}

				addContent($this->view->betrieb_form($bezirk, '', $this->model->getBasics_lebensmittel(), $this->model->getBasics_foodsaver(), $this->model->get_kette(), db_get_betrieb_kategorie(), db_get_betrieb_status()));
			} else {
				info('Diesen Betrieb kannst Du nicht bearbeiten');
			}

			addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				pageLink('betrieb', 'back_to_overview')
			)), s('actions')), CNT_RIGHT);
		} elseif (isset($_GET['id'])) {
			go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
		} else {
			addBread(s('betrieb_bread'), '/?page=betrieb');

			if (S::may('bieb')) {
				addContent($this->v_utils->v_menu(array(
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
					['name' => s('bezirk')],
					['name' => 'Status', 'width' => 50],
					['name' => 'Aktionen', 'sort' => false, 'width' => 75]
				], $storesRows, ['pager' => true]);

				addJs('$("#comment").dialog({title:"Kommentar zum Betrieb"});');

				addContent($this->v_utils->v_field($table, 'Alle Betriebe aus dem Bezirk ' . $bezirk['name']));
			} else {
				info('Es sind noch keine Betriebe eingetragen');
			}
		}
	}

	private function handle_edit()
	{
		global $g_data;
		if (submitted()) {
			$g_data['stadt'] = $g_data['ort'];

			if ($this->model->update_betrieb($_GET['id'], $g_data)) {
				info(s('betrieb_edit_success'));
				go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
			} else {
				error(s('error'));
			}
		}
	}

	private function handle_add($bezirk_id)
	{
		global $g_data;
		if (submitted()) {
			$g_data['status_date'] = date('Y-m-d H:i:s');

			if (!isset($g_data['bezirk_id'])) {
				$g_data['bezirk_id'] = getBezirkId();
			}

			$g_data['stadt'] = $g_data['ort'];

			if ($id = $this->model->add_betrieb($g_data)) {
				$this->model->add_betrieb_notiz(array(
					'foodsaver_id' => fsId(),
					'betrieb_id' => $id,
					'text' => '{BETRIEB_ADDED}',
					'zeit' => date('Y-m-d H:i:s', (time() - 10)),
					'milestone' => 1
				));

				if (isset($g_data['first_post']) && !empty($g_data['first_post'])) {
					$this->model->add_betrieb_notiz(array(
						'foodsaver_id' => fsId(),
						'betrieb_id' => $id,
						'text' => $g_data['first_post'],
						'zeit' => date('Y-m-d H:i:s'),
						'milestone' => 0
					));
				}

				$foodsaver = $this->model->getFoodsaver($g_data['bezirk_id']);

				$this->model->addBell($foodsaver, 'store_new_title', 'store_new', 'img img-store brown', array(
					'href' => '/?page=fsbetrieb&id=' . (int)$id
				), array(
					'user' => S::user('name'),
					'name' => $g_data['name']
				), 'store-new-' . (int)$id);

				info(s('betrieb_add_success'));

				go('/?page=fsbetrieb&id=' . (int)$id);
			} else {
				error(s('error'));
			}
		}
	}

	private function db_get_betrieb_kategorie()
	{
		$out = $this->model->q('
				SELECT
				`id`,
				`name`
				
				FROM 		`' . PREFIX . 'betrieb_kategorie`
				ORDER BY `name`');

		return $out;
	}

	private function db_get_betrieb_status()
	{
		$out = $this->model->q('
				SELECT
				`id`,
				`name`
				
				FROM 		`' . PREFIX . 'betrieb_status`
				ORDER BY `name`');

		return $out;
	}
}
