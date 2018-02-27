<?php

namespace Foodsharing\Modules\Team;

use Foodsharing\Modules\Core\View;

class TeamView extends View
{
	public function user($user): string
	{
		$socials = '';

		if ($user['homepage'] != '') {
			$socials .= '<li><a title="Homepage" href="' . $user['homepage'] . '" target="_blank"><i class="fa fa-globe"></i></a></li>';
		}

		if ($user['twitter'] != '') {
			$socials .= '<li><a title="twitter" href="' . $user['twitter'] . '" target="_blank"><i class="fa fa-twitter"></i></a></li>';
		}

		if ($user['github'] != '') {
			$socials .= '<li><a title="github" href="' . $user['github'] . '" target="_blank"><i class="fa fa-github"></i></a></li>';
		}

		if ($user['tox'] !== '') {
			$socials .= '<li><a title="tox: sichere skype alternative" href="#" onclick="u_tox(\'' . $user['id'] . '\');return false;" target="_blank"><i class="fa fa-lock"></i></a></li>';
		}

		if (!empty($socials)) {
			$socials = '
			<ul id="team-socials">
				' . $socials . '
			</ul>';
		}

		$out = '
				
		<div id="team-user" class="corner-all">
			<span class="img" style="background-image:url(/images/' . $user['photo'] . ');"></span>
			<h1>' . $user['name'] . '</h1>
			<small>' . $user['position'] . '</small>
			<p>' . nl2br($user['desc']) . '</p>
					
			<span class="foot corner-bottom">
				' . $socials . '					
			</span>
		</div>' . $this->toxPopTpl($user);

		return $out;
	}

	private function toxPopTpl($user): string
	{
		return '
		<a style="display:none;overflow:hidden:width:1px;height:1px;" id="tox-pop-' . $user['id'] . '-opener" href="#tox-pop-' . $user['id'] . '">&nbsp;</a>
		<div id="tox-pop-' . $user['id'] . '" class="white-popup mfp-hide tox-popup corner-all">
			<div style="text-align:center">
				<a href="https://tox.im/de" target="_blank"><img src="http://tox.im/assets/imgs/logo_head.png" /></a>
				' . $this->v_utils->v_info('Tox ist eine sichere Open Source-Alternative zu Skype oder WhatsApp. Mit der Tox-ID kannst Du ' . $user['name'] . ' zu Deiner Kontaktliste hinzufügen.') . '
				<h3>Tox-ID von ' . $user['name'] . '</h3>
				<input type="text" class="tox-id" value="' . $user['tox'] . '" />
				<div class="tox-qr"></div>
			</div>
		</div>';
	}

	public function contactForm($user): string
	{
		return $this->v_utils->v_quickform('Schreibe ' . $user['name'] . ' eine E-Mail!', array(
			$this->v_utils->v_form_text('name'),
			$this->v_utils->v_form_text('email'),
			$this->v_utils->v_form_textarea('message'),
			$this->v_utils->v_form_hidden('id', (int)$user['id'])
		), array('id' => 'contactform'));
	}

	public function teamList($team, $header): string
	{
		foreach ($team as $key => $t) {
			if (isset($firsts[(int)$t['id']])) {
				unset($team[$key]);
				array_unshift($team, $t);
			}
		}

		$out = '
		<ul id="teamList" class="linklist">';

		foreach ($team as $t) {
			$socials = '&nbsp;';
			if ($t['homepage'] != '') {
				$socials .= '<i class="fa fa-globe"><span>' . $t['homepage'] . '</span></i>';
			}

			if ($t['twitter'] != '') {
				$socials .= '<i class="fa fa-twitter"><span>' . $t['twitter'] . '</span></i>';
			}

			if ($t['github'] != '') {
				$socials .= '<i class="fa fa-github"><span>' . $t['github'] . '</span></i>';
			}

			if ($t['tox'] != '') {
				$socials .= '<i class="fa fa-lock"><span>' . $t['id'] . '</span></i>';
			}

			$out .= '
			<li>
				<a id="t-' . $t['id'] . '" href="/team/' . $t['id'] . '" class="corner-all" target="_self">
					<span class="img" style="background-image:url(/images/q_' . $t['photo'] . ');"></span>
					<h3>' . $t['name'] . ' ' . $t['nachname'] . '</h3>
					<span class="subtitle">' . $t['position'] . '</span>
					<span class="desc">
						' . $this->func->tt($t['desc'], 240) . '
					</span>
					<span class="foot corner-bottom">
						' . $socials . '	
					</span>
				</a>
				' . $this->toxPopTpl($t) . '
			</li>';
		}

		$out .= '
		</ul>';

		return $header['body'] . $out;
	}
}
