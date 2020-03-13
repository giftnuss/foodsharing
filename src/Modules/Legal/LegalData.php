<?php

namespace Foodsharing\Modules\Legal;

use Symfony\Component\Validator\Constraints as Assert;

class LegalData
{
	/**
	 * @Assert\Type("boolean")
	 * @Assert\IsTrue(message="legal.must_accept_pp")
	 */
	public $privacyPolicyAcknowledged;

	/**
	 * @Assert\Type("boolean")
	 */
	public $privacyNoticeAcknowledged;
}
