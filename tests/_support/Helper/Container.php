<?php

namespace Helper;

use Foodsharing\DI;
use PDO;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Container extends \Codeception\Module\Db
{

	private $container;

	public function __construct($moduleContainer, $config = null)
	{
		parent::__construct($moduleContainer, $config);
		$this->container = new ContainerBuilder();
		$loader = new YamlFileLoader($this->container, new FileLocator(__DIR__));
		$loader->load('../../../includes/services.yaml');
		$this->container
			->register(\PDO::class, \PDO::class)
			->addArgument($this->config['dsn'])
			->addArgument($this->config['user'])
			->addArgument($this->config['password'])
			->addMethodCall('setAttribute', [PDO::ATTR_EMULATE_PREPARES, false]);
		$this->container->compile();
	}

	public function get($id)
	{
		return $this->container->get($id);
	}
}
