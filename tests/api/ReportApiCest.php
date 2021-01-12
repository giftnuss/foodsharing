<?php

use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;

class ReportApiCest
{
	private $parentRegion;
	private $region;
	private $reportGroup;
	private $reportGroupAdmin;
	private $arbitrationGroup;
	private $arbitrationGroupAdmin;
	private $subRegion;
	private $foodsaver;
	private $subRegionFoodsaver;
	private $foodsharer;

	public function _before(\ApiTester $I)
	{
		//Create regions
		$this->parentRegion = $I->createRegion();
		$this->region = $I->createRegion(null, ['parent_id' => $this->parentRegion['id']]);
		$this->subRegion = $I->createRegion(null, ['parent_id' => $this->region['id']]);

		// Create Workgroup and Workgroup Function for report
		$this->reportGroup = $I->createWorkingGroup('Meldungsbearbeitung', ['parent_id' => $this->region['id']]);
		$I->haveInDatabase('fs_region_function', ['region_id' => $this->reportGroup['id'], 'function_id' => WorkgroupFunction::REPORT, 'target_id' => $this->region['id']]);

		//create report admins and assign to report workgroup
		$this->reportGroupAdmin = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);
		$I->addRegionMember($this->reportGroup['id'], $this->reportGroupAdmin['id']);
		$I->addRegionAdmin($this->reportGroup['id'], $this->reportGroupAdmin['id']);

		// same for arbitration workgroup
		$this->arbitrationGroup = $I->createWorkingGroup('Schiedsstelle', ['parent_id' => $this->region['id']]);
		$I->haveInDatabase('fs_region_function', ['region_id' => $this->arbitrationGroup['id'], 'function_id' => WorkgroupFunction::ARBITRATION, 'target_id' => $this->region['id']]);
		$this->arbitrationGroupAdmin = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);
		$I->addRegionMember($this->arbitrationGroup['id'], $this->arbitrationGroupAdmin['id']);
		$I->addRegionAdmin($this->arbitrationGroup['id'], $this->arbitrationGroupAdmin['id']);


		$this->foodsaver = $I->createFoodsaver(null, ['bezirk_id' => $this->region['id']]);
		$this->subRegionFoodsaver = $I->createFoodsaver(null, ['bezirk_id' => $this->subRegion['id']]);
		$this->foodsharer = $I->createFoodsharer();
	}

	public function seeReportAboutFoodsaverInRegion(\ApiTester $I)
	{
		$I->login($this->reportGroupAdmin['email']);
		$I->addReport($this->foodsaver['id'], $this->foodsaver['id']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		codecept_debug($I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK));
		codecept_debug($I->seeResponseContainsJson(['data' => ['fs_id' => $this->foodsaver['id'], 'rp_id' => $this->foodsaver['id']]]));
	}

	public function cantSeeReportAboutFoodsaverInSubRegion(\ApiTester $I)
	{
		$I->login($this->reportGroupAdmin['email']);
		$I->addReport($this->foodsharer['id'], $this->subRegionFoodsaver['id']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->cantSeeResponseContainsJson(['data' => ['fs_id' => $this->subRegionFoodsaver['id'], 'rp_id' => $this->foodsharer['id']]]);
	}

	public function dontSeeReportAboutSelf(\ApiTester $I)
	{
		$I->login($this->reportGroupAdmin['email']);
		$I->addReport($this->foodsharer['id'], $this->reportGroupAdmin['id']);
		$I->addReport($this->subRegionFoodsaver['id'], $this->reportGroupAdmin['id']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->dontSeeResponseContainsJson(['data' => ['fs_id' => $this->reportGroupAdmin['id']]]);
	}

	public function dontSeeReportAboutFoodsharerReporterNotInRegion(\ApiTester $I)
	{
		$I->login($this->reportGroupAdmin['email']);
		$I->addReport($this->reportGroupAdmin['id'], $this->foodsharer['id']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->dontSeeResponseContainsJson(['data' => ['fs_id' => $this->foodsharer['id']]]);
	}

	public function ArbitrationAdminSeesReportAboutReportAdmin(\ApiTester $I)
	{
		$I->login($this->arbitrationGroupAdmin['email']);
		$I->addReport($this->foodsharer['id'], $this->reportGroupAdmin['id']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseContainsJson(['data' => ['fs_id' => $this->reportGroupAdmin['id'], 'rp_id' => $this->foodsharer['id']]]);
	}

	public function foodsaverCannotAccessReports(\ApiTester $I)
	{
		$I->login($this->foodsaver['email']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
	}

	public function foodsharerCannotAccessReports(\ApiTester $I)
	{
		$I->login($this->foodsharer['email']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
	}

	public function reportTeamCanAccessReports(\ApiTester $I)
	{
		$fs = $I->createFoodsaver();
		$reportTeamRegion = $I->createRegion('report team region', ['id' => Foodsharing\Modules\Core\DBConstants\Region\RegionIDs::EUROPE_REPORT_TEAM]);
		$I->addRegionMember(Foodsharing\Modules\Core\DBConstants\Region\RegionIDs::EUROPE_REPORT_TEAM, $fs['id']);
		$I->login($fs['email']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
	}
}
