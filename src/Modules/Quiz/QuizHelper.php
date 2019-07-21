<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
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
		$passed_fs_quiz = $this->quizGateway->hasPassedQuiz($fs_id, Role::FOODSAVER);
		$passed_bib_quiz = $this->quizGateway->hasPassedQuiz($fs_id, Role::STORE_MANAGER);
		$passed_bot_quiz = $this->quizGateway->hasPassedQuiz($fs_id, Role::AMBASSADOR);

		$count_verantwortlich = $this->storeGateway->getStoreCountForBieb($fs_id);
		$count_botschafter = $this->foodsaverGateway->getBezirkCountForBotschafter($fs_id);

		$quiz_rolle = Role::FOODSHARER;
		if ($passed_fs_quiz) {
			$quiz_rolle = Role::FOODSAVER;
		}
		if ($passed_bib_quiz) {
			$quiz_rolle = Role::STORE_MANAGER;
		}
		if ($passed_bot_quiz) {
			$quiz_rolle = Role::AMBASSADOR;
		}

		$this->quizGateway->setRole($fs_id, $quiz_rolle);

		$hastodo_id = 0;
		if (
			$fs_role == Role::FOODSAVER && !$passed_fs_quiz
		) {
			$hastodo_id = Role::FOODSAVER;
		} elseif (
			($fs_role > Role::FOODSAVER || $count_verantwortlich > 0) && !$passed_bib_quiz
		) {
			$hastodo_id = Role::STORE_MANAGER;
		} elseif (
			($fs_role > Role::STORE_MANAGER || $count_botschafter > 0) && !$passed_bot_quiz
		) {
			$hastodo_id = Role::AMBASSADOR;
		}

		return $hastodo_id;
	}
}
