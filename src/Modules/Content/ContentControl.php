<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Parsedown;

class ContentControl extends Control
{
	private $contentGateway;

	public function __construct(
		ContentModel $model,
		ContentView $view,
		ContentGateway $contentGateway
	) {
		$this->model = $model;
		$this->view = $view;
		$this->contentGateway = $contentGateway;

		parent::__construct();
	}

	public function index()
	{
		if (!isset($_GET['sub'])) {
			if (!S::may('orga')) {
				$this->func->go('/');
			}
			$this->model;

			if ($this->func->getAction('neu')) {
				$this->handle_add();

				$this->func->addBread($this->func->s('bread_content'), '/?page=content');
				$this->func->addBread($this->func->s('bread_new_content'));

				$this->func->addContent($this->content_form());

				$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					$this->func->pageLink('content', 'back_to_overview')
				)), $this->func->s('actions')), CNT_RIGHT);
			} elseif ($id = $this->func->getActionId('delete')) {
				if ($this->model->del_content($id)) {
					$this->func->info($this->func->s('content_deleted'));
					$this->func->goPage();
				}
			} elseif ($id = $this->func->getActionId('edit')) {
				$this->handle_edit();

				$this->func->addBread($this->func->s('bread_content'), '/?page=content');
				$this->func->addBread($this->func->s('bread_edit_content'));

				$data = $this->model->getOne_content($id);
				$this->func->setEditData($data);

				$this->func->addContent($this->content_form());

				$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					$this->func->pageLink('content', 'back_to_overview')
				)), $this->func->s('actions')), CNT_RIGHT);
			} elseif ($id = $this->func->getActionId('view')) {
				if ($cnt = $this->contentGateway->getContent($id)) {
					$this->func->addBread($cnt['title']);
					$this->func->addTitle($cnt['title']);

					$this->func->addContent($this->view->simple($cnt));
				}
			} elseif (isset($_GET['id'])) {
				$this->func->go('/?page=content&a=edit&id=' . (int)$_GET['id']);
			} else {
				$this->func->addBread($this->func->s('content_bread'), '/?page=content');

				if ($data = $this->model->getBasics_content()) {
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

					$this->func->addContent($this->v_utils->v_field($table, 'Öffentliche Webseiten bearbeiten'));
				} else {
					$this->func->info($this->func->s('content_empty'));
				}

				$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					array('href' => '/?page=content&a=neu', 'name' => $this->func->s('neu_content'))
				)), 'Aktionen'), CNT_RIGHT);
			}
		}
	}

	public function partner()
	{
		if ($cnt = $this->contentGateway->getContent(10)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->partner($cnt));
		}
	}

	public function unterstuetzung()
	{
		if ($cnt = $this->contentGateway->getContent(42)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->simple($cnt));
		}
	}

	public function presse()
	{
		if ($cnt = $this->contentGateway->getContent(58)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesGermany()
	{
		if ($cnt = $this->contentGateway->getContent(52)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesAustria()
	{
		if ($cnt = $this->contentGateway->getContent(61)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesSwitzerland()
	{
		if ($cnt = $this->contentGateway->getContent(62)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->simple($cnt));
		}
	}

	public function forderungen()
	{
		if ($cnt = $this->contentGateway->getContent(60)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->simple($cnt));
		}
	}

	public function leeretonne()
	{
		if ($cnt = $this->contentGateway->getContent(46)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->simple($cnt));
		}
	}

	public function fairteilerrettung()
	{
		if ($cnt = $this->contentGateway->getContent(49)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->simple($cnt));
		}
	}

	public function faq()
	{
		$this->func->addBread('F.A.Q');
		$this->func->addTitle('F.A.Q.');

		$cat_ids = array(1, 6, 7);
		if (S::may('fs')) {
			$cat_ids[] = 2;
			$cat_ids[] = 4;
		}
		if (S::may('bot')) {
			$cat_ids[] = 5;
		}

		if ($faq = $this->model->listFaq($cat_ids)) {
			$this->func->addContent($this->view->faq($faq));
		}
	}

	public function impressum()
	{
		if ($cnt = $this->contentGateway->getContent(8)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->impressum($cnt));
		}
	}

	public function about()
	{
		if ($cnt = $this->contentGateway->getContent(9)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->about($cnt));
		}
	}

	public function ratgeber()
	{
		$this->func->addBread('Ratgeber');
		$this->func->addTitle('Ratgeber Lebensmittelsicherheit');
		$this->func->addContent($this->view->ratgeber());
	}

	public function joininfo()
	{
		$this->func->addBread('Mitmachen');
		$this->func->addTitle('Mitmachen - Unsere Regeln');
		$this->func->addContent($this->view->joininfo());
	}

	public function fuer_unternehmen()
	{
		if ($cnt = $this->contentGateway->getContent(4)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->partner($cnt));
		}
	}

	public function infohub()
	{
		if ($cnt = $this->contentGateway->getContent(59)) {
			$this->func->addBread($cnt['title']);
			$this->func->addTitle($cnt['title']);

			$this->func->addContent($this->view->simple($cnt));
		}
	}

	public function changelog()
	{
		$this->func->addBread('Changelog');
		$this->func->addTitle('Changelog');
		$markdown = file_get_contents('CHANGELOG.md');
		$markdown = preg_replace('/\@(\S+)/', '[@\1](https://gitlab.com/\1)', $markdown);
		$markdown = preg_replace('/!([0-9]+)/', '[!\1](https://gitlab.com/foodsharing-dev/foodsharing/merge_requests/\1)', $markdown);
		$Parsedown = new Parsedown();
		$cl['body'] = $Parsedown->parse($markdown);
		$cl['title'] = 'Changelog';
		$this->func->addContent($this->view->simple($cl));
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
			if ($this->model->update_content($_GET['id'], $g_data)) {
				$this->func->info($this->func->s('content_edit_success'));
				$this->func->go('/?page=content&a=edit&id=' . (int)$_GET['id']);
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
			if ($this->model->add_content($g_data)) {
				$this->func->info($this->func->s('content_add_success'));
				$this->func->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}
}
