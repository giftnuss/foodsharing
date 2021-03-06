<?php

class CompanyCest
{
	private $bezirk;
	private $bezirk_id;

	private function createStoreAndUsers()
	{
		$I = $this->tester;
		$this->bezirk = $this->tester->createRegion();
		$this->bezirk_id = $this->bezirk['id'];
		$this->store = $I->createStore($this->bezirk_id);
		$this->storeCoordinator = $I->createStoreCoordinator(null, ['bezirk_id' => $this->bezirk_id]);
		$this->participatorA = $I->createFoodsaver(null, ['bezirk_id' => $this->bezirk_id]);
		$this->participatorB = $I->createFoodsaver(null, ['bezirk_id' => $this->bezirk_id]);
		$this->sameRegionFoodsaver = $I->createFoodsaver(null, ['bezirk_id' => $this->bezirk_id]);
		$this->unconnectedFoodsaver = $I->createFoodsaver();
		$this->unconnectedFoodsharer = $I->createFoodsharer();
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
		$I->addStoreTeam($this->store['id'], $this->participatorA['id']);
		$I->addStoreTeam($this->store['id'], $this->participatorB['id']);
	}

	private function loginAsCoordinator()
	{
		$I = $this->tester;
		$I->login($this->storeCoordinator['email']);
	}

	private function loginAsMember()
	{
		$I = $this->tester;
		$I->login($this->participatorA['email']);
	}

	private function loginAsFoodsharer()
	{
		$I = $this->tester;
		$I->login($this->unconnectedFoodsharer['email']);
	}

	private function loginAsUnconnectedFoodsaver()
	{
		$I = $this->tester;
		$I->login($this->unconnectedFoodsaver['email']);
	}

	private function loginAsSameRegionFoodsaver()
	{
		$I = $this->tester;
		$I->login($this->sameRegionFoodsaver['email']);
	}

	public function CoordinatorCanSeeCompanyOnDashboard(AcceptanceTester $I)
	{
		$this->loginAsCoordinator();
		$I->see('Du bist verantwortlich', 'div.head.ui-widget-header.ui-corner-top');
		$I->see($this->store['name'], 'a.ui-corner-all');
	}

	public function MemberCanSeeCompanyOnDashboard(AcceptanceTester $I)
	{
		$this->loginAsMember();
		$I->see('Du holst Lebensmittel ab bei', 'div.head.ui-widget-header.ui-corner-top');
		$I->see($this->store['name'], 'a.ui-corner-all');
	}

	/**
	 * @example["loginAsCoordinator"]
	 * @example["loginAsMember"]
	 */
	public function CanAccessCompanyPage(AcceptanceTester $I, Codeception\Example $example)
	{
		call_user_func([$this, $example[0]]);
		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->see($this->store['name'], '#main .bread');
	}

	/**
	 * @example["loginAsFoodsharer"]
	 * @example["loginAsUnconnectedFoodsaver"]
	 * @example["loginAsSameRegionFoodsaver"]
	 */
	public function CanNotAccessCompanyPage(AcceptanceTester $I, Codeception\Example $example)
	{
		call_user_func([$this, $example[0]]);
		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->cantSeeInCurrentUrl('fsbetrieb');
	}

	/**
	 * @example["loginAsCoordinator", true]
	 * @example["loginAsMember", false]
	 */
	public function CanAccessCompanyEditPage(AcceptanceTester $I, Codeception\Example $example)
	{
		$canAccess = $example[1];
		call_user_func([$this, $example[0]]);
		$I->amOnPage($I->storeEditUrl($this->store['id']));
		if ($canAccess) {
			$I->see('Bezirk ??ndern');
			$I->see('Betriebsansprechpartner');
		} else {
			$I->dontSee('Bezirk ??ndern');
			$I->dontSee('Betriebsansprechpartner');
		}
	}

	public function _before(AcceptanceTester $I)
	{
		$this->tester = $I;
		$this->createStoreAndUsers();
	}

	public function _after(AcceptanceTester $I)
	{
	}
}
