<?php

namespace Foodsharing\Modules\EmailTemplateAdmin;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;

class EmailTemplateAdminControl extends Control
{
	private $v_utils;

	public function __construct()
	{
		$this->model = new EmailTemplateAdminModel();
		$this->view = new EmailTemplateAdminView();
		global $g_view_utils;
		$this->v_utils = $g_view_utils;

		if (!S::may('orga')) {
			go('/');
		}
		parent::__construct();
	}

	public function index()
	{
		if (getAction('neu')) {
			$this->handle_add();

			addBread(s('bread_message_tpl'), '/?page=message_tpl');
			addBread(s('bread_new_message_tpl'));

			addContent($this->view->message_tpl_form());

			addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				pageLink('message_tpl', 'back_to_overview')
			)), s('actions')), CNT_RIGHT);
		} elseif ($id = getActionId('delete')) {
			if ($this->model->del_message_tpl($id)) {
				info(s('message_tpl_deleted'));
				goPage();
			}
		} elseif ($id = getActionId('edit')) {
			$this->handle_edit();

			addBread(s('bread_message_tpl'), '/?page=message_tpl');
			addBread(s('bread_edit_message_tpl'));

			$data = $this->model->getOne_message_tpl($id);
			setEditData($data);

			addContent($this->view->message_tpl_form());

			addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				pageLink('message_tpl', 'back_to_overview')
			)), s('actions')), CNT_RIGHT);
		} else {
			addBread(s('message_tpl_bread'), '/?page=message_tpl');

			if ($data = $this->model->getBasics_message_tpl()) {
				$rows = array();
				foreach ($data as $d) {
					$rows[] = array(
						array('cnt' => $d['id']),
						array('cnt' => '<a class="linkrow ui-corner-all" href="/?page=message_tpl&a=edit&id=' . $d['id'] . '">' . $d['name'] . '</a>')
					);
				}

				$table = $this->v_utils->v_tablesorter(array(
					array('name' => 'ID', 'width' => 30),
					array('name' => s('name'))
				), $rows);

				addContent($this->v_utils->v_field($table, 'Alle E-Mail-Vorlagen'));
			} else {
				info(s('message_tpl_empty'));
			}

			addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				array('href' => '/?page=message_tpl&a=neu', 'name' => s('neu_message_tpl'))
			)), 'Aktionen'), CNT_RIGHT);
		}
	}

	private function handle_edit()
	{
		global $g_data;
		if (submitted()) {
			if ($this->model->update_message_tpl($_GET['id'], $g_data)) {
				info(s('message_tpl_edit_success'));
				goPage();
			} else {
				error(s('error'));
			}
		}
	}

	private function handle_add()
	{
		global $g_data;
		if (submitted()) {
			if ($this->model->add_message_tpl($g_data)) {
				info(s('message_tpl_add_success'));
				goPage();
			} else {
				error(s('error'));
			}
		}
	}
}
