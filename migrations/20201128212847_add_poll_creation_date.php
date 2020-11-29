<?php

use Phinx\Migration\AbstractMigration;

class AddPollCreationDate extends AbstractMigration
{
	/**
	 * Adds the column 'creation_timestamp' to 'fs_poll' that is filled at creation of a poll.
	 */
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
