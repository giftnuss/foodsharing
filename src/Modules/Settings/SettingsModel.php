<?php

namespace Foodsharing\Modules\Settings;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Quiz\QuizGateway;

class SettingsModel extends Db
{
	/**
	 * @var QuizGateway
	 */
	private $quizGateway;

	/**
	 * SettingsModel constructor.
	 *
	 * @param QuizGateway $quizGateway
	 */
	public function __construct(QuizGateway $quizGateway)
	{
		$this->quizGateway = $quizGateway;

		parent::__construct();
	}

	public function updateFollowFairteiler($fid, $infotype)
	{
		return $this->update('
			UPDATE 		`fs_fairteiler_follower`
			SET 		`infotype` = ' . (int)$infotype . '
			WHERE 		`fairteiler_id` = ' . (int)$fid . '
			AND 		`foodsaver_id` = ' . (int)$this->session->id() . '
		');
	}

	public function updateFollowThread($themeId, $infotype)
	{
		return $this->update('
			UPDATE 		`fs_theme_follower`
			SET 		`infotype` = ' . (int)$infotype . '
			WHERE 		`theme_id` = ' . (int)$themeId . '
			AND 		`foodsaver_id` = ' . (int)$this->session->id() . '
		');
	}

	public function unfollowThread($unfollow)
	{
		return $this->del('
			DELETE FROM 	`fs_theme_follower`
			WHERE 	foodsaver_id = ' . (int)$this->session->id() . '
			AND 	theme_id IN(' . implode(',', $unfollow) . ')
		');
	}

	public function unfollowFairteiler($unfollow)
	{
		return $this->del('
			DELETE FROM 	`fs_fairteiler_follower`
			WHERE 	foodsaver_id = ' . (int)$this->session->id() . '
			AND 	fairteiler_id IN(' . implode(',', $unfollow) . ')
		');
	}

	public function getFsCount($regionId)
	{
		return (int)$this->qOne('
			SELECT
				COUNT(hb.foodsaver_id)

			FROM
				fs_foodsaver_has_bezirk hb

			WHERE
				hb.bezirk_id = ' . (int)$regionId . '

			AND
				hb.active = 1
		');
	}

	public function getNewMail($token)
	{
		return $this->qOne('SELECT newmail FROM fs_mailchange WHERE `token` = ' . $this->strval($token) . ' AND foodsaver_id = ' . (int)$this->session->id());
	}

	public function updateRole($role_id, $current_role)
	{
		if ($role_id > $current_role) {
			$this->update('UPDATE fs_foodsaver SET `rolle` = ' . (int)$role_id . ' WHERE id = ' . (int)$this->session->id());
		}
	}

	public function hasQuizCleared($quiz_id)
	{
		if ($res = $this->qOne('
				SELECT COUNT(foodsaver_id) AS `count`
				FROM fs_quiz_session
				WHERE foodsaver_id =' . (int)$this->session->id() . '
				AND quiz_id = ' . (int)$quiz_id . '
				AND `status` = 1
			')
		) {
			if ($res > 0) {
				return true;
			}
		}

		return false;
	}

	public function updateSleepMode($status, $from, $to, $msg)
	{
		return $this->update('
 			UPDATE
 				fs_foodsaver

 			SET
 				`sleep_status` = ' . (int)$status . ',
 				`sleep_from` = ' . $this->dateval($from) . ',
 				`sleep_until` = ' . $this->dateval($to) . ',
 				`sleep_msg` = ' . $this->strval($msg) . '

 			WHERE
 				id = ' . (int)$this->session->id() . '
 		');
	}
}
