<?php 

class MailboxXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new MailboxModel();
		$this->view = new MailboxView();

		parent::__construct();
		
		if(!S::may('bieb'))
		{
			return false;
		}
	}
	
	public function testmail()
	{
		if(!S::may('orga'))
		{
			return false;
		}
		
		if(!validEmail($_POST['email']))
		{
			return array(
				'status' => 1,
				'script' => 'pulseError("Mit der E-Mail Adresse stimmt etwas nicht!");'
			);
		}
		else
		{
			libmail(false, $_POST['email'], $_POST['subject'], $_POST['message']);
			return array(
				'status' => 1,
				'script' => 'pulseInfo("E-Mail wurde versendet!");'
			);
		}
	}
	
	public function attach()
	{		
		$init = '';
		if(isset($_FILES['etattach']['size']) && $_FILES['etattach']['size'] < 9136365 && $this->attach_allow($_FILES['etattach']['name'], $_FILES['etattach']['type']))
		{
			$new_filename = uniqid();
			
			$ext = strtolower($_FILES['etattach']['name']);
			$ext = explode('.', $ext);
			if(count($ext) > 1)
			{
				$ext = end($ext);
				$ext = trim($ext);
				$ext = '.'.preg_replace('/[^a-z0-9]/', '', $ext);
			}
			else
			{
				$ext = '';
			}
			
			$new_filename = $new_filename.$ext;
			
			move_uploaded_file($_FILES['etattach']['tmp_name'], 'data/mailattach/tmp/'.$new_filename);
			
			$init = 'window.parent.mb_finishFile("'.$new_filename.'");';
		}
		elseif(!$this->attach_allow($_FILES['etattach']['name']))
		{
			$init = 'window.parent.pulseInfo(\''.jsSafe(s('wrong_file')).'\');window.parent.mb_removeLast();';
		}
		else 
		{
			$init = 'window.parent.pulseInfo(\''.jsSafe(s('file_to_big')).'\');window.parent.mb_removeLast();';
		}
		
		echo '<html><head>

		<script type="text/javascript">
			function init()
			{
				'.$init.'
			}
		</script>
				
		</head><body onload="init();"></body></html>';
		
		exit();
	}
	
	public function loadmails()
	{
		
		$last_refresh = (int)Mem::get('mailbox_refresh');
		
		$cur_time = (int)time();

		if(
			$last_refresh == 0
			||
			($cur_time - $last_refresh) > 30
		)
		{
			Mem::set('mailbox_refresh', $cur_time);
			$ref = $this->refresh();
		}

		$mb_id = (int)$_GET['mb'];
		if($this->model->mayMailbox($mb_id,$_GET['type']))
		{
			if($messages = $this->model->listMessages($mb_id,$_GET['folder']))
			{
				$nc_js = '';
				if($boxes = $this->model->getBoxes())
				{					
					if($newcount = $this->model->getNewCount($boxes))
					{
						foreach ($newcount as $nc)
						{
							$nc_js .= '
								$( "ul.dynatree-container a.dynatree-title:contains(\''.$nc['name'].'@'.DEFAULT_HOST.'\')" ).removeClass("nonew").addClass("newmail").text("'.$nc['name'].'@'.DEFAULT_HOST.' ('.(int)$nc['count'].')");';
						}
					}
				}
				$vontext = 'Von';
				if($_GET['folder'] == 'sent')
				{
					$vontext = 'An';
				}
				return array(
					'status' => 1,
					'html' => $this->view->listMessages($messages),
					'append' => '#messagelist tbody',
					'script' => '
						$("#messagelist .from a:first").text("'.$vontext.'");
						$("#messagelist tbody tr").mouseover(function(){
							$("#messagelist tbody tr").removeClass("selected focused");
							$(this).addClass("selected focused");
							
						});
						$("#messagelist tbody tr").mouseout(function(){
							$("#messagelist tbody tr").removeClass("selected focused");							
						});
						$("#messagelist tbody tr").click(function(){
							ajreq("loadMail",{id:($(this).attr("id").split("-")[1])});
						});
						$("#messagelist tbody td").disableSelection();
						'.$nc_js.'
					'
				);
			}
			else
			{
				return array(
						'status' => 1,
						'html' => $this->view->noMessage(),
						'append' => '#messagelist tbody'
				);
			}
		}
	}
	
	public function move()
	{
		if($this->model->mayMessage($_GET['mid']))
		{
			$folder = $this->model->getVal('folder', 'mailbox_message', $_GET['mid']);
			
			$new_folder = 3;
			if($folder == 3)
			{
				$this->model->deleteMessage($_GET['mid']);
			}
			else
			{
				$this->model->move($_GET['mid'],$_GET['f']);
			}

			return array(
				'status' => 1,
				'script' => '$("tr#message-'.(int)$_GET['mid'].'").remove();$("#message-body").dialog("close");'
			);
		}
	}
	
	public function send_message()
	{
		/*
		 *  an		an
			body	body
			mb		1
			sub		betr
		 */
		
		/*
		 * security only 1 email per minute
		*/
		
		if($last = (int)Mem::user(fsId(), 'mailbox-last'))
		{
			if((time() - $last) < 60)
			{
				return array(
					'status' => 1,
					'script' => 'pulseError("Du kannst nur eine E-Mail pro Minute versenden, bitte warte einen Augenblick...");'
				);
			}
		}
		
		Mem::userSet(fsId(),'mailbox-last', time());
		
		if($this->model->mayMailbox($_POST['mb']))
		{
			if($mailbox = $this->model->getMailbox($_POST['mb']))
			{
				$an = explode(';', $_POST['an']);
				$tmp = array();
				foreach ($an as $a)
				{
					$tmp[$a] = $a;
				}
				$an = $tmp;
				if(count($an) > 100)
				{
					return array(
						'status' => 1,
						'script' => 'pulseError("Zu viele Empfänger");'
					);
				}
					
					/*
					$smtp = new fSMTP('kunden.greensta.de');
					$smtp->authenticate('admin@lebensmittelretten.de', 'passwort123');
					$email = new fEmail();
					$email->addRecipient($_POST['an']);
					$email->setBody($_POST['body']);
					$email->setSubject($_POST['sub']);
					$email->setFromEmail($mailbox.'@'.DEFAULT_HOST);
					$email->send($smtp);
					*/
					
					$attach = false;
					
					
					
					if(isset($_POST['attach']) && is_array($_POST['attach']))
					{
						$attach = array();
						foreach ($_POST['attach'] as $a)
						{
							
							if(isset($a['name']) && isset($a['tmp']))
							{
								$tmp = str_replace(array('/','\\'), '', $a['tmp']);
								$name = strtolower($a['name']);
								str_replace(array('ä','ö','ü','ß',' '), array('ae','oe','ue','ss','_'), $name);
								$name = preg_replace('/[^a-z0-9\-\.]/', '', $name);
								
								if(file_exists('data/mailattach/tmp/'.$tmp))
								{
									$attach[] = array(
										'path' => 'data/mailattach/tmp/'.$tmp,
										'name' => $name
									);
									
								}
							}
						}
					}
					
					
					
					$this->libPlainMail(
						$an,
						array(
							'email' => $mailbox['name'].'@'.DEFAULT_HOST,
							'name' => $mailbox['email_name']
						),
						$_POST['sub'],
						$_POST['body'],
						$attach
					);
					
					
					if(!empty($attach))
					{
						foreach ($attach as $a)
						{
							@unlink($a['path']);
						}
					}
					
					$to = array();
					foreach ($an as $a)
					{
						if(validEmail($a))
						{
							$t = explode('@', $a);
								
							$to[] = array(
								'personal' => $a,
								'mailbox' => $t[0],
								'host' =>$t[1]
							);
						}
					}
					
					if($this->model->saveMessage(
							$_POST['mb'], 
							2, 
							json_encode(array(
								'host' => DEFAULT_HOST,
								'mailbox' => $mailbox['name'],
								'personal' => $mailbox['email_name']
							)), 
							json_encode($to), 
							$_POST['sub'], 
							$_POST['body'], 
							nl2br($_POST['body']),
							date('Y-m-d H:i:s'),
							'',
							1
					))
					{
						$this->model->setAnswered($_POST['reply']);
						return array(
								'status' => 1,
								'script' => '
									pulseInfo("'.s('send_success').'");
									mb_clearEditor();
									mb_closeEditor();'
						);
					}
			}
		}
		
	}
	
	public function fmail()
	{
		if($this->model->mayMessage((int)$_GET['id']))
		{
			$html = $this->model->getVal('body_html', 'mailbox_message', $_GET['id']);
			
			if(strpos(strtolower($html), '<body') === false)
			{
				$html = '<html><head><style type="text/css">body,div,h1,h2,h3,h4,h5,h6,td,th,p{font-family:Arial,Helvetica,Verdana;}body,div,td,th,p{font-size:13px;}body{margin:0;padding:0;}</style></head><body onload="parent.u_readyBody();">'.$html.'</body></html>';
			}
			else
			{
				$html = str_replace(array('<body','<BODY','<Body'), '<body onload="parent.u_readyBody();"', $html);
				$html = str_replace(array('<head>','<HEAD>','<Head>'), '<head><style type="text/css">body,div,h1,h2,h3,h4,h5,h6,td,th,p{font-family:Arial,Helvetica,Verdana;}body,div,td,th,p{font-size:13px;}body{margin:0;padding:0;}</style>', $html);
			}
			
			$html = str_replace('href="mailto:', 'onclick="parent.mb_new_message(this.href.replace(\'mailto:\',\'\'));return false;" href="mailto:', $html);
			
			/*
			$html = tidy_get_output($tidy);
				
			$doc = new DOMDocument();
			$doc->loadHTML($html);
				
			$node = $dom->getElementsByTagName('body')->item(0);
			$node->setAttribute('onload','parent.u_readyBody();');
				
			echo $doc->saveHTML();
			*/
			echo $html;
			exit();
			
			//$tidy = tidy_parse_string($html);
			//$tidy->cleanRepair();
			
			//$html = tidy_get_output($tidy);
			//str_replace(array('<body'), '<body onload="parent.u_readyBody();"', $html);
			/*
			$dom = new DOMDocument();
			// we want nice output
			$dom->preserveWhiteSpace = false;
			$dom->loadHTML($html);
			$dom->formatOutput = true;
			
			$dom->getElementsByTagName('body')->item(0)
				->setAttribute('onload','parent.u_readyBody();');
			
			$style = $dom->createElement('style','body,div,h1,h2,h3,h4,h5,h6,td,th,p{font-family:Arial,Helvetica,Verdana;}body,div,td,th,p{font-size:13px;}body{margin:0;padding:0;}');
			$style->setAttribute('type', 'text/css');
			
			$head = $dom->getElementsByTagName('head')->item(0);
			$head->appendChild($style);
			
			$script_tags = $dom->getElementsByTagName('script');

			for ($i = 0; $i < $script_tags->length; $i++) {
				$script_tags->item($i)->parentNode->removeChild($script_tags->item($i));
			}
			
			echo $dom->saveHTML();
			exit;
			*/
		}
	}
	
	public function loadMail()
	{
		if($mail = $this->model->getMessage($_GET['id']))
		{
			if($this->model->mayMessage($mail['id']))
			{
				$this->model->setRead($_GET['id'],1);
				$mail['attach'] = trim($mail['attach']);
				if(!empty($mail['attach']))
				{
					$mail['attach'] = json_decode($mail['attach'],true);
				}
					
				return array(
						'status' => 1,
						'html' => $this->view->message($mail),
						'append' => '#message-body',
						'script' => '
			
					bodymin = 50;
					if($("#mailattch").length > 0)
					{
						bodymin = 93;
					}
			
					$("#message-body").dialog("option",{
						title: \''.jsSafe($mail['subject']).'\',
						height: ($( window ).height()-40)
					});
					$(".mailbox-body").css({
						"height" : ($("#message-body").height()-bodymin)+"px",
						"overflow":"auto"
					});
					$(".mailbox-body-loader").css({
						"height" : ($("#message-body").height()-bodymin)+"px",
						"overflow":"auto"
					});
					$("#message-body").dialog("open");
					$("tr#message-'.(int)$_GET['id'].' .read-0,tr#message-'.(int)$_GET['id'].'").addClass("read-1").removeClass("read-0");
					u_loadBody();'
				);
			}
		}
	}
	
	public function refresh()
	{
		return array(
				'status' => 1,
				'script' => 'mb_refresh();'
		);
	}
	
	public function cronrefresh()
	{
		error_reporting(E_ALL);
		ini_set('display_errors','1');

		$mailbox = new fMailbox('imap', IMAP_HOST, IMAP_USER, IMAP_PASS);
		
		$messages = $mailbox->listMessages();
		if(is_array($messages))
		{
			$have_send = array();
			foreach ($messages as $msg)
			{
				if($message = $mailbox->fetchMessage((int)$msg['uid']))
				{
					$mboxes = array();
					if(isset($message['headers']) && isset($message['headers']['to']))
					{
						foreach ($message['headers']['to'] as $to)
						{
							if(strtolower($to['host']) == DEFAULT_HOST)
							{
								$mboxes[] = $to['mailbox'];
							}
						}
						if(isset($message['headers']['cc']))
						{
							foreach ($message['headers']['cc'] as $to)
							{
								if(strtolower($to['host']) == DEFAULT_HOST)
								{
									$mboxes[] = $to['mailbox'];
								}
							}
						}
						if(isset($message['headers']['bcc']))
						{
							foreach ($message['headers']['cc'] as $to)
							{
								if(strtolower($to['host']) == DEFAULT_HOST)
								{
									$mboxes[] = $to['mailbox'];
								}
							}
						}
						if(empty($mboxes))
						{
							continue;
						}
	
						if($mb_ids = $this->model->getMailboxIds($mboxes))
						{
							$body = '';
							$html = '';
							if(isset($message['html']))
							{
								require_once 'lib/Html2Text.php';
								$h2t = new Html2Text($message['html']);
								$body = $h2t->get_text();
								$html = $message['html'];
								$html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
							}
							elseif(isset($message['text']))
							{
								$body = $message['text'];
								$html = nl2br(autolink($message['text']));
							}
							else
							{
								$body = json_encode($message);
							}
								
							$attach = '';
							if(isset($message['attachment']) && !empty($message['attachment']))
							{
								$attach = array();
								foreach ($message['attachment'] as $a)
								{
									if($this->attach_allow($a['filename'],$a['mimetype']))
									{
										$new_filename = uniqid();
										$path = 'data/mailattach/';
										while (file_exists($path.$new_filename))
										{
											$i++;
											$new_filename = $i.'-'.$a['filename'];
										}
	
										file_put_contents($path.$new_filename, $a['data']);
										$attach[] = array(
												'filename' => $new_filename,
												'origname' => $a['filename'],
												'mime' => $a['mimetype']
										);
									}
								}
								$attach = json_encode($attach);
							}
								
							foreach ($mb_ids as $id)
							{
								if(!isset($have_send[$id]))
								{
									$have_send[$id] = array();
								}
								$md = $message['received'].':'.$message['headers']['subject'];
								if(!isset($have_send[$id][$md]))
								{
									$have_send[$id][$md] = true;
									$this->model->saveMessage(
											$id, // mailbox id
											1, // folder
											json_encode($message['headers']['from']), // sender
											json_encode($message['headers']['to']), // to
											strip_tags($message['headers']['subject']), // subject
											$body,
											$html,
											date('Y-m-d H:i:s',strtotime($message['received'])), // time,
											$attach, // attachements
											0,
											0
									);
								}
	
							}
								
							$mailbox->deleteMessages((int)$message['uid']);
								
						}
	
					}
					//echo $message['text']."<br />==========================<br />";
				}
			}
				
		}
	}
	
	public function libPlainMail($to,$from,$subject,$message,$attach = false)
	{
		$email = false;
		if(is_array($to) && !isset($to['name']))
		{
			$email = $to;
		}
		else if(is_array($to) && isset($to['email']))
		{
			$email = $to['email'];
			$name = $to['name'];
		}
		else
		{
			$email = $to;
			$name = $to;
		}
		
		$from_email = $from;
		$from_name = $from;
		if(is_array($from))
		{
			$from_email = $from['email'];
			$from_name = $from['name'];
		}
		
		require_once ROOT_DIR.'lib/PHPMailer/class.phpmailer.php';
	
		$mail = new PHPMailer();
		//Tell PHPMailer to use SMTP
		$mail->IsSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug  = 0;
		$mail->Debugoutput = 'html';
		//Set the hostname of the mail server
		$mail->Host       = "kunden.greensta.de";
		//Set the SMTP port number - likely to be 25, 465 or 587
		$mail->Port       = 25;
		//Whether to use SMTP authentication
		$mail->SMTPAuth   = true;
		//Username to use for SMTP authentication
		$mail->Username   = "admin@lebensmittelretten.de";
		//Password to use for SMTP authentication
		$mail->Password   = "passwort123";
		//Set who the message is to be sent from
		$mail->SetFrom($from_email, $from_name);
		//Set an alternative reply-to address
		//$mail->AddReplyTo($bezirk['email'],$bezirk['email_name']);
		//Set who the message is to be sent to
	
		if(is_array($email))
		{
			foreach ($email as $e)
			{
				if(validEmail($e))
				{
					$this->model->addContact($e);
					$mail->AddAddress($e,$e);
				}
			}
		}
		else
		{
			$mail->AddAddress($email,$email);
		}
	
		//Set the subject line
		$mail->Subject = $subject;
		//Read an HTML message body from an external file, convert referenced images to embedded, convert HTML into a basic plain-text alternative body
		$mail->Body = $message;
		//$mail->AltBody = $message;
	
		//Attach an image file
		//$mail->AddAttachment('images/phpmailer_mini.gif');
		$mail->CharSet = 'utf-8';
	
		$mail->SetLanguage('de');
	
		if($attach !== false)
		{
			foreach ($attach as $a)
			{
				$mail->AddAttachment($a['path'],$a['name']);
				//$mail->Attach($a['path'],$a['mime'],'inline',$a['name']);
			}
		}
	
		//Send the message, check for errors
		if(!$mail->Send()) {
			logg($mail->ErrorInfo);
			return false;
		} else {
			return true;
		}
	}
	
	public function attach_allow($filename,$mime)
	{
		if(strlen($filename) < 300)
		{
			$ext = explode('.', $filename);
			$ext = end($ext);
			$ext = strtolower($ext);
			$notallowed = array(
				'php' => true,
				'html' => true,
				'htm' => true,
				'php5' => true,
				'php4' => true,
				'php3' => true,
				'php2' => true,
				'php1' => true
			);
			$notallowed_mime = array();
			
			if(!isset($notallowed[$ext]) && !isset($notallowed_mime[$mime]))
			{
				return true;
			}
			
		}
		
		return false;
	}
	
}

