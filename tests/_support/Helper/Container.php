<?php

namespace Helper;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Container extends \Codeception\Module
{
	private $container;

	public function __construct($moduleContainer, $config = null)
	{
		parent::__construct($moduleContainer, $config);
		$this->container = new ContainerBuilder();
		$loader = new YamlFileLoader($this->container, new FileLocator(__DIR__));
		$loader->load('../../../includes/services.yaml');
		$this->container->compile();
	}

	public function container()
	{
		return $this->container;
	}
}
