<?php


// TODO: sanitize env name
// TODO: maybe have a default env?
// TODO: check if there is not already a concept of app environment elsewhere

use DebugBar\DataCollector\PDO\TraceablePDO;
use Foodsharing\DI;

$FS_ENV = getenv('FS_ENV');
$env_filename = 'config.inc.' . $FS_ENV . '.php';

if (file_exists($env_filename)) {
	require_once $env_filename;
} else {
	die('no config found for env [' . $FS_ENV . ']');
}
if (!defined('SOCK_URL')) {
	define('SOCK_URL', 'http://127.0.0.1:1338/');
}

date_default_timezone_set('Europe/Berlin');
/*
 * Read revision from revision file.
 * It is supposed to define SRC_REVISION.
 */
if (file_exists('revision.inc.php')) {
	require_once 'revision.inc.php';
}

/*
 * Configure Raven (sentry.io client) for remote error reporting
 */
if (defined('SENTRY_URL')) {
	$client = new Raven_Client(SENTRY_URL);
	$client->install();
	$client->tags_context(array('FS_ENV' => $FS_ENV));
	if (defined('SRC_REVISION')) {
		$client->setRelease(SRC_REVISION);
	}
}

define('FPDF_FONTPATH', __DIR__ . '/lib/font/');

if ($FS_ENV === 'dev') {
	// In development we need to wrap the PDO instance for the DebugBar
	$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_DB, DB_USER, DB_PASS);
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$traceablePDO = new TraceablePDO($pdo);
	DI::useTraceablePDO($traceablePDO);
	Foodsharing\Debug\DebugBar::register($traceablePDO);
} else {
	DI::useDefaultPDO();
}

DI::compile();
