<?php

namespace Foodsharing\unit;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\RegionPermissions;

final class RegionPermissionsTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var RegionPermissions
	 */
	protected $regionPermissions;

	protected function _before()
	{
		$mock = $this->makeEmpty(Session::class, ['may' => function ($role) { return $role == 'fs'; }]);
		$this->regionPermissions = new RegionPermissions($this->tester->get(RegionGateway::class), $mock);
	}

	public function testMayNotJoinWorkGroup()
	{
		$region = $this->tester->createWorkingGroup('asdf');
		$this->tester->assertFalse($this->regionPermissions->mayJoinRegion($region['id']));
	}

	public function testMayJoinNormalRegion()
	{
		$region = $this->tester->createRegion();
		$this->tester->assertTrue($this->regionPermissions->mayJoinRegion($region['id']));
	}
}
