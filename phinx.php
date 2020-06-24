<?php

include __DIR__ . '/config.inc.php';

return
[
	'paths' => [
		'migrations' => '%%PHINX_CONFIG_DIR%%/migrations',
		'seeds' => '%%PHINX_CONFIG_DIR%%/seeds'
	],
	'environments' => [
		'default_migration_table' => 'phinxlog',
		'default_environment' => 'symfony',
		'symfony' => [
			'adapter' => 'mysql',
			'host' => DB_HOST,
			'user' => DB_USER,
			'pass' => DB_PASS,
			'name' => DB_DB,
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci'
		]
	],
	'version_order' => 'creation'
];
