<?php

use Phinx\Migration\AbstractMigration;

class RegionWorkgroupFunction extends AbstractMigration
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
		/**
		 * from incremental-20200419-region-workgroup-function:
		 * CREATE TABLE `fs_region_function`.
		) ;
		 */
		$table = $this->table('fs_region_function');
		/* phinx always creates a primary key ID, so we don't need it here if that fits our needs. */
		/* fields are by default not nullable, so we need to specify that where applicable */
		$table->addColumn('region_id', 'integer', ['signed' => false])
			->addColumn('function_id', 'integer', ['signed' => false])
			->addColumn('target_id', 'integer', ['null' => true])
			/* the foreign key has been missing in the original migration, so the cool way would have been to write an extra migration for it.
			As I expect some more minor changes to bring this into production, I'll just add it here to keep migrations folder a bit cleaner */
			->addForeignKey('region_id', 'fs_bezirk', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
			->create();
	}
}
