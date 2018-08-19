<?php

class ReportApiCest
{
	private $parentRegion;
	private $region;
	private $subRegion;
	private $ambassador;
	private $parentAmbassador;
	private $foodsaver;
	private $subRegionFoodsaver;
	private $foodsharer;

	public function _before(\ApiTester $I)
	{
		$this->parentRegion = $I->createRegion();
		$this->region = $I->createRegion(null, $this->parentRegion['id']);
		$this->subRegion = $I->createRegion(null, $this->region['id']);
		$this->ambassador = $I->createAmbassador(null, ['bezirk_id' => $this->region['id']]);
		$I->addBezirkAdmin($this->region['id'], $this->ambassador['id']);
		$this->parentAmbassador = $I->createAmbassador(null, ['bezirk_id' => $this->parentRegion['id']]);
		$I->addBezirkAdmin($this->parentRegion['id'], $this->parentAmbassador['id']);
		$this->foodsaver = $I->createFoodsaver(null, ['bezirk_id' => $this->region['id']]);
		$this->subRegionFoodsaver = $I->createFoodsaver(null, ['bezirk_id' => $this->subRegion['id']]);
		$this->foodsharer = $I->createFoodsharer();
	}

	public function seeReportAboutFoodsaverInRegion(\ApiTester $I)
	{
		$I->login($this->ambassador['email']);
		$I->addReport($this->foodsharer['id'], $this->foodsaver['id']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseContainsJson(['data' => ['fs_id' => $this->foodsaver['id'], 'rp_id' => $this->foodsharer['id']]]);
	}

	public function seeReportAboutFoodsaverInSubRegion(\ApiTester $I)
	{
		$I->login($this->ambassador['email']);
		$I->addReport($this->foodsharer['id'], $this->subRegionFoodsaver['id']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseContainsJson(['data' => ['fs_id' => $this->subRegionFoodsaver['id'], 'rp_id' => $this->foodsharer['id']]]);
	}

	public function dontSeeReportAboutSelf(\ApiTester $I)
	{
		$I->login($this->ambassador['email']);
		$I->addReport($this->foodsharer['id'], $this->ambassador['id']);
		$I->addReport($this->subRegionFoodsaver['id'], $this->ambassador['id']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->dontSeeResponseContainsJson(['data' => ['fs_id' => $this->ambassador['id']]]);
	}

	public function seeReportAboutFoodsharerReporterInRegion(\ApiTester $I)
	{
		$I->login($this->ambassador['email']);
		$I->addReport($this->foodsaver['id'], $this->foodsharer['id']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseContainsJson(['data' => ['fs_id' => $this->foodsharer['id'], 'rp_id' => $this->foodsaver['id']]]);
	}

	public function dontSeeReportAboutFoodsharerReporterNotInRegion(\ApiTester $I)
	{
		$I->login($this->ambassador['email']);
		$I->addReport($this->parentAmbassador['id'], $this->foodsharer['id']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->dontSeeResponseContainsJson(['data' => ['fs_id' => $this->foodsharer['id']]]);
	}

	public function parentAmbassadorSeesReportAboutAmbassador(\ApiTester $I)
	{
		$I->login($this->parentAmbassador['email']);
		$I->addReport($this->foodsharer['id'], $this->ambassador['id']);
		$I->sendGET($I->apiReportListForRegion($this->parentRegion['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseContainsJson(['data' => ['fs_id' => $this->ambassador['id'], 'rp_id' => $this->foodsharer['id']]]);
	}

	public function parentAmbassadorCannotFilterBySubregion(\ApiTester $I)
	{
		$I->login($this->parentAmbassador['email']);
		$I->sendGET($I->apiReportListForRegion($this->region['id']));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
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
}
