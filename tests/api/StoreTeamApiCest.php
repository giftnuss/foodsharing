<?php

namespace api;

use ApiTester;
use Codeception\Example;
use Codeception\Util\HttpCode as Http;
use Faker;
use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus as STATUS;

class StoreTeamApiCest
{
	private $store;
	private $user;
	private $manager;
	private $region;
	private $faker;

	private const API_STORES = 'api/stores/';

	public function _before(ApiTester $I)
	{
		$this->region = $I->createRegion();
		$this->store = $I->createStore($this->region['id']);
		$this->user = $I->createFoodsaver();
		$this->manager = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);
		$this->manager2 = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);
		$I->addStoreTeam($this->store['id'], $this->manager['id'], true);
		$this->faker = Faker\Factory::create('de_DE');
	}

	public function cannotManageStoreTeamUnlessResponsible(ApiTester $I): void
	{
		$I->sendPOST(self::API_STORES . $this->store['id'] . '/members/' . $this->user['id']);
		$I->seeResponseCodeIs(Http::UNAUTHORIZED);

		$I->login($this->manager2['email']);
		$I->sendDELETE(self::API_STORES . $this->store['id'] . '/members/' . $this->user['id']);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		$I->login($this->user['email']);
		$I->sendPATCH(self::API_STORES . $this->store['id'] . '/members/' . $this->user['id'] . '/standby');
		$I->seeResponseCodeIs(Http::FORBIDDEN);
	}

	public function canAddTeamMember(ApiTester $I): void
	{
		$I->login($this->manager['email']);
		$I->sendPOST(self::API_STORES . $this->store['id'] . '/members/' . $this->user['id']);
		$I->seeResponseCodeIs(Http::OK);

		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->user['id'],
			'verantwortlich' => 0,
			'active' => STATUS::MEMBER,
		]);
	}

	/**
	 * Test removing regular team members and standby team members.
	 *
	 * @example { "isStandby": false }
	 * @example { "isStandby": true }
	 */
	public function canRemoveTeamMember(ApiTester $I, Example $example): void
	{
		$I->addStoreTeam($this->store['id'], $this->user['id'], false, $example['isStandby']);

		$I->login($this->manager['email']);
		$I->sendDELETE(self::API_STORES . $this->store['id'] . '/members/' . $this->user['id']);
		$I->seeResponseCodeIs(Http::OK);

		$I->dontSeeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->user['id'],
		]);
	}

	/**
	 * @example { "isStandby": false }
	 * @example { "isStandby": true }
	 */
	public function cannotRemoveManager(ApiTester $I, Example $example): void
	{
		$I->addStoreTeam($this->store['id'], $this->manager2['id'], true, $example['isStandby']);

		$I->login($this->manager['email']);
		$I->sendDELETE(self::API_STORES . $this->store['id'] . '/members/' . $this->manager2['id']);
		$I->seeResponseCodeIs(Http::UNPROCESSABLE_ENTITY);

		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->manager2['id'],
			'verantwortlich' => 1,
		]);
	}

	/**
	 * @example { "isStandby": false }
	 * @example { "isStandby": true }
	 */
	public function canPromoteToManager(ApiTester $I, Example $example): void
	{
		$I->addStoreTeam($this->store['id'], $this->manager2['id'], false, $example['isStandby']);

		$I->login($this->manager['email']);
		$I->sendPATCH(self::API_STORES . $this->store['id'] . '/managers/' . $this->manager2['id']);
		$I->seeResponseCodeIs(Http::OK);

		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->manager2['id'],
			'verantwortlich' => 1,
			'active' => STATUS::MEMBER,
		]);
	}

	public function cannotPromoteToManager(ApiTester $I): void
	{
		$I->addStoreTeam($this->store['id'], $this->user['id'], false, false);

		$I->login($this->manager['email']);
		$I->sendPATCH(self::API_STORES . $this->store['id'] . '/managers/' . $this->user['id']);
		$I->seeResponseCodeIs(Http::UNPROCESSABLE_ENTITY);

		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->user['id'],
			'verantwortlich' => 0,
			'active' => STATUS::MEMBER,
		]);
	}

	public function canDemoteManager(ApiTester $I): void
	{
		$I->addStoreTeam($this->store['id'], $this->manager2['id'], true);

		$I->login($this->manager['email']);
		$I->sendDELETE(self::API_STORES . $this->store['id'] . '/managers/' . $this->manager2['id']);
		$I->seeResponseCodeIs(Http::OK);

		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->manager2['id'],
			'verantwortlich' => 0,
			'active' => STATUS::MEMBER,
		]);
	}

	public function cannotDemoteLastManager(ApiTester $I): void
	{
		$I->login($this->manager['email']);
		$I->sendDELETE(self::API_STORES . $this->store['id'] . '/managers/' . $this->manager['id']);
		$I->seeResponseCodeIs(Http::UNPROCESSABLE_ENTITY);

		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->manager['id'],
			'verantwortlich' => 1,
			'active' => STATUS::MEMBER,
		]);
	}

	public function canMoveTeamMemberToStandby(ApiTester $I): void
	{
		$I->addStoreTeam($this->store['id'], $this->user['id'], false, false);

		$I->login($this->manager['email']);
		$I->sendPATCH(self::API_STORES . $this->store['id'] . '/members/' . $this->user['id'] . '/standby');
		$I->seeResponseCodeIs(Http::OK);

		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->user['id'],
			'verantwortlich' => 0,
			'active' => STATUS::JUMPER,
		]);
	}

	public function canMoveStandbyMemberToTeam(ApiTester $I): void
	{
		$I->addStoreTeam($this->store['id'], $this->user['id'], false, true);

		$I->login($this->manager['email']);
		$I->sendDELETE(self::API_STORES . $this->store['id'] . '/members/' . $this->user['id'] . '/standby');
		$I->seeResponseCodeIs(Http::OK);

		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $this->user['id'],
			'verantwortlich' => 0,
			'active' => STATUS::MEMBER,
		]);
	}
}
