<?php

namespace Foodsharing\Modules\Settings;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Quiz\QuizGateway;
use DateTime;

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
					'date' => date(DateTime::ISO8601),
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
		return $this->db->update('fs_foodsaver',
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
		return $this->db->fetchByCriteria('fs_foodsaver',
			[
				'sleep_status',
				'sleep_from',
				'sleep_until',
				'sleep_msg'
			],
			['id' => $fsId]
		);
	}

	public function updateSleepMode(int $fsId, int $status, string $from, string $to, string $msg): int
	{
		return $this->db->update('fs_foodsaver',
			[
				'sleep_status' => $status,
				'sleep_from' => $from,
				'sleep_until' => $to,
				'sleep_msg' => strip_tags($msg)
			],
			['id' => $fsId]
		);
	}

	public function addNewMail(int $fsId, string $email, string $token): int
	{
		return $this->db->insertOrUpdate('fs_mailchange',
			[
				'foodsaver_id' => $fsId,
				'newmail' => strip_tags($email),
				'time' => $this->db->now(),
				'token' => strip_tags($token)
			]
		);
	}

	public function changeMail(int $fsId, string $email): int
	{
		$this->deleteMailChanges($fsId);

		return $this->db->update('fs_foodsaver',
			['email' => strip_tags($email)],
			['id' => $fsId]
		);
	}

	public function abortChangemail(int $fsId): int
	{
		return $this->deleteMailChanges($fsId);
	}

	private function deleteMailChanges(int $fsId): int
	{
		return $this->db->delete('fs_mailchange',
			['foodsaver_id' => $fsId]
		);
	}

	public function getMailChange(int $fsId): string
	{
		return $this->db->fetchValueByCriteria('fs_mailchange',
			'newmail',
			['foodsaver_id' => $fsId]
		);
	}

	public function getNewMail(int $fsId, string $token): string
	{
		return $this->db->fetchValueByCriteria('fs_mailchange',
			'newmail',
			[
				'token' => strip_tags($token),
				'foodsaver_id' => $fsId
			]
		);
	}

	public function updateFollowFoodSharePoint(int $fsId, int $foodSharePointId, int $infoType): int
	{
		return $this->db->update('fs_fairteiler_follower',
			['infotype' => $infoType],
			[
				'foodsaver_id' => $fsId,
				'fairteiler_id' => $foodSharePointId
			]
		);
	}

	public function unfollowFoodSharePoints(int $fsId, array $fspIds): int
	{
		return $this->db->delete('fs_fairteiler_follower',
			[
				'foodsaver_id' => $fsId,
				'fairteiler_id' => $fspIds
			]
		);
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

	public function updateFollowThread(int $fsId, int $themeId, int $infoType): int
	{
		return $this->db->update('fs_theme_follower',
			['infotype' => $infoType],
			[
				'foodsaver_id' => $fsId,
				'theme_id' => $themeId
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

	public function updateRole(int $fsId, int $newRoleId, int $currentRole): void
	{
		if ($newRoleId > $currentRole) {
			$this->db->update('fs_foodsaver',
				['rolle' => $newRoleId],
				['id' => $fsId]
			);
		}
	}

	final public function getQuizSession(int $sessionId, int $fsId): array
	{
		if ($session = $this->getQuizSessionForFs($sessionId, $fsId)) {
			$tmp = array();
			$session['try_count'] = $this->getQuizSessionTryCount($fsId, $session['quiz_id']);

			/*
			 * First of all sort the question array and get all questions_ids etc to calculate the result
			 */
			if (!empty($session['quiz_questions'])) {
				$session['quiz_questions'] = unserialize($session['quiz_questions']);

				foreach ($session['quiz_questions'] as $quizQuestion) {
					$tmp[$quizQuestion['id']] = $quizQuestion;
					$ttmp = array();
					if (isset($quizQuestion['answers'])) {
						foreach ($quizQuestion['answers'] as $answer) {
							$ttmp[$answer] = $answer;
						}
					}
					if (!empty($ttmp)) {
						$tmp[$quizQuestion['id']]['answers'] = $ttmp;
					}
				}
			}

			if (!empty($session['quiz_result'])) {
				$session['quiz_result'] = unserialize($session['quiz_result']);

				foreach ($session['quiz_result'] as $k => $quizResult) {
					$session['quiz_result'][$k]['user'] = $tmp[$quizResult['id']];

					foreach ($quizResult['answers'] as $k2 => $v2) {
						$session['quiz_result'][$k]['answers'][$k2]['right'] = 0;
						if ($v2['right'] == 1) {
							$session['quiz_result'][$k]['answers'][$k2]['right'] = 1;
						}
						if ($v2['right'] == 2) {
							$session['quiz_result'][$k]['answers'][$k2]['right'] = 2;
						}
						$session['quiz_result'][$k]['answers'][$k2]['user_say'] = false;
						if (isset($session['quiz_result'][$k]['user']['answers'][$v2['id']])) {
							$session['quiz_result'][$k]['answers'][$k2]['user_say'] = true;
						}
					}
					if (!isset($session['quiz_result'][$k]['user']['userduration'])) {
						$session['quiz_result'][$k]['userduration'] = $session['quiz_result'][$k]['user']['duration'];
					} else {
						$session['quiz_result'][$k]['userduration'] = $session['quiz_result'][$k]['user']['userduration'];
					}
					if (!isset($session['quiz_result'][$k]['user']['noco'])) {
						$session['quiz_result'][$k]['noco'] = false;
					} else {
						$session['quiz_result'][$k]['noco'] = $session['quiz_result'][$k]['user']['noco'];
					}
					unset($session['quiz_result'][$k]['user']);
				}

				if ($quiz = $this->getQuiz($session['quiz_id'])) {
					$session = array_merge($quiz, $session);
					unset($session['quiz_questions']);

					/*
					 * Add questions they're complete right answered
					 */
					$session['quiz_result'] = $this->addRightAnswers($tmp, $session['quiz_result']);

					return $session;
				}
			}
		}

		return [];
	}

	private function getQuizSessionForFs(int $sessionId, int $fsId): array
	{
		return $this->db->fetchByCriteria('fs_quiz_session',
			[
				'quiz_id',
				'status',
				'quiz_index',
				'quiz_questions',
				'quiz_result',
				'fp',
				'maxfp'
			],
			[
				'id' => $sessionId,
				'foodsaver_id' => $fsId
			]
		);
	}

	private function getQuizSessionTryCount(int $fsId, int $quizId): int
	{
		return $this->db->count('fs_quiz_session',
			[
				'foodsaver_id' => $fsId,
				'quiz_id' => $quizId
			]
		);
	}

	private function getQuiz(int $quizId): array
	{
		return $this->db->fetchByCriteria('quiz',
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

	public function storeApiToken(int $fsId, string $token): void
	{
		$this->db->insert('fs_apitoken',
			[
				'foodsaver_id' => $fsId,
				'token' => $token
			]
		);
	}
}
