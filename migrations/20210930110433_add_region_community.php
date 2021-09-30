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
			'primary_key' => ['id'],
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10,
				'identity' => 'enable',
				'comment' => 'unique id of community pin'
			])
			->addColumn('region_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
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
				'limit' => MysqlAdapter::TEXT_SMALL,
				'after' => 'lat',
				'comment' => 'description'
			])
			->addForeignKey('region_id', 'fs_bezirk', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
			])
			->create();
	}
}
