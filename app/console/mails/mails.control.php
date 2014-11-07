<?php

class MailsControl extends ConsoleControl
{		
	static $smtp = false;
	static $last_connect;
	private $model;
	
	public function __construct()
	{
		error_reporting(E_ALL);
		ini_set('display_errors','1');
		MailsControl::$smtp = false;
	}
	
	/**
	 * Method to start socket server the server will listen for mail jobs
	 */
	private function socketServer()
	{
		$server = new SocketServer();
		
		$server->addHandler('email',$this,'handleEmail');
		
		$server->start();
	}
	
	/**
	 * default method starts the server
	 */
	public function index()
	{
		$this->socketServer();
	}
	
	/**
	 * public method to stop socket server friendly
	 * accessable over commandline with: php run.php mails stop
	 */
	public function stop()
	{
		$client = new SocketClient();
		
		$client->serverSignal('close');
	}
	
	/**
	 * This Method will check for new E-Mails and sort it to the mailboxes
	 */
	public function mailboxupdate()
	{
		$this->model = new MailsModel();
		
		$mailbox = new fMailbox('imap', IMAP_HOST, IMAP_USER, IMAP_PASS);
		
		$messages = $mailbox->listMessages();
		if(is_array($messages))
		{
			info(count($messages).' in Inbox');
			
			$progressbar = $this->progressbar(count($messages));
			
			$have_send = array();
			$i=0;
			
			foreach ($messages as $msg)
			{
				$i++;
				$progressbar->update($i);
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
							$mailbox->deleteMessages((int)$msg['uid']);
							continue;
						}
		
						$mb_ids = $this->model->getMailboxIds($mboxes);
						
						if(!$mb_ids)
						{
							$mb_ids = $this->model->getMailboxIds(array('lost'));
						}
						
						if( $mb_ids )
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
						}
					}
				}
				
				$mailbox->deleteMessages((int)$msg['uid']);
			}
			echo "\n";
			success('ready :o)');
		}
	}
	
	private function attach_allow($filename,$mime)
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
	
	public static function handleEmail($data)
	{
		$data = $data->getData();
		info('mail arrived ...');
		$email = new fEmail();
		$email->setFromEmail($data['from'][0],$data['from'][1]);
		$email->setSubject($data['subject']);
		$email->setHTMLBody($data['html']);
		$email->setBody($data['body']);
		
		if(!empty($data['attachments']))
		{
			foreach ($data['attachments'] as $a)
			{
				$file = new fFile($a[0]);
				
				// only files smaller 10 MB
				if($file->getSize() < 1310720)
				{
					$email->addAttachment($file,$a[1]);
				}
			}
		}
		
		foreach ($data['recipients'] as $r)
		{
			$email->addRecipient($r[0],$r[1]);
		}
		
		// reconnect first time and force after 60 seconds inactive
		if(MailsControl::$smtp === false || (time() - MailsControl::$last_connect) > 60)
		{
			MailsControl::smtpReconnect();
		}
		
		$max_try = 5;
		$sended = false;
		while(!$sended)
		{
			$max_try--;
			try {
				info('send email try '.(5-$max_try));
				$email->send(MailsControl::$smtp);
				success('email send OK');
				
				// remove atachements from temp folder
				if(!empty($data['attachments']))
				{
					foreach ($data['attachments'] as $a)
					{
						@unlink($a[0]);
					}
				}
				
				return true;
				$sended = true;
				break;
			} 
			catch (Exception $e) 
			{
				MailsControl::smtpReconnect();
				error('email send error: ' . $e->getMessage());
			}
			
			if($max_try == 0)
			{
				return false;
				break;
			}
		}
		
		return true;
	}
	
	/**
	 * checks current status and renew the connection to smtp server
	 */
	public static function smtpReconnect()
	{
		info('SMTP reconnect.. ');
		try
		{
			if(MailsControl::$smtp !== false)
			{
				info('close smtp and sleep 5 sec ...');
				@MailsControl::$smtp->close();
				//sleep(5);
			}

			info('connect...');
			MailsControl::$smtp = new fSMTP(SMTP_HOST,SMTP_PORT);
			//MailsControl::$smtp->authenticate(SMTP_USER, SMTP_PASS);
			MailsControl::$last_connect = time();
			
			success('reconnect OK');
			
			return true;
		}
		catch (Exception $e)
		{
			error('reconnect failed: ' . $e->getMessage());
			return false;
		}
	
		return true;
	}
}
