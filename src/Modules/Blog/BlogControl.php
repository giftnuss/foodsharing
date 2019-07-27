<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;

class BlogControl extends Control
{
	private $blogGateway;
	private $timeHelper;
	private $dataHelper;
	private $identificationHelper;

	public function __construct(
		BlogModel $model,
		BlogView $view,
		BlogGateway $blogGateway,
		TimeHelper $timeHelper,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper
	) {
		$this->model = $model;
		$this->view = $view;
		$this->blogGateway = $blogGateway;
		$this->timeHelper = $timeHelper;
		$this->dataHelper = $dataHelper;
		$this->identificationHelper = $identificationHelper;

		parent::__construct();
		if ($id = $this->identificationHelper->getActionId('delete')) {
			if ($this->model->canEdit($id)) {
				if ($this->model->del_blog_entry($id)) {
					$this->flashMessageHelper->info($this->translationHelper->s('blog_entry_deleted'));
				}
			} else {
				$this->flashMessageHelper->info('Diesen Artikel kannst Du nicht löschen');
			}
			$this->routeHelper->goPage();
		}
		$this->pageHelper->addBread($this->translationHelper->s('blog_bread'), '/?page=blog');
		$this->pageHelper->addTitle($this->translationHelper->s('blog_bread'));
	}

	public function index()
	{
		if (!isset($_GET['sub'])) {
			$this->listNews();
		}
	}

	public function listNews()
	{
		$page = 1;
		if (isset($_GET['p'])) {
			$page = (int)$_GET['p'];
		}

		if ($news = $this->model->listNews($page)) {
			$out = '';
			foreach ($news as $n) {
				$out .= $this->view->newsListItem($n);
			}

			$this->pageHelper->addContent($this->v_utils->v_field($out, $this->translationHelper->s('news')));
			$this->pageHelper->addContent($this->view->pager($page));
		} elseif ($page > 1) {
			$this->routeHelper->go('/?page=blog');
		}
	}

	public function read()
	{
		if ($news = $this->model->getPost($_GET['id'])) {
			$this->pageHelper->addBread($news['name']);
			$this->pageHelper->addContent($this->view->newsPost($news));
		}
	}

	public function manage()
	{
		if ($this->session->mayEditBlog()) {
			$this->pageHelper->addBread($this->translationHelper->s('manage_blog'));
			$title = 'Blog Artikel';

			$this->pageHelper->addContent($this->view->headline($title));

			if ($data = $this->model->listArticle()) {
				$this->pageHelper->addContent($this->view->listArticle($data));
			} else {
				$this->flashMessageHelper->info($this->translationHelper->s('blog_entry_empty'));
			}

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				array(
					'href' => '/?page=blog&sub=add',
					'name' => $this->translationHelper->s('new_article')
				)
			)), $this->translationHelper->s('actions')), CNT_LEFT);
		}
	}

	public function post()
	{
		if ($this->session->mayEditBlog()) {
			if (isset($_GET['id'])) {
				if ($post = $this->model->getOne_blog_entry($_GET['id'])) {
					if ($post['active'] == 1) {
						$this->pageHelper->addTitle($post['name']);
						$this->pageHelper->addBread($post['name'], '/?page=blog&post=' . (int)$post['id']);
						$this->pageHelper->addContent($this->view->topbar($post['name'], $this->timeHelper->niceDate($post['time_ts'])));
						$this->pageHelper->addContent($this->v_utils->v_field($post['body'], $post['name'], array('class' => 'ui-padding')));
					}
				}
			}
		}
	}

	public function add()
	{
		if ($this->session->mayEditBlog()) {
			$this->handle_add();

			$this->pageHelper->addBread($this->translationHelper->s('bread_new_blog_entry'));

			$regions = $this->session->getRegions();
			if (!$this->session->may('orga')) {
				$bot_ids = $this->session->getMyAmbassadorRegionIds();
				foreach ($regions as $k => $v) {
					if ($v['type'] != Type::WORKING_GROUP || !in_array($v['id'], $bot_ids)) {
						unset($regions[$k]);
					}
				}
			}

			$this->pageHelper->addContent($this->view->blog_entry_form($regions, true));

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->routeHelper->pageLink('blog', 'back_to_overview')
			)), $this->translationHelper->s('actions')), CNT_LEFT);
		} else {
			$this->flashMessageHelper->info('Du darfst keine Artikel erstellen!');
			$this->routeHelper->goPage();
		}
	}

	private function handle_add()
	{
		global $g_data;

		if ($this->session->mayEditBlog() && $this->submitted()) {
			$g_data['foodsaver_id'] = $this->session->id();
			$g_data['time'] = date('Y-m-d H:i:s');

			if ($this->model->canAdd((int)$this->session->id(), $g_data['bezirk_id']) && $this->model->add_blog_entry($g_data)) {
				$this->flashMessageHelper->info($this->translationHelper->s('blog_entry_add_success'));
				$this->routeHelper->goPage();
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}

	public function edit()
	{
		if ($this->session->mayEditBlog() && $this->model->canEdit($_GET['id']) && ($data = $this->model->getOne_blog_entry($_GET['id']))) {
			$this->handle_edit();

			$this->pageHelper->addBread($this->translationHelper->s('bread_blog_entry'), '/?page=blog&sub=manage');
			$this->pageHelper->addBread($this->translationHelper->s('bread_edit_blog_entry'));

			$this->dataHelper->setEditData($data);
			$regions = $this->session->getRegions();

			$this->pageHelper->addContent($this->view->blog_entry_form($regions));

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->routeHelper->pageLink('blog', 'back_to_overview')
			)), $this->translationHelper->s('actions')), CNT_LEFT);
		} else {
			$this->flashMessageHelper->info('Diesen Artikel kannst Du nicht bearbeiten');
			$this->routeHelper->goPage();
		}
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->session->mayEditBlog() && $this->submitted()) {
			$data = $this->model->getValues(array('time', 'foodsaver_id'), 'blog_entry', $_GET['id']);

			$g_data['foodsaver_id'] = $data['foodsaver_id'];
			$g_data['time'] = $data['time'];

			if ($this->blogGateway->update_blog_entry($_GET['id'], $g_data)) {
				$this->flashMessageHelper->info($this->translationHelper->s('blog_entry_edit_success'));
				$this->routeHelper->goPage();
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}
}
