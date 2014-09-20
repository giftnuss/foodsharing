<?php 
class ChatXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new ChatModel();
		$this->view = new ChatView();

		parent::__construct();
	}
	
	public function init()
	{
		if(may())
		{
			if($user = $this->model->getUser($_GET['id']))
			{
				$msg = false;
				if($m = $this->model->getLasMsg($_GET['id']))
				{
					foreach ($m as $k => $mm)
					{
						$m[$k]['t'] = date('c',strtotime($mm['sent']));
					}
					$this->model->setRead($_GET['id']);
					$msg = $m;
				}
				$user['msg'] = $msg;
				return $user;
			}
		}
	}
	
	public function sanitize($text) {
		$text = htmlspecialchars($text, ENT_QUOTES);
		$text = str_replace("\n\r","\n",$text);
		$text = str_replace("\r\n","\n",$text);
		$text = str_replace("\n","<br>",$text);
		return $text;
	}
	
	public function sendchat() 
	{
		$_SESSION['isChatting'] = true;
		$from = fsId();
		$to = (int)$_POST['to'];
		$message = $_POST['message'];
	
		$_SESSION['openChatBoxes'][$_POST['to']] = date('Y-m-d H:i:s', time());
	
		$messagesan = $this->sanitize($message);
	
		if (!isset($_SESSION['chatHistory'][$_POST['to']])) {
			$_SESSION['chatHistory'][$_POST['to']] = '';
		}
	
		$user = $this->model->getUser2($_POST['to']);
	
		$_SESSION['chatHistory'][$_POST['to']] .= <<<EOD
					   {
			"s": "1",
			"f": "{$to}",
			"m": "{$messagesan}",
			"n":"{$user['name']}",
			"p":"{$user['photo']}"
	   },
EOD;
		unset($_SESSION['tsChatBoxes'][$_POST['to']]);
	
		
		$sql = 'INSERT INTO fs_message 
				(sender_id,recip_id,msg,`time`,`unread`,`attach`,`name`) 
				values ('.(int)$from.', '.(int)$to.','.$this->model->strval($messagesan).',NOW(),1,"","")';
		
		$id = $this->model->insert($sql);
		
		if(!empty($user['gcm']) || !empty($user['iosid']))
		{
			$this->model->addPushQueue($from, $to, S::user('name').' hat Dir eine Nachricht geschrieben', $messagesan,array(
				'gcm' => $user['gcm'],
				'iosid' => $user['iosid']
			),array('t' => 0,'i'=>(int)$from,'c' => time()),$id);
		}
		
		//$this->pushMessage($to, strip_tags($messagesan), 'Du hast eine Nachricht erhalten',array('t' => '1','i' => $to));
		
		//$this->model->message($to, $from, strip_tags($messagesan),);
		$this->mailMessage(fsId(), (int)$to, strip_tags($messagesan));
		
		echo json_encode(array(
				'status' => 1,
				'f' => $user['id'],
				'n' => $user['name'],
				'p' => $user['photo']
		));
		exit();
	
		/*
		 echo "1";
		exit(0);
		*/
	}
}