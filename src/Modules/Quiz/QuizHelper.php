<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Store\StoreGateway;

class QuizHelper
{
	private $quizGateway;
	private $storeGateway;
	private $foodsaverGateway;

	public function __construct(QuizGateway $quizGateway, StoreGateway $storeGateway, FoodsaverGateway $foodsaverGateway)
	{
		$this->quizGateway = $quizGateway;
		$this->storeGateway = $storeGateway;
		$this->foodsaverGateway = $foodsaverGateway;
	}

	public function refreshQuizData($fs_id, $fs_role)
	{
		$count_fs_quiz = $this->quizGateway->countByQuizId($fs_id, 1);
		$count_bib_quiz = $this->quizGateway->countByQuizId($fs_id, 2);
		$count_bot_quiz = $this->quizGateway->countByQuizId($fs_id, 3);

		$count_verantwortlich = $this->storeGateway->getStoreCountForBieb($fs_id);
		$count_botschafter = $this->foodsaverGateway->getBezirkCountForBotschafter($fs_id);

		$quiz_rolle = 0;
		if ($count_fs_quiz > 0) {
			$quiz_rolle = 1;
		}
		if ($count_bib_quiz > 0) {
			$quiz_rolle = 2;
		}
		if ($count_bot_quiz > 0) {
			$quiz_rolle = 3;
		}

		$this->quizGateway->setRole($fs_id, $quiz_rolle);

		$hastodo_id = 0;
		if (
			$fs_role == 1 && $count_fs_quiz == 0
		) {
			$hastodo_id = 1;
		} elseif (
			($fs_role > 1 || $count_verantwortlich > 0) && $count_bib_quiz === 0
		) {
			$hastodo_id = 2;
		} elseif (
			($fs_role > 2 || $count_botschafter > 0) && $count_bot_quiz === 0
		) {
			$hastodo_id = 3;
		}

		return $hastodo_id;
	}
}
