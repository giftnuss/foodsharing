<?php

namespace Foodsharing\Dev;

use Carbon\Carbon;
use Codeception\CustomCommandInterface;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingScope;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingType;
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
	protected $welcomeAdmins = [];
	protected $votingAdmins = [];
	protected $fspAdmins = [];
	protected $reportAdmins = [];
	protected $arbitrationAdmins = [];
	protected $mediationAdmins = [];
	protected $storesGroupAdmins = [];
	protected $fsManagementAdmins = [];
	protected $prAdmins = [];
	protected $moderationAdmins = [];

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

	protected function configure(): void
	{
		$this->setDescription('Seed the dev db.');
		$this->setHelp('This commands adds seed data to the database. The general rule is that before running this command, you have a working instance of foodsharing without customized data (e.g. missing regions, quizzes, ...) but already including all the data that is directly used in the code (so you will not get any internal server errors). The future goal is, to make the code as much independent of data as possible and move all data you may want to playing around into the seed.');
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

		return 0;
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

	protected function CreateFunctionWorkgroups(int $region1)
	{
		$I = $this->helper;
		$password = 'user';
		// Create a welcome Group
		$this->output->writeln('- create welcome group');
		$welcomeGroup = $I->createWorkingGroup('Begrüßung Göttingen', ['parent_id' => $region1, 'email_name' => 'Begruessung.Göttingen', 'teaser' => 'Hier sind die Begrüßer für unseren Bezirk']);
		$I->haveInDatabase('fs_region_function', ['region_id' => $welcomeGroup['id'], 'function_id' => WorkgroupFunction::WELCOME, 'target_id' => $region1]);
		foreach (range(1, 4) as $i) {
			$user = $I->createStoreCoordinator($password, ['email' => 'userwelcome' . $i . '@example.com', 'bezirk_id' => $region1]);
			$I->addRegionMember($welcomeGroup['id'], $user['id']);
			$I->addRegionAdmin($welcomeGroup['id'], $user['id']);
//			$this->output->writeln(' User ' . $user['id'] . ' added to ' . $welcomeGroup['id']);
			$this->welcomeAdmins[] = $user['id'];
		}
		$this->output->writeln(' done');

		// Create voting Group
		$this->output->writeln('- create voting group');
		$votingGroup = $I->createWorkingGroup('Abstimmungen Göttingen', ['parent_id' => $region1, 'email_name' => 'Abstimmung.Goettingen', 'teaser' => 'Hier sind die Abstimmungen für unseren Bezirk']);
		$I->haveInDatabase('fs_region_function', ['region_id' => $votingGroup['id'], 'function_id' => WorkgroupFunction::VOTING, 'target_id' => $region1]);
		foreach (range(1, 4) as $i) {
			$user = $I->createStoreCoordinator($password, ['email' => 'uservoting' . $i . '@example.com', 'bezirk_id' => $region1]);
			$I->addRegionMember($votingGroup['id'], $user['id']);
			$I->addRegionAdmin($votingGroup['id'], $user['id']);
//			$this->output->writeln(' User ' . $user['id'] . ' added to ' . $votingGroup['id']);
			$this->votingAdmins[] = $user['id'];
		}
		$this->output->writeln(' done');

		// Create fsp Group
		$this->output->writeln('- create fsp group');
		$fspGroup = $I->createWorkingGroup('Fairteiler Göttingen', ['parent_id' => $region1, 'email_name' => 'Fairteiler.Goettingen', 'teaser' => 'Hier sind die Fairteileransprechpartner für unseren Bezirk']);
		$I->haveInDatabase('fs_region_function', ['region_id' => $fspGroup['id'], 'function_id' => WorkgroupFunction::FSP, 'target_id' => $region1]);
		foreach (range(1, 2) as $i) {
			$user = $I->createStoreCoordinator($password, ['email' => 'userfsp' . $i . '@example.com', 'bezirk_id' => $region1]);
			$I->addRegionMember($fspGroup['id'], $user['id']);
			$I->addRegionAdmin($fspGroup['id'], $user['id']);
//			$this->output->writeln(' User ' . $user['id'] . ' added to ' . $welcomeGroup['id']);
			$this->fspAdmins[] = $user['id'];
		}
		$this->output->writeln(' done');

		// Create STORESAdmins Group
		$this->output->writeln('- create store coordination group');
		$storesGroup = $I->createWorkingGroup('Betriebskoordination Göttingen', ['parent_id' => $region1, 'email_name' => 'betriebskoordination.Goettingen', 'teaser' => 'Hier sind die Betriebskoordinationsansprechpartner für unseren Bezirk']);
		$I->haveInDatabase('fs_region_function', ['region_id' => $storesGroup['id'], 'function_id' => WorkgroupFunction::STORES, 'target_id' => $region1]);
		foreach (range(1, 3) as $i) {
			$user = $I->createStoreCoordinator($password, ['email' => 'userstoregroup' . $i . '@example.com', 'bezirk_id' => $region1]);
			$I->addRegionMember($storesGroup['id'], $user['id']);
			$I->addRegionAdmin($storesGroup['id'], $user['id']);
//			$this->output->writeln(' User ' . $user['id'] . ' added to ' . $storesGroup['id']);
			$this->storesGroupAdmin[] = $user['id'];
		}
		$this->output->writeln(' done');

		// Create REPORTAdmins Group
		$this->output->writeln('- create report group');
		$reportGroup = $I->createWorkingGroup('Meldungsbearbeitung Göttingen', ['parent_id' => $region1, 'email_name' => 'meldungsbearbeitung.Goettingen', 'teaser' => 'Hier sind die Meldungsbearbeiter für unseren Bezirk']);
		$I->haveInDatabase('fs_region_function', ['region_id' => $reportGroup['id'], 'function_id' => WorkgroupFunction::REPORT, 'target_id' => $region1]);
		foreach (range(1, 4) as $i) {
			$user = $I->createStoreCoordinator($password, ['email' => 'userreport' . $i . '@example.com', 'bezirk_id' => $region1]);
			$I->addRegionMember($reportGroup['id'], $user['id']);
			$I->addRegionAdmin($reportGroup['id'], $user['id']);
//			$this->output->writeln(' User ' . $user['id'] . ' added to ' . $reportGroup['id']);
			$this->reportAdmins[] = $user['id'];
		}
		$this->output->writeln(' done');

		// Create MediationAdmins Group
		$this->output->writeln('- create mediation group');
		$mediationGroup = $I->createWorkingGroup('Mediation Göttingen', ['parent_id' => $region1, 'email_name' => 'Mediation Göttingen', 'email' => 'mediation.goettingen', 'teaser' => 'Hier sind die Meldungsbearbeiter für unseren Bezirk']);
		$I->haveInDatabase('fs_region_function', ['region_id' => $mediationGroup['id'], 'function_id' => WorkgroupFunction::MEDIATION, 'target_id' => $region1]);
		foreach (range(1, 3) as $i) {
			$user = $I->createStoreCoordinator($password, ['email' => 'usermediation' . $i . '@example.com', 'bezirk_id' => $region1]);
			$I->addRegionMember($mediationGroup['id'], $user['id']);
			$I->addRegionAdmin($mediationGroup['id'], $user['id']);
//			$this->output->writeln(' User ' . $user['id'] . ' added to ' . $mediationGroup['id']);
			$this->mediationAdmins[] = $user['id'];
		}
		$this->output->writeln(' done');

		// Create ArbitrationAdmins Group
		$this->output->writeln('- create arbitration group');
		$arbitrationGroup = $I->createWorkingGroup('Schiedsstelle Göttingen', ['parent_id' => $region1, 'email_name' => 'schiedstelle.Goettingen', 'teaser' => 'Hier ist das Schiedsstellenteam für unseren Bezirk']);
		$I->haveInDatabase('fs_region_function', ['region_id' => $arbitrationGroup['id'], 'function_id' => WorkgroupFunction::ARBITRATION, 'target_id' => $region1]);
		foreach (range(1, 4) as $i) {
			$user = $I->createStoreCoordinator($password, ['email' => 'userarbitration' . $i . '@example.com', 'bezirk_id' => $region1]);
			$I->addRegionMember($arbitrationGroup['id'], $user['id']);
			$I->addRegionAdmin($arbitrationGroup['id'], $user['id']);
//			$this->output->writeln(' User ' . $user['id'] . ' added to ' . $arbitrationGroup['id']);
			$this->arbitrationAdmins[] = $user['id'];
		}
		$this->output->writeln(' done');

		// Create FSMANAGEMENT Group
		$this->output->writeln('- create fsmanagement group');
		$fsmanagementGroup = $I->createWorkingGroup('Verwaltung Göttingen', ['parent_id' => $region1, 'email_name' => 'verwaltung.Goettingen', 'teaser' => 'Hier ist das Verwaltungsteam für unseren Bezirk']);
		$I->haveInDatabase('fs_region_function', ['region_id' => $fsmanagementGroup['id'], 'function_id' => WorkgroupFunction::FSMANAGEMENT, 'target_id' => $region1]);
		foreach (range(1, 3) as $i) {
			$user = $I->createStoreCoordinator($password, ['email' => 'userpr' . $i . '@example.com', 'bezirk_id' => $region1]);
			$I->addRegionMember($fsmanagementGroup['id'], $user['id']);
			$I->addRegionAdmin($fsmanagementGroup['id'], $user['id']);
//			$this->output->writeln(' User ' . $user['id'] . ' added to ' . $fsmanagementGroup['id']);
			$this->fsManagementAdmins[] = $user['id'];
		}
		$this->output->writeln(' done');

		// Create PR Group
		$this->output->writeln('- create pr group');
		$prGroup = $I->createWorkingGroup('Öffentlichkeitsarbeit Göttingen', ['parent_id' => $region1, 'email_name' => 'oeffentlichkeitsarbeit.Goettingen', 'teaser' => 'Hier ist das Öffentlichkeitsarbeitsteam für unseren Bezirk']);
		$I->haveInDatabase('fs_region_function', ['region_id' => $prGroup['id'], 'function_id' => WorkgroupFunction::PR, 'target_id' => $region1]);
		foreach (range(1, 5) as $i) {
			$user = $I->createStoreCoordinator($password, ['email' => 'userfsmanagement' . $i . '@example.com', 'bezirk_id' => $region1]);
			$I->addRegionMember($prGroup['id'], $user['id']);
			$I->addRegionAdmin($prGroup['id'], $user['id']);
//			$this->output->writeln(' User ' . $user['id'] . ' added to ' . $prGroup['id']);
			$this->prGroup[] = $user['id'];
		}
		$this->output->writeln(' done');

		// Create MODERATION Group
		$this->output->writeln('- create moderation group');
		$moderationGroup = $I->createWorkingGroup('Moderation Göttingen', ['parent_id' => $region1, 'email_name' => 'moderation.Goettingen', 'teaser' => 'Hier ist das Moderationsteam für unseren Bezirk']);
		$I->haveInDatabase('fs_region_function', ['region_id' => $moderationGroup['id'], 'function_id' => WorkgroupFunction::MODERATION, 'target_id' => $region1]);
		foreach (range(1, 5) as $i) {
			$user = $I->createStoreCoordinator($password, ['email' => 'usermoderation' . $i . '@example.com', 'bezirk_id' => $region1]);
			$I->addRegionMember($moderationGroup['id'], $user['id']);
			$I->addRegionAdmin($moderationGroup['id'], $user['id']);
//			$this->output->writeln(' User ' . $user['id'] . ' added to ' . $moderationGroup['id']);
			$this->moderationAdmins[] = $user['id'];
		}
		$this->output->writeln(' done');
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
		$I->_getDbh()->beginTransaction();
		$I->_getDriver()->executeQuery('SET FOREIGN_KEY_CHECKS=0;', []);
		$regionOne = $I->createRegion('Göttingen', ['has_children' => 1]);
		$region1 = $regionOne['id'];
		$regionTwo = $I->createRegion('Entenhausen');
		$region2 = $regionTwo['id'];
		$regionOneWorkGroup = $I->createWorkingGroup('Schnippelparty Göttingen', ['parent_id' => $regionOne['id']]);
		$region_vorstand = RegionIDs::TEAM_BOARD_MEMBER;
		$ag_aktive = RegionIDs::TEAM_ADMINISTRATION_MEMBER;
		$ag_testimonials = RegionIDs::TEAM_BOARD_MEMBER;
		$ag_quiz = RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP;
		$ag_startpage = RegionIDs::PR_START_PAGE;
		$ag_partnerandteam = RegionIDs::PR_PARTNER_AND_TEAM_WORK_GROUP;
		$password = 'user';
		$region1WorkGroup = $regionOneWorkGroup['id']; // workgroup 'Schnippelparty Göttingen' from 'Göttingen'

		$region1Subregion = $I->createRegion('Stadtteil von Göttingen', ['type' => Type::PART_OF_TOWN, 'parent_id' => $region1]);

		// Create users
		$this->output->writeln('Create basic users:');
		$user1 = $I->createFoodsharer($password, ['email' => 'user1@example.com', 'name' => 'One', 'bezirk_id' => $region1]);
		$this->writeUser($user1, $password, 'foodsharer');

		$user2 = $I->createFoodsaver($password, ['email' => 'user2@example.com', 'name' => 'Two', 'bezirk_id' => $region1]);
		$this->writeUser($user2, $password, 'foodsaver');

		$userStoreManager = $I->createStoreCoordinator($password, ['email' => 'storemanager1@example.com', 'name' => 'Three', 'bezirk_id' => $region1]);
		$this->writeUser($userStoreManager, $password, 'store coordinator');

		$userStoreManager2 = $I->createStoreCoordinator($password, ['email' => 'storemanager2@example.com', 'name' => 'Four', 'bezirk_id' => $region1]);
		$this->writeUser($userStoreManager2, $password, 'store coordinator2');

		$userbot = $I->createAmbassador($password, [
			'email' => 'userbot@example.com',
			'name' => 'Bot',
			'bezirk_id' => $region1,
			'about_me_intern' => 'hello!'
		]);
		$this->writeUser($userbot, $password, 'ambassador');

		$userbot2 = $I->createAmbassador($password, [
			'email' => 'userbot2@example.com',
			'name' => 'Bot2',
			'bezirk_id' => $region1,
			'about_me_intern' => 'hello!'
		]);
		$this->writeUser($userbot2, $password, 'ambassador');

		$userbotregion2 = $I->createAmbassador($password, [
			'email' => 'userbotreg2@example.com',
			'name' => 'Bot Entenhausen',
			'bezirk_id' => $region2,
			'about_me_intern' => 'hello!'
		]);
		$I->addRegionAdmin($region2, $userbotregion2['id']);

		$this->writeUser($userbotregion2, $password, 'ambassador');

		$userorga = $I->createOrga($password, false, ['email' => 'userorga@example.com', 'name' => 'Orga', 'bezirk_id' => $region1]);
		$this->writeUser($userorga, $password, 'orga');
		$this->output->writeln('- done');

		$this->output->writeln('Create some user interaction:');
		// Create buddyset
		$I->addBuddy($userbot['id'], $userorga['id']);

		// Add users to region
		$this->output->writeln('- add users to region');
		$I->addRegionAdmin($region1, $userbot['id']);
		$I->addRegionAdmin($region1, $userbot2['id']);
		$I->addRegionMember($ag_quiz, $userbot['id']);
		$I->addRegionAdmin($ag_quiz, $userbot['id']);
		$I->addRegionMember($ag_startpage, $userStoreManager['id']);
		$I->addRegionAdmin($ag_startpage, $userStoreManager['id']);
		$I->addRegionMember($ag_partnerandteam, $userStoreManager2['id']);
		$I->addRegionAdmin($ag_partnerandteam, $userStoreManager2['id']);
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

		$this->output->writeln('- create store chains');
		foreach (range(0, 50) as $_) {
			$I->addStoreChain();
			$this->output->write('.');
		}
		$this->output->writeln(' done');

		$this->output->writeln('- create food types');
		foreach (range(0, 10) as $_) {
			$I->addStoreFoodType();
			$this->output->write('.');
		}
		$this->output->writeln(' done');

		// Forum theads and posts
		$this->output->writeln('- create forum threads and posts');
		$thread = $I->addForumThread($region1, $userbot['id']);
		$I->addForumThreadPost($thread['id'], $user2['id']);
		$thread = $I->addForumThread($region1, $user2['id']);
		$I->addForumThreadPost($thread['id'], $user1['id']);
		$thread = $I->addForumThread($region1, $user1['id']);
		$I->addForumThreadPost($thread['id'], $userorga['id']);

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
			$I->addForumThreadPost($thread['id'], $user['id']);
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

		// Create more Forum Threads
		$this->output->writeln('- Create more forum Threads');
		$randomFsList = array_slice($this->foodsavers, -100, 100, true);
		foreach ($this->getRandomIDOfArray($randomFsList, 30) as $random_user) {
			foreach (range(0, 5) as $_) {
				$I->addForumThread($region1, $random_user);
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

		$this->CreateFunctionWorkgroups($region1);

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
			$I->createFoodbasket($user);
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

		$I->addReport($this->getRandomIDOfArray($this->reportAdmins), $this->getRandomIDOfArray($this->foodsavers), 0, 0);
		$I->addReport($this->getRandomIDOfArray($this->foodsavers), $this->getRandomIDOfArray($this->reportAdmins), 0, 0);
		$I->addReport($this->getRandomIDOfArray($this->arbitrationAdmins), $this->getRandomIDOfArray($this->foodsavers), 0, 0);
		$I->addReport($this->getRandomIDOfArray($this->foodsavers), $this->getRandomIDOfArray($this->arbitrationAdmins), 0, 0);
		$I->addReport($this->getRandomIDOfArray($this->reportAdmins), $this->getRandomIDOfArray($this->arbitrationAdmins), 0, 0);
		$I->addReport($this->getRandomIDOfArray($this->arbitrationAdmins), $this->getRandomIDOfArray($this->reportAdmins), 0, 0);
		$I->addReport($this->getRandomIDOfArray($this->foodsavers), $this->getRandomIDOfArray($this->foodsavers), 0, 0);

		$this->output->writeln(' done');

		$this->output->writeln('Create quizzes');
		foreach (range(1, 3) as $quizRole) {
			$I->createQuiz($quizRole, 3);
			$this->output->write('.');
		}
		$this->output->writeln(' done');

		$this->output->writeln('Create polls');
		foreach ([VotingType::SELECT_ONE_CHOICE, VotingType::SELECT_MULTIPLE, VotingType::THUMB_VOTING,
					 VotingType::SCORE_VOTING] as $type) {
			$this->createPoll($region1, $userbot['id'], $type,
				[$user2['id'], $userStoreManager['id'], $userStoreManager2['id'], $userbot['id'], $userorga['id']]
			);
			$this->output->write('.');
		}
		$this->createPoll($region1, $userbot['id'], VotingType::SELECT_ONE_CHOICE,
			[$user2['id'], $userStoreManager['id'], $userStoreManager2['id'], $userbot['id'], $userorga['id']],
			Carbon::now('-14 days'), Carbon::now('-7 days')
		);
		$this->output->write('.');

		$this->output->writeln(' done');

		$I->_getDriver()->executeQuery('SET FOREIGN_KEY_CHECKS=1;', []);
		$I->_getDbh()->commit();
	}

	private function createPoll(int $regionId, int $authorId, int $type, array $voterIds,
								?Carbon $startDate = null, ?Carbon $endDate = null)
	{
		$possibleValues = [];
		switch ($type) {
			case VotingType::SELECT_ONE_CHOICE:
			case VotingType::SELECT_MULTIPLE:
				$possibleValues = [1];
				break;
			case VotingType::THUMB_VOTING:
				$possibleValues = [1, 0, -1];
				break;
			case VotingType::SCORE_VOTING:
				$possibleValues = [3, 2, 1, 0, -1, -2, -3];
				break;
		}

		$params = ['type' => $type, 'scope' => VotingScope::FOODSAVERS];
		if (!is_null($startDate)) {
			$params['start'] = $startDate->format('Y-m-d H:i:s');
		}
		if (!is_null($endDate)) {
			$params['end'] = $endDate->format('Y-m-d H:i:s');
		}

		$poll = $this->helper->createPoll($regionId, $authorId, $params);
		foreach (range(0, 3) as $_) {
			$this->helper->createPollOption($poll['id'], $possibleValues);
		}
		$this->helper->addVoters($poll['id'], $voterIds);
	}
}
