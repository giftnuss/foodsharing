<?php

use Foodsharing\Modules\Region\RegionGateway;

class RegionGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var RegionGateway
	 */
	private $gateway;

	/**
	 * @var array
	 */
	private $foodsaver;

	/**
	 * @var array
	 */
	private $otherFoodsaver;

	/**
	 * @var array
	 */
	private $foodSharePoint;

	/**
	 * @var array
	 */
	private $region;

	/**
	 * @var array
	 */
	private $childRegion;

	/**
	 * @var array
	 */
	private $childChildRegion;

	/**
	 * @var array
	 */
	private $unrelatedRegion;

	protected function _before()
	{
		$this->gateway = $this->tester->get(RegionGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->region = $this->tester->createRegion('God');
		$this->tester->addRegionMember($this->region['id'], $this->foodsaver['id']);
		$this->childRegion = $this->tester->createRegion('Jesus', $this->region['id']);
		$this->childChildRegion = $this->tester->createRegion('Human', $this->childRegion['id']);
		$this->unrelatedRegion = $this->tester->createRegion('Something else');
	}

	public function testGetAllRegions()
	{
		$regions = $this->gateway->listIdsForFoodsaverWithDescendants($this->foodsaver['id']);
		$this->assertEquals(3, count($regions));
		$this->assertEquals([$this->region['id'], $this->childRegion['id'], $this->childChildRegion['id']], $regions);
	}

	public function testGetRegions()
	{
		$regions = $this->gateway->listForFoodsaver($this->foodsaver['id']);
		$this->assertEquals(1, count($regions));
		$this->assertEquals([$this->region['id']], array_keys($regions));
		$this->assertEquals([
			'id' => $this->region['id'],
			'name' => $this->region['name'],
			'type' => $this->region['type']
		], $regions[$this->region['id']]);
	}

	public function testGetDescendantsAndSelf()
	{
		$regions = $this->gateway->listIdsForDescendantsAndSelf($this->region['id']);
		$this->assertEquals(3, count($regions));
		$this->assertEquals([$this->region['id'], $this->childRegion['id'], $this->childChildRegion['id']], $regions);
	}

	public function testGetDescendantsAndSelfWithoutSelf()
	{
		$regions = $this->gateway->listIdsForDescendantsAndSelf($this->region['id'], false);
		$this->assertEquals(2, count($regions));
		$this->assertEquals([$this->childRegion['id'], $this->childChildRegion['id']], $regions);
	}

	public function testListRegionsIncludingParents(): void
	{
		$regions = $this->gateway->listRegionsIncludingParents([$this->childRegion['id']]);
		$this->assertEquals([$this->region['id'], $this->childRegion['id']], $regions);
	}
}
