<?php

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;

class FoodsaverGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Foodsaver\FoodsaverGateway
	 */
	private $gateway;
	private $foodsharer;
	private $foodsaver;
	private $region;
	private $regionMember;
	private $regionAdmin;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Foodsaver\FoodsaverGateway::class);

		$this->foodsharer = $this->tester->createFoodsharer(null, ['newsletter' => 1]);
		$this->foodsaver = $this->tester->createFoodsaver(null, ['newsletter' => 1]);

		$this->region = $this->tester->createRegion('TestRegion');
		$regionId = $this->region['id'];
		$this->regionMember = $this->tester->createFoodsaver(null, ['bezirk_id' => $regionId]);
		$this->regionAdmin = $this->tester->createAmbassador(null, ['bezirk_id' => $regionId]);
		$this->tester->addRegionAdmin($regionId, $this->regionAdmin['id']);
	}

	public function testUpdateProfile()
	{
		$this->gateway->updateProfile($this->foodsaver['id'], ['anschrift' => 'my new street']);
		$this->tester->seeInDatabase('fs_foodsaver', [
			'id' => $this->foodsaver['id'],
			'anschrift' => 'my new street'
		]);

		// strips tags
		$this->gateway->updateProfile($this->foodsaver['id'], ['anschrift' => '<script>my new street']);
		$this->tester->seeInDatabase('fs_foodsaver', [
			'id' => $this->foodsaver['id'],
			'anschrift' => 'my new street'
		]);
	}

	public function testGetPhoto()
	{
		$this->gateway->updatePhoto($this->foodsaver['id'], 'mypicture.png');
		$this->tester->assertEquals(
			$this->gateway->getPhotoFileName($this->foodsaver['id']),
			'mypicture.png'
		);
	}

	public function testUpdateGroupMembersByAdditionWhileLeavingAdmin()
	{
		$regionId = $this->region['id'];
		$newFoodsaver = $this->tester->createFoodsaver();

		$fsIds = [$newFoodsaver['id'], $this->regionMember['id']];
		$updates = $this->gateway->updateGroupMembers($regionId, $fsIds, true);

		$this->tester->seeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $newFoodsaver['id'], 'bezirk_id' => $regionId]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionAdmin['id'], 'bezirk_id' => $regionId]);
		$this->tester->assertEquals(1, $updates['inserts'], 'Wrong number of inserts for!');
		$this->tester->assertEquals(0, $updates['deletions'], 'Wrong number of deletions!');
	}

	public function testUpdateGroupMembersByAdditionWhileNotLeavingAdmin()
	{
		$regionId = $this->region['id'];
		$newFoodsaver = $this->tester->createFoodsaver();

		$fsIds = [$newFoodsaver['id'], $this->regionMember['id']];
		$updates = $this->gateway->updateGroupMembers($regionId, $fsIds, false);

		$this->tester->seeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $newFoodsaver['id'], 'bezirk_id' => $regionId]);
		$this->tester->dontSeeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionAdmin['id'], 'bezirk_id' => $regionId]);
		$this->tester->assertEquals(1, $updates['inserts'], 'Wrong number of inserts!');
		$this->tester->assertEquals(1, $updates['deletions'], 'Wrong number of deletions!');
	}

	public function testUpdateGroupMembersByDeletionWhileLeavingAdmin()
	{
		$regionId = $this->region['id'];

		$updates = $this->gateway->updateGroupMembers($regionId, [], true);

		$this->tester->dontSeeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionMember['id'], 'bezirk_id' => $regionId]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionAdmin['id'], 'bezirk_id' => $regionId]);
		$this->tester->assertEquals(0, $updates['inserts'], 'Wrong number of inserts!');
		$this->tester->assertEquals(1, $updates['deletions'], 'Wrong number of deletions!');
	}

	public function testUpdateGroupMembersByDeletionWhileNotLeavingAdmin()
	{
		$regionId = $this->region['id'];

		$updates = $this->gateway->updateGroupMembers($regionId, [], false);

		$this->tester->dontSeeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionMember['id'], 'bezirk_id' => $regionId]);
		$this->tester->dontSeeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionAdmin['id'], 'bezirk_id' => $regionId]);
		$this->tester->assertEquals(0, $updates['inserts'], 'Wrong number of inserts!');
		$this->tester->assertEquals(2, $updates['deletions'], 'Wrong number of deletions!');
	}

	public function testGetRegionBotsEmailList()
	{
		$regions = [0 => $this->region['id'], 1 => 0];

		$emails = $this->gateway->getRegionAmbassadorsEmailAddresses($regions);

		$this->tester->assertIsArray($emails);
		$this->tester->assertCount(1, $emails);
		$bot = $this->regionAdmin;
		$this->tester->assertArrayHasKey($bot['id'], $emails);
		$this->tester->assertEquals($bot['email'], $emails[$bot['id']]['email']);
	}

	public function testGetRegionFoodsaversEmailList()
	{
		$regions = [0 => $this->region['id'], 1 => 0];

		$emails = $this->gateway->getEmailAddressesFromRegions($regions);

		$this->tester->assertIsArray($emails);
		$this->tester->assertCount(2, $emails);
		$bot = $this->regionAdmin;
		$this->tester->assertArrayHasKey($bot['id'], $emails);
		$this->tester->assertEquals($bot['email'], $emails[$bot['id']]['email']);
		$fs = $this->regionMember;
		$this->tester->assertEquals($fs['email'], $emails[$fs['id']]['email']);
	}

	public function testGetSingleEmailAddress()
	{
		$email = $this->gateway->getEmailAddress($this->foodsaver['id']);

		$this->tester->assertEquals($this->foodsaver['email'], $email);
	}

	public function testGetAllEmailAddresses()
	{
		$foodsavers = [$this->foodsharer, $this->foodsaver, $this->regionAdmin, $this->regionMember];
		$expectedResult = $this->expectedEmailResult($foodsavers);

		$emails = $this->gateway->getEmailAddresses();

		$this->tester->assertCount(count($expectedResult), $emails);
		$result = $this->serializeEmails($emails);
		$intersection = array_intersect($expectedResult, $result);
		$this->tester->assertCount(count($foodsavers), $intersection, 'Result does not match expactations: ' . serialize($result));
	}

	public function testGetAllEmailAddressesFromNewsletterSubscribers()
	{
		$foodsavers = [$this->foodsharer, $this->foodsaver];
		$expectedResult = $this->expectedEmailResult($foodsavers);

		$emails = $this->gateway->getNewsletterSubscribersEmailAddresses();

		$this->tester->assertCount(count($expectedResult), $emails);
		$result = $this->serializeEmails($emails);
		$intersection = array_intersect($expectedResult, $result);
		$this->tester->assertCount(count($foodsavers), $intersection, 'Result does not match expactations: ' . serialize($result));
	}

	public function testGetAllEmailAddressesFromNewsletterSubscribersExcludeFoodsharers()
	{
		$foodsavers = [$this->foodsaver];
		$expectedResult = $this->expectedEmailResult($foodsavers);

		$emails = $this->gateway->getNewsletterSubscribersEmailAddresses(Role::FOODSAVER);

		$this->tester->assertCount(count($expectedResult), $emails);
		$result = $this->serializeEmails($emails);
		$intersection = array_intersect($expectedResult, $result);
		$this->tester->assertCount(count($foodsavers), $intersection, 'Result does not match expactations: ' . serialize($result));
	}

	public function testGetAllEmailAddressesExcludeFoodsharers()
	{
		$foodsavers = [$this->foodsaver, $this->regionAdmin, $this->regionMember];
		$expectedResult = $this->expectedEmailResult($foodsavers);

		$emails = $this->gateway->getEmailAddresses(Role::FOODSAVER);

		$this->tester->assertCount(count($expectedResult), $emails);
		$result = $this->serializeEmails($emails);
		$intersection = array_intersect($expectedResult, $result);
		$this->tester->assertCount(count($foodsavers), $intersection, 'Result does not match expactations: ' . serialize($result));
	}

	public function testGetAllEmailAddressesFromStoreManagersOrBelow()
	{
		$foodsavers = [$this->foodsharer, $this->foodsaver, $this->regionMember];
		$expectedResult = $this->expectedEmailResult($foodsavers);

		$emails = $this->gateway->getEmailAddresses(Role::FOODSHARER, Role::STORE_MANAGER);

		$this->tester->assertCount(count($expectedResult), $emails);
		$result = $this->serializeEmails($emails);
		$intersection = array_intersect($expectedResult, $result);
		$this->tester->assertCount(count($foodsavers), $intersection, 'Result does not match expactations: ' . serialize($result));
	}

	public function testGetAllEmailAddressesFromRegion()
	{
		$foodsavers = [$this->regionAdmin, $this->regionMember];
		$expectedResult = $this->expectedEmailResult($foodsavers);

		$emails = $this->gateway->getEmailAddressesFromMainRegions([$this->region['id']]);

		$this->tester->assertCount(count($expectedResult), $emails);
		$result = $this->serializeEmails($emails);
		$intersection = array_intersect($expectedResult, $result);
		$this->tester->assertCount(count($foodsavers), $intersection, 'Result does not match expactations: ' . serialize($result));
	}

	private function expectedEmailResult(array $foodsavers): array
	{
		$out = [];
		foreach ($foodsavers as $fs) {
			$out[] = serialize([
				'id' => $fs['id'],
				'email' => $fs['email']
			]);
		}

		return $out;
	}

	private function serializeEmails(array $emails): array
	{
		$out = [];
		foreach ($emails as $e) {
			$out[] = serialize($e);
		}

		return $out;
	}

	public function testGetActiveAmbassadors()
	{
		$inactiveAmbassador = $this->tester->createAmbassador(null, ['active' => 0]);

		$foodsavers = $this->gateway->getActiveAmbassadors();

		$this->tester->assertCount(1, $foodsavers);
		$this->tester->assertEquals($this->regionAdmin['id'], $foodsavers[0]['id']);
	}

	public function testGetFoodsaversWithoutAmbassadors()
	{
		$foodsavers = $this->gateway->getFoodsaversWithoutAmbassadors();

		$this->tester->assertCount(3, $foodsavers);
	}

	public function testFoodsaverExists()
	{
		$randomNotExistingFsId = 1238513513;
		$this->tester->assertFalse($this->gateway->foodsaverExists($randomNotExistingFsId));
		$fs = $this->tester->createFoodsaver();
		$this->tester->assertTrue($this->gateway->foodsaverExists($fs['id']));
		$this->gateway->deleteFoodsaver($fs['id']);
		$this->tester->assertFalse($this->gateway->foodsaverExists($fs['id']));
	}

	public function testFoodsaversExist()
	{
		$randomNotExistingFsId = 1238513513;
		$fs = $this->tester->createFoodsaver();
		$fs2 = $this->tester->createFoodsaver();
		$this->tester->assertFalse($this->gateway->foodsaversExist([$randomNotExistingFsId, $fs['id']]));
		$this->tester->assertTrue($this->gateway->foodsaversExist([$fs['id']]));
		$this->tester->assertTrue($this->gateway->foodsaversExist([$fs['id'], $fs2['id']]));
	}
}
