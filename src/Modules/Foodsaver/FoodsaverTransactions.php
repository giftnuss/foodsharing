<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\Store\StoreModel;

class FoodsaverTransactions
{
	private $foodsaverGateway;
	private $quizSessionGateway;

	public function __construct(
		FoodsaverGateway $foodsaverGateway,
		QuizSessionGateway $quizSessionGateway
	) {
		$this->foodsaverGateway = $foodsaverGateway;
		$this->quizSessionGateway = $quizSessionGateway;
	}

	public function downgradeAndBlockForQuizPermanently(int $fsId, StoreModel $storeModel): int
	{
		$this->quizSessionGateway->blockUserForQuiz($fsId, Role::FOODSAVER);

		return $this->foodsaverGateway->downgradePermanently($fsId, $storeModel);
	}
}
