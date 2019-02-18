<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Modules\Core\BaseGateway;

class QuizGateway extends BaseGateway
{
	public function countByQuizId($fs_id, $quiz_id)
	{
		return $this->db->count('fs_quiz_session', [
			'foodsaver_id' => $fs_id,
			'quiz_id' => $quiz_id,
			'status' => 1
		]);
	}

	public function setRole($fs_id, $quiz_rolle)
	{
		$this->db->update(
			'fs_foodsaver',
			['quiz_rolle' => $quiz_rolle],
			['id' => $fs_id]
		);
	}

	public function getExsistingSession(int $quizId, int $fsId)
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
				`quiz_id` = :quizId
				
			AND
				foodsaver_id = :fsId
				
			AND
				`status` = 0
		', [
			'quizId' => $quizId,
			'fsId' => $fsId
		]);
		if ($session) {
			$session['quiz_questions'] = unserialize($session['quiz_questions']);

			return $session;
		} else {
			return null;
		}
	}
}
