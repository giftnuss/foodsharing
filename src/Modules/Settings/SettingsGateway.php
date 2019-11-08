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

	public function logChangedSetting($fsId, $old, $new, $logChangedKeys, $changerId = null)
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
		return $this->db->fetch('
			SELECT
				`quiz_id`,
				`status`,
				`quiz_index`,
				`quiz_questions`,
				`quiz_result`,
				`fp`,
				`maxfp`

			FROM
				fs_quiz_session

			WHERE
				`id` = :sessionId
			AND
				`foodsaver_id` = :fsId
		', [':sessionId' => $sessionId, ':fsId' => $fsId]);
	}

	private function getQuizSessionTryCount(int $fsId, int $quizId): string
	{
		return $this->db->fetchValue('
			SELECT
				COUNT(quiz_id)

			FROM
				fs_quiz_session

			WHERE
				foodsaver_id = :fsId
			AND
				`quiz_id` = :quizId
		', [':fsId' => $fsId, ':quizId' => $quizId]);
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

	public function getFoodSharePoint(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT
				ft.id,
				ft.name,
				ff.infotype,
				ff.`type`

			FROM
				`fs_fairteiler_follower` ff,
				`fs_fairteiler` ft

			WHERE
				ff.fairteiler_id = ft.id
			AND
				ff.foodsaver_id = :fsId
		', [':fsId' => $fsId]);
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
		return $this->db->delele('fs_mailchange', ['foodsaver_id' => $fsId]);
	}
}
