<?php
namespace Helper;
use DateTime;

class Foodsharing extends \Codeception\Module\Db
{

	/**
	* Insert a new foodsharer into the database.
	*
	* @param string pass to set as foodsharer password
	* @param array extra_params override params
	*
	* @return an array with all the foodsaver fields
	*/
	public function createFoodsharer($pass = null, $extra_params = [])
	{
		$email = sq().'@test.com';
		if(!isset($pass))
		{
			$pass = 'password';
		}
		$params = array_merge([
			'email' => $email,
			'bezirk_id' => 0,
			'name' => sq(),
			'nachname' => sq(),
			'verified' => 0,
			'rolle' => 0,
			'plz' => '10178',
			'stadt' => 'Berlin',
			'lat' => '52.5237395',
			'lon' => '13.3986951',
			'passwd' => $this->encryptMd5($email, $pass),
			'anmeldedatum' => $this->toDateTime(),
			'active' => 1,
		], $extra_params);
		$id = $this->haveInDatabase('fs_foodsaver', $params);
		$params['id'] = $id;
		return $params;
	}

	public function createQuizTry($fs_id, $level, $status)
	{
		$v = [
			'quiz_id' => $level,
			'status' => $status,
			'foodsaver_id' => $fs_id,
		];
		$this->haveInDatabase('fs_quiz_session', $v);
	}

	public function createFoodsaver($pass = null, $extra_params = [])
	{
		$params = array_merge([
			'verified' => 1,
			'rolle' => 1,
			'quiz_rolle' => 1,
			'anschrift' => sq(),
		], $extra_params);
		$params = $this->createFoodsharer($pass, $params);
		$this->createQuizTry($params['id'], 1, 1);
		return $params;
	}

	public function createStoreCoordinator($pass = null, $extra_params = [])
	{
		$params = array_merge([
			'rolle' => 2,
			'quiz_rolle' => 2,
		], $extra_params);
		$params = $this->createFoodsaver($pass, $params);
		$this->createQuizTry($params['id'], 2, 1);
		return $params;
	}

	public function createAmbassador($pass = null, $extra_params = [])
	{
		$params = array_merge([
			'rolle' => 3,
			'quiz_rolle' => 3,
		], $extra_params);
		$params = $this->createStoreCoordinator($pass, $params);
		$this->createQuizTry($params['id'], 3, 1);
		return $params;
	}

	public function createOrga($pass = null, $is_admin = false, $extra_params = [])
	{
		$params = array_merge([
			'rolle' => ($is_admin ? 5 : 4),
			'orgateam' => 1,
			'admin' => ($is_admin ? 1 : 0),
		]);

		$params = $this->createAmbassador($pass, $params);
		return $params;
	}

	/** creates a store in given bezirk.
	 * Does not care about handling anything related like conversations etc.
	 * @param $bezirk_id
	 * @param array $extra_params
	 * @return array
	 */
	public function createStore($bezirk_id, $extra_params = [])
	{
		$params = array_merge([
			'betrieb_status_id' => 1,
			'bezirk_id' => $bezirk_id,
			'name' => 'betrieb_'.sq(),
			'status' => 1, // same as betrieb_status_id
		], $extra_params);
		$params['id'] = $this->haveInDatabase('fs_betrieb', $params);
		return $params;
	}

	/** Adds a user or an array of users to the store team.
	 * If the user is not confirmed yet, the waiter status is ignored.
	 * Care: This method does not care about store conversations!
	 * Care: This method does not care about adding the user to the matching bezirk!
	 */
	public function addStoreTeam($store_id, $fs_id, $is_coordinator = false, $is_waiting = false, $is_confirmed = true)
	{
		if(is_array($fs_id))
		{
			foreach($fs_id as $fs)
			{
				$this->addStoreTeam($store_id, $fs, $is_coordinator, $is_waiting, $is_confirmed);
			}
		} else {
			$v = [
				'betrieb_id' => $store_id,
				'foodsaver_id' => $fs_id,
				'active' => $is_confirmed ? ($is_waiting ? 2 : 1) : 0,
				'verantwortlich' => $is_coordinator ? 1 : 0,
			];
			$this->haveInDatabase('fs_betrieb_team', $v);
		}
	}

	public function addBezirkMember($bezirk_id, $fs_id, $is_admin = false, $is_active = true)
	{
		if (is_array($fs_id)) {
			array_map(function ($x) use ($bezirk_id, $is_admin){
				$this->addBezirkMember($bezirk_id, $x, $is_admin);
			}, $fs_id);
		} else {
			$v = [
				'bezirk_id' => $bezirk_id,
				'foodsaver_id' => $fs_id,
				'active' => $is_active ? 1 : 0,

			];
			$this->haveInDatabase('fs_foodsaver_has_bezirk', $v);
			if ($is_admin) {
				$v = [
					'bezirk_id' => $bezirk_id,
					'foodsaver_id' => $fs_id,
				];
				$this->haveInDatabase('fs_botschafter', $v);
			}
		}
	}

	// copied from elsewhere....
	private function encryptMd5($email,$pass)
	{
		$email = strtolower($email);
		return md5($email.'-lz%&lk4-'.$pass);
	}

	private function toDateTime($date = null)
	{
		$dt = new DateTime($date);
		return $dt->format('Y-m-d H:i:s');
	}

}
