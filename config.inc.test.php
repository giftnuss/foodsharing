<?php

/* Adding Whoops during testing can be very useful as the screenshots in the tests/_output folder can show a nice
	 error message. It also catches warnings and the whole site runs in a way that is always throwing warnings out.
	 But hopefully we fix all those at some point :)
*/
Foodsharing\Debug\Whoops::register();

/* Codeception remote codecoverage - only used for testing */
/*define('C3_CODECOVERAGE_ERROR_LOG_FILE', '/app/tests/_output/c3_error.log'); //Optional (if not set the default c3 output dir will be used)
include __DIR__ . '/vendor/codeception/c3/c3.php';
*/

$protocol = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
	$protocol = 'https';
}

define('PROTOCOL', $protocol);
define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_DB', 'foodsharing');
define('INFLUX_DSN', 'udp+influxdb://influxdb:8089/foodsharing');
define('ERROR_REPORT', E_ALL);
define('BASE_URL', $protocol . '://lmr.local/');
define('DEFAULT_EMAIL', 'noreply@foodsharing.de');
define('SUPPORT_EMAIL', 'it@foodsharing.network');
define('DEFAULT_EMAIL_NAME', 'foodsharing-Freiwillige');
define('VERSION', '0.8.1');
define('EMAIL_PUBLIC', 'info@foodsharing.de');
define('EMAIL_PUBLIC_NAME', 'foodsharing-Freiwillige');
define('NOREPLY_EMAIL_HOST', 'foodsharing.de');
define('PLATFORM_MAILBOX_HOST', 'foodsharing.network');

define('MAILBOX_OWN_DOMAINS', ['foodsharing.network', 'lebensmittelretten.de', 'foodsharing.de']);

define('MAILER_HOST', 'maildev');
define('MEM_ENABLED', true);

define('SOCK_URL', 'http://chat:1338/');
define('REDIS_HOST', 'redis');
define('REDIS_PORT', 6379);

define('BOUNCE_IMAP_HOST', null);
define('BOUNCE_IMAP_USER', null);
define('BOUNCE_IMAP_PASS', null);
define('BOUNCE_IMAP_PORT', null);

define('IMAP', []);

if (!defined('ROOT_DIR')) {
	define('ROOT_DIR', './');
}

define('CSRF_TEST_TOKEN', '__TESTTOKEN__');

define('WEBPUSH_PUBLIC_KEY', 'BGBBW8RtRe4LpGT+6Q7BJGGSbgcULM/w9BrxBLva2AVf85Pj7t4xrViT3lsxn8Dp0fpJ1SPoDbwP1n6gt3/R7ps='); // test public key
define('WEBPUSH_PRIVATE_KEY', 'z5g0ssYryhDhQnwVAZ2Q2oOiqF3ZngJzkLXMrww8gDU='); // test private key
