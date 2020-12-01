<?php

use Foodsharing\Utility\LoginService;

class MailActivationTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected UnitTester $tester;

	private LoginService $service;

	protected function _before()
	{
		$this->service = $this->tester->get(LoginService::class);
	}

	public function testGenerateActivationToken()
	{
		$token = $this->service->generateMailActivationToken(1);
		$data = json_decode(base64_decode($token), true);
		$this->assertArrayHasKey('t', $data);
		$this->assertArrayHasKey('c', $data);
		$this->assertArrayHasKey('d', $data);
		$this->assertSame($data['d'], date('Ymd'));
		$this->assertSame($data['c'], 1);
	}

	public function testTokenValidation()
	{
		$count = LoginService::ACTIVATION_MAIL_LIMIT_PER_DAY + 1;
		$token = $this->service->generateMailActivationToken($count);
		$validation = $this->service->validateTokenLimit($token);
		$this->assertSame($validation['isValid'], false);
		$this->assertSame($validation['count'], $count + 1);
	}
}
