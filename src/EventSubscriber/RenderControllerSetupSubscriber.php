<?php

namespace Foodsharing\EventSubscriber;

use Foodsharing\Debug\DebugBar;
use Foodsharing\Entrypoint\IndexController;
use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\ContentSecurityPolicy;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\FoodsharingController;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;
use Foodsharing\Modules\Core\InfluxMetrics;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Definition: "render controller"
 * Any controllers that render parts of the website.
 * This definition includes:
 * - Classes with the suffix "Control" invoked by IndexController,
 * - Symfony Controllers inheriting from FoodsharingController.
 * It does not include anything to do with xhr, xhrapp, or the REST API.
 *
 * This holds all logic that used to be executed in index.php before calling the Control class.
 * It does this for the IndexController (to handle old Controllers)
 * and for any Controller that inherits from FoodsharingController (to handle Controllers that have been migrated)
 *
 * The reason for this EventSubscriber is to extract the setup logic formerly located in IndexController,
 * so it can be shared with any Symfony controllers that were previously legacy controllers called through IndexController,
 * without breaking any implicit expectations (for example: about the session, certain headers, static parts of the page)
 *
 * The end goal is to find better ways to do some of the things currently done here,
 * or find out if other code can be rewritten to get rid of code here.
 * Some code could certainly be better solved somehow else.
 * Also, for code that continues to be necessary (maybe even for REST) could be extracted into separate EventSubscribers.
 */
class RenderControllerSetupSubscriber implements EventSubscriberInterface
{
	/**
	 * this attribute key is set by onKernelController if the request is handled by a render controller
	 * (and therefore needs legacy postprocessing).
	 */
	private const NEEDS_POSTPROCESSING = 'fs_needs_postprocessing';

	/**
	 * @var ContainerInterface Kernel container needed to access any service,
	 * instead of just the ones specified in AbstractController::getSubscribedServices
	 */
	private ContainerInterface $fullServiceContainer;

	public function __construct(ContainerInterface $container)
	{
		$this->fullServiceContainer = $container;
	}

	public static function getSubscribedEvents()
	{
		return [
			KernelEvents::CONTROLLER => 'onKernelController',
			KernelEvents::RESPONSE => 'onKernelResponse',
		];
	}

	/**
	 * This event is fired before the controller determined by routing is called.
	 * Here, we first filter based on the controller, because
	 * this should only do anything for render controllers.
	 * Basically, this is for all non-REST/XHR code.
	 */
	public function onKernelController(ControllerEvent $event)
	{
		$controller = $event->getController();

		// when a controller class defines multiple action methods, the controller
		// is returned as [$controllerInstance, 'methodName']
		if (is_array($controller)) {
			$controller = $controller[0];
		}

		if (!$this->isRenderController($controller)) {
			return;
		}

		$request = $event->getRequest();

		// for post processing, mark this request so onKernelResponse knows it should act on the response

		$request->attributes->set(self::NEEDS_POSTPROCESSING, true);

		// The actual work this does starts here!

		/* @var Session $session */
		$session = $this->get(Session::class);
		$session->initIfCookieExists();

		// is this actually used anywhere? (prod?)
		global $g_page_cache;
		if (isset($g_page_cache) && strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
			/* @var Mem $mem */
			$mem = $this->get(Mem::class);
			/* @var InfluxMetrics $influxdb */
			$influxdb = $this->get(InfluxMetrics::class);
			$cache = new Caching($g_page_cache, $session, $mem, $influxdb);
			$cache->lookup();
		}

		$translator = $this->get('translator');
		$translator->setLocale($session->getLocale());

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
		$g_data = $this->get(DataHelper::class)->getPostData();

		// TODO check if all of these are actually needed anymore
		$pageHelper = $this->get(PageHelper::class);
		$pageHelper->addHidden('<div id="u-profile"></div>');
		$pageHelper->addHidden('<ul id="hidden-info"></ul>');
		$pageHelper->addHidden('<ul id="hidden-error"></ul>');
		$pageHelper->addHidden('<div id="dialog-confirm" title="Wirklich l&ouml;schen?"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span id="dialog-confirm-msg"></span><input type="hidden" value="" id="dialog-confirm-url" /></p></div>');
		$pageHelper->addHidden('<div id="uploadPhoto"><form method="post" enctype="multipart/form-data" target="upload" action="/xhr.php?f=addPhoto"><input type="file" name="photo" onchange="uploadPhoto();" /></form><div id="uploadPhoto-preview"></div><iframe name="upload" width="1" height="1" src=""></iframe></div>');

		$contentGateway = $this->get(ContentGateway::class);
		global $g_broadcast_message;
		$g_broadcast_message = $contentGateway->get(ContentId::BROADCAST_MESSAGE)['body'];

		$debugBar = $this->get(DebugBar::class);
		if ($debugBar->isEnabled()) {
			$pageHelper->addHead($debugBar->renderHead());
		}

		// this can go, uc was introduced for some links in emails way back in 2014:
		//	4f9f8e3e2389a2859de198a9d0eae12a8de997ee (/app/core/core.control.php, ctrl+f for 'uc=', if you're interested)
		// i can't find it anywhere in the current codebase anymore, so it should be safe to get rid of
		// its usage was finally removed in !1064 (manually merged in f3f79f90f0aa393fe7d8843d1dc28ba75b281569)
		if ($session->may()) {
			$uc = $request->query->get('uc');
			if ($uc !== null) {
				if ($session->id() != $uc) {
					$this->get(Mem::class)->logout($session->id());
					$this->get(RouteHelper::class)->goLogin();
				}
			}
		}
	}

	public function onKernelResponse(ResponseEvent $event)
	{
		$request = $event->getRequest();

		// this attribute is set by onKernelController if the controller that handled the request is a render controller.
		// we should not do anything if this request was not for a render controller,
		// to maintain exactly the same behavior as before
		if ($request->attributes->get(self::NEEDS_POSTPROCESSING) !== true) {
			return;
		}

		$response = $event->getResponse();

		$response->headers->set('X-Frame-Options', 'DENY');
		$response->headers->set('X-Content-Type-Options', 'nosniff');

		$csp = $this->get(ContentSecurityPolicy::class);
		$cspString = $csp->generate($request->getSchemeAndHttpHost(), CSP_REPORT_URI, CSP_REPORT_ONLY);
		$cspParts = explode(': ', $cspString, 2);
		$response->headers->set($cspParts[0], $cspParts[1]);

		$debugBar = $this->get(DebugBar::class);
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
	}

	private function isRenderController(object $controller): bool
	{
		return $controller instanceof IndexController || $controller instanceof FoodsharingController;
	}

	private function get($id)
	{
		return $this->fullServiceContainer->get($id);
	}
}
