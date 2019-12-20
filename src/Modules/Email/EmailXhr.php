<?php

namespace Foodsharing\Modules\Email;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\NewsletterEmailPermissions;

class EmailXhr extends Control
{
	private $newsletterEmailPermissions;

	public function __construct(NewsletterEmailPermissions $newsletterEmailPermissions)
	{
		$this->newsletterEmailPermissions = $newsletterEmailPermissions;
		parent::__construct();
	}

	public function testmail()
	{
		if (!$this->newsletterEmailPermissions->mayAdministrateNewsletterEmail()) {
			return false;
		}

		if (!$this->emailHelper->validEmail($_POST['email'])) {
			return array(
				'status' => 1,
				'script' => 'pulseError("Mit der E-Mail-Adresse stimmt etwas nicht!");'
			);
		}

		$this->emailHelper->libmail(false, $_POST['email'], $_POST['subject'], $_POST['message']);

		return array(
			'status' => 1,
			'script' => 'pulseInfo("E-Mail wurde versendet!");'
		);
	}
}
