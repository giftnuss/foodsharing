<?php

namespace Foodsharing\Dev;

use Carbon\Carbon;
use Codeception\CustomCommandInterface;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\WorkGroup\WorkGroupGateway;
use Helper\Foodsharing;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SeedCommand extends Command implements CustomCommandInterface
{
	use \Codeception\Command\Shared\Config;

	/**
	 * @var Foodsharing
	 */
	protected $helper;

	/**
	 * @var OutputInterface
	 */
	protected $output;

	protected $foodsavers = [];
	protected $stores = [];

	/**
	 * @var WorkGroupGateway
	 */
	protected $workGroupGateway;

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
		$this->output->writeln('Clearing existing ' . FS_ENV . ' seed data');
		$this->helper->clear();

		$this->output->writeln('Seeding ' . FS_ENV . ' database');
		$this->seed();

		$this->output->writeln('All done!');
	}

	protected function getRandomIDOfArray(array $value, $number = 1)
	{
		$rand = array_rand($value, $number);
		if ($number === 1) {
			return $value[$rand];
		}
		if (count($rand) > 0) {
			return array_intersect_key($value, $rand);
		}

		return [];
	}

	protected function CreateMorePickups()
	{
		for ($m = 0; $m <= 10; ++$m) {
			$store_id = $this->getRandomIDOfArray($this->stores);
			for ($i = 0; $i <= 10; ++$i) {
				$pickupDate = Carbon::create(2019, 4, random_int(1, 30), random_int(1, 24), random_int(1, 59));
				for ($k = 0; $k <= 2; ++$k) {
					$foodSaver_id = $this->getRandomIDOfArray($this->foodsavers);
					$this->helper->addCollector($foodSaver_id, $store_id, ['date' => $pickupDate->toDateTimeString()]);
				}
			}
			$this->output->write('.');
		}
	}

	private function writeUser($user, $password, $name = 'user')
	{
		$this->output->writeln('- created ' . $name . ' ' . $user['email'] . ' with password "' . $password . '"');
	}

	protected function seed()
	{
		$I = $this->helper;
		$region1 = '241'; // this is called 'Göttingen'
		$region_vorstand = RegionIDs::TEAM_BOARD_MEMBER;
		$ag_aktive = RegionIDs::TEAM_ADMINISTRATION_MEMBER;
		$ag_testimonials = RegionIDs::TEAM_BOARD_MEMBER;
		$ag_quiz = RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP;
		$password = 'user';
		$region1WorkGroup = '1135'; // workgroup 'Schnippelparty Göttingen' from 'Göttingen'

		// Create users
		$this->output->writeln('Create basic users:');
		$user1 = $I->createFoodsharer($password, ['email' => 'user1@example.com', 'name' => 'One', 'bezirk_id' => $region1]);
		$this->writeUser($user1, $password, 'foodsharer');

		$user2 = $I->createFoodsaver($password, ['email' => 'user2@example.com', 'name' => 'Two', 'bezirk_id' => $region1]);
		$this->writeUser($user2, $password, 'foodsaver');

		$userStoreManager = $I->createStoreCoordinator($password, ['email' => 'storemanager1@example.com', 'name' => 'Three', 'bezirk_id' => $region1]);
		$this->writeUser($userStoreManager, $password, 'store coordinator');

		$userbot = $I->createAmbassador($password, [
			'email' => 'userbot@example.com',
			'name' => 'Bot',
			'bezirk_id' => $region1,
			'about_me_intern' => 'hello!'
		]);
		$this->writeUser($userbot, $password, 'ambassador');

		$userorga = $I->createOrga($password, false, ['email' => 'userorga@example.com', 'name' => 'Orga', 'bezirk_id' => $region1]);
		$this->writeUser($userorga, $password, 'orga');
		$this->output->writeln('- done');

		$this->output->writeln('Create some user interaction:');
		// Create buddyset
		$I->addBuddy($userbot['id'], $userorga['id']);

		// Add users to region
		$this->output->writeln('- add users to region');
		$I->addRegionAdmin($region1, $userbot['id']);
		$I->addRegionMember($ag_quiz, $userbot['id']);
		$I->addRegionAdmin($ag_quiz, $userbot['id']);
		$I->addRegionMember($region_vorstand, $userbot['id']);
		$I->addRegionMember($ag_aktive, $userbot['id']);

		$I->addRegionMember($ag_testimonials, $user2['id']);

		// Make ambassador responsible for all work groups in the region
		$this->output->writeln('- make ambassador responsible for all work groups');
		$workGroupsIds = $I->grabColumnFromDatabase('fs_bezirk', 'id', ['parent_id' => $region1, 'type' => Type::WORKING_GROUP]);
		foreach ($workGroupsIds as $id) {
			$I->addRegionMember($id, $userbot['id']);
			$I->addRegionAdmin($id, $userbot['id']);
		}

		// Create store team conversations
		$this->output->writeln('- create store team conversations');
		$conv1 = $I->createConversation([$userbot['id'], $user2['id'], $userStoreManager['id']], ['name' => 'betrieb_bla', 'locked' => 1]);
		$conv2 = $I->createConversation([$userbot['id']], ['name' => 'springer_bla', 'locked' => 1]);
		$I->addConversationMessage($userStoreManager['id'], $conv1['id']);
		$I->addConversationMessage($userbot['id'], $conv1['id']);
		$I->addConversationMessage($userbot['id'], $conv2['id']);

		// Create a store and add team members
		$this->output->writeln('- create store and add team members');
		$store = $I->createStore($region1, $conv1['id'], $conv2['id'], ['betrieb_status_id' => 5]);
		$I->addStoreTeam($store['id'], $user2['id']);
		$I->addStoreTeam($store['id'], $userStoreManager['id'], true);
		$I->addStoreTeam($store['id'], $userbot['id'], true);
		$I->addRecurringPickup($store['id']);

		// Forum theads and posts
		$this->output->writeln('- create forum threads and posts');
		$theme = $I->addForumTheme($region1, $userbot['id']);
		$I->addForumThemePost($theme['id'], $user2['id']);
		$theme = $I->addForumTheme($region1, $user2['id']);
		$I->addForumThemePost($theme['id'], $user1['id']);
		$theme = $I->addForumTheme($region1, $user1['id']);
		$I->addForumThemePost($theme['id'], $userorga['id']);

		$this->output->writeln('- follow a food share point');
		$foodSharePoint = $I->createFoodSharePoint($userbot['id'], $region1);
		$I->addFoodSharePointFollower($user2['id'], $foodSharePoint['id']);
		$I->addFoodSharePointPost($userbot['id'], $foodSharePoint['id']);
		$this->output->writeln('- done');

		// create users and collect their ids in a list
		$this->output->writeln('Create some more users');
		$this->foodsavers = [$user2['id'], $userbot['id'], $userorga['id']];
		foreach (range(0, 100) as $_) {
			$user = $I->createFoodsaver($password, ['bezirk_id' => $region1]);
			$this->foodsavers[] = $user['id'];
			$I->addStoreTeam($store['id'], $user['id']);
			$I->addCollector($user['id'], $store['id']);
			$I->addStoreNotiz($user['id'], $store['id']);
			$I->addForumThemePost($theme['id'], $user['id']);
			$this->output->write('.');
		}
		$this->output->writeln(' done');

		// create conversations between users
		$this->output->writeln('Create conversations between users');
		foreach ($this->foodsavers as $user) {
			foreach ($this->getRandomIDOfArray($this->foodsavers, 10) as $chatpartner) {
				if ($user !== $chatpartner) {
					$conv = $I->createConversation([$user, $chatpartner]);
					$I->addConversationMessage($user, $conv['id']);
					$I->addConversationMessage($chatpartner, $conv['id']);
				}
			}
			$this->output->write('.');
		}
		$this->output->writeln(' done');

		// add some users to a workgroup
		$this->output->writeln('Add users to workgroup');
		// but only the ones we generated above
		$randomFsList = array_slice($this->foodsavers, -100, 100, true);
		foreach ($this->getRandomIDOfArray($randomFsList, 10) as $random_user) {
			$I->addRegionMember($region1WorkGroup, $random_user);
			$this->output->write('.');
		}
		$this->output->writeln(' done');

		// create more stores and collect their ids in a list
		$this->output->writeln('Create some stores');
		$this->stores = [$store['id']];
		foreach (range(0, 40) as $_) {
			// TODO conversations are missing the other store members
			$conv1 = $I->createConversation([$userbot['id']], ['name' => 'team', 'locked' => 1]);
			$conv2 = $I->createConversation([$userbot['id']], ['name' => 'springer', 'locked' => 1]);

			$store = $I->createStore($region1, $conv1['id'], $conv2['id']);
			foreach (range(0, 5) as $_) {
				$I->addRecurringPickup($store['id']);
			}
			$this->stores[] = $store['id'];
			$this->output->write('.');
		}
		$this->output->writeln(' done');

		$this->output->writeln('Create more pickups');
		$this->CreateMorePickups();
		$this->output->writeln(' done');

		// create foodbaskets
		$this->output->writeln('Create foodbaskets');
		foreach (range(0, 500) as $_) {
			$user = $this->getRandomIDOfArray($this->foodsavers);
			$foodbasket = $I->createFoodbasket($user);
			$commenter = $this->getRandomIDOfArray($this->foodsavers);
			$I->addFoodbasketWallpost($commenter, $foodbasket['id']);
			$this->output->write('.');
		}
		$this->output->writeln(' done');

		// create food share point
		$this->output->writeln('Create food share points');
		foreach ($this->getRandomIDOfArray($this->foodsavers, 50) as $user) {
			$foodSharePoint = $I->createFoodSharePoint($user, $region1);
			foreach ($this->getRandomIDOfArray($this->foodsavers, 10) as $follower) {
				if ($user !== $follower) {
					$I->addFoodSharePointFollower($follower, $foodSharePoint['id']);
				}
				$I->addFoodSharePointPost($follower, $foodSharePoint['id']);
			}
			$this->output->write('.');
		}
		$this->output->writeln(' done');

		$this->output->writeln('Create blog posts');
		foreach (range(0, 20) as $_) {
			$I->addBlogPost($userbot['id'], $region1);
			$this->output->write('.');
		}
		$this->output->writeln(' done');

		$this->output->writeln('Create reports');
		foreach (range(0, 4) as $_) {
			$I->addReport($this->getRandomIDOfArray($this->foodsavers), $this->getRandomIDOfArray($this->foodsavers), 0, 0);
			$this->output->write('.');
		}

		foreach (range(0, 3) as $_) {
			$I->addReport($this->getRandomIDOfArray($this->foodsavers), $this->getRandomIDOfArray($this->foodsavers), 0, 1);
			$this->output->write('.');
		}
		$this->output->writeln(' done');

		$this->output->writeln('Create quizzes');
		foreach (range(1, 3) as $quizRole) {
			$I->createQuiz($quizRole, 3);
			$this->output->write('.');
		}
		$this->output->writeln(' done');
	}
}
