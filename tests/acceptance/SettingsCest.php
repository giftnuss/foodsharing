<?php

use Foodsharing\Modules\Core\DBConstants\Info\InfoType;

class SettingsCest
{
	private $foodsaver;
	private $foodSharePoint;

	public function _before(AcceptanceTester $I)
	{
		$this->foodsaver = $I->createFoodsaver();
		$region = $I->createRegion();
		$this->foodSharePoint = $I->createFoodSharePoint($this->foodsaver['id'], $region['id']);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example["newsletter", false]
	 * @example["newsletter", true]
	 * @example["infomail_message", false]
	 * @example["infomail_message", true]
	 */
	public function userCanChangeGeneralSubscriptions(AcceptanceTester $I, \Codeception\Example $example)
	{
		$field = $example[0];
		$selector = $this->createSelector($field);
		$targetValue = $example[1] ? 1 : 0;
		$initValue = $example[1] ? 0 : 1;
		$this->foodsaver = $I->createFoodsaver(null, [$field => $initValue]);

		$I->login($this->foodsaver['email']);
		$I->amOnPage('/?page=settings&sub=info');
		$I->waitForPageBody();
		$I->seeOptionIsSelected($selector, $initValue);
		$I->selectOption($selector, $targetValue);
		$I->click('Speichern');

		$I->waitForPageBody();
		$I->seeOptionIsSelected($selector, $targetValue);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example[0, "NONE"]
	 * @example[1, "EMAIL"]
	 * @example[2, "BELL"]
	 */
	public function userCanChangeFoodsharepointSubscriptions(AcceptanceTester $I, \Codeception\Example $example)
	{
		$targetValue = $example[0];
		$selector = $this->createSelector('fairteiler_' . $this->foodSharePoint['id']);
		$follower = $I->createFoodsaver();
		$I->addFoodSharePointFollower($follower['id'], $this->foodSharePoint['id']);

		$I->login($follower['email']);
		$I->amOnPage('/?page=settings&sub=info');
		$I->waitForPageBody();
		$I->seeOptionIsSelected($selector, InfoType::EMAIL);
		$I->selectOption($selector, $targetValue);
		$I->click('Speichern');

		$I->waitForPageBody();
		if ($targetValue == InfoType::NONE) {
			$I->dontSeeElement($selector);
		} else {
			$I->seeOptionIsSelected($selector, $targetValue);
		}
	}

	public function managerCanNotChangeFoodsharepointSubscriptions(AcceptanceTester $I)
	{
		$selector = $this->createSelector('fairteiler_' . $this->foodSharePoint['id']);

		$I->login($this->foodsaver['email']);
		$I->amOnPage('/?page=settings&sub=info');
		$I->waitForPageBody();
		$I->seeOptionIsSelected($selector, InfoType::EMAIL);
		$I->selectOption($selector, InfoType::BELL);
		$I->click('Speichern');

		$I->waitForPageBody();
		$I->seeOptionIsSelected($selector, InfoType::EMAIL);
	}

	private function createSelector(string $field)
	{
		return '#' . $field . '-wrapper input[name="' . $field . '"]';
	}
}
