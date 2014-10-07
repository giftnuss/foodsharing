<?php 
class MsgXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new MsgModel();
		$this->view = new MsgView();

		parent::__construct();
		
		if(!S::may())
		{
			echo '';
			exit();
		}
	}
	
	/**
	 * ajax call to refresh infobar messages
	 */
	public function infobar()
	{
		$xhr = new Xhr();
		$conversations = $this->model->listConversations(10);
		$xhr->addData('html', $this->view->conversationList($conversations,'conv.chat'));
		
		$xhr->send();
	}
	
	/**
	 * ajax call to load an existing conversation
	 */
	public function loadconversation()
	{
		if($this->mayConversation((int)$_GET['id']))
		{
			if($member = $this->model->listConversationMembers($_GET['id']))
			{
				$xhr = new Xhr();
				$xhr->addData('member', $member);
				if($msgs = $this->model->loadConversationMessages($_GET['id']))
				{
					$xhr->addData('messages', $msgs);
				}
				
				$xhr->send();
			}
		}
	}
	
	/**
	 * ajax call to send a message to an conversation
	 * 
	 * GET['b'] = body text
	 * GET['c'] = conversation id
	 */
	public function sendmsg()
	{
		$xhr = new Xhr();
		if($this->mayConversation($_POST['c']))
		{
			
			if(isset($_POST['b']))
			{
				$body = strip_tags($_POST['b']);
				$body = trim($body);
				if(!empty($body))
				{
					if($message_id = $this->model->sendMessage($_POST['c'],$body))
					{
						$xhr->setStatus(1);
						$xhr->addData('msg', array(
							'id' => $message_id,
							'body' => $body,
							'time' => date('Y-m-d H:i:s'),
							'fs_photo' => S::user('photo'),
							'fs_name' => S::user('name'),
							'fs_id' 
						));
						$xhr->send();
					}
				}
			}
		}
		$xhr->addMessage(s('error'),'error');
		$xhr->send();
	}
	
	/**
	 * ajax call to load all active conversations
	 */
	public function loadconvlist()
	{
		session_write_close();
		
		if($conversations = $this->model->listConversations())
		{
			$xhr = new Xhr();
			$xhr->addData('convs', $conversations);
			$xhr->send();
		}
	}
	
	/**
	 * Method to check that the user is part of an conversation and has access, to reduce database querys we store conversation_ids in an array
	 * 
	 * @param Integer $conversation_id
	 */
	private function mayConversation($conversation_id)
	{
		$ids = array();
		
		// first get the session array
		if(!($ids = S::get('msg_conversations')))
		{
			$ids = array();
		}
		
		// check if the conversation in stored in the session
		if(isset($ids[(int)$conversation_id]))
		{
			return true;
		}
		else if($this->model->mayConversation($conversation_id))
		{
			$ids[$conversation_id] = true;
			S::set('msg_conversations', $ids);
			return true;
		}
		
		return false;
	}
	
	public function user2conv()
	{
		$xhr = new Xhr();
		
		if(isset($_GET['fsid']) && (int)$_GET['fsid'] > 0)
		{
			if($cid = $this->model->addConversation(array((int)$_GET['fsid']=>(int)$_GET['fsid']),false))
			{
				$xhr->setStatus(1);
				$xhr->addData('cid', $cid);
				$xhr->send();
			}
		}
		
		$xhr->setStatus(0);
		$xhr->send();
	}
	
	/**
	 * ajax call to add an new conversation to this call comes 2 important POST parameters recip => an array with user ids body => the message body text
	 */
	public function newconversation()
	{
		/*
		 *  body	asd
			recip[]	56
			recip[]	58
		 */
		
		/*
		 * Check is there are correct post data sendet?
		 */
		if(isset($_POST['recip']) && isset($_POST['body']))
		{
			/*
			 * initiate an xhr object
			 */
			$xhr = new Xhr();
			
			/*
			 * Make all ids to int and remove doubles check its not 0
			 */
			$recip = array();
			foreach ($_POST['recip'] as $r)
			{
				if((int)$r > 0)
				{
					$recip[(int)$r] = (int)$r;
				}				
			}
			
			/*
			 * quick body text preparing
			 */
			$body = trim(strip_tags($_POST['body']));
			
			if(!empty($recip) && $body != '')
			{
				/*
				 * add conversation if successfull send an success message otherwise error
				 */
				if($cid = $this->model->addConversation($recip,$body))
				{
					/*
					 * add the conversation id to ajax output
					 */
					$xhr->addData('cid', $cid);
					//$xhr->addMessage(s('send_successfull'),'success');
				}
				else
				{
					$xhr->addMessage(s('error'),'error');
				}
			}
			else
			{
				$xhr->addMessage(s('wrong_recip_count'),'error');
			}
			
			/*
			 * send all ajax stuff to the client
			 */
			$xhr->send();
		}
		
	}
	
	/**
	 * ajax call to check every time updates in all conversations
	 * GET[m] is the last message id and GET[cid] is the current conversation id
	 */
	public function heartbeat()
	{
		S::noWrite();
		
		$xhr = new Xhr();
		$xhr->keepAlive(60);
		
		$cid = false;
		$lmid = false;
		if(isset($_GET['cid']) && $this->mayConversation($_GET['cid']) && isset($_GET['mid']))
		{
			$cid = (int)$_GET['cid'];
			$lmid = (int)$_GET['mid'];
		}
		
		for($i=0;$i<30;$i++)
		{
		// so on after 10 seconds one complete conversation update check
			if($conv_ids = $this->model->checkConversationUpdates())
			{
				$this->model->setAsRead($conv_ids);
			
				/*
				 * check is a new message there for active conversation?
				 */
				if($cid && isset($conv_ids[$cid]))
				{
					if($messages = $this->model->getLastMessages($cid,$lmid))
					{
						$xhr->addData('messages', $messages);
					}
				}
				
				$xhr->setStatus(1);
				$xhr->addData('conv_ids', $conv_ids);
				if($convs = $this->model->listConversationUpdates($conv_ids))
				{
					$xhr->addData('convs', $convs);
				}
				$xhr->send();
			}
				
			// sleep 0.5 second for conversation updates
			usleep(500000);
		}
		
		
		$xhr->setStatus(0);
		$xhr->send();
	}
	
	/**
	 * polling call for retrieving new chat messages to given conversations
	 */
	public function chat($opt)
	{
		if($conv_ids = $this->model->checkChatUpdates($opt['ids']))
		{
			$this->model->setAsRead($conv_ids);
			
			/*
			 * check is a new message there for active conversations?
			*/
			
			$out = array();
			foreach ($opt['infos'] as $i)
			{
				if(isset($conv_ids[$i['id']]))
				{
					if($messages = $this->model->getLastMessages($i['id'],$i['lmid']))
					{
						$out[] = array(
							'cid' => $i['id'],
							'msg' => $messages
						);
					}
				}
				
			}
			
			if(!empty($out))
			{
				return array(
					'data' => $out,
					'script' => 'conv.push(ajax.data);'
				);
			}
		}
		return false;
	}
	
	/**
	 * Method to store actually opened chat windows in the session
	 * 
	 * @param array $opt
	 */
	public function setSessionInfo($opt)
	{	
		if(isset($opt['infos']))
		{
			S::set('activechats', $opt['infos']);
		}
	}
	
	/**
	 * Method to remove open chatboxes from the session
	 *
	 * @param array $opt
	 */
	public function removeSessionInfo($opt)
	{
		if(isset($opt['infos']))
		{
			S::set('activechats', array());
		}
	}
	
	public function people()
	{
		session_write_close();
		$term = trim($_GET['term']);
		if($people = $this->model->findConnectedPeople($term))
		{
			echo json_encode($people);
			exit();
		}
		
		echo json_encode(array());
		exit();
	}
}