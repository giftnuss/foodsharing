<?php

use Foodsharing\FoodsharingKernel as Kernel;

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
			'connection' => $pdo
		]
	],
	'version_order' => 'creation'
];
