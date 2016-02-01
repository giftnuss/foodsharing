<?php
class ContentControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new ContentModel();
		$this->view = new ContentView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		if(!isset($_GET['sub']))
		{
			if(!S::may('orga'))
			{
				go('/');
			}
			
			$db = $this->model;
			
			if(getAction('neu'))
			{
				handle_add();
			
				addBread(s('bread_content'),'/?page=content');
				addBread(s('bread_new_content'));
			
				addContent(content_form());
			
				addContent(v_field(v_menu(array(
				pageLink('content','back_to_overview')
				)),s('actions')),CNT_RIGHT);
			}
			elseif($id = getActionId('delete'))
			{
				if($db->del_content($id))
				{
					info(s('content_deleted'));
					goPage();
				}
			}
			elseif($id = getActionId('edit'))
			{
				handle_edit();
			
				addBread(s('bread_content'),'/?page=content');
				addBread(s('bread_edit_content'));
			
				$data = $db->getOne_content($id);
				setEditData($data);
			
				addContent(content_form());
			
				addContent(v_field(v_menu(array(
				pageLink('content','back_to_overview')
				)),s('actions')),CNT_RIGHT);
			}
			else if($id = getActionId('view'))
			{
				if($cnt = $this->model->getContent($id))
				{
					addBread($cnt['title']);
					addTitle($cnt['title']);

					addContent($this->view->simple($cnt));
				}
			}
			else if(isset($_GET['id']))
			{
				go('/?page=content&a=edit&id='.(int)$_GET['id']);
			}
			else
			{
				addBread(s('content_bread'),'/?page=content');
			
				if($data = $db->getBasics_content())
				{
					$rows = array();
					foreach ($data as $d)
					{
			
						$rows[] = array(
								array('cnt'=>$d['id']),
								array('cnt' => '<a class="linkrow ui-corner-all" href="/?page=content&id='.$d['id'].'">'.$d['name'].'</a>'),
								array('cnt' => v_toolbar(array('id'=>$d['id'],'types' => array('edit','delete'),'confirmMsg'=>sv('delete_sure',$d['name'])))
								));
					}
			
					$table = v_tablesorter(array(
							array('name' => 'ID','width'=>30),
							array('name' => s('name')),
							array('name' => s('actions'),'sort' => false,'width' => 50)
					),$rows);
			
					addContent(v_field($table,'Öffentliche Webseiten bearbeiten'));
				}
				else
				{
					info(s('content_empty'));
				}
			
				addContent(v_field(v_menu(array(
				array('href' => '/?page=content&a=neu','name' => s('neu_content'))
				)),'Aktionen'),CNT_RIGHT);
			}
		}
		
	}
	
	public function partner()
	{
		if($cnt = $this->model->getContent(10))
		{
			addBread($cnt['title']);
			addTitle($cnt['title']);
			
			addContent($this->view->partner($cnt));
		}
	}

	public function fuer_unternehmen()
	{
		if($cnt = $this->model->getContent(4))
		{
			addBread($cnt['title']);
			addTitle($cnt['title']);
			
			addContent($this->view->partner($cnt));
		}
	}

	public function unterstuetzung()
	{
		if($cnt = $this->model->getContent(42))
		{
			addBread($cnt['title']);
			addTitle($cnt['title']);
			
			addContent($this->view->simple($cnt));
		}
	}
	
	public function leeretonne()
	{
		if($cnt = $this->model->getContent(46))
		{
			addBread($cnt['title']);
			addTitle($cnt['title']);
			
			addContent($this->view->simple($cnt));
		}
	}

	public function fair-teiler-rettung()
	{
		if($cnt = $this->model->getContent(49))
		{
			addBread($cnt['title']);
			addTitle($cnt['title']);
			
			addContent($this->view->simple($cnt));
		}

	}
	
	public function faq()
	{
		addBread('F.A.Q');
		addTitle('F.A.Q.');
		
		$cat_ids = array(1,6,7);
		if(S::may('fs'))
		{
			$cat_ids[] = 2;
			$cat_ids[] = 4;
		}
		if(S::may('bot'))
		{
			$cat_ids[] = 5;
		}
		
		if($faq = $this->model->listFaq($cat_ids))
		{
			addContent($this->view->faq($faq));
		}
	}
	
	public function impressum()
	{
		if($cnt = $this->model->getContent(8))
		{
			addBread($cnt['title']);
			addTitle($cnt['title']);
				
			addContent($this->view->impressum($cnt));
		}
	}
	
	public function about()
	{
		if($cnt = $this->model->getContent(9))
		{
			addBread($cnt['title']);
			addTitle($cnt['title']);
				
			addContent($this->view->about($cnt));
		}
	}
	
	public function ratgeber()
	{
		$this->setContentWidth(9, 7);
		addBread('Ratgeber');
		addTitle('Ratgeber');
		addContent($this->view->ratgeber());
		
		addContent($this->view->ratgeberRight(),CNT_RIGHT);
	}
}

function content_form($title = 'Content Management')
{
	global $db;

	return v_form('faq', array(
			v_field(
					v_form_text('name',array('required'=>true)).
					v_form_text('title',array('required'=>true)),

					$title,
					array('class'=>'ui-padding')
			),
			v_field(v_form_tinymce('body',array('filemanager' => true,'public_content'=>true,'nowrapper'=>true)), 'Inhalt')
	),array('submit'=>s('save')));
}

function handle_edit()
{
	global $db;
	global $g_data;



	if(submitted())
	{
		$g_data['last_mod'] = date('Y-m-d H:i:s');
		$g_data['body'] = handleImages($g_data['body']);

		if($db->update_content($_GET['id'],$g_data))
		{
			info(s('content_edit_success'));
			go('/?page=content&a=edit&id='.(int)$_GET['id']);
		}
		else
		{
			error(s('error'));
		}
	}
}
function handle_add()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		$g_data['last_mod'] = date('Y-m-d H:i:s');
		$g_data['body'] = handleImages($g_data['body']);
		if($db->add_content($g_data))
		{
			info(s('content_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}

function handleImages($body)
{
  /* temporarily disable this as it produces broken links */
  return $body;

	if(strpos($body,'<') === false)
	{
		return $body;
	}

	$doc = new DOMDocument();
	$doc->loadHTML($body);

	$tags = $doc->getElementsByTagName('img');

	try
	{
		foreach($tags as $tag)
		{
			$src = $tag->getAttribute('src');
				
			$wwith = $tag->getAttribute('width');
			$hheight = $tag->getAttribute('height');
			$iname = $tag->getAttribute('name');
				
			if(!empty($wwith) || !empty($hheight))
			{
				$old_filepath = '';

				$file = explode('/', $src);
				$filename = end($file);

				if(strpos($src,'images/upload/') !== false)
				{
					$old_filepath = explode('images/upload', $src);
					$old_filepath = end($old_filepath);
				}
				elseif(!empty($iname) && strpos($iname,'/') !== false)
				{
					$old_filepath = $iname;
				}



				$file = 'images/upload'.$old_filepath;

				if(file_exists($file) && !is_dir($file))
				{
						
					$ffile = explode('/', $old_filepath);
					$filename = end($ffile);
						
					$new_path = 'images/content/';
					$new_filename = $filename;
					$y = 1;
						
						
						
					while (file_exists($new_path.$new_filename))
					{
						$new_filename = $y.'-'.$filename;
						$y++;
					}
					
					copy($file, $new_path.$new_filename);
					chmod($new_path.$new_filename, 0777);
					
					$fimage = new fImage($new_path.$new_filename);
					if(!empty($src) && $width = $tag->getAttribute('width'))
					{
						$fimage->resize($width, 0);
					}
					else if(!empty($src) && $height = $tag->getAttribute('height'))
					{
						$fimage->resize(0, $height);
					}
					$fimage->saveChanges();
					$tag->setAttribute('src', 'https://foodsharing.de/'.$new_path.$new_filename);
					$tag->setAttribute('name', $old_filepath);
					$tag->removeAttribute('width');
					$tag->removeAttribute('height');
				}
			}
			elseif (substr($src, 0,7) != 'http://' && substr($src, 0,8) != 'https://')
			{
				$tag->setAttribute('src','https://foodsharing.de/'.$src);
			}
		}



		$html = $doc->saveHTML();
		$html = explode('<body>', $html);
		$html = end($html);
		$html = explode('</body>', $html);
		$html = $html[0];
		return $html;
	}
	catch(Exception $e)
	{
		if(isAdmin())
		{
			echo($e->getMessage());
			die();
		}

		return $body;
	}
}
