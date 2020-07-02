<?php

namespace Foodsharing\Entrypoint;

use Foodsharing\Debug\DebugBar;
use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\ContentSecurityPolicy;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Routing;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\Database;
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
use Twig\Environment;

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

	public function index(
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
		Database $db
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

		global $g_lang;
		require_once 'lang/DE/de.php';

		error_reporting(E_ALL);

		if (isset($_GET['logout'])) {
			$_SESSION['client'] = [];
			unset($_SESSION['client']);
		}

		global $content_left_width;
		$content_left_width = 6;
		global $content_right_width;
		$content_right_width = 6;

		global $g_template;
		$g_template = 'default';
		global $g_data;
		$g_data = $dataHelper->getPostData();

		$pageHelper->addHidden('<a id="' . $identificationHelper->id('fancylink') . '" href="#fancy">&nbsp;</a>');
		$pageHelper->addHidden('<div id="' . $identificationHelper->id('fancy') . '"></div>');

		$pageHelper->addHidden('<div id="u-profile"></div>');
		$pageHelper->addHidden('<ul id="hidden-info"></ul>');
		$pageHelper->addHidden('<ul id="hidden-error"></ul>');
		$pageHelper->addHidden('<div id="dialog-confirm" title="Wirklich l&ouml;schen?"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span id="dialog-confirm-msg"></span><input type="hidden" value="" id="dialog-confirm-url" /></p></div>');
		$pageHelper->addHidden('<div id="uploadPhoto"><form method="post" enctype="multipart/form-data" target="upload" action="/xhr.php?f=addPhoto"><input type="file" name="photo" onchange="uploadPhoto();" /> <input type="hidden" id="uploadPhoto-fs_id" name="fs_id" value="" /></form><div id="uploadPhoto-preview"></div><iframe name="upload" width="1" height="1" src=""></iframe></div>');

		// lib/inc.php END

		global $g_broadcast_message;

		$g_broadcast_message = $db->fetchValue('SELECT `body` FROM fs_content WHERE `id` = 51');

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
		if ($controllerUsedResponse) {
			if ($debugBar->isEnabled()) {
				$response->setContent(str_replace(
					'</body>',
					$debugBar->renderContent() . '</body>',
					$response->getContent()
				));
			}

			if (isset($cache) && $cache->shouldCache()) {
				$cache->cache($page);
			}
		} else {
			if ($debugBar->isEnabled()) {
				$pageHelper->addContent($debugBar->renderContent(), CNT_BOTTOM);
			}
			/* @var Environment $twig */
			$twig = $this->get('twig');
			$page = $twig->render('layouts/' . $g_template . '.twig', $pageHelper->generateAndGetGlobalViewData());

			if (isset($cache) && $cache->shouldCache()) {
				$cache->cache($page);
			}

			$response->setContent($page);
		}

		return $response;
	}
}
