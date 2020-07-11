<?php

use Phinx\Migration\AbstractMigration;

class AddForumSearchIndex extends AbstractMigration
{
	public function change()
	{
		$this->table('fs_theme')
			->addIndex(['name'], [
				'name' => 'name',
				'unique' => false,
				'type' => 'fulltext',
			])
			->save();
	}
}
