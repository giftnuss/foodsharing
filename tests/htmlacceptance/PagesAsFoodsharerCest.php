<?php

class PagesAsFoodsharerCest
{
	private $emptyFoodsharer;

	public function _before(HtmlAcceptanceTester $I)
	{
		$this->emptyFoodsharer = $I->createFoodsharer(null, ['plz' => '', 'stadt' => '', 'anschrift' => '']);
		$this->foodsaver = $I->createFoodsaver();
		$I->login($this->emptyFoodsharer['email']);
	}

	public function canVisitSettingsPage(HtmlAcceptanceTester $I)
	{
		$I->amOnPage($I->settingsUrl());
		$I->see('Account löschen');
		$I->seeResponseCodeIs(200);
	}
}
