<?php

use Phinx\Migration\AbstractMigration;

class AddConversationStoreId extends AbstractMigration
{
	public function change()
	{
		$table = $this->table('fs_conversation');
		$table
			->addColumn('store_id', 'integer', [
				'null' => true,
				'after' => 'name',
				'comment' => 'ID of the associated store, if one exists',
			])
			->update()
		;
	}
}
