<?php

namespace Foodsharing\Modules\Email;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;

class EmailXhr extends Control
{
	public function __construct(Model $model)
	{
		$this->model = $model;

		parent::__construct();
	}

	public function testmail()
	{
		if (!S::may('orga')) {
			return false;
		}

		if (!$this->func->validEmail($_POST['email'])) {
			return array(
				'status' => 1,
				'script' => 'pulseError("Mit der E-Mail-Adresse stimmt etwas nicht!");'
			);
		} else {
			$this->func->libmail(false, $_POST['email'], $_POST['subject'], $_POST['message']);

			return array(
				'status' => 1,
				'script' => 'pulseInfo("E-Mail wurde versendet!");'
			);
		}
	}
}
