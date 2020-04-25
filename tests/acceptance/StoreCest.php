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

	public function watchFetchlist(AcceptanceTester $I)
	{
		$I->haveInDatabase('fs_abholer', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->foodsaver['id'],
			'date' => Carbon::now()
		]);

		$I->login($this->storeCoordinator['email']);
		$I->amOnPage($I->storeUrl($this->store['id']));
		$I->waitForText('Optionen');
		$I->click('Abholungshistorie');
		$I->waitForText('Zeitraum');
		$I->fillField('#daterange_from', '01.01.1970');
		$I->fillField('#daterange_to', Carbon::now()->toString());
		$I->click('#daterange_submit');

		$I->waitForText($this->foodsaver['name']);
		$I->see($this->foodsaver['name'] . ' ' . $this->foodsaver['nachname']);
	}
}
