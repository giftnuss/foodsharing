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
		$hasPassedFoodsaverQuiz = $this->quizGateway->hasPassedQuiz($fsId, Role::FOODSAVER);
		$hasPassedStoreManagerQuiz = $this->quizGateway->hasPassedQuiz($fsId, Role::STORE_MANAGER);
		$hasPassedAmbassadorQuiz = $this->quizGateway->hasPassedQuiz($fsId, Role::AMBASSADOR);

		$quizRole = Role::FOODSHARER;
		if ($hasPassedAmbassadorQuiz) {
			$quizRole = Role::AMBASSADOR;
		} elseif ($hasPassedStoreManagerQuiz) {
			$quizRole = Role::STORE_MANAGER;
		} elseif ($hasPassedFoodsaverQuiz) {
			$quizRole = Role::FOODSAVER;
		}

		$doesManageStores = (int)$this->storeGateway->getStoreCountForBieb($fsId) > 0;
		$doesRepresentRegions = (int)$this->foodsaverGateway->getBezirkCountForBotschafter($fsId) > 0;

		$this->quizGateway->setFsQuizRole($fsId, $quizRole);

		if ($fsRole == Role::FOODSAVER && !$hasPassedFoodsaverQuiz) {
			return Role::FOODSAVER;
		} elseif (($fsRole > Role::FOODSAVER || $doesManageStores) && !$hasPassedStoreManagerQuiz) {
			return Role::STORE_MANAGER;
		} elseif (($fsRole > Role::STORE_MANAGER || $doesRepresentRegions) && !$hasPassedAmbassadorQuiz) {
			return Role::AMBASSADOR;
		}

		return Role::FOODSHARER;
	}
}
