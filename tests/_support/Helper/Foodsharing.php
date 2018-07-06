<?php

namespace Helper;

use DateTime;
use Faker;

class Foodsharing extends \Codeception\Module\Db
{
	public $faker;
	private $email_counter = 1;

	public function __construct($moduleContainer, $config = null)
	{
		parent::__construct($moduleContainer, $config);
		$this->faker = Faker\Factory::create('de_DE');
	}

	public function clear()
	{
		$this->driver->executeQuery('
			DELETE FROM fs_foodsaver;
			DELETE FROM fs_foodsaver_has_bezirk;
			DELETE FROM fs_foodsaver_has_conversation;
			DELETE FROM fs_conversation;
			DELETE FROM fs_msg;
			DELETE FROM fs_betrieb_team;
			DELETE FROM fs_betrieb;
			DELETE FROM fs_abholer;
			DELETE FROM fs_abholzeiten;
			DELETE FROM fs_botschafter;
			DELETE FROM fs_theme_post;
			DELETE FROM fs_bezirk_has_theme;
			DELETE FROM fs_theme;
			DELETE FROM fs_betrieb_notiz;
			DELETE FROM fs_fairteiler;
			DELETE FROM fs_fairteiler_follower;
			DELETE FROM fs_fairteiler_has_wallpost;
			DELETE FROM fs_wallpost;
			DELETE FROM fs_basket;
			DELETE FROM fs_basket_has_wallpost;
		', []);
	}

	public function clearTable($table)
	{
		$this->driver->deleteQueryByCriteria($table, []);
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
			'email' => ($this->email_counter++) . '.' . $this->faker->email,
			'bezirk_id' => 0,
			'name' => $this->faker->firstName,
			'nachname' => $this->faker->lastName,
			'verified' => 0,
			'rolle' => 0,
			'plz' => $this->faker->postcode,
			'stadt' => $this->faker->city,
			'lat' => $this->faker->latitude,
			'lon' => $this->faker->longitude,
			'anmeldedatum' => $this->faker->dateTimeBetween('-5 years', '-5 days'),
			'geb_datum' => $this->faker->dateTimeBetween('-80 years', '-18 years'),
			'last_login' => $this->faker->dateTimeBetween('-1 years', '-1 hours'),
			'anschrift' => $this->faker->streetName,
			'handy' => $this->faker->phoneNumber,
			'photo_public' => 1,
			'active' => 1,
			'privacy_policy_accepted_date' => '2018-05-24 10:24:53',
			'privacy_notice_accepted_date' => '2018-05-24 18:25:28',
			'token' => uniqid()
		], $extra_params);
		$params['passwd'] = $this->encryptMd5($params['email'], $pass);
		$params['geb_datum'] = $this->toDateTime($params['geb_datum']);
		$params['last_login'] = $this->toDateTime($params['last_login']);
		$params['anmeldedatum'] = $this->toDateTime($params['anmeldedatum']);
		$id = $this->haveInDatabase('fs_foodsaver', $params);
		if ($params['bezirk_id']) {
			$this->addBezirkMember($params['bezirk_id'], $id);
		}
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

	public function createStore($bezirk_id, $team_conversation = null, $springer_conversation = null, $extra_params = [])
	{
		$params = array_merge([
			'betrieb_status_id' => $this->faker->numberBetween(0, 6),
			'status' => 1,
			'added' => $this->faker->dateTime(),
			'plz' => $this->faker->postcode(),
			'stadt' => $this->faker->city(),
			'str' => $this->faker->streetAddress(),
			'hsnr' => $this->faker->numberBetween(0, 1000),
			'lat' => $this->faker->latitude(),
			'lon' => $this->faker->longitude(),
			'name' => 'betrieb_' . $this->faker->company(),
			'status_date' => $this->faker->dateTime(),
			'ansprechpartner' => $this->faker->name(),
			'telefon' => $this->faker->phoneNumber(),
			'fax' => $this->faker->phoneNumber(),
			'email' => $this->faker->email(),
			'begin' => $this->faker->date('Y-m-d'),
			'besonderheiten' => '',
			'public_info' => '',
			'public_time' => 0,
			'ueberzeugungsarbeit' => 0,
			'presse' => 0,
			'sticker' => 0,
			'abholmenge' => $this->faker->numberBetween(0, 70),
			'team_status' => 1,
			'prefetchtime' => 1209600,

			// relations
			'bezirk_id' => $bezirk_id,
			'team_conversation_id' => $team_conversation,
			'springer_conversation_id' => $springer_conversation,
			'kette_id' => 0,
			'betrieb_kategorie_id' => 0,
		], $extra_params);
		$params['status_date'] = $this->toDateTime($params['status_date']);
		$params['added'] = $this->toDateTime($params['added']);

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

	public function addCollector($user, $store, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'betrieb_id' => $store,
			'date' => $this->faker->dateTime(),
			'confirmed' => 1
		], $extra_params);
		$params['date'] = $this->toDateTime($params['date']);

		$id = $this->haveInDatabase('fs_abholer', $params);
		$params['id'] = $id;

		return $params;
	}

	public function addStoreNotiz($user, $store, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'betrieb_id' => $store,
			'milestone' => 0,
			'text' => $this->faker->realText(100),
			'zeit' => $this->faker->dateTime(),
			'last' => 0, // should be 1 for newest entry, can't do that here though
			], $extra_params);
		$params['zeit'] = $this->toDateTime($params['zeit']);

		$id = $this->haveInDatabase('fs_betrieb_notiz', $params);
		$params['id'] = $id;

		return $params;
	}

	public function addRecurringPickup($store, $extra_params = [])
	{
		$hours = $this->faker->numberBetween(0, 23);
		$minutes = $this->faker->randomElement($array = array('00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'));

		$params = array_merge([
			'betrieb_id' => $store,
			'dow' => $this->faker->numberBetween(0, 6),
			'time' => sprintf('%02d:%s:00', $hours, $minutes),
			'fetcher' => $this->faker->numberBetween(1, 8),
		], $extra_params);

		$id = $this->haveInDatabase('fs_abholzeiten', $params);
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

		$extra_params = array_merge([
			'teaser' => 'an autogenerated working group without a description',
		], $extra_params);

		return $this->createRegion($name, $parentId, 7, $extra_params);
	}

	public function createRegion($name = null, $parentId = 741, $type = 9, $extra_params = [])
	{
		if ($name == null) {
			$name = $this->faker->lastName() . '-region';
		}
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

	public function addBezirkAdmin($bezirk_id, $fs_id)
	{
		$v = [
			'bezirk_id' => $bezirk_id,
			'foodsaver_id' => $fs_id,
		];
		$this->haveInDatabase('fs_botschafter', $v);
	}

	public function addBezirkMember($bezirk_id, $fs_id, $is_active = true)
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
		}
	}

	public function addForumTheme($bezirk_id, $fs_id, $bot_theme = false, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $fs_id,
			'name' => $this->faker->sentence(),
			'time' => $this->faker->dateTime(),
			'active' => '1',
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);

		$theme_id = $this->haveInDatabase('fs_theme', $params);

		$this->haveInDatabase('fs_bezirk_has_theme', [
			'theme_id' => $theme_id,
			'bezirk_id' => $bezirk_id,
			'bot_theme' => ($bot_theme ? 1 : 0),
		]);

		$post_params = [
			'body' => $this->faker->realText(500),
			'time' => $params['time'],
		];
		$this->addForumThemePost($theme_id, $fs_id, $post_params);

		$params['post'] = $post_params;
		$params['id'] = $theme_id;

		return $params;
	}

	public function addForumThemePost($theme_id, $fs_id, $extra_params = [])
	{
		$params = array_merge([
			'theme_id' => $theme_id,
			'foodsaver_id' => $fs_id,
			'body' => $this->faker->realText(200),
			'time' => $this->faker->dateTime(),
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);

		$params['id'] = $this->haveInDatabase('fs_theme_post', $params);

		$this->updateForumThemeWithPost($theme_id, $params);

		return $params;
	}

	public function createConversation($users, $extra_params = [])
	{
		$params = array_merge([
			'locked' => 1,
			'name' => null,
			'start' => $this->faker->dateTime(),
			'last' => $this->faker->dateTime(),
			'last_foodsaver_id' => $users[0],
			'start_foodsaver_id' => $users[0],
			'last_message_id' => null,
			'last_message' => '',
			'member' => '',
		], $extra_params);
		$params['start'] = $this->toDateTime($params['start']);
		$params['last'] = $this->toDateTime($params['last']);
		$id = $this->haveInDatabase('fs_conversation', $params);

		foreach ($users as $user) {
			$this->addUserToConversation($user, $id);
		}

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

	public function addConversationMessage($user, $conversation, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'conversation_id' => $conversation,
			'body' => $this->faker->realText(100),
			'time' => $this->faker->dateTime()
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);

		$id = $this->haveInDatabase('fs_msg', $params);

		$this->updateInDatabase('fs_conversation', [
			'last_message' => $params['body'],
			'last_message_id' => $id,
			'last_foodsaver_id' => $user,
			'last' => $params['time']
		], ['id' => $conversation]);

		$params['id'] = $id;

		return $params;
	}

	public function createFairteiler($user, $bezirk, $extra_params = [])
	{
		$params = array_merge([
			'bezirk_id' => $bezirk,
			'name' => $this->faker->city(),
			'desc' => $this->faker->text(200),
			'status' => 1,
			'anschrift' => $this->faker->address(),
			'plz' => $this->faker->postcode(),
			'ort' => $this->faker->city(),
			'lat' => $this->faker->latitude(),
			'lon' => $this->faker->longitude(),
			'add_date' => $this->faker->dateTime(),
			'add_foodsaver' => $user,
		], $extra_params);
		$params['add_date'] = $this->toDateTime($params['add_date']);

		$id = $this->haveInDatabase('fs_fairteiler', $params);

		$this->addFairteilerAdmin($user, $id);

		$params['id'] = $id;

		return $params;
	}

	public function addFairteilerFollower($user, $fairteiler, $extra_params = [])
	{
		$params = array_merge([
			'fairteiler_id' => $fairteiler,
			'foodsaver_id' => $user,
			'type' => 1,
			'infotype' => 1,
		], $extra_params);
		$this->haveInDatabase('fs_fairteiler_follower', $params);

		return $params;
	}

	public function addFairteilerAdmin($user, $fairteiler, $extra_params = [])
	{
		return $this->addFairteilerFollower($user, $fairteiler, array_merge($extra_params, ['type' => 2]));
	}

	public function addFairteilerPost($user, $fairteiler, $extra_params = [])
	{
		$post = $this->createWallpost($user, $extra_params);
		$this->haveInDatabase('fs_fairteiler_has_wallpost', [
			'fairteiler_id' => $fairteiler,
			'wallpost_id' => $post['id'],
		]);

		return $post;
	}

	public function createWallpost($user, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'body' => $this->faker->realText(200),
			'time' => $this->faker->dateTime(),
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);

		$id = $this->haveInDatabase('fs_wallpost', $params);

		$params['id'] = $id;

		return $params;
	}

	public function createFoodbasket($user, $bezirk = 241, $extra_params = [])
	{
		$params = array_merge([
			'foodsaver_id' => $user,
			'status' => 1,
			'time' => $this->faker->dateTime($max = 'now'),
			'until' => $this->faker->dateTimeBetween('now', '+14 days'),
			'fetchtime' => null,
			'description' => $this->faker->realText(200),
			'picture' => null,
			'tel' => $this->faker->phoneNumber(),
			'handy' => $this->faker->phoneNumber(),
			'contact_type' => 1,
			'location_type' => 0,
			'weight' => $this->faker->numberBetween(1, 100),
			'lat' => $this->faker->latitude,
			'lon' => $this->faker->longitude,
			'bezirk_id' => $bezirk,
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);
		$params['until'] = $this->toDateTime($params['until']);

		$id = $this->haveInDatabase('fs_basket', $params);

		$params['id'] = $id;

		return $params;
	}

	public function addFoodbasketWallpost($user, $foodbasket, $extra_params = [])
	{
		$post = $this->createWallpost($user, $extra_params);
		$this->haveInDatabase('fs_basket_has_wallpost', [
			'basket_id' => $foodbasket,
			'wallpost_id' => $post['id'],
		]);

		return $post;
	}

	public function addBells($users, $extra_params = [])
	{
		$params = array_merge([
			'name' => 'title',
			'body' => $this->faker->text(50),
			'vars' => '',
			'attr' => '',
			'icon' => '',
			'identifier' => '',
			'time' => $this->faker->dateTime($max = 'now'),
			'closeable' => 1
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);

		$bell_id = $this->haveInDatabase('fs_bell', $params);

		foreach ($users as $user) {
			$this->haveInDatabase('fs_foodsaver_has_bell', [
				'foodsaver_id' => $user['id'],
				'bell_id' => $bell_id,
				'seen' => 0
			]);
		}
	}

	public function addBlogPost($authorId, $regionId, $extra_params = [])
	{
		$params = array_merge([
			'bezirk_id' => $regionId,
			'foodsaver_id' => $authorId,
			'name' => $this->faker->text(40),
			'body' => $this->faker->text(),
			'teaser' => $this->faker->text(50),
			'time' => $this->faker->dateTime($max = 'now'),
			'active' => 1,
			'picture' => ''
		], $extra_params);
		$params['time'] = $this->toDateTime($params['time']);
		$params['id'] = $this->haveInDatabase('fs_blog_entry', $params);

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
		if ($date === null) {
			return null;
		}
		if ($date instanceof DateTime) {
			$dt = $date;
		} else {
			$dt = new DateTime($date);
		}

		return $dt->format('Y-m-d H:i:s');
	}
}
