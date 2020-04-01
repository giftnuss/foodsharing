<?php

class ReportGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Report\ReportGateway
	 */
	protected $gateway;
	protected $region;
	protected $childRegion;
	protected $childChildRegion;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Report\ReportGateway::class);
		$this->region = $this->tester->createRegion('Computer');
		$this->childRegion = $this->tester->createRegion('Motherboard', $this->region['id']);
		$this->childChildRegion = $this->tester->createRegion('CPU', $this->childRegion['id']);
	}

	public function testGetSubRegionAmbassadorReports()
	{
		$foodsaverInChild = $this->tester->createFoodsaver(null, ['bezirk_id' => $this->childRegion['id']]);
		$randomFoodsaver = $this->tester->createFoodsaver();
		$adminInChild = $this->tester->createFoodsaver(null, ['bezirk_id' => $this->childRegion['id']]);
		$this->tester->addRegionAdmin($this->childRegion['id'], $adminInChild['id']);
		$reportAgainstAmb = $this->tester->addReport($foodsaverInChild['id'], $adminInChild['id']);
		$anotherReportAgainstAmb = $this->tester->addReport($randomFoodsaver['id'], $adminInChild['id']);
		$reportAgainstUser = $this->tester->addReport($adminInChild['id'], $foodsaverInChild['id']);
		$result = $this->gateway->getReportsByReporteeRegions([$this->childRegion['id'], $this->childChildRegion['id']], null, true);
		$this->tester->assertCount(2, $result);
	}
}
