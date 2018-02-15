<?php


// TODO: sanitize env name
// TODO: maybe have a default env?
// TODO: check if there is not already a concept of app environment elsewhere

use DebugBar\DataCollector\PDO\TraceablePDO;
use Foodsharing\DI;

$FS_ENV = getenv('FS_ENV');
$env_filename = 'config.inc.' . $FS_ENV . '.php';
define('FS_ENV', $FS_ENV);

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

/* global definitions for Foodsharing\Lib\Func until they might
go away or somewhere else :) */
define('CNT_MAIN', 0);
define('CNT_RIGHT', 1);
define('CNT_TOP', 2);
define('CNT_BOTTOM', 3);
define('CNT_LEFT', 4);
define('CNT_OVERTOP', 5);

/* this initializes the static class - can be refactored when we have DI, should be fine for now */
Foodsharing\Lib\Db\Mem::connect();

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_DB;
if ($FS_ENV === 'dev') {
	// In development we need to wrap the PDO instance for the DebugBar
	$pdo = new PDO($dsn, DB_USER, DB_PASS);
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$traceablePDO = new TraceablePDO($pdo);
	DI::$shared->useTraceablePDO($traceablePDO);
	Foodsharing\Debug\DebugBar::register($traceablePDO);
} else {
	DI::$shared->usePDO($dsn, DB_USER, DB_PASS);
}

DI::$shared->configureMysqli(DB_HOST, DB_USER, DB_PASS, DB_DB);
DI::$shared->compile();
