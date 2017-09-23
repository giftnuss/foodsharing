<?php

namespace Foodsharing\Debug;

use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;

use Foodsharing\Debug\Collectors\DatabaseQueryCollector;

/**
 * A static helper class to wrap our use of \DebugBar.
 *
 * It will set itself up on first use, if the user should not see it, don't call any add* methods.
 *
 * Call DebugBar::addQuery() whenever you have an SQL query to add
 *
 * Call DebugBar::render*() methods as late as possible. If nothing was added it will render an empty string.
 *
 */
class DebugBar
{
	private static $initialized = false;

	/* @var $queryCollector \Foodsharing\Debug\Collectors\DatabaseQueryCollector */
	private static $queryCollector;

	/* @var $debugbar \DebugBar\StandardDebugBar */
	private static $debugbar;

	private static function initialize()
	{
		self::$debugbar = new \DebugBar\DebugBar();

		self::$debugbar->addCollector(new PhpInfoCollector());
		self::$debugbar->addCollector(new MessagesCollector());
		self::$debugbar->addCollector(new RequestDataCollector());
		self::$debugbar->addCollector(new TimeDataCollector());
		self::$debugbar->addCollector(new MemoryCollector());

		self::$queryCollector = new DatabaseQueryCollector();
		self::$debugbar->addCollector(self::$queryCollector);

		self::$initialized = true;
	}

	public static function addMessage($message)
	{
		if (!self::$initialized) {
			self::initialize();
		}
		self::$debugbar['messages']->info($message);
	}

	public static function addQuery($query, $duration)
	{
		if (!self::$initialized) {
			self::initialize();
		}
		self::$queryCollector->addQuery($query, $duration);
	}

	public static function renderHead()
	{
		if (self::$initialized) {
			return self::$debugbar->getJavascriptRenderer()->renderHead();
		} else {
			return '';
		}
	}

	public static function renderContent()
	{
		if (self::$initialized) {
			return self::$debugbar->getJavascriptRenderer()->render();
		} else {
			return '';
		}
	}
}
