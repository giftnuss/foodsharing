<?php
if(!S::may())
{
	goLogin();
}
if(!isset($_GET['bid']))
{
	$bezirk_id = getBezirkId();
}
else
{
	$bezirk_id = (int)$_GET['bid'];
}

if(getAction('neu'))
{
	handle_add();
	$title = 'Nachricht schreiben';
	
	$back = false;
	
	if(isset($_GET['list']))
	{
		if(!empty($_GET['list']))
		{
			$list = explode(',', $_GET['list']);
			$tmp = array();
			foreach ($list as $l)
			{
				if((int)$l > 0)
				{
					$tmp[(int)$l] = (int)$l;
				}
			}
			
			if((int)$_GET['list'] > 0)
			{
				if($betrieb = $db->getBetrieb($_GET['list']))
				{
					global $g_data;
					$g_data['recip'] = $db->getBetriebTeam($_GET['list']);
					$title = 'Nachricht ans Team von '.$betrieb['name'];
					$back = array(
						'name' => s('back_to_betrieb'),
						'href' => '?page=fsbetrieb&id='.(int)$_GET['list']
					);
				}
				
			}
		}
		elseif(isBotFor($bezirk_id) || ($bezirk_id == getBezirkId()) || isOrgateam())
		{
			global $g_data;
			$g_data['recip'] = $db->getFsBasicsReq($bezirk_id);
			
			$bezirk = $db->getBezirk($bezirk_id);
			$title = 'Nachricht an alle Foodsaver aus '.$bezirk['name'];
			$back = array(
					'name' => s('back_to_bezirk'),
					'href' => '?page=bezirk&id='.(int)$bezirk_id
			);
		}
	}
	elseif (isset($_GET['slist']))
	{
		if($betrieb = $db->getBetrieb($_GET['slist']))
		{
			global $g_data;
			$g_data['recip'] = $db->getBetriebSpringer($_GET['slist']);
			$title = 'Nachricht an die Springer von '.$betrieb['name'];
			$back = array(
					'name' => s('back_to_betrieb'),
					'href' => '?page=fsbetrieb&id='.(int)$_GET['slist']
			);
		}
	}
	
	if($back === false)
	{
		addContent(v_field(v_menu(array(
		pageLink('message','back_to_overview')
		)),s('actions')),CNT_RIGHT);
	}
	else
	{
		addContent(v_menu(array($back),s('actions')),CNT_RIGHT);
	}
	
	addBread(s('bread_message'),'?page=message');
	addBread(s('bread_new_message'));
			
	addContent(message_form($title));

	
}
elseif($id = getActionId('delete'))
{
	if($db->del_message($id))
	{
		info(s('message_deleted'));
		goPage();
	}
}
elseif($id = getActionId('edit'))
{
	go('?page=message&a=neu');
	handle_edit();
	
	addBread(s('bread_message'),'?page=message');
	addBread(s('bread_edit_message'));
	
	$data = $db->getOne_message($id);
	setEditData($data);
			
	addContent(message_form());
			
	addContent(v_field(v_menu(array(
		pageLink('message','back_to_overview')
	)),s('actions')),CNT_RIGHT);
}
else
{
	addBread(s('message_bread'),'?page=message');
	
	if($data = $db->getConversations())
	{
		
		addJs
		('
			$("#messagelist li a").click(function(){
				chat(parseInt($(this).attr("href").replace("#","")));
			});
		');
		addContent(v_field(v_messageList($data),'Aktuelle Unterhaltungen'));
		
	}
	else
	{
		info(s('message_empty'));		
	}
			
	addContent(v_field(v_menu(array(
		array('href' => '?page=message&a=neu','name' => s('neu_message'))
	)),'Aktionen'),CNT_RIGHT);
	
	if(isset($_GET['conv']) && (int)$_GET['conv'] > 0)
	{
		addJs('chat('.(int)$_GET['conv'].');');
	}
	
}					
function message_form($title = 'Nachricht')
{
	global $db;
		
	return v_quickform($title,array(
		
		
	
		//v_form_select('sender_id'),
		//v_form_select('recip_id'),
		//v_form_select('unread'),
		
			
		v_form_tagselect('recip',array('required'=>true,'values'=> array('id'=>3))),
		//v_form_text('name'),
		v_form_textarea('msg',array('required'=>true)),
		//v_form_text('time'),
		//v_form_file('attach')	
	));
}

function handle_edit()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		if($db->update_message($_GET['id'],$g_data))
		{
			info(s('message_edit_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
function handle_add()
{
	if(fsid() == 3674)
	{
		info(s('message_send_success'));
                goPage();
	}
	global $db;
	global $g_data;
	if(submitted())
	{
		if($attach = handleAttach('attach'))
		{
			$g_data['attach'] = json_encode($attach);
		}
		else
		{
			$g_data['attach'] = '';
		}
		
		/*
		 * [recip] => Array
        (
            [3-a] => Peter Ruiz Neumann
            [38-a] => Raphael Fellmer
            [244-a] => Peter Busch
        )
		 */
		
		$g_data['name'] = substr($g_data['msg'], 0,50).' ...';
		handleTagselect('recip');
		
		foreach($g_data['recip'] as $r)
		{
			$db->addMessage(fsId(),$r,$g_data['name'],$g_data['msg'],$g_data['attach']);
		}

		info(s('message_send_success'));
		goPage();

	}
}
				
?>
