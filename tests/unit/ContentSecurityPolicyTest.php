<?php

class ContentSecurityPolicyTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Lib\ContentSecurityPolicy
	 */
	private $csp;

	protected function _before()
	{
		$this->csp = $this->tester->get(\Foodsharing\Lib\ContentSecurityPolicy::class);
	}

	public function testWebsocketUrlFor(): void
	{
		$this->assertEquals('ws://localhost:1234', $this->csp->websocketUrlFor('http://localhost:1234'));
		$this->assertEquals('ws://insecure.com', $this->csp->websocketUrlFor('http://insecure.com'));
		$this->assertEquals('wss://secure.com', $this->csp->websocketUrlFor('https://secure.com'));
	}

	public function testPolicy(): void
	{
		$policy = $this->csp->generate('http://reporthere.com', true);
		$this->assertContains('Content-Security-Policy', $policy);
		$this->assertContains('report-uri http://reporthere.com;', $policy);
	}

	public function testReportOnlyPolicy(): void
	{
		$policy = $this->csp->generate('http://reporthere.com', true);
		$this->assertContains('Content-Security-Policy-Report-Only', $policy);
		$this->assertContains('report-uri http://reporthere.com;', $policy);
	}
}
