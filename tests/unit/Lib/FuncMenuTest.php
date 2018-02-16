<?php

use Foodsharing\Lib\Func;
use Foodsharing\Lib\View\Utils;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;

class FuncMenuTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var Func
	 */
	private $func;

	/**
	 * @var array
	 */
	private $foodsaver;

	/**
	 * @var array
	 */
	private $region;

	/**
	 * @var array
	 */
	private $store;

	protected function _before()
	{
		$this->func = $this->tester->get(Func::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->region = $this->tester->createRegion('some region');
		$this->store = $this->tester->createStore($this->region['id']);
		$this->tester->addBezirkMember($this->region['id'], $this->foodsaver['id']);

		// TODO: remove these once globals are gone
		global $g_view_utils;
		global $g_func;
		$g_func = $this->func;
		$g_view_utils = new Utils();
	}

	public function testLoggedOutMenu()
	{
		$menu = $this->call('getMenuFn', $this->menuArgs(['loggedIn' => false]));
		foreach (['default', 'mobile'] as $type) {
			$this->assertContains('Mach mit!', $menu[$type]);
		}
		$this->assertNotContains('mobilemenu', $menu['default']);
		$this->assertContains('mobilemenu', $menu['mobile']);
	}

	public function testLoggedInMenu()
	{
		$menu = $this->call('getMenuFn', $this->menuArgs());
		foreach (['default', 'mobile'] as $type) {
			$this->assertNotContains('Mach mit!', $menu[$type]);
			$this->assertContains(htmlspecialchars($this->region['name']), $menu[$type]);
		}
	}

	public function testMenuBlogEntryIsAdded()
	{
		foreach (['default', 'mobile'] as $type) {
			$this->assertContains(
				'menu_blog',
				$this->diff(
					$this->call('getMenuFn', $this->menuArgs())[$type],
					$this->call('getMenuFn', $this->menuArgs(['mayEditBlog' => true]))[$type]
				)
			);
		}
	}

	public function testMenuQuizEntryIsAdded()
	{
		foreach (['default', 'mobile'] as $type) {
			$this->assertContains(
				'menu_quiz',
				$this->diff(
					$this->call('getMenuFn', $this->menuArgs())[$type],
					$this->call('getMenuFn', $this->menuArgs(['mayEditQuiz' => true]))[$type]
				)
			);
		}
	}

	public function testMenuReportsEntryIsAdded()
	{
		foreach (['default', 'mobile'] as $type) {
			$this->assertContains(
				'menu_reports',
				$this->diff(
					$this->call('getMenuFn', $this->menuArgs())[$type],
					$this->call('getMenuFn', $this->menuArgs(['mayHandleReports' => true]))[$type]
				)
			);
		}
	}

	public function testMenuOrgaEntriesAreAdded()
	{
		foreach (['default', 'mobile'] as $type) {
			$this->assertContains(
				'menu_manage_regions',
				$this->diff(
					$this->call('getMenuFn', $this->menuArgs())[$type],
					$this->call('getMenuFn', $this->menuArgs(['isOrgaTeam' => true]))[$type]
				)
			);
		}
	}

	public function testMenuBotEntriesAreAdded()
	{
		foreach (['default'] as $type) {
			$this->assertContains(
				'Verifizierungen',
				$this->diff(
					$this->call('getMenuFn', $this->menuArgs())[$type],
					$this->call('getMenuFn', $this->menuArgs([
						'regions' => [array_merge($this->region, ['isBot' => true])],
					]))[$type]
				)
			);
		}
	}

	public function testMenuStoreEntriesAreAdded()
	{
		foreach (['default', 'mobile'] as $type) {
			$this->assertContains(
				htmlspecialchars($this->store['name']),
				$this->diff(
					$this->call('getMenuFn', $this->menuArgs())[$type],
					$this->call('getMenuFn', $this->menuArgs([
						'stores' => [$this->store]
					]))[$type]
				)
			);
		}
	}

	private function call($method, $args)
	{
		return call_user_func_array([$this->func, $method], $args);
	}

	private function menuArgs($extraParams = [])
	{
		return $this->paramsToArgs(array_merge([
			'loggedIn' => true,
			'regions' => [array_merge($this->region, ['isBot' => false])],
			'hasFsRole' => true,
			'isOrgaTeam' => false,
			'mayEditBlog' => false,
			'mayEditQuiz' => false,
			'mayHandleReports' => false,
			'stores' => [],
			'workingGroups' => [],
			'sessionMailbox' => false,
			'fsId' => $this->foodsaver['id'],
			'image' => ''
		], $extraParams));
	}

	private function paramsToArgs($params)
	{
		return [
			$params['loggedIn'],
			$params['regions'],
			$params['hasFsRole'],
			$params['isOrgaTeam'],
			$params['mayEditBlog'],
			$params['mayEditQuiz'],
			$params['mayHandleReports'],
			$params['stores'],
			$params['workingGroups'],
			$params['sessionMailbox'],
			$params['fsId'],
			$params['image']
		];
	}

	private function diff(string $a, string $b)
	{
		$builder = new DiffOnlyOutputBuilder('');
		$differ = new Differ($builder);

		return $differ->diff($a, $b);
	}
}
