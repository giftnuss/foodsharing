<?php

namespace Foodsharing\Modules\Profile;

use Carbon\Carbon;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Lib\View\vPage;
use Foodsharing\Modules\Core\DBConstants\Buddy\BuddyId;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;
use Foodsharing\Modules\Core\View;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TimeHelper;
use Foodsharing\Utility\TranslationHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileView extends View
{
	private $foodsaver;
	private $profilePermissions;
	private $reportPermissions;

	public function __construct(
		\Twig\Environment $twig,
		Session $session,
		Utils $viewUtils,
		ProfilePermissions $profilePermissions,
		ReportPermissions $reportPermissions,
		DataHelper $dataHelper,
		IdentificationHelper $identificationHelper,
		ImageHelper $imageService,
		PageHelper $pageHelper,
		RouteHelper $routeHelper,
		Sanitizer $sanitizerService,
		TimeHelper $timeHelper,
		TranslationHelper $translationHelper,
		TranslatorInterface $translator
	) {
		parent::__construct(
			$twig,
			$session,
			$viewUtils,
			$dataHelper,
			$identificationHelper,
			$imageService,
			$pageHelper,
			$routeHelper,
			$sanitizerService,
			$timeHelper,
			$translationHelper,
			$translator
		);

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

		if ($this->profilePermissions->maySeeBounceWarning($this->foodsaver['id'])) {
			if ($this->foodsaver['emailIsBouncing']) {
				$warningMessage = '<h1>' . $this->translator->trans('profile.mailBounceWarning', ['{email}' => $this->foodsaver['email']]) . '</h1>';
				$warningContainer = '<div>' . $this->v_utils->v_info($warningMessage, false, false) . '</div>';
				$page->addSection($warningContainer, $this->translator->trans('profile.warning'));
			}
		}

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
			<div>
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
		$out = '<div class="bootstrap">';

		if ($this->session->isOrgaTeam()) {
			$out .= '<a class="btn btn-sm btn-danger cancel-all-button" href="#" onclick="'
				. 'if(confirm(\''
					. $this->translator->trans('profile.cancelAll', ['{name}' => $this->foodsaver['name']])
				. '\')){'
				. 'ajreq(\'deleteAllDatesFromFoodsaver\','
				. '{app:\'profile\',fsid:' . $this->foodsaver['id'] . '}'
				. ')};return false;">'
					. $this->translationHelper->s('cancel_all')
				. '</a>';
		}

		$out .= '
<div class="clear datelist">';
		foreach ($fetchDates as $date) {
			$userConfirmedForPickup = ($date['confirmed'] == 1 ? '✓' : '?') . '&nbsp;';

			$out .= '
	<div class="row align-items-center p-1 border-top">';
			$out .= '
		<div class="col my-1">
			<a href="/?page=fsbetrieb&id=' . $date['betrieb_id'] . '" class="ui-corner-all">
				<span class="title">'
				. $userConfirmedForPickup . $this->timeHelper->niceDate($date['date_ts']) .
				'</span>
			</a>
		</div>
		<div class="col my-1 text-center text-md-left">
			<a href="/?page=fsbetrieb&id=' . $date['betrieb_id'] . '" class="ui-corner-all">
				<span class="title">' . $date['betrieb_name'] . '</span>
			</a>
		</div>';

			if ($this->session->isAdminFor($date['bezirk_id']) || $this->session->isOrgaTeam()) {
				$out .= '
		<div class="col flex-grow-0 flex-shrink-1">
			<a class="btn btn-sm btn-secondary" href="#" onclick="ajreq(\'deleteSinglePickup\',{app:\'profile\',fsid:' . $this->foodsaver['id'] . ',storeId:' . $date['betrieb_id'] . ',date:' . $date['date_ts'] . '});return false;">austragen</a>
		</div>';
			}
			$out .= '
	</div>';
		}
		$out .= '
</div>';

		return $out . '</div>';
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
			$opt .= '<li class="buddyRequest"><a onclick="sendBuddyRequest(' . (int)$this->foodsaver['id'] . ');return false;" href="#"><i class="fas fa-user fa-fw"></i>Ich kenne ' . $name . '</a></li>';
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

		$this->pageHelper->addJs('
			$("#disabledreports-link").fancybox({
				closeClick: false,
				closeBtn: true,
			});
		');

		$this->pageHelper->addHidden('
			<div id="disabledreports">
				<div class="popbox bootstrap">
					<h3>Regelverletzung melden</h3>
					<p>Bis zum 30. September ist es nicht möglich, Regelverletzungen über das System zu melden. Mehr Infos dazu findet ihr <a href="https://foodsharing.de/?page=blog&sub=read&id=254">in diesem Blogeintrag</a>.</p>
				</div>
			</div>
		');

		return '
		<ul class="linklist">
			' . $writeMessage . $opt . '
			<li><a href="#disabledreports" id="disabledreports-link" onclick="return false;" class="item"><i class="far fa-life-ring fa-fw"></i>Regelverletzung melden</a></li>
		</ul>';
	}

	private function sideInfos(): string
	{
		$fsId = $this->foodsaver['id'];
		$infos = [];

		if ($this->profilePermissions->maySeeLastLogin($fsId)) {
			if (isset($this->foodsaver['last_login'])) {
				$last_login = Carbon::parse($this->foodsaver['last_login'])->format('d.m.Y');
			} else {
				$last_login = $this->translator->trans('profile.never');
			}
			$infos[] = [
				'name' => $this->translator->trans('profile.lastLogin'),
				'val' => $last_login,
			];
		}

		if ($this->profilePermissions->maySeeRegistrationDate($fsId)) {
			if (isset($this->foodsaver['anmeldedatum'])) {
				$registration_date = Carbon::parse($this->foodsaver['anmeldedatum'])->format('d.m.Y');
			} else {
				$registration_date = $this->translator->trans('profile.never');
			}
			$infos[] = [
				'name' => $this->translator->trans('profile.registrationDate'),
				'val' => $registration_date,
			];
		}

		$privateMail = $this->foodsaver['email'] ?? '';
		if ($privateMail && $this->profilePermissions->maySeePrivateEmail($fsId)) {
			$url = '/?page=mailbox&mailto=' . urlencode($privateMail);
			$splitMail = implode('<wbr>@', explode('@', $privateMail));
			$infos[] = [
				'name' => $this->translator->trans('profile.privateMail'),
				'val' => '<a href="' . $url . '">' . $splitMail . '</a>',
			];
		}

		$fsMail = $this->foodsaver['mailbox'] ?? '';
		if ($fsMail && $this->profilePermissions->maySeeEmailAddress($fsId)) {
			if ($this->session->id() == $fsId) {
				$url = '/?page=mailbox';
			} else {
				$url = '/?page=mailbox&mailto=' . urlencode($fsMail);
			}
			$splitMail = implode('<wbr>@', explode('@', $fsMail));
			$infos[] = [
				'name' => $this->translator->trans('profile.fsMail'),
				'val' => '<a href="' . $url . '">' . $splitMail . '</a>',
			];
		}

		$buddycount = $this->foodsaver['stat_buddycount'];
		if ($buddycount > 0) {
			$infos[] = [
				'name' => 'Bekannte',
				'val' => $this->translator->trans('profile.buddycount' . ($buddycount == 1 ? '1' : ''), [
					'{count}' => $buddycount,
					'{name}' => $this->foodsaver['name'],
				]),
			];
		}

		if ($this->foodsaver['stat_fetchcount'] > 0 && $this->profilePermissions->maySeeFetchRate($fsId)) {
			$infos[] = [
				'name' => 'Abholquote',
				'val' => $this->foodsaver['stat_fetchrate'] . '&thinsp;%',
			];
		}

		$isFoodsaver = $this->foodsaver['rolle'] > Role::FOODSHARER;
		$infos[] = [
			'name' => $this->translator->trans($isFoodsaver ? 'profile.foodsaverId' : 'profile.foodsharerId'),
			'val' => $fsId,
		];

		$out = '<dl class="profile-sideinfos">';
		foreach ($infos as $info) {
			$out .= '<dt>' . $info['name'] . '</dt>';
			$out .= '<dd>' . $info['val'] . '</dd>';
		}
		$out .= '</dl>';

		return $out;
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
		<div>
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
		if (!$this->session->may('fs')) {
			return '';
		}

		$bananaCount = count($this->foodsaver['bananen']);
		if ($bananaCount === 0) {
			$bananaCount = '&nbsp;';
		}

		$buttonClass = ' bouched';
		$giveBanana = '';

		if (!$this->foodsaver['bouched'] && ($this->foodsaver['id'] != $this->session->id())) {
			$buttonClass = '';
			$giveBanana = '
		<div class="mb-2">
			<a class="btn btn-secondary btn-sm" href="#" onclick="
				$(this).hide().next().removeClass(\'d-none\');
				$(\'#bouch-ta\').autosize();
				$.fancybox.update();
				return false;"
			>
				Schenke ' . $this->foodsaver['name'] . ' eine Banane
			</a>
			<div class="d-none">
				<div class="info">
					Hier kannst Du etwas dazu schreiben, warum Du ' . $this->foodsaver['name'] . ' gerne eine Banane schenken möchtest. Du kannst jedem Foodsaver nur eine Banane schenken!<br />
					Bitte gib die Vertrauensbanane nur an Foodsaver, die Du persönlich kennst und bei denen Du weißt, dass sie zuverlässig und engagiert sind und Du sicher bist, dass sie die Verhaltensregeln und die Rechtsvereinbarung einhalten.
					<p>
						<strong>Vertrauensbananen können nicht zurückgenommen werden. Sei bitte deswegen besonders achtsam, wem Du eine schenkst.</strong>
					</p>
				</div>
				<div class="d-flex">
					<textarea id="bouch-ta" class="textarea mr-2" placeholder="min. 100 Zeichen..."></textarea>
					<a href="#" class="btn btn-sm btn-secondary float-right d-inline-flex" onclick="
						trySendBanana(' . (int)$this->foodsaver['id'] . ');
						return false;"
					>
						<img src="/img/banana.png" class="align-self-center" />
					</a>
				</div>
			</div>
		</div>
		';
		}

		$this->pageHelper->addJs('
			$(".stat_bananacount").fancybox({
				closeClick: false,
				closeBtn: true,
			});
		');

		$this->pageHelper->addHidden('
			<div id="bananas">
				<div class="popbox bootstrap">
					<h3>' . str_replace('&nbsp;', '', $bananaCount) . ' Vertrauensbananen</h3>
					' . $giveBanana . '
					<table class="pintable">
						<tbody>
							' . $this->renderBananasTable($this->foodsaver['bananen']) . '
						</tbody>
					</table>
				</div>
			</div>
		');

		return '
			<a href="#bananas" onclick="return false;" class="item stat_bananacount' . $buttonClass . '">
				<span class="val">' . $bananaCount . '</span>
				<span class="name">&nbsp;</span>
			</a>
		';
	}

	private function renderBananasTable(array $bananasFrom): string
	{
		$out = '';

		foreach ($bananasFrom as $foodsaver) {
			$fsName = $foodsaver['name'];
			$when = $this->timeHelper->niceDate($foodsaver['time_ts']);
			$photo = $this->imageService->img($foodsaver['photo'], '50');
			$text = nl2br(strip_tags($foodsaver['msg']));
			$out .= '
			<tr class="border-top">
				<td>
					<a title="' . $fsName . '" href="/profile/' . $foodsaver['id'] . '">
						<img src="' . $photo . '">
					</a>
				</td>
				<td>
					<span class="msg">' . $text . '</span>
					<div class="foot">
						<span class="time">' . $when . ' von ' . $fsName . '</span>
					</div>
				</td>
			</tr>
			';
		}

		return $out;
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
						$typeOfChange = $this->translationHelper->s('lostVerification');
						break;
					case 1:
						$class = 'verify';
						$typeOfChange = $this->translationHelper->s('wasVerified');
						break;
					default:
						$class = '';
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
