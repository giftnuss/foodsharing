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

	public function testGetConversationMemberNamesExcept()
	{
		$testConversation = $this->tester->createConversation([$this->testFoodsaver1['id'], $this->testFoodsaver2['id']]);

		$result = $this->gateway->getConversationMemberNamesExcept($testConversation['id'], $this->testFoodsaver1['id']);
		$this->assertContains($this->testFoodsaver2['name'], $result);
		$this->assertNotContains($this->testFoodsaver1['name'], $result);
	}

	public function testGetProperConversationNameReturnsProperConversationNameForNamedConversations()
	{
		$testConversationName = 'conversationName';

		$testConversation = $this->tester->createConversation(
			[$this->testFoodsaver1['id'], $this->testFoodsaver2['id']],
			['name' => $testConversationName]
		);

		$this->tester->assertEquals(
			$testConversationName,
			$this->gateway->getProperConversationNameForFoodsaver($this->testFoodsaver1['id'], $testConversation['id'])
		);
	}

	public function testGetProperConversationNameReturnsProperConverationNameForStoreTeamConversation()
	{
		$testConversation = $this->tester->createConversation([$this->testFoodsaver1['id'], $this->testFoodsaver2['id']]);

		$testStore = $this->tester->createStore(
			$this->tester->createRegion()['id'],
			$testConversation['id']
		);

		$this->assertEquals(
			$this->translationHelper->s('store') . ' ' . $testStore['name'],
			$this->gateway->getProperConversationNameForFoodsaver($this->testFoodsaver1['id'], $testConversation['id'])
		);
	}

	public function testGetProperConversationNameReturnsProperConverationNameForStoreSpringerConversation()
	{
		$testConversation = $this->tester->createConversation([$this->testFoodsaver1['id'], $this->testFoodsaver2['id']]);

		$testStore = $this->tester->createStore(
			$this->tester->createRegion()['id'],
			null,
			$testConversation['id']
		);

		$this->assertEquals(
			$this->translationHelper->s('store') . ' ' . $testStore['name'],
			$this->gateway->getProperConversationNameForFoodsaver($this->testFoodsaver1['id'], $testConversation['id'])
		);
	}

	public function testGetProperConversationNameReturnsProperConversationNameForTwoMemberConversation()
	{
		$testConversation = $this->tester->createConversation([$this->testFoodsaver1['id'], $this->testFoodsaver2['id']]);

		$this->assertEquals(
			$this->testFoodsaver2['name'],
			$this->gateway->getProperConversationNameForFoodsaver($this->testFoodsaver1['id'], $testConversation['id'])
		);
	}
}
