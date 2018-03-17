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
	private $bezirk;

	protected function _before()
	{
		$this->gateway = $this->tester->get(FairTeilerGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->otherFoodsaver = $this->tester->createFoodsaver();
		$this->bezirk = $this->tester->createRegion('peter');
		$this->fairteiler = $this->tester->createFairteiler($this->foodsaver['id'], $this->bezirk['id'], [
			'picture' => 'picture/cat.jpg'
		]);
	}

	public function testUpdateFairteiler()
	{
		$data = [
			'bezirk_id' => $this->bezirk['id'],
			'name' => 'asdf',
			'desc' => $this->fairteiler['desc'],
			'anschrift' => $this->fairteiler['anschrift'],
			'plz' => $this->fairteiler['plz'],
			'ort' => $this->fairteiler['ort'],
			'lat' => $this->fairteiler['lat'],
			'lon' => $this->fairteiler['lon'],
			'picture' => null
			];
		$response = $this->gateway->updateFairteiler($this->fairteiler['id'], $data);
		$this->assertTrue($response);
		$this->tester->seeInDatabase('fs_fairteiler', ['name' => 'asdf']);
	}

	public function testUpdateFairteilerReturnsTrueIfNothingChanged()
	{
		$data = [
			'bezirk_id' => $this->bezirk['id'],
			'name' => $this->fairteiler['name'],
			'desc' => $this->fairteiler['desc'],
			'anschrift' => $this->fairteiler['anschrift'],
			'plz' => $this->fairteiler['plz'],
			'ort' => $this->fairteiler['ort'],
			'lat' => $this->fairteiler['lat'],
			'lon' => $this->fairteiler['lon'],
			'picture' => null
		];
		$response = $this->gateway->updateFairteiler(
			$this->fairteiler['id'], $data
		);
		$this->assertTrue($response);
		$this->tester->seeInDatabase('fs_fairteiler', ['name' => $this->fairteiler['name']]);
	}

	public function testUpdateFairteilerDoesNotStripTags()
	{
		/* strip_tags happens in the controller in this case */
		$this->tester->dontSeeInDatabase('fs_fairteiler', ['name' => 'asdf']);
		$data = [
			'bezirk_id' => $this->bezirk['id'],
			'name' => 'asdf<script>',
			'desc' => $this->fairteiler['desc'],
			'anschrift' => $this->fairteiler['anschrift'],
			'plz' => $this->fairteiler['plz'],
			'ort' => $this->fairteiler['ort'],
			'lat' => $this->fairteiler['lat'],
			'lon' => $this->fairteiler['lon'],
			'picture' => null
		];

		$response = $this->gateway->updateFairteiler(
			$this->fairteiler['id'],
			$data
		);
		$this->assertTrue($response);
		$this->tester->seeInDatabase('fs_fairteiler', ['name' => 'asdf<script>']);
		$this->tester->dontSeeInDatabase('fs_fairteiler', ['name' => 'asdf']);
	}

	public function testUpdateFairteilerThrowsIfIDNotFound()
	{
		$this->expectException(\Exception::class);
		$this->gateway->updateFairteiler(
			99999999, []
		);
		$this->tester->dontSeeInDatabase('fs_fairteiler', ['name' => 'asdf']);
	}

	public function testFollow()
	{
		$params = [
			'fairteiler_id' => $this->fairteiler['id'],
			'foodsaver_id' => $this->otherFoodsaver['id'],
			'infotype' => 1,
			'type' => 1
		];
		$this->tester->dontSeeInDatabase('fs_fairteiler_follower', $params);
		$this->gateway->follow($this->fairteiler['id'], $this->otherFoodsaver['id'], 1);
		$this->tester->seeInDatabase('fs_fairteiler_follower', $params);
	}

	public function testFollowDoesNotOverwriteExistingType()
	{
		$params = [
			'fairteiler_id' => $this->fairteiler['id'],
			'foodsaver_id' => $this->foodsaver['id'],
			'infotype' => 1,
			'type' => 2
		];

		// Our foodsaver is an admin of the fairteiler so already has a type 2 entry
		$this->tester->seeInDatabase('fs_fairteiler_follower', $params);

		$this->gateway->follow($this->fairteiler['id'], $this->foodsaver['id'], 1);

		// They should keep their type 2 (meaning admin)
		$this->tester->seeInDatabase('fs_fairteiler_follower', $params);

		// And should not have a type 1 entry
		$this->tester->dontSeeInDatabase('fs_fairteiler_follower', array_merge($params, ['type' => 1]));
	}

	public function testUnfollow()
	{
		// Our foodsaver is an admin of the fairteiler so has a type 2 entry
		$this->tester->seeInDatabase('fs_fairteiler_follower', [
			'fairteiler_id' => $this->fairteiler['id'],
			'foodsaver_id' => $this->foodsaver['id'],
			'infotype' => 1,
			'type' => 2
		]);

		$this->gateway->unfollow($this->fairteiler['id'], $this->foodsaver['id']);

		// There are now no follow entries for this fairteiler/foodsaver combination
		$this->tester->dontSeeInDatabase('fs_fairteiler_follower', [
			'fairteiler_id' => $this->fairteiler['id'],
			'foodsaver_id' => $this->foodsaver['id'],
		]);
	}

	public function testGetFairteiler()
	{
		$fairteiler = $this->gateway->getFairteiler($this->fairteiler['id']);
		$this->assertEquals($fairteiler['id'], $this->fairteiler['id']);
		$this->assertEquals($fairteiler['picture'], 'picture/cat.jpg');
		$this->assertEquals($fairteiler['pic'], [
			'thumb' => 'images/picture/crop_1_60_cat.jpg',
			'head' => 'images/picture/crop_0_528_cat.jpg',
			'orig' => 'images/picture/cat.jpg'
		]);
	}

	public function testAddFairTeiler()
	{
		$data = [
			'bezirk_id' => $this->bezirk['id'],
			'name' => 'my nice new fairteiler',
			'desc' => $this->fairteiler['desc'],
			'anschrift' => $this->fairteiler['anschrift'],
			'plz' => $this->fairteiler['plz'],
			'ort' => $this->fairteiler['ort'],
			'lat' => $this->fairteiler['lat'],
			'lon' => $this->fairteiler['lon'],
			'picture' => 'picture/cat.jpg'
		];
		$id = $this->gateway->addFairteiler(
			$this->foodsaver['id'],
			$data
		);

		$this->assertGreaterThanOrEqual(0, $id);

		$this->tester->seeInDatabase('fs_fairteiler', [
			'name' => 'my nice new fairteiler'
		]);

		$this->tester->seeInDatabase('fs_fairteiler_follower', [
			'fairteiler_id' => $id,
			'foodsaver_id' => $this->foodsaver['id'],
		]);
	}
}
