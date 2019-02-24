<?php

namespace Foodsharing\Modules\EmailTemplateAdmin;

use Foodsharing\Modules\Core\Control;

class EmailTemplateAdminControl extends Control
{
	private $emailTemplateAdminGateway;

	public function __construct(EmailTemplateAdminView $view, EmailTemplateAdminGateway $emailTemplateAdminGateway)
	{
		$this->view = $view;
		$this->emailTemplateAdminGateway = $emailTemplateAdminGateway;

		parent::__construct();

		if (!$this->session->may('orga')) {
			$this->routeHelper->go('/');
		}
	}

	public function index()
	{
		if ($this->func->getAction('neu')) {
			$this->handle_add();

			$this->pageHelper->addBread($this->func->s('bread_message_tpl'), '/?page=message_tpl');
			$this->pageHelper->addBread($this->func->s('bread_new_message_tpl'));

			$this->pageHelper->addContent($this->view->message_tpl_form());

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->func->pageLink('message_tpl', 'back_to_overview')
			)), $this->func->s('actions')), CNT_RIGHT);
		} elseif ($id = $this->func->getActionId('delete')) {
			if ($this->emailTemplateAdminGateway->del_message_tpl($id)) {
				$this->func->info($this->func->s('message_tpl_deleted'));
				$this->routeHelper->goPage();
			}
		} elseif ($id = $this->func->getActionId('edit')) {
			$this->handle_edit();

			$this->pageHelper->addBread($this->func->s('bread_message_tpl'), '/?page=message_tpl');
			$this->pageHelper->addBread($this->func->s('bread_edit_message_tpl'));

			$data = $this->emailTemplateAdminGateway->getOne_message_tpl($id);
			$this->func->setEditData($data);

			$this->pageHelper->addContent($this->view->message_tpl_form());

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->func->pageLink('message_tpl', 'back_to_overview')
			)), $this->func->s('actions')), CNT_RIGHT);
		} else {
			$this->pageHelper->addBread($this->func->s('message_tpl_bread'), '/?page=message_tpl');

			if ($data = $this->emailTemplateAdminGateway->getBasics_message_tpl()) {
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

				$this->pageHelper->addContent($this->v_utils->v_field($table, 'Alle E-Mail-Vorlagen'));
			} else {
				$this->func->info($this->func->s('message_tpl_empty'));
			}

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				array('href' => '/?page=message_tpl&a=neu', 'name' => $this->func->s('neu_message_tpl'))
			)), 'Aktionen'), CNT_RIGHT);
		}
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->func->submitted()) {
			if ($this->emailTemplateAdminGateway->update_message_tpl($_GET['id'], $g_data)) {
				$this->func->info($this->func->s('message_tpl_edit_success'));
				$this->routeHelper->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}

	private function handle_add()
	{
		global $g_data;
		if ($this->func->submitted()) {
			if ($this->emailTemplateAdminGateway->add_message_tpl($g_data)) {
				$this->func->info($this->func->s('message_tpl_add_success'));
				$this->routeHelper->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}
}
