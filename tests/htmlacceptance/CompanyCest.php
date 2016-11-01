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
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], True);
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

	public function CoordinatorCanSeeCompanyOnDashboard(\Htmlacceptancetester $I) {
		$this->loginAsCoordinator();
		$I->see('Du bist verantwortlich', 'div.head.ui-widget-header.ui-corner-top');
		$I->see($this->store['name'], 'a.ui-corner-all');
	}

	public function MemberCanSeeCompanyOnDashboard(\Htmlacceptancetester $I) {
		$this->loginAsMember();
		$I->see('Du holst Essen ab bei', 'div.head.ui-widget-header.ui-corner-top');
		$I->see($this->store['name'], 'a.ui-corner-all');
	}

	/**
	 * @param HtmlacceptanceTester $I
	 * @example["loginAsCoordinator"]
	 * @example["loginAsMember"]
	 */
	public function CanAccessCompanyPage(\HtmlacceptanceTester $I, \Codeception\Example $example) {
		$this->$example[0]();
		$I->amOnPage($I->StoreUrl($this->store['id']));
		$I->see($this->store['name'].'-Team', 'div.head.ui-widget-header.ui-corner-top');
	}

    public function _before(HtmlacceptanceTester $I)
    {
		$this->tester = $I;
		$this->createStoreAndUsers();
    }

    public function _after(HtmlacceptanceTester $I)
    {
    }

}
