<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class AddRegionOption extends AbstractMigration
{
	public function change(): void
	{
		$this->table('fs_region_options', [
			'id' => false,
			'primary_key' => ['region_id', 'option_type'],
		])
			->addColumn('region_id', 'integer', [
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
			->addForeignKey('region_id', 'fs_bezirk', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
			])
			->create();
	}
}
