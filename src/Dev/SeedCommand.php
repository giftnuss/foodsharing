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

		$conv1 = $I->createConversation([$userbot['id'], $user2['id']], ['name' => 'betrieb_bla']);
		$conv2 = $I->createConversation([$userbot['id']], ['name' => 'springer_bla']);
		$I->addConversationMessage($userbot['id'], $conv1['id']);
		$I->addConversationMessage($userbot['id'], $conv2['id']);

		$store = $I->createStore($bezirk1, $conv1['id'], $conv2['id']);
		$I->addStoreTeam($store['id'], $user2['id']);
		$I->addStoreTeam($store['id'], $userbot['id'], true);

		$theme = $I->addForumTheme($bezirk1, $userbot['id']);
		$I->addForumThemePost($theme['id'], $user2['id']);

		$fairteiler = $I->createFairteiler($userbot['id'], $bezirk1);
		$I->addFairteilerFollower($user2['id'], $fairteiler['id']);
		$I->addFairteilerPost($userbot['id'], $fairteiler['id']);

		// load test
		foreach (range(0, 200) as $number) {
			$saver = $I->createFoodsaver('user', ['bezirk_id' => $bezirk1]);
			$I->addBezirkMember($bezirk1, $saver['id']);
			$I->addStoreTeam($store['id'], $saver['id']);
			$I->addCollector($saver['id'], $store['id']);
			$I->addStoreNotiz($saver['id'], $store['id']);
			$I->addForumThemePost($theme['id'], $saver['id']);

			if ($number > 0 && $number % 100 == 0) {
				$this->output->writeln($number);
			}
		}
	}
}
