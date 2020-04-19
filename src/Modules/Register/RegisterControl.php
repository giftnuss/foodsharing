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
			$this->flashMessageHelper->info($this->translationHelper->s('you_are_already_register_please_logg_out_if_you_want_to_register_again'));
			$this->routeHelper->go('/?page=dashboard');
		} else {
			$this->pageHelper->addBread($this->translationHelper->s('registration'));
			$this->pageHelper->addTitle($this->translationHelper->s('registration'));

			$this->pageHelper->addContent($this->view->registerForm());
		}
	}
}
