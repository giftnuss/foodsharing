<?php

use Phinx\Migration\AbstractMigration;

class RemoveFaq extends AbstractMigration
{
	/**
	 * Change Method.
	 *
	 * Write your reversible migrations using this method.
	 *
	 * More information on writing migrations is available here:
	 * https://book.cakephp.org/phinx/0/en/migrations.html
	 *
	 * The following commands can be used in this method and Phinx will
	 * automatically reverse them when rolling back:
	 *
	 *    createTable
	 *    renameTable
	 *    addColumn
	 *    addCustomColumn
	 *    renameColumn
	 *    addIndex
	 *    addForeignKey
	 *
	 * Any other destructive changes will result in an error when trying to
	 * rollback the migration.
	 *
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 */
	public function change()
	{
		/*
		 * -- data upkeep for !1568
		* DROP TABLE fs_faq;
		* DROP TABLE fs_faq_category;
		*/

		$this->table('fs_faq')->drop()->save();
		$this->table('fs_faq_category')->drop()->save();
	}
}
