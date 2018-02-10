<?php

namespace Foodsharing\Debug;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Symfony\Component\Console\Command\Command;
use Codeception\CustomCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Faker;

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

	protected function seed()
	{
		$I = $this->helper;
		$faker = Faker\Factory::create('de_DE');
		$bezirk1 = '241'; // this is called 'Göttingen'
		$bezirk_vorstand = '1373';
		$ag_quiz = '341';

		$I->createFoodsharer('user1', ['email' => 'user1@example.com', 'name' => 'One', 'bezirk_id' => $bezirk1]);
		$user2 = $I->createFoodsaver('user2', ['email' => 'user2@example.com', 'name' => 'Two', 'bezirk_id' => $bezirk1]);
		$userbot = $I->createAmbassador('userbot', [
			'email' => 'userbot@example.com',
			'name' => 'Bot',
			'bezirk_id' => $bezirk1,
			'about_me_public' => 'hello!'
		]);
		$I->createOrga('userbot', false, ['email' => 'userorga@example.com', 'name' => 'Orga', 'bezirk_id' => $bezirk1]);

		$I->addBezirkMember($bezirk1, $userbot['id'], true);
		$I->addBezirkMember($bezirk1, $user2['id']);

		$I->addBezirkMember($ag_quiz, $userbot['id'], true);

		$I->addBezirkMember($bezirk_vorstand, $userbot['id']);
		$I->addBezirkMember('1565', $userbot['id']);

		$I->addBezirkMember('1564', $user2['id']);

		$conv1 = $I->createConversation(['name' => 'betrieb_bla']);
		$conv2 = $I->createConversation(['name' => 'springer_bla']);

		$I->addUserToConversation($user2['id'], $conv1['id']);
		$I->addUserToConversation($userbot['id'], $conv1['id']);

		$I->addUserToConversation($userbot['id'], $conv2['id']);

		$store = $I->createStore($bezirk1, [
			'name' => 'asd',
			'status' => 0,
			'springer_conversation_id' => $conv1['id'],
			'team_conversation_id' => $conv2['id']
		]);
		$I->addStoreTeam($store['id'], $user2['id']);
		$I->addStoreTeam($store['id'], $userbot['id'], true);

		$I->addCollector($userbot['id'], $store['id'], '2017-04-15 09:00:00');
		$I->addCollector($userbot['id'], $store['id'], '2017-04-19 08:00:00');
		$I->addCollector($userbot['id'], $store['id'], '2017-06-19 09:00:00');
		$I->addCollector($userbot['id'], $store['id'], '2017-06-20 09:00:00');

		$theme = $I->addForumTheme($bezirk1, $userbot['id'], $faker->jobTitle, $faker->realText(1000), $faker->dateTime());
		$I->addForumThemePost($theme, $user2['id'], $faker->realText(100));

		// load test
		foreach(range(0, 200) as $number) {
			$saver = $I->createFoodsaver('user', ['bezirk_id' => $bezirk1]);
			$I->addBezirkMember($bezirk1, $saver['id']);
			$I->addStoreTeam($store['id'], $saver['id']);
			$I->addCollector($saver['id'], $store['id'], $faker->dateTime);
			$I->addStoreNotiz($saver['id'], $store['id'], $faker->realText(50), $faker->dateTime());
			$I->addForumThemePost($theme, $saver['id'], $faker->realText(100), $faker->dateTime());

			if ($number > 0 && $number % 100 == 0) {
				$this->output->writeln($number);
			}
		}
	}
}
