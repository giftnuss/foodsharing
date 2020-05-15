<?php

use Foodsharing\Modules\Search\SearchGateway;

class SearchGatewayTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;

	protected SearchGateway $searchGateway;

	public function _before()
	{
		$this->searchGateway = $this->tester->get(SearchGateway::class);
	}

	public function testSearchUserInGroups()
	{
		/* Just check that database is really empty before we start so test logic stays true */
		/* TODO: The current test infrastructure does not reset the database in between tests although it should. This should be taken care of at some point,
		until then we just expect this test to fail here and then... */
		//$this->tester->seeNumRecords(0, 'fs_foodsaver');
		$region1 = $this->tester->createRegion();
		$region2 = $this->tester->createRegion();
		$fs1 = $this->tester->createFoodsaver(null, ['name' => 'Alberto', 'nachname' => 'Albertino']);
		$fs2 = $this->tester->createFoodsaver(null, ['name' => 'Albert', 'nachname' => 'Hunne']);
		$fs3 = $this->tester->createFoodsaver(null, ['name' => 'Fred', 'nachname' => 'Weiß']);
		$fs4 = $this->tester->createFoodsaver(null, ['name' => 'Karl-Heinz', 'nachname' => 'Liebermensch']);
		$fs5 = $this->tester->createFoodsaver(null, ['name' => 'Matthias (Matze)', 'nachname' => 'Altenburg von um Heuschreckenland']);
		$this->tester->addRegionMember($region1['id'], $fs4['id']);
		$this->tester->addRegionMember($region2['id'], $fs2['id']);
		$this->tester->addRegionMember($region2['id'], $fs3['id']);
		$f1 = $fs1['id'];
		$f2 = $fs2['id'];
		$f3 = $fs3['id'];
		$f4 = $fs4['id'];
		$f5 = $fs5['id'];
		$this->assertEqualsCanonicalizing([$f1, $f2], array_column($this->searchGateway->searchUserInGroups('Albe', null), 'id'));
		$this->assertEqualsCanonicalizing([$f4], array_column($this->searchGateway->searchUserInGroups('Karl-Heinz', null), 'id'));
		$this->assertEqualsCanonicalizing([$f5], array_column($this->searchGateway->searchUserInGroups('-(Matze)', null), 'id'));
		$this->assertEqualsCanonicalizing([$f5], array_column($this->searchGateway->searchUserInGroups('von Heuschreckenland', null), 'id'));
		$this->assertEqualsCanonicalizing([$f5], array_column($this->searchGateway->searchUserInGroups('um Heuschreckenland', null), 'id'));
		$this->assertEqualsCanonicalizing([], array_column($this->searchGateway->searchUserInGroups('Fr*d', null), 'id'));
		$this->assertEqualsCanonicalizing([$f2], array_column($this->searchGateway->searchUserInGroups('Alb', [$region2['id']]), 'id'));
	}
}
