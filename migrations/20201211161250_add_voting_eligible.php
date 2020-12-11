<?php

use Phinx\Migration\AbstractMigration;

class AddVotingEligible extends AbstractMigration
{
	public function change()
	{
		// poll table
		$this->table('fs_poll')
			->addColumn('eligible_to_vote', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10,
				'default' => 0,
				'comment' => 'number of users who are eligible to vote'
			])
			->save();
	}
}
