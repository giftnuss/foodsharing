<?php

namespace Foodsharing\Modules\FAQAdmin;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;

class FAQAdminControl extends Control
{
	private $v_utils;

	public function __construct()
	{
		if (!S::may('orga')) {
			goLogin();
		}

		parent::__construct();

		global $g_view_utils;
		$this->v_utils = $g_view_utils;
		$this->view = new FAQAdminView();
		$this->model = new Model();
	}

	public function index()
	{
		if (getAction('neu')) {
			$this->handle_add();

			addBread(s('bread_faq'), '/?page=faq');
			addBread(s('bread_new_faq'));

			addContent($this->view->faq_form($this->model->getBasics_faq_category()));

			addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				pageLink('faq', 'back_to_overview')
			)), s('actions')), CNT_RIGHT);
		} elseif ($id = getActionId('delete')) {
			if ($this->model->del_faq($id)) {
				info(s('faq_deleted'));
				goPage();
			}
		} elseif ($id = getActionId('edit')) {
			$this->handle_edit();
			addBread(s('bread_faq'), '/?page=faq');
			addBread(s('bread_edit_faq'));

			$data = $this->model->getOne_faq($id);
			setEditData($data);

			addContent($this->view->faq_form($this->model->getBasics_faq_category()));

			addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				pageLink('faq', 'back_to_overview')
			)), s('actions')), CNT_RIGHT);
		} elseif (isset($_GET['id'])) {
			$data = $this->model->getOne_faq($_GET['id']);
			print_r($data);
		} else {
			addBread(s('faq_bread'), '/?page=faq');

			if ($data = $this->model->get_faq()) {
				$sort = array();
				foreach ($data as $d) {
					if (!isset($sort[$d['faq_kategorie_id']])) {
						$sort[$d['faq_kategorie_id']] = array();
					}
					$sort[$d['faq_kategorie_id']][] = $d;
				}

				foreach ($sort as $key => $data) {
					$rows = array();
					foreach ($data as $d) {
						$rows[] = array(
							array('cnt' => '<a class="linkrow ui-corner-all" href="/?page=faq&a=edit&id=' . $d['id'] . '">' . $d['name'] . '</a>'),
							array('cnt' => $this->v_utils->v_toolbar(array('id' => $d['id'], 'types' => array('edit', 'delete'), 'confirmMsg' => sv('delete_sure', $d['name'])))
							));
					}

					$table = $this->v_utils->v_tablesorter(array(
						array('name' => s('name')),
						array('name' => s('actions'), 'sort' => false, 'width' => 50)
					), $rows);

					addContent($this->v_utils->v_field($table, $this->model->getVal('name', 'faq_category', $key)));
				}
			} else {
				info(s('faq_empty'));
			}

			addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				array('href' => '/?page=faq&a=neu', 'name' => s('neu_faq'))
			)), 'Aktionen'), CNT_RIGHT);
		}
	}

	private function handle_edit()
	{
		global $db;
		global $g_data;

		if (submitted()) {
			$g_data['foodsaver_id'] = fsId();
			if ($this->model->update_faq($_GET['id'], $g_data)) {
				info(s('faq_edit_success'));
				goPage();
			} else {
				error(s('error'));
			}
		}
	}

	private function handle_add()
	{
		global $db;
		global $g_data;

		if (submitted()) {
			$g_data['foodsaver_id'] = fsId();
			if ($this->model->add_faq($g_data)) {
				info(s('faq_add_success'));
				goPage();
			} else {
				error(s('error'));
			}
		}
	}
}
