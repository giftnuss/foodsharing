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

		define('DB_HOST', $this->config['host']);
		define('DB_USER', $this->config['user']);
		define('DB_PASS', $this->config['password']);
		define('DB_DB', $this->config['db']);

		global $container;
		$container = initializeLegacyContainer();
		$this->di = $container;
	}

	public function get($id)
	{
		return $this->di->get($id);
	}
}
