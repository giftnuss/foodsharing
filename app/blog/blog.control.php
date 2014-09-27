<?php
class BlogControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new BlogModel();
		$this->view = new BlogView();
		
		parent::__construct();
		if($id = getActionId('delete'))
		{
			if($this->model->canEdit($id))
			{
				if($this->model->del_blog_entry($id))
				{
					info(s('blog_entry_deleted'));
				}
			}
			else
			{
				info('Diesen Artikel kannst Du nicht löschen');
			}
			goPage();
		}
		addBread(s('blog_bread'),'?page=blog');
		addTitle(s('blog_bread'));
	}
	
	public function index()
	{
		
	}
	
	public function manage()
	{
		if(S::may('bot'))
		{
			addBread(s('manage_blog'));
			$title = 'Blog Artikel';
			
			addContent($this->view->headline($title));
				
			if($data = $this->model->listArticle())
			{
				addContent($this->view->listArticle($data));
			}
			else
			{
				info(s('blog_entry_empty'));
			}
				
			addContent(v_field(v_menu(array(
			array(
			'href' => '?page=blog&sub=add',
			'name' => s('new_article')
			)
			)),s('actions')),CNT_LEFT);
		}
	}
	
	public function post()
	{
		if(isset($_GET['id']))
		{
			if($post = $this->model->getOne_blog_entry($_GET['id']))
			{
				if($post['active'] == 1)
				{
					addTitle($post['name']);
					addBread($post['name'],'?page=blog&post=' . (int)$post['id']);
					addContent($this->view->topbar($post['name'], niceDate($post['time_ts'])));
					addContent(v_field($post['body'], $post['name'],array('class' => 'ui-padding')));
				}
			}
		}
	}
	
	public function add()
	{
		$this->handle_add();
		
		addBread(s('bread_new_blog_entry'));
		
		$bezirke = $this->model->getBezirke();
		
		addContent($this->view->blog_entry_form($bezirke,true));
		
		addContent(v_field(v_menu(array(
			pageLink('blog','back_to_overview')
		)),s('actions')),CNT_LEFT);
	}
	
	public function handle_add()
	{
		global $g_data;
		
		if(submitted())
		{
			$g_data['foodsaver_id'] = fsId();
			$g_data['time'] = date('Y-m-d H:i:s');
			
			if($this->model->add_blog_entry($g_data))
			{
				info(s('blog_entry_add_success'));
				goPage();
			}
			else
			{
				error(s('error'));
			}
		}
	}
	
	public function edit()
	{
		if($this->model->canEdit($_GET['id']) && ($data = $this->model->getOne_blog_entry($_GET['id'])))
		{
			$this->handle_edit();
			
			addBread(s('bread_blog_entry'),'?page=blog&sub=manage');
			addBread(s('bread_edit_blog_entry'));
			
			
			setEditData($data);
			$bezirke = $this->model->getBezirke();
			
			addContent($this->view->blog_entry_form($bezirke));
				
			addContent(v_field(v_menu(array(
			pageLink('blog','back_to_overview')
			)),s('actions')),CNT_LEFT);
		}
		else
		{
			info('Diesen Artikel kannst Du nicht bearbeiten');
			goPage();
		}
	}
	
	public function handle_edit()
	{
		global $g_data;
		if(submitted())
		{
			
			
			$data = $this->model->getValues(array('time','foodsaver_id'), 'blog_entry', $_GET['id']);

			$g_data['foodsaver_id'] = $data['foodsaver_id'];
			$g_data['time'] = $data['time'];
			
			
			if($this->model->update_blog_entry($_GET['id'],$g_data))
			{
				info(s('blog_entry_edit_success'));
				goPage();
			}
			else
			{
				error(s('error'));
			}
		}
	}
}