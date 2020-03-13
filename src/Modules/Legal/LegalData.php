<?php

namespace Foodsharing\Modules\Legal;

use Symfony\Component\Validator\Constraints as Assert;

class LegalData
{
	/**
	 * @Assert\Type("boolean")
	 * @Assert\IsTrue(message="legal.must_accept_pp")
	 */
	private $privacyPolicyAcknowledged;

	/**
	 * @Assert\Type("boolean")
	 */
	private $privacyNoticeAcknowledged;

	public function __construct(bool $privacyPolicyAcknowledged = false, bool $privacyNoticeAcknowledged = false)
	{
		$this->privacyPolicyAcknowledged = $privacyPolicyAcknowledged;
		$this->privacyNoticeAcknowledged = $privacyNoticeAcknowledged;
	}

	public function isPrivacyPolicyAcknowledged(): bool
	{
		return $this->privacyPolicyAcknowledged;
	}

	public function setPrivacyPolicyAcknowledged(?bool $acknowledged): void
	{
		if ($acknowledged !== null) {
			$this->privacyPolicyAcknowledged = $acknowledged;
		}
	}

	public function isPrivacyNoticeAcknowledged(): bool
	{
		return $this->privacyNoticeAcknowledged;
	}

	public function setPrivacyNoticeAcknowledged(?bool $acknowledged): void
	{
		if ($acknowledged !== null) {
			$this->privacyPolicyAcknowledged = $acknowledged;
		}
	}
}
