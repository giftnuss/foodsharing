<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;

class QuizSessionGateway extends BaseGateway
{
	public function collectQuizStatus(int $quizId, int $fsId): array
	{
		$out = array(
			'passed' => 0,
			'running' => 0,
			'failed' => 0,
			'last_try' => 0,
			'times' => 0
		);

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

				if ($r['status'] == SessionStatus::RUNNING) {
					++$out['running'];
				} elseif ($r['status'] == SessionStatus::PASSED) {
					++$out['passed'];
				} elseif ($r['status'] == SessionStatus::FAILED) {
					++$out['failed'];
				}
			}
		}

		return $out;
	}

	public function initQuizSession(int $fsId, int $quizId, array $questions, int $maxFailurePoints, int $questionCount, int $easyMode = 0): int
	{
		$questions = serialize($questions);

		return $this->db->insert('fs_quiz_session',
			[
				'foodsaver_id' => $fsId,
				'quiz_id' => $quizId,
				'status' => SessionStatus::RUNNING,
				'quiz_index' => 0,
				'quiz_questions' => $questions,
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
		$quizResult = serialize($quizResult);
		$questions = serialize($questions);

		return $this->db->update(
			'fs_quiz_session',
			[
				'quiz_result' => $quizResult,
				'quiz_questions' => $questions,
				'time_end' => $this->db->now(),
				'status' => ($failurePoints <= $maxFailurePoints) ? SessionStatus::PASSED : SessionStatus::FAILED,
				'fp' => $failurePoints,
				'maxfp' => $maxFailurePoints
			],
			['id' => $sessionId]
		);
	}

	public function getSessions(int $quizId): array
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

	public function getUserSessions(int $fsId): array
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

	public function getRunningSession(int $quizId, int $fsId): array
	{
		$session = $this->db->fetch('
			SELECT
				id,
				quiz_index,
				quiz_questions,
				easymode

			FROM
				fs_quiz_session

			WHERE
				quiz_id = :quizId
			AND
				foodsaver_id = :fsId
			AND
				status = :status
		', [
			':quizId' => $quizId,
			':fsId' => $fsId,
			':status' => SessionStatus::RUNNING
		]);
		if ($session) {
			$session['quiz_questions'] = unserialize($session['quiz_questions']);

			return $session;
		}

		return [];
	}

	public function updateQuizSession(int $sessionId, array $questions, int $quizIndex): int
	{
		$questions = serialize($questions);

		return $this->db->update(
			'fs_quiz_session',
			[
				'quiz_questions' => $questions,
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
		$deletionLimit = 1;

		return $this->db->delete('fs_quiz_session', ['id' => $sessionId], $deletionLimit);
	}

	public function countSessions(int $fsId, int $quizId, int $sessionStatus): int
	{
		return $this->db->count('fs_quiz_session', [
			'foodsaver_id' => $fsId,
			'quiz_id' => $quizId,
			'status' => $sessionStatus
		]);
	}

	public function getLastTry(int $fsId, int $quizId): int
	{
		return $this->db->fetchValue('
      SELECT UNIX_TIMESTAMP(`time_start`) AS time_ts
      FROM fs_quiz_session
      WHERE foodsaver_id = :fsId
      AND quiz_id = :quizId
      ORDER BY time_ts DESC
    ', [':fsId' => $fsId, ':quizId' => $quizId]);
	}
}
