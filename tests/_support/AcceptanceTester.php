<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
	use _generated\AcceptanceTesterActions;

	/**
	* Wait to see the body element
	*/
	public function waitForPageBody()
	{
		return $this->waitForElement(['css' => 'body']);
	}

	/**
	* Insert a new foodsaver into the database.
	*
	* @param string pass to set as foodsaver password
	* @param array extra_params override params
	*
	* @return an array with all the foodsaver fields
	*/
	public function createFoodsaver($pass, $extra_params = [])
	{
		$email = sq('email').'@test.com';
		$I = $this;
		$params = array_merge([
			'email' => $email,
			'bezirk_id' => 1, // Deutschland
			'name' => sq('name'),
			'nachname' => sq('nachname'),
			'verified' => 0,
			'rolle' => 0,
			'plz' => '10178',
			'stadt' => 'Berlin',
			'lat' => '52.5237395',
			'lon' => '13.3986951',
			'passwd' => $this->encryptMd5($email, $pass),
		], $extra_params);
		$id = $I->haveInDatabase('fs_foodsaver', $params);
		$params['id'] = $id;
		return $params;
	}

	public function login($email, $password)
	{
		$I = $this;
		$I->amOnPage('/');
		$I->fillField('email_adress', $email);
		$I->fillField('password', $password);
		$I->click('#loginbar input[type=submit]');
		$I->waitForPageBody();
		$I->see('Willkommen');
	}

	// copied from elsewhere....
	private function encryptMd5($email,$pass)
	{
		$email = strtolower($email);
		return md5($email.'-lz%&lk4-'.$pass);
	}

}