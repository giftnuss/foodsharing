<?php

namespace Foodsharing\Modules\Settings;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Info\InfoType;
use Foodsharing\Modules\Core\DBConstants\Quiz\QuizStatus;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\FoodSharePoint\FoodSharePointGateway;
use Foodsharing\Modules\Quiz\QuizGateway;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\Region\ForumFollowerGateway;
use Foodsharing\Modules\Region\RegionGateway;

class SettingsControl extends Control
{
	private $settingsGateway;
	private $foodsaver;
	private $quizGateway;
	private $quizSessionGateway;
	private $contentGateway;
	private $foodsaverGateway;
	private $foodSharePointGateway;
	private $dataHelper;
	private $forumFollowerGateway;
	private $regionGateway;

	public function __construct(
		SettingsView $view,
		SettingsGateway $settingsGateway,
		QuizGateway $quizGateway,
		QuizSessionGateway $quizSessionGateway,
		ContentGateway $contentGateway,
		FoodsaverGateway $foodsaverGateway,
		FoodSharePointGateway $foodSharePointGateway,
		DataHelper $dataHelper,
		ForumFollowerGateway $forumFollowerGateway,
		RegionGateway $regionGateway
	) {
		$this->view = $view;
		$this->settingsGateway = $settingsGateway;
		$this->quizGateway = $quizGateway;
		$this->quizSessionGateway = $quizSessionGateway;
		$this->contentGateway = $contentGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->foodSharePointGateway = $foodSharePointGateway;
		$this->dataHelper = $dataHelper;
		$this->forumFollowerGateway = $forumFollowerGateway;
		$this->regionGateway = $regionGateway;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}

		if (isset($_GET['newmail'])) {
			$this->handle_newmail();
		}

		$this->foodsaver = $this->foodsaverGateway->getFoodsaverDetails($this->session->id());

		if (!isset($_GET['sub'])) {
			$this->routeHelper->go('/?page=settings&sub=general');
		}

		$this->pageHelper->addTitle($this->translationHelper->s('settings'));
	}

	public function index()
	{
		$this->pageHelper->addBread('Einstellungen', '/?page=settings');

		$menu = [
			['name' => $this->translationHelper->s('settings_general'), 'href' => '/?page=settings&sub=general'],
			['name' => $this->translationHelper->s('settings_info'), 'href' => '/?page=settings&sub=info']
		];

		$menu[] = ['name' => $this->translationHelper->s('bcard'), 'href' => '/?page=bcard'];
		//$menu[] = array('name' => $this->translationHelper->s('calendar'), 'href' => '/?page=settings&sub=calendar');

		$this->pageHelper->addContent($this->view->menu($menu, ['title' => $this->translationHelper->s('settings'), 'active' => $this->getSub()]), CNT_LEFT);

		$menu = [];
		$menu[] = ['name' => $this->translationHelper->s('sleeping_user'), 'href' => '/?page=settings&sub=sleeping'];
		$menu[] = ['name' => 'E-Mail-Adresse ändern', 'click' => 'ajreq(\'changemail\');return false;'];

		if ($this->foodsaver['rolle'] == Role::FOODSHARER) {
			$menu[] = ['name' => 'Werde ' . $this->translationHelper->s('rolle_1_' . $this->foodsaver['geschlecht']), 'href' => '/?page=settings&sub=upgrade/up_fs'];
		} elseif ($this->foodsaver['rolle'] == Role::FOODSAVER) {
			$menu[] = ['name' => 'Werde ' . $this->translationHelper->s('rolle_2_' . $this->foodsaver['geschlecht']), 'href' => '/?page=settings&sub=upgrade/up_bip'];
		}
		$menu[] = ['name' => $this->translationHelper->s('delete_account'), 'href' => '/?page=settings&sub=deleteaccount'];
		$this->pageHelper->addContent($this->view->menu($menu, ['title' => $this->translationHelper->s('account_option'), 'active' => $this->getSub()]), CNT_LEFT);
	}

	public function sleeping()
	{
		if ($sleep = $this->settingsGateway->getSleepData($this->session->id())) {
			$this->pageHelper->addContent($this->view->sleepMode($sleep));
		}
	}

	public function quizsession()
	{
		if ($session = $this->quizSessionGateway->getExtendedUserSession($_GET['sid'], $this->session->id())) {
			$this->pageHelper->addContent($this->view->quizSession($session, $session['try_count'], $this->contentGateway));
		}
	}

	public function up_fs()
	{
		$quizRole = Role::FOODSAVER;
		if ($this->session->may()) {
			if ($quiz = $this->quizGateway->getQuiz($quizRole)) {
				$this->handleQuizStatus($quiz, $quizRole);
			}
		}
	}

	public function up_bip()
	{
		$quizRole = Role::STORE_MANAGER;
		if ($this->session->may() && $this->foodsaver['rolle'] > Role::FOODSHARER) {
			if (!$this->foodsaver['verified']) {
				$this->pageHelper->addContent($this->view->simpleContent($this->contentGateway->get(45)));
			} else {
				if ($quiz = $this->quizGateway->getQuiz($quizRole)) {
					$fsId = $this->session->id();
					if (!$this->quizSessionGateway->hasPassedQuiz($fsId, Role::FOODSAVER)) {
						$this->flashMessageHelper->info('Du darfst zunächst das Foodsaver Quiz machen');
						$this->routeHelper->go('/?page=settings&sub=upgrade/up_fs');
					}

					$this->handleQuizStatus($quiz, $quizRole);
				}
			}
		}
	}

	public function up_bot()
	{
		$quizRole = Role::AMBASSADOR;
		if ($this->session->may() && $this->foodsaver['rolle'] >= Role::STORE_MANAGER) {
			if ($quiz = $this->quizGateway->getQuiz($quizRole)) {
				$this->handleQuizStatus($quiz, $quizRole);
			} else {
				$this->pageHelper->addContent($this->v_utils->v_info('Fehler! Quizdaten Für Deine Rolle konnten nicht geladen werden. Bitte wende Dich an den IT-Support: <a href=mailto:' . SUPPORT_EMAIL . '>' . SUPPORT_EMAIL . '</a>'));
			}
		} else {
			switch ($this->foodsaver['rolle']) {
				case Role::FOODSHARER:
					$this->flashMessageHelper->info('Du musst erst Foodsaver werden');
					$this->routeHelper->go('/?page=settings&sub=upgrade/up_fs');
					break;

				case Role::FOODSAVER:
					$this->flashMessageHelper->info('Du musst erst BetriebsverantwortlicheR werden');
					$this->routeHelper->go('/?page=settings&sub=upgrade/up_bip');
					break;

				default:
					$this->routeHelper->go('/?page=settings');
					break;
			}
		}
	}

	private function handleQuizStatus(array $quiz, int $role): void
	{
		$fsId = $this->session->id();
		$desc = $this->contentGateway->get(12);
		$quizStatus = $this->quizSessionGateway->getQuizStatus($role, $fsId);
		switch ($quizStatus['status']) {
			case QuizStatus::NEVER_TRIED:
				$this->pageHelper->addContent($this->view->quizIndex($quiz, $desc));
				break;

			case QuizStatus::RUNNING:
				$this->pageHelper->addContent($this->view->quizContinue($quiz, $desc));
				break;

			case QuizStatus::PASSED:
				$this->confirmRole($role);
				break;

			case QuizStatus::FAILED:
				$failCount = $this->quizSessionGateway->countSessions($fsId, $role, SessionStatus::FAILED);
				$this->pageHelper->addContent($this->view->quizRetry($quiz, $desc, $failCount, 3));
				break;

			case QuizStatus::PAUSE:
				if ($role == Role::FOODSAVER) {
					$this->foodsaverGateway->riseRole($fsId, Role::FOODSHARER);
				}
				$lastTry = $this->quizSessionGateway->getLastTry($fsId, $role);
				$this->pageHelper->addContent($this->view->pause($quizStatus['wait'], $desc));
				break;

			case QuizStatus::PAUSE_ELAPSED:
				$this->pageHelper->addContent($this->view->quizIndex($quiz, $desc));
				break;

			default:
				$this->pageHelper->addContent($this->view->quizFailed($this->contentGateway->get(13)));
		}
	}

	private function confirmRole(int $role): void
	{
		switch ($role) {
			case Role::FOODSAVER:
				$this->confirm_fs();
				break;

			case Role::STORE_MANAGER:
				$this->confirm_bip();
				break;

			case Role::AMBASSADOR:
				$this->confirm_bot();
				break;

			default:
		}
	}

	private function confirm_fs()
	{
		$fsId = $this->session->id();
		if ($this->quizSessionGateway->hasPassedQuiz($fsId, Role::FOODSAVER)) {
			if ($this->isSubmitted()) {
				if (empty($_POST['accepted'])) {
					$check = false;
					$this->flashMessageHelper->error($this->translationHelper->s('not_rv_accepted'));
				} else {
					$this->session->set('hastodoquiz', false);
					$this->mem->delPageCache('/?page=dashboard', $fsId);
					if (!$this->session->may('fs')) {
						$this->foodsaverGateway->riseRole($fsId, Role::FOODSAVER);
					}
					$this->flashMessageHelper->info('Danke! Du bist jetzt Foodsaver');
					$this->routeHelper->go('/?page=relogin&url=' . urlencode('/?page=dashboard'));
				}
			}
			$cnt = $this->contentGateway->get(14);
			$rv = $this->contentGateway->get(30);
			$this->pageHelper->addContent($this->view->confirmFs($cnt, $rv));
		}
	}

	private function confirm_bip()
	{
		$fsId = $this->session->id();
		if ($this->quizSessionGateway->hasPassedQuiz($fsId, Role::STORE_MANAGER)) {
			if ($this->isSubmitted()) {
				if (empty($_POST['accepted'])) {
					$check = false;
					$this->flashMessageHelper->error($this->translationHelper->s('not_rv_accepted'));
				} else {
					$this->foodsaverGateway->riseRole($fsId, Role::STORE_MANAGER);
					$this->flashMessageHelper->info('Danke! Du bist jetzt Betriebsverantwortlicher');
					$this->routeHelper->go('/?page=relogin&url=' . urlencode('/?page=dashboard'));
				}
			}
			$cnt = $this->contentGateway->get(15);
			$rv = $this->contentGateway->get(31);
			$this->pageHelper->addContent($this->view->confirmBip($cnt, $rv));
		}
	}

	private function confirm_bot(): void
	{
		$this->pageHelper->addBread('Botschafter werden');
		$fsId = $this->session->id();
		if ($this->quizSessionGateway->hasPassedQuiz($fsId, Role::AMBASSADOR)) {
			$showform = true;

			if ($this->submitted()) {
				global $g_data;
				$g_data = $_POST;

				$isDataComplete = true;

				if (empty($_POST['about_me_public'])) {
					$isDataComplete = false;
					$this->flashMessageHelper->error('Deine Kurzbeschreibung ist leer');
				}

				if (!isset($_POST['rv_botschafter'])) {
					$isDataComplete = false;
					$this->flashMessageHelper->error($this->translationHelper->s('not_rv_accepted'));
				}

				if (empty((int)$_POST['bezirk'])) {
					$isDataComplete = false;
					$this->flashMessageHelper->error('Du hast keinen Bezirk gewählt, in dem Du Botschafter werden möchtest');
				}

				if ($isDataComplete) {
					$data = $this->dataHelper->unsetAll($_POST, ['new_bezirk']);
					$this->foodsaverGateway->updateProfile($fsId, $data);

					$this->pageHelper->addContent($this->v_utils->v_field(
						$this->v_utils->v_info($this->translationHelper->s('upgrade_bot_success')),
						$this->translationHelper->s('upgrade_request_send'),
						[
							'class' => 'ui-padding'
						]
					));

					$g_data = [];
					$showform = false;
				}
			}

			if ($showform) {
				$this->pageHelper->addJs('$("#upBotsch").on("submit", function(ev){
					check = true;
					if($("#bezirk").val() == 0)
					{
						check = false;
						error("Du musst einen Bezirk ausw&auml;hlen");
					}

					if(!check)
					{
						ev.preventDefault();
					}

				});');

				// Rechtsvereinbarung

				$rv = $this->contentGateway->get(32);

				$this->pageHelper->addContent(
					$this->view->confirmBot($this->contentGateway->get(16)) .

					$this->v_utils->v_form('upBotsch', [$this->v_utils->v_field(
						$this->v_utils->v_bezirkChooser('bezirk', $this->regionGateway->getRegion($this->session->getCurrentRegionId()), ['label' => 'In welcher Region möchtest Du Botschafter werden?']) .
						'<div style="display:none" id="bezirk-notAvail">' . $this->v_utils->v_form_text('new_bezirk') . '</div>' .
						$this->v_utils->v_form_select('time', ['values' => [
							['id' => 1, 'name' => '3-5 Stunden'],
							['id' => 2, 'name' => '5-8 Stunden'],
							['id' => 3, 'name' => '9-12 Stunden'],
							['id' => 4, 'name' => '13-15 Stunden'],
							['id' => 5, 'name' => '15-20 Stunden']
						]]) .
						$this->v_utils->v_form_textarea('about_me_public', ['desc' => 'Um möglichst transparent, aber auch offen, freundlich, seriös und einladend gegenüber den Lebensmittelbetrieben, den Foodsavern sowie allen, die bei foodsharing mitmachen wollen, aufzutreten, wollen wir neben Deinem Foto, Namen und Telefonnummer auch eine Beschreibung Deiner Person als Teil von foodsharing mit aufnehmen. Bitte fass Dich also relativ kurz, hier unsere Vorlage: https://foodsharing.de/ueber-uns Gerne kannst Du auch Deine Website, Projekt oder sonstiges erwähnen, was Du öffentlich an Informationen teilen möchtest, die vorteilhaft sind.']),

						'Botschafter werden',

						['class' => 'ui-padding']
					),

						$this->v_utils->v_field($rv['body'] . $this->v_utils->v_form_checkbox('rv_botschafter', ['required' => true, 'values' => [
								['id' => 1, 'name' => $this->translationHelper->s('rv_accept')]
							]]), $rv['title'], ['class' => 'ui-padding'])
					], ['submit' => 'Antrag auf Botschafterrolle verbindlich absenden'])
				);
			}
		}
	}

	public function deleteaccount()
	{
		$this->pageHelper->addBread($this->translationHelper->s('delete_account'));
		$this->pageHelper->addContent($this->view->delete_account($this->session->id()));
	}

	public function general()
	{
		$this->handle_edit();

		$data = $this->foodsaverGateway->getFoodsaver($this->session->id());

		$this->dataHelper->setEditData($data);

		$this->pageHelper->addContent($this->view->foodsaver_form());

		$this->pageHelper->addContent($this->picture_box(), CNT_RIGHT);
	}

	public function calendar()
	{
		$this->pageHelper->addBread($this->translationHelper->s('calendar'));
		$token = $this->generate_api_token($this->session->id());
		$this->pageHelper->addContent($this->view->settingsCalendar($token));
	}

	public function info()
	{
		$fsId = $this->session->id();
		global $g_data;
		if (isset($_POST['form_submit']) && $_POST['form_submit'] == 'settingsinfo') {
			$newsletter = 1;
			if ($_POST['newsletter'] != 1) {
				$newsletter = 0;
			}
			$infomail = 1;
			if ($_POST['infomail_message'] != 1) {
				$infomail = 0;
			}
			$fspIdsToUnfollow = [];
			$threadIdsToUnfollow = [];
			foreach ($_POST as $key => $infoType) {
				if (substr($key, 0, 11) == 'fairteiler_') {
					$foodSharePointId = (int)substr($key, 11);
					if (!empty($foodSharePointId)) {
						if ($infoType == InfoType::NONE) {
							$fspIdsToUnfollow[] = $foodSharePointId;
						} else {
							$this->foodSharePointGateway->updateInfoType($fsId, $foodSharePointId, $infoType);
						}
					}
				} elseif (substr($key, 0, 7) == 'thread_') {
					$themeId = (int)substr($key, 7);
					if (!empty($themeId)) {
						if ($infoType == InfoType::NONE) {
							$threadIdsToUnfollow[] = $themeId;
						} else {
							$this->forumFollowerGateway->updateInfoType($fsId, $themeId, $infoType);
						}
					}
				}
			}

			if (!empty($fspIdsToUnfollow)) {
				$this->foodSharePointGateway->unfollowFoodSharePoints($fsId, $fspIdsToUnfollow);
			}
			if (!empty($threadIdsToUnfollow)) {
				$fsId = $this->session->id();
				foreach ($threadIdsToUnfollow as $singleThreadId) {
					$this->forumFollowerGateway->unfollowThreadByEmail($fsId, $singleThreadId);
				}
			}

			if ($this->settingsGateway->saveInfoSettings($fsId, $newsletter, $infomail)) {
				$this->flashMessageHelper->info($this->translationHelper->s('changes_saved'));
			}
		}
		$this->pageHelper->addBread($this->translationHelper->s('settings_info'));

		$g_data = $this->foodsaverGateway->getSubscriptions($fsId);

		$foodSharePoints = $this->foodSharePointGateway->listFoodsaversFoodSharePoints($fsId);
		$threads = $this->forumFollowerGateway->getForumThreads($fsId);

		$this->pageHelper->addContent($this->view->settingsInfo($foodSharePoints, $threads));
	}

	public function handle_edit()
	{
		if ($this->submitted()) {
			$data = $this->dataHelper->getPostData();
			$data['stadt'] = $data['ort'];
			$check = true;

			if (!empty($data['homepage'])) {
				if (substr($data['homepage'], 0, 4) != 'http') {
					$data['homepage'] = 'http://' . $data['homepage'];
				}

				if (!$this->validUrl($data['homepage'])) {
					$check = false;
					$this->flashMessageHelper->error('Mit Deiner Homepage-URL stimmt etwas nicht');
				}
			}

			if ($check) {
				if ($oldFs = $this->foodsaverGateway->getFoodsaver($this->session->id())) {
					$logChangedFields = ['stadt', 'plz', 'anschrift', 'telefon', 'handy', 'geschlecht', 'geb_datum'];
					$this->settingsGateway->logChangedSetting($this->session->id(), $oldFs, $data, $logChangedFields);
				}

				if (!isset($data['bezirk_id'])) {
					$data['bezirk_id'] = $this->session->getCurrentRegionId();
				}
				if ($this->foodsaverGateway->updateProfile($this->session->id(), $data)) {
					try {
						$this->session->refreshFromDatabase();
						$this->flashMessageHelper->info($this->translationHelper->s('foodsaver_edit_success'));
					} catch (\Exception $e) {
						$this->routeHelper->goPage('logout');
					}
				} else {
					$this->flashMessageHelper->error($this->translationHelper->s('error'));
				}
			}
		}
	}

	private function validUrl($url)
	{
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			return false;
		}

		return true;
	}

	private function picture_box(): string
	{
		$photo = $this->foodsaverGateway->getPhotoFileName($this->session->id());

		return $this->view->picture_box($photo);
	}

	private function handle_newmail()
	{
		if ($email = $this->settingsGateway->getNewMail($this->session->id(), $_GET['newmail'])) {
			$this->pageHelper->addJs("ajreq('changemail3');");
		} else {
			$this->flashMessageHelper->info($this->translationHelper->s('mailchange_failed'));
		}
	}

	private function upgrade()
	{
		/* This needs to be here for routing of upgrade/ to work. Do not remove! */
	}

	/**
	 * Creates and saves a new API token for given user.
	 *
	 * @param int $fsId Foodsaver ID
	 *
	 * @return false in case of error or weak algorithm, generated token otherwise
	 */
	private function generate_api_token(int $fsId): string
	{
		if ($token = bin2hex(openssl_random_pseudo_bytes(10))) {
			$this->settingsGateway->saveApiToken($fsId, $token);

			return $token;
		}

		return false;
	}
}
