<?php

use Foodsharing\Modules\Core\DBConstants\Info\InfoType;

class SettingsCest
{
	private $region;
	private $foodSharePoint;

	private $fspAdmin;
	private $foodsaver;

	final public function _before(AcceptanceTester $I): void
	{
		$this->foodsaver = $I->createFoodsaver();
		$this->fspAdmin = $I->createFoodsaver();
		$this->region = $I->createRegion();
		$this->foodSharePoint = $I->createFoodSharePoint($this->fspAdmin['id'], $this->region['id']);
	}

	/**
	 * @example["newsletter", false]
	 * @example["newsletter", true]
	 * @example["infomail_message", false]
	 * @example["infomail_message", true]
	 */
	final public function userCanChangeGeneralSubscriptions(AcceptanceTester $I, Codeception\Example $example): void
	{
		$field = $example[0];
		$selector = $this->createSelector($field);
		$targetValue = (string)($example[1] ? 1 : 0);
		$initValue = (string)($example[1] ? 0 : 1);
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

	final public function userCanFollowAFoodsharepoint(AcceptanceTester $I): void
	{
		$newFsp = $I->createFoodSharePoint($this->fspAdmin['id'], $this->region['id']);

		$I->login($this->foodsaver['email']);
		$I->amOnPage($I->foodSharePointGetUrl($newFsp['id']));
		$I->waitForPageBody();
		$I->click('Diesem Fairteiler folgen');
		$I->waitForText($newFsp['name'] . ' folgen');
		$I->click('Speichern');
		$I->waitForPageBody();

		$I->see('Fairteiler nicht mehr folgen');
		$I->amOnPage('/?page=settings&sub=info');
		$I->waitForPageBody();
		$I->see('Updates vom Fairteiler "' . $newFsp['name'] . '"');
	}

	final public function userCanUnfollowAFoodsharepoint(AcceptanceTester $I): void
	{
		$I->addFoodSharePointFollower($this->foodsaver['id'], $this->foodSharePoint['id']);

		$I->login($this->foodsaver['email']);
		$I->amOnPage($I->foodSharePointGetUrl($this->foodSharePoint['id']));
		$I->waitForPageBody();
		$I->click('Fairteiler nicht mehr folgen');
		$I->waitForPageBody();

		$I->see('Diesem Fairteiler folgen');
	}

	/**
	 * @example[0, "NONE"]
	 * @example[1, "EMAIL"]
	 * @example[2, "BELL"]
	 */
	final public function userCanChangeFoodsharepointSubscriptions(AcceptanceTester $I, Codeception\Example $example): void
	{
		$targetValue = (string)$example[0];
		$selector = $this->createSelector('fairteiler_' . $this->foodSharePoint['id']);
		$I->addFoodSharePointFollower($this->foodsaver['id'], $this->foodSharePoint['id']);

		$I->login($this->foodsaver['email']);
		$I->amOnPage('/?page=settings&sub=info');
		$I->waitForPageBody();
		$I->seeOptionIsSelected($selector, (string)InfoType::EMAIL);
		$I->selectOption($selector, $targetValue);
		$I->click('Speichern');

		$I->waitForPageBody();
		if ($targetValue === (string)InfoType::NONE) {
			$I->dontSeeElement($selector);
		} else {
			$I->seeOptionIsSelected($selector, $targetValue);
		}
	}

	final public function managerCanNotChangeFoodsharepointSubscriptions(AcceptanceTester $I): void
	{
		$selector = $this->createSelector('fairteiler_' . $this->foodSharePoint['id']);

		$I->login($this->fspAdmin['email']);
		$I->amOnPage('/?page=settings&sub=info');
		$I->waitForPageBody();
		$I->seeOptionIsSelected($selector, (string)InfoType::EMAIL);
		$I->selectOption($selector, (string)InfoType::BELL);
		$I->click('Speichern');

		$I->waitForPageBody();
		$I->seeOptionIsSelected($selector, (string)InfoType::EMAIL);
	}

	final public function canEditInternalSelfDescription(AcceptanceTester $I): void
	{
		$newSelfDesc = 'This is a new self description!';
		$I->login($this->foodsaver['email']);
		$I->amOnPage('/?page=settings&sub=general');
		$I->waitForPageBody();
		$I->fillField('#about_me_intern', $newSelfDesc);
		$I->click('Speichern');
		$I->waitForPageBody();

		$I->amOnPage('/profile/' . $this->foodsaver['id']);
		$I->waitForPageBody();
		$I->see($newSelfDesc);
	}

	private function createSelector(string $field): string
	{
		return '#' . $field . '-wrapper input[name="' . $field . '"]';
	}
}
