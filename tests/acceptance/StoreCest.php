<?php

use Carbon\Carbon;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;

class StoreCest
{
	private $region;
	private $store;

	private $foodsaver;
	private $storeCoordinator;

	public function _before(AcceptanceTester $I)
	{
		$this->region = $I->createRegion();
		$regionId = $this->region['id'];

		$this->store = $I->createStore($regionId, null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED]);

		$this->foodsaver = $I->createFoodsaver();
		$I->addRegionMember($regionId, $this->foodsaver['id']);
		$I->addStoreTeam($this->store['id'], $this->foodsaver['id']);

		$this->storeCoordinator = $I->createStoreCoordinator();
		$I->addRegionMember($regionId, $this->storeCoordinator['id']);
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], true);
	}

	public function willKeepApproxPickupTime(AcceptanceTester $I)
	{
		$I->login($this->storeCoordinator['email']);

		// Check original value
		$I->amOnPage('/?page=betrieb&a=edit&id=' . $this->store['id']);
		$I->see('Keine Angabe', '#public_time option[selected]');

		// Change option and save the page
		$I->selectOption('public_time', 'morgens');
		$I->click('Senden');

		// Check the page again to make sure our option was saved
		$I->amOnPage('/?page=betrieb&a=edit&id=' . $this->store['id']);
		$I->see('morgens', '#public_time option[selected]');
	}

	public function seePickupHistory(AcceptanceTester $I)
	{
		$I->haveInDatabase('fs_abholer', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->foodsaver['id'],
			'date' => Carbon::now()->subYears(3)->subHours(8),
		]);

		$I->login($this->storeCoordinator['email']);
		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->waitForText('Abholungshistorie');
		// expand UI (should be collapsed by default)
		$I->click('.pickup-history-title');
		$I->waitForText('Abholungen anzeigen');
		// select a date ~4 years in the past, to see if the calendar works
		$I->click('#datepicker-from');
		$I->click('button[title="Vorheriges Jahr"]');
		$I->click('button[title="Vorheriges Jahr"]');
		$I->click('button[title="Vorheriges Jahr"]');
		$I->click('button[title="Vorheriger Monat"]');
		$I->click('button[title="Vorheriges Jahr"]');
		$I->click('.b-calendar-grid-body > .row:first-child > .col:last-child');
		// submit search
		$I->click('.pickup-search-button > button');

		$I->waitForElement('.pickup-date', 5);
		$I->see($this->foodsaver['name'] . ' ' . $this->foodsaver['nachname']);
		$I->waitForText('vor etwa 3 Jahren');
	}
}
