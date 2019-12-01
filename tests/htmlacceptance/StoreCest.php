<?php

class StoreCest
{
	private $bezirk_id = 241;

	public function _before(HtmlAcceptanceTester $I)
	{
		$this->createStoreAndUsers($I);
	}

	private function createStoreAndUsers(HtmlAcceptanceTester $I)
	{
		$this->store = $I->createStore($this->bezirk_id);
		$this->storeCoordinator = $I->createStoreCoordinator(null, ['bezirk_id' => $this->bezirk_id]);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
	}

	public function WillKeepApproxPickupTime(\HtmlAcceptanceTester $I)
	{
		$I->login($this->storeCoordinator['email']);

		// Check original value
		$I->amOnPage('/?page=betrieb&a=edit&id=' . $this->store['id']);
		$I->see('Keine Angabe', '#public_time option[selected]');

		// Change option and save the page
		$I->selectOption('public_time', 'morgens');
		$I->click('Senden');

		// Check the page again to make sure our option was saved
		$I->amOnPage('/?page=betrieb&a=edit&id=' . $this->store['id']);
		$I->see('morgens', '#public_time option[selected]');
	}
}
