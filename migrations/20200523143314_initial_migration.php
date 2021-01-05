<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Util\Literal;

/* More migration history can be found in e2bfd22332cd32fa58a975f86298ce3174d98c7f where pure SQL migrations have been transformed to phinx */
class InitialMigration extends Phinx\Migration\AbstractMigration
{
	public function change()
	{
		$this->execute('set sql_mode="NO_AUTO_VALUE_ON_ZERO";');
		$this->execute("ALTER DATABASE CHARACTER SET 'utf8mb4';");
		$this->execute("ALTER DATABASE COLLATE='utf8mb4_unicode_ci';");
		$this->table('fs_basket', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('status', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('time', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'status',
			])
			->addColumn('update', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'time',
			])
			->addColumn('until', 'date', [
				'null' => false,
				'after' => 'update',
			])
			->addColumn('fetchtime', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'until',
			])
			->addColumn('description', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'fetchtime',
			])
			->addColumn('picture', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 150,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'description',
			])
			->addColumn('tel', 'string', [
				'null' => false,
				'default' => '',
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'picture',
			])
			->addColumn('handy', 'string', [
				'null' => false,
				'default' => '',
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'tel',
			])
			->addColumn('contact_type', 'string', [
				'null' => false,
				'default' => '1',
				'limit' => 20,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'handy',
			])
			->addColumn('location_type', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'contact_type',
			])
			->addColumn('weight', 'float', [
				'null' => true,
				'default' => null,
				'after' => 'location_type',
			])
			->addColumn('lat', Literal::from('float(10,6)'), [
				'null' => false,
				'default' => '0.000000',
				'after' => 'weight',
			])
			->addColumn('lon', Literal::from('float(10,6)'), [
				'null' => false,
				'default' => '0.000000',
				'after' => 'lat',
			])
			->addColumn('bezirk_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'lon',
			])
			->addColumn('fs_id', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '10',
				'after' => 'bezirk_id',
			])
			->addColumn('appost', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'fs_id',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'basket_FKIndex1',
				'unique' => false,
			])
			->addIndex(['bezirk_id'], [
				'name' => 'bezirk_id',
				'unique' => false,
			])
			->addIndex(['lat', 'lon'], [
				'name' => 'lat',
				'unique' => false,
			])
			->addIndex(['fs_id'], [
				'name' => 'fs_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_basket_anfrage', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'basket_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('basket_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('status', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'basket_id',
			])
			->addColumn('time', 'datetime', [
				'null' => false,
				'after' => 'status',
			])
			->addColumn('appost', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'time',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'foodsaver_has_basket_FKIndex1',
				'unique' => false,
			])
			->addIndex(['basket_id'], [
				'name' => 'foodsaver_has_basket_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_basket_has_art', [
			'id' => false,
			'primary_key' => ['basket_id', 'art_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('basket_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('art_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'basket_id',
			])
			->create();
		$this->table('fs_basket_has_types', [
			'id' => false,
			'primary_key' => ['basket_id', 'types_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('basket_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('types_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'basket_id',
			])
			->create();
		$this->table('fs_answer', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('question_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('text', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'question_id',
			])
			->addColumn('explanation', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'text',
			])
			->addColumn('right', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'explanation',
			])
			->addIndex(['question_id'], [
				'name' => 'answer_FKIndex1',
				'unique' => false,
			])
			->create();
		$this->table('fs_fetchweight', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_REGULAR,
			])
			->addColumn('weight', 'decimal', [
				'null' => false,
				'precision' => 5,
				'scale' => 1,
				'after' => 'id',
			])
			->create();
		$this->table('fs_mailchange', [
			'id' => false,
			'primary_key' => ['foodsaver_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('newmail', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'foodsaver_id',
			])
			->addColumn('time', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'newmail',
			])
			->addColumn('token', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 300,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'time',
			])
			->create();
		$this->table('fs_apitoken', [
			'id' => false,
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('token', 'string', [
				'null' => false,
				'limit' => 255,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'foodsaver_id',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'foodsaver_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_email_blacklist', [
			'id' => false,
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('email', 'string', [
				'null' => false,
				'limit' => 255,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
			])
			->addColumn('since', 'timestamp', [
				'null' => false,
				'default' => 'CURRENT_TIMESTAMP',
				'after' => 'email',
			])
			->addColumn('reason', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'since',
			])
			->create();
		$this->table('fs_quiz_session', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('quiz_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('status', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'quiz_id',
			])
			->addColumn('quiz_index', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'status',
			])
			->addColumn('quiz_questions', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'quiz_index',
			])
			->addColumn('quiz_result', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'quiz_questions',
			])
			->addColumn('time_start', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'quiz_result',
			])
			->addColumn('time_end', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'time_start',
			])
			->addColumn('fp', 'decimal', [
				'null' => true,
				'default' => null,
				'precision' => 5,
				'scale' => 2,
				'after' => 'time_end',
			])
			->addColumn('maxfp', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'fp',
			])
			->addColumn('quest_count', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'maxfp',
			])
			->addColumn('easymode', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'quest_count',
			])
			->addIndex(['quiz_id'], [
				'name' => 'quiz_result_FKIndex1',
				'unique' => false,
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'quiz_result_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_question_has_quiz', [
			'id' => false,
			'primary_key' => ['question_id', 'quiz_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('question_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('quiz_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'question_id',
			])
			->addColumn('fp', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'quiz_id',
			])
			->addIndex(['question_id'], [
				'name' => 'question_has_quiz_FKIndex1',
				'unique' => false,
			])
			->addIndex(['quiz_id'], [
				'name' => 'question_has_quiz_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_fetchdate', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('betrieb_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('time', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'betrieb_id',
			])
			->addColumn('fetchercount', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'time',
			])
			->addIndex(['betrieb_id', 'time'], [
				'name' => 'betrieb_id',
				'unique' => true,
			])
			->addIndex(['betrieb_id'], [
				'name' => 'fetchdate_FKIndex1',
				'unique' => false,
			])
			->create();
		$this->table('fs_quiz', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'id',
			])
			->addColumn('desc', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'name',
			])
			->addColumn('maxfp', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_SMALL,
				'signed' => false,
				'after' => 'desc',
			])
			->addColumn('questcount', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_SMALL,
				'signed' => false,
				'after' => 'maxfp',
			])
			->create();
		$this->table('fs_stat_abholmengen', [
			'id' => false,
			'primary_key' => ['betrieb_id', 'date'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('betrieb_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('date', 'datetime', [
				'null' => false,
				'after' => 'betrieb_id',
			])
			->addColumn('abholmenge', 'decimal', [
				'null' => false,
				'precision' => 5,
				'scale' => 1,
				'after' => 'date',
			])
			->addIndex(['betrieb_id', 'date'], [
				'name' => 'betrieb_id',
				'unique' => true,
			])
			->create();
		$this->table('fs_question', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('text', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'id',
			])
			->addColumn('duration', 'integer', [
				'null' => false,
				'limit' => '3',
				'signed' => false,
				'after' => 'text',
			])
			->addColumn('wikilink', 'string', [
				'null' => false,
				'limit' => 250,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'duration',
			])
			->create();
		$this->table('fs_bezirk_closure', [
			'id' => false,
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('bezirk_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('ancestor_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'bezirk_id',
			])
			->addColumn('depth', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'ancestor_id',
			])
			->addIndex(['ancestor_id'], [
				'name' => 'ancestor_id',
				'unique' => false,
			])
			->addIndex(['bezirk_id'], [
				'name' => 'bezirk_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_email_bounces', [
			'id' => false,
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_general_ci',
			'comment' => '',
		])
			->addColumn('email', 'string', [
				'null' => false,
				'limit' => 255,
				'collation' => 'utf8mb4_general_ci',
				'encoding' => 'utf8mb4',
			])
			->addColumn('bounced_at', 'datetime', [
				'null' => false,
				'after' => 'email',
			])
			->addColumn('bounce_category', 'string', [
				'null' => false,
				'limit' => 255,
				'collation' => 'utf8mb4_general_ci',
				'encoding' => 'utf8mb4',
				'after' => 'bounced_at',
			])
			->addIndex(['email'], [
				'name' => 'email',
				'unique' => false,
			])
			->create();
		$this->table('fs_contact', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 180,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'id',
			])
			->addColumn('email', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 180,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'name',
			])
			->addIndex(['email'], [
				'name' => 'email',
				'unique' => true,
			])
			->create();
		$this->table('fs_report', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('reporter_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('reporttype', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'reporter_id',
			])
			->addColumn('betrieb_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'reporttype',
			])
			->addColumn('time', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'betrieb_id',
			])
			->addColumn('committed', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'time',
			])
			->addColumn('msg', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'committed',
			])
			->addColumn('tvalue', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 300,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'msg',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'report_FKIndex1',
				'unique' => false,
			])
			->addIndex(['reporter_id'], [
				'name' => 'report_reporter',
				'unique' => false,
			])
			->addIndex(['betrieb_id'], [
				'name' => 'report_betrieb',
				'unique' => false,
			])
			->create();
		$this->table('fs_bell', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'id',
			])
			->addColumn('body', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'name',
			])
			->addColumn('vars', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'body',
			])
			->addColumn('attr', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 500,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'vars',
			])
			->addColumn('icon', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 150,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'attr',
			])
			->addColumn('identifier', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 40,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'icon',
			])
			->addColumn('time', 'datetime', [
				'null' => false,
				'after' => 'identifier',
			])
			->addColumn('closeable', 'integer', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'time',
			])
			->addColumn('expiration', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'closeable',
			])
			->addIndex(['expiration'], [
				'name' => 'expiration',
				'unique' => false,
			])
			->create();
		$this->table('fs_faq', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('faq_kategorie_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 500,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'faq_kategorie_id',
			])
			->addColumn('answer', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'name',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'faq_FKIndex1',
				'unique' => false,
			])
			->addIndex(['faq_kategorie_id'], [
				'name' => 'faq_kategorie_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_abholzeiten', [
			'id' => false,
			'primary_key' => ['betrieb_id', 'dow', 'time'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('betrieb_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('dow', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'betrieb_id',
			])
			->addColumn('time', 'time', [
				'null' => false,
				'default' => '00:00:00',
				'after' => 'dow',
			])
			->addColumn('fetcher', 'integer', [
				'null' => false,
				'default' => '4',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'time',
			])
			->create();
		$this->table('fs_msg', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('conversation_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'conversation_id',
			])
			->addColumn('body', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'foodsaver_id',
			])
			->addColumn('time', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'body',
			])
			->addColumn('is_htmlentity_encoded', 'boolean', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'time',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'message_FKIndex1',
				'unique' => false,
			])
			->addIndex(['conversation_id', 'time'], [
				'name' => 'message_conversationTimeIndex',
				'unique' => false,
			])
			->create();
		$this->table('fs_content', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 20,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'id',
			])
			->addColumn('title', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'name',
			])
			->addColumn('body', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'title',
			])
			->addColumn('last_mod', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'body',
			])
			->create();
		$this->table('fs_application_has_wallpost', [
			'id' => false,
			'primary_key' => ['application_id', 'wallpost_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('application_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('wallpost_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'application_id',
			])
			->addIndex(['application_id'], [
				'name' => 'application_id',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'wallpost_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_basket_has_wallpost', [
			'id' => false,
			'primary_key' => ['basket_id', 'wallpost_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('basket_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('wallpost_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'basket_id',
			])
			->addIndex(['basket_id'], [
				'name' => 'basket_has_wallpost_FKIndex1',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'basket_has_wallpost_FKIndex2',
				'unique' => false,
			])
			->addIndex(['basket_id'], [
				'name' => 'basket_id',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'wallpost_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_bezirk_has_theme', [
			'id' => false,
			'primary_key' => ['theme_id', 'bezirk_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('theme_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('bezirk_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'theme_id',
			])
			->addColumn('bot_theme', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'bezirk_id',
			])
			->addIndex(['bezirk_id'], [
				'name' => 'bezirk_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_betrieb_has_lebensmittel', [
			'id' => false,
			'primary_key' => ['betrieb_id', 'lebensmittel_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('betrieb_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('lebensmittel_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'betrieb_id',
			])
			->create();
		$this->table('fs_betrieb_notiz', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('betrieb_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('milestone', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'betrieb_id',
			])
			->addColumn('text', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'milestone',
			])
			->addColumn('zeit', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'text',
			])
			->addColumn('last', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'zeit',
			])
			->addIndex(['betrieb_id'], [
				'name' => 'betrieb_notitz_FKIndex1',
				'unique' => false,
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'betrieb_notiz_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_bezirk', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('parent_id', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => MysqlAdapter::INT_REGULAR,
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('has_children', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'parent_id',
			])
			->addColumn('type', 'integer', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'has_children',
			])
			->addColumn('teaser', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'type',
			])
			->addColumn('desc', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'teaser',
			])
			->addColumn('photo', 'string', [
				'null' => false,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'desc',
			])
			->addColumn('master', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '10',
				'signed' => false,
				'after' => 'photo',
			])
			->addColumn('mailbox_id', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '10',
				'signed' => false,
				'after' => 'master',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'mailbox_id',
			])
			->addColumn('email', 'string', [
				'null' => false,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'name',
			])
			->addColumn('email_pass', 'string', [
				'null' => false,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'email',
			])
			->addColumn('email_name', 'string', [
				'null' => false,
				'limit' => 100,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'email_pass',
			])
			->addColumn('apply_type', 'integer', [
				'null' => false,
				'default' => '2',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'email_name',
			])
			->addColumn('banana_count', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'apply_type',
			])
			->addColumn('fetch_count', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'banana_count',
			])
			->addColumn('week_num', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'fetch_count',
			])
			->addColumn('report_num', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'week_num',
			])
			->addColumn('stat_last_update', 'datetime', [
				'null' => false,
				'after' => 'report_num',
			])
			->addColumn('stat_fetchweight', 'decimal', [
				'null' => false,
				'precision' => 10,
				'signed' => false,
				'scale' => 2,
				'after' => 'stat_last_update',
			])
			->addColumn('stat_fetchcount', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'stat_fetchweight',
			])
			->addColumn('stat_postcount', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'stat_fetchcount',
			])
			->addColumn('stat_betriebcount', 'integer', [
				'null' => false,
				'limit' => '7',
				'signed' => false,
				'after' => 'stat_postcount',
			])
			->addColumn('stat_korpcount', 'integer', [
				'null' => false,
				'limit' => '7',
				'signed' => false,
				'after' => 'stat_betriebcount',
			])
			->addColumn('stat_botcount', 'integer', [
				'null' => false,
				'limit' => '7',
				'signed' => false,
				'after' => 'stat_korpcount',
			])
			->addColumn('stat_fscount', 'integer', [
				'null' => false,
				'limit' => '7',
				'signed' => false,
				'after' => 'stat_botcount',
			])
			->addColumn('stat_fairteilercount', 'integer', [
				'null' => false,
				'limit' => '7',
				'signed' => false,
				'after' => 'stat_fscount',
			])
			->addColumn('conversation_id', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '10',
				'signed' => false,
				'after' => 'stat_fairteilercount',
			])
			->addColumn('moderated', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'conversation_id',
			])
			->addIndex(['parent_id'], [
				'name' => 'parent_id',
				'unique' => false,
			])
			->addIndex(['type'], [
				'name' => 'type',
				'unique' => false,
			])
			->addIndex(['mailbox_id'], [
				'name' => 'mailbox_id',
				'unique' => false,
			])
			->addIndex(['master'], [
				'name' => 'master',
				'unique' => false,
			])
			->create();
		$this->table('fs_blog_entry', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('bezirk_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'bezirk_id',
			])
			->addColumn('active', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 100,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'active',
			])
			->addColumn('teaser', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 500,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'name',
			])
			->addColumn('body', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'teaser',
			])
			->addColumn('time', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'body',
			])
			->addColumn('picture', 'string', [
				'null' => false,
				'limit' => 150,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'time',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'blog_entry_FKIndex1',
				'unique' => false,
			])
			->addIndex(['bezirk_id'], [
				'name' => 'blog_entry_FKIndex2',
				'unique' => false,
			])
			->addIndex(['active'], [
				'name' => 'active',
				'unique' => false,
			])
			->create();
		$this->table('fs_foodsaver_has_conversation', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('conversation_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('unread', 'integer', [
				'null' => true,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'conversation_id',
			])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
				'after' => 'unread',
			])
			->addIndex(['foodsaver_id', 'conversation_id'], [
				'name' => 'foodsaver_id',
				'unique' => true,
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'foodsaver_has_conversation_FKIndex1',
				'unique' => false,
			])
			->addIndex(['conversation_id'], [
				'name' => 'foodsaver_has_conversation_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_mailbox', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'id',
			])
			->addColumn('member', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'name',
			])
			->addColumn('last_access', 'datetime', [
				'null' => false,
				'after' => 'member',
			])
			->addIndex(['name'], [
				'name' => 'email_unique',
				'unique' => true,
			])
			->addIndex(['member'], [
				'name' => 'member',
				'unique' => false,
			])
			->create();
		$this->table('fs_event_has_wallpost', [
			'id' => false,
			'primary_key' => ['event_id', 'wallpost_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('event_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('wallpost_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'event_id',
			])
			->addIndex(['event_id'], [
				'name' => 'event_id',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'wallpost_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_theme', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('last_post_id', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 260,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'last_post_id',
			])
			->addColumn('time', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'name',
			])
			->addColumn('active', 'integer', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'time',
			])
			->addColumn('sticky', 'boolean', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'active',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'theme_FKIndex1',
				'unique' => false,
			])
			->addIndex(['last_post_id'], [
				'name' => 'last_post_id',
				'unique' => false,
			])
			->addIndex(['active'], [
				'name' => 'active',
				'unique' => false,
			])
			->create();
		$this->table('fs_bezirk_has_wallpost', [
			'id' => false,
			'primary_key' => ['bezirk_id', 'wallpost_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('bezirk_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('wallpost_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'bezirk_id',
			])
			->addIndex(['bezirk_id'], [
				'name' => 'bezirk_id',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'wallpost_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_botschafter', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'bezirk_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('bezirk_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'foodsaver_has_bezirk_FKIndex1',
				'unique' => false,
			])
			->addIndex(['bezirk_id'], [
				'name' => 'foodsaver_has_bezirk_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_buddy', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'buddy_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('buddy_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('confirmed', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'buddy_id',
			])
			->addIndex(['confirmed'], [
				'name' => 'buddy_confirmed',
				'unique' => false,
			])
			->addIndex(['buddy_id'], [
				'name' => 'buddy_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_conversation', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('locked', 'boolean', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'id',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 40,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'locked',
			])
			->addColumn('last', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'name',
			])
			->addColumn('last_foodsaver_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'last',
			])
			->addColumn('last_message_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'last_foodsaver_id',
			])
			->addColumn('last_message', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'last_message_id',
			])
			->addColumn('last_message_is_htmlentity_encoded', 'boolean', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'last_message',
			])
			->addIndex(['last_foodsaver_id'], [
				'name' => 'conversation_last_fs_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_foodsaver_has_contact', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'contact_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'default' => 0,
				'signed' => false,
			])
			->addColumn('contact_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'default' => 0,
				'after' => 'foodsaver_id',
			])
			->addIndex(['contact_id'], [
				'name' => 'contact_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_event', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('bezirk_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('location_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'bezirk_id',
			])
			->addColumn('public', 'boolean', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'location_id',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'public',
			])
			->addColumn('start', 'datetime', [
				'null' => false,
				'after' => 'name',
			])
			->addColumn('end', 'datetime', [
				'null' => false,
				'after' => 'start',
			])
			->addColumn('description', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'end',
			])
			->addColumn('bot', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'description',
			])
			->addColumn('online', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'bot',
			])
			->addIndex(['location_id'], [
				'name' => 'event_FKIndex1',
				'unique' => false,
			])
			->addIndex(['bezirk_id'], [
				'name' => 'event_FKIndex2',
				'unique' => false,
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'event_FKIndex3',
				'unique' => false,
			])
			->create();
		$this->table('fs_email_status', [
			'id' => false,
			'primary_key' => ['email_id', 'foodsaver_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('email_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'email_id',
			])
			->addColumn('status', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'foodsaver_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_foodsaver_has_wallpost', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'wallpost_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('wallpost_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('usercomment', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'wallpost_id',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'foodsaver_has_wallpost_FKIndex1',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'foodsaver_has_wallpost_FKIndex2',
				'unique' => false,
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'foodsaver_id',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'wallpost_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_ipblock', [
			'id' => false,
			'primary_key' => ['ip', 'context'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('ip', 'string', [
				'null' => false,
				'limit' => 20,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
			])
			->addColumn('context', 'string', [
				'null' => false,
				'limit' => 10,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'ip',
			])
			->addColumn('start', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'context',
			])
			->addColumn('duration', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'start',
			])
			->create();
		$this->table('fs_pass_gen', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'date'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('date', 'datetime', [
				'null' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('bot_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'date',
			])
			->addIndex(['bot_id'], [
				'name' => 'bot_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_lebensmittel', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'id',
			])
			->create();
		$this->table('fs_fairteiler_follower', [
			'id' => false,
			'primary_key' => ['fairteiler_id', 'foodsaver_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('fairteiler_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'fairteiler_id',
			])
			->addColumn('type', 'integer', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('infotype', 'integer', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'type',
			])
			->addIndex(['fairteiler_id'], [
				'name' => 'fairteiler_verantwortlich_FKIndex1',
				'unique' => false,
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'fairteiler_verantwortlich_FKIndex2',
				'unique' => false,
			])
			->addIndex(['type'], [
				'name' => 'type',
				'unique' => false,
			])
			->addIndex(['infotype'], [
				'name' => 'infotype',
				'unique' => false,
			])
			->create();
		$this->table('fs_betrieb_team', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('betrieb_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('verantwortlich', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'betrieb_id',
			])
			->addColumn('active', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_REGULAR,
				'after' => 'verantwortlich',
			])
			->addColumn('stat_last_update', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'active',
			])
			->addColumn('stat_fetchcount', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'stat_last_update',
			])
			->addColumn('stat_first_fetch', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'stat_fetchcount',
			])
			->addColumn('stat_last_fetch', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'stat_first_fetch',
			])
			->addColumn('stat_add_date', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'stat_last_fetch',
			])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
				'after' => 'stat_add_date',
			])
			->addIndex(['foodsaver_id', 'betrieb_id'], [
				'name' => 'foodsaver_id',
				'unique' => true,
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'foodsaver_has_betrieb_FKIndex1',
				'unique' => false,
			])
			->addIndex(['betrieb_id'], [
				'name' => 'foodsaver_has_betrieb_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_fairteiler_has_wallpost', [
			'id' => false,
			'primary_key' => ['fairteiler_id', 'wallpost_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('fairteiler_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('wallpost_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'fairteiler_id',
			])
			->addIndex(['fairteiler_id'], [
				'name' => 'fairteiler_has_wallpost_FKIndex1',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'fairteiler_has_wallpost_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_kette', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 60,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'id',
			])
			->addColumn('logo', 'string', [
				'null' => false,
				'limit' => 30,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'name',
			])
			->create();
		$this->table('fs_faq_category', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'id',
			])
			->create();
		$this->table('fs_fairteiler', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('bezirk_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 260,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'bezirk_id',
			])
			->addColumn('picture', 'string', [
				'null' => false,
				'limit' => 100,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'name',
			])
			->addColumn('status', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'picture',
			])
			->addColumn('desc', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'status',
			])
			->addColumn('anschrift', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 260,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'desc',
			])
			->addColumn('plz', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 5,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'anschrift',
			])
			->addColumn('ort', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 100,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'plz',
			])
			->addColumn('lat', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 100,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'ort',
			])
			->addColumn('lon', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 100,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'lat',
			])
			->addColumn('add_date', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'lon',
			])
			->addColumn('add_foodsaver', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'add_date',
			])
			->addIndex(['bezirk_id'], [
				'name' => 'fairteiler_FKIndex1',
				'unique' => false,
			])
			->create();
		$this->table('fs_verify_history', [
			'id' => false,
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('fs_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('date', 'datetime', [
				'null' => false,
				'after' => 'fs_id',
			])
			->addColumn('bot_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'date',
			])
			->addColumn('change_status', 'boolean', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'bot_id',
			])
			->addIndex(['fs_id'], [
				'name' => 'fs_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_push_notification_subscription', [
			'id' => false,
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_REGULAR,
			])
			->addColumn('data', 'text', [
				'null' => true,
				'default' => null,
				'limit' => 65535,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'foodsaver_id',
			])
			->addColumn('type', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 24,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'data',
			])
			->create();
		$this->table('fs_rating', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'rater_id', 'ratingtype'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => 'ratingtype 1+2 = bananen, 4+5 = betriebsmeldung',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('rater_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('ratingtype', 'integer', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'rater_id',
			])
			->addColumn('rating', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'ratingtype',
			])
			->addColumn('msg', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'rating',
			])
			->addColumn('time', 'datetime', [
				'null' => false,
				'after' => 'msg',
			])
			->addIndex(['rater_id'], [
				'name' => 'fk_foodsaver_has_foodsaver_foodsaver1_idx',
				'unique' => false,
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'fk_foodsaver_has_foodsaver_foodsaver_idx',
				'unique' => false,
			])
			->create();
		$this->table('fs_location', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'id',
			])
			->addColumn('lat', 'decimal', [
				'null' => true,
				'default' => null,
				'precision' => 10,
				'scale' => 8,
				'after' => 'name',
			])
			->addColumn('lon', 'decimal', [
				'null' => true,
				'default' => null,
				'precision' => 11,
				'scale' => 8,
				'after' => 'lat',
			])
			->addColumn('zip', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 10,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'lon',
			])
			->addColumn('city', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 100,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'zip',
			])
			->addColumn('street', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'city',
			])
			->create();
		$this->table('fs_foodsaver_has_bell', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'bell_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('bell_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('seen', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'bell_id',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'foodsaver_has_bell_FKIndex1',
				'unique' => false,
			])
			->addIndex(['bell_id'], [
				'name' => 'foodsaver_has_bell_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_foodsaver_has_bezirk', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'bezirk_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('bezirk_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('active', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => '10',
				'signed' => false,
				'comment' => '0=beworben,1=aktiv,10=vielleicht',
				'after' => 'bezirk_id',
			])
			->addColumn('added', 'datetime', [
				'null' => false,
				'after' => 'active',
			])
			->addColumn('application', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'added',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'foodsaver_has_bezirk_FKIndex1',
				'unique' => false,
			])
			->addIndex(['bezirk_id'], [
				'name' => 'foodsaver_has_bezirk_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_report_has_wallpost', [
			'id' => false,
			'primary_key' => ['fsreport_id', 'wallpost_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('fsreport_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('wallpost_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'fsreport_id',
			])
			->addIndex(['fsreport_id'], [
				'name' => 'fsreport_id',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'wallpost_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_question_has_wallpost', [
			'id' => false,
			'primary_key' => ['question_id', 'wallpost_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('question_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('wallpost_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'question_id',
			])
			->addColumn('usercomment', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'wallpost_id',
			])
			->addIndex(['question_id'], [
				'name' => 'question_has_wallpost_FKIndex1',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'question_has_wallpost_FKIndex2',
				'unique' => false,
			])
			->addIndex(['question_id'], [
				'name' => 'question_id',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'wallpost_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_pass_request', [
			'id' => false,
			'primary_key' => ['foodsaver_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'foodsaver_id',
			])
			->addColumn('time', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'name',
			])
			->create();
		$this->table('fs_mailbox_member', [
			'id' => false,
			'primary_key' => ['mailbox_id', 'foodsaver_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('mailbox_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'mailbox_id',
			])
			->addColumn('email_name', 'string', [
				'null' => false,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'foodsaver_id',
			])
			->addIndex(['mailbox_id'], [
				'name' => 'mailbox_has_foodsaver_FKIndex1',
				'unique' => false,
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'mailbox_has_foodsaver_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_theme_follower', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'theme_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('theme_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('infotype', 'boolean', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'theme_id',
			])
			->addColumn('bell_notification', 'boolean', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'infotype',
			])
			->addIndex(['infotype'], [
				'name' => 'infotype',
				'unique' => false,
			])
			->addIndex(['theme_id'], [
				'name' => 'theme_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_foodsaver_archive', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('bezirk_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('position', 'string', [
				'null' => false,
				'default' => '',
				'limit' => 255,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'bezirk_id',
			])
			->addColumn('verified', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'position',
			])
			->addColumn('last_pass', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'verified',
			])
			->addColumn('new_bezirk', 'string', [
				'null' => false,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'last_pass',
			])
			->addColumn('want_new', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'new_bezirk',
			])
			->addColumn('mailbox_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'want_new',
			])
			->addColumn('rolle', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'mailbox_id',
			])
			->addColumn('type', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'rolle',
			])
			->addColumn('plz', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 10,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'type',
			])
			->addColumn('stadt', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 100,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'plz',
			])
			->addColumn('lat', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 20,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'stadt',
			])
			->addColumn('lon', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 20,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'lat',
			])
			->addColumn('photo', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'lon',
			])
			->addColumn('email', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'photo',
			])
			->addColumn('password', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 100,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'email',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'password',
			])
			->addColumn('admin', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'name',
			])
			->addColumn('nachname', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'admin',
			])
			->addColumn('anschrift', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'nachname',
			])
			->addColumn('telefon', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 30,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'anschrift',
			])
			->addColumn('homepage', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 255,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'telefon',
			])
			->addColumn('handy', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'homepage',
			])
			->addColumn('geschlecht', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'handy',
			])
			->addColumn('geb_datum', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'geschlecht',
			])
			->addColumn('anmeldedatum', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'geb_datum',
			])
			->addColumn('privacy_notice_accepted_date', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'anmeldedatum',
			])
			->addColumn('privacy_policy_accepted_date', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'privacy_notice_accepted_date',
			])
			->addColumn('orgateam', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'privacy_policy_accepted_date',
			])
			->addColumn('active', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'orgateam',
			])
			->addColumn('data', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'active',
			])
			->addColumn('about_me_public', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'data',
			])
			->addColumn('newsletter', 'boolean', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'about_me_public',
			])
			->addColumn('token', 'string', [
				'null' => false,
				'limit' => 25,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'newsletter',
			])
			->addColumn('infomail_message', 'boolean', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'token',
			])
			->addColumn('last_login', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'infomail_message',
			])
			->addColumn('stat_fetchweight', 'decimal', [
				'null' => false,
				'default' => '0.00',
				'signed' => false,
				'precision' => 9,
				'scale' => 2,
				'after' => 'last_login',
			])
			->addColumn('stat_fetchcount', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '10',
				'signed' => false,
				'after' => 'stat_fetchweight',
			])
			->addColumn('stat_ratecount', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '10',
				'signed' => false,
				'after' => 'stat_fetchcount',
			])
			->addColumn('stat_rating', 'decimal', [
				'null' => false,
				'default' => '0.00',
				'signed' => false,
				'precision' => 4,
				'scale' => 2,
				'after' => 'stat_ratecount',
			])
			->addColumn('stat_postcount', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_REGULAR,
				'after' => 'stat_rating',
			])
			->addColumn('stat_buddycount', 'integer', [
				'null' => false,
				'limit' => '7',
				'signed' => false,
				'after' => 'stat_postcount',
			])
			->addColumn('stat_bananacount', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '7',
				'signed' => false,
				'after' => 'stat_buddycount',
			])
			->addColumn('stat_fetchrate', 'decimal', [
				'null' => false,
				'default' => '100.00',
				'precision' => 6,
				'scale' => 2,
				'after' => 'stat_bananacount',
			])
			->addColumn('sleep_status', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'stat_fetchrate',
			])
			->addColumn('sleep_from', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'sleep_status',
			])
			->addColumn('sleep_until', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'sleep_from',
			])
			->addColumn('sleep_msg', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'sleep_until',
			])
			->addColumn('option', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'sleep_msg',
			])
			->addColumn('beta', 'boolean', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'option',
			])
			->addColumn('quiz_rolle', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'beta',
			])
			->addColumn('contact_public', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'quiz_rolle',
			])
			->addColumn('deleted_at', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'contact_public',
			])
			->addColumn('about_me_intern', 'text', [
				'null' => true,
				'default' => null,
				'limit' => 65535,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'deleted_at',
			])->create();
		$this->table('fs_post_reaction', [
			'id' => false,
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('post_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('time', 'datetime', [
				'null' => false,
				'after' => 'post_id',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_REGULAR,
				'after' => 'time',
			])
			->addColumn('key', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 63,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'foodsaver_id',
			])
			->addIndex(['post_id', 'foodsaver_id', 'key'], [
				'name' => 'post-foodsaver-key',
				'unique' => true,
			])
			->addIndex(['post_id'], [
				'name' => 'post_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_foodsaver', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('bezirk_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('position', 'string', [
				'null' => false,
				'default' => '',
				'limit' => 255,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'bezirk_id',
			])
			->addColumn('verified', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'position',
			])
			->addColumn('last_pass', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'verified',
			])
			->addColumn('new_bezirk', 'string', [
				'null' => false,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'last_pass',
			])
			->addColumn('want_new', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'new_bezirk',
			])
			->addColumn('mailbox_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'want_new',
			])
			->addColumn('rolle', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'mailbox_id',
			])
			->addColumn('type', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'rolle',
			])
			->addColumn('plz', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 10,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'type',
			])
			->addColumn('stadt', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 100,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'plz',
			])
			->addColumn('lat', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 20,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'stadt',
			])
			->addColumn('lon', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 20,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'lat',
			])
			->addColumn('photo', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'lon',
			])
			->addColumn('email', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'photo',
			])
			->addColumn('password', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 100,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'email',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'password',
			])
			->addColumn('admin', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'name',
			])
			->addColumn('nachname', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'admin',
			])
			->addColumn('anschrift', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'nachname',
			])
			->addColumn('telefon', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 30,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'anschrift',
			])
			->addColumn('homepage', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 255,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'telefon',
			])
			->addColumn('handy', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'homepage',
			])
			->addColumn('geschlecht', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'handy',
			])
			->addColumn('geb_datum', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'geschlecht',
			])
			->addColumn('anmeldedatum', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'geb_datum',
			])
			->addColumn('privacy_notice_accepted_date', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'anmeldedatum',
			])
			->addColumn('privacy_policy_accepted_date', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'privacy_notice_accepted_date',
			])
			->addColumn('orgateam', 'integer', [
				'null' => true,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'privacy_policy_accepted_date',
			])
			->addColumn('active', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'orgateam',
			])
			->addColumn('data', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'active',
			])
			->addColumn('about_me_public', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'data',
			])
			->addColumn('newsletter', 'boolean', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'about_me_public',
			])
			->addColumn('token', 'string', [
				'null' => false,
				'limit' => 25,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'newsletter',
			])
			->addColumn('infomail_message', 'boolean', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'token',
			])
			->addColumn('last_login', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'infomail_message',
			])
			->addColumn('stat_fetchweight', 'decimal', [
				'null' => false,
				'default' => '0.00',
				'signed' => false,
				'precision' => 9,
				'scale' => 2,
				'after' => 'last_login',
			])
			->addColumn('stat_fetchcount', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '10',
				'signed' => false,
				'after' => 'stat_fetchweight',
			])
			->addColumn('stat_ratecount', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '10',
				'signed' => false,
				'after' => 'stat_fetchcount',
			])
			->addColumn('stat_rating', 'decimal', [
				'null' => false,
				'default' => '0.00',
				'signed' => false,
				'precision' => 4,
				'scale' => 2,
				'after' => 'stat_ratecount',
			])
			->addColumn('stat_postcount', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_REGULAR,
				'after' => 'stat_rating',
			])
			->addColumn('stat_buddycount', 'integer', [
				'null' => false,
				'limit' => '7',
				'signed' => false,
				'after' => 'stat_postcount',
			])
			->addColumn('stat_bananacount', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '7',
				'signed' => false,
				'after' => 'stat_buddycount',
			])
			->addColumn('stat_fetchrate', 'decimal', [
				'null' => false,
				'default' => '100.00',
				'precision' => 6,
				'scale' => 2,
				'after' => 'stat_bananacount',
			])
			->addColumn('sleep_status', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'stat_fetchrate',
			])
			->addColumn('sleep_from', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'sleep_status',
			])
			->addColumn('sleep_until', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'sleep_from',
			])
			->addColumn('sleep_msg', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'sleep_until',
			])
			->addColumn('option', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'sleep_msg',
			])
			->addColumn('beta', 'boolean', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'option',
			])
			->addColumn('quiz_rolle', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'beta',
			])
			->addColumn('contact_public', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'quiz_rolle',
			])
			->addColumn('deleted_at', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'contact_public',
			])
			->addColumn('about_me_intern', 'text', [
				'null' => true,
				'default' => null,
				'limit' => 65535,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'deleted_at',
			])
			->addIndex(['email'], [
				'name' => 'email',
				'unique' => true,
			])
			->addIndex(['bezirk_id'], [
				'name' => 'foodsaver_FKIndex2',
				'unique' => false,
			])
			->addIndex(['plz'], [
				'name' => 'plz',
				'unique' => false,
			])
			->addIndex(['want_new'], [
				'name' => 'want_new',
				'unique' => false,
			])
			->addIndex(['mailbox_id'], [
				'name' => 'mailbox_id',
				'unique' => false,
			])
			->addIndex(['newsletter'], [
				'name' => 'newsletter',
				'unique' => false,
			])
			->addIndex(['name', 'nachname'], [
				'name' => 'name',
				'unique' => false,
				'type' => 'fulltext',
			])
			->create();
		$this->table('fs_abholer', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('betrieb_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('date', 'datetime', [
				'null' => false,
				'after' => 'betrieb_id',
			])
			->addColumn('confirmed', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'date',
			])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
				'after' => 'confirmed',
			])
			->addIndex(['foodsaver_id', 'betrieb_id', 'date'], [
				'name' => 'foodsaver_id',
				'unique' => true,
			])
			->addIndex(['betrieb_id'], [
				'name' => 'betrieb_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_send_email', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('mailbox_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('mode', 'integer', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'mailbox_id',
			])
			->addColumn('complete', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'mode',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'complete',
			])
			->addColumn('message', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'name',
			])
			->addColumn('zeit', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'message',
			])
			->addColumn('recip', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'zeit',
			])
			->addColumn('attach', 'string', [
				'null' => false,
				'limit' => 500,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'recip',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'send_email_FKIndex1',
				'unique' => false,
			])
			->create();
		$this->table('fs_mailbox_message', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('mailbox_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('folder', 'integer', [
				'null' => true,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'mailbox_id',
			])
			->addColumn('sender', 'text', [
				'null' => true,
				'default' => null,
				'limit' => 65535,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'folder',
			])
			->addColumn('to', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'sender',
			])
			->addColumn('subject', 'text', [
				'null' => true,
				'default' => null,
				'limit' => 65535,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'to',
			])
			->addColumn('body', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'subject',
			])
			->addColumn('body_html', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'body',
			])
			->addColumn('time', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'body_html',
			])
			->addColumn('attach', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'time',
			])
			->addColumn('read', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'attach',
			])
			->addColumn('answer', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'read',
			])
			->addIndex(['folder'], [
				'name' => 'email_message_folder',
				'unique' => false,
			])
			->addIndex(['mailbox_id', 'read'], [
				'name' => 'mailbox_message_FKIndex1',
				'unique' => false,
			])
			->create();
		$this->table('fs_betrieb', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('betrieb_status_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('bezirk_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'betrieb_status_id',
			])
			->addColumn('added', 'date', [
				'null' => false,
				'after' => 'bezirk_id',
			])
			->addColumn('plz', 'string', [
				'null' => false,
				'limit' => 5,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'added',
			])
			->addColumn('stadt', 'string', [
				'null' => false,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'plz',
			])
			->addColumn('lat', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 20,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'stadt',
			])
			->addColumn('lon', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 20,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'lat',
			])
			->addColumn('kette_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'lon',
			])
			->addColumn('betrieb_kategorie_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_REGULAR,
				'after' => 'kette_id',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'betrieb_kategorie_id',
			])
			->addColumn('str', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 120,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'name',
			])
			->addColumn('hsnr', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 20,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'str',
			])
			->addColumn('status_date', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'hsnr',
			])
			->addColumn('status', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'status_date',
			])
			->addColumn('ansprechpartner', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 60,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'status',
			])
			->addColumn('telefon', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'ansprechpartner',
			])
			->addColumn('fax', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'telefon',
			])
			->addColumn('email', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 60,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'fax',
			])
			->addColumn('begin', 'date', [
				'null' => true,
				'default' => null,
				'after' => 'email',
			])
			->addColumn('besonderheiten', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'begin',
			])
			->addColumn('public_info', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 200,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'besonderheiten',
			])
			->addColumn('public_time', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'public_info',
			])
			->addColumn('ueberzeugungsarbeit', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'public_time',
			])
			->addColumn('presse', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'ueberzeugungsarbeit',
			])
			->addColumn('sticker', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'presse',
			])
			->addColumn('abholmenge', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'sticker',
			])
			->addColumn('team_status', 'integer', [
				'null' => false,
				'default' => '1',
				'limit' => MysqlAdapter::INT_TINY,
				'comment' => '0 = Team Voll; 1 = Es werden noch Helfer gesucht; 2 = Es werden dringend Helfer gesucht',
				'after' => 'abholmenge',
			])
			->addColumn('prefetchtime', 'integer', [
				'null' => false,
				'default' => '1209600',
				'limit' => '10',
				'signed' => false,
				'after' => 'team_status',
			])
			->addColumn('team_conversation_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'prefetchtime',
			])
			->addColumn('springer_conversation_id', 'integer', [
				'null' => true,
				'default' => null,
				'limit' => '10',
				'signed' => false,
				'after' => 'team_conversation_id',
			])
			->addColumn('deleted_at', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'springer_conversation_id',
			])
			->addIndex(['kette_id'], [
				'name' => 'betrieb_FKIndex2',
				'unique' => false,
			])
			->addIndex(['bezirk_id'], [
				'name' => 'betrieb_FKIndex3',
				'unique' => false,
			])
			->addIndex(['betrieb_status_id'], [
				'name' => 'betrieb_FKIndex5',
				'unique' => false,
			])
			->addIndex(['plz'], [
				'name' => 'plz',
				'unique' => false,
			])
			->addIndex(['team_status'], [
				'name' => 'team_status',
				'unique' => false,
			])
			->create();
		$this->table('fs_wallpost', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('body', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'foodsaver_id',
			])
			->addColumn('time', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'body',
			])
			->addColumn('attach', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'time',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'wallpost_FKIndex1',
				'unique' => false,
			])
			->create();
		$this->table('fs_theme_post', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('theme_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'id',
			])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'theme_id',
			])
			->addColumn('reply_post', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('body', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'reply_post',
			])
			->addColumn('time', 'datetime', [
				'null' => true,
				'default' => null,
				'after' => 'body',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'theme_post_FKIndex1',
				'unique' => false,
			])
			->addIndex(['theme_id'], [
				'name' => 'theme_post_FKIndex2',
				'unique' => false,
			])
			->addIndex(['reply_post'], [
				'name' => 'reply_post',
				'unique' => false,
			])
			->create();
		$this->table('fs_usernotes_has_wallpost', [
			'id' => false,
			'primary_key' => ['usernotes_id', 'wallpost_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('usernotes_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('wallpost_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'usernotes_id',
			])
			->addColumn('usercomment', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'after' => 'wallpost_id',
			])
			->addIndex(['usernotes_id'], [
				'name' => 'usernotes_has_wallpost_FKIndex1',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'usernotes_has_wallpost_FKIndex2',
				'unique' => false,
			])
			->addIndex(['usernotes_id'], [
				'name' => 'usernotes_id',
				'unique' => false,
			])
			->addIndex(['wallpost_id'], [
				'name' => 'wallpost_id',
				'unique' => false,
			])
			->create();
		$this->table('fs_foodsaver_has_event', [
			'id' => false,
			'primary_key' => ['foodsaver_id', 'event_id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('foodsaver_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
			])
			->addColumn('event_id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'after' => 'foodsaver_id',
			])
			->addColumn('status', 'integer', [
				'null' => false,
				'default' => '0',
				'limit' => MysqlAdapter::INT_TINY,
				'signed' => false,
				'after' => 'event_id',
			])
			->addIndex(['foodsaver_id'], [
				'name' => 'foodsaver_has_event_FKIndex1',
				'unique' => false,
			])
			->addIndex(['event_id'], [
				'name' => 'foodsaver_has_event_FKIndex2',
				'unique' => false,
			])
			->create();
		$this->table('fs_betrieb_kategorie', [
			'id' => false,
			'primary_key' => ['id'],
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('id', 'integer', [
				'null' => false,
				'limit' => '10',
				'signed' => false,
				'identity' => 'enable',
			])
			->addColumn('name', 'string', [
				'null' => true,
				'default' => null,
				'limit' => 50,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'id',
			])
			->create();
		$this->table('fs_foodsaver_change_history', [
			'id' => false,
			'engine' => 'InnoDB',
			'encoding' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'comment' => '',
		])
			->addColumn('date', 'timestamp', [
				'null' => false,
				'default' => 'CURRENT_TIMESTAMP',
			])
			->addColumn('fs_id', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_REGULAR,
				'after' => 'date',
			])
			->addColumn('changer_id', 'integer', [
				'null' => false,
				'limit' => MysqlAdapter::INT_REGULAR,
				'after' => 'fs_id',
			])
			->addColumn('object_name', 'text', [
				'null' => false,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'changer_id',
			])
			->addColumn('old_value', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'object_name',
			])
			->addColumn('new_value', 'text', [
				'null' => true,
				'default' => null,
				'limit' => MysqlAdapter::TEXT_MEDIUM,
				'collation' => 'utf8mb4_unicode_ci',
				'encoding' => 'utf8mb4',
				'after' => 'old_value',
			])
			->addIndex(['fs_id'], [
				'name' => 'fs_id',
				'unique' => false,
			])
			->create();
		$this->execute('SET FOREIGN_KEY_CHECKS=0;');
		$this->table('fs_answer')
				->addForeignKey('question_id', 'fs_question', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_apitoken')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_application_has_wallpost')
				->addForeignKey('application_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('wallpost_id', 'fs_wallpost', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_basket_anfrage')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('basket_id', 'fs_basket', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_basket_has_wallpost')
				->addForeignKey('basket_id', 'fs_basket', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('wallpost_id', 'fs_wallpost', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_bezirk')
				->addForeignKey('parent_id', 'fs_bezirk', 'id', ['update' => 'CASCADE'])
				->update();
		$this->table('fs_bezirk_closure')
				->addForeignKey('bezirk_id', 'fs_bezirk', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('ancestor_id', 'fs_bezirk', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->update();
		$this->table('fs_bezirk_has_theme')
				->addForeignKey('bezirk_id', 'fs_bezirk', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('theme_id', 'fs_theme', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_bezirk_has_wallpost')
				->addForeignKey('bezirk_id', 'fs_bezirk', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('wallpost_id', 'fs_wallpost', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_botschafter')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('bezirk_id', 'fs_bezirk', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_buddy')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('buddy_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_email_status')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('email_id', 'fs_send_email', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_event')
				->addForeignKey('bezirk_id', 'fs_bezirk', 'id', ['delete' => 'SET_NULL'])
				->addForeignKey('location_id', 'fs_location', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_event_has_wallpost')
				->addForeignKey('event_id', 'fs_event', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('wallpost_id', 'fs_wallpost', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_fairteiler')
				->addForeignKey('bezirk_id', 'fs_bezirk', 'id', ['delete' => 'SET_NULL'])
				->update();
		$this->table('fs_fairteiler_follower')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('fairteiler_id', 'fs_fairteiler', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_fairteiler_has_wallpost')
				->addForeignKey('fairteiler_id', 'fs_fairteiler', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('wallpost_id', 'fs_wallpost', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_foodsaver_has_bell')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('bell_id', 'fs_bell', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_foodsaver_has_bezirk')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('bezirk_id', 'fs_bezirk', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_foodsaver_has_contact')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('contact_id', 'fs_contact', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_foodsaver_has_conversation')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('conversation_id', 'fs_conversation', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_foodsaver_has_event')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('event_id', 'fs_event', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_foodsaver_has_wallpost')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('wallpost_id', 'fs_wallpost', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_report_has_wallpost')
				->addForeignKey('fsreport_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('wallpost_id', 'fs_wallpost', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_mailbox_member')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('mailbox_id', 'fs_mailbox', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_mailbox_message')
				->addForeignKey('mailbox_id', 'fs_mailbox', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_mailchange')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_msg')
				->addForeignKey('conversation_id', 'fs_conversation', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_pass_gen')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('bot_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_pass_request')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_post_reaction')
				->addForeignKey('post_id', 'fs_theme_post', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->update();
		$this->table('fs_question_has_quiz')
				->addForeignKey('question_id', 'fs_question', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('quiz_id', 'fs_quiz', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_question_has_wallpost')
				->addForeignKey('question_id', 'fs_question', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('wallpost_id', 'fs_wallpost', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_quiz_session')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_rating')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('rater_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_theme_follower')
				->addForeignKey('foodsaver_id', 'fs_foodsaver', 'id', ['delete' => 'CASCADE'])
				->addForeignKey('theme_id', 'fs_theme', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_theme_post')
				->addForeignKey('theme_id', 'fs_theme', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_usernotes_has_wallpost')
				->addForeignKey('wallpost_id', 'fs_wallpost', 'id', ['delete' => 'CASCADE'])
				->update();
		$this->table('fs_fetchweight')->insert([
			['id' => '0', 'weight' => '1.5'],
			['id' => '1', 'weight' => '2.0'],
			['id' => '2', 'weight' => '4.0'],
			['id' => '3', 'weight' => '7.5'],
			['id' => '4', 'weight' => '15'],
			['id' => '5', 'weight' => '25'],
			['id' => '6', 'weight' => '45'],
			['id' => '7', 'weight' => '64']
		])->save();
		$content = [['id' => '4', 'name' => 'fuer-unternehmen', 'title' => 'Für Unternehmen', 'body' => '<p><span>Wir freuen uns sehr, dass Ihr Betrieb an foodsharing interessiert ist! Gemeinsam mit foodsharing k&ouml;nnen Sie sich daf&uuml;r einsetzen, dass aussortierte und unverk&auml;ufliche Lebensmittel eine sinnvolle Verwendung anstelle der Entsorgung erfahren.</span></p>
<p><span><strong>Was ist unser Ziel?</strong><br />Wir Foodsaver sind eine Gruppe von Menschen, die sich ehrenamtlich daf&uuml;r engagieren, dass weniger Lebensmittel in den M&uuml;ll wandern. Weltweit landet n&auml;mlich jedes dritte produzierte Lebensmittel in der Tonne. In jedem einzelnen stecken aber Arbeitszeit, Ressourcen, zum Teil lange Transportwege und Geld. Foodsharing bietet eine M&ouml;glichkeit, all das wieder wertzusch&auml;tzen, indem wir Essen eine zweite Chance geben.</span></p>
<p><span><strong>Das k&ouml;nnen wir Ihrem Betrieb bieten:</strong><br /> Deshalb k&uuml;mmern wir uns um alle Lebensmittel, die aus verschiedenen Gr&uuml;nden nicht mehr verkauft werden k&ouml;nnen, aber noch genie&szlig;bar sind. Falls gew&uuml;nscht, wird das Abgeholte von den Foodsavern nach Verwertbarkeit sortiert. Die noch genie&szlig;baren Produkte werden anschlie&szlig;end weiter verteilt. Damit die Rechtssicherheit f&uuml;r die Lebensmittelspendebetriebe gew&auml;hrleistet ist, &nbsp;unterschreiben alle Foodsaver eine<a href="https://wiki.foodsharing.de/Rechtsvereinbarung" target="_blank" style="text-decoration: none;"><span> </span></a><a href="https://wiki.foodsharing.de/Rechtsvereinbarung" target="_blank">Rechtsvereinbarung</a></span><span>, mit der sie die volle Verantwortung f&uuml;r die abgeholten Lebensmittel &uuml;bernehmen.</span></p>
<p><span><strong>Und was machen wir mit dem Essen?</strong><br /> Ein Gro&szlig;teil der geretteten Lebensmittel wird von den Foodsavern an Vereine, Tafeln, Suppenk&uuml;chen, FreundInnen, NachbarInnen, und nat&uuml;rlich &uuml;ber das foodsharing-Netzwerk oder Fair-Teiler (&ouml;ffentliche Regale zum Austausch von Lebensmitteln) verschenkt, der Rest wird von den Foodsavern selbst verwertet. Wir sehen uns als Erg&auml;nzung und Unterst&uuml;tzung der &uuml;ber 900 Tafeln in Deutschland. Als flexible, lokal organisierte Initiative k&ouml;nnen Foodsaver auch Kleinstmengen, Produkte &uuml;ber dem Mindesthaltbarkeitsdatum, an Wochenenden/Feiertagen und spontan abholen. Von Betrieben, die mit einer Tafel oder einer &auml;hnlichen Initiative zusammenarbeiten, werden nur Lebensmittel abgeholt, die von jenen aus rechtlichen oder logistischen Gr&uuml;nden nicht verwendet werden k&ouml;nnen - also nur das, was wirklich im M&uuml;ll landen w&uuml;rde. Es ist umso erfreulicher, wenn der karitative Gedanke der Tafeln mit dem foodsharing-Motto &bdquo;verwenden statt verschwenden&ldquo; einhergeht.</span> <br /><span>Mittlerweile sind &uuml;ber 38.000 engagierte Menschen in Deutschland, &Ouml;sterreich, Liechtenstein und der Schweiz akkreditierte LebensmittelretterInnen. Gemeinsam kooperieren wir mit 4.500 Betrieben, darunter f&uuml;hrende Bioh&auml;ndlerInnen wie SuperBioMarkt und die </span><a href="https://wiki.foodsharing.de/images/9/9b/Tonnen_sind_kein_Platz_f%C3%BCr_Lebensmittel.pdf" target="_blank"><span>Bio Company</span></a><span>. Insgesamt wurden so schon &uuml;ber 15.000 Tonnen Lebensmittel gerettet!</span></p>
<p><span><strong>Jetzt sind Sie gefragt!</strong><br /> Ob Laden, Supermarkt, Restaurant, Kantine, ProduzentIn, Getr&auml;nkemarkt, H&auml;ndlerIn und alle, die sonst in der Lebensmittelbranche t&auml;tig sind - jedeR ist willkommen!</span> <br /><span>Um mit uns in Kontakt zu treten und zu erfahren, wie Sie mit uns zusammenarbeiten k&ouml;nnen, schreiben Sie einfach eine E-Mail an <a href="mailto:info@foodsharing.de" target="_blank">info@foodsharing.de</a>.</span></p>
<p><span><strong>Klingt gut - aber was habe ich davon?</strong></span></p>
<ul>
<li>Mit uns k&ouml;nnen Sie einen Beitrag gegen die Verschwendung leisten. Ethischer Umgang mit Resten und aussortierten Lebensmittel ist schon an sich ein Wert.</li>
<li>Mit uns sparen Sie Geld und Arbeitskraft.</li>
<ul>
<li><span>Einsparung der Kosten f&uuml;r die M&uuml;llentsorgung. &ldquo;Dass wir dadurch [Kooperation mit foodsharing] sehr viel weniger Containerkapazit&auml;t brauchen und zus&auml;tzlich Kosten sparen, ist ein willkommener Nebeneffekt.&rdquo; Georg Kaiser, Gesch&auml;ftsf&uuml;hrer der BIO COMPANY</span></li>
<li><span>Einsparung der Arbeit f&uuml;r Sortierung und Entsorgung: Foodsaver &uuml;bernehmen das Sortieren der nicht mehr verk&auml;uflichen Lebensmittel in &lsquo;genie&szlig;bar&rsquo; und &lsquo;nicht mehr genie&szlig;bar&rsquo;, sowie die Entsorgung des anfallenden M&uuml;lls.</span></li>
</ul>
<li>Mit uns sind Sie flexibel. <span>Unsere Foodsaver k&ouml;nnen auch am Wochenende, Feiertagen, sp&auml;t abends, nachts und fr&uuml;h morgens die aussortierten Waren abholen. Wir k&ouml;nnen auch bei Ausfall der Tafeln und unerwarteten Vorf&auml;llen (K&uuml;hlanlagenausfall, falsche Lieferung usw.) zeitnah nach Anruf einspringen, da wir lokal aufgestellt und flexibel sind. In der Regel werden feste Tage und feste Uhrzeiten mit den Foodsavern f&uuml;r die Abholung ausgemacht, so dass Sie genau wissen, wann die Lebensmittel abgeholt werden. Sie bestimmen den am besten geeigneten Zeitpunkt und sprechen sich mit uns ab.</span></li>
<li>Mit uns gewinnen Sie Ansehen bei Ihrer Kundschaft. <span>Geben Sie Ihren KundInnen die M&ouml;glichkeit, sich ganz bewusst f&uuml;r Ihren Betrieb zu entscheiden, der keine Lebensmittel verschwendet. Falls gew&uuml;nscht, werden Sie als Unterst&uuml;tzerIn erw&auml;hnt, was wiederum Werbung f&uuml;r Sie bedeutet. Sie k&ouml;nnen Ihre Kooperation au&szlig;erdem mit unserem foodsharing-Sticker sichtbar machen. Sprechen Sie uns darauf an!</span></li>
<li>Mit uns sind Sie auch rechtlich auf der sicheren Seite.<span> Lebensmittelabgaben bedeuten keine rechtlichen Risiken f&uuml;r Sie, weil alle unsere Foodsaver einem </span><a href="https://wiki.foodsharing.de/Rechtsvereinbarung#Rechtsvereinbarung_Teil_II_-_Haftungsausschluss" target="_blank" style="text-decoration: none;"><span>Haftungsausschluss</span></a><span> zugestimmt haben. Mit der Abgabe der Lebensmittel an die Foodsaver &uuml;bernehmen wir die volle Verantwortung f&uuml;r deren weitere Verwendung. Wir verpflichten uns zur Zuverl&auml;ssigkeit den Betrieben gegen&uuml;ber und zur Einhaltung der hygienischen Richtlinien bei Lagerung und Transport der Ware. Wir verpflichten uns au&szlig;erdem, die Waren nicht weiter zu verkaufen.</span></li>
</ul>
<p><strong>Kontakt: <a href="mailto:info@foodsharing.de" target="_blank">info@foodsharing.de</a><br /></strong></p>', 'last_mod' => '2020-01-06 17:50:50'],
			['id' => '8', 'name' => 'impressum', 'title' => 'Impressum', 'body' => '<div class="mainframe">
<div class="imprint">
<h4>Angaben gem&auml;&szlig; &sect; 5 TMG:</h4>
<p>Foodsharing e.V.<br />Neven-DuMont-Str. 14<br /> 50667 K&ouml;ln</p>
<h4>Vertreten durch:</h4>
<p>Frank Bowinkelmann<br /> <span>Foodsharing e.V.</span><br />Neven-DuMont-Str. 14<br />50667 K&ouml;ln</p>
<h4>Kontakt:</h4>
<p>E-Mail:&nbsp;<a href="mailto:info@foodsharing.de" target="_blank">info@foodsharing.de</a><br /> Fax: 0221 /&nbsp;<span>9420 2512</span></p>
<h4>Registereintrag:</h4>
<p>Eintragung im Vereinsregister.<br /> Registergericht: Amtsgericht K&ouml;ln<br /> Registernummer: VR 17439</p>
<h4>Satzung:</h4>
<p><a href="https://wiki.foodsharing.de/images/d/d2/Satzung_foodsharing_eV_Bundesweit_Sitz_Koeln.pdf" target="_blank">zum Download</a></p>
<h4>Verantwortlich f&uuml;r den Inhalt nach &sect; 55 Abs. 2 RStV:</h4>
<p>Frank Bowinkelmann<br /> <span>Neven-DuMont-Str. 14</span><br /><span>50667 K&ouml;ln</span></p>
<h4>Haftungsausschluss:</h4>
<p><strong>Haftung f&uuml;r getauschte Lebensmittel</strong><br /> Der Seitenbetreiber Foodsharing e.V. und seine F&ouml;rderinstitutionen, Sponsoren, Spender und Dienstleister &uuml;bernehmen keinerlei Haftung f&uuml;r die Angebote Dritter auf der Webseite foodsharing.de und Lebensmittelretten.de. Die Internet-Seite foodsharing.de und lebensmittelretten.de vermittelt lediglich Anbieter von Lebensmitteln und Interessenten an diesen Lebensmitteln. Verantwortlich f&uuml;r das Befolgen aller privatrechtlichen, lebensmittelrechtlichen und gesundheitlich bedeutenden Aspekte beim Teilen von Lebensmitteln sind die Anbieter und Interessenten selbst. Wir weisen ausdr&uuml;cklich auf die Regeln und Informationen im Bereich <a href="../ratgeber" target="_blank">&bdquo;Ratgeber und Foodsharing-Etikette&ldquo;</a> hin, die f&uuml;r alle Nutzer verbindlich gelten. Foodsharing e.V. ist kein Lebensmittelunternehmer im Sinne der EU- und der deutschen Gesetze und Verordnungen. <br />Quelle: Foodsharing e.V.<br /> <strong>Haftung f&uuml;r Inhalte</strong><br /> Die Inhalte unserer Seiten wurden mit gr&ouml;&szlig;ter Sorgfalt erstellt. F&uuml;r die Richtigkeit, Vollst&auml;ndigkeit und Aktualit&auml;t der Inhalte k&ouml;nnen wir jedoch keine Gew&auml;hr &uuml;bernehmen. Als Diensteanbieter sind wir gem&auml;&szlig; &sect; 7 Abs.1 TMG f&uuml;r eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach &sect;&sect; 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, &uuml;bermittelte oder gespeicherte fremde Informationen zu &uuml;berwachen oder nach Umst&auml;nden zu forschen, die auf eine rechtswidrige T&auml;tigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unber&uuml;hrt. Eine diesbez&uuml;gliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung m&ouml;glich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.<br /> <strong>Haftung f&uuml;r Links</strong><br /> Unser Angebot enth&auml;lt Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb k&ouml;nnen wir f&uuml;r diese fremden Inhalte auch keine Gew&auml;hr &uuml;bernehmen. F&uuml;r die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf m&ouml;gliche Rechtsverst&ouml;&szlig;e &uuml;berpr&uuml;ft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.<br /> <strong>Urheberrecht</strong><br /> Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielf&auml;ltigung, Bearbeitung, Verbreitung und jede Art der Verwertung au&szlig;erhalb der Grenzen des Urheberrechtes bed&uuml;rfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur f&uuml;r den privaten, nicht kommerziellen Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.</p>
<p></p>
</div>
</div>', 'last_mod' => '2019-12-02 23:38:34'],
			['id' => '9', 'name' => 'mission', 'title' => 'Mission ', 'body' => '<p><strong>Herzlich Willkommen bei foodsharing!<br /></strong><br /> <span>foodsharing ist eine 2012 entstandene Initiative gegen die Lebensmittelverschwendung, welche Lebensmittel "rettet", die man ansonsten wegwerfen w&uuml;rde.</span> <span>&Uuml;ber 200.000 registrierte NutzerInnen in Deutschland/&Ouml;sterreich/Schweiz, und &uuml;ber 25.000 Freiwillige, sogenannte Foodsaver, machen diese Initiative mittlerweile zu einer internationalen Bewegung. Es kooperieren &uuml;ber 3.000 Betriebe, bei denen bisher schon 7,8 Millionen Kilogramm Lebensmittel vor der Verschwendung bewahrt worden sind. T&auml;glich finden etwa 1.000 weitere Abholungen statt.</span><br /><br /> <span>Die Plattform foodsharing.de basiert auf ehrenamtlichem Engagement. Das Retten und Teilen von Lebensmitteln findet geldfrei statt. Der gemeinn&uuml;tzige foodsharing e.V. sorgt als Betreiber der Webseite daf&uuml;r, dass diese unkommerziell und ohne Werbung bleibt.</span><br /> <br /><span>Allein in Deutschland ist die Lebensmittelverschwendung ein gro&szlig;es Problem: Etwa ein Drittel aller Lebensmittel werden verschwendet. Und dabei wird nicht nur das Lebensmittel an sich weggeworfen, sondern auch die Ressourcen die z.B. in Anbau, Ernte, Verpackung, Transport und Lagerung geflossen sind.</span> <span>Die Verschwendung findet &uuml;berall statt: bei Anbau, Ernte, Weiterverarbeitung, Verkauf sowie beim Endverbraucher.</span> <span>foodsharing sensibilisiert f&uuml;r das Thema soweit m&ouml;glich bei allen AkteurInnen mit denen die Initiative in Kontakt steht. Bei unterschiedlichen Aktionen machen die MitstreiterInnen auf die unglaubliche Verschwendung in der Gesellschaft aufmerksam und bieten L&ouml;sungsans&auml;tze an.</span> <span>Ziel ist, auf pers&ouml;nlicher Ebene Aufkl&auml;rung, Umdenken und verantwortliches Handeln anzusto&szlig;en.</span><br /><br /> <span>foodsharing bringt Menschen unterschiedlichster Hintergr&uuml;nde zusammen und begeistert zum Mitmachen, Mitdenken und verantwortungsvollem Umgang mit den Ressourcen unseres Planeten.</span> <span>Es gibt keine andere Initiative dieser Gr&ouml;&szlig;e, welche in diesem Umfang ehrenamtlich t&auml;tig ist, &ouml;ffentlich kommuniziert, wie viele Lebensmittel weggeworfen werden, und aus einer Nachhaltigkeitsperspektive L&ouml;sungsans&auml;tze bietet.</span><br /> <span>___</span><br /> <span>Hier erf&auml;hrst Du mehr &uuml;ber uns:</span><br /> <span><a href="/?page=content&amp;sub=forderungen" target="_blank"><strong>Forderungen</strong></a> - unser Forderungspapier</span><br /> <span><strong><a href="/team" target="_blank">Team</a></strong>&nbsp;- unser Team und foodsharing Kontakte</span><br /> <span><a href="/partner" target="_blank"><strong>Partner</strong></a> - diese Partner unterst&uuml;tzen foodsharing</span><br /> <span><a href="/statistik" target="_blank"><strong>Statistik</strong></a> - foodsharing Zahlen &amp; Fakten</span><br /> <span><a href="/?page=content&amp;sub=presse" target="_blank"><strong>Presse</strong></a> - Kontakte und Informationen f&uuml;r Presse</span></p>
<p><br /><span>F&uuml;r aktuelle Berichte und Ank&uuml;ndigungen kannst Du uns auch&nbsp;<a href="https://de-de.facebook.com/foodsharing.de" target="_blank">auf facebook</a> besuchen.</span> <br /><span>Ausf&uuml;hrliche Informationen &uuml;ber unser Lebensmittelretten findest Du in <a href="https://youtu.be/dqsVjuK3rTc" target="_blank">diesem Erkl&auml;r-Video</a>.</span><br /><br /> <span>Wir w&uuml;nschen Dir viel Spass und freuen uns &uuml;ber Anregungen und Fragen!</span><br /> <span>Dein foodsharing-Team</span></p>', 'last_mod' => '2019-06-15 12:46:42'],
			['id' => '10', 'name' => 'partner', 'title' => 'Partner', 'body' => '<!--
Die Seite wird durch Julian Brinke (julian@foodsharing-krefeld.de) betreut.
Logogroesse (hxb) 100x390 px (max)
Bitte alle Änderungen absprechen
-->
<p></p>
<!-- -->
<p></p>
<!-- -->
<p></p>
<!-- Unterstützt durch -->
<p></p>
<!-- -->
<p></p>
<!-- -->
<div class="head ui-widget-header ui-corner-top">Unterst&uuml;tzt durch:</div>
<div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
<div class="partner">
<h3><a href="https://www.geoapify.com/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/geoapify.png" class="logo" /> <br /> Geoapify GmbH | geoapify.com </a></h3>
<div class="clear"></div>
<p>Schnelle, moderne und kosteng&uuml;nstige APIs f&uuml;r Karten, Adressensuche, Routing und Navigation f&uuml;r Ihre App und Website. Gerne bieten wir zuverl&auml;ssige Geo- und Standortdienste f&uuml;r jedes Projekt, egal ob gro&szlig; oder klein.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://kanzlei-broich.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/Broich.png" class="logo" /> <br /> Broich | kanzlei-broich.de </a></h3>
<div class="clear"></div>
<p>Rechtsanwalt Bernd Broich entwickelte 2017 gemeinsam mit foodsharing die Mustersatzungen f&uuml;r Ortsvereine. Dar&uuml;ber hinaus ber&auml;t er den foodsharing e.V. in allen Fragen rund um das Thema Vereinsrecht.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://cms.law/de/deu/" target="_blank"><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/CMS.png" class="logo" /> <br /> CMS | cms.law </a></h3>
<div class="clear"></div>
<p>Seit Juni 2018 unterst&uuml;tzt uns die Anwaltskanzlei CMS bei &uuml;berregionalen Angelegenheiten in vielf&auml;ltigen Themenbereichen wie Lebensmittelrecht, Haftungsfragen, Versicherungs-Recht, Vereinsrecht, Steuerrecht und Datenschutz. Die Anw&auml;lte die uns unterst&uuml;tzen sind in ganz Deutschland verteilt, mit einem Schwerpunkt in K&ouml;ln - wobei die Zusammenarbeit sowieso online erfolgt. Wir freuen uns sehr &uuml;ber diese wichtige Unterst&uuml;tzung, und hoffen auf eine gute und langfristige Zusammenarbeit.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://pinkcarrots.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/pinkcarrots.jpg" class="logo" /> <br /> PINK CARROTS | pinkcarrots.de </a></h3>
<div class="clear"></div>
<p>Zum 5 j&auml;hrigen foodsharing Geburtstag (Dezember 2017) wurde die "don\'t let good food go bad" Kampagne gelauncht. Die Kommunikationsagentur PINK CARROTS aus Frankfurt hat das Kampagnen Konzept f&uuml;r foodsharing entwickelt und mit ihrem Know How auch bei dem Entwurf zur neuen Startseite unterst&uuml;tzt. Die Kampagne richtet sich insbesondere an Menschen, die bisher wenig &uuml;ber Lebensmittelverschwendung oder foodsharing wissen. Der humorvolle Auftritt der f&uuml;nf illustrierten Charakt&auml;re "walking bread", "dick milch", "saure gurke", "faules ei" und "food porn" wird von zahlreichen Bezirken in Deutschland, &Ouml;sterreich und Schweiz genutzt, um die Message "don\'t let good food go bad" weiter zu verbreiten und auf die foodsharing Aktivit&auml;ten aufmerksam zu machen.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://www.manitu.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/manitu.jpg" class="logo" /> <br /> Manitu | manitu.de </a></h3>
<div class="clear"></div>
<p>Da wir mittlerweile schon &uuml;ber drei Millionen Seitenaufrufe hatten&nbsp;und tagt&auml;glich mehr Menschen die Plattform nutzen, haben wir uns nach einem neuen Partner umgeschaut und einen ganz vorbildlichen Betrieb gefunden der uns mit einem eigenem Server unterst&uuml;tzt. Manitu arbeitet seit Jahren ausschlie&szlig;lich mit Strom aus erneuerbaren Energien und setzt sich ganzheitlich mit einer ethischen Firmenphilosophie f&uuml;r Nachhaltigkeit und mehr Menschlichkeit ein.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://greensta.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/greensta.jpg" class="logo" /> <br /> Greensta &Ouml;ko Webhosting | greensta.de </a></h3>
<div class="clear"></div>
<p>Seit Beginn im Sommer 2013 unterst&uuml;tzte Greensta mit einem mit Greenpeace Energy&nbsp;laufenden Server die kostenlose Freiwilligenplattform von foodsharing. Damit geh&ouml;rt die Unterst&uuml;tzung von dem zu 100 % mit erneuerbaren Energien arbeitenden Firma Greensta zum elementaren Fundament der lebensmittelrettenden Bewegung, die ausschlie&szlig;lich auf unendgeltlichem Engagment fu&szlig;t.<br /> Derzeit unterst&uuml;tzt unser Parnter Greensta unsere&nbsp;Initiative gegen die Verschwendung von Lebensmitteln mit der &Uuml;bernahme der Kosten f&uuml;r das Mail-Konto @foodsharing.de.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://bitkomplex.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/bitkomplex_logo.png" width="494" class="logo" /> <br /> Bitkomplex </a></h3>
<div class="clear"></div>
<p>Im Januar ist bitkomplex kurzfristig eingesprungen und betreibt seitdem unseren E-Mail Verkehr f&uuml;r foodsharing.network. Die Server von Bitkomplex werden ausschlie&szlig;lich mit &Ouml;kostrom betrieben.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://abd-partner.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/ABundD.jpg" class="logo" /> <br /> ab&amp;d Rechtsanw&auml;lte | abd-partner.de </a></h3>
<div class="clear"></div>
<p>Tobias Bystry und seine Kanzlei ab&amp;d Rechtsanw&auml;lte unterst&uuml;tzen foodsharing seit Fr&uuml;hling&nbsp;2013&nbsp;mit einer&nbsp;pro Bono Hilfe in allen&nbsp;Rechtsfragen rund&nbsp;um:&nbsp;Fair-Teiler, Lebensmittelspenderbetrieben und sonstigen Vereinbarungen und Rechtsangelegenheiten.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://bosch-stiftung.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/Robert_Bosch_Verantwortlichen.jpg" class="logo" /> <br /> Robert Bosch Stiftung - Verantwortlichen Programm | bosch-stiftung.de </a></h3>
<div class="clear"></div>
<p>Seit Herbst 2017 unterst&uuml;tzt die Robert Bosch Stiftung foodsharing in ihrem Programm "Die Verantwortlichen" durch beratende T&auml;tigkeiten bei der aktuell laufenden Umstrukturierung.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://bmbf.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/bmbf.jpg" class="logo" /> <br /> Bundesministerium f&uuml;r Bildung und Forschung | bmbf.de </a></h3>
<div class="clear"></div>
<p>Das Bundesministerium f&uuml;r Bildung und Forschung unterst&uuml;tzte foodsharing bei einzelnen lokalen Projekten.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://prototypefund.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/Prototype_Fund.jpg" class="logo" /> <br /> Prototype Fund | prototypefund.de </a></h3>
<div class="clear"></div>
<p>Der Prototype Fund f&ouml;rderte den foodsharing-Programmierer Raphael Wintrich in 2017 f&uuml;r 6 Monate, um die OpenSource-Stellung des aktuellen Codes voran zu bringen.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://dieUmweltDruckerei.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/dieUmweltDruckerei.jpg" class="logo" /> <br /> dieUmweltDruckerei | dieUmweltDruckerei.de </a></h3>
<div class="clear"></div>
<p>Als nachhaltige Druckerei setzen wir auf ressourcenschonende Materialien und eine emissionsarme Produktion von Printmedien. Wir verwenden ausschlie&szlig;lich Recyclingpapiere. Bei den von uns eingesetzten veganen Druckfarben sind mineral&ouml;lhaltige Bestandteile weitestgehend durch Zutaten auf Basis nachwachsender Rohstoffe ersetzt. Wir arbeiten mit Strom aus erneuerbaren Energien. Alle unvermeidbaren CO2-Emissionen, die im gesamten Druckprozess und beim Versand entstehen, kompensieren wir und unsere Partner durch Investitionen in Klimaschutzprojekte.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://print-pool.com/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/PrintPool.jpg" class="logo" /> <br /> Print Pool | print-pool.com </a></h3>
<div class="clear"></div>
<p>Print Pool - Planet Friendly Printing ist eine der &ouml;koligischsten Druckereien in Deutschland und unser starker Partner f&uuml;r den Printbereich.<br />Verantwortung f&uuml;r Umwelt-Themen und Schonung von Ressourcen bilden das Fundament dieser nachhaltigen Druckerei beim umweltfreundlichen Drucken. Flyer aus Recyclingpapier oder Visitenkarten aus FSC-Mix geh&ouml;ren zum nachhaltigen Standard. Gedruckt wird&nbsp;mit mineral&ouml;lfreien Druckfarben auf Pflanzen&ouml;lbasis. Die verwendeten Bindeleime sind kasein- und gelatinefrei und entsprechen veganen Standards.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://drucknatuer.ch/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/Drucknatuer.svg.png" class="logo" /> <br /> Drucknat&uuml;r | drucknatuer.ch </a></h3>
<div class="clear"></div>
<p>Dank der kostenlosen Flyerspenden von Drucknat&uuml;r, k&ouml;nnen wir seit 2016 auch in der Schweizer foodsharing-Community &ouml;kologisch bedacht und trotzdem geldfrei Flyer zur Verf&uuml;gung stellen. Wir freuen uns weiterhin auf eine gute und lange Zusammenarbeit - 1000 Dank ans Drucknat&uuml;r-Team!</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://sticker-ticker.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/StickerTicker.jpg" class="logo" /> <br /> Sticker-Ticker | sticker-ticker.de </a></h3>
<div class="clear"></div>
<p>Da wir selber in der Foodsharing Community aktiv sind, lag es f&uuml;r uns nahe, das Projekt auch mit Aufklebern tatkr&auml;ftig zu unterst&uuml;tzen. Generell ist bei uns ein verantwortungsbewusster Umgang mit allen Ressourcen selbstverst&auml;ndlich. Zudem verwenden wir fast ausschlie&szlig;lich Verpackungsmaterialen von befreundeten Unternehmen wieder, was nicht nur g&uuml;nstiger, sondern (ganz &auml;hnlich wie das Foodsharing) &auml;u&szlig;erst sinnvoll ist!</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://badgematic.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/badgematic.jpg" class="logo" /> <br /> Badgematic | badgematic.de </a></h3>
<div class="clear"></div>
<p>Die Badgematic Button GmbH vertreibt alle n&ouml;tigen Materialien rund um die (eigene) Buttonherstellung und spendete f&uuml;r das Internationale Treffen erstmals 500 Buttonrohlinge, damit sich Foodsaver unterwegs auch wiedererkennen k&ouml;nnen. So sind sie seit April 2015 Partner von foodsharing und wir danken herzlich f&uuml;r die Unterst&uuml;tzung!</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://tictex.com/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/tictex.jpg" class="logo" /> <br /> TicTex | tictex.com </a></h3>
<div class="clear"></div>
<p>foodsharing unterst&uuml;tzen wir mit Textilien f&uuml;r Veranstaltungen und Promotionzwecke, weil wir daran glauben, dass Nachhaltigkeit mehr als ein Schlagwort sein sollte. TicTex ist&nbsp;der Onlineshop&nbsp;f&uuml;r Basic-Fashion und Textilveredelung. Entgegen dem Trend der schnelllebigen Mode vertreiben wir Basics, die nicht nach 3 Monaten aussortiert werden, weil sie nicht mehr \'in\' sind. Ganz besonders freut es uns, verst&auml;rkt Hersteller im Sortiment zu haben, die auf faire Produktion, Recycling und Biobaumwolle Wert legen &ndash; wie zum Beispiel EarthPositive und Salvage.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://klin-tec.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/klintec.jpg" class="logo" /> <br /> klin-Tec | klin-tec.de </a></h3>
<div class="clear"></div>
<p>Die Marke KLIN-TEC&reg; steht f&uuml;r einen vorausschauenden und verantwortungsbewussten Umgang mit unserem wichtigsten Rohstoff Wasser. Auch in Zukunft muss jeder Mensch freien Zugang zu sauberem, trinkf&auml;higem Wasser haben. Um hierf&uuml;r nicht noch mehr Tonnen von Plastikflaschen produzieren zu m&uuml;ssen und die Umwelt weiter zu belasten, haben wir den KLIN-TEC&reg; Wasserfilter entwickelt. Wir unterst&uuml;tzen die Veranstaltungen des &sbquo;foodsharing-Projekts&lsquo; sehr gerne mit unseren Produkten.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://prime-inventions.de/" target="_blank"> Prime Inventions | prime-inventions.de </a></h3>
<div class="clear"></div>
<p>Prime Inventions entwickelt und vertreibt weltweit innovative Produkte rund um das Thema sauberes Wasser. Unter den eigenen eingetragenen Marken Aquawhirler, AquaKalko, AquaAvanti und YaraOvi sind die Produkte weltweit bekannt. Es ist Prime Inventions ein besonderes Anliegen Nutzer zu helfen ihr eigenes Wasser vor Ort zur filtern und damit kostbare Ressourcen zu sparen und unn&ouml;tigen Abfall zu vermeiden. Prime Inventions setzt sich ebenso f&uuml;r foodsharing ein und freut sich &uuml;ber jedes gerettete Lebensmittel und hat dies sogar ins Firmenleitbild mit aufgenommen.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
</div>
<!-- ui-widget ui-widget-content corner-bottom margin-bottom ui-padding -->
<p></p>
<!-- -->
<p></p>
<!-- -->
<p></p>
<!-- Aktuerre im Netzwerk -->
<p></p>
<!-- -->
<p></p>
<!-- -->
<div class="head ui-widget-header ui-corner-top">Akteure im Netzwerk:</div>
<div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
<div class="partner">
<h3><a href="https://tafel.de/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/Tafel_Deutschland.jpg" class="logo" /> <br /> Die Tafeln | tafel.de </a></h3>
<div class="clear"></div>
<p>Die Tafeln: Lebensmittel retten. Menschen helfen. Seit Anfang an arbeitet foodsharing eng mit den bundesweiten Tafeln zusammen. foodsharing l&auml;sst der Tafel bei Kooperationen und Abholungen aufgrund ihres Bed&uuml;ftigkeits-Anspruches immer den Vortritt. Mit dem starken Nachhaltigkeits-Anspruch und dem Wunsch, der Lebensmittelverschwendung in allen Bereichen entgegen zu wirken, rettet foodsharing dort und zu den Zeitpunkten, wo es der Tafel nicht m&ouml;glich ist. So bilden beide eine wunderbare Erg&auml;nzung, und zusammen ein starkes Team. Mit der offiziellen Kooperationsvereinbarung in 2015 hat dies auch einen formellen Rahmen bekommen, und foodsharing freut sich auf die weitere enge und gute Zusammenarbeit gegen die Lebensmittelverschwendung.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://duh.de/themen/recycling/abfallvermeidung/lebensmittelverschwendung/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/DUH.jpg" class="logo" /> <br /> Deutsche Umwelthilfe (DUH) | duh.de </a></h3>
<div class="clear"></div>
<p>Die Deutsche Umwelthilfe (DUH) ist ein anerkannter Umwelt- und Verbraucherschutzverband, der sich seit 1975 aktiv f&uuml;r den Erhalt unserer nat&uuml;rlichen Lebensgrundlagen und die Belange von Verbrauchern einsetzt. Sie ist politisch unabh&auml;ngig, gemeinn&uuml;tzig, klageberechtigt und engagiert sich vor allem auf nationaler und europ&auml;ischer Ebene. Kritische Verbraucher, Umweltorganisationen, Politiker, Entscheidungstr&auml;ger aus der Wirtschaft sowie Medien sind wichtige Partner. Im Bereich Kreislaufwirtschaft setzt sich die DUH f&uuml;r Abfallvermeidung, einen verantwortlichen Konsum und eine nachhaltige Wirtschaftsweise ein.</p>
</div>
<!-- /partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
</div>
<!-- ui-widget ui-widget-content corner-bottom margin-bottom ui-padding -->
<p></p>
<!-- -->
<p></p>
<!-- -->
<p></p>
<!-- Wir sind Mitglieb bei -->
<p></p>
<!-- -->
<p></p>
<!-- -->
<div class="head ui-widget-header ui-corner-top">Wir sind Mitglied bei:</div>
<div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
<div class="partner">
<h3><a href="https://wir-haben-es-satt.de" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/wir_haben_es_satt.jpg" width="2197" class="logo" /> <br /> Wir haben es satt!-B&uuml;ndnis | wir-haben-es-satt.de </a></h3>
<div class="clear"></div>
<p>F&uuml;r eine andere Landwirtschaftspolitik Das B&uuml;ndnis steht f&uuml;r die Agrar- und Ern&auml;hrungswende. Wir fordern den Stopp der industriellen Landwirtschaft &amp; Lebensmittelproduktion. Wir wollen artgerechte Tierhaltung und gut erzeugte Lebensmittel von B&auml;uerinnen und Bauern f&uuml;r alle! Daf&uuml;r gehen wir demonstrieren, bringen Menschen aus Stadt und Land im Dialog und f&uuml;r Aktionen zusammen. Wir sind bunt, vielf&auml;ltig und wir ziehen an einem Strang - von konventionell bis Bio, von Verbraucher*innen bis Lebensmittelhandwerk. Wir haben es satt! wird von rund 55 Organisationen getragen.</p>
</div>
<!-- partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://www.zivilgesellschaft-ist-gemeinnuetzig.de" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/allianz.jpg" class="logo" /> <br /> Allianz &bdquo;Rechtssicherheit f&uuml;r politische Willensbildung&ldquo; | zivilgesellschaft-ist-gemeinnuetzig.de </a></h3>
<div class="clear"></div>
<p>Wir sind Mitglied der Allianz &bdquo;Rechtssicherheit f&uuml;r politische Willensbildung&ldquo;, um gemeinsam mit anderen Organisationen das Gemeinn&uuml;tzigkeitsrecht zu &auml;ndern. Zivilgesellschaft ist gemeinn&uuml;tzig &ndash; doch Organisationen der Zivilgesellschaft, die sich politisch &auml;u&szlig;ern, sind st&auml;ndig der Gefahr ausgesetzt, ihre Gemeinn&uuml;tzigkeit zu verlieren. Das wollen wir &auml;ndern und Rechtssicherheit schaffen durch gesetzliche Klarstellungen.</p>
</div>
<!-- partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://klima-allianz.de" target="_blank"> <img src="https://foodsharing-krefeld.de/_partner/klima-allianz.jpg" width="482" class="logo" /> <br /> Klima Allianz | klima-allianz.de.de </a></h3>
<div class="clear"></div>
<p>Die Klima-Allianz Deutschland ist das breite gesellschaftliche B&uuml;ndnis f&uuml;r den Klimaschutz. Mit &uuml;ber 120 Mitgliedsorganisationen aus den Bereichen Umwelt, Kirche, Entwicklung, Bildung, Kultur, Gesundheit, Verbraucherschutz, Jugend und Gewerkschaften setzt sie sich f&uuml;r eine ambitionierte Klimapolitik und eine erfolgreiche Energiewende auf lokaler, nationaler, europ&auml;ischer und internationaler Ebene ein. Ihre Mitgliedsorganisationen repr&auml;sentieren zusammen rund 25 Millionen Menschen.</p>
</div>
<!-- partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
</div>
<!-- ui-widget ui-widget-content corner-bottom margin-bottom ui-padding -->
<p></p>
<!-- -->
<p></p>
<!-- -->
<p></p>
<!-- Auszeichnungen -->
<p></p>
<!-- -->
<p></p>
<!-- -->
<div class="head ui-widget-header ui-corner-top">Auszeichnungen:</div>
<div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
<div class="partner">
<h3><a href="https://www.worldsummitawards.org/" target="_blank"> <img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/wsa.jpg" class="logo" /> <br /> WorldSummit Award | worldsummitawards.org</a></h3>
<div class="clear"></div>
<p>Gewinner des WSA-Germany 2018<br /> Nominiert f&uuml;r den UN-World Summit Award 2018</p>
</div>
<!-- partner -->
<div class="shortcode-spacer-1">&nbsp;</div>
</div>
<!-- ui-widget ui-widget-content corner-bottom margin-bottom ui-padding -->
<p></p>
<!-- -->
<p></p>
<!-- -->
<p></p>
<!-- Kooperatonsbetriebe -->
<p></p>
<!-- -->
<p></p>
<!-- -->
<div class="head ui-widget-header ui-corner-top">Kooperationsbetriebe:</div>
<div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/10-Logo-Bio-Company.jpg" width="350" class="logo" /></h3>
<p><strong>Bio Company</strong>:<br /> <a href="https://biocompany.eu" target="_blank">biocompany.eu</a></p>
<div class="clear"></div>
<p>Die Bio Company ist der erste&nbsp;Partner von foodsharing und hat schon&nbsp;im April&nbsp;2012 begonnen unverk&auml;ufliche Lebensmittel an die LebensmittelretterInnen in Berlin zu geben. Auch bei der Crowdfunding Kampagne f&uuml;r foodsharing unterst&uuml;tze die Bio Supermarkt Kette die Plattfrom mit&nbsp;2000&euro;. Mittlerweile kooperieren&nbsp;die meisten der 35 Filialen neben Tafeln, Vereinen und anderen Einrichtungen mit foodsharing, damit m&ouml;glichst keine Lebensmittel mehr in die Tonne m&uuml;ssen. Der Gesch&auml;ftsf&uuml;hrer Georg Kaiser ebnete mit seinem Vertrauen und seinem Engagement den Weg f&uuml;r das heutige foodsharing-Netzwerk.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/Kaufland_online_KL_standard_L_sRGB.png" width="130" class="logo" /></h3>
<p><strong>Kaufland</strong>:<br /> <a href="https://kaufland.de" target="_blank">Kaufland.de</a></p>
<div class="clear"></div>
<p>Die &Uuml;bernahme von &ouml;kologischer und sozialer Verantwortung ist f&uuml;r Kaufland wichtiger Bestandteil der Unternehmenspolitik. Dies beginnt bei einer verantwortungsvollen Gestaltung des Sortiments und setzt sich mit dem Engagement f&uuml;r gesellschaftliche und &ouml;kologische Belange fort. Ein besonderes Anliegen ist Kaufland der verantwortungsvolle Umgang mit Lebensmitteln, wozu auch die Vermeidung von Lebensmittelabf&auml;llen geh&ouml;rt. Gemeinsam mit foodsharing engagiert sich Kaufland daher aktiv gegen Lebensmittelverschwendung.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/superbiomarkt.jpg" width="200" class="logo" /></h3>
<p><strong>Super Bio Markt</strong>:<br /> <a href="https://superbiomarkt.de" target="_blank">superbiomarkt.de</a></p>
<div class="clear"></div>
<p>Als Naturkostfachh&auml;ndler mit tief verwurzelten &ouml;kologischen Werten ist es Teil der Unternehmensphilosophie der&nbsp;SuperBioMarkt&nbsp;AG, sich gegen die Verschwendung von Lebensmitteln einzusetzen. Neben weiteren Ma&szlig;nahmen in diesem Bereich kooperiert das Unternehmen seit Anfang 2014 mit foodsharing. Nach einer erfolgreichen Pilotphase in verschiedenen St&auml;dten wird nun die Zusammenarbeit schrittweise auf s&auml;mtliche M&auml;rkte&nbsp;ausgeweitet. Au&szlig;erdem unterst&uuml;tzen wir verschiedene Fair-Teiler mit&nbsp;kostenlosen K&uuml;hlger&auml;ten.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/erdkorn.jpg" width="150" class="logo" /></h3>
<p><strong>Erdkorn</strong>:<br /> <a href="https://erdkorn.de" target="_blank">erdkorn.de</a></p>
<div class="clear"></div>
<p>Die Biomarktkette Erdkorn stellt Lebensmittel, Fr&uuml;chte und Gem&uuml;se, die aufgrund des abgelaufenen Mindesthaltbarkeitsdatums bzw. mangelnder Frische in einer Filiale nicht mehr verkauft werden, Foodsavern zum Fairteilen und Verwerten zur Verf&uuml;gung. Die meisten der deutschlandweit 9 Filialen kooperieren bereits. Hier&nbsp;&auml;u&szlig;ert sich Erdkorn zur Zusammenarbeit und ihrem Engagement gegen die Verschwendung von Lebensmitteln.&nbsp;(<a href="https://erdkorn.de/cms/index.php/soziale-verantwortung/654-foodsharingde" target="_blank">Link</a>)</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3></h3>
<p><strong>Reformhaus Engelhardt</strong>:<br /> <a href="https://reformhaus-engelhardt.de" target="_blank">reformhaus-engelhardt.de</a></p>
<div class="clear"></div>
<p>Das Projekt foodsharing erf&auml;hrt unsere Unterst&uuml;tzung, weil es auf die Bedeutsamkeit von Lebensmitteln aufmerksam macht und ihnen besondere Wertsch&auml;tzung entgegenbringt. Das gro&szlig;artige soziale und nachhaltige Engagement, welches die Beteiligten aufbringen ist au&szlig;erordentlich und sollte eine weitaus gr&ouml;&szlig;ere gesellschaftliche Anerkennung erfahren als bisher. Wir freuen uns auch weiterhin den gemeinsamen Gedanken weiterzutragen und die Projektentwicklung durch unsere Beitr&auml;ge zu begleiten. In unseren Gesch&auml;ften werden foodsharing auch in Zukunft die T&uuml;ren offen sein. Ein verantwortungsbewusster Umgang mit Ressourcen ist in unserer Unternehmensphilosophie fest verankert. Wir integrieren diesen Gedanken in unserer t&auml;glichen Arbeit und richten unsere Entscheidungen fest danach aus.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/budni_300.jpg" width="170" class="logo" /></h3>
<p><strong>Budnikowsky</strong>:<br /> <a href="https://budni.de" target="_blank">budni.de </a></p>
<div class="clear"></div>
<p>Das Hamburger Drogeriemarktunternehmen Budnikowsky unterst&uuml;tzt foodsharing seit 2014 gerne mit Lebensmittelspenden aus verschiedenen BUDNI-Filialen. Mehr zu BUDNIs Engagement: <a href="https://budni.de/gutes-tun" target="_blank">https://budni.de/gutes-tun</a></p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/govinda-logo-500px.png" width="140" class="logo" /></h3>
<p><strong>Govinda Natur&nbsp;GmbH</strong>:<br /> <a href="https://govindanatur.de" target="_blank">govindanatur.de</a></p>
<div class="clear"></div>
<p>Foodsharing f&ouml;rdert einen verantwortungsvollen Umgang mit Lebensmitteln und setzt sich aktiv gegen Lebensmittelverschwendung ein. Govinda Natur ist von der vorbildlichen Arbeit tief beeindruckt und unterst&uuml;tzt die Initiative sehr gerne mit Lebensmitteln. Die Unternehmensphilosophie von Govinda Natur basiert auf der Wertsch&auml;tzung von Mensch und Umwelt. Geleitet von der Idee einer Kreislaufwirtschaft bem&uuml;ht sich der Naturkosthersteller um eine nachhaltige und ressourcenschonende Erzeugung der Produkte. Das Unternehmen engagiert sich seit vielen Jahren in Fair Trade Initiativen und unterst&uuml;tzt soziale und &ouml;kologische Projekte im In- und Ausland.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos von Partnern/naturgut logo.png" width="150" class="logo" /></h3>
<p><strong>Naturgut</strong>:<br /> <a href="https://naturgut.net" target="_blank">naturgut.net</a></p>
<div class="clear"></div>
<p>Der Bio-Supermarkt wei&szlig; die M&ouml;glichkeit sehr zu sch&auml;tzen, dass noch gute, aber nicht mehr verkaufbare Lebensmittel nicht in die Tonne wandern m&uuml;ssen, sondern seit 2015 &uuml;ber Foodsharing noch verteilt und gegessen werden k&ouml;nnen. So k&ouml;nnen hochwertige Bio-Produkte auf weiterem Wege interessierten Menschen nahe gebracht werden. NATURGUT ist der f&uuml;hrende Anbieter von Bio-Produkten in Stuttgart und Umgebung. Die 11 Filialen werden t&auml;glich mit frischer Ware beliefert - wann immer m&ouml;glich saisonal und regional. Es gibt 75 regionale Lieferanten, zu denen eine langj&auml;hrige Partnerschaft besteht.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/basicbio.jpg" width="150" class="logo" /></h3>
<p><strong>basic - Bio-Genuss f&uuml;r alle</strong>:<br /> <a href="https://basicbio.de/" target="_blank">basicbio.de/</a></p>
<div class="clear"></div>
<p>Wir freuen uns, bereits seit Mai 2016 Kooperationspartner von Foodsharing zu sein. Das Konzept hat uns von Anfang an &uuml;berzeugt, denn wir sehen uns als Biounternehmen in der Pflicht, f&uuml;r die Umwelt zu arbeiten und einen enkeltauglichen Lebensstil zu erm&ouml;glichen. Dazu geh&ouml;rt f&uuml;r uns, dass alle Lebensmittel, die produziert wurden, auch in den Verkauf gehen. Wir arbeiten engagiert und wirksam daran, Ph&auml;nomene wie &bdquo;Containern&ldquo; &uuml;berfl&uuml;ssig zu machen, indem wir mit Foodsharing so eng kooperieren, dass fast nichts mehr im Abfall landet, was noch genie&szlig;bar ist. Das ist, aus unserer Sicht, der bessere Weg.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/naturata-logo.svg.png" width="150px" class="logo" /></h3>
<p><strong>Naturata</strong>:<br /> <a href="https://naturata.de/" target="_blank">https://naturata.de/</a></p>
<div class="clear"></div>
<p>Als f&uuml;hrender Anbieter von biologischen und bio-dynamischen Lebensmitteln zeichnet sich die NATURATA AG durch beste Qualit&auml;t, Nachhaltigkeit und einzigartigen Geschmack aus. Die Marke macht dabei den extra Schritt, um Verbrauchern mehr als Standard Bio zu garantieren. Die rund 300 Premium-Produkte enthalten daher ausschlie&szlig;lich nat&uuml;rliche, biologische Zutaten und werden besonders schonend weiterverarbeitet. &Uuml;ber 50 Prozent der produzierten Produkte haben zudem Demeter-Qualit&auml;t. NATURATA entwickelt Produkte, die nicht nur Bio-Genuss auf h&ouml;chstem Niveau garantieren, sondern auch einen wertvollen Beitrag f&uuml;r Mensch und Umwelt leisten. Faire und vertrauensvolle Partnerschaften, nachhaltiges Wirtschaften und die F&ouml;rderung sozialer und umweltorientierter Themen sind wesentlicher Bestandteil der Unternehmensphilosophie.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/beiersdorf.png" class="logo" /></h3>
<p><strong>Beiersdorf AG</strong>:<br /> <a href="https://beiersdorf.de" target="_blank">beiersdorf.de</a></p>
<div class="clear"></div>
<p>Die Beiersdorf AG stieg unter dem Motto &bdquo;We care, you share&ldquo; als erstes Gro&szlig;unternehmen in die Zusammenarbeit mit Foodsharing e. V. ein. Die Initiative von Beiersdorf gr&uuml;ndet auf der Umsetzung der Nachhaltigkeitsstrategie &bdquo;We care.&ldquo; und unterst&uuml;tzt verantwortungsvolle Ressourcennutzung. Seit Juni 2013 spendet Beiersdorf regelm&auml;&szlig;ig nicht verbrauchte Speisen aus dem Hamburger Betriebsrestaurant &ndash; vom Auberginenmus bis zur Zitronencreme &ndash; an soziale Einrichtungen der Hansestadt und lebt vor, wie die Vermeidung von &Uuml;berproduktion im Catering und der Gemeinschaftsversorgung funktionieren kann.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/gekko.png" width="100" class="logo" /></h3>
<p><strong>Gekko Getr&auml;nke- &amp; Handelskollektiv</strong>:<br /> <a href="https://gekko-berlin.de" target="_blank">gekko-berlin.de </a></p>
<div class="clear"></div>
<p>Gekko setzt sich&nbsp;seit dem Start von foodsharing gegen die Verschwendung von noch genie&szlig;baren Getr&auml;nken ein. &Uuml;ber ein Duzend Paletten Getr&auml;nke konnten so schon seit Beginn der Kooperation vor der Vernichtung bewahrt werden.<br /> Gekko&nbsp;arbeitet mit verschiedenen Getr&auml;nkeherstellern zusammen, wobei die Gro&szlig;zahl der&nbsp;Partner kleine bzw. Kleinsthersteller oder Unternehmensgr&uuml;nder sind, die regional, biologisch oder per Handherstellung produzieren, kollektiv arbeiten, oder fair handeln.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/2-lemonaid-charitea-logo.jpg" width="120" class="logo" /></h3>
<p><strong>LemonAid</strong>:<br /> <a href="https://lemonaid.de" target="_blank">lemonaid.de</a></p>
<div class="clear"></div>
<p>Lemonaid &amp; ChariTea helfen foodharing seit Beginn des Projektes im Jahre 2012 mit der kostenfreien Bereitstellung von K&uuml;hlschr&auml;nken f&uuml;r verschiedene&nbsp;Fair-Teiler in ganz Deutschland. Sie machen Fairtrade-Limonaden &amp; Eistees aus nat&uuml;rlichen Zutaten. Mit jeder verkauften Flaschen unterst&uuml;tzen sie Entwicklungsprojekte in den Anbauregionen.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/Aronia.jpg.png" width="100" class="logo" /></h3>
<p><strong>Aronia Original</strong>:<br /> <a href="https://aronia-original.de" target="_blank">aronia-original.de</a></p>
<div class="clear"></div>
<p>Das junge&nbsp;Bio Unternehmen m&ouml;chte keine S&auml;fte, Nahrungserg&auml;nzungsmitteln usw. wegschmei&szlig;en und freut sich &uuml;ber die Zusammenarbeit mit den Lebensmittelrettenden, damit alle noch genie&szlig;baren Waren dort landen, wohin sie geh&ouml;ren.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3></h3>
<p><strong>Ethiquable</strong>:<br /> <a href="https://ethiquable.de" target="_blank">ethiquable.de</a></p>
<div class="clear"></div>
<p>Ethiquable ist eine Genossenschaft die sich ganzheitlich um einen fairen, ethischen Umgang mit Menschen, Tieren und Ressourcen bem&uuml;ht. Dabei ist den Menschen von Ethiquable auch wichtig, keine Lebensmittel wegzuschmei&szlig;en und achten darauf, r&uuml;cksichtsvoll und nachhaltig mit den Erzeugnissen umzugehen. Bleibt mal was &uuml;brig, was sich nicht mehr verkaufen l&auml;sst, werden die Lebensmittel an die Mitarbeitenden bzw. an foodsharing verschenkt.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/myey.gif" width="422" class="logo" /></h3>
<p><strong>MyEy</strong>:<br /> <a href="https://myey.info/" target="_blank">myey.de</a></p>
<div class="clear"></div>
<p>MyEy ist die wertvolle Alternative zu tierischem Ei beim Backen, Braten und Kochen. Im Jahr 2014 erhielt MyEy den peta progress award als "richtungsweisendes vorbildliches Unternehmen" mit seinen "fortschrittlichen Produkten f&uuml;r einen ethischen Lebensstil." Der Ei-Ersatz von MyEy ist nicht nur AUFschlagbar, sondern dar&uuml;ber hinaus auch noch VEGAN-zertifiziert und BIO-zertifiziert. Vegane Produkte sind die Grundlage f&uuml;r einen verantwortungsvollen Umgang mit Ressourcen auf unserem Planeten, daher unterst&uuml;tzen wir gerne das Projekt foodsharing.de.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/vetzi.jpg" width="100" class="logo" /></h3>
<p><strong>VETZI</strong>:<br /> <a href="https://vegane-schnitzel-selber-machen.de/" target="_blank">vetzi.de</a></p>
<div class="clear"></div>
<p>Nachhaltigkeit und ein bewusster und fairer Umgang mit Lebensmitteln sind ein wichtiger Teil der veganen Bewegung! Diese Ideen motivieren auch uns. Deswegen unterst&uuml;tzen wir von VETZI &ndash; "vegane Schnitzel selber machen" die Foodsharing Initiative. Ihr leistet einen unsch&auml;tzbaren Beitrag gegen Lebensmittelverschwendung und f&uuml;r eine soziale und &ouml;kologische Herangehensweise an das Thema Essen. Es ist inspirierend und ermutigend zu sehen wie viel schon in so kurzer Zeit erreicht wurde! Macht weiter so!</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/byodo.jpg" width="100" class="logo" /></h3>
<p><strong>Byodo</strong>:<br /> <a href="https://byodo.de/" target="_blank">byodo.de</a></p>
<div class="clear"></div>
<p>Unsere Ressourcen sind kostbar und kein Lebensmittel sollte verschwendet werden. Daher unterst&uuml;tzen wir die wertvolle Arbeit von Foodsharing sehr gerne. Bio-Qualit&auml;t, die man schmeckt, riecht, auf dem Gaumen f&uuml;hlt und genie&szlig;t &ndash; daf&uuml;r steht die Byodo Naturkost GmbH. Seit mehr als 30 Jahren stellt das inhabergef&uuml;hrte Unternehmen mit Sitz in M&uuml;hldorf am Inn Bio-Feinkost in h&ouml;chster 100% Bio-Qualit&auml;t und mit bestem Geschmack her. Die Produkte werden nur &uuml;ber den Bio-Fachhandel und Online-Shop vertrieben.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3></h3>
<p><strong>Landlinie</strong>:<br /> <a href="https://landlinie.de/" target="_blank">landlinie.de</a></p>
<div class="clear"></div>
<p>Die LANDLINIE Lebensmittel&ndash;Vertrieb GmbH mit Sitz in H&uuml;rth bei K&ouml;ln ist ein Lebensmittel&ndash;Gro&szlig;handel f&uuml;r biologisch erzeugte Produkte und Spezialist im Bio Obst&ndash; und Gem&uuml;se-Bereich. Wir sind seit &uuml;ber 25 Jahren erfolgreich deutschlandweit t&auml;tig, seit 1991 Demeter Vertragsh&auml;ndler. Wir beliefern ca. 500 Kunden regelm&auml;&szlig;ig mit einer Auswahl von rund 3.000 Lebensmitteln durch unseren eigenen Fuhrpark und Logistik&ndash;Partner. Eine punktgenaue Lieferung, eine optimale Beratung durch unsere Mitarbeiter im Au&szlig;en&ndash; und Innendienst sowie die Qualit&auml;t unserer Bio-Produkte bilden die Basis unseres Erfolges.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/Barnhouse%20Logo%20neu%20color.jpg" width="100" class="logo" /></h3>
<p><strong>Barnhouse</strong>:<br /> <a href="https://barnhouse.de/" target="_blank">barnhouse.de</a></p>
<div class="clear"></div>
<p>Seit unserer Firmengr&uuml;ndung vor 36 Jahren sind wir zu 100% dem Bio-Gedanken bei der Herstellung unserer Krunchys verpflichtet. F&uuml;r uns ist ein biologisch erzeugtes Produkt immer noch das bestm&ouml;gliche aller Lebensmittel &ndash; nie nur Trend oder Mode oder eine M&ouml;glichkeit, schnell Geld zu verdienen. Diese Achtung vor Nahrungsmitteln findet sich auch bei Foodsharing.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/logo-galeries-lafayette-16092015.png" width="200" class="logo" /></h3>
<p><strong>Galeries Lafayette</strong>:<br /> <a href="https://galerieslafayette.de/" target="_blank">galerieslafayette.de</a></p>
<div class="clear"></div>
<p>Die Kooperation mit Foodsharing besteht seit Anfang 2016. Seitdem werden t&auml;glich Obst, Gem&uuml;se, Feinkost, Backwaren und viele weitere K&ouml;stlichkeiten vor der Tonne gerettet und vor allem an soziale Abgabestellen weitergegeben. So freuen sich beispielsweise die Diakonie Neuk&ouml;lln, die Bahnhofsmission, diverse Fl&uuml;chtlingsheime und die Obdachlosen am Alex immer wieder &uuml;ber die Delikatessen, die von Galeries Lafayette gespendet wurden. <br /> Seit 1996 kommt auch Berlin in den Genuss franz&ouml;sischer Delikatessen &ndash; bei Galeries Lafayette in der Friedrichstra&szlig;e im Herzen der Hauptstadt. Hier ist die erste und auch die einzige deutsche Dependance beheimatet &ndash; und l&auml;ngst zu einem beliebten Treffpunkt aller Gourmets geworden.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://cap-markt.de//" target="_blank"><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/caplogo.png" width="170" class="logo" /></a></h3>
<p><strong>CAP-Lebensmittelm&auml;rkte</strong>:<br /> <a href="https://cap-markt.de/" target="_blank">cap-markt.de</a> | <a href="https://nintegra.de/" target="_blank">NintegrA gGmbH</a> | <a href="https://neuearbeit.de/" target="_blank">Neue Arbeit gGmbH</a></p>
<div class="clear"></div>
<p>Seit September 2016 unterst&uuml;tzen die CAP-Lebensmittelm&auml;rkte (der NintegrA gGmbH und des Sozialunternehmens Neue Arbeit GmbH) die Initiative Foodsharing e.V. Bereits seit &uuml;ber zehn Jahren liefern die CAP-M&auml;rkte Lebensmittel mit sehr kurzem Mindesthaltbarkeitsdatum an die Tafeln der Region. Mit der Zusammenarbeit von CAP und Foodsharing e.V. gelingt es nun fast vollst&auml;ndig, auf das Wegwerfen von Lebensmitteln zu verzichten.<br /> Als diakonisches Unternehmen verpflichtet uns unser Satzungsauftrag zur Bewahrung der Sch&ouml;pfung und damit einhergehend, alles Erdenkliche gegen Lebensmittelverschwendung zu tun. In unseren M&auml;rkten arbeiten mit einem Anteil von mindestens 40 Prozent Menschen mit einer Schwerbehinderung.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
<div class="partner">
<h3><a href="https://hobbybrauerversand.de/" target="_blank"><img src="https://media.foodsharing.de/files/Logos%20von%20Partnern/hobbybrauerversand.png" class="logo" /></a></h3>
<p><strong>Hopfen und mehr</strong>:<br /> <a href="https://hobbybrauerversand.de/" target="_blank">https://hobbybrauerversand.de/</a></p>
<div class="clear"></div>
<p>Bio-Zertifikat - Umweltschutz lebt vom mitmachen - Unser Partner - Hopfen und mehr - spendet uns regelm&auml;ssig Malzs&auml;cke zum hygienischen und umweltfreundlichen Verpacken von geretteten Lebensmitteln.</p>
</div>
<div class="shortcode-spacer-1">&nbsp;</div>
</div>
<!-- ui-widget ui-widget-content corner-bottom margin-bottom ui-padding -->
<p></p>', 'last_mod' => '2020-04-09 14:45:43'],
			['id' => '11', 'name' => 'statistik', 'title' => 'Statistik', 'body' => '<div>{STAT_GESAMT}</div>', 'last_mod' => '2019-03-11 22:39:57'],
			['id' => '12', 'name' => 'quiz-description', 'title' => 'Anleitung Quiz', 'body' => '<ul>
<li>Fragen und Antworten sorgf&auml;ltig durchlesen</li>
<li>Die (Deiner Meinung nach richtigen) Antworten durch draufklicken ausw&auml;hlen (mehrere Antworten k&ouml;nnen richtig sein)</li>
<li>Nach dem Beantworten der Frage, bekommst Du eine Zwischenauswertung mit Erkl&auml;rung zu den jeweiligen Antworten, was Dir dabei hilft zu verstehen, was warum richtig oder falsch ist.&nbsp;Lies Dir bitte alle Erkl&auml;rungen sorgf&auml;ltig durch und hinterlasse einen&nbsp;Kommentar, wenn etwas unlogisch, nicht verst&auml;ndlich oder der gleichen vorkommt.</li>
<li>Am Ende bekommst Du direkt ein Feedback, ob Du bestanden hast, welche Fehler Du wo genau gemacht hast und kannst noch mal in Ruhe&nbsp;die Begr&uuml;ndungen dazu durchlesen.</li>
</ul>
<p></p>', 'last_mod' => '2019-02-26 11:54:14'],
			['id' => '13', 'name' => 'quiz-failed', 'title' => 'Du hast leider bei 5 Versuchen das Quiz für Foodsaver nicht bestanden.', 'body' => '<p>Damit m&ouml;glichst viele Lebensmittel gerettet werden k&ouml;nnen, ist Zuverl&auml;ssigkeit, sichere Beherrschung der Abholungen und professionelles Auftreten bei den Betrieben und im Team unverzichtbar. Mit den Antworten, die du in allen deinen Quiz-Anl&auml;ufen gegeben hast, vermittelst du das nicht. Es ist deutlich, dass du als Foodsaver eher nicht geeignet bist. Du kannst dich trotzdem in der Gemeinschaft einbringen, indem du z. B. Essensk&ouml;rbe online stellst oder die Essensk&ouml;rbe von anderen abholst. Damit kannst du ebenfalls etwas gegen Lebensmittelverschwendung tun und musst daf&uuml;r kein Foodsaver sein.</p>', 'last_mod' => '2020-01-05 20:41:18'],
			['id' => '14', 'name' => 'confirm-fs', 'title' => 'Bestätigung Foodsaver', 'body' => '<p>Herzlichen Gl&uuml;ckwunsch, Du hast das Quiz zum Foodsaver bestanden!</p>
<p><span>Um das Upgrade abzuschlie&szlig;en, gehe bitte bis zum Ende der Seite und best&auml;tige, dass Du die Rechtsvereinbarung gelesen und akzeptiert hast. Mit einem Klick auf Best&auml;tigen wirst Du dann zum Foodsaver.</span></p>
<p><span>W&auml;hle einen Bezirk aus und der/die f&uuml;r dich zust&auml;ndige&nbsp;</span>BotschafterIn wird sich innerhalb der n&auml;chsten Tage mit Dir in Verbindung setzen.</p>
<p>Zun&auml;chst werden die 3 Einf&uuml;hrungsabholungen (<a href="http://wiki.foodsharing.de/Einf&uuml;hrungsabholungen" target="_blank">wiki.foodsharing.de/Einf&uuml;hrungsabholungen)</a>&nbsp;gemacht, daf&uuml;r schl&auml;gt der/die&nbsp;jeweilige BotschafterIn bzw. Vertrauensperson Termine f&uuml;r die Abholungen vor, die rechtzeitig (min. 24 Stunden) vor dem Termin von Dir best&auml;tigt werden. Du wirst dann&nbsp;informiert, wann und wo man sich trifft und wie man sich erkennt.&nbsp;</p>
<p>Teile weiter Deine &uuml;bersch&uuml;ssigen Lebensmittel,&nbsp;schau Dich auf der Karte um, lerne andere Foodsaver kennen, besuche Fair-Teiler und lese Dich weiter ins Wiki ein!</p>
<p>Sollte sich der oder die BotschafterIn von Dir nicht melden, suche auf der Karte nach den n&auml;chsten BotschafterInnen und schreibe sie an.</p>
<p></p>
<p>Willkommen&nbsp;in der Welt des Lebensmittelrettens! Viele Freude bei allem und auf einen guten Start!</p>
<p>Herzlich&nbsp;das&nbsp;gesamte foodsharing Team</p>', 'last_mod' => '2019-02-15 12:08:27'],
			['id' => '15', 'name' => 'confirm-bip', 'title' => 'Bestätigung BetriebsverantwortlicheR', 'body' => '<p>Herzlichen Gl&uuml;ckwunsch, Du hast das Quiz zum betriebsverantwortlichen Foodsaver bestanden... bitte best&auml;tigen</p>', 'last_mod' => '2014-08-13 01:07:13'],
			['id' => '16', 'name' => 'confirm-bot', 'title' => 'Bestätigung BotschafterIn', 'body' => '<p>Herzlichen Gl&uuml;ckwunsch, Du hast das Quiz zur/zum BotschafterIn bestanden.</p>
<p>Bitte nimm Dir noch ein paar Minuten Zeit und f&uuml;lle die nachfolgenden Fragen aus.</p>
<p></p>', 'last_mod' => '2014-08-13 01:08:05'],
			['id' => '17', 'name' => 'quiz-starttext', 'title' => 'Jetzt geht es los - folgendes ist zu beachten:', 'body' => '<p></p>
<ul>
<li>Bist Du sicher, dass Du alle aufgef&uuml;hrten Wiki-Dokumente aufmerksam durchgelesen und verinnerlicht hast? Ohne das Wissen und die Erfahrungen, welche im Wiki&nbsp;innerhalb von 2,5 Jahren zusammen getragen wurden, ist ein Bestehen des Quizes nicht m&ouml;glich.</li>
<li>W&auml;hrend des Quizes darf Dir nicht geholfen werden. Suche Dir deswegen einen ruhigen Ort in dem Du ungest&ouml;rt das Quiz machen kannst.</li>
<li>Bitte alle Fragen und Antworten sorgf&auml;ltig durchlesen.</li>
<li>
<p>Beachte, dass auch mehrere Antworten richtig sein k&ouml;nnen.</p>
</li>
<li>Nach dem Beantworten jeder einzelnen&nbsp;Frage bekommst Du eine Zwischenauswertung mit Erkl&auml;rung zu den jeweiligen Antworten. Diese hilft Dir dabei zu verstehen, was warum richtig oder falsch ist.&nbsp;Lies Dir bitte alle Erkl&auml;rungen sorgf&auml;ltig durch und hinterlasse ein Kommentar, wenn etwas unlogisch, nicht verst&auml;ndlich oder einfach falsch ist.</li>
<li>Am Ende bekommst Du direkt Feedback, ob Du bestanden hast und welche Fehler Du wo genau gemacht hast. Du kannst dann noch mal in Ruhe&nbsp;die Begr&uuml;ndungen dazu durchlesen.</li>
</ul>
<p><strong>Hinweis:</strong><br /><span>Alle hier aufgef&uuml;hrten Beschreibungen basieren auf F&auml;llen aus dem echten Foodsaver-Leben. Eine m&ouml;gliche Anlehnung an Ereignisse, die sich so oder so &auml;hnlich zugetragen haben, ist gewollt.</span></p>
<p></p>', 'last_mod' => '2018-08-16 20:34:33'],
			['id' => '18', 'name' => 'quiz-popup', 'title' => 'Quiz - Jetzt geht\'s los!', 'body' => '<p><span><strong>Jetzt geht es los! Bitte beachte</strong>:</span></p>
<ul>
<li>
<p><strong>Alles gelesen? </strong><span>Bist Du sicher, dass Du alle </span><span>im <strong>Wiki-Artikel</strong></span><a href="https://wiki.foodsharing.de/Quiz#Quiz_f.C3.BCr_Foodsaver" target="_blank" style="text-decoration: none;"><span> </span><span>Quiz</span></a><span> aufgef&uuml;hrten Wiki-Dokumente aufmerksam durchgelesen und verinnerlicht hast? Ohne das Wissen und die Erfahrungen, </span><strong>die dort</strong><span> zusammengetragen wurden, ist ein Bestehen des Quiz nicht m&ouml;glich.</span></p>
</li>
<li>
<p><span></span><strong>PC oder Laptop, nicht Telefon! </strong><span>Auf Mobiltelefonen sind die Fragenseiten un&uuml;bersichtlich, und manchmal funktioniert nicht alles richtig. Am besten geeignet ist der Firefox-Browser.</span></p>
</li>
<li>
<p><strong>Cache!</strong><span> Bitte leere vor jedem Quiz-Versuch den Browser-Cache, damit nicht aus Versehen die Fragenseite des letzten Durchlaufs aufgerufen wird.</span></p>
</li>
<li>
<p><strong>Ungest&ouml;rt!</strong><span> Setz Dich alleine in einen ruhigen Raum und sorge daf&uuml;r, dass Du nicht gest&ouml;rt wirst.</span></p>
</li>
<li>
<p><strong>Alleine!</strong><span> Bitte beantworte die Fragen selbst&auml;ndig. Denn es geht ja darum, dass Du selbst bereit bist, Foodsaver zu werden.</span></p>
</li>
<li>
<p><strong>Unterst&uuml;tzung</strong><span><strong>!</strong> Du kannst dir gerne jemand als Unterst&uuml;tzung holen, um zum Beispiel die Fragen und Antworten richtig zu verstehen oder um zu helfen, wenn Du nerv&ouml;s/hektisch/zu schnell wirst.</span></p>
</li>
<li>
<p><strong>Spicken ist erlaubt!</strong><span> Du darfst das Wiki und alle deine Notizen verwenden.</span></p>
</li>
</ul>
<p></p>
<p><span><strong>So l&auml;uft das Quiz ab</strong>:</span></p>
<ul>
<li>
<p><span>Das Quiz besteht aus Multiple-Choice-Fragen, die zuf&auml;llig aus einem gro&szlig;en Pool von Fragen ausgew&auml;hlt werden.</span></p>
</li>
<li>
<p><span>Die meisten Fragen beschreiben eine konkrete Situation. Du sollst dann </span><strong>jede Antwort ausw&auml;hlen</strong><span>, die eine m&ouml;gliche, akzeptable Vorgehensweise beschreibt. (&Uuml;brigens: alle Situationen sind echt und tats&auml;chlich schon so vorgekommen.)</span></p>
</li>
<li>
<p><span>Dabei k&ouml;nnen auch </span><strong>mehrere, alle oder keine </strong><span>Antwort richtig sein.</span></p>
</li>
<li>
<p><span>Nimm dir viel Zeit! Lies </span><strong>sorgf&auml;ltig und konzentriert </strong><span>alle Fragen und m&ouml;glichen Antworten und denke gut dar&uuml;ber nach!</span></p>
</li>
<li>
<p><strong>Runter scrollen</strong><span> nicht vergessen, damit Du auch wirklich alle Antworten siehst!</span></p>
</li>
<li>
<p><span>Nach jeder Frage bekommst Du eine Auswertung mit Erkl&auml;rungen zu den jeweiligen Antworten. Das kann dir helfen zu verstehen, warum eine Antwort richtig oder falsch ist. Lies dir bitte die Erkl&auml;rungen sorgf&auml;ltig durch und hinterlasse einen Kommentar, wenn etwas nicht verst&auml;ndlich, unlogisch oder falsch vorkommt. (Leider k&ouml;nnen wir auf die Kommentare nicht antworten, da wir aus Datenschutzgr&uuml;nden keine Kontaktdaten erhalten. Aber wir werten die Kommentare regelm&auml;&szlig;ig aus.)</span></p>
</li>
<li>
<p><span>Am Ende bekommst Du direkt mitgeteilt, ob Du bestanden hast und welche Fehler Du gemacht hast. Du kannst dann nochmal in Ruhe alle Fragen, Antworten und Begr&uuml;ndungen dazu durchlesen. Au&szlig;erdem kannst Du zu jeder Frage einen Kommentar hinterlassen.</span></p>
</li>
</ul>
<p></p>
<p><span>Jetzt w&uuml;nschen wir dir gute Gedanken und viel Erfolg beim Quiz!</span></p>', 'last_mod' => '2019-11-09 11:27:15'],
			['id' => '19', 'name' => 'quiz-failed-fs-try1', 'title' => 'Diesmal hat es leider nicht geklappt', 'body' => '<p><span>Aber kein Grund zur Sorge: Das war ja erst dein erster Versuch. Lies dir hier nochmal in Ruhe die Fragen und die dazugeh&ouml;rigen Antworten durch, damit es beim n&auml;chsten Mal besser klappt. Vielleicht hilft es dir, wenn du nochmal in die </span><a href="https://wiki.foodsharing.de/Quiz" target="_blank" style="text-decoration: none;"><span>Artikel im Wiki</span></a><span> schaust.</span></p>
<p></p>
<p><span>Gern kannst du ein Problem auch mit einer/einem Botschafter*in an deinem Wohnort (oder in der N&auml;he) besprechen. Du findest ihre Emailadresse auf der foodsharing-Seite: in der Men&uuml;-Leiste rechts das Brief-Symbol f&uuml;hrt dich zum richtigen Land (Deutschland, &Ouml;sterreich, ...), und dort steht dann die Liste aller Bezirke.</span></p>', 'last_mod' => '2019-11-09 11:39:26'],
			['id' => '20', 'name' => 'quiz-failed-fs-try2', 'title' => 'Diesmal hat es leider nicht geklappt', 'body' => '<p><span>Das sind leider zum zweiten Mal mehr Fehlerpunkte, als zum Bestehen erlaubt sind. Lies Dir hier noch mal in Ruhe die Fragen und die dazugeh&ouml;rigen Antworten durch, damit es beim n&auml;chsten Mal besser klappt. Vielleicht solltest du vor deinem 3. Versuch die </span><a href="https://wiki.foodsharing.de/Quiz" target="_blank" style="text-decoration: none;"><span>Artikel im Wiki</span></a><span> nochmal genauer lesen.</span></p>
<p></p>
<p><span>Gern kannst du ein Problem auch mit einer/einem Botschafter*in an deinem Wohnort (oder in der N&auml;he) besprechen. Du findest ihre Emailadresse auf der foodsharing-Seite: in der Men&uuml;-Leiste rechts das Brief-Symbol f&uuml;hrt dich zum richtigen Land (Deutschland, &Ouml;sterreich, ...), und dort steht dann die Liste aller Bezirke.</span></p>', 'last_mod' => '2019-11-09 11:40:46'],
			['id' => '21', 'name' => 'quiz-failed-fs-try3', 'title' => 'Diesmal hat es leider nicht geklappt', 'body' => '<p><span>Leider hast du erneut zu viele Fehlerpunkte. Damit m&ouml;glichst viele Lebensmittel gerettet werden k&ouml;nnen, ist Zuverl&auml;ssigkeit, sichere Beherrschung der Abholungen und professionelles Auftreten bei den Betrieben und im Team unverzichtbar.</span></p>
<p><span>Mit den Antworten, die du gegeben hast, vermittelst du das zum jetzigen Zeitpunkt leider nicht.</span></p>
<p><span>Daher bekommst du einen Monat Lernpause. Danach kannst du das Quiz erneut ablegen.</span></p>
<p></p>
<p><span>Gern kannst du ein Problem auch mit einer/einem Botschafter*in an deinem Wohnort (oder in der N&auml;he) besprechen. Du findest ihre Emailadresse auf der foodsharing-Seite: in der Men&uuml;-Leiste rechts das Brief-Symbol f&uuml;hrt dich zum richtigen Land (Deutschland, &Ouml;sterreich, ...), und dort steht dann die Liste aller Bezirke.</span></p>', 'last_mod' => '2019-11-09 11:41:02'],

			['id' => '28', 'name' => 'datenschutz', 'title' => 'Datenschutzerklärung', 'body' => '<h3><span>Datenschutzerkl&auml;rung foodsharing</span></h3>
<p></p>
<h4><span>1. Name und Kontaktdaten des f&uuml;r die Verarbeitung Verantwortlichen sowie des betrieblichen Datenschutzbeauftragten</span></h4>
<p><span>Diese Datenschutzinformation gilt f&uuml;r die Datenverarbeitung durch</span></p>
<p><span>foodsharing e. V.</span><span><br /></span>Neven-DuMont-Str. 14<span><br /></span><span>50667 K&ouml;ln</span></p>
<p><span>und f&uuml;r folgende Internetseiten (inkl. Subdomains):</span></p>
<p><span>foodsharing.de</span><span><br /></span><span>foodsharing.at</span><span><br /></span><span>foodsharingschweiz.ch</span><span><br /></span><span>foodsharing.network</span></p>
<p><span>Der betriebliche Datenschutzbeauftragte ist unter der o. g. Anschrift, z. Hd. Abteilung Datenschutz, bzw. per E-Mail unter datenschutz@foodsharing.de erreichbar.</span></p>
<h4><span>2. Erhebung und Speicherung personenbezogener Daten sowie Art und Zweck von deren Verwendung</span></h4>
<h5><span>a) Beim Besuch der Website</span></h5>
<p><span>Beim Aufruf der o. g. Websites werden durch den auf deinem Endger&auml;t zum Einsatz kommenden Browser automatisch Informationen an den Server unserer Website gesendet. Diese Informationen werden tempor&auml;r in einem sog. Logfile gespeichert. Folgende Informationen werden dabei ohne dein Zutun erfasst und bis zur automatisierten L&ouml;schung gespeichert:</span></p>
<ul>
<li>
<p><span>IP-Adresse des anfragenden Rechners,</span></p>
</li>
<li>
<p><span>Datum und Uhrzeit des Zugriffs,</span></p>
</li>
<li>
<p><span>Name und URL der abgerufenen Datei,</span></p>
</li>
<li>
<p><span>Website, von der aus der Zugriff erfolgt (Referrer-URL),</span></p>
</li>
<li>
<p><span>verwendeter Browser und ggf. das Betriebssystem deines Rechners sowie</span></p>
</li>
<li>
<p><span>der Name deines Zugangsanbieters.</span></p>
</li>
</ul>
<p><span>Die genannten Daten werden durch uns zu folgenden Zwecken verarbeitet:</span></p>
<ul>
<li>
<p><span>Gew&auml;hrleistung eines reibungslosen Verbindungsaufbaus der Website,</span></p>
</li>
<li>
<p><span>Gew&auml;hrleistung einer komfortablen Nutzung unserer Website,</span></p>
</li>
<li>
<p><span>Auswertung der Systemsicherheit und -stabilit&auml;t sowie</span></p>
</li>
<li>
<p><span>zu weiteren administrativen Zwecken.</span></p>
</li>
</ul>
<p><span>Die Rechtsgrundlage f&uuml;r die Datenverarbeitung ist Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;f DSGVO. Unser berechtigtes Interesse folgt aus oben aufgelisteten Zwecken zur Datenerhebung. In keinem Fall verwenden wir die erhobenen Daten zu dem Zweck, R&uuml;ckschl&uuml;sse auf deine Person zu ziehen.</span></p>
<p><span>Dar&uuml;ber hinaus setzen wir beim Besuch unserer Website Cookies sowie Analysedienste ein. N&auml;here Erl&auml;uterungen dazu erh&auml;ltst du unter den Ziffern&nbsp;4 und 5 dieser Datenschutzerkl&auml;rung.</span></p>
<h5><span>b) Bei Anmeldung f&uuml;r unseren Newsletter</span></h5>
<p><span>Sofern du nach Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;a DSGVO ausdr&uuml;cklich eingewilligt hast, verwenden wir deine E-Mail-Adresse daf&uuml;r, dir regelm&auml;&szlig;ig unseren Newsletter zu &uuml;bersenden. F&uuml;r den Empfang des Newsletters ist die Angabe einer E-Mail-Adresse ausreichend.</span></p>
<p><span>Die Abmeldung ist jederzeit m&ouml;glich, zum Beispiel &uuml;ber einen Link am Ende eines jeden Newsletters. Alternativ kannst du deinen Abmeldewunsch gerne auch jederzeit per E-Mail an </span><span>info@foodsharing.de</span><span> senden. Bist du bei foodsharing angemeldet, kannst du den Newsletterbezug auch in deinem Profil unter Einstellungen&rarr;Benachrichtigungen ausschalten.</span></p>
<h5><span>c) Bei Nutzung unseres Kontaktformulars oder anderer Kontaktm&ouml;glichkeiten</span></h5>
<p><span>Bei Fragen jeglicher Art bieten wir dir die M&ouml;glichkeit, mit uns &uuml;ber ein auf der Website bereitgestelltes Formular Kontakt aufzunehmen. Dabei ist die Angabe einer g&uuml;ltigen E-Mail-Adresse erforderlich, damit wir wissen, von wem die Anfrage stammt und um diese beantworten zu k&ouml;nnen. Weitere Angaben k&ouml;nnen freiwillig get&auml;tigt werden.</span></p>
<p><span>Die Datenverarbeitung zum Zwecke der Kontaktaufnahme mit uns erfolgt nach Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;a DSGVO auf Grundlage deiner freiwillig erteilten Einwilligung.</span></p>
<p><span>Die f&uuml;r die Benutzung des Kontaktformulars von uns erhobenen personenbezogenen Daten werden nach Erledigung der von dir gestellten Anfrage automatisch gel&ouml;scht.</span></p>
<p><span>Nimmst du mit uns durch andere genannte Kontaktm&ouml;glichkeiten, bspw. per E-Mail, Kontakt auf, werden deine Angaben gespeichert, damit auf diese zur Bearbeitung und Beantwortung deiner Anfrage zur&uuml;ckgegriffen werden kann. F&uuml;r die Weitergabe dieser Daten gelten die in Ziffer&nbsp;3 getroffenen Ausf&uuml;hrungen.</span></p>
<h5><span>d) Bei der Anmeldung als Foodsharer bzw. Foodsaver</span></h5>
<p><span>Auf unserer Plattform werden Abholung und Weitergabe &uuml;bersch&uuml;ssiger Lebensmittel koordiniert. Hierzu ist die Erstellung eines Benutzeraccounts und die Anmeldung mit diesem erforderlich. Aus lebensmittelrechtlicher Sicht m&uuml;ssen Personen, die Lebensmittel in Verkehr bringen, identifizierbar sein, f&uuml;r den Fall, dass haftungsrechtliche Fragen im Umgang mit den Lebensmitteln entstehen. Ebenso muss der Weg, den bestimmte Lebensmittel genommen haben, nach Forderung der beh&ouml;rdlichen Lebensmittel&uuml;berwachung l&uuml;ckenlos r&uuml;ckzuverfolgen sein. Um Lebensmittel &uuml;ber foodsharing.de retten oder anbieten zu k&ouml;nnen, ist es daher</span><span> gem. Art. 6 Abs. 1 S. 1 lit. c DSGVO</span><span> zwingend erforderlich, dass du dich mit deinem vollst&auml;ndigen Namen, deinem derzeitigen Wohnsitz und deinem korrekten Geburtsdatum anmeldest sowie Informationen bereitstellst, wie du unmittelbar erreicht werden kannst, z.&nbsp;B. deine Telefonnummer und eine g&uuml;ltige E-Mail-Adresse. Du verpflichtest dich, diese Informationen aktuell zu halten. Die Angabe weiterer Informationen ist freiwillig.</span></p>
<p><span>F&uuml;r die Nutzung der foodsharing-Plattform ist ferner eine r&auml;umliche Zuordnung n&ouml;tig, anhand derer bspw. bestimmt wird, welche Bezirke f&uuml;r dich zust&auml;ndig sind (vgl. Unterpunkt p), welche kooperierenden Betriebe sich in deiner N&auml;he befinden (vgl. Unterpunkt f) oder f&uuml;r welchen Bereich von dir angebotene Essensk&ouml;rbe gelten und wo sie abzuholen sind (vgl. Unterpunkt e). Hierzu werden anhand deiner angegebenen Anschrift mit einem Kartendienst (derzeit photon.komoot.de) die zugeh&ouml;rigen Geokoordinaten (geographische Breite und geographische L&auml;nge) ermittelt und in deinem Profil gespeichert. </span></p>
<p><span>Dazu steht ein Eingabefeld zur Verf&uuml;gung, in das du deine Anschrift eingeben kannst, die dann in der Karte gesucht wird. Dabei werden nur die eingegebenen Suchbegriffe (das hei&szlig;t i. d. R. Stra&szlig;e, Hausnummer, ggf. PLZ und Ort) an den Kartendienst &uuml;bermittelt, ohne jeglichen Personenbezug (d. h. wir &uuml;bermitteln systemseitig weder deinen Namen, noch eindeutige Benutzerkennungen und auch keine anderen Informationen &uuml;ber dich, solange du sie nicht selbst ins Suchfeld eingibst). Der Kartendienst liefert dann die ermittelten Geokoordinaten zur&uuml;ck, die wir in deinem Benutzerprofil zu o. g. Zwecken speichern. Die hinterlegten Koordinaten &auml;ndern sich nur, wenn du deine Adresse &auml;nderst und auf diese Weise mit dem Kartendienst eine neue Geokodierung vornimmst. Eine Bestimmung deines Aufenthaltsorts &uuml;ber von dir verwendete mobile Ger&auml;te (Geolokation) erfolgt derzeit nicht.</span></p>
<p><span>Die verwendete E-Mail-Adresse dient ferner als eindeutiges Identifikationsmerkmal und als Anmeldekennung. Mit der Anmeldung willigst du ein, dass dir vom System wichtige Nachrichten und Neuerungen aus </span><span>deinen Bezirken und Arbeitsgruppen</span><span> per E-Mail &uuml;bermittelt werden k&ouml;nnen</span><span>,</span><span> sowie dass dich deine zust&auml;ndigen Botschafter*innen oder Orgamenschen anschreiben k&ouml;nnen, wenn es irgendwo Kl&auml;rungsbedarf gibt</span><span>. Wor&uuml;ber du per E-Mail informiert wirst, kannst du in deinem Profil unter Einstellungen&rarr;Benachrichtigungen steuern. </span><span>Im Falle von Fragen oder zur Kommunikation mit Dir im Zusammenhang mit </span><span>f</span><span>oodsharing haben wir bzw. die Botschafter*innen der Bezirke auch ein berechtigtes Interesse unsererseits zur Verarbeitung der hier genannten personenbezogenen Daten (Art. 6 Abs. 1 S. 1 lit. f DSGVO).</span></p>
<p><span>Dein Geburtsdatum, deine E-Mail-Adresse sowie die Wohnortdaten</span><span>, die du bei der Anmeldung bzw. unter Einstellungen angibst, sind </span><span>zun&auml;chst nur intern</span><span> f&uuml;r globale Administrator*innen (sog. &bdquo;Orgamenschen&ldquo;) und f&uuml;r die verantwortlichen Botschafter*innen der Bezirke einsehbar, f&uuml;r die du dich angemeldet hast. Diese Personen sind dem Datengeheimnis verpflichtet, haben also deine personenbezogenen Daten vertraulich zu behandeln und nur f&uuml;r foodsharingrelevante Zwecke zu verwenden. Eine Weitergabe dieser Daten an Dritte erfolgt nur insofern</span><span> in &Uuml;bereinstimmung mit Art. 6 Abs. 1 S. 1 lit. c, e DSGVO</span><span> eine gesetzliche Verpflichtung dazu besteht (bspw. an Beh&ouml;rden der Lebensmittel&uuml;berwachung im Falle von Anschl&auml;gen oder Epidemien) sowie in den anderen in Ziffer&nbsp;3 dieser Datenschutzerkl&auml;rung genannten Ausnahmen.</span></p>
<h5><span>e) Anbieten von Essensk&ouml;rben</span></h5>
<p><span>Als Foodsharer oder Foodsaver kannst du sog. &bdquo;Essensk&ouml;rbe&ldquo; anlegen, mit denen du eigene Lebensmittel verschenken kannst, die du nicht mehr ben&ouml;tigst. </span><span>Essensk&ouml;rbe (konkret Beschreibungstext, Einstell- und G&uuml;ltigkeitsdatum, ggf. Foto und die angegebene Position des Essenskorbs, standardm&auml;&szlig;ig die in deinem Profil hinterlegte Adresse) sind grunds&auml;tzlich &ouml;ffentlich f&uuml;r jedermann*frau im Internet einsehbar und k&ouml;nnen damit theoretisch auch von Dritten verwendet werden, um auf das Angebot aufmerksam zu machen. Dein Vorname, die angegebene Kontaktm&ouml;glichkeit und der Link zu deinem internen Profil hingegen ist nur plattformintern f&uuml;r angemeldete Nutzer*innen sichtbar. Mit dem Einstellen eines Essenskorbs willigst du gem&auml;&szlig; Art. 6 Abs. 1 S. 1 lit. a DSGVO in die Verarbeitung der entsprechenden Daten ein und gestattest foodsharing die Nutzung der &uuml;bermittelten Daten und Fotos zu diesem Zweck. Ferner gestattest du, dass deine angegebene Adresse als Position des Essenskorbs mit einer Nadel auf einer Karte angezeigt wird. Dies ist notwendig, damit andere Menschen sehen k&ouml;nnen, wo der Essenskorb abzuholen ist. Ebenfalls willigst du ein, dass man dich auf dem ausgew&auml;hlten Weg kontaktieren kann, um die Abholung des Essenskorbs zu arrangieren. Sobald dein Essenskorb abgeholt wurde oder du ihn entfernst, wird er nicht mehr angezeigt, zu Nachweiszwecken wird jedoch intern gespeichert, wer welchen Essenskorb angefragt hat und nach einer Abholung, ob diese erfolgreich war. Dies ist ebenfalls aus den in Unterpunkt&nbsp;d genannten Gr&uuml;nden und damit im Sinne von Art. 6 Abs. 1 S. 1 lit. c DSGVO erforderlich.</span></p>
<h5><span>f) Abholung von Lebensmitteln bei kooperierenden Betrieben</span></h5>
<p><span>Wenn du dich entschlie&szlig;t, Foodsaver zu werden, kannst du dich f&uuml;r anstehende Abholungen bei kooperierenden Betrieben eintragen und dann mit anderen Foodsavern zusammen &uuml;bersch&uuml;ssige Lebensmittel direkt bei den kooperierenden Betrieben abholen. In diesem Zusammenhang wird erfasst, bei welchen Betrieben du jeweils wann eingetragen bist bzw. abholen wirst. Dies ist f&uuml;r alle Foodsaver im jeweiligen Team sichtbar und dient der Planung des Personaleinsatzes f&uuml;r anstehende Abholungen und der Abstimmung der jeweils Abholenden untereinander.</span></p>
<p><span>Die Abholdaten werden ebenfalls</span><span> gem. Art. 6 Abs. 1 S. 1 lit. c DSGVO</span><span> r&uuml;ckwirkend gespeichert und gem&auml;&szlig; der geltenden gesetzlichen Vorgaben f&uuml;r mindestens f&uuml;nf Jahre aufbewahrt. R&uuml;ckblickend sehen, wann du in einem bestimmten Betrieb Lebensmittel abgeholt hast, k&ouml;nnen nur die f&uuml;r den jeweiligen Betrieb verantwortlichen Foodsaver, die Botschafter*innen des zugeh&ouml;rigen Bezirks und die Orgamenschen. Dies ist erforderlich, um Vorg&auml;nge im Nachhinein aufkl&auml;ren zu k&ouml;nnen oder um die bei einer konkreten Abholung beteiligten Foodsaver bei Bekanntwerden von R&uuml;ckrufaktionen oder etwaig aufgekommenen Beschwerden kontaktieren zu k&ouml;nnen. </span><span>Ebenfalls k&ouml;nnen Informationen zu vergangenen Abholungen (Anzahl, H&auml;ufigkeit) gem. Art. 6 Abs. 1 S. 1 lit. f DSGVO von den vorgenannten Personen dazu genutzt werden, k&uuml;nftige Abholungen &ndash; ggf. auch solche bei anderen Betrieben, f&uuml;r die sie oder Dritte zust&auml;ndig sind &ndash; gerechter vergeben zu k&ouml;nnen. Konkrete Details zu solchen Regelungen sind den Beschreibungstexten der jeweiligen Betriebe zu entnehmen bzw. von den Betriebsverantwortlichen zu erfragen. </span></p>
<p><span>Bei l&auml;ngerer Inaktivit&auml;t k&ouml;nnen dich die zust&auml;ndigen Betriebsverantwortlichen ggf. vor&uuml;bergehend aus dem Team nehmen. Bei bereits vollen Teams besteht kein Anspruch, (wieder-)aufgenommen zu werden. Solltest du l&auml;nger </span><span>verreisen</span><span> oder anderweitig inaktiv sein, sprich am besten mit den zust&auml;ndigen Betriebsverantwortlichen und Botschafter*innen.</span></p>
<p><span>Um die Kontaktaufnahme zu erleichtern, k&ouml;nnen andere Foodsaver in deinen Teams deinen Namen, dein Profilfoto und deine Telefonnummer sehen, sowie dich direkt per PN kontaktieren. Dies ist erforderlich, um einen reibungslosen Ablauf der Abholungen zu gew&auml;hrleisten. (Andere Foodsaver k&ouml;nnen jedoch bspw. nicht deine Wohnanschrift, deine E-Mail-Adresse oder dein Geburtsdatum sehen, solange sie nicht als u. a. mit der Nutzerverwaltung betraute Botschafter*innen oder Orgamenschen entsprechende administrative Rechte im zugeh&ouml;rigen Bezirk haben.)</span></p>
<p><span>Als Foodsaver ben&ouml;tigst du ebenfalls zwingend ein Portraitfoto, auf dem du erkennbar bist. Das dient</span><span> gem. Art. 6 Abs. 1 S. 1 lit. f DSGVO</span><span> zum einen der Erkennbarkeit durch andere bei der Abholung beteiligte Nutzer*innen sowie ggf. durch Mitarbeiter*innen des kooperierenden Betriebs und in dem Zusammenhang der Optimierung des Betriebsablaufs und dem Verhindern von Missbrauch durch Gesichtsabgleich</span><span>, worin die vorgenannten Personen ein berechtigtes Interesse haben</span><span>. &Uuml;berdies wird das Foto benutzt, um einen foodsharing</span><span>-A</span><span>usweis f&uuml;r dich zu erstellen, der dich zu Abholungen legitimiert und auf dem du ebenfalls erkennbar sein musst. Dieser Ausweis enth&auml;lt neben deinem Foto noch deinen vollst&auml;ndigen Namen, deine eindeutige foodsharing-ID, G&uuml;ltigkeitsdaten sowie einen QR-Code. </span></p>
<p><span>QR-Codes sind Grafikmuster, die mit den Strichcodes auf Produktverpackungen verwandt sind. In QR-Codes lassen sich Internetadressen oder andere &nbsp;Informationen einbetten und mit entsprechenden Ger&auml;ten oder entsprechenden Smartphone-Apps maschinell auslesen. Der QR-Code auf deinem Ausweis enth&auml;lt den Link auf dein foodsharing-Profil (siehe auch Unterpunkt g). Dies dient </span><span>uns</span><span> der schnellen Kontrolle, ob der vorgelegte Ausweis g&uuml;ltig ist und deine Legitimation nach wie vor gegeben ist. Weitere Daten speichern wir nicht in den QR-Codes.</span></p>
<p><span>Damit die kooperierenden Betriebe im Zweifelsfall in der Lage sind zu pr&uuml;fen, welche Foodsaver dort abholberechtigt sind, gestattest du uns gem. Art. 6 Abs. 1 S. 1 lit. a DSGVO, ihnen auf Anforderung eine Liste mit den Foodsharing-IDs und/oder den Namen der abholberechtigten Foodsaver im jeweiligen Team zu &uuml;bermitteln und ihnen etwaige &Auml;nderungen daran mitzuteilen, sofern dies im Einzelfall nicht ohnehin schon gem&auml;&szlig; Art. 6 Abs. 1 S. 1 lit. b bzw. c DSGVO zul&auml;ssig ist.&nbsp;</span></p>
<p><span>Weiterhin k&ouml;nnen Abholdaten, also Informationen, welche Foodsaver am jeweiligen Tag f&uuml;r die Abholung eingetragen waren, auf Anfrage an den Betrieb &uuml;bermittelt werden, wenn dieser ein berechtigtes Interesse nachweisen kann.</span></p>
<p><span>Um nachvollziehen zu k&ouml;nnen, wer sich oder ggf. andere f&uuml;r anstehende Abholungen ein- oder austr&auml;gt, Foodsaver in Teams zuf&uuml;gt oder aus diesen entfernt, Termine oder Beschreibungen &auml;ndert, werden diese Vorg&auml;nge in der Datenbank protokolliert und k&ouml;nnen zu einem sp&auml;teren Zeitpunkt von den Betriebsverantwortlichen und zust&auml;ndigen BOTs eingesehen werden.</span></p>
<h5><span>g) Benutzerprofil</span></h5>
<p><span>Jede*r angemeldete Nutzer*in hat eine Profilseite. Deine Profilseite k&ouml;nnen nur am System angemeldete Nutzer*innen sehen. Dort befindet sich zum Zweck der Erkennbarkeit ebenfalls dein Foto, dein Vorname, </span><span>dein Stammbezirk,</span><span> eine Auflistung </span><span>weiterer</span><span> Bezirke, in denen du angemeldet bist, sowie eine M&ouml;glichkeit, dir eine Nachricht (PN) zu hinterlassen. </span></p>
<p><span>Ebenfalls wird auf der Profilseite angezeigt, wie viele Vertrauensbananen du erhalten hast (vgl. Unterpunkt h), wie viele Forenbeitr&auml;ge du verfasst hast, </span><span>bei wie vielen Betrieben du abholst und bei welchen, </span><span>wie oft du f&uuml;r Abholungen eingetragen warst und wie viele kg Lebensmittel du dabei basierend auf der mittleren Sch&auml;tzung f&uuml;r die jeweiligen Betriebe in Summe gerettet hast. Dies dient anderen Nutzer*innen dazu, einen groben Ersteindruck &nbsp;zu erhalten, wie routiniert und engagiert du als Foodsaver bist und kann damit ein h&ouml;heres Vertrauen schaffen. </span><span>Ebenfalls wird anderen angemeldeten Foodsavern auf deinem Profil <span><span>ggf. deine interne E-Mail-Adresse gem&auml;&szlig; Unterpunkt &bdquo;E-Mail-Postf&auml;cher&ldquo; und&nbsp;</span></span>ggf. deine sog. Abholquote angezeigt, die sich aus den tats&auml;chlich erfolgten Abholungen und den Meldungen &uuml;ber vers&auml;umte Abholungen errechnet. Dies dient der Einsch&auml;tzung deiner Abholzuverl&auml;ssigkeit. An deiner Erkennbarkeit und der Darstellung deines Engagements besteht ein berechtigtes Interesses anderer Nutzer*innen. Rechtsgrundlage der Verarbeitung ist damit Art. 6 Abs. 1 S. 1 lit. f DSGVO.</span></p>
<p><span>Botschafter*innen deiner Bezirke sowie &uuml;bergreifende Orgamenschen k&ouml;nnen dar&uuml;ber hinaus in deinem Benutzerprofil noch dein Registrierungsdatum sehen, den Zeitpunkt deiner Verifikation und der Ausweiserstellung, das Datum der letzten Anmeldung, bei welchen Betrieben du abholst sowie deine vollst&auml;ndigen Daten gem&auml;&szlig; Unterpunkt&nbsp;d einsehen und im Bedarfsfall bearbeiten. Dies ist zur dezentralen Verwaltung der Bezirke unumg&auml;nglich. </span><span>Mithin besteht ein berechtigtes Interesse der Botschafter*innen hieran gem&auml;&szlig; Art. 6 Abs. 1 lit. f) DSGVO.</span></p>
<p><span>Wenn du zeitweilig nicht einsatzf&auml;hig sein solltest, kannst du das den anderen Foodsavern in deinem Profil mittels einer sog. Schlafm&uuml;tze anzeigen. Sie sehen dann, dass sie vorrangig andere Menschen ansprechen m&uuml;ssen, wenn sie Unterst&uuml;tzung ben&ouml;tigen sollten. Du kannst dich in deinen Profileinstellungen wahlweise f&uuml;r einen bestimmten vordefinierten Zeitraum oder auf unbestimmte Zeit in den Schlafm&uuml;tzenmodus versetzen. Beachte bitte, dass s&auml;mtliche in dem Zusammenhang vorgenommene Angaben auch f&uuml;r alle angemeldeten Nutzer*innen auf deiner Profilseite zu sehen sein werden.</span></p>
<h5><span>h) </span><span>Meldungen zu Regelverletzungen</span><span> und Vertrauensbananen</span></h5>
<p><span>F&uuml;r eine zuverl&auml;ssige Abwicklung ist die Einhaltung bestimmter grundlegender Verhaltensregeln unumg&auml;nglich. Sollte es zu Verletzungen dieser Verhaltensregeln kommen, m&uuml;ssen die verantwortlichen Personen davon Kenntnis erlangen um ggf. einlenken zu k&ouml;nnen bzw. Gespr&auml;che mit den betreffenden Foodsavern zu f&uuml;hren.</span><span> Diesbez&uuml;gliche Daten werden auf Grundlage von Art. 6 Abs. 1 S. 1 lit. f DSGVO verarbeitet.</span></p>
<p><span>Um dies abzubilden, nutzen wir als internes Feedback- und Bewertungssystem </span><span>Meldungen zu Regelverletzungen</span><span> und Vertrauensbananen. Erstere sind wichtig, um erkennen und erfassen zu k&ouml;nnen, ob es im Einzelfall zu Verletzungen der grunds&auml;tzlichen Verhaltensregeln gekommen ist und um diesen nachzugehen, zweiteres ist eine positive Auszeichnung zuverl&auml;ssiger und engagierter Nutzer*innen. </span></p>
<p><span>Der Inhalt von Meldungen zu Regelverletzungen ist vertraulich. Eingegangene Meldungen zu Regelverletzungen werden von der zentralen Meldegruppe (ZMG) an die jeweils zust&auml;ndige Bearbeitungsinstanz gem&auml;&szlig; </span><a href="https://wiki.foodsharing.de/Regelverletzungen_-_Konsequenzen_und_Bearbeitung" target="_blank"><span>https://wiki.foodsharing.de/Regelverletzungen_-_Konsequenzen_und_Bearbeitung</span></a><span> &uuml;bermittelt und k&ouml;nnen nur von den Botschafter*innen der Bezirke in denen du angemeldet bist eingesehen werden sowie von den mit der Bearbeitung vertrauten Meldungsgruppen bzw. Mediationsteams, die ihrerseits unterrichtet sind, dass es sich bei Meldungen zu Regelverletzungen um sensible Informationen </span><span>handelt,</span><span> und die verpflichtet sind, Inhalt und Vorhandensein solcher Meldungen entsprechend vertraulich zu behandeln. Nat&uuml;rlich steht dir aber auch ein Auskunftsrecht gem&auml;&szlig; Art. 15 DSGVO zu, sofern dies keine Datenschutzinteressen Dritter ber&uuml;hrt. Wende dich hierzu bitte an die zust&auml;ndige Meldungsgruppe. </span></p>
<p><span>Mit der Meldungsbearbeitung betraute Instanzen k&ouml;nnen zur Kl&auml;rung der Angelegenheit pers&ouml;nlich bzw. &uuml;ber alle von dir hinterlegten Kontaktinformationen (E-Mail, Direktnachricht, telefonisch, auf dem Postweg) Kontakt mit dir aufnehmen. Meldungen bzw. infolge dessen verh&auml;ngte Konsequenzen verfallen nach einer gewissen Zeitspanne ohne erneute Vorkommnisse. N&auml;heres dazu ist unter </span><a href="https://wiki.foodsharing.de/Regelverletzungen_-_Konsequenzen_und_Bearbeitung" target="_blank"><span>https://wiki.foodsharing.de/Regelverletzungen_-_Konsequenzen_und_Bearbeitung</span></a><span> beschrieben.</span></p>
<p><span>Die Vertrauensbananen, die du von anderen Nutzer*innen erhalten hast, sind zusammen mit einem frei formulierbaren Bewertungstext f&uuml;r alle angemeldeten Nutzer*innen auf deiner Profilseite zu sehen. Vertrauensbananen k&ouml;nnen im Allgemeinen nicht zur&uuml;ckgenommen und auch nicht von den beteiligten Nutzer*innen gel&ouml;scht werden. Solltest du Vertrauensbananen mit nachweislich beleidigendem, ehrverletzendem oder auf sonstige Weise rechtswidrigem Inhalt erhalten haben, kannst du dich allerdings an </span><span>den IT-Support</span><span> (it@foodsharing.network)</span><span> oder an datenschutz@foodsharing.de wenden und eine L&ouml;schung beantragen.</span></p>
<p><span>Sofern dar&uuml;ber hinaus eine rechtliche Verpflichtung besteht, sich hinsichtlich Regelverletzungen gegen&uuml;ber Beh&ouml;rden oder Dritten zu &auml;u&szlig;ern, ist Rechtsgrundlage f&uuml;r die Verarbeitung bzw. &Uuml;bermittlung deiner personenbezogenen Daten Art. 6 Abs. 1 1 S. 1 c DSGVO.</span></p>
<h5><span>i) Kommunikation in Foren und an elektronischen Pinnw&auml;nden</span></h5>
<p><span>Zur Online-Kommunikation untereinander in den Bezirken, in den Abholteams der Betriebe sowie in bestimmten Arbeitsgruppen (AGs) gibt es </span><span>interne Diskussionsforen sowie die M&ouml;glichkeit, zentrale Ank&uuml;ndigungen auf Pinnw&auml;nden zu hinterlegen. Nutzer*innen im selben Bezirk, Team oder AG k&ouml;nnen sehen, welche anderen Nutzer*innen der jeweiligen Gruppe angeh&ouml;ren. In den Foren bzw. auf den Pinnw&auml;nden</span><span> kannst du Beitr&auml;ge lesen und verfassen</span><span> sowie auf bestehende Beitr&auml;ge </span><span>u. U. </span><span>mit verschiedenen Emoji-Symbolen reagieren</span><span>. Beachte dabei jedoch, dass alle anderen Personen, die im jeweiligen Bereich angemeldet sind oder sich in Zukunft anmelden k&ouml;nnen, deine Forenbeitr&auml;ge </span><span>und Reaktionen </span><span>sowie deinen Vornamen, dein Profilfoto und den Link zu deinem Profil sehen k&ouml;nnen. Mit dem Absenden eines neuen Forenbeitrags bzw. einer Antwort auf andere Beitr&auml;ge willigst du dem ein</span><span> (Art. 6 Abs. 1 S. 1 lit. a DSGVO)</span><span>. Solltest du die L&ouml;schung oder Sperrung deines Nutzeraccounts beantragen, bleiben deine Forenbeitr&auml;ge </span><span>und Reaktionen </span><span>bestehen, aber werden als &bdquo;Beitrag von nicht mehr angemeldete*r Nutzer*in&ldquo; </span><span>o. &auml;. </span><span>angezeigt.</span></p>
<p><span>Vom Seitenbetreiber entsprechend autorisierte Personen k&ouml;nnen nach deren Ermessen Foren- oder Pinnwandbeitr&auml;ge ggf. auch ohne R&uuml;cksprache ausblenden oder l&ouml;schen, wenn diese nach deren Auffassung gegen geltendes Recht oder die guten Sitten versto&szlig;en oder den Betriebsablauf erheblich zu st&ouml;ren imstande sind.</span></p>
<h5><span>j) Direktnachrichten (PNs)</span></h5>
<p><span>Zur Kommunikation untereinander k&ouml;nnen &uuml;ber die foodsharing-Plattform direkte Nachrichten (PNs) an andere Nutzer*innen oder an Gruppen von Nutzer*innen verschickt werden. Du willigst ein, dass man dich in foodsharingbezogenen Angelegenheiten per PN kontaktieren darf. Standardm&auml;&szlig;ig bekommst du eingehende PNs per E-Mail an deine hinterlegte E-Mail-Adresse weitergeleitet, sofern du nicht eingeloggt bist. Dieses Verhalten kannst du jedoch in deinen Benachrichtigungseinstellungen &auml;ndern.</span></p>
<p><span>Direktnachrichten, die du per PN oder in einem Gruppenchat erh&auml;ltst, gelten als vertrauliche Kommunikation. Bevor du Inhalte aus deinem Nachrichtenverlauf bzw. aus Chats mit anderen an andere Personen weitergibst, musst du dich im Allgemeinen vergewissern, dass der*die Verfasser*in der urspr&uuml;nglichen Nachricht damit einverstanden ist</span><span> (Art. 6 Abs. 1 S. 1 lit. a DSGVO)</span><span>. </span><span>Wenn du von Nutzer*innen unverh&auml;ltnism&auml;&szlig;ige Nachrichten erhalten solltest, wende dich an deine zust&auml;ndigen Botschafter*innen.</span></p>
<h5><span>k) Veranstaltungen</span></h5>
<p><span>F&uuml;r verschiedene Events, Telefonkonferenzen oder Plena k&ouml;nnen im System Veranstaltungen angelegt werden, zu denen sich alle Foodsaver des jeweiligen Bezirks oder der jeweiligen Arbeitsgruppe einladen lassen. Wenn du eingeladen wurdest, kannst du wahlweise zu- oder absagen oder signalisieren, dass du nur vielleicht k&ouml;nnen wirst. Die Information, wer jeweils zugesagt hat oder &bdquo;vielleicht&ldquo; ausgew&auml;hlt hat, kann von allen anderen eingeladenen Personen bzw. von allen Personen im Bezirk bzw. der AG, auf den bzw. die sich die Einladung erstreckt, gesehen werden. Dies ist zur Planung der Veranstaltung sowie zur Kommunikation untereinander notwendig und sinnvoll. Wenn du nicht an der Veranstaltung teilnehmen willst, kannst du absagen und erscheinst dann nicht mehr in der Liste. Zu jeder Veranstaltung gibt es eine separate Pinnwand, auf der du Beitr&auml;ge schreiben kannst, die die anderen eingeladenen bzw. im jeweiligen Bezirk bzw. der jeweiligen AG sehen k&ouml;nnen. Dies dient ebenfalls der Abstimmung bzw. der Vorbereitung auf den jeweiligen Termin.</span></p>
<h5><span>l) Kartenansicht</span></h5>
<p><span>Zur Darstellung von Karten auf unserer Website werden Kartenkacheln, sog. Tiles, von einem Drittanbieter (derzeit Geoapify) bezogen. Dieser liefert die zur Darstellung des angeforderten Kartenausschnitts notwendigen Kacheln an deinen Browser bzw. die App aus. Wir &uuml;bertragen dabei keinerlei Nutzer- oder personenbezogene Daten an diesen Anbieter. Beim Anbieter fallen jedoch Verbindungsdaten an, er erh&auml;lt durch den direkten Abruf der Kartenkacheln also bspw. deine IP-Adresse und k&ouml;nnte in Abh&auml;ngigkeit davon, welche Kartenausschnitte du besonders h&auml;ufig anforderst, R&uuml;ckschl&uuml;sse darauf ziehen, in welcher Stadt oder Region du m&ouml;glicherweise wohnst &ndash; was allerdings bei jeder Kartenanwendung, die du im Internet nutzt, ebenfalls der Fall ist.</span></p>
<p><span>Wenn du im Suchfeld der jeweiligen Kartenansicht nach bestimmten Objekten suchst, werden deine Eingaben an einen Drittanbieter (derzeit photon.komoot.de) &uuml;bertragen, der die zu diesen Suchbegriffen gefundenen Ergebnisse und deren Geokoordinaten an die Anwendung zur&uuml;ckliefert. N&auml;heres hierzu ist im Unterpunkt &bdquo;Vervollst&auml;ndigung/Korrektur von Adresseingaben&ldquo; beschrieben.</span></p>
<p><span><span>Eine Bestimmung deines Aufenthaltsorts &uuml;ber die Ortungsfunktion der von dir verwendeten mobilen Ger&auml;te (Geolokation) erfolgt&nbsp;</span><span>nur mit deiner ausdr&uuml;cklichen Einwilligung und demnach im Einklang mit Art. 6 Abs. 1 S. 1 lit. a DSGVO.</span></span></p>
<p><span>Botschafter*innen und Orgamenschen k&ouml;nnen sich in einer Karte die Positionen der hinterlegten Wohnorte der Foodsaver in den Regionen anzeigen lassen, f&uuml;r die sie jeweils zust&auml;ndig sind. Dies dient insbesondere strategischen Erw&auml;gungen, bspw. im Zusammenhang, ob in einem bestimmten Stadtteil die Ansprache neuer Betriebe sinnvoll ist und personell abgedeckt werden kann oder eher nicht. Hierbei k&ouml;nnen Botschafter*innen bzw. Orgamenschen keine Daten von dir sehen, die sie nicht ohnehin gem&auml;&szlig; Unterpunkt&nbsp;g berechtigt sind zu sehen. Ferner sind sie gem&auml;&szlig; Unterpunkt&nbsp;d verpflichtet, auch diesbez&uuml;glich das Datengeheimnis zu wahren und erlangte Informationen nur nach Ma&szlig;gabe der Ziffern&nbsp;3 bzw. 5b weiterzugeben.</span></p>
<h5><span>m) &bdquo;Fair-Teiler&ldquo;</span></h5>
<p><span>Zur Abgabe geretteter Lebensmittel gibt es sog. &bdquo;Fair-Teiler&ldquo;, i.&nbsp;d.&nbsp;R. &ouml;ffentlich zug&auml;ngliche Abgabepunkte, f&uuml;r die die jeweiligen Betreiber*innen zust&auml;ndig sind. Zu jedem Fair-Teiler gibt es eine Pinnwand, auf der Informationen und Neuigkeiten ver&ouml;ffentlicht werden k&ouml;nnen. Beitr&auml;ge auf der Fair-Teiler-Pinnwand sind &ouml;ffentlich einsehbar. Wenn du dort etwas schreibst, taucht dein Vorname und eine verkleinerte Version deines Profilfotos bei deinem Beitrag auf und kann auch f&uuml;r nicht-angemeldete Benutzer*innen und ggf. f&uuml;r Suchmaschinen sichtbar sein.</span></p>
<p><span>Einige Fair-Teiler sind mobil ausgelegt, bspw. auf Lastenr&auml;dern. Zum Teil sind diese mobilen Fair-Teiler mit Ortungsvorrichtungen ausgestattet, die den derzeitigen Fair-Teiler-Standort in Echtzeit an die foodsharing-Seite oder an andere Plattformen &uuml;bermitteln, damit andere sehen k&ouml;nnen, wo sich der mobile Fair-Teiler im Moment befindet. Falls mobile Fair-Teiler Ortungsvorrichtungen eingebaut haben und du befugt sein solltest, die mobilen Fair-Teiler zu bewegen, musst du wenn dir Schl&uuml;ssel oder Zahlencode &uuml;bergeben werden auf den Sachverhalt hingewiesen werden, dass sich in dem mobilen Fair-Teiler eine Ortungsvorrichtung befindet.</span></p>
<p><span>Wir selbst nehmen keine Video&uuml;berwachung von Fair-Teilern vor. Sollten sich Fair-Teiler an Orten befinden, die von Dritten video&uuml;berwacht werden (bspw. von den Eigent&uuml;mern der Liegenschaft oder von entsprechenden Beh&ouml;rden), haben diese entsprechend der gesetzlichen Bestimmungen auf diesen Sachverhalt hinzuweisen.</span></p>
<h5><span>n) Quiz</span></h5>
<p><span>Um dich erfolgreich bei foodsharing einbringen zu k&ouml;nnen, ist einiges an theoretischem Wissen erforderlich, das wir in unserem foodsharing-eigenen Wiki vermitteln. Um Foodsaver, Betriebsverantwortliche*r oder Botschafter*in zu werden, musst du jeweils online ein kurzes Quiz bestehen, in dem deine Qualifikation gepr&uuml;ft wird. W&auml;hrend und nach dem Ausf&uuml;hren des Quizzes wird gespeichert, wann das Quiz durchgef&uuml;hrt wurde und welche Antworten gew&auml;hlt worden sind. Dies erm&ouml;glicht dir, das Quiz zu pausieren und zu einem sp&auml;teren Zeitpunkt fortzusetzen. Zudem hast du auch die M&ouml;glichkeit, zu einzelnen Fragen Kommentare zu hinterlassen, um die Qualit&auml;t der Quizfragen zu verbessern. Auf die Quizauswertung und die Kommentare haben nur Orgamenschen und Admins der Quiz-AG Zugriff. </span></p>
<h5><span>o) E-Mail-Postf&auml;cher</span></h5>
<p><span>Wenn du bei foodsharing bestimmte Aufgaben &uuml;bernimmst, bspw. als Betriebsverantwortlich*e oder Botschafter*in, bekommst du vom System automatisch E-Mail-Adressen zugewiesen, um n&ouml;tige Au&szlig;enkommunikation f&uuml;hren zu k&ouml;nnen. Hier ist zu unterscheiden zwischen deiner pers&ouml;nlichen E-Mail-Adresse (i.&nbsp;d.&nbsp;R. der Form </span><span>erster Buchstabe des &nbsp;vornamens</span><span>.nachname@</span><span>foodsharing.network</span><span>) und funktionsbezogenen E-Mail-Adressen f&uuml;r </span><span>Arbeitsgruppen</span><span> oder Bezirke (z.&nbsp;B. musterstadt@</span><span>foodsharing.network</span><span>). </span><span>Vergebene</span><span> E-Mail-Adressen sollen nur f&uuml;r &bdquo;dienstliche&ldquo; foodsharingbezogene und nicht f&uuml;r private Kommunikation genutzt werden. Insbesondere auf funktionsbezogene E-Mail-Postf&auml;cher und dar&uuml;ber gef&uuml;hrte Kommunikation kann bei ge&auml;nderten Aufgabenverteilungen eben in Zukunft auch von anderen Nutzer*innen zugegriffen werden. Auch setzen wir automatische Virenscanner und Spamfilter ein, so dass wir rein rechtlich gezwungen sind uns vorbehalten zu m&uuml;ssen, sozusagen als Dienstherr Zugriff auf deine von uns bereitgestellten dienstlichen E-Mail-Postf&auml;cher bzw. den dar&uuml;ber abgewickelten Mailverkehr zu nehmen. Schon deshalb eignen sich die automatisch vergebenen E-Mail-Adressen nicht f&uuml;r vertrauliche und/oder private Kommunikation.</span></p>
<p><span>E-Mails, die du an bezirks- oder funktionsbezogene Sammeladressen schickst (bspw. also an bezirk@foodsharing.network oder an arbeitsgruppe@foodsharing.network), landen im Sammelpostfach des jeweiligen Bezirks bzw. der jeweiligen Arbeitsgruppe, auf das die Botschafter*innen des jeweiligen Bezirks bzw. die Administrator*innen der jeweiligen Arbeitsgruppe zugreifen k&ouml;nnen. Du willigst ein, dass E-Mails dorthin sofern das zur Bearbeitung notwendig ist auch an andere Arbeitsgruppen oder Bezirke weitergeleitet werden d&uuml;rfen. Wir gew&auml;hrleisten, dass alle Empf&auml;nger*innen von E-Mails in unserem System entsprechend der Datenschutzgrunds&auml;tze unterwiesen sind und die Informationen vertraulich behandeln, sie also insbesondere auch bei Weiterleitung an die entsprechenden internen Stellen, die f&uuml;r die Bearbeitung zust&auml;ndig sind, nur an entsprechend befugte Personen weiterleiten. Nachrichten, die du per E-Mail an pers&ouml;nliche E-Mail-Adressen oder systemintern per PN sendest, sind von den Empf&auml;nger*innen grunds&auml;tzlich vertraulich zu behandeln und d&uuml;rfen nur mit deiner ausdr&uuml;cklichen Einwilligung Dritten weitergeleitet werden (siehe auch Unterabschnitt j).</span></p>
<h5><span>p) Zuordnung zu Bezirken</span></h5>
<p><span>Du kannst als Foodsaver selbst w&auml;hlen, in welchen Bezirken du gerne t&auml;tig sein m&ouml;chtest. Da die Initiativen auf Bezirksebene jedoch unterschiedlich organisiert sind, besteht nicht grunds&auml;tzlich ein Aufnahmeanspruch. Wende dich ggf. an die Botschafter*innen vor Ort um die konkreten Modalit&auml;ten zu erfahren. Wenn du einem Bezirk beitrittst und angenommen wirst, wirst du vom System automatisch auch den &uuml;bergeordneten Bezirken zugef&uuml;gt, um &uuml;ber deren Foren jeweils ebenfalls auch dich betreffende &uuml;berregionale Ank&uuml;ndigungen zu erhalten. (Beispiel: Wenn du dem Bezirk M&uuml;nchen-Schwabing beitrittst, dann wirst du automatisch auch den &uuml;bergeordneten Bezirken M&uuml;nchen, Oberbayern, Bayern und Deutschland zugef&uuml;gt.) </span><span>Zust&auml;ndige Botschafter*innen und Orgamenschen k&ouml;nnen dich n&ouml;tigenfalls auch aus Bezirken entfernen und anderen Bezirken zuweisen, etwa bei Umzug, l&auml;ngerer Inaktivit&auml;t oder wenn aus organisatorischen Gr&uuml;nden Bezirke zusammengelegt oder aufgel&ouml;st werden.&nbsp;<span><span>Hierbei wird protokolliert, wer etwaige (Stamm-)Bezirkswechsel vorgenommen hat und in welchen Bezirken du vorher warst. Diese Informationen k&ouml;nnen ebenfalls von Botschafter*innen und Orgamenschen eingesehen werden. Dies dient insbesondere dem Nachweis und der Einhaltung vertraglicher Pflichten gem. Art. 6 Abs. 1 S. 1 lit. c DSGVO.</span></span></span></p>
<h5><span>q) Vervollst&auml;ndigung/Korrektur von Adresseingaben</span></h5>
<p><span>Betriebe, Fair-Teiler, Essensk&ouml;rbe, Eventlokationen und die Wohnorte von Nutzer*innen sind Objekte mit geographischem Bezug, sie haben in der Regel eine konkrete Postanschrift und Lage, anhand derer wir sie auch in Karten und r&auml;umlichen Suchergebnissen anzeigen. Beim Einstellen/Bearbeiten solcher Objekte kannst du Adressen hinterlegen oder &auml;ndern. Um die Adresseingabe zu erleichtern, wird ein zentrales Suchfeld zur Verf&uuml;gung gestellt, in dem du nach Objekten, Stra&szlig;en, Hausnummern, St&auml;dten usw. suchen kannst. Die dort eingegebenen Informationen werden mit einem Kartendienst (derzeit photon.komoot.de) abgeglichen, der darauf basierend entsprechende Suchergebnisse anzeigt. Mit Auswahl eines Suchergebnisses werden die zugeh&ouml;rigen Geokoordinaten (geographische Breite und geographische L&auml;nge) des gew&auml;hlten Objekts sowie die richtige Schreibweise der gesuchten Adresse ermittelt, die wir dann zu dem Objekt speichern. Bei diesem Vorgang werden lediglich die ins Suchfeld eingegebenen Suchbegriffe an den Kartendienst &uuml;bermittelt (das hei&szlig;t i. d. R. Stra&szlig;e, Hausnummer, ggf. PLZ und Ort), ohne jeglichen Personenbezug (d. h. systemseitig werden weder dein Name, noch eindeutige Benutzerkennungen und auch keine anderen Informationen &uuml;ber dich an den Kartendienst &uuml;bermittelt, solange du diese Informationen nicht selbst ins Suchfeld eingibst).</span></p>
<h5><span>r) Push-Notifications</span></h5>
<p><span>Damit wir effizient Push-Benachrichtigungen an dein Endger&auml;t senden k&ouml;nnen, nutzen wir einen Push-Server, welcher Benachrichtigungen von verschiedenen Anwendungen geb&uuml;ndelt an dein Endger&auml;t weiterleitet. Dadurch muss dein Endger&auml;t nur eine Verbindung zu einem einzelnen Server aufbauen. Falls du foodsharing im Browser nutzt, wird der in deinem Browser konfigurierte Push-Server (gem&auml;&szlig; RFC 8291 und RFC 8030) genutzt. In der Android App nutzen wir als Push-Server Firebase Cloud Messaging der Firma Google und in der iOS App die Apple Push Notifications services der Firma Apple.</span></p>
<p><span>Wenn du Push-Benachrichtigungen aktivierst, erhalten wir vom Anbieter des Push-Servers eine eindeutige und nur f&uuml;r uns geltende Kennung und einen auf deinem Endger&auml;t generierten kryptografischen Schl&uuml;ssel, die/den wir in der Datenbank ablegen und mit der/dem wir Benachrichtigungen f&uuml;r dich verschl&uuml;sseln und an diesen Anbieter schicken k&ouml;nnen, der sie dann an dein Endger&auml;t weiterleitet, wo die Nachrichten entschl&uuml;sselt und angezeigt werden.</span></p>
<p><span>Durch diesen Prozess fallen beim Anbieter des Push-Servers Verbindungsdaten an, die dieser m&ouml;glicherweise speichert. Das ist notwendig, damit dir Push-Notifications &uuml;berhaupt zugestellt werden k&ouml;nnen, und in den Datenschutzerkl&auml;rungen der jeweiligen Anbieter geregelt.</span></p>
<h5><span>s) Abstimmungen</span></h5>
<p><span>Sofern auf der foodsharing-Plattform Abstimmungen oder Wahlen mit dem daf&uuml;r vorgesehenen Abstimmungsfeature durchgef&uuml;hrt werden, speichern wir wenn du deine Stimme abgibst nur, dass du bereits gew&auml;hlt hast und z&auml;hlen deine Stimme direkt im Abstimmungsergebnis durch Erh&ouml;hen bzw. Verringern des entsprechenden Z&auml;hlers in der Ergebnistabelle. Wir speichern aber nicht ab, wie du abgestimmt hast. Damit ist die Wahl geheim und die Stimmabgabe absolut, sie kann zu einem sp&auml;teren Zeitpunkt nicht mehr eingesehen oder ge&auml;ndert werden. Wenn du selbst f&uuml;r eine angesetzte Wahl kandidierst, willigst du ein, dass die Ersteller der Abstimmung deinen Namen und/oder deinen Profillink als Wahloption aufnehmen und dass alle Wahlberechtigten diese Informationen w&auml;hrend und nach Beendigung der Wahl im Wahlergebnis sehen k&ouml;nnen.</span></p>
<h4><span>3. Weitergabe von Daten</span></h4>
<p><span>Eine &Uuml;bermittlung deiner pers&ouml;nlichen Daten an Dritte zu anderen als den im Folgenden aufgef&uuml;hrten Zwecken findet nicht statt.</span></p>
<p><span>Wir geben deine pers&ouml;nlichen Daten nur an Dritte weiter, wenn:</span></p>
<p><span>&bull; Du deine nach Art. 6 Abs. 1 S. 1 lit. a DSGVO ausdr&uuml;ckliche Einwilligung dazu erteilt hast,</span></p>
<p><span>&bull; die Weitergabe nach Art. 6 Abs. 1 S. 1 lit. f DSGVO zur Geltendmachung, Aus&uuml;bung oder Verteidigung von Rechtsanspr&uuml;chen erforderlich ist und kein Grund zur Annahme besteht, dass du ein &uuml;berwiegendes schutzw&uuml;rdiges Interesse an der Nichtweitergabe deiner Daten hast,</span></p>
<p><span>&bull; f&uuml;r den Fall, dass f&uuml;r die Weitergabe nach Art. 6 Abs. 1 S. 1 lit. c DSGVO eine gesetzliche Verpflichtung besteht (bspw. an Lebensmittel&uuml;berwachungs- oder Strafverfolgungsbeh&ouml;rden), sowie</span></p>
<p><span>&bull; dies gesetzlich zul&auml;ssig und nach Art. 6 Abs. 1 S. 1 lit. b DSGVO f&uuml;r die Abwicklung von Vertragsverh&auml;ltnissen mit dir erforderlich ist (bspw. die &Uuml;bermittlung von Name und Adresse an die Post bzw. den Paketzusteller, wenn du Werbematerial bei uns bestellst).</span></p>
<h4><span>4. Aktive Inhalte und Cookies</span></h4>
<h5><span>a) JavaScript</span></h5>
<p><span>JavaScript ist eine clientseitige Skriptsprache, die verwendet wird, um Benutzerinteraktion durchzuf&uuml;hren bzw. auszuwerten, Seiteninhalte zu ver&auml;ndern, nachzuladen oder zu generieren. Foodsharing.de verwendet JavaScript zu ebendiesen Zwecken. Die &Uuml;bertragung von Daten zwischen deinem Browser und der Anwendung erfolgt dabei verschl&uuml;sselt (vgl. Ziff.&nbsp;9). Die meisten Browser akzeptieren JavaScript automatisch. Du kannst deinen Browser jedoch so konfigurieren, dass keine aktiven Inhalte auf deinem Computer ausgef&uuml;hrt werden. Die vollst&auml;ndige Deaktivierung von JavaScript wird jedoch dazu f&uuml;hren, dass du nicht alle Funktionen der Plattform wie vorgesehen nutzen kannst. Insbesondere die Nutzung von mitgliederexklusiven Angeboten (Ziff. 2d&nbsp;ff.) funktioniert gr&ouml;&szlig;tenteils nur, wenn du JavaScript aktiviert hast, da sonst keine direkte Interaktion mit der Anwendung m&ouml;glich ist.</span></p>
<h5><span>b) Cookies</span></h5>
<p><span>Wir setzen auf unserer Seite Cookies ein. Hierbei handelt es sich um kleine Dateien, die dein Browser automatisch erstellt und die auf deinem Endger&auml;t (Laptop, Tablet, Smartphone o.&nbsp;&auml;.) gespeichert werden, wenn du unsere Seite besuchst. Cookies richten auf deinem Endger&auml;t keinen Schaden an, enthalten keine Viren, Trojaner oder sonstige Schadsoftware.</span></p>
<p><span>In dem Cookie werden Informationen abgelegt, die sich jeweils im Zusammenhang mit dem spezifisch eingesetzten Endger&auml;t ergeben. Damit k&ouml;nnen wir erkennen, wenn Benutzer im Rahmen einer Sitzung oder auch sp&auml;ter erneut Seiten unserer Plattform aufrufen. Dies bedeutet, dass wir Benutzer &uuml;ber die Dauer ihres Besuchs wiedererkennen k&ouml;nnen, jedoch nicht, dass wir dadurch unmittelbar Kenntnis von deren Identit&auml;t erhalten. Dies ist nur im Zusammenhang mit einer expliziten personenbezogenen Anmeldung (siehe Ziffer&nbsp;2d) m&ouml;glich.</span></p>
<p><span>Der Einsatz von Cookies dient einerseits dazu, die Nutzung unseres Angebots f&uuml;r dich angenehmer zu gestalten. So setzen wir sogenannte Session-Cookies zur Ablaufsteuerung &nbsp;und der &Uuml;bertragung eingegebener Daten an Folgeseiten ein. Diese werden gel&ouml;scht, wenn du dich von der Seite abmeldest oder deinen Webbrowser schlie&szlig;t.</span></p>
<p><span>Dar&uuml;ber hinaus setzen wir ebenfalls zur Optimierung der Benutzerfreundlichkeit tempor&auml;re Cookies ein, die f&uuml;r einen bestimmten festgelegten Zeitraum auf deinem Endger&auml;t gespeichert werden. Besuchst du unsere Seite erneut, um unsere Dienste in Anspruch zu nehmen, wird automatisch erkannt, dass du bereits bei uns warst und welche Eingaben und Einstellungen du get&auml;tigt hast, um diese nicht noch einmal eingeben zu m&uuml;ssen.</span></p>
<p><span>Zum anderen setzen wir Cookies ein, um die Nutzung unserer Website statistisch zu erfassen und zum Zwecke der Optimierung unseres Angebotes f&uuml;r dich auszuwerten (siehe Ziff.&nbsp;5). Diese Cookies erm&ouml;glichen es uns, bei einem erneuten Besuch unserer Seite automatisch zu erkennen, dass du bereits bei uns warst. Diese Cookies werden nach einer jeweils definierten Zeit automatisch gel&ouml;scht.</span></p>
<p><span>Die durch Cookies verarbeiteten Daten sind f&uuml;r die genannten Zwecke zur Wahrung unserer berechtigten Interessen sowie der Dritter nach Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;f DSGVO erforderlich.</span></p>
<p><span>Die meisten Browser akzeptieren Cookies automatisch. Du kannst deinen Browser jedoch so konfigurieren, dass keine Cookies auf deinem Computer gespeichert werden oder stets ein Hinweis erscheint, bevor ein neuer Cookie angelegt wird. Die vollst&auml;ndige Deaktivierung von Cookies kann jedoch dazu f&uuml;hren, dass du nicht alle Funktionen unserer Website nutzen kannst. Insbesondere die Nutzung von mitgliederexklusiven Angeboten (Ziff.&nbsp;2d&nbsp;ff.) sind nur m&ouml;glich, wenn du Sitzungscookies zul&auml;sst.</span></p>
<h4><span>5. Analyse-Tools</span></h4>
<h5><span>a</span><span>) Statistische Auswertungen</span></h5>
<p><span>Wir nehmen auf Basis der in Ziffer 2a aufgef&uuml;hrten Zugriffsprotokolle statistische Auswertungen auf Grundlage des Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;f DSGVO zum Zwecke der Optimierung unseres Angebots vor. Hierbei werten wir nur auf unserem Server anfallende Daten aus und &uuml;bertragen keinerlei personenbezogene Zugriffsdaten an Dritte. Wir setzen keinerlei Z&auml;hlpixel oder Third-Party-Cookies Dritter ein.</span></p>
<p><span>Wir behalten uns </span><span>ebenfalls </span><span>vor, statistische Auswertungen zu den angemeldeten Nutzer*innen auf Grundlage des Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;f DSGVO durchzuf&uuml;hren, bspw. zur Alters- und Geschlechtsstruktur der angemeldeten Foodsaver. Hierbei werden von dir angegebene personenbezogene Daten jedoch nur kumuliert bzw. anonymisiert verwendet, so dass anhand der statistischen Auswertungen keinerlei direkte oder indirekte R&uuml;ckschl&uuml;sse auf deine Person m&ouml;glich sind.</span></p>
<h5><span>b) Facebook Insights</span></h5>
<p><span>Mit dem Besuch unserer Facebook-Seite willigst du ein, dass Facebook nutzerbezogene Daten erfasst und uns anonymisiert als Seitenstatistiken (sog. &bdquo;Insights&ldquo;) zur Verf&uuml;gung stellt. Rechtsgrundlage ist damit Art. 6 Abs. 1 S. 1 lit. a resp. b DSGVO. Die Datenerhebung erfolgt in diesem Fall jedoch nicht durch uns, sondern durch Facebook und wir haben auch keinerlei Einfluss auf Art und Umfang der Erhebung, ebenfalls k&ouml;nnen wir ihr nicht widersprechen. Bei diesbez&uuml;glichen Fragen und Auskunftsersuchen wende dich daher bitte direkt an Facebook bzw. an den in deren Datenrichtlinie genannten Datenschutzbeauftragten.</span></p>
<h4><span>6. Social Media Plug-ins</span></h4>
<p><span>Wir setzen auf unserer Website derzeit keinerlei Social Media Plug-ins ein.</span></p>
<h4><span>7. Betroffenenrechte</span></h4>
<p><span>Du hast das Recht:</span></p>
<ul>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;15 DSGVO Auskunft &uuml;ber deine von uns verarbeiteten personenbezogenen Daten zu verlangen. Insbesondere kannst du Auskunft &uuml;ber die Verarbeitungszwecke, die Kategorie der personenbezogenen Daten, die Kategorien von Empf&auml;ngern, gegen&uuml;ber denen deine Daten offengelegt wurden oder werden, die geplante Speicherdauer, das Bestehen eines Rechts auf Berichtigung, L&ouml;schung, Einschr&auml;nkung der Verarbeitung oder Widerspruch, das Bestehen eines Beschwerderechts, die Herkunft deiner Daten, sofern diese nicht bei uns erhoben wurden, sowie &uuml;ber das Bestehen einer automatisierten Entscheidungsfindung einschlie&szlig;lich Profiling und ggf. aussagekr&auml;ftigen Informationen zu deren Einzelheiten verlangen;</span></p>
</li>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;16 DSGVO unverz&uuml;glich die Berichtigung unrichtiger oder Vervollst&auml;ndigung deiner bei uns gespeicherten personenbezogenen Daten zu verlangen;</span></p>
</li>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;17 DSGVO die L&ouml;schung deiner bei uns gespeicherten personenbezogenen Daten zu verlangen, soweit nicht die Verarbeitung zur Aus&uuml;bung des Rechts auf freie Meinungs&auml;u&szlig;erung und Information, zur Erf&uuml;llung einer rechtlichen Verpflichtung, aus Gr&uuml;nden des &ouml;ffentlichen Interesses oder zur Geltendmachung, Aus&uuml;bung oder Verteidigung von Rechtsanspr&uuml;chen erforderlich ist;</span></p>
</li>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;18 DSGVO die Einschr&auml;nkung der Verarbeitung deiner personenbezogenen Daten zu verlangen, soweit die Richtigkeit der Daten von dir bestritten wird, die Verarbeitung unrechtm&auml;&szlig;ig ist, du aber deren L&ouml;schung ablehnst und wir die Daten nicht mehr ben&ouml;tigen, du diese jedoch zur Geltendmachung, Aus&uuml;bung oder Verteidigung von Rechtsanspr&uuml;chen ben&ouml;tigst oder du gem&auml;&szlig; Art.&nbsp;21 DSGVO Widerspruch gegen die Verarbeitung eingelegt hast;</span></p>
</li>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;20 DSGVO deine personenbezogenen Daten, die du uns bereitgestellt hast, in einem strukturierten, g&auml;ngigen und maschinenlesbaren Format zu erhalten oder die &Uuml;bermittlung an einen anderen Verantwortlichen zu verlangen;</span></p>
</li>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;7 Abs.&nbsp;3 DSGVO deine einmal erteilte Einwilligung jederzeit gegen&uuml;ber uns zu widerrufen. Dies hat zur Folge, dass wir die Datenverarbeitung, die auf dieser Einwilligung beruhte, f&uuml;r die Zukunft nicht mehr fortf&uuml;hren d&uuml;rfen, und</span></p>
</li>
<li>
<p><span>dich gem&auml;&szlig; Art.&nbsp;77 DSGVO bei einer Aufsichtsbeh&ouml;rde beschweren, wenn du .der Ansicht bist, dass die Verarbeitung der dich betreffenden personenbezogenen Daten gegen die DSGVO verst&ouml;&szlig;t.</span></p>
</li>
</ul>
<p><span>Die f&uuml;r uns unmittelbar zust&auml;ndige Aufsichtsbeh&ouml;rde ist:</span></p>
<p><span><span> </span></span><span>Landesbeauftragte f&uuml;r Datenschutz und Informationsfreiheit </span><span><br /></span><span><span> </span></span><span>Nordrhein-Westfalen</span><span><br /></span><span><span> </span></span><span>Postfach 20 04 44</span><span><br /></span><span><span> </span></span><span>40102 D&uuml;sseldorf</span></p>
<p><span>Ansonsten kannst du dich aber auch an die Aufsichtsbeh&ouml;rde deines &uuml;blichen Aufenthaltsortes oder Arbeitsplatzes wenden.</span></p>
<h4><span>8. Widerspruchsrecht</span></h4>
<p><span>Sofern deine personenbezogenen Daten auf Grundlage von berechtigten Interessen gem&auml;&szlig; Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;f DSGVO verarbeitet werden, hast du das Recht, gem&auml;&szlig; Art.&nbsp;21 DSGVO Widerspruch gegen die Verarbeitung deiner personenbezogenen Daten einzulegen, soweit daf&uuml;r Gr&uuml;nde vorliegen, die sich aus deiner besonderen Situation ergeben oder sich der Widerspruch gegen Direktwerbung richtet. Im letzteren Fall hast du ein generelles Widerspruchsrecht, das ohne Angabe einer besonderen Situation von uns umgesetzt wird.</span></p>
<p><span>M&ouml;chtest du von deinem Widerrufs- oder Widerspruchsrecht Gebrauch machen, gen&uuml;gt eine E-Mail an datenschutz@foodsharing.de</span></p>
<h4><span>9. Datensicherheit</span></h4>
<p><span>Wir verwenden innerhalb des Website-Besuchs das verbreitete SSL-Verfahren (Secure Socket Layer) in Verbindung mit der jeweils h&ouml;chsten Verschl&uuml;sselungsstufe, die von deinem Browser unterst&uuml;tzt wird. In der Regel handelt es sich dabei um eine 256-Bit-Verschl&uuml;sselung oder h&ouml;her. Falls dein Browser keine 256-Bit-Verschl&uuml;sselung unterst&uuml;tzt, greifen wir stattdessen auf 128-Bit-v3-Technologie zur&uuml;ck. Ob eine einzelne Seite unseres Internetauftrittes verschl&uuml;sselt &uuml;bertragen wird, erkennst du an der geschlossenen Darstellung des Sch&uuml;ssel- beziehungsweise Schloss-Symbols in der Adresszeile oder der Statusleiste deines Browsers.</span></p>
<p><span>Wir bedienen uns im &Uuml;brigen geeigneter technischer und organisatorischer Sicherheitsma&szlig;nahmen, um deine Daten gegen zuf&auml;llige oder vors&auml;tzliche Manipulationen, teilweisen oder vollst&auml;ndigen Verlust, Zerst&ouml;rung oder gegen den unbefugten Zugriff Dritter zu sch&uuml;tzen. Unsere Sicherheitsma&szlig;nahmen werden entsprechend der technologischen Entwicklung fortlaufend verbessert.</span></p>
<h4><span>10. Aktualit&auml;t und &Auml;nderung dieser Datenschutzerkl&auml;rung</span></h4>
<p><span>Diese Datenschutzerkl&auml;rung ist aktuell g&uuml;ltig und hat den Stand Mai 2020</span><span>.</span></p>
<p><span>Durch die Weiterentwicklung unserer Website und Angebote dar&uuml;ber oder aufgrund ge&auml;nderter gesetzlicher beziehungsweise beh&ouml;rdlicher Vorgaben kann es notwendig werden, diese Datenschutzerkl&auml;rung zu &auml;ndern. Die jeweils aktuelle Datenschutzerkl&auml;rung kann jederzeit auf unserer Website abgerufen und/oder ausgedruckt werden.</span></p>', 'last_mod' => '2020-05-16 00:09:33'],

			['id' => '30', 'name' => 'rv-foodsaver', 'title' => 'Rechtsvereinbarung für Foodsaver', 'body' => '<p><strong>Eigenerkl&auml;rung - Verhaltenskodex und Sorgfaltspflichten</strong></p>
<p><span>Ich erkl&auml;re das Folgende:<br /><br /></span>Ich werde im Rahmen von foodsharing als Foodsaver t&auml;tig werden. Das hei&szlig;t, ich hole bei LebensmittelspenderInnen Lebensmittel ab und verpflichte mich, diese entweder selbst zu verbrauchen oder ausschlie&szlig;lich unentgeltlich an Dritte weiterzugeben (privat, Suppenk&uuml;chen, Tafeln, Bahnhofsmissionen, gemeinn&uuml;tzige Vereine, Fair-Teiler, online als Essenskorb etc.).<br /><br />Das oberste Ziel ist es, alle noch genie&szlig;baren Lebensmittel vor der Vernichtung zu bewahren und sie dem menschlichen Verzehr zuzuf&uuml;hren. Als Foodsaver handle ich ehrenamtlich aus sozialen, ethischen und &ouml;kologischen Gr&uuml;nden, um die Lebensmittelverschwendung und damit den Hunger, die Ressourcenverschwendung und den Klimawandel uvm. zu minimieren.<br /><br />Die Foodsaver sind eine effiziente, lokale und zeitnahe Erg&auml;nzung zu anderen gemeinn&uuml;tzigen Organisationen wie z.B. den Tafeln. Zielsetzung ist es, neben gro&szlig;en Lebensmittelh&auml;nderInnen, m&ouml;glichst allen kleinen LebensmittelspenderInnen wie B&auml;ckereien, Biol&auml;den, Restaurants etc. die Kooperation mit den Foodsavern zu erm&ouml;glichen, sodass unabh&auml;ngig von der Gr&ouml;&szlig;e des Lebensmittelbetriebes keine noch genie&szlig;baren Lebensmittel weggeworfen werden m&uuml;ssen.<br /><br />Die umfassende Zufriedenheit unserer Kooperationsbetriebe ist ein elementarer Teil des Lebensmittelrettens. Ich verpflichte mich, mich daf&uuml;r mit zuverl&auml;ssigem, freundlichem und aufgeschlossenem Verhalten gegen&uuml;ber den Menschen und Betrieben auf allen Ebenen einzusetzen.<br /><br />Ich verpflichte mich, keine Betriebe anzusprechen bzw. Kooperationen aufzubauen, so lange ich nicht das Quiz zum Betriebsverantwortlichen bestanden habe. Generell ist es nur in Absprache mit dem Betriebskettenteam gestattet, Betriebe mit mehr als 2 Filialen anzusprechen.<br /><br />Ziel ist es, eine Abholquote von 100% zu erreichen. Um diese zu gew&auml;hrleisten, bin ich als</p>
<p><span>Foodsaver dazu angehalten, alle Abholtermine auf der Website einzutragen und gut mit anderen Foodsavern vernetzt zu sein.&nbsp;Bei unerwartetem Ausfall wie z.B. durch Krankheit etc. bin ich dazu verpflichtet, mich schnellstm&ouml;glich aus dem Kalender auszutragen und mich um einen Ersatzfoodsaver zu k&uuml;mmern, der schon mal bei dem Lebensmittelspendebetrieb abgeholt hat und nur im Notfall einen Foodsaver zu w&auml;hlen, der bei dem Betrieb noch nie abgeholt hat. Sollte sich bis 24 Stunden vor dem Abholtermin kein Ersatz gefunden haben, muss das Suchen nach einem Ersatz via Telefon und E-Mail fortgef&uuml;hrt werden, bis jemand gefunden wird. Ist die Suche auch bis zu einer Stunde vor Abholtermin nicht erfolgreich, muss die Filiale umgehend telefonisch informiert werden, dass an dem betreffenden Tag keine Abholung vorgenommen werden kann. </span></p>
<p><span>In den Ausnahmesituationen, in denen trotz aller Bem&uuml;hungen keine Abholung stattfinden konnte, muss das Team des Betriebes per Pinnwandeintrag &uuml;ber das Nichterscheinen informiert werden sowie das Nichtabholen als eigener Versto&szlig; gemeldet werden.<br /><br /></span>Als Foodsaver sichere ich zu, K&uuml;hlware und leicht verderbliche Lebensmittel&nbsp;bis zur &Uuml;bergabe an Dritte sachgerecht zu lagern bzw. zu k&uuml;hlen und andernfalls solche Lebensmittel nicht an Dritte weiterzugeben.<br /><br />Als Foodsaver garantiere ich, w&auml;hrend der Abholungen oder danach keine noch essbaren Lebensmittel zu entsorgen und mich verantwortlich und fachgerecht um die Entsorgung der nicht mehr genie&szlig;baren Lebensmittel, aber auch Verpackungen, Kartons etc. zu k&uuml;mmern.<br /><br />Desweiteren verpflichte ich mich, den Ort, an dem die Ware entgegengenommen bzw. getrennt wird, mindestens so sauber zu hinterlassen, wie ich ihn vorgefunden habe.<br /><br /><span>Die Lebensmittel werden zu den Zeiten abgeholt, zu welchen es die Lebensmittelspenderbetriebe</span><span> </span><span>w&uuml;nschen. Normalerweise sind dies feste Zeiten, allerdings stehen die Foodsaver auch</span><span> </span><span>bereit, um au&szlig;erterminlich Lebensmittel abzuholen.<br /><br /></span><span>Ich best&auml;tige, die </span><a href="http://wiki.foodsharing.de/Verhaltensregeln" target="_blank"><span>Verhaltensregeln</span></a><span> und andere </span><a href="http://wiki.foodsharing.de" target="_blank"><span>foodsharing-Wiki-Dokumente</span></a><span> gelesen und verstanden zu haben und verpflichte mich, mich nach diesen zu verhalten. Wenn ich Kenntnis davon erlange, dass diese Verhaltensregeln von anderen Foodsavern, Betriebsverantwortlichen oder BotschafterInnen nicht eingehalten werden, melde ich diese Verst&ouml;&szlig;e &uuml;ber das Formular "Versto&szlig; melden" im Profil des jeweiligen Users.<br /><br /></span>Als Foodsaver erkl&auml;re ich, die in dieser Vereinbarung festgehaltenen Werte zu achten und foodsharing nicht zu sch&auml;digen. Dies beinhaltet insbesondere die Pflicht, jegliche diskreditierenden Aussagen gegen foodsharing, ihren BotschafterInnen, Foodsavern und anderen Unterst&uuml;tzerInnen, auch nach Beendigung meiner Teilnahme als Foodsaver, zu unterlassen. Ich nehme zur Kenntnis, dass ich bei Versto&szlig; gegen diese Erkl&auml;rung, insbesondere wenn ich foodsharing durch meine Handlungen oder Aussagen vors&auml;tzlich oder grob fahrl&auml;ssig sch&auml;dige, von einer Teilnahme als Foodsaver ausgeschlossen werde bzw. mir die Teilnahme als Foodsaver untersagt wird.<br /><br />Ich verpflichte mich auch, mich &uuml;ber aktuelle Informationen und Neuigkeiten auf dem Laufenden zu halten (Regelm&auml;&szlig;ige foodsharing-Treffen aufsuchen, Newsletter lesen, Foren auf der Homepage besuchen, Mails lesen).<br /><br />foodsharing ist&nbsp;in erster Linie parteipolitisch neutral.&nbsp;<span>Ich verpflichte mich, mich diesbez&uuml;glich an die Regeln und Vorgaben im Dokument </span><a href="http://wiki.foodsharing.de/Foodsharing_und_Politik" target="_blank"><span>&bdquo;Foodsharing und Politk&ldquo;</span></a><span> zu halten.</span></p>', 'last_mod' => '2019-06-11 13:35:31'],
			['id' => '31', 'name' => 'rv-biebs', 'title' => 'Rechtsvereinbarung', 'body' => '<p><strong>Zusatzrechtsvereinbarung f&uuml;r Betriebsverantwortliche bei foodsharing:</strong></p>
<p><strong>&nbsp;</strong></p>
<p><span>Zus&auml;tzlich bin ich als BetriebsverantwortlicheR daf&uuml;r verantwortlich, nur Betriebe anzusprechen, bei denen ich auch garantieren kann, dass gen&uuml;gend Foodsaver bereit stehen, die notfalls auch 7 Tage die Woche ab Erstkontakt Lebensmittel abholen k&ouml;nnen.<br /><br /></span>Bevor ich als BetriebsverantwortlicheR aktiv werde, verpflichte ich mich, mit den BotschafterInnen meiner Region in Kontakt zu treten und erst nach Absprache mit ihnen neue KooperationspartnerInnen zu suchen. Dabei versichere ich, nur inhaberInnengef&uuml;hrte Betriebe anzusprechen und f&uuml;r alle Betriebe mit mehr als 2 Filialen das Betriebskettenteam zu kontaktieren.</p>
<p><span><br /></span>Noch bevor ich einen Betrieb anspreche, &uuml;berpr&uuml;fe ich, ob der Betrieb nicht bereits eingetragen wurde. Nach jedem Kontakt zum Betrieb trage ich alle relevanten Informationen noch am selben Tag bei foodsharing ein bzw. aktualisiere alle in Erfahrung gebrachten Informationen, die bei der Betriebseintragung abgefragt werden.</p>
<p><span><br /></span>Alle Betriebe, die von mir im Rahmen von foodsharing angelegt und angesprochen werden, sind Teil des foodsharing-Netzwerkes. Wird mir aufgrund von Verst&ouml;&szlig;en gegen die foodsharing-Regeln die Betriebsverantwortlichkeit entzogen, &uuml;bernimmt bis zur Ernennung eines neuen Betriebsverantwortlichen der/die zust&auml;ndige BotschafterIn die Betriebsverantwortlichkeit. Das Gleiche gilt nach freiwilligem R&uuml;ckzug als BetriebsverantwortlicheR. Es ist nicht gestattet, nach freiwilligem oder unfreiwilligem Verlassen des Betriebes weiterhin Lebensmittel in diesem Betrieb abzuholen. Des Weiteren ist es untersagt, die Kooperation zwischen foodsharing und einem Betrieb ohne Einverst&auml;ndnis und Absprache mit den BundeslandbotschafterInnen bzw. des Orgateams zu beenden.</p>
<p><span><br />Ich habe daf&uuml;r Sorge zu tragen, dass es bei allen Betrieben, bei denen ich die Betriebsverantwortlichkeit innehabe, eine gute Stimmung gibt und sich alle Beteiligten - Foodsaver sowie Angestellten - wohlf&uuml;hlen. Damit Problemen vorgebeugt wird bzw. entstandene Probleme schnell gel&ouml;st werden, werde ich ausreichend Kommunikation betreiben.<br /><br /></span>Ich verplichte mich, jeden Versto&szlig; gegen die Verhaltensregeln zu &uuml;berpr&uuml;fen und ggf. Konsequenzen zu ziehen, zu schlichten und einzelne Foodsaver, nach Absprache mit den zugeh&ouml;rigen BotschafterInnen, aus dem Team zu nehmen.<br /><br />Au&szlig;erdem werde ich alle wichtigen Informationen zum Status der Kooperation sowie &Auml;nderungen umgehend auf die Pinnwand schreiben bzw. unter &ldquo;Betrieb&rdquo; einarbeiten.</p>
<p><span><br /></span>Zus&auml;tzlich erkl&auml;re ich mich als BetriebsverantwortlicheR bereit, foodsharing&nbsp;immer verantwortungsbewusst und motiviert zu repr&auml;sentieren. Ich bin mir bewusst, dass ich im Hinblick auf die Foodsaver, Betriebsverantwortlichen und BotschafterInnenkollegInnen eine Vorbildfunktion innehabe, die mir Freude bereitet und die ich ernstnehme. Das <a href="http://wiki.foodsharing.de/Betriebsverantwortliche" target="_blank">Wiki-Dokument</a>&nbsp;bez&uuml;glich meiner Aufgaben und anderen Verpflichtungen als BetriebsverantwortlicheR habe ich gelesen, verinnerlicht und stehe dahinter.</p>
<p></p>
<p></p>', 'last_mod' => '2019-06-11 13:35:58'],
			['id' => '32', 'name' => 'rv-botschafter', 'title' => 'Rechtsvereinbarung', 'body' => '<p>Derzeit keine, werden noch bearbeitet und dann nachgereicht.&nbsp;</p>', 'last_mod' => '2015-01-07 05:32:12'],
			['id' => '33', 'name' => 'quiz-hinweis', 'title' => 'Wichtiger Hinweis:', 'body' => '<div class="ace-line">
<p><span>Liebe*r&nbsp;{NAME},<br /><br /></span>sch&ouml;n, dass Du dabei bist und Dich gegen die Lebensmittelverschwendung einsetzen willst!<br />Willst Du in Zukunft wie schon tausende andere&nbsp;<strong>Foodsaver werden und Lebensmittel bei B&auml;ckereien, Superm&auml;rkten, Restaurants etc.&nbsp;retten?</strong><br />Vielleicht sogar BetriebsverantwortlicheR werden oder Dich bei einen der unz&auml;hligen Arbeitsgruppen einbringen? Solltest Du&nbsp;Lust auf noch mehr Verantwortung haben und Deine Region mitaufbauen wollen bzw. bestehende BotschafterInnen unterst&uuml;tzen wollen, kannst Du Dich auch als&nbsp;foodsharing BotschafterIn bewerben. Lese Dich jetzt in die notwendigen <a href="https://wiki.foodsharing.de/Foodsaver" target="_blank">Dokumente im Wiki</a>&nbsp;ein, um dann <a href="/?page=settings&amp;sub=up_fs" target="_blank">das kleine Quiz</a> zu absolvieren.<br /><br />Du hast die M&ouml;glichkeit zwischen dem Quiz mit 10 Fragen und Zeitlimit oder dem&nbsp;<span>Quiz mit 20 Fragen ohne Zeitlimit.<br /><br /></span><strong>Sch&ouml;n, dass Du dabei bist und Dich einbringen willst! Wir freuen uns auf Deine Unterst&uuml;tzung!</strong></p>
<span><span>Herzlich Dein&nbsp;foodsharing Orgateam</span></span></div>', 'last_mod' => '2019-03-12 11:02:45'],

			['id' => '36', 'name' => 'bot-last-quiz-popup', 'title' => 'Wichtig: BotschafterInnen Quiz jetzt machen', 'body' => '<div>Liebe*r {NAME}</div>
<div>hiermit erinnern wir Dich an das BotschafterInnen Quiz f&uuml;r welches Du noch bis einschlie&szlig;lich dem 12.01 Zeit hast. Bitte lies Dir Dir dazu alle n&ouml;tigen Wiki-Dokumente durch, solltest Du das Foodsaver und/oder&nbsp;Betriebsverantwortlichen Quiz noch nicht gemacht haben, musst Du diese erst bestehen um das BotschafterInnen Quiz machen zu k&ouml;nnen.</div>
<div></div>
<div>Vielen Dank dir Dir f&uuml;r Deinen Einsatz, wir sind Dir sehr dankbar und nun gutes gelingen beim Quiz!</div>
<div></div>
<div>Einen wunderbaren Einstieg in die BotschafterInnen Welt</div>
<div></div>
<div>Herzlich Euer fooddsharing Orgateam</div>', 'last_mod' => '2019-11-09 12:17:09'],
			['id' => '37', 'name' => 'myfoodsharing-at-mai', 'title' => 'Myfoodsharing.at Hauptseite', 'body' => '<div class="campaign topbarpadding">
<div class="campaignimg"><img src="/img/fsgabelgwrgbklein.png" /></div>
<div class="campaigntext">
<div class="field">
<h2>Gedanken zu Corona</h2>
<div class="ui-widget ui-widget-content margin-bottom ui-padding">
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing#Einschr&auml;nkung_sozialer_Kontakte" target="_blank" class="ui-button"><strong>&Uuml;bernehmt Verantwortung</strong> f&uuml;r euch selbst und eure Mitmenschen &ndash; bleibt, wenn m&ouml;glich zu Hause, trefft Menschen nur, wenn es notwendig ist </a></div>
</div>
</div>
<div class="clear"></div>
</div>
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing" target="_blank" class="ui-button">Haltet unbedingt die <strong>Hygieneregeln</strong> ein </a></div>
</div>
</div>
<div class="clear"></div>
</div>
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing#Solidarit&auml;t" target="_blank" class="ui-button"><strong>Seid solidarisch</strong> - informiert euch, wie ihr wen wo unterst&uuml;tzen k&ouml;nnt </a></div>
</div>
</div>
<div class="clear"></div>
</div>
</div>
</div>
</div>
</div>', 'last_mod' => '2020-03-19 13:58:48'],
			['id' => '38', 'name' => 'foodsharing-de-main', 'title' => 'foodsharing.de Hauptseite', 'body' => '<div class="campaign topbarpadding">
<div class="campaignimg"><img src="/img/fsgabelgwrgbklein.png" /></div>
<div class="campaigntext">
<div class="field">
<h2>Gedanken zu Corona</h2>
<div class="ui-widget ui-widget-content margin-bottom ui-padding">
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing#Einschr&auml;nkung_sozialer_Kontakte" target="_blank" class="ui-button"><strong>&Uuml;bernehmt Verantwortung</strong> f&uuml;r euch selbst und eure Mitmenschen &ndash; bleibt, wenn m&ouml;glich zu Hause, trefft Menschen nur, wenn es notwendig ist </a></div>
</div>
</div>
<div class="clear"></div>
</div>
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing" target="_blank" class="ui-button">Haltet unbedingt die <strong>Hygieneregeln</strong> ein </a></div>
</div>
</div>
<div class="clear"></div>
</div>
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing#Solidarit&auml;t" target="_blank" class="ui-button"><strong>Seid solidarisch</strong> - informiert euch, wie ihr wen wo unterst&uuml;tzen k&ouml;nnt </a></div>
</div>
</div>
<div class="clear"></div>
</div>
</div>
</div>
<h2>Petition</h2>
<div class="ui-widget ui-widget-content margin-bottom ui-padding">
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://www.change.org/lebensmittel-retten" target="_blank" class="ui-button"> &bdquo;Lebensmittelrettung muss einfacher werden&ldquo; - Hier geht es zur Petition von foodsharing und Deutscher Umwelthilfe</a></div>
</div>
</div>
<div class="clear"></div>
</div>
</div>
</div>
</div>', 'last_mod' => '2020-04-09 17:16:19'],
			['id' => '39', 'name' => 'team-header', 'title' => 'Kontakt', 'body' => '<div class="head ui-widget-header ui-corner-top">Ehemalige:</div>
<div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
<p><a href="/team/ehemalige" target="_blank">Hier</a> kommst du zu unseren ehemaligen Unterst&uuml;tzenden.</p>
</div>
<p></p>
<div class="head ui-widget-header ui-corner-top">Kontaktanfragen:</div>
<div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
<p>Auf dieser Seite findest Du die Namen, Bilder und Aufgaben der foodsharing e. V.-Vorst&auml;nde und weiterer Aktiver.</p>
<p>Du kannst gerne Kontakt mit uns aufnehmen! Wir bitten Dich, Dein Anliegen nur einer Person zu schreiben. F&uuml;r <strong>allgemeine Anfragen</strong> stehen wir Dir unter <a href="mailto:info@foodsharing.de" target="_blank">info@foodsharing.de</a> (oder <a href="mailto:info@foodsharingschweiz.ch" target="_blank">info@foodsharingschweiz.ch</a> f&uuml;r die Schweiz) zur Verf&uuml;gung oder leiten Dich gerne an passende Ansprechpersonen &ndash; auch in foodsharing-Bezirken &ndash; weiter. Ein paar direkte Anlaufstellen:</p>
<p></p>
<ul>
<li><strong>Deutschland: </strong>
<ul>
<li>Berlin: <a href="mailto:berlin[at]foodsharing.network" target="_blank">berlin[at]foodsharing.network</a></li>
<li>Bonn: <a href="mailto:bonn[at]foodsharing.network" target="_blank">bonn[at]foodsharing.network</a>&nbsp;</li>
<li>Chemnitz: <a href="mailto:chemnitz[at]foodsharing.network" target="_blank">chemnitz[at]foodsharing.network</a>&nbsp;</li>
<li>Dresden: <a href="mailto:dresden[at]foodsharing.network" target="_blank">dresden[at]foodsharing.network</a>&nbsp;</li>
<li>D&uuml;sseldorf: <a href="mailto:duesseldorf[at]foodsharing.network" target="_blank">duesseldorf[at]foodsharing.network</a>&nbsp;</li>
<li>Frankfurt: <a href="mailto:frankfurt.am.main[at]foodsharing.network" target="_blank">frankfurt.am.main[at]foodsharing.network</a>&nbsp;</li>
<li>Freiburg: <a href="mailto:freiburg[at]foodsharing.network" target="_blank">freiburg[at]foodsharing.network</a>&nbsp;</li>
<li>Gie&szlig;en: <a href="mailto:giessen[at]foodsharing.network" target="_blank">giessen[at]foodsharing.network</a>&nbsp;</li>
<li>Hamburg: <a href="mailto:hamburg[at]foodsharing.network" target="_blank">hamburg[at]foodsharing.network</a>&nbsp;</li>
<li>K&ouml;ln: <a href="mailto:koeln[at]foodsharing.network" target="_blank">koeln[at]foodsharing.network</a>&nbsp;</li>
<li>M&uuml;nchen: <a href="mailto:muenchen[at]foodsharing.network" target="_blank">muenchen[at]foodsharing.network</a>&nbsp;</li>
<li>Stuttgart: <a href="mailto:stuttgart[at]foodsharing.network" target="_blank">stuttgart[at]foodsharing.network</a>&nbsp;</li>
<li>Wiesbaden: <a href="mailto:wiesbaden[at]foodsharing.network" target="_blank">wiesbaden[at]foodsharing.network</a>&nbsp;</li>
<li>&hellip; <a href="/?page=content&amp;sub=communitiesGermany" target="_blank">weitere Bezirke anzeigen</a> &hellip;</li>
</ul>
</li>
</ul>
<p></p>
<ul>
<li><strong>&Ouml;sterreich: </strong>
<ul>
<li>Graz: <a href="mailto:graz[at]foodsharing.network" target="_blank">graz[at]foodsharing.network</a>&nbsp;</li>
<li>Wien: <a href="mailto:wien[at]foodsharing.network" target="_blank">wien[at]foodsharing.network</a>&nbsp;</li>
</ul>
</li>
</ul>
<p></p>
<ul>
<li><strong>Schweiz:</strong>
<ul>
<li>Basel: <a href="mailto:info[at]foodsharingschweiz.ch" target="_blank">info[at]foodsharingschweiz.ch</a>&nbsp;</li>
<li>Bern: <a href="mailto:bern[at]foodsharing.network" target="_blank">bern[at]foodsharing.network</a>&nbsp;</li>
<li>Z&uuml;rich: <a href="mailto:zuerich[at]foodsharing.network" target="_blank">zuerich[at]foodsharing.network</a>&nbsp;</li>
<li>Zug: <a href="mailto:zug[at]foodsharing.network" target="_blank">zug[at]foodsharing.network</a>&nbsp;</li>
</ul>
</li>
</ul>
<p></p>
<p></p>
<p>IIf you are interested in carrying the idea of sharing and saving food around the world (or to your own country) visit <a href="https://foodsaving.world" target="_blank">https://foodsaving.world</a>, join the forum there or contact: <a href="mailto:info@foodsaving.world" target="_blank">info@foodsaving.world</a><a target="_blank">&nbsp;</a></p>
<p></p>
<p>Hast Du Probleme mit der Website oder Deinem Account, wende Dich bitte an&nbsp;<a href="mailto:it@foodsharing.network" target="_blank">it@foodsharing.network</a>.</p>
</div>
<p></p>
<div class="head ui-widget-header ui-corner-top">Vorstand:</div>
<div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
<p>Der Vorstand des foodsharing e.V. h&auml;lt die F&auml;den zusammen, vernetzt Deutschland, &Ouml;sterreich und die Schweiz als Nutzer der Plattform foodsharing.de, und ist f&uuml;r &uuml;berregionalen Angelegenheiten zust&auml;ndig. Der Vorstand arbeitet im Team sehr viel in engen Absprachen miteinander, und haupts&auml;chlich online zusammen, um alle Anliegen der gro&szlig;en foodsharing Community und von Extern zu bek&uuml;mmern. Dar&uuml;ber hinaus besch&auml;ftigt sich der Vorstand ausgiebig mit der Organisationsentwicklung und der politischen Dimension der Lebensmittelverschwendung.</p>
</div>', 'last_mod' => '2020-05-15 10:13:28'],

			['id' => '42', 'name' => 'Spenden', 'title' => 'SPENDEN', 'body' => '<p><span>Liebe foodsharing-Begeisterte,<br /><br />foodsharing w&auml;chst und gedeiht &ndash; das ist wunderbar! Es ist unfassbar, was wir seit 2012 gemeinsam geschafft haben! Es gibt keine andere Organisation in der Gr&ouml;&szlig;enordnung, die ausschlie&szlig;lich ehrenamtlich und f&uuml;r Nutzende kostenlos funktioniert &ndash; darauf d&uuml;rfen wir wirklich stolz sein.<br /><br />Ausschlie&szlig;lich ehrenamtlich - aber wof&uuml;r wird dann Geld gebraucht?<br /><br />Wir bem&uuml;hen uns f&uuml;r jegliche Ausgaben um Sponsering. In manchen F&auml;llen gelingt dies jedoch nicht oder nicht vollst&auml;ndig, z.B. bei Ausgaben f&uuml;r:<br /><br />&nbsp;&nbsp;&nbsp;&nbsp; - Finanzierung von Aktionen und Veranstaltungen (z.B. Fahrtkosten, Materialien, Flyer)<br />&nbsp;&nbsp;&nbsp;&nbsp; <span>- Rechtsberatung<br />&nbsp;&nbsp;&nbsp;&nbsp; - Versicherungen</span><br /><br />Deshalb freuen wir uns &uuml;ber Spenden in jeglicher H&ouml;he</span> &uuml;ber <span><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=CLPZCSCKGNXE4" target="_blank">PayPal (hier klicken)</a></span> oder auf unser Konto:<br /><br /><span>foodsharing e.V.</span><br />IBAN: DE66 4306 0967 4063 8156 00<br />BIC: GENODEM1GLS</p>
<p><span>Da der foodsharing e.V. gemeinn&uuml;tzig ist, k&ouml;nnen wir ab 200&euro; steuerwirksame Zuwendungsbest&auml;tigungen ausstellen. Bitte vermerke in diesem Falle Deine Postanschrift bei der &Uuml;berweisung. Sollte die Quittung nicht innerhalb von acht Wochen bei Dir sein, bitten wir Dich um eine Mail an <a href="mailto:spenden@foodsharing.de" target="_blank">spenden@foodsharing.de</a>. <br /></span></p>
<p></p>
<h3>SPENDEN ALS F&Ouml;RDERMITGLIED</h3>
<p><span>Wenn Du uns regelm&auml;&szlig;ig unterst&uuml;tzen m&ouml;chtest, dann freuen wir uns sehr, wenn Du Dich als F&ouml;rdermiglied eintragen l&auml;sst. Daf&uuml;r einfach das <a href="https://drive.google.com/file/d/15vt_p347ic90WSArEJkT6mogWiwcoRnP/view?usp=sharing" target="_blank">Formular (hier klicken)</a> herunterladen, ausdrucken, ausf&uuml;llen und unterschrieben an die Postadresse des foodsharing e.V. senden: </span></p>
<p><span>foodsharing e.V. <br />Marsiliusstra&szlig;e 36<br />50937 K&ouml;ln </span></p>
<p></p>
<p><strong>SPENDEN &Uuml;BER BILDUNGSSPENDER</strong></p>
<p></p>
<p><a href="https://bildungsspender.de/donation.php?org_id=450937006" target="_blank"> <img src="https://foodsharing.de/images/wallpost/5cc0ad1d8ed37.png" width="180" /></a></p>
<p></p>
<p></p>
<p><a href="https://www.bildungsspender.de/foodsharing/spendenprojekt" target="_blank">Unsere Seite</a>&nbsp;bei den Bildungsspendern</p>
<p></p>
<h3>SPENDEN &Uuml;BER SCHULENGEL.DE</h3>
<p>Du kannst Foodsharing auch unterst&uuml;tzen, indem Du f&uuml;r Deine Online-Eink&auml;ufe in Zukunft einen kleinen Umweg machst. Wenn Du etwas &uuml;ber unsere Seite bei Schulengel.de einkaufst, bekommen wir als Dankesch&ouml;n eine Provision als Spende &uuml;berwiesen. Das klappt bei den meisten Online-Shops.&nbsp;<br /><br />Unsere Seite auf Schulengel.de:<br /><a href="https://www.schulengel.de/einrichtungen/details/5599-foodsharing-ev" target="_blank">https://www.schulengel.de/einrichtungen/details/5599-foodsharing-ev </a><a href="https://www.schulengel.de/einrichtungen/details/5599-foodsharing-ev" target="_blank"><br /><br /></a><strong>Wir z&auml;hlen auf Eure Unterst&uuml;tzung! <br />Viele Gr&uuml;&szlig;e,<br />Euer foodsharing-Team</strong></p>', 'last_mod' => '2020-01-07 21:17:53'],
			['id' => '45', 'name' => 'not_verified_for_bie', 'title' => 'Noch nicht verifiziert', 'body' => '<p></p>
<p>Dein Botschafter muss dich erst Verifizieren, anschlie&szlig;end kannst du das Betriebsverantwortlichen Quiz machen.</p>
<p>Bitte spreche deinen Botschafter auf deine Verifizierung an.&nbsp;</p>', 'last_mod' => '2015-05-04 17:39:44'],
			['id' => '46', 'name' => 'vergangene_Kampagnen', 'title' => 'Vergangene Kampagnen', 'body' => '<h2>Stopp den Lebensmittelm&uuml;ll - Verschwendungsfasten 2019</h2>
<p><img src="https://cloud.foodsharing.network/s/f8rfwTCRJRnEsyz/preview" width="627" /></p>
<p>Jedes Jahr wandern in Deutschland 18 Millionen Tonnen Lebensmittel in den M&uuml;ll. Dabei lie&szlig;e sich mehr als die H&auml;lfte davon einfach vermeiden.<strong> Somit ist Lebensmittelverschwendung eins der unn&ouml;tigsten Probleme unserer Gesellschaft!</strong> Um auf dieses Drama aufmerksam zu machen,&nbsp;laden wir zur Fastenzeit vom 6. M&auml;rz bis 20. April 2019 zum Lebensmittelverschwendungs-Fasten ein. Und wir wollen, dass auch Ern&auml;hrungsministerin Julia Kl&ouml;ckner mitmacht.</p>
<p><strong><a href="https://www.change.org/p/julia-kl%C3%B6ckner-verschwendungsfasten-2019-kein-essen-in-den-m%C3%BCll" target="_blank">Fordert darum jetzt mit uns</a>&nbsp;von Ministerin Julia Kl&ouml;ckner (CDU): &bdquo;Setzen Sie mit uns ein Zeichen und fasten Sie zur Fastenzeit Lebensmittelverschwendung! Schluss mit dem unn&ouml;tigen Wegwerfen von kostbaren Lebensmitteln!</strong> Befassen Sie sich 40 Tage lang intensiv mit diesem Problem. Sie haben lange genug auf das Wohlwollen von Unternehmen gehofft &ndash; nun brauchen wir endlich wirksame Ma&szlig;nahmen, um die Lebensmittelabf&auml;lle bis 2030 zu halbieren.&ldquo;</p>
<p><strong><a href="https://www.change.org/p/julia-kl%C3%B6ckner-verschwendungsfasten-2019-kein-essen-in-den-m%C3%BCll" target="_blank">Hier geht\'s zur Petition.</a></strong> Damit Frau Kl&ouml;ckner direkt abschreiben kann, haben wir einen <a href="https://www.duh.de/fileadmin/user_upload/download/Projektinformation/Kreislaufwirtschaft/Lebensmittelverschwendung/181210_Aktionsplan_foodsharing_DUH_FINAL.pdf" target="_blank"><strong>Aktionsplan</strong> vorbereitet (klicken zum Download).</a></p>
<p>--- --- ---</p>
<div class="csc-header-n1">
<p><strong>Wir fasten Lebensmittelverschwendung. Mach auch Du mit!</strong></p>
</div>
<p>Jedes Jahr wandern in Deutschland 18 Millionen Tonnen Lebensmittel in den M&uuml;ll. Wenn es so weitergeht, wird Deutschland sein Ziel nicht erreichen, Lebensmittelabf&auml;lle bis 2030 um die H&auml;lfte zu reduzieren. Gemeinsam mit der&nbsp;<a href="https://www.duh.de/" target="_blank">Deutschen Umwelthilfe (DUH)</a> zeigen wir Gesellschaft und Politik, dass es auch ohne Lebensmittelverschwendung geht. F&uuml;r exklusive Tipps zum Fasten von unseren Promis kannst Du Dich bei unserer Partnerin, der Deutschen Umwelthilfe, anmelden: <a href="https://www.duh.de/verschwendungsfasten-2019/" target="_blank">https://www.duh.de/verschwendungsfasten-2019/</a></p>
<p></p>
<h2>Leere Tonne</h2>
<p><img src="https://cloud.foodsharing.network/s/FzwTMQAdzbxX26c/preview" width="627" /></p>
<p><span></span><span><span>Superm&auml;rkte und andere H&auml;ndler*innen verursachen 14 % der Lebensmittelverschwendung&sup1;! Was sie dabei ungern zugeben: &Uuml;ber 90 % davon k&ouml;nnte vermieden werden! Da auf freiwilliger Basis bisher sehr wenig passiert ist, fordern wir eine gesetzliche L&ouml;sung nach franz&ouml;sischem und wallonischem Vorbild, um die Verschwendung zu reduzieren. </span><strong>Unsere Forderung kannst du in 30 Sekunden unter <a href="http://www.leeretonne.de/" target="_blank" style="text-decoration: none;">leeretonne.de</a> unterschreiben:</strong></span></p>
<p>&ldquo;(...) Super&shy;m&auml;rkte und andere Lebens&shy;mit&shy;tel&shy;h&auml;nd&shy;ler sind zu ver&shy;pflich&shy;ten, alle unver&shy;k&auml;uf&shy;li&shy;chen, aber noch genie&szlig;&shy;ba&shy;ren Lebens&shy;mit&shy;tel an Orga&shy;ni&shy;sa&shy;tio&shy;nen abzu&shy;ge&shy;ben, die dem Gemein&shy;wohl ver&shy;pflich&shy;tet sind, oder ihren Mit&shy;ar&shy;bei&shy;tern oder Kun&shy;den zu schenken. Was nicht mehr f&uuml;r den mensch&shy;li&shy;chen Ver&shy;zehr geeig&shy;net ist, sollte an Tiere ver&shy;f&uuml;t&shy;tert wer&shy;den. Kom&shy;pos&shy;tie&shy;rung und &bdquo;Ener&shy;ge&shy;ti&shy;sche Ver&shy;wer&shy;tung&ldquo;, wie die Ver&shy;bren&shy;nung oder Ver&shy;g&auml;&shy;rung zu Bio&shy;gas, soll nur m&ouml;g&shy;lich sein, wenn die Lebens&shy;mit&shy;tel weder f&uuml;r Mensch noch Tier geeig&shy;net sind.&rdquo;</p>
<p><span>Die Kampagne wird von foodsharing, </span><a href="https://www.aktion-agrar.de/wegwerfstopp/" target="_blank" style="text-decoration: none;"><span>Aktion Agrar</span></a><span>, der </span><a href="https://www.bundjugend.de/blog/kampagne/leere-tonne/" target="_blank" style="text-decoration: none;"><span>BUNDjugend</span></a><span> und </span><a href="https://slowfoodyouth.de/leere-tonne-kampagne/" target="_blank" style="text-decoration: none;"><span>Slow Food Youth</span></a><span> getragen. Wir konzentrieren uns auf den Handel, weil er eine Scharnierfunktion hat: Einerseits werden dort eine Menge Lebensmittel weggeworfen und andererseits ent&shy;schei&shy;det der Handel durch die Beschaf&shy;fungs&shy;pra&shy;xis mit dar&shy;&uuml;ber, wieviel Gem&uuml;se als unver&shy;k&auml;uf&shy;lich auf den &Auml;ckern ver&shy;bleibt. Durch seine Wer&shy;bung und Kauf&shy;an&shy;reize mit&shy;tels Son&shy;der&shy;an&shy;ge&shy;bo&shy;ten und Gro&szlig;&shy;ge&shy;bin&shy;den steu&shy;ert er, was und wie&shy;viel Kon&shy;su&shy;men&shy;t*in&shy;nen mehr nach Hause tra&shy;gen, als sie eigent&shy;lich ben&ouml;&shy;ti&shy;gen. Das f&uuml;hrt zu Kon&shy;sum&shy;rausch und ver&shy;sch&auml;rft die &Uuml;ber&shy;pro&shy;duk&shy;tion ent&shy;lang der gesam&shy;ten Produktionskette.</span></p>
<p><span>Wenn Du mehr Informationen haben m&ouml;chtest, warum wir aktiv sind, dann besuch uns gerne unter </span><a href="http://www.leeretonne.de/" target="_blank" style="text-decoration: none;"><span>leeretonne.de</span></a><span>. Bei Fragen steht Dir das foodsharing-Team der Leeren Tonne unter </span><a href="mailto:leeretonne@foodsharing.network" target="_blank" style="text-decoration: none;"><span>leeretonne@foodsharing.network</span></a><span> zur Verf&uuml;gung - wir suchen auch noch Mitarbeitende, die sich f&uuml;r den Wegwerfstopp einsetzen wollen und spannende Aktionen mitplanen m&ouml;chten!</span></p>
<p></p>
<p><strong>Was bisher geschah:</strong></p>
<p><span>Im Juli haben wir eine Aktion vor der REWE-Zentrale in K&ouml;ln gestartet. Wir gehen davon aus, dass die meisten Supermarkt-Ketten die B&auml;ckereien im Eingangsbereich vertraglich dazu verpflichten, bis Ladenschluss f&uuml;r volle Regale zu sorgen. Das f&uuml;hrt zu einer Verschwendung aller Backwaren, die dann noch &uuml;brig sind, denn insgesamt landen 25% der Backwaren in Deutschland auf dem M&uuml;ll! Im Juli haben wir eine Demonstration vor der REWE-Zentrale in K&ouml;ln gestartet, da foodsharing einen solchen Knebelvertrag zwischen REWE und einer B&auml;ckerei offen legen konnte. Die Aktion hatte ein gro&szlig;es Medienecho, was uns sehr gefreut hat, denn dadurch haben wir viele Menschen erreicht!</span></p>
<p><img src="http://www.leeretonne.de/wp-content/uploads/2015/07/IMG_7483.jpeg?50fee3" width="400" /></p>
<p><span>Zum Weltern&auml;hrungstag am 16.10. haben wir Bundestagsabgeordnete zu einer gro&szlig;en Tafel aus geretteten Lebensmitteln eingeladen, um erlebbar zu machen, wie viel Essen im M&uuml;ll landet!</span></p>
<p><span><img src="http://www.leeretonne.de/wp-content/uploads/2015/10/Bild_ganz_Leere_tonne.jpg?50fee3" width="400" /></span></p>
<p><span><span>In der Vorweihnachtszeit 2015 sind in &uuml;ber 20 St&auml;dten Weihnachtsm&auml;nner in M&uuml;lltonnen auf die Stra&szlig;e gegangen und haben gegen den Wegwerfwahn protestiert: Vermutlich kennst Du das auch, dass Du bei Abholungen nach Weihnachten ganz viel Weihnachtsschokolade abholst? Sie wird nur aussortiert (und landet ohne foodsharing &amp; Tafel im M&uuml;ll), weil die Saison vorbei ist - und mit ihr wird auch der wertvolle Kakao aus dem globalen S&uuml;den entsorgt. Deswegen wurden Postkarten unterschrieben und an Bundestagsabgeordnete &uuml;bergeben - mit der Forderung nach einem bundesweiten Gesetz!</span></span></p>
<p><span><img src="http://www.leeretonne.de/wp-content/uploads/2015/07/IMG_8110-copy.jpg?50fee3" width="400" /></span></p>
<p><span>Alle weiteren News findest Du </span><a href="http://www.leeretonne.de/news/" target="_blank" style="text-decoration: none;"><span>hier.</span></a></p>
<p><strong>Statement von Valentin Thurn, foodsharing-Gr&uuml;nder und Filmemacher (&lsquo;Taste the Waste&rsquo;)</strong></p>
<p><span>Don\'t waste it &mdash; taste it!</span></p>
<p><span>Es ist ein Skan&shy;dal, dass die H&auml;lfte aller Lebens&shy;mit&shy;tel ver&shy;schwen&shy;det wird. Wir suchen mit ver&shy;ant&shy;wor&shy;tungs&shy;be&shy;wu&szlig;&shy;ten H&auml;nd&shy;lern nach L&ouml;sungen.</span></p>
<p><span><img src="http://www.leeretonne.de/wp-content/uploads/2015/07/leeretonne_Valentin.jpg?50fee3" width="200" /></span></p>
<p><strong>Statement von Raphael Fellmer, Gr&uuml;nder von Lebensmittelretten</strong></p>
<p><span>Ein Ach&shy;tel der Welt&shy;be&shy;v&ouml;l&shy;ke&shy;rung lei&shy;det an Hun&shy;ger. Dabei m&uuml;ss&shy;ten sie es gar nicht, denn die welt&shy;weite Pro&shy;duk&shy;tion von Lebens&shy;mit&shy;teln gen&uuml;gt, um 14 Mil&shy;li&shy;ar&shy;den Men&shy;schen satt zu machen. Und doch wird ein Drit&shy;tel der glo&shy;ba&shy;len Land&shy;wirt&shy;schaft ver&shy;schwen&shy;det. Dem kann und will ich nicht mehr taten&shy;los zuse&shy;hen. Die Ver&shy;ant&shy;wor&shy;tung f&uuml;r die 1,3 Mil&shy;li&shy;ar&shy;den Ton&shy;nen weg&shy;ge&shy;wor&shy;fe&shy;ner Lebens&shy;mit&shy;tel liegt in unse&shy;ren H&auml;nden!</span></p>
<p><span>Des&shy;we&shy;gen setze ich mich seit &uuml;ber 5 Jah&shy;ren ehren&shy;amt&shy;lich gegen die Ver&shy;schwen&shy;dung von Lebens&shy;mit&shy;teln ein und bin dank&shy;bar, dass das Thema end&shy;lich auf den Tisch kommt und es die Peti&shy;tion Leere Tonne gibt. Es ist ein Zei&shy;chen f&uuml;r einen klei&shy;nen, aber bedeu&shy;ten&shy;den Schritt auf dem Weg zur&uuml;ck zur Wert&shy;sch&auml;t&shy;zung der Lebensmittel.</span></p>
<p><span>Ich bin &uuml;ber&shy;zeugt, dass die Aktion Leere Tonne ein wich&shy;ti&shy;ger Bestand&shy;teil sein wird, um Lebens&shy;mit&shy;tel in Zukunft auf geson&shy;derte Weise zu ent&shy;sor&shy;gen. N&auml;m&shy;lich wo sie hin&shy;ge&shy;h&ouml;&shy;ren: in unse&shy;re M&auml;gen.</span></p>
<p><img src="http://www.leeretonne.de/wp-content/uploads/2015/07/Raphael_testimonial.jpg?50fee3" width="200" /></p>
<p></p>
<p>Quelle:</p>
<p>&sup1; <a href="https://www.wwf.de/fileadmin/fm-wwf/Publikationen-PDF/WWF_Studie_Das_grosse_Wegschmeissen.pdf" target="_blank" style="text-decoration: none;">https://www.wwf.de/fileadmin/fm-wwf/Publikationen-PDF/WWF_Studie_Das_grosse_Wegschmeissen.pdf</a><span> </span></p>', 'last_mod' => '2019-12-05 12:34:42'],
			['id' => '47', 'name' => 'foodsharingschweiz-t', 'title' => 'wird ignoriert', 'body' => '<div class="campaign topbarpadding">
<div class="campaignimg"><img src="/img/fsgabelgwrgbklein.png" /></div>
<div class="campaigntext">
<div class="field">
<h2>Gedanken zu Corona</h2>
<div class="ui-widget ui-widget-content margin-bottom ui-padding">
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing#Einschr&auml;nkung_sozialer_Kontakte" target="_blank" class="ui-button"><strong>&Uuml;bernehmt Verantwortung</strong> f&uuml;r euch selbst und eure Mitmenschen &ndash; bleibt, wenn m&ouml;glich zu Hause, trefft Menschen nur, wenn es notwendig ist </a></div>
</div>
</div>
<div class="clear"></div>
</div>
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing" target="_blank" class="ui-button">Haltet unbedingt die <strong>Hygieneregeln</strong> ein </a></div>
</div>
</div>
<div class="clear"></div>
</div>
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing#Solidarit&auml;t" target="_blank" class="ui-button"><strong>Seid solidarisch</strong> - informiert euch, wie ihr wen wo unterst&uuml;tzen k&ouml;nnt </a></div>
</div>
</div>
<div class="clear"></div>
</div>
</div>
</div>
</div>
</div>', 'last_mod' => '2020-03-19 13:58:54'],
			['id' => '48', 'name' => 'beta-foodsharing-mai', 'title' => 'Beta foodsharing Startseite', 'body' => '<div class="campaign topbarpadding">
<div class="campaignimg"><img src="/img/fsgabelgwrgbklein.png" /></div>
<div class="campaigntext">
<div class="field">
<h2>Gedanken zu Corona</h2>
<div class="ui-widget ui-widget-content margin-bottom ui-padding">
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing#Einschr&auml;nkung_sozialer_Kontakte" target="_blank" class="ui-button"><strong>&Uuml;bernehmt Verantwortung</strong> f&uuml;r euch selbst und eure Mitmenschen &ndash; bleibt, wenn m&ouml;glich zu Hause, trefft Menschen nur, wenn es notwendig ist </a></div>
</div>
</div>
<div class="clear"></div>
</div>
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing" target="_blank" class="ui-button">Haltet unbedingt die <strong>Hygieneregeln</strong> ein </a></div>
</div>
</div>
<div class="clear"></div>
</div>
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing#Solidarit&auml;t" target="_blank" class="ui-button"><strong>Seid solidarisch</strong> - informiert euch, wie ihr wen wo unterst&uuml;tzen k&ouml;nnt </a></div>
</div>
</div>
<div class="clear"></div>
</div>
</div>
</div>
<h2>Petition</h2>
<div class="ui-widget ui-widget-content margin-bottom ui-padding">
<div class="post event">
<div class="container activity_feed_content">
<div class="activity_feed_content_text">
<div class="activity_feed_content_info"><a href="https://www.change.org/lebensmittel-retten" target="_blank" class="ui-button"> &bdquo;Lebensmittelrettung muss einfacher werden&ldquo; - Hier geht es zur Petition von foodsharing und Deutscher Umwelthilfe</a></div>
</div>
</div>
<div class="clear"></div>
</div>
</div>
<br />
<h4>Dies ist die Beta-Test-Version von foodsharing</h4>
<h5>Wir unterst&uuml;tzen das Entwicklerteam, suchen gemeinsam <a href="http://devdocs.foodsharing.network/it-tasks.html" target="_blank">Programmierer</a> - sowie HelferInnen und melden Fehler konzentriert und im Detail an <a href="mailto:it@foodsharing.network?subject=BETA-Fehler" target="_blank">it@foodsharing.network</a></h5>
</div>
</div>', 'last_mod' => '2020-04-09 12:46:00'],
			['id' => '49', 'name' => 'aktion-fairteiler', 'title' => 'Rette die Fair-Teiler!', 'body' => '<h2><img src="https://media.foodsharing.de/files/Upload%20sonstiges/1.png" width="400" /></h2>
<p>Die Berliner Lebensmittel&auml;mter m&ouml;chten <a href="https://www.berliner-zeitung.de/berlin/petition-fuer--fairteiler--gegruendet-foodsharing-initiative-wehrt-sich-gegen-auflagen-der-lebensmittelaufsicht,10809148,33732024.html" target="_blank"><span>Auflagen f&uuml;r foodsharing Fair-Teiler vorschreiben</span></a><span>, weil sie die Fair-Teiler als Lebensmittelbetrieb einstufen. Das w&uuml;rde uns dazu zwingen, viele Fair-Teiler in Berlin zu schlie&szlig;en! Allerdings betreibt foodsharing inzwischen &uuml;ber 350 Fair-Teiler - und an keinem anderen Ort wird der Einsatz von tausenden Ehrenamtlichen gegen die Lebensmittelverschwendung so torpediert! Deswegen fordern wir eine realistische Einsch&auml;tzung der Sachlage durch die Beh&ouml;rden und einen gut ausgearbeiteten Leitfaden f&uuml;r Fair-Teiler vom Berliner Senat. </span><strong>Wenn Du uns unterst&uuml;tzen und die Fair-Teiler retten m&ouml;chtest</strong><span><span><strong>,</strong> dann <span> </span><a href="https://weact.campact.de/p/fair-teiler-retten" target="_blank" style="text-decoration: none;"><span>unterschreibe unsere Petition</span></a><span> und</span>&nbsp;<a href="mailto:Eva-maria.Milke@ba-fk.berlin.de,Torsten.Kuehne@ba-pankow.berlin.de,sabine.toepfer-kataw@senjust.berlin.de,ordvetleb1@ba-pankow.berlin.de,staatssekretaerin@senjust.berlin.de?bcc=petition@lebensmittelretten.de&amp;subject=Beschwerde&amp;body=Sehr%20geehrte%20Frau%20Staatssekret%C3%A4rin%20Toepfer-Kataw%2C%20Herr%20Stadtrat%20K%C3%BChne%2C%20Herr%20Dr.%20Zengerling%20und%20Frau%20Milke%2C%0A%0A%0A" target="_blank">schicke eine Mail an die verantwortlichen Personen</a> (siehe Vorlage weiter unten), damit deutlich wird, wie viele Menschen den Protest unterst&uuml;tzen!</span></span></p>
<p>A) Unsere Forderungen <br /> B) Was passierte <br /> C) Was die Beh&ouml;rden von uns fordern<br /> D) Warum wir Fair-Teiler brauchen <br />E) Wie kann ich unterst&uuml;tzen? <br />F) Presseecho <br />G) Mail-Vorlage</p>
<h2><strong>A) Unsere Forderungen</strong></h2>
<ul>
<li><span>Das Lebensmittelamt in Berlin soll Fair-Teiler als privaten &Uuml;bergabeort und nicht als Lebensmittelbetrieb einstufen - wie andere St&auml;dte auch!</span></li>
<li><span>Der Senat soll einen Leitfaden mit foodsharing zum Betreiben von Fair-Teilern erarbeiten. Schon jetzt hat foodsharing ausgereifte Regelungen - wenn diese mit dem Senat verfeinert werden, hat das Lebensmittelamt eine Richtlinie, an die es sich halten kann. Dar&uuml;ber hinaus m&ouml;ge sich der Senat auf EU-Ebene f&uuml;r eine eindeutigere Gesetzeslage einsetzen, so dass Fair-Teiler ohne Zweifel europaweit als private &Uuml;bergabeorte gelten!</span></li>
</ul>
<h2><strong>B) Was passierte</strong></h2>
<p><span>Wir sind sehr entt&auml;uscht &uuml;ber die Vorgehensweise vom Lebensmittelaufsichtsamt und dem Senat! Bei einem pers&ouml;nlichen Treffen am 7.1.2016 im Amt haben wir gemeinsam mit Herrn Dr. Zengerling und Frau Milke&nbsp;<span>(Lebensmittelaufsicht)</span> Auflagen ausgearbeitet. Diese Absprachen wurden in den Auflagen ignoriert! Dar&uuml;ber hinaus hat Frau Staatssekret&auml;rin T&ouml;pfer-Kataw </span><a href="https://www.morgenpost.de/berlin/article206992037/Oeffentliche-Kuehlschraenke-koennten-bald-verboten-werden.html" target="_blank"><span>nur die Presse</span></a><span> &uuml;ber die neuen Auflagen informiert, ohne uns in Kenntnis zu setzen - mit einem Monat Versp&auml;tung wurden uns die Auflagen offiziell zugesandt!<br /><br /> Das Lebensmittelaufsichtsamt in Berlin-Pankow unter der Leitung von Herrn Dr. Zengerling hat uns mitgeteilt, dass Bu&szlig;gelder von bis zu 50.000&euro; gefordert werden k&ouml;nnen, wenn Auflagen nicht erf&uuml;llt w&uuml;rden. Bis jetzt wurden bereits mehrere Fair-Teiler geschlossen. Herr Dr. Zengerling sieht in den Fair-Teilern, so wie wir sie bisher betreiben, ein hohes gesundheitliches Risiko und m&ouml;chte sich selber vor rechtlichen Konsequenzen absichern, da er bei Schadensf&auml;llen nicht wegen Fahrl&auml;ssigkeit belangt werden m&ouml;chte. Wir k&ouml;nnen diese Sorge nicht nachvollziehen: Die </span><a href="https://wiki.foodsharing.de/images/f/fd/Fairteiler_regeln.pdf" target="_blank" style="text-decoration: none;"><span>foodsharing-Regelungen</span></a><span> wurden mit mehreren f&uuml;hrenden LebensmittelkontrolleurInnen ausgearbeitet und schr&auml;nken die gesundheitlichen Risiken so weit es geht ein, so dass das nur ein kleines, unvermeidbares Restrisiko bleibt! Dar&uuml;ber hinaus konnten die Giftkeksangriffe (die nichts mit unseren Fair-Teilern zu tun hatten) in Berlin trotz strenger Richtlinien nicht durch das Lebensmittelamt vermieden werden und werden durch eine R&uuml;ckverfolgbarkeit nicht ausgeschlossen.</span></p>
<h2><strong>C) Was die Beh&ouml;rden von uns fordern</strong></h2>
<ul>
<li>Fair-Teiler m&uuml;ssen unter st&auml;ndiger Aufsicht einer verantwortlichen Person stehen</li>
<li>Nur diese Person darf Lebensmittel in den Fair-Teiler legen und muss die Lebensmittel vorher &uuml;berpr&uuml;fen und kennzeichnen</li>
<li>Wir m&uuml;ssen eine R&uuml;ckverfolgbarkeit gew&auml;hrleisten, also eine Liste f&uuml;hren, wer? wann? was? f&uuml;r Lebensmittel durch die Fair-Teiler in den Verkehr gebracht werden.</li>
</ul>
<p><span>Diese Auflagen sind f&uuml;r uns im privaten, ehrenamtlichen Bereich - wie bei Fair-Teilern -<span> nicht umsetzbar. Sie</span>&nbsp;w&uuml;rden daf&uuml;r sorgen, dass Fair-Teiler geschlossen werden m&uuml;ssen.<br />Deswegen setzen wir uns daf&uuml;r ein, dass Fair-Teiler weiterhin bestehen k&ouml;nnen!</span></p>
<p><span>Erst Ende Februar wurden diese Auflagen offiziell <a href="https://www.berlin.de/sen/verbraucherschutz/aufgaben/gesundheitlicher-verbraucherschutz/lebensmittelretter/artikel.445869.php" target="_blank">von der Senatsverwaltung ver&ouml;ffentlicht</a>. Die Argumentation beruft sich auf eine Verordnung, in der es hei&szlig;t: &bdquo;Lebensmittelunternehmen sind alle Unternehmen, (...) die eine mit der Produktion, der Verarbeitung und dem Vertrieb von Lebensmitteln zusammenh&auml;ngende T&auml;tigkeit ausf&uuml;hren.&ldquo; - Wir sind kein Unternehmen, Foodsaver*innen nicht einmal Mitglied des Vereins und deswegen auch kein Lebensmittelunternehmen! Deswegen halten wir die Argumentation f&uuml;r absolut hinf&auml;llig.</span></p>
<h2><span><strong>D) Warum wir Fair-Teiler brauchen</strong></span></h2>
<p><span>Die foodsharing-&Uuml;bergabeorte tragen zur Reduzierung der Lebensmittelverschwendung in Privathaushalten bei! Die Bundesregierung hat 2012 beschlossen, die Verschwendung bis 2020 zu halbieren und eine teure Kampagne adressiert an Privatpersonen gestartet: &ldquo;Zu gut f&uuml;r die Tonne&rdquo;. Jetzt stellen sich Verantwortliche aus den eigenen Reihen der CDU gegen diesen Beschluss, indem sie Fair-Teiler als innovativen Weg nicht mehr erm&ouml;glichen!<br />Dar&uuml;ber hinaus sind Fair-Teiler ein sozialer Treffpunkt im Kiez und erm&ouml;glichen Bed&uuml;rftigen, ohne Stigmatisierung und Diskriminierung Essen zu beziehen - wie allen anderen auch!<br />Wir m&ouml;chten mit Fair-Teilern einen Beitrag f&uuml;r eine zukunftsf&auml;hige Welt schaffen, indem wir uns f&uuml;r Ressourcenschonung und ein solidarisches Miteinander engagieren - und deswegen k&auml;mpfen wir daf&uuml;r, dass Fair-Teiler weiterhin bestehen k&ouml;nnen!</span></p>
<h2><strong>E) Wie kann ich unterst&uuml;tzen?</strong></h2>
<p><span>Wir protestieren gegen diese Einsch&auml;tzungen. Um deutlich zu machen, wie viele wir sind,&nbsp;<span>haben wir </span><a href="https://weact.campact.de/p/fair-teiler-retten" target="_blank" style="text-decoration: none;"><span>eine Petition gestartet</span></a><span> und senden <a href="mailto:Eva-maria.Milke@ba-fk.berlin.de,Torsten.Kuehne@ba-pankow.berlin.de,sabine.toepfer-kataw@senjust.berlin.de,ordvetleb1@ba-pankow.berlin.de,staatssekretaerin@senjust.berlin.de?bcc=petition@lebensmittelretten.de&amp;subject=Beschwerde&amp;body=Sehr%20geehrte%20Frau%20Staatssekret%C3%A4rin%20Toepfer-Kataw%2C%20Herr%20Stadtrat%20K%C3%BChne%2C%20Herr%20Dr.%20Zengerling%20und%20Frau%20Milke%2C%0A%0A%0A" target="_blank">E-Mails an die Verantwortlichen!</a></span><a href="mailto:Eva-maria.Milke@ba-fk.berlin.de,Torsten.Kuehne@ba-pankow.berlin.de,sabine.toepfer-kataw@senjust.berlin.de,ordvetleb1@ba-pankow.berlin.de,staatssekretaerin@senjust.berlin.de?bcc=petition@lebensmittelretten.de&amp;subject=Beschwerde&amp;body=Sehr%20geehrte%20Frau%20Staatssekret%C3%A4rin%20Toepfer-Kataw%2C%20Herr%20Stadtrat%20K%C3%BChne%2C%20Herr%20Dr.%20Zengerling%20und%20Frau%20Milke%2C%0A%0A%0A" target="_blank"> Die Vorlage</a> findest Du weiter unten - ver&auml;ndere sie gerne und signiere mit Deinem Namen!&nbsp;<br />Bitte teile den Aufruf zur Rettung der Fair-Teiler! </span></p>
<p><span>Dank Dir f&uuml;r Deine Unterst&uuml;tzung!<br />Herzlich, Dein foodsharing Team <br />(Aktive aus Berlin, dem Vorstand und Orgateam - bei Fragen wende Dich gerne an <a href="mailto:fairteiler.berlin@lebensmittelretten.de" target="_blank">fairteiler.berlin@lebensmittelretten.de</a>)</span></p>
<p></p>
<p><span>Weiterf&uuml;hrende Infos stehen <a href="https://wiki.foodsharing.de/Fair-Teiler#Aktuelles_-_Probleme_in_Berlin" target="_blank">auch im Wiki</a>.<br /></span><span>--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- </span></p>
<h1>F) Presseecho</h1>
<p><a href="https://media.foodsharing.de/files/Upload%20sonstiges/5.2.2016%20Pressemitteilung%20foodsharing%20-%20Fair-Teiler.pdf" target="_blank">Pressemitteilung von foodsharing</a>, 05-Feb</p>
<p>07-Feb -&nbsp;<a href="https://www.berliner-zeitung.de/berlin/petition-fuer--fairteiler--gegruendet-foodsharing-initiative-wehrt-sich-gegen-auflagen-der-lebensmittelaufsicht,10809148,33732024.html" target="_blank">Berliner Zeitung</a> <br />03-Feb - <a href="https://news.utopia.de/behoerden-wollen-foodsharing-stoppen-11863/" target="_blank">Utopia</a> <br />01-Feb - <a href="https://www.morgenpost.de/berlin/article206992037/Oeffentliche-Kuehlschraenke-koennten-bald-verboten-werden.html" target="_blank">Morgenpost </a><br /><span>--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- </span></p>
<h1><strong>G) Mail-Vorlage</strong></h1>
<p><strong>Achtung</strong>: Die Mail wird inzwischen von den Beh&ouml;rden als Spam eingestuft und gefiltert! Aber es&nbsp;hilft, ein paar S&auml;tze und den Betreff zu &auml;ndern und die Empf&auml;nger*innen in den BCC zu sortieren!<br /><span>Bitte erg&auml;nze Deinen Namen am Ende der Mail und &auml;ndere die Schreibeweise von "Fair-Teiler" und "foodsharing", sind auch schon hier in der Vorlage absichtlich falsch geschrieben weil die&nbsp;<span>Mail inzwischen als Spam erkannt</span>&nbsp;wird!<br />Also werdet bitte kreativ (z.B. "foo dsharing", "fodsharing", "fooodsharing", "fudsharing", "fo&ograve;dsharing" etc. hauptsache eigene Kreationenen, sonst fischen die wieder die Mails raus und wir wollen ja, dass die Beschwerdemails ankommen!<br />Die beste Idee ist wahrscheinlich, eine ganz pers&ouml;nliche&nbsp;Mail zu verfassen, ohne viele S&auml;tze. Das bringt am meisten, denn sie m&uuml;ssen die Mail lesen und k&ouml;nnen sie nicht rausfiltern! Aber auch hier zur Sicherheit foodsharing und Fair-Teiler anders schreiben. Daf&uuml;r hier eine <a href="mailto:Eva-maria.Milke@ba-fk.berlin.de,Torsten.Kuehne@ba-pankow.berlin.de,sabine.toepfer-kataw@senjust.berlin.de,ordvetleb1@ba-pankow.berlin.de,staatssekretaerin@senjust.berlin.de?bcc=petition@lebensmittelretten.de&amp;subject=Beschwerde&amp;body=Sehr%20geehrte%20Frau%20Staatssekret%C3%A4rin%20Toepfer-Kataw%2C%20Herr%20Stadtrat%20K%C3%BChne%2C%20Herr%20Dr.%20Zengerling%20und%20Frau%20Milke%2C%0A%0A%0A" target="_blank">Mailvorlage</a>&nbsp;ohne Inhalt, aber mit Betreff, Adressat und erster Zeile: Sehr geehrte ... )</span></p>
<p></p>
<h3>Hier die Mailvorlage noch mit Inhalt, als Inspiration (foodsharing und Fair-Teiler sind absichtlich falsch geschrieben)</h3>
<p></p>
<h3><strong>Betreff</strong><span>: Beschwerde</span></h3>
<p><strong>An</strong><span>: </span><a href="mailto:Eva-maria.Milke@ba-fk.berlin.de" target="_blank" style="text-decoration: none;"><span>Eva-maria.Milke@ba-fk.berlin.de,</span></a><span></span><a href="mailto:Torsten.Kuehne@ba-pankow.berlin.de" target="_blank" style="text-decoration: none;"><span>Torsten.Kuehne@ba-pankow.berlin.de,</span></a><span></span><a href="mailto:sabine.toepfer-kataw@senjust.berlin.de" target="_blank" style="text-decoration: none;"><span>sabine.toepfer-kataw@senjust.berlin.de,</span></a><span></span><a href="mailto:ordvetleb1@ba-pankow.berlin.de" target="_blank" style="text-decoration: none;"><span>ordvetleb1@ba-pankow.berlin.de</span></a>,<a href="mailto:staatssekretaerin@senjust.berlin.de" target="_blank">staatssekretaerin@senjust.berlin.de</a></p>
<p><strong>BCC</strong><span>: </span><a href="mailto:petition@lebensmittelretten.de" target="_blank" style="text-decoration: none;"><span>petition@lebensmittelretten.de</span></a> (Blindkopie, damit wir z&auml;hlen k&ouml;nnen, wie viele Mails versandt wurden)</p>
<p><span>Sehr geehrte Frau Staatssekret&auml;rin Toepfer-Kataw, Herr Stadtrat K&uuml;hne, Herr Dr. Zengerling und Frau Milke,</span></p>
<p></p>
<p><br /><span>die Einstufung der Fai r Teiler als Lebensmittelbetriebe wird dazu f&uuml;hren, dass viele Fai r Teiler in Berlin geschlossen werden m&uuml;ssen. Diese drastische Vorgehensweise ist einmalig in Deutschland. Das finde ich unverantwortlich und fordere Sie deswegen zu einer realistischeren Beurteilung der Sachlage auf!</span><br /><span>Fai r Teiler sind private &Uuml;bergabeorte von Lebensmitteln und gleichzeitig ein sozialer Beitrag zur Reduktion der Lebensmittelverschwendung.</span><span></span><span>&nbsp;Sie f&ouml;rdern das Bewusstsein f&uuml;r diese Thematik in der Bev&ouml;lkerung und fungieren als soziale Treffpunkte in der Nachbarschaft. Fodsharing Deutschland hat dabei eine innovative Vorreiterrolle eingenommen. Mittlerweile wurde diese Idee u.a. von L&auml;ndern wie der Schweiz, Brasilien, Spanien und S&uuml;dkorea &uuml;bernommen. In &Ouml;sterreich werden Fai r Teiler sogar durch das Lebensmittelministerium unterst&uuml;tzt und gef&ouml;rdert. Dar&uuml;ber hinaus erreichen Nahrungsmittel durch Fai r Teiler sehr viele Menschen und direkt auch Bed&uuml;rftige, die sich ohne Stigmatisierung bedienen k&ouml;nnen. Ich halte die Regelungen von fodsharing f&uuml;r die Fai r Teiler f&uuml;r absolut ausreichend und die gesundheitlichen Gefahren f&uuml;r gering, was die Tatsache belegt, dass es bisher keinerlei Vorf&auml;lle an den &uuml;ber 350 Fai r Teiler gab.</span><br /><br /><span>Herr Dr. Zengerling und Frau Milke, durch Ihre ungerechtfertigte Einstufung von Fai r Teiler als Lebensmittelunternehmen und die damit verbundenen Auflagen ist eine Fortf&uuml;hrung dieser innovativen Idee in Berlin nicht mehr m&ouml;glich. Ich fordere Sie deswegen auf, Fai r Teiler so zu bewerten, wie es andere Lebensmittel&auml;mter auch tun: als privaten &Uuml;bergabeort f&uuml;r Lebensmittel!</span><br /><br /><span>Auf Bundesebene hat die Regierung 2012 unter der CDU/CSU beschlossen, den Lebensmittelm&uuml;ll bis 2020 um die H&auml;lfte zu reduzieren. Um dieses Ziel in Privathaushalten zu erreichen, wurde die Informationskampagne &ldquo;Zu gut f&uuml;r die Tonne&rdquo; ins Leben gerufen und jeder B&uuml;rger/in aufgerufen, aktiv in diesem Sinne zu wirken.</span><br /><br /><span>Mit Ihren bisherigen Forderungen stellen Sie sich gegen die erkl&auml;rte Politik der Bundesregierung, Herr Stadtrat K&uuml;hne! Es entt&auml;uscht mich sehr, dass Sie zivilgesellschaftliches Engagement gegen Essensverschwendung und f&uuml;r die w&uuml;rdige Versorgung Bed&uuml;rftiger blockieren.</span><br /><br /><span>Frau Staatssekret&auml;rin Toepfer-Kataw und Herr Stadtrat K&uuml;hne, unterst&uuml;tzen Sie das Lebensmittelamt, indem Sie gemeinsam mit fodsharing einen Leitfaden f&uuml;r die Fai r Teiler erstellen, damit diese einmalige Initiative von tausenden Freiwilligen weiter bestehen kann!</span><br /><br /><span>Mit freundlichen Gr&uuml;&szlig;en</span></p>', 'last_mod' => '2019-03-04 18:15:48'],
			['id' => '51', 'name' => 'broadcast', 'title' => 'broadcast', 'body' => '<p><span>Gedanken und Hinweise zur aktuellen Situation:&nbsp;<a href="https://foodsharing.de/?page=bezirk&amp;bid=741&amp;sub=forum&amp;tid=103467" target="_blank">Europa-Forum (wenn Du eingeloggt bist)</a>&nbsp;und&nbsp;</span><a href="https://wiki.foodsharing.de/FAQ_zu_Corona_und_foodsharing" target="_blank">FAQ zu Corona und foodsharing</a></p>', 'last_mod' => '2020-05-23 18:28:59'],
			['id' => '52', 'name' => 'com_germany', 'title' => 'Hier findet ihr foodsharing-Gemeinschaften', 'body' => '<h2>foodsharing-Bezirke</h2>
<p>Hinweis: Mit Strg+F kann die Liste in vielen Browsern nach L&auml;ndern, St&auml;dten, Bundesl&auml;ndern oder Stadtteilen durchsucht werden.</p>
<p></p>
<h1><strong>Deutschland <br /></strong></h1>
<p></p>
<h2><strong>Baden-W&uuml;rtenberg <a href="mailto:baden.wuerttemberg@foodsharing.network" target="_blank">baden.wuerttemberg[at]foodsharing.network</a></strong></h2>
<ul>
<li>Albstadt Region &nbsp;<a href="mailto:albstadt@foodsharing.network" target="_blank">albstadt[at]foodsharing.network</a></li>
<li>Balingen-Hechingen <a href="mailto:balingen.hechingen@foodsharing.network" target="_blank">balingen.hechingen[at]foodsharing.network</a></li>
<li>Biberach a. d. Ri&szlig; &nbsp;<a href="mailto:biberach.riss@foodsharing.network" target="_blank">biberach.riss[at]foodsharing.network</a></li>
<li>B&ouml;blingen Landkreis &nbsp;<a href="mailto:boeblingen.landkreis@foodsharing.network" target="_blank">boeblingen.landkreis[at]foodsharing.network</a></li>
<li>Bodensee-Region
<ul>
<li>Konstanz <a href="mailto:konstanz@foodsharing.network" target="_blank">konstanz[at]foodsharing.network</a></li>
<li>Radolfzell <a href="mailto:radolfzell@foodsharing.network" target="_blank">radolfzell[at]foodsharing.network</a></li>
<li>Ravensburg und Umgebung <a href="mailto:ravensburg@foodsharing.network" target="_blank">ravensburg[at]foodsharing.network</a></li>
<li>Singen (Hohentwiel) und Region <a href="mailto:singen@foodsharing.network" target="_blank">singen[at]foodsharing.network</a></li>
<li>&Uuml;berlingen <a href="mailto:ueberlingen@foodsharing.network" target="_blank">ueberlingen[at]foodsharing.network</a></li>
</ul>
</li>
<li>Bruchsal&nbsp;<a href="mailto:bruchsal@foodsharing.network" target="_blank">bruchsal[at]foodsharing.network</a></li>
<li>Calw <a href="mailto:calw@foodsharing.network" target="_blank">calw[at]foodsharing.network</a></li>
<li>Crailsheim&nbsp;<a href="mailto:crailsheim@foodsharing.network" target="_blank">crailsheim[at]foodsharing.network</a></li>
<li>Emmendingen <a href="mailto:emmendingen.lkr@foodsharing.network" target="_blank">emmendingen.lkr[at]foodsharing.network</a></li>
<li>Esslingen Landkreis
<ul>
<li>Esslingen <a href="mailto:esslingen@foodsharing.network" target="_blank">esslingen[at]foodsharing.network</a></li>
<li>Kirchheim unter Teck <a href="mailto:kirchheim.teck@foodsharing.network" target="_blank">kirchheim.teck[at]foodsharing.network</a></li>
</ul>
</li>
<li>Ettlingen <a href="mailto:ettlingen@foodsharing.network" target="_blank">ettlingen[at]foodsharing.network</a>&nbsp; &nbsp; &nbsp; &nbsp;</li>
<li>Fildern-N&uuml;rtingen <a href="mailto:fildern-nuertingen@foodsharing.network" target="_blank">fildern-nuertingen[at]foodsharing.network</a></li>
<li>Freiburg i. Brsg. <a href="mailto:freiburg@foodsharing.network" target="_blank">freiburg[at]foodsharing.network</a></li>
<li>Freudenstadt&nbsp;<a href="mailto:freudenstadt@foodsharing.network" target="_blank">freudenstadt[at]foodsharing.network</a></li>
<li>G&ouml;ppingen Landkreis <a href="mailto:goeppingen@foodsharing.network" target="_blank">goeppingen[at]foodsharing.network</a></li>
<li>Heidelberg und Region <a href="mailto:region.heidelberg@foodsharing.network" target="_blank">region.heidelberg[at]foodsharing.network</a></li>
<li>Heilbronn <a href="mailto:heilbronn@foodsharing.network" target="_blank">heilbronn[at]foodsharing.network</a></li>
<li>Horb am Neckar&nbsp;<a href="mailto:horb-am-neckar@foodsharing.network" target="_blank">horb-am-neckar[at]foodsharing.network</a></li>
<li>Karlsruhe <a href="mailto:karlsruhe@foodsharing.network" target="_blank">karlsruhe[at]foodsharing.network</a></li>
<li>Leutkirch (Allg&auml;u) und Region <a href="mailto:leutkirch@foodsharing.network" target="_blank">leutkirch[at]foodsharing.network</a></li>
<li>L&ouml;rrach <a href="mailto:loerrach@foodsharing.network" target="_blank">loerrach[at]foodsharing.network</a></li>
<li>Ludwigsburg und Region <a href="mailto:ludwigsburg@foodsharing.network" target="_blank">ludwigsburg[at]foodsharing.network</a></li>
<li>Mannheim <a href="mailto:mannheim@foodsharing.network" target="_blank">mannheim[at]foodsharing.network</a></li>
<li>Markgr&auml;flerland&nbsp;<a href="mailto:markgraeflerland@foodsharing.network" target="_blank">markgraeflerland[at]foodsharing.network</a></li>
<li>Mosbach &amp; Region <a href="mailto:mosbach@foodsharing.network" target="_blank">mosbach[at]foodsharing.network</a></li>
<li>Neuhausen (Enzkreis) und Region <a href="mailto:neuhausen-enzkreis@foodsharing.network" target="_blank">neuhausen-enzkreis[at]foodsharing.network</a></li>
<li>&Ouml;hringen&nbsp;<a href="mailto:oehringen@foodsharing.network" target="_blank">oehringen[at]foodsharing.network</a></li>
<li>Ortenaukreis <a href="mailto:ortenaukreis@foodsharing.network" target="_blank">ortenaukreis[at]foodsharing.network</a></li>
<li>Pforzheim <a href="mailto:pforzheim@foodsharing.network" target="_blank">pforzheim[at]foodsharing.network</a></li>
<li>Rastatt/Baden-Baden&nbsp;<a href="mailto:rastatt.baden-baden@foodsharing.network" target="_blank">rastatt.baden-baden[at]foodsharing.network</a></li>
<li>Rems-Murr-Kreis <a href="mailto:rems-murr-kreis@foodsharing.network" target="_blank">rems-murr-kreis[at]foodsharing.network</a></li>
<li>Reutlingen <a href="mailto:reutlingen@foodsharing.network" target="_blank">reutlingen[at]foodsharing.network</a></li>
<li>Rottenburg am Neckar <a href="mailto:rottenburg@foodsharing.network" target="_blank">rottenburg[at]foodsharing.network</a></li>
<li>Rottweil <a href="mailto:rottweil@foodsharing.network" target="_blank">rottweil[at]foodsharing.network</a></li>
<li>Schw&auml;bisch Hall &amp; Region <a href="mailto:schwaebisch-hall@foodsharing.network" target="_blank">schwaebisch-hall[at]foodsharing.network</a></li>
<li>Schwetzingen und Hockenheim <a href="mailto:schwetzingen.hockenheim@foodsharing.network" target="_blank">schwetzingen.hockenheim[at]foodsharing.network</a></li>
<li>Sigmaringen &amp; Region <a href="mailto:sigmaringen@foodsharing.network" target="_blank">sigmaringen[at]foodsharing.network</a></li>
<li>Steinlachtal <a href="mailto:steinlachtal@foodsharing.network" target="_blank">steinlachtal[at]foodsharing.network</a></li>
<li>Stockach&nbsp;<a href="mailto:stockach2@foodsharing.network" target="_blank">stockach2[at]foodsharing.network</a></li>
<li>Stuttgart <a href="mailto:stuttgart@foodsharing.network" target="_blank">stuttgart[at]foodsharing.network</a></li>
<li>T&uuml;bingen <a href="mailto:tuebingen@foodsharing.network" target="_blank">tuebingen[at]foodsharing.network</a></li>
<li>Ulm und anliegende Regionen <a href="mailto:ulm@foodsharing.network" target="_blank">ulm[at]foodsharing.network</a></li>
<li>Villingen-Schwenningen <a href="mailto:villingen-schwenningen@foodsharing.network" target="_blank">villingen-schwenningen[at]foodsharing.network</a>&nbsp;</li>
<li>Waldshut-Tiengen und Umgebung <a href="mailto:waldshut-tiengen@foodsharing.network" target="_blank">waldshut-tiengen[at]foodsharing.network</a>&nbsp; &nbsp;</li>
<li>Wendlingen <a href="mailto:wendlingen@foodsharing.network" target="_blank">wendlingen[at]foodsharing.network</a>&nbsp;</li>
</ul>
<h2></h2>
<h2><strong>Bayern <a href="mailto:bayern@foodsharing.network" target="_blank">bayern[at]foodsharing.network</a></strong></h2>
<ul>
<li>Ammersee <a href="mailto:ammersee@foodsharing.network" target="_blank">ammersee[at]foodsharing.network</a></li>
<li>Aschaffenburg <a href="mailto:aschaffenburg@foodsharing.network" target="_blank">aschaffenburg[at]foodsharing.network</a></li>
<li>Augsburg <a href="mailto:Augsburg@foodsharing.network" target="_blank">Augsburg[at]foodsharing.network</a>
<ul>
<li>K&ouml;nigsbrunn <a href="mailto:koenigsbrunn@foodsharing.network" target="_blank">koenigsbrunn[at]foodsharing.network</a></li>
<li>Mering <a href="mailto:mering@foodsharing.network" target="_blank">mering[at]foodsharing.network</a></li>
</ul>
</li>
<li>Bamberg <a href="mailto:bamberg@foodsharing.network" target="_blank">bamberg[at]foodsharing.network</a></li>
<li>Bayreuth <a href="mailto:bayreuth@foodsharing.network" target="_blank">bayreuth[at]foodsharing.network</a></li>
<li>Bayreuth-Land&nbsp;<a href="mailto:bayreuth.land@foodsharing.network" target="_blank">bayreuth.land[at]foodsharing.network</a></li>
<li>Berchtesgadener Land <a href="mailto:berchtesgadener.land@foodsharing.network" target="_blank">berchtesgadener.land[at]foodsharing.network</a></li>
<li>Buchloe <a href="mailto:buchloe@foodsharing.network" target="_blank">buchloe[at]foodsharing.network</a></li>
<li>Coburg <a href="mailto:coburg@foodsharing.network" target="_blank">coburg[at]foodsharing.network</a></li>
<li>Dinkelsb&uuml;hl&nbsp;<a href="mailto:dinkelsbuehl@foodsharing.network" target="_blank">dinkelsbuehl[at]foodsharing.network</a></li>
<li>Donauw&ouml;rth <a href="mailto:donauwoerth@foodsharing.network" target="_blank">donauwoerth[at]foodsharing.network</a></li>
<li>Eching <a href="mailto:eching@foodsharing.network" target="_blank">eching[at]foodsharing.network</a></li>
<li>Eichst&auml;tt Landkreis <a href="mailto:eichstaett.landkreis@foodsharing.network" target="_blank">eichstaett.landkreis[at]foodsharing.network</a></li>
<li>Erding&nbsp;<a href="mailto:erding@foodsharing.network" target="_blank">erding[at]foodsharing.network</a></li>
<li>Erlangen <a href="mailto:erlangen@foodsharing.network" target="_blank">erlangen[at]foodsharing.network</a></li>
<li>Fr&auml;nkische Schweiz <a href="mailto:fraenkische-schweiz@foodsharing.network" target="_blank">fraenkische-schweiz[at]foodsharing.network</a></li>
<li>Freising <a href="mailto:freising@foodsharing.network" target="_blank">freising[at]foodsharing.network</a></li>
<li>H&ouml;chstadt-Aisch&nbsp;<a href="mailto:hoechstadt.aisch@foodsharing.network" target="_blank">hoechstadt.aisch[at]foodsharing.network</a></li>
<li>Hof und Region <a href="mailto:hof@foodsharing.network" target="_blank">hof[at]foodsharing.network</a></li>
<li>Ingolstadt <a href="mailto:ingolstadt@foodsharing.network" target="_blank">ingolstadt[at]foodsharing.network</a></li>
<li>Kehlheim Landkreis&nbsp;<a href="mailto:kehlheim.landkreis@foodsharing.network" target="_blank">kehlheim.landkreis[at]foodsharing.network</a></li>
<li>Kempten (Allg&auml;u) <a href="mailto:kempten.allgaeu@foodsharing.network" target="_blank">kempten.allgaeu[at]foodsharing.network</a></li>
<li>Kirchheim, Pliening, Poing <a href="mailto:kirchheim.pliening.poing@foodsharing.network" target="_blank">kirchheim.pliening.poing[at]foodsharing.network</a></li>
<li>Kulmbach&nbsp;<a href="mailto:kulmbach@foodsharing.network" target="_blank">kulmbach[at]foodsharing.network</a></li>
<li>Landkreis Miltenberg <a href="mailto:miltenberg@foodsharing.network" target="_blank">miltenberg[at]foodsharing.network</a></li>
<li>Landsberg am Lech <a href="mailto:landsberg.am.lech@foodsharing.network" target="_blank">landsberg.am.lech[at]foodsharing.network</a></li>
<li>Landshut <a href="mailto:landshut@foodsharing.network" target="_blank">landshut[at]foodsharing.network</a></li>
<li>Main Spessart&nbsp;<a href="mailto:main.spessart@foodsharing.network" target="_blank">main.spessart[at]foodsharing.network</a></li>
<li>Marktredwitz, Wunsiedel&nbsp;<a href="mailto:marktredwitz.wunsiedel@foodsharing.network" target="_blank">marktredwitz.wunsiedel[at]foodsharing.network</a></li>
<li>Meitingen&nbsp;<a href="mailto:meitingen@foodsharing.network" target="_blank">meitingen[at]foodsharing.network</a></li>
<li>Memmingen und Region <a href="mailto:memmingen@foodsharing.network" target="_blank">memmingen[at]foodsharing.network</a></li>
<li>Mittleres Vilstal&nbsp;<a href="mailto:mittleres.vilstal@foodsharing.network" target="_blank">mittleres.vilstal[at]foodsharing.network</a></li>
<li>M&uuml;nchen <a href="mailto:muenchen@foodsharing.network" target="_blank">muenchen[at]foodsharing.network</a></li>
<li>N&uuml;rnberg(er Land) <a href="mailto:nuernberg@foodsharing.network" target="_blank">nuernberg[at]foodsharing.network</a>
<ul>
<li>F&uuml;rth(er Land) <a href="mailto:fuerth@foodsharing.network" target="_blank">fuerth[at]foodsharing.network</a></li>
</ul>
</li>
<li>Oberallg&auml;u&nbsp;<a href="mailto:obreallgaeu@foodsharing.network" target="_blank">obreallgaeu[at]foodsharing.network</a></li>
<li>Passau&nbsp;<a href="mailto:passau@foodsharing.network" target="_blank">passau[at]foodsharing.network</a></li>
<li>Pfaffenhofen an der Ilm <a href="mailto:pfaffenhofen.an.der.ilm@foodsharing.network" target="_blank">pfaffenhofen.an.der.ilm[at]foodsharing.network</a></li>
<li>Regensburg <a href="mailto:regensburg@foodsharing.network" target="_blank">regensburg[at]foodsharing.network</a></li>
<li>Rosenheim <a href="mailto:rosenheim@foodsharing.network" target="_blank">rosenheim[at]foodsharing.network</a></li>
<li>Schweinfurt und Region <a href="mailto:schweinfurt@foodsharing.network" target="_blank">schweinfurt[at]foodsharing.network</a></li>
<li>Staffelsee, Kochelsee und Umgebung <a href="mailto:staffelsee.kochelsee@foodsharing.network" target="_blank">staffelsee.kochelsee[at]foodsharing.network</a></li>
<li>Straubing und Region <a href="mailto:straubing@foodsharing.network" target="_blank">straubing[at]foodsharing.network</a></li>
<li>Vilsbiburg&nbsp;<a href="mailto:vilsbiburg@foodsharing.network" target="_blank">vilsbiburg[at]foodsharing.network</a>&nbsp;</li>
<li>Weiden in der Opf. und Umgebung <a href="mailto:weiden.opf@foodsharing.network" target="_blank">weiden.opf[at]foodsharing.network</a></li>
<li>Wertingen&nbsp;<a href="mailto:wertingen@foodsharing.network" target="_blank">wertingen[at]foodsharing.network</a></li>
<li>W&uuml;rzburg <a href="mailto:wuerzburg@foodsharing.network" target="_blank">wuerzburg[at]foodsharing.network</a></li>
</ul>
<h2></h2>
<h2><strong>Berlin <a href="mailto:berlin@foodsharing.network" target="_blank">berlin[at]foodsharing.network</a></strong></h2>
<ul>
<li>Charlottenburg-Wilmersdorf <a href="mailto:charlottenburg.wilmersdorf@foodsharing.network" target="_blank">charlottenburg.wilmersdorf[at]foodsharing.network</a>
<ul>
<li>Charlottenburg <a href="mailto:charlottenburg@foodsharing.network" target="_blank">charlottenburg[at]foodsharing.network</a></li>
<li>Charlottenburg-Nord <a href="mailto:charlottenburg.nord@foodsharing.network" target="_blank">charlottenburg.nord[at]foodsharing.network</a></li>
<li>Grunewald <a href="mailto:grunewald@foodsharing.network" target="_blank">grunewald[at]foodsharing.network</a></li>
<li>Halensee und Schmargendorf <a href="mailto:halensee.und.schmargendorf@foodsharing.network" target="_blank">halensee.und.schmargendorf[at]foodsharing.network</a></li>
<li>Westend <a href="mailto:westend@foodsharing.network" target="_blank">westend[at]foodsharing.network</a></li>
<li>Wilmersdorf <a href="mailto:wilmersdorf@foodsharing.network" target="_blank">wilmersdorf[at]foodsharing.network</a></li>
</ul>
</li>
<li>Friedrichshain <a href="mailto:friedrichshain@foodsharing.network" target="_blank">friedrichshain[at]foodsharing.network</a></li>
<li>Kreuzberg <a href="mailto:kreuzberg@foodsharing.network" target="_blank">kreuzberg[at]foodsharing.network</a></li>
<li>Lichtenberg (Bezirk) <a href="mailto:lichtenberg.bezirk@foodsharing.network" target="_blank">lichtenberg.bezirk[at]foodsharing.network</a>
<ul>
<li>Falkenberg, Malchow, Wartenburg <a href="mailto:falkenberg.malchow.wartenburg@foodsharing.network" target="_blank">falkenberg.malchow.wartenburg[at]foodsharing.network</a></li>
<li>Hohensch&ouml;nhausen <a href="mailto:hohenschoenhausen@foodsharing.network" target="_blank">hohenschoenhausen[at]foodsharing.network</a></li>
<li>Karlshorst <a href="mailto:karlshorst@foodsharing.network" target="_blank">karlshorst[at]foodsharing.network</a></li>
<li>Lichtenberg <a href="mailto:lichtenberg@foodsharing.network" target="_blank">lichtenberg[at]foodsharing.network</a></li>
<li>Rummelsburg und Friedrichsfelde <a href="mailto:rummelsburg.und.friedrichsfelde@foodsharing.network" target="_blank">rummelsburg.und.friedrichsfelde[at]foodsharing.network</a></li>
</ul>
</li>
<li>Marzahn-Hellersdorf <a href="mailto:marzahn.hellersdorf@foodsharing.network" target="_blank">marzahn.hellersdorf[at]foodsharing.network</a>
<ul>
<li>Biesdorf <a href="mailto:biesdorf@foodsharing.network" target="_blank">biesdorf[at]foodsharing.network</a></li>
<li>Hellersdorf <a href="mailto:hellersdorf@foodsharing.network" target="_blank">hellersdorf[at]foodsharing.network</a></li>
<li>Kaulsdorf <a href="mailto:kaulsdorf@foodsharing.network" target="_blank">kaulsdorf[at]foodsharing.network</a></li>
<li>Mahlsdorf <a href="mailto:mahlsdorf@foodsharing.network" target="_blank">mahlsdorf[at]foodsharing.network</a></li>
<li>Marzahn <a href="mailto:marzahn@foodsharing.network" target="_blank">marzahn[at]foodsharing.network</a></li>
</ul>
</li>
<li>Mitte Berlin (Bezirk) <a href="mailto:mitte@foodsharing.network" target="_blank">mitte[at]foodsharing.network</a>
<ul>
<li>Gesundbrunnen <a href="mailto:gesundbrunnen@foodsharing.network" target="_blank">gesundbrunnen[at]foodsharing.network</a></li>
<li>Moabit, Tiergarten, Hansaviertel <a href="mailto:moabit@foodsharing.network" target="_blank">moabit[at]foodsharing.network</a></li>
<li>Wedding <a href="mailto:wedding@foodsharing.network" target="_blank">wedding[at]foodsharing.network</a></li>
</ul>
</li>
<li>Neuk&ouml;lln (Bezirk) <a href="mailto:neukoelln.bezirk@foodsharing.network" target="_blank">neukoelln.bezirk[at]foodsharing.network</a>
<ul>
<li>Britz <a href="mailto:britz@foodsharing.network" target="_blank">britz[at]foodsharing.network</a></li>
<li>Gropiusstadt <a href="mailto:gropiusstadt@foodsharing.network" target="_blank">gropiusstadt[at]foodsharing.network</a></li>
<li>Neuk&ouml;lln <a href="mailto:neukoelln@foodsharing.network" target="_blank">neukoelln[at]foodsharing.network</a></li>
<li>Rudow <a href="mailto:rudow@foodsharing.network" target="_blank">rudow[at]foodsharing.network</a></li>
</ul>
</li>
<li>Pankow (Bezirk) <a href="mailto:pankow.prenzlauer.berg@foodsharing.network" target="_blank">pankow.prenzlauer.berg[at]foodsharing.network</a>
<ul>
<li>N&ouml;rdlicher Teil Pankows <a href="mailto:noerdlicher.teil.pankows@foodsharing.network" target="_blank">noerdlicher.teil.pankows[at]foodsharing.network</a></li>
<li>Pankow <a href="mailto:pankow@foodsharing.network" target="_blank">pankow[at]foodsharing.network</a></li>
<li>Prenzlauer Berg <a href="mailto:prenzlauer.berg@foodsharing.network" target="_blank">prenzlauer.berg[at]foodsharing.network</a></li>
<li>Wei&szlig;ensee <a href="mailto:weissensee@foodsharing.network" target="_blank">weissensee[at]foodsharing.network</a></li>
</ul>
</li>
<li>Reinickendorf (Bezirk) <a href="mailto:reinickendorf@foodsharing.network" target="_blank">reinickendorf[at]foodsharing.network</a>
<ul>
<li>M&auml;rkisches Viertel <a href="mailto:maerkisches.viertel@foodsharing.network" target="_blank">maerkisches.viertel[at]foodsharing.network</a></li>
<li>Reinickendorf <a href="mailto:reinickendorf1@foodsharing.network" target="_blank">reinickendorf1[at]foodsharing.network</a></li>
<li>Frohnau <a href="mailto:frohnau@foodsharing.network" target="_blank">frohnau[at]foodsharing.network</a></li>
<li>Heiligensee <a href="mailto:heiligensee@foodsharing.network" target="_blank">heiligensee[at]foodsharing.network</a></li>
<li>Tegel <a href="mailto:tegel@foodsharing.network" target="_blank">tegel[at]foodsharing.network</a></li>
<li>Waidmannslust <a href="mailto:waidmannslust@foodsharing.network" target="_blank">waidmannslust[at]foodsharing.network</a></li>
<li>Wittenau <a href="mailto:wittenau@foodsharing.network" target="_blank">wittenau[at]foodsharing.network</a></li>
</ul>
</li>
<li>Spandau <a href="mailto:spandau@foodsharing.network" target="_blank">spandau[at]foodsharing.network</a></li>
<li>Steglitz-Zehlendorf <a href="mailto:steglitz.zehlendorf@foodsharing.network" target="_blank">steglitz.zehlendorf[at]foodsharing.network</a>
<ul>
<li>Dahlem <a href="mailto:dahlem@foodsharing.network" target="_blank">dahlem[at]foodsharing.network</a></li>
<li>Lankwitz <a href="mailto:lankwitz@foodsharing.network" target="_blank">lankwitz[at]foodsharing.network</a></li>
<li>Lichterfelde <a href="mailto:lichterfelde@foodsharing.network" target="_blank">lichterfelde[at]foodsharing.network</a></li>
<li>Nikolassee <a href="mailto:nikolassee@foodsharing.network" target="_blank">nikolassee[at]foodsharing.network</a></li>
<li>Steglitz <a href="mailto:steglitz@foodsharing.network" target="_blank">steglitz[at]foodsharing.network</a></li>
<li>Wannsee <a href="mailto:wannsee@foodsharing.network" target="_blank">wannsee[at]foodsharing.network</a></li>
<li>Zehlendorf <a href="mailto:zehlendorf@foodsharing.network" target="_blank">zehlendorf[at]foodsharing.network</a></li>
</ul>
</li>
<li>Tempelhof - Sch&ouml;neberg <a href="mailto:tempelhof.schoeneberg@foodsharing.network" target="_blank">tempelhof.schoeneberg[at]foodsharing.network</a>
<ul>
<li>Friedenau <a href="mailto:friedenau@foodsharing.network" target="_blank">friedenau[at]foodsharing.network</a></li>
<li>Marienfelde, Lichtenrade <a href="mailto:marienfelde.lichtenrade@foodsharing.network" target="_blank">marienfelde.lichtenrade[at]foodsharing.network</a></li>
<li>Sch&ouml;neberg <a href="mailto:schoeneberg@foodsharing.network" target="_blank">schoeneberg[at]foodsharing.network</a></li>
<li>Tempelhof, Mariendorf <a href="mailto:tempelhof.mariendorf@foodsharing.network" target="_blank">tempelhof.mariendorf[at]foodsharing.network</a></li>
</ul>
</li>
<li>Treptow-K&ouml;penick <a href="mailto:treptow.koepenick@foodsharing.network" target="_blank">treptow.koepenick[at]foodsharing.network</a>
<ul>
<li>Adlershof <a href="mailto:adlershof@foodsharing.network" target="_blank">adlershof[at]foodsharing.network</a></li>
<li>Alt-Treptow <a href="mailto:alt.treptow@foodsharing.network" target="_blank">alt.treptow[at]foodsharing.network</a></li>
<li>Altglienicke <a href="mailto:altglienicke@foodsharing.network" target="_blank">altglienicke[at]foodsharing.network</a></li>
<li>Baumschulenweg <a href="mailto:baumschulenweg@foodsharing.network" target="_blank">baumschulenweg[at]foodsharing.network</a></li>
<li>Bohnsdorf <a href="mailto:bohnsdorf@foodsharing.network" target="_blank">bohnsdorf[at]foodsharing.network</a></li>
<li>Friedrichshagen <a href="mailto:friedrichshagen@foodsharing.network" target="_blank">friedrichshagen[at]foodsharing.network</a></li>
<li>Gr&uuml;nau <a href="mailto:gruenau@foodsharing.network" target="_blank">gruenau[at]foodsharing.network</a></li>
<li>Johannisthal <a href="mailto:johannisthal@foodsharing.network" target="_blank">johannisthal[at]foodsharing.network</a></li>
<li>K&ouml;penick <a href="mailto:koepenick@foodsharing.network" target="_blank">koepenick[at]foodsharing.network</a></li>
<li>Pl&auml;nterwald <a href="mailto:plaenterwald@foodsharing.network" target="_blank">plaenterwald[at]foodsharing.network</a></li>
<li>Rahnsdorf <a href="mailto:rahnsdorf@foodsharing.network" target="_blank">rahnsdorf[at]foodsharing.network</a></li>
<li>Sch&ouml;neweide <a href="mailto:schoeneweide@foodsharing.network" target="_blank">schoeneweide[at]foodsharing.network</a></li>
</ul>
</li>
</ul>
<h2></h2>
<h2>Brandenburg<a href="mailto:brandenburg@foodsharing.network" target="_blank"> brandenburg[at]foodsharing.network</a></h2>
<ul>
<li>Bernau bei Berlin und Umgebung <a href="mailto:bernau.bei.berlin.und.umgebung@foodsharing.network" target="_blank">bernau.bei.berlin.und.umgebung[at]foodsharing.network</a></li>
<li>Brandenburg an der Havel <a href="mailto:brandenburg.an.der.havel@foodsharing.network" target="_blank">brandenburg.an.der.havel[at]foodsharing.network</a></li>
<li>Cottbus <a href="mailto:cottbus@foodsharing.network" target="_blank">cottbus[at]foodsharing.network</a></li>
<li>Eberswalde <a href="mailto:eberswalde@foodsharing.network" target="_blank">eberswalde[at]foodsharing.network</a></li>
<li>Erkner, Sch&ouml;neiche, Woltersdorf <a href="mailto:erkner.schoeneiche.woltersdorf@foodsharing.network" target="_blank">erkner.schoeneiche.woltersdorf[at]foodsharing.network</a></li>
<li>Frankfurt (Oder) <a href="mailto:frankfurt.oder@foodsharing.network" target="_blank">frankfurt.oder[at]foodsharing.network</a></li>
<li>Luckau (Niederlausitz) <a href="mailto:Luckau.Niederlausitz@foodsharing.network" target="_blank">Luckau.Niederlausitz[at]foodsharing.network</a></li>
<li>Luckenwalde, J&uuml;terbog <a href="mailto:luckenwalde.jueterbog@foodsharing.network" target="_blank">luckenwalde.jueterbog[at]foodsharing.network</a></li>
<li>Neuenhagen und Hoppegarten <a href="mailto:neuenhagen.hoppegarten@foodsharing.network" target="_blank">neuenhagen.hoppegarten[at]foodsharing.network</a></li>
<li>Oberhavel Landkreis <a href="mailto:oberhavel@foodsharing.network" target="_blank">oberhavel[at]foodsharing.network</a></li>
<li>Oranienburg und Umgebung <a href="mailto:oranienburg@foodsharing.network" target="_blank">oranienburg[at]foodsharing.network</a></li>
<li>Ostprignitz-Ruppin Landkreis <a href="mailto:ostprignitz.ruppin@foodsharing.network" target="_blank">ostprignitz.ruppin[at]foodsharing.network</a></li>
<li>Potsdam <a href="mailto:potsdam@foodsharing.network" target="_blank">potsdam[at]foodsharing.network</a></li>
<li>Sch&ouml;nefeld <a href="mailto:schoenefeld@foodsharing.network" target="_blank">schoenefeld[at]foodsharing.network</a></li>
<li>Teltow - Stahnsdorf - Kleinmachnow <a href="mailto:teltow.stahnsdorf.kleinmachnow@foodsharing.network" target="_blank">teltow.stahnsdorf.kleinmachnow[at]foodsharing.network</a></li>
<li>Werder - Gro&szlig;kreutz (Havel) <a href="mailto:werder.grosskreutz@foodsharing.network" target="_blank">werder.grosskreutz[at]foodsharing.network</a>
<h1></h1>
</li>
</ul>
<h2>Bremen <a href="mailto:bremen@foodsharing.network" target="_blank">bremen[at]foodsharing.network</a></h2>
<ul>
<li>Bremen (Stadt) <a href="mailto:bremen.stadt@foodsharing.network" target="_blank">bremen.stadt[at]foodsharing.network</a></li>
<li>Bremerhaven und anliegende Regionen <a href="mailto:bremenhaven@foodsharing.network" target="_blank">bremerhaven[at]foodsharing.network</a></li>
<li>Findorff <a href="mailto:bremen.findorff@foodsharing.network" target="_blank">bremen.findorff[at]foodsharing.network</a></li>
<li>Habenhausen <a href="mailto:bremen.habenhausen@foodsharing.network" target="_blank">bremen.habenhausen[at]foodsharing.network</a></li>
<li>Hemelingen <a href="mailto:hemelingen@foodsharing.network" target="_blank">hemelingen[at]foodsharing.network</a></li>
<li>Horn-Lehe <a href="mailto:bremen.horn-lehe@foodsharing.network" target="_blank">bremen.horn-lehe[at]foodsharing.network</a></li>
<li>Huchting <a href="mailto:bremen.huchting@foodsharing.network" target="_blank">bremen.huchting[at]foodsharing.network</a></li>
<li>Mitte <a href="mailto:bremen.mitte@foodsharing.network" target="_blank">bremen.mitte[at]foodsharing.network</a></li>
<li>Neustadt <a href="mailto:bremen.neustadt@foodsharing.network" target="_blank">bremen.neustadt[at]foodsharing.network</a></li>
<li>&Ouml;stliche Vorstadt <a href="mailto:bremen.oestliche.vorstadt@foodsharing.network" target="_blank">bremen.oestliche.vorstadt[at]foodsharing.network</a></li>
<li>Schwachhausen <a href="mailto:bremen.schwachhausen@foodsharing.network" target="_blank">bremen.schwachhausen[at]foodsharing.network</a></li>
<li>Vahr <a href="mailto:bremen.vahr@foodsharing.network" target="_blank">bremen.vahr[at]foodsharing.network</a></li>
<li>Walle <a href="mailto:bremen.walle@foodsharing.network" target="_blank">bremen.walle[at]foodsharing.network</a></li>
<li>Woltmershausen <a href="mailto:bremen.woltmershausen@foodsharing.network" target="_blank">bremen.woltmershausen[at]foodsharing.network</a></li>
<li>Bremen - Nord (Stadt) <a href="mailto:bremen.nord@foodsharing.network" target="_blank">bremen.nord[at]foodsharing.network</a></li>
</ul>
<h2></h2>
<h2>Hamburg <a href="mailto:hamburg@foodsharing.network" target="_blank">hamburg[at]foodsharing.network</a></h2>
<h2></h2>
<h2>Hessen <a href="mailto:hessen@foodsharing.network" target="_blank">hessen[at]foodsharing.network</a></h2>
<ul>
<li>Biblis <a href="mailto:biblis@foodsharing.network" target="_blank">biblis[at]foodsharing.network</a></li>
<li>Darmstadt <a href="mailto:darmstadt@foodsharing.network" target="_blank">darmstadt[at]foodsharing.network</a></li>
<li>Dieburg <a href="mailto:dieburg@foodsharing.network" target="_blank">dieburg[at]foodsharing.network</a></li>
<li>Eschwege, Bad Sooden-Allendorf <a href="mailto:eschwege.bsa@foodsharing.network" target="_blank">eschwege.bsa[at]foodsharing.network</a></li>
<li>Frankfurt am Main <a href="mailto:frankfurt.am.main@foodsharing.network" target="_blank">frankfurt.am.main[at]foodsharing.network</a></li>
<li>Fulda Landkreis <a href="mailto:fulda@foodsharing.network" target="_blank">fulda[at]foodsharing.network</a></li>
<li>Gie&szlig;en Landkreis <a href="mailto:giessen@foodsharing.network" target="_blank">giessen[at]foodsharing.network</a></li>
<li>Gro&szlig;-Gerau Kreis <a href="mailto:gross.gerau.und.region@foodsharing.network" target="_blank">gross.gerau.und.region[at]foodsharing.network</a></li>
<li>Hanau <a href="mailto:hanau@foodsharing.network" target="_blank">hanau[at]foodsharing.network</a></li>
<li>Herborn, Dillenburg <a href="mailto:herborn.dillenburg@foodsharing.network" target="_blank">herborn.dillenburg[at]foodsharing.network</a></li>
<li>Hessische Bergstra&szlig;e <a href="mailto:hessische.bergstra&szlig;e@foodsharing.network" target="_blank">hessische.bergstra&szlig;e[at]foodsharing.network</a></li>
<li>Hochtaunuskreis <a href="mailto:hochtaunuskreis@foodsharing.network" target="_blank">hochtaunuskreis[at]foodsharing.network</a></li>
<li>Kassel <a href="mailto:kassel@foodsharing.network" target="_blank">kassel[at]foodsharing.network</a></li>
<li>Lampertheim <a href="mailto:lampertheim@foodsharing.network" target="_blank">lampertheim[at]foodsharing.network</a></li>
<li>Limburg, Diez &amp; Umgebung <a href="mailto:limburg.diez@foodsharing.network" target="_blank">limburg.diez[at]foodsharing.network</a></li>
<li>Main-Kinzig-Kreis <a href="mailto:main.kinzig@foodsharing.network" target="_blank">main.kinzig[at]foodsharing.network</a></li>
<li>Main-Spitze (MZ-Kastel, MZ-Kostheim, GiGu) <a href="mailto:main-spitze@foodsharing.network" target="_blank">main-spitze[at]foodsharing.network</a></li>
<li>Main-Taunus-Kreis <a href="mailto:main.taunus@foodsharing.network" target="_blank">main.taunus[at]foodsharing.network</a></li>
<li>Marburg <a href="mailto:marburg@foodsharing.network" target="_blank">marburg[at]foodsharing.network</a></li>
<li>Odenwald Nord-West <a href="mailto:odenwald.nord.west@foodsharing.network" target="_blank">odenwald.nord.west[at]foodsharing.network</a></li>
<li>Offenbach <a href="mailto:offenbach@foodsharing.network" target="_blank">offenbach[at]foodsharing.network</a></li>
<li>Rheingau-Taunus-Kreis <a href="mailto:rheingau@foodsharing.network" target="_blank">rheingau[at]foodsharing.network</a></li>
<li>Weilburg <a href="mailto:weilburg.weilmuenster@foodsharing.network" target="_blank">weilburg.weilmuenster[at]foodsharing.network</a></li>
<li>Weinheim, Birkenau, Viernheim &amp; Umgebung <a href="mailto:weinheim.birkenau.viernheim@foodsharing.network" target="_blank">weinheim.birkenau.viernheim[at]foodsharing.network</a></li>
<li>Wetzlar <a href="mailto:wetzlar@foodsharing.network" target="_blank">wetzlar[at]foodsharing.network</a></li>
<li>Wiesbaden <a href="mailto:wiesbaden@foodsharing.network" target="_blank">wiesbaden[at]foodsharing.network</a></li>
<li>Witzenhausen&nbsp;<a href="mailto:witzenhausen@foodsharing.network" target="_blank">witzenhausen[at]foodsharing.network</a></li>
</ul>
<h2></h2>
<h2>Mecklenburg-Vorpommern <a href="mailto:mecklenburg.vorpommern@foodsharing.network" target="_blank">mecklenburg.vorpommern[at]foodsharing.network</a></h2>
<ul>
<li>Greifswald <a href="mailto:greifswald@foodsharing.network" target="_blank">greifswald[at]foodsharing.network</a></li>
<li>Rostock <a href="mailto:rostock@foodsharing.network" target="_blank">rostock[at]foodsharing.network</a></li>
<li>Schwerin <a href="mailto:schwerin@foodsharing.network" target="_blank">schwerin[at]foodsharing.network</a></li>
</ul>
<h2></h2>
<h2>Niedersachsen <a href="mailto:niedersachsen@foodsharing.network" target="_blank">niedersachsen[at]foodsharing.network</a></h2>
<ul>
<li><span><span>Achim</span> <a href="mailto:achim@foodsharing.network" target="_blank">achim[at]foodsharing.network</a></span></li>
<li><span>Aurich</span> <a href="mailto:aurich@foodsharing.network" target="_blank">aurich[at]foodsharing.network</a></li>
<li><span>Bevensen</span> <span><span></span></span><a href="mailto:bevensen@foodsharing.network" target="_blank">bevensen[at]foodsharing.network</a></li>
<li><span>Braunschweig <a href="mailto:braunschweig@foodsharing.network" target="_blank">braunschweig[at]foodsharing.network</a></span></li>
<li>Buxtehude <a href="mailto:buxtehude@foodsharing.network" target="_blank">buxtehude[at]foodsharing.network</a></li>
<li>Celle <a href="mailto:celle@foodsharing.network" target="_blank">celle[at]foodsharing.network</a></li>
<li>Cremlingen &amp; Sickte <a href="mailto:cremlingen@foodsharing.network" target="_blank">cremlingen[at]foodsharing.network</a></li>
<li>Dornum (Ostfriesland) <a href="mailto:dornum@foodsharing.network" target="_blank">dornum[at]foodsharing.network</a></li>
<li>Einbeck Kreiensen <a href="mailto:Einbeck-Kreiensen@foodsharing.network" target="_blank">Einbeck-Kreiensen[at]foodsharing.network</a></li>
<li>Emden <a href="mailto:emden@foodsharing.network" target="_blank">emden[at]foodsharing.network</a></li>
<li>Emsland <a href="mailto:emsland@foodsharing.network" target="_blank">emsland[at]foodsharing.network</a></li>
<li>Ganderkesee <a href="mailto:ganderkesee@foodsharing.network" target="_blank">ganderkesee[at]foodsharing.network</a></li>
<li>Gemeinde Lehre und K&ouml;nigslutter <a href="mailto:lehre@foodsharing.network" target="_blank">lehre[at]foodsharing.network</a></li>
<li>Gifhorn <a href="mailto:gifhorn@foodsharing.network" target="_blank">gifhorn[at]foodsharing.network</a></li>
<li>Glausthal-Zellerfeld <a href="mailto:clausthal-zellerfeld@foodsharing.network" target="_blank">clausthal-zellerfeld[at]foodsharing.network</a></li>
<li>Goslar <a href="mailto:goslar@foodsharing.network" target="_blank">goslar[at]foodsharing.network</a></li>
<li>G&ouml;ttingen <a href="mailto:goettingen@foodsharing.network" target="_blank">goettingen[at]foodsharing.network</a></li>
<li><span>hameln <a href="mailto:hameln1@foodsharing.network" target="_blank">h<span>ameln1</span>[at]foodsharing.network</a></span></li>
<li>Hannover <a href="mailto:hannover@foodsharing.network" target="_blank">hannover[at]foodsharing.network</a></li>
<li>Helmstedt <a href="mailto:helmstedt@foodsharing.network" target="_blank">helmstedt[at]foodsharing.network</a></li>
<li>Hildesheim <a href="mailto:hildesheim@foodsharing.network" target="_blank">hildesheim[at]foodsharing.network</a></li>
<li>Holzminden und Region <a href="mailto:holzminden@foodsharing.network" target="_blank">holzminden[at]foodsharing.network</a></li>
<li>Landkreis Cuxhaven&nbsp;/ Kehdingen <a href="mailto:cuxhaven@foodsharing.network" target="_blank">cuxhaven[at]foodsharing.network</a></li>
<li>Landkreis Diepholz <a href="mailto:landkreis.diepholz@foodsharing.network" target="_blank">landkreis.diepholz[at]foodsharing.network</a>
<ul>
<li>Diepholz <a href="mailto:diepholz@foodsharing.network" target="_blank">diepholz[at]foodsharing.network</a></li>
</ul>
</li>
<li>Landkreis Friesland <a href="mailto:landkreis.friesland@foodsharing.network" target="_blank">landkreis.friesland[at]foodsharing.network</a></li>
<li>Landkreis&nbsp;Wittmund <a href="mailto:landkreis-wittmund@foodsharing.network" target="_blank">landkreis-wittmund[at]foodsharing.network</a></li>
<li>L&uuml;chow-Dannenberg Landkreis <a href="mailto:luechow.dannenberg.landkreis@foodsharing.network" target="_blank">luechow.dannenberg.landkreis[at]foodsharing.network</a></li>
<li>L&uuml;neburg <a href="mailto:lueneburg@foodsharing.network" target="_blank">lueneburg[at]foodsharing.network</a></li>
<li>Melle <a href="mailto:melle@foodsharing.network" target="_blank">melle[at]foodsharing.network</a></li>
<li>Neu Wulmstorf <a href="mailto:neu-wulmstorf@foodsharing.network" target="_blank">neu-wulmstorf[at]foodsharing.network</a></li>
<li>Nienburg Weser Landkreis
<ul>
<li>Steyerberg Flecken <a href="mailto:steyerberg.flecken@foodsharing.network" target="_blank">steyerberg.flecken[at]foodsharing.network</a></li>
</ul>
</li>
<li>Oberharz <a href="mailto:oberharz@foodsharing.network" target="_blank">oberharz[at]foodsharing.network</a>
<ul>
<li>Bad Lauterberg <a href="mailto:bad.lauterberg@foodsharing.network" target="_blank">bad.lauterberg[at]foodsharing.network</a></li>
<li>Braunlage <a href="mailto:braunlage@foodsharing.network" target="_blank">braunlage[at]foodsharing.network</a></li>
<li>Sankt Andreasberg <a href="mailto:sankt.andreasberg@foodsharing.network" target="_blank">sankt.andreasberg[at]foodsharing.network</a></li>
</ul>
</li>
<li>Oldenburg und Umgebung <a href="mailto:oldenburg@foodsharing.network" target="_blank">oldenburg[at]foodsharing.network</a></li>
<li>Osnabr&uuml;ck <a href="mailto:osnabrueck@foodsharing.network" target="_blank">osnabrueck[at]foodsharing.network</a></li>
<li>Osterholz und angrenzende Regionen <a href="mailto:osterholz@foodsharing.network" target="_blank">osterholz[at]foodsharing.network</a></li>
<li>Peine <a href="mailto:peine@foodsharing.network" target="_blank">peine[at]foodsharing.network</a></li>
<li>Rhauderfehn <a href="mailto:rhauderfehn@foodsharing.network" target="_blank">rhauderfehn[at]foodsharing.network</a></li>
<li>Rosengarten <a href="mailto:rosengarten@foodsharing.network" target="_blank">rosengarten[at]foodsharing.network</a></li>
<li>Sch&ouml;ppenstedt&nbsp;<a href="mailto:schoeppenstedt@foodsharing.network" target="_blank">schoeppenstedt[at]foodsharing.network</a></li>
<li>Sch&ouml;ningen <a href="mailto:schoeningen@foodsharing.network" target="_blank">schoeningen[at]foodsharing.network</a></li>
<li>Stade <a href="mailto:stade1@foodsharing.network" target="_blank">stade1[at]foodsharing.network</a></li>
<li>Vechta <a href="mailto:vechta@foodsharing.network" target="_blank">vechta[at]foodsharing.network</a></li>
<li>Wildeshausen und Umgebung <a href="mailto:wildeshausen@foodsharing.network" target="_blank">wildeshausen[at]foodsharing.network</a></li>
<li>Winsen (Luhe) <a href="mailto:winsen@foodsharing.network" target="_blank">winsen[at]foodsharing.network</a></li>
<li>Wolfenb&uuml;ttel <a href="mailto:wolfenbuettel@foodsharing.network" target="_blank">wolfenbuettel[at]foodsharing.network</a></li>
<li>Wolfsburg <a href="mailto:wolfsburg@foodsharing.network" target="_blank">wolfsburg[at]foodsharing.network</a></li>
</ul>
<h2></h2>
<h2>Nordrhein-Westfalen <a href="mailto:nordrhein.westfalen@foodsharing.network" target="_blank">nordrhein.westfalen[at]foodsharing.network</a></h2>
<ul>
<li>Aachen <a href="mailto:aachen@foodsharing.network" target="_blank">aachen[at]foodsharing.network</a><br />
<ul>
<li>Alsdorf <a href="mailto:alsdorf@foodsharing.network" target="_blank">alsdorf[at]foodsharing.network</a></li>
<li>Eschweiler <a href="mailto:eschweiler@foodsharing.network" target="_blank">eschweiler[at]foodsharing.network</a></li>
<li>Roetgen <a href="mailto:roetgen@foodsharing.network" target="_blank">roetgen[at]foodsharing.network</a></li>
</ul>
</li>
<li>Bergheim <a href="mailto:bergheim@foodsharing.network" target="_blank">bergheim[at]foodsharing.network</a></li>
<li>Bergisch Gladbach <a href="mailto:bergisch.gladbach@foodsharing.network" target="_blank">bergisch.gladbach[at]foodsharing.network</a></li>
<li>Beverungen <a href="mailto:Beverungen@foodsharing.network" target="_blank">beverungen[at]foodsharing.network</a></li>
<li>Bielefeld <a href="mailto:bielefeld@foodsharing.network" target="_blank">bielefeld[at]foodsharing.network</a></li>
<li>Bocholt <a href="mailto:Bocholt@foodsharing.network" target="_blank">bocholt[at]foodsharing.network</a></li>
<li>Bochum <a href="mailto:bochum@foodsharing.network" target="_blank">bochum[at]foodsharing.network</a></li>
<li>Bonn <a href="mailto:Bonn@foodsharing.network" target="_blank">Bonn[at]foodsharing.network</a></li>
<li>Brilon - Marsberg - Bad W&uuml;nnenberg <a href="mailto:brilon@foodsharing.network" target="_blank">brilon[at]foodsharing.network</a></li>
<li>Br&uuml;hl <a href="mailto:bruehl@foodsharing.network" target="_blank">bruehl[at]foodsharing.network</a></li>
<li>Burbach, Neunkirchen (Siegerland) <a href="mailto:Burbach@foodsharing.network" target="_blank">Burbach[at]foodsharing.network</a></li>
<li>Castrop-Rauxel&nbsp;<a href="mailto:castrop-rauxel@foodsharing.network" target="_blank">castrop-rauxel[at]foodsharing.network</a></li>
<li>Detmold &amp; Umgebung <a href="mailto:detmold@foodsharing.network" target="_blank">detmold[at]foodsharing.network</a></li>
<li>Dinslaken <a href="mailto:dinslaken@foodsharing.network" target="_blank">dinslaken[at]foodsharing.network</a></li>
<li>Dormagen <a href="mailto:dormagen@foodsharing.network" target="_blank">dormagen[at]foodsharing.network</a></li>
<li>Dorsten <a href="mailto:dorsten@foodsharing.network" target="_blank">dorsten[at]foodsharing.network</a></li>
<li>Dortmund <a href="mailto:dortmund@foodsharing.network" target="_blank">dortmund[at]foodsharing.network</a></li>
<li>Duisburg <a href="mailto:duisburg@foodsharing.network" target="_blank">duisburg[at]foodsharing.network</a></li>
<li>D&uuml;ren <a href="mailto:dueren@foodsharing.network" target="_blank">dueren[at]foodsharing.network</a></li>
<li>D&uuml;sseldorf <a href="mailto:duesseldorf@foodsharing.network" target="_blank">duesseldorf[at]foodsharing.network</a>
<ul>
<li>D&uuml;sseldorf - Mitte <a href="mailto:duesseldorf-mitte@foodsharing.network" target="_blank">duesseldorf-mitte[at]foodsharing.network</a></li>
<li>D&uuml;sseldorf - Nord <a href="mailto:duesseldorf-nord@foodsharing.network" target="_blank">duesseldorf-nord[at]foodsharing.network</a></li>
<li>D&uuml;sseldorf - Ost <a href="mailto:duesseldorf-ost@foodsharing.network" target="_blank">duesseldorf-ost[at]foodsharing.network</a></li>
<li>D&uuml;sseldorf - S&uuml;d <a href="mailto:duesseldorf-sued@foodsharing.network" target="_blank">duesseldorf-sued[at]foodsharing.network</a></li>
<li>D&uuml;sseldorf - West <a href="mailto:duesseldorf-west@foodsharing.network" target="_blank">duesseldorf-west[at]foodsharing.network</a></li>
</ul>
</li>
<li>Eitorf <a href="mailto:eitorf@foodsharing.network" target="_blank">eitorf[at]foodsharing.network</a></li>
<li>Engelskirchen - Lindlar&nbsp;<a href="mailto:engelskirchen@foodsharing.network" target="_blank">engelskirchen[at]foodsharing.network</a></li>
<li>Erftstadt <a href="mailto:erftstadt@foodsharing.network" target="_blank">erftstadt[at]foodsharing.network</a></li>
<li>Erkrath <a href="mailto:erkrath@foodsharing.network" target="_blank">erkrath[at]foodsharing.network</a></li>
<li>Essen <a href="mailto:essen@foodsharing.network" target="_blank">essen[at]foodsharing.network</a></li>
<li>Euskirchen <a href="mailto:euskirchen@foodsharing.network" target="_blank">euskirchen[at]foodsharing.network</a></li>
<li>Frechen <a href="mailto:frechen@foodsharing.network" target="_blank">frechen[at]foodsharing.network</a></li>
<li>Fr&ouml;ndenberg <a href="mailto:froendenberg@foodsharing.network" target="_blank">froendenberg[at]foodsharing.network</a></li>
<li>Geldern <a href="mailto:geldern@foodsharing.network" target="_blank">geldern[at]foodsharing.network</a></li>
<li>Gelsenkirchen <a href="mailto:gelsenkirchen@foodsharing.network" target="_blank">gelsenkirchen[at]foodsharing.network</a></li>
<li><span>Gladbeck <a href="mailto:gladbeck@foodsharing.network" target="_blank">g<span>ladbeck</span>[at]foodsharing.network</a><br /></span></li>
<li>Gummersbach <a href="mailto:gummersbach@foodsharing.network" target="_blank">gummersbach[at]foodsharing.network</a></li>
<li>G&uuml;tersloh <a href="mailto:guetersloh@foodsharing.network" target="_blank">guetersloh[at]foodsharing.network</a></li>
<li>Haan <a href="mailto:haan@foodsharing.network" target="_blank">haan[at]foodsharing.network</a></li>
<li>Hagen <a href="mailto:hagen@foodsharing.network" target="_blank">hagen[at]foodsharing.network</a></li>
<li>Hamm (Westfalen) <a href="mailto:hamm.westfalen@foodsharing.network" target="_blank">hamm.westfalen[at]foodsharing.network</a></li>
<li>Hattingen&nbsp;<a href="mailto:hattingen@foodsharing.network" target="_blank">hattingen[at]foodsharing.network</a></li>
<li>Hennef <a href="mailto:hennef@foodsharing.network" target="_blank">hennef[at]foodsharing.network</a></li>
<li>Herne <a href="mailto:Herne@foodsharing.network" target="_blank">Herne[at]foodsharing.network</a></li>
<li>Hilden <a href="mailto:hilden@foodsharing.network" target="_blank">hilden[at]foodsharing.network</a></li>
<li><span>Halver, H&uuml;ckeswagen, Radevormwald, Wipperf&uuml;rth</span>&nbsp;<a href="mailto:hueckeswagen@foodsharing.network" target="_blank">hueckeswagen[at]foodsharing.network</a></li>
<li>Iserlohn <a href="mailto:iserlohn@foodsharing.network" target="_blank">iserlohn[at]foodsharing.network</a></li>
<li>Kaarst <a href="mailto:kaarst@foodsharing.network" target="_blank">kaarst[at]foodsharing.network</a></li>
<li>Kamen - Bergkamen <a href="mailto:kamen@foodsharing.network" target="_blank">kamen[at]foodsharing.network</a></li>
<li>Kerpen <a href="mailto:kerpen@foodsharing.network" target="_blank">kerpen[at]foodsharing.network</a></li>
<li>K&ouml;ln <a href="mailto:koeln@foodsharing.network" target="_blank">koeln[at]foodsharing.network</a>
<ul>
<li>Chorweiler <a href="mailto:Chorweiler@foodsharing.network" target="_blank">Chorweiler[at]foodsharing.network</a></li>
<li>Ehrenfeld <a href="mailto:ehrenfeld@foodsharing.network" target="_blank">ehrenfeld[at]foodsharing.network</a></li>
<li>Innenstadt K&ouml;ln <a href="mailto:Innenstadt_Koeln@foodsharing.network" target="_blank">Innenstadt_koeln[at]foodsharing.network</a></li>
<li>Junkersdorf Weiden <a href="mailto:junkersdorf_weiden@foodsharing.network" target="_blank">junkersdorf_weiden[at]foodsharing.network</a></li>
<li>K&ouml;ln Kalk &amp; Deutz <a href="mailto:koeln.rechtsrheinisch@foodsharing.network" target="_blank">koeln.rechtsrheinisch[at]foodsharing.network</a></li>
<li>K&ouml;ln Lindenthal - S&uuml;lz <a href="mailto:koeln.lindenthal-suelz@foodsharing.network" target="_blank">koeln.lindenthal-suelz[at]foodsharing.network</a></li>
<li>K&ouml;ln M&uuml;lheim <a href="mailto:koeln.muelheim@foodsharing.network" target="_blank">koeln.muelheim[at]foodsharing.network</a></li>
<li>Nippes <a href="mailto:nippes@foodsharing.network" target="_blank">nippes[at]foodsharing.network</a></li>
<li>Porz <a href="mailto:porz@foodsharing.network" target="_blank">porz[at]foodsharing.network</a></li>
<li>Rodenkirchen - Rondorf <a href="mailto:rondorf@foodsharing.network" target="_blank">rondorf[at]foodsharing.network</a></li>
<li>S&uuml;dstadt <a href="mailto:koeln.sued@foodsharing.network" target="_blank">koeln.sued[at]foodsharing.network</a></li>
<li>Zollstock - Raderthal <a href="mailto:zollstock.raderthal@foodsharing.network" target="_blank">zollstock.raderthal[at]foodsharing.network</a></li>
</ul>
</li>
<li>Krefeld <a href="mailto:krefeld@foodsharing.network" target="_blank">krefeld[at]foodsharing.network</a></li>
<li>Kreis Heinsberg <a href="mailto:kreis.heinsberg@foodsharing.network" target="_blank">kreis.heinsberg[at]foodsharing.network</a></li>
<li>Langenfeld (Rheinland) &amp; Monheim <a href="mailto:langenfeld.monheim@foodsharing.network" target="_blank">langenfeld.monheim[at]foodsharing.network</a></li>
<li>Leverkusen <a href="mailto:leverkusen@foodsharing.network" target="_blank">leverkusen[at]foodsharing.network</a></li>
<li>Lippstadt <a href="mailto:lippstadt@foodsharing.network" target="_blank">lippstadt[at]foodsharing.network</a></li>
<li>Lohmar&nbsp;<a href="mailto:lohmar@foodsharing.network" target="_blank">lohmar[at]foodsharing.network</a></li>
<li>Lotte, Westerkappeln<span>, Mettingen</span> <a href="mailto:lotte@foodsharing.network" target="_blank">lotte[at]foodsharing.network</a></li>
<li>L&uuml;nen <a href="mailto:luenen@foodsharing.network" target="_blank">luenen[at]foodsharing.network</a></li>
<li>Marl <a href="mailto:marl@foodsharing.network" target="_blank">marl[at]foodsharing.network</a></li>
<li>Meerbusch <a href="mailto:meerbusch1@foodsharing.network" target="_blank">meerbusch1[at]foodsharing.network</a></li>
<li><span>Menden (Sauerland) <a href="mailto:menden@foodsharing.network" target="_blank">menden[at]foodsharing.network</a><br /></span></li>
<li>Mettmann <a href="mailto:mettmann@foodsharing.network" target="_blank">mettmann[at]foodsharing.network</a></li>
<li>Moers <a href="mailto:Moers@foodsharing.network" target="_blank">moers[at]foodsharing.network</a></li>
<li>M&ouml;nchengladbach <a href="mailto:moenchengladbach@foodsharing.network" target="_blank">moenchengladbach[at]foodsharing.network</a></li>
<li>M&uuml;lheim an der Ruhr <a href="mailto:muelheim.an.der.ruhr@foodsharing.network" target="_blank">muelheim.an.der.ruhr[at]foodsharing.network</a></li>
<li>M&uuml;nster <a href="mailto:muenster@foodsharing.network" target="_blank">muenster[at]foodsharing.network</a></li>
<li>Neukirchen-Vluyn <a href="mailto:neukirchen.vluyn@foodsharing.network" target="_blank">neukirchen.vluyn[at]foodsharing.network</a></li>
<li>Neunkirchen-Seelscheid&nbsp;<a href="mailto:neunkirchen-seelscheid@foodsharing.network" target="_blank">neunkirchen-seelscheid[at]foodsharing.network</a></li>
<li>Neuss <a href="mailto:neuss@foodsharing.network" target="_blank">neuss[at]foodsharing.network</a></li>
<li>Oberhausen <a href="mailto:Oberhausen@foodsharing.network" target="_blank">oberhausen[at]foodsharing.network</a></li>
<li><span>Overath <a href="mailto:overath@foodsharing.network" target="_blank">o<span>verath</span>[at]foodsharing.network</a></span> <span><span></span></span></li>
<li>Paderborn <a href="mailto:paderborn@foodsharing.network" target="_blank">paderborn[at]foodsharing.network</a></li>
<li>Pulheim <a href="mailto:pulheim@foodsharing.network" target="_blank">pulheim[at]foodsharing.network</a></li>
<li>Recklinghausen <a href="mailto:recklinghausen@foodsharing.network" target="_blank">recklinghausen[at]foodsharing.network</a></li>
<li>Remscheid <a href="mailto:remscheid@foodsharing.network" target="_blank">remscheid[at]foodsharing.network</a></li>
<li>Rheinberg <a href="mailto:Rheinberg@foodsharing.network" target="_blank">rheinberg[at]foodsharing.network</a></li>
<li>Rheinisch-Bergischer Kreis (Nord) <a href="mailto:rbk-nord@foodsharing.network" target="_blank">rbk-nord[at]foodsharing.network</a></li>
<li>Sankt Augustin&nbsp;<a href="mailto:sankt-augustin@foodsharing.network" target="_blank">sankt-augustin[at]foodsharing.network</a></li>
<li>Schalksm&uuml;hle-L&uuml;denscheid <a href="mailto:schalksmuehle-luedenscheid@foodsharing.network" target="_blank">schalksmuehle-luedenscheid[at]foodsharing.network</a></li>
<li>Siegburg <a href="mailto:siegburg@foodsharing.network" target="_blank">siegburg[at]foodsharing.network</a></li>
<li>Siegen <a href="mailto:siegen@foodsharing.network" target="_blank">siegen[at]foodsharing.network</a></li>
<li>soest <a href="mailto:soest@foodsharing.network" target="_blank">soest[at]foodsharing.network</a></li>
<li>Solingen <a href="mailto:solingen@foodsharing.network" target="_blank">solingen[at]foodsharing.network</a></li>
<li><span>Stolberg (Rheinland)</span>&nbsp;<a href="mailto:stolberg.rhld@foodsharing.network" target="_blank">stolberg.rhld[at]foodsharing.network</a></li>
<li>Troisdorf <a href="mailto:troisdorf@foodsharing.network" target="_blank">troisdorf[at]foodsharing.network</a></li>
<li>Unna <a href="mailto:unna@foodsharing.network" target="_blank">unna[at]foodsharing.network</a></li>
<li>Velbert <a href="mailto:velbert@foodsharing.network" target="_blank">velbert[at]foodsharing.network</a></li>
<li>Waldbr&ouml;l und Umgebung <a href="mailto:waldbroel@foodsharing.network" target="_blank">waldbroel[at]foodsharing.network</a></li>
<li>Waltrop <a href="mailto:waltrop@foodsharing.network" target="_blank">waltrop[at]foodsharing.network</a></li>
<li>Werne <a href="mailto:werne@foodsharing.network" target="_blank">werne[at]foodsharing.network</a></li>
<li>Windeck <a href="mailto:windeck@foodsharing.network" target="_blank">windeck[at]foodsharing.network</a></li>
<li>Witten <a href="mailto:witten@foodsharing.network" target="_blank">witten[at]foodsharing.network</a></li>
<li>Wuppertal <a href="mailto:wuppertal@foodsharing.network" target="_blank">wuppertal[at]foodsharing.network</a></li>
<li>wuerselen <a href="mailto:wuerselen@foodsharing.network" target="_blank">wuerselen[at]foodsharing.network</a></li>
<li>Xanten <a href="mailto:Xanten@foodsharing.network" target="_blank">xanten[at]foodsharing.network</a></li>
</ul>
<h2></h2>
<h2>Rheinland-Pfalz <a href="mailto:rheinland.pfalz@foodsharing.network" target="_blank">rheinland.pfalz[at]foodsharing.network</a></h2>
<ul>
<li>Alzey und Region <a href="mailto:alzey@foodsharing.network" target="_blank">alzey[at]foodsharing.network</a></li>
<li>Bad D&uuml;rkheim und Region <a href="mailto:bad.duerkheim.und.region@foodsharing.network" target="_blank">bad.duerkheim.und.region[at]foodsharing.network</a></li>
<li>Bad Neuenahr-Ahrweiler&nbsp;<a href="mailto:bad.neuenahr-ahrweiler@foodsharing.network" target="_blank">bad.neuenahr-ahrweiler[at]foodsharing.network</a></li>
<li>Betzdorf und Umgebung&nbsp;<a href="mailto:betzdorf.umgebung@foodsharing.network" target="_blank">betzdorf.umgebung[at]foodsharing.network</a></li>
<li>Gr&uuml;nstadt, Leiningerland, Donnersbergkreis&nbsp;<a href="mailto:gruenstadt.leiningerland.donnersbergkreis@foodsharing.network" target="_blank">gruenstadt.leiningerland.donnersbergkreis[at]foodsharing.network</a></li>
<li>Kaiserslautern <a href="mailto:kaiserslautern@foodsharing.network" target="_blank">kaiserslautern[at]foodsharing.network</a></li>
<li>Koblenz <a href="mailto:koblenz@foodsharing.network" target="_blank">koblenz[at]foodsharing.network</a></li>
<li>Landau in der Pfalz <a href="mailto:landau.in.der.pfalz@foodsharing.network" target="_blank">landau.in.der.pfalz[at]foodsharing.network</a></li>
<li>Ludwigshafen, Frankenthal, n&ouml;rdl. Rhein-Pfalz Kreis <a href="mailto:ludwigshafen@foodsharing.network" target="_blank">ludwigshafen.frankenthal[at]foodsharing.network</a></li>
<li>Mainz <a href="mailto:mainz@foodsharing.network" target="_blank">mainz[at]foodsharing.network</a></li>
<li>Mayen <a href="mailto:mayen.rlp@foodsharing.network" target="_blank">mayen.rlp[at]foodsharing.network</a></li>
<li>Montabaur &amp; Bad Ems <a href="mailto:montabaur@foodsharing.network" target="_blank">montabaur[at]foodsharing.network</a></li>
<li>Neustadt und Landkreis S&uuml;dliche Weinstra&szlig;e <a href="mailto:neustadt.suedliche.weinstrasse@foodsharing.network" target="_blank">neustadt.suedliche.weinstrasse[at]foodsharing.network</a></li>
<li>N&ouml;rdlicher Rhein-Pfalz-Kreis <a href="mailto:noerdl.rhein.pfalz.kreis@foodsharing.network" target="_blank">noerdl.rhein.pfalz.kreis[at]foodsharing.network</a></li>
<li>Oberwesterwaldkreis&nbsp;<a href="mailto:oberwesterwaldkreis@foodsharing.network" target="_blank">oberwesterwaldkreis[at]foodsharing.network</a></li>
<li>Pirmasens <a href="mailto:pirmasens@foodsharing.network" target="_blank">pirmasens[at]foodsharing.network</a></li>
<li>Rhaunen und Umgebung <a href="mailto:rhaunen@foodsharing.network" target="_blank">rhaunen[at]foodsharing.network</a></li>
<li>Speicher, die Fidei und Umgebung <a href="mailto:speicher.fidei@foodsharing.network" target="_blank">speicher.fidei[at]foodsharing.network</a></li>
<li>Speyer und s&uuml;dl. Rhein-Pfalz Kreis <a href="mailto:speyer.suedl.rhein.pfalz.kreis@foodsharing.network" target="_blank">speyer.suedl.rhein.pfalz.kreis[at]foodsharing.network</a></li>
<li>Trier <a href="mailto:trier@foodsharing.network" target="_blank">trier[at]foodsharing.network</a></li>
<li>Wittlich &amp; Region <a href="mailto:wittlich@foodsharing.network" target="_blank">wittlich[at]foodsharing.network</a></li>
<li>Worms, Wonnegau und Region <a href="mailto:worms.wonnegau.und.region@foodsharing.network" target="_blank">worms.wonnegau.und region[at]foodsharing.network</a></li>
<li>Zweibr&uuml;cken <a href="mailto:zweibruecken@foodsharing.network" target="_blank">zweibruecken[at]foodsharing.network</a></li>
</ul>
<h2></h2>
<h2>Saarland <a href="mailto:saarland@foodsharing.network" target="_blank">saarland[at]foodsharing.network</a></h2>
<ul>
<li>Merzig-Wadern Landkreis <a href="mailto:merzig-wadern-landkreis@foodsharing.network" target="_blank">merzig-wadern-landkreis[at]foodsharing.network </a></li>
<li>Saarbr&uuml;cken <a href="mailto:saarbruecken@foodsharing.network" target="_blank">saarbruecken[at]foodsharing.network</a></li>
<li>Saarlouis Landkreis <a href="mailto:saarlouis-landkreis@foodsharing.network" target="_blank">saarlouis-landkreis[at]foodsharing.network</a></li>
<li>Sankt Ingbert <a href="mailto:sankt.ingbert@foodsharing.network" target="_blank">sankt.ingbert[at]foodsharing.network</a></li>
<li>Sankt Wendel <a href="mailto:sankt.wendel@foodsharing.network" target="_blank">sankt.wendel[at]foodsharing.network</a></li>
</ul>
<h2></h2>
<h2>Sachsen <a href="mailto:sachsen@foodsharing.network" target="_blank">sachsen[at]foodsharing.network</a></h2>
<ul>
<li>Chemnitz <a href="mailto:chemnitz@foodsharing.network" target="_blank">chemnitz[at]foodsharing.network</a></li>
<li>Dresden <a href="mailto:dresden@foodsharing.network" target="_blank">dresden[at]foodsharing.network</a></li>
<li>Freiberg <a href="mailto:freiberg@foodsharing.network" target="_blank">freiberg[at]foodsharing.network</a></li>
<li>G&ouml;rlitz&nbsp;<a href="mailto:goerlitz@foodsharing.network" target="_blank">goerlitz[at]foodsharing.network</a></li>
<li>Leipzig <a href="mailto:leipzig@foodsharing.network" target="_blank">leipzig[at]foodsharing.network</a></li>
<li>Muldentalkreis <a href="mailto:muldentalkreis@foodsharing.network" target="_blank">muldentalkreis[at]foodsharing.network</a></li>
<li>Pirna, Heidenau, K&ouml;nigstein <a href="mailto:pirna.heidenau.koenigstein@foodsharing.network" target="_blank">pirna.heidenau.koenigstein[at]foodsharing.network</a></li>
<li>Zittau <a href="mailto:zittau@foodsharing.network" target="_blank">zittau[at]foodsharing.network</a></li>
<li>Zwickau <a href="mailto:zwickau@foodsharing.network" target="_blank">zwickau[at]foodsharing.network</a></li>
</ul>
<h2></h2>
<h2>Sachsen-Anhalt <a href="mailto:sachsen.anhalt@foodsharing.network" target="_blank">sachsen.anhalt[at]foodsharing.network</a></h2>
<ul>
<li>Halberstadt <a href="mailto:halberstadt@foodsharing.network" target="_blank">halberstadt[at]foodsharing.network</a></li>
<li>Halle (Saale) <a href="mailto:halle.saale@foodsharing.network" target="_blank">halle.saale[at]foodsharing.network</a></li>
<li>Magdeburg <a href="mailto:magdeburg@foodsharing.network" target="_blank">magdeburg[at]foodsharing.network</a></li>
<li>Oschersleben (Bode) <a href="mailto:oschersleben.bode@foodsharing.network" target="_blank">oschersleben.bode[at]foodsharing.network</a></li>
<li>Wernigerode <a href="mailto:wernigerode@foodsharing.network" target="_blank">wernigerode[at]foodsharing.network</a></li>
</ul>
<h2></h2>
<h2>Schleswig-Holstein <a href="mailto:schleswig.holstein@foodsharing.network" target="_blank">schleswig.holstein[at]foodsharing.network</a></h2>
<ul>
<li>Dithmarschen <a href="mailto:Dithmarschen@foodsharing.network" target="_blank">Dithmarschen[at]foodsharing.network</a></li>
<li>Flensburg <a href="mailto:flensburg@foodsharing.network" target="_blank">flensburg[at]foodsharing.network</a></li>
<li>Herzogtum-Lauenburg <a href="mailto:Herzogtum-Lauenburg@foodsharing.network" target="_blank">Herzogtum-Lauenburg[at]foodsharing.network</a></li>
<li>Kiel <a href="mailto:kiel@foodsharing.network" target="_blank">kiel[at]foodsharing.network</a></li>
<li>Kreis Segeberg <a href="mailto:kreis.segeberg@foodsharing.network" target="_blank">kreis.segeberg[at]foodsharing.network</a>
<ul>
<li>Norderstedt <a href="mailto:norderstedt@foodsharing.network" target="_blank">norderstedt[at]foodsharing.network</a></li>
</ul>
</li>
<li>Kreis Stormarn <a href="mailto:stormarn@foodsharing.network" target="_blank">stormarn[at]foodsharing.network</a>
<ul>
<li>Bad Oldesloe <a href="mailto:bad.oldesloe@foodsharing.network" target="_blank">bad.oldesloe[at]foodsharing.network</a></li>
</ul>
</li>
<li>L&uuml;beck <a href="mailto:luebeck@foodsharing.network" target="_blank">luebeck[at]foodsharing.network</a></li>
<li>Neum&uuml;nster <a href="mailto:neumuenster@foodsharing.network" target="_blank">neumuenster[at]foodsharing.network</a></li>
<li>Nordfriesland Kreis <a href="mailto:husum@foodsharing.network" target="_blank">husum[at]foodsharing.network</a></li>
<li>Ostholstein Kreis <a href="mailto:badschwartau@foodsharing.network" target="_blank">badschwartau[at]foodsharing.network</a><br />
<ul>
<li>Neustadt Holstein <a href="mailto:NeustadtOH@foodsharing.network" target="_blank">NeustadtOH[at]foodsharing.network</a></li>
<li>Oldenburg_Holstein <a href="mailto:Oldenburg_Holstein@foodsharing.network" target="_blank">Oldenburg_Holstein[at]foodsharing.network</a></li>
</ul>
</li>
<li>Pinneberg Kreis <a href="mailto:pinneberg@foodsharing.network" target="_blank">pinneberg[at]foodsharing.network</a></li>
<li>Rendsburg-Eckernf&ouml;rde Kreis <a href="mailto:rendsburg.eckernfoerde.kreis@foodsharing.network" target="_blank">rendsburg.eckernfoerde.kreis[at]foodsharing.network</a></li>
<li>Schleswig-Flensburg Kreis <a href="mailto:schleswig@foodsharing.network" target="_blank">schleswig[at]foodsharing.network</a></li>
<li>Sylt <a href="mailto:sylt@foodsharing.network" target="_blank">sylt[at]foodsharing.network</a></li>
</ul>
<h2></h2>
<h2>Th&uuml;ringen <a href="mailto:thueringen@foodsharing.network" target="_blank">thueringen[at]foodsharing.network</a></h2>
<ul>
<li>Erfurt <a href="mailto:erfurt@foodsharing.network" target="_blank">erfurt[at]foodsharing.network</a></li>
<li>Hildburghausen <a href="mailto:hildburghausen@foodsharing.network" target="_blank">hildburghausen[at]foodsharing.network</a></li>
<li>Ilmenau <a href="mailto:ilmenau@foodsharing.network" target="_blank">ilmenau[at]foodsharing.network</a></li>
<li>Jena <a href="mailto:jena@foodsharing.network" target="_blank">jena[at]foodsharing.network</a></li>
<li>Nordhausen Landkreis <a href="mailto:nordhausen@foodsharing.network" target="_blank">nordhausen[at]foodsharing.network</a></li>
<li>Weimar <a href="mailto:weimar@foodsharing.network" target="_blank">weimar[at]foodsharing.network</a></li>
</ul>
<h1></h1>
<p></p>
<p></p>
<p>Weitere Community-Seiten:</p>
<h1><a href="https://foodsharing.de/?page=content&amp;sub=communitiesSwitzerland" target="_blank">Schweiz </a></h1>
<h1><a href="https://foodsharing.de/?page=content&amp;sub=communitiesAustria" target="_blank">&Ouml;sterreich</a></h1>
<p></p>
<p>Weitere aktive L&auml;nder:</p>
<h1><a href="mailto:Belgien@foodsharing.network" target="_blank">Belgien</a></h1>
<h1><strong><a href="mailto:niederlande@foodsharing.network" target="_blank">Niederlande</a> <br /></strong></h1>', 'last_mod' => '2020-05-08 17:58:55'],
			['id' => '53', 'name' => 'team-aktive-header', 'title' => 'Admins', 'body' => '<div class="head ui-widget-header ui-corner-top">Admins</div>
<div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">Foodsharing ist nur dank dem Engagement tausender Ehrenamtlicher m&ouml;glich! Hier stellen sich einige Menschen vor, die &uuml;berregionale Arbeitsgruppen koordinieren, die Webseite entwickeln oder andere wichtige Aufgaben f&uuml;r ganz foodsharing &uuml;bernehmen:</div>', 'last_mod' => '2018-02-01 13:23:05'],
			['id' => '54', 'name' => 'team-ehemalige-heade', 'title' => 'Ehemalige', 'body' => '<div class="head ui-widget-header ui-corner-top">Ehemalige</div>
<div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding"><br />Gemeinsam blicken wir auf eine erfolgreiche Entsehungsgeschichte von foodsharing zur&uuml;ck. Wir m&ouml;chten an dieser Stelle all den Menschen danken, die daran mitwirkten, indem sie zum Teil Vollzeit-ehrenamtlich, oder in einem sehr hohen Ma&szlig;e, f&uuml;r das Orgateam oder den Vorstand aktiv waren!</div>', 'last_mod' => '2018-02-23 09:24:21'],
			['id' => '58', 'name' => 'presse', 'title' => 'Presseinformation', 'body' => '<p><strong>Allgemeine Presseanfragen</strong><br /> Es freut uns, dass auch Sie &uuml;ber foodsharing berichten m&ouml;chten! F&uuml;r Interviews, Hintergrundinformationen oder weitere Anfragen wenden Sie sich gerne an unser ehrenamtliches <strong>Presse-Team</strong>.<br /><br /> Kerstin Bergmann<br /> <a href="mailto:presse@foodsharing.de" target="_blank">presse@foodsharing.de</a><br /><br /> Bei Spezialfragen zu einzelnen Bereichen k&ouml;nnen Sie direkt &uuml;ber unsere&nbsp;<a href="/team" target="_blank">Team-Seite</a> die verantwortliche Person herausfinden und kontaktieren.<br /><br /> Allgemeine <strong>Pressetexte</strong> finden Sie in unserem Wiki sowohl&nbsp;<a href="https://wiki.foodsharing.de/Pressetext" target="_blank">ausf&uuml;hrlich</a> als auch <a href="https://wiki.foodsharing.de/Pressetext_kurz" target="_blank">k&uuml;rzer</a>: <a href="https://wiki.foodsharing.de/Pressetext" target="_blank">https://wiki.foodsharing.de/Pressetext.</a> Aktuelle statistische Daten &uuml;ber die Nutzung der Plattform entnehmen Sie bitte <a href="/statistik" target="_blank">dieser Seite.</a><br /><br /> <strong>Fotos und Logo</strong> k&ouml;nnen Sie&nbsp;<a href="https://drive.google.com/drive/folders/0B2u0BeBttLvMYkROVjd0ejIwT1k?usp=sharing" target="_blank">hier downloaden</a>. Diese d&uuml;rfen Sie gerne unter Angabe der Quelle verwenden. Sie sind Eigentum von foodsharing. Die Urheberrechte liegen zu jeder Zeit beim foodsharing e.V.<br /><br /> <strong>Kurzbeschreibung foodsharing, November 2017:</strong><br /> Seit 2012 rettet die foodsharing-Bewegung t&auml;glich tonnenweise gute Lebensmittel vor dem M&uuml;ll. Wir verteilen sie ehrenamtlich und kostenfrei im Bekanntenkreis, der Nachbarschaft, in Obdachlosenheimen, Schulen, Kinderg&auml;rten und &uuml;ber die Plattform foodsharing.de. Unsere &ouml;ffentlich zug&auml;nglichen Regale und K&uuml;hlschr&auml;nke, sog. &bdquo;Fair-Teiler&ldquo;, stehen allen zur Verf&uuml;gung. 200.000 Menschen aus Deutschland, &Ouml;sterreich und der Schweiz nutzen regelm&auml;&szlig;ig die Internetplattform nach dem Motto: &bdquo;Teile Lebensmittel, anstatt sie wegzuwerfen!&ldquo;. Inzwischen engagieren sich dar&uuml;ber hinaus 48.000 Menschen ehrenamtlich als Foodsaver*innen, indem sie &uuml;berproduzierte Lebensmittel von B&auml;ckereien, Superm&auml;rkten, Kantinen und Gro&szlig;h&auml;ndlern abholen und verteilen. Das geschieht kontinuierlich &uuml;ber 500 Mal am Tag bei fast 5.000 Kooperationspartnern. <br /><br /></p>
<p><strong>Letzte Pressemitteilungen:</strong></p>
<p>2020-04-09&nbsp;<a href="https://drive.google.com/file/d/168Jgf1qnQ9B3qbqWk-2-fuwts_waysVV/view?usp=sharing" target="_blank">Lebensmittelrettung durch Corona-Krise erschwert: Deutsche Umwelthilfe und Foodsharing fordern sofortige Rechtssicherheit</a><br />2019-04-04 <a href="https://drive.google.com/open?id=1uw8DE0zzjmypfZHyvSDZVN_Men0uaGCO" target="_blank">55.000 Unterschriften gegen Lebensmittelverschwendung: Deutsche Umwelthilfe und foodsharing stellen Ern&auml;hrungsministerin Julia Kl&ouml;ckner Petition vor</a> <br />2019-03-05 <a href="https://drive.google.com/open?id=1JttRXiY0DAqyQdeTGZz4HNePy5-uXc-H" target="_blank">90 Prozent unverkaufter Lebensmittel wandern vom Regal in die Tonne. Das entspricht 11,5 Millionen Mahlzeiten t&auml;glich.</a> <br />2019-03-05 <a href="https://drive.google.com/open?id=1qXwnjfDet3qvk9AXyMn38tCESOGA3-HG" target="_blank">Hintergrund und Berechnung der heutigen Pressemitteilung</a> <br /> 2019-02-20 <a href="https://drive.google.com/file/d/1n6dO396ddQ1T8JWMM2XWDVG5FmP5xK5-/view?usp=sharing" target="_blank">Lebensmittel retten, aber richtig! foodsharing und Deutsche Umwelthilfe kritisieren unzureichende Strategie von Ern&auml;hrungsministerin Kl&ouml;ckner</a><br /> 2019-01-22 <a href="https://drive.google.com/file/d/1rbPi2FpUpCneiSHUdMyD7JHTqDTI5K1a/view?usp=sharing" target="_blank">Schluss mit Lebensmittelm&uuml;ll &ndash; Deutsche Umwelthilfe und foodsharing kritisieren unzureichende Strategie der Bundesregierung, Lebensmittelm&uuml;ll zu stoppen und fordern Julia Kl&ouml;ckner zum Verschwendungsfasten auf</a><br /> 2018-12-12 <a href="https://drive.google.com/file/d/10npkuFn208_OiFi8O8iq-U_ejUrcmL1i/view?usp=sharing" target="_blank">Lebensmittelverschwendung stoppen: Deutsche Umwelthilfe und foodsharing fordern verbindlichen Aktionsplan statt freiwilliger Konzernvereinbarungen</a> <br />2017-12-12 <a href="/?page=blog&amp;sub=read&amp;id=229" target="_blank">Anpacken statt Abwarten: Deutsche Umwelthilfe und foodsharing fordern verbindliche nationale Strategie zur Halbierung der Lebensmittelverschwendung bis 2030</a> zu den <a href="https://drive.google.com/open?id=13pKyQzKvcBxPeRfFSaQuYMpIQIxoX6LP" target="_blank">Bildern der Presseaktion</a>.<br /> Weitere Pressemitteilungen zum <strong>foodsharing-Geburtstag</strong> von&nbsp;<a href="http://pinkcarrotshealth.com/de/blog/don-t-let-good-food-go-bad-foodsharing-e-v-und-pink-carrots-rufen-zur-rettung-von-lebensmitteln-auf" target="_blank">PINK CARROTS</a> und der <a href="https://www.biocompany.de/neuigkeiten/gemeinsam-gegen-lebensmittelverschwendung.html" target="_blank">BIO COMPANY</a>.<br />2017-03-31<a href="/?page=blog&amp;sub=read&amp;id=227" target="_blank">&bdquo;Wegwerfstopp f&uuml;r Superm&auml;rkte&ldquo; wieder im Gespr&auml;ch; Kampagne "Leere Tonne"</a><br /> 2017-03-01 <a href="/?page=blog&amp;sub=read&amp;id=225" target="_blank">Lebensmittelverschwendung: Mit falschen Zahlen schiebt Ern&auml;hrungsminister Christian Schmidt den Verbrauchern den schwarzen Peter zu</a><br /> 2016-12-11 <a href="https://foodsharing.de/?page=blog&amp;sub=read&amp;id=222" target="_blank">Vier Jahre foodsharing: Eine Erfolgsgeschichte</a><br /> 2016-08-11 <a href="/?page=blog&amp;sub=read&amp;id=221" target="_blank">foodsharing-Festival 2016 mit &uuml;ber 600 Teilnehmenden in Berlin</a></p>', 'last_mod' => '2020-04-09 14:34:34'],
			['id' => '59', 'name' => 'Infohub', 'title' => 'Übersicht - Neuigkeiten & Informationen von foodsharing', 'body' => '<p><span>Neben dem t&auml;glichen Einsatz unserer Lebensmittelretter*innen, verstehen wir uns auch als bildungspolitische Bewegung, die sich nachhaltigen Umwelt- und Konsumzielen verpflichtet f&uuml;hlt. Daher m&ouml;chten wir Dich &uuml;ber unsere Aktivit&auml;ten sowie das Thema Lebensmittel- und Ressourcenverschwendung informieren: </span></p>
<p><span><strong><a href="https://foodsharing.de/news" target="_blank">News</a></strong>&nbsp;- wichtige Mitteilungen an die &Ouml;ffentlichkeit &uuml;ber foodsharing</span><br /> <span><strong><a href="https://foodsharing.de/faq" target="_blank">F.A.Q.</a></strong>&nbsp;- Erl&auml;uterungen von foodsharing-Abl&auml;ufen</span><br /> <span><strong><a href="https://foodsharing.de/ratgeber" target="_blank">Ratgeber</a></strong>&nbsp;- Tipps zum bewussten Umgang mit Lebensmitteln</span><br /> <span><strong><a href="https://foodsharing.de/unterstuetzung" target="_blank">Spendenaufruf</a></strong>&nbsp;- Unterst&uuml;tzung</span></p>
<p><br /><span>F&uuml;r aktuelle Berichte und Ank&uuml;ndigungen kannst Du uns auch&nbsp;<a href="https://de-de.facebook.com/foodsharing.de" target="_blank">auf facebook</a> besuchen.</span> <br /><span>Ausf&uuml;hrliche Informationen &uuml;ber unser Lebensmittelretten findest Du in <a href="https://youtu.be/dqsVjuK3rTc" target="_blank">diesem Erkl&auml;r-Video</a>.</span><br /><br /> <span>Wir w&uuml;nschen Dir viel Spass und freuen uns &uuml;ber Anregungen und Fragen!</span><br /> <span>Dein foodsharing-Team</span></p>', 'last_mod' => '2017-12-10 13:07:00'],
			['id' => '60', 'name' => 'Forderungen', 'title' => 'Don’t let good food go bad - Aktionsplan gegen Lebensmittelverschwendung', 'body' => '<p><strong>Aktionsplan</strong> mit Deutscher Umwelthilfe<br />ver&ouml;ffentlicht zum 6. foodsharing-Geburtstag am 12.12.<strong>2018</strong><br /><strong><a href="https://www.duh.de/fileadmin/user_upload/download/Projektinformation/Kreislaufwirtschaft/Lebensmittelverschwendung/181210_Aktionsplan_foodsharing_DUH_FINAL.pdf" target="_blank">zum Download</a></strong><br /><br />"Im Rahmen der Nachhaltigkeitsziele der Vereinten Nationen verpflichtete sich Deutschland dazu, die Lebensmittelabf&auml;lle im Einzelhandel und in privaten Haushalten bis 2030 um 50 % zu reduzieren 2 . Doch die deutsche Bundesregierung l&auml;sst bisher keine ernsthaften Bem&uuml;hungen erkennen, dieses Ziel zu erreichen. Das im November 2018 vom Bundesministerium f&uuml;r Ern&auml;hrung und Landwirtschaft und dem Bundesumweltministerium ver&ouml;ffentlichte Eckpunktepapier setzt auf freiwillige Zielvereinbarungen mit den Unternehmen und verz&ouml;gert so notwendige Entscheidungen. Um Deutschland vor einem erneuten Wortbruch &ndash; wie bei den Klimaschutzzielen f&uuml;r 2020 &ndash; zu bewahren, zeigen foodsharing und Deutsche Umwelthilfe mit einem Aktionsplan die Ma&szlig;nahmen auf, ohne die das Halbierungsziel nicht erreicht werden kann." <br /><br /> <strong>Stellungnahme</strong> an das Bundesministerium f&uuml;r Ern&auml;hrung und Landwirtschaft (BMEL)<br />zur Strategie gegen Lebensmittelverschwendung, vom 18. Dezember 2018<br /><strong><a href="https://drive.google.com/file/d/1O3RmGNz222SKg27O58CAZjdT3lHDygo9/view?usp=sharing" target="_blank">zum Download</a></strong></p>
<p><strong>Forderungen &amp; Vors&auml;tze aus 2017:</strong><br />--- Download der&nbsp;<a href="https://wiki.foodsharing.de/images/c/c6/Forderugen_lang_2017-12.pdf" target="_blank">ausf&uuml;hrlichen Forderungen</a> und&nbsp;<a href="https://wiki.foodsharing.de/images/1/13/Forderungen%26Vorsaetze_DUH%26foodsharing_Flyer.pdf" target="_blank">des Flyers</a> ---<br /> Lebensmittelverschwendung ist ein Skandal mit gravierenden lokalen und globalen Auswirkungen auf Klima, Umwelt und Menschen. Deswegen beschloss die UN in ihren nachhaltigen Entwicklungszielen, sie bis 2030 zu halbieren (Sustainable Development Goal 12.3). Damit wir dieses Ziel erreichen, muss viel passieren. Die Bundesregierung aus Union und SPD - aber insbesondere das CSU-gef&uuml;hrte Bundesministerium f&uuml;r Ern&auml;hrung und Landwirtschaft (BMEL) - haben die letzten Jahre verschlafen. Um glaubw&uuml;rdig und wirksam das Problem zu l&ouml;sen, richtet foodsharing zu seinem f&uuml;nfj&auml;hrigen Bestehen gemeinsam mit der&nbsp;<a href="https://www.duh.de/foodsharing/" target="_blank">Deutschen Umwelthilfe</a> <strong>f&uuml;nf Kernforderungen</strong> an die n&auml;chste Koalition.<br /><br /> <strong>Anpacken statt Abwarten:</strong> Du musst nicht auf die Bundesregierung warten! Verschenke gemeinsam mit foodsharing und Deutscher Umwelthilfe <strong>f&uuml;nf gute Vors&auml;tze</strong> f&uuml;r das kommende Jahr und setze sie selbst um! F&uuml;r einen respektvollen Umgang mit Lebensmitteln!</p>
<p>1. Wir fordern eine <b>nationale Strategie </b>der Bundesregierung, um die Lebensmittelverschwendung vom Acker bis zum Teller wirksam bis 2030 zu halbieren. Alle relevanten Akteure m&uuml;ssen bei der Entwicklung und Umsetzung eingebunden werden.</p>
<ul>
<li>Klasse statt Masse: Mit meiner <b>pers&ouml;nlichen </b> Strategie werde ich meine n&auml;chsten Eink&auml;ufe planen. Dank eines Einkaufszettels kaufe ich nur, was ich brauche und das m&ouml;glichst regional, saisonal und biologisch sowie unverpackt oder in Mehrweg-Verpackungen.</li>
</ul>
<p><br /> 2. Wir fordern eine klare Definition sowie eine <b>Erfassungs- und Dokumentationspflicht </b>von Lebensmittelverlusten, bei der alle Wegw&uuml;rfe in Landwirtschaft, Industrie und Handel erhoben werden. F&uuml;r Forschung und zum Monitoring m&uuml;ssen die Daten <b>transparent </b>zur Verf&uuml;gung stehen.</p>
<ul>
<li>Ich werde f&uuml;r zwei Wochen ein Wegwerftagebuch f&uuml;hren (z.B. respect-food.eu). Darin <b>dokumentiere</b> ich, wie viele Lebensmittel bei mir tats&auml;chlich im M&uuml;ll landen. Tierprodukte werde ich dabei besonders ber&uuml;cksichtigen, da deren Herstellung viele Ressourcen verbraucht, h&auml;ufig auf Massentierhaltung basiert und negative Auswirkungen auf Klima und Umwelt hat.</li>
</ul>
<p>3. Wegwerfen darf sich nicht mehr lohnen! Wir fordern die Bundesregierung auf, branchenspezifische und verbindliche <b>Zielmarken</b> zur schrittweisen Reduzierung des Lebensmittelabfalls in der gesamten Wertsch&ouml;pfungskette festzulegen. Um diese im Einzelhandel erreichen zu k&ouml;nnen, fordern wir schon jetzt einen <b>Wegwerfstopp: </b> Die Regierung muss Superm&auml;rkte gesetzlich verpflichten, genie&szlig;bare, aber unverkaufte Ware zuerst an soziale Organisationen zu spenden, bevor sie entsorgt werden.</p>
<ul>
<li>Anpacken statt Abwarten: Mein <b>Ziel</b> ist es, schon im n&auml;chsten Jahr nur noch halb so viel Essen wegzuwerfen wie bisher! Auf meiner Biotonne wird stehen: &bdquo;Was kostete dieser Abfall mich und die Umwelt? Wie kann ich diesen Abfall n&auml;chstes Mal vermeiden?"</li>
</ul>
<p>4. Wir fordern Rechtssicherheit und Klarstellungen f&uuml;r alle T&auml;tigkeiten der <b>Lebensmittelretter*innen</b> und Foodsharer*innen. foodsharing und unsere Fair-Teiler d&uuml;rfen von &Auml;mtern und Beh&ouml;rden nicht wie ein gewerbliches Lebensmittelunternehmen behandelt werden!</p>
<ul>
<li>Bevor ich das n&auml;chste Mal Essen entsorge, schaue ich auf foodsharing.de, ob ich es online verschenken kann. Oder ich klingel einfach in der Nachbarschaft. Dar&uuml;ber hinaus <b>engagiere</b> ich mich als Foodsaver*in aktiv gegen die Lebensmittelverschwendung und hole &uuml;berz&auml;hlige, aber noch genie&szlig;bare Produkte bei Gesch&auml;ften ab.</li>
</ul>
<p>5. Schluss mit der Symbolpolitik: Die Bundesregierung muss sich daf&uuml;r einsetzen, dass <b>Mindesthaltbarkeits- (MHD) und Verbrauchsdatum</b> f&uuml;r Verbraucher*innen klar verst&auml;ndlich sind. Daf&uuml;r sind &Auml;nderungen der Daten<b> </b>sowie wirksame Informationen der Verbraucher*innen notwendig.</p>
<ul>
<li>Teller statt Tonne: Bei meinen n&auml;chsten Eink&auml;ufen suche ich verst&auml;rkt Sonderangebote, bei denen das <b>MHD fast &uuml;berschritten</b> ist. Ich verlasse mich auf meine Sinne: Erst anschauen und riechen, dann probieren &ndash; und genie&szlig;en!</li>
</ul>
<p><i>Achtung: Nach &Uuml;berschreibten des MHD kann ein Lebensmittel problemlos verzehrt werden, solange es noch einwandfrei ist; nach Ablauf des Verbrauchsdatums (&bdquo;zu verbrauchen bis&ldquo;) ist der Verkauf eines Produktes nicht mehr zul&auml;ssig und es kann gesundheitsgef&auml;hrdend sein.</i></p>
<p><a href="https://www.duh.de/foodsharing/" target="_blank">in Kooperation mit</a><br /><img src="https://www.duh.de/typo3conf/ext/ig_project/Resources/Public/Icons/duh-logo-black.png" width="408" /></p>', 'last_mod' => '2019-03-04 10:20:38'],
			['id' => '61', 'name' => 'com_austria', 'title' => 'Communities Österreich', 'body' => '<p><strong>Liste der Ortsgrguppen in &Ouml;sterreich</strong></p>
<p></p>
<p></p>
<ul>
<li>K&auml;rnten <a href="mailto:kaernten@foodsharing.network" target="_blank">kaernten@foodsharing.network</a></li>
<li>Klagenfurt <a href="mailto:klagenfurt@foodsharing.network" target="_blank">klagenfurt@foodsharing.network</a></li>
<li>Nieder&ouml;sterreich <a href="mailto:Niederoesterreich@foodsharing.network" target="_blank">Niederoesterreich@foodsharing.network</a></li>
<li>Baden <a href="mailto:baden@foodsharing.network" target="_blank">baden@foodsharing.network</a></li>
<li>Gerasdorf <a href="mailto:gerasdorf@foodsharing.network" target="_blank">gerasdorf@foodsharing.network</a></li>
<li>Korneuburg <a href="mailto:korneuburg@foodsharing.network" target="_blank">korneuburg@foodsharing.network</a></li>
<li>Krems <a href="mailto:krems@foodsharing.network" target="_blank">krems@foodsharing.network</a></li>
<li>M&ouml;dling <a href="mailto:moedling@foodsharing.network" target="_blank">moedling@foodsharing.network</a></li>
<li>St P&ouml;lten <a href="mailto:st.poelten@foodsharing.network" target="_blank">st.poelten@foodsharing.network</a></li>
<li>Tulln <a href="mailto:tulln@foodsharing.network" target="_blank">tulln@foodsharing.network</a></li>
<li>V&ouml;sendorf <a href="mailto:voesendorf@foodsharing.network" target="_blank">voesendorf@foodsharing.network</a></li>
<li>Wiener Neustadt <a href="mailto:wiener.neustadt@foodsharing.network" target="_blank">wiener.neustadt@foodsharing.network</a></li>
<li>Ober&ouml;sterreich <a href="mailto:oberoesterreich@foodsharing.network" target="_blank">oberoesterreich@foodsharing.network</a></li>
<li>Linz <a href="mailto:linz@foodsharing.network" target="_blank">linz@foodsharing.network</a></li>
<li>Salzburg (Land) <a href="mailto:salzburg@foodsharing.network" target="_blank">salzburg@foodsharing.network</a></li>
<li>Salzburg (Stadt) <a href="mailto:salzburg.stadt@foodsharing.network" target="_blank">salzburg.stadt@foodsharing.network</a></li>
<li>Steiermark <a href="mailto:steiermark@foodsharing.network" target="_blank">steiermark@foodsharing.network</a></li>
<li>Graz <a href="mailto:graz@foodsharing.network" target="_blank">graz@foodsharing.network</a></li>
<li>Graz-Umgebung Nord <a href="mailto:graz.umgebung.nord@foodsharing.network" target="_blank">graz.umgebung.nord@foodsharing.network</a></li>
<li>Graz-Umgebung S&uuml;d <a href="mailto:graz.umgebung.sued@foodsharing.network" target="_blank">graz.umgebung.sued@foodsharing.network</a></li>
<li>Tirol <a href="mailto:tirol@foodsharing.network" target="_blank">tirol@foodsharing.network</a></li>
<li>Innsbruck <a href="mailto:innsbruck@foodsharing.network" target="_blank">innsbruck@foodsharing.network</a></li>
<li>W&ouml;rgl (Stadt) <a href="mailto:woergl@foodsharing.network" target="_blank">woergl@foodsharing.network</a></li>
<li>Wien <a href="mailto:wien@foodsharing.network" target="_blank">wien@foodsharing.network</a></li>
<li>Wien (Stadt) <a href="mailto:wien.stadt@foodsharing.network" target="_blank">wien.stadt@foodsharing.network</a></li>
</ul>', 'last_mod' => '2019-01-23 10:29:22'],
			['id' => '62', 'name' => 'com_switzerland', 'title' => 'Ortsgruppen Schweiz', 'body' => '<h1>Eine Liste der Ortsgruppen der Schweiz</h1>
<p>Die Namen der Bezirke beziehen sich jeweils auf den Kanton und nicht strikt auf die Stadt.</p>
<p></p>
<ul>
<li>Aargau&nbsp;<a href="mailto:aargau@foodsharing.network" target="_blank">aargau[at]foodsharing.network</a></li>
<li>Basel&nbsp;<a href="mailto:basel@foodsharing.network" target="_blank">basel[at]foodsharing.network</a></li>
<li>Bern&nbsp;<a href="mailto:bern@foodsharing.network" target="_blank">bern[at]foodsharing.network</a></li>
<li>Glarus <a href="mailto:glarus@foodsharing.network" target="_blank">glarus[at]foodsharing.network</a></li>
<li>Luzern&nbsp;<a href="mailto:luzern@foodsharing.network" target="_blank">luzern[at]foodsharing.network</a></li>
<li>St. Gallen&nbsp;<a href="mailto:st.gallen@foodsharing.network" target="_blank">st.gallen[at]foodsharing.network</a></li>
<li>Zug&nbsp;<a href="mailto:zug@foodsharing.network" target="_blank">zug[at]foodsharing.network</a></li>
<li>Z&uuml;rich&nbsp;<a href="mailto:zuerich@foodsharing.network" target="_blank">zuerich[at]foodsharing.network</a></li>
</ul>', 'last_mod' => '2019-11-16 15:30:01'],
			['id' => '63', 'name' => 'Transparenz', 'title' => 'Tranzparenz', 'body' => '<p></p>
<p><img src="https://lh4.googleusercontent.com/H2skmEtKwF8O6UuYDfkHzZSeW70BfRExs7qTa72v2AvTLV_fy-IAOnyrwk8sc3KnthAWgDVkBzdB5eZEvYEs1VuqNoyPaKSoxR6uKIJYPUkbvMt9VeRSQCHndChg7BhXQQuJf6qjf2tm293l2A" width="256" /></p>
<p></p>
<p><span>Transparenz ist uns wichtig. Deshalb haben wir uns der </span><span>Initiative Transparente Zivilgesellschaft</span><span> </span></p>
<p><span>des Transparency International e.V. angeschlossen. </span></p>
<p><span></span></p>
<p><span>Wir verpflichten uns die folgenden zehn Informationen der &Ouml;ffentlichkeit zur Verf&uuml;gung zu stellen und aktuell zu halten.</span></p>
<p></p>
<p><span>Hier findest Du unsere </span><a href="https://drive.google.com/open?id=0B4SK2gd-M61RWHB5eWVLQjg2cHM" target="_blank" style="text-decoration: none;"><span>Selbstverpflichtungserkl&auml;rung</span></a><span>.</span></p>
<p></p>
<p><strong>1. Name, Sitz, Anschrift und Gr&uuml;ndungsjahr: </strong></p>
<p><span>Der foodsharing e.V., Marsiliusstra&szlig;e 36, 50937 K&ouml;ln ist seit dem 12.10.2012 beim Amtsgericht K&ouml;ln im Vereinsregister auf dem Registerblatt VR 17439 eingetragen. </span></p>
<p><span>Seit dem 12.12.2012 ist die Internetplattform </span><a href="http://foodsharing.de" target="_blank" style="text-decoration: none;"><span>foodsharing.de</span></a><span> online, eine Plattform, die Privatpersonen das Teilen von &uuml;berfl&uuml;ssigen Lebensmitteln erm&ouml;glicht. Am 12.12.2014 kam es zur Fusion mit lebensmittelretten.de, &uuml;ber welche damals die Abholungen von Betrieben koordiniert wurden. Seither wird alles &uuml;ber die gemeinsame Plattform foodsharing.de organisiert.</span></p>
<p><span></span></p>
<h1><span>2. Satzung sowie weitere wesentliche Dokumente, die Auskunft dar&uuml;ber geben, welche konkreten Ziele wir verfolgen und wie diese erreicht werden:</span></h1>
<p><span>Hier kannst Du Dir ein </span><a href="https://wiki.foodsharing.de/images/0/09/Satzung_foodsharing_e.V._2016-12-04.pdf" target="_blank" style="text-decoration: none;"><span>PDF der Satzung </span></a><span>herunterladen.</span></p>
<p><span>Au&szlig;erdem findest Du hier unsere </span><a href="https://wiki.foodsharing.de/Grunds%C3%A4tze" target="_blank" style="text-decoration: none;"><span>foodsharing - Grunds&auml;tze</span></a><span>. </span></p>
<p><span>&Uuml;ber unsere Wirkungsr&auml;ume kannst Du Dich in unserem WIKI (z.B. unter </span><a href="https://wiki.foodsharing.de/Pressetext" target="_blank" style="text-decoration: none;"><span>https://wiki.foodsharing.de/Pressetext</span></a><span>) informieren </span></p>
<p></p>
<p><strong>3. Bescheid vom Finanzamt &uuml;ber die Anerkennung als steuerbeg&uuml;nstigte, gemeinn&uuml;tzige K&ouml;rperschaft:</strong></p>
<p><span>Der foodsharing e. V. dient steuerbeg&uuml;nstigten gemeinn&uuml;tzigen Zwecken im Sinne der &sect;&sect; 51 ff. AO. Die Satzungszwecke entsprechen &sect; 52 Abs. 2 Satz 1 Nr. 7 und 16 AO. </span></p>
<p><span>Hier kannst Du Dir ein </span><span>PDF des aktuellen Steuerfreistellungsbescheids </span><span>herunterladen.</span><span><br /></span><span>&nbsp; </span></p>
<p><strong>4. Personen und Aufgaben des Vorstands:</strong></p>
<p><span>Informationen zu unserem Vorstand und zu Verantwortungsbereichen findest Du unter dem Men&uuml;punkt </span><a href="https://foodsharing.de/team" target="_blank" style="text-decoration: none;"><span>&Uuml;ber Uns/Team</span></a><span> und im</span><span> </span><a href="https://foodsharing.de/impressum" target="_blank" style="text-decoration: none;"><span>Impressum</span></a><span>. </span></p>
<p><span></span></p>
<p><strong>5. Bericht &uuml;ber unsere T&auml;tigkeiten:</strong></p>
<p><span>Hier kann Du Dir die &ouml;ffentlichen </span><span>Protokolle der Vorstands-Telefonkonferenzen</span><span> des Vorstandes des foodsharing e.V. ansehen, die alle 14 Tage stattfinden. </span></p>
<p><span>Hier kann Du Dir au&szlig;erdem die </span><a href="https://drive.google.com/open?id=0B4SK2gd-M61ROVMwTHdtQ3RtVWM" target="_blank" style="text-decoration: none;"><span>Protokolle der Mitgliederversammlungen</span></a><span> des foodsharing e.V. ansehen. </span></p>
<p><span>Weitere Informationen &uuml;ber die Aktionen von foodsharing findest Du in unserem </span><a href="https://foodsharing.de/news" target="_blank" style="text-decoration: none;"><span>News-Blog</span></a><span>. </span></p>
<p><span></span></p>
<p><span><strong>6. Personalstruktur:</strong> </span></p>
<p><span>Unter dem Men&uuml;punkt </span><a href="https://foodsharing.de/team" target="_blank" style="text-decoration: none;"><span>&Uuml;ber Uns/Team</span></a><span> findest Du die Fotos und Aufgaben der Mitglieder des gesch&auml;ftsf&uuml;hrenden und des erweiterten Vorstands und weiterer Personen, die sich auf Bundesebene besonders aktiv f&uuml;r foodsharing engagieren. </span></p>
<p><span></span></p>
<p><span>Der Vorstand sowie Botschafter/innen der einzelnen St&auml;dte, Betriebsverantwortliche und alle Foodsaver/innen arbeiten bisher ausschlie&szlig;lich ehrenamtlich. </span><span><br /></span><span>F&uuml;r die im Vorstand, also auf Bundesebene, Arbeitenden m&ouml;chten wir m&ouml;glichst in naher Zukunft eine minimale Bezahlung erm&ouml;glichen. So soll gew&auml;hrleistet werden, dass ein langfristiges Engagement weiterhin f&uuml;r alle m&ouml;glich bleibt.</span></p>
<p><span><br /></span><span>Auf regionaler Ebene wird den Ortsgruppen freigestellt selbstst&auml;ndig dar&uuml;ber zu entscheiden, ob Gelder f&uuml;r eine Bezahlung (zb. von Botschaftern) gesammelt werden, oder ob das umfassende Engagement weiterhin rein ehrenamtlich strukturiert werden soll.</span></p>
<p></p>
<p><strong>7. Mittelherkunft:</strong></p>
<p><span>Informationen &uuml;ber die Mittelherkunft von 2015 bis 2017 findest Du hier:</span></p>
<p><a href="https://drive.google.com/open?id=1DWEL3uu5SpFL5BI5J9CJdZwKurQyxL8CGJB4_bfr5JU" target="_blank" style="text-decoration: none;"><span>Mittelherkunft 2015 und 201</span></a><span>6 (ins WIKI)</span></p>
<p><span></span></p>
<p><strong>8. Mittelverwendung:</strong></p>
<p><span>Einen &Uuml;berblick &uuml;ber die Mittelverwendung bekommst Du anhand des &nbsp;j&auml;hrlichen Jahresabschluss (Einnahmen-&Uuml;berschuss-Rechnungen): </span></p>
<p><a href="https://drive.google.com/open?id=1d83EPjgne-kxW3eZK_QRJgRpwlr7dqmj" target="_blank" style="text-decoration: none;"><span>Jahresabschluss 2015</span></a><span> (ins wiki)</span></p>
<p><a href="https://drive.google.com/open?id=1egLELuRs7e1TedbZft5ibJSxaTv9ubIm" target="_blank" style="text-decoration: none;"><span>Jahresabschluss 2016</span></a><span> (ins wiki)</span><span><br /><br /></span></p>
<p><strong>9. Gesellschaftsrechtliche Verbundenheit:</strong></p>
<p><span>Der foodsharing e. V. f&uuml;hlt sich seinen </span><a href="https://foodsharing.de/partner" target="_blank" style="text-decoration: none;"><span>Kooperationspartnern/innen</span></a><span> sowie </span><a href="https://foodsharing.de/unterstuetzung" target="_blank" style="text-decoration: none;"><span>Unterst&uuml;tzern/innen </span></a><span>verbunden. </span><span><br /></span><span>&nbsp; </span></p>
<p><strong>10. Zuwendungen, die mehr als 10 Prozent der Gesamtjahreseinnahmen ausmachen:</strong></p>
<p><span>Diese sind in der Mittelherkunft 2015 bis 2017 dargestellt (s. Punkte 7.)</span></p>
<p></p>', 'last_mod' => '2018-05-02 11:20:47'],
			['id' => '64', 'name' => 'datenschutzbelehrung', 'title' => '-- ignored --', 'body' => '<p>Betriebsverantwortliche und Botschafter*innen haben zur Erf&uuml;llung ihrer Aufgaben Zugriff auf bestimmte personenbezogene Daten anderer Nutzer*innen. In dem Zusammenhang m&uuml;ssen wir dich auf folgenden Sachverhalt hinweisen:</p>
<p>Personenbezogene Daten, zu denen du im Rahmen deiner T&auml;tigkeit bei Foodsharing Zugang erh&auml;ltst oder Kenntnis erlangst, sind nach Art. 5 Abs. 1, Art. 32 Abs. 4 Datenschutz-Grundverordnung (DSGVO) vertraulich zu behandeln. Nach &sect; 53 BDSG ist es untersagt, personenbezogene Daten unbefugt zu verarbeiten oder zu nutzen (Datengeheimnis).</p>
<p>S&auml;mtliche im Rahmen deiner T&auml;tigkeit bei Foodsharing erlangte personenbezogene Daten sind nur zum vorgesehenen Zweck und in &Uuml;bereinstimmung mit&nbsp;<a href="/?page=legal" target="_blank">unserer Datenschutzerkl&auml;rung</a> sowie den geltenden Datenschutzgesetzen (<a href="https://www.bgbl.de/xaver/bgbl/start.xav?startbk=Bundesanzeiger_BGBl&amp;jumpTo=bgbl117s2097.pdf" target="_blank">BDSG</a> und <a href="https://eur-lex.europa.eu/legal-content/DE/TXT/PDF/?uri=CELEX:32016R0679&amp;from=EN" target="_blank">DSGVO</a>) zu verwenden. Sie sind insbesondere dahingehend vertraulich zu behandeln, dass sie unbefugten Dritten nicht ohne Einwilligung der betroffenen Person zug&auml;nglich gemacht werden d&uuml;rfen. Ausnahmen sind in Ziffer 3 unserer Datenschutzvereinbarung sowie in Art. 6 Abs. 1 DSGVO geregelt.</p>
<p>Diese Verpflichtung besteht auch nach Beendigung deiner T&auml;tigkeit bei Foodsharing fort.</p>
<p>Verst&ouml;&szlig;e gegen die Vertraulichkeit k&ouml;nnen nach geltender Rechtslage mit Freiheits- oder Geldstrafe geahndet werden (vgl. insbes. &sect;&sect; 42, 43 BDSG).</p>', 'last_mod' => '2018-05-24 18:25:28'],
			['id' => '66', 'name' => 'petition-fasten', 'title' => 'foodsharing-Städte', 'body' => '<p><img src="https://cloud.foodsharing.network/s/3WaqTgfSri2WgJd/preview" width="627" /></p>
<h2><a href="https://www.foodsharing-staedte.org/de" target="_blank">www.foodsharing-staedte.org</a></h2>
<h2></h2>
<p></p>
<p>Worum geht es?<br />foodsharing ist mehr als Lebensmittelretten! Und genau das wollen wir darstellen: Mit der Bewegung "foodsharing-St&auml;dte" m&ouml;chten wir den Austausch zwischen den St&auml;dten f&ouml;rdern und mit Ideen dazu anregen, gemeinsam lokal gegen Lebensmittelverschwendung aktiv zu werden. Ein besonders gro&szlig;es Anliegen ist uns dabei die Zusammenarbeit zwischen der Zivilgesellschaft (foodsaver*innen, sowie Bewohner*innen der Stadt, die bisher noch keine Ber&uuml;hrung mit foodsharing hatten) und der &ouml;ffentlichen Hand (also der Stadt-/Gemeindeverwaltung, Abgeordneten und B&uuml;rgermeister*innen).</p>
<p></p>
<p>Du bist neugierig geworden? Dann schau einfach auf unserer Website vorbei: <a href="https://www.foodsharing-staedte.org/de" target="_blank">www.foodsharing-staedte.org</a></p>', 'last_mod' => '2020-03-08 12:13:36'],
			['id' => '67', 'name' => 'Freundeskreis', 'title' => 'Freundeskreis', 'body' => '<p>Inhalt hier einsetzen.&nbsp;</p>', 'last_mod' => '2019-01-22 16:48:59'],
			['id' => '68', 'name' => 'transparenz', 'title' => 'transparenz', 'body' => '<p>Hier erscheinen in K&uuml;rze die Information zur Initiative Transparente Zivilgesellschaft</p>', 'last_mod' => '2019-02-17 18:02:05'],
			['id' => '69', 'name' => 'Akademie', 'title' => 'foodsharing-Akademie für Lebensmittel-Wertschätzung', 'body' => '<p></p>
<h3><a href="https://www.foodsharing-akademie.org/" target="_blank">www.foodsharing-akademie.org</a></h3>
<p></p>
<ol>
<li>Download des Materials f&uuml;r die Bildungsarbeit</li>
<li>Was ist die foodsharing-Akademie?</li>
<li>Kontakt, Anfragen &amp; mitmachen</li>
<li>Akademie: Warum und wof&uuml;r?</li>
<li>Unterst&uuml;tzung</li>
</ol>
<p></p>
<h3><b>1. Download des foodsharing-Materials f&uuml;r die Bildungsarbeit</b></h3>
<p>Die &bdquo;Vorratskammer&ldquo; ist eine Materialsammlung der foodsharing-Akademie. Darin sind sowohl Methoden und Fortbildungen enthalten, als auch globale Hintergr&uuml;nde wie Studien oder eine Zeittafel zur Entwicklung der Lebensmittelverschwendung in Deutschland. <a href="https://drive.google.com/open?id=1lTme5ofRy1H09H9NYTcLnkh4grevshd1" target="_blank">Zum Download</a> <br />Zur ersten Multiplikator*innen-Fortbildung entstand eine informative Dokumentation. <a href="https://drive.google.com/open?id=1n5JIF_IdYc-rfE6PrAix5uL3EGEMxrYc" target="_blank">Zum Download</a></p>
<h3><b>2. Was ist die foodsharing-Akademie?</b></h3>
<p>In &uuml;ber 300 Orten rettet foodsharing Lebensmittel. <span>Die Akademie vernetzt die Freiwilligen, stellt Material f&uuml;r Workshops und Vortr&auml;ge zur Verf&uuml;gung und gibt umfassende Informationen zu relevanten Themen f&uuml;r die Freiwilligen. Dazu f&uuml;hren wir Fortbildungen f&uuml;r Menschen durch, die sich gegen Lebensmittelverschwendung einsetzen. In der Online-Arbeitsgruppe &bdquo;Bildung&ldquo; k&ouml;nnen sich Referent*innen zudem &uuml;ber Methoden und pers&ouml;nliche Erfahrungen austauschen.<br /></span><br />&nbsp;&nbsp; Dadurch m&ouml;chten wir nachhaltig etwas ver&auml;ndern. Kinder lernen bereits fr&uuml;h den Wert von Lebensmitteln sch&auml;tzen. Ortsgruppen sollen Unterst&uuml;tzung zu Vereinsrecht oder Moderationen bekommen. Und auch in Unternehmen m&ouml;chten wir Essen vor der Tonne bewahren &ndash; auch durch (Fort)Bildung und Informationen als Erg&auml;nzung zum Retten.</p>
<h3><b>3. Kontakt, Anfragen &amp; mitmachen</b></h3>
<p>Du hast Fragen, m&ouml;chtest mitwirken oder suchst eine*n Referent*in f&uuml;r einen Workshop oder Vortrag? Unter <a href="mailto:bildung@foodsharing.de" target="_blank">akademie@foodsharing.de</a> kannst Du Kontakt mit uns aufnehmen. Unter &bdquo;<a href="/?page=content&amp;sub=workshops" target="_blank">Vortr&auml;ge und Workshops</a>&ldquo; gibt es dazu weitere Informationen.</p>
<h3><b>4. Akademie: Warum und wof&uuml;r?</b></h3>
<p>Durch unsere Abholungen sch&uuml;tzen wir aufw&auml;ndig produzierte Ware vor der sinnlosen Entsorgung. Damit sensibilisieren wir sowohl die involvierten Mitarbeitenden der Unternehmen als auch die Abholenden, an die wir &bdquo;Essensk&ouml;rbe&ldquo; verteilen. Mit der foodsharing-Akademie m&ouml;chten wir auch Menschen dar&uuml;ber hinaus erreichen. Beispielsweise Kinder und Jugendliche, die statistisch mehr verschwenden als &auml;ltere Generationen. <br />&nbsp;&nbsp; Wir vermitteln den Wert unserer LEBENsmittel. Wie wird eigentlich ein Brot hergestellt? Wie viele Ressourcen stecken in einer Milch? Darauf aufbauend schauen wir uns den verantwortungsvollen Umgang damit an. Sowohl &bdquo;Reste&ldquo;verwertung oder die richtige Lagerung k&ouml;nnen dabei entscheidend sein. Die foodsharing-Akademie schult Multiplikator*innen f&uuml;r Workshops und Vortr&auml;ge und vermittelt diese an anfragende Interessierte.</p>
<h3><b>5. Unterst&uuml;tzung</b></h3>
<p>Unsere Erfolge haben wir durch ehrenamtliches Engagement erreicht. F&uuml;r manche Projekte m&uuml;ssen wir jedoch Geld in die Hand nehmen: Bei Fortbildungen der foodsharing-Akademie ben&ouml;tigen wir beispielsweise ein Seminarhaus oder Fachreferent*innen. Da foodsharing weitgehend geldfrei entstand, haben wir bisher wenig Einnahmen. Deswegen macht Deine kleine Spende mit dem Stichwort &bdquo;Akademie&ldquo; einen gro&szlig;en Unterschied! <a href="/unterstuetzung" target="_blank">Zur Spendenseite</a></p>', 'last_mod' => '2019-12-19 20:59:10'],
			['id' => '71', 'name' => 'Vorträge & Workshops', 'title' => 'Bildungs-Anfragen: Vorträge & Workshops zu Essensverschwendung', 'body' => '<p><span>Bestimmt gibt es auch in Deiner N&auml;he motivierte und kompetente FoodsaverInnen, die Du f&uuml;r einen Workshop oder Vortrag gewinnen kannst! Unsere Freiwilligen k&ouml;nnen Dir nicht nur aus der Praxis berichten und Anekdoten aus dem &bdquo;Retteralltag&ldquo; erz&auml;hlen &ndash; sondern kennen sich auch gut mit Lebensmittelverschwendung aus. </span><b>Wir f&ouml;rdern die Wertsch&auml;tzung f&uuml;r&lsquo;s Essen, indem wir Vortr&auml;ge, Workshops oder Seminare anbieten. </b><a href="mailto:bildung@foodsharing.de" target="_blank"><b>Frag uns einfach an!</b></a><span></span> <br /><br /> In diesen Bildungsveranstaltungen ist, abh&auml;ngig von den Erfahrungswerten und Wissensschwerpunkten der Foodsaver vor Ort, vieles m&ouml;glich. Folgende Bausteine sind denkbar:</p>
<ul>
<li>w&auml;hrend einer Reise vom Feld bis zum Teller gehen wir Stationen unserer Nahrungsmittel durch. Dabei verdeutlichen wir, was eigentlich drin steckt im Essen und n&ouml;tig ist, um es herzustellen</li>
<li><span>bei einer </span><span>Supermarktrallye </span><span>hinterfragen wir die angebotenen Produkte in einem Supermarkt etwas genauer</span></li>
<li><span>beim Klassiker &bdquo;</span><span>Wie r&auml;ume ich meinen K&uuml;hlschrank ein?</span><span>&ldquo; befassen wir uns mit der richtigen Lagerung von Lebensmitteln<br /></span></li>
</ul>
<p>Insgesamt ist uns dabei wichtig, nach vorne zu schauen: Was k&ouml;nnen wir machen und bewegen? Je nach Zielgruppe &uuml;berlegen wir entweder, wie jede*r von uns Essen wertsch&auml;tzen kann. Oder wir entwickeln M&ouml;glichkeiten, wie Deine Schule, Institution oder Mensa Lebensmittel vor der Tonne bewahren kann. Aber auch der gr&ouml;&szlig;ere Blick auf Deine Kommune oder die Bundesebene sind m&ouml;glich. <br /><br /> Was genau wir machen, entscheidest Du im Kontakt mit den Referent*innen. Damit wir den Kontakt herstellen k&ouml;nnen, schreib uns einfach eine Mail: <a href="mailto:bildung@foodsharing.de" target="_blank">bildung@foodsharing.de</a> (Datenschutzhinweis: Deine Mail wird intern weitergeleitet) <br /> Sofern Du schon eine konkrete Veranstaltung im Blick hast, helfen uns Informationen zu:</p>
<ul>
<li>Zielgruppe (Anzahl, Altersgruppe, Kontext/Institution &hellip;)</li>
<li>inhaltlichem Schwerpunkt</li>
<li>Zielen</li>
<li>Zeit (Dauer, regelm&auml;&szlig;iges Unterrichtsfach, &hellip;)</li>
</ul>
<p>Du m&ouml;chtest selber eine Veranstaltung leiten und suchst Material? Oder Du bist an der foodsharing-Akademie interessiert? <a href="/?page=content&amp;sub=academy" target="_blank">Hier entlang!</a></p>', 'last_mod' => '2019-06-15 12:36:59'],
			['id' => '72', 'name' => 'fs_Festival', 'title' => 'foodsharing Festival', 'body' => '<p><img src="https://foodsharing.de/images/wallpost/5c6ae934017da.png" width="195" /></p>
<p></p>
<p>Eine wachsende foodsharing Gemeinschaft braucht Orte, um sich pers&ouml;nlich zu begegnen. Aus den ersten gro&szlig;en Berlin-Treffen wurden Internationale Treffen und schlie&szlig;lich das foodsharing Festival, was seit 2016 in der wundersch&ouml;nen Malzfabrik in Berlin stattfindet. Das Festival erm&ouml;glicht Austausch und Vernetzung zwischen den Besuchenden &ndash; seien es langj&auml;hriger Foodsaver*innen oder absolute Neulinge. Alle aktuellen Infos und R&uuml;ckblicke aus den letzten Jahren findest du hier: <a href="http://www.foodsharing-festival.org/" target="_blank">www.foodsharing-festival.org</a></p>
<p></p>
<p><img src="https://foodsharing.de/images/wallpost/5c6a98f8ad792.jpg" width="627" /></p>', 'last_mod' => '2019-03-12 09:16:49'],
			['id' => '73', 'name' => 'Kontakt', 'title' => 'Kontakt', 'body' => '<div class="head ui-widget-header ui-corner-top">Kontaktanfragen:</div>
<div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">Du kannst gerne Kontakt mit uns aufnehmen! Wir bitten Dich aber, Dein Anliegen nur einer Person zu schreiben. <br />F&uuml;r <strong>allgemeine Anfragen</strong> stehen wir Dir unter <a href="mailto:info@foodsharing.de" target="_blank">info@foodsharing.de</a> (oder <a href="mailto:info@foodsharingschweiz.ch" target="_blank">info@foodsharingschweiz.ch</a> f&uuml;r die Schweiz) zur Verf&uuml;gung oder leiten Dich gerne an passende Ansprechpersonen &ndash; auch in foodsharing-Bezirken &ndash; weiter.<br /><br />Hast Du Probleme mit der Website oder Deinem Account, wende Dich bitte an&nbsp;<a href="mailto:it@foodsharing.network" target="_blank">it@foodsharing.network</a><br /><br />Ein paar direkte, lokale Anlaufstellen finden sich hier: <br /><br />
<ul>
<li><strong><a href="/?page=content&amp;sub=communitiesGermany" target="_blank">Deutschland </a></strong></li>
</ul>
<ul>
<li><strong><a href="/?page=content&amp;sub=communitiesAustria" target="_blank">&Ouml;sterreich</a></strong></li>
</ul>
<ul>
<li><strong><a href="/?page=content&amp;sub=communitiesSwitzerland" target="_blank">Schweiz</a></strong></li>
</ul>
<br /><br /> If you are interested in carrying the idea of sharing and saving food around the world (or to your own country) contact: <a href="mailto:foodsaving@yunity.org" target="_blank">foodsaving@yunity.org</a><a target="_blank">&nbsp;</a></div>', 'last_mod' => '2019-06-15 12:47:44'],
			['id' => '74', 'name' => 'International', 'title' => 'International', 'body' => '<p>If you are interested in carrying the idea of sharing and saving food around the world (or to your own country) contact: <a href="mailto:foodsaving@yunity.org" target="_blank">foodsaving@yunity.org</a><a target="_blank"> <br /><br /></a></p>', 'last_mod' => '2019-02-17 18:32:04'],
			['id' => '75', 'name' => 'datenschutz-cloud', 'title' => 'Datenschutzerklärung foodsharing cloud', 'body' => '<h3><span>Datenschutzerkl&auml;rung foodsharing cloud</span></h3>
<p></p>
<h4><span>1. Name und Kontaktdaten des f&uuml;r die Verarbeitung Verantwortlichen sowie des betrieblichen Datenschutzbeauftragten</span></h4>
<p><span>Diese Datenschutzinformation gilt f&uuml;r die Datenverarbeitung durch</span></p>
<p><span>foodsharing e. V.</span><span><br /></span><span>Marsiliusstra&szlig;e 36</span><span><br /></span><span>50937 K&ouml;ln</span></p>
<p><span>und f&uuml;r folgende Internetseiten (inkl. Subdomains):</span></p>
<p><span>cloud.foodsharing.network</span></p>
<p><span>Der betriebliche Datenschutzbeauftragte ist unter der o. g. Anschrift, z. Hd. Abteilung Datenschutz, bzw. per E-Mail unter datenschutz@foodsharing.de erreichbar.</span></p>
<h4><span>2. Erhebung und Speicherung personenbezogener Daten sowie Art und Zweck von deren Verwendung</span></h4>
<h5><span>a) Beim Besuch der Website</span></h5>
<p><span>Beim Aufruf der o. g. Websites werden durch den auf deinem Endger&auml;t zum Einsatz kommenden Browser automatisch Informationen an den Server unserer Website gesendet. Diese Informationen werden tempor&auml;r in einem sog. Logfile gespeichert. Folgende Informationen werden dabei ohne dein Zutun erfasst und bis zur automatisierten L&ouml;schung gespeichert:</span></p>
<ul>
<li>
<p><span>IP-Adresse des anfragenden Rechners,</span></p>
</li>
<li>
<p><span>Datum und Uhrzeit des Zugriffs,</span></p>
</li>
<li>
<p><span>Name und URL der abgerufenen Datei,</span></p>
</li>
<li>
<p><span>Website, von der aus der Zugriff erfolgt (Referrer-URL),</span></p>
</li>
<li>
<p><span>verwendeter Browser und ggf. das Betriebssystem deines Rechners sowie</span></p>
</li>
<li>
<p><span>der Name deines Zugangsanbieters.</span></p>
</li>
</ul>
<p><span>Die genannten Daten werden durch uns zu folgenden Zwecken verarbeitet:</span></p>
<ul>
<li>
<p><span>Gew&auml;hrleistung eines reibungslosen Verbindungsaufbaus der Website,</span></p>
</li>
<li>
<p><span>Gew&auml;hrleistung einer komfortablen Nutzung unserer Website,</span></p>
</li>
<li>
<p><span>Auswertung der Systemsicherheit und -stabilit&auml;t sowie</span></p>
</li>
<li>
<p><span>zu weiteren administrativen Zwecken.</span></p>
</li>
</ul>
<p><span>Die Rechtsgrundlage f&uuml;r die Datenverarbeitung ist Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;f DSGVO. Unser berechtigtes Interesse folgt aus oben aufgelisteten Zwecken zur Datenerhebung. In keinem Fall verwenden wir die erhobenen Daten zu dem Zweck, R&uuml;ckschl&uuml;sse auf deine Person zu ziehen.</span></p>
<p><span>Dar&uuml;ber hinaus setzen wir beim Besuch unserer Website Cookies sowie Analysedienste ein. N&auml;here Erl&auml;uterungen dazu erh&auml;ltst du unter den Ziffern&nbsp;4 und 5 dieser Datenschutzerkl&auml;rung.</span></p>
<h5><span>b) Bei der Anmeldung zum Cloud-Dienst</span></h5>
<p><span>Foodsharing stellt seinen Nutzer*innen unter cloud.foodsharing.network einen eigenen Cloud-Dienst auf Basis der frei verf&uuml;gbaren Software Nextcloud zur Verf&uuml;gung. Die Nutzung dieses Dienstes ist freiwillig, nutzt du ihn, stimmst du gem&auml;&szlig; Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;a DSGVO der Speicherung und Verarbeitung der von dir &uuml;bermittelten Daten auf unseren Servern zu. Zur Anmeldung ist die Angabe einer g&uuml;ltigen E-Mail-Adresse und eines Benutzernamens notwendig. Weitere Angaben zu deiner Person sind freiwillig.</span></p>
<h5><span>c) Bei der Nutzung des Cloud-Dienstes</span></h5>
<p><span>Du kannst in deiner pers&ouml;nlichen Umgebung Dateien (Dokumente, Bilder etc.) sowie Kontakte und Termine im Rahmen des dir zur Verf&uuml;gung stehenden Speicherplatzes hochladen. Deine pers&ouml;nlichen Dateien werden verschl&uuml;sselt gespeichert, solange du die Dateien, Kontakte oder Termine nicht explizit f&uuml;r andere Nutzer*innen freigibst, hast nur du Zugriff darauf. Gibst du Dateien, Kontakte oder Termine anderen Nutzer*innen frei, geschieht dies ebenfalls mit deiner Einwilligung auf Grundlage von Art. 6. Abs. 1. S. 1 lit. a DSGVO. Das gleiche gilt f&uuml;r die Nutzung gemeinsam genutzter Elemente, die andere Nutzer*innen mit dir teilen.</span></p>
<p><span>Du verpflichtest dich, keine strafbaren Inhalte in der Cloud zu speichern und bei der Weitergabe personenbezogener Daten die geltenden Datenschutzvorgaben zu beachten. Dies bedeutet insbesondere, dass du bspw. Kontaktdaten nur mit ausdr&uuml;cklicher Einwilligung der sie betreffenden Personen speichern und an Dritte weitergeben darfst.</span></p>
<h5><span>d) E-Mail-Benachrichtigungen</span></h5>
<p><span>Du willigst ein, dass deine angegebene E-Mail-Adresse dazu verwendet werden kann, dich bei bestimmten Ereignissen zu informieren. In den Nutzereinstellungen kannst du selbst festlegen, bei welchen Ereignissen dich das System informieren soll. Dort lassen sich auf Wunsch auch s&auml;mtliche Ereignisse deaktivieren.</span></p>
<h5><span>e) Datenexport und -l&ouml;schung</span></h5>
<p><span>Du kannst jederzeit selbst in den Einstellungen einen vollst&auml;ndigen Export deiner gespeicherten Daten oder die vollst&auml;ndige L&ouml;schung derselben anfordern.</span></p>
<h4><span>3. Weitergabe von Daten</span></h4>
<p><span>Eine &Uuml;bermittlung deiner pers&ouml;nlichen Daten an Dritte zu anderen als den im Folgenden aufgef&uuml;hrten Zwecken findet nicht statt.</span></p>
<p><span>Wir geben deine pers&ouml;nlichen Daten nur an Dritte weiter, wenn:</span></p>
<p><span>&bull; Du deine nach Art. 6 Abs. 1 S. 1 lit. a DSGVO ausdr&uuml;ckliche Einwilligung dazu erteilt hast,</span></p>
<p><span>&bull; die Weitergabe nach Art. 6 Abs. 1 S. 1 lit. f DSGVO zur Geltendmachung, Aus&uuml;bung oder Verteidigung von Rechtsanspr&uuml;chen erforderlich ist und kein Grund zur Annahme besteht, dass du ein &uuml;berwiegendes schutzw&uuml;rdiges Interesse an der Nichtweitergabe deiner Daten hast,</span></p>
<p><span>&bull; f&uuml;r den Fall, dass f&uuml;r die Weitergabe nach Art. 6 Abs. 1 S. 1 lit. c DSGVO eine gesetzliche Verpflichtung besteht, sowie</span></p>
<p><span>&bull; dies gesetzlich zul&auml;ssig und nach Art. 6 Abs. 1 S. 1 lit. b DSGVO f&uuml;r die Abwicklung von Vertragsverh&auml;ltnissen mit dir erforderlich ist.</span></p>
<p><span>Dies erstreckt sich jedoch nur auf Anmeldedaten und &ouml;ffentlich freigegebene Daten. Deine privaten Daten sind serverseitig verschl&uuml;sselt gespeichert und k&ouml;nnen nur mit deinem pers&ouml;nlichen Passwort entschl&uuml;sselt werden. (Das hei&szlig;t insbesondere auch: Vergisst oder verlierst du deine Zugangsdaten, gibt es keine M&ouml;glichkeit mehr, an deine in der Cloud gespeicherten privaten Daten heranzukommen. Bewahre daher den Wiederherstellungsschl&uuml;ssel gut auf.)</span></p>
<h4><span>4. Aktive Inhalte und Cookies</span></h4>
<h5><span>a) JavaScript</span></h5>
<p><span>JavaScript ist eine clientseitige Skriptsprache, die verwendet wird, um Benutzerinteraktion durchzuf&uuml;hren bzw. auszuwerten, Seiteninhalte zu ver&auml;ndern, nachzuladen oder zu generieren. Unser Cloudsystem verwendet JavaScript zu ebendiesen Zwecken. Die &Uuml;bertragung von Daten zwischen deinem Browser und der Anwendung erfolgt dabei verschl&uuml;sselt (vgl. Ziff.&nbsp;9). Die meisten Browser akzeptieren JavaScript automatisch. Du kannst deinen Browser jedoch so konfigurieren, dass keine aktiven Inhalte auf deinem Computer ausgef&uuml;hrt werden. Die vollst&auml;ndige Deaktivierung von JavaScript wird jedoch dazu f&uuml;hren, dass du nicht alle Funktionen der Cloud wie vorgesehen nutzen kannst.</span></p>
<h5><span>b) Cookies</span></h5>
<p><span>Wir setzen auf unserer Seite Cookies ein. Hierbei handelt es sich um kleine Dateien, die dein Browser automatisch erstellt und die auf deinem Endger&auml;t (Laptop, Tablet, Smartphone o.&nbsp;&auml;.) gespeichert werden, wenn du unsere Seite besuchst. Cookies richten auf deinem Endger&auml;t keinen Schaden an, enthalten keine Viren, Trojaner oder sonstige Schadsoftware.</span></p>
<p><span>In dem Cookie werden Informationen abgelegt, die sich jeweils im Zusammenhang mit dem spezifisch eingesetzten Endger&auml;t ergeben. Damit k&ouml;nnen wir erkennen, wenn Benutzer im Rahmen einer Sitzung oder auch sp&auml;ter erneut Seiten unserer Plattform aufrufen. Dies bedeutet, dass wir Benutzer &uuml;ber die Dauer ihres Besuchs wiedererkennen k&ouml;nnen, jedoch nicht, dass wir dadurch unmittelbar Kenntnis von deren Identit&auml;t erhalten. Dies ist nur im Zusammenhang mit einer expliziten personenbezogenen Anmeldung (siehe Ziffer&nbsp;2b) m&ouml;glich.</span></p>
<p><span>Der Einsatz von Cookies dient einerseits dazu, die Nutzung unseres Angebots f&uuml;r dich angenehmer zu gestalten. So setzen wir sogenannte Session-Cookies zur Ablaufsteuerung &nbsp;und der &Uuml;bertragung eingegebener Daten an Folgeseiten ein. Diese werden gel&ouml;scht, wenn du dich von der Seite abmeldest oder deinen Webbrowser schlie&szlig;t.</span></p>
<p><span>Dar&uuml;ber hinaus setzen wir ebenfalls zur Optimierung der Benutzerfreundlichkeit tempor&auml;re Cookies ein, die f&uuml;r einen bestimmten festgelegten Zeitraum auf deinem Endger&auml;t gespeichert werden. Besuchst du unsere Seite erneut, um unsere Dienste in Anspruch zu nehmen, wird automatisch erkannt, dass du bereits bei uns warst und welche Eingaben und Einstellungen du get&auml;tigt hast, um diese nicht noch einmal eingeben zu m&uuml;ssen.</span></p>
<p><span>Zum anderen setzen wir Cookies ein, um die Nutzung unserer Website statistisch zu erfassen und zum Zwecke der Optimierung unseres Angebotes f&uuml;r dich auszuwerten (siehe Ziff.&nbsp;5). Diese Cookies erm&ouml;glichen es uns, bei einem erneuten Besuch unserer Seite automatisch zu erkennen, dass du bereits bei uns warst. Diese Cookies werden nach einer jeweils definierten Zeit automatisch gel&ouml;scht.</span></p>
<p><span>Die durch Cookies verarbeiteten Daten sind f&uuml;r die genannten Zwecke zur Wahrung unserer berechtigten Interessen sowie der Dritter nach Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;f DSGVO erforderlich.</span></p>
<p><span>Die meisten Browser akzeptieren Cookies automatisch. Du kannst deinen Browser jedoch so konfigurieren, dass keine Cookies auf deinem Computer gespeichert werden oder stets ein Hinweis erscheint, bevor ein neuer Cookie angelegt wird. Die vollst&auml;ndige Deaktivierung von Cookies kann jedoch dazu f&uuml;hren, dass du nicht alle Funktionen nutzen kannst.</span></p>
<h4><span>5. Analyse-Tools</span></h4>
<h5><span>a) Statistische Auswertungen</span></h5>
<p><span>Wir nehmen auf Basis der in Ziffer 2a aufgef&uuml;hrten Zugriffsprotokolle statistische Auswertungen auf Grundlage des Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;f DSGVO zum Zwecke der Optimierung unseres Angebots vor. Hierbei werten wir nur auf unserem Server anfallende Daten aus und &uuml;bertragen keinerlei personenbezogene Zugriffsdaten an Dritte. Wir setzen keinerlei Z&auml;hlpixel oder Third-Party-Cookies Dritter ein. Wir behalten uns ebenfalls vor, statistische Auswertungen zu den angemeldeten Nutzer*innen auf Grundlage des Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;f DSGVO durchzuf&uuml;hren.</span></p>
<h4><span>6. Social Media Plug-ins</span></h4>
<p><span>Wir setzen derzeit keinerlei Social Media Plug-ins ein.</span></p>
<h4><span>7. Betroffenenrechte</span></h4>
<p><span>Du hast das Recht:</span></p>
<ul>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;15 DSGVO Auskunft &uuml;ber deine von uns verarbeiteten personenbezogenen Daten zu verlangen. Insbesondere kannst du Auskunft &uuml;ber die Verarbeitungszwecke, die Kategorie der personenbezogenen Daten, die Kategorien von Empf&auml;ngern, gegen&uuml;ber denen deine Daten offengelegt wurden oder werden, die geplante Speicherdauer, das Bestehen eines Rechts auf Berichtigung, L&ouml;schung, Einschr&auml;nkung der Verarbeitung oder Widerspruch, das Bestehen eines Beschwerderechts, die Herkunft deiner Daten, sofern diese nicht bei uns erhoben wurden, sowie &uuml;ber das Bestehen einer automatisierten Entscheidungsfindung einschlie&szlig;lich Profiling und ggf. aussagekr&auml;ftigen Informationen zu deren Einzelheiten verlangen;</span></p>
</li>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;16 DSGVO unverz&uuml;glich die Berichtigung unrichtiger oder Vervollst&auml;ndigung deiner bei uns gespeicherten personenbezogenen Daten zu verlangen;</span></p>
</li>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;17 DSGVO die L&ouml;schung deiner bei uns gespeicherten personenbezogenen Daten zu verlangen, soweit nicht die Verarbeitung zur Aus&uuml;bung des Rechts auf freie Meinungs&auml;u&szlig;erung und Information, zur Erf&uuml;llung einer rechtlichen Verpflichtung, aus Gr&uuml;nden des &ouml;ffentlichen Interesses oder zur Geltendmachung, Aus&uuml;bung oder Verteidigung von Rechtsanspr&uuml;chen erforderlich ist;</span></p>
</li>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;18 DSGVO die Einschr&auml;nkung der Verarbeitung deiner personenbezogenen Daten zu verlangen, soweit die Richtigkeit der Daten von dir bestritten wird, die Verarbeitung unrechtm&auml;&szlig;ig ist, du aber deren L&ouml;schung ablehnst und wir die Daten nicht mehr ben&ouml;tigen, du diese jedoch zur Geltendmachung, Aus&uuml;bung oder Verteidigung von Rechtsanspr&uuml;chen ben&ouml;tigst oder du gem&auml;&szlig; Art.&nbsp;21 DSGVO Widerspruch gegen die Verarbeitung eingelegt hast;</span></p>
</li>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;20 DSGVO deine personenbezogenen Daten, die du uns bereitgestellt hast, in einem strukturierten, g&auml;ngigen und maschinenlesbaren Format zu erhalten oder die &Uuml;bermittlung an einen anderen Verantwortlichen zu verlangen;</span></p>
</li>
<li>
<p><span>gem&auml;&szlig; Art.&nbsp;7 Abs.&nbsp;3 DSGVO deine einmal erteilte Einwilligung jederzeit gegen&uuml;ber uns zu widerrufen. Dies hat zur Folge, dass wir die Datenverarbeitung, die auf dieser Einwilligung beruhte, f&uuml;r die Zukunft nicht mehr fortf&uuml;hren d&uuml;rfen, und</span></p>
</li>
<li>
<p><span>dich gem&auml;&szlig; Art.&nbsp;77 DSGVO bei einer Aufsichtsbeh&ouml;rde beschweren, wenn du .der Ansicht bist, dass die Verarbeitung der dich betreffenden personenbezogenen Daten gegen die DSGVO verst&ouml;&szlig;t.</span></p>
</li>
</ul>
<p><span>Die f&uuml;r uns unmittelbar zust&auml;ndige Aufsichtsbeh&ouml;rde ist:</span></p>
<p><span><span> </span></span><span>Landesbeauftragte f&uuml;r Datenschutz und Informationsfreiheit </span><span><br /></span><span><span> </span></span><span>Nordrhein-Westfalen</span><span><br /></span><span><span> </span></span><span>Postfach 20 04 44</span><span><br /></span><span><span> </span></span><span>40102 D&uuml;sseldorf</span></p>
<p><span>Ansonsten kannst du dich aber auch an die Aufsichtsbeh&ouml;rde deines &uuml;blichen Aufenthaltsortes oder Arbeitsplatzes wenden.</span></p>
<h4><span>8. Widerspruchsrecht</span></h4>
<p><span>Sofern deine personenbezogenen Daten auf Grundlage von berechtigten Interessen gem&auml;&szlig; Art.&nbsp;6 Abs.&nbsp;1 S.&nbsp;1 lit.&nbsp;f DSGVO verarbeitet werden, hast du das Recht, gem&auml;&szlig; Art.&nbsp;21 DSGVO Widerspruch gegen die Verarbeitung deiner personenbezogenen Daten einzulegen, soweit daf&uuml;r Gr&uuml;nde vorliegen, die sich aus deiner besonderen Situation ergeben oder sich der Widerspruch gegen Direktwerbung richtet. Im letzteren Fall hast du ein generelles Widerspruchsrecht, das ohne Angabe einer besonderen Situation von uns umgesetzt wird.</span></p>
<p><span>M&ouml;chtest du von deinem Widerrufs- oder Widerspruchsrecht Gebrauch machen, gen&uuml;gt eine E-Mail an datenschutz@foodsharing.de</span></p>
<h4><span>9. Datensicherheit</span></h4>
<p><span>Wir verwenden innerhalb des Website-Besuchs das verbreitete SSL-Verfahren (Secure Socket Layer) in Verbindung mit der jeweils h&ouml;chsten Verschl&uuml;sselungsstufe, die von deinem Browser unterst&uuml;tzt wird. In der Regel handelt es sich dabei um eine 256-Bit-Verschl&uuml;sselung oder h&ouml;her. Falls dein Browser keine 256-Bit-Verschl&uuml;sselung unterst&uuml;tzt, greifen wir stattdessen auf 128-Bit-v3-Technologie zur&uuml;ck. Ob eine einzelne Seite unseres Internetauftrittes verschl&uuml;sselt &uuml;bertragen wird, erkennst du an der geschlossenen Darstellung des Sch&uuml;ssel- beziehungsweise Schloss-Symbols in der Adresszeile oder der Statusleiste deines Browsers.</span></p>
<p><span>Wir bedienen uns im &Uuml;brigen geeigneter technischer und organisatorischer Sicherheitsma&szlig;nahmen, um deine Daten gegen zuf&auml;llige oder vors&auml;tzliche Manipulationen, teilweisen oder vollst&auml;ndigen Verlust, Zerst&ouml;rung oder gegen den unbefugten Zugriff Dritter zu sch&uuml;tzen. Unsere Sicherheitsma&szlig;nahmen werden entsprechend der technologischen Entwicklung fortlaufend verbessert.</span></p>
<h4><span>10. Aktualit&auml;t und &Auml;nderung dieser Datenschutzerkl&auml;rung</span></h4>
<p><span>Diese Datenschutzerkl&auml;rung ist aktuell g&uuml;ltig und hat den Stand Februar 2019.</span></p>
<p><span>Durch die Weiterentwicklung unserer Website und Angebote dar&uuml;ber oder aufgrund ge&auml;nderter gesetzlicher beziehungsweise beh&ouml;rdlicher Vorgaben kann es notwendig werden, diese Datenschutzerkl&auml;rung zu &auml;ndern. Die jeweils aktuelle Datenschutzerkl&auml;rung kann jederzeit auf unserer Website abgerufen und/oder ausgedruckt werden.</span></p>
<p></p>', 'last_mod' => '2019-02-28 12:46:59']];
		$this->table('fs_content')->insert($content)->save();

		$fs_bezirk = [
			['id' => 0, 'parent_id' => null, 'has_children' => '1', 'type' => '0', 'teaser' => 'Root', 'desc' => 'Root', 'photo' => '', 'master' => '0', 'mailbox_id' => '0', 'name' => '', 'email' => '', 'email_pass' => '', 'email_name' => '', 'apply_type' => '0', 'banana_count' => '0', 'fetch_count' => '0', 'week_num' => '0', 'report_num' => '0', 'stat_last_update' => '2020-05-24 02:17:18', 'stat_fetchweight' => '702651.00', 'stat_fetchcount' => '48754', 'stat_postcount' => '152', 'stat_betriebcount' => '13', 'stat_korpcount' => '0', 'stat_botcount' => '0', 'stat_fscount' => '0', 'stat_fairteilercount' => '0', 'conversation_id' => '0', 'moderated' => '0'],
			['id' => '392', 'parent_id' => '0', 'has_children' => '1', 'type' => '8', 'teaser' => '', 'desc' => '', 'photo' => '', 'master' => '392', 'mailbox_id' => '32678', 'name' => 'Arbeitsgruppen Überregional', 'email' => '', 'email_pass' => '', 'email_name' => 'Foodsharing Arbeitsgruppen Überregional', 'apply_type' => '2', 'banana_count' => '0', 'fetch_count' => '0', 'week_num' => '0', 'report_num' => '0', 'stat_last_update' => '2020-05-24 02:17:57', 'stat_fetchweight' => '5176.00', 'stat_fetchcount' => '208', 'stat_postcount' => '53969', 'stat_betriebcount' => '1', 'stat_korpcount' => '0', 'stat_botcount' => '1', 'stat_fscount' => '3360', 'stat_fairteilercount' => '0', 'conversation_id' => '0', 'moderated' => '0'],
			['id' => '258', 'parent_id' => '0', 'has_children' => '1', 'type' => '7', 'teaser' => 'Das Forum des alten Orgateams', 'desc' => '', 'photo' => '', 'master' => '0', 'mailbox_id' => '528', 'name' => 'Orgateam Archiv', 'email' => '', 'email_pass' => '', 'email_name' => 'Foodsharing Orgateam', 'apply_type' => '0', 'banana_count' => '0', 'fetch_count' => '0', 'week_num' => '0', 'report_num' => '0', 'stat_last_update' => '2020-05-24 02:17:55', 'stat_fetchweight' => '179.00', 'stat_fetchcount' => '135', 'stat_postcount' => '6308', 'stat_betriebcount' => '0', 'stat_korpcount' => '0', 'stat_botcount' => '0', 'stat_fscount' => '22', 'stat_fairteilercount' => '0', 'conversation_id' => '0', 'moderated' => '1'],
			['id' => '741', 'parent_id' => '0', 'has_children' => '1', 'type' => '6', 'teaser' => '', 'desc' => '', 'photo' => '', 'master' => '0', 'mailbox_id' => '25467', 'name' => 'Europa', 'email' => 'europa', 'email_pass' => '', 'email_name' => 'Foodsharing Europa', 'apply_type' => '2', 'banana_count' => '0', 'fetch_count' => '0', 'week_num' => '0', 'report_num' => '0', 'stat_last_update' => '2020-05-24 02:18:15', 'stat_fetchweight' => '33829400.50', 'stat_fetchcount' => '2116647', 'stat_postcount' => '1733615', 'stat_betriebcount' => '23002', 'stat_korpcount' => '7031', 'stat_botcount' => '1004', 'stat_fscount' => '74600', 'stat_fairteilercount' => '891', 'conversation_id' => '0', 'moderated' => '0'],
			['id' => '1373', 'parent_id' => '0', 'has_children' => '0', 'type' => '7', 'teaser' => '.', 'desc' => '', 'photo' => 'workgroup/d441822c461c17a96c2a58bada5861dcb09efbe1.png', 'master' => '1373', 'mailbox_id' => '26644', 'name' => 'Vereinsvorstand', 'email' => '', 'email_pass' => '', 'email_name' => 'Foodsharing Vereinsvorstand', 'apply_type' => '0', 'banana_count' => '0', 'fetch_count' => '0', 'week_num' => '0', 'report_num' => '0', 'stat_last_update' => '2020-05-24 02:18:25', 'stat_fetchweight' => '0.00', 'stat_fetchcount' => '0', 'stat_postcount' => '4', 'stat_betriebcount' => '0', 'stat_korpcount' => '0', 'stat_botcount' => '0', 'stat_fscount' => '12', 'stat_fairteilercount' => '0', 'conversation_id' => '0', 'moderated' => '1'],
			['id' => '1564', 'parent_id' => '258', 'has_children' => '0', 'type' => '7', 'teaser' => 'x', 'desc' => '', 'photo' => '', 'master' => '0', 'mailbox_id' => '30177', 'name' => 'Ehemalige (Vorstand und Orgateam)', 'email' => 'ehemalige', 'email_pass' => '', 'email_name' => 'Foodsharing Ehemalige', 'apply_type' => '0', 'banana_count' => '0', 'fetch_count' => '0', 'week_num' => '0', 'report_num' => '0', 'stat_last_update' => '2020-05-24 02:18:27', 'stat_fetchweight' => '0.00', 'stat_fetchcount' => '0', 'stat_postcount' => '0', 'stat_betriebcount' => '0', 'stat_korpcount' => '0', 'stat_botcount' => '0', 'stat_fscount' => '13', 'stat_fairteilercount' => '0', 'conversation_id' => '0', 'moderated' => '0'],
			['id' => '1565', 'parent_id' => '258', 'has_children' => '0', 'type' => '7', 'teaser' => 'Wer hier in der Gruppe aufgelistet wird, erscheint auch auf der Teamseite. Ist bisher eine stille Gruppe.', 'desc' => '', 'photo' => '', 'master' => '0', 'mailbox_id' => '30176', 'name' => 'Aktive (Überregional)', 'email' => '', 'email_pass' => '', 'email_name' => 'Foodsharing Aktive', 'apply_type' => '2', 'banana_count' => '0', 'fetch_count' => '0', 'week_num' => '0', 'report_num' => '0', 'stat_last_update' => '2020-05-24 02:18:27', 'stat_fetchweight' => '0.00', 'stat_fetchcount' => '0', 'stat_postcount' => '8', 'stat_betriebcount' => '0', 'stat_korpcount' => '0', 'stat_botcount' => '0', 'stat_fscount' => '5', 'stat_fairteilercount' => '0', 'conversation_id' => '0', 'moderated' => '0'],
			['id' => '341', 'parent_id' => '392', 'has_children' => '1', 'type' => '7', 'teaser' => 'Wir beschäftigen uns mit dem derzeit so nötig zu überarbeitenden Anmeldevorgang und Quiz, mit dem man sich für den Status des Foodsavers, Filialverantwortlichen oder gar den der BotschafterInnen qualifizieren kann.

Wenn Du Dich schon sehr mit der Lebensmittelrettung auskennst, sie schon anderen gekonnt vermitteln kannst, sowie Lust hast, dir kreative Fragen auszudenken und dir Gedanken zur Anmeldung zu machen, bist du hier herzlich willkommen!

Achtung: Für NACHRICHTEN an die Gruppe:
Verwende möglichst die Emailadresse, bitte!
Wenn du den Button "Gruppe kontaktieren" verwendest, dann schreibe bitte eine Emailadresse von dir dazu (oder deine Foodsaver-ID).
Wir können sonst nicht antworten, da uns Absender*in und Emailadresse leider nicht automatisch angezeigt werden!

(*) Die Administratoren dieser Gruppe haben Zugriff auf das Quiz, die Quizkommentare und die Quizauswertungen.(341, QUIZ_AND_REGISTRATION_WORK_GROUP)', 'desc' => 'Wir besch&auml;ftigen uns mit dem derzeit so n&ouml;tig zu &uuml;berarbeitenden Anmeldevorgang, dem auch demn&auml;chst ein Quiz anschlie&szlig;bar sein soll, mit dem man sich f&uuml;r den Status des Foodsavers, Filialverantwortlichen oder gar der BotschafterInnen qualifiziert.', 'photo' => '', 'master' => '392', 'mailbox_id' => '19708', 'name' => 'Anmeldevorgang & Quiz', 'email' => '', 'email_pass' => '', 'email_name' => 'Foodsharing Anmeldevorgang  Quiz', 'apply_type' => '2', 'banana_count' => '0', 'fetch_count' => '0', 'week_num' => '0', 'report_num' => '0', 'stat_last_update' => '2020-05-24 02:17:56', 'stat_fetchweight' => '0.00', 'stat_fetchcount' => '0', 'stat_postcount' => '157', 'stat_betriebcount' => '0', 'stat_korpcount' => '0', 'stat_botcount' => '0', 'stat_fscount' => '13', 'stat_fairteilercount' => '0', 'conversation_id' => '0', 'moderated' => '0']
	];
		$this->table('fs_bezirk')->insert($fs_bezirk)->save();
		$this->execute('SET FOREIGN_KEY_CHECKS=1;');
		$this->execute('set sql_mode="";');
	}
}
