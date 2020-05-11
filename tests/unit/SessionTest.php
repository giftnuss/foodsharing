<?php

use Foodsharing\Lib\Session;

class SessionTest extends \Codeception\Test\Unit
{
	protected $tester;

	private Session $session;

	protected function _before()
	{
		$this->session = $this->tester->get(\Foodsharing\Lib\Session::class);
		$this->session->init();
	}

	public function testRefreshFromDatabase()
	{
		$options = ['test', 'answer' => 42, ['foo', 'bar']];
		$foodsharer = $this->tester->createFoodsharer(null, ['option' => serialize($options)]);

		$this->session->refreshFromDatabase($foodsharer['id']);
	}
}
