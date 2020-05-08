<?php

namespace Foodsharing\EventListener;

use Foodsharing\Modules\Core\InfluxMetrics;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExecutionTimingListener implements EventSubscriberInterface
{
	private $tokens;
	private $influxMetrics;

	public function __construct(InfluxMetrics $influxMetrics)
	{
		$this->influxMetrics = $influxMetrics;
	}

	public function onKernelController(ControllerEvent $event)
	{
		$controller = $event->getController();
		// when a controller class defines multiple action methods, the controller
		// is returned as [$controllerInstance, 'methodName']
		if (!is_array($controller)) {
			return;
		}

		$this->influxMetrics->addPageStatData(['controller' => get_class($controller[0]), 'method' => $controller[1]]);
	}

	public static function getSubscribedEvents()
	{
		return [
			KernelEvents::CONTROLLER => 'onKernelController',
		];
	}
}
