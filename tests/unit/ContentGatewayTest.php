<?php

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;

class ContentGatewayTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	private ContentGateway $gateway;

	protected function _before()
	{
		$this->gateway = $this->tester->get(ContentGateway::class);
	}

	public function testGetContent()
	{
		$content = $this->gateway->get(ContentId::QUIZ_REMARK_PAGE_33);
		$this->assertNotNull($content);
		$this->assertEquals('Wichtiger Hinweis:', $content['title']);
		$this->assertStringContainsString('Lebensmittelverschwendung', $content['body']);
	}
}
