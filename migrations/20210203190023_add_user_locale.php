<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class AddUserLocale extends AbstractMigration
{
	public function change(): void
	{
		$this->table('fs_foodsaver_has_options', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'option_type'],
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('option_type', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
				'comment' => 'category of the option',
			])
			->addColumn('option_value', 'string', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_SMALL,
				'after' => 'option_type',
				'comment' => 'value of the option'
			])
			->addColumn('option_date', 'datetime', [
				'null' => false,
				'after' => 'option_date',
				'comment' => 'last timestamp at which this option was changed',
			])
			->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
			])
			->create();
	}
}
