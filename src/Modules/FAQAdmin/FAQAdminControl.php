<?php

namespace Foodsharing\Modules\FAQAdmin;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\FAQPermissions;

class FAQAdminControl extends Control
{
	private $faqGateway;
	private $identificationHelper;
	private $dataHelper;
	private $faqPermissions;

	public function __construct(
		FAQAdminView $view,
		FAQGateway $faqGateway,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		FAQPermissions $faqPermissions
	) {
		$this->view = $view;
		$this->faqGateway = $faqGateway;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;
		$this->faqPermissions = $faqPermissions;

		parent::__construct();

		if (!$this->faqPermissions->mayEditFAQ()) {
			$this->routeHelper->goLogin();
		}
	}

	public function index()
	{
		if ($this->identificationHelper->getAction('neu')) {
			$this->handle_add();

			$this->pageHelper->addBread($this->translationHelper->s('bread_faq'), '/?page=faq');
			$this->pageHelper->addBread($this->translationHelper->s('bread_new_faq'));

			$this->pageHelper->addContent($this->view->faq_form($this->faqGateway->getBasics_faq_category()));

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
				$this->routeHelper->pageLink('faq', 'back_to_overview')
			]), $this->translationHelper->s('actions')), CNT_RIGHT);
		} elseif ($id = $this->identificationHelper->getActionId('delete')) {
			if ($this->faqGateway->del_faq($id)) {
				$this->flashMessageHelper->info($this->translationHelper->s('faq_deleted'));
				$this->routeHelper->goPage();
			}
		} elseif ($id = $this->identificationHelper->getActionId('edit')) {
			$this->handle_edit();
			$this->pageHelper->addBread($this->translationHelper->s('bread_faq'), '/?page=faq');
			$this->pageHelper->addBread($this->translationHelper->s('bread_edit_faq'));

			$data = $this->faqGateway->getOne_faq($id);
			$this->dataHelper->setEditData($data);

			$this->pageHelper->addContent($this->view->faq_form($this->faqGateway->getBasics_faq_category()));

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
				$this->routeHelper->pageLink('faq', 'back_to_overview')
			]), $this->translationHelper->s('actions')), CNT_RIGHT);
		} elseif (isset($_GET['id'])) {
			$data = $this->faqGateway->getOne_faq($_GET['id']);
			print_r($data);
		} else {
			$this->pageHelper->addBread($this->translationHelper->s('faq_bread'), '/?page=faq');

			if ($data = $this->faqGateway->get_faq()) {
				$sort = [];
				foreach ($data as $d) {
					if (!isset($sort[$d['faq_kategorie_id']])) {
						$sort[$d['faq_kategorie_id']] = [];
					}
					$sort[$d['faq_kategorie_id']][] = $d;
				}

				$categoryData = $this->faqGateway->getBasics_faq_category();
				$mappedData = array_combine(array_column($categoryData, 'id'), array_column($categoryData, 'name'));
				foreach ($sort as $key => $data) {
					$rows = [];
					foreach ($data as $d) {
						$rows[] = [
							['cnt' => '<a class="linkrow ui-corner-all" href="/?page=faq&a=edit&id=' . $d['id'] . '">' . $d['name'] . '</a>'],
							['cnt' => $this->v_utils->v_toolbar(['id' => $d['id'], 'types' => ['edit', 'delete'], 'confirmMsg' => $this->translationHelper->sv('delete_sure', $d['name'])])
							]];
					}

					$table = $this->v_utils->v_tablesorter([
						['name' => $this->translationHelper->s('name')],
						['name' => $this->translationHelper->s('actions'), 'sort' => false, 'width' => 50]
					], $rows);

					$this->pageHelper->addContent($this->v_utils->v_field($table, $mappedData[$key]));
				}
			} else {
				$this->flashMessageHelper->info($this->translationHelper->s('faq_empty'));
			}

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
				['href' => '/?page=faq&a=neu', 'name' => $this->translationHelper->s('neu_faq')]
			]), 'Aktionen'), CNT_RIGHT);
		}
	}

	private function handle_edit()
	{
		global $g_data;

		if ($this->submitted()) {
			$g_data['foodsaver_id'] = $this->session->id();
			if ($this->faqGateway->update_faq($_GET['id'], $g_data)) {
				$this->flashMessageHelper->info($this->translationHelper->s('faq_edit_success'));
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
			$g_data['foodsaver_id'] = $this->session->id();
			if ($this->faqGateway->add_faq($g_data)) {
				$this->flashMessageHelper->info($this->translationHelper->s('faq_add_success'));
				$this->routeHelper->goPage();
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}
}
