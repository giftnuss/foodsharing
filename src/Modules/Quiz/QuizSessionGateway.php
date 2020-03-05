<?php

namespace Foodsharing\Modules\Quiz;

use Carbon\Carbon;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Quiz\QuizStatus;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;

class QuizSessionGateway extends BaseGateway
{
	private $quizGateway;

	public function __construct(Database $db, QuizGateway $quizGateway)
	{
		parent::__construct($db);

		$this->quizGateway = $quizGateway;
	}

	public function initQuizSession(int $fsId, int $quizId, array $questions, int $maxFailurePoints, int $questionCount, int $easyMode = 0): int
	{
		return $this->db->insert(
			'fs_quiz_session',
			[
				'foodsaver_id' => $fsId,
				'quiz_id' => $quizId,
				'status' => SessionStatus::RUNNING,
				'quiz_index' => 0,
				'quiz_questions' => serialize($questions),
				'time_start' => $this->db->now(),
				'fp' => 0,
				'maxfp' => $maxFailurePoints,
				'quest_count' => $questionCount,
				'easymode' => $easyMode
			]
	  );
	}

	public function finishQuizSession(int $sessionId, array $questions, array $quizResult, float $failurePoints, int $maxFailurePoints): int
	{
		return $this->db->update(
			'fs_quiz_session',
			[
				'quiz_result' => serialize($quizResult),
				'quiz_questions' => serialize($questions),
				'time_end' => $this->db->now(),
				'status' => ($failurePoints <= $maxFailurePoints) ? SessionStatus::PASSED : SessionStatus::FAILED,
				'fp' => $failurePoints,
				'maxfp' => $maxFailurePoints
			],
			['id' => $sessionId]
		);
	}

	public function listSessions(int $quizId): array
	{
		return $this->db->fetchAll('
				SELECT
					s.id,
					MAX(s.time_start) AS time_start,
					MIN(s.`status`) AS min_status,
					MAX(s.`status`) AS max_status,
					MIN(s.`fp`) AS min_fp,
					MAX(s.`fp`) AS max_fp,
					UNIX_TIMESTAMP(MAX(s.time_start)) AS time_start_ts,
					CONCAT(fs.name," ",fs.nachname) AS fs_name,
					fs.photo AS fs_photo,
					fs.id AS fs_id,
					count(s.foodsaver_id) AS trycount

				FROM
					fs_quiz_session s
						LEFT JOIN fs_foodsaver fs
						ON s.foodsaver_id = fs.id

				WHERE
					s.quiz_id = :quizId

				GROUP BY
					s.foodsaver_id

				ORDER BY
					time_start DESC
			', [':quizId' => $quizId]);
	}

	public function listUserSessions(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT
				s.id,
				s.fp,
				s.status,
				s.time_start,
				UNIX_TIMESTAMP(s.time_start) AS time_start_ts,
				q.name AS quiz_name,
				q.id AS quiz_id

			FROM
				fs_quiz_session s
					LEFT JOIN fs_quiz q
					ON s.quiz_id = q.id

			WHERE
				s.foodsaver_id = :fsId

			ORDER BY
				q.id, s.time_start DESC
		', [':fsId' => $fsId]);
	}

	final public function getExtendedUserSession(int $sessionId, int $fsId): array
	{
		if ($session = $this->getUserSession($sessionId, $fsId)) {
			$questions = [];
			$session['try_count'] = $this->countSessions($fsId, $session['quiz_id']);

			if (!empty($session['quiz_questions'])) {
				$session['quiz_questions'] = unserialize($session['quiz_questions']);
				$questions = $this->collectQuestionsWithAnswers($session['quiz_questions']);
			}

			if (!empty($session['quiz_result'])) {
				$session['quiz_result'] = unserialize($session['quiz_result']);

				foreach ($session['quiz_result'] as $quizKey => $quizResult) {
					$user = $questions[$quizResult['id']];
					$session['quiz_result'][$quizKey]['user'] = $user;

					foreach ($quizResult['answers'] as $answerKey => $givenAnswer) {
						$session['quiz_result'][$quizKey]['answers'][$answerKey]['right'] = $givenAnswer['right'];

						$isAnswerGiven = isset($user['answers'][$givenAnswer['id']]);
						$session['quiz_result'][$quizKey]['answers'][$answerKey]['user_say'] = $isAnswerGiven;
					}

					$duration = isset($user['userduration']) ? $user['userduration'] : $user['duration'];
					$session['quiz_result'][$quizKey]['userduration'] = $duration;

					$noco = isset($user['noco']) ? $user['noco'] : false;
					$session['quiz_result'][$quizKey]['noco'] = $noco;

					unset($session['quiz_result'][$quizKey]['user']);
				}

				if ($quiz = $this->quizGateway->getQuiz($session['quiz_id'])) {
					$session = array_merge($quiz, $session);
					unset($session['quiz_questions']);

					$session['quiz_result'] = $this->addRightAnswers($questions, $session['quiz_result']);

					return $session;
				}
			}
		}

		return [];
	}

	private function getUserSession(int $sessionId, int $fsId): array
	{
		return $this->db->fetchByCriteria(
			'fs_quiz_session',
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

	private function collectQuestionsWithAnswers(array $quizQuestions): array
	{
		$out = [];

		foreach ($quizQuestions as $quizQuestion) {
			$out[$quizQuestion['id']] = $quizQuestion;
			$answers = [];
			if (isset($quizQuestion['answers'])) {
				foreach ($quizQuestion['answers'] as $answer) {
					$answers[$answer] = $answer;
				}
			}
			if (!empty($answers)) {
				$out[$quizQuestion['id']]['answers'] = $answers;
			}
		}

		return $out;
	}

	/*
	 * In the session are only the failed answers stored in so now we get all the right answers and fill out the array
	 */
	private function addRightAnswers(array $indexList, array $fullList): array
	{
		$out = [];

		$number = 0;

		foreach ($indexList as $id => $value) {
			++$number;
			if (!isset($fullList[$id])) {
				if ($question = $this->quizGateway->getQuestion($id)) {
					$answers = [];
					if ($qanswers = $this->quizGateway->getAnswers($id)) {
						foreach ($qanswers as $a) {
							$answers[$a['id']] = $a;
							$answers[$a['id']]['user_say'] = $a['right'];
						}
					}
					$out[$id] = [
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
					];
				}
			} else {
				$out[$id] = $fullList[$id];
			}
		}

		return $out;
	}

	/**
	 *	Determines a user's current quiz status.
	 *
	 *	@param int $quizId Quiz level/role
	 *	@param int $fsId Foodsaver ID
	 *
	 *	@return array indicates the status of type DBConstants\Quiz\QuizStatus ('status') and a possible waiting time in days ('wait')
	 */
	public function getQuizStatus(int $quizId, int $fsId): array
	{
		$quizSessionStatus = $this->collectQuizStatus($quizId, $fsId);
		$pauseEnd = Carbon::createFromTimestamp($quizSessionStatus['last_try'])->addDays(30);

		$result = ['status' => QuizStatus::DISQUALIFIED, 'wait' => 0];

		$now = Carbon::now();
		if ($quizSessionStatus['times'] == 0) {
			$result['status'] = QuizStatus::NEVER_TRIED;
		} elseif ($quizSessionStatus['running'] > 0) {
			$result['status'] = QuizStatus::RUNNING;
		} elseif ($quizSessionStatus['passed'] > 0) {
			$result['status'] = QuizStatus::PASSED;
		} elseif ($quizSessionStatus['failed'] < 3) {
			$result['status'] = QuizStatus::FAILED;
		} elseif ($quizSessionStatus['failed'] == 3 && $now->isBefore($pauseEnd)) {
			$result['status'] = QuizStatus::PAUSE;
			$result['wait'] = $now->diffInDays($pauseEnd);
		} elseif ($quizSessionStatus['failed'] == 3 && $now->greaterThanOrEqualTo($pauseEnd)) {
			$result['status'] = QuizStatus::PAUSE_ELAPSED;
		} elseif ($quizSessionStatus['failed'] == 4) {
			$result['status'] = QuizStatus::PAUSE_ELAPSED;
		}

		return $result;
	}

	public function collectQuizStatus(int $quizId, int $fsId): array
	{
		$out = [
			'passed' => 0,
			'running' => 0,
			'failed' => 0,
			'last_try' => 0,
			'times' => 0
		];

		$res = $this->db->fetchAll('
			SELECT foodsaver_id, `status`, UNIX_TIMESTAMP(`time_start`) AS time_ts
			FROM fs_quiz_session
			WHERE foodsaver_id = :fsId
			AND quiz_id = :quizId
		', [':fsId' => $fsId, ':quizId' => $quizId]);
		if ($res) {
			foreach ($res as $r) {
				++$out['times'];
				if ($r['time_ts'] > $out['last_try']) {
					$out['last_try'] = $r['time_ts'];
				}

				switch ($r['status']) {
					case SessionStatus::RUNNING:
						++$out['running'];
						break;
					case SessionStatus::PASSED:
						++$out['passed'];
						break;
					case SessionStatus::FAILED:
						++$out['failed'];
						break;
					default:
				}
			}
		}

		return $out;
	}

	public function getRunningSession(int $quizId, int $fsId): array
	{
		$session = $this->db->fetchByCriteria(
			'fs_quiz_session',
			[
				'id',
				'quiz_index',
				'quiz_questions',
				'easymode'
			],
			[
				'quiz_id' => $quizId,
				'foodsaver_id' => $fsId,
				'status' => SessionStatus::RUNNING
			]
		);
		if ($session) {
			$session['quiz_questions'] = unserialize($session['quiz_questions']);

			return $session;
		}

		return [];
	}

	public function updateQuizSession(int $sessionId, array $questions, int $quizIndex): int
	{
		return $this->db->update(
			'fs_quiz_session',
			[
				'quiz_questions' => serialize($questions),
				'quiz_index' => $quizIndex
			],
			['id' => $sessionId]
		);
	}

	public function abortSession(int $sid, int $fsId): int
	{
		return $this->db->update(
			'fs_quiz_session',
			['status' => SessionStatus::FAILED],
			[
				'id' => $sid,
				'foodsaver_id' => $fsId
			]
		);
	}

	public function deleteSession(int $sessionId): int
	{
		return $this->db->delete('fs_quiz_session', ['id' => $sessionId]);
	}

	public function hasPassedQuiz(int $fsId, int $quizId): bool
	{
		$passedCount = $this->countSessions($fsId, $quizId, SessionStatus::PASSED);

		return $passedCount > 0;
	}

	/**
	 * Returns the number of sessions matching the given foodsaver Id, quiz Id, and session status.
	 * If sessionStatus is null, all status types are counted.
	 */
	public function countSessions(int $fsId, int $quizId, int $sessionStatus = null): int
	{
		$criteria = [
			'foodsaver_id' => $fsId,
			'quiz_id' => $quizId,
		];
		if (!is_null($sessionStatus)) {
			$criteria['status'] = $sessionStatus;
		}

		return $this->db->count(
			'fs_quiz_session',
			$criteria
		);
	}

	public function getLastTry(int $fsId, int $quizId): int
	{
		return $this->db->fetchValue('
			SELECT UNIX_TIMESTAMP(`time_start`) AS time_ts
			FROM fs_quiz_session
			WHERE foodsaver_id = :fsId
			AND quiz_id = :quizId
			ORDER BY time_ts DESC
		  ', [':fsId' => $fsId, ':quizId' => $quizId]
		);
	}

	/**
	 * Adds seven missed quiz entries for specified user, effectively blocking the user from doing that quiz again.
	 *
	 * @param int $fsId FS that will be blocked
	 * @param int $quizId Quiz for which FS will be blocked
	 */
	public function blockUserForQuiz(int $fsId, int $quizId): void
	{
		for ($i = 1; $i <= 7; ++$i) {
			$this->db->insertIgnore(
				'fs_quiz_session',
				[
					'foodsaver_id' => $fsId,
					'quiz_id' => $quizId,
					'status' => SessionStatus::FAILED,
					'time_start' => $this->db->now()
				]
			);
		}
	}
}
