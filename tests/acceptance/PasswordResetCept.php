<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('do a password reset');

$newpass = 'TEEEEST';

$user = $I->createFoodsaver();


$I->amOnPage('/?page=login');
$I->click('Passwort vergessen?');

$I->see('Bitte trage hier Deine E-Mail-Adresse ein');
$I->fillField('#email', $user['email']);
$I->click('Senden');

$I->see('Alles klar! Dir wurde ein Link zum Passwortändern per E-Mail zugeschickt');

// recieve a mail
$I->expectNumMails(1, 1000);
$mail = $I->getMails()[0];

$I->assertEquals($mail->headers->to, $user['email'], 'correct recipient'); 

$I->assertRegExp('/http:\/\/.*passwordReset.*&k=[a-f0-9]+/', $mail->html, 'mail should contain a link');
preg_match('/http:\/\/.*?\/(.*?)"/', $mail->html, $matches);
$link = $matches[1];

// there was an strange %20-whitespace appended to the link in the template.
// the template got updated, but test may fail when there is still the old template in the database
// -> see commit 84ea2f1868b91a0cfabd85caa31139364b93f7f7


// go to link in the mail
$I->amOnPage($link);
$I->see('Jetzt kannst Du Dein Passwort ändern');
$I->fillField('#pass1', $newpass);
$I->fillField('#pass2', 'INVALID');
$I->click('Speichern');
$I->see('die Passwörter stimmen nicht überein');


$I->fillField('#pass1', $newpass);
$I->fillField('#pass2', $newpass);
$I->click('Speichern');

$I->seeCurrentUrlEquals('/?page=login');

// password got replaced after login
$I->seeInDatabase('fs_foodsaver', [
	'email' => $user['email'],
	'passwd' => null, // md5
	'fs_password' => null // sha1
]);

// new hash is valid
$newHash = $I->grabFromDatabase('fs_foodsaver', 'password', ['email' => $user['email']]);
$I->assertTrue(password_verify($newpass, $newHash));
