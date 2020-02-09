<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Settings\SettingsGateway;
use Mobile_Detect;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginControl extends Control
{
	/**
	 * @var FormFactoryBuilder
	 */
	private $formFactory;

	private $loginGateway;
	private $settingsGateway;
	private $contentGateway;

	public function __construct(LoginView $view, LoginGateway $loginGateway, ContentGateway $contentGateway, SettingsGateway $settingsGateway)
	{
		$this->view = $view;
		$this->loginGateway = $loginGateway;
		$this->settingsGateway = $settingsGateway;
		$this->contentGateway = $contentGateway;

		parent::__construct();
	}

	/**
	 * @required
	 */
	public function setFormFactory(FormFactoryBuilder $formFactory): void
	{
		$this->formFactory = $formFactory;
	}

	public function unsubscribe()
	{
		$this->pageHelper->addTitle('Newsletter Abmeldung');
		$this->pageHelper->addBread('Newsletter Abmeldung');
		if (isset($_GET['e']) && $this->emailHelper->validEmail($_GET['e'])) {
			$this->settingsGateway->unsubscribeNewsletter($_GET['e']);
			$this->pageHelper->addContent($this->v_utils->v_info('Du wirst nun keine weiteren Newsletter von uns erhalten', 'Erfolg!'));
		}
	}

	public function index(Request $request, Response $response)
	{
		if (!$this->session->may()) {
			$has_subpage = $request->query->has('sub');

			$form = $this->formFactory->getFormFactory()->create(LoginForm::class);
			$form->handleRequest($request);

			if (!$has_subpage) {
				if ($form->isSubmitted() && $form->isValid()) {
					$this->handleLogin($request);
				}

				$ref = false;
				if (isset($_GET['ref'])) {
					$ref = urldecode($_GET['ref']);
				}

				$action = '/?page=login';
				if ($ref) {
					$action = '/?page=login&ref=' . urlencode($ref);
				} elseif (!isset($_GET['ref'])) {
					$action = '/?page=login&ref=' . urlencode($_SERVER['REQUEST_URI']);
				}

				$params = [
					'action' => $action,
					'form' => $form->createView(),
				];

				$response->setContent($this->render('pages/Login/page.twig', $params));
			}
		} else {
			if (!isset($_GET['sub']) || $_GET['sub'] != 'unsubscribe') {
				$this->routeHelper->go('/?page=dashboard');
			}
		}
	}

	public function activate()
	{
		if ($this->loginGateway->activate($_GET['e'], $_GET['t'])) {
			$this->flashMessageHelper->info($this->translationHelper->s('activation_success'));
			$this->routeHelper->goPage('login');
		} else {
			$this->flashMessageHelper->error($this->translationHelper->s('activation_failed'));
			$this->routeHelper->goPage('login');
		}
	}

	private function handleLogin(Request $request): void
	{
		$email_address = $request->request->get('login_form')['email_address'];
		$password = $request->request->get('login_form')['password'];

		$fs_id = $this->loginGateway->login($email_address, $password);

		if ($fs_id === null) {
			$this->flashMessageHelper->error($this->translationHelper->s('wrong_credentials'));

			return;
		}

		$this->session->login($fs_id);

		if (isset($_POST['ismob'])) {
			$_SESSION['mob'] = (int)$_POST['ismob'];
		}

		$mobileDetect = new Mobile_Detect();
		if ($mobileDetect->isMobile()) {
			$_SESSION['mob'] = 1;
		}

		if (isset($_GET['ref'])) {
			$this->routeHelper->go(urldecode($_GET['ref']));
		}
		$this->routeHelper->go('/?page=dashboard');
	}

	public function passwordReset()
	{
		$k = false;

		if (isset($_GET['k'])) {
			$k = strip_tags($_GET['k']);
		}

		$this->pageHelper->addTitle('Password zurücksetzen');
		$this->pageHelper->addBread('Passwort zurücksetzen');

		if (isset($_POST['email']) || isset($_GET['m'])) {
			$mail = '';
			if (isset($_GET['m'])) {
				$mail = $_GET['m'];
			} else {
				$mail = $_POST['email'];
			}
			if (!$this->emailHelper->validEmail($mail)) {
				$this->flashMessageHelper->error('Sorry! Hast Du Dich vielleicht bei Deiner E-Mail-Adresse vertippt?');
			} else {
				if ($this->loginGateway->addPassRequest($mail)) {
					$this->flashMessageHelper->info('Alles klar! Dir wurde ein Link zum Passwortändern per E-Mail zugeschickt.');
				} else {
					$this->flashMessageHelper->error('Sorry, diese E-Mail-Adresse ist uns nicht bekannt.');
				}
			}
		}

		if ($k !== false && $this->loginGateway->checkResetKey($k)) {
			if ($this->loginGateway->checkResetKey($k)) {
				if (isset($_POST['pass1'], $_POST['pass2'])) {
					if ($_POST['pass1'] == $_POST['pass2']) {
						$check = true;
						if ($this->loginGateway->newPassword($_POST)) {
							$this->view->success('Prima, Dein Passwort wurde erfolgreich geändert. Du kannst Dich jetzt Dich einloggen.');
						} elseif (strlen($_POST['pass1']) < 5) {
							$check = false;
							$this->flashMessageHelper->error('Sorry, Dein gewähltes Passwort ist zu kurz.');
						} elseif (!$this->loginGateway->checkResetKey($_POST['k'])) {
							$check = false;
							$this->flashMessageHelper->error('Sorry, Du hast zu lang gewartet. Bitte beantrage noch einmal ein neues Passwort!');
						} else {
							$check = false;
							$this->flashMessageHelper->error('Sorry, es gibt ein Problem mir Deinen Daten. Ein Administrator wurde informiert.');
							/*
							$this->emailHelper->tplMail(11, 'kontakt@prographix.de',array(
								'data' => '<pre>'.print_r($_POST,true).'</pre>'
							));
							*/
						}

						if ($check) {
							$this->routeHelper->go('/?page=login');
						}
					} else {
						$this->flashMessageHelper->error('Sorry, die Passwörter stimmen nicht überein.');
					}
				}
				$this->pageHelper->addJs('$("#pass1").val("");');
				$this->pageHelper->addContent($this->view->newPasswordForm($k));
			} else {
				$this->flashMessageHelper->error('Sorry, Du hast ein bisschen zu lange gewartet. Bitte beantrage ein neues Passwort!');
				$this->pageHelper->addContent($this->view->passwordRequest(), CNT_LEFT);
			}
		} else {
			$this->pageHelper->addContent($this->view->passwordRequest());
		}
	}
}
