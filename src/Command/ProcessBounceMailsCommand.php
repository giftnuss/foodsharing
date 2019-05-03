<?php

namespace Foodsharing\Command;

use Foodsharing\Lib\Mail\BounceProcessing;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessBounceMailsCommand extends Command
{
	protected static $defaultName = 'foodsharing:process-bounce-emails';

	private $bounceProcessing;

	public function __construct(BounceProcessing $bounceProcessing)
	{
		$this->bounceProcessing = $bounceProcessing;
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('fetches email bounces and stores them in the database');
	}

	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		$this->bounceProcessing->process();
		$output->writeln('processed ' . $this->bounceProcessing->getNumberOfProcessedBounces() . ' bounces');
	}
}
