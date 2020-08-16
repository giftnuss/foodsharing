<?php

namespace Foodsharing\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
	protected static $defaultName = 'foodsharing:setup';

	protected function configure(): void
	{
		$this->setDescription('Prepares the environment to run the foodsharing application.');
		$this->setHelp('This command creates necessary folders so they can be used inside the app. It might be expanded to do more a-like things as well.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->mkdirs();

		return 0;
	}

	private function mkdirs(): void
	{
		$dirs = ['images', 'images/basket', 'images/wallpost', 'images/picture', 'images/workgroup', 'data/attach', 'data/mailattach', 'data/mailattach/tmp', 'data/pass', 'data/visite', 'cache/searchindex', 'tmp'];
		umask(0);
		foreach ($dirs as $dir) {
			if (!file_exists($dir)) {
				mkdir($dir, 0770, true);
			}
		}
	}
}
