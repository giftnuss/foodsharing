<?php

namespace Helper;

use Foodsharing\DI;

class Container extends \Codeception\Module\Db
{
	private $di;

	public function __construct($moduleContainer, $config = null)
	{
		parent::__construct($moduleContainer, $config);
		$this->di = new DI();
		$this->di->usePDO($this->config['dsn'], $this->config['user'], $this->config['password']);
		$this->di->compile();
	}

	public function get($id)
	{
		return $this->di->get($id);
	}
}
