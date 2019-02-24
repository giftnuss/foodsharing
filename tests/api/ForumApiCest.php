<?php

class ForumApiCest
{
	private $tester;
	private $user;
	private $region;
	private $thread;
	private $faker;

	public function _before(\ApiTester $I)
	{
		$this->tester = $I;
		$this->user = $I->createFoodsaver();
		$this->region = $I->createRegion();
		$I->addBezirkMember($this->region['id'], $this->user['id']);
		$this->thread = $I->addForumTheme($this->region['id'], $this->user['id']);
		$this->faker = Faker\Factory::create('de_DE');
	}

	/**
	 * @param ApiTester $I
	 */
	public function deleteNonExistingForumPostIs404(\ApiTester $I): void
	{
		$I->login($this->user['email']);
		$I->sendDELETE('api/forum/post/9999999');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
		$I->seeResponseIsJson();
	}

	/**
	 * @param ApiTester $I
	 */
	public function deleteOwnPostSucceeds(\ApiTester $I): void
	{
		$I->login($this->user['email']);
		$I->sendDELETE('api/forum/post/' . $this->thread['post']['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	/**
	 * @param ApiTester $I
	 */
	public function deleteForeignPostFails403(\ApiTester $I): void
	{
		$foreigner = $I->createFoodsaver();
		$I->login($foreigner['email']);
		$I->sendDELETE('api/forum/post/' . $this->thread['post']['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
		$I->seeResponseIsJson();
	}

	/**
	 * @param ApiTester $I
	 * @throws Exception
	 */
	public function canUseEmojis(\ApiTester $I): void
	{
		$I->login($this->user['email']);
		$body = 'I am so 😂 for you! ' . $this->faker->text(50);
		$threadPath = 'api/forum/thread/' . $this->thread['id'];
		$I->sendPOST($threadPath . '/posts', [
			'body' => $body
		]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NO_CONTENT);
		$I->seeInDatabase('fs_theme_post', ['body' => $body]);
		$I->sendGET($threadPath);
		$I->seeResponseIsJson();
		$I->assertEquals(
			'<p>' . $body . '</p>',
			$I->grabDataFromResponseByJsonPath('$.data.posts[1].body')[0]
		);
	}
}
