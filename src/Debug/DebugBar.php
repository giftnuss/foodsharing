<?php

namespace Foodsharing\Debug;

use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use Foodsharing\Debug\Collectors\DatabaseQueryCollector;

/**
 * A static helper class to wrap our use of \DebugBar.
 *
 * It will set itself up on first use, if the user should not see it, don't call any add* methods.
 *
 * Call DebugBar::addQuery() whenever you have an SQL query to add
 *
 * Call DebugBar::render*() methods as late as possible. If nothing was added it will render an empty string.
 */
class DebugBar
{
	private static $initialized = false;

	/* @var $queryCollector \Foodsharing\Debug\Collectors\DatabaseQueryCollector */
	private static $queryCollector;

	/* @var $debugbar \DebugBar\StandardDebugBar */
	private static $debugbar;

	public static function register()
	{
		self::$debugbar = new \DebugBar\DebugBar();

		self::$debugbar->addCollector(new PhpInfoCollector());
		self::$debugbar->addCollector(new MessagesCollector());
		self::$debugbar->addCollector(new RequestDataCollector());
		self::$debugbar->addCollector(new MemoryCollector());

		self::$queryCollector = new DatabaseQueryCollector();
		self::$debugbar->addCollector(self::$queryCollector);

		self::$initialized = true;
	}

	public static function isEnabled()
	{
		return self::$initialized;
	}

	public static function addMessage($message)
	{
		if (!self::$initialized) {
			return;
		}
		self::$debugbar['messages']->info($message);
	}

	public static function addQuery($sql, $duration, $success, $error_code = null, $error_message = null)
	{
		if (!self::$initialized) {
			return;
		}
		self::$queryCollector->addQuery([$sql, $duration, $success, $error_code, $error_message]);
	}

	public static function renderHead()
	{
		if (!self::$initialized) {
			return '';
		}

		return self::$debugbar->getJavascriptRenderer()->renderHead();
	}

	public static function renderContent()
	{
		if (!self::$initialized) {
			return '';
		}

		return self::$debugbar->getJavascriptRenderer()->render();
	}
}
