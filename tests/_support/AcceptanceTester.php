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
		* Define custom actions here
		*/

		public function waitForPageBody()
		{
			return $this->waitForElement(['css' => 'body']);
		}

		/**
		* Insert a new foodsaver into the database.
		*
		* @param array params with at least email, name, bezirk_id, name, nachname
		* @param string pass to set as foodsaver password
		*
		* @return an array with all the foodsaver fields
		*/
		public function createFoodsaver($params, $pass)
		{
			$I = $this;
			$foodsaver_params = array_merge([
				'verified' => 0,
				'rolle' => 0,
				'plz' => '10178',
				'stadt' => 'Berlin',
				'lat' => '52.5237395',
				'lon' => '13.3986951',
				'passwd' => $this->encryptMd5($params['email'], $pass),
			], $params);
			$id = $I->haveInDatabase('fs_foodsaver', $foodsaver_params);
			$foodsaver_params['id'] = $id;
			return $foodsaver_params;
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