<?php

class StoreUserCest
{
	private $min_quantity_option = 1;
	private $max_quantity_option = 7;
	private $quantity_element_name = 'Abholmenge im Schnitt';

	private $mentioning_public = 0;
	private $mentioning_private = 1;
	private $mentioning_element_name = 'Namensnennung';

	public function _before(HtmlAcceptanceTester $I)
	{
		$this->bezirk_id = $I->createRegion('A region I test with');
		$this->storeCoordinator = $I->createStoreCoordinator(null, ['bezirk_id' => $this->bezirk_id['id']]);
		$I->login($this->storeCoordinator['email']);
	}

	public function DontSeeTheFetchedQuantity(HtmlAcceptanceTester $I)
	{
		$this->createStoreWithFetchedQuantity($I, '');

		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->dontSee($this->quantity_element_name);
	}

	public function SeeTheMinimumFetchedQuantity(HtmlAcceptanceTester $I)
	{
		$this->createStoreWithFetchedQuantity($I, $this->min_quantity_option);

		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->see($this->quantity_element_name);
	}

	public function SeeTheMaximumFetchedQuantity(HtmlAcceptanceTester $I)
	{
		$this->createStoreWithFetchedQuantity($I, $this->max_quantity_option);

		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->see($this->quantity_element_name);
	}

	private function createStoreWithFetchedQuantity($I, $fetched_quantity)
	{
		$this->store = $I->createStore($this->bezirk_id['id'], null, null, ['abholmenge' => $fetched_quantity]);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
	}

	public function SeeStoreMayBeMentionedPublicly(HtmlAcceptanceTester $I)
	{
		$this->createStoreWithFetchedQuantity($I, $this->mentioning_public);

		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->see($this->mentioning_element_name);
	}

	public function SeeStoreMustBeMentionedPrivately(HtmlAcceptanceTester $I)
	{
		$this->createStoreWithFetchedQuantity($I, $this->mentioning_private);

		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->see($this->mentioning_element_name);
	}

	private function createStoreWithMentioning($I, $mentioning)
	{
		$this->store = $I->createStore($this->bezirk_id['id'], null, null, ['presse' => $mentioning]);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
	}
}
