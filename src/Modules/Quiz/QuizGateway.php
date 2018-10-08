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
}
