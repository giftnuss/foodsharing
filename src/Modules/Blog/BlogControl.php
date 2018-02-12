<?php

namespace Foodsharing\Modules\Blog;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;

class BlogControl extends Control
{
	public function __construct()
	{
		$this->model = new BlogModel();
		$this->view = new BlogView();

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
		$this->func->addBread($this->func->s('blog_bread'), '/?page=blog');
		$this->func->addTitle($this->func->s('blog_bread'));
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

			$this->func->addContent($this->v_utils->v_field($out, $this->func->s('news')));
			$this->func->addContent($this->view->pager($page));
		} elseif ($page > 1) {
			$this->func->go('/?page=blog');
		}
	}

	public function read()
	{
		if ($news = $this->model->getPost($_GET['id'])) {
			$this->func->addBread($news['name']);
			$this->func->addContent($this->view->newsPost($news));
		}
	}

	public function manage()
	{
		if ($this->func->mayEditBlog()) {
			$this->func->addBread($this->func->s('manage_blog'));
			$title = 'Blog Artikel';

			$this->func->addContent($this->view->headline($title));

			if ($data = $this->model->listArticle()) {
				$this->func->addContent($this->view->listArticle($data));
			} else {
				$this->func->info($this->func->s('blog_entry_empty'));
			}

			$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
				array(
					'href' => '/?page=blog&sub=add',
					'name' => $this->func->s('new_article')
				)
			)), $this->func->s('actions')), CNT_LEFT);
		}
	}

	public function post()
	{
		if ($this->func->mayEditBlog()) {
			if (isset($_GET['id'])) {
				if ($post = $this->model->getOne_blog_entry($_GET['id'])) {
					if ($post['active'] == 1) {
						$this->func->addTitle($post['name']);
						$this->func->addBread($post['name'], '/?page=blog&post=' . (int)$post['id']);
						$this->func->addContent($this->view->topbar($post['name'], $this->func->niceDate($post['time_ts'])));
						$this->func->addContent($this->v_utils->v_field($post['body'], $post['name'], array('class' => 'ui-padding')));
					}
				}
			}
		}
	}

	public function add()
	{
		if ($this->func->mayEditBlog()) {
			$this->handle_add();

			$this->func->addBread($this->func->s('bread_new_blog_entry'));

			$bezirke = $this->model->getBezirke();
			if (!S::may('orga')) {
				$bot_ids = $this->model->getBotBezirkIds();
				foreach ($bezirke as $k => $v) {
					if ($v['type'] != 7 || !in_array($v['id'], $bot_ids)) {
						unset($bezirke[$k]);
					}
				}
			}

			$this->func->addContent($this->view->blog_entry_form($bezirke, true));

			$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
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

		if ($this->func->mayEditBlog() && $this->func->submitted()) {
			$g_data['foodsaver_id'] = $this->func->fsId();
			$g_data['time'] = date('Y-m-d H:i:s');

			if ($this->model->canAdd((int)$this->func->fsId(), $g_data['bezirk_id']) && $this->model->add_blog_entry($g_data)) {
				$this->func->info($this->func->s('blog_entry_add_success'));
				$this->func->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}

	public function edit()
	{
		if ($this->func->mayEditBlog() && $this->model->canEdit($_GET['id']) && ($data = $this->model->getOne_blog_entry($_GET['id']))) {
			$this->handle_edit();

			$this->func->addBread($this->func->s('bread_blog_entry'), '/?page=blog&sub=manage');
			$this->func->addBread($this->func->s('bread_edit_blog_entry'));

			$this->func->setEditData($data);
			$bezirke = $this->model->getBezirke();

			$this->func->addContent($this->view->blog_entry_form($bezirke));

			$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
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
		if ($this->func->mayEditBlog() && $this->func->submitted()) {
			$data = $this->model->getValues(array('time', 'foodsaver_id'), 'blog_entry', $_GET['id']);

			$g_data['foodsaver_id'] = $data['foodsaver_id'];
			$g_data['time'] = $data['time'];

			if ($this->model->update_blog_entry($_GET['id'], $g_data)) {
				$this->func->info($this->func->s('blog_entry_edit_success'));
				$this->func->goPage();
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}
}
