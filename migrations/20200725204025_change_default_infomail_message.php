<?php

use Phinx\Migration\AbstractMigration;

class ChangeDefaultInfomailMessage extends AbstractMigration
{
	public function up()
	{
		$this->execute('ALTER TABLE `fs_foodsaver` CHANGE `infomail_message` `infomail_message` TINYINT(1) NULL DEFAULT NULL;');
	}
}
