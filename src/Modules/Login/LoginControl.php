<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Services\SearchService;
use Mobile_Detect;

class LoginControl extends Control
{
	private $searchService;

	public function __construct(LoginModel $model, LoginView $view, SearchService $searchService)
	{
		$this->model = $model;
		$this->view = $view;
		$this->searchService = $searchService;

		parent::__construct();
	}

	public function unsubscribe()
	{
		$this->func->addTitle('Newsletter Abmeldung');
		$this->func->addBread('Newsletter Abmeldung');
		if (isset($_GET['e']) && $this->func->validEmail($_GET['e'])) {
			$this->model->update('UPDATE `' . PREFIX . "foodsaver` SET newsletter=0 WHERE email='" . $this->model->safe($_GET['e']) . "'");
			$this->func->addContent($this->v_utils->v_info('Du wirst nun keine weiteren Newsletter von uns erhalten', 'Erfolg!'));
			file_put_contents('../unsubscribe.txt', $_GET['e'] . "\n", FILE_APPEND);
		}
	}

	public function index()
	{
		if (!S::may()) {
			if (!isset($_GET['sub'])) {
				if (isset($_POST['email_adress'])) {
					$this->handleLogin();
				}
				$ref = false;
				if (isset($_GET['ref'])) {
					$ref = urldecode($_GET['ref']);
				}
				$this->func->addContent($this->view->login($ref));
			}
		} else {
			if (!isset($_GET['sub']) || $_GET['sub'] != 'unsubscribe') {
				$this->func->go('/?page=dashboard');
			}
		}
	}

	public function activate()
	{
		if ($this->model->activate($_GET['e'], $_GET['t'])) {
			$this->func->info($this->func->s('activation_success'));
			$this->func->goPage('login');
		} else {
			$this->func->error($this->func->s('activation_failed'));
			$this->func->goPage('login');
		}
	}

	private function handleLogin()
	{
		if ($this->model->login($_POST['email_adress'], $_POST['password'])) {
			$token = $this->searchService->writeSearchIndexToDisk(S::user('token'));

			if (isset($_POST['ismob'])) {
				$_SESSION['mob'] = (int)$_POST['ismob'];
			}

			$mobdet = new Mobile_Detect();
			if ($mobdet->isMobile()) {
				$_SESSION['mob'] = 1;
			}

			if ((isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], URL_INTERN) !== false) || isset($_GET['logout'])) {
				if (isset($_GET['ref'])) {
					$this->func->go(urldecode($_GET['ref']));
				}
				$this->func->go(str_replace('/?page=login&logout', '/?page=dashboard', $_SERVER['HTTP_REFERER']));
			} else {
				$this->func->go('/?page=dashboard');
			}
		} else {
			$this->func->error('Falsche Zugangsdaten');
		}
	}

	public function passwordReset()
	{
		$k = false;

		if (isset($_GET['k'])) {
			$k = strip_tags($_GET['k']);
		}

		$this->func->addTitle('Password zurücksetzen');
		$this->func->addBread('Passwort zurücksetzen');

		if (isset($_POST['email']) || isset($_GET['m'])) {
			$mail = '';
			if (isset($_GET['m'])) {
				$mail = $_GET['m'];
			} else {
				$mail = $_POST['email'];
			}
			if (!$this->func->validEmail($mail)) {
				$this->func->error('Sorry! Hast Du Dich vielleicht bei Deiner E-Mail-Adresse vertippt?');
			} else {
				if ($this->model->addPassRequest($mail)) {
					$this->func->info('Alles klar! Dir wurde ein Link zum Passwortändern per E-Mail zugeschickt.');
				} else {
					$this->func->error('Sorry, diese E-Mail-Adresse ist uns nicht bekannt.');
				}
			}
		}

		if ($k !== false && $this->model->checkResetKey($k)) {
			if ($this->model->checkResetKey($k)) {
				if (isset($_POST['pass1']) && isset($_POST['pass2'])) {
					if ($_POST['pass1'] == $_POST['pass2']) {
						$check = true;
						if ($this->model->newPassword($_POST)) {
							$this->func->success('Prima, Dein Passwort wurde erfolgreich geändert. Du kannst Dich jetzt Dich einloggen.');
						} elseif (strlen($_POST['pass1']) < 5) {
							$check = false;
							$this->func->error('Sorry, Dein gewähltes Passwort ist zu kurz.');
						} elseif (!$this->model->checkResetKey($_POST['k'])) {
							$check = false;
							$this->func->error('Sorry, Du hast zu lang gewartet. Bitte beantrage noch einmal ein neues Passwort!');
						} else {
							$check = false;
							$this->func->error('Sorry, es gibt ein Problem mir Deinen Daten. Ein Administrator wurde informiert.');
							/*
							$this->func->tplMail(11, 'kontakt@prographix.de',array(
								'data' => '<pre>'.print_r($_POST,true).'</pre>'
							));
							*/
						}

						if ($check) {
							$this->func->go('/?page=login');
						}
					} else {
						$this->func->error('Sorry, die Passwörter stimmen nicht überein.');
					}
				}
				$this->func->addJs('$("#pass1").val("");');
				$this->func->addContent($this->view->newPasswordForm($k));
			} else {
				$this->func->error('Sorry, Du hast ein bisschen zu lange gewartet. Bitte beantrage ein neues Passwort!');
				$this->func->addContent($this->view->passwordRequest(), CNT_LEFT);
			}
		} else {
			$this->func->addContent($this->view->passwordRequest());
		}
	}
}
