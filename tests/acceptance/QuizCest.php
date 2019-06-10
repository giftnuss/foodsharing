<?php

class QuizCest
{
  private $foodsharer;
  private $foodsaver;

	public function _before(AcceptanceTester $I)
	{
    $foodsaverQuiz = $I->addQuiz(1);
    $bipQuiz = $I->addQuiz(2);
    $this->foodsharer = $I->createFoodsharer();
    $this->foodsaver = $I->createFoodsaver();
	}

	// tests

	/**
	 * @param AcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example["foodsharer", "Werde Foodsaver", "up_fs", "Quiz mit Zeitlimit"]
	 * @example["foodsaver", "Werde Betriebsverantwortliche", "up_bip", "Quiz jetzt starten"]
	 */
	public function canStartUpgradeQuiz(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email']);
		$I->amOnPage($I->settingsUrl());
    $I->click($example[1]);
    $I->seeInCurrentUrl('sub=upgrade/' . $example[2]);
    $I->click($example[3]);
	}
}
