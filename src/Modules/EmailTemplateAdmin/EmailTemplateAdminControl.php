<?php

namespace Foodsharing\Modules\EmailTemplateAdmin;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Modules\Core\Control;

class EmailTemplateAdminControl extends Control
{
	private $emailTemplateAdminGateway;
	private $identificationHelper;
	private $dataHelper;

	public function __construct(
		EmailTemplateAdminView $view,
		EmailTemplateAdminGateway $emailTemplateAdminGateway,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper
	) {
		$this->view = $view;
		$this->emailTemplateAdminGateway = $emailTemplateAdminGateway;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;

		parent::__construct();

		if (!$this->session->may('orga')) {
			$this->routeHelper->go('/');
		}
	}

	public function index()
	{
		if ($this->identificationHelper->getAction('neu')) {
			$this->handle_add();

			$this->pageHelper->addBread($this->translationHelper->s('bread_message_tpl'), '/?page=message_tpl');
			$this->pageHelper->addBread($this->translationHelper->s('bread_new_message_tpl'));

			$this->pageHelper->addContent($this->view->message_tpl_form());

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->routeHelper->pageLink('message_tpl', 'back_to_overview')
			)), $this->translationHelper->s('actions')), CNT_RIGHT);
		} elseif ($id = $this->identificationHelper->getActionId('delete')) {
			if ($this->emailTemplateAdminGateway->del_message_tpl($id)) {
				$this->flashMessageHelper->info($this->translationHelper->s('message_tpl_deleted'));
				$this->routeHelper->goPage();
			}
		} elseif ($id = $this->identificationHelper->getActionId('edit')) {
			$this->handle_edit();

			$this->pageHelper->addBread($this->translationHelper->s('bread_message_tpl'), '/?page=message_tpl');
			$this->pageHelper->addBread($this->translationHelper->s('bread_edit_message_tpl'));

			$data = $this->emailTemplateAdminGateway->getOne_message_tpl($id);
			$this->dataHelper->setEditData($data);

			$this->pageHelper->addContent($this->view->message_tpl_form());

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->routeHelper->pageLink('message_tpl', 'back_to_overview')
			)), $this->translationHelper->s('actions')), CNT_RIGHT);
		} else {
			$this->pageHelper->addBread($this->translationHelper->s('message_tpl_bread'), '/?page=message_tpl');

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
					array('name' => $this->translationHelper->s('name'))
				), $rows);

				$this->pageHelper->addContent($this->v_utils->v_field($table, 'Alle E-Mail-Vorlagen'));
			} else {
				$this->flashMessageHelper->info($this->translationHelper->s('message_tpl_empty'));
			}

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				array('href' => '/?page=message_tpl&a=neu', 'name' => $this->translationHelper->s('neu_message_tpl'))
			)), 'Aktionen'), CNT_RIGHT);
		}
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->submitted()) {
			if ($this->emailTemplateAdminGateway->update_message_tpl($_GET['id'], $g_data)) {
				$this->flashMessageHelper->info($this->translationHelper->s('message_tpl_edit_success'));
				$this->routeHelper->goPage();
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}

	private function handle_add()
	{
		global $g_data;
		if ($this->submitted()) {
			if ($this->emailTemplateAdminGateway->add_message_tpl($g_data)) {
				$this->flashMessageHelper->info($this->translationHelper->s('message_tpl_add_success'));
				$this->routeHelper->goPage();
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}
}
