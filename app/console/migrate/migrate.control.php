<?php

class MigrateControl extends ConsoleControl
{		
	private $model;
	private $smtp;
	
	public function __construct()
	{
		$this->model = new MigrateModel();
		$this->smtp = false;
	}
	
	public function tmpmail()
	{
		if($users = $this->model->q('SELECT id,email FROM users WHERE deleted = 0'))
		{
			if($send = $this->model->q('SELECT user_id, email FROM mailed'))
			{
				$users = $this->filterSended($users,$send);
			}
			
			$bar = $this->progressbar(count($users));
			
			/*
			 * Make Email
			 */
			
			$email = new fEmail();
			$email->setFromEmail('info@foodsharing.de','foodsharing');
			$email->setSubject('Test');
			$email->setBody('test123');
			$email->setHTMLBody('<strong>Hallo</strong>');
			
			$this->smtpReconnect();
			
			/*
			 * Make email end
			 */
			
			info('noch ' . count($users).' zu senden');
			
			$count = 0;
			foreach ($users as $i => $u)
			{
				$count++;
				
				if($this->sendNewsletter($u['email'],$email))
				{
					$this->model->insert('INSERT INTO `mailed`(`user_id`, `email`, `time`) VALUES ('.$u['id'].','.$this->model->strval($u['email']).',NOW())');
				}
				
				
				
				if($count == 50)
				{
					$count = 0;
					$this->smtpReconnect();
					$bar->update($i);
				}
			}
		}
	}
	
	private function sendNewsletter($address,$email)
	{
		$email->clearRecipients();
		//$email->addRecipient($address);
		$email->addRecipient('raphi@waldorfweb.net');

		$max_try = 2;
		while ($max_try > 0)
		{
			try {
				$email->send($this->smtp);
				
				$max_try = 0;
				
				return true;
			} catch (Exception $e) {
				$max_try--;
				sleep(5);
				$this->smtpReconnect();
				$email->send($this->smtp);
			}
		}
		
		
		return false;
	}
	
	private function smtpReconnect()
	{
		if($this->smtp !== false)
		{
			@$this->smtp->close();
			sleep(5);
		}

		$this->smtp = new fSMTP(SMTP_HOST,25);
		$this->smtp->authenticate(SMTP_USER, SMTP_PASS);
	}
	
	private function filterSended($users,$sendet)
	{
		$t_send = array();
		foreach ($sendet as $s)
		{
			$t_send[$s['email']] = true;
		}
		
		$tmp = array();
		
		foreach ($users as $s)
		{
			if(!isset($t_send[$s['email']]))
			{
				$tmp[] = $s;
			}
		}
		
		return $tmp;
	}
	
	public function chats()
	{
		info('getold conversations');
		
		$count_complete = (int)$this->model->qOne('SELECT COUNT(id) FROM fs_message WHERE sender_id != 0 AND recip_id != 0');
		
		if($convs = $this->model->listOldConversations())
		{
			file_put_contents('convs.txt',print_r($convs,true));
			success(count($convs).' conversations found');
			$bar = $this->progressbar($count_complete);
			$x=0;
			$cur_msg_count = 0;
			foreach ($convs as $c)
			{
				$bar->update($cur_msg_count);
				$x++;
				
				$recip1 = array_shift($c);
				$recip2 = end($c);				

				if($conversation_id = $this->model->getConversationId($recip1,$recip2))
				{
					
					$mindate = '';
					$maxdate = '';
					$unread = 0;
					$last_foodsaver_id = 0;
					$last_message = '';
					$last_message_id = 0;
					
					if($messages = $this->model->listOldMessages($recip1,$recip2))
					{
						$i = 0;
						foreach ($messages as $msg)
						{
							$cur_msg_count++;
							$i++;
							if($i == 1)
							{
								$mindate = $msg['time'];
								//info($mindate);
							}
							
							$body = str_replace(array('<br />','<br>','<br/>','<p>','</p>'),"\n",$msg['msg']);
							$body = strip_tags($body);
							$body = trim($body);
							$id = $this->model->addMsg($conversation_id,$msg['sender_id'],$body,$msg['time']);
						
							if($i == count($messages))
							{
								$maxdate = $msg['time'];
								$unread = $msg['unread'];
								$last_foodsaver_id = $msg['sender_id'];
							
								$body = str_replace(array('<br />','<br>','<br/>','<p>','</p>'),"\n",$msg['msg']);
								$body = strip_tags($body);
								$body = trim($body);
							
								$last_message = $body;
								$last_message_id = $id;
								//info('max: '.$maxdate);
							}
							
						}
					}
					
					$this->model->connectUser($conversation_id,$recip1,$recip2,$unread);
					
					$this->model->updateConversation(
						$conversation_id, 
						$maxdate, 
						$mindate, 
						$last_foodsaver_id, 
						$last_message, 
						$last_message_id
					);
				}
			}
		}
		else
		{
			error('no conversations found');
		}
	}
}
