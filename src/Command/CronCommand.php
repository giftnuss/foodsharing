<?php

namespace Foodsharing\Command;

use Foodsharing\Modules\Mails\MailsControl;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends Command
{
	protected static $defaultName = 'foodsharing:cronjob';

	/**
	 * @var MailsControl
	 */
	private $mailsControl;

	public function __construct(MailsControl $mailsControl)
	{
		$this->mailsControl = $mailsControl;

		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('Executes regular maintenance tasks.');
		$this->setHelp('This command executes background tasks that need to be run in regular intervals.
		While the exact interval should not matter, it must still be chosen sane. See implementation for details.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		$this->mailsControl->fetchMails();
	}
}
