<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Modules\Core\DBConstants\Quiz\RoleType;
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
		$passed_fs_quiz = $this->quizGateway->hasUserPassedQuiz($fs_id, RoleType::FOODSAVER);
		$passed_bib_quiz = $this->quizGateway->hasUserPassedQuiz($fs_id, RoleType::STORE_COORDINATOR);
		$passed_bot_quiz = $this->quizGateway->hasUserPassedQuiz($fs_id, RoleType::AMBASSADOR);

		$count_verantwortlich = $this->storeGateway->getStoreCountForBieb($fs_id);
		$count_botschafter = $this->foodsaverGateway->getBezirkCountForBotschafter($fs_id);

		$quiz_rolle = RoleType::FOODSHARER;
		if ($passed_fs_quiz) {
			$quiz_rolle = RoleType::FOODSAVER;
		}
		if ($passed_bib_quiz) {
			$quiz_rolle = RoleType::STORE_COORDINATOR;
		}
		if ($passed_bot_quiz) {
			$quiz_rolle = RoleType::AMBASSADOR;
		}

		$this->quizGateway->setRole($fs_id, $quiz_rolle);

		$hastodo_id = 0;
		if (
			$fs_role == RoleType::FOODSAVER && !$passed_fs_quiz
		) {
			$hastodo_id = RoleType::FOODSAVER;
		} elseif (
			($fs_role > RoleType::FOODSAVER || $count_verantwortlich > 0) && !$passed_bib_quiz
		) {
			$hastodo_id = RoleType::STORE_COORDINATOR;
		} elseif (
			($fs_role > RoleType::STORE_COORDINATOR || $count_botschafter > 0) && !$passed_bot_quiz
		) {
			$hastodo_id = RoleType::AMBASSADOR;
		}

		return $hastodo_id;
	}
}
