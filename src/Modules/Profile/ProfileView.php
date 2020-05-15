<?php

namespace Foodsharing\Modules\Profile;

use Carbon\Carbon;
use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Helpers\PageHelper;
use Foodsharing\Helpers\RouteHelper;
use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Lib\View\vPage;
use Foodsharing\Modules\Core\DBConstants\Buddy\BuddyId;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;
use Foodsharing\Modules\Core\View;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Services\ImageService;
use Foodsharing\Services\SanitizerService;
use Symfony\Component\Translation\TranslatorInterface;

class ProfileView extends View
{
	private $foodsaver;
	private $profilePermissions;
	private $reportPermissions;

	public function __construct(
		\Twig\Environment $twig,
		Utils $viewUtils,
		Session $session,
		SanitizerService $sanitizerService,
		PageHelper $pageHelper,
		TimeHelper $timeHelper,
		ImageService $imageService,
		RouteHelper $routeHelper,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		TranslationHelper $translationHelper,
		ProfilePermissions $profilePermissions,
		ReportPermissions $reportPermissions,
		TranslatorInterface $translator
	) {
		parent::__construct($twig, $viewUtils, $session, $sanitizerService, $pageHelper, $timeHelper, $imageService,
			$routeHelper, $identificationHelper, $dataHelper, $translationHelper, $translator);

		$this->profilePermissions = $profilePermissions;
		$this->reportPermissions = $reportPermissions;
	}

	public function profile(
		string $wallPosts,
		bool $profileVisitorMayAdminThisFoodsharer,
		bool $profileVisitorMaySeeHistory,
		array $userCompanies = [],
		array $fetchDates = []
	): void {
		$page = new vPage($this->foodsaver['name'], $this->infos());
		$page->addSection($wallPosts, 'Status-Updates von ' . $this->foodsaver['name']);

		if ($this->session->id() != $this->foodsaver['id']) {
			$this->pageHelper->addStyle('#wallposts .tools{display:none;}');
		}

		if ($fetchDates) {
			$page->addSection($this->fetchDates($fetchDates), 'Nächste Abholtermine');
		}

		$page->addSectionLeft(
			$this->photo($profileVisitorMayAdminThisFoodsharer, $profileVisitorMaySeeHistory)
		);

		$page->addSectionLeft($this->sideInfos(), 'Infos');

		if ($profileVisitorMayAdminThisFoodsharer && $userCompanies) { // AMB functionality
			$page->addSectionLeft($this->sideInfosCompanies($userCompanies), 'Betriebe (' . count($userCompanies) . ')');
		}
		$page->render();
	}

	private function infos(): string
	{
		/*
		* Information
		 */
		$out = $this->renderInformation();

		/*
		 * Statistics
		 */
		[$fetchWeight, $fetchCount, $foodBasketCount, $postCount] = $this->renderStatistics();

		/*
		 * Bananas
		*/
		$bananaCount = $this->renderBananas();

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

	private function fetchDates(array $fetchDates): string
	{
		$out = '<div class="ui-padding" id="double">';

		if ($this->session->isOrgaTeam()) {
			$out .= '<a class="button button-big" href="#" onclick="ajreq(\'deleteAllDatesFromFoodsaver\',{app:\'profile\',fsid:' . $this->foodsaver['id'] . '});return false;">' . $this->translationHelper->s(
					'cancel_all'
				) . '</a>';
		}

		$out .= '<ul class="datelist linklist" id="double">';
		foreach ($fetchDates as $date) {
			$userConfirmedForPickup = $date['confirmed'] == 1 ? '✓&nbsp;' : '?&nbsp;';

			$out .= '<li>
						<a href="/?page=fsbetrieb&id=' . $date['betrieb_id'] . '" class="ui-corner-all">
							<span class="title">' . $userConfirmedForPickup . $this->timeHelper->niceDate($date['date_ts']) . '</span>
						</a>
					</li>
					<li>
						<a href="/?page=fsbetrieb&id=' . $date['betrieb_id'] . '" class="ui-corner-all">
							<span class="title">' . $date['betrieb_name'] . '</span>
						</a>
					</li>';

			if ($this->session->isAdminFor($date['bezirk_id']) || $this->session->isOrgaTeam()) {
				$out .= '<li>
							<a class="button button-big" href="#" onclick="ajreq(\'deleteSinglePickup\',{app:\'profile\',fsid:' . $this->foodsaver['id'] . ',storeId:' . $date['betrieb_id'] . ',date:' . $date['date_ts'] . '});return false;">austragen</a>
						</li>';
			} elseif ($this->session->may('fs')) {
				$out .= '<li>
							<a class="button button-big disabled" hidden=hidden href="#">austragen</a>
							</li>';
			} else {
				$out .= '<li>
							<a class="button button-big disabled" disabled=disabled href="#"></a>
						</li>';
			}
		}

		return $out . '
					</ul>
				</div>';
	}

	private function photo(bool $profileVisitorMayAdminThisFoodsharer, bool $profileVisitorMaySeeHistory): string
	{
		$menu = $this->profileMenu($profileVisitorMayAdminThisFoodsharer, $profileVisitorMaySeeHistory);

		$sleep_info = '';
		$online = '';

		if ($this->foodsaver['online']) {
			$online = '<div style="margin-top:10px;">' . $this->v_utils->v_info(
					$this->foodsaver['name'] . ' ist online!',
					false,
					'<i class="fas fa-circle" style="color:var(--fs-green);"></i>'
				) . '</div>';
		}

		return '<div style="text-align:center;">
					' . $this->imageService->avatar($this->foodsaver, 130) . $sleep_info . '
				</div>
				' . $online . '
				' . $menu;
	}

	private function profileMenu(bool $profileVisitorMayAdminThisFoodsharer, bool $profileVisitorMaySeeHistory): string
	{
		$opt = '';

		if ($profileVisitorMayAdminThisFoodsharer) {
			$opt .= '<li><a href="/?page=foodsaver&a=edit&id=' . $this->foodsaver['id'] . '"><i class="fas fa-pencil-alt fa-fw"></i>Profil bearbeiten</a></li>';
		}
		if ($this->foodsaver['buddy'] === BuddyId::NO_BUDDY && $this->foodsaver['id'] != $this->session->id()) {
			$name = explode(' ', $this->foodsaver['name']);
			$name = $name[0];
			$opt .= '<li class="buddyRequest"><a onclick="ajreq(\'request\',{app:\'buddy\',id:' . (int)$this->foodsaver['id'] . '});return false;" href="#"><i class="fas fa-user fa-fw"></i>Ich kenne ' . $name . '</a></li>';
		}
		if ($profileVisitorMaySeeHistory) {
			$opt .= '<li><a href="#" onclick="ajreq(\'history\',{app:\'profile\',fsid:' . (int)$this->foodsaver['id'] . ',type:1});"><i class="fas fa-file-alt fa-fw"></i>Passhistorie</a></li>';
			$opt .= '<li><a href="#" onclick="ajreq(\'history\',{app:\'profile\',fsid:' . (int)$this->foodsaver['id'] . ',type:0});"><i class="fas fa-file-alt fa-fw"></i>Verifizierungshistorie</a></li>';
		}

		if ($this->reportPermissions->mayHandleReports()) {
			if (isset($this->foodsaver['note_count'])) {
				$opt .= '<li><a href="/profile/' . (int)$this->foodsaver['id'] . '/notes/"><i class="far fa-file-alt fa-fw"></i>' . $this->translationHelper->sv(
						'notes_count',
						['count' => $this->foodsaver['note_count']]
					) . '</a></li>';
			}
			if (isset($this->foodsaver['violation_count']) && $this->foodsaver['violation_count'] > 0) {
				$opt .= '<li><a href="/?page=report&sub=foodsaver&id=' . (int)$this->foodsaver['id'] . '"><i class="far fa-meh fa-fw"></i>' . $this->translationHelper->sv(
						'violation_count',
						['count' => $this->foodsaver['violation_count']]
					) . '</a></li>';
			}
		}

		$writeMessage = $this->foodsaver['id'] != $this->session->id() ?
			'<li><a href="#" onclick="chat(' . $this->foodsaver['id'] . ');return false;"><i class="fas fa-comment fa-fw"></i>Nachricht schreiben</a></li>'
			: '';

		return '
		<ul class="linklist">
			' . $writeMessage . $opt . '
			<li><a href="#" onclick="ajreq(\'reportDialog\',{app:\'report\',fsid:' . (int)$this->foodsaver['id'] . '});return false;"><i class="far fa-life-ring fa-fw"></i>Regelverletzung melden</a></li>
		</ul>';
	}

	private function sideInfos(): string
	{
		$infos = [];

		if ($this->session->may('orga')) {
			$last_login = (
			$this->foodsaver['last_login']
				? Carbon::parse($this->foodsaver['last_login'])->format('d.m.Y')
				: $this->translationHelper->s('Never')
			);
			$registration_date = Carbon::parse($this->foodsaver['anmeldedatum']);

			$infos[] = [
				'name' => $this->translationHelper->s('last_login'),
				'val' => $last_login,
			];
			$infos[] = [
				'name' => $this->translationHelper->s('registration_date'),
				'val' => $registration_date->format('d.m.Y'),
			];
			$infos[] = [
				'name' => $this->translationHelper->s('private_mail'),
				'val' => '<a href="/?page=mailbox&mailto=' . urlencode(
						$this->foodsaver['email']
					) . '">' . $this->foodsaver['email'] . '</a>',
			];
		}

		if (isset($this->foodsaver['mailbox']) && $this->profilePermissions->maySeeEmailAddress($this->foodsaver['id'])) {
			$url = $this->session->id() == $this->foodsaver['id']
				? '/?page=mailbox'
				: '/?page=mailbox&mailto=' . urlencode($this->foodsaver['mailbox']);
			$infos[] = [
				'name' => $this->translationHelper->s('mailbox'),
				'val' => '<a href="' . $url . '">' . $this->foodsaver['mailbox'] . '</a>',
			];
		}

		if ($this->foodsaver['stat_buddycount'] > 0) {
			$infos[] = [
				'name' => 'Bekannte',
				'val' => $this->foodsaver['name'] . (($this->foodsaver['stat_buddycount'] == 1) ? ' kennt ' : ' kennen ') . $this->foodsaver['stat_buddycount'] . ' Foodsaver',
			];
		}

		if ($this->foodsaver['stat_fetchcount'] > 0) {
			$infos[] = [
				'name' => 'Abholquote',
				'val' => $this->foodsaver['stat_fetchrate'] . '<span style="white-space:nowrap">&thinsp;</span>%'
			];
		}

		$infos[] = [
			'name' => ($this->foodsaver['rolle'] > Role::FOODSHARER) ? 'Foodsaver ID' : 'Foodsharer ID',
			'val' => $this->foodsaver['id']
		];

		$out = '';
		foreach ($infos as $info) {
			$out .= '<p><strong>' . $info['name'] . '</strong><br />' . $info['val'] . '</p>';
		}

		return '
		<div class="pure-g">
		    <div class="infos"> ' . $out . ' </div>
		</div>';
	}

	/**
	 * Create HTML for list of stores on the profile.
	 * Each store has a symbol in front indicating if the user is
	 *  - waiting for approval (a question mark)
	 *  - in store (the shopping basket used for stores)
	 *  - Springer = waiting list (a coffee mug).
	 *
	 * @return string: HTML with the list
	 */
	private function sideInfosCompanies(array $userCompanies): string
	{
		$out = '';
		foreach ($userCompanies as $company) {
			switch ($company['active']) {
				case MembershipStatus::APPLIED_FOR_TEAM:
					$userStatusOfStore = '<i class="far fa-question-circle fw"></i> ';
					break;
				case MembershipStatus::MEMBER:
					$userStatusOfStore = '<i class="fas fa-shopping-cart fw"></i> ';
					break;
				case MembershipStatus::JUMPER:
					$userStatusOfStore = '<i class="fas fa-mug-hot fw"></i> ';
					break;
				default:
					$userStatusOfStore = '';
					break;
			}
			$out .= '<p><a class="light" href="/?page=fsbetrieb&id=' . $company['id'] . '">' . $userStatusOfStore . $company['name'] . '</a></p>';
		}

		return '
		<div class="pure-g">
		    <div class="infos"> ' . $out . ' </div>
		</div>';
	}

	public function userNotes(
		string $notes,
		bool $profileVisitorMayAdminThisFoodsharer,
		bool $profileVisitorMaySeeHistory,
		array $userCompanies
	): void {
		$page = new vPage(
			$this->translationHelper->sv('notes_about', ['name' => $this->foodsaver['name']]),
			$this->v_utils->v_info($this->translationHelper->s('user_notes_info')) . $notes
		);
		$page->setBread($this->translationHelper->s('notes'));

		$page->addSectionLeft($this->photo($profileVisitorMayAdminThisFoodsharer, $profileVisitorMaySeeHistory));
		$page->addSectionLeft($this->sideInfos(), 'Infos');

		if ($this->session->may('orga')) {
			$page->addSectionLeft(
				$this->sideInfosCompanies($userCompanies),
				$this->translationHelper->sv('stores', ['count' => count($userCompanies)])
			);
		}

		$page->render();
	}

	public function getHistory(array $history, int $changeType): string
	{
		$out = '
			<ul class="linklist history">';

		$curDate = '';
		foreach ($history as $h) {
			if ($curDate !== $h['date']) {
				$out = $this->renderTypeOfHistoryEntry($changeType, $h, $out);

				$curDate = $h['date'];
			}

			$out = $h['bot_id'] === null
				? $out . '<li>
					Es liegt keine Information &uuml;ber den Ersteller vor
				</li>
				'
				: $out . '
				<li>
					<a class="corner-all" href="/profile/' . (int)$h['bot_id'] . '">
						<span class="n">' . $h['name'] . ' ' . $h['nachname'] . '</span>
						<span class="t"></span>
						<span class="c"></span>
					</a>
				</li>';
		}
		$out .= '
		</ul>';
		if ($curDate === '') {
			$out = $this->translationHelper->s('no_data');
		}

		return $out;
	}

	public function setData(array $data): void
	{
		$this->foodsaver = $data;
	}

	private function renderStatistics(): array
	{
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

		return [$fetchWeight, $fetchCount, $foodBasketCount, $postCount];
	}

	private function renderBananas(): string
	{
		if ($this->session->may('fs')) {
			$bananaCount = count($this->foodsaver['bananen']);
			if ($bananaCount === 0) {
				$bananaCount = '&nbsp;';
			}

			$banananButtonClass = ' bouched';
			$giveBanana = '';

			if (!$this->foodsaver['bouched'] && ($this->foodsaver['id'] != $this->session->id())) {
				$banananButtonClass = '';
				$giveBanana = '
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

			$this->pageHelper->addJs(
				'
			$(".stat_bananacount").magnificPopup({
				type:"inline"
			});'
			);
			$bananaCountButton = '
			<a href="#bananas" onclick="return false;" class="item stat_bananacount' . $banananButtonClass . '">
				<span class="val">' . $bananaCount . '</span>
				<span class="name">&nbsp;</span>
			</a>
			';

			$bananaCountButton .= '
			<div id="bananas" class="white-popup mfp-hide corner-all">
				<h3>' . str_replace('&nbsp;', '', $bananaCount) . ' Vertrauensbananen</h3>
				' . $giveBanana . '
				<table class="pintable">
					<tbody>';
			$odd = 'even';

			foreach ($this->foodsaver['bananen'] as $foodsaver) {
				if ($odd === 'even') {
					$odd = 'odd';
				} else {
					$odd = 'even';
				}
				$bananaCountButton .= '
				<tr class="' . $odd . ' bpost">
					<td class="img"><a title="' . $foodsaver['name'] . '" href="/profile/' . $foodsaver['id'] . '"><img src="' . $this->imageService->img(
						$foodsaver['photo']
					) . '"></a></td>
					<td><span class="msg">' . nl2br($foodsaver['msg']) . '</span>
					<div class="foot">
						<span class="time">' . $this->timeHelper->niceDate(
						$foodsaver['time_ts']
					) . ' von ' . $foodsaver['name'] . '</span>
					</div></td>
				</tr>';
			}
			$bananaCountButton .= '
					</tbody>
				</table>
			</div>';
		} else {
			$bananaCountButton = '';
		}

		return $bananaCountButton;
	}

	private function renderInformation(): string
	{
		$infos = [];
		[$ambassador, $infos] = $this->renderAmbassadorInformation($infos);
		$infos = $this->renderFoodsaverInformation($ambassador, $infos);
		$infos = $this->renderOrgaTeamMemberInformation($infos);
		$infos = $this->renderSleepingHatInformation($infos);
		$infos = $this->renderAboutMeInternalInformation($infos);

		$out = '';
		foreach ($infos as $info) {
			$out .= '<p><strong>' . $info['name'] . '</strong><br />' . $info['val'] . '</p>';
		}

		return $out;
	}

	private function renderAmbassadorInformation(array $infos): array
	{
		$ambassador = [];
		if ($this->foodsaver['botschafter']) {
			foreach ($this->foodsaver['botschafter'] as $foodsaver) {
				$ambassador[$foodsaver['id']] = '<a class="light" href="/?page=bezirk&bid=' . $foodsaver['id'] . '&sub=forum">' . $foodsaver['name'] . '</a>';
			}
			$infos[] = [
				'name' => $this->translationHelper->sv(
					'ambassador_districts',
					[
						'name' => $this->foodsaver['name'],
						'gender' => $this->translationHelper->genderWord(
							$this->foodsaver['geschlecht'],
							'',
							'in',
							'_in'
						),
					]
				),
				'val' => implode(', ', $ambassador),
			];
		}

		return [$ambassador, $infos];
	}

	private function renderFoodsaverInformation(array $ambassador, array $infos): array
	{
		if ($this->foodsaver['foodsaver']) {
			$fsa = [];
			$fsHomeDistrict = [];
			foreach ($this->foodsaver['foodsaver'] as $foodsaver) {
				if ($foodsaver['id'] == $this->foodsaver['bezirk_id']) {
					$fsHomeDistrict[] = '<a class="light" href="/?page=bezirk&bid=' . $foodsaver['id'] . '&sub=forum">' . $foodsaver['name'] . '</a>';
				}
				if (!isset($ambassador[$foodsaver['id']])) {
					$fsa[] = '<a class="light" href="/?page=bezirk&bid=' . $foodsaver['id'] . '&sub=forum">' . $foodsaver['name'] . '</a>';
				}
			}
			if (!empty($fsa)) {
				$infos[] = [
					'name' => $this->translationHelper->sv(
						'foodsaver_districts',
						['name' => $this->foodsaver['name']]
					),
					'val' => implode(', ', $fsa),
				];
			}
			if (!empty($fsHomeDistrict)) {
				$infos[] = [
					'name' => $this->translationHelper->sv(
						'foodsaver_home_district',
						['name' => $this->foodsaver['name']]
					),
					'val' => implode(', ', $fsHomeDistrict),
				];
			}
		}

		return $infos;
	}

	private function renderAboutMeInternalInformation(array $infos): array
	{
		if ($this->foodsaver['about_me_intern']) {
			$infos[] = [
				'name' => $this->translationHelper->s('about_me_intern_profile'),
				'val' => $this->foodsaver['about_me_intern'],
			];
		}

		return $infos;
	}

	private function renderOrgaTeamMemberInformation(array $infos): array
	{
		if ($this->foodsaver['orga']) {
			$ambassador = [];
			foreach ($this->foodsaver['orga'] as $foodsaver) {
				if ($this->session->isOrgaTeam()) {
					$ambassador[$foodsaver['id']] = '<a class="light" href="/?page=bezirk&bid=' . $foodsaver['id'] . '&sub=forum">' . $foodsaver['name'] . '</a>';
				} else {
					$ambassador[$foodsaver['id']] = $foodsaver['name'];
				}
			}
			$infos[] = [
				'name' => $this->translationHelper->sv(
					'foodsaver_workgroups',
					[
						'gender' => $this->translationHelper->genderWord(
							$this->foodsaver['geschlecht'],
							'Er',
							'Sie',
							'Er/Sie'
						),
					]
				),
				'val' => implode(', ', $ambassador),
			];
		}

		return $infos;
	}

	private function renderSleepingHatInformation(array $infos): array
	{
		switch ($this->foodsaver['sleep_status']) {
			case 1:
				$infos[] = [
					'name' => $this->translationHelper->sv(
						'foodsaver_sleeping_hat_time',
						[
							'name' => $this->foodsaver['name'],
							'datum_von' => date('d.m.Y', $this->foodsaver['sleep_from_ts']),
							'datum_bis' => date('d.m.Y', $this->foodsaver['sleep_until_ts']),
						]
					),
					'val' => $this->foodsaver['sleep_msg'],
				];
				break;
			case 2:
				$infos[] = [
					'name' => $this->translationHelper->sv(
						'foodsaver_sleeping_hat_time_undefined',
						['name' => $this->foodsaver['name']]
					),
					'val' => $this->foodsaver['sleep_msg'],
				];
				break;
			default:
				break;
		}

		return $infos;
	}

	private function renderTypeOfHistoryEntry(int $changeType, array $h, string $out): string
	{
		switch ($changeType) {
			case 0:
				$typeOfChange = '';
				switch ($h['change_status']) {
					case 0:
						$class = 'unverify';
						$typeOfChange = $this->translationHelper->s('de_verified');
						break;
					case 1:
						$class = 'verify';
						$typeOfChange = $this->translationHelper->s('verified');
						break;
					default:
						break;
				}
				$out .= '<li class="title"><span class="' . $class . '">' . $typeOfChange . '</span> am ' . $this->timeHelper->niceDate(
						$h['date_ts']
					) . ' durch:</li>';
				break;
			case 1:
				$out = $h['bot_id'] === null
					? $out . '<li class="title">' . $this->timeHelper->niceDate(
						$h['date_ts']
					) . '</li>'
					: $out . '<li class="title">' . $this->timeHelper->niceDate($h['date_ts']) . ' durch:</li>';
				break;
			default:
				break;
		}

		return $out;
	}
}
