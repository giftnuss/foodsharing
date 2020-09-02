<?php

namespace Foodsharing\Modules\Register;

use Foodsharing\Modules\Core\Control;

class RegisterControl extends Control
{
	public function __construct(
		RegisterView $view
	) {
		$this->view = $view;

		parent::__construct();
	}

	public function index()
	{
		if ($this->session->may()) {
			$this->flashMessageHelper->info($this->translator->trans('register.account-exists'));
			$this->routeHelper->go('/?page=dashboard');
		} else {
			$this->pageHelper->addBread($this->translator->trans('register.title'));
			$this->pageHelper->addTitle($this->translator->trans('register.title'));

			$this->pageHelper->addContent($this->view->registerForm());
		}
	}
}
