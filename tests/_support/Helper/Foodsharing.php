<?php
namespace Helper;

class Foodsharing extends \Codeception\Module\Db
{

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

	// copied from elsewhere....
	private function encryptMd5($email,$pass)
	{
		$email = strtolower($email);
		return md5($email.'-lz%&lk4-'.$pass);
	}

}