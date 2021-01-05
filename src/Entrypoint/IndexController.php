<?php

namespace Foodsharing\Entrypoint;

use Foodsharing\Annotation\DisableCsrfProtection;
use Foodsharing\Lib\Routing;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
	/**
	 * @var ContainerInterface Kernel container needed to access any service,
	 * instead of just the ones specified in AbstractController::getSubscribedServices
	 */
	private ContainerInterface $fullServiceContainer;

	public function __construct(ContainerInterface $container)
	{
		$this->fullServiceContainer = $container;
	}

	/**
	 * @DisableCsrfProtection CSRF Protection (originally done for the REST API)
	 * breaks POST on these entrypoints right now,
	 * so this annotation disables it.
	 */
	public function __invoke(
		Request $request,
		RouteHelper $routeHelper,
		PageHelper $pageHelper
	): Response {
		$response = new Response('--');

		$app = $routeHelper->getPage();

		$controller = $routeHelper->getLegalControlIfNecessary() ?? Routing::getClassName($app, 'Control');
		try {
			global $container;
			$container = $this->fullServiceContainer;
			$obj = $container->get(ltrim($controller, '\\'));
		} catch (ServiceNotFoundException $e) {
		}

		if (isset($obj)) {
			$action = $request->query->get('a');
			if ($action !== null && is_callable([$obj, $action])) {
				$obj->$action($request, $response);
			} else {
				$obj->index($request, $response);
			}
			$sub = $obj->getSub();
			if ($sub !== false && is_callable([$obj, $sub])) {
				$obj->$sub($request, $response);
			}
		} else {
			$response->setStatusCode(Response::HTTP_NOT_FOUND);
			$response->setContent('');
		}

		$controllerUsedResponse = $response->getContent() !== '--';
		if (!$controllerUsedResponse) {
			global $g_template;
			$page = $this->renderView('layouts/' . $g_template . '.twig', $pageHelper->generateAndGetGlobalViewData());

			$response->setContent($page);
		}

		return $response;
	}
}
