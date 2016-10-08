<?php
$I = new AcceptanceTester($scenario);

// http://codeception.com/docs/modules/WebDriver


$I->wantTo('try to create a new company as foodsaver');
$I->amOnPage('/');
$I->fillField('email_adress', 'userB@example.com');
$I->fillField('password', 'userb');
$I->click('#loginbar input[type=submit]');
$I->amOnPage('/?page=dashboard');
$I->see('Hallo');
$I->dontSee('Neuen Betrieb eintragen');



// disable popups, as they are not supported in PhantomJS
// if they were could use seeInPopup/acceptPopup
$I->executeJS("window.confirm = function(){return true;};");
$I->executeJS("window.alert = function(){return true;};");

// check if user see Link to "Neuen Betrieb eintragen"

$I->amOnPage('/?page=betrieb&bid=903');
$I->dontSee('Neuen Betrieb eintragen');

// check if foodsaver without bieb-quiz can add new stores

$I->amOnPage('/?page=betrieb&a=new&bid=903');
$I->seeCurrentUrlEquals('/?page=settings&sub=upgrade/up_bip'); // it redirects