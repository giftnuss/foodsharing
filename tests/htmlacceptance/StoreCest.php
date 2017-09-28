<?php

class StoreCest
{
	private function createStoreAndUsers()
	{
		$I = $this->tester;
		$this->store = $I->createStore(241);
		$this->storeCoordinator = $I->createStoreCoordinator('pw');
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
		$I->addBezirkMember($this->store['bezirk_id'], $this->storeCoordinator['id']);
	}

	private function loginAsCoordinator()
	{
		$I = $this->tester;
		$I->login($this->storeCoordinator['email'], 'pw');
	}

	public function WillKeepApproxPickupTime(\HtmlAcceptanceTester $I)
	{
		$this->loginAsCoordinator();

		// Check original value
		$I->amOnPage('/?page=betrieb&a=edit&id=' . $this->store['id']);
		$I->see('Keine Angabe', '#public_time option[selected]');

		// Change option and save the page
		$I->selectOption('public_time','morgens');
		$I->click('Senden');

		// Check the page again to make sure our option was saved
		$I->amOnPage('/?page=betrieb&a=edit&id=' . $this->store['id']);
		$I->see('morgens', '#public_time option[selected]');
	}

	public function _before(HtmlAcceptanceTester $I)
	{
		$this->tester = $I;
		$this->createStoreAndUsers();
	}

}
