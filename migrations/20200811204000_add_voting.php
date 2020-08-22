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
				'limit' => 10,
				'identity' => 'enable',
				'comment' => 'unique id of the poll'
			])
			->addColumn('region_id', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10,
				'comment' => 'region with which the poll is associated'
			])
			->addColumn('name', 'string', [
				'null' => true,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'comment' => 'title of the poll'
			])
			->addColumn('description', 'text', [
				'null' => true,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'comment' => 'description of the poll'
			])
			->addColumn('scope', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 2,
				'comment' => 'determines who will be invited to vote'
			])
			->addColumn('type', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 2,
				'comment' => 'determines how a vote is cast and which values are possible for each option'
			])
			->addColumn('start', 'datetime', [
				'null' => false,
				'comment' => 'start date and time for the poll'
			])
			->addColumn('end', 'datetime', [
				'null' => false,
				'comment' => 'end date and time for the poll'
			])
			->addColumn('author', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10,
				'comment' => 'id of the user who created the poll'
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
				'limit' => 10,
				'comment' => 'the poll to which this option belongs'
			])
			->addColumn('option', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 2,
				'comment' => 'index of the option'
			])
			->addColumn('option_text', 'string', [
				'null' => true,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'comment' => 'description text of the option'
			])
			->addForeignKey('poll_id', 'fs_poll', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			])
			->create();

		// table that counts votes for each value of an option
		$this->table('fs_poll_option_has_value', [
			'id' => false,
			'primary_key' => ['poll_id', 'option', 'value']
		])
			->addColumn('poll_id', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10,
				'comment' => 'the poll to which the option belongs'
			])
			->addColumn('option', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 2,
				'comment' => 'index of the option'
			])
			->addColumn('value', 'integer', [
				'null' => false,
				'signed' => true,
				'limit' => 2,
				'comment' => 'value for the option'
			])
			->addColumn('votes', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10,
				'default' => 0,
				'comment' => 'number of current votes for the value'
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
				'limit' => 10,
				'comment' => 'id of the voter'
			])
			->addColumn('poll_id', 'integer', [
				'null' => false,
				'signed' => false,
				'limit' => 10,
				'comment' => 'id of the poll'
			])
			->addColumn('has_voted', 'integer', [
				'null' => false,
				'limit' => 1,
				'default' => 0,
				'comment' => 'whether the voter has already voted in the poll'
			])
			->addColumn('time', 'datetime', [
				'null' => false,
				'default' => 'CURRENT_TIMESTAMP',
				'update' => 'CURRENT_TIMESTAMP',
				'comment' => 'time at which the voter has voted'
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
