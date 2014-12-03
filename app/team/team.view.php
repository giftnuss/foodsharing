<?php
class TeamView extends View
{
	
	public function user($user)
	{
		$subtitle = '';
		if($user['groups'])
		{
			foreach ($user['groups'] as $g)
			{
				//$subtitle .= ', ' . '<a href="/arbeitsgruppe/' . $g['id'] . '">'.$g['name'].'</a>';
				$subtitle .= ', ' . $g['name'];
			}
			
			$subtitle = '<p class="subtitle">'.substr($subtitle,2).'</p>';
		}
		
		$socials = '';
		
		if($user['homepage'] != '')
		{
			$socials .= '<li><a title="Homepage" href="' . $user['homepage'] . '" target="_blank"><i class="fa fa-globe"></i></a></li>';
		}
		
		if($user['twitter'] != '')
		{
			$socials .= '<li><a title="twitter" href="' . $user['twitter'] . '" target="_blank"><i class="fa fa-twitter"></i></a></li>';
		}
		
		if($user['github'] != '')
		{
			$socials .= '<li><a title="github" href="' . $user['github'] . '" target="_blank"><i class="fa fa-github"></i></a></li>';
		}
		
		if($user['tox'] != '')
		{
			$socials .= '<li><a title="tox: sichere skype alternative" href="#" onclick="u_tox(\'' . $user['tox'] . '\');return false;" target="_blank"><i class="fa fa-lock"></i></a></li>';
		}
		
		if(!empty($socials))
		{
			$socials = '
			<ul id="team-socials">
				'.$socials.'
			</ul>';
		}
		
		$out = '
				
		<div id="team-user" class="corner-all">
			<span class="img" style="background-image:url(/images/'.$user['photo'].');"></span>
			<h1>'.$user['name'].'</h1>
			<small>'.$user['position'].'</small>
			'.$subtitle.'
			<p>'.nl2br($user['desc']).'</p>
					
			<span class="foot corner-bottom">
				'.$socials.'					
			</span>
		</div>';
		return $out;
	}
	
	public function contactForm($user)
	{
		return v_quickform('schreibe ' . $user['name'] . ' eine E-Mail', array(
			v_form_text('name'),
			v_form_text('email'),
			v_form_textarea('message'),
			v_form_hidden('id', (int)$user['id'])
		),array('id' => 'contactform'));
	}
	
	public function teamlist($team)
	{

		$out = '
		<ul id="teamlist" class="linklist">';
		
		foreach ($team as $t)
		{
			$socials = '<i class="fa fa-envelope"></i>';
			
			if($t['homepage'] != '')
			{
				$socials .= '<i class="fa fa-globe"><span>' . $t['homepage'] . '</span></i>';
			}
			
			if($t['twitter'] != '')
			{
				$socials .= '<i class="fa fa-twitter"><span>' . $t['twitter'] . '</span></i>';
			}
			
			if($t['github'] != '')
			{
				$socials .= '<i class="fa fa-github"><span>'.$t['github'].'</span></i>';
			}
			
			if($t['tox'] != '')
			{
				$socials .= '<i class="fa fa-lock"><span>'.$t['tox'].'</span></i>';
			}
			
			$out .= '
			<li>
				<a id="t-'.$t['id'].'" href="/team/'.$t['id'].'" class="corner-all" target="_blank">
					<span class="img" style="background-image:url(/images/q_'.$t['photo'].');"></span>
					<h3>'.$t['name'].'</h3>
					<span class="subtitle">'.$t['position'].'</span>
					<span class="desc">
						'.tt($t['desc'],80).'
					</span>
					<span class="foot corner-bottom">
						'.$socials.'
						
						
								
					</span>
				</a>
			</li>';
		}
	
		$out .= '
		</ul>';
		
		return $out;
	}
}