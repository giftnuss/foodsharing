<?php

namespace Foodsharing\Lib\Mail;

use BounceMailHandler\BounceMailHandler;
use Foodsharing\Modules\Mails\MailsGateway;

class BounceProcessing
{
	private $bounceMailHandler;

	private $mailsGateway;

	private $numBounces;

	public function __construct(BounceMailHandler $bounceMailHandler, MailsGateway $mailsGateway)
	{
		$this->bounceMailHandler = $bounceMailHandler;
		$this->mailsGateway = $mailsGateway;
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
			$this->mailsGateway->addBounceForMail($email, $ruleCat, new \DateTime());
			++$this->numBounces;
		}
	}
}
