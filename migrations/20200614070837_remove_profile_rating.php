<?php

use Phinx\Migration\AbstractMigration;

class RemoveProfileRating extends AbstractMigration
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
		 * -- data upkeep for !1530
		* -- PK was previously: PRIMARY KEY (`foodsaver_id`, `rater_id`, `ratingtype`)
		* ALTER TABLE fs_rating DROP PRIMARY KEY;
		* ALTER TABLE fs_rating DROP IF EXISTS rating;
		* ALTER TABLE fs_rating DROP IF EXISTS ratingtype;
		* ALTER TABLE fs_rating ADD PRIMARY KEY(`foodsaver_id`, `rater_id`);
		 */

		$table = $this->table('fs_rating');
		/* this migration cannot be rolled back, as we are using methods that are not in the list above. That's allright, we don't want to rollback. */

		/* we need to run the changePrimaryKey command on its own as phinx doesn't automatically put them in the right order - it runs this change
		after removing the columns otherwise, which wouldn't work. */
		$table->changePrimaryKey(['foodsaver_id', 'rater_id'])
			->update();

		/* I don't know if it is valid to use a table object for multiple updates, but it works, does the right things and even puts it in the
		same transaction */
		$table->removeColumn('rating')
			->removeColumn('ratingtype')
			->update();
	}
}
