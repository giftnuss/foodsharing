<?php

namespace Foodsharing\Modules\Profile;

use Carbon\Carbon;
use Foodsharing\Lib\View\vPage;
use Foodsharing\Modules\Core\View;

class ProfileView extends View
{
	private $foodsaver;

	public function profile($wallPosts, bool $showEditButton = false, bool $showPassportGenerationHistoryButton = false, bool $showVerificationHistoryButton = false, bool $showSideInfoCompanies = false, $userCompanies = null, $userCompaniesCount = null, $fetchDates = null)
	{
		$page = new vPage($this->foodsaver['name'], $this->infos());
		$page->addSection($wallPosts, 'Status-Updates von ' . $this->foodsaver['name']);

		if ($this->session->id() != $this->foodsaver['id']) {
			$this->pageHelper->addStyle('#wallposts .tools{display:none;}');
		}

		if ($fetchDates) { // AMB functionality
			$page->addSection($this->fetchDates($fetchDates), 'Nächste Abholtermine');
		}

		$page->addSectionLeft($this->photo($showEditButton, $showPassportGenerationHistoryButton, $showVerificationHistoryButton));

		if ($this->foodsaver['stat_buddycount'] > 0 || $this->foodsaver['stat_fetchcount'] > 0 || $this->session->may('orga')) {
			$page->addSectionLeft($this->sideInfos(), 'Infos');
		}

		if ($showSideInfoCompanies && $userCompanies) { // AMB functionality
			$page->addSectionLeft($this->sideInfosCompanies($userCompanies), 'Betriebe (' . $userCompaniesCount . ')');
		}
		$page->render();
	}

	private function fetchDates($fetchDates) // AMB functionality
	{
		$out = '
				<div class="ui-padding" id="double">';

		if ($this->session->isOrgaTeam()) {
			$out .= '<a class="button button-big" href="#" onclick="ajreq(\'deleteAllDatesFromFoodsaver\',{app:\'profile\',fsid:' . $this->foodsaver['id'] . '});return false;">' . $this->translationHelper->s('cancel_all') . '</a>';
		}

		$out .= '<ul class="datelist linklist" id="double">';
		foreach ($fetchDates as $d) {
			$userConfirmedForPickup = $d['confirmed'] == 1 ? '✓&nbsp;' : '?&nbsp;';

			$out .= '
						<li>
							<a href="/?page=fsbetrieb&id=' . $d['betrieb_id'] . '" class="ui-corner-all">
								<span class="title">' . $userConfirmedForPickup . $this->timeHelper->niceDate($d['date_ts']) . '</span>
							</a>
						</li>
						<li>
							<a href="/?page=fsbetrieb&id=' . $d['betrieb_id'] . '" class="ui-corner-all">
								<span class="title">' . $d['betrieb_name'] . '</span>
							</a>
						</li>';

			if ($this->session->isOrgaTeam() || $this->session->isAdminFor($d['bezirk_id'])) {
				$out .= '<li>
							<a class="button button-big" href="#" onclick="ajreq(\'deleteSinglePickup\',{app:\'profile\',fsid:' . $this->foodsaver['id'] . ',storeId:' . $d['betrieb_id'] . ',date:' . $d['date_ts'] . '});return false;">austragen</a>
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

	/**
	 * Create HTML for list of stores on the profile.
	 * Each store has a symbol in front indicating if the user is
	 *  - waiting for approval (a question mark)
	 *  - in store (the shopping basket used for stores)
	 *  - Springer = waiting list (a coffee mug).
	 *
	 * @param array $userCompanies
	 *
	 * @return string: HTML with the list
	 */
	private function sideInfosCompanies(array $userCompanies): string
	{
		$out = '';
		foreach ($userCompanies as $b) {
			switch ($b['active']) {
				case 0:  // asked to be in store team
					$userStatusOfStore = '<i class="far fa-question-circle fw"></i> ';
					break;
				case 1: // in store team
					$userStatusOfStore = '<i class="fas fa-shopping-cart fw"></i> ';
					break;
				case 2: // Springer (waiting list)
					$userStatusOfStore = '<i class="fas fa-mug-hot fw"></i> ';
					break;
				default: // should not happen
					$userStatusOfStore = '';
					break;
			}
			$out .= '<p><a class="light" href="/?page=fsbetrieb&id=' . $b['id'] . '">' . $userStatusOfStore . $b['name'] . '</a></p>';
		}

		return '
		<div class="pure-g">
		    <div class="infos"> ' . $out . ' </div>
		</div>';
	}

	public function userNotes($notes, bool $showEditButton, bool $showPassportGenerationHistoryButton, bool $showVerificationHistoryButton, $userCompanies, $userCompaniesCount)
	{
		$page = new vPage($this->foodsaver['name'] . ' Notizen', $this->v_utils->v_info($this->translationHelper->s('user_notes_info')) . $notes);
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
			$last_login = (
				$this->foodsaver['last_login']
				? Carbon::parse($this->foodsaver['last_login'])->format('d.m.Y')
				: $this->translationHelper->s('Never')
			);
			$registration_date = Carbon::parse($this->foodsaver['anmeldedatum']);

			$infos[] = array(
				'name' => $this->translationHelper->s('last_login'),
				'val' => $last_login
			);
			$infos[] = array(
				'name' => $this->translationHelper->s('registration_date'),
				'val' => $registration_date->format('d.m.Y')
			);
			$infos[] = array(
				'name' => $this->translationHelper->s('private_mail'),
				'val' => '<a href="/?page=mailbox&mailto=' . urlencode($this->foodsaver['email']) . '">' . $this->foodsaver['email'] . '</a>'
			);
			if (isset($this->foodsaver['mailbox'])) {
				$infos[] = array(
					'name' => $this->translationHelper->s('mailbox'),
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
			$ambassador = array();
			foreach ($this->foodsaver['botschafter'] as $b) {
				$ambassador[$b['id']] = '<a class="light" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a>';
			}
			$infos[] = array(
				'name' => $this->translationHelper->sv('ambassador_districts', array('name' => $this->foodsaver['name'], 'gender' => $this->translationHelper->genderWord($this->foodsaver['geschlecht'], '', 'in', '_in'))),
				'val' => implode(', ', $ambassador)
			);
		}

		if ($this->foodsaver['foodsaver']) {
			$fsa = array();
			$fsHomeDistrict = array();
			foreach ($this->foodsaver['foodsaver'] as $b) {
				if ($b['id'] == $this->foodsaver['bezirk_id']) {
					$fsHomeDistrict[] = '<a class="light" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a>';
				}
				if (!isset($ambassador[$b['id']])) {
					$fsa[] = '<a class="light" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a>';
				}
			}
			if (!empty($fsa)) {
				$infos[] = array(
					'name' => $this->translationHelper->sv('foodsaver_districts', array('name' => $this->foodsaver['name'])),
					'val' => implode(', ', $fsa)
				);
			}
			if (!empty($fsHomeDistrict)) {
				$infos[] = array(
					'name' => $this->translationHelper->sv('foodsaver_home_district', array('name' => $this->foodsaver['name'])),
					'val' => implode(', ', $fsHomeDistrict)
				);
			}
		}

		if ($this->foodsaver['orga']) {
			$ambassador = array();
			foreach ($this->foodsaver['orga'] as $b) {
				if ($this->session->isOrgaTeam()) {
					$ambassador[$b['id']] = '<a class="light" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a>';
				} else {
					$ambassador[$b['id']] = $b['name'];
				}
			}
			$infos[] = array(
				'name' => $this->translationHelper->sv('foodsaver_workgroups', array('gender' => $this->translationHelper->genderWord($this->foodsaver['geschlecht'], 'Er', 'Sie', 'Er/Sie'))),
				'val' => implode(', ', $ambassador)
			);
		}

		if ($this->foodsaver['sleep_status'] == 1) {
			$infos[] = array(
				'name' => $this->translationHelper->sv('foodsaver_sleeping_hat_time', array('name' => $this->foodsaver['name'], 'datum_von' => date('d.m.Y', $this->foodsaver['sleep_from_ts']), 'datum_bis' => date('d.m.Y', $this->foodsaver['sleep_until_ts']))),
				'val' => $this->foodsaver['sleep_msg']
			);
		}

		if ($this->foodsaver['sleep_status'] == 2) {
			$infos[] = array(
				'name' => $this->translationHelper->sv('foodsaver_sleeping_hat_time_undefined', array('name' => $this->foodsaver['name'])),
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
		$fetchWeight = '';
		if ($this->foodsaver['stat_fetchweight'] > 0) {
			$fetchWeight = '
				<span class="item stat_fetchweight">
					<span class="val">' . number_format($this->foodsaver['stat_fetchweight'], 0, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>kg</span>
					<span class="name">gerettet</span>
				</span>';
		}

		$fetchCount = '';
		if ($this->foodsaver['stat_fetchcount'] > 0) {
			$fetchCount = '
				<span class="item stat_fetchcount">
					<span class="val">' . number_format($this->foodsaver['stat_fetchcount'], 0, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>x</span>
					<span class="name">abgeholt</span>
				</span>';
		}

		$foodBasketCount = '
				<a href="/essenskoerbe">
				    <span class="item stat_basketcount">
					    <span class="val">' . number_format($this->foodsaver['basketCount'], 0, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>x</span>
					    <span class="name">Essenskörbe</span>
				    </span>
				</a>';

		if ($this->session->may('fs')) { // for foodsavers only
			$postCount = '
				<span class="item stat_postcount">
					<span class="val">' . number_format($this->foodsaver['stat_postcount'], 0, ',', '.') . '</span>
					<span class="name">Beiträge</span>
				</span>';
		} else {
			$postCount = '';
		}

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

			$this->pageHelper->addJs('
			$(".stat_bananacount").magnificPopup({
				type:"inline"
			});');
			$bananaCount = '
			<a href="#bananas" onclick="return false;" class="item stat_bananacount' . $banana_button_class . '">
				<span class="val">' . $count_banana . '</span>
				<span class="name">&nbsp;</span>
			</a>
			';

			$bananaCount .= '
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
				$bananaCount .= '
				<tr class="' . $odd . ' bpost">
					<td class="img"><a title="' . $b['name'] . '" href="/profile/' . $b['id'] . '"><img src="' . $this->imageService->img($b['photo']) . '"></a></td>
					<td><span class="msg">' . nl2br($b['msg']) . '</span>
					<div class="foot">
						<span class="time">' . $this->timeHelper->niceDate($b['time_ts']) . ' von ' . $b['name'] . '</span>
					</div></td>
				</tr>';
			}
			$bananaCount .= '
					</tbody>
				</table>
			</div>';
		} else {
			$bananaCount = '';
		}

		return '
			<div class="pure-g">
				<div class="profile statdisplay">
					' . $fetchWeight . '
					' . $fetchCount . '
					' . $postCount . '
					' . $foodBasketCount . '
					' . $bananaCount . '
				</div>
			    <div class="infos"> ' . $out . ' </div>
			</div>';
	}

	public function getHistory($history, $changeType)
	{
		$out = '
			<ul class="linklist history">';
		$class = '';

		$curDate = 0;
		foreach ($history as $h) {
			if ($curDate != $h['date']) {
				if ($changeType == 0) {
					$typeOfChange = '';
					if ($h['change_status'] == 0) {
						$class = 'unverify';
						$typeOfChange = 'Entverifiziert';
					}
					if ($h['change_status'] == 1) {
						$class = 'verify';
						$typeOfChange = 'Verifiziert';
					}
					$out .= '<li class="title"><span class="' . $class . '">' . $typeOfChange . '</span> am ' . $this->timeHelper->niceDate($h['date_ts']) . ' durch:</li>';
				}
				if ($changeType == 1) {
					if (!is_null($h['bot_id'])) {
						$out .= '<li class="title">' . $this->timeHelper->niceDate($h['date_ts']) . ' durch:</li>';
					} else {
						$out .= '<li class="title">' . $this->timeHelper->niceDate($h['date_ts']) . '</li>';
					}
				}

				$curDate = $h['date'];
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
		if ($curDate == 0) {
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
					' . $this->imageService->avatar($this->foodsaver, 130) . $sleep_info . '
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
				$opt .= '<li><a href="/profile/' . (int)$this->foodsaver['id'] . '/notes/"><i class="far fa-file-alt fa-fw"></i>' . $this->translationHelper->sv('notes_count', array('count' => $this->foodsaver['note_count'])) . '</a></li>';
			}
			if (isset($this->foodsaver['violation_count']) && $this->foodsaver['violation_count'] > 0) {
				$opt .= '<li><a href="/?page=report&sub=foodsaver&id=' . (int)$this->foodsaver['id'] . '"><i class="far fa-meh fa-fw"></i>' . $this->translationHelper->sv('violation_count', array('count' => $this->foodsaver['violation_count'])) . '</a></li>';
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
	<div id="' . $this->identificationHelper->id($title) . '" class="xv_set">
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
