<?php

use Foodsharing\Modules\Core\DBConstants\FoodSharePoint\FollowerType;
use Foodsharing\Modules\Core\DBConstants\Info\InfoType;
use Foodsharing\Modules\FoodSharePoint\FoodSharePointGateway;

class FoodSharePointGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var FoodSharePointGateway
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
	private $bezirk;

	protected function _before()
	{
		$this->gateway = $this->tester->get(FoodSharePointGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->otherFoodsaver = $this->tester->createFoodsaver();
		$this->bezirk = $this->tester->createRegion('peter');
		$this->foodSharePoint = $this->tester->createFoodSharePoint(
			$this->foodsaver['id'],
			$this->bezirk['id'],
			['picture' => 'picture/cat.jpg']
		);
	}

	public function testUpdateFoodSharePoint()
	{
		$data = [
			'bezirk_id' => $this->bezirk['id'],
			'name' => 'asdf',
			'desc' => $this->foodSharePoint['desc'],
			'anschrift' => $this->foodSharePoint['anschrift'],
			'plz' => $this->foodSharePoint['plz'],
			'ort' => $this->foodSharePoint['ort'],
			'lat' => $this->foodSharePoint['lat'],
			'lon' => $this->foodSharePoint['lon'],
			'picture' => null
			];
		$response = $this->gateway->updateFoodSharePoint($this->foodSharePoint['id'], $data);
		$this->assertTrue($response);
		$this->tester->seeInDatabase('fs_fairteiler', ['name' => 'asdf']);
	}

	public function testUpdateFoodSharePointReturnsTrueIfNothingChanged()
	{
		$data = [
			'bezirk_id' => $this->bezirk['id'],
			'name' => $this->foodSharePoint['name'],
			'desc' => $this->foodSharePoint['desc'],
			'anschrift' => $this->foodSharePoint['anschrift'],
			'plz' => $this->foodSharePoint['plz'],
			'ort' => $this->foodSharePoint['ort'],
			'lat' => $this->foodSharePoint['lat'],
			'lon' => $this->foodSharePoint['lon'],
			'picture' => null
		];
		$response = $this->gateway->updateFoodSharePoint(
			$this->foodSharePoint['id'], $data
		);
		$this->assertTrue($response);
		$this->tester->seeInDatabase('fs_fairteiler', ['name' => $this->foodSharePoint['name']]);
	}

	public function testUpdateFoodSharePointDoesNotStripTags()
	{
		/* strip_tags happens in the controller in this case */
		$this->tester->dontSeeInDatabase('fs_fairteiler', ['name' => 'asdf']);
		$data = [
			'bezirk_id' => $this->bezirk['id'],
			'name' => 'asdf<script>',
			'desc' => $this->foodSharePoint['desc'],
			'anschrift' => $this->foodSharePoint['anschrift'],
			'plz' => $this->foodSharePoint['plz'],
			'ort' => $this->foodSharePoint['ort'],
			'lat' => $this->foodSharePoint['lat'],
			'lon' => $this->foodSharePoint['lon'],
			'picture' => null
		];

		$response = $this->gateway->updateFoodSharePoint(
			$this->foodSharePoint['id'],
			$data
		);
		$this->assertTrue($response);
		$this->tester->seeInDatabase('fs_fairteiler', ['name' => 'asdf<script>']);
		$this->tester->dontSeeInDatabase('fs_fairteiler', ['name' => 'asdf']);
	}

	public function testUpdateFoodSharePointThrowsIfIDNotFound()
	{
		$this->expectException(\Exception::class);
		$this->gateway->updateFoodSharePoint(
			99999999, []
		);
		$this->tester->dontSeeInDatabase('fs_fairteiler', ['name' => 'asdf']);
	}

	public function testFollow()
	{
		$params = [
			'fairteiler_id' => $this->foodSharePoint['id'],
			'foodsaver_id' => $this->otherFoodsaver['id'],
			'infotype' => InfoType::EMAIL,
			'type' => FollowerType::FOLLOWER
		];
		$this->tester->dontSeeInDatabase('fs_fairteiler_follower', $params);
		$this->gateway->follow($this->otherFoodsaver['id'], $this->foodSharePoint['id'], InfoType::EMAIL);
		$this->tester->seeInDatabase('fs_fairteiler_follower', $params);
	}

	public function testFollowDoesNotOverwriteExistingType()
	{
		$params = [
			'fairteiler_id' => $this->foodSharePoint['id'],
			'foodsaver_id' => $this->foodsaver['id'],
			'infotype' => InfoType::EMAIL,
			'type' => FollowerType::FOOD_SHARE_POINT_MANAGER
		];

		// Our foodsaver is an admin of the food share point so already has a type 2 entry
		$this->tester->seeInDatabase('fs_fairteiler_follower', $params);

		$this->gateway->follow($this->foodsaver['id'], $this->foodSharePoint['id'], InfoType::EMAIL);

		// They should keep their type 2 (meaning admin)
		$this->tester->seeInDatabase('fs_fairteiler_follower', $params);

		// And should not have a type 1 entry
		$this->tester->dontSeeInDatabase('fs_fairteiler_follower', array_merge($params, ['type' => 1]));
	}

	public function testUnfollow()
	{
		// Our foodsaver is an admin of the food share point so has a type 2 entry
		$this->tester->seeInDatabase('fs_fairteiler_follower', [
			'fairteiler_id' => $this->foodSharePoint['id'],
			'foodsaver_id' => $this->foodsaver['id'],
			'infotype' => InfoType::EMAIL,
			'type' => FollowerType::FOOD_SHARE_POINT_MANAGER
		]);

		$this->gateway->unfollow($this->foodsaver['id'], $this->foodSharePoint['id']);

		// There are now no follow entries for this food share point/foodsaver combination
		$this->tester->dontSeeInDatabase('fs_fairteiler_follower', [
			'fairteiler_id' => $this->foodSharePoint['id'],
			'foodsaver_id' => $this->foodsaver['id'],
		]);
	}

	public function testGetFoodSharePoint()
	{
		$foodSharePoint = $this->gateway->getFoodSharePoint($this->foodSharePoint['id']);
		$this->assertEquals($foodSharePoint['id'], $this->foodSharePoint['id']);
		$this->assertEquals($foodSharePoint['picture'], 'picture/cat.jpg');
		$this->assertEquals($foodSharePoint['pic'], [
			'thumb' => 'images/picture/crop_1_60_cat.jpg',
			'head' => 'images/picture/crop_0_528_cat.jpg',
			'orig' => 'images/picture/cat.jpg'
		]);
	}

	public function testAddFoodSharePoint()
	{
		$data = [
			'bezirk_id' => $this->bezirk['id'],
			'name' => 'my nice new food share point',
			'desc' => $this->foodSharePoint['desc'],
			'anschrift' => $this->foodSharePoint['anschrift'],
			'plz' => $this->foodSharePoint['plz'],
			'ort' => $this->foodSharePoint['ort'],
			'lat' => $this->foodSharePoint['lat'],
			'lon' => $this->foodSharePoint['lon'],
			'picture' => 'picture/cat.jpg'
		];
		$id = $this->gateway->addFoodSharePoint(
			$this->foodsaver['id'],
			$data
		);

		$this->assertGreaterThanOrEqual(0, $id);

		$this->tester->seeInDatabase('fs_fairteiler', [
			'name' => 'my nice new food share point'
		]);

		$this->tester->seeInDatabase('fs_fairteiler_follower', [
			'fairteiler_id' => $id,
			'foodsaver_id' => $this->foodsaver['id'],
		]);
	}
}
