<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Modules\Core\Control;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mobile_Detect;

class LoginControl extends Control
{
	/**
	 * @var FormFactoryBuilder
	 */
	private $formFactory;

	private $loginGateway;

	public function __construct(LoginModel $model, LoginView $view, LoginGateway $loginGateway)
	{
		$this->model = $model;
		$this->view = $view;
		$this->loginGateway = $loginGateway;

		parent::__construct();
	}

	/**
	 * @required
	 *
	 * @param FormFactoryBuilder $formFactory
	 */
	public function setFormFactory(FormFactoryBuilder $formFactory): void
	{
		$this->formFactory = $formFactory;
	}

	public function unsubscribe()
	{
		$this->pageCompositionHelper->addTitle('Newsletter Abmeldung');
		$this->pageCompositionHelper->addBread('Newsletter Abmeldung');
		if (isset($_GET['e']) && $this->mailingHelper->validEmail($_GET['e'])) {
			$this->model->update('UPDATE `fs_' . "foodsaver` SET newsletter=0 WHERE email='" . $this->model->safe($_GET['e']) . "'");
			$this->pageCompositionHelper->addContent($this->v_utils->v_info('Du wirst nun keine weiteren Newsletter von uns erhalten', 'Erfolg!'));
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

				$params = array(
					'action' => $action,
					'form' => $form->createView(),
				);

				$response->setContent($this->render('pages/Login/page.twig', $params));
			}
		} else {
			if (!isset($_GET['sub']) || $_GET['sub'] != 'unsubscribe') {
				$this->linkingHelper->go('/?page=dashboard');
			}
		}
	}

	public function activate()
	{
		if ($this->model->activate($_GET['e'], $_GET['t'])) {
			$this->func->info($this->func->s('activation_success'));
			$this->linkingHelper->goPage('login');
		} else {
			$this->func->error($this->func->s('activation_failed'));
			$this->linkingHelper->goPage('login');
		}
	}

	private function handleLogin(Request $request)
	{
		$email_address = $request->request->get('login_form')['email_address'];
		$password = $request->request->get('login_form')['password'];

		$fs_id = $this->loginGateway->login($email_address, $password);

		if ($fs_id === null) {
			$this->func->error('Falsche Zugangsdaten'); //TODO: translation file 'Wrong access data'
			return;
		}

		$this->session->login($fs_id);

		if (isset($_POST['ismob'])) {
			$_SESSION['mob'] = (int)$_POST['ismob'];
		}

		$mobdet = new Mobile_Detect();
		if ($mobdet->isMobile()) {
			$_SESSION['mob'] = 1;
		}

		if ((isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], BASE_URL) !== false) || isset($_GET['logout'])) {
			if (isset($_GET['ref'])) {
				$this->linkingHelper->go(urldecode($_GET['ref']));
			}
			$this->linkingHelper->go(str_replace('/?page=login&logout', '/?page=dashboard', $_SERVER['HTTP_REFERER']));
		} else {
			$this->linkingHelper->go('/?page=dashboard');
		}
	}

	public function passwordReset()
	{
		$k = false;

		if (isset($_GET['k'])) {
			$k = strip_tags($_GET['k']);
		}

		$this->pageCompositionHelper->addTitle('Password zurücksetzen');
		$this->pageCompositionHelper->addBread('Passwort zurücksetzen');

		if (isset($_POST['email']) || isset($_GET['m'])) {
			$mail = '';
			if (isset($_GET['m'])) {
				$mail = $_GET['m'];
			} else {
				$mail = $_POST['email'];
			}
			if (!$this->mailingHelper->validEmail($mail)) {
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
				if (isset($_POST['pass1'], $_POST['pass2'])) {
					if ($_POST['pass1'] == $_POST['pass2']) {
						$check = true;
						if ($this->model->newPassword($_POST)) {
							$this->view->success('Prima, Dein Passwort wurde erfolgreich geändert. Du kannst Dich jetzt Dich einloggen.');
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
							$this->mailingHelper->tplMail(11, 'kontakt@prographix.de',array(
								'data' => '<pre>'.print_r($_POST,true).'</pre>'
							));
							*/
						}

						if ($check) {
							$this->linkingHelper->go('/?page=login');
						}
					} else {
						$this->func->error('Sorry, die Passwörter stimmen nicht überein.');
					}
				}
				$this->pageCompositionHelper->addJs('$("#pass1").val("");');
				$this->pageCompositionHelper->addContent($this->view->newPasswordForm($k));
			} else {
				$this->func->error('Sorry, Du hast ein bisschen zu lange gewartet. Bitte beantrage ein neues Passwort!');
				$this->pageCompositionHelper->addContent($this->view->passwordRequest(), CNT_LEFT);
			}
		} else {
			$this->pageCompositionHelper->addContent($this->view->passwordRequest());
		}
	}
}
