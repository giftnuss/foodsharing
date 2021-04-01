<?php

use Phinx\Migration\AbstractMigration;

class AddPushSubscriptionId extends AbstractMigration
{
	public function change()
	{
		// poll table
		$this->table('fs_push_notification_subscription')
			->addColumn('id', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10,
			])
			->changePrimaryKey('id')
			->save();
	}
}
