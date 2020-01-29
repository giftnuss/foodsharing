<?php

use Foodsharing\Services\LoginService;
use Foodsharing\Modules\Login\LoginGateway;

class MailActivationTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
    protected $tester;
    
    /**
     * @var LoginGateway
     */
    private $gateway;

    /**
     * @var LoginService
     */
    private $service;

	/**
	 * @var array
	 */
	private $foodsaver;

	/**
	 * @var array
	 */
	private $inactiveFoodsaver;

	protected function _before()
	{
        $this->gateway = $this->tester->get(LoginGateway::class);
        $this->service = $this->tester->get(LoginService::class);
        $this->foodsaver = $this->tester->createFoodsaver(['active' => 1]);
        $this->inactiveFoodsaver = $this->tester->createFoodsaver(['active' => 0, 'token' => 'faketoken', 'email' => 'test@example.com']);
    }
    
    public function testGenerateActivationToken()
    {
        $token = $this->service->generateMailActivationToken(1);
        $data = base64_decode($token);
        $this->assertArrayHasKey('t', $data);
        $this->assertArrayHasKey('c', $data);
        $this->assertArrayHasKey('d', $data);
        $this->assertTrue($data['d'] === date('Ymd'));
        $this->assertTrue($data['c'] === 1);
    }

    public function testTokenValidation()
    {
        $count = LoginService::ACTIVATION_MAIL_LIMIT_PER_DAY + 1;
        $token = $this->service->generateMailActivationToken($count);
        $validation = $this->service->validateTokenLimit($token);
        $this->assertTrue($validation['isValid'] === false);
        $this->assertTrue($validation['count'] === $count);
    }
}
