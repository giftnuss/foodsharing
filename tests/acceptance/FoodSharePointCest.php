<?php

class FoodSharePointCest
{
	private $testBezirk;
	private $responsible;
	private $otherBot;
	private $user;
	private $foodSharePoint;

	public function _before(AcceptanceTester $I)
	{
		$this->testBezirk = $I->createRegion('MyFunnyBezirk');
		$this->user = $I->createFoodsharer(null, ['bezirk_id' => $this->testBezirk['id']]);
		$this->responsible = $I->createAmbassador(null, ['bezirk_id' => $this->testBezirk['id']]);
		$I->addRegionAdmin($this->testBezirk['id'], $this->responsible['id']);
		$this->otherBot = $I->createAmbassador(null, ['bezirk_id' => $this->testBezirk['id']]);
		$I->addRegionAdmin($this->testBezirk['id'], $this->otherBot['id']);
		$this->foodSharePoint = $I->createFoodSharePoint($this->responsible['id'], $this->testBezirk['id']);
	}

	public function canSeeFoodSharePointInList(AcceptanceTester $I)
	{
		$I->amOnPage($I->foodSharePointRegionListUrl($this->testBezirk['id']));
		$I->waitForText($this->foodSharePoint['name']);
		$I->click($this->foodSharePoint['name']);
		$I->waitForText(explode("\n", $this->foodSharePoint['anschrift'])[0]);
	}

	public function redirectForGetPage(AcceptanceTester $I)
	{
		$I->amOnPage($I->foodSharePointGetUrlShort($this->foodSharePoint['id']));
		$I->waitForText(explode("\n", $this->foodSharePoint['anschrift'])[0]);
		$I->seeCurrentUrlEquals($I->foodSharePointGetUrl($this->foodSharePoint['id']));
	}

	public function createFoodSharePoint(AcceptanceTester $I)
	{
		$I->login($this->responsible['email']);
		$I->amOnPage($I->foodSharePointRegionListUrl($this->testBezirk['id']));
		$I->waitForText('Fair-Teiler eintragen', 10);
		$I->click('Fair-Teiler eintragen');
		$I->waitForText('In welchem Bezirk');
		$I->selectOption('#bezirk_id', $this->testBezirk['id']);
		$I->fillField('#name', 'The greatest fairsharepoint');
		$I->fillField('#desc', 'Blablabla if you come here be hungry!');
		$I->unlockAllInputFields();
		$I->fillField('#anschrift', 'Kantstrasse 20');
		$I->fillField('#plz', '04808');
		$I->fillField('#ort', 'Wurzen');
		$I->fillFieldJs('#lat', '1.23');
		$I->fillFieldJs('#lon', '2.48');
		$I->click('Speichern');
		$I->waitForText('wurde erfolgreich eingetragen');
		$id = $I->grabFromDatabase('fs_fairteiler', 'id', ['name' => 'The greatest fairsharepoint', 'bezirk_id' => $this->testBezirk['id']]);
		$I->amOnPage($I->foodSharePointGetUrl($id));
		$I->waitForText('Kantstrasse 20', 10);
	}

	public function editFoodSharePoint(AcceptanceTester $I)
	{
		$user = $I->createFoodsaver(null, ['bezirk_id' => $this->testBezirk['id']]);
		$I->login($this->responsible['email']);
		$I->amOnPage($I->foodSharePointEditUrl($this->foodSharePoint['id']));
		$I->waitForText('Schreibe hier ein paar grundsätzliche Infos über den Fair-Teiler! Insbesondere wann er zugänglich/geöffnet ist');
		$I->fillField('#name', 'Der BESTE fairshare point!');
		$I->addInTagSelect($user['name'], '#bfoodsaver');
		$I->click('Speichern');
		$I->waitForText('erfolgreich bearbeitet');
		$I->waitForText($user['name'] . ' ' . $user['nachname']);
	}

	/**
	 * @example["user", false]
	 * @example["responsible", true]
	 * @example["otherBot", true]
	 */
	public function mayEditFoodSharePoint(AcceptanceTester $I, \Codeception\Example $example)
	{
		$user = $this->{$example[0]};
		$I->login($user['email']);
		$I->amOnPage($I->foodSharePointEditUrl($this->foodSharePoint['id']));
		if ($example[1]) {
			$I->waitForText('Schreibe hier ein paar');
		} else {
			/* just see the fairteiler page if not enough permissions to edit */
			$I->waitForText('Beachte, dass Deine Beiträge');
		}
	}

	public function mayNotEditFoodSharePointWrongBid(AcceptanceTester $I)
	{
		$region = $I->createRegion('another funny region');
		$bot = $I->createAmbassador(null, ['bezirk_id' => $region['id']]);
		$I->addRegionAdmin($region['id'], $bot['id']);
		$I->login($bot['email']);
		$I->amOnPage($I->foodSharePointEditUrl($this->foodSharePoint['id']) . '&bid=' . $region['id']);
		/* does not get edit view although region admin of another region (regression) */
		$I->waitForText('Beachte, dass Deine Beiträge');
	}
}
