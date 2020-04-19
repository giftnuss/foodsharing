<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Store\StoreGateway;

class QuizHelper
{
	private $quizSessionGateway;
	private $storeGateway;
	private $foodsaverGateway;

	public function __construct(QuizSessionGateway $quizSessionGateway, StoreGateway $storeGateway, FoodsaverGateway $foodsaverGateway)
	{
		$this->quizSessionGateway = $quizSessionGateway;
		$this->storeGateway = $storeGateway;
		$this->foodsaverGateway = $foodsaverGateway;
	}

	public function refreshQuizData(int $fsId, int $fsRole): int
	{
		$this->refreshFsQuizRole($fsId);

		return $this->nextQuizTodo($fsId, $fsRole);
	}

	public function refreshFsQuizRole(int $fsId): int
	{
		foreach ([Role::AMBASSADOR, Role::STORE_MANAGER, Role::FOODSAVER] as $quizRole) {
			if ($this->quizSessionGateway->hasPassedQuiz($fsId, $quizRole)) {
				return $this->foodsaverGateway->setQuizRole($fsId, $quizRole);
			}
		}

		return $this->foodsaverGateway->setQuizRole($fsId, Role::FOODSHARER);
	}

	public function nextQuizTodo(int $fsId, int $fsRole): int
	{
		$doesManageStores = (int)$this->storeGateway->getStoreCountForBieb($fsId) > 0;

		if ($fsRole == Role::FOODSAVER && !$this->quizSessionGateway->hasPassedQuiz($fsId, Role::FOODSAVER)) {
			return Role::FOODSAVER;
		} elseif (($fsRole > Role::FOODSAVER || $doesManageStores) && !$this->quizSessionGateway->hasPassedQuiz($fsId, Role::STORE_MANAGER)) {
			return Role::STORE_MANAGER;
		} elseif (($fsRole > Role::STORE_MANAGER || $this->foodsaverGateway->isAdminForAnyGroupOrRegion($fsId)) && !$this->quizSessionGateway->hasPassedQuiz($fsId, Role::AMBASSADOR)) {
			return Role::AMBASSADOR;
		}

		return Role::FOODSHARER;
	}
}
