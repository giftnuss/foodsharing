<?php

use Phinx\Migration\AbstractMigration;

class AddPollCreationDate extends AbstractMigration
{
	public function change()
	{
		$this->table('fs_poll')
			->addColumn('creation_timestamp', 'datetime', [
				'null' => false,
				'after' => 'author',
			])
			->update();
	}
}
