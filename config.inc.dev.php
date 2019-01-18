<?php

if (php_sapi_name() != 'cli') {
	/* Whoops catches all error messages in CLI mode as well :( */
	Foodsharing\Debug\Whoops::register();
}

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
define('BASE_URL', $protocol . '://localhost:18080');

define('INFLUX_DSN', 'influxdb://influxdb:8086/foodsharing');

define('DEFAULT_EMAIL', 'noreply@foodsharing.de');
define('SUPPORT_EMAIL', 'it@foodsharing.network');
define('DEFAULT_EMAIL_NAME', 'foodsharing-Freiwillige');
define('VERSION', '0.8.3');
define('EMAIL_PUBLIC', 'info@foodsharing.de');
define('EMAIL_PUBLIC_NAME', 'foodsharing-Freiwillige');
define('NOREPLY_EMAIL_HOST', 'foodsharing.de');
define('PLATFORM_MAILBOX_HOST', 'foodsharing.network');
//define('GOOGLE_API_KEY', 'AIzaSyCkFfCoOnj8ZjGGcApHS1rX6Rt6OxrW6hQ'); // Original key; API limit reached 20181121
//define('GOOGLE_API_KEY', 'AIzaSyD6MLWJNn7I_Qdpkixxo2uL77hW5srdQwk'); // This key is sponsored by @Jo for testing purpose.
define('GOOGLE_API_KEY', 'AIzaSyCkFfCoOnj8ZjGGcApHS1rX6Rt6OxrW6hQ'); // matthias fsdev key

define('MAILBOX_OWN_DOMAINS', array('foodsharing.network', 'lebensmittelretten.de', 'foodsharing.de'));

define('SMTP_HOST', 'maildev');
define('SMTP_PORT', 25);

define('MEM_ENABLED', true);

define('SOCK_URL', 'http://chat:1338/');
define('REDIS_HOST', 'redis');
define('REDIS_PORT', 6379);

define('IMAP', [
	['host' => 'imap', 'user' => 'user', 'password' => 'pass']
]);

if (!defined('ROOT_DIR')) {
	define('ROOT_DIR', './');
}
