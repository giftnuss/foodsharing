<?php

use Foodsharing\RestKernel;
use Symfony\Component\DependencyInjection\Container;

require __DIR__ . '/../vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

/**
 * @return Container
 */
function initializeLegacyContainer()
{
	$env = $_SERVER['FS_ENV'] ?? 'dev';
	$debug = (bool)($_SERVER['APP_DEBUG'] ?? ('prod' !== $env));
	$kernel = new RestKernel($env, $debug);
	$kernel->boot();

	return $kernel->getContainer();
}
