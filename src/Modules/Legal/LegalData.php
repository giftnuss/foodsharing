<?php

namespace Foodsharing\Modules\Legal;

use Symfony\Component\Validator\Constraints as Assert;

class LegalData
{
	/**
	 * @Assert\Type("boolean")
	 * @Assert\IsTrue(message="legal.must_accept_pp")
	 */
	public $privacy_policy;

	/**
	 * @Assert\Type("integer")
	 * @Assert\Range(
	 *   min = 0,
	 *   max = 2
	 *     )
	 */
	public $privacy_notice;
}
