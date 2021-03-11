<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAustriaPartnerContentId extends AbstractMigration
{
	/**
	 * Adds the content id 79 for the Austrian partners page. The content and last modification timestamp are copied
	 * from the partners page for Germany.
	 */
	public function change(): void
	{
		$partners = $this->fetchRow('SELECT body,last_mod FROM fs_content WHERE id = 10');
		$this->table('fs_content')
			->insert([
				'id' => '79',
				'name' => 'partner-au',
				'title' => 'Partner Österreich',
				'body' => $partners['body'],
				'last_mod' => $partners['last_mod']
			])
			->save();
	}
}
