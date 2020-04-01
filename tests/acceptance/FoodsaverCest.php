<?php

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;

class FoodsaverCest
{
	private $region;
	private $foodsharer;
	private $orga;

	public function _before(AcceptanceTester $I)
	{
		$this->region = $I->createRegion();
		$regionId = $this->region['id'];
		$this->foodsharer = $I->createFoodsharer();
		$I->addRegionMember($regionId, $this->foodsharer['id']);
		$this->orga = $I->createOrga();
		$I->addRegionAdmin($regionId, $this->orga['id']);
	}

	public function downgradeFoodsharerPermanently(AcceptanceTester $I)
	{
		$fsId = $this->foodsharer['id'];

		$I->login($this->orga['email']);
		$I->amOnPage('/?page=foodsaver&a=edit&id=' . $fsId);
		$I->selectOption('Benutzerrolle', 'Foodsaver/in');
		$I->click('Senden');

		$I->amOnPage('/?page=foodsaver&a=edit&id=' . $fsId);
		$I->selectOption('Benutzerrolle', 'Foodsharer/in');
		$I->click('Senden');

		$I->dontSeeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $fsId]);
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $fsId]);
		$I->dontSeeInDatabase('fs_botschafter', ['foodsaver_id' => $fsId]);
		$I->dontSeeInDatabase('fs_betrieb_team', ['foodsaver_id' => $fsId]);
		$I->dontSeeInDatabase('fs_abholer', ['foodsaver_id' => $fsId]);
		$I->dontSeeInDatabase('fs_foodsaver_has_conversation', ['foodsaver_id' => $fsId]);
		$I->seeNumRecords(7, 'fs_quiz_session', ['foodsaver_id' => $fsId, 'quiz_id' => Role::FOODSAVER, 'status' => SessionStatus::FAILED]);
		$I->seeInDatabase('fs_foodsaver', ['rolle' => Role::FOODSHARER, 'quiz_rolle' => Role::FOODSHARER]);
	}
}
