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
	private array $foodsaver;
	private ProfilePermissions $profilePermissions;
	private ReportPermissions $reportPermissions;

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
		array $userStores = [],
		array $fetchDates = []
	): void {
		$page = new vPage($this->foodsaver['name'], $this->infos());
		$fsId = $this->foodsaver['id'];

		if ($this->profilePermissions->maySeeBounceWarning($fsId)) {
			if ($this->foodsaver['emailIsBouncing']) {
				$warningMessage = '<h1>' . $this->translator->trans('profile.mailBounceWarning', [
					'{email}' => $this->foodsaver['email'],
				]) . '</h1>';
				$warningContainer = '<div>'
					. $this->v_utils->v_info($warningMessage, '', '<i class="fas fa-exclamation-triangle"></i>')
					. '</div>';
				$page->addSection($warningContainer, $this->translator->trans('profile.warning'));
			}
		}

		$page->addSection($wallPosts, $this->translator->trans('profile.pinboard', [
			'{name}' => $this->foodsaver['name'],
		]));

		if ($this->session->id() != $fsId) {
			$this->pageHelper->addStyle('#wallposts .tools {display:none;}');
		}

		if ($fetchDates) {
			$page->addSection($this->fetchDates($fetchDates), $this->translator->trans('dashboard.pickupdates'));
		}

		$page->addSectionLeft(
			$this->photo($profileVisitorMayAdminThisFoodsharer, $profileVisitorMaySeeHistory)
		);

		$page->addSectionLeft($this->sideInfos(), $this->translator->trans('profile.infos.title'));

		if ($profileVisitorMayAdminThisFoodsharer && $userStores) { // AMB functionality
			$page->addSectionLeft(
				$this->sideInfosStores($userStores),
				$this->translator->trans('profile.nav.storelist', ['{count}' => count($userStores)])
			);
		}

		$page->render();
	}

	private function infos(): string
	{
		$infos = $this->renderInformation();
		$stats = join('', $this->renderStatistics());
		$bananas = $this->renderBananas();

		return '
			<div>
				<div class="profile statdisplay">
					' . $stats . '
					' . $bananas . '
				</div>
			    <div class="infos"> ' . $infos . ' </div>
			</div>';
	}

	private function fetchDates(array $fetchDates): string
	{
		$out = '<div class="bootstrap">';

		if ($this->session->isOrgaTeam()) {
			$out .= '<a class="btn btn-sm btn-danger cancel-all-button" href="#" onclick="'
				. 'if(confirm(\''
					. $this->translator->trans('profile.signoutAllConfirmation', ['{name}' => $this->foodsaver['name']])
				. '\')){'
				. 'ajreq(\'deleteAllDatesFromFoodsaver\','
				. '{app:\'profile\',fsid:' . $this->foodsaver['id'] . '}'
				. ')};return false;">'
					. $this->translator->trans('profile.signoutAll')
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
			<a class="btn btn-sm btn-secondary" href="#" onclick="'
			. 'ajreq(\'deleteSinglePickup\','
			. '{app:\'profile\''
			. ',fsid:' . $this->foodsaver['id']
			. ',storeId:' . $date['betrieb_id']
			. ',date:' . $date['date_ts']
			. '});return false;">' . $this->translator->trans('profile.signoutPickup') . '</a>
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
		$online = '';
		if ($this->foodsaver['online']) {
			$online = '<div class="mt-2">' . $this->v_utils->v_info(
				$this->translator->trans('profile.online', ['{name}' => $this->foodsaver['name']]),
				'',
				'<i class="fas fa-circle text-secondary"></i>'
			) . '</div>';
		}

		$menu = $this->profileMenu($profileVisitorMayAdminThisFoodsharer, $profileVisitorMaySeeHistory);

		return '<div class="text-center">'
			. $this->imageService->avatar($this->foodsaver, 130) . '
		</div>' . $online . $menu;
	}

	private function profileMenu(bool $profileVisitorMayAdminThisFoodsharer, bool $profileVisitorMaySeeHistory): string
	{
		$fsId = intval($this->foodsaver['id']);
		$opt = '';

		if ($profileVisitorMayAdminThisFoodsharer) {
			$opt .= '<li><a href="/?page=foodsaver&a=edit&id=' . $fsId . '">'
				. '<i class="fas fa-pencil-alt fa-fw"></i>' . $this->translator->trans('profile.nav.edit')
				. '</a></li>';
		}
		if ($this->foodsaver['buddy'] === BuddyId::NO_BUDDY && $fsId != $this->session->id()) {
			$name = explode(' ', $this->foodsaver['name']);
			$name = $name[0];
			$opt .= '<li class="buddyRequest"><a onclick="trySendBuddyRequest(' . $fsId . '); return false;" href="#">'
				. '<i class="fas fa-user fa-fw"></i>' . $this->translator->trans('profile.nav.buddy', ['{name}' => $name])
				. '</a></li>';
		}
		if ($profileVisitorMaySeeHistory) {
			$opt .= '<li><a href="#" onclick="ajreq(\'history\',{app:\'profile\',fsid:' . $fsId . ',type:1});">'
				. '<i class="fas fa-file-alt fa-fw"></i>' . $this->translator->trans('profile.nav.history')
				. '</a></li>';
			$opt .= '<li><a href="#" onclick="ajreq(\'history\',{app:\'profile\',fsid:' . $fsId . ',type:0});">'
				. '<i class="fas fa-file-alt fa-fw"></i>' . $this->translator->trans('profile.nav.verificationHistory')
				. '</a></li>';
		}

		$showNotes = isset($this->foodsaver['note_count']);
		if ($this->reportPermissions->mayHandleReports() && $showNotes) {
			$opt .= '<li><a href="/profile/' . $fsId . '/notes/">'
				. '<i class="far fa-file-alt fa-fw"></i>' . $this->translator->trans('profile.nav.notes', [
					'{count}' => $this->foodsaver['note_count'],
				]) . '</a></li>';
		}

		$hasViolations = isset($this->foodsaver['violation_count']) && $this->foodsaver['violation_count'] > 0;
		if ($this->reportPermissions->mayHandleReports() && $hasViolations) {
			$opt .= '<li><a href="/?page=report&sub=foodsaver&id=' . $fsId . '">'
				. '<i class="far fa-meh fa-fw"></i>' . $this->translator->trans('profile.nav.violations', [
					'{count}' => $this->foodsaver['violation_count'],
				]) . '</a></li>';
		}

		$writeMessage = '';
		if ($fsId != $this->session->id()) {
			$writeMessage = '<li><a href="#" onclick="chat(' . $fsId . ');return false;">'
				. '<i class="fas fa-comment fa-fw"></i>' . $this->translator->trans('chat.open_chat')
			. '</a></li>';
		}

		$this->pageHelper->addJs('
			$("#disabledreports-link").fancybox({
				closeClick: false,
				closeBtn: true,
			});
		');

		$this->pageHelper->addHidden('
			<div id="disabledreports" class="popbox bootstrap">
				<h3>Regelverletzung melden</h3>
				<hr>
				<p>
					Aktuell werden Regelverletzungen nicht an dieser Stelle gemeldet.
					<br>
					Wendet euch bei Bedarf an die lokale Meldegruppe in eurem Bezirk.
				</p>
				<p>
					Mehr Infos zum Meldesystem findet ihr
					<a href="https://foodsharing.de/?page=blog&sub=read&id=255">in diesem Blogeintrag</a>.
				</p>
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
				$last_login = $this->translator->trans('profile.infos.never');
			}
			$infos[] = [
				'name' => $this->translator->trans('profile.infos.lastLogin'),
				'val' => $last_login,
			];
		}

		if ($this->profilePermissions->maySeeRegistrationDate($fsId)) {
			if (isset($this->foodsaver['anmeldedatum'])) {
				$registration_date = Carbon::parse($this->foodsaver['anmeldedatum'])->format('d.m.Y');
			} else {
				$registration_date = $this->translator->trans('profile.infos.never');
			}
			$infos[] = [
				'name' => $this->translator->trans('profile.infos.registrationDate'),
				'val' => $registration_date,
			];
		}

		$privateMail = $this->foodsaver['email'] ?? '';
		if ($privateMail && $this->profilePermissions->maySeePrivateEmail($fsId)) {
			$url = '/?page=mailbox&mailto=' . urlencode($privateMail);
			$splitMail = implode('<wbr>@', explode('@', $privateMail));
			$infos[] = [
				'name' => $this->translator->trans('profile.infos.privateMail'),
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
				'name' => $this->translator->trans('profile.infos.fsMail'),
				'val' => '<a href="' . $url . '">' . $splitMail . '</a>',
			];
		}

		$buddycount = $this->foodsaver['stat_buddycount'];
		if ($buddycount > 0) {
			$infos[] = [
				'name' => $this->translator->trans('profile.infos.buddies'),
				'val' => $this->translator->trans('profile.infos.buddycount' . ($buddycount == 1 ? '1' : ''), [
					'{count}' => $buddycount,
					'{name}' => $this->foodsaver['name'],
				]),
			];
		}

		if ($this->foodsaver['stat_fetchcount'] > 0 && $this->profilePermissions->maySeeFetchRate($fsId)) {
			$infos[] = [
				'name' => $this->translator->trans('profile.infos.fetchrate'),
				'val' => $this->foodsaver['stat_fetchrate'] . '&thinsp;%',
			];
		}

		$isFoodsaver = $this->foodsaver['rolle'] > Role::FOODSHARER;
		$infos[] = [
			'name' => $this->translator->trans($isFoodsaver ? 'profile.infos.foodsaverId' : 'profile.infos.foodsharerId'),
			'val' => $fsId,
		];

		$out = '<dl class="profile-infos profile-side">';
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
	private function sideInfosStores(array $userStores): string
	{
		$out = '';
		foreach ($userStores as $store) {
			switch ($store['active']) {
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
			$out .= '<p><a class="light" href="/?page=fsbetrieb&id=' . $store['id'] . '">' . $userStatusOfStore . $store['name'] . '</a></p>';
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
		array $userStores
	): void {
		$page = new vPage(
			$this->translator->trans('profile.notes.title', ['{name}' => $this->foodsaver['name']]),
			$this->v_utils->v_info($this->translator->trans('profile.notes.info')) . $notes
		);
		$page->setBread($this->translator->trans('profile.notes.bread'));

		$page->addSectionLeft($this->photo($profileVisitorMayAdminThisFoodsharer, $profileVisitorMaySeeHistory));
		$page->addSectionLeft($this->sideInfos(), $this->translator->trans('profile.infos.title'));

		if ($this->session->may('orga')) {
			$page->addSectionLeft(
				$this->sideInfosStores($userStores),
				$this->translator->trans('profile.storelist', ['{count}' => count($userStores)])
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
				? $out . '<li>' . $this->translator->trans('profile.history.noActor') . '</li>'
				: $out . '<li>
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
			$out = $this->translator->trans('profile.history.noData');
		}

		return $out;
	}

	public function setData(array $data): void
	{
		$this->foodsaver = $data;
	}

	private function renderStatistics(): array
	{
		$stats = [];
		if (($fetchWeight = $this->foodsaver['stat_fetchweight']) > 0) {
			$label = $this->translator->trans('profile.stats.weight');
			$stats[] = $this->renderStat($fetchWeight, 'kg', $label, 'stat_fetchweight');
		}

		if (($fetchCount = $this->foodsaver['stat_fetchcount']) > 0) {
			$label = $this->translator->trans('profile.stats.count');
			$stats[] = $this->renderStat($fetchCount, 'x', $label, 'stat_fetchcount');
		}

		if (($basketCount = $this->foodsaver['basketCount']) > 0) {
			$label = $this->translator->trans('profile.stats.baskets');
			$stats[] = '<a href="/essenskoerbe">'
				. $this->renderStat($basketCount, 'x', $label, 'stat_basketcount')
			. '</a>';
		}

		if ($this->session->may('fs')) {
			$label = $this->translator->trans('profile.stats.posts');
			$stats[] = $this->renderStat($this->foodsaver['stat_postcount'], '', $label, 'stat_postcount');
		}

		return $stats;
	}

	private function renderStat($number, string $suffix, string $label, string $class): string
	{
		return '<span class="item ' . $class . '">'
			. '<span class="val">' . number_format($number, 0, ',', '.')
			. ($suffix ? '<span style="white-space:nowrap">&thinsp;</span>' . $suffix : '')
			. '</span>
			<span class="name">' . $label . '</span>
		</span>';
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
				' . $this->translator->trans('profile.banana.give', ['{name}' => $this->foodsaver['name']]) . '
			</a>
			<div class="d-none">
				<div class="info">
					' . $this->translator->trans('profile.banana.details', ['{name}' => $this->foodsaver['name']]) . '
					<br>
					<strong>' . $this->translator->trans('profile.banana.undo') . '</strong>
					<br>
					' . $this->translator->trans('profile.banana.vouch') . '
				</div>
				<div class="d-flex">
					<textarea id="bouch-ta" class="textarea mr-2" placeholder="' . $this->translator->trans('profile.banana.placeholder') . '"></textarea>
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
					<h3>' . $this->translator->trans('profile.banana.title', [
						'{count}' => str_replace('&nbsp;', '', $bananaCount),
					]) . '</h3>' . $giveBanana . '
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
						<span class="time">' . $when . $this->translator->trans('profile.banana.by') . $fsName . '</span>

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

		$out = '<dl class="profile-infos profile-main">';
		foreach ($infos as $info) {
			$out .= '<dt>' . $info['name'] . '</dt>';
			$out .= '<dd>' . $info['val'] . '</dd>';
		}
		$out .= '</dl>';

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
				'name' => $this->translator->trans('profile.ambRegions', [
					'{name}' => $this->foodsaver['name'],
					'{role}' => $this->translationHelper->genderWord(
						$this->foodsaver['geschlecht'],
						$this->translator->trans('terminology.ambassador.m'),
						$this->translator->trans('terminology.ambassador.f'),
						$this->translator->trans('terminology.ambassador.d')
					),
				]),
				'val' => implode(', ', $ambassador),
			];
		}

		return [$ambassador, $infos];
	}

	private function renderFoodsaverInformation(array $ambassador, array $infos): array
	{
		if ($this->foodsaver['foodsaver']) {
			$fsa = [];
			$fsHomeDistrict = '';
			foreach ($this->foodsaver['foodsaver'] as $foodsaver) {
				if ($foodsaver['id'] == $this->foodsaver['bezirk_id']) {
					$fsHomeDistrict = '<a class="light" href="/?page=bezirk&bid=' . $foodsaver['id'] . '&sub=forum">' . $foodsaver['name'] . '</a>';
					if ($this->profilePermissions->maySeeHistory($this->foodsaver['id']) && !empty($this->foodsaver['home_district_history'])) {
						$fsHomeDistrict .= ' (<a class="light" href="/profile/' . $this->foodsaver['home_district_history']['changer_id'] . '">' . $this->foodsaver['home_district_history']['changer_full_name'] . '</a> ';
						$fsHomeDistrict .= Carbon::parse($this->foodsaver['home_district_history']['date'])->format('d.m.Y H:i:s') . ')';
					}
				}
				if (!isset($ambassador[$foodsaver['id']])) {
					$fsa[] = '<a class="light" href="/?page=bezirk&bid=' . $foodsaver['id'] . '&sub=forum">' . $foodsaver['name'] . '</a>';
				}
			}
			if (!empty($fsa)) {
				$infos[] = [
					'name' => $this->translator->trans('profile.regions', ['{name}' => $this->foodsaver['name']]),
					'val' => implode(', ', $fsa),
				];
			}
			if (!empty($fsHomeDistrict)) {
				$infos[] = [
					'name' => $this->translator->trans('profile.homeRegion', ['{name}' => $this->foodsaver['name']]),
					'val' => $fsHomeDistrict,
				];
			}
		}

		return $infos;
	}

	private function renderAboutMeInternalInformation(array $infos): array
	{
		if ($this->foodsaver['about_me_intern']) {
			$infos[] = [
				'name' => $this->translator->trans('profile.about_me_intern'),
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
				'name' => $this->translator->trans('profile.workgroups', ['{name}' => $this->foodsaver['name']]),
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
					'name' => $this->translator->trans('profile.sleepinfo', [
						'{name}' => $this->foodsaver['name'],
						'{from}' => date('d.m.Y', $this->foodsaver['sleep_from_ts']),
						'{until}' => date('d.m.Y', $this->foodsaver['sleep_until_ts']),
					]),
					'val' => $this->foodsaver['sleep_msg'],
				];
				break;
			case 2:
				$infos[] = [
					'name' => $this->translator->trans('profile.sleeping', ['{name}' => $this->foodsaver['name']]),
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
		$when = $this->timeHelper->niceDate($h['date_ts']);

		switch ($changeType) {
			case 0:
				$typeOfChange = '';
				switch ($h['change_status']) {
					case 0:
						$class = 'unverify';
						$typeOfChange = $this->translator->trans('profile.history.lostVerification');
						break;
					case 1:
						$class = 'verify';
						$typeOfChange = $this->translator->trans('profile.history.wasVerified');
						break;
					default:
						$class = '';
						break;
				}
				$out .= '<li class="title">'
					. '<span class="' . $class . '">' . $typeOfChange . '</span>'
					. ' am ' . $when . ' durch:' . '</li>';
				break;
			case 1:
				$out = $h['bot_id'] === null
					? $out . '<li class="title">' . $when . '</li>'
					: $out . '<li class="title">' . $when . ' durch:' . '</li>';
				break;
			default:
				break;
		}

		return $out;
	}
}
