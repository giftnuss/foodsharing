<?php

use Foodsharing\Modules\Email\EmailGateway;

class EmailGatewayTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	private EmailGateway $gateway;

	protected function _before()
	{
		$this->gateway = $this->tester->get(EmailGateway::class);
	}

	public function testInitEmail()
	{
		$sender = $this->tester->createFoodsaver();
		$recipient = $this->tester->createFoodsaver();
		$message = 'test';
		$mailboxId = 42;

		$this->gateway->initEmail($sender['id'], $mailboxId, [$recipient], $message, '', '');

		$this->tester->seeInDatabase('fs_send_email', ['foodsaver_id' => $sender['id'], 'message' => $message]);
		$this->tester->seeInDatabase('fs_email_status', ['foodsaver_id' => $recipient['id']]);
	}
}
