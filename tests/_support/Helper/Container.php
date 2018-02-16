<?php

namespace Helper;

use Foodsharing\DI;

class Container extends \Codeception\Module\Db
{
	private $di;

	public function __construct($moduleContainer, $config = null)
	{
		parent::__construct($moduleContainer, $config);
	}

	public function _initialize()
	{
		parent::_initialize();
		$this->di = DI::$shared;
		if (!$this->di->isCompiled()) {
			$this->di->usePDO($this->config['dsn'], $this->config['user'], $this->config['password']);
			$this->di->configureMysqli($this->config['host'], $this->config['user'], $this->config['password'], $this->config['db']);
			$this->di->compile();
		}
	}

	public function get($id)
	{
		return $this->di->get($id);
	}
}
