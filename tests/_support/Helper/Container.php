<?php

namespace Helper;

use Codeception\Module\Db;

require_once __DIR__ . '/../../../includes/setup.php';

class Container extends Db
{
	private $di;

	public function __construct($moduleContainer, $config = null)
	{
		parent::__construct($moduleContainer, $config);
	}

	final public function _initialize(): void
	{
		parent::_initialize();
		global $container;
		$container = initializeContainer();
		$this->di = $container;
	}

	public function get($id)
	{
		return $this->di->get($id);
	}
}
