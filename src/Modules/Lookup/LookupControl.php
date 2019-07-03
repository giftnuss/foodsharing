<?php

namespace Foodsharing\Modules\Lookup;

use Foodsharing\Modules\Console\ConsoleControl;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class LookupControl extends ConsoleControl
{
	private $foodsaverGateway;
	private $lookupGateway;

	public function __construct(LookupGateway $lookupGateway, FoodsaverGateway $foodsaverGateway)
	{
		$this->lookupGateway = $lookupGateway;
		$this->foodsaverGateway = $foodsaverGateway;

		parent::__construct();
	}

	private function loadFile()
	{
		global $argv;
		$filename = $argv[3];
		if (!file_exists($filename)) {
			self::error('Could not load file ' . $filename);
			die();
		}

		$this->info('Loading emails from ' . $filename);

		return array_map('str_getcsv', file($filename));
	}

	public function lookup(): void
	{
		$csv = $this->loadFile();
		foreach ($csv as $row) {
			$email = $row[0];
			$fs = $this->lookupGateway->getFoodsaverByEmail($email);
			if (empty($fs)) {
				continue;
			}

			echo $fs['id'] . ',' . $fs['last_login'] . ',' . $fs['bezirk_id'] . ',' . $fs['name'] . ',' . $fs['nachname'] . implode(',', $row) . "\n";
		}
	}

	public function deleteOldUsers()
	{
		$csv = $this->loadFile();
		foreach ($csv as $row) {
			$email = $row[0];
			$fs = $this->lookupGateway->getFoodsaverByEmail($email);
			if (empty($fs)) {
				continue;
			}
			$date = new \DateTime($fs['last_login']);
			$olderThan = new \DateTime();
			$olderThan->sub(new \DateInterval('P6M'));
			if ($date < $olderThan) {
				$this->info('Deleted user ' . $fs['id']);
				$this->foodsaverGateway->del_foodsaver($fs['id']);
			}
		}
	}
}
