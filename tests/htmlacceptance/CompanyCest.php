<?php


class CompanyCest
{
	private function createStoreAndUsers()
	{
		$I = $this->tester;
		$this->store = $I->createStore(241);
		$this->storeCoordinator = $I->createStoreCoordinator('pw');
		$this->participatorA = $I->createFoodsaver('pw');
		$this->participatorB = $I->createFoodsaver('pw');
		$this->sameRegionFoodsaver = $I->createFoodsaver();
		$this->unconnectedFoodsaver = $I->createFoodsaver();
		$this->unconnectedFoodsharer = $I->createFoodsharer();
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
		$I->addStoreTeam($this->store['id'], $this->participatorA['id']);
		$I->addStoreTeam($this->store['id'], $this->participatorB['id']);

		$I->addBezirkMember($this->store['bezirk_id'], $this->storeCoordinator['id']);
		$I->addBezirkMember($this->store['bezirk_id'], $this->participatorA['id']);
		$I->addBezirkMember($this->store['bezirk_id'], $this->participatorB['id']);
		$I->addBezirkMember($this->store['bezirk_id'], $this->sameRegionFoodsaver['id']);
	}

	private function loginAsCoordinator()
	{
		$I = $this->tester;
		$I->login($this->storeCoordinator['email'], 'pw');
	}

	private function loginAsMember()
	{
		$I = $this->tester;
		$I->login($this->participatorA['email'], 'pw');
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

	public function CoordinatorCanSeeCompanyOnDashboard(\HtmlAcceptanceTester $I)
	{
		$this->loginAsCoordinator();
		$I->see('Du bist verantwortlich', 'div.head.ui-widget-header.ui-corner-top');
		$I->see($this->store['name'], 'a.ui-corner-all');
	}

	public function MemberCanSeeCompanyOnDashboard(\HtmlAcceptanceTester $I)
	{
		$this->loginAsMember();
		$I->see('Du holst Lebensmittel ab bei', 'div.head.ui-widget-header.ui-corner-top');
		$I->see($this->store['name'], 'a.ui-corner-all');
	}

	/**
	 * @param HtmlAcceptanceTester $I
	 * @example["loginAsCoordinator"]
	 * @example["loginAsMember"]
	 */
	public function CanAccessCompanyPage(\HtmlAcceptanceTester $I, \Codeception\Example $example)
	{
		call_user_func(array($this, $example[0]));
		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->see($this->store['name'] . '-Team', 'div.head.ui-widget-header.ui-corner-top');
	}

	/**
	 * @param HtmlAcceptanceTester $I
	 * @example["loginAsFoodsharer"]
	 * @example["loginAsUnconnectedFoodsaver"]
	 * @example["loginAsSameRegionFoodsaver"]
	 */
	public function CanNotAccessCompanyPage(\HtmlAcceptanceTester $I, \Codeception\Example $example)
	{
		call_user_func(array($this, $example[0]));
		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->cantSeeInCurrentUrl('fsbetrieb');
	}

	/**
	 * @param HtmlAcceptanceTester $I
	 * @example["loginAsCoordinator", true]
	 * @example["loginAsMember", false]
	 */
	public function CanAccessCompanyEditPage(\HtmlAcceptanceTester $I, \Codeception\Example $example)
	{
		$canAccess = $example[1];
		call_user_func(array($this, $example[0]));
		$I->amOnPage($I->storeEditUrl($this->store['id']));
		if ($canAccess) {
			$I->see('Stammbezirk');
			$I->see('Betriebsansprechpartner');
		} else {
			$I->dontSee('Stammbezirk');
			$I->dontSee('Betriebsansprechpartner');
		}
	}

	public function _before(HtmlAcceptanceTester $I)
	{
		$this->tester = $I;
		$this->createStoreAndUsers();
	}

	public function _after(HtmlAcceptanceTester $I)
	{
	}
}
