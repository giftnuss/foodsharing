<?php

use Carbon\Carbon;
use Codeception\Util\Locator;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;

class StoreCest
{
	private $region;
	private $store;

	private $foodsaver;
	private $coordinator;

	public function _before(AcceptanceTester $I)
	{
		$this->region = $I->createRegion();
		$regionId = $this->region['id'];

		$this->store = $I->createStore($regionId, null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED]);

		$this->foodsaver = $I->createFoodsaver();
		$I->addBezirkMember($regionId, $this->foodsaver['id']);
		$I->addStoreTeam($this->store['id'], $this->foodsaver['id']);

		$this->coordinator = $I->createStoreCoordinator();
		$I->addBezirkMember($regionId, $this->coordinator['id']);
		$I->addStoreTeam($this->store['id'], $this->coordinator['id'], true);
	}

	public function watchFetchlist(AcceptanceTester $I)
	{
		$I->haveInDatabase('fs_abholer', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->foodsaver['id'],
			'date' => Carbon::now()
		]);

		$I->login($this->coordinator['email']);
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
