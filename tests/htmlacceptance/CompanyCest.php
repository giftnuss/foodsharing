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
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
		$I->addStoreTeam($this->store['id'], $this->participatorA['id']);
		$I->addStoreTeam($this->store['id'], $this->participatorB['id']);

		$I->addBezirkMember($this->store['bezirk_id'], $this->storeCoordinator['id']);
		$I->addBezirkMember($this->store['bezirk_id'], $this->participatorA['id']);
		$I->addBezirkMember($this->store['bezirk_id'], $this->participatorB['id']);
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
		$I->amOnPage($I->StoreUrl($this->store['id']));
		$I->see($this->store['name'] . '-Team', 'div.head.ui-widget-header.ui-corner-top');
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
