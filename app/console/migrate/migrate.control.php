<?php

class MigrateControl extends ConsoleControl
{		
	private $model;
	
	public function __construct()
	{
		$this->model = new MigrateModel();
	}
	
	public function chats()
	{
		info('getold conversations');
		
		$count_complete = (int)$this->model->qOne('SELECT COUNT(id) FROM fs_msg WHERE sender_id != 0 AND recip_id != 0');
		
		if($convs = $this->model->listOldConversations())
		{
			file_put_contents('convs.txt',print_r($convs,true));
			success(count($convs).' conversations found');
			$bar = $this->progressbar(count($convs));
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
