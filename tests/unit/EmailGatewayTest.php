<?php

class EmailGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Email\EmailGateway
	 */
	private $gateway;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Email\EmailGateway::class);
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
