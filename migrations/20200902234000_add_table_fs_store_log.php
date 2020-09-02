<?php

use Phinx\Migration\AbstractMigration;

class AddTableFsStoreLog extends AbstractMigration
{
	public function change()
	{
		$this->table('fs_store_log', [
			'primary_key' => ['id']
		])
			->addColumn('store_id', 'integer', ['null' => false, 'limit' => 10, 'comment' => 'ID of Store'])
			->addColumn('date_activity', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => 'when did the action take place'])
			->addColumn('action', 'integer', ['null' => false, 'limit' => 4, 'comment' => 'action type that was performed'])
			->addColumn('fs_id_a', 'integer', ['null' => false, 'limit' => 10, 'comment' => 'foodsaver_id who is doing the action'])
			->addColumn('fs_id_p', 'integer', ['limit' => 10, 'comment' => 'to which foodsaver_id is it done to'])
			->addColumn('date_reference', 'datetime', ['null' => true, 'comment' => 'date referenced (slot or wallpost entry)'])
			->addColumn('content', 'string', ['null' => true, 'limit' => 255, 'comment' => 'Text from the store-wall-entry'])
			->addColumn('reason', 'string', ['null' => true, 'limit' => 255, 'comment' => 'Why a negativ action was done'])
			->create();
	}
}
