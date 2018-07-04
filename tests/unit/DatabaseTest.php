<?php


class DatabaseTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Core\Database
	 */
	private $db;

	protected function _before()
	{
		$this->db = $this->tester->get(\Foodsharing\Modules\Core\Database::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->foodsaver2 = $this->tester->createFoodsaver();
	}

	public function testFetchByCriteria()
	{
		$result = $this->db->fetchByCriteria(
			'fs_foodsaver',
			['email', 'name'],
			['id' => $this->foodsaver['id']]
		);
		$this->tester->assertEquals(
			$result,
			['email' => $this->foodsaver['email'], 'name' => $this->foodsaver['name']]
		);
	}

	public function testFetchAllByCriteria()
	{
		$result = $this->db->fetchAllByCriteria(
			'fs_foodsaver',
			['email'],
			['id' => $this->foodsaver['id']]
		);
		$this->tester->assertEquals(
			$result,
			[['email' => $this->foodsaver['email']]]
		);
	}

	public function testFetchAllValuesByCriteria()
	{
		$result = $this->db->fetchAllValuesByCriteria(
			'fs_foodsaver',
			'email',
			['id' => $this->foodsaver['id']]
		);
		$this->tester->assertEquals(
			$result,
			[$this->foodsaver['email']]
		);
	}

	public function testFetchValueByCriteria()
	{
		$result = $this->db->fetchValueByCriteria(
			'fs_foodsaver',
			'email',
			['id' => $this->foodsaver['id']]
		);
		$this->tester->assertEquals(
			$result,
			$this->foodsaver['email']
		);
	}

	public function testFetchValueByCriteriaThrowsIfNotFound()
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Expected one or more results, but none was returned');
		$this->db->fetchValueByCriteria(
			'fs_foodsaver',
			'email',
			['id' => -1]
		);
	}
}
