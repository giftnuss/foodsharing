<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class AddVoting extends AbstractMigration
{
	public function change()
	{
		// poll table
		$this->table('fs_poll', [
			'id' => false,
			'primary_key' => ['id']
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10
			])
			->addColumn('region_id', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10
			])
			->addColumn('name', 'string', [
				'null' => true,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4'
			])
			->addColumn('description', 'text', [
				'null' => true,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4'
			])
			->addColumn('scope', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 2
			])
			->addColumn('type', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 2
			])
			->addColumn('start', 'datetime', [
				'null' => false
			])
			->addColumn('end', 'datetime', [
				'null' => false
			])
			->addColumn('author', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10
			])
			->addForeignKey('region_id', 'fs_bezirk', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			])
			->create();

		// options table
		$this->table('fs_poll_has_options', [
			'id' => false,
			'primary_key' => ['poll_id', 'option']
		])
			->addColumn('poll_id', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10
			])
			->addColumn('option', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 2
			])
			->addColumn('option_text', 'string', [
				'null' => true,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4'
			])
			->addColumn('upvotes', 'integer', [
				'null' => false,
				'signed' => true,
				'limit' => 10
			])
			->addColumn('neutralvotes', 'integer', [
				'null' => false,
				'signed' => true,
				'limit' => 10
			])
			->addColumn('downvotes', 'integer', [
				'null' => false,
				'signed' => true,
				'limit' => 10
			])
			->addForeignKey('poll_id', 'fs_poll', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			])
			->create();

		// voter table
		$this->table('fs_foodsaver_has_poll', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'poll_id']
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10
			])
			->addColumn('poll_id', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10
			])
			->addColumn('has_voted', 'integer', [
				'null' => false,
				'limit' => 1,
				'default' => 0
			])
			->addColumn('time', 'datetime', [
				'null' => false,
				'default' => 'CURRENT_TIMESTAMP',
				'update' => 'CURRENT_TIMESTAMP'
			])
			->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			])
			->addForeignKey('poll_id', 'fs_poll', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			])
			->create();
	}
}
