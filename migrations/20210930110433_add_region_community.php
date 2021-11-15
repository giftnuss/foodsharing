<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class AddRegionCommunity extends AbstractMigration
{
	public function change(): void
	{
		$this->table('fs_region_pin', [
			'id' => false,
			'primary_key' => ['region_id'],
		])
			->addColumn('region_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'comment' => 'region id'
			])
			->addColumn('lat', 'string', [
				'null' => false,
				'limit' => 20,
				'after' => 'region_id',
				'comment' => 'latitude'
			])
			->addColumn('lon', 'string', [
				'null' => false,
				'limit' => 20,
				'after' => 'lat',
				'comment' => 'longitude'
			])
			->addColumn('desc', 'string', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_REGULAR,
				'after' => 'lat',
				'comment' => 'description'
			])
			->addColumn('status', 'integer', [
				'null' => false,
				'default' => 0,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'desc',
				'comment' => 'state of the pin'
			])
			->addForeignKey('region_id', 'fs_bezirk', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
			])
			->create();
	}
}
