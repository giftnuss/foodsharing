<?php

$phonenumber = '+49483123213';
$mobilenumber = '+491518417482';
$I = new AcceptanceTester($scenario);
$I->wantTo('edit my profile fields as a foodsaver');

$foodsaver = $I->createFoodsaver(null, ['name' => 'fs', 'nachname' => 'one']);

$I->login($foodsaver['email']);

$I->amOnPage('/?page=settings');
$I->waitForPageBody();

/*
 * This part should check geocoding. Apparently, the Typeahead autocompletion does not come up in selenium. Help appreciated.
$I->fillField('#addresspicker', 'Kantstraße 20, Wurzen');
$I->wait(1);
$I->click('Kantstraße 20, Wurzen, Germany', '.tt-dropdown-menu');

$I->see('Kantstraße 20', '#anschrift');
$I->see('Wurzen', '#ort');
$I->see('04808', '#plz');
*/

$I->fillField('#telefon', $phonenumber);
$I->fillField('#handy', $mobilenumber);
$I->fillField('#geb_datum', '1988-05-31');
$I->fillField('#homepage', 'www.matthias-larisch.de');
$I->fillField('#about_me_public', 'Ich mag foodsharing.');
$I->selectOption('#about_me_public', 'Meine Daten niemandem zeigen.');

$I->click('Speichern');

$I->wait(0.5);
$I->waitForPageBody();
/*
 * There is no way to change these without typeahead/geocode. Maybe some complaining users are right? :)
$I->see('Kantstraße 20', '#anschrift');
$I->see('Wurzen', '#ort');
$I->see('04808', '#plz');
*/
$I->seeInField('#telefon', $phonenumber);
$I->seeInField('#handy', $mobilenumber);
$I->seeInField('#geb_datum', '1988-05-31');
$I->seeInField('#homepage', 'http://www.matthias-larisch.de');
$I->seeInField('#about_me_public', 'Ich mag foodsharing.');
$I->seeOptionIsSelected('#photo_public-wrapper', 'Meine Daten niemandem zeigen.');

$I->fillField('#homepage', 'https://www.matthias-larisch.de');
$I->click('Speichern');

$I->wait(0.5);
$I->waitForPageBody();
$I->seeInField('#homepage', 'https://www.matthias-larisch.de');
/* I have no picture - that is the default */
$I->seeElement('//img[@src="img/portrait.png"]');

$I->click('Neues Foto hochladen');
$I->attachFile('//input[@type="file"][@name="uploadpic"]', 'avatar-300px.png');
$I->waitForElement('#fotoupload-save');
$I->clickWithLeftButton('#fotoupload-save');
/* Now I have a picture and should not see the default */
$I->dontSeeElement('//img[@src="img/portrait.png"]');
