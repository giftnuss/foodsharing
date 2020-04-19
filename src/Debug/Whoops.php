<?php

namespace Foodsharing\Debug;

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\Misc;

/**
 * A static helper class to register a Whoops error handler.
 * Call as early as possible.
 */
class Whoops
{
	public static function register(): void
	{
		$whoops = new Run();
		$whoops->pushHandler(new PrettyPageHandler());

		if (Misc::isAjaxRequest()) {
			$whoops->pushHandler(new JsonResponseHandler());
		}

		$whoops->register();
	}
}
