<?php

use Foodsharing\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/vendor/autoload.php';
require_once 'config.inc.php';

// The check is to ensure we don't use .env in production
$env = $_SERVER['FS_ENV'] ?? 'dev';
$debug = (bool)($_SERVER['APP_DEBUG'] ?? ('prod' !== $env));

if ($debug) {
	umask(0000);

	Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
	Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
	Request::setTrustedHosts(explode(',', $trustedHosts));
}

$kernel = new Kernel($env, $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
