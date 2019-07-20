<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class QuizGateway extends BaseGateway
{
	private $bellGateway;
	private $foodsaverGateway;

	public function __construct(
		Database $db,
		BellGateway $bellGateway,
		FoodsaverGateway $foodsaverGateway
	) {
		parent::__construct($db);

		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
	}

	public function getQuizzes(): array
	{
		return $this->db->fetchAll('
			SELECT id, name
			FROM fs_quiz
			ORDER BY id
		');
	}

	public function addQuiz(string $name, string $desc, int $maxfp, int $questcount): int
	{
		return $this->db->insert('fs_quiz',
			[
				'name' => $name,
				'desc' => $desc,
				'maxfp' => $maxfp,
				'questcount' => $questcount
			]
		);
	}

	public function updateQuiz(int $id, string $name, string $desc, string $maxfp, string $questcount): int
	{
		return $this->db->update(
			'fs_quiz',
			[
				'name' => $name,
				'desc' => $desc,
				'maxfp' => $maxfp,
				'questcount' => $questcount
			],
			['id' => $id]
		);
	}

	public function getQuiz(int $id): array
	{
		return $this->db->fetchByCriteria(
			'fs_quiz',
			['id', 'name', 'desc', 'maxfp', 'questcount'],
			['id' => $id]
		);
	}

	public function getQuizName(int $quizId): string
	{
		return $this->db->fetchValueByCriteria(
			'fs_quiz',
			'name',
			['id' => $quizId]
		);
	}

	public function getQuizStatus(int $quizId, int $fsId): array
	{
		$out = array(
			'cleared' => 0,
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
		', ['fsId' => $fsId, 'quizId' => $quizId]);
		if ($res) {
			foreach ($res as $r) {
				++$out['times'];
				if ($r['time_ts'] > $out['last_try']) {
					$out['last_try'] = $r['time_ts'];
				}

				if ($r['status'] == SessionStatus::RUNNING) {
					++$out['running'];
				} elseif ($r['status'] == SessionStatus::PASSED) {
					++$out['cleared'];
				} elseif ($r['status'] == SessionStatus::FAILED) {
					++$out['failed'];
				}
			}
		}

		return $out;
	}

	public function initQuizSession($fsId, $quiz_id, $questions, $maxfp, $questcount, $easymode = 0)
	{
		$questions = serialize($questions);

		return $this->db->insert('fs_quiz_session',
			[
				'foodsaver_id' => $fsId,
				'quiz_id' => $quiz_id,
				'status' => SessionStatus::RUNNING,
				'quiz_index' => 0,
				'quiz_questions' => $questions,
				'time_start' => $this->db->now(),
				'fp' => 0,
				'maxfp' => $maxfp,
				'quest_count' => $questcount,
				'easymode' => $easymode
				]);
	}

	public function finishQuiz(int $session_id, string $questions, string $quiz_result, float $fp, int $maxfp): int
	{
		$quiz_result = serialize($quiz_result);
		$questions = serialize($questions);

		return $this->db->update(
			'fs_quiz_session',
			[
				'quiz_result' => $quiz_result,
				'quiz_questions' => $questions,
				'time_end' => $this->db->now(),
				'status' => ($fp <= $maxfp) ? SessionStatus::PASSED : SessionStatus::FAILED,
				'fp' => $fp,
				'maxfp' => $maxfp
			],
			['id' => $session_id]
		);
	}

	public function getSessions($quizId): array
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
			'quizId' => $quizId,
			'fsId' => $fsId,
			'status' => SessionStatus::RUNNING
		]);
		if ($session) {
			$session['quiz_questions'] = unserialize($session['quiz_questions']);

			return $session;
		}

		return [];
	}

	public function updateQuizSession(int $session_id, string $questions, int $quiz_index): int
	{
		$questions = serialize($questions);

		return $this->db->update(
			'fs_quiz_session',
			[
				'quiz_questions' => $questions,
				'quiz_index' => $quiz_index
			],
			['id' => $session_id]
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

	public function deleteSession(int $id): int
	{
		return $this->db->delete('fs_quiz_session', ['id' => $id], 1);
	}

	public function hasUserPassedQuiz(int $fsId, int $quizId): bool
	{
		$sessionCount = $this->db->count('fs_quiz_session', [
			'foodsaver_id' => $fsId,
			'quiz_id' => $quizId,
			'status' => SessionStatus::PASSED
		]);

		return $sessionCount > 0;
	}

	public function getSessionDetails(int $fsId): array
	{
		return $this->db->fetchByCriteria(
			'fs_foodsaver',
			['name', 'nachname', 'photo', 'rolle', 'geschlecht', 'sleep_status'],
			['id' => $fsId]
		);
	}

	public function setRole(int $fsId, int $quizRole): int
	{
		return $this->db->update(
			'fs_foodsaver',
			['quiz_rolle' => $quizRole],
			['id' => $fsId]
		);
	}

	public function addQuestion(int $quizId, string $text, int $fp, int $duration): int
	{
		$questionId = $this->db->insert(
			'fs_question',
			[
				'text' => $text,
				'duration' => $duration
			]
		);
		if ($questionId > 0) {
			$this->db->insert(
				'fs_question_has_quiz',
				[
					'question_id' => $questionId,
					'quiz_id' => $quizId,
					'fp' => $fp
				]
			);

			return $questionId;
		}

		return 0;
	}

	public function getQuestion(int $questionId): array
	{
		return $this->db->fetch('
			SELECT
					q.id,
					q.`text`,
					q.duration,
					q.wikilink,
					hq.fp,
					hq.quiz_id

				FROM
					fs_question q
					LEFT JOIN fs_question_has_quiz hq
					ON hq.question_id = q.id

				WHERE
					q.id = :questionId
		', ['questionId' => $questionId]);
	}

	public function getRandomQuestions(int $count, int $fp, int $quizId): array
	{
		return $this->db->fetchAll('
			SELECT
				q.id,
				q.duration,
				hq.fp

			FROM
				fs_question q
				LEFT JOIN fs_question_has_quiz hq
				ON hq.question_id = q.id

			WHERE
				hq.quiz_id = :quizId
			AND
				hq.fp = :fp

			ORDER BY
				RAND()

			LIMIT :count
		', ['quizId' => $quizId, 'fp' => $fp, 'count' => $count]);
	}

	public function getQuestionMetas(int $quizId): array
	{
		$questions = $this->db->fetchAll('
			SELECT
				q.id,
				q.duration,
				hq.fp

			FROM
				fs_question q
				LEFT JOIN fs_question_has_quiz hq
				ON hq.question_id = q.id

			WHERE
				hq.quiz_id = :quizId
		', ['quizId' => $quizId]);
		if ($questions) {
			$outmeta = array();
			$meta = $this->db->fetchAll('
				SELECT 	hq.fp, COUNT(q.id) AS `count`
				FROM fs_question q
					LEFT JOIN fs_question_has_quiz hq
					ON hq.question_id = q.id

				WHERE
					hq.quiz_id = :quizId

				GROUP BY
					hq.fp
			', ['quizId' => $quizId]);
			if ($meta) {
				foreach ($meta as $m) {
					if (!isset($outmeta[$m['fp']])) {
						$outmeta[$m['fp']] = $m['count'];
					}
				}
			}

			return array(
				'meta' => $outmeta,
				'question' => $questions
			);
		}

		return [];
	}

	public function updateQuestion(int $questionId, int $quizId, string $text, int $fp, int $duration, string $wikilink): int
	{
		$this->db->update(
			'fs_question',
			[
				'text' => $text,
				'duration' => $duration,
				'wikilink' => $wikilink
			],
			['id' => $questionId]
		);

		return $this->db->update(
			'fs_question_has_quiz',
			['fp' => $fp],
			[
				'question_id' => $questionId,
				'quiz_id' => $quizId
			]
		);
	}

	public function deleteQuestion(int $questionId): void
	{
		$this->db->delete('fs_answer', ['question_id' => $questionId]);
		$this->db->delete('fs_question', ['id' => $questionId]);
		$this->db->delete('fs_question_has_quiz', ['question_id' => $questionId]);
	}

	public function listQuestions(int $quizId): array
	{
		$questions = $this->getQuestions($quizId);
		if ($questions) {
			foreach ($questions as $key => $q) {
				$questions[$key]['answers'] = $this->getAnswers($q['id']);
				$questions[$key]['comment_count'] = $this->getCommentCount($q['id']);
			}

			return $questions;
		}

		return [];
	}

	private function getCommentCount(int $questionId): int
	{
		return $this->db->fetchValue('
			SELECT COUNT(question_id)
			FROM fs_question_has_wallpost
			WHERE question_id = :questionId
		', ['questionId' => $questionId]);
	}

	public function getRightQuestions(int $quizId): array
	{
		$out = array();
		$questions = $this->getQuestions($quizId);
		if ($questions) {
			foreach ($questions as $q) {
				$questionId = $q['id'];
				$out[$questionId] = $q;
				$answers = $this->getAnswers($questionId);
				if ($answers) {
					$out[$questionId]['answers'] = array();
					foreach ($answers as $a) {
						$out[$questionId]['answers'][$a['id']] = $a;
					}
				}
			}

			return $out;
		}

		return [];
	}

	private function getQuestions(int $quizId): array
	{
		return $this->db->fetchAll('
			SELECT
				q.id,
				q.text,
				q.duration,
				q.wikilink,
				hq.fp

			FROM
				fs_question q
				LEFT JOIN fs_question_has_quiz hq
				ON hq.question_id = q.id

			WHERE
				hq.quiz_id = :quizId
		', ['quizId' => $quizId]);
	}

	public function addAnswer(int $questionId, string $text, string $explanation, int $right): int
	{
		return $this->db->insert(
			'fs_answer',
			[
				'question_id' => $questionId,
				'text' => $text,
				'explanation' => $explanation,
				'right' => $right
			]
		);
	}

	public function getAnswer(int $answerId): array
	{
		return $this->db->fetchByCriteria(
			'fs_answer',
			['id', 'question_id', 'text', 'explanation', 'right'],
			['id' => $answerId]
		);
	}

	public function getAnswers(int $questionId): array
	{
		return $this->db->fetchAllByCriteria(
			'fs_answer',
			['id', 'text', 'explanation', 'right'],
			['question_id' => $questionId]
		);
	}

	public function updateAnswer(int $answerId, string $text, string $explanation, int $right): int
	{
		return $this->db->update(
			'fs_answer',
			[
				'text' => $text,
				'explanation' => $explanation,
				'right' => $right
			],
			['id' => $answerId]
		);
	}

	public function deleteAnswer(int $answerId): int
	{
		return $this->db->delete('fs_answer', ['id' => $answerId]);
	}

	public function addUserComment(int $questionId, int $fsId, string $comment): bool
	{
		$commentId = $this->db->insert(
			'fs_wallpost',
			[
				'foodsaver_id' => $fsId,
				'body' => $comment,
				'time' => $this->db->now()
			]
		);

		return $this->handleUserComment($questionId, $commentId, $comment);
	}

	private function handleUserComment(int $questionId, int $commentId, string $comment): bool
	{
		if ($commentId > 0) {
			if ($quizAMBs = $this->foodsaverGateway->getBotschafter(341)) {
				$this->bellGateway->addBell(
					$quizAMBs,
					'new_quiz_comment_title',
					'new_quiz_comment',
					'fas fa-question-circle',
					array('href' => '/?page=quiz&sub=wall&id=' . $questionId),
					array('comment' => $comment)
				);
			}
			$this->db->insert(
				'fs_question_has_wallpost',
				[
					'question_id' => $questionId,
					'wallpost_id' => $commentId,
					'usercomment' => 1
				]
			);

			return true;
		}

		return false;
	}
}
