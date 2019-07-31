<?php

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;

class QuizCest
{
	private $foodsharer;
	private $foodsaver;
	private $storeManager;
	private $quizzes;

	public function _before(AcceptanceTester $I)
	{
		$this->foodsharer = $I->createFoodsharer();
		$this->foodsaver = $I->createFoodsaver();

		$this->quizzes = [];
		foreach ([Role::FOODSAVER, Role::STORE_MANAGER] as $role) {
			$this->quizzes[$role] = $I->createQuiz($role);
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example["foodsharer", "Werde Foodsaver", "Quiz ohne Zeitlimit"]
	 * @example["foodsaver", "Werde Betriebsverantwortliche", "Quiz jetzt starten"]
	 */
	public function canStartQuiz(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email']);
		$I->amOnPage($I->settingsUrl());

		$I->click($example[1]);
		$quizRole = $this->{$example[0]}['rolle'] + 1;
		$I->seeCurrentUrlEquals($I->upgradeQuizUrl($quizRole));

		$I->waitForText('Du musst noch das Quiz bestehen!');
		$I->click($example[2]);

		$quizName = $this->quizzes[$quizRole]['name'];
		$I->waitForText($quizName . '-Quiz');
		$I->click('Quiz starten');

		$questionText = $this->quizzes[$quizRole]['questions'][0]['text'];
		$I->waitForText($questionText);
	}
}
