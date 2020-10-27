<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Lib\WebSocketConnection;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

final class ProfileGateway extends BaseGateway
{
	private WebSocketConnection $webSocketConnection;

	public function __construct(Database $db, WebSocketConnection $webSocketConnection)
	{
		parent::__construct($db);
		$this->webSocketConnection = $webSocketConnection;
	}

	/**
	 * @param int $fsId id of the foodsaver we want the info from
	 * @param int $viewerId id of foodsaver looking for info. Pass -1 to prevent loading profile information of the viewer.
	 * @param bool $mayHandleReports info such as nb. of violations is only retrieved if this is true
	 */
	public function getData(int $fsId, int $viewerId, bool $mayHandleReports): array
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
		if ($viewerId != -1) {
			$stm = 'SELECT 1 FROM `fs_rating` WHERE rater_id = :viewerId AND foodsaver_id = :fs_id';

			try {
				if ($this->db->fetchValue($stm, [':viewerId' => $viewerId, ':fs_id' => $fsId])) {
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

		$stm = '
			SELECT his.date,
			       his.changer_id,
			       concat(ch.name," " ,ch.nachname) as changer_full_name,
			       his.old_value as old_region,
			       bez.name as  old_region_name
			FROM `fs_foodsaver_change_history` his
				left outer join fs_foodsaver ch on his.changer_id  = ch.id
				left outer join fs_bezirk bez on his.old_value = bez.id
			where
				fs_id = :fs_id and
				object_name = \'bezirk_id\'
			order by date desc
			limit 1';
		if ($home_district_history = $this->db->fetch($stm, [':fs_id' => $fsId])) {
			$data['home_district_history'] = $home_district_history;
		}

		return $data;
	}

	public function isUserVerified(int $userId): bool
	{
		return boolval($this->db->fetchValueByCriteria('fs_foodsaver', 'verified', ['id' => $userId]));
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

	public function giveBanana(int $fsId, string $message = '', ?int $sessionId): int
	{
		if ($sessionId === null) {
			throw new \UnexpectedValueException('Must be logged in to give banana.');
		}

		return $this->db->insert(
			'fs_rating',
			[
				'foodsaver_id' => $fsId,
				'rater_id' => $sessionId,
				'msg' => $message,
				'time' => $this->db->now(),
			]
		);
	}

	/**
	 * Returns whether the user with the raterId has already given a banana with the user with userId.
	 */
	public function hasGivenBanana(?int $raterId, int $userId): bool
	{
		if ($raterId === null) {
			return false;
		}

		return $this->db->exists('fs_rating', ['foodsaver_id' => $userId, 'rater_id' => $raterId]);
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

			WHERE a.betrieb_id = b.id
			AND   a.foodsaver_id = :fs_id
			AND   a.`date` > NOW()

			ORDER BY a.`date`

			LIMIT :limit
		';

		return $this->db->fetchAll($stm, [':fs_id' => $fsId, ':limit' => $limit]);
	}

	public function getRecentPickups(int $fsId, \DateTime $from, \DateTime $to): array
	{
		$stm = '
			SELECT 	p1.id,
			        p1.date,
			        UNIX_TIMESTAMP(p1.date) AS date_ts,
			        p1.foodsaver_id as foodsaverId,
			        p1.betrieb_id AS storeId,
			        b.name AS storeTitle

			FROM      fs_abholer p1
			LEFT JOIN fs_abholer p2
			    ON    p1.betrieb_id = p2.betrieb_id
			    AND   p1.date = p2.date
			LEFT JOIN fs_betrieb b
			    ON    p1.betrieb_id = b.id

			WHERE p2.foodsaver_id = :fs_id
			  AND p2.date > :date_from
			  AND p2.date < :date_to

			ORDER BY p1.date DESC
		';

		return $this->db->fetchAll($stm, [
			':fs_id' => $fsId,
			':date_from' => $this->db->date($from),
			':date_to' => $this->db->date($to),
		]);
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

		return $ret;
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
