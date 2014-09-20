<?php 
class BezirkXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new BezirkModel(15);
		$this->view = new BezirkView();
		

		parent::__construct();
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
}