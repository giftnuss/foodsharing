<?php

use Foodsharing\Kernel;

require __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/config.inc.php';

$kernel = new Kernel('dev', false);
$kernel->boot();
$pdo = $kernel->getContainer()->get('PDO');

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
			/* silence some PHP notices that are generated otherwise */
			'user' => 'unused',
			'pass' => 'unused',
			'name' => 'foodsharing',
			'connection' => $pdo,
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci'
		]
	],
	'version_order' => 'creation'
];
