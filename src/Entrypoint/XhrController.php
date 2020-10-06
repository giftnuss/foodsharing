<?php

namespace Foodsharing\Entrypoint;

use Foodsharing\Annotation\DisableCsrfProtection;
use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\Xhr\XhrMethods;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\InfluxMetrics;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class XhrController extends AbstractController
{
	/*
	   methods wich are excluded from the CSRF Protection.
	   We start with every method and remove one by another
	   NEVER ADD SOMETING TO THIS LIST!
	*/
	private const csrf_whitelist = [
		// 'getPinPost',
		// 'activeSwitch',
		// 'grabInfo',
		// 'childBezirke',
		// 'bBubble',
		// 'loadMarker',
		// 'uploadPictureRefactorMeSoon',
		'uploadPicture',
		// 'cropagain',
		'pictureCrop',
		// 'out',
		// 'getRecip',
		'addPhoto',
		// 'continueMail',
		'uploadPhoto',
		// 'update_newbezirk',
		// 'update_abholen',
		// 'bezirkTree',
		// 'bteamstatus',
		// 'getBezirk',
		// 'acceptBezirkRequest',
		// 'denyBezirkRequest',
		// 'denyRequest',
		// 'acceptRequest',
		// 'warteRequest',
		// 'betriebRequest',
		// 'saveBezirk',
		// 'delDate',
		// 'fetchDeny',
		// 'fetchConfirm',
		// 'delBPost',
		// 'delPost',
		// 'abortEmail',
		// 'bcontext'
	];

	/**
	 * @DisableCsrfProtection CSRF Protection (originally done for the REST API)
	 * breaks POST on these entrypoints right now,
	 * so this annotation disables it.
	 * Note that this entry point still performs CSRF checks on its own,
	 * except for what's specified in csrf_whitelist.
	 */
	public function __invoke(
		Request $request,
		Session $session,
		Mem $mem,
		InfluxMetrics $influxdb,
		XhrMethods $xhr
	): Response {
		$session->initIfCookieExists();

		// is this actually used anywhere? (prod?)
		global $g_page_cache;
		if (isset($g_page_cache)) {
			$cache = new Caching($g_page_cache, $session, $mem, $influxdb);
			$cache->lookup();
		}

		$action = $request->query->get('f');

		if ($action === null) {
			return new Response(Response::HTTP_BAD_REQUEST);
		}

		if (!in_array($action, XhrController::csrf_whitelist) && !$session->isValidCsrfHeader()) {
			$response = new Response();
			$response->setProtocolVersion('1.1');
			$response->setStatusCode(Response::HTTP_FORBIDDEN);
			$response->setContent('CSRF Failed: CSRF token missing or incorrect.');

			return $response;
		}

		$func = 'xhr_' . $action;
		if (!method_exists($xhr, $func)) {
			return new Response(Response::HTTP_BAD_REQUEST);
		}

		$response = new Response();

		$influxdb->addPageStatData(['controller' => $func]);

		ob_start();
		echo $xhr->$func($_GET);
		$page = ob_get_contents();
		ob_end_clean();

		if ($page === XhrResponses::PERMISSION_DENIED) {
			$response->setProtocolVersion('1.1');
			$response->setStatusCode(Response::HTTP_FORBIDDEN);
			$response->setContent('Permission denied');

			return $response;
		}

		if (is_string($page) && (!trim($page) || $page[0] == '{' || $page[0] == '[')) {
			// just assume it's JSON, to prevent the browser from interpreting it as
			// HTML, which could result in XSS possibilities
			$response->headers->set('Content-Type', 'application/json');
		}

		// check for page caching
		if (isset($cache) && $cache->shouldCache()) {
			$cache->cache($page);
		}

		$response->setContent($page);

		return $response;
	}
}
