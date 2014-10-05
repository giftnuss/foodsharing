<?php
class MsgView extends View
{
	public function top()
	{
		return '
		<div class="welcome ui-padding margin-bottom ui-corner-all">
	
			<div class="welcome_profile_image">
				<a onclick="profile(56);return false;" href="#">
					<img width="50" height="50" src="img/message.png" alt="'.s('messages').'" class="image_online">
				</a>
			</div>
			<div class="welcome_profile_name">
				<div class="user_display_name">
					'.s('your_messages').'
				</div>
				<div class="welcome_quick_link">
					
					<div class="clear"></div>
				</div>
			</div>
			<div class="welcome_profile_survived v-desktop">
				<a class="button" href="#">'.s('new_message').'</a>
			</div>
		
			<div class="clear"></div>
		</div>';
	}
	
	public function leftMenu()
	{
		return $this->menu(array(
			array('name' => s('new_message'),'click' => 'msg.compose();return false;')
		));
	}
	
	public function compose()
	{
		$content = $this->peopleChooser('compose_recipients');
		
		$content .= v_form_textarea('compose_body');
		
		$content .= v_input_wrapper(false, '<a class="button" id="compose_submit" href="#">'.s('send').'</a>');
		
		return '<div id="compose">'.v_field($content, s('new_message'),array('class' => 'ui-padding')).'</div>';
	}
	
	public function conversationList($conversations)
	{
		$list = '';
		
		if(!empty($conversations))
		{
			foreach ($conversations as $c)
			{
				$pics = '';
				$names = '';
				if(!empty($c['member']))
				{
					$picwidth = 50;
					$size = 'med';
					
					if(count($c['member']) > 2)
					{
						$picwidth = 25;
						$size = 'mini';
						shuffle($c['member']);
					}
					
					foreach($c['member'] as $m)
					{
						if($m['id'] == fsId())
						{
							continue;
						}
						$pics .= '<img src="'.img($m['photo'],$size).'" width="'.$picwidth.'" />';
						$names .= ', '.$m['name'];
					}
					$names = substr($names, 2);
					$list .= '<li id="convlist-'.$c['id'].'"><a href="#" onclick="msg.loadConversation('.$c['id'].');return false;"><span class="pics">'.$pics.'</span><span class="names">'.$names.'</span><span class="msg">'.$c['last_message'].'</span><span class="time">'.niceDate($c['last_ts']).'</span><span class="clear"></span></a></li>';
				}
			}
		}
		else
		{
			$list = '<li class="noconv">'.v_info(s('no_conversations')).'</li>';
		}
		
		return v_field('<div id="conversation-list"><ul class="linklist">'.$list.'</ul></div>', '<i class="fa fa-comments"></i> '.s('conversations'));
	}
	
	public function conversation()
	{
		$out = '
			<div id="msg-conversation" class="corner-all"><ul></ul><div class="loader" style="display:none;"><i class="fa fa-refresh fa-spin"></i></div></div>
		';
		
		$out .= '
			<div id="msg-control">
				<form>
					'.v_form_textarea('msg_answer',array('nolabel'=>true,'placeholder' => s('write_something'))).'	
					<p><input type="submit" class="button" name="submit" value="'.s('submit').'" /></p>
				</form>
			</div>';
		
		return '<div id="msg-conversation-wrapper" style="display:none;">'.v_field($out, '<span id="msg-conversation-title"><i class="fa fa-comment"></i></span>',array('class' => 'ui-padding')).'</div>';
	}
}