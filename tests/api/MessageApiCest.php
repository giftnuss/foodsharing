<?php

use Codeception\Util\HttpCode;

class MessagesApiCest
{
	private $user;
	private $faker;

	public function _before(ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
		$this->faker = Faker\Factory::create('de_DE');
	}

	public function getAllConversations(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendGET('api/conversations');
		$I->seeResponseIsJson();
	}

	public function getSingleConversation(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendGET('api/conversations/1');
		$I->seeResponseIsJson();
	}

	public function canFetchConversationWithDeletedUser(ApiTester $I)
	{
		$deletedUser = $I->createFoodsaver(null, [
			'verified' => 0,
			'rolle' => 0,
			'plz' => null,
			'stadt' => null,
			'lat' => null,
			'lon' => null,
			'photo' => null,
			'email' => null,
			'password' => null,
			'name' => null,
			'nachname' => null,
			'anschrift' => null,
			'telefon' => null,
			'handy' => null,
			'geb_datum' => null,
			'deleted_at' => $this->faker->dateTime($max = '-1 week')->format('Y-m-d\TH:i:s'),
		]);
		$conv = $I->createConversation([$this->user['id'], $deletedUser['id']]);

		$I->login($this->user['email']);
		$I->sendGET('api/conversations/' . $conv['id']);
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseIsJson();
	}
}
