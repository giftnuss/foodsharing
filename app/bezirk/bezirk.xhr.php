<?php 

include '../../lib/XhrResponses.php';

class BezirkXhr extends Control
{
	
	public function __construct()
	{
		$this->responses = new XhrResponses;
		$this->model = new BezirkModel(15);
		$this->view = new BezirkView();
		

		parent::__construct();
	}
	
	public function followTheme()
	{
		if(!S::may())
		{
			return $this->responses->fail_permissions();
		}

		$bot_theme = $this->model->getBotThemestatus($_GET['tid']);

		if(!($bot_theme['bot_theme']==0 && mayBezirk($bot_theme['bezirk_id'])) &&
			!($bot_theme['bot_theme']==1 && isBotFor($bot_theme['bezirk_id'])))
		{
			return $this->responses->fail_generic();
		}
		$this->model->followTheme($_GET['tid']);
		return $this->responses->success();
	}

	public function unfollowTheme()
	{
		if(!S::may())
		{
			return $this->responses->fail_permissions();
		}

		$bot_theme = $this->model->getBotThemestatus($_GET['tid']);

		if(!($bot_theme['bot_theme']==0 && mayBezirk($bot_theme['bezirk_id'])) &&
			!($bot_theme['bot_theme']==1 && isBotFor($bot_theme['bezirk_id'])))
		{
			return $this->responses->fail_generic();
		}

		$this->model->unfollowTheme($_GET['tid']);
		return $this->responses->success();
	}

	public function stickTheme()
	{
		$topic_count = $this->model->checkTopicArea($_GET['bid'],$_GET['tid']);
		if($topic_count == 0 || !S::may() || (!isOrgaTeam() && !isBotFor($_GET['bid']))
		{
			return $this->responses->fail_permissions();
		}

		$this->model->stickTheme($_GET['tid']);
		return $this->responses->success();
	}

	public function unstickTheme()
	{
		$topic_count = $this->model->checkTopicArea($_GET['bid'],$_GET['tid']);
		if($topic_count == 0 || !S::may() || (!isOrgaTeam() && !isBotFor($_GET['bid']))
		{
			return $this->responses->fail_permissions();
		}

		$this->model->unstickTheme($_GET['tid']);
		return $this->responses->success();
	}

	public function morethemes()
	{
		if(isset($_GET['page']) && mayBezirk($_GET['bid']))
		{
			$sub = 'forum';
			
			if((int)$_GET['bot'] == 1)
			{
				$sub = 'botforum';
			}
			
			$this->view->bezirk_id = (int)$_GET['bid'];
			$themes = $this->model->getThemes($_GET['bid'],(int)$_GET['bot'],(int)$_GET['page'],(int)$_GET['last']);
			return array(
				'status' => 1,
				'html' => $this->view->forum_index($themes,true,$sub)
			);
		}
	}
	
	public function saverequestnote()
	{
		$note = strip_tags($_GET['note']);
		$note = trim($note);
		
		if(!empty($note))
		{
			if($this->model->mayBezirk($_GET['bid']))
			{
				$this->model->updateRequestNote($_GET['bid'],$_GET['fid'],$note);
			}
			
		}
		
	}
	
	public function regBot()
	{
		$bot_count = $this->model->getBotCount($_GET['bid']);
		$fs_count = $this->model->getFsCount($_GET['bid']);
		
		if(
			$_GET['form'] != 'bot'
			||
			($bot_count == 0)
			||
			($fs_count < 10)
		)
		{
			return array(
				'status' => 1
			);
		}
		
		$dialog = new XhrDialog();
		$dialog->setTitle('Botschafter Anmeldung noch nicht möglich');
		$dialog->addContent('
			'.v_info('
					<p>In diesem Bezirk gibt es schon <strong>'.$bot_count.' Botschafter/innen</strong> und <strong>'.$fs_count.' Foodsaver</strong></p>
			').'
			<p style="margin-top:15px;">Bitte schließe dich erst dem Netzwerk als Foodsaver an, Danke Dir sehr!</p>
		');
		
		$dialog->addButton('jetzt Foodsaver werden', 'goTo("/mach-mit/?form=foodsaver");');
		$out = $dialog->xhrout();
		
		$out['status'] = 0;
		
		return $out;
	}
	
	public function quickreply()
	{
		if(isset($_GET['bid']) && isset($_GET['tid']) && isset($_GET['pid']) && S::may() && isset($_POST['msg']) && $_POST['msg'] != '')
		{
			$sub = 'forum';
			if($_GET['sub'] != 'forum')
			{
				$sub = 'botforum';
			}
			
			$body = strip_tags($_POST['msg']);
			$body = nl2br($body);
			$body = autolink($body);
			
			if( $bezirk = $this->model->getValues( array('id','name'),'bezirk',$_GET['bid'] ) )
			{
				if($post_id = $this->model->addThemePost($_GET['tid'], $body,$_GET['pid'],$bezirk))
				{
					if($follower = $this->model->getThreadFollower($_GET['tid']))
					{
						$theme = $this->model->getVal('name','theme',$_GET['tid']);

						foreach ($follower as $f)
						{
							
							tplMail(19, $f['email'],array(
								'anrede' => genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
								'name' => $f['name'],
								'link' => 'http://www.'.DEFAULT_HOST.'/?page=bezirk&bid='.$bezirk['id'].'&sub='.$sub.'&tid='.(int)$_GET['tid'].'&pid='.$post_id.'#post'.$post_id,
								'theme' => $theme,
								'post' => $body,
								'poster' => S::user('name')
							));
							
						}
					}
				
					echo json_encode(array(
							'status' => 1,
							'message' => 'Prima! Deine Antwort wurde gespeichert.'
					));
					exit();
					
				}
			}
			
			/*
			 * end add post
			 */
			
			
		}
		
		echo json_encode(array(
				'status' => 0,
				'message' => s('post_could_not_saved')
		));
		exit();
	}
	
	public function signout() {
		$data = $_GET;
		if($this->model->mayBezirk($data['bid']))
		{
			$this->model->del('DELETE FROM `'.PREFIX.'foodsaver_has_bezirk` WHERE `bezirk_id` = '.(int)$data['bid'].' AND `foodsaver_id` = '.(int)fsId().' ');
			$this->model->del('DELETE FROM `'.PREFIX.'botschafter` WHERE `bezirk_id` = '.(int)$data['bid'].' AND `foodsaver_id` = '.(int)fsId().' ');
			return array('status' => 1);
		}

		return array('status' => 0);
	}
}
