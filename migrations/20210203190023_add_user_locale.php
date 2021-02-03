<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserLocale extends AbstractMigration
{
	public function change(): void
	{
		$this->table('fs_foodsaver')
			->addColumn('locale', 'string', [
				'null' => true,
				'limit' => 10,
				'comment' => 'frontend language selected by the user'
			])
			->save();
	}
}
