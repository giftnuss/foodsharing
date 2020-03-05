<?php

namespace Foodsharing\Debug;

use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\StandardDebugBar;
use Foodsharing\Debug\Collectors\DatabaseQueryCollector;
use Foodsharing\Debug\Collectors\PrettierPDOCollector;

class EnabledDebugBar implements DebugBar
{
	/* @var DatabaseQueryCollector $queryCollector */
	private $queryCollector;

	/* @var StandardDebugBar $bar */
	private $bar;

	public function __construct(TraceablePDO $traceablePDO)
	{
		$this->bar = new \DebugBar\DebugBar();
		$this->bar->addCollector(new PhpInfoCollector());
		$this->bar->addCollector(new MessagesCollector());
		$this->bar->addCollector(new RequestDataCollector());
		$this->bar->addCollector(new MemoryCollector());

		$pdoCollector = new PrettierPDOCollector($traceablePDO);
		$pdoCollector->setRenderSqlWithParams(true, '');
		$this->bar->addCollector($pdoCollector);

		$this->queryCollector = new DatabaseQueryCollector();
		$this->bar->addCollector($this->queryCollector);
	}

	public function isEnabled()
	{
		return true;
	}

	public function addMessage($message)
	{
		$this->bar['messages']->info($message);
	}

	public function addQuery($sql, $duration, $success, $error_code = null, $error_message = null)
	{
		$this->queryCollector->addQuery([$sql, $duration, $success, $error_code, $error_message]);
	}

	public function renderHead()
	{
		return $this->bar->getJavascriptRenderer()->renderHead();
	}

	public function renderContent()
	{
		return $this->bar->getJavascriptRenderer()->render();
	}
}
