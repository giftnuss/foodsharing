<?php

use Foodsharing\Lib\Routing;

class RouterReturnsCorrectClassNameTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	protected function _before()
	{
	}

	protected function _after()
	{
	}

	// tests
	public function testReturnNullOnInvalidAppName()
	{
		$this->assertNull(Routing::getClassName('IAmaSurelyNotExistingApp'));
	}

	public function testReturnFqcnForControlClass()
	{
		$actual = Routing::getClassName('settings', 'Control');
		$this->assertEquals('\\Foodsharing\\Modules\\Settings\\SettingsControl', $actual);
	}

	public function testReturnFqcnForXhrClass()
	{
		$actual = Routing::getClassName('settings', 'Xhr');
		$this->assertEquals('\\Foodsharing\\Modules\\Settings\\SettingsXhr', $actual);
	}
}
