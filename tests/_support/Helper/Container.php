<?php

namespace Helper;

require_once __DIR__ . '/../../../includes/setup.php';

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

		define('DSN', $this->config['dsn']);
		define('DB_HOST', $this->config['host']);
		define('DB_USER', $this->config['user']);
		define('DB_PASS', $this->config['password']);
		define('DB_DB', $this->config['db']);

		global $container;
		$container = initializeLegacyContainer();
		$this->di = $container;

		/*
		$this->di = DI::$shared;
		if (!$this->di->isCompiled()) {
			$this->di->usePDO($this->config['dsn'], $this->config['user'], $this->config['password']);
			$this->di->configureMysqli($this->config['host'], $this->config['user'], $this->config['password'], $this->config['db']);
			$this->di->compile();
		}
		*/
	}

	public function get($id)
	{
		return $this->di->get($id);
	}
}
