<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddForumThreadStatus extends AbstractMigration
{
	public function change(): void
	{
		$this->table('fs_theme')
			->addColumn('status', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => 10,
				'signed' => false,
				'comment' => 'status of the thread (open or closed)',
			])
			->save();
	}
}
