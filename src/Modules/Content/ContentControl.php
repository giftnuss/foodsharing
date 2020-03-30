<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;
use Foodsharing\Permissions\ContentPermissions;
use Parsedown;

class ContentControl extends Control
{
	private $contentGateway;
	private $identificationHelper;
	private $dataHelper;
	private $contentPermissions;

	public function __construct(
		ContentView $view,
		ContentGateway $contentGateway,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		ContentPermissions $contentPermissions
	) {
		$this->view = $view;
		$this->contentGateway = $contentGateway;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;
		$this->contentPermissions = $contentPermissions;

		parent::__construct();
	}

	public function index(): void
	{
		if (!isset($_GET['sub'])) {
			if (!$this->contentPermissions->mayEditContent()) {
				$this->routeHelper->go('/');
			}
			$this->model;

			if ($this->identificationHelper->getAction('neu')) {
				$this->handle_add();

				$this->pageHelper->addBread($this->translationHelper->s('bread_content'), '/?page=content');
				$this->pageHelper->addBread($this->translationHelper->s('bread_new_content'));

				$this->pageHelper->addContent($this->content_form());

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
					$this->routeHelper->pageLink('content', 'back_to_overview')
				]), $this->translationHelper->s('actions')), CNT_RIGHT);
			} elseif ($id = $this->identificationHelper->getActionId('delete')) {
				if ($this->contentGateway->delete($id)) {
					$this->flashMessageHelper->info($this->translationHelper->s('content_deleted'));
					$this->routeHelper->goPage();
				}
			} elseif ($id = $this->identificationHelper->getActionId('edit')) {
				$this->handle_edit();

				$this->pageHelper->addBread($this->translationHelper->s('bread_content'), '/?page=content');
				$this->pageHelper->addBread($this->translationHelper->s('bread_edit_content'));

				$data = $this->contentGateway->getDetail($id);
				$this->dataHelper->setEditData($data);

				$this->pageHelper->addContent($this->content_form());

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
					$this->routeHelper->pageLink('content', 'back_to_overview')
				]), $this->translationHelper->s('actions')), CNT_RIGHT);
			} elseif ($id = $this->identificationHelper->getActionId('view')) {
				if ($cnt = $this->contentGateway->get($id)) {
					$this->pageHelper->addBread($cnt['title']);
					$this->pageHelper->addTitle($cnt['title']);

					$this->pageHelper->addContent($this->view->simple($cnt));
				}
			} elseif (isset($_GET['id'])) {
				$this->routeHelper->go('/?page=content&a=edit&id=' . (int)$_GET['id']);
			} else {
				$this->pageHelper->addBread($this->translationHelper->s('content_bread'), '/?page=content');

				if ($data = $this->contentGateway->list()) {
					$rows = [];
					foreach ($data as $d) {
						$rows[] = [
							['cnt' => $d['id']],
							['cnt' => '<a class="linkrow ui-corner-all" href="/?page=content&id=' . $d['id'] . '">' . $d['name'] . '</a>'],
							['cnt' => $this->v_utils->v_toolbar(['id' => $d['id'], 'types' => ['edit', 'delete'], 'confirmMsg' => $this->translationHelper->sv('delete_sure', $d['name'])])
							]];
					}

					$table = $this->v_utils->v_tablesorter([
						['name' => 'ID', 'width' => 30],
						['name' => $this->translationHelper->s('name')],
						['name' => $this->translationHelper->s('actions'), 'sort' => false, 'width' => 50]
					], $rows);

					$this->pageHelper->addContent($this->v_utils->v_field($table, 'Öffentliche Webseiten bearbeiten'));
				} else {
					$this->flashMessageHelper->info($this->translationHelper->s('content_empty'));
				}

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
					['href' => '/?page=content&a=neu', 'name' => $this->translationHelper->s('neu_content')]
				]), 'Aktionen'), CNT_RIGHT);
			}
		}
	}

	public function partner(): void
	{
		if ($cnt = $this->contentGateway->get(ContentId::PARTNER_PAGE_10)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->partner($cnt));
		}
	}

	public function unterstuetzung(): void
	{
		if ($cnt = $this->contentGateway->get(ContentId::SUPPORT_FOODSHARING_PAGE_42)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function presse(): void
	{
		if ($cnt = $this->contentGateway->get(58)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesGermany(): void
	{
		if ($cnt = $this->contentGateway->get(52)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesAustria(): void
	{
		if ($cnt = $this->contentGateway->get(61)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function communitiesSwitzerland(): void
	{
		if ($cnt = $this->contentGateway->get(62)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function forderungen(): void
	{
		if ($cnt = $this->contentGateway->get(60)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function contact(): void
	{
		if ($cnt = $this->contentGateway->get(73)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function academy(): void
	{
		if ($cnt = $this->contentGateway->get(69)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function festival(): void
	{
		if ($cnt = $this->contentGateway->get(72)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function international(): void
	{
		if ($cnt = $this->contentGateway->get(74)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function transparency(): void
	{
		if ($cnt = $this->contentGateway->get(68)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function leeretonne(): void
	{
		if ($cnt = $this->contentGateway->get(46)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function foodSharePointRescue(): void
	{
		if ($cnt = $this->contentGateway->get(49)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function faq(): void
	{
		$this->pageHelper->addBread('F.A.Q');
		$this->pageHelper->addTitle('F.A.Q.');

		$cat_ids = [1, 6, 7];
		if ($this->session->may('fs')) {
			$cat_ids[] = 2;
			$cat_ids[] = 4;
		}
		if ($this->session->may('bot')) {
			$cat_ids[] = 5;
		}

		if ($faq = $this->contentGateway->listFaq($cat_ids)) {
			$this->pageHelper->addContent($this->view->faq($faq));
		}
	}

	public function impressum(): void
	{
		if ($cnt = $this->contentGateway->get(8)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->impressum($cnt));
		}
	}

	public function about(): void
	{
		if ($cnt = $this->contentGateway->get(9)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->about($cnt));
		}
	}

	public function ratgeber(): void
	{
		$this->pageHelper->addBread('Ratgeber');
		$this->pageHelper->addTitle('Ratgeber Lebensmittelsicherheit');
		$this->pageHelper->addContent($this->view->ratgeber());
	}

	public function joininfo(): void
	{
		$this->pageHelper->addBread('Mitmachen');
		$this->pageHelper->addTitle('Mitmachen - Unsere Regeln');
		$this->pageHelper->addContent($this->view->joininfo());
	}

	public function fuer_unternehmen(): void
	{
		if ($cnt = $this->contentGateway->get(4)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->partner($cnt));
		}
	}

	public function infohub(): void
	{
		if ($cnt = $this->contentGateway->get(59)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function fsstaedte(): void
	{
		if ($cnt = $this->contentGateway->get(66)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function workshops(): void
	{
		if ($cnt = $this->contentGateway->get(71)) {
			$this->pageHelper->addBread($cnt['title']);
			$this->pageHelper->addTitle($cnt['title']);

			$this->pageHelper->addContent($this->view->simple($cnt));
		}
	}

	public function changelog(): void
	{
		$this->pageHelper->addBread('Changelog');
		$this->pageHelper->addTitle('Changelog');
		$markdown = file_get_contents('CHANGELOG.md');
		$markdown = preg_replace('/\@(\S+)/', '[@\1](https://gitlab.com/\1)', $markdown);
		$markdown = preg_replace('/!([0-9]+)/', '[!\1](https://gitlab.com/foodsharing-dev/foodsharing/merge_requests/\1)', $markdown);
		$markdown = preg_replace('/#([0-9]+)/', '[#\1](https://gitlab.com/foodsharing-dev/foodsharing/issues/\1)', $markdown);
		$Parsedown = new Parsedown();
		$cl['body'] = $Parsedown->parse($markdown);
		$cl['title'] = 'Changelog';
		$this->pageHelper->addContent($this->view->simple($cl));
	}

	private function content_form($title = 'Content Management')
	{
		return $this->v_utils->v_form('faq', [
			$this->v_utils->v_field(
				$this->v_utils->v_form_text('name', ['required' => true]) .
				$this->v_utils->v_form_text('title', ['required' => true]),

				$title,
				['class' => 'ui-padding']
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('body', ['public_content' => true, 'nowrapper' => true]), 'Inhalt')
		], ['submit' => $this->translationHelper->s('save')]);
	}

	private function handle_edit(): void
	{
		global $g_data;
		if ($this->submitted()) {
			$g_data['last_mod'] = date('Y-m-d H:i:s');
			if ($this->contentGateway->update($_GET['id'], $g_data)) {
				$this->flashMessageHelper->info($this->translationHelper->s('content_edit_success'));
				$this->routeHelper->go('/?page=content&a=edit&id=' . (int)$_GET['id']);
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}

	private function handle_add(): void
	{
		global $g_data;
		if ($this->submitted()) {
			$g_data['last_mod'] = date('Y-m-d H:i:s');
			if ($this->contentGateway->create($g_data)) {
				$this->flashMessageHelper->info($this->translationHelper->s('content_add_success'));
				$this->routeHelper->goPage();
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}
}
