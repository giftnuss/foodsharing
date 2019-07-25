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

	public function refreshQuizData(int $fsId, int $fsRole): int
	{
		$this->refreshFsQuizRole($fsId);

		return $this->nextQuizTodo($fsId, $fsRole);
	}

	public function refreshFsQuizRole(int $fsId): int
	{
		foreach ([Role::AMBASSADOR, Role::STORE_MANAGER, Role::FOODSAVER] as $quizRole) {
			if ($this->quizGateway->hasPassedQuiz($fsId, $quizRole)) {
				return $this->foodsaverGateway->setQuizRole($fsId, $quizRole);
			}
		}

		return $this->foodsaverGateway->setQuizRole($fsId, Role::FOODSHARER);
	}

	public function nextQuizTodo(int $fsId, int $fsRole): int
	{
		$doesManageStores = (int)$this->storeGateway->getStoreCountForBieb($fsId) > 0;
		$doesRepresentRegions = (int)$this->foodsaverGateway->getBezirkCountForBotschafter($fsId) > 0;

		if ($fsRole == Role::FOODSAVER && !$this->quizGateway->hasPassedQuiz($fsId, Role::FOODSAVER)) {
			return Role::FOODSAVER;
		} elseif (($fsRole > Role::FOODSAVER || $doesManageStores) && !$this->quizGateway->hasPassedQuiz($fsId, Role::STORE_MANAGER)) {
			return Role::STORE_MANAGER;
		} elseif (($fsRole > Role::STORE_MANAGER || $doesRepresentRegions) && !$this->quizGateway->hasPassedQuiz($fsId, Role::AMBASSADOR)) {
			return Role::AMBASSADOR;
		}

		return Role::FOODSHARER;
	}
}
