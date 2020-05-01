<?php

namespace Foodsharing\Command;

use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Services\StoreService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixStoreChatNamesCommand extends Command
{
	protected static $defaultName = 'foodsharing:fixStoreChatNames';

	/**
	 * @var StoreGateway
	 */
	private $storeGateway;

	/**
	 * @var StoreService
	 */
	private $storeService;

	public function __construct(StoreGateway $storeGateway, StoreService $storeService)
	{
		$this->storeGateway = $storeGateway;
		$this->storeService = $storeService;

		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('Updates all store conversation names to the current store names.');
		$this->setHelp('This command should just be needed as a one-time fix to bring all store conversation names up to date. It can also be used whenever conventions on store chat naming changes.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		$stores = $this->storeGateway->getStores();
		foreach ($stores as $store) {
			$this->storeService->setStoreNameInConversations($store['id'], $store['name']);
		}
	}
}
