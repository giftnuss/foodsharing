<?php

namespace Foodsharing\Lib\Mail;

use BounceMailHandler\BounceMailHandler;
use Foodsharing\Modules\Email\EmailGateway;

class BounceProcessing
{
	private $bounceMailHandler;

	private $emailGateway;

	private $numBounces;

	public function __construct(BounceMailHandler $bounceMailHandler, EmailGateway $emailGateway)
	{
		$this->bounceMailHandler = $bounceMailHandler;
		$this->emailGateway = $emailGateway;
		$this->numBounces = 0;
	}

	public function process()
	{
		$this->bounceMailHandler->actionFunction = [$this, 'handleBounce'];
		$this->bounceMailHandler->openMailbox();
		$this->bounceMailHandler->processMailbox();
		/* catch errors/notices that would otherwise fall through */
		imap_errors();
	}

	public function getNumberOfProcessedBounces()
	{
		return $this->numBounces;
	}

	public function handleBounce($msgnum, $bounceType, $email, $subject, $xheader, $remove, $ruleNo = false, $ruleCat = false, $totalFetched = 0, $body = '', $headerFull = '', $bodyFull = '')
	{
		if ($bounceType !== false) {
			$this->emailGateway->addBounceForMail($email, $ruleCat, new \DateTime());
			++$this->numBounces;
		}
	}
}
