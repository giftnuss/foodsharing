<?php
/**
 * Created by IntelliJ IDEA.
 * User: matthias
 * Date: 16.02.18
 * Time: 10:06.
 */
class FairTeilerCest
{
	private $testBezirk;
	private $responsible;
	private $user;
	private $fairTeiler;

	public function _before(AcceptanceTester $I)
	{
		$this->testBezirk = $I->createRegion('MyFunnyBezirk');
		$this->user = $I->createFoodsharer(null, ['bezirk_id' => $this->testBezirk['id']]);
		$this->responsible = $I->createFoodsaver();
		$this->fairTeiler = $I->createFairteiler($this->responsible['id'], $this->testBezirk['id']);
	}

	/**
	 * @param AcceptanceTester $I
	 *
	 * */
	public function CanSeeFairTeilerInList(AcceptanceTester $I)
	{
		$I->amOnPage($I->fairTeilerRegionListUrl($this->testBezirk['id']));
		$I->waitForText($this->fairTeiler['name']);
		$I->click($this->fairTeiler['name']);
		$I->waitForText(explode("\n", $this->fairTeiler['anschrift'])[0]);
	}

	public function RedirectForGetPage(AcceptanceTester $I)
	{
		$I->amOnPage($I->fairTeilerGetUrlShort($this->fairTeiler['id']));
		$I->waitForText(explode("\n", $this->fairTeiler['anschrift'])[0]);
		$I->seeCurrentUrlEquals($I->fairTeilerGetUrl($this->fairTeiler['id']));
	}

	public function CreateFairTeiler(AcceptanceTester $I)
	{
		$I->login($this->user['email']);
		$I->amOnPage($I->fairTeilerRegionListUrl($this->testBezirk));
		$I->waitForText('Fair-Teiler eintragen');
		$I->click('Fair-Teiler eintragen');
		$I->waitForText('In welchem Bezirk');
		$I->
	}
}
