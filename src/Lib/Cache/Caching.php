<?php

namespace Foodsharing\Lib\Cache;

use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;

class Caching
{
	private $cacheRules;
	private $cacheMode;
	private $session;

	public function __construct($cache_rules, Session $session)
	{
		$this->session = $session;
		$this->cacheRules = $cache_rules;
		$this->cacheMode = $this->session->may() ? 'u' : 'g';
	}

	public function lookup()
	{
		if (isset($this->cacheRules[$_SERVER['REQUEST_URI']][$this->cacheMode]) && ($page = Mem::getPageCache()) !== false && !isset($_GET['flush'])) {
			echo $page;
			exit();
		}
	}

	public function shouldCache()
	{
		return isset($this->cacheRules[$_SERVER['REQUEST_URI']][$this->cacheMode]);
	}

	public function cache($content)
	{
		Mem::setPageCache(
			$content,
			$this->cacheRules[$_SERVER['REQUEST_URI']][$this->cacheMode]
		);
	}
}
