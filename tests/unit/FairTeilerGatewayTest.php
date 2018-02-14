<?php

use Foodsharing\Modules\FairTeiler\FairTeilerGateway;

class FairTeilerGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var FairTeilerGateway
	 */
	private $gateway;

	protected function _before()
	{
		$this->gateway = $this->tester->get(FairTeilerGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->bezirk = $this->tester->createRegion('peter');
		$this->fairteiler = $this->tester->createFairteiler($this->foodsaver['id'], $this->bezirk['id']);
	}

	public function testUpdateFairteiler()
	{
		$response = $this->gateway->updateFairteiler(
			$this->fairteiler['id'],
			$this->bezirk['id'],
			'asdf',
			$this->fairteiler['desc'],
			$this->fairteiler['anschrift'],
			$this->fairteiler['plz'],
			$this->fairteiler['ort'],
			$this->fairteiler['lat'],
			$this->fairteiler['lon'],
			null
		);
		$this->assertEquals(true, $response);
		$this->tester->seeInDatabase('fs_fairteiler', ['name' => 'asdf']);
	}

	public function testUpdateFairteilerReturnsTrueIfNothingChanged()
	{
		$response = $this->gateway->updateFairteiler(
			$this->fairteiler['id'],
			$this->bezirk['id'],
			$this->fairteiler['name'],
			$this->fairteiler['desc'],
			$this->fairteiler['anschrift'],
			$this->fairteiler['plz'],
			$this->fairteiler['ort'],
			$this->fairteiler['lat'],
			$this->fairteiler['lon'],
			null
		);
		$this->assertEquals(true, $response);
		$this->tester->seeInDatabase('fs_fairteiler', ['name' => $this->fairteiler['name']]);
	}

	public function testUpdateFairteilerStripsTags()
	{
		$this->tester->dontSeeInDatabase('fs_fairteiler', ['name' => 'asdf']);

		$response = $this->gateway->updateFairteiler(
			$this->fairteiler['id'],
			$this->bezirk['id'],
			'asdf<script>',
			$this->fairteiler['desc'],
			$this->fairteiler['anschrift'],
			$this->fairteiler['plz'],
			$this->fairteiler['ort'],
			$this->fairteiler['lat'],
			$this->fairteiler['lon'],
			null
		);
		$this->assertEquals(true, $response);
		$this->tester->dontSeeInDatabase('fs_fairteiler', ['name' => 'asdf<script>']);
		$this->tester->seeInDatabase('fs_fairteiler', ['name' => 'asdf']);
	}

	public function testUpdateFairteilerThrowsIfIDNotFound()
	{
		$this->expectException(\Exception::class);
		$this->gateway->updateFairteiler(
			99999999,
			$this->bezirk['id'],
			'asdf',
			$this->fairteiler['desc'],
			$this->fairteiler['anschrift'],
			$this->fairteiler['plz'],
			$this->fairteiler['ort'],
			$this->fairteiler['lat'],
			$this->fairteiler['lon'],
			null
		);
		$this->tester->dontSeeInDatabase('fs_fairteiler', ['name' => 'asdf']);
	}
}
