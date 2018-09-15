<?php

require __DIR__ . '/includes/setup.php';
require_once 'config.inc.php';

/* @var $container Container */
global $container;
$container = initializeLegacyContainer();

/*
 * force only executing on commandline
*/
if (!isset($argv)) {
	header('Location: ' . BASE_URL);
	exit();
}

require_once ROOT_DIR . 'lang/DE/de.php';

$app = 'Console';
$method = 'index';

if (isset($argv[3]) && $argv[3] == 'quiet') {
	define('QUIET', true);
} else {
	define('QUIET', false);
}

if (isset($argv) && is_array($argv)) {
	if (count($argv) > 1) {
		$app = $argv[1];
	}
	if (count($argv) > 2) {
		$method = $argv[2];
	}
}

$app = '\\Foodsharing\\Modules\\' . $app . '\\' . $app . 'Control';
echo "Starting $app::$method...\n";

$appInstance = $container->get(ltrim($app, '\\'));

if (is_callable([$appInstance, $method])) {
	$appInstance->$method();
	exit();
}

echo 'Modul ' . $app . ' konnte nicht geladen werden';
