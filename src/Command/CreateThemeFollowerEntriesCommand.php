<?php

namespace Foodsharing\Command;

use Foodsharing\Modules\Region\ForumFollowerGateway;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateThemeFollowerEntriesCommand extends Command
{
	protected static $defaultName = 'foodsharing:createThemeFollowerEntries';

	private ForumFollowerGateway $forumFollowerGateway;

	public function __construct(ForumFollowerGateway $forumFollowerGateway)
	{
		$this->forumFollowerGateway = $forumFollowerGateway;
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('Creates theme follower entries for all participants of a forum thread');
		$this->setHelp('This command goes together with change from 2020-05 release where bell notifications can be enabled/disabled per thread. This creates a default notification setting for all participants of a thread that do not have an info entry yet.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		$output->writeln('created ' . $this->forumFollowerGateway->createFollowerEntriesForExistingThreads() . ' follower entries');
	}
}
