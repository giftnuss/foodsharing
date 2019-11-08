<?php

class SettingsCest
{
	private $foodsaver;

	protected function _before(AcceptanceTester $I)
	{
	}

	/**
	 * @param AcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example["newsletter", false]
	 * @example["newsletter", true]
	 * @example["infomail_message", false]
	 * @example["infomail_message", true]
	 */
	public function userCanChangeSubscriptions(AcceptanceTester $I, \Codeception\Example $example)
	{
		$field = $example[0];
		$element = '#' . $field . '-wrapper input[name="' . $field . '"]';
		$targetValue = $example[1] ? 1 : 0;
		$initValue = $example[1] ? 0 : 1;
		$this->foodsaver = $I->createFoodsaver(null, [$field => $initValue]);

		$I->login($this->foodsaver['email']);
		$I->amOnPage('/?page=settings&sub=info');
		$I->waitForPageBody();
		$I->seeOptionIsSelected($element, $initValue);

		$I->selectOption($element, $targetValue);
		$I->click('Speichern');

		$I->waitForPageBody();
		$I->seeOptionIsSelected($element, $targetValue);
	}
}
