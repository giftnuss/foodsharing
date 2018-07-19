<?php

namespace Foodsharing\Modules\Migrate;

use Foodsharing\Modules\Console\ConsoleControl;

class MigrateControl extends ConsoleControl
{
	private $migrateGateway;

	public function __construct(MigrateGateway $migrateGateway)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
		$this->migrateGateway = $migrateGateway;
		parent::__construct();
	}

	public function ForumPostRemoveBr()
	{
		$num = $this->migrateGateway->forumPostsRemoveBr('2018-07-19 22:02:00');
		self::info('Migrated ' . $num . ' posts.');
	}
}
