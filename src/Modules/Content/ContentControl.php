<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Modules\Core\Control;
use Parsedown;

class ContentControl extends Control
{
	private $contentGateway;

	public function __construct(
		ContentView $view,
		ContentGateway $contentGateway
	) {
		$this->view = $view;
		$this->contentGateway = $contentGateway;

		parent::__construct();
	}

	public function index()
	{
		if (!isset($_GET['sub'])) {
			if (!$this->session->may('orga')) {
				$this->linkingHelper->go('/');
			}
			$this->model;

			if ($this->func->getAction('neu')) {
				$this->handle_add();

				$this->pageCompositionHelper->addBread($this->func->s('bread_content'), '/?page=content');
				$this->pageCompositionHelper->addBread($this->func->s('bread_new_content'));

				$this->pageCompositionHelper->addContent($this->content_form());

				$this->pageCompositionHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					$this->func->pageLink('content', 'back_to_overview')
				)), $this->func->s('actions')), CNT_RIGHT);
			} elseif ($id = $this->func->getActionId('delete')) {
				if ($this->contentGateway->delete($id)) {
					$this->func->info($this->func->s('content_deleted'));
					$this->linkingHelper->goPage();
				}
			} elseif ($id = $this->func->getActionId('edit')) {
				$this->handle_edit();

				$this->pageCompositionHelper->addBread($this->func->s('bread_content'), '/?page=content');
				$this->pageCompositionHelper->addBread($this->func->s('bread_edit_content'));

				$data = $this->contentGateway->getDetail($id);
				$this->func->setEditData($data);

				$this->pageCompositionHelper->addContent($this->content_form());

				$this->pageCompositionHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					$this->func->pageLink('content', 'back_to_overview')
				)), $this->func->s('actions')), CNT_RIGHT);
			} elseif ($id = $this->func->getActionId('view')) {
				if ($cnt = $this->contentGateway->get($id)) {
					$this->pageCompositionHelper->addBread($cnt['title']);
					$this->pageCompositionHelper->addTitle($cnt['title']);

					$this->pageCompositionHelper->addContent($this->view->simple($cnt));
				}
			} elseif (isset($_GET['id'])) {
				$this->linkingHelper->go('/?page=content&a=edit&id=' . (int)$_GET['id']);
			} else {
				$this->pageCompositionHelper->addBread($this->func->s('content_bread'), '/?page=content');

				if ($data = $this->contentGateway->list()) {
					$rows = array();
					foreach ($data as $d) {
						$rows[] = array(
							array('cnt' => $d['id']),
							array('cnt' => '<a class="linkrow ui-corner-all" href="/?page=content&id=' . $d['id'] . '">' . $d['name'] . '</a>'),
							array('cnt' => $this->v_utils->v_toolbar(array('id' => $d['id'], 'types' => array('edit', 'delete'), 'confirmMsg' => $this->func->sv('delete_sure', $d['name'])))
							));
					}

					$table = $this->v_utils->v_tablesorter(array(
						array('name' => 'ID', 'width' => 30),
						array('name' => $this->func->s('name')),
						array('name' => $this->func->s('actions'), 'sort' => false, 'width' => 50)
					), $rows);

					$this->pageCompositionHelper->addContent($this->v_utils->v_field($table, 'Öffentliche Webseiten bearbeiten'));
				} else {
					$this->func->info($this->func->s('content_empty'));
				}

				$this->pageCompositionHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					array('href' => '/?page=content&a=neu', 'name' => $this->func->s('neu_content'))
				)), 'Aktionen'), CNT_RIGHT);
			}
		}
	}

	public function partner()
	{
		if ($cnt = $this->contentGateway->get(10)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->partner($cnt));
		}
	}

	public function unterstuetzung()
	{
		if ($cnt = $this->contentGateway->get(42)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->simple($cnt));
		}
	}

	public function presse()
	{
		if ($cnt = $this->contentGateway->get(58)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesGermany()
	{
		if ($cnt = $this->contentGateway->get(52)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesAustria()
	{
		if ($cnt = $this->contentGateway->get(61)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesSwitzerland()
	{
		if ($cnt = $this->contentGateway->get(62)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->simple($cnt));
		}
	}

	public function forderungen()
	{
		if ($cnt = $this->contentGateway->get(60)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->simple($cnt));
		}
	}

	public function leeretonne()
	{
		if ($cnt = $this->contentGateway->get(46)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->simple($cnt));
		}
	}

	public function fairteilerrettung()
	{
		if ($cnt = $this->contentGateway->get(49)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->simple($cnt));
		}
	}

	public function faq(): void
	{
		$this->pageCompositionHelper->addBread('F.A.Q');
		$this->pageCompositionHelper->addTitle('F.A.Q.');

		$cat_ids = array(1, 6, 7);
		if ($this->session->may('fs')) {
			$cat_ids[] = 2;
			$cat_ids[] = 4;
		}
		if ($this->session->may('bot')) {
			$cat_ids[] = 5;
		}

		if ($faq = $this->contentGateway->listFaq($cat_ids)) {
			$this->pageCompositionHelper->addContent($this->view->faq($faq));
		}
	}

	public function impressum()
	{
		if ($cnt = $this->contentGateway->get(8)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->impressum($cnt));
		}
	}

	public function about()
	{
		if ($cnt = $this->contentGateway->get(9)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->about($cnt));
		}
	}

	public function ratgeber()
	{
		$this->pageCompositionHelper->addBread('Ratgeber');
		$this->pageCompositionHelper->addTitle('Ratgeber Lebensmittelsicherheit');
		$this->pageCompositionHelper->addContent($this->view->ratgeber());
	}

	public function joininfo()
	{
		$this->pageCompositionHelper->addBread('Mitmachen');
		$this->pageCompositionHelper->addTitle('Mitmachen - Unsere Regeln');
		$this->pageCompositionHelper->addContent($this->view->joininfo());
	}

	public function fuer_unternehmen()
	{
		if ($cnt = $this->contentGateway->get(4)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->partner($cnt));
		}
	}

	public function infohub()
	{
		if ($cnt = $this->contentGateway->get(59)) {
			$this->pageCompositionHelper->addBread($cnt['title']);
			$this->pageCompositionHelper->addTitle($cnt['title']);

			$this->pageCompositionHelper->addContent($this->view->simple($cnt));
		}
	}

	public function changelog()
	{
		$this->pageCompositionHelper->addBread('Changelog');
		$this->pageCompositionHelper->addTitle('Changelog');
		$markdown = file_get_contents('CHANGELOG.md');
		$markdown = preg_replace('/\@(\S+)/', '[@\1](https://gitlab.com/\1)', $markdown);
		$markdown = preg_replace('/!([0-9]+)/', '[!\1](https://gitlab.com/foodsharing-dev/foodsharing/merge_requests/\1)', $markdown);
		$markdown = preg_replace('/#([0-9]+)/', '[#\1](https://gitlab.com/foodsharing-dev/foodsharing/issues/\1)', $markdown);
		$Parsedown = new Parsedown();
		$cl['body'] = $Parsedown->parse($markdown);
		$cl['title'] = 'Changelog';
		$this->pageCompositionHelper->addContent($this->view->simple($cl));
	}

	private function content_form($title = 'Content Management')
	{
		return $this->v_utils->v_form('faq', array(
			$this->v_utils->v_field(
				$this->v_utils->v_form_text('name', array('required' => true)) .
				$this->v_utils->v_form_text('title', array('required' => true)),

				$title,
				array('class' => 'ui-padding')
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('body', array('public_content' => true, 'nowrapper' => true)), 'Inhalt')
		), array('submit' => $this->func->s('save')));
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->func->submitted()) {
			$g_data['last_mod'] = date('Y-m-d H:i:s');
			if ($this->contentGateway->update($_GET['id'], $g_data)) {
				$this->func->info($this->func->s('content_edit_success'));
				$this->linkingHelper->go('/?page=content&a=edit&id=' . (int)$_GET['id']);
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}

	private function handle_add()
	{
		global $g_data;
		if ($this->func->submitted()) {
			$g_data['last_mod'] = date('Y-m-d H:i:s');
			if ($this->contentGateway->create($g_data)) {
				$this->func->info($this->func->s('content_add_success'));
				$this->linkingHelper->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}
}
