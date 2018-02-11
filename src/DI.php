<?php

namespace Foodsharing;

use DebugBar\DataCollector\PDO\TraceablePDO;
use PDO;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DI
{
	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerBuilder
	 */
	private static $container;

	public static function init() {
		DI::$container = new ContainerBuilder();
		$loader = new YamlFileLoader(DI::$container, new FileLocator(__DIR__));
		$loader->load('../includes/services.yaml');
	}

	public static function useTraceablePDO($traceablePDO) {
		DI::$container->set(PDO::class, $traceablePDO);
		DI::$container->register(PDO::class, TraceablePDO::class);
	}

	public static function useDefaultPDO() {
		DI::$container
			->register(\PDO::class, \PDO::class)
			->addArgument('mysql:host=' . DB_HOST . ';dbname=' . DB_DB)
			->addArgument(DB_USER)
			->addArgument(DB_PASS)
			->addMethodCall('setAttribute', [PDO::ATTR_EMULATE_PREPARES, false]);
	}

	public static function compile() {
		DI::$container->compile();
	}

	/**
	 * @throws \Exception
	 */
	public static function get($id)
	{
		return DI::$container->get($id);
	}
}

DI::init();
