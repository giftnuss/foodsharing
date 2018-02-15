<?php

namespace Foodsharing;

use DebugBar\DataCollector\PDO\TraceablePDO;
use PDO;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DI
{
	/**
	 * @var \Foodsharing\DI
	 */
	public static $shared;

	private $container;

	public static function initShared()
	{
		self::$shared = new self();
	}

	public function __construct()
	{
		$this->container = new ContainerBuilder();
		$loader = new YamlFileLoader($this->container, new FileLocator(__DIR__));

		$definition = new Definition();
		$definition
			->setAutowired(true)
			->setAutoconfigured(true)
			->setPublic(true);

		$loader->registerClasses($definition, 'Foodsharing\\', '*', '{Lib/Flourish,Lib/Cache,Lib/View,Dev,Debug}');
	}

	public function useTraceablePDO($traceablePDO)
	{
		$this->container->set(PDO::class, $traceablePDO);
		$this->container->register(PDO::class, TraceablePDO::class);
	}

	public function usePDO($dsn, $user, $password)
	{
		$this->container
			->register(\PDO::class, \PDO::class)
			->addArgument($dsn)
			->addArgument($user)
			->addArgument($password)
			->addMethodCall('setAttribute', [PDO::ATTR_EMULATE_PREPARES, false])
			->addMethodCall('setAttribute', [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
	}

	public function compile()
	{
		$this->container->compile();
	}

	/**
	 * @throws \Exception
	 */
	public function get($id)
	{
		return $this->container->get($id);
	}
}

DI::initShared();
