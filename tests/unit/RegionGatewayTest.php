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
	private $fairteiler;

	/**
	 * @var array
	 */
	private $region;

	/**
	 * @var array
	 */
	private $childRegion;

	protected function _before()
	{
		$this->gateway = $this->tester->get(RegionGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->region = $this->tester->createRegion('God');
		$this->tester->addBezirkMember($this->region['id'], $this->foodsaver['id']);
		$this->childRegion = $this->tester->createRegion('Jesus', $this->region['id']);
	}

	public function testGetAllRegions()
	{
		$regions = $this->gateway->listIdsForFoodsaverWithDescendants($this->foodsaver['id']);
		$this->assertEquals(2, count($regions));
		$this->assertEquals($regions, [$this->region['id'], $this->childRegion['id']]);
	}

	public function testGetRegions()
	{
		$regions = $this->gateway->listForFoodsaver($this->foodsaver['id']);
		$this->assertEquals(count($regions), 1);
		$this->assertEquals(array_keys($regions), [$this->region['id']]);
		$this->assertEquals($regions[$this->region['id']], [
			'id' => $this->region['id'],
			'name' => $this->region['name'],
			'type' => $this->region['type']
		]);
	}

	public function testGetDescendantsAndSelf()
	{
		$regions = $this->gateway->listIdsForDescendantsAndSelf($this->region['id']);
		$this->assertEquals(count($regions), 2);
		$this->assertEquals($regions, [$this->region['id'], $this->childRegion['id']]);
	}


	public function testGetParentRegions(): void
	{
		$regions = $this->gateway->listRegionsIncludingParents([108]); // 108 is ID of Zurich
		$this->assertEquals($regions, [108, 106, 741, 0]); // Zurich, Switzerland, Europe, don't know what that is.
	}
}
