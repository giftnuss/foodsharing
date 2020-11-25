<?php

namespace Foodsharing\Entrypoint;

use Foodsharing\Annotation\DisableCsrfProtection;
use Foodsharing\Debug\DebugBar;
use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\ContentSecurityPolicy;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Routing;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;
use Foodsharing\Modules\Core\InfluxMetrics;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
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
		ContentSecurityPolicy $csp,
		Session $session,
		Mem $mem,
		InfluxMetrics $influxdb,
		DebugBar $debugBar,
		RouteHelper $routeHelper,
		PageHelper $pageHelper,
		DataHelper $dataHelper,
		IdentificationHelper $identificationHelper,
		ContentGateway $contentGateway
	): Response {
		$response = new Response('--');

		$response->headers->set('X-Frame-Options', 'DENY');
		$response->headers->set('X-Content-Type-Options', 'nosniff');

		$cspString = $csp->generate($request->getSchemeAndHttpHost(), CSP_REPORT_URI, CSP_REPORT_ONLY);
		$cspParts = explode(': ', $cspString, 2);
		$response->headers->set($cspParts[0], $cspParts[1]);

		// lib/inc.php START

		$session->initIfCookieExists();

		// is this actually used anywhere? (prod?)
		global $g_page_cache;
		if (isset($g_page_cache) && strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
			$cache = new Caching($g_page_cache, $session, $mem, $influxdb);
			$cache->lookup();
		}

		$translator = $this->fullServiceContainer->get('translator');
		$translator->setLocale($session->getLocale());

		error_reporting(E_ALL);

		if (isset($_GET['logout'])) {
			$_SESSION['client'] = [];
			unset($_SESSION['client']);
		}

		global $g_template;
		$g_template = 'default';
		global $g_data;
		$g_data = $dataHelper->getPostData();

		$pageHelper->addHidden('<div id="u-profile"></div>');
		$pageHelper->addHidden('<ul id="hidden-info"></ul>');
		$pageHelper->addHidden('<ul id="hidden-error"></ul>');
		$pageHelper->addHidden('<div id="dialog-confirm" title="Wirklich l&ouml;schen?"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span id="dialog-confirm-msg"></span><input type="hidden" value="" id="dialog-confirm-url" /></p></div>');
		$pageHelper->addHidden('<div id="uploadPhoto"><form method="post" enctype="multipart/form-data" target="upload" action="/xhr.php?f=addPhoto"><input type="file" name="photo" onchange="uploadPhoto();" /></form><div id="uploadPhoto-preview"></div><iframe name="upload" width="1" height="1" src=""></iframe></div>');

		// lib/inc.php END

		global $g_broadcast_message;
		$g_broadcast_message = $contentGateway->get(ContentId::BROADCAST_MESSAGE)['body'];

		if ($debugBar->isEnabled()) {
			$pageHelper->addHead($debugBar->renderHead());
		}

		if ($session->may()) {
			$uc = $request->query->get('uc');
			if ($uc !== null) {
				if ($session->id() != $uc) {
					$mem->logout($session->id());
					$routeHelper->goLogin();
				}
			}
		}

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
			$sub = $sub = $obj->getSubFunc();
			if ($sub !== false && is_callable([$obj, $sub])) {
				$obj->$sub($request, $response);
			}
		} else {
			$response->setStatusCode(Response::HTTP_NOT_FOUND);
			$response->setContent('');
		}

		$page = $response->getContent();
		$controllerUsedResponse = $page !== '--';
		if (!$controllerUsedResponse) {
			$page = $this->renderView('layouts/' . $g_template . '.twig', $pageHelper->generateAndGetGlobalViewData());

			$response->setContent($page);
		}

		if ($debugBar->isEnabled()) {
			// append the debug bar at the very end of <body>
			$response->setContent(str_replace(
				'</body>',
				$debugBar->renderContent() . '</body>',
				$response->getContent()
			));
		}

		if (isset($cache) && $cache->shouldCache()) {
			$cache->cache($response->getContent());
		}

		return $response;
	}
}
