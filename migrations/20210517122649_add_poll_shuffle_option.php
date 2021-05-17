<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class AddPollShuffleOption extends AbstractMigration
{
	public function change(): void
	{
		$this->table('fs_poll')
			->addColumn('shuffle_options', 'integer', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'eligible_votes_count',
			])
			->update();
	}
}
