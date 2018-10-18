<?php

class ContentGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Content\ContentGateway
	 */
	private $gateway;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Content\ContentGateway::class);
	}

	public function testGetContent()
	{
		$content = $this->gateway->get(33);
		$this->assertNotNull($content);
		$this->assertEquals('Wichtiger Hinweis:', $content['title']);
		$this->assertContains('Lebensmittelverschwendung', $content['body']);
	}
}
