<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class QuizSessionGateway extends BaseGateway
{
  public function collectQuizStatus(int $quizId, int $fsId): array
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

  public function initQuizSession(int $fsId, int $quizId, string $questions, int $maxfp, int $questcount, int $easymode = 0): int
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
				'maxfp' => $maxfp,
				'quest_count' => $questcount,
				'easymode' => $easymode
			]
    );
	}
}
