<?php

namespace Foodsharing\Lib\Cache;

use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\InfluxMetrics;

class Caching
{
	private $cacheRules;
	private $cacheMode;
	private $session;
	private $mem;
	private $metrics;

	public function __construct($cache_rules, Session $session, Mem $mem, InfluxMetrics $metrics)
	{
		$this->session = $session;
		$this->mem = $mem;
		$this->cacheRules = $cache_rules;
		$this->cacheMode = $this->session->may() ? 'u' : 'g';
		$this->metrics = $metrics;
	}

	public function lookup()
	{
		if (isset($this->cacheRules[$_SERVER['REQUEST_URI']][$this->cacheMode]) && ($page = $this->mem->getPageCache()) !== false && !isset($_GET['flush'])) {
			$this->metrics->addPageStatData(['cached' => 1]);
			echo $page;
			exit();
		} else {
			$this->metrics->addPageStatData(['cached' => 0]);
		}
	}

	public function shouldCache()
	{
		return isset($this->cacheRules[$_SERVER['REQUEST_URI']][$this->cacheMode]);
	}

	public function cache($content)
	{
		$this->mem->setPageCache(
			$content,
			$this->cacheRules[$_SERVER['REQUEST_URI']][$this->cacheMode]
		);
	}
}
