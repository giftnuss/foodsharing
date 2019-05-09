<?php

class StoreUserCest
{
	private $min_amount_option = 1;
	private $max_amount_option = 7;
	private $amount_select_element_name = 'Abholmenge im Schnitt';

	public function _before(HtmlAcceptanceTester $I)
	{
		$this->bezirk_id = $I->createRegion('A region I test with');
		$this->storeCoordinator = $I->createStoreCoordinator(null, ['bezirk_id' => $this->bezirk_id['id']]);
		$I->login($this->storeCoordinator['email']);
	}

	public function DontSeeTheAmountOfFood(HtmlAcceptanceTester $I)
	{
		$this->createStoreWithAmount($I, '');

		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->dontSee($this->amount_select_element_name);
	}

	public function SeeTheMinimumAmountOfFood(HtmlAcceptanceTester $I)
	{
		$this->createStoreWithAmount($I, $this->min_amount_option);

		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->see($this->amount_select_element_name);
	}

	public function SeeTheMaximumAmountOfFood(HtmlAcceptanceTester $I)
	{
		$this->createStoreWithAmount($I, $this->max_amount_option);

		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->see($this->amount_select_element_name);
	}

	private function createStoreWithAmount($I, $store_amount_option)
	{
		$this->store = $I->createStore($this->bezirk_id['id'], null, null, ['abholmenge' => $store_amount_option]);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
	}
}
