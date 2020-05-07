<?php

use Codeception\Test\Unit;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Modules\Message\MessageGateway;

class MessageGatewayTest extends Unit
{
	/**
	 * @var UnitTester
	 */
	protected $tester;
	/**
	 * @var MessageGateway
	 */
	private $gateway;

	/**
	 * @var TranslationHelper
	 */
	private $translationHelper;

	private $testFoodsaver1;
	private $testFoodsaver2;

	protected function _before()
	{
		$this->gateway = $this->tester->get(MessageGateway::class);
		$this->translationHelper = $this->tester->get(TranslationHelper::class);

		$this->testFoodsaver1 = $this->tester->createFoodsaver();
		$this->testFoodsaver2 = $this->tester->createFoodsaver();
	}

	public function testGetConversationName()
	{
		$testName = 'test conversation';
		$testConverstaion = $this->tester->createConversation(
			[$this->testFoodsaver1['id'], $this->testFoodsaver2['id']],
			['name' => $testName]
		);

		$this->tester->assertEquals(
			$testName,
			$this->gateway->getConversationName($testConverstaion['id'])
		);
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

	public function testSetConversationMembers()
	{
		$fsa = $this->tester->createFoodsaver();
		$fsb = $this->tester->createFoodsaver();
		$fsc = $this->tester->createFoodsaver();
		$conversation = $this->tester->createConversation([$fsb['id'], $fsa['id']]);
		$cid = $conversation['id'];
		$members = [$fsa['id'], $fsb['id']];
		$this->tester->assertEqualsCanonicalizing($members, $this->tester->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', ['conversation_id' => $cid]));
		$members = [$fsa['id']];
		$this->gateway->setConversationMembers($cid, $members);
		$this->tester->assertEqualsCanonicalizing($members, $this->tester->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', ['conversation_id' => $cid]));
		$members = [$fsa['id'], $fsb['id'], $fsc['id']];
		$this->gateway->setConversationMembers($cid, $members);
		$this->tester->assertEqualsCanonicalizing($members, $this->tester->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', ['conversation_id' => $cid]));
		$members = [];
		$this->gateway->setConversationMembers($cid, $members);
		$this->tester->assertEqualsCanonicalizing($members, $this->tester->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', ['conversation_id' => $cid]));
		$members = [$fsa['id'], $fsb['id'], $fsc['id']];
		$this->gateway->setConversationMembers($cid, $members);
		$this->tester->assertEqualsCanonicalizing($members, $this->tester->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', ['conversation_id' => $cid]));
	}
}
