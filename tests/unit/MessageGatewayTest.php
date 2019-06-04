<?php

class MessageGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Message\MessageGateway
	 */
	private $gateway;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Message\MessageGateway::class);
	}

	public function testGetOrCreateConversationGetsExistingConversation()
	{
		$fsa = $this->tester->createFoodsaver();
		$fsb = $this->tester->createFoodsaver();
		$conversation = $this->tester->createConversation([$fsb['id'], $fsa['id']]);
		$result = $this->gateway->getOrCreateConversation([$fsa['id'], $fsb['id']]);
		$this->tester->assertEquals($conversation['id'], $result);
	}

	public function testGetOrCreateConversationGetsNewConversation()
	{
		$fsa = $this->tester->createFoodsaver();
		$fsb = $this->tester->createFoodsaver();
		$fsc = $this->tester->createFoodsaver();
		$conversation = $this->tester->createConversation([$fsb['id'], $fsa['id']]);
		$result = $this->gateway->getOrCreateConversation([$fsa['id'], $fsb['id'], $fsc['id']]);
		$this->tester->assertNotEquals($conversation['id'], $result);
		$result = $this->gateway->getOrCreateConversation([$fsa['id'], $fsc['id']]);
		$this->tester->assertNotEquals($conversation['id'], $result);
	}
}
