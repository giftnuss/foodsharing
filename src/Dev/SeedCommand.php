<?php

namespace Foodsharing\Dev;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Symfony\Component\Console\Command\Command;
use Codeception\CustomCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SeedCommand extends Command implements CustomCommandInterface
{
	use \Codeception\Command\Shared\Config;

	/**
	 * @var \Helper\Foodsharing
	 */
	protected $helper;

	/**
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	protected $output;

	protected $foodsavers = [];

	/**
	 * returns the name of the command.
	 *
	 * @return string
	 */
	public static function getCommandName()
	{
		return 'foodsharing:seed';
	}

	public function getDescription()
	{
		return 'seed the dev db';
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->output = $output;

		$config = $this->getGlobalConfig();
		$di = new Di();
		$module = new ModuleContainer($di, $config);
		$this->helper = $module->create('\Helper\Foodsharing');
		$this->helper->_initialize();

		// Clear existing data to prevent collisions
		$this->helper->clear();

		$this->seed();
	}

	protected function getRandomUser($number = 1)
	{
		$rand = array_rand($this->foodsavers, $number);

		if ($number === 1) {
			return $this->foodsavers[$rand];
		}
		if (count($rand) > 0) {
			return array_intersect_key($this->foodsavers, $rand);
		}

		return [];
	}

	protected function seed()
	{
		$I = $this->helper;
		$bezirk1 = '241'; // this is called 'Göttingen'
		$bezirk_vorstand = '1373';
		$ag_aktive = '1565';
		$ag_testimonials = '1564';
		$ag_quiz = '341';

		$I->createFoodsharer('user', ['email' => 'user1@example.com', 'name' => 'One', 'bezirk_id' => $bezirk1]);
		$user2 = $I->createFoodsaver('user', ['email' => 'user2@example.com', 'name' => 'Two', 'bezirk_id' => $bezirk1]);
		$userbot = $I->createAmbassador('user', [
			'email' => 'userbot@example.com',
			'name' => 'Bot',
			'bezirk_id' => $bezirk1,
			'about_me_public' => 'hello!'
		]);
		$userorga = $I->createOrga('user', false, ['email' => 'userorga@example.com', 'name' => 'Orga', 'bezirk_id' => $bezirk1]);

		$I->addBezirkAdmin($bezirk1, $userbot['id']);
		$I->addBezirkMember($ag_quiz, $userbot['id']);
		$I->addBezirkAdmin($ag_quiz, $userbot['id']);

		$I->addBezirkMember($bezirk_vorstand, $userbot['id']);
		$I->addBezirkMember($ag_aktive, $userbot['id']);

		$I->addBezirkMember($ag_testimonials, $user2['id']);

		$conv1 = $I->createConversation([$userbot['id'], $user2['id']], ['name' => 'betrieb_bla']);
		$conv2 = $I->createConversation([$userbot['id']], ['name' => 'springer_bla']);
		$I->addConversationMessage($userbot['id'], $conv1['id']);
		$I->addConversationMessage($userbot['id'], $conv2['id']);

		$store = $I->createStore($bezirk1, $conv1['id'], $conv2['id'], ['betrieb_status_id' => 5]);
		$I->addStoreTeam($store['id'], $user2['id']);
		$I->addStoreTeam($store['id'], $userbot['id'], true);
		$I->addRecurringPickup($store['id']);

		$theme = $I->addForumTheme($bezirk1, $userbot['id']);
		$I->addForumThemePost($theme['id'], $user2['id']);

		$fairteiler = $I->createFairteiler($userbot['id'], $bezirk1);
		$I->addFairteilerFollower($user2['id'], $fairteiler['id']);
		$I->addFairteilerPost($userbot['id'], $fairteiler['id']);

		// create users and collect their ids in a list
		$this->foodsavers = [$user2['id'], $userbot['id'], $userorga['id']];
		foreach (range(0, 100) as $_) {
			$user = $I->createFoodsaver('user', ['bezirk_id' => $bezirk1]);
			$this->foodsavers[] = $user['id'];
			$I->addStoreTeam($store['id'], $user['id']);
			$I->addCollector($user['id'], $store['id']);
			$I->addStoreNotiz($user['id'], $store['id']);
			$I->addForumThemePost($theme['id'], $user['id']);
		}

		// create conversations between users
		foreach ($this->foodsavers as $user) {
			foreach ($this->getRandomUser(10) as $chatpartner) {
				if ($user !== $chatpartner) {
					$conv = $I->createConversation([$user, $chatpartner]);
					$I->addConversationMessage($user, $conv['id']);
					$I->addConversationMessage($chatpartner, $conv['id']);
				}
			}
		}

		// create more stores
		foreach (range(0, 20) as $_) {
			// TODO conversations are missing the other store members
			$conv1 = $I->createConversation([$userbot['id']], ['name' => 'team']);
			$conv2 = $I->createConversation([$userbot['id']], ['name' => 'springer']);

			$store = $I->createStore($bezirk1, $conv1['id'], $conv2['id']);
			foreach (range(0, 5) as $_) {
				$I->addRecurringPickup($store['id']);
			}
		}

		// create foodbaskets
		foreach (range(0, 500) as $_) {
			$user = $this->getRandomUser();
			$foodbasket = $I->createFoodbasket($user);
			$commenter = $this->getRandomUser();
			$I->addFoodbasketWallpost($commenter, $foodbasket['id']);
		}

		// create fairteiler
		foreach ($this->getRandomUser(50) as $user) {
			$fairteiler = $I->createFairteiler($user, $bezirk1);
			foreach ($this->getRandomUser(10) as $follower) {
				if ($user !== $follower) {
					$I->addFairteilerFollower($follower, $fairteiler['id']);
				}
				$I->addFairteilerPost($follower, $fairteiler['id']);
			}
		}

		foreach (range(0, 20) as $_) {
			$I->addBlogPost($userbot['id'], $bezirk1);
		}
	}
}
