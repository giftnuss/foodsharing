<?php

use Foodsharing\Modules\Region\ForumFollowerGateway;

class ForumFollowerGatewayTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	private ForumFollowerGateway $gateway;

	public function _before()
	{
		$this->gateway = $this->tester->get(ForumFollowerGateway::class);
		parent::_before();
	}

	public function testCreateFollowerEntriesForExistingThreads()
	{
		/* call it initially because the database is not initially empty */
		$this->gateway->createFollowerEntriesForExistingThreads();
		$region = $this->tester->createRegion();
		$user1 = $this->tester->createFoodsaver();
		$user2 = $this->tester->createFoodsaver();
		$thread1 = $this->tester->addForumTheme($region['id'], $user1['id']);
		$thread2 = $this->tester->addForumTheme($region['id'], $user1['id']);
		$post1 = $this->tester->addForumThemePost($thread1['id'], $user2['id']);
		/* Initially, both users have no notifications (as our test helper only creaetes the actual thread; if that changes, this test needs to be changed */
		$this->tester->dontSeeInDatabase('fs_theme_follower', ['foodsaver_id' => $user1['id']]);
		$this->tester->dontSeeInDatabase('fs_theme_follower', ['foodsaver_id' => $user2['id']]);
		/* Disable notifications for user1, thread 2 */
		$this->tester->haveInDatabase('fs_theme_follower', ['foodsaver_id' => $user1['id'], 'theme_id' => $thread2['id'], 'infotype' => 0, 'bell_notification' => '0']);
		$this->assertEquals(2, $this->gateway->createFollowerEntriesForExistingThreads());
		$this->tester->seeInDatabase('fs_theme_follower', ['foodsaver_id' => $user1['id'], 'theme_id' => $thread1['id'], 'infotype' => 0, 'bell_notification' => 1]);
		$this->tester->seeInDatabase('fs_theme_follower', ['foodsaver_id' => $user1['id'], 'theme_id' => $thread2['id'], 'infotype' => 0, 'bell_notification' => 0]);
		$this->tester->seeInDatabase('fs_theme_follower', ['foodsaver_id' => $user2['id'], 'theme_id' => $thread1['id'], 'infotype' => 0, 'bell_notification' => 1]);
		$this->tester->dontSeeInDatabase('fs_theme_follower', ['foodsaver_id' => $user2['id'], 'theme_id' => $thread2['id']]);
	}
}
