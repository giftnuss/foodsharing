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
		if($convs = $this->model->listOldConversations())
		{
			info(count($convs).' conversations found');
			die();
			foreach ($convs as $c)
			{
				
				/*
				 	sender_id, 
					recip_id
				 */
				if($conversation_id = $this->model->getConversationId($c['sender_id'],$c['recip_id']))
				{
					$start = $this->model->getOldConvStartDate($c['sender_id'],$c['recip_id']);
					$this->model->updateConversation($conversation_id, $start, $last, $last_foodsaver_id, $last_message, $last_message_id);
				}
			}
		}
		else
		{
			error('no conversations found');
		}
	}
}
