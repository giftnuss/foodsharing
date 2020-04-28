<?php

class LegalControlCest
{
	public function testGivenIAmNotLoggedInThenTheLegalPageShowsThePrivacyPolicyWithoutAskingForConsent(AcceptanceTester $I)
	{
		$I->amOnPage('/?page=legal');
		$I->see('Datenschutzerklärung');
		$I->dontSee('Nimmst du die Vereinbarung zur Kenntnis?');
	}

	public function testGivenIAmLoggedInThenICanAcceptThePrivacyPolicy(AcceptanceTester $I)
	{
		$foodsharer = $I->createFoodsharer();
		$I->login($foodsharer['email']);
		$lastModified = $I->updateThePrivacyPolicyDate();
		$I->amOnPage('/?page=dashbord');
		$I->see('Datenschutzerklärung');
		$I->checkOption('#legal_form_privacyPolicyAcknowledged');
		$I->click('Einstellungen übernehmen');
		$I->amOnPage('/?page=dashbord');
		$I->dontSee('Datenschutzerklärung');
		$I->logout();
		$I->resetThePrivacyPolicyDate($lastModified);
	}

	public function testGivenIAmLoggedInAndIDontAcceptThePrivacyPolicyThenIAmStillAskedForConsent(AcceptanceTester $I)
	{
		$foodsharer = $I->createFoodsharer();
		$I->login($foodsharer['email']);
		$lastModified = $I->updateThePrivacyPolicyDate();
		$I->amOnPage('/?page=dashbord');
		$I->see('Datenschutzerklärung');
		$I->uncheckOption('#legal_form_privacyPolicyAcknowledged');
		$I->click('Einstellungen übernehmen');
		$I->see('Nimmst du die Vereinbarung zur Kenntnis?');
		$I->logout();
		$I->resetThePrivacyPolicyDate($lastModified);
	}

	public function testGivenIAmLoggedInAndWantToDeleteMyAccountThenIGetRedirectedToTheDeleteAccountPage(AcceptanceTester $I)
	{
		$foodsharer = $I->createFoodsharer();
		$I->login($foodsharer['email']);
		$lastModified = $I->updateThePrivacyPolicyDate();
		$I->amOnPage('/?page=dashbord');
		$I->see('Datenschutzerklärung');
		$I->click('Ich möchte meinen Account löschen.');
		$I->amOnPage('/?page=settings&sub=deleteaccount');
		$I->logout();
		$I->resetThePrivacyPolicyDate($lastModified);
	}

	public function testGivenIAmLoggedInAndHaveARoleHigherThanOneThenICanAcceptThePrivacyPolicyAndNotice(AcceptanceTester $I)
	{
		$orga = $I->createOrga();
		$I->login($orga['email']);
		$lastModified = $I->updateThePrivacyPolicyDate();
		$I->amOnPage('/?page=dashbord');
		$I->see('Datenschutzerklärung');
		$I->checkOption('#legal_form_privacyPolicyAcknowledged');
		$I->checkOption('#legal_form_privacyNoticeAcknowledged', 'Ich stimme zu.');
		$I->click('Einstellungen übernehmen');
		$I->amOnPage('/?page=dashbord');
		$I->dontSee('Datenschutzerklärung');
		$I->logout();
		$I->resetThePrivacyPolicyDate($lastModified);
	}

	public function testGivenIAmLoggedInAndAHaveRoleHigherThanOneThenICanDegradeToFoodsaver(AcceptanceTester $I)
	{
		$orga = $I->createOrga();
		$I->login($orga['email']);
		$lastModified = $I->updateThePrivacyPolicyDate();
		$I->amOnPage('/?page=dashbord');
		$I->see('Datenschutzerklärung');
		$I->checkOption('#legal_form_privacyPolicyAcknowledged');
		$I->checkOption('#legal_form_privacyNoticeAcknowledged', 'Ich stimme nicht zu und möchte zum Foodsaver herabgestuft werden');
		$I->click('Einstellungen übernehmen');
		$I->amOnPage('/?page=dashbord');
		$I->dontSee('Datenschutzerklärung');
		$I->logout();
		$I->resetThePrivacyPolicyDate($lastModified);
	}
}
