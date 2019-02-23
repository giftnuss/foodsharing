<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

final class ProfileGateway extends BaseGateway
{
	private $fs_id;
	private $session;
	private $mem;

	public function __construct(Database $db, Mem $mem, Session $session)
	{
		parent::__construct($db);
		$this->mem = $mem;
		$this->session = $session;
	}

	public function setFsId(int $id): void
	{
		$this->fs_id = $id;
	}

	public function getData(int $fsId)
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
					fs.`photo_public`,
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
		if (($data = $this->db->fetch($stm, [':fs_id' => $this->fs_id])) === []
		) {
			return false;
		}

		$data['bouched'] = false;
		$data['bananen'] = false;

		$stm = 'SELECT 1 FROM `fs_rating` WHERE rater_id = :fsId AND foodsaver_id = :fs_id AND ratingtype = 2';

		try {
			if ($this->db->fetchValue($stm, [':fsId' => $fsId, ':fs_id' => $this->fs_id])) {
				$data['bouched'] = true;
			}
		} catch (\Exception $e) {
			// has to be caught until we can check whether a to be fetched value does really exist.
		}
		$data['online'] = $this->mem->userIsActive((int)$this->fs_id);

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
		';
		$data['bananen'] = $this->db->fetchAll($stm, [':fs_id' => $this->fs_id]);

		if (!$data['bananen']) {
			$data['bananen'] = array();
		}

		$this->db->update('fs_foodsaver', ['stat_bananacount' => count($data['bananen'])], ['id' => (int)$this->fs_id]);
		$data['stat_bananacount'] = count($data['bananen']);

		$data['botschafter'] = false;
		$data['foodsaver'] = false;
		$data['orga'] = false;

		if ($this->session->mayHandleReports()) {
			$data['violation_count'] = (int)$this->getViolationCount($this->fs_id);
			$data['note_count'] = (int)$this->getNotesCount($this->fs_id);
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
		if ($bot = $this->db->fetchAll($stm, [':fs_id' => $this->fs_id])
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
		if ($fs = $this->db->fetchAll($stm, [':fs_id' => $this->fs_id])
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
		if ($orga = $this->db->fetchAll($stm, [':fs_id' => $this->fs_id])
		) {
			$data['orga'] = $orga;
		}

		$data['pic'] = false;
		if (!empty($data['photo']) && file_exists('images/' . $data['photo'])) {
			$data['pic'] = array(
				'original' => 'images/' . $data['photo'],
				'medium' => 'images/130_q_' . $data['photo'],
				'mini' => 'images/50_q_' . $data['photo']
			);
		}

		return $data;
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

	private function getViolationCount(int $fsId): int
	{
		$stm = '
			SELECT
				COUNT(r.id)
			FROM
	            `fs_report` r
			WHERE
				r.foodsaver_id = :fs_id
		';

		return (int)$this->db->fetchValue($stm, [':fs_id' => $fsId]);
	}

	public function rate(int $fsId, int $rate, int $type = 1, string $message = '')
	{
		return $this->db->insert(
			'fs_rating',
			[
				'foodsaver_id' => $fsId,
				'rater_id' => $this->session->id(),
				'rating' => $rate,
				'ratingtype' => $type,
				'msg' => $message,
				'time' => $this->db->now(),
			]
		);
	}

	public function getRateMessage(int $fsId): array
	{
		return $this->db->fetchByCriteria(
			'fs_rating',
			'msg',
			['foodsaver_id' => $fsId, 'rater_id' => $this->session->id()]
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

		return ($ret === false) ? array() : $ret;
	}

	public function getCompanies(int $fsId): array
	{
		$stm = '
			SELECT 	b.id,
					b.name,
					bt.verantwortlich,
					bt.active

			FROM 	fs_betrieb_team bt,
					fs_betrieb b

			WHERE 	bt.betrieb_id = b.id
			AND
					bt.foodsaver_id = :fs_id
			ORDER BY b.name ASC
		';

		return $this->db->fetchAll($stm, [':fs_id' => $fsId]);
	}

	public function getCompaniesCount(int $fsId)
	{
		$stm = '
			SELECT 	count(b.id)

			FROM 	fs_betrieb_team bt,
					fs_betrieb b

			WHERE 	bt.betrieb_id = b.id
			AND
					bt.foodsaver_id = :fs_id
		';

		return $this->db->fetchValue($stm, [':fs_id' => $fsId]);
	}

	public function buddyStatus(int $fsId)
	{
		try {
			if (($status = $this->db->fetchValueByCriteria(
					'fs_buddy',
					'confirmed',
					['foodsaver_id' => $this->session->id(), 'buddy_id' => $fsId]
				)) !== []) {
				return $status;
			}
		} catch (\Exception $e) {
			// has to be caught until we can check whether a to be fetched value does really exist.
		}

		return -1;
	}
}
