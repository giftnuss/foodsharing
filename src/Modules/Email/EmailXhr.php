<?php

namespace Foodsharing\Modules\Email;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;

class EmailXhr extends Control
{
	public function __construct(Db $model)
	{
		$this->model = $model;

		parent::__construct();
	}

	public function testmail()
	{
		if (!$this->session->may('orga')) {
			return false;
		}

		if (!$this->mailingHelper->validEmail($_POST['email'])) {
			return array(
				'status' => 1,
				'script' => 'pulseError("Mit der E-Mail-Adresse stimmt etwas nicht!");'
			);
		}

		$this->mailingHelper->libmail(false, $_POST['email'], $_POST['subject'], $_POST['message']);

		return array(
			'status' => 1,
			'script' => 'pulseInfo("E-Mail wurde versendet!");'
		);
	}
}
