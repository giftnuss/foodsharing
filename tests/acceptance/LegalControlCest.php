<?php

class LegalControlCest
{
	/* ToDo: The legal tests fiddle with data. If one of them fails, likely all other tests afterwards fail as well as the database is not reset in between. */
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
		$I->amOnPage('/?page=dashboard');
		$I->see('Datenschutzerklärung');
		$I->checkOption('#legal_form_privacyPolicyAcknowledged');
		$I->click('Einstellungen übernehmen');
		$I->waitForText('schön, dass Du dabei bist und Dich gegen die Lebensmittelverschwendung');
		$I->dontSee('Datenschutzerklärung');
		$I->logMeOut();
		$I->resetThePrivacyPolicyDate($lastModified);
	}

	public function testGivenIAmLoggedInAndIDontAcceptThePrivacyPolicyThenIAmStillAskedForConsent(AcceptanceTester $I)
	{
		$foodsharer = $I->createFoodsharer();
		$I->login($foodsharer['email']);
		$lastModified = $I->updateThePrivacyPolicyDate();
		$I->amOnPage('/?page=dashboard');
		$I->see('Datenschutzerklärung');
		$I->uncheckOption('#legal_form_privacyPolicyAcknowledged');
		$I->click('Einstellungen übernehmen');
		$I->see('Nimmst du die Vereinbarung zur Kenntnis?');
		$I->logMeOut();
		$I->resetThePrivacyPolicyDate($lastModified);
	}

	public function testGivenIAmLoggedInAndWantToDeleteMyAccountThenIGetRedirectedToTheDeleteAccountPage(AcceptanceTester $I)
	{
		$foodsharer = $I->createFoodsharer();
		$I->login($foodsharer['email']);
		$lastModified = $I->updateThePrivacyPolicyDate();
		$I->amOnPage('/?page=dashboard');
		$I->see('Datenschutzerklärung');
		$I->click('Ich möchte meinen Account löschen.');
		$I->amOnPage('/?page=settings&sub=deleteaccount');
		$I->logMeOut();
		$I->resetThePrivacyPolicyDate($lastModified);
	}

	public function testGivenIAmLoggedInAndHaveARoleHigherThanOneThenICanAcceptThePrivacyPolicyAndNotice(AcceptanceTester $I)
	{
		$user = $I->createAmbassador();
		$I->login($user['email']);
		$lastModifiedpp = $I->updateThePrivacyPolicyDate();
		$I->amOnPage('/?page=dashboard');
		$I->see('Datenschutzerklärung');
		$I->dontSeeCheckboxIsChecked('#legal_form_privacyPolicyAcknowledged');
		$I->seeOptionIsSelected('#legal_form_privacyNoticeAcknowledged', 'Ich stimme zu');
		$I->click('Einstellungen übernehmen');
		$I->wait(1);
		$I->dontSeeCheckboxIsChecked('#legal_form_privacyPolicyAcknowledged');
		$I->checkOption('#legal_form_privacyPolicyAcknowledged');
		$I->click('Einstellungen übernehmen');
		$I->waitForText('Dein Stammbezirk ist');
		$I->dontSee('Datenschutzerklärung');

		$I->seeInDatabase('fs_foodsaver', ['id' => $user['id'], 'rolle' => 3]);
		$I->logMeOut();
		$I->resetThePrivacyPolicyDate($lastModifiedpp);
	}

	public function testGivenIAmLoggedInAndAHaveRoleHigherThanOneThenICanDegradeToFoodsaver(AcceptanceTester $I)
	{
		$user = $I->createAmbassador();
		$I->login($user['email']);
		$lastModified = $I->updateThePrivacyPolicyDate();
		$I->amOnPage('/?page=dashboard');
		$I->see('Datenschutzerklärung');
		$I->checkOption('#legal_form_privacyPolicyAcknowledged');
		$I->selectOption('#legal_form_privacyNoticeAcknowledged', 'Ich stimme nicht zu und möchte zum Foodsaver herabgestuft werden');
		$I->click('Einstellungen übernehmen');
		$I->seeInPopup('Bist du dir sicher?');
		$I->cancelPopup();
		$I->click('Einstellungen übernehmen');
		$I->seeInPopup('Bist du dir sicher?');
		$I->seeInDatabase('fs_foodsaver', ['id' => $user['id'], 'rolle' => 3]);
		$I->acceptPopup();
		$I->waitForText('Dein Stammbezirk ist');
		$I->seeInDatabase('fs_foodsaver', ['id' => $user['id'], 'rolle' => 1]);
		$I->logMeOut();
		$I->resetThePrivacyPolicyDate($lastModified);
	}
}
