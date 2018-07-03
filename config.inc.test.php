<?php

/* Codeception remote codecoverage - only used for testing */
define('C3_CODECOVERAGE_ERROR_LOG_FILE', '/app/tests/_output/c3_error.log'); //Optional (if not set the default c3 output dir will be used)
include __DIR__ . '/vendor/codeception/c3/c3.php';

$protocol = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
	$protocol = 'https';
}

define('PROTOCOL', $protocol);
define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_DB', 'foodsharing');
define('ERROR_REPORT', E_ALL);
define('BASE_URL', $protocol . '://lmr.local/');
define('DEFAULT_EMAIL', 'noreply@lebensmittelretten.de');
define('SUPPORT_EMAIL', 'it@lebensmittelretten.de');
define('DEFAULT_EMAIL_NAME', 'Foodsharing Freiwillige');
define('VERSION', '0.8.1');
define('EMAIL_PUBLIC', 'info@lebensmittelretten.de');
define('EMAIL_PUBLIC_NAME', 'Foodsharing Freiwillige');
define('DEFAULT_EMAIL_HOST', 'lebensmittelretten.de');
define('MAPZEN_API_KEY', 'mapzen-RaXru7A');
define('GOOGLE_API_KEY', '');

define('MAILBOX_OWN_DOMAINS', array('lebensmittelretten.de'));

define('SMTP_HOST', 'maildev');
define('SMTP_PORT', 25);
define('MEM_ENABLED', true);

define('SOCK_URL', 'http://chat:1338/');
define('REDIS_HOST', 'redis');
define('REDIS_PORT', 6379);

if (!defined('ROOT_DIR')) {
	define('ROOT_DIR', './');
}

Foodsharing\Debug\Whoops::register();
