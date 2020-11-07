<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Settings\SettingsGateway;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginControl extends Control
{
	private LoginGateway $loginGateway;
	private SettingsGateway $settingsGateway;
	private ContentGateway $contentGateway;

	public function __construct(
		LoginView $view,
		LoginGateway $loginGateway,
		ContentGateway $contentGateway,
		SettingsGateway $settingsGateway
	) {
		$this->view = $view;
		$this->loginGateway = $loginGateway;
		$this->settingsGateway = $settingsGateway;
		$this->contentGateway = $contentGateway;

		parent::__construct();
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
			if (!$has_subpage) {
				$this->pageHelper->addContent($this->view->loginForm());
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
			$this->flashMessageHelper->success($this->translator->trans('register.activation_success'));
			$this->routeHelper->goPage('login');
		} else {
			$this->flashMessageHelper->error($this->translator->trans('register.activation_failed'));
			$this->routeHelper->goPage('login');
		}
	}

	public function passwordReset()
	{
		$k = false;

		if (isset($_GET['k'])) {
			$k = strip_tags($_GET['k']);
		}

		$this->pageHelper->addTitle($this->translator->trans('login.pwreset.bread'));
		$this->pageHelper->addBread($this->translator->trans('login.pwreset.bread'));

		if (isset($_POST['email']) || isset($_GET['m'])) {
			$mail = '';
			if (isset($_GET['m'])) {
				$mail = $_GET['m'];
			} else {
				$mail = $_POST['email'];
			}
			if (!$this->emailHelper->validEmail($mail)) {
				$this->flashMessageHelper->error($this->translator->trans('login.pwreset.wrongMail'));
			} else {
				if ($this->loginGateway->addPassRequest($mail)) {
					$this->flashMessageHelper->info($this->translator->trans('login.pwreset.mailSent'));
				} else {
					$this->flashMessageHelper->error($this->translator->trans('login.pwreset.wrongMail'));
				}
			}
		}

		if ($k !== false && $this->loginGateway->checkResetKey($k)) {
			if ($this->loginGateway->checkResetKey($k)) {
				if (isset($_POST['pass1'], $_POST['pass2'])) {
					if ($_POST['pass1'] == $_POST['pass2']) {
						$check = true;
						if ($this->loginGateway->newPassword($_POST)) {
							$this->view->success(
								$this->translator->trans('login.pwreset.success')
							);
						} elseif (strlen($_POST['pass1']) < 5) {
							$check = false;
							$this->flashMessageHelper->error($this->translator->trans('login.pwreset.tooShort'));
						} elseif (!$this->loginGateway->checkResetKey($_POST['k'])) {
							$check = false;
							$this->flashMessageHelper->error($this->translator->trans('login.pwreset.expired'));
						} else {
							$check = false;
							$this->flashMessageHelper->error($this->translator->trans('login.pwreset.error'));
						}

						if ($check) {
							$this->routeHelper->go('/?page=login');
						}
					} else {
						$this->flashMessageHelper->error($this->translator->trans('login.pwreset.mismatch'));
					}
				}
				$this->pageHelper->addJs('$("#pass1").val("");');
				$this->pageHelper->addContent($this->view->newPasswordForm($k));
			} else {
				$this->flashMessageHelper->error($this->translator->trans('login.pwreset.expired'));
				$this->pageHelper->addContent($this->view->passwordRequest(), CNT_LEFT);
			}
		} else {
			$this->pageHelper->addContent($this->view->passwordRequest());
		}
	}
}
