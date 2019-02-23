<?php

namespace Foodsharing\Modules\Settings;

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Quiz\QuizModel;

class SettingsControl extends Control
{
	private $gateway;
	private $foodsaver;
	private $quizModel;
	private $contentGateway;
	private $foodsaverGateway;

	public function __construct(SettingsModel $model, SettingsView $view, SettingsGateway $gateway, QuizModel $quizModel, ContentGateway $contentGateway, FoodsaverGateway $foodsaverGateway)
	{
		$this->model = $model;
		$this->view = $view;
		$this->gateway = $gateway;
		$this->quizModel = $quizModel;
		$this->contentGateway = $contentGateway;
		$this->foodsaverGateway = $foodsaverGateway;

		parent::__construct();

		if (!$this->session->may()) {
			$this->func->goLogin();
		}

		if (isset($_GET['newmail'])) {
			$this->handle_newmail();
		}

		$this->foodsaver = $this->model->getValues(array('rolle', 'email', 'name', 'nachname', 'geschlecht', 'verified'), 'foodsaver', $this->session->id());

		if (!isset($_GET['sub'])) {
			$this->func->go('/?page=settings&sub=general');
		}

		$this->pageCompositionHelper->addTitle($this->func->s('settings'));
	}

	public function index()
	{
		$this->pageCompositionHelper->addBread('Einstellungen', '/?page=settings');

		$menu = array(
			array('name' => $this->func->s('settings_general'), 'href' => '/?page=settings&sub=general'),
			array('name' => $this->func->s('settings_info'), 'href' => '/?page=settings&sub=info')
		);

		$menu[] = array('name' => $this->func->s('bcard'), 'href' => '/?page=bcard');
		//$menu[] = array('name' => $this->func->s('calendar'), 'href' => '/?page=settings&sub=calendar');

		$this->pageCompositionHelper->addContent($this->view->menu($menu, array('title' => $this->func->s('settings'), 'active' => $this->getSub())), CNT_LEFT);

		$menu = array();
		$menu[] = array('name' => $this->func->s('sleeping_user'), 'href' => '/?page=settings&sub=sleeping');
		$menu[] = array('name' => 'E-Mail-Adresse ändern', 'click' => 'ajreq(\'changemail\');return false;');

		if ($this->foodsaver['rolle'] == 0) {
			$menu[] = array('name' => 'Werde ' . $this->func->s('rolle_1_' . $this->foodsaver['geschlecht']), 'href' => '/?page=settings&sub=upgrade/up_fs');
		} elseif ($this->foodsaver['rolle'] == 1) {
			$menu[] = array('name' => 'Werde ' . $this->func->s('rolle_2_' . $this->foodsaver['geschlecht']), 'href' => '/?page=settings&sub=upgrade/up_bip');
		}
		$menu[] = array('name' => $this->func->s('delete_account'), 'href' => '/?page=settings&sub=deleteaccount');
		$this->pageCompositionHelper->addContent($this->view->menu($menu, array('title' => $this->func->s('account_option'), 'active' => $this->getSub())), CNT_LEFT);
	}

	public function sleeping()
	{
		if ($sleep = $this->model->getSleepData()) {
			$this->pageCompositionHelper->addContent($this->view->sleepMode($sleep));
		}
	}

	public function up_bip()
	{
		if ($this->session->may() && $this->foodsaver['rolle'] > 0) {
			if (!$this->foodsaver['verified']) {
				$this->pageCompositionHelper->addContent($this->view->simpleContent($this->contentGateway->get(45)));
			} else {
				if (($status = $this->quizModel->getQuizStatus(2)) && ($quiz = $this->quizModel->getQuiz(2))) {
					if ((int)$this->model->qOne('SELECT COUNT(id) FROM fs_quiz_session WHERE quiz_id = 1 AND status = 1 AND foodsaver_id = ' . (int)$this->session->id()) == 0) {
						$this->func->info('Du darfst zunächst das Foodsaver Quiz machen');
						$this->func->go('/?page=settings&sub=upgrade/up_fs');
					}
					$desc = $this->contentGateway->get(12);

					// Quiz wurde noch gar nicht probiert
					if ($status['times'] == 0) {
						$this->pageCompositionHelper->addContent($this->view->quizIndex($quiz, $desc));
					} // quiz ist bereits bestanden
					elseif ($status['cleared'] > 0) {
						return $this->confirm_bip();
					} // es läuft ein quiz weitermachen
					elseif ($status['running'] > 0) {
						$this->pageCompositionHelper->addContent($this->view->quizContinue($quiz, $desc));
					} // Quiz wurde shcon probiert aber noche keine 3x nicht bestanden
					elseif ($status['failed'] < 3) {
						$this->pageCompositionHelper->addContent($this->view->quizRetry($quiz, $desc, $status['failed'], 3));
					} // 3x nicht bestanden 30 Tage Lernpause
					elseif ($status['failed'] == 3 && (time() - $status['last_try']) < (86400 * 30)) {
						$days_to_wait = ((time() - $status['last_try']) - (86400 * 30) / 30);

						return $this->view->pause($days_to_wait, $desc);
					} // Lernpause vorbei noch keine weiteren 2 Fehlversuche
					elseif ($status['failed'] >= 3 && $status['failed'] < 5 && (time() - $status['last_try']) >= (86400 * 14)) {
						$this->pageCompositionHelper->addContent($this->view->quizIndex($quiz, $desc));
					} // hat alles nichts genützt
					else {
						$this->pageCompositionHelper->addContent($this->view->quizFailed($this->contentGateway->get(13)));
					}
				}
			}
		}
	}

	public function quizsession()
	{
		if ($session = $this->model->getQuizSession($_GET['sid'])) {
			$this->pageCompositionHelper->addContent($this->view->quizSession($session, $session['try_count'], $this->contentGateway));
		}
	}

	public function up_fs()
	{
		if ($this->session->may()) {
			if (($status = $this->quizModel->getQuizStatus(1)) && ($quiz = $this->quizModel->getQuiz(1))) {
				$desc = $this->contentGateway->get(12);

				// Quiz wurde noch gar nicht probiert
				if ($status['times'] == 0) {
					$this->pageCompositionHelper->addContent($this->view->quizIndex($quiz, $desc));
				} // quiz ist bereits bestanden
				elseif ($status['cleared'] > 0) {
					return $this->confirm_fs();
				} // es läuft ein quiz weitermachen
				elseif ($status['running'] > 0) {
					$this->pageCompositionHelper->addContent($this->view->quizContinue($quiz, $desc));
				} // Quiz wurde shcon probiert aber noche keine 3x nicht bestanden
				elseif ($status['failed'] < 3) {
					$this->pageCompositionHelper->addContent($this->view->quizRetry($quiz, $desc, $status['failed'], 3));
				} // 3x nicht bestanden 30 Tage Lernpause
				elseif ($status['failed'] == 3 && (time() - $status['last_try']) < (86400 * 30)) {
					$this->model->updateRole(0, $this->foodsaver['rolle']);
					$days_to_wait = ((time() - $status['last_try']) - (86400 * 30) / 30);

					return $this->view->pause($days_to_wait, $desc);
				} // Lernpause vorbei noch keine weiteren 2 Fehlversuche
				elseif ($status['failed'] >= 3 && $status['failed'] < 5 && (time() - $status['last_try']) >= (86400 * 14)) {
					$this->pageCompositionHelper->addContent($this->view->quizIndex($quiz, $desc));
				} // hat alles nichts genützt
				else {
					$this->pageCompositionHelper->addContent($this->view->quizFailed($this->contentGateway->get(13)));
				}
			}
		}
	}

	public function up_bot()
	{
		if ($this->session->may() && $this->foodsaver['rolle'] >= 2) {
			if (($status = $this->quizModel->getQuizStatus(3)) && ($quiz = $this->quizModel->getQuiz(3))) {
				$desc = $this->contentGateway->get(12);

				// Quiz wurde noch gar nicht probiert
				if ($status['times'] == 0) {
					$this->pageCompositionHelper->addContent($this->view->quizIndex($quiz, $desc));
				} // es läuft ein quiz weitermachen
				elseif ($status['running'] > 0) {
					$this->pageCompositionHelper->addContent($this->view->quizContinue($quiz, $desc));
				} // quiz ist bereits bestanden
				elseif ($status['cleared'] > 0) {
					return $this->confirm_bot();
				} // Quiz wurde shcon probiert aber noche keine 3x nicht bestanden
				elseif ($status['failed'] < 3) {
					$this->pageCompositionHelper->addContent($this->view->quizRetry($quiz, $desc, $status['failed'], 3));
				} // 3x nicht bestanden 30 Tage Lernpause
				elseif ($status['failed'] == 3 && (time() - $status['last_try']) < (86400 * 30)) {
					$days_to_wait = ((time() - $status['last_try']) - (86400 * 30) / 30);

					return $this->view->pause($days_to_wait, $desc);
				} // Lernpause vorbei noch keine weiteren 2 Fehlversuche
				elseif ($status['failed'] >= 3 && $status['failed'] < 5 && (time() - $status['last_try']) >= (86400 * 14)) {
					$this->pageCompositionHelper->addContent($this->view->quizIndex($quiz, $desc));
				} // hat alles nichts genützt
				else {
					return $this->view->quizFailed($this->contentGateway->get(13));
				}
			} else {
				$this->pageCompositionHelper->addContent($this->v_utils->v_info('Fehler! Quizdaten Für Deine Rolle konnten nicht geladen werden. Bitte wende Dich an den IT-Support:<a href=mailto:' . SUPPORT_EMAIL . '"">' . SUPPORT_EMAIL . '</a>'));
			}
		} else {
			switch ($this->foodsaver['rolle']) {
				case 0:
					$this->func->info('Du musst erst Foodsaver werden');
					$this->func->go('/?page=settings&sub=upgrade/up_fs');
					break;

				case 1:
					$this->func->info('Du musst erst BetriebsverantwortlicheR werden');
					$this->func->go('/?page=settings&sub=upgrade/up_bip');
					break;

				default:
					$this->func->go('/?page=settings');
					break;
			}
		}
	}

	private function confirm_fs()
	{
		if ($this->model->hasQuizCleared(1)) {
			if ($this->isSubmitted()) {
				if (empty($_POST['accepted'])) {
					$check = false;
					$this->func->error($this->func->s('not_rv_accepted'));
				} else {
					$this->session->set('hastodoquiz', false);
					$this->mem->delPageCache('/?page=dashboard', $this->session->id());
					if (!$this->session->may('fs')) {
						$this->model->updateRole(1, $this->foodsaver['rolle']);
					}
					$this->func->info('Danke! Du bist jetzt Foodsaver');
					$this->func->go('/?page=relogin&url=' . urlencode('/?page=dashboard'));
				}
			}
			$cnt = $this->contentGateway->get(14);
			$rv = $this->contentGateway->get(30);
			$this->pageCompositionHelper->addContent($this->view->confirmFs($cnt, $rv));
		}
	}

	private function confirm_bip()
	{
		if ($this->model->hasQuizCleared(2)) {
			if ($this->isSubmitted()) {
				if (empty($_POST['accepted'])) {
					$check = false;
					$this->func->error($this->func->s('not_rv_accepted'));
				} else {
					$this->model->updateRole(2, $this->foodsaver['rolle']);
					$this->func->info('Danke! Du bist jetzt Betriebsverantwortlicher');
					$this->func->go('/?page=relogin&url=' . urlencode('/?page=dashboard'));
				}
			}
			$cnt = $this->contentGateway->get(15);
			$rv = $this->contentGateway->get(31);
			$this->pageCompositionHelper->addContent($this->view->confirmBip($cnt, $rv));
		}
	}

	private function confirm_bot()
	{
		$this->pageCompositionHelper->addBread('Botschafter werden');

		if ($this->model->hasQuizCleared(3)) {
			$showform = true;

			$rolle = 3;

			if ($this->func->submitted()) {
				global $g_data;
				$g_data = $_POST;

				$check = true;
				if (!isset($_POST['photo_public'])) {
					$check = false;
					$this->func->error('Du musst zustimmen, dass wir Dein Foto veröffentlichen dürfen.');
				}

				if (empty($_POST['about_me_public'])) {
					$check = false;
					$this->func->error('Deine Kurzbeschreibung ist leer');
				}

				if (!isset($_POST['tel_public'])) {
					$check = false;
					$this->func->error('Du musst zustimmen, dass wir Deine Telefonnummer veröffentlichen.');
				}

				if (!isset($_POST['rv_botschafter'])) {
					$check = false;
					$this->func->error($this->func->s('not_rv_accepted'));
				}

				if ((int)$_POST['bezirk'] == 0) {
					$check = false;
					$this->func->error('Du hast keinen Bezirk gewählt, in dem Du Botschafter werden möchtest');
				}

				if ($check) {
					$data = $this->func->unsetAll($_POST, array('photo_public', 'new_bezirk'));
					$this->model->updateFields($data, 'fs_foodsaver', $this->session->id());

					$this->pageCompositionHelper->addContent($this->v_utils->v_field(
						$this->v_utils->v_info($this->func->s('upgrade_bot_success')),
						$this->func->s('upgrade_request_send'),
						array(
							'class' => 'ui-padding'
						)
					));

					$g_data = array();
					$showform = false;
				}
			}

			if ($showform) {
				$this->pageCompositionHelper->addJs('$("#upBotsch").on("submit", function(ev){
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

				$this->pageCompositionHelper->addContent(
					$this->view->confirmBot($this->contentGateway->get(16)) .

					$this->v_utils->v_form('upBotsch', array($this->v_utils->v_field(
						$this->v_utils->v_bezirkChooser('bezirk', $this->func->getBezirk(), array('label' => 'In welcher Region möchtest Du Botschafter werden?')) .
						'<div style="display:none" id="bezirk-notAvail">' . $this->v_utils->v_form_text('new_bezirk') . '</div>' .
						$this->v_utils->v_form_select('time', array('values' => array(
							array('id' => 1, 'name' => '3-5 Stunden'),
							array('id' => 2, 'name' => '5-8 Stunden'),
							array('id' => 3, 'name' => '9-12 Stunden'),
							array('id' => 4, 'name' => '13-15 Stunden'),
							array('id' => 5, 'name' => '15-20 Stunden')
						))) .
						$this->v_utils->v_form_radio('photo_public', array('required' => true, 'values' => array(
							array('id' => 1, 'name' => 'Ich bin einverstanden das Mein Name und Mein Foto veröffentlicht werden'),
							array('id' => 2, 'name' => 'Bitte NUR meinen Namen veröffentlichen')
						))) .
						$this->v_utils->v_form_checkbox('tel_public', array('desc' => 'Neben Deinem vollem Namen (und eventuell Foto) ist es für
										Händler, foodsharing-Freiwillge, Interessierte und die Presse
										einfacher und direkter, Dich neben der für Deine
										Region/Stadt/Bezirk zugewiesenen Botschafter-E-Mail-Adresse (z. B. mainz@' . PLATFORM_MAILBOX_HOST . ')
										über Deine Festnetz- bzw. Handynummer zu erreichen. Bitte gib
										hier alle Nummern an, die wir veröffentlichen dürfen und am
										besten noch gewünschte Anrufzeiten.', 'required' => true, 'values' => array(
							array('id' => 1, 'name' => 'Ich bin einverstanden, dass meine Telefonnummer veröffentlicht wird.')
						))) .
						$this->v_utils->v_form_textarea('about_me_public', array('desc' => 'Um möglichst transparent, aber auch offen, freundlich, seriös und einladend gegenüber den Lebensmittelbetrieben, den Foodsavern sowie allen, die bei foodsharing mitmachen wollen, aufzutreten, wollen wir neben Deinem Foto, Namen und Telefonnummer auch eine Beschreibung Deiner Person als Teil von foodsharing mit aufnehmen. Bitte fass Dich also relativ kurz, hier unsere Vorlage: https://foodsharing.de/ueber-uns Gerne kannst Du auch Deine Website, Projekt oder sonstiges erwähnen, was Du öffentlich an Informationen teilen möchtest, die vorteilhaft sind.')),

						'Botschafter werden',

						array('class' => 'ui-padding')
					),

						$this->v_utils->v_field($rv['body'] . $this->v_utils->v_form_checkbox('rv_botschafter', array('required' => true, 'values' => array(
								array('id' => 1, 'name' => $this->func->s('rv_accept'))
							))), $rv['title'], array('class' => 'ui-padding'))
					), array('submit' => 'Antrag auf Botschafterrolle verbindlich absenden'))
				);
			}
		}
	}

	public function deleteaccount()
	{
		$this->pageCompositionHelper->addBread($this->func->s('delete_account'));
		$this->pageCompositionHelper->addContent($this->view->delete_account($this->session->id()));
	}

	public function general()
	{
		$this->handle_edit();

		$data = $this->foodsaverGateway->getOne_foodsaver($this->session->id());

		$this->func->setEditData($data);

		$this->pageCompositionHelper->addContent($this->view->foodsaver_form());

		$this->pageCompositionHelper->addContent($this->picture_box(), CNT_RIGHT);
	}

	public function calendar()
	{
		$this->pageCompositionHelper->addBread($this->func->s('calendar'));
		$token = $this->generate_api_token($this->session->id());
		$this->pageCompositionHelper->addContent($this->view->settingsCalendar($token));
	}

	public function info()
	{
		global $g_data;
		if (isset($_POST['form_submit']) && $_POST['form_submit'] == 'settingsinfo') {
			$nl = 1;
			if ($_POST['newsletter'] != 1) {
				$nl = 0;
			}
			$infomail = 1;
			if ($_POST['infomail_message'] != 1) {
				$infomail = 0;
			}
			$unfollow_fairteiler = array();
			$unfollow_thread = array();
			foreach ($_POST as $key => $p) {
				if (substr($key, 0, 11) == 'fairteiler_') {
					$ft = (int)substr($key, 11);
					if ($ft > 0) {
						if ($p == 0) {
							$unfollow_fairteiler[] = $ft;
						} elseif ($p < 4) {
							$this->model->updateFollowFairteiler($ft, $p);
						}
					}
				} elseif (substr($key, 0, 7) == 'thread_') {
					$ft = (int)substr($key, 7);
					if ($ft > 0) {
						if ($p == 0) {
							$unfollow_thread[] = $ft;
						} elseif ($p < 4) {
							$this->model->updateFollowThread($ft, $p);
						}
					}
				}
			}

			if (!empty($unfollow_fairteiler)) {
				$this->model->unfollowFairteiler($unfollow_fairteiler);
			}
			if (!empty($unfollow_thread)) {
				$this->model->unfollowThread($unfollow_thread);
			}

			if ($this->model->saveInfoSettings($nl, $infomail)) {
				$this->func->info($this->func->s('changes_saved'));
			}
		}
		$this->pageCompositionHelper->addBread($this->func->s('settings_info'));

		$g_data = $this->model->getValues(array('infomail_message', 'newsletter'), 'foodsaver', $this->session->id());

		$fairteiler = $this->model->getFairteiler();
		$threads = $this->model->getForumThreads();

		$this->pageCompositionHelper->addContent($this->view->settingsInfo($fairteiler, $threads));
	}

	public function handle_edit()
	{
		if ($this->func->submitted()) {
			$data = $this->func->getPostData();
			$data['stadt'] = $data['ort'];
			$check = true;

			if (!empty($data['homepage'])) {
				if (substr($data['homepage'], 0, 4) != 'http') {
					$data['homepage'] = 'http://' . $data['homepage'];
				}

				if (!$this->validUrl($data['homepage'])) {
					$check = false;
					$this->func->error('Mit Deiner Homepage URL stimmt etwas nicht');
				}
			}

			if (!empty($data['github'])) {
				if (substr($data['github'], 0, 19) != 'https://github.com/') {
					$data['github'] = 'https://github.com/' . $data['github'];
				}

				if (!$this->validUrl($data['github'])) {
					$check = false;
					$this->func->error('Mit Deiner github URL stimmt etwas nicht');
				}
			}

			if (!empty($data['twitter'])) {
				if (substr($data['twitter'], 0, 20) != 'https://twitter.com/') {
					$data['twitter'] = 'https://twitter.com/' . $data['twitter'];
				}

				if (!$this->validUrl($data['twitter'])) {
					$check = false;
					$this->func->error('Mit Deiner twitter URL stimmt etwas nicht');
				}
			}

			if (!empty($data['tox'])) {
				$data['tox'] = preg_replace('/[^0-9A-Z]/', '', $data['tox']);
			}

			if ($check) {
				if ($oldFs = $this->foodsaverGateway->getOne_foodsaver($this->session->id())) {
					$logChangedFields = array('stadt', 'plz', 'anschrift', 'telefon', 'handy', 'geschlecht', 'geb_datum');
					$this->gateway->logChangedSetting($this->session->id(), $oldFs, $data, $logChangedFields);
				}

				if (!isset($data['bezirk_id'])) {
					$data['bezirk_id'] = $this->session->getCurrentBezirkId();
				}
				if ($this->foodsaverGateway->updateProfile($this->session->id(), $data)) {
					$this->session->refreshFromDatabase();
					$this->func->info($this->func->s('foodsaver_edit_success'));
				} else {
					$this->func->error($this->func->s('error'));
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

	public function picture_box()
	{
		$photo = $this->foodsaverGateway->getPhoto($this->session->id());

		if (!(file_exists('images/thumb_crop_' . $photo))) {
			$p_cnt = $this->v_utils->v_photo_edit('img/portrait.png');
		} else {
			$p_cnt = $this->v_utils->v_photo_edit('images/thumb_crop_' . $photo);
		}

		return $this->v_utils->v_field($p_cnt, 'Dein Foto');
	}

	private function handle_newmail()
	{
		if ($email = $this->model->getNewMail($_GET['newmail'])) {
			$this->pageCompositionHelper->addJs("ajreq('changemail3');");
		}
	}

	private function upgrade()
	{
		/* This needs to be here for routing of upgrade/ to work. Do not remove! */
	}

	/** Creates and saves a new API token for given user
	 * @param $fs Foodsaver ID
	 *
	 * @return false in case of error or weak algorithm, generated token otherwise
	 */
	private function generate_api_token($fs)
	{
		$token = bin2hex(openssl_random_pseudo_bytes(10, $strong));
		if (!$strong || $token === false) {
			return false;
		}

		$this->model->insert('INSERT INTO fs_apitoken (foodsaver_id, token) VALUES (' . (int)$fs . ', "' . $token . '")');

		return $token;
	}
}
