<?php

use Foodsharing\Modules\Report\ReportGateway;

class ReportGatewayTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	protected ReportGateway $gateway;
	protected $region;
	protected $childRegion;
	protected $childChildRegion;

	protected function _before()
	{
		$this->gateway = $this->tester->get(ReportGateway::class);
		$this->region = $this->tester->createRegion('Computer');
		$this->childRegion = $this->tester->createRegion('Motherboard', ['parent_id' => $this->region['id']]);
		$this->childChildRegion = $this->tester->createRegion('CPU', ['parent_id' => $this->childRegion['id']]);
	}
}
