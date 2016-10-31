<?php

class CompanyCest
{
	private function createStoreAndUsers(\ApiTester $I)
	{
		$this->store = $I->createStore(241);
		$this->storeCoordinator = $I->createStoreCoordinator('pw');
		$this->participatorA = $I->createFoodsaver('pw');
		$this->participatorB = $I->createFoodsaver('pw');
		$I->addStoreTeam($this->store['id'], $this->storeCoordinator['id'], True);
		$I->addStoreTeam($this->store['id'], $this->participatorA['id']);
		$I->addStoreTeam($this->store['id'], $this->participatorB['id']);

		$I->addBezirkMember($this->store['bezirk_id'], $this->storeCoordinator['id']);
		$I->addBezirkMember($this->store['bezirk_id'], $this->participatorA['id']);
		$I->addBezirkMember($this->store['bezirk_id'], $this->participatorB['id']);
	}

	public function CoordinatorCanSeeCompanyOnDashboard(\ApiTester $I) {
		$this->createStoreAndUsers($I);
		$I->login($this->storeCoordinator['email'], 'pw');
		$I->seeRegExp('~.*'.$this->store['name'].'</a>.*~i');
	}
}
