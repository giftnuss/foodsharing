<?php

namespace Foodsharing\EventListener;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Foodsharing\Annotation\DisableCsrfProtection;
use Foodsharing\Lib\Session;

class CsrfListener
{
	/** @var Reader */
	private $reader;

	private $session;

	/**
	 * @param Reader $reader
	 */
	public function __construct(Reader $reader, Session $session)
	{
		$this->reader = $reader;
		$this->session = $session;
	}

	public function onKernelController(FilterControllerEvent $event)
	{
		if (!is_array($controllers = $event->getController())) {
			return;
		}

		$request = $event->getRequest();
		$content = $request->getContent();

		list($controller, $methodName) = $controllers;
		$reflectionObject = new \ReflectionObject($controller);
		$reflectionMethod = $reflectionObject->getMethod($methodName);
		$methodAnnotation = $this->reader
			->getMethodAnnotation($reflectionMethod, DisableCsrfProtection::class);

		if ($methodAnnotation) {
			// CSRF Protection is disabled for this method
			return;
		}

		if (!$this->session->isValidCsrfHeader()) {
			throw new AccessDeniedHttpException('CSRF Failed: CSRF token missing or incorrect.');
		}
	}
}
