<?php

namespace Helper;
use DateTime;
use Faker;

class Foodsharing extends \Codeception\Module\Db
{
	public $faker;

	public function __construct($moduleContainer, $config = null)
	{
		parent::__construct($moduleContainer, $config);
		$this->faker = Faker\Factory::create();
	}

	public function clear ()
	{
		$this->driver->executeQuery('
			DELETE FROM fs_foodsaver;
			DELETE FROM fs_foodsaver_has_bezirk;
			DELETE FROM fs_foodsaver_has_conversation;
			DELETE FROM fs_conversation;
			DELETE FROM fs_betrieb_team;
			DELETE FROM fs_betrieb;
			DELETE FROM fs_abholer;
		', []);
	}
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
		if (!isset($pass)) {
			$pass = 'password';
		}
		$params = array_merge([
			'email' => $this->faker->email,
			'bezirk_id' => 0,
			'name' => $this->faker->firstName,
			'nachname' => $this->faker->lastName,
			'verified' => 0,
			'rolle' => 0,
			'plz' => '10178',
			'stadt' => 'Berlin',
			'lat' => '52.5237395',
			'lon' => '13.3986951',
			'anmeldedatum' => $this->toDateTime(),
			'active' => 1,
		], $extra_params);
		$params['passwd'] = $this->encryptMd5($params['email'], $pass);
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
			'anschrift' => $this->faker->address,
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
		], $extra_params);

		$params = $this->createAmbassador($pass, $params);

		return $params;
	}

	/** creates a store in given bezirk.
	 * Does not care about handling anything related like conversations etc.
	 *
	 * @param $bezirk_id
	 * @param array $extra_params
	 *
	 * @return array
	 */


	public function createStore($bezirk_id, $extra_params = [])
	{
		$params = array_merge([
			'betrieb_status_id' => 1,
			'status' => 1, // same as betrieb_status_id
			'added' => '2017-01-03',
			'plz' => '',
			'stadt' => '',
			'str' => '',
			'hsnr' => '',
			'lat' => '',
			'lon' => '',
			'name' => 'betrieb_' . $this->faker->name,
			'status_date' => '2017-01-03',
			'ansprechpartner' => '',
			'telefon' => '',
			'fax' => '',
			'email' => '',
			'begin' => '0000-00-00',
			'besonderheiten' => '',
			'public_info' => '',
			'public_time' => 0,
			'ueberzeugungsarbeit' => 0,
			'presse' => 0,
			'sticker' => 0,
			'abholmenge' => 0,
			'team_status' => 1,
			'prefetchtime' => 1209600,

			// relations
			'bezirk_id' => $bezirk_id,
			'team_conversation_id' => NULL,
			'springer_conversation_id' => NULL,
			'kette_id' => 0,
			'betrieb_kategorie_id' => 0,
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
		if (is_array($fs_id)) {
			foreach ($fs_id as $fs) {
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

	public function addCollector($user, $store, $date, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'betrieb_id' => $store,
			'date' => $date,
			'confirmed' => 1
		], $extra_params);
		$id = $this->haveInDatabase('fs_abholer', $params);
		$params['id'] = $id;

		return $params;
	}

	public function createWorkingGroup($name, $extra_params = [])
	{
		/* 392 is global working groups */
		$parentId = 392;
		if (array_key_exists('parent_id', $extra_params)) {
			$parentId = $extra_params[$parentId];
		}

		return $this->createRegion($name, $parentId, 7, $extra_params);
	}

	public function createRegion($name, $parentId = 741, $type = 8, $extra_params = [])
	{
		/* 741 is germany, so suitable for normal sub regions */
		$v = array_merge([
			'parent_id' => $parentId,
			'name' => $name,
			'type' => $type],
			$extra_params);
		$v['id'] = $this->haveInDatabase('fs_bezirk', $v);
		/* Add to closure table for hierarchies */
		$this->driver->executeQuery('INSERT INTO `fs_bezirk_closure`
		(ancestor_id, bezirk_id, depth)
		SELECT t.ancestor_id, ?, t.depth+1 FROM `fs_bezirk_closure` AS t WHERE t.bezirk_id = ?
		UNION ALL SELECT ?, ?, 0', array($v['id'], $parentId, $v['id'], $v['id']));

		return $v;
	}

	public function addBezirkMember($bezirk_id, $fs_id, $is_admin = false, $is_active = true)
	{
		if (is_array($fs_id)) {
			array_map(function ($x) use ($bezirk_id, $is_admin) {
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

	public function addForumTheme($bezirk_id, $fs_id, $title, $text, $date = null, $bot_theme = false)
	{
		$v = [
			'foodsaver_id' => $fs_id,
			'name' => $title,
			'time' => $this->toDateTime($date),
			'active' => '1',
		];
		$theme_id = $this->haveInDatabase('fs_theme', $v);
		$v = [
			'theme_id' => $theme_id,
			'bezirk_id' => $bezirk_id,
			'bot_theme' => ($bot_theme ? 1 : 0),
		];
		$this->haveInDatabase('fs_bezirk_has_theme', $v);
		$this->addForumThemePost($theme_id, $fs_id, $text, $date);

		return $theme_id;
	}

	public function addForumThemePost($theme_id, $fs_id, $text, $date = null)
	{
		$v = [
			'theme_id' => $theme_id,
			'foodsaver_id' => $fs_id,
			'body' => $text,
			'time' => $this->toDateTime($date),
		];
		$v['id'] = $this->haveInDatabase('fs_theme_post', $v);
		$this->updateForumThemeWithPost($theme_id, $v);
	}

	public function createConversation($extra_params = [])
	{
		$params = array_merge([
			'locked' => 1,
			'name' => 'betrieb_bla',
			'start' => NULL,
			'last' => NULL,
			'last_foodsaver_id' => NULL,
			'start_foodsaver_id' => NULL,
			'last_message_id' => NULL,
			'last_message' => '',
			'member' => '',
		], $extra_params);
		$id = $this->haveInDatabase('fs_conversation', $params);
		$params['id'] = $id;

		return $params;
	}

	public function addUserToConversation($user, $conversation, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'conversation_id' => $conversation,
			'unread' => 0,
		], $extra_params);

		$id = $this->haveInDatabase('fs_foodsaver_has_conversation', $params);
		$params['id'] = $id;

		return $params;
	}

	// =================================================================================================================
	// private methods
	// =================================================================================================================

	private function updateForumThemeWithPost($theme_id, $post)
	{
		$last_post_id = $this->grabFromDatabase('fs_theme', 'last_post_id', ['id' => $theme_id]);
		$last_post_date = new DateTime($this->grabFromDatabase('fs_theme_post', 'time', ['id' => $last_post_id]));
		$this_post_date = new DateTime($post['time']);
		if ($last_post_date >= $this_post_date) {
			$this->driver->executeQuery('UPDATE fs_theme SET last_post_id = ? WHERE id = ?', [$post['id'], $theme_id]);
		}
	}

	// copied from elsewhere....
	private function encryptMd5($email, $pass)
	{
		$email = strtolower($email);

		return md5($email . '-lz%&lk4-' . $pass);
	}

	private function toDateTime($date = null)
	{
		$dt = new DateTime($date);

		return $dt->format('Y-m-d H:i:s');
	}
}
