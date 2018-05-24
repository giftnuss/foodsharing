<?php

namespace Foodsharing\Modules\Legal;

use Symfony\Component\Validator\Constraints as Assert;

class LegalData
{
	/**
	 * @Assert\Type("string")
	 * @Assert\NotBlank()
	 */
	public $privacy_policy_date;

	/**
	 * @Assert\Type("boolean")
	 * @Assert\IsTrue(message="legal.must_accept_pp")
	 */
	public $privacy_policy;
}
