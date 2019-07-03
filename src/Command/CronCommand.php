<?php

namespace Foodsharing\Command;

use Foodsharing\Modules\Bell\BellUpdateTrigger;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Mails\MailsControl;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends Command
{
	protected static $defaultName = 'foodsharing:cronjob';

	/**
	 * @var BellUpdateTrigger
	 */
	private $bellUpdateTrigger;

	/**
	 * @var MailsControl
	 */
	private $mailsControl;

	public function __construct(BellUpdateTrigger $bellUpdateTrigger, StoreGateway $storeGateway, MailsControl $mailsControl)
	{
		$this->bellUpdateTrigger = $bellUpdateTrigger;
		$this->mailsControl = $mailsControl;
		/* storeGateway is intentionally injected because it registers an updatetrigger.
			TODO this mechanism seems very likely to get back on us :-) */
		($storeGateway);

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
		$this->bellUpdateTrigger->triggerUpdate();
		$this->mailsControl->fetchMails();
	}
}
