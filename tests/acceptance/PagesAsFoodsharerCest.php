<?php

class PagesAsFoodsharerCest
{
	private $emptyFoodsharer;

	public function _before(AcceptanceTester $I)
	{
		$this->emptyFoodsharer = $I->createFoodsharer(null, ['plz' => '', 'stadt' => '', 'anschrift' => '']);
		$this->foodsaver = $I->createFoodsaver();
		$I->login($this->emptyFoodsharer['email']);
	}

	public function canVisitSettingsPage(AcceptanceTester $I)
	{
		$I->amOnPage($I->settingsUrl());
		$I->see('Account löschen');
	}
}
