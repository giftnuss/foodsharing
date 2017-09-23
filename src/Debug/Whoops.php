<?php

namespace Foodsharing\Debug;

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;

/**
 * A static helper class to register a Whoops error handler.
 * Call as early as possible.
 */
class Whoops
{

	public static function register()
	{
		$run     = new \Whoops\Run;
		$handler = new PrettyPageHandler;
		$handler->setPageTitle("Whoops! There was a problem.");

		$run->pushHandler($handler);

		if (\Whoops\Util\Misc::isAjaxRequest()) {
			$run->pushHandler(new JsonResponseHandler);
		}

		$run->register();
	}

}
