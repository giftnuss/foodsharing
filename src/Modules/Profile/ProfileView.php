<?php

namespace Foodsharing\Modules\Profile;

use Flourish\fDate;
use Foodsharing\Lib\View\vPage;
use Foodsharing\Modules\Core\View;

class ProfileView extends View
{
	private $foodsaver;

	public function profile($wallposts, bool $showEditButton = false, bool $showPassportGenerationHistoryButton = false, bool $showVerificationHistoryButton = false, bool $showSideInfoCompanies = false, $userCompanies = null, $userCompaniesCount = null, $fetchDates = null)
	{
		$page = new vPage($this->foodsaver['name'], $this->infos());
		$page->addSection($wallposts, 'Status-Updates von ' . $this->foodsaver['name']);

		if ($this->session->id() != $this->foodsaver['id']) {
			$this->func->addStyle('#wallposts .tools{display:none;}');
		}

		if ($fetchDates) {
			$page->addSection($this->fetchDates($fetchDates), 'Nächste Abholtermine');
		}

		$page->addSectionLeft($this->photo($showEditButton, $showPassportGenerationHistoryButton, $showVerificationHistoryButton));

		if ($this->foodsaver['stat_buddycount'] > 0 || $this->foodsaver['stat_fetchcount'] > 0 || $this->session->may('orga')) {
			$page->addSectionLeft($this->sideInfos(), 'Infos');
		}

		if ($showSideInfoCompanies && $userCompanies) {
			$page->addSectionLeft($this->sideInfosCompanies($userCompanies), 'Betriebe (' . $userCompaniesCount . ')');
		}
		$page->render();
	}

	private function fetchDates($fetchDates)
	{
		$out = '
				<div class="ui-padding" id="double">
				<a class="button button-big" href="#" onclick="ajreq(\'deleteFromSlot\',{app:\'profile\',fsid:' . $this->foodsaver['id'] . ',bid:0,date:0});return false;">Aus allen austragen</a>
					<ul class="datelist linklist" id="double">';
		foreach ($fetchDates as $d) {
			$userConfirmedForPickup = $d['confirmed'] == 1 ? '✓ ' : '? ';

			$out .= '
						<li>
							<a href="/?page=fsbetrieb&id=' . $d['betrieb_id'] . '" class="ui-corner-all">
								<span class="title">' . $userConfirmedForPickup . $this->func->niceDate($d['date_ts']) . '</span>
							</a>
						</li>
						<li>
							<a href="/?page=fsbetrieb&id=' . $d['betrieb_id'] . '" class="ui-corner-all">
								<span class="title">' . $d['betrieb_name'] . '</span>
							</a>
						</li>';

			if ($this->session->isOrgaTeam() || $this->session->isAdminFor($d['bezirk_id'])) {
				$out .= '<li>
							<a class="button button-big" href="#" onclick="ajreq(\'deleteFromSlot\',{app:\'profile\',fsid:' . $this->foodsaver['id'] . ',deleteAll:false,bid:' . $d['betrieb_id'] . ',date:' . $d['date_ts'] . '});return false;">austragen</a>
							</li>';
			} else {
				$out .= '<li>
							<a class="button button-big disabled" disabled=disabled href="#">austragen</a>
							</li>';
			}
		}
		$out .= '
					</ul>
				</div>';

		return $out;
	}

	private function sideInfosCompanies($userCompanies)
	{
		$out = '';
		foreach ($userCompanies as $b) {
			$userStatusOfStore = $b['active'] == 1 ? '✓ ' : '? ';
			$out .= '<p><a class="light" href="/?page=fsbetrieb&id=' . $b['id'] . '">' . $userStatusOfStore . $b['name'] . '</a></p>';
		}

		return '
		<div class="pure-g">
		    <div class="infos"> ' . $out . ' </div>
		</div>';
	}

	public function usernotes($notes, bool $showEditButton, bool $showPassportGenerationHistoryButton, bool $showVerificationHistoryButton, $userCompanies, $userCompaniesCount)
	{
		$page = new vPage($this->foodsaver['name'] . ' Notizen', $this->v_utils->v_info($this->func->s('user_notes_info')) . $notes);
		$page->setBread('Notizen');

		$page->addSectionLeft($this->photo($showEditButton, $showPassportGenerationHistoryButton, $showVerificationHistoryButton));
		$page->addSectionLeft($this->sideInfos(), 'Infos');

		if ($this->session->may('orga')) {
			$page->addSectionLeft($this->sideInfosCompanies($userCompanies), 'Betriebe (' . $userCompaniesCount . ')');
		}

		$page->render();
	}

	private function sideInfos()
	{
		$infos = array();

		if ($this->session->may('orga')) {
			$last_login = new fDate($this->foodsaver['last_login']);
			$registration_date = new fDate($this->foodsaver['anmeldedatum']);

			$infos[] = array(
				'name' => $this->func->s('last_login'),
				'val' => $last_login->format('d.m.Y')
			);
			$infos[] = array(
				'name' => $this->func->s('registration_date'),
				'val' => $registration_date->format('d.m.Y')
			);
			$infos[] = array(
				'name' => $this->func->s('private_mail'),
				'val' => '<a href="/?page=mailbox&mailto=' . urlencode($this->foodsaver['email']) . '">' . $this->foodsaver['email'] . '</a>'
			);
			if (isset($this->foodsaver['mailbox'])) {
				$infos[] = array(
					'name' => $this->func->s('mailbox'),
					'val' => '<a href="/?page=mailbox&mailto=' . urlencode($this->foodsaver['mailbox']) . '">' . $this->foodsaver['mailbox'] . '</a>'
				);
			}
		}

		if ($this->foodsaver['stat_buddycount'] > 0) {
			$infos[] = array(
				'name' => 'Bekannte',
				'val' => $this->foodsaver['name'] . (($this->foodsaver['stat_buddycount'] == 1) ? ' kennt ' : ' kennen ') . $this->foodsaver['stat_buddycount'] . ' Foodsaver'
			);
		}

		if ($this->foodsaver['stat_fetchcount'] > 0) {
			$infos[] = array(
				'name' => 'Abholquote',
				'val' => $this->foodsaver['stat_fetchrate'] . '<span style="white-space:nowrap">&thinsp;</span>%'
			);
		}

		$out = '';
		foreach ($infos as $key => $info) {
			$out .= '<p><strong>' . $info['name'] . '</strong><br />' . $info['val'] . '</p>';
		}

		return '
		<div class="pure-g">
		    <div class="infos"> ' . $out . ' </div>
		</div>';
	}

	public function infos()
	{
		$infos = array();

		if ($this->foodsaver['botschafter']) {
			$bot = array();
			foreach ($this->foodsaver['botschafter'] as $b) {
				$bot[$b['id']] = '<a class="light" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a>';
			}
			$infos[] = array(
				'name' => $this->func->sv('ambassador_districts', array('name' => $this->foodsaver['name'], 'gender' => $this->func->genderWord($this->foodsaver['geschlecht'], '', 'in', '_in'))),
				'val' => implode(', ', $bot)
			);
		}

		if ($this->foodsaver['foodsaver']) {
			$fsa = array();
			$fshomedistrict = array();
			foreach ($this->foodsaver['foodsaver'] as $b) {
				if ($b['id'] == $this->foodsaver['bezirk_id']) {
					$fshomedistrict[] = '<a class="light" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a>';
				}
				if (!isset($bot[$b['id']])) {
					$fsa[] = '<a class="light" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a>';
				}
			}
			if (!empty($fsa)) {
				$infos[] = array(
					'name' => $this->func->sv('foodsaver_districts', array('name' => $this->foodsaver['name'])),
					'val' => implode(', ', $fsa)
				);
			}
			if (!empty($fshomedistrict)) {
				$infos[] = array(
					'name' => $this->func->sv('foodsaver_home_district', array('name' => $this->foodsaver['name'])),
					'val' => implode(', ', $fshomedistrict)
				);
			}
		}

		if ($this->foodsaver['orga']) {
			$bot = array();
			foreach ($this->foodsaver['orga'] as $b) {
				if ($this->session->isOrgaTeam()) {
					$bot[$b['id']] = '<a class="light" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a>';
				} else {
					$bot[$b['id']] = $b['name'];
				}
			}
			$infos[] = array(
				'name' => $this->func->sv('foodsaver_workgroups', array('gender' => $this->func->genderWord($this->foodsaver['geschlecht'], 'Er', 'Sie', 'Er/Sie'))),
				'val' => implode(', ', $bot)
			);
		}

		if ($this->foodsaver['sleep_status'] == 1) {
			$infos[] = array(
				'name' => $this->func->sv('foodsaver_sleeping_hat_time', array('name' => $this->foodsaver['name'], 'datum_von' => date('d.m.Y', $this->foodsaver['sleep_from_ts']), 'datum_bis' => date('d.m.Y', $this->foodsaver['sleep_until_ts']))),
				'val' => $this->foodsaver['sleep_msg']
			);
		}

		if ($this->foodsaver['sleep_status'] == 2) {
			$infos[] = array(
				'name' => $this->func->sv('foodsaver_sleeping_hat_time_undefined', array('name' => $this->foodsaver['name'])),
				'val' => $this->foodsaver['sleep_msg']
			);
		}

		$out = '';
		foreach ($infos as $key => $info) {
			$out .= '<p><strong>' . $info['name'] . '</strong><br />' . $info['val'] . '</p>';
		}

		/*
		 * Statistics
		 */
		$fetchweight = '';
		if ($this->foodsaver['stat_fetchweight'] > 0) {
			$fetchweight = '
				<span class="item stat_fetchweight">
					<span class="val">' . number_format($this->foodsaver['stat_fetchweight'], 0, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>kg</span>
					<span class="name">gerettet</span>
				</span>';
		}

		$fetchcount = '';
		if ($this->foodsaver['stat_fetchcount'] > 0) {
			$fetchcount = '
				<span class="item stat_fetchcount">
					<span class="val">' . number_format($this->foodsaver['stat_fetchcount'], 0, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>x</span>
					<span class="name">abgeholt</span>
				</span>';
		}

		$postcount = '';
		if ($this->foodsaver['stat_postcount'] > 0) {
			$postcount = '
				<span class="item stat_postcount">
					<span class="val">' . number_format($this->foodsaver['stat_postcount'], 0, ',', '.') . '</span>
					<span class="name">Beiträge</span>
				</span>';
		}

		$bananacount = '';

		/*
		 * Banana
		*/
		if ($this->session->may('fs')) {
			$count_banana = count($this->foodsaver['bananen']);
			if ($count_banana == 0) {
				$count_banana = '&nbsp;';
			}

			$banana_button_class = ' bouched';
			$givebanana = '';

			if (!$this->foodsaver['bouched'] && ($this->foodsaver['id'] != $this->session->id())) {
				$banana_button_class = '';
				$givebanana = '
				<a onclick="$(this).hide().next().show().children(\'textarea\').autosize();return false;" href="#">Schenke ' . $this->foodsaver['name'] . ' eine Banane</a>
				<div class="vouch-banana-wrapper" style="display:none;">
					<div class="vouch-banana-desc">
						Hier kannst Du etwas dazu schreiben, warum Du ' . $this->foodsaver['name'] . ' gerne eine Banane schenken möchtest. Du kannst jedem Foodsaver nur eine Banane schenken!<br />
						Bitte gib die Vertrauensbanane nur an Foodsaver, die Du persönlich kennst und bei denen Du weißt, dass sie zuverlässig und engagiert sind und Du sicher bist, dass sie die Verhaltensregeln und die Rechtsvereinbarung einhalten.
						<p><strong>Vertrauensbananen können nicht zurückgenommen werden. Sei bitte deswegen besonders achtsam, wem Du eine schenkst.</strong></p>
						<a href="#" style="float:right;" onclick="ajreq(\'rate\',{app:\'profile\',type:2,id:' . (int)$this->foodsaver['id'] . ',message:$(\'#bouch-ta\').val()});return false;"><img src="/img/banana.png" /></a>
					</div>
					<textarea id="bouch-ta" class="textarea" placeholder="min. 100 Zeichen..." style="height:50px;"></textarea>
				</div>';
			}

			$this->func->addJs('
			$(".stat_bananacount").magnificPopup({
				type:"inline"
			});');
			$bananacount = '
			<a href="#bananas" onclick="return false;" class="item stat_bananacount' . $banana_button_class . '">
				<span class="val">' . $count_banana . '</span>
				<span class="name">&nbsp;</span>
			</a>
			';

			$bananacount .= '
			<div id="bananas" class="white-popup mfp-hide corner-all">
				<h3>' . str_replace('&nbsp;', '', $count_banana) . ' Vertrauensbananen</h3>
				' . $givebanana . '
				<table class="pintable">
					<tbody>';
			$odd = 'even';
			foreach ($this->foodsaver['bananen'] as $b) {
				if ($odd == 'even') {
					$odd = 'odd';
				} else {
					$odd = 'even';
				}
				$bananacount .= '
				<tr class="' . $odd . ' bpost">
					<td class="img"><a title="' . $b['name'] . '" href="/profile/' . $b['id'] . '"><img src="' . $this->func->img($b['photo']) . '"></a></td>
					<td><span class="msg">' . nl2br($b['msg']) . '</span>
					<div class="foot">
						<span class="time">' . $this->func->niceDate($b['time_ts']) . ' von ' . $b['name'] . '</span>
					</div></td>
				</tr>';
			}
			$bananacount .= '
					</tbody>
				</table>
			</div>';
		}

		return '
			<div class="pure-g">
				<div class="profile statdisplay">
					' . $fetchweight . '
					' . $fetchcount . '
					' . $postcount . '
					' . $bananacount . '
				</div>
			    <div class="infos"> ' . $out . ' </div>
			</div>';
	}

	public function getHistory($history, $changetype)
	{
		$out = '
			<ul class="linklist history">';
		$class = '';

		$curdate = 0;
		foreach ($history as $h) {
			if ($curdate != $h['date']) {
				if ($changetype == 0) {
					$typeofchange = '';
					if ($h['change_status'] == 0) {
						$class = 'unverify';
						$typeofchange = 'Entverifiziert';
					}
					if ($h['change_status'] == 1) {
						$class = 'verify';
						$typeofchange = 'Verifiziert';
					}
					$out .= '<li class="title"><span class="' . $class . '">' . $typeofchange . '</span> am ' . $this->func->niceDate($h['date_ts']) . ' durch:</li>';
				}
				if ($changetype == 1) {
					if (!is_null($h['bot_id'])) {
						$out .= '<li class="title">' . $this->func->niceDate($h['date_ts']) . ' durch:</li>';
					} else {
						$out .= '<li class="title">' . $this->func->niceDate($h['date_ts']) . '</li>';
					}
				}

				$curdate = $h['date'];
			}
			if (!is_null($h['bot_id'])) {
				$out .= '
				<li>
					<a class="corner-all" href="/profile/' . (int)$h['bot_id'] . '">
						<span class="n">' . $h['name'] . ' ' . $h['nachname'] . '</span>
						<span class="t"></span>
						<span class="c"></span>
					</a>
				</li>';
			} else {
				$out .= '
				<li>
					Es liegt keine Information &uuml;ber den Ersteller vor
				</li>';
			}
		}
		$out .= '
		</ul>';
		if ($curdate == 0) {
			$out = 'Es liegen keine Daten vor';
		}

		return $out;
	}

	private function photo(bool $showEditButton, bool $showPassportGenerationHistoryButton, bool $showVerificationHistoryButton)
	{
		$menu = $this->profileMenu($showEditButton, $showPassportGenerationHistoryButton, $showVerificationHistoryButton);

		$sleep_info = '';

		$online = '';

		if ($this->foodsaver['online']) {
			$online = '<div style="margin-top:10px;">' . $this->v_utils->v_info($this->foodsaver['name'] . ' ist online!', false, '<i class="fas fa-circle" style="color:#5ab946;"></i>') . '</div>';
		}

		return '<div style="text-align:center;">
					' . $this->func->avatar($this->foodsaver, 130) . $sleep_info . '
				</div>
				' . $online . '
				' . $menu;
	}

	private function profileMenu(bool $showEditButton, bool $showPassportGenerationHistoryButton, bool $showVerificationHistoryButton)
	{
		$opt = '';

		if ($showEditButton) {
			$opt .= '<li><a href="/?page=foodsaver&a=edit&id=' . $this->foodsaver['id'] . '"><i class="fas fa-pencil-alt fa-fw"></i>Profil bearbeiten</a></li>';
		}
		if ($this->foodsaver['buddy'] === -1 && $this->foodsaver['id'] != $this->session->id()) {
			$name = explode(' ', $this->foodsaver['name']);
			$name = $name[0];
			$opt .= '<li class="buddyRequest"><a onclick="ajreq(\'request\',{app:\'buddy\',id:' . (int)$this->foodsaver['id'] . '});return false;" href="#"><i class="fas fa-user fa-fw"></i>Ich kenne ' . $name . '</a></li>';
		}
		if ($showPassportGenerationHistoryButton) {
			$opt .= '<li><a href="#" onclick="ajreq(\'history\',{app:\'profile\',fsid:' . (int)$this->foodsaver['id'] . ',type:1});"><i class="fas fa-file-alt fa-fw"></i>Passhistorie</a></li>';
		}
		if ($showVerificationHistoryButton) {
			$opt .= '<li><a href="#" onclick="ajreq(\'history\',{app:\'profile\',fsid:' . (int)$this->foodsaver['id'] . ',type:0});"><i class="fas fa-file-alt fa-fw"></i>Verifizierungshistorie</a></li>';
		}

		if ($this->session->mayHandleReports()) {
			if (isset($this->foodsaver['note_count'])) {
				$opt .= '<li><a href="/profile/' . (int)$this->foodsaver['id'] . '/notes/"><i class="far fa-file-alt fa-fw"></i>' . $this->func->sv('notes_count', array('count' => $this->foodsaver['note_count'])) . '</a></li>';
			}
			if (isset($this->foodsaver['violation_count']) && $this->foodsaver['violation_count'] > 0) {
				$opt .= '<li><a href="/?page=report&sub=foodsaver&id=' . (int)$this->foodsaver['id'] . '"><i class="far fa-meh fa-fw"></i>' . $this->func->sv('violation_count', array('count' => $this->foodsaver['violation_count'])) . '</a></li>';
			}
		}

		return '
		<ul class="linklist">
			<li><a href="#" onclick="chat(' . $this->foodsaver['id'] . ');return false;"><i class="fas fa-comment fa-fw"></i>Nachricht schreiben</a></li>
			' . $opt . '
			<li><a href="#" onclick="ajreq(\'reportDialog\',{app:\'report\',fsid:' . (int)$this->foodsaver['id'] . '});return false;"><i class="far fa-life-ring fa-fw"></i>Regelverletzung melden</a></li>
		</ul>';
	}

	public function setData($data)
	{
		$this->foodsaver = $data;
	}

	public function xv_set($rows, $title = false)
	{
		if (!$title) {
			$title = '';
		} else {
			$title = '<h3>' . $title . '</h3>';
		}
		$out = '
	<div id="' . $this->func->id($title) . '" class="xv_set">
		' . $title;
		foreach ($rows as $r) {
			$out .= '
		<div class="xv_row">
			<span class="xv_label">' . $r['name'] . '</span><span class="xv_val">' . $r['val'] . '</span>
		</div>';
		}

		return $out . '
	</div>';
	}
}
