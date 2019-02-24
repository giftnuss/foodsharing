<?php

$FS_ENV = getenv('FS_ENV');
$env_filename = __DIR__ . '/config.inc.' . $FS_ENV . '.php';
if (defined('FS_ENV')) {
	if (FS_ENV !== $FS_ENV) {
		die('different values of FS_ENV const (' . FS_ENV . ') and ENV var (' . $FS_ENV . ')');
	}
} else {
	define('FS_ENV', $FS_ENV);
}

if (file_exists($env_filename)) {
	require_once $env_filename;
} else {
	die('no config found for env [' . $FS_ENV . ']');
}
if (!defined('SOCK_URL')) {
	define('SOCK_URL', 'http://127.0.0.1:1338/');
}

date_default_timezone_set('Europe/Berlin');
locale_set_default('de-DE');
/*
 * Read revision from revision file.
 * It is supposed to define SRC_REVISION.
 */
$revision_filename = __DIR__ . '/revision.inc.php';
if (file_exists($revision_filename)) {
	require_once $revision_filename;
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

if (!defined('RAVEN_JAVASCRIPT_CONFIG') && getenv('RAVEN_JAVASCRIPT_CONFIG')) {
	define('RAVEN_JAVASCRIPT_CONFIG', getenv('RAVEN_JAVASCRIPT_CONFIG'));
}

if (!defined('CSP_REPORT_ONLY')) {
	define('CSP_REPORT_ONLY', true);
}

define('FPDF_FONTPATH', __DIR__ . '/lib/font/');

/* global definitions for Foodsharing\\Helpers\PageComposition Helper*/
define('CNT_MAIN', 0);
define('CNT_RIGHT', 1);
define('CNT_TOP', 2);
define('CNT_BOTTOM', 3);
define('CNT_LEFT', 4);
define('CNT_OVERTOP', 5);

define('DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_DB . ';charset=utf8mb4');
