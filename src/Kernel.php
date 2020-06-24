<?php

namespace Foodsharing;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
	use MicroKernelTrait;

	protected function configureContainer(ContainerConfigurator $container): void
	{
		$container->import('../config/{packages}/*.yaml');
		$container->import('../config/{packages}/' . $this->environment . '/*.yaml');
	}

	protected function configureRoutes(RoutingConfigurator $routes): void
	{
		$routes->import('../config/{routes}/' . $this->environment . '/*.yaml');
		$routes->import('../config/{routes}/*.yaml');

		if (file_exists(\dirname(__DIR__) . '/config/routes.yaml')) {
			$routes->import('../config/{routes}.yaml');
		} else {
			$path = \dirname(__DIR__) . '/config/routes.php';
			(require $path)($routes->withPath($path), $this);
		}
	}
}
