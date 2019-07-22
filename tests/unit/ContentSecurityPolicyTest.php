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
		$policy = $this->csp->generate('http://somehttphost.com', 'http://reporthere.com', true);
		$this->assertStringContainsString('Content-Security-Policy', $policy);
		$this->assertStringContainsString('report-uri http://reporthere.com;', $policy);
	}

	public function testReportOnlyPolicy(): void
	{
		$policy = $this->csp->generate('http://somehttphost.com', 'http://reporthere.com', true);
		$this->assertStringContainsString('Content-Security-Policy-Report-Only', $policy);
		$this->assertStringContainsString('report-uri http://reporthere.com;', $policy);
	}

	public function testIncludesWsForHttpHost(): void
	{
		$policy = $this->csp->generate('http://somehttphost.com', 'http://reporthere.com', false);
		$this->assertRegExp('/.*connect-src[^;]+ws:\/\/somehttphost.com.*/', $policy);
	}

	public function testIncludesWssForHttpsHost(): void
	{
		$policy = $this->csp->generate('https://somehttphost.com', 'http://reporthere.com', false);
		$this->assertRegExp('/.*connect-src[^;]+wss:\/\/somehttphost.com.*/', $policy);
	}
}
