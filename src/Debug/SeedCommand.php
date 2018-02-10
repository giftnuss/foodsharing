<?php

namespace Foodsharing\Debug;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Symfony\Component\Console\Command\Command;
use Codeception\CustomCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SeedCommand extends Command implements CustomCommandInterface
{
	use \Codeception\Command\Shared\FileSystem;
	use \Codeception\Command\Shared\Config;

	/**
	 * @var \Helper\Foodsharing
	 */
	protected $helper;

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

		$user1 = $I->createFoodsharer('user1', ['email' => 'user1@example.com', 'name' => 'One']);
		$user2 = $I->createFoodsharer('user2', ['email' => 'user2@example.com', 'name' => 'Two']);
		$userbot = $I->createAmbassador('userbot', ['email' => 'userbot@example.com', 'name' => 'Bot']);
		$I->createOrga('userbot', false, ['email' => 'userorga@example.com', 'name' => 'Orga']);

		$I->addBezirkMember($bezirk1, $userbot['id'], true);
		$I->addBezirkMember($bezirk1, $user1['id']);
		$I->addBezirkMember($bezirk1, $user2['id']);

		$I->addBezirkMember('1373', $userbot['id']);
		$I->addBezirkMember('1565', $userbot['id']);

		$I->addBezirkMember('1564', $user1['id']);

		$conv1 = $I->createConversation(['name' => 'betrieb_bla']);
		$conv2 = $I->createConversation(['name' => 'springer_bla']);

		$I->addUserToConversation($user2['id'], $conv1['id']);
		$I->addUserToConversation($userbot['id'], $conv1['id']);

		$I->addUserToConversation($userbot['id'], $conv2['id']);

		$store = $I->createStore('241', [
			'name' => 'asd',
			'status' => 0,
			'springer_conversation_id' => $conv1['id'],
			'team_conversation_id' => $conv2['id']
		]);
		$I->addStoreTeam($store['id'], $user1['id']);
		$I->addStoreTeam($store['id'], $userbot['id'], true);

		$I->addCollector($userbot['id'], $store['id'], '2017-04-15 09:00:00');
		$I->addCollector($userbot['id'], $store['id'], '2017-04-19 08:00:00');
		$I->addCollector($userbot['id'], $store['id'], '2017-06-19 09:00:00');
		$I->addCollector($userbot['id'], $store['id'], '2017-06-20 09:00:00');
	}
}
