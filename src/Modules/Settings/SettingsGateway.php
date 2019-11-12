<?php

namespace Foodsharing\Modules\Settings;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Quiz\QuizGateway;

class SettingsGateway extends BaseGateway
{
	/**
	 * @var QuizGateway
	 */
	private $quizGateway;

	/**
	 * SettingsGateway constructor.
	 *
	 * @param QuizGateway $quizGateway
	 */
	public function __construct(Database $db, QuizGateway $quizGateway)
	{
		parent::__construct($db);

		$this->quizGateway = $quizGateway;
	}

	public function logChangedSetting(int $fsId, array $old, array $new, array $logChangedKeys, int $changerId = null): void
	{
		if (!$changerId) {
			$changerId = $fsId;
		}
		/* the logic is not exactly matching the update mechanism but should be close enough to get all changes... */
		foreach ($logChangedKeys as $k) {
			if (array_key_exists($k, $new) && $new[$k] != $old[$k]) {
				$this->db->insert('fs_foodsaver_change_history', [
					'date' => date(\DateTime::ISO8601),
					'fs_id' => $fsId,
					'changer_id' => $changerId,
					'object_name' => $k,
					'old_value' => $old[$k],
					'new_value' => $new[$k]
				]);
			}
		}
	}

	public function saveInfoSettings(int $fsId, int $newsletter, int $infomail): int
	{
		return $this->db->update(
			'fs_foodsaver',
			[
				'newsletter' => $newsletter,
				'infomail_message' => $infomail
			],
			['id' => $fsId]
		);
	public function unsubscribeNewsletter(string $email)
	{
		$this->db->update('fs_foodsaver', ['newsletter' => 0], ['email' => $email]);
	}

	public function getSleepData(int $fsId): array
	{
		return $this->db->fetch('
			SELECT
				sleep_status,
				sleep_from,
				sleep_until,
				sleep_msg

			FROM
				fs_foodsaver

			WHERE
				id = :fsId
		', [':fsId' => $fsId]);
	}

	private function getQuiz(int $quizId): array
	{
		return $this->db->fetchByCriteria(
			'quiz',
			[
				'name',
				'desc'
			],
			['id' => $quizId]
		);
	}

	/*
	 * In the session are only the failed answers stored in so now we get all the right answers and fill out the array
	 */
	private function addRightAnswers(array $indexList, array $fullList): array
	{
		$out = array();

		$number = 0;

		foreach ($indexList as $id => $value) {
			++$number;
			if (!isset($fullList[$id])) {
				if ($question = $this->quizGateway->getQuestion($id)) {
					$answers = array();
					if ($qanswers = $this->quizGateway->getAnswers($id)) {
						foreach ($qanswers as $a) {
							$answers[$a['id']] = $a;
							$answers[$a['id']]['user_say'] = $a['right'];
						}
					}
					$out[$id] = array(
						'id' => $id,
						'text' => $question['text'],
						'duration' => $question['duration'],
						'wikilink' => $question['wikilink'],
						'fp' => $question['fp'],
						'answers' => $answers,
						'number' => $number,
						'percent' => 0,
						'userfp' => 0,
						'userduration' => 10,
						'noco' => 0
					);
				}
			} else {
				$out[$id] = $fullList[$id];
			}
		}

		return $out;
	}

	public function addNewMail(int $fsId, string $email, string $token): int
	{
		return $this->db->insertOrUpdate(
			'fs_mailchange',
			[
				'foodsaver_id' => $fsId,
				'newmail' => strip_tags($email),
				'time' => $this->db->now(),
				'token' => strip_tags($token)
			]
		);
	}

	public function abortChangemail(int $fsId): int
	{
		return $this->deleteMailChanges($fsId);
	}

	public function changeMail(int $fsId, string $email): int
	{
		$this->deleteMailChanges($fsId);

		return $this->db->update(
			'fs_foodsaver',
			['email' => strip_tags($email)],
			['id' => $fsId]
		);
	}

	private function deleteMailChanges(int $fsId): int
	{
		return $this->db->delete('fs_mailchange', ['foodsaver_id' => $fsId]);
	}

	public function getMailchange(int $fsId): string
	{
		return $this->db->fetchValue('
			SELECT `newmail`
			FROM fs_mailchange
			WHERE foodsaver_id = :fsId
		', [':fsId' => $fsId]);
	}

	public function getForumThreads(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT
				th.id,
				th.name,
				tf.infotype

			FROM
				`fs_theme_follower` tf
				LEFT JOIN `fs_theme` th
				ON tf.theme_id = th.id

			WHERE
				tf.foodsaver_id = :fsId
		', [':fsId' => $fsId]);
	}

	public function updateFollowFoodSharePoint(int $fsId, int $foodSharePointId, int $infoType): int
	{
		return $this->db->update(
			'fs_fairteiler_follower',
			['infotype' => $infoType],
			[
				'foodsaver_id' => $fsId,
				'fairteiler_id' => $foodSharePointId
			]
		);
	}

	public function updateFollowThread(int $fsId, int $themeId, int $infoType): int
	{
		return $this->db->update(
			'fs_theme_follower',
			['infotype' => $infoType],
			[
				'foodsaver_id' => $fsId,
				'theme_id' => $themeId
			]
		);
	}

	public function unfollowFoodSharePoints(int $fsId, array $fspIds): int
	{
		return $this->db->delete(
			'fs_fairteiler_follower',
			[
				'foodsaver_id' => $fsId,
				'fairteiler_id' => $fspIds
			]
		);
	}

	public function unfollowThreads(int $fsId, array $themeIds): int
	{
		return $this->db->delete(
			'fs_theme_follower',
			[
				'foodsaver_id' => $fsId,
				'theme_id' => $themeIds
			]
		);
	}

	public function getNewMail(int $fsId, string $token): string
	{
		return $this->db->fetchValue('
			SELECT newmail
			FROM fs_mailchange
			WHERE `token` = :token
				AND foodsaver_id = :fsId
		', [':fsId' => $fsId, ':token' => strip_tags($token)]);
	}

	public function updateRole(int $fsId, int $roleId, int $currentRole): void
	{
		if ($roleId > $currentRole) {
			$this->db->update(
				'fs_foodsaver',
				['rolle' => $roleId],
				['id' => $fsId]
			);
		}
	}

	public function updateSleepMode(int $fsId, int $status, string $from, string $to, string $msg): int
	{
		return $this->db->update(
			'fs_foodsaver',
			[
				'sleep_status' => $status,
				'sleep_from' => $from,
				'sleep_until' => $to,
				'sleep_msg' => strip_tags($msg)
			],
			['id' => $fsId]
		);
	}

	public function storeApiToken(int $fsId, string $token): void
	{
		$this->db->insert(
			'fs_apitoken',
			[
				'foodsaver_id' => $fsId,
				'token' => $token
			]
		);
	}
}
