<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('Change mail in profile');
$pass = sq('pass');
$newmail = 'test@blaa.com';

$user = $I->createFoodsaver($pass);

$I->login($user['email'], $pass);

// request mail with link
$I->amOnPage('/?page=settings&sub=general');
$I->click('E-Mail-Adresse ändern');
$I->waitForElementVisible('#newmail');
$I->fillField('#newmail', $newmail);
$I->executeJS("$('button:contains(E-Mail-Adresse ändern)').trigger("click") ");
$I->waitForElementVisible('#pulse-info', 4);
$I->see('Gehe jetzt zu Deinem');

// recieve a mail
$I->expectNumMails(1, 5);
$mail = $I->getMails()[0];
$I->assertEquals($mail->headers->to, $newmail, 'correct recipient');
$I->assertRegExp('/http:\/\/.*&newmail=[a-f0-9]+/', $mail->html, 'mail should contain a link');
preg_match('/http:\/\/.*?(\/.*?)"/', $mail->html, $matches);
$link = $matches[1];

// open link, fill in password and submit
$I->amOnPage($link);
$I->waitForElementVisible('#passcheck');
$I->fillField('#passcheck', $pass);
$I->executeJS("$('button:contains(Bestätigen)').trigger("click") ");
$I->waitForElementVisible('#pulse-info', 4);
$I->see('Deine E-Mail-Adresse wurde geändert!');

$I->seeInDatabase('fs_foodsaver', array('id' => $user['id'], 'email' => $newmail));
