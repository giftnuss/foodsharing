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
		$this->responsible = $I->createAmbassador(null, ['bezirk_id' => $this->testBezirk['id']]);
		$I->addBezirkAdmin($this->testBezirk['id'], $this->responsible['id']);
		$this->otherBot = $I->createAmbassador(null, ['bezirk_id' => $this->testBezirk['id']]);
		$I->addBezirkAdmin($this->testBezirk['id'], $this->otherBot['id']);
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
		$I->login($this->responsible['email']);
		$I->amOnPage($I->fairTeilerRegionListUrl($this->testBezirk['id']));
		$I->waitForText('Fair-Teiler eintragen');
		$I->click('Fair-Teiler eintragen');
		$I->waitForText('In welchem Bezirk');
		$I->selectOption('#bezirk_id', $this->testBezirk['id']);
		$I->fillField('#name', 'The greatest fairsharepoint');
		$I->fillField('#desc', 'Blablabla if you come here be hungry!');
		$I->unlockAllInputFields();
		$I->fillField('#anschrift', 'Kantstraße 20');
		$I->fillField('#plz', '04808');
		$I->fillField('#ort', 'Wurzen');
		$I->fillFieldJs('#lat', '1.23');
		$I->fillFieldJs('#lon', '2.48');
		$I->click('Speichern');
		$I->waitForText('wurde erfolgreich eingetragen');
		$id = $I->grabFromDatabase('fs_fairteiler', 'id', ['name' => 'The greatest fairsharepoint']);
		$I->amOnPage($I->fairTeilerGetUrl($id));
		$I->waitForText('Kantstraße 20');
	}

	public function EditFairTeiler(AcceptanceTester $I)
	{
		$user = $I->createFoodsaver(null, ['bezirk_id' => $this->testBezirk['id']]);
		$I->login($this->responsible['email']);
		$I->amOnPage($I->fairTeilerEditUrl($this->fairTeiler['id']));
		$I->waitForText('Schreibe hier ein paar grundsätzliche Infos über den Fair-Teiler! Insbesondere wann er zugänglich/geöffnet ist');
		$I->fillField('#name', 'Der BESTE fairshare point!');
		$I->addInTagSelect($user['name'], '#bfoodsaver');
		$I->click('Speichern');
		$I->waitForText('erfolgreich bearbeitet');
		$I->see($user['name'] . ' ' . $user['nachname']);
	}

	/**
	 * @param AcceptanceTester $I
	 * @example["user", false]
	 * @example["responsible", true]
	 * @example["otherBot", false]
	 */
	public function MayEditFairTeiler(AcceptanceTester $I, \Codeception\Example $example)
	{
		$user = $this->{$example[0]};
		$I->login($user['email']);
		$I->amOnPage($I->fairTeilerEditUrl($this->fairTeiler['id']));
		if ($example[1]) {
			$I->waitForText('Schreibe hier ein paar');
		} else {
			$I->waitUrlEquals('/?page=login');
		}
	}
}
