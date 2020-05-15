<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Lib\WebSocketConnection;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

final class ProfileGateway extends BaseGateway
{
	private $webSocketConnection;

	public function __construct(Database $db, WebSocketConnection $webSocketConnection)
	{
		parent::__construct($db);
		$this->webSocketConnection = $webSocketConnection;
	}

	/**
	 * @param int $fsId id of the foodsaver we want the info from
	 * @param int $raterId id of foodsaver doing the "rating" (Banana) on a given foodsaver. Pass -1 to prevent loading of rater information
	 * @param bool $mayHandleReports info such as nb. of violations is only retrieved if this is true
	 */
	public function getData(int $fsId, int $raterId, bool $mayHandleReports): array
	{
		$stm = '
			SELECT 	fs.`id`,
					fs.`bezirk_id`,
					fs.`plz`,
					fs.`stadt`,
					fs.`lat`,
					fs.`lon`,
					fs.`email`,
					fs.`name`,
					fs.`nachname`,
					fs.`anschrift`,
					fs.`telefon`,
					fs.`handy`,
					fs.`geschlecht`,
					fs.`geb_datum`,
					fs.`anmeldedatum`,
					fs.`photo`,
					fs.`about_me_intern`,
					fs.`about_me_public`,
					fs.`orgateam`,
					fs.`data`,
					fs.`last_login`,
					fs.stat_fetchweight,
					fs.stat_fetchcount,
					fs.stat_ratecount,
					fs.stat_rating,
					fs.stat_postcount,
					fs.stat_buddycount,
					fs.stat_fetchrate,
					fs.stat_bananacount,
					fs.verified,
					fs.anmeldedatum,
					fs.sleep_status,
					fs.sleep_msg,
					fs.sleep_from,
					fs.sleep_until,
					fs.rolle,
					UNIX_TIMESTAMP(fs.sleep_from) AS sleep_from_ts,
					UNIX_TIMESTAMP(fs.sleep_until) AS sleep_until_ts,
					fs.mailbox_id,
					fs.deleted_at

			FROM 	fs_foodsaver fs

			WHERE 	fs.id = :fs_id
			';
		if (($data = $this->db->fetch($stm, [':fs_id' => $fsId])) === []
		) {
			return [];
		}
		$data['online'] = $this->webSocketConnection->isUserOnline($fsId);

		$data['bouched'] = false;
		$data['bananen'] = false;
		if ($raterId != -1) {
			$stm = 'SELECT 1 FROM `fs_rating` WHERE rater_id = :rater_id AND foodsaver_id = :fs_id AND ratingtype = 2';

			try {
				if ($this->db->fetchValue($stm, [':rater_id' => $raterId, ':fs_id' => $fsId])) {
					$data['bouched'] = true;
				}
			} catch (\Exception $e) {
				// has to be caught until we can check whether a to be fetched value does really exist.
			}
		}
		$this->loadBananas($data, $fsId);

		$data['botschafter'] = false;
		$data['foodsaver'] = false;
		$data['orga'] = false;

		if ($mayHandleReports) {
			$data['violation_count'] = $this->getViolationCount($fsId);
			$data['note_count'] = $this->getNotesCount($fsId);
		}

		$stm = '
			SELECT 	bz.`name`,
					bz.`id`
			FROM 	`fs_bezirk` bz,
					fs_botschafter b
			WHERE 	b.`bezirk_id` = bz.`id`
			AND 	b.foodsaver_id = :fs_id
			AND 	bz.type != 7
		';
		if ($bot = $this->db->fetchAll($stm, [':fs_id' => $fsId])
		) {
			$data['botschafter'] = $bot;
		}

		$stm = '
			SELECT 	bz.`name`,
					bz.`id`
			FROM 	`fs_bezirk` bz,
					fs_foodsaver_has_bezirk b
			WHERE 	b.`bezirk_id` = bz.`id`
			AND 	b.foodsaver_id = :fs_id
			AND 	bz.type != 7
		';
		if ($fs = $this->db->fetchAll($stm, [':fs_id' => $fsId])
		) {
			$data['foodsaver'] = $fs;
		}

		$stm = '
			SELECT 	bz.`name`,
					bz.`id`
			FROM 	`fs_bezirk` bz,
					fs_botschafter b
			WHERE 	b.`bezirk_id` = bz.`id`
			AND 	b.foodsaver_id = :fs_id
			AND 	bz.type = 7
		';
		if ($orga = $this->db->fetchAll($stm, [':fs_id' => $fsId])
		) {
			$data['orga'] = $orga;
		}

		$data['pic'] = false;
		if (!empty($data['photo']) && file_exists('images/' . $data['photo'])) {
			$data['pic'] = [
				'original' => 'images/' . $data['photo'],
				'medium' => 'images/130_q_' . $data['photo'],
				'mini' => 'images/50_q_' . $data['photo'],
			];
		}

		return $data;
	}

	/**
	 * @param array $data pass by reference with "&" --> otherwise the array will only be changed in scope of the method
	 * @param int $fsId the foodsaver id for which bananas should be loaded
	 */
	private function loadBananas(array &$data, int $fsId): void
	{
		$stm = '
					SELECT 	fs.id,
							fs.name,
							fs.photo,
							r.`msg`,
							r.`time`,
							UNIX_TIMESTAMP(r.`time`) AS time_ts
					FROM 	`fs_foodsaver` fs,
							 `fs_rating` r
					WHERE 	r.rater_id = fs.id
					AND 	r.foodsaver_id = :fs_id
					AND 	r.ratingtype = 2
					ORDER BY time DESC
			';
		$data['bananen'] = $this->db->fetchAll($stm, [':fs_id' => $fsId]);
		$bananaCountNew = count($data['bananen']);

		if ($data['stat_bananacount'] != $bananaCountNew) {
			$this->db->update('fs_foodsaver', ['stat_bananacount' => $bananaCountNew], ['id' => $fsId]);
			$data['stat_bananacount'] = $bananaCountNew;
		}

		if (!$data['bananen']) {
			$data['bananen'] = [];
		}
	}

	private function getViolationCount(int $fsId): int
	{
		return (int)$this->db->count('fs_report', ['foodsaver_id' => $fsId]);
	}

	private function getNotesCount(int $fsId): int
	{
		$stm = '
			SELECT
				COUNT(wallpost_id)
			FROM
	           	`fs_usernotes_has_wallpost`
			WHERE
				usernotes_id = :fs_id
		';

		return (int)$this->db->fetchValue($stm, [':fs_id' => $fsId]);
	}

	public function rate(int $fsId, int $rate, int $type = 1, string $message = '', int $sessionId): int
	{
		return $this->db->insert(
			'fs_rating',
			[
				'foodsaver_id' => $fsId,
				'rater_id' => $sessionId,
				'rating' => $rate,
				'ratingtype' => $type,
				'msg' => $message,
				'time' => $this->db->now(),
			]
		);
	}

	public function getRateMessage(int $fsId, int $sessionId)
	{
		return $this->db->fetchValueByCriteria(
			'fs_rating',
			'msg',
			['foodsaver_id' => $fsId, 'rater_id' => $sessionId]
		);
	}

	public function getNextDates(int $fsId, int $limit = 10): array
	{
		$stm = '
			SELECT 	a.`date`,
					UNIX_TIMESTAMP(a.`date`) AS date_ts,
					b.name AS betrieb_name,
					b.id AS betrieb_id,
					b.bezirk_id AS bezirk_id,
					confirmed AS confirmed
			FROM   `fs_abholer` a,
			       `fs_betrieb` b

			WHERE a.betrieb_id =b.id
			AND   a.foodsaver_id = :fs_id
			AND   a.`date` > NOW()

			ORDER BY a.`date`

			LIMIT :limit
		';

		return $this->db->fetchAll($stm, [':fs_id' => $fsId, ':limit' => $limit]);
	}

	public function getPassHistory(int $fsId): array
	{
		$stm = '
			SELECT
			  pg.foodsaver_id,
			  pg.date,
			  UNIX_TIMESTAMP(pg.date) AS date_ts,
			  pg.bot_id,
			  fs.nachname,
			  fs.name
			FROM
			  fs_pass_gen pg
			LEFT JOIN
			  fs_foodsaver fs
			ON
			  pg.bot_id = fs.id
			WHERE
			  pg.foodsaver_id = :fs_id
			ORDER BY
			  pg.date
			DESC
		';

		return $this->db->fetchAll($stm, [':fs_id' => $fsId]);
	}

	public function getVerifyHistory(int $fsId): array
	{
		$stm = '
			SELECT
			  vh.fs_id,
			  vh.date,
			  UNIX_TIMESTAMP(vh.date) AS date_ts,
			  vh.change_status,
			  vh.bot_id,
			  fs.nachname,
			  fs.name
			FROM
			  fs_verify_history vh
			LEFT JOIN
			  fs_foodsaver fs
			ON
			  vh.bot_id = fs.id
			WHERE
			  vh.fs_id = :fs_id
			ORDER BY
			  vh.date
			DESC
		';
		$ret = $this->db->fetchAll($stm, [':fs_id' => $fsId]);

		return ($ret === false) ? [] : $ret;
	}

	public function listStoresOfFoodsaver(int $fsId): array
	{
		$stm = '
			SELECT 	b.id,
					b.name,
					bt.verantwortlich,
					bt.active
			FROM 	fs_betrieb_team bt,
					fs_betrieb b
			WHERE 	bt.betrieb_id = b.id
			AND		bt.foodsaver_id = :fs_id
			ORDER BY b.name
		';

		return $this->db->fetchAll($stm, [':fs_id' => $fsId]);
	}

	public function buddyStatus(int $fsId, int $sessionId): int
	{
		try {
			if (($status = $this->db->fetchValueByCriteria(
					'fs_buddy',
					'confirmed',
					['foodsaver_id' => $sessionId, 'buddy_id' => $fsId]
				)) !== []) {
				return $status;
			}
		} catch (\Exception $e) {
			// has to be caught until we can check whether a to be fetched value does really exist.
		}

		return -1;
	}
}
