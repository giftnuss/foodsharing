<?php

namespace Foodsharing\Modules\EmailTemplateAdmin;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;

class EmailTemplateAdminControl extends Control
{
	public function __construct()
	{
		$this->model = new EmailTemplateAdminModel();
		$this->view = new EmailTemplateAdminView();

		if (!S::may('orga')) {
			$this->func->go('/');
		}
		parent::__construct();
	}

	public function index()
	{
		if ($this->func->getAction('neu')) {
			$this->handle_add();

			$this->func->addBread($this->func->s('bread_message_tpl'), '/?page=message_tpl');
			$this->func->addBread($this->func->s('bread_new_message_tpl'));

			$this->func->addContent($this->view->message_tpl_form());

			$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->func->pageLink('message_tpl', 'back_to_overview')
			)), $this->func->s('actions')), CNT_RIGHT);
		} elseif ($id = $this->func->getActionId('delete')) {
			if ($this->model->del_message_tpl($id)) {
				$this->func->info($this->func->s('message_tpl_deleted'));
				$this->func->goPage();
			}
		} elseif ($id = $this->func->getActionId('edit')) {
			$this->handle_edit();

			$this->func->addBread($this->func->s('bread_message_tpl'), '/?page=message_tpl');
			$this->func->addBread($this->func->s('bread_edit_message_tpl'));

			$data = $this->model->getOne_message_tpl($id);
			$this->func->setEditData($data);

			$this->func->addContent($this->view->message_tpl_form());

			$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->func->pageLink('message_tpl', 'back_to_overview')
			)), $this->func->s('actions')), CNT_RIGHT);
		} else {
			$this->func->addBread($this->func->s('message_tpl_bread'), '/?page=message_tpl');

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
					array('name' => $this->func->s('name'))
				), $rows);

				$this->func->addContent($this->v_utils->v_field($table, 'Alle E-Mail-Vorlagen'));
			} else {
				$this->func->info($this->func->s('message_tpl_empty'));
			}

			$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				array('href' => '/?page=message_tpl&a=neu', 'name' => $this->func->s('neu_message_tpl'))
			)), 'Aktionen'), CNT_RIGHT);
		}
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->func->submitted()) {
			if ($this->model->update_message_tpl($_GET['id'], $g_data)) {
				$this->func->info($this->func->s('message_tpl_edit_success'));
				$this->func->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}

	private function handle_add()
	{
		global $g_data;
		if ($this->func->submitted()) {
			if ($this->model->add_message_tpl($g_data)) {
				$this->func->info($this->func->s('message_tpl_add_success'));
				$this->func->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}
}
