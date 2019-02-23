<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Modules\Core\Control;

class BlogControl extends Control
{
	private $blogGateway;
	private $timeHelper;

	public function __construct(BlogModel $model, BlogView $view, BlogGateway $blogGateway, TimeHelper $timeHelper)
	{
		$this->model = $model;
		$this->view = $view;
		$this->blogGateway = $blogGateway;
		$this->timeHelper = $timeHelper;

		parent::__construct();
		if ($id = $this->func->getActionId('delete')) {
			if ($this->model->canEdit($id)) {
				if ($this->model->del_blog_entry($id)) {
					$this->func->info($this->func->s('blog_entry_deleted'));
				}
			} else {
				$this->func->info('Diesen Artikel kannst Du nicht löschen');
			}
			$this->func->goPage();
		}
		$this->pageCompositionHelper->addBread($this->func->s('blog_bread'), '/?page=blog');
		$this->pageCompositionHelper->addTitle($this->func->s('blog_bread'));
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

			$this->pageCompositionHelper->addContent($this->v_utils->v_field($out, $this->func->s('news')));
			$this->pageCompositionHelper->addContent($this->view->pager($page));
		} elseif ($page > 1) {
			$this->func->go('/?page=blog');
		}
	}

	public function read()
	{
		if ($news = $this->model->getPost($_GET['id'])) {
			$this->pageCompositionHelper->addBread($news['name']);
			$this->pageCompositionHelper->addContent($this->view->newsPost($news));
		}
	}

	public function manage()
	{
		if ($this->session->mayEditBlog()) {
			$this->pageCompositionHelper->addBread($this->func->s('manage_blog'));
			$title = 'Blog Artikel';

			$this->pageCompositionHelper->addContent($this->view->headline($title));

			if ($data = $this->model->listArticle()) {
				$this->pageCompositionHelper->addContent($this->view->listArticle($data));
			} else {
				$this->func->info($this->func->s('blog_entry_empty'));
			}

			$this->pageCompositionHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				array(
					'href' => '/?page=blog&sub=add',
					'name' => $this->func->s('new_article')
				)
			)), $this->func->s('actions')), CNT_LEFT);
		}
	}

	public function post()
	{
		if ($this->session->mayEditBlog()) {
			if (isset($_GET['id'])) {
				if ($post = $this->model->getOne_blog_entry($_GET['id'])) {
					if ($post['active'] == 1) {
						$this->pageCompositionHelper->addTitle($post['name']);
						$this->pageCompositionHelper->addBread($post['name'], '/?page=blog&post=' . (int)$post['id']);
						$this->pageCompositionHelper->addContent($this->view->topbar($post['name'], $this->timeHelper->niceDate($post['time_ts'])));
						$this->pageCompositionHelper->addContent($this->v_utils->v_field($post['body'], $post['name'], array('class' => 'ui-padding')));
					}
				}
			}
		}
	}

	public function add()
	{
		if ($this->session->mayEditBlog()) {
			$this->handle_add();

			$this->pageCompositionHelper->addBread($this->func->s('bread_new_blog_entry'));

			$bezirke = $this->session->getRegions();
			if (!$this->session->may('orga')) {
				$bot_ids = $this->session->getBotBezirkIds();
				foreach ($bezirke as $k => $v) {
					if ($v['type'] != 7 || !in_array($v['id'], $bot_ids)) {
						unset($bezirke[$k]);
					}
				}
			}

			$this->pageCompositionHelper->addContent($this->view->blog_entry_form($bezirke, true));

			$this->pageCompositionHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->func->pageLink('blog', 'back_to_overview')
			)), $this->func->s('actions')), CNT_LEFT);
		} else {
			$this->func->info('Du darfst keine Artikel erstellen!');
			$this->func->goPage();
		}
	}

	private function handle_add()
	{
		global $g_data;

		if ($this->session->mayEditBlog() && $this->func->submitted()) {
			$g_data['foodsaver_id'] = $this->session->id();
			$g_data['time'] = date('Y-m-d H:i:s');

			if ($this->model->canAdd((int)$this->session->id(), $g_data['bezirk_id']) && $this->model->add_blog_entry($g_data)) {
				$this->func->info($this->func->s('blog_entry_add_success'));
				$this->func->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}

	public function edit()
	{
		if ($this->session->mayEditBlog() && $this->model->canEdit($_GET['id']) && ($data = $this->model->getOne_blog_entry($_GET['id']))) {
			$this->handle_edit();

			$this->pageCompositionHelper->addBread($this->func->s('bread_blog_entry'), '/?page=blog&sub=manage');
			$this->pageCompositionHelper->addBread($this->func->s('bread_edit_blog_entry'));

			$this->func->setEditData($data);
			$bezirke = $this->session->getRegions();

			$this->pageCompositionHelper->addContent($this->view->blog_entry_form($bezirke));

			$this->pageCompositionHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				$this->func->pageLink('blog', 'back_to_overview')
			)), $this->func->s('actions')), CNT_LEFT);
		} else {
			$this->func->info('Diesen Artikel kannst Du nicht bearbeiten');
			$this->func->goPage();
		}
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->session->mayEditBlog() && $this->func->submitted()) {
			$data = $this->model->getValues(array('time', 'foodsaver_id'), 'blog_entry', $_GET['id']);

			$g_data['foodsaver_id'] = $data['foodsaver_id'];
			$g_data['time'] = $data['time'];

			if ($this->blogGateway->update_blog_entry($_GET['id'], $g_data)) {
				$this->func->info($this->func->s('blog_entry_edit_success'));
				$this->func->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}
}
